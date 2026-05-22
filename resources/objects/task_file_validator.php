<?php
/**
 * Task-File Validation Service
 *
 * Simple ML-based approach:
 *   1. Look at the task title + description to figure out what document type is expected
 *      (by scanning for any known ML category name mentioned in the task text).
 *   2. Run the uploaded file through the ML model to get its predicted document category.
 *   3. Accept if the predicted category matches the expected one; reject otherwise.
 *
 * If the ML model is unavailable, falls back to a plain text search so uploads are
 * never blocked purely due to the model being offline.
 */

class TaskFileValidator {

    private $db;
    private $mlService;
    private $textExtractor;

    public function __construct() {
        $this->db = Database::getInstance();
        require_once __DIR__ . '/ml_service_incremental.php';
        require_once __DIR__ . '/text_extractor.php';
        $this->mlService    = new IncrementalMLService();
        $this->textExtractor = new TextExtractor();
    }

    // -------------------------------------------------------------------------
    // Public entry point
    // -------------------------------------------------------------------------

    /**
     * Validate whether the uploaded file matches what the task is asking for.
     *
     * @param int    $taskId   Task ID
     * @param string $filePath Absolute / server-relative path to the uploaded file
     * @param string $mimeType MIME type of the uploaded file
     * @return array
     */
    public function validateFileForTask($taskId, $filePath, $mimeType) {
        try {
            // 1. Load task
            $task = $this->db->selectOne(
                "SELECT t.*, tc.task_category AS task_category
                 FROM task_tbl t
                 LEFT JOIN task_category_tbl tc ON t.task_category_id = tc.task_category_id
                 WHERE t.task_id = ?",
                [$taskId]
            );

            if (!$task) {
                return ['success' => false, 'error' => 'Task not found'];
            }

            // 2. Extract text from the uploaded file
            $extractResult = $this->textExtractor->extractText($filePath, $mimeType);

            if (is_array($extractResult) && !($extractResult['success'] ?? true)) {
                return [
                    'success' => false,
                    'error'   => 'Could not extract text from file: ' . ($extractResult['error'] ?? 'Unknown error')
                ];
            }

            $fileText = is_array($extractResult) ? ($extractResult['text'] ?? '') : $extractResult;

            if (empty($fileText) || strlen(trim($fileText)) < 20) {
                return [
                    'success' => false,
                    'error'   => 'File appears to be empty or contains insufficient text for validation'
                ];
            }

            // 3. Core validation: ML category check
            $check = $this->checkDocumentType($task, $fileText);

            // 4. Build response
            $isValid    = $check['is_match'];
            $score      = $check['is_match'] ? 100 : 0;
            $confidence = $this->getConfidenceLevel($check['file_confidence'] * 100);

            return [
                'success'          => true,
                'is_valid'         => $isValid,
                'overall_score'    => $score,
                'confidence'       => $confidence,
                'validation_details' => [
                    'expected_document_type'  => $check['expected_type'],
                    'predicted_document_type' => $check['predicted_type'],
                    'file_confidence'         => round($check['file_confidence'] * 100, 1),
                    'ml_used'                 => $check['ml_used'],
                    'fallback_text_search'    => $check['fallback_text_search'],
                ],
                'task_info' => [
                    'title'       => $task['task_title'],
                    'category'    => $task['task_category'],
                    'description' => $task['task_description'],
                ],
                'file_info' => [
                    'word_count' => str_word_count($fileText),
                    'preview'    => substr($fileText, 0, 500),
                ],
                'recommendation' => $this->generateRecommendation($isValid, $check),
            ];

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Validation failed: ' . $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Core logic
    // -------------------------------------------------------------------------

    /**
     * Determine what document type the task expects, then check the uploaded file.
     *
     * Steps:
     *   A. Get the list of categories the ML model was trained on.
     *   B. Scan the task title + description to find which category is mentioned.
     *   C. Run the file through the ML model → predicted category.
     *   D. Compare expected vs. predicted.
     *
     * @return array {
     *   expected_type, predicted_type, is_match,
     *   file_confidence, ml_used, fallback_text_search
     * }
     */
    private function checkDocumentType($task, $fileText) {
        $taskText      = trim(($task['task_title'] ?? '') . ' ' . ($task['task_description'] ?? ''));
        $knownCategories = $this->getKnownMLCategories();

        // --- A. Find the expected document type from the task text ---------------
        $expectedType = $this->detectExpectedType($taskText, $knownCategories);

        // --- B. Classify the uploaded file via ML --------------------------------
        $prediction     = $this->mlService->predict($fileText);
        $mlSuccess      = !empty($prediction['success']);
        $rawCategory    = $mlSuccess ? strtolower(trim($prediction['category'] ?? '')) : '';
        $fileConfidence = $mlSuccess ? ($prediction['confidence'] ?? 0) : 0;

        // Apply confidence threshold — below it the prediction is unreliable,
        // treat the same as "others" (unrecognized document).
        $confidenceThreshold = floatval($this->mlService->getSetting('ml_confidence_threshold') ?? 60) / 100;
        $predictedType = ($mlSuccess && $fileConfidence >= $confidenceThreshold) ? $rawCategory : ($mlSuccess ? 'others' : '');

        // --- C. If ML worked, evaluate the result ---------------------------------
        if ($mlSuccess && $predictedType !== '') {

            // "others" means the ML cannot classify this file into any known document
            // type — always reject, regardless of what the task expects.
            if ($predictedType === 'others') {
                return [
                    'expected_type'        => $expectedType ?: 'a recognized document type',
                    'predicted_type'       => 'others',
                    'is_match'             => false,
                    'file_confidence'      => $fileConfidence,
                    'ml_used'              => true,
                    'fallback_text_search' => false,
                    'predicted_as_others'  => true,
                ];
            }

            if ($expectedType !== '') {
                // Normal case: ML classified the file and we know what is expected.
                $isMatch = ($predictedType === $expectedType);
                return [
                    'expected_type'        => $expectedType,
                    'predicted_type'       => $predictedType,
                    'is_match'             => $isMatch,
                    'file_confidence'      => $fileConfidence,
                    'ml_used'              => true,
                    'fallback_text_search' => false,
                ];
            }

            // expectedType is empty — the task mentions a document type that the ML
            // model has not been trained on (e.g. "Activity Design").
            // We cannot do a category-to-category comparison, but we can still check
            // whether the significant words from the task title appear in the
            // predicted category name as a rough heuristic.
            $taskTitleWords = $this->extractSignificantWords($taskText);
            $categoryMatchesTitle = false;
            foreach ($taskTitleWords as $word) {
                if (stripos($predictedType, $word) !== false) {
                    $categoryMatchesTitle = true;
                    break;
                }
            }

            // Also surface what the task is likely asking for from the title itself
            // so the rejection message is meaningful.
            $likelyExpected = $this->guessExpectedTypeFromTitle($taskText);

            return [
                'expected_type'        => $likelyExpected ?: null,
                'predicted_type'       => $predictedType,
                'is_match'             => $categoryMatchesTitle,
                'file_confidence'      => $fileConfidence,
                'ml_used'              => true,
                'fallback_text_search' => false,
                'untrained_category'   => true,   // task expects a type not in ML training data
            ];
        }

        // --- D. ML unavailable: fall back to plain text search -------------------
        // Check if the expected category name literally appears in the file text.
        $fallbackMatch = false;
        if ($expectedType !== '') {
            $fallbackMatch = (stripos($fileText, $expectedType) !== false);
        } else {
            // Cannot determine expected type and ML failed → accept (benefit of the doubt)
            $fallbackMatch = true;
        }

        return [
            'expected_type'      => $expectedType,
            'predicted_type'     => null,   // ML unavailable
            'is_match'           => $fallbackMatch,
            'file_confidence'    => 0,
            'ml_used'            => false,
            'fallback_text_search' => true,
        ];
    }

    /**
     * Scan $taskText for any known ML category name and return the first match.
     * Returns empty string if none found.
     */
    private function detectExpectedType($taskText, array $knownCategories) {
        $taskLower = strtolower($taskText);
        // Exclude "others" from detection — it is a catch-all, not a real expected type
        foreach ($knownCategories as $cat) {
            if (strtolower($cat) === 'others') continue;
            // Match as whole word so "letter" doesn't match "newsletter"
            if (preg_match('/\b' . preg_quote(strtolower($cat), '/') . '\b/', $taskLower)) {
                return strtolower($cat);
            }
        }
        return '';
    }

    /**
     * Extract the most likely document type noun from the task title when it
     * does not match any trained ML category.
     * Strips common verb/filler words and returns the remaining phrase.
     */
    private function guessExpectedTypeFromTitle($taskText) {
        $fillers = [
            'submit', 'provide', 'upload', 'give', 'send', 'create', 'make',
            'write', 'prepare', 'produce', 'attach', 'your', 'the', 'a', 'an',
            'for', 'on', 'about', 'of', 'and', 'or', 'to', 'me', 'us',
            'please', 'kindly', 'this', 'that', 'based', 'yesterday',
            'meeting', 'today', 'last', 'week', 'month',
        ];

        $text  = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', ' ', $taskText));
        $words = array_filter(explode(' ', $text), function($w) use ($fillers) {
            return strlen($w) >= 3 && !in_array($w, $fillers);
        });

        // Return up to the first 3 significant words as the guessed type
        $significant = array_slice(array_values($words), 0, 3);
        return implode(' ', $significant);
    }

    /**
     * Extract significant words (4+ chars, no stop words) from text.
     */
    private function extractSignificantWords($text) {
        $stopWords = [
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'from', 'is', 'are', 'was', 'were', 'be', 'been',
            'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'should',
            'could', 'can', 'may', 'might', 'this', 'that', 'these', 'those',
            'submit', 'give', 'send', 'create', 'make', 'write', 'your',
        ];
        $text  = strtolower(preg_replace('/[^a-z0-9\s]/i', ' ', $text));
        $words = array_unique(array_filter(explode(' ', $text), function($w) use ($stopWords) {
            return strlen($w) >= 4 && !in_array($w, $stopWords);
        }));
        return array_values($words);
    }

    /**
     * Retrieve the list of document categories the active ML model knows about.
     * Reads the `categories` JSON column from ml_models_tbl for the active model.
     */
    private function getKnownMLCategories() {
        try {
            $modelId = $this->mlService->getSetting('active_ml_model_id');
            if (!$modelId) return [];

            $model = $this->db->selectOne(
                "SELECT categories FROM ml_models_tbl WHERE model_id = ?",
                [$modelId]
            );

            if (!$model || empty($model['categories'])) return [];

            $cats = json_decode($model['categories'], true);
            return is_array($cats) ? $cats : [];

        } catch (Exception $e) {
            return [];
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Get confidence level description from a 0-100 score.
     */
    private function getConfidenceLevel($score) {
        if ($score >= 85) return 'Very High';
        if ($score >= 70) return 'High';
        if ($score >= 50) return 'Moderate';
        if ($score >= 30) return 'Low';
        return 'Very Low';
    }

    /**
     * Build a human-readable recommendation based on validation result.
     */
    private function generateRecommendation($isValid, $check) {
        $expected   = $check['expected_type']       ?? null;
        $predicted  = $check['predicted_type']      ?? null;
        $fallback   = $check['fallback_text_search'] ?? false;
        $mlUsed     = $check['ml_used']             ?? false;
        $asOthers   = $check['predicted_as_others'] ?? false;
        $untrained  = $check['untrained_category']  ?? false;

        if ($isValid) {
            if ($expected === null) {
                return [
                    'status'     => 'accepted',
                    'message'    => 'File accepted. The task did not specify a required document type.',
                    'reasons'    => ['No specific document type requirement detected in the task'],
                    'suggestion' => null,
                ];
            }
            return [
                'status'     => 'accepted',
                'message'    => 'File accepted. The document matches what the task requires.',
                'reasons'    => [
                    $mlUsed
                        ? "ML classified your file as \"" . ucfirst($predicted) . "\", which matches the expected type \"" . ucfirst($expected) . "\""
                        : "The word \"" . ucfirst($expected) . "\" was found in the document content, matching the task requirement"
                ],
                'suggestion' => null,
            ];
        }

        // --- Rejected ---
        $reasons = [];

        if ($asOthers) {
            // ML explicitly classified the file as "others" (unrecognized document)
            $reasons[] = "The ML model could not recognize this file as a valid document type — it was classified as \"Others\"";
            if ($expected && $expected !== 'a recognized document type') {
                $reasons[] = "The task requires a \"" . ucfirst($expected) . "\"";
            }
            return [
                'status'     => 'rejected',
                'message'    => 'File rejected. The document was not recognized as a valid document type.',
                'reasons'    => $reasons,
                'suggestion' => $expected && $expected !== 'a recognized document type'
                    ? 'Please upload a valid ' . ucfirst($expected) . ' document.'
                    : 'Please upload the correct type of document for this task.',
            ];
        }

        if ($untrained && $expected !== null) {
            // Task expects a type not in ML training data (e.g. "Activity Design")
            // and ML classified the file as something else entirely
            $reasons[] = "The task requires a \"" . ucfirst($expected) . "\" but the ML classified your file as \"" . ucfirst($predicted) . "\"";
            $reasons[] = "Note: \"" . ucfirst($expected) . "\" is not yet in the ML training data — train the model with " . ucfirst($expected) . " documents for more accurate detection";
            return [
                'status'     => 'rejected',
                'message'    => 'File rejected. The document type does not match the task requirement.',
                'reasons'    => $reasons,
                'suggestion' => 'Please upload a ' . ucfirst($expected) . ' document.',
            ];
        }

        if ($expected !== null && $predicted !== null) {
            $reasons[] = "The task requires a \"" . ucfirst($expected) . "\" but the ML classified your file as \"" . ucfirst($predicted) . "\"";
        } elseif ($expected !== null && $fallback) {
            $reasons[] = "The task requires a \"" . ucfirst($expected) . "\" but that word was not found in your file";
        }

        return [
            'status'     => 'rejected',
            'message'    => 'This file does not appear to match the task requirements.',
            'reasons'    => $reasons ?: ['Document type does not match what the task is asking for'],
            'suggestion' => $expected
                ? 'Please upload a document that is a ' . ucfirst($expected) . '.'
                : 'Please upload the correct document type for this task.',
        ];
    }
}
?>
