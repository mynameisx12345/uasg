<?php
	require_once("db_config.php");

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

		public function uploadFile($file, $uploadDir = 'uploads/', $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']) {
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

        		// Return file information
        		return [
            		'success' => true,
            		'filename' => $filename,
            		'original_name' => $file['name'],
            		'path' => $fullPath,
            		'size' => $file['size'],
            		'type' => $file['type'],
            		'extension' => $extension,
            		'upload_time' => date('Y-m-d H:i:s')
        		];

    		} catch (Exception $e) {
        		return [
            		'success' => false,
            		'error' => $e->getMessage()
        		];
    		}
		}

		public function uploadToGoogleDrive($file, $folderId = null) {
    		// Placeholder function for Google Drive integration
    		// This will be implemented when you set up the Google Drive API
    		
    		try {
        		// For now, we'll just simulate a Google Drive upload
        		// and return a fake drive_id for testing purposes
        		
        		$driveId = 'gd_' . uniqid() . '_' . time();
        		
        		return [
            		'success' => true,
            		'drive_id' => $driveId,
            		'file_name' => $file['name'] ?? 'unknown',
            		'mime_type' => $file['type'] ?? 'application/octet-stream',
            		'message' => 'File uploaded to Google Drive (simulated)'
        		];
        		
        		// TODO: Implement actual Google Drive API integration
        		/*
        		$client = new Google_Client();
        		$client->setClientId('your-client-id');
        		$client->setClientSecret('your-client-secret');
        		$client->setRedirectUri('your-redirect-uri');
        		$client->addScope(Google_Service_Drive::DRIVE_FILE);
        		
        		$service = new Google_Service_Drive($client);
        		
        		$fileMetadata = new Google_Service_Drive_DriveFile([
            		'name' => $file['name'],
            		'parents' => $folderId ? [$folderId] : null
        		]);
        		
        		$content = file_get_contents($file['tmp_name']);
        		$uploadedFile = $service->files->create($fileMetadata, [
            		'data' => $content,
            		'mimeType' => $file['type'],
            		'uploadType' => 'multipart'
        		]);
        		
        		return [
            		'success' => true,
            		'drive_id' => $uploadedFile->getId(),
            		'file_name' => $uploadedFile->getName(),
            		'mime_type' => $uploadedFile->getMimeType()
        		];
        		*/
        		
    		} catch (Exception $e) {
        		return [
            		'success' => false,
            		'error' => $e->getMessage()
        		];
    		}
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

				if($user->insert()) {
					$connection->commit();
					return ['success' => true, 'message' => 'User created successfully'];
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

		public function deleteUser($data) {
			if(empty($data['user_id']) || empty(trim($data['reason']))) {
				throw new Exception("Missing required fields");
			}

			$connection = $this->db->getConnection();
			$connection->beginTransaction();

			try {
				// Get user data before deletion for logging
				$query = "SELECT u.*, p.* FROM user_tbl u 
						  JOIN profile_tbl p ON u.profile_id = p.profile_id 
						  WHERE u.user_id = :user_id";
				$stmt = $connection->prepare($query);
				$stmt->execute([':user_id' => $data['user_id']]);
				$userData = $stmt->fetch(PDO::FETCH_ASSOC);

				if(!$userData) {
					throw new Exception("User not found");
				}

				// Log deletion
				$deleteLog = new Delete([
					'data' => json_encode($userData),
					'reason' => trim($data['reason']),
					'table' => 'user_tbl',
					'datetime' => date('Y-m-d H:i:s')
				]);

				if(!$deleteLog->insert()) {
					throw new Exception("Failed to log deletion");
				}

				// Delete user (this will cascade delete profile due to foreign key constraints)
				$user = new User();
				if($user->delete('user_id', $data['user_id'])) {
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
	}

	class EntityManager {
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
	}

	
?>