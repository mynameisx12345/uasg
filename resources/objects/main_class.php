<?php
	require_once("db_config.php");
	require_once("permission_class.php");
	require_once("google_nlp_service.php");
	
	class Main{
		protected $table;
    	protected $fields = [];

    	public function __construct($table, $data = []) {
        	$this->table = $table;

        	foreach ($data as $key => $value) {
            	// Only add non-null values to fields
            	if ($value !== null) {
                	$this->fields[$key] = $value;
            	}
        	}
    	}

    	// Automatically builds and runs INSERT query
   	 	public function insert() {
        	if (empty($this->fields)) {
            	throw new Exception("No data to insert.");
        	}

        	$db = Database::getInstance()->getConnection();
        	$columns = implode(", ", array_keys($this->fields));
        	$placeholders = ":" . implode(", :", array_keys($this->fields));

        	$query = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        	$stmt = $db->prepare($query);

        	return $stmt->execute($this->fields);
    	}

    	public function checkFromTable($column = null) {
    		if (empty($this->fields)) {
        		throw new Exception("No data to check.");
    		}

    		$db = Database::getInstance()->getConnection();

    		// If a specific column is provided, check only that one
    		if ($column !== null) {
        		if (!array_key_exists($column, $this->fields)) {
            		throw new Exception("Column '$column' not found in data fields.");
        		}		

        		$value = $this->fields[$column];

        		// Whitelist validation for column
        		if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
           			throw new Exception("Invalid column name.");
        		}

        		$query = "SELECT COUNT(*) as cnt FROM {$this->table} WHERE $column = :val LIMIT 1";
        		$stmt = $db->prepare($query);
        		$stmt->execute([':val' => $value]);
        		$row = $stmt->fetch(PDO::FETCH_ASSOC);

        		return $row && $row['cnt'] > 0;
    		}

    		// If no column specified, check all fields as conditions (AND)
    		$conditions = [];
    		$params = [];
    		foreach ($this->fields as $key => $val) {
        		if (!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
            		throw new Exception("Invalid column name.");
        		}
        		$conditions[] = "$key = :$key";
        		$params[":$key"] = $val;
    		}

    		$where = implode(" AND ", $conditions);
    		$query = "SELECT COUNT(*) as cnt FROM {$this->table} WHERE $where LIMIT 1";
    		$stmt = $db->prepare($query);
    		$stmt->execute($params);
   		 	$row = $stmt->fetch(PDO::FETCH_ASSOC);

    		return $row && $row['cnt'] > 0;
		}

    	// Optional: Get last inserted ID
    	public function insertAndGetId() {
        	if ($this->insert()) {
            	return Database::getInstance()->getConnection()->lastInsertId();
        	}
        	return false;
    	}

    	public function updateSingleValue($field,$primary,$id,$value){
    		$db = Database::getInstance()->getConnection();
    		$query = "UPDATE {$this->table} SET $field = :val WHERE $primary = :id";
    		$stmt = $db->prepare($query);
    		return $stmt->execute([':val'=>$value,':id'=>$id]);
    	}

    	public function update($idField, $idValue) {
        	if (empty($this->fields)) {
            	throw new Exception("No data to update.");
        	}

        	$db = Database::getInstance()->getConnection();
        	$assignments = [];
        	foreach ($this->fields as $key => $value) {
            	$assignments[] = "$key = :$key";
        	}

        	$setClause = implode(", ", $assignments);
        	$query = "UPDATE {$this->table} SET $setClause WHERE $idField = :__id";
        	$stmt = $db->prepare($query);

        	// Add ID param to binding
        	$this->fields['__id'] = $idValue;

        	return $stmt->execute($this->fields);
    	}

    	// Optional: Add a generic select method
    	public static function selectAll($table) {
       	 	$db = Database::getInstance()->getConnection();
        	return $db->select("SELECT * FROM $table");
   	 	}

   	 	public function getAllWithidden($hidden = []) {
    		$db = Database::getInstance()->getConnection();
    		$query = "SELECT * FROM {$this->table}";
    		$rows = $db->select($query);

    		$arr = [];
    		$arr["data"] = $rows;
    		$arr["hidden"] = $hidden;

    		// Build columns dynamically from first row
    		$arr["columns"] = [];
    		if (!empty($rows)) {
        		foreach (array_keys($rows[0]) as $col) {
            		$arr["columns"][] = [
                		"data" => $col,
                		"title" => ucwords(str_replace("_", " ", $col)),
                		"visible" => !in_array($col, $hidden)
            		];
        		}
    		}
    		return $arr;
		}

   	 	public function getAllRecords(){
   	 		$db = Database::getInstance()->getConnection();
   	 		return $db->select("SELECT * FROM $this->table");
   	 	}

   	 	public function delete($idField, $idValue) {
        	$db = Database::getInstance()->getConnection();
        	$query = "DELETE FROM {$this->table} WHERE $idField = :id";
        	$stmt = $db->prepare($query);
        	return $stmt->execute([':id' => $idValue]);
    	}

    	public function exists($column, $value) {
   			 $db = Database::getInstance()->getConnection();

    		// Protect against SQL injection by whitelisting column names
    		if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
        		throw new Exception("Invalid column name.");
    		}

    		$query = "SELECT COUNT(*) as cnt FROM {$this->table} WHERE $column = :val LIMIT 1";
    		$stmt = $db->prepare($query);
    		$stmt->execute([':val' => $value]);
    		$row = $stmt->fetch(PDO::FETCH_ASSOC);

    		return $row && $row['cnt'] > 0;
		}

		public function findOrCreate($idField = "id", $uniqueFields = null) {
    		if (empty($this->fields)) {
       	 		throw new Exception("No data provided.");
    		}

    		$db = Database::getInstance()->getConnection();

    		// If no specific unique fields are provided, use all fields
    		$fieldsToCheck = $uniqueFields ?? array_keys($this->fields);

    		$conditions = [];
    		$params = [];
    		foreach ($fieldsToCheck as $field) {
        		if (!array_key_exists($field, $this->fields)) {
            		throw new Exception("Field '$field' not found in data.");
        		}
        		if (!preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
            		throw new Exception("Invalid column name.");
        		}
        		$conditions[] = "$field = :$field";
        		$params[":$field"] = $this->fields[$field];
    		}

    		$where = implode(" AND ", $conditions);

    		// Try to find existing record
    		$query = "SELECT $idField FROM {$this->table} WHERE $where LIMIT 1";
    		$stmt = $db->prepare($query);
    		$stmt->execute($params);
    		$row = $stmt->fetch(PDO::FETCH_ASSOC);

    		if ($row && isset($row[$idField])) {
        		// Found → return its ID
        		return $row[$idField];
    		}

    		// Not found → insert
    		if ($this->insert()) {
        		return $db->lastInsertId();
    		}

    		return false;
		}

		public function getChildren($childTable, $foreignKey, $parentId, $hidden = []) {
    		$db = Database::getInstance()->getConnection();
    
    		// validate column name
    		if (!preg_match('/^[a-zA-Z0-9_]+$/', $foreignKey)) {
        		throw new Exception("Invalid column name.");
    		}

    		$query = "SELECT * FROM {$childTable} WHERE {$foreignKey} = :parentId";
    		$rows = $db->select($query, [':parentId' => $parentId]);

    		$arr = [];
    		$arr["data"] = $rows;
    		$arr["hidden"] = $hidden;

    		// Build dynamic columns
    		$arr["columns"] = [];
    		if (!empty($rows)) {
        		foreach (array_keys($rows[0]) as $col) {
            		$arr["columns"][] = [
                		"data" => $col,
                		"title" => ucwords(str_replace("_", " ", $col)),
                		"visible" => !in_array($col, $hidden)
            		];
        		}
    		}
    		return $arr;
		}

		public function uploadFile($data) {
    		require_once __DIR__ . '/nlpcloud_service.php';
			$db = Database::getInstance()->getConnection();
			$systemname = 'uasg';
			$file = $data['file'];
			$allowedTypes = $data['allowed_types'] ?? ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
			$baseUploadDir = '../uploads/files/';
			$uploadedBy = $data['uploaded_by'] ?? ($_SESSION['user_id'] ?? null);
			
			try {
				// Validate file upload
				if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
					throw new Exception('File upload failed or no file selected');
				}

				// Check file size (50MB limit)
				$maxSize = 50 * 1024 * 1024; // 50MB in bytes
				if ($file['size'] > $maxSize) {
					throw new Exception('File size exceeds 50MB limit');
				}

				// Get file extension and original filename
				$originalFilename = $file['name'];
				$fileInfo = pathinfo($originalFilename);
				$extension = strtolower($fileInfo['extension'] ?? '');

				// Validate file type
				if (!in_array($extension, $allowedTypes)) {
					throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $allowedTypes));
				}

				// Generate unique system filename (will be stored in database)
				$systemFilename = uniqid() . '_' . time() . '.' . $extension;
				
				// Temporary upload to analyze (we'll move it to category folder after NLP)
				$tempPath = $baseUploadDir . $systemFilename;
				
				// Create base upload directory if it doesn't exist
				if (!is_dir($baseUploadDir)) {
					if (!mkdir($baseUploadDir, 0755, true)) {
						throw new Exception('Failed to create upload directory');
					}
				}

				// Move uploaded file to temporary location first
				if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
					throw new Exception('Failed to move uploaded file');
				}

				// ⭐ CHECK IF NLP DATA IS ALREADY PROVIDED (from preview) - AVOID DUPLICATE API CALL!
				if (isset($data['nlp_analysis']) && !empty($data['nlp_analysis'])) {
					// Reuse cached NLP results from preview
					$nlpAnalysisData = is_string($data['nlp_analysis']) ? json_decode($data['nlp_analysis'], true) : $data['nlp_analysis'];
					$categoryTag = $data['category_tag'] ?? 'Uncategorized';
					$categoryScore = $data['category_score'] ?? 0;
					$extractedText = $nlpAnalysisData['extracted_text'] ?? '';
					$wordCount = $nlpAnalysisData['word_count'] ?? 0;
					$keywords = $nlpAnalysisData['keywords'] ?? [];
					$entities = $nlpAnalysisData['entities'] ?? [];
					$fullAnalysis = json_encode($nlpAnalysisData);
					$processingTime = 0; // Already processed
					$classificationMethod = 'cached';
					$mlModelId = null;
					
					$nlpResult = [
						'success' => true,
						'category_tag' => $categoryTag,
						'category_score' => $categoryScore,
						'extracted_text' => $extractedText,
						'word_count' => $wordCount,
						'keywords' => $keywords,
						'entities' => $entities,
						'full_analysis' => $nlpAnalysisData,
						'processing_time_ms' => 0,
						'from_cache' => true // Indicate this was cached
					];
				} else {
					// 🤖 ML CLASSIFICATION INTEGRATION
					// Check ML settings to determine classification method
					require_once __DIR__ . '/ml_service.php';
					$mlService = new MLClassificationService();
					$classificationMethodSetting = $mlService->getSetting('classification_method') ?? 'hybrid';
					$mlConfidenceThreshold = floatval($mlService->getSetting('ml_confidence_threshold') ?? 60);
					$nlpFallbackEnabled = ($mlService->getSetting('nlp_fallback_enabled') ?? '1') === '1';
					
					$useML = in_array($classificationMethodSetting, ['custom_ml', 'hybrid']);
					$mlModelId = null;
					$classificationMethod = $classificationMethodSetting;
					$mlPrediction = null;
					
					// Try ML classification first if enabled
					if ($useML) {
						require_once __DIR__ . '/text_extractor.php';
						$extractor = new TextExtractor();
						$extractedText = $extractor->extractText($tempPath);
						
						if (!empty($extractedText)) {
							try {
								$mlPrediction = $mlService->predict($extractedText);
								
								if ($mlPrediction['success']) {
									$mlModelId = $mlPrediction['model_id'];
									$mlConfidence = $mlPrediction['confidence'] * 100; // Convert to percentage
									
									// Check if ML confidence meets threshold
									if ($mlConfidence >= $mlConfidenceThreshold) {
										// Use ML prediction
										$categoryTag = $mlPrediction['category'];
										$categoryScore = $mlPrediction['confidence'];
										$keywords = []; // ML doesn't extract keywords
										$entities = []; // ML doesn't extract entities
										$wordCount = str_word_count($extractedText);
										$fullAnalysis = [
											'method' => 'custom_ml',
											'model_id' => $mlModelId,
											'model_name' => $mlPrediction['model_name'],
											'predicted_category' => $categoryTag,
											'confidence' => $categoryScore,
											'all_predictions' => $mlPrediction['all_predictions'],
											'extracted_text' => substr($extractedText, 0, 500) // Store first 500 chars
										];
										$processingTime = $mlPrediction['prediction_time_ms'];
										
										$nlpResult = [
											'success' => true,
											'category_tag' => $categoryTag,
											'category_score' => $categoryScore,
											'extracted_text' => $extractedText,
											'word_count' => $wordCount,
											'keywords' => $keywords,
											'entities' => $entities,
											'full_analysis' => $fullAnalysis,
											'processing_time_ms' => $processingTime,
											'provider' => 'Custom ML',
											'from_ml' => true
										];
									} else {
										// ML confidence too low, fallback to NLP if enabled
										$useML = false; // Trigger NLP fallback below
										error_log("ML confidence ($mlConfidence%) below threshold ($mlConfidenceThreshold%). Falling back to NLP.");
									}
								} else {
									// ML prediction failed
									$useML = false;
									error_log('ML prediction failed: ' . ($mlPrediction['error'] ?? 'Unknown error'));
								}
							} catch (Exception $e) {
								$useML = false;
								error_log('ML prediction exception: ' . $e->getMessage());
							}
						} else {
							$useML = false;
							error_log('Text extraction failed for ML prediction');
						}
					}
					
					// If ML not used or failed, use NLP Cloud
					if (!$useML || !isset($nlpResult)) {
						$nlp = new NLPCloudService();
						$nlpResult = $nlp->analyzeFile($tempPath, $file['type']);

						if (!$nlpResult['success']) {
							// NLP failed, but we still upload the file
							error_log('NLP analysis failed: ' . ($nlpResult['error'] ?? 'Unknown error'));
						}

						// Extract NLP data
						$categoryTag = $nlpResult['category_tag'] ?? 'Uncategorized';
						$categoryScore = $nlpResult['category_score'] ?? 0;
						$extractedText = $nlpResult['extracted_text'] ?? '';
						$wordCount = $nlpResult['word_count'] ?? 0;
						$keywords = $nlpResult['keywords'] ?? [];
						$entities = $nlpResult['entities'] ?? [];
						$fullAnalysis = $nlpResult['full_analysis'] ?? [];
						$processingTime = $nlpResult['processing_time_ms'] ?? 0;
						
						// If we tried ML but fell back, note that in the method
						if ($mlPrediction && !empty($mlPrediction['category'])) {
							$classificationMethod = 'hybrid_nlp_fallback';
						}
					}
				}
				
				// Common processing for both cached and fresh NLP results
				$sentiment = $nlpResult['sentiment'] ?? null;
				$provider = $nlpResult['provider'] ?? 'nlpcloud';

				// ⭐ NEW: Get or create category in category_tbl
				$categorySlug = $this->getCategorySlug($categoryTag);
				$categoryId = $this->getOrCreateCategory($categoryTag, $categorySlug);
				
				// ⭐ NEW: Create category-based directory structure
				$categoryDir = $baseUploadDir . $categorySlug . '/';
				if (!is_dir($categoryDir)) {
					if (!mkdir($categoryDir, 0755, true)) {
						throw new Exception('Failed to create category directory: ' . $categorySlug);
					}
				}
				
				// ⭐ NEW: Move file from temp location to category folder
				$finalPath = $categoryDir . $systemFilename;
				if (!rename($tempPath, $finalPath)) {
					// If rename fails, try copy and delete
					if (!copy($tempPath, $finalPath)) {
						throw new Exception('Failed to move file to category directory');
					}
					unlink($tempPath);
				}
				
			// Database path (relative from project root)
			$dbPath = 'uploads/files/' . $categorySlug . '/' . $systemFilename;

			// Insert into file_upload_tbl with ALL required fields including ML classification data

			$stmt = $db->prepare(
				"INSERT INTO file_upload_tbl 
				(category_id, category_tag, category_score, mime_type, original_filename, file_name, file_path, file_size, datetime_uploaded, uploaded_by, classification_method, ml_model_id) 
				VALUES (:category_id, :category_tag, :category_score, :mime_type, :original_filename, :file_name, :file_path, :file_size, :datetime_uploaded, :uploaded_by, :classification_method, :ml_model_id)"
			);
			$stmt->execute([
				':category_id' => $categoryId,
				':category_tag' => $categoryTag,
				':category_score' => $categoryScore,
				':mime_type' => $file['type'],
				':original_filename' => $originalFilename,
				':file_name' => $systemFilename,
				':file_path' => $dbPath,
				':file_size' => $file['size'],
				':datetime_uploaded' => date('Y-m-d H:i:s'),
				':uploaded_by' => $uploadedBy,
				':classification_method' => $classificationMethod ?? 'nlpcloud',
				':ml_model_id' => $mlModelId
			]);				// Get last inserted file_upload_id
				$fileId = $db->lastInsertId();
				
				// Insert NLP analysis data into file_nlp_analysis_tbl
				if ($extractedText) {
					$stmt = $db->prepare(
						"INSERT INTO file_nlp_analysis_tbl 
						(file_upload_id, extracted_text, word_count, suggested_category, category_confidence, 
						 keywords, entities, sentiment, full_analysis, provider, processing_time_ms) 
						VALUES (:file_upload_id, :extracted_text, :word_count, :suggested_category, :category_confidence, 
						        :keywords, :entities, :sentiment, :full_analysis, :provider, :processing_time_ms)"
					);
					$stmt->execute([
						':file_upload_id' => $fileId,
						':extracted_text' => $extractedText,
						':word_count' => $wordCount,
						':suggested_category' => $categoryTag,
						':category_confidence' => $categoryScore,
						':keywords' => json_encode($keywords),
						':entities' => json_encode($entities),
						':sentiment' => json_encode($sentiment),
						':full_analysis' => json_encode($fullAnalysis),
						':provider' => $provider,
						':processing_time_ms' => $processingTime
					]);
				}

			return [
				'success' => true,
				'file_id' => $fileId,
				'filename' => $systemFilename,
				'original_name' => $originalFilename,
				'path' => $finalPath,
				'db_path' => $dbPath,
				'category_slug' => $categorySlug,
				'size' => $file['size'],
				'type' => $file['type'],
				'extension' => $extension,
				'upload_time' => date('Y-m-d H:i:s'),
				'category_tag' => $categoryTag,
				'category_score' => $categoryScore,
				'category_id' => $categoryId,
				'nlp_result' => $nlpResult
			];		} catch (Exception $e) {
			return [
				'success' => false,
				'error' => $e->getMessage()
			];
		}
}

