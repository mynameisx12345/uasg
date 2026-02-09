<?php
/**
 * Task-File Validation Service
 * Uses NLP + ML to validate if uploaded file matches task requirements
 */

class TaskFileValidator {
    
    private $db;
    private $mlService;
    private $textExtractor;
    
    public function __construct() {
        $this->db = Database::getInstance();
        require_once __DIR__ . '/ml_service_incremental.php';
        require_once __DIR__ . '/text_extractor.php';
        $this->mlService = new IncrementalMLService();
        $this->textExtractor = new TextExtractor();
    }
    
    /**
     * Validate if uploaded file matches task requirements
     * 
     * @param int $taskId Task ID
     * @param string $filePath Path to uploaded file
     * @param string $mimeType File MIME type
     * @return array Validation result with match score and reasons
     */
    public function validateFileForTask($taskId, $filePath, $mimeType) {
        try {
            // Get task details
            $task = $this->db->selectOne(
                "SELECT t.*, tc.category_name as task_category 
                 FROM task_tbl t
                 LEFT JOIN task_category_tbl tc ON t.task_category_id = tc.task_category_id
                 WHERE t.task_id = ?",
                [$taskId]
            );
            
            if (!$task) {
                return [
                    'success' => false,
                    'error' => 'Task not found'
                ];
            }
            
            // Extract text from uploaded file
            $extractResult = $this->textExtractor->extractText($filePath, $mimeType);
            
            if (is_array($extractResult) && !$extractResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Could not extract text from file: ' . ($extractResult['error'] ?? 'Unknown error')
                ];
            }
            
            $fileText = is_array($extractResult) ? $extractResult['text'] : $extractResult;
            
            if (empty($fileText) || strlen(trim($fileText)) < 20) {
                return [
                    'success' => false,
                    'error' => 'File appears to be empty or contains insufficient text for validation'
                ];
            }
            
            // Perform validation checks
            $validationResults = [];
            
            // 1. Keyword Match Analysis
            $keywordMatch = $this->analyzeKeywordMatch($task, $fileText);
            $validationResults['keyword_match'] = $keywordMatch;
            
            // 2. ML Category Match
            $categoryMatch = $this->analyzeMLCategoryMatch($task, $fileText);
            $validationResults['category_match'] = $categoryMatch;
            
            // 3. Content Relevance Score
            $relevanceScore = $this->calculateRelevanceScore($task, $fileText);
            $validationResults['relevance_score'] = $relevanceScore;
            
            // Calculate overall match score (0-100)
            $overallScore = $this->calculateOverallScore($validationResults);
            
            // Determine if file is acceptable
            $isValid = $overallScore >= 60; // 60% threshold
            $confidence = $this->getConfidenceLevel($overallScore);
            
            return [
                'success' => true,
                'is_valid' => $isValid,
                'overall_score' => $overallScore,
                'confidence' => $confidence,
                'validation_details' => $validationResults,
                'task_info' => [
                    'title' => $task['task_title'],
                    'category' => $task['task_category'],
                    'description' => $task['task_description']
                ],
                'file_info' => [
                    'word_count' => str_word_count($fileText),
                    'preview' => substr($fileText, 0, 500)
                ],
                'recommendation' => $this->generateRecommendation($isValid, $overallScore, $validationResults)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Validation failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Analyze keyword match between task and file
     */
    private function analyzeKeywordMatch($task, $fileText) {
        // Extract keywords from task title and description
        $taskText = $task['task_title'] . ' ' . $task['task_description'];
        $taskKeywords = $this->extractKeywords($taskText);
        
        // Extract keywords from file
        $fileKeywords = $this->extractKeywords($fileText);
        
        // Calculate overlap
        $matchingKeywords = array_intersect($taskKeywords, $fileKeywords);
        $matchPercentage = count($taskKeywords) > 0 
            ? (count($matchingKeywords) / count($taskKeywords)) * 100 
            : 0;
        
        return [
            'score' => round($matchPercentage, 2),
            'task_keywords' => array_slice($taskKeywords, 0, 10),
            'matching_keywords' => array_slice($matchingKeywords, 0, 10),
            'missing_keywords' => array_slice(array_diff($taskKeywords, $matchingKeywords), 0, 5)
        ];
    }
    
    /**
     * Analyze if ML-predicted category matches task category
     */
    private function analyzeMLCategoryMatch($task, $fileText) {
        $prediction = $this->mlService->predict($fileText);
        
        if (!$prediction['success']) {
            return [
                'score' => 0,
                'predicted_category' => null,
                'task_category' => $task['task_category'],
                'match' => false,
                'confidence' => 0,
                'error' => $prediction['error'] ?? 'ML prediction failed'
            ];
        }
        
        $predictedCategory = strtolower(trim($prediction['category']));
        $taskCategory = strtolower(trim($task['task_category']));
        
        // Check if categories match or are similar
        $isMatch = ($predictedCategory === $taskCategory);
        $score = $isMatch ? 100 : $this->calculateCategorySimilarity($predictedCategory, $taskCategory);
        
        return [
            'score' => round($score, 2),
            'predicted_category' => $prediction['category'],
            'task_category' => $task['task_category'],
            'match' => $isMatch,
            'confidence' => round($prediction['confidence'] * 100, 2)
        ];
    }
    
    /**
     * Calculate content relevance score
     */
    private function calculateRelevanceScore($task, $fileText) {
        $score = 0;
        $reasons = [];
        
        // 1. Check if task title words appear in file (40 points max)
        $titleWords = $this->extractSignificantWords($task['task_title']);
        $titleMatches = 0;
        foreach ($titleWords as $word) {
            if (stripos($fileText, $word) !== false) {
                $titleMatches++;
            }
        }
        $titleScore = count($titleWords) > 0 
            ? ($titleMatches / count($titleWords)) * 40 
            : 0;
        $score += $titleScore;
        
        if ($titleScore >= 30) {
            $reasons[] = "Title keywords strongly present";
        } elseif ($titleScore >= 15) {
            $reasons[] = "Some title keywords found";
        } else {
            $reasons[] = "Few title keywords found";
        }
        
        // 2. Check description keywords (30 points max)
        $descWords = $this->extractSignificantWords($task['task_description']);
        $descMatches = 0;
        foreach ($descWords as $word) {
            if (stripos($fileText, $word) !== false) {
                $descMatches++;
            }
        }
        $descScore = count($descWords) > 0 
            ? ($descMatches / count($descWords)) * 30 
            : 0;
        $score += $descScore;
        
        // 3. Document length appropriateness (30 points max)
        $wordCount = str_word_count($fileText);
        if ($wordCount >= 100 && $wordCount <= 10000) {
            $lengthScore = 30;
            $reasons[] = "Document length appropriate";
        } elseif ($wordCount >= 50) {
            $lengthScore = 20;
            $reasons[] = "Document is short but acceptable";
        } elseif ($wordCount < 50) {
            $lengthScore = 5;
            $reasons[] = "Document too short";
        } else {
            $lengthScore = 15;
            $reasons[] = "Document very long";
        }
        $score += $lengthScore;
        
        return [
            'score' => round($score, 2),
            'reasons' => $reasons,
            'word_count' => $wordCount
        ];
    }
    
    /**
     * Calculate overall validation score
     */
    private function calculateOverallScore($validationResults) {
        $weights = [
            'keyword_match' => 0.30,      // 30% weight
            'category_match' => 0.40,     // 40% weight (most important)
            'relevance_score' => 0.30     // 30% weight
        ];
        
        $totalScore = 
            ($validationResults['keyword_match']['score'] * $weights['keyword_match']) +
            ($validationResults['category_match']['score'] * $weights['category_match']) +
            ($validationResults['relevance_score']['score'] * $weights['relevance_score']);
        
        return round($totalScore, 2);
    }
    
    /**
     * Extract keywords from text
     */
    private function extractKeywords($text) {
        // Common stop words to exclude
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 
                      'of', 'with', 'by', 'from', 'is', 'are', 'was', 'were', 'be', 'been',
                      'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'should',
                      'could', 'can', 'may', 'might', 'this', 'that', 'these', 'those', 'create'];
        
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
        $words = explode(' ', $text);
        
        $keywords = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) >= 3 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }
        
