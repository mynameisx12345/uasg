<?php
/**
 * ML Classification Service
 * PHP wrapper for Python ML classifier
 * Handles training, prediction, and model management
 */

class MLClassificationService {
    private $db;
    private $pythonPath;
    private $mlScriptPath;
    private $modelsDir;
    private $datasetsDir;
    
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Python executable path - try to find Python on Windows
        $this->pythonPath = $this->findPython();
        
        // ML script path
        $this->mlScriptPath = __DIR__ . '/../ml/ml_classifier.py';
        
        // Storage directories
        $this->modelsDir = __DIR__ . '/../../uploads/ml_models/';
        $this->datasetsDir = __DIR__ . '/../../uploads/ml_datasets/';
        
        // Create directories if they don't exist
        if (!file_exists($this->modelsDir)) {
            mkdir($this->modelsDir, 0755, true);
        }
        if (!file_exists($this->datasetsDir)) {
            mkdir($this->datasetsDir, 0755, true);
        }
    }
    
    /**
     * Find Python executable
     */
    private function findPython() {
        // Try common Python paths
        $possiblePaths = [
            'C:\\Users\\User\\AppData\\Local\\Programs\\Python\\Python314\\python.exe',
            'C:\\Users\\User\\AppData\\Local\\Programs\\Python\\Python313\\python.exe',
            'C:\\Users\\User\\AppData\\Local\\Programs\\Python\\Python312\\python.exe',
            'C:\\Users\\User\\AppData\\Local\\Programs\\Python\\Python311\\python.exe',
            'python',           // In PATH
            'python3',          // In PATH (Linux/Mac)
            'py',               // Python Launcher (Windows)
            'C:\\Python314\\python.exe',
            'C:\\Python313\\python.exe',
            'C:\\Python312\\python.exe',
            'C:\\Python311\\python.exe',
            'C:\\Python310\\python.exe',
            'C:\\Python39\\python.exe',
            'C:\\Python38\\python.exe',
            'C:\\Program Files\\Python314\\python.exe',
            'C:\\Program Files\\Python313\\python.exe',
            'C:\\Program Files\\Python312\\python.exe',
            'C:\\Program Files\\Python311\\python.exe',
            'C:\\Program Files\\Python310\\python.exe',
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Fallback: try to execute and check version
        foreach ($possiblePaths as $path) {
            $output = @shell_exec("\"$path\" --version 2>&1");
            if ($output && stripos($output, 'python') !== false) {
                return $path;
            }
        }
        
        // Default fallback
        return 'python';
    }
    
    /**
     * Upload and validate training dataset CSV
     */
    public function uploadDataset($file, $datasetName, $description, $uploadedBy) {
        try {
            // Validate file
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('File upload error');
            }
            
            // Check file type
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($fileExt !== 'csv') {
                throw new Exception('Only CSV files are allowed');
            }
            
            // Generate unique filename
            $filename = uniqid('dataset_') . '_' . time() . '.csv';
            $filePath = $this->datasetsDir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception('Failed to save file');
            }
            
            // Validate CSV structure using Python
            $validation = $this->validateDatasetCSV($filePath);
            
            if (!$validation['success']) {
                unlink($filePath); // Delete invalid file
                throw new Exception($validation['error']);
            }
            
            // Save to database
            $query = "INSERT INTO ml_training_datasets_tbl 
                      (dataset_name, file_path, total_samples, categories_count, categories, uploaded_by, description)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $categoriesJson = json_encode($validation['categories']);
            
            $result = $this->db->insert($query, [
                $datasetName,
                'uploads/ml_datasets/' . $filename,
                $validation['total_samples'],
                $validation['categories_count'],
                $categoriesJson,
                $uploadedBy,
                $description
            ]);
            
            return [
                'success' => true,
                'dataset_id' => $result,
                'total_samples' => $validation['total_samples'],
                'categories' => $validation['categories'],
                'categories_count' => $validation['categories_count']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate CSV file structure
     */
    private function validateDatasetCSV($csvPath) {
        try {
            // Read and validate CSV
            $handle = fopen($csvPath, 'r');
            if (!$handle) {
                throw new Exception('Cannot open CSV file');
            }
            
            // Check header
            $header = fgetcsv($handle);
            if (!$header || !in_array('text', $header) || !in_array('category', $header)) {
                fclose($handle);
                throw new Exception('CSV must have "text" and "category" columns');
            }
            
            // Count samples and categories
            $categories = [];
            $sampleCount = 0;
            
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) >= 2) {
                    $textIdx = array_search('text', $header);
                    $catIdx = array_search('category', $header);
                    
                    $text = $row[$textIdx] ?? '';
                    $category = $row[$catIdx] ?? '';
                    
                    if (!empty($text) && !empty($category)) {
                        $categories[$category] = true;
                        $sampleCount++;
                    }
                }
            }
            
            fclose($handle);
            
            if ($sampleCount < 5) {
                throw new Exception('Dataset must have at least 5 samples');
            }
            
            if (count($categories) < 2) {
                throw new Exception('Dataset must have at least 2 categories');
            }
            
            return [
                'success' => true,
                'total_samples' => $sampleCount,
                'categories' => array_keys($categories),
                'categories_count' => count($categories)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Train ML model from dataset
     */
    public function trainModel($datasetId, $modelName, $modelType, $trainedBy) {
        try {
            // Get dataset info
            $dataset = $this->db->selectOne(
                "SELECT * FROM ml_training_datasets_tbl WHERE dataset_id = ?",
                [$datasetId]
            );
            
            if (!$dataset) {
                throw new Exception('Dataset not found');
            }
            
            // Full path to CSV
            $csvPath = __DIR__ . '/../../' . $dataset['file_path'];
            
            if (!file_exists($csvPath)) {
                throw new Exception('Dataset file not found');
            }
            
            // Generate unique model filename
            $modelFilename = uniqid('model_') . '_' . time();
            $modelPath = $this->modelsDir . $modelFilename . '.pkl';
            $vectorizerPath = $this->modelsDir . $modelFilename . '_vectorizer.pkl';
            
            // Create temporary Python script
            $tempScript = tempnam(sys_get_temp_dir(), 'ml_train_') . '.py';
            
            // Normalize paths for Python (use forward slashes)
            $csvPathNorm = str_replace('\\', '/', realpath($csvPath));
            $modelPathNorm = str_replace('\\', '/', $modelPath);
            $vectorizerPathNorm = str_replace('\\', '/', $vectorizerPath);
            $mlDirNorm = str_replace('\\', '/', dirname($this->mlScriptPath));
            
            // Write Python script
            $pythonCode = <<<PYTHON
import sys
sys.path.append('{$mlDirNorm}')
from ml_classifier import MLDocumentClassifier
import json

try:
    classifier = MLDocumentClassifier(model_type='{$modelType}')
    result = classifier.train('{$csvPathNorm}')
    
    if result['success']:
        save_result = classifier.save_model('{$modelPathNorm}', '{$vectorizerPathNorm}')
        result.update(save_result)
    
    print(json.dumps(result))
except Exception as e:
    print(json.dumps({'success': False, 'error': str(e)}))
PYTHON;
            
            file_put_contents($tempScript, $pythonCode);
            
            // Build Python command - use full path and escape properly
            $command = sprintf('"%s" "%s" 2>&1', $this->pythonPath, $tempScript);
            
            // Execute Python script
            $output = shell_exec($command);
            
            // Clean up temp script
            @unlink($tempScript);
            
            // Check if output is valid
            if (empty($output)) {
                throw new Exception('Training failed: No output from Python script. ' .
                    'Python Path: ' . $this->pythonPath . '. ' .
                    'Command: ' . $command . '. ' .
                    'Please ensure Python 3.8+ is installed and the following packages are available: pandas, scikit-learn, numpy. ' .
                    'Try running: pip install pandas scikit-learn numpy');
            }
            
            // Parse result
            $result = json_decode($output, true);
            
            if (!$result || !isset($result['success'])) {
                throw new Exception('Training failed. Python output: ' . substr($output, 0, 500));
            }
            
            if (!$result['success']) {
                throw new Exception($result['error'] ?? 'Training failed');
            }
            
            // Deactivate other models first
            $this->db->execute("UPDATE ml_models_tbl SET is_active = 0");
            
            // Save model to database
            $query = "INSERT INTO ml_models_tbl 
                      (model_name, dataset_id, model_type, model_path, vectorizer_path,
                       accuracy_score, precision_score, recall_score, f1_score,
                       categories, training_samples, test_samples, is_active, trained_by)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)";
            
            $categoriesJson = json_encode($result['categories']);
            
            $modelId = $this->db->insert($query, [
                $modelName,
                $datasetId,
                $modelType,
                'uploads/ml_models/' . $modelFilename . '.pkl',
                'uploads/ml_models/' . $modelFilename . '_vectorizer.pkl',
                $result['accuracy'],
                $result['precision'],
                $result['recall'],
                $result['f1_score'],
                $categoriesJson,
                $result['training_samples'],
                $result['test_samples'],
                $trainedBy
            ]);
            
            // Update settings to use this model
            $this->db->execute(
                "UPDATE ml_settings_tbl SET setting_value = ? WHERE setting_key = 'active_ml_model_id'",
                [$modelId]
            );
            
            return [
                'success' => true,
                'model_id' => $modelId,
                'accuracy' => $result['accuracy'],
                'precision' => $result['precision'],
                'recall' => $result['recall'],
                'f1_score' => $result['f1_score'],
                'categories' => $result['categories']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Predict category using active ML model
     */
    public function predict($text) {
        try {
            // Get active model
            $modelId = $this->getSetting('active_ml_model_id');
            
            if (!$modelId) {
                throw new Exception('No active ML model');
            }
            
            $model = $this->db->selectOne(
                "SELECT * FROM ml_models_tbl WHERE model_id = ? AND is_active = 1",
                [$modelId]
            );
            
            if (!$model) {
                throw new Exception('Active model not found');
            }
            
            // Get full paths
            $modelPath = __DIR__ . '/../../' . $model['model_path'];
            $vectorizerPath = __DIR__ . '/../../' . $model['vectorizer_path'];
            
            if (!file_exists($modelPath) || !file_exists($vectorizerPath)) {
                throw new Exception('Model files not found');
            }
            
            // Normalize paths for Windows
            $modelPath = str_replace('\\', '/', $modelPath);
            $vectorizerPath = str_replace('\\', '/', $vectorizerPath);
            $mlScriptDir = str_replace('\\', '/', dirname($this->mlScriptPath));
            
            // Escape text for Python string (escape single quotes and backslashes)
            $textEscaped = str_replace(['\\', "'"], ['\\\\', "\\'"], $text);
            
            // Create temporary Python script (better for Windows)
            $tempScript = tempnam(sys_get_temp_dir(), 'ml_predict_') . '.py';
            $pythonCode = <<<PYTHON
import sys
sys.path.append('{$mlScriptDir}')
from ml_classifier import MLDocumentClassifier
import json

classifier = MLDocumentClassifier()
load_result = classifier.load_model('{$modelPath}', '{$vectorizerPath}')

if load_result['success']:
    text = '{$textEscaped}'
    result = classifier.predict(text, return_probabilities=True)
    print(json.dumps(result))
else:
    print(json.dumps(load_result))
PYTHON;
            
            file_put_contents($tempScript, $pythonCode);
            
            try {
                // Execute Python script
                $command = escapeshellarg($this->pythonPath) . ' ' . escapeshellarg($tempScript) . ' 2>&1';
                $output = shell_exec($command);
                
                // Parse result
                $result = json_decode($output, true);
                
                if (!$result || !isset($result['success'])) {
                    throw new Exception('Prediction failed: ' . ($output ?: 'Unknown error'));
                }
                
                if (!$result['success']) {
                    throw new Exception($result['error'] ?? 'Prediction failed');
                }
                
                // Update model usage stats
                $this->db->execute(
                    "UPDATE ml_models_tbl SET last_used_at = NOW(), usage_count = usage_count + 1 WHERE model_id = ?",
                    [$modelId]
                );
                
                return [
                    'success' => true,
                    'category' => $result['category'],
                    'confidence' => $result['confidence'],
                    'prediction_time_ms' => $result['prediction_time_ms'],
                    'model_id' => $modelId,
                    'model_name' => $model['model_name'],
                    'all_scores' => $result['all_scores'] ?? []
                ];
                
            } finally {
                // Clean up temp file
                @unlink($tempScript);
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get ML setting value
     */
    public function getSetting($key) {
        $result = $this->db->selectOne(
            "SELECT setting_value FROM ml_settings_tbl WHERE setting_key = ?",
            [$key]
        );
        return $result ? $result['setting_value'] : null;
    }
    
    /**
     * Update ML setting
     */
    public function updateSetting($key, $value, $userId = null) {
        return $this->db->execute(
            "UPDATE ml_settings_tbl SET setting_value = ?, updated_by = ? WHERE setting_key = ?",
            [$value, $userId, $key]
        );
    }
    
    /**
     * Get all models
     */
    public function getAllModels() {
        return $this->db->select(
            "SELECT m.*, d.dataset_name, u.user_name as trainer_name 
             FROM ml_models_tbl m
             LEFT JOIN ml_training_datasets_tbl d ON m.dataset_id = d.dataset_id
             LEFT JOIN user_tbl u ON m.trained_by = u.user_id
             ORDER BY m.trained_at DESC"
        );
    }
    
    /**
     * Get all datasets
     */
    public function getAllDatasets() {
        return $this->db->select(
            "SELECT d.*, u.user_name as uploader_name 
             FROM ml_training_datasets_tbl d
             LEFT JOIN user_tbl u ON d.uploaded_by = u.user_id
             ORDER BY d.uploaded_at DESC"
        );
    }
    
    /**
     * Activate a specific model
     */
    public function activateModel($modelId) {
        try {
            // Deactivate all models
            $this->db->execute("UPDATE ml_models_tbl SET is_active = 0");
            
            // Activate selected model
            $this->db->execute("UPDATE ml_models_tbl SET is_active = 1 WHERE model_id = ?", [$modelId]);
            
            // Update setting
            $this->updateSetting('active_ml_model_id', $modelId);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Delete model
     */
    public function deleteModel($modelId) {
        try {
            $model = $this->db->selectOne("SELECT * FROM ml_models_tbl WHERE model_id = ?", [$modelId]);
            
            if ($model) {
                // Delete files
                $modelPath = __DIR__ . '/../../' . $model['model_path'];
                $vectorizerPath = __DIR__ . '/../../' . $model['vectorizer_path'];
                
                if (file_exists($modelPath)) unlink($modelPath);
                if (file_exists($vectorizerPath)) unlink($vectorizerPath);
                
                // Delete from database
                $this->db->execute("DELETE FROM ml_models_tbl WHERE model_id = ?", [$modelId]);
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
