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
     * Process queued training samples
     * Trains model incrementally on accumulated samples
     */
    public function processTrainingQueue($batchSize = 10) {
        try {
            $enabled = $this->getSetting('incremental_learning_enabled');
            if ($enabled != '1') {
                return [
                    'success' => false,
                    'error' => 'Incremental learning not enabled'
                ];
            }
            
            // Get active incremental model
            $modelId = $this->getSetting('active_ml_model_id');
            if (!$modelId) {
                throw new Exception('No active ML model');
            }
            
            $model = $this->db->selectOne(
                "SELECT * FROM ml_models_tbl WHERE model_id = ? AND is_active = 1 AND is_incremental = 1",
                [$modelId]
            );
            
            if (!$model) {
                throw new Exception('No active incremental model found');
            }
            
            // Get pending samples
            $samples = $this->db->select(
                "SELECT * FROM ml_incremental_training_queue_tbl 
                 WHERE status = 'pending' 
                 ORDER BY created_at ASC 
                 LIMIT ?",
                [$batchSize]
            );
            
            if (empty($samples)) {
                return [
                    'success' => true,
                    'message' => 'No pending samples to process',
                    'samples_processed' => 0
                ];
            }
            
            // Extract texts and categories
            $texts = [];
            $categories = [];
            $queueIds = [];
            
            foreach ($samples as $sample) {
                $texts[] = $sample['training_text'];
                $categories[] = $sample['confirmed_category'];
                $queueIds[] = $sample['queue_id'];
            }
            
            // Get model path
            $modelPath = __DIR__ . '/../../' . $model['model_path'];
            if (!file_exists($modelPath)) {
                throw new Exception('Model file not found');
            }
            
            // Normalize paths
            $modelPathNorm = str_replace('\\', '/', $modelPath);
            
            // Create temporary JSON file with training data
            $tempDataFile = tempnam($this->trainingQueueDir, 'train_batch_') . '.json';
            file_put_contents($tempDataFile, json_encode([
                'texts' => $texts,
                'categories' => $categories
            ]));
            
            $tempDataFileNorm = str_replace('\\', '/', $tempDataFile);
            
            // Execute incremental training
            $command = sprintf(
                '"%s" -c "import sys; sys.path.append(\'%s\'); from incremental_ml_classifier import IncrementalMLClassifier; import json; ' .
                'classifier = IncrementalMLClassifier(); ' .
                'classifier.load_model(\'%s\'); ' .
                'data = json.load(open(\'%s\')); ' .
                'result = classifier.incremental_train(data[\'texts\'], data[\'categories\']); ' .
                'print(json.dumps(result)); ' .
                'classifier.save_model(\'%s\') if result[\'success\'] else None" 2>&1',
                $this->pythonPath,
                str_replace('\\', '/', dirname($this->incrementalScriptPath)),
                $modelPathNorm,
                $tempDataFileNorm,
                $modelPathNorm
            );
            
            $output = shell_exec($command);
            @unlink($tempDataFile);
            
            if (empty($output)) {
                throw new Exception('Incremental training failed: No output');
            }
            
            $result = json_decode($output, true);
            
            if (!$result || !$result['success']) {
                throw new Exception($result['error'] ?? 'Incremental training failed: ' . substr($output, 0, 300));
            }
            
            // Update queue status
            $queueIdsStr = implode(',', array_map('intval', $queueIds));
            $this->db->execute(
                "UPDATE ml_incremental_training_queue_tbl 
                 SET status = 'processed', processed_at = NOW() 
                 WHERE queue_id IN ($queueIdsStr)"
            );
            
            // Update model stats
            $this->db->execute(
                "UPDATE ml_models_tbl 
                 SET total_incremental_samples = total_incremental_samples + ?,
                     last_incremental_update = NOW()
                 WHERE model_id = ?",
                [count($samples), $modelId]
            );
            
            return [
                'success' => true,
                'samples_processed' => count($samples),
                'total_samples_trained' => $result['total_samples_trained'] ?? null,
                'new_categories' => $result['new_categories_added'] ?? []
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Learn from file upload automatically
     * Called after user confirms/corrects the category
     */
    public function learnFromUpload($fileUploadId, $extractedText, $confirmedCategory, $predictionConfidence = null) {
        try {
            // Only learn if category was confirmed/corrected by user
            if (empty($confirmedCategory) || $confirmedCategory === 'Others') {
                return [
                    'success' => false,
                    'error' => 'Category must be confirmed before learning'
                ];
            }
            
            // Add to training queue
            $result = $this->addTrainingSample(
                $extractedText,
                $confirmedCategory,
                $fileUploadId,
                $predictionConfidence
            );
            
            if (!$result['success']) {
                return $result;
            }
            
            // Check if we should process the queue (every 10 samples)
            $pendingCount = $this->db->selectOne(
                "SELECT COUNT(*) as count FROM ml_incremental_training_queue_tbl WHERE status = 'pending'"
            );
            
            if ($pendingCount && $pendingCount['count'] >= 10) {
                // Process queue automatically
                return $this->processTrainingQueue(10);
            }
            
            return [
                'success' => true,
                'message' => 'Sample queued for learning',
                'pending_samples' => $pendingCount['count'] ?? 0
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
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