/**
 * Get all file permissions - DEPRECATED
 * Note: file_permission_tbl is obsolete - now using NLP category_tag
 */
public function getFilePermissions() {
	return []; // Obsolete - file_category_tbl removed
}

/**
 * Convert category name to URL-safe slug for directory names
 * Examples: "Meeting Minutes" -> "meeting-minutes", "Resolution" -> "resolution"
 */
private function getCategorySlug($categoryName) {
	$slug = strtolower(trim($categoryName));
	$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
	$slug = trim($slug, '-');
	return $slug ?: 'uncategorized';
}

/**
 * Get category ID from category_tbl, create if doesn't exist
 * This ensures every NLP-detected category has a database entry
 */
private function getOrCreateCategory($categoryName, $categorySlug) {
	$db = Database::getInstance()->getConnection();
	
	// Try to find existing category by name
	$stmt = $db->prepare("SELECT category_id FROM category_tbl WHERE category_name = :name LIMIT 1");
	$stmt->execute([':name' => $categoryName]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	
	if ($result) {
		return $result['category_id'];
	}
	
	// Category doesn't exist, create it
	$stmt = $db->prepare(
		"INSERT INTO category_tbl (category_name, category_slug, description) 
		VALUES (:name, :slug, :description)"
	);
	$stmt->execute([
		':name' => $categoryName,
		':slug' => $categorySlug,
		':description' => 'Auto-created by NLP classification'
	]);
	
	return $db->lastInsertId();
}

}
	// FilePermission class deprecated - file_category_id no longer exists
	class FilePermission extends Main{
		public function __construct($params = []){
			parent::__construct('file_permission_tbl',[
				'file_permission_id' => $params["id"] ?? null,
				'position_id' => $params["position_id"] ?? null,
			]);
		}
	}	class Position extends Main{
		public function __construct($params = []){
			parent::__construct('position_tbl',[
				'position_id' => $params["id"] ?? null,
				'position' => $params["position"] ?? null
			]);
		}
	}

	class Profile extends Main{
		public function __construct($params = []){
			parent::__construct('profile_tbl',[
				'profile_id' => $params["id"] ?? null,
				'fname' => $params["fname"] ?? null,
				'mname' => $params["mname"] ?? null,
				'lname' => $params["lname"] ?? null,
				'auxname' => $params["auxname"] ?? null,
				'gender' => $params["gender"] ?? null,
				'birthdate' => $params["bday"] ?? null,
				'contact_number' => $params["contact_number"] ?? null,
				'email' => $params["email"] ?? null
			]);
		}
	}

	class FileUpload extends Main{
		public function __construct($params = []){
			parent::__construct('file_upload_tbl',[
				'file_upload_id' => $params["id"] ?? null,
				'category_tag' => $params["category_tag"] ?? null,
				'category_score' => $params["category_score"] ?? null,
				'mime_type' => $params["mime_type"] ?? null,
				'file_name' => $params["file_name"] ?? null,
				'drive_id' => $params["drive_id"] ?? null,
				'datetime_uploaded' => $params["datetime_uploaded"] ?? null,
				'uploaded_by' => $params["uploaded_by"] ?? null
			]);
		}
	}

	class TaskCategory extends Main{
		public function __construct($params = []){
			parent::__construct('task_category_tbl',[
				'task_category_id' => $params["id"] ?? null,
				'task_category' => $params["task_category"] ?? null
			]);
		}
	}

	class Task extends Main{
		public function __construct($params = []){
			parent::__construct('task_tbl',[
				'task_id' => $params["id"] ?? null,
				'task_category_id' => $params["task_category_id"] ?? null,
				'task_title' => $params["task_title"] ?? null,
				'task_description' => $params["task_description"] ?? null,
				'task_deadline' => $params["task_deadline"] ?? null,
				'assigned_to' => $params["assigned_to"] ?? null
			]);
		}

		public function getTasks() {
        	return $this->getChildren("task_tbl", "task_category_id", $this->fields['task_category_id']);
    	}
	}

	class TaskSubmission extends Main{
		public function __construct($params = []){
			parent::__construct('task_submission_tbl',[
				'task_submission_id' => $params["id"] ?? null,
				'task_id' => $params["task_id"] ?? null,
				'file_upload_id' => $params["file_upload_id"] ?? null,
				'check_status' => $params["check_status"] ?? null
			]);
		}
	}

	class User extends Main{
		public function __construct($params = []){
			parent::__construct('user_tbl',[
				'user_id' => $params["id"] ?? null,
				'user_name' => $params["user_name"] ?? null,
				'pass_word' => $params["pass_word"] ?? null,
				'position_id' => $params["position_id"] ?? null,
				'profile_id' => $params["profile_id"] ?? null,
				'user_type' => $params["user_type"] ?? null,
				'auth_token' => $params["auth_token"] ?? null
			]);
		}
	}

	class Delete extends Main{
		public function __construct($params = []){
			parent::__construct('deleted_record_tbl',[
				'delete_id' => $params["id"] ?? null,
				'data_deleted' => $params["data"] ?? null,
				'reason_for_deletion' => $params["reason"] ?? null,
				'table_origin' => $params["table"] ?? null,
				'datetime_deleted' => $params["datetime"] ?? null
			]);
		}
	}

	class UserManager {
		private $db;

		public function __construct() {
			$this->db = Database::getInstance();
		}

		public function changeMemberPassword($memberId, $data) {
			$db = Database::getInstance()->getConnection();
			$user = $db->selectOne("SELECT * FROM user_tbl WHERE user_id = ?", [$memberId]);
			if (!$user) return ['success' => false, 'msg' => 'User not found'];
			if (!password_verify($data['current_password'], $user['pass_word'])) return ['success' => false, 'msg' => 'Current password incorrect'];
			if ($data['new_password'] !== $data['confirm_password']) return ['success' => false, 'msg' => 'Passwords do not match'];
			$hashed = password_hash($data['new_password'], PASSWORD_DEFAULT);
			$db->query("UPDATE user_tbl SET pass_word = ? WHERE user_id = ?", [$hashed, $memberId]);
			return ['success' => true, 'msg' => 'Password changed'];
		}

		public function getUsersByType($userType) {
			try {
				$query = "SELECT u.*, p.* FROM user_tbl u 
						  JOIN profile_tbl p ON u.profile_id = p.profile_id 
						  WHERE u.user_type = :user_type";
				return $this->db->select($query, [':user_type' => $userType]);
			} catch (Exception $e) {
				throw new Exception("Failed to get users: " . $e->getMessage());
			}
		}

		public function createUser($data) {
			// Validate required fields
			$required = ['user_name', 'password', 'fname', 'lname', 'email', 'user_type', 'gender', 'birthdate'];
			foreach($required as $field) {
				if(empty(trim($data[$field] ?? ""))) {
					throw new Exception("Missing required field: " . $field);
				}
			}

			$connection = $this->db->getConnection();
			$connection->beginTransaction();

			try {
				// Check if username already exists
				$user = new User(['user_name' => trim($data['user_name'])]);
				if($user->checkFromTable('user_name')) {
					throw new Exception("Username already exists");
				}

				// Check if email already exists
				$profile = new Profile(['email' => trim($data['email'])]);
				if($profile->checkFromTable('email')) {
					throw new Exception("Email already exists");
				}

			// Get position ID based on user type
			$position_id = 5; // Default to Adviser for subadmins
			if($data['user_type'] === 'student') {
				$position_id = 2; // Student Government Member
			} elseif($data['user_type'] === 'admin') {
				$position_id = 3; // System Administrator
			} elseif($data['user_type'] === 'subadmin' && !empty($data['subadmin_role'])) {
				// Map subadmin role to position_id
				$roleMap = [
					'Adviser' => 5,
					'President' => 6,
					'Vice-President' => 7,
					'Secretary' => 8
				];
				$position_id = $roleMap[$data['subadmin_role']] ?? 5; // Default to Adviser if role not found
			}				// Create profile first
				$profile = new Profile([
					'fname' => trim($data['fname']),
					'mname' => trim($data['mname'] ?? ""),
					'lname' => trim($data['lname']),
					'auxname' => trim($data['auxname'] ?? ""),
					'gender' => $data['gender'],
					'bday' => $data['birthdate'],
					'contact_number' => trim($data['contact_number'] ?? ""),
					'email' => trim($data['email'])
				]);

				$profile_id = $profile->insertAndGetId();
				if(!$profile_id) {
					throw new Exception("Failed to create profile");
				}

				// Create user
				$user = new User([
					'user_name' => trim($data['user_name']),
					'pass_word' => password_hash($data['password'], PASSWORD_DEFAULT),
					'position_id' => $position_id,
					'profile_id' => $profile_id,
					'user_type' => $data['user_type']
				]);

				$user_id = $user->insertAndGetId();
				if($user_id) {
					// Handle sub-admin role if provided
					if($data['user_type'] === 'subadmin' && !empty($data['subadmin_role'])) {
						$this->setSubadminRole($user_id, $data['subadmin_role']);
					}

					$connection->commit();
					return $user_id; // Return user ID for permission setting
				} else {
					throw new Exception("Failed to create user");
				}

			} catch(Exception $e) {
				$connection->rollback();
				throw $e;
			}
		}

		public function updateUser($data) {
			// Validate required fields
			$required = ['user_id', 'user_name', 'fname', 'lname', 'email', 'gender', 'birthdate'];
			foreach($required as $field) {
				if(empty(trim($data[$field] ?? ""))) {
					throw new Exception("Missing required field: " . $field);
				}
			}

			$connection = $this->db->getConnection();
			$connection->beginTransaction();

			try {
				// Get current user data
				$query = "SELECT u.*, p.profile_id FROM user_tbl u 
						  JOIN profile_tbl p ON u.profile_id = p.profile_id 
						  WHERE u.user_id = :user_id";
				$stmt = $connection->prepare($query);
				$stmt->execute([':user_id' => $data['user_id']]);
				$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

				if(!$currentUser) {
					throw new Exception("User not found");
				}

				// Check if username is taken by another user
				$query = "SELECT user_id FROM user_tbl WHERE user_name = :username AND user_id != :user_id";
				$stmt = $connection->prepare($query);
				$stmt->execute([':username' => trim($data['user_name']), ':user_id' => $data['user_id']]);
				if($stmt->rowCount() > 0) {
					throw new Exception("Username already exists");
				}

				// Check if email is taken by another user
				$query = "SELECT p.profile_id FROM profile_tbl p 
						  JOIN user_tbl u ON p.profile_id = u.profile_id 
						  WHERE p.email = :email AND u.user_id != :user_id";
				$stmt = $connection->prepare($query);
				$stmt->execute([':email' => trim($data['email']), ':user_id' => $data['user_id']]);
				if($stmt->rowCount() > 0) {
					throw new Exception("Email already exists");
				}

				// Update profile
				$profile = new Profile([
					'fname' => trim($data['fname']),
					'mname' => trim($data['mname'] ?? ""),
					'lname' => trim($data['lname']),
					'auxname' => trim($data['auxname'] ?? ""),
					'gender' => $data['gender'],
					'bday' => $data['birthdate'],
					'contact_number' => trim($data['contact_number'] ?? ""),
					'email' => trim($data['email'])
				]);

				if(!$profile->update('profile_id', $currentUser['profile_id'])) {
					throw new Exception("Failed to update profile");
				}

				// Update user
				$userData = [
					'user_name' => trim($data['user_name'])
				];

				// Only update password if provided
				if(!empty(trim($data['password']))) {
					$userData['pass_word'] = password_hash($data['password'], PASSWORD_DEFAULT);
				}

				$user = new User($userData);
				if($user->update('user_id', $data['user_id'])) {
					// Handle sub-admin role if provided
					if($data['user_type'] === 'subadmin' && !empty($data['subadmin_role'])) {
						$this->setSubadminRole($data['user_id'], $data['subadmin_role']);
					}

					$connection->commit();
					return ['success' => true, 'message' => 'User updated successfully'];
				} else {
					throw new Exception("Failed to update user");
				}

			} catch(Exception $e) {
				$connection->rollback();
				throw $e;
			}
		}

		public function deleteUser($user_id, $user_type = '', $reason = 'Administrative deletion') {
			// Handle both old array format and new parameter format
			if(is_array($user_id)) {
				$data = $user_id;
				$user_id = $data['user_id'] ?? 0;
				$reason = $data['reason'] ?? 'Administrative deletion';
			}

			if(empty($user_id)) {
				throw new Exception("User ID is required");
			}

			$connection = $this->db->getConnection();
			$connection->beginTransaction();

			try {
				// Get user data before deletion for logging
				$query = "SELECT u.*, p.* FROM user_tbl u 
						  JOIN profile_tbl p ON u.profile_id = p.profile_id 
						  WHERE u.user_id = :user_id";
				$stmt = $connection->prepare($query);
				$stmt->execute([':user_id' => $user_id]);
				$userData = $stmt->fetch(PDO::FETCH_ASSOC);

				if(!$userData) {
					throw new Exception("User not found");
				}

				// Log deletion
				$deleteLog = new Delete([
					'data' => json_encode($userData),
					'reason' => trim($reason),
					'table' => 'user_tbl',
					'datetime' => date('Y-m-d H:i:s')
				]);

				if(!$deleteLog->insert()) {
					throw new Exception("Failed to log deletion");
				}

				// Delete permissions first if they exist
				try {
					$query = "DELETE FROM subadmin_permissions_tbl WHERE user_id = :user_id";
					$stmt = $connection->prepare($query);
					$stmt->execute([':user_id' => $user_id]);
				} catch(Exception $e) {
					// Table might not exist, continue
				}

				// Delete sub-admin role if exists
				try {
					$query = "DELETE FROM subadmin_roles_tbl WHERE user_id = :user_id";
					$stmt = $connection->prepare($query);
					$stmt->execute([':user_id' => $user_id]);
				} catch(Exception $e) {
					// Table might not exist, continue
				}

				// Delete user (this will cascade delete profile due to foreign key constraints)
				$user = new User();
				if($user->delete('user_id', $user_id)) {
					$connection->commit();
					return ['success' => true, 'message' => 'User deleted successfully'];
				} else {
					throw new Exception("Failed to delete user");
				}

			} catch(Exception $e) {
				$connection->rollback();
				throw $e;
			}
		}

		public function getUserById($user_id) {
			if(empty($user_id)) {
				throw new Exception("User ID required");
			}

			try {
				$query = "SELECT u.*, p.* FROM user_tbl u 
						  JOIN profile_tbl p ON u.profile_id = p.profile_id 
						  WHERE u.user_id = :user_id";
				$userData = $this->db->select($query, [':user_id' => $user_id]);

				if(empty($userData)) {
					throw new Exception("User not found");
				}

				return $userData[0];

			} catch(Exception $e) {
				throw $e;
			}
		}

		public function setSubadminRole($user_id, $role) {
			if(empty($user_id) || empty($role)) {
				return false;
			}

			try {
				$connection = $this->db->getConnection();
				
				// Check if role already exists, update or insert
				$query = "INSERT INTO subadmin_roles_tbl (user_id, role) VALUES (:user_id, :role) 
						  ON DUPLICATE KEY UPDATE role = :role2";
				$stmt = $connection->prepare($query);
				return $stmt->execute([
					':user_id' => $user_id,
					':role' => $role,
					':role2' => $role
				]);
			} catch(Exception $e) {
				// If table doesn't exist, we'll just store it in user_tbl for now
				$connection = $this->db->getConnection();
				$query = "UPDATE user_tbl SET user_type = :user_type WHERE user_id = :user_id";
				$stmt = $connection->prepare($query);
				return $stmt->execute([
					':user_type' => 'subadmin',
					':user_id' => $user_id
				]);
			}
		}

		public function getSubadminRole($user_id) {
			try {
				$connection = $this->db->getConnection();
				$query = "SELECT role FROM subadmin_roles_tbl WHERE user_id = :user_id";
				$stmt = $connection->prepare($query);
				$stmt->execute([':user_id' => $user_id]);
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				return $result ? $result['role'] : null;
			} catch(Exception $e) {
				return null;
			}
		}

		public function setUserPermissions($user_id, $permissions) {
			if(empty($user_id) || empty($permissions)) {
				return false;
			}

			try {
				$connection = $this->db->getConnection();
				$connection->beginTransaction();

				// Delete existing permissions
				$query = "DELETE FROM subadmin_permissions_tbl WHERE user_id = :user_id";
				$stmt = $connection->prepare($query);
				$stmt->execute([':user_id' => $user_id]);

				// Insert new permissions
				foreach($permissions as $module => $perms) {
					$query = "INSERT INTO subadmin_permissions_tbl 
							  (user_id, permission_key, permission_name, can_view, can_create, can_edit, can_delete) 
							  VALUES (:user_id, :permission_key, :permission_name, :can_view, :can_create, :can_edit, :can_delete)";
					$stmt = $connection->prepare($query);
					$stmt->execute([
						':user_id' => $user_id,
						':permission_key' => $module,
						':permission_name' => ucwords(str_replace('_', ' ', $module)),
						':can_view' => isset($perms['view']) ? 1 : 0,
						':can_create' => isset($perms['create']) ? 1 : 0,
						':can_edit' => isset($perms['edit']) ? 1 : 0,
						':can_delete' => isset($perms['delete']) ? 1 : 0
					]);
				}

				$connection->commit();
				return true;
			} catch(Exception $e) {
				$connection->rollback();
				throw new Exception("Failed to set permissions: " . $e->getMessage());
			}
		}

		public function getUserPermissions($user_id) {
			try {
				$query = "SELECT * FROM subadmin_permissions_tbl WHERE user_id = :user_id";
				return $this->db->select($query, [':user_id' => $user_id]);
			} catch(Exception $e) {
				return [];
			}
		}

		public function getUsersByTypeWithRoles($userType) {
			try {
				if($userType === 'subadmin') {
					$query = "SELECT u.*, p.*, sr.role as subadmin_role 
							  FROM user_tbl u 
							  JOIN profile_tbl p ON u.profile_id = p.profile_id 
							  LEFT JOIN subadmin_roles_tbl sr ON u.user_id = sr.user_id
							  WHERE u.user_type = :user_type";
				} else {
					$query = "SELECT u.*, p.* FROM user_tbl u 
							  JOIN profile_tbl p ON u.profile_id = p.profile_id 
							  WHERE u.user_type = :user_type";
				}
				return $this->db->select($query, [':user_type' => $userType]);
			} catch (Exception $e) {
				throw new Exception("Failed to get users: " . $e->getMessage());
			}
		}
	}

	class EntityManager extends Main {

    public static function createPosition($name) {
        try {
            $name = trim($name);
            if(empty($name)) {
                throw new Exception("Position name is required");
            }

            $pos = new Position(['position' => $name]);
            $pos->findOrCreate('position_id', ['position']);
            return ['success' => true, 'message' => 'Successfully added new position'];
        } catch(Exception $e) {
            throw new Exception("Failed! An error was detected: " . $e->getMessage());
        }
    }

    public static function createTaskCategory($name) {
        try {
            $name = trim($name);
            if(empty($name)) {
                throw new Exception("Task category name is required");
            }

            $tc = new TaskCategory(['task_category' => $name]);
            $tc->findOrCreate('task_category_id', ['task_category']);
            return ['success' => true, 'message' => 'Successfully added new task category'];
        } catch(Exception $e) {
            throw new Exception("Failed! An error was detected: " . $e->getMessage());
        }
    }

    // ✅ Fixed database calls
    public static function getAllPositions() {
        $db = Database::getInstance(); // Use wrapper, not raw PDO
        $query = "SELECT position_id, position FROM position_tbl ORDER BY position ASC";
        return $db->select($query);
    }

    public static function getAllTaskCategories() {
        $db = Database::getInstance();
        $query = "SELECT task_category_id, task_category FROM task_category_tbl ORDER BY task_category ASC";
        return $db->select($query);
    }

    public static function getAllFileCategories() {
        $db = Database::getInstance();
        $query = "SELECT category_id as file_category_id, category_name as file_category, category_slug, description FROM category_tbl ORDER BY category_name ASC";
        return $db->select($query);
    }
}

	class TaskManager {
	/**
	 * Assign a task to a specific member or all student members
	 * @param array $taskData - Task details (title, description, deadline, etc.)
	 * @param int|null $memberId - If set, assign to this member only; if null, assign to all student members
	 * @return array - Result status and assigned member IDs
	 */
	
		private $db;

		public function __construct() {
			$this->db = Database::getInstance();
		}

		public function assignTaskToMembers($taskData, $memberId = null) {
			$db = Database::getInstance();
			$assignedIds = [];
			if ($memberId) {
				// Assign to one member
				$taskData['assigned_to'] = $memberId;
				$db->execute("INSERT INTO task_tbl (task_title, task_description, task_deadline, assigned_to, task_category_id) VALUES (?, ?, ?, ?, ?)", [
					$taskData['task_title'],
					$taskData['task_description'],
					$taskData['task_deadline'],
					$taskData['assigned_to'],
					$taskData['task_category_id'] ?? null
				]);
				$assignedIds[] = $memberId;
			} else {
				// Assign to all student members
				$students = $db->select("SELECT user_id FROM user_tbl WHERE user_type = 'student'");
				foreach ($students as $student) {
					$taskData['assigned_to'] = $student['user_id'];
					$db->execute("INSERT INTO task_tbl (task_title, task_description, task_deadline, assigned_to, task_category_id) VALUES (?, ?, ?, ?, ?)", [
						$taskData['task_title'],
						$taskData['task_description'],
						$taskData['task_deadline'],
						$taskData['assigned_to'],
						$taskData['task_category_id'] ?? null
					]);
					$assignedIds[] = $student['user_id'];
				}
			}
			return [
				'status' => 'SUCCESS',
				'assigned_member_ids' => $assignedIds,
				'msg' => ($memberId ? 'Task assigned to member.' : 'Task assigned to all student members.')
			];
		}

		/*public function getMemberActiveTasks($memberId) {
			$db = Database::getInstance()->getConnection();
			// Remove assigned_to reference, fetch tasks for memberId if possible
			$tasks = $db->select("SELECT * FROM task_tbl WHERE task_deadline >= CURDATE()", []);
			return ['data' => $tasks];
		}*/


		
		
		public function getMemberActiveTasks($memberId) {
			$db = Database::getInstance();

			try {
				$sql = "
					SELECT 
						t.task_id,
						t.task_title,
						c.task_category AS task_category,
						t.task_deadline,
						CASE 
							WHEN t.task_deadline >= CURDATE() THEN 'active'
							ELSE 'inactive'
						END AS task_status
					FROM task_tbl AS t
					LEFT JOIN task_category_tbl AS c
						ON c.task_category_id = t.task_category_id
					LEFT JOIN task_submission_tbl AS s
						ON s.task_id = t.task_id 
						AND s.submitted_by = ?
						AND s.check_status = 'Approved'
					WHERE 
						t.assigned_to = ?
						AND s.task_submission_id IS NULL
					ORDER BY t.task_deadline ASC
				";

				// Execute query - pass memberId twice for both placeholders
				$tasks = $db->select($sql, [$memberId, $memberId]) ?: [];

				// Normalize keys
				$tasks = array_map(function($t) {
					return [
						'task_id'       => (int)($t['task_id'] ?? 0),
						'task_title'    => $t['task_title'] ?? '',
						'task_category' => $t['task_category'] ?? '-',
						'task_deadline' => $t['task_deadline'] ?? '',
						'task_status'   => $t['task_status'] ?? 'inactive',
					];
				}, $tasks);

				return ['data' => $tasks];

			} catch (Exception $e) {
				return ['data' => [], 'error' => $e->getMessage()];
			}
		}


		/*public function getMemberTaskSubmissions($memberId) {
			$db = Database::getInstance()->getConnection();
			$subs = $db->select("SELECT * FROM task_submission_tbl WHERE submitted_by = ?", [$memberId]);
			return ['data' => $subs];
		}*/
		public function getMemberTaskSubmissions($memberId) 
{
			$db = Database::getInstance(); // <-- FIXED

			$sql = "
				SELECT 
					t.task_id,
					t.task_title,
					c.task_category,
					t.task_deadline,
					
					s.task_submission_id,
					s.check_status,
					s.file_upload_id,
					
					f.file_name

				FROM task_tbl t
				LEFT JOIN task_category_tbl c 
					ON t.task_category_id = c.task_category_id
				LEFT JOIN task_submission_tbl s 
					ON s.task_id = t.task_id AND s.submitted_by = ?
				LEFT JOIN file_upload_tbl f 
					ON f.file_upload_id = s.file_upload_id
				
				WHERE t.assigned_to IS NULL 
				OR FIND_IN_SET(?, t.assigned_to)
			";

			$rows = $db->select($sql, [$memberId, $memberId]);

			return ['data' => $rows];
		}

		public function getMemberTaskDetails($memberId, $taskId) {
			$db = Database::getInstance()->getConnection();
			$task = $db->selectOne("SELECT * FROM task_tbl WHERE task_id = ? AND assigned_to = ?", [$taskId, $memberId]);
			return $task ? ['success' => true, 'data' => $task] : ['success' => false, 'msg' => 'Task not found'];
		}

		public function createTask($data) {
			try {
				// Validate required fields
				if (empty($data['task_title']) || empty($data['task_description']) || 
					empty($data['task_deadline']) || empty($data['task_category_id'])) {
					throw new Exception("All task fields are required");
				}

				// Validate assigned_to is required and valid
				if (empty($data['assigned_to']) || !is_numeric($data['assigned_to'])) {
					throw new Exception("Task must be assigned to a specific member");
				}
				
				$assignedTo = (int)$data['assigned_to'];

				// Create task
				$task = new Task([
					'task_category_id' => $data['task_category_id'],
					'task_title' => $data['task_title'],
					'task_description' => $data['task_description'],
					'task_deadline' => $data['task_deadline'],
					'assigned_to' => $assignedTo
				]);

				$taskId = $task->insertAndGetId();
				if (!$taskId) {
					throw new Exception("Failed to create task");
				}

				// Notify the assigned member
				$this->notifyMembersNewTask($taskId, $data['task_title'], $assignedTo);

				return ["status" => "SUCCESS", "msg" => "Task created successfully", "task_id" => $taskId];
			} catch (Exception $e) {
				return ["status" => "ERROR", "msg" => $e->getMessage()];
			}
		}

		public function getTasks($userId = null) {
			try {
				$query = "SELECT t.*, tc.task_category, 
						 COUNT(ts.task_submission_id) as submission_count,
						 COUNT(CASE WHEN ts.check_status = 'approved' THEN 1 END) as approved_count,
						 COUNT(CASE WHEN ts.check_status = 'pending' THEN 1 END) as pending_count,
						 CONCAT(p.fname, ' ', p.lname) as assigned_member_name
						 FROM task_tbl t 
						 LEFT JOIN task_category_tbl tc ON t.task_category_id = tc.task_category_id
						 LEFT JOIN task_submission_tbl ts ON t.task_id = ts.task_id
						 LEFT JOIN user_tbl u ON t.assigned_to = u.user_id
						 LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
						 GROUP BY t.task_id
						 ORDER BY t.task_deadline ASC";
				
				return $this->db->select($query);
			} catch (Exception $e) {
				return [];
			}
		}

		public function getTasksForMember($userId) {
			$db = Database::getInstance()->getConnection();
			try {
				// Get user's position to check permissions
				$userQuery = "SELECT u.position_id FROM user_tbl u WHERE u.user_id = :user_id";
				$userData = $this->db->select($userQuery, [':user_id' => $userId]);
				
				if (empty($userData)) return [];
				$positionId = $userData[0]['position_id'];

				// Get tasks with permission check
			$query = "SELECT t.*, tc.task_category,
							ts.task_submission_id, ts.check_status, ts.file_upload_id,
							fu.file_name, fu.datetime_uploaded as submission_date,
							CONCAT(p.fname, ' ', p.lname) AS full_name
					FROM task_tbl t 
					LEFT JOIN task_category_tbl tc ON t.task_category_id = tc.task_category_id
					LEFT JOIN task_submission_tbl ts ON t.task_id = ts.task_id
					LEFT JOIN file_upload_tbl fu ON ts.file_upload_id = fu.file_upload_id 
						AND fu.uploaded_by = :user_id
					LEFT JOIN user_tbl u ON fu.uploaded_by = u.user_id
					LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
					ORDER BY t.task_deadline ASC";				return $this->db->select($query, [':position_id' => $positionId, ':user_id' => $userId]);
			} catch (Exception $e) {
				return [];
			}
		}
		public function submitMemberTaskFile($data) {
			$db = Database::getInstance()->getConnection();

			// Validate
			if (empty($data['task_id']) || empty($data['file_id'])) {
				return ['success' => false, 'msg' => 'Task ID and File ID are required.'];
			}

			if (empty($data['submitted_by'])) {
				return ['success' => false, 'msg' => 'Submitted by is required.'];
			}

			try {
				// Validate file exists
				$stmt = $db->prepare("SELECT file_upload_id FROM file_upload_tbl WHERE file_upload_id = ?");
				$stmt->execute([$data['file_id']]);
				if ($stmt->rowCount() == 0) {
					return ['success' => false, 'msg' => 'File does not exist.'];
				}

				// Validate task exists
				$stmt = $db->prepare("SELECT task_id FROM task_tbl WHERE task_id = ?");
				$stmt->execute([$data['task_id']]);
				if ($stmt->rowCount() == 0) {
					return ['success' => false, 'msg' => 'Task does not exist.'];
				}

				// Prevent duplicate submission
				$stmt = $db->prepare("SELECT task_submission_id 
									FROM task_submission_tbl 
									WHERE task_id = ? AND file_upload_id = ?");
				$stmt->execute([$data['task_id'], $data['file_id']]);
				if ($stmt->rowCount() > 0) {
					return ['success' => false, 'msg' => 'This file has already been submitted for this task.'];
				}

				// Insert submission correctly (NOW WITH submitted_by)
				$stmt = $db->prepare("
					INSERT INTO task_submission_tbl 
						(task_id, file_upload_id, check_status, submitted_by)
					VALUES 
						(?, ?, 'Pending', ?)
				");

				$stmt->execute([
					$data['task_id'],
					$data['file_id'],
					$data['submitted_by']
				]);

				return [
					'success' => true,
					'msg' => 'Task file submitted successfully.'
				];

			} catch (PDOException $e) {
				return ['success' => false, 'msg' => $e->getMessage()];
			}
		}

		public function submitTask($data) {
			try {
				// Validate required fields
				if (empty($data['task_id']) || empty($data['file_upload_id'])) {
					throw new Exception("Task ID and file are required");
				}

				// Check if submission already exists
				$existing = $this->db->select(
					"SELECT * FROM task_submission_tbl WHERE task_id = :task_id AND file_upload_id IN 
					(SELECT file_upload_id FROM file_upload_tbl WHERE uploaded_by = :user_id)",
					[':task_id' => $data['task_id'], ':user_id' => $data['uploaded_by']]
				);

				if (!empty($existing)) {
					// Update existing submission
					$submission = new TaskSubmission([
						'task_id' => $data['task_id'],
						'file_upload_id' => $data['file_upload_id'],
						'check_status' => 'pending'
					]);
					$result = $submission->update('task_submission_id', $existing[0]['task_submission_id']);
				} else {
					// Create new submission
					$submission = new TaskSubmission([
						'task_id' => $data['task_id'],
						'file_upload_id' => $data['file_upload_id'],
						'check_status' => 'pending'
					]);
					$result = $submission->insert();
				}

				if (!$result) {
					throw new Exception("Failed to submit task");
				}

				// Notify advisers about new submission
				$this->notifyAdvisersNewSubmission($data['task_id'], $data['uploaded_by']);

				return ["status" => "SUCCESS", "msg" => "Task submitted successfully"];
			} catch (Exception $e) {
				return ["status" => "ERROR", "msg" => $e->getMessage()];
			}
		}

		public function reviewSubmission($data) {
			$db = Database::getInstance()->getConnection();
			try {
				// Validate required fields
				if (empty($data['task_submission_id']) || empty($data['check_status'])) {
					throw new Exception("Submission ID and status are required");
				}

				// Update submission status
				$submission = new TaskSubmission();
				$result = $submission->updateSingleValue(
					'check_status', 
					'task_submission_id', 
					$data['task_submission_id'], 
					$data['check_status']
				);

				if (!$result) {
					throw new Exception("Failed to update submission status");
				}

				// Get submission details for notification
				$submissionData = $this->db->select(
					"SELECT ts.*, fu.uploaded_by, t.task_title 
					 FROM task_submission_tbl ts
					 JOIN file_upload_tbl fu ON ts.file_upload_id = fu.file_upload_id
					 JOIN task_tbl t ON ts.task_id = t.task_id
					 WHERE ts.task_submission_id = :id",
					[':id' => $data['task_submission_id']]
				);

				if (!empty($submissionData)) {
					$this->notifyMemberSubmissionReview(
						$submissionData[0]['uploaded_by'],
						$submissionData[0]['task_title'],
						$data['check_status']
					);
				}

				return ["status" => "SUCCESS", "msg" => "Submission reviewed successfully"];
			} catch (Exception $e) {
				return ["status" => "ERROR", "msg" => $e->getMessage()];
			}
		}

		public function getSubmissions($taskId = null) {
			$db = Database::getInstance()->getConnection();
			try {
				$query = "SELECT ts.*, t.task_title, t.task_deadline,
						 fu.file_name, fu.datetime_uploaded,
						 p.fname, p.lname, u.user_name
						 FROM task_submission_tbl ts
						 JOIN task_tbl t ON ts.task_id = t.task_id
						 JOIN file_upload_tbl fu ON ts.file_upload_id = fu.file_upload_id
						 JOIN user_tbl u ON fu.uploaded_by = u.user_id
						 JOIN profile_tbl p ON u.profile_id = p.profile_id";
				
				$params = [];
				if ($taskId) {
					$query .= " WHERE ts.task_id = :task_id";
					$params[':task_id'] = $taskId;
				}
				
				$query .= " ORDER BY fu.datetime_uploaded DESC";
				
				return $this->db->select($query, $params);
			} catch (Exception $e) {
				return [];
			}
		}

		public function deleteTask($taskId, $reason) {
			$db = Database::getInstance()->getConnection();
			try {
				// Get task data for deletion record
				$taskData = $this->db->select(
					"SELECT * FROM task_tbl WHERE task_id = :id", 
					[':id' => $taskId]
				);

				if (empty($taskData)) {
					throw new Exception("Task not found");
				}

				// Save deletion record
				$deleteRecord = new Delete([
					'data_deleted' => json_encode($taskData[0]),
					'reason_for_deletion' => $reason,
					'table_origin' => 'task_tbl',
					'datetime' => date('Y-m-d H:i:s')
				]);
				$deleteRecord->insert();

				// Delete task (cascades to submissions)
				$task = new Task();
				$result = $task->delete('task_id', $taskId);

				if (!$result) {
					throw new Exception("Failed to delete task");
				}

				return ["status" => "SUCCESS", "msg" => "Task deleted successfully"];
			} catch (Exception $e) {
				return ["status" => "ERROR", "msg" => $e->getMessage()];
			}
		}

		private function notifyMembersNewTask($taskId, $taskTitle, $assignedTo = null) {
			$db = Database::getInstance()->getConnection();

			if ($assignedTo) {
				// Notify only the assigned user
				$members = $this->db->select("
					SELECT u.user_id, CONCAT(p.fname, ' ', p.lname) AS full_name
					FROM user_tbl u
					JOIN profile_tbl p ON u.profile_id = p.profile_id
					WHERE u.user_id = ?
				", [$assignedTo]);
			} else {
				// Notify all members
				$members = $this->db->select("
					SELECT u.user_id, CONCAT(p.fname, ' ', p.lname) AS full_name
					FROM user_tbl u
					JOIN profile_tbl p ON u.profile_id = p.profile_id
					JOIN position_tbl pos ON u.position_id = pos.position_id
					WHERE pos.position = 'Student Government Member'
				");
			}

			$notificationManager = new NotificationManager();
			foreach ($members as $member) {
				$notificationManager->createNotification([
					'user_id' => $member['user_id'],
					'type' => 'new_task',
					'title' => 'New Task Assigned',
					'message' => "New task '{$taskTitle}' has been assigned to you",
					'related_id' => $taskId
				]);
			}
		}

		private function notifyAdvisersNewSubmission($taskId, $submitterId) {
			$db = Database::getInstance()->getConnection();
			// Get task title and submitter name
			$data = $this->db->select(
				"SELECT t.task_title, CONCAT(p.fname, ' ', p.lname) AS full_name
				FROM task_tbl t
				JOIN user_tbl u ON u.user_id = :user_id
				JOIN profile_tbl p ON u.profile_id = p.profile_id
				WHERE t.task_id = :task_id",
				[':task_id' => $taskId, ':user_id' => $submitterId]
			);

			if (!empty($data)) {
				$taskTitle = $data[0]['task_title'];
				$submitterName = $data[0]['full_name'];

				// Get all advisers
				$advisers = $this->db->select(
					"SELECT u.user_id, CONCAT(p.fname, ' ', p.lname) AS full_name
					FROM user_tbl u
					JOIN position_tbl pos ON u.position_id = pos.position_id
					JOIN profile_tbl p ON u.profile_id = p.profile_id
					WHERE pos.position = 'Adviser'"
				);

				$notificationManager = new NotificationManager();
				foreach ($advisers as $adviser) {
					$notificationManager->createNotification([
						'user_id' => $adviser['user_id'],
						'type' => 'new_submission',
						'title' => 'New Task Submission',
						'message' => "{$submitterName} submitted '{$taskTitle}'",
						'related_id' => $taskId
					]);
				}
			}
		}

		private function notifyMemberSubmissionReview($userId, $taskTitle, $status) {
			$notificationManager = new NotificationManager();
			$statusText = ucfirst($status);
			$message = "Your submission for '{$taskTitle}' has been {$statusText}";

			$notificationManager->createNotification([
				'user_id' => $userId,
				'type' => 'submission_reviewed',
				'title' => 'Submission Reviewed',
				'message' => $message,
				'related_id' => null
			]);
		}

		public function updateTask($data) {
			try {
				if (empty($data['task_id']) || empty($data['task_title']) || empty($data['task_description']) || empty($data['task_deadline']) || empty($data['task_category_id'])) {
					throw new Exception("All task fields are required");
				}

				$task = new Task([
					'task_category_id' => $data['task_category_id'],
					'task_title' => $data['task_title'],
					'task_description' => $data['task_description'],
					'task_deadline' => $data['task_deadline']
				]);

				$updated = $task->update('task_id', $data['task_id']);

				if (!$updated) {
					throw new Exception("Failed to update task");
				}

				return ["status" => "SUCCESS", "msg" => "Task updated successfully"];
			} catch (Exception $e) {
				return ["status" => "ERROR", "msg" => $e->getMessage()];
			}
		}
	}

	class NotificationManager {
		private $db;

		public function __construct() {
			$this->db = Database::getInstance()->getConnection();
		}

		/** GET notifications */
		public function getNotifications($userId, $unreadOnly = false, $limit = 20) {
			$query = "SELECT * FROM notifications_tbl WHERE user_id = :user_id";
			$params = [':user_id' => $userId];

			if ($unreadOnly) {
				$query .= " AND is_read = 0";
			}

			$query .= " ORDER BY datetime_created DESC LIMIT $limit";

			// Uses your select() method (safe)
			return Database::getInstance()->select($query, $params);
		}

		/** Mark single notification as read */
		public function markAsRead($notificationId) {
			$query = "UPDATE notifications_tbl SET is_read = 1 WHERE notification_id = :id";

			$stmt = $this->db->prepare($query);
			return $stmt->execute([':id' => $notificationId]);
		}

		/** Create notification */
		public function createNotification($data) {
			$query = "INSERT INTO notifications_tbl 
						(user_id, type, title, message, related_id, is_read, datetime_created)
					VALUES 
						(:user_id, :type, :title, :message, :related_id, 0, NOW())";

			$stmt = $this->db->prepare($query);

			return $stmt->execute([
				':user_id' => $data['user_id'],
				':type' => $data['type'],
				':title' => $data['title'],
				':message' => $data['message'],
				':related_id' => $data['related_id']
			]);
		}

		/** Count unread notifications */
		public function getUnreadCount($userId) {
			$query = "SELECT COUNT(*) AS count 
					FROM notifications_tbl 
					WHERE user_id = :user_id AND is_read = 0";

			$result = Database::getInstance()->select($query, [':user_id' => $userId]);
			return $result[0]['count'] ?? 0;
		}
	}

	class FileManager {

    // Set file permissions (deprecated - file categories no longer use permissions)
    public function setFilePermission($positionId, $categoryId) {
        $db = Database::getInstance();
        
        // Check if permission already exists
        $exists = $db->selectOne("
            SELECT permission_id 
            FROM category_permissions_tbl 
            WHERE position_id = ? AND category_id = ?
            LIMIT 1
        ", [$positionId, $categoryId]);
        
        if ($exists) {
            return [
                "status" => "ERROR",
                "msg" => "Permission already exists for this position and category"
            ];
        }
        
        // Insert new permission
        $db->execute("
            INSERT INTO category_permissions_tbl (position_id, category_id, created_at) 
            VALUES (?, ?, NOW())
        ", [$positionId, $categoryId]);
        
        return [
            "status" => "SUCCESS",
            "msg" => "Permission granted successfully"
        ];
    }

    // Remove file permissions
    public function removeFilePermission($positionId, $categoryId) {
        $db = Database::getInstance();
        
        $result = $db->execute("
            DELETE FROM category_permissions_tbl 
            WHERE position_id = ? AND category_id = ?
        ", [$positionId, $categoryId]);
        
        if ($result) {
            return [
                "status" => "SUCCESS",
                "msg" => "Permission revoked successfully"
            ];
        } else {
            return [
                "status" => "ERROR",
                "msg" => "Permission not found or already removed"
            ];
        }
    }

    // Check if user has access to a specific category
    private function userCanAccessCategory($userId, $categoryId) {
        $db = Database::getInstance();
        
        // Get user's position
        $user = $db->selectOne("SELECT position_id FROM user_tbl WHERE user_id = ?", [$userId]);
        if (!$user) return false;
        
        // Admin (position_id=3) always has full access
        if ($user['position_id'] == 3) return true;
        
        // Check if position has permission for this category
        $permission = $db->selectOne("
            SELECT permission_id 
            FROM category_permissions_tbl 
            WHERE position_id = ? AND category_id = ?
            LIMIT 1
        ", [$user['position_id'], $categoryId]);
        
        return $permission !== null;
    }

    // Download files with permission check
    public function downloadFile($fileId, $userId = null) {
        $db = Database::getInstance();

        $file = $db->selectOne("
            SELECT fu.file_name, fu.file_path, fu.mime_type, fu.original_filename, fu.category_id 
            FROM file_upload_tbl fu
            WHERE fu.file_upload_id = ?
            LIMIT 1
        ", [$fileId]);

        if (!$file) {
            throw new Exception("File not found.");
        }

        // Check permission if userId is provided and category_id exists
        if ($userId && $file['category_id']) {
            if (!$this->userCanAccessCategory($userId, $file['category_id'])) {
                throw new Exception("Access denied: You don't have permission to access this file category.");
            }
        }

        $fullPath = '../'.$file['file_path'];

        if (!file_exists($fullPath)) {
            throw new Exception("File does not exist on server.");
        }

        if (ob_get_length()) ob_end_clean();

        // Use original filename for download, fallback to system filename
        $downloadName = !empty($file['original_filename']) ? $file['original_filename'] : basename($file['file_name']);

        header("Content-Description: File Transfer");
        header("Content-Type: " . ($file['mime_type'] ?: "application/octet-stream"));
        header("Content-Disposition: attachment; filename=\"" . $downloadName . "\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate");
        header("Pragma: public");
        header("Content-Length: " . filesize($fullPath));

        readfile($fullPath);
        exit;
    }

    // Get filtered files by category and user permissions
    public function getFilteredFiles($categoryId = null, $userId = null) {
        $db = Database::getInstance();

        // Get user's position
        $user = $db->selectOne("SELECT position_id FROM user_tbl WHERE user_id = ?", [$userId]);
        if (!$user) throw new Exception("User not found.");

        $positionId = $user['position_id'];
        $isAdmin = ($positionId == 3);

        $params = [];
        $query = "
            SELECT 
                fu.file_upload_id,
                fu.file_name,
            fu.mime_type,
            fu.category_tag,
            fu.category_score,
            fu.datetime_uploaded,
            fu.file_path,
            fu.file_size,
            prof.fname,
            prof.lname,
            fu.category_tag as file_category
        FROM file_upload_tbl fu
        LEFT JOIN user_tbl u ON fu.uploaded_by = u.user_id
        INNER JOIN profile_tbl prof ON u.profile_id = prof.profile_id
    ";

    // Filter by category_tag if specified
    if ($categoryId !== null) {
        $query .= " WHERE fu.category_tag = ?";
        $params[] = $categoryId;
    }

    $query .= " ORDER BY fu.datetime_uploaded DESC";        return $db->select($query, $params);
    }

    // Get all files with permission filtering
    public function getAllFiles($userId = null) {
        $db = Database::getInstance();

        // Check if user exists and get their position
        $userPosition = null;
        if ($userId) {
            $user = $db->selectOne("SELECT position_id FROM user_tbl WHERE user_id = ?", [$userId]);
            $userPosition = $user['position_id'] ?? null;
        }

        // Admin (position_id=3) sees all files
        $isAdmin = ($userPosition == 3);

        if ($isAdmin || !$userId) {
            // Admin or no user specified - return all files
            $query = "
                SELECT 
                    fu.file_upload_id,
                    fu.original_filename,
                    fu.file_name,
                    fu.mime_type,
                    fu.category_id,
                    fu.category_tag,
                    fu.category_score,
                    fu.datetime_uploaded,
                    fu.file_path,
                    fu.file_size,
                    prof.fname,
                    prof.lname,
                    c.category_name as file_category,
                    c.category_slug
                FROM file_upload_tbl fu
                LEFT JOIN user_tbl u ON fu.uploaded_by = u.user_id 
                INNER JOIN profile_tbl prof ON u.profile_id = prof.profile_id
                LEFT JOIN category_tbl c ON fu.category_id = c.category_id
                ORDER BY fu.datetime_uploaded DESC
            ";
            return $db->select($query);
        } else {
            // Non-admin users - filter by category permissions
            $query = "
                SELECT 
                    fu.file_upload_id,
                    fu.original_filename,
                    fu.file_name,
                    fu.mime_type,
                    fu.category_id,
                    fu.category_tag,
                    fu.category_score,
                    fu.datetime_uploaded,
                    fu.file_path,
                    fu.file_size,
                    prof.fname,
                    prof.lname,
                    c.category_name as file_category,
                    c.category_slug
                FROM file_upload_tbl fu
                LEFT JOIN user_tbl u ON fu.uploaded_by = u.user_id 
                INNER JOIN profile_tbl prof ON u.profile_id = prof.profile_id
                LEFT JOIN category_tbl c ON fu.category_id = c.category_id
                WHERE fu.category_id IN (
                    SELECT category_id 
                    FROM category_permissions_tbl 
                    WHERE position_id = ?
                )
                ORDER BY fu.datetime_uploaded DESC
            ";
            return $db->select($query, [$userPosition]);
        }
    }

    // Get files accessible to subadmin/adviser based on their permissions
    public function getAdviserAccessibleFiles($userId, $filters = []) {
        $db = Database::getInstance();
        
        // Get user's type and position
        $user = $db->selectOne("SELECT user_type, position_id FROM user_tbl WHERE user_id = ?", [$userId]);
        if (!$user) {
            return ['data' => []];
        }
        
        $isSubadmin = ($user['user_type'] === 'subadmin');
        $isAdmin = ($user['position_id'] == 3);
        
        // Base query with category join
        $query = "
            SELECT 
                fu.file_upload_id,
                fu.original_filename,
                fu.file_name,
                fu.mime_type,
                fu.category_id,
                fu.category_tag,
                fu.category_score,
                fu.datetime_uploaded,
                fu.file_path,
                fu.file_size,
                prof.fname,
                prof.lname,
                c.category_name as file_category,
                c.category_slug
            FROM file_upload_tbl fu
            LEFT JOIN user_tbl u ON fu.uploaded_by = u.user_id 
            INNER JOIN profile_tbl prof ON u.profile_id = prof.profile_id
            LEFT JOIN category_tbl c ON fu.category_id = c.category_id
        ";
        
        $params = [];
        $whereConditions = [];
        
        // For subadmins, enforce category permissions
        if ($isSubadmin) {
            // Check if user has permission to view files module
            require_once __DIR__ . '/permission_class.php';
            if (!SubadminPermission::hasPermission($userId, 'file_management', 'view')) {
                return ['data' => []];
            }
            
            // ✅ ENFORCE CATEGORY PERMISSIONS - Only show files from allowed categories
            if (!$isAdmin) {
                $whereConditions[] = "fu.category_id IN (
                    SELECT category_id 
                    FROM file_permission_tbl 
                    WHERE position_id = ?
                )";
                $params[] = $user['position_id'];
            }
        }
        
        // Apply additional filters
        if (!empty($filters['category_id'])) {
            $whereConditions[] = "fu.category_id = ?";
            $params[] = $filters['category_id'];
        }        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(fu.datetime_uploaded) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(fu.datetime_uploaded) <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Add WHERE clause if there are conditions
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        $query .= " ORDER BY fu.datetime_uploaded DESC";
        
        $files = $db->select($query, $params);
        return ['data' => $files ?: []];
    }

    // Upload file with NLP
    public function uploadFile($data) {
        require_once __DIR__ . '/nlpcloud_service.php';
        $db = Database::getInstance();

        $file = $data['file'];
        $allowedTypes = $data['allowed_types'] ?? ['jpg','jpeg','png','pdf','doc','docx','xls','xlsx','ppt','pptx'];
        $baseUploadDir = '../uploads/files/';
        $uploadedBy = $data['uploaded_by'] ?? ($_SESSION['user_id'] ?? null);

        try {
            if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('File upload failed or no file selected');
            }

            if ($file['size'] > 50*1024*1024) throw new Exception('File size exceeds 50MB limit');

            $originalFilename = $file['name'];
            $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedTypes)) {
                throw new Exception('File type not allowed');
            }

            if (!is_dir($baseUploadDir)) mkdir($baseUploadDir, 0755, true);

            $systemFilename = uniqid() . '_' . time() . '.' . $extension;
            $tempPath = $baseUploadDir . $systemFilename;

            if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
                throw new Exception('Failed to move uploaded file');
            }

            // ⭐ CHECK IF NLP DATA IS ALREADY PROVIDED (from preview) - AVOID DUPLICATE API CALL!
            if (isset($data['nlp_analysis']) && !empty($data['nlp_analysis'])) {
                // Reuse cached NLP results from preview
                $nlpAnalysisData = is_string($data['nlp_analysis']) ? json_decode($data['nlp_analysis'], true) : $data['nlp_analysis'];
                $filecategory = $data['category_tag'] ?? 'Uncategorized';
                $score = $data['category_score'] ?? 0;
                
                $result = [
                    'success' => true,
                    'category_tag' => $filecategory,
                    'category_score' => $score,
                    'extracted_text' => $nlpAnalysisData['extracted_text'] ?? '',
                    'word_count' => $nlpAnalysisData['word_count'] ?? 0,
                    'keywords' => $nlpAnalysisData['keywords'] ?? [],
                    'entities' => $nlpAnalysisData['entities'] ?? [],
                    'sentiment' => $nlpAnalysisData['sentiment'] ?? null,
                    'full_analysis' => $nlpAnalysisData,
                    'provider' => $nlpAnalysisData['provider'] ?? 'nlpcloud',
                    'processing_time_ms' => 0,
                    'from_cache' => true
                ];
            } else {
                // No cached data - perform NLP analysis (uses API call)
                $nlp = new NLPCloudService();
                $result = $nlp->analyzeFile($tempPath, $file['type']);

                if (!$result['success']) {
                    throw new Exception('NLP analysis failed: ' . ($result['error'] ?? 'Unknown error'));
                }

                $filecategory = $result['category_tag'] ?? 'Uncategorized';
                $score = $result['category_score'] ?? 0;
            }

            // ⭐ NEW: Get or create category and organize files by category
            $categorySlug = $this->getCategorySlug($filecategory);
            $categoryId = $this->getOrCreateCategory($filecategory, $categorySlug);
            
            // Create category directory
            $categoryDir = $baseUploadDir . $categorySlug . '/';
            if (!is_dir($categoryDir)) {
                if (!mkdir($categoryDir, 0755, true)) {
                    throw new Exception('Failed to create category directory: ' . $categorySlug);
                }
            }
            
            // Move file to category folder
            $finalPath = $categoryDir . $systemFilename;
            if (!rename($tempPath, $finalPath)) {
                if (!copy($tempPath, $finalPath)) {
                    throw new Exception('Failed to move file to category directory');
                }
                unlink($tempPath);
            }
            
            $dbPath = 'uploads/files/' . $categorySlug . '/' . $systemFilename;

            $fileId = $db->insert("
				INSERT INTO file_upload_tbl 
				(category_id, category_tag, category_score, mime_type, original_filename, file_name, file_path, file_size, datetime_uploaded, uploaded_by) 
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			", [
				$categoryId,
				$filecategory,
				$score,
				$file['type'],
				$originalFilename,
				$systemFilename,
				$dbPath,
				$file['size'],
				date('Y-m-d H:i:s'),
				$uploadedBy
			]);

            // Save detailed NLP analysis to analysis table
            if (isset($result['extracted_text'])) {
                $db->insert("
                    INSERT INTO file_nlp_analysis_tbl 
                    (file_upload_id, extracted_text, word_count, suggested_category, category_confidence, 
                     keywords, entities, sentiment, full_analysis, provider, processing_time_ms) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [
                    $fileId,
                    $result['extracted_text'],
                    $result['word_count'] ?? 0,
                    $result['category_tag'] ?? 'Uncategorized',
                    $result['category_score'] ?? 0,
                    json_encode($result['keywords'] ?? []),
                    json_encode($result['entities'] ?? []),
                    json_encode($result['sentiment'] ?? null),
                    json_encode($result['full_analysis'] ?? $result),
                    $result['provider'] ?? 'nlpcloud',
                    $result['processing_time_ms'] ?? null
                ]);
            }

			return [
				'success' => true,
				'file_id' => $fileId,
				'filename' => $systemFilename,
				'original_name' => $originalFilename,
				'path' => $finalPath,
				'db_path' => $dbPath,
				'category_slug' => $categorySlug,
				'size' => $file['size'],
				'type' => $file['type'],
				'extension' => $extension,
				'upload_time' => date('Y-m-d H:i:s'),
				'category_tag' => $filecategory,
				'category_score' => $score,
				'category_id' => $categoryId,
				'nlp_result' => $result
			];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Get file categories (deprecated - now uses OpenAI NLP categorization)
    public function getFileCategories() {
        $db = Database::getInstance();
        // Return unique categories from existing files
        $rows = $db->select("
            SELECT DISTINCT category_tag as file_category
            FROM file_upload_tbl
            WHERE category_tag IS NOT NULL AND category_tag != ''
            ORDER BY category_tag
        ");

        $categories = [];
        foreach ($rows as $row) {
            $categories[] = [
                'file_category' => $row['file_category'],
                'keywords' => [] // No longer applicable with NLP
            ];
        }
        return array_values($categories);
    }

    /**
     * Convert category name to URL-safe slug for directory names
     */
    private function getCategorySlug($categoryName) {
        $slug = strtolower(trim($categoryName));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug ?: 'uncategorized';
    }

    /**
     * Get category ID from category_tbl, create if doesn't exist
     */
    private function getOrCreateCategory($categoryName, $categorySlug) {
        $db = Database::getInstance();
        
        // Try to find existing category
        $stmt = $db->getConnection()->prepare("SELECT category_id FROM category_tbl WHERE category_name = :name LIMIT 1");
        $stmt->execute([':name' => $categoryName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return $result['category_id'];
        }
        
        // Create new category
        $stmt = $db->getConnection()->prepare(
            "INSERT INTO category_tbl (category_name, category_slug, description) 
            VALUES (:name, :slug, :description)"
        );
        $stmt->execute([
            ':name' => $categoryName,
            ':slug' => $categorySlug,
            ':description' => 'Auto-created by NLP classification'
        ]);
        
        return $db->getConnection()->lastInsertId();
    }

    // Get all file permissions (deprecated - file category permissions no longer used)
    public function getFilePermissions() {
        $db = Database::getInstance();
        
        $permissions = $db->select("
            SELECT 
                cp.permission_id,
                cp.position_id,
                p.position as position_name,
                cp.category_id,
                c.category_name as file_category,
                c.category_slug,
                cp.created_at
            FROM category_permissions_tbl cp
            INNER JOIN position_tbl p ON cp.position_id = p.position_id
            INNER JOIN category_tbl c ON cp.category_id = c.category_id
            ORDER BY p.position ASC, c.category_name ASC
        ");
        
        return $permissions ?: [];
    }

    // Delete member file
    public function deleteMemberFile($memberId, $fileId) {
        $db = Database::getInstance();
        $file = $db->selectOne("SELECT * FROM file_upload_tbl WHERE file_upload_id = ? AND uploaded_by = ?", [$fileId, $memberId]);
        if (!$file) return ['success' => false, 'msg' => 'File not found or unauthorized'];

        // Check if file is linked to an approved task submission
        $approvedSubmission = $db->selectOne("
            SELECT ts.task_submission_id 
            FROM task_submission_tbl ts
            WHERE ts.file_upload_id = ? AND ts.check_status = 'Approved'
            LIMIT 1
        ", [$fileId]);
        
        if ($approvedSubmission) {
            return ['success' => false, 'msg' => 'Cannot delete file: This file is linked to an approved task submission'];
        }

        // Delete physical file if exists
        $filePath = '../' . $file['file_path'];
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                return ['success' => false, 'msg' => 'Failed to delete physical file'];
            }
        }

        // Delete database record
        $db->execute("DELETE FROM file_upload_tbl WHERE file_upload_id = ?", [$fileId]);
        return ['success' => true, 'msg' => 'File deleted successfully'];
    }

    // Admin delete file with protection check
    public function deleteFile($fileId, $userId, $reason = '') {
        $db = Database::getInstance();
        
        // Get file details with category
        $file = $db->selectOne("SELECT * FROM file_upload_tbl WHERE file_upload_id = ?", [$fileId]);
        if (!$file) {
            return ['status' => 'ERROR', 'msg' => 'File not found'];
        }

        // Check category permission if category_id exists and user is not admin
        if ($file['category_id'] && $userId) {
            $user = $db->selectOne("SELECT position_id FROM user_tbl WHERE user_id = ?", [$userId]);
            
            // If not admin (position_id != 3), check category permission
            if ($user && $user['position_id'] != 3) {
                if (!$this->userCanAccessCategory($userId, $file['category_id'])) {
                    return ['status' => 'ERROR', 'msg' => 'Access denied: You don\'t have permission to delete files in this category'];
                }
            }
        }

        // Check if file is linked to an approved task submission (only admin can delete these)
        $approvedSubmission = $db->selectOne("
            SELECT ts.task_submission_id 
            FROM task_submission_tbl ts
            WHERE ts.file_upload_id = ? AND ts.check_status = 'Approved'
            LIMIT 1
        ", [$fileId]);
        
        // Get user type from session
        session_start();
        $userType = $_SESSION['user_type'] ?? '';
        
        if ($approvedSubmission && $userType !== 'admin') {
            return ['status' => 'ERROR', 'msg' => 'Only administrators can delete files linked to approved task submissions'];
        }

        // Delete physical file if exists
        $filePath = $file['file_path'];
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                return ['status' => 'ERROR', 'msg' => 'Failed to delete physical file'];
            }
        }

        // Delete database record
        $db->execute("DELETE FROM file_upload_tbl WHERE file_upload_id = ?", [$fileId]);
        
        // Log deletion if reason provided
        if (!empty($reason)) {
            error_log("File deleted - ID: $fileId, Reason: $reason, User: $userId");
        }
        
        return ['status' => 'SUCCESS', 'msg' => 'File deleted successfully'];
    }

    // Subadmin/Adviser delete file with protection check
    public function deleteAdviserFile($adviserId, $data) {
        $db = Database::getInstance();
        $fileId = $data['file_id'] ?? 0;
        $reason = $data['reason'] ?? '';
        
        // Get file details
        $file = $db->selectOne("SELECT * FROM file_upload_tbl WHERE file_upload_id = ?", [$fileId]);
        if (!$file) {
            return ['status' => 'ERROR', 'msg' => 'File not found'];
        }

        // Check if file is linked to an approved task submission
        $approvedSubmission = $db->selectOne("
            SELECT ts.task_submission_id 
            FROM task_submission_tbl ts
            WHERE ts.file_upload_id = ? AND ts.check_status = 'Approved'
            LIMIT 1
        ", [$fileId]);
        
        if ($approvedSubmission) {
            return ['status' => 'ERROR', 'msg' => 'Cannot delete file: This file is linked to an approved task submission. Only administrators can delete approved task files.'];
        }

        // Delete physical file if exists
        $filePath = $file['file_path'];
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                return ['status' => 'ERROR', 'msg' => 'Failed to delete physical file'];
            }
        }

        // Delete database record
        $db->execute("DELETE FROM file_upload_tbl WHERE file_upload_id = ?", [$fileId]);
        
        // Log deletion
        if (!empty($reason)) {
            error_log("File deleted by adviser - ID: $fileId, Reason: $reason, Adviser: $adviserId");
        }
        
        return ['status' => 'SUCCESS', 'msg' => 'File deleted successfully'];
    }

    public function downloadMemberFile($memberId, $fileId) {
        $db = Database::getInstance();
        $file = $db->selectOne("SELECT * FROM file_upload_tbl WHERE file_upload_id = ? AND uploaded_by = ?", [$fileId, $memberId]);
        if (!$file) return ['success' => false, 'msg' => 'File not found or unauthorized'];
        return $file;
    }

    public function getMemberFileDetails($memberId, $fileId) {
        $db = Database::getInstance();
        $file = $db->selectOne("SELECT * FROM file_upload_tbl WHERE file_upload_id = ? AND uploaded_by = ?", [$fileId, $memberId]);
        return $file ? ['success' => true, 'data' => $file] : ['success' => false, 'msg' => 'File not found'];
    }

    public function getMemberFiles($memberId, $filters = []) {
        $db = Database::getInstance();
        $where = "uploaded_by = ?";
        $params = [$memberId];

        if (!empty($filters['category'])) {
            $where .= " AND category_tag = ?";
            $params[] = $filters['category'];
        }

        $files = $db->select("SELECT * FROM file_upload_tbl WHERE $where ORDER BY datetime_uploaded DESC", $params);
        return ['data' => $files];
    }

    /**
     * Search files by content (full-text search)
     * Searches both NLP extracted text AND actual file contents on disk
     * @param string $searchText - Text to search for in file contents
     * @param int|null $categoryId - Optional category filter
     * @param int|null $userId - User ID for permission filtering (null for admin)
     * @return array - Array of matching files with metadata
     */
    public function searchFilesByContent($searchText, $categoryId = null, $userId = null) {
        $db = Database::getInstance();
        
        // Check if user is admin
        $isAdmin = false;
        if ($userId) {
            $user = $db->selectOne("SELECT position_id FROM user_tbl WHERE user_id = ?", [$userId]);
            $isAdmin = ($user && $user['position_id'] == 3);
        }
        
        // Build query to get all files (we'll filter by content in PHP)
        $query = "
            SELECT DISTINCT
                fu.file_upload_id,
                fu.original_filename,
                fu.file_name,
                fu.file_path,
                fu.file_size,
                fu.mime_type,
                fu.datetime_uploaded,
                fu.uploaded_by,
                fu.category_id,
                c.category_name as file_category,
                c.category_slug,
                p.fname,
                p.lname,
                fna.extracted_text,
                fna.word_count,
                fna.category_confidence
            FROM file_upload_tbl fu
            LEFT JOIN category_tbl c ON fu.category_id = c.category_id
            LEFT JOIN user_tbl u ON fu.uploaded_by = u.user_id
            LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
            LEFT JOIN file_nlp_analysis_tbl fna ON fu.file_upload_id = fna.file_upload_id
            WHERE 1=1
        ";
        
        $params = [];
        
        // Add category filter
        if (!empty($categoryId)) {
            $query .= " AND fu.category_id = :categoryId";
            $params[':categoryId'] = $categoryId;
        }
        
        // Add permission filter for non-admin users
        if ($userId && !$isAdmin) {
            $userPosition = $db->selectOne("SELECT position_id FROM user_tbl WHERE user_id = ?", [$userId]);
            if ($userPosition) {
                $query .= " AND fu.category_id IN (
                    SELECT category_id 
                    FROM file_permission_tbl 
                    WHERE position_id = :positionId
                )";
                $params[':positionId'] = $userPosition['position_id'];
            }
        }
        
        $query .= " ORDER BY fu.datetime_uploaded DESC";
        
        $stmt = $db->getConnection()->prepare($query);
        $stmt->execute($params);
        $allFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no search text, return all files
        if (empty($searchText)) {
            return $allFiles;
        }
        
        // Filter files by content - CHECK ALL FILES before returning results
        $matchingFiles = [];
        $searchLower = strtolower($searchText);
        
        require_once(__DIR__ . '/text_extractor.php');
        $textExtractor = new TextExtractor();
        
        // Process EVERY file in the database
        foreach ($allFiles as $file) {
            $matchFound = false;
            $fileContent = null;
            
            // Always read actual file content from disk first for most accurate search
            if (!empty($file['file_path'])) {
                $fullPath = __DIR__ . '/../../' . $file['file_path'];
                
                if (file_exists($fullPath)) {
                    try {
                        // Extract text from the actual file on disk
                        $extractionResult = $textExtractor->extractText($fullPath, $file['mime_type']);
                        
                        // TextExtractor returns an array with 'text' key
                        if (is_array($extractionResult) && !empty($extractionResult['text'])) {
                            $fileContent = $extractionResult['text'];
                        } elseif (is_string($extractionResult)) {
                            $fileContent = $extractionResult;
                        }
                        
                        // Search in actual file content (most reliable)
                        if (!empty($fileContent) && stripos($fileContent, $searchText) !== false) {
                            $matchFound = true;
                            // Update extracted_text in result for display purposes
                            $file['extracted_text'] = $fileContent;
                        }
                    } catch (Exception $e) {
                        // If extraction fails, log and continue checking other sources
                        error_log("Failed to extract text from {$file['file_path']}: " . $e->getMessage());
                    }
                }
            }
            
            // If no match in file content, check other sources
            if (!$matchFound) {
                // Check filename
                if (stripos($file['original_filename'], $searchText) !== false) {
                    $matchFound = true;
                }
                
                // Check category name
                if (!$matchFound && stripos($file['file_category'], $searchText) !== false) {
                    $matchFound = true;
                }
                
                // Check NLP extracted text from database (fallback if file reading failed)
                if (!$matchFound && !empty($file['extracted_text'])) {
                    if (stripos($file['extracted_text'], $searchText) !== false) {
                        $matchFound = true;
                    }
                }
            }
            
            // Add to results ONLY if match found after checking ALL sources
            if ($matchFound) {
                $matchingFiles[] = $file;
            }
        }
        
        // Return results only after ALL files have been checked
        return $matchingFiles;
    }

    /**
     * Get NLP analysis data for a specific file
     * @param int $fileId - File upload ID
     * @param int|null $userId - User ID for permission check
     * @return array - NLP analysis data or error
     */
    public function getFileNLPAnalysis($fileId, $userId = null) {
        $db = Database::getInstance();
        
        // Get file info first
        $file = $db->selectOne("
            SELECT fu.*, c.category_name, c.category_slug
            FROM file_upload_tbl fu
            LEFT JOIN category_tbl c ON fu.category_id = c.category_id
            WHERE fu.file_upload_id = ?
        ", [$fileId]);
        
        if (!$file) {
            return ['status' => 'ERROR', 'msg' => 'File not found'];
        }
        
        // Check permissions if userId provided
        if ($userId) {
            $user = $db->selectOne("SELECT position_id FROM user_tbl WHERE user_id = ?", [$userId]);
            $isAdmin = ($user && $user['position_id'] == 3);
            
            // Non-admin users must have category permission
            if (!$isAdmin && $file['category_id']) {
                if (!$this->userCanAccessCategory($userId, $file['category_id'])) {
                    return ['status' => 'ERROR', 'msg' => 'Access denied: You do not have permission to view this file'];
                }
            }
        }
        
        // Get NLP analysis data
        $nlpData = $db->selectOne("
            SELECT * FROM file_nlp_analysis_tbl 
            WHERE file_upload_id = ?
        ", [$fileId]);
        
        if (!$nlpData) {
            return [
                'status' => 'WARNING',
                'msg' => 'No NLP analysis available for this file',
                'file' => $file,
                'analysis' => null
            ];
        }
        
        // Parse JSON fields
        $nlpData['keywords'] = json_decode($nlpData['keywords'] ?? '[]', true);
        $nlpData['entities'] = json_decode($nlpData['entities'] ?? '[]', true);
        $nlpData['sentiment'] = json_decode($nlpData['sentiment'] ?? '{}', true);
        $nlpData['full_analysis'] = json_decode($nlpData['full_analysis'] ?? '{}', true);
        
        return [
            'status' => 'SUCCESS',
            'file' => $file,
            'analysis' => $nlpData
        ];
    }
}

class DashboardManager {
    public function getMemberDashboardStats($memberId) {
        $db = Database::getInstance();
        $stats = [];
        $stats['totalFiles'] = $db->selectOne("SELECT COUNT(*) as count FROM file_upload_tbl WHERE uploaded_by = ?", [$memberId])['count'] ?? 0;
        $stats['activeTasks'] = $db->selectOne("SELECT COUNT(*) as count FROM task_tbl WHERE assigned_to = ? AND task_deadline >= CURDATE()", [$memberId])['count'] ?? 0;
        //$stats['activeTasks'] = $db->select("SELECT COUNT(*) as count FROM task_tbl WHERE task_deadline >= CURDATE()")['count'] ?? 0;
		$stats['completedTasks'] = $db->selectOne("SELECT COUNT(*) as count FROM task_submission_tbl WHERE submitted_by = ? AND check_status = 'approved'", [$memberId])['count'] ?? 0;
        $stats['categoryCount'] = $db->selectOne("SELECT COUNT(DISTINCT category_tag) as count FROM file_upload_tbl WHERE uploaded_by = ? AND category_tag IS NOT NULL", [$memberId])['count'] ?? 0;
        return ['success' => true, 'data' => $stats];
    }
    public function getMemberRecentActivity($memberId) {
        $db = Database::getInstance();
        $activities = $db->select("SELECT datetime_uploaded as date, file_name as action, category_tag as category, 'File Upload' as type FROM file_upload_tbl WHERE uploaded_by = ? ORDER BY datetime_uploaded DESC LIMIT 10", [$memberId]);
        return ['success' => true, 'data' => $activities];
    }
}

?>