        return array_unique($keywords);
    }
    
    /**
     * Extract significant words (longer, more meaningful)
     */
    private function extractSignificantWords($text) {
        $keywords = $this->extractKeywords($text);
        return array_filter($keywords, function($word) {
            return strlen($word) >= 4; // Only words with 4+ characters
        });
    }
    
    /**
     * Calculate similarity between two categories
     */
    private function calculateCategorySimilarity($cat1, $cat2) {
        similar_text($cat1, $cat2, $percent);
        return $percent;
    }
    
    /**
     * Get confidence level description
     */
    private function getConfidenceLevel($score) {
        if ($score >= 85) return 'Very High';
        if ($score >= 70) return 'High';
        if ($score >= 60) return 'Moderate';
        if ($score >= 40) return 'Low';
        return 'Very Low';
    }
    
    /**
     * Generate recommendation message
     */
    private function generateRecommendation($isValid, $score, $validationResults) {
        if (!$isValid) {
            $reasons = [];
            
            if ($validationResults['category_match']['score'] < 50) {
                $reasons[] = "Document category (" . $validationResults['category_match']['predicted_category'] . 
                           ") does not match task category (" . $validationResults['category_match']['task_category'] . ")";
            }
            
            if ($validationResults['keyword_match']['score'] < 30) {
                $reasons[] = "Missing important keywords from task title/description";
                if (!empty($validationResults['keyword_match']['missing_keywords'])) {
                    $reasons[] = "Missing: " . implode(', ', $validationResults['keyword_match']['missing_keywords']);
                }
            }
            
            if ($validationResults['relevance_score']['word_count'] < 50) {
                $reasons[] = "Document is too short for proper validation";
            }
            
            return [
                'status' => 'rejected',
                'message' => 'This file does not appear to match the task requirements.',
                'reasons' => $reasons,
                'suggestion' => 'Please upload a document that addresses: ' . $validationResults['keyword_match']['task_keywords'][0] ?? 'the task requirements'
            ];
        }
        
        if ($score >= 85) {
            return [
                'status' => 'excellent',
                'message' => 'Excellent match! This file appears to perfectly match the task requirements.',
                'reasons' => ['All validation checks passed with high confidence'],
                'suggestion' => null
            ];
        }
        
        if ($score >= 70) {
            return [
                'status' => 'good',
                'message' => 'Good match! This file appears to be appropriate for the task.',
                'reasons' => ['Most validation checks passed'],
                'suggestion' => null
            ];
        }
        
        return [
            'status' => 'acceptable',
            'message' => 'Acceptable match. The file meets minimum requirements.',
            'reasons' => ['Basic validation checks passed'],
            'suggestion' => 'Consider reviewing if this is the best file for this task.'
        ];
    }
}
?>
