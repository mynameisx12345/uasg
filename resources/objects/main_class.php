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
            	$this->fields[$key] = $value;
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
       	 	$db = Database::getInstance();
        	return $db->select("SELECT * FROM $table");
   	 	}

   	 	public function getAllWithidden($hidden = []) {
    		$db = Database::getInstance();
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
   	 		$db = Database::getInstance();
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
    		$db = Database::getInstance();
    
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
    		require_once __DIR__ . '/google_nlp_service.php';
			$db = Database::getInstance()->getConnection();
			$file = $data['file'];
			$allowedTypes = $data['allowed_types'] ?? ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
			$uploadDir = $data['upload_dir'] ?? 'uploads/';
			$uploadedBy = $data['uploaded_by'] ?? ($_SESSION['user_id'] ?? null);

			try {
				// Validate file upload
				if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
					throw new Exception('File upload failed or no file selected');
				}

				// Check file size (10MB limit)
				$maxSize = 10 * 1024 * 1024; // 10MB in bytes
				if ($file['size'] > $maxSize) {
					throw new Exception('File size exceeds 10MB limit');
				}

				// Get file extension
				$fileInfo = pathinfo($file['name']);
				$extension = strtolower($fileInfo['extension'] ?? '');

				// Validate file type
				if (!in_array($extension, $allowedTypes)) {
					throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $allowedTypes));
				}

				// Create upload directory if it doesn't exist
				$uploadPath = rtrim($uploadDir, '/') . '/';
				if (!is_dir($uploadPath)) {
					if (!mkdir($uploadPath, 0755, true)) {
						throw new Exception('Failed to create upload directory');
					}
				}

				// Generate unique filename
				$filename = uniqid() . '_' . time() . '.' . $extension;
				$fullPath = $uploadPath . $filename;

				// Move uploaded file
				if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
					throw new Exception('Failed to move uploaded file');
				}

				// Get categories from DB
				$categories = $this->getFileCategories();

				// Load Google NLP config
				$configFile = __DIR__ . '/../../config/google_nlp_config.php';
				$apiKey = '';
				if (file_exists($configFile)) {
					$config = include($configFile);
					$apiKey = $config['api_key'] ?? '';
				}
				$nlp = new GoogleNLPService($apiKey);

				// Run NLP on the uploaded file
				$result = $nlp->analyzeFileAndSuggestCategory($fullPath, $file['type'], $categories);
				$filecategory = $result['suggested_category_name'] ?? 'Uncategorized';
				$categoryid = $result["suggested_category_id"] ?? null;
				$score = $result['confidence'] ?? 0;

				$stmt = $db->prepare(
					"INSERT INTO file_upload_tbl (file_category_id, category_tag, category_score, mime_type, file_name, file_path, file_size, datetime_uploaded, uploaded_by) 
					VALUES (:categoryid, :category_tag, :category_score, :mime_type, :file_name, :file_path, :file_size, :datetime_uploaded, :uploaded_by)"
				);
				$stmt->execute([
					':categoryid' => $categoryid,
					':category_tag' => $filecategory,
					':category_score' => $score,
					':mime_type' => $file['type'],
					':file_name' => $filename,
					':file_path' => $fullPath,
					':file_size' => $file['size'],
					':datetime_uploaded' => date('Y-m-d H:i:s'),
					':uploaded_by' => $uploadedBy
				]);

				return [
					'success' => true,
					'filename' => $filename,
					'original_name' => $file['name'],
					'path' => $fullPath,
					'size' => $file['size'],
					'type' => $file['type'],
					'extension' => $extension,
					'upload_time' => date('Y-m-d H:i:s'),
					'nlp_result' => $result
				];

			} catch (Exception $e) {
				return [
					'success' => false,
					'error' => $e->getMessage()
				];
			}
		}

		public function getFileCategories() {
			$db = Database::getInstance();
			$query = "
				SELECT fc.file_category_id, fc.file_category, fck.keyword
				FROM file_category_tbl fc
				LEFT JOIN file_category_key_tbl fck ON fc.file_category_id = fck.file_category_id
				ORDER BY fc.file_category_id, fck.keyword
			";
			$rows = $db->select($query);

			$categories = [];
			foreach ($rows as $row) {
				$id = $row['file_category_id'];
				if (!isset($categories[$id])) {
					$categories[$id] = [
						'file_category_id' => $id,
						'file_category' => $row['file_category'],
						'keywords' => []
					];
				}
				if (!empty($row['keyword'])) {
					$categories[$id]['keywords'][] = $row['keyword'];
				}
			}
			// Re-index to get a simple array
			return array_values($categories);
		}

		/**
     * Get all file permissions: which positions can access which file categories
     * Returns array of {file_permission_id, position_id, position, file_category_id, file_category}
     */
    public function getFilePermissions() {
        $db = Database::getInstance();
        $query = "
            SELECT fp.file_permission_id, fp.position_id, p.position, fp.file_category_id, fc.file_category
            FROM file_permission_tbl fp
            JOIN position_tbl p ON fp.position_id = p.position_id
            JOIN file_category_tbl fc ON fp.file_category_id = fc.file_category_id
            ORDER BY fp.position_id, fp.file_category_id
        ";
        return $db->select($query);
    }

	}


	class FileCategory extends Main{
		public function __construct($params = []){
			parent::__construct('file_category_tbl',[
				'file_category_id' => $params["id"] ?? null,
				'file_category' => $params["category"] ?? null
			]);
		}
	}

	class FileCategoryKey extends Main{
		public function __construct($params = []){
			parent::__construct('file_category_key_tbl',[
				'file_category_key_id' => $params["id"] ?? null,
				'file_category_id' => $params["file_category_id"] ?? null,
				'keyword' => $params["keyword"] ?? null
			]);
		}
	}

	class FilePermission extends Main{
		public function __construct($params = []){
			parent::__construct('file_permission_tbl',[
				'file_permission_id' => $params["id"] ?? null,
				'position_id' => $params["position_id"] ?? null,
				'file_category_id' => $params["file_category_id"] ?? null,
			]);
		}
	}

	class Position extends Main{
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
				'file_category_id' => $params["file_category_id"] ?? null,
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
				'task_deadline' => $params["task_deadline"] ?? null
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
				'reason_for_deleteion' => $params["reason"] ?? null,
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
				$position_id = 1; // Default to adviser
				if($data['user_type'] === 'student') {
					$position_id = 2; // Student Government Member
				}

				// Create profile first
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

	class EntityManager extends Main{
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

		public static function createFileCategory($name) {
			try {
				$name = trim($name);
				if(empty($name)) {
					throw new Exception("File category name is required");
				}

				$fc = new FileCategory(['category' => $name]);
				$fc->findOrCreate('file_category_id', ['file_category']);
				return ['success' => true, 'message' => 'Successfully added new file category'];
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

		public static function getAllPositions() {
			$pos = new Position();
			return $pos->getAllRecords();
		}

		public static function getAllFileCategories() {
			$fc = new FileCategory();
			return $fc->getAllRecords();
		}

		public static function getAllTaskCategories() {
			$tc = new TaskCategory();
			return $tc->getAllRecords();
		}

		public static function createFileCategoryKeyword($categoryId, $keyword) {
			try {
				$keyword = trim($keyword);
				if(empty($keyword)) {
					throw new Exception("Keyword is required");
				}

				if(empty($categoryId)) {
					throw new Exception("File category ID is required");
				}

				// Check if keyword already exists for this category
				$fck = new FileCategoryKey([
					'file_category_id' => $categoryId,
					'keyword' => $keyword
				]);
				
				if($fck->checkFromTable()) {
					throw new Exception("Keyword already exists for this category");
				}

				$fck->insert();
				return ['success' => true, 'message' => 'Successfully added keyword'];
			} catch(Exception $e) {
				throw new Exception("Failed! An error was detected: " . $e->getMessage());
			}
		}

		public static function getFileCategoryKeywords($categoryId = null) {
			try {
				$db = Database::getInstance();
				if($categoryId) {
					$query = "SELECT fck.*, fc.file_category 
							  FROM file_category_key_tbl fck
							  JOIN file_category_tbl fc ON fck.file_category_id = fc.file_category_id
							  WHERE fck.file_category_id = :category_id
							  ORDER BY fck.keyword";
					return $db->select($query, [':category_id' => $categoryId]);
				} else {
					$query = "SELECT fck.*, fc.file_category 
							  FROM file_category_key_tbl fck
							  JOIN file_category_tbl fc ON fck.file_category_id = fc.file_category_id
							  ORDER BY fc.file_category, fck.keyword";
					return $db->select($query);
				}
			} catch(Exception $e) {
				throw new Exception("Failed to retrieve keywords: " . $e->getMessage());
			}
		}

		public static function updateFileCategoryKeyword($keywordId, $keyword) {
			try {
				$keyword = trim($keyword);
				if(empty($keyword)) {
					throw new Exception("Keyword is required");
				}

				$fck = new FileCategoryKey();
				$result = $fck->updateSingleValue('keyword', 'file_category_key_id', $keywordId, $keyword);
				
				if(!$result) {
					throw new Exception("Failed to update keyword");
				}

				return ['success' => true, 'message' => 'Successfully updated keyword'];
			} catch(Exception $e) {
				throw new Exception("Failed! An error was detected: " . $e->getMessage());
			}
		}

		public static function deleteFileCategoryKeyword($keywordId, $reason = "Admin deletion") {
			try {
				// Get keyword data before deletion for logging
				$db = Database::getInstance();
				$keywordData = $db->select(
					"SELECT fck.*, fc.file_category 
					 FROM file_category_key_tbl fck
					 JOIN file_category_tbl fc ON fck.file_category_id = fc.file_category_id
					 WHERE fck.file_category_key_id = :id", 
					[':id' => $keywordId]
				);

				if(empty($keywordData)) {
					throw new Exception("Keyword not found");
				}

				// Log deletion
				$deleteLog = new Delete([
					'data' => json_encode($keywordData[0]),
					'reason' => $reason,
					'table' => 'file_category_key_tbl',
					'datetime' => date('Y-m-d H:i:s')
				]);
				$deleteLog->insert();

				// Delete keyword
				$fck = new FileCategoryKey();
				$result = $fck->delete('file_category_key_id', $keywordId);
				
				if(!$result) {
					throw new Exception("Failed to delete keyword");
				}

				return ['success' => true, 'message' => 'Successfully deleted keyword'];
			} catch(Exception $e) {
				throw new Exception("Failed! An error was detected: " . $e->getMessage());
			}
		}
	}

	class TaskManager {
		private $db;

		public function __construct() {
			$this->db = Database::getInstance();
		}

		public function createTask($data) {
			try {
				// Validate required fields
				if (empty($data['task_title']) || empty($data['task_description']) || 
					empty($data['task_deadline']) || empty($data['task_category_id'])) {
					throw new Exception("All task fields are required");
				}

				// Create task
				$task = new Task([
					'task_category_id' => $data['task_category_id'],
					'task_title' => $data['task_title'],
					'task_description' => $data['task_description'],
					'task_deadline' => $data['task_deadline']
				]);

				$taskId = $task->insertAndGetId();
				if (!$taskId) {
					throw new Exception("Failed to create task");
				}

				// Create notifications for all UASG members
				$this->notifyMembersNewTask($taskId, $data['task_title']);

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
						 COUNT(CASE WHEN ts.check_status = 'pending' THEN 1 END) as pending_count
						 FROM task_tbl t 
						 LEFT JOIN task_category_tbl tc ON t.task_category_id = tc.task_category_id
						 LEFT JOIN task_submission_tbl ts ON t.task_id = ts.task_id
						 GROUP BY t.task_id
						 ORDER BY t.task_deadline ASC";
				
				return $this->db->select($query);
			} catch (Exception $e) {
				return [];
			}
		}

		public function getTasksForMember($userId) {
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
						LEFT JOIN file_category_tbl fc ON tc.task_category = fc.file_category
						LEFT JOIN file_permission_tbl fp ON fc.file_category_id = fp.file_category_id 
							AND fp.position_id = :position_id
						LEFT JOIN task_submission_tbl ts ON t.task_id = ts.task_id
						LEFT JOIN file_upload_tbl fu ON ts.file_upload_id = fu.file_upload_id 
							AND fu.uploaded_by = :user_id
						LEFT JOIN user_tbl u ON fu.uploaded_by = u.user_id
						LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
						WHERE fp.file_permission_id IS NOT NULL
						ORDER BY t.task_deadline ASC";

				return $this->db->select($query, [':position_id' => $positionId, ':user_id' => $userId]);
			} catch (Exception $e) {
				return [];
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

		private function notifyMembersNewTask($taskId, $taskTitle) {
			// Get all UASG members
			$members = $this->db->select(
				"SELECT p.profile_id AS user_id, CONCAT(p.fname, ' ', p.lname) AS full_name
					FROM profile_tbl p
					JOIN position_tbl pos ON p.position_id = pos.position_id
					WHERE pos.position = 'Student Government Member'"
			);

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
			$this->db = Database::getInstance();
		}

		public function createNotification($data) {
			try {
				// Insert notification (we need a notifications table, let's create it in the notification)
				$query = "INSERT INTO notifications_tbl (user_id, type, title, message, related_id, is_read, datetime_created) 
						  VALUES (:user_id, :type, :title, :message, :related_id, 0, NOW())";
				
				$conn = $this->db->getConnection();
				$stmt = $conn->prepare($query);
				
				return $stmt->execute([
					':user_id' => $data['user_id'],
					':type' => $data['type'],
					':title' => $data['title'],
					':message' => $data['message'],
					':related_id' => $data['related_id']
				]);
			} catch (Exception $e) {
				// If notifications table doesn't exist, we'll create it later
				return true;
			}
		}

		public function getNotifications($userId, $unreadOnly = false) {
			try {
				$query = "SELECT * FROM notifications_tbl WHERE user_id = :user_id";
				if ($unreadOnly) {
					$query .= " AND is_read = 0";
				}
				$query .= " ORDER BY datetime_created DESC";
				
				return $this->db->select($query, [':user_id' => $userId]);
			} catch (Exception $e) {
				return [];
			}
		}

		public function markAsRead($notificationId) {
			try {
				$query = "UPDATE notifications_tbl SET is_read = 1 WHERE notification_id = :id";
				$conn = $this->db->getConnection();
				$stmt = $conn->prepare($query);
				return $stmt->execute([':id' => $notificationId]);
			} catch (Exception $e) {
				return false;
			}
		}

		public function getUnreadCount($userId) {
			try {
				$query = "SELECT COUNT(*) as count FROM notifications_tbl WHERE user_id = :user_id AND is_read = 0";
				$result = $this->db->select($query, [':user_id' => $userId]);
				return !empty($result) ? $result[0]['count'] : 0;
			} catch (Exception $e) {
				return 0;
			}
		}
	}

	class FileManager {

		//set file permissions
		public function setFilePermission($positionId, $categoryId) {
			$db = Database::getInstance()->getConnection();

			// Check if permission already exists
			$check = $db->prepare("
				SELECT COUNT(*) 
				FROM file_permission_tbl 
				WHERE position_id = :pid AND file_category_id = :cid
			");
			$check->execute([
				':pid' => $positionId,
				':cid' => $categoryId
			]);

			if ($check->fetchColumn() > 0) {
				throw new Exception("Permission already exists.");
			}

			// Insert new permission
			$stmt = $db->prepare("
				INSERT INTO file_permission_tbl (position_id, file_category_id)
				VALUES (:pid, :cid)
			");

			$stmt->execute([
				':pid' => $positionId,
				':cid' => $categoryId
			]);

			return [
				"status" => "SUCCESS",
				"msg" => "Permission granted successfully"
			];
		}

		//download files
		public function downloadFile($fileId) {
			$db = Database::getInstance()->getConnection();

			// Fetch file record
			$stmt = $db->prepare("
				SELECT file_name, file_path, mime_type 
				FROM file_upload_tbl 
				WHERE file_upload_id = :id
				LIMIT 1
			");
			$stmt->execute([':id' => $fileId]);
			$file = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$file) {
				throw new Exception("File not found.");
			}

			$fullPath = $file['file_path'];

			if (!file_exists($fullPath)) {
				throw new Exception("File does not exist on server.");
			}

			// Clean output buffer if needed
			if (ob_get_length()) {
				ob_end_clean();
			}

			// Headers for downloading
			header("Content-Description: File Transfer");
			header("Content-Type: " . ($file['mime_type'] ?: "application/octet-stream"));
			header("Content-Disposition: attachment; filename=\"" . basename($file['file_name']) . "\"");
			header("Expires: 0");
			header("Cache-Control: must-revalidate");
			header("Pragma: public");
			header("Content-Length: " . filesize($fullPath));

			// Output file content
			readfile($fullPath);
			exit;
		}
		//get filtered files by category
		public function getFilteredFiles($categoryId = null, $userId = null)
		{
			$db = Database::getInstance()->getConnection();

			// Step 1 — Get user's position
			$stmt = $db->prepare("SELECT u.position_id FROM user_tbl u WHERE u.user_id = :uid");
			$stmt->execute([':uid' => $userId]);
			$user = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$user) {
				throw new Exception("User not found.");
			}

			$positionId = $user['position_id'];
			$isAdmin = ($positionId == 3);

			// Step 2 — Build query based on admin or regular user
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
					fc.file_category
				FROM file_upload_tbl fu
				LEFT JOIN user_tbl u ON fu.uploaded_by = u.user_id
				INNER JOIN profile_tbl prof ON u.profile_id = prof.profile_id
				LEFT JOIN file_category_tbl fc ON fu.file_category_id = fc.file_category_id
			";

			if ($isAdmin) {
				// Admin: filter only by category if specified
				if ($categoryId !== null) {
					$query .= " WHERE fu.file_category_id = :cid";
					$params[':cid'] = $categoryId;
				}
			} else {
				// Non-admin: check permissions
				$permStmt = $db->prepare("
					SELECT file_category_id 
					FROM file_permission_tbl 
					WHERE position_id = :pid
				");
				$permStmt->execute([':pid' => $positionId]);
				$permittedCategories = $permStmt->fetchAll(PDO::FETCH_COLUMN);

				if (empty($permittedCategories)) {
					// User has no permissions
					return [];
				}

				// Build WHERE clause
				$query .= " WHERE fu.file_category_id IN (" . implode(',', array_map('intval', $permittedCategories)) . ")";
				
				// If categoryId is provided, further filter
				if ($categoryId !== null) {
					if (!in_array($categoryId, $permittedCategories)) {
						// User does not have permission for this category
						return [];
					}
					$query .= " AND fu.file_category_id = :cid";
					$params[':cid'] = $categoryId;
				}
			}

			$query .= " ORDER BY fu.datetime_uploaded DESC";

			$stmt = $db->prepare($query);
			$stmt->execute($params);

			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		//get all files
		public function getAllFiles($userId = null) {
			$db = Database::getInstance()->getConnection();

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
					fc.file_category
				FROM file_upload_tbl fu
				LEFT JOIN user_tbl u ON fu.uploaded_by = u.user_id INNER JOIN profile_tbl prof ON u.profile_id = prof.profile_id
				LEFT JOIN file_category_tbl fc ON fu.file_category_id = fc.file_category_id
			";

			// If user-specific listing is needed
			$params = [];
			if (!empty($userId)) {
				$query .= " WHERE fu.uploaded_by = :uid ";
				$params[':uid'] = $userId;
			}

			$query .= " ORDER BY fu.datetime_uploaded DESC";

			$stmt = $db->prepare($query);
			$stmt->execute($params);

			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		public function uploadFile($data) {
    		require_once __DIR__ . '/google_nlp_service.php';
			$db = Database::getInstance()->getConnection();
			$file = $data['file'];
			$allowedTypes = $data['allowed_types'] ?? ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
			$uploadDir = $data['upload_dir'] ?? 'uploads/';
			$uploadedBy = $data['uploaded_by'] ?? ($_SESSION['user_id'] ?? null);

			try {
				// Validate file upload
				if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
					throw new Exception('File upload failed or no file selected');
				}

				// Check file size (10MB limit)
				$maxSize = 10 * 1024 * 1024; // 10MB in bytes
				if ($file['size'] > $maxSize) {
					throw new Exception('File size exceeds 10MB limit');
				}

				// Get file extension
				$fileInfo = pathinfo($file['name']);
				$extension = strtolower($fileInfo['extension'] ?? '');

				// Validate file type
				if (!in_array($extension, $allowedTypes)) {
					throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $allowedTypes));
				}

				// Create upload directory if it doesn't exist
				$uploadPath = rtrim($uploadDir, '/') . '/';
				if (!is_dir($uploadPath)) {
					if (!mkdir($uploadPath, 0755, true)) {
						throw new Exception('Failed to create upload directory');
					}
				}

				// Generate unique filename
				$filename = uniqid() . '_' . time() . '.' . $extension;
				$fullPath = $uploadPath . $filename;

				// Move uploaded file
				if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
					throw new Exception('Failed to move uploaded file');
				}

				// Get categories from DB
				$categories = $this->getFileCategories();

				// Load Google NLP config
				$configFile = __DIR__ . '/../../config/google_nlp_config.php';
				$apiKey = '';
				if (file_exists($configFile)) {
					$config = include($configFile);
					$apiKey = $config['api_key'] ?? '';
				}
				$nlp = new GoogleNLPService($apiKey);

				// Run NLP on the uploaded file
				$result = $nlp->analyzeFileAndSuggestCategory($fullPath, $file['type'], $categories);
				$filecategory = $result['suggested_category_name'] ?? 'Uncategorized';
				$categoryid = $result['suggested_category_id'] ?? null;
				$score = $result['confidence'] ?? 0;

				$stmt = $db->prepare(
					"INSERT INTO file_upload_tbl (file_category_id, category_tag, category_score, mime_type, file_name, file_path, file_size, datetime_uploaded, uploaded_by) 
					VALUES (:categoryid, :category_tag, :category_score, :mime_type, :file_name, :file_path, :file_size, :datetime_uploaded, :uploaded_by)"
				);
				$stmt->execute([
					':categoryid' => $categoryid,
					':category_tag' => $filecategory,
					':category_score' => $score,
					':mime_type' => $file['type'],
					':file_name' => $filename,
					':file_path' => $fullPath,
					':file_size' => $file['size'],
					':datetime_uploaded' => date('Y-m-d H:i:s'),
					':uploaded_by' => $uploadedBy
				]);

				return [
					'success' => true,
					'filename' => $filename,
					'original_name' => $file['name'],
					'path' => $fullPath,
					'size' => $file['size'],
					'type' => $file['type'],
					'extension' => $extension,
					'upload_time' => date('Y-m-d H:i:s'),
					'nlp_result' => $result
				];

			} catch (Exception $e) {
				return [
					'success' => false,
					'error' => $e->getMessage()
				];
			}
		}

		public function getFileCategories() {
			$db = Database::getInstance();
			$query = "
				SELECT fc.file_category_id, fc.file_category, fck.keyword
				FROM file_category_tbl fc
				LEFT JOIN file_category_key_tbl fck ON fc.file_category_id = fck.file_category_id
				ORDER BY fc.file_category_id, fck.keyword
			";
			$rows = $db->select($query);

			$categories = [];
			foreach ($rows as $row) {
				$id = $row['file_category_id'];
				if (!isset($categories[$id])) {
					$categories[$id] = [
						'file_category_id' => $id,
						'file_category' => $row['file_category'],
						'keywords' => []
					];
				}
				if (!empty($row['keyword'])) {
					$categories[$id]['keywords'][] = $row['keyword'];
				}
			}
			// Re-index to get a simple array
			return array_values($categories);
		}

				/**
			* Get all file permissions: which positions can access which file categories
			* Returns array of {file_permission_id, position_id, position, file_category_id, file_category}
			*/
		public function getFilePermissions() {
			$db = Database::getInstance();
			$query = "
				SELECT fp.file_permission_id, fp.position_id, p.position, fp.file_category_id, fc.file_category
				FROM file_permission_tbl fp
				JOIN position_tbl p ON fp.position_id = p.position_id
				JOIN file_category_tbl fc ON fp.file_category_id = fc.file_category_id
				ORDER BY fp.position_id, fp.file_category_id
			";
			return $db->select($query);
		}

	}

?>