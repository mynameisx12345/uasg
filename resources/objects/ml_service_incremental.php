<?php
/**
 * Incremental ML Classification Service
 * PHP wrapper for incremental Python ML classifier
 * Supports continuous learning from uploaded files
 */

// Ensure MLClassificationService is loaded
require_once __DIR__ . '/ml_service.php';

class IncrementalMLService extends MLClassificationService {
    
    private $incrementalScriptPath;
    private $trainingQueueDir;
    
    public function __construct() {
        parent::__construct();
        
        // Path to incremental ML script
        $this->incrementalScriptPath = __DIR__ . '/../ml/incremental_ml_classifier.py';
        
        // Directory for queued training samples
        $this->trainingQueueDir = __DIR__ . '/../../uploads/ml_training_queue/';
        
        if (!file_exists($this->trainingQueueDir)) {
            mkdir($this->trainingQueueDir, 0755, true);
        }
    }
    
    /**
     * Train initial incremental model from CSV
     * This creates the base model that will learn incrementally
     */
    public function trainIncrementalModel($datasetId, $modelName, $trainedBy) {
        try {
            // Get dataset info
            $dataset = $this->db->selectOne(
                "SELECT * FROM ml_training_datasets_tbl WHERE dataset_id = ?",
                [$datasetId]
            );
            
            if (!$dataset) {
                throw new Exception('Dataset not found');
            }
            
            $csvPath = __DIR__ . '/../../' . $dataset['file_path'];
            
            if (!file_exists($csvPath)) {
                throw new Exception('Dataset file not found');
            }
            
            // Generate unique model filename
            $modelFilename = uniqid('incremental_model_') . '_' . time();
            $modelPath = $this->modelsDir . $modelFilename . '.pkl';
            
            // Normalize paths
            $csvPathNorm = str_replace('\\', '/', realpath($csvPath));
            $modelPathNorm = str_replace('\\', '/', $modelPath);
            $scriptDirNorm = str_replace('\\', '/', dirname($this->incrementalScriptPath));
            
            // Build Python command for initial training
            $command = sprintf(
                '"%s" "%s" initial_train --csv "%s" --model "%s" 2>&1',
                $this->pythonPath,
                $this->incrementalScriptPath,
                $csvPathNorm,
                $modelPathNorm
            );
            
            $output = shell_exec($command);
            
            if (empty($output)) {
                throw new Exception('Training failed: No output from Python');
            }
            
            $result = json_decode($output, true);
            
            if (!$result || !$result['success']) {
                throw new Exception($result['error'] ?? 'Training failed: ' . substr($output, 0, 500));
            }
            
            // Deactivate other models
            $this->db->execute("UPDATE ml_models_tbl SET is_active = 0");
            
            // Save model to database with incremental flag
            $query = "INSERT INTO ml_models_tbl 
                      (model_name, dataset_id, model_type, model_path, vectorizer_path,
                       accuracy_score, precision_score, recall_score, f1_score,
                       categories, training_samples, test_samples, is_active, trained_by,
                       is_incremental, total_incremental_samples)
                      VALUES (?, ?, 'incremental_sgd', ?, NULL, ?, ?, ?, ?, ?, ?, ?, 1, ?, 1, ?)";
            
            $categoriesJson = json_encode($result['categories']);
            
            $modelId = $this->db->insert($query, [
                $modelName,
                $datasetId,
                'uploads/ml_models/' . $modelFilename . '.pkl',
                $result['accuracy'],
                $result['precision'],
                $result['recall'],
                $result['f1_score'],
                $categoriesJson,
                $result['training_samples'],
                $result['test_samples'],
                $trainedBy,
                $result['training_samples']
            ]);
            
            // Update settings
            $this->updateSetting('active_ml_model_id', $modelId);
            $this->updateSetting('incremental_learning_enabled', '1');
            
            return [
                'success' => true,
                'model_id' => $modelId,
                'accuracy' => $result['accuracy'],
                'categories' => $result['categories'],
                'training_type' => 'initial_incremental'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Add training sample from uploaded file
     * This queues the sample for incremental training
     */
    public function addTrainingSample($text, $category, $fileUploadId = null, $confidence = null) {
        try {
            // Check if incremental learning is enabled
            $enabled = $this->getSetting('incremental_learning_enabled');
            if ($enabled != '1') {
                return [
                    'success' => false,
                    'error' => 'Incremental learning not enabled'
                ];
            }
            
            // Save to training queue in database
            $query = "INSERT INTO ml_incremental_training_queue_tbl 
                      (file_upload_id, training_text, confirmed_category, prediction_confidence, status)
                      VALUES (?, ?, ?, ?, 'pending')";
            
            $queueId = $this->db->insert($query, [
                $fileUploadId,
                $text,
                $category,
                $confidence
            ]);
            
            return [
                'success' => true,
                'queue_id' => $queueId,
                'message' => 'Sample queued for incremental training'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Process queued training samples.
     * Works with BOTH regular SVM models and incremental SGD models:
     *  - SGD (is_incremental = 1): uses partial_fit via IncrementalMLClassifier
     *  - SVM / other (is_incremental = 0): rebuilds the full CSV with original
     *    data + queued samples, then retrains the model in-place.
     */
    public function processTrainingQueue($batchSize = 10) {
        try {
            error_reporting(E_ALL & ~E_DEPRECATED);
            $enabled = $this->getSetting('incremental_learning_enabled');
            if ($enabled != '1') {
                return ['success' => false, 'error' => 'Incremental learning not enabled'];
            }

            $modelId = $this->getSetting('active_ml_model_id');
            if (!$modelId) throw new Exception('No active ML model');

            // Accept ANY active model (incremental or regular)
            $model = $this->db->selectOne(
                "SELECT * FROM ml_models_tbl WHERE model_id = ? AND is_active = 1",
                [$modelId]
            );
            if (!$model) throw new Exception('Active model not found');

            // Get pending samples
            $samples = $this->db->select(
                "SELECT * FROM ml_incremental_training_queue_tbl
                 WHERE status = 'pending'
                 ORDER BY created_at ASC
                 LIMIT ?",
                [$batchSize]
            );

            if (empty($samples)) {
                return ['success' => true, 'message' => 'No pending samples to process', 'samples_processed' => 0];
            }

            $texts      = array_column($samples, 'training_text');
            $categories = array_column($samples, 'confirmed_category');
            $queueIds   = array_column($samples, 'queue_id');

            $modelPath = __DIR__ . '/../../' . $model['model_path'];
            if (!file_exists($modelPath)) throw new Exception('Model file not found');
            $modelPathNorm = str_replace('\\', '/', $modelPath);

            $mlDirNorm = str_replace('\\', '/', dirname($this->incrementalScriptPath));

            // ── Branch: incremental SGD model ────────────────────────────────
            if (!empty($model['is_incremental']) && $model['is_incremental'] == 1) {

                $tempDataFile = tempnam(sys_get_temp_dir(), 'train_batch_') . '.json';
                file_put_contents($tempDataFile, json_encode(
                    ['texts' => $texts, 'categories' => $categories],
                    JSON_UNESCAPED_UNICODE
                ));
                $tempDataFileNorm = str_replace('\\', '/', $tempDataFile);

                $tempScript = tempnam(sys_get_temp_dir(), 'ml_incr_') . '.py';
                $pythonCode = <<<PYTHON
import sys
sys.path.append('{$mlDirNorm}')
from incremental_ml_classifier import IncrementalMLClassifier
import json

with open('{$tempDataFileNorm}', 'r', encoding='utf-8') as f:
    data = json.load(f)

classifier = IncrementalMLClassifier()
classifier.load_model('{$modelPathNorm}')
result = classifier.incremental_train(data['texts'], data['categories'])
if result['success']:
    classifier.save_model('{$modelPathNorm}')
print(json.dumps(result))
PYTHON;
                file_put_contents($tempScript, $pythonCode);

                $output = shell_exec(escapeshellarg($this->pythonPath) . ' ' . escapeshellarg($tempScript) . ' 2>&1');
                @unlink($tempScript);
                @unlink($tempDataFile);

                if (empty($output)) throw new Exception('Incremental training failed: No output');
                $result = json_decode($output, true);
                if (!$result || !$result['success']) {
                    throw new Exception($result['error'] ?? 'Incremental training failed: ' . substr($output, 0, 300));
                }

            // ── Branch: regular SVM / NaiveBayes / RandomForest ─────────────
            } else {

                // Get original training CSV
                $dataset = $this->db->selectOne(
                    "SELECT * FROM ml_training_datasets_tbl WHERE dataset_id = ?",
                    [$model['dataset_id']]
                );
                if (!$dataset) throw new Exception('Original dataset not found for retraining');

                $originalCsv = __DIR__ . '/../../' . $dataset['file_path'];
                if (!file_exists($originalCsv)) throw new Exception('Original CSV file not found');

                // Build combined CSV: original rows + new queued rows
                $combinedCsv = tempnam(sys_get_temp_dir(), 'ml_combined_') . '.csv';
                $fpOut = fopen($combinedCsv, 'w');
                fputcsv($fpOut, ['text', 'category']); // header

                // Original data (skip header)
                $fpIn = fopen($originalCsv, 'r');
                $header = fgetcsv($fpIn); // skip original header
                // strip BOM from first field
                if ($header) $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
                while (($row = fgetcsv($fpIn)) !== false) {
                    if (count($row) >= 2 && !empty(trim($row[0]))) {
                        fputcsv($fpOut, [trim($row[0]), trim($row[1])]);
                    }
                }
                fclose($fpIn);

                // New queued samples
                foreach ($texts as $i => $text) {
                    if (!empty(trim($text))) {
                        fputcsv($fpOut, [trim($text), trim($categories[$i])]);
                    }
                }
                fclose($fpOut);

                $combinedCsvNorm = str_replace('\\', '/', $combinedCsv);

                // Get vectorizer path
                $vectorizerPath = __DIR__ . '/../../' . $model['vectorizer_path'];
                $vectorizerPathNorm = str_replace('\\', '/', $vectorizerPath);

                $mlRegDirNorm = str_replace('\\', '/', dirname(
                    __DIR__ . '/../ml/ml_classifier.py'
                ));
                $modelType = $model['model_type']; // 'svm', 'naive_bayes', 'random_forest'

                $tempScript = tempnam(sys_get_temp_dir(), 'ml_retrain_') . '.py';
                $pythonCode = <<<PYTHON
import sys
sys.path.append('{$mlRegDirNorm}')
from ml_classifier import MLDocumentClassifier
import json, os

classifier = MLDocumentClassifier(model_type='{$modelType}')
result = classifier.train('{$combinedCsvNorm}')

if result['success']:
    classifier.save_model('{$modelPathNorm}', '{$vectorizerPathNorm}')

print(json.dumps(result))
PYTHON;
                file_put_contents($tempScript, $pythonCode);

                $output = shell_exec(escapeshellarg($this->pythonPath) . ' ' . escapeshellarg($tempScript) . ' 2>&1');
                @unlink($tempScript);
                @unlink($combinedCsv);

                if (empty($output)) throw new Exception('Model retrain failed: No output');
                $result = json_decode($output, true);
                if (!$result || !$result['success']) {
                    throw new Exception($result['error'] ?? 'Model retrain failed: ' . substr($output, 0, 300));
                }

                // Update accuracy stats
                $this->db->execute(
                    "UPDATE ml_models_tbl SET
                        accuracy_score  = ?,
                        precision_score = ?,
                        recall_score    = ?,
                        f1_score        = ?,
                        training_samples = ?
                     WHERE model_id = ?",
                    [
                        $result['accuracy'],
                        $result['precision'],
                        $result['recall'],
                        $result['f1_score'],
                        $result['training_samples'],
                        $modelId
                    ]
                );
            }

            // ── Mark queue items as processed ────────────────────────────────
            $queueIdsStr = implode(',', array_map('intval', $queueIds));
            $this->db->execute(
                "UPDATE ml_incremental_training_queue_tbl
                 SET status = 'processed', processed_at = NOW()
                 WHERE queue_id IN ($queueIdsStr)"
            );

            // Update incremental sample counter + timestamp
            $this->db->execute(
                "UPDATE ml_models_tbl
                 SET total_incremental_samples = COALESCE(total_incremental_samples, 0) + ?,
                     last_incremental_update   = NOW()
                 WHERE model_id = ?",
                [count($samples), $modelId]
            );

            return [
                'success'          => true,
                'samples_processed' => count($samples),
                'total_samples_trained' => $result['training_samples'] ?? ($result['total_samples_trained'] ?? null),
                'new_categories'   => $result['new_categories_added'] ?? []
            ];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Learn from file upload automatically.
     * Called immediately after a file is successfully uploaded.
     * Queues the extracted text + confirmed category as a training sample.
     * When 10 samples accumulate the model is automatically retrained.
     */
    public function learnFromUpload($fileUploadId, $extractedText, $confirmedCategory, $predictionConfidence = null) {
        try {
            error_reporting(E_ALL & ~E_DEPRECATED);
            if (empty($confirmedCategory) || empty(trim($extractedText))) {
                return ['success' => false, 'error' => 'Missing category or text'];
            }

            // Auto-enable incremental learning if it hasn't been explicitly set yet
            $enabled = $this->getSetting('incremental_learning_enabled');
            if ($enabled === null || $enabled === '') {
                $this->updateSetting('incremental_learning_enabled', '1');
            } elseif ($enabled != '1') {
                return ['success' => false, 'error' => 'Incremental learning is disabled'];
            }

            // Queue the sample (bypass the "enabled" guard in addTrainingSample
            // since we just confirmed it above)
            $query = "INSERT INTO ml_incremental_training_queue_tbl
                      (file_upload_id, training_text, confirmed_category, prediction_confidence, status)
                      VALUES (?, ?, ?, ?, 'pending')";
            $queueId = $this->db->insert($query, [
                $fileUploadId,
                $extractedText,
                $confirmedCategory,
                $predictionConfidence
            ]);

            // Check if we've hit the batch threshold → retrain now
            $pendingCount = $this->db->selectOne(
                "SELECT COUNT(*) as count FROM ml_incremental_training_queue_tbl WHERE status = 'pending'"
            );
            $pending = intval($pendingCount['count'] ?? 0);

            $batchSize = intval($this->getSetting("incremental_batch_size") ?: 10);
            if ($pending >= $batchSize) {
                $trainResult = $this->processTrainingQueue($batchSize);
                return array_merge($trainResult, [
                    'queue_id'       => $queueId,
                    'pending_before' => $pending,
                    'retrain_triggered' => true
                ]);
            }

            return [
                'success'         => true,
                'queue_id'        => $queueId,
                'message'         => 'Sample queued for learning',
                'pending_samples' => $pending,
                'retrain_triggered' => false
            ];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get incremental learning statistics
     */
    public function getIncrementalStats() {
        try {
            $modelId = $this->getSetting('active_ml_model_id');
            
            if (!$modelId) {
                return [
                    'success' => false,
                    'error' => 'No active model'
                ];
            }
            
            $model = $this->db->selectOne(
                "SELECT * FROM ml_models_tbl WHERE model_id = ? AND is_incremental = 1",
                [$modelId]
            );
            
            if (!$model) {
                return [
                    'success' => false,
                    'error' => 'Not an incremental model'
                ];
            }
            
            $pendingCount = $this->db->selectOne(
                "SELECT COUNT(*) as count FROM ml_incremental_training_queue_tbl WHERE status = 'pending'"
            );
            
            $processedCount = $this->db->selectOne(
                "SELECT COUNT(*) as count FROM ml_incremental_training_queue_tbl WHERE status = 'processed'"
            );
            
            $categoryCounts = $this->db->select(
                "SELECT confirmed_category, COUNT(*) as count 
                 FROM ml_incremental_training_queue_tbl 
                 WHERE status = 'processed'
                 GROUP BY confirmed_category
                 ORDER BY count DESC"
            );
            
            return [
                'success' => true,
                'model_name' => $model['model_name'],
                'initial_samples' => $model['training_samples'],
                'incremental_samples' => $model['total_incremental_samples'] ?? 0,
                'total_samples' => $model['training_samples'] + ($model['total_incremental_samples'] ?? 0),
                'pending_samples' => $pendingCount['count'] ?? 0,
                'processed_samples' => $processedCount['count'] ?? 0,
                'last_update' => $model['last_incremental_update'],
                'category_distribution' => $categoryCounts
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>
