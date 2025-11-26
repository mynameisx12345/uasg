<?php
	// Prevent any output before JSON
	error_reporting(E_ALL);
	ini_set('display_errors', 1); // Don't display errors in output
	ini_set('log_errors', 1); // Log errors instead
	
	// Prevent caching of AJAX responses
	header("Cache-Control: no-cache, no-store, must-revalidate");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	ob_start();
	
	session_start();
	require_once("../resources/objects/db_config.php");
	require_once("../resources/objects/main_class.php");

	if (!isset($_SESSION['user_id'])) {
		http_response_code(401); // Unauthorized
		echo json_encode(['error' => 'Authentication required']);
		exit;
	}
	
	// Check if this is a download request (GET allowed for downloads)
	if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['CALL']) && $_GET['CALL'] === 'download') {
		// Handle file download
		ob_end_clean(); // Clear and disable buffer
		
		$fileId = $_GET['file_id'] ?? 0;
		
		try {
			$fileManager = new FileManager();
			$fileManager->downloadFile($fileId);
			exit; // downloadFile handles headers and output
		} catch(Exception $e) {
			header("Content-Type: application/json");
			echo json_encode(["status" => "ERROR", "msg" => "Download failed: " . $e->getMessage()]);
			exit;
		}
	}
	
	// Clear any previous output and set headers for normal AJAX
	ob_end_clean();
	ob_start(); // Start fresh buffer
	header("Content-Type: application/json"); // always return JSON

	if(!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
    	http_response_code(403);
    	echo json_encode(["status"=>"error","message"=>"Forbidden"]);
    	exit;
	}

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    	echo json_encode(["error" => "Invalid request method."]);
    	exit;
	}

	function is_ajax_request() {
    	return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	}

	// Usage
	if (!is_ajax_request()) {
    	echo json_encode(["error" => "Unauthorized request."]);
    	exit;
	}

	// Check if user is logged in and is admin
	if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
		echo json_encode(["error" => "Authentication required. Please login as admin."]);
		exit;
	}	


	if(empty($_POST["CALL"])){
		echo json_encode(["error" => "Request invalid"]);
		exit;
	}

	$call = $_POST["CALL"];
	$result = [];

	// NLP-based file search

	if($call === 'nlp_search_files') {
		require_once '../resources/objects/google_nlp_service.php';
		$searchWord = $_POST['SEARCH_WORD'] ?? '';
		$categoryId = $_POST['CATEGORY_ID'] ?? '';
		$nlp = new GoogleNLPService();
		$results = [];
		// Directories to scan
		$uploadDirs = [
			realpath(__DIR__ . '/../uploads/files'),
			realpath(__DIR__ . '/../admin/uploads'),
			realpath(__DIR__ . '/../member/uploads'),
			realpath(__DIR__ . '/../subadmin/uploads')
		];
		foreach($uploadDirs as $dir) {
			if(!$dir || !is_dir($dir)) continue;
			$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
			foreach ($rii as $file) {
				if ($file->isDir()) continue;
				$filePath = $file->getPathname();
				$mimeType = mime_content_type($filePath);
				$fileName = $file->getFilename();
				// Extract text
				$textResult = $nlp->extractTextFromFile($filePath, $mimeType);
				$text = $textResult['success'] ? $textResult['text'] : '';
				if($searchWord && stripos($text, $searchWord) === false && stripos($fileName, $searchWord) === false) continue;
				// Optionally, filter by category if you have a mapping (not implemented here)
				$results[] = [
					'file_name' => $fileName,
					'file_path' => $filePath,
					'mime_type' => $mimeType,
					'file_size' => $file->getSize(),
					'datetime_uploaded' => date('Y-m-d H:i:s', $file->getMTime()),
					'file_category' => '', // Category detection can be added if needed
					'file_upload_id' => $filePath // Use path as ID for browsing
				];
			}
		}
		echo json_encode(["status" => "SUCCESS", "data" => $results]);
		exit;
	}

	// NLP analysis before upload (for preview)
	if($call === 'nlp_analyze') {
		require_once '../resources/objects/google_nlp_service.php';
		$m = new Main('file_upload_tbl');
		if(isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
			try {
				//require_once '../resources/objects/nlp_helper.php'; // Helper for Google NLP
				//$nlp = new NLPHelper();
				$file = $_FILES['file'];
				$tmpPath = $file['tmp_name'];
				$mimeType = $file['type'];

				$configFile = '../config/google_nlp_config.php';
				$apiKey = '';
				if (file_exists($configFile)) {
					$config = include($configFile);
					$apiKey = $config['api_key'] ?? '';
				}

				$nlp = new GoogleNLPService($apiKey);
				$categories = $m->getFileCategories() ?: [];
				$res = $nlp->analyzeFileAndSuggestCategory($tmpPath, $mimeType, $categories);

				$result = [
					'status' => 'SUCCESS',
					'category' => $res['suggested_category_name'] ?? 'Uncategorized',
					'score'=> $res['confidence_score'] ?? 0,
					'nlp_analysis' => $res
				];
			} catch(Exception $e) {
				$result = ["status" => "ERROR", "msg" => "NLP analysis failed: " . $e->getMessage()];
			}
		} else {
			$result = ["status" => "ERROR", "msg" => "No file uploaded for NLP analysis."];
		}
		echo json_encode($result);
		exit;
	}

	if($call == 1){
		$data = $_POST['DATA'] ?? [];
        $name = trim($data['NAME'] ?? "");
		
		try {
			EntityManager::createPosition($name);
			$result = ["status" => "SUCCESS","msg" => "<span class='success'>Successfully added new position</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 2){
		$data = $_POST["DATA"] ?? [];
		$name = trim($data["NAME"] ?? "");
		
		try {
			EntityManager::createFileCategory($name);
			$result = ["status" => "SUCCESS","msg" => "<span class='success'>Successfully added new file category</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 3){
		$data = $_POST["DATA"] ?? [];
		$name = trim($data["NAME"] ?? "");
		
		try {
			EntityManager::createTaskCategory($name);
			$result = ["status" => "SUCCESS","msg" => "<span class='success'>Successfully added new task category</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 4){
		try {
			$positions = EntityManager::getAllPositions() ?: [];
			// For DataTables, return just the data array wrapped in data property
			$result = ["data" => $positions];
		} catch(Exception $e) {
			$result = ["data" => [], "error" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 5){
		try {
			// Get file categories with their keywords
			$db = Database::getInstance();
			$fileCategories = $db->select("
				SELECT fc.file_category_id, fc.file_category,
					   GROUP_CONCAT(fck.keyword SEPARATOR ', ') as keywords
				FROM file_category_tbl fc
				LEFT JOIN file_category_key_tbl fck ON fc.file_category_id = fck.file_category_id
				GROUP BY fc.file_category_id, fc.file_category
				ORDER BY fc.file_category
			") ?: [];
			$result = ["data" => $fileCategories];
		} catch(Exception $e) {
			$result = ["data" => [], "error" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 6){
		try {
			$taskCategories = EntityManager::getAllTaskCategories() ?: [];
			$result = ["data" => $taskCategories];
		} catch(Exception $e) {
			$result = ["data" => [], "error" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 7){
		// Get admin users
		try {
			// Test with mock data first
			$mockData = [
				[
					'user_id' => 1,
					'user_name' => 'admin',
					'fname' => 'System',
					'lname' => 'Administrator',
					'mname' => '',
					'auxname' => '',
					'email' => 'admin@uasg.edu',
					'contact_number' => '09123456789',
					'gender' => 'Male',
					'birthdate' => '1990-01-01'
				]
			];
			
			// Try to get real data, fallback to mock
			if(class_exists('UserManager')) {
				$userManager = new UserManager();
				$users = $userManager->getUsersByType('admin');
				
				if(empty($users)) {
					// No users found, return mock data for testing
					$result = ["data" => $mockData];
				} else {
					$result = ["data" => $users];
				}
			} else {
				// Class not found, return mock data
				$result = ["data" => $mockData];
			}
			
		} catch(Exception $e) {
			error_log("Error in CALL 7: " . $e->getMessage());
			// Return mock data on error for testing
			$result = ["data" => [
				[
					'user_id' => 1,
					'user_name' => 'admin',
					'fname' => 'System',
					'lname' => 'Administrator',
					'mname' => '',
					'auxname' => '',
					'email' => 'admin@uasg.edu',
					'contact_number' => '09123456789',
					'gender' => 'Male',
					'birthdate' => '1990-01-01'
				]
			]];
		}
		echo json_encode($result);
	}else if($call == 8){
		// Get subadmin users with roles
		try {
			if(class_exists('UserManager')) {
				$userManager = new UserManager();
				$users = $userManager->getUsersByTypeWithRoles('subadmin');
				$result = ["data" => $users ? $users : []];
			} else {
				$result = ["data" => []];
			}
		} catch(Exception $e) {
			$result = ["data" => []];
		}
		echo json_encode($result);
	}else if($call == 9){
		// Get student users
		try {
			if(class_exists('UserManager')) {
				$userManager = new UserManager();
				$users = $userManager->getUsersByType('student');
				$result = ["data" => $users ? $users : []];
			} else {
				$result = ["data" => []];
			}
		} catch(Exception $e) {
			$result = ["data" => []];
		}
		echo json_encode($result);
	}else if($call == 10){
		// Create new user with enhanced data handling
		$data = [
			'user_name' => $_POST['user_name'] ?? '',
			'password' => $_POST['password'] ?? '',
			'fname' => $_POST['fname'] ?? '',
			'mname' => $_POST['mname'] ?? '',
			'lname' => $_POST['lname'] ?? '',
			'auxname' => $_POST['auxname'] ?? '',
			'gender' => $_POST['gender'] ?? '',
			'birthdate' => $_POST['birthdate'] ?? '',
			'contact_number' => $_POST['contact_number'] ?? '',
			'email' => $_POST['email'] ?? '',
			'user_type' => $_POST['user_type'] ?? '',
			'subadmin_role' => $_POST['subadmin_role'] ?? '',
			'permissions' => $_POST['permissions'] ?? []
		];
		
		try {
			$userManager = new UserManager();
			$userId = $userManager->createUser($data);
			
			// Handle sub-admin permissions
			if ($data['user_type'] === 'subadmin' && !empty($data['permissions'])) {
				$userManager->setUserPermissions($userId, $data['permissions']);
			}
			
			$result = ["status" => "SUCCESS", "msg" => "<span class='success'>User created successfully</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to create user: " . $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 11){
		// Update user with enhanced data handling
		$data = [
			'user_id' => $_POST['user_id'] ?? 0,
			'user_name' => $_POST['user_name'] ?? '',
			'password' => $_POST['password'] ?? '',
			'fname' => $_POST['fname'] ?? '',
			'mname' => $_POST['mname'] ?? '',
			'lname' => $_POST['lname'] ?? '',
			'auxname' => $_POST['auxname'] ?? '',
			'gender' => $_POST['gender'] ?? '',
			'birthdate' => $_POST['birthdate'] ?? '',
			'contact_number' => $_POST['contact_number'] ?? '',
			'email' => $_POST['email'] ?? '',
			'user_type' => $_POST['user_type'] ?? '',
			'subadmin_role' => $_POST['subadmin_role'] ?? '',
			'permissions' => $_POST['permissions'] ?? []
		];
		
		try {
			$userManager = new UserManager();
			$userManager->updateUser($data);
			
			// Handle sub-admin permissions
			if ($data['user_type'] === 'subadmin' && !empty($data['permissions'])) {
				$userManager->setUserPermissions($data['user_id'], $data['permissions']);
			}
			
			$result = ["status" => "SUCCESS", "msg" => "<span class='success'>User updated successfully</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to update user: " . $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 12){
		// Delete user with enhanced data handling
		$user_id = $_POST['user_id'] ?? 0;
		$user_type = $_POST['user_type'] ?? '';
		
		try {
			$userManager = new UserManager();
			$userManager->deleteUser($user_id, $user_type);
			$result = ["status" => "SUCCESS", "msg" => "<span class='success'>User deleted successfully</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to delete user: " . $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 13){
		// Get single user for editing with enhanced data
		$user_id = $_POST['user_id'] ?? ($_POST['USER_ID'] ?? 0);
		
		try {
			$userManager = new UserManager();
			$userData = $userManager->getUserById($user_id);
			
			// Add sub-admin role if applicable
			if ($userData && $userData['user_type'] === 'subadmin') {
				$userData['subadmin_role'] = $userManager->getSubadminRole($user_id);
			}
			
			$result = ["status" => "SUCCESS", "data" => $userData];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 14){
		// Get all files
		$user_id = $_POST['USER_ID'] ?? null;
		
		try {
			$fileManager = new FileManager();
			$files = $fileManager->getAllFiles($user_id);
			$result = ["data" => $files ?: []];
		} catch(Exception $e) {
			$result = ["data" => [], "status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 15){
		// Get user permissions for sub-admin
		$user_id = $_POST['USER_ID'] ?? 0;
		$categoryid = (isset($_POST['CATEGORY_ID']) && trim($_POST['CATEGORY_ID']) !== '') ? trim($_POST['CATEGORY_ID']) : null;
		try {
			$fileManager = new FileManager();
			//$result["data"] = $fileManager->getFilteredFiles($categoryId, $userId = null);
			//$permissions = $userManager->getUserPermissions($user_id);
			$result = ["status"=>"SUCCESS", "data"=> $fileManager->getFilteredFiles($categoryid, $user_id) ?: []];
			//$result = ["status" => "SUCCESS", "data" => $permissions ?: []];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage(), "data" => []];
		}
		echo json_encode($result);
	}else if($call == 16){
		// Upload file with Google NLP auto-categorization and user-confirmed category
		
		if(isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
			$data = [
				'file' => $_FILES['file'],
				'uploaded_by' => $_POST['uploaded_by'] ?? ($_SESSION['user_id'] ?? 0),
				'category_tag' => $_POST['category_tag'] ?? 'Uncategorized',
				'category_score' => $_POST['category_score'] ?? 0,
				'nlp_analysis' => $_POST['nlp_analysis'] ?? null,
				'file_path' => null // Will be set by FileManager
			];
		} else {
			$data = $_POST['DATA'] ?? [];
		}
		try {
			$fileManager = new Main('file_upload_tbl');
			if (!method_exists($fileManager, 'uploadFile')) {
				error_log('FileManager class does not have uploadFile method!');
				$result = ["status" => "ERROR", "msg" => "Internal error: uploadFile method missing."];
			} else {
				$uploadResult = $fileManager->uploadFile($data);
				$result = $uploadResult;
				if(!$uploadResult['success']) {
					error_log('File upload failed: ' . ($uploadResult['error'] ?? 'Unknown error'));
					$result = ["success" => false, "msg" => "File upload failed: " . ($uploadResult['error'] ?? 'Unknown error')];
				}
			}		
		} catch(Exception $e) {
			error_log('Exception during file upload: ' . $e->getMessage());
			$result = ["success" => false, "msg" => "Failed to upload file: " . $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 39){
		// Update user permissions for sub-admin
		$user_id = $_POST['user_id'] ?? 0;
		$permissions = $_POST['permissions'] ?? [];
		
		try {
			$userManager = new UserManager();
			$userManager->setUserPermissions($user_id, $permissions);
			$result = ["status" => "SUCCESS", "msg" => "Permissions updated successfully"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to update permissions: " . $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 17){
		// Delete file
		$data = $_POST['DATA'] ?? [];
		
		try {
			$fileManager = new FileManager();
			$result = $fileManager->deleteFile($data['file_id'], $data['user_id'], $data['reason']);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to delete file: " . $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 18){
		// Set file permission
		$data = $_POST['DATA'] ?? [];
		
		try {
			$fileManager = new FileManager();
			$result = $fileManager->setFilePermission($data['position_id'], $data['file_category_id']);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to set permission: " . $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 19){
		// Remove file permission
		$data = $_POST['DATA'] ?? [];
		
		try {
			$fileManager = new FileManager();
			$result = $fileManager->removeFilePermission($data['position_id'], $data['file_category_id']);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to remove permission: " . $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 20){
		// Get file permissions
		try {
			$fileManager = new FileManager();
			$permissions = $fileManager->getFilePermissions();
			$result = ["data" => $permissions ?: []];
		} catch(Exception $e) {
			$result = ["data" => [], "status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 21){
		// Get user accessible categories
		$user_id = $_POST['USER_ID'] ?? 0;
		
		try {
			$fileManager = new FileManager();
			$result["data"] = $fileManager->getUserAccessibleCategories($user_id);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 22){
		// Create new task
		$data = $_POST['DATA'] ?? [];
		
		try {
			$taskManager = new TaskManager();
			$result = $taskManager->createTask($data);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 23){
		// Get all tasks (for advisers)
		try {
			$taskManager = new TaskManager();
			$result["data"] = $taskManager->getTasks();
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 24){
		// Get tasks for member
		$user_id = $_POST['USER_ID'] ?? 0;
		
		try {
			$taskManager = new TaskManager();
			$result["data"] = $taskManager->getTasksForMember($user_id);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 25){
		// Submit task
		$data = $_POST['DATA'] ?? [];
		
		try {
			$taskManager = new TaskManager();
			$result = $taskManager->submitTask($data);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 26){
		// Review submission
		$data = $_POST['DATA'] ?? [];
		
		try {
			$taskManager = new TaskManager();
			$result = $taskManager->reviewSubmission($data);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 27){
		// Get submissions for task
		$task_id = $_POST['TASK_ID'] ?? null;
		
		try {
			$taskManager = new TaskManager();
			$result["data"] = $taskManager->getSubmissions($task_id);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 28){
		// Delete task
		$data = $_POST['DATA'] ?? [];
		
		try {
			$taskManager = new TaskManager();
			$result = $taskManager->deleteTask($data['task_id'], $data['reason']);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 29){
		// Get notifications
		$user_id = $_POST['USER_ID'] ?? 0;
		$unread_only = $_POST['UNREAD_ONLY'] ?? false;
		
		try {
			$notificationManager = new NotificationManager();
			$result["data"] = $notificationManager->getNotifications($user_id, $unread_only);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 30){
		// Mark notification as read
		$notification_id = $_POST['NOTIFICATION_ID'] ?? 0;
		
		try {
			$notificationManager = new NotificationManager();
			$result = $notificationManager->markAsRead($notification_id);
			$result = ["status" => "SUCCESS", "msg" => "Notification marked as read"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 31){
		// Get unread notification count
		$user_id = $_POST['USER_ID'] ?? 0;
		
		try {
			$notificationManager = new NotificationManager();
			$result["count"] = $notificationManager->getUnreadCount($user_id);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 32){
		// Create file category keyword
		$data = $_POST['DATA'] ?? [];
		$categoryId = $data['category_id'] ?? 0;
		$keyword = trim($data['keyword'] ?? "");
		
		try {
			EntityManager::createFileCategoryKeyword($categoryId, $keyword);
			$result = ["status" => "SUCCESS","msg" => "<span class='success'>Successfully added keyword</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 33){
		// Get file category keywords
		$categoryId = $_POST['CATEGORY_ID'] ?? null;
		
		try {
			$keywords = EntityManager::getFileCategoryKeywords($categoryId);
			$result = ["data" => $keywords];
		} catch(Exception $e) {
			$result = ["data" => [], "error" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 34){
		// Update file category keyword
		$data = $_POST['DATA'] ?? [];
		$keywordId = $data['keyword_id'] ?? 0;
		$keyword = trim($data['keyword'] ?? "");
		
		try {
			EntityManager::updateFileCategoryKeyword($keywordId, $keyword);
			$result = ["status" => "SUCCESS","msg" => "<span class='success'>Successfully updated keyword</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 35){
		// Delete file category keyword
		$data = $_POST['DATA'] ?? [];
		$keywordId = $data['keyword_id'] ?? 0;
		$reason = trim($data['reason'] ?? "Admin deletion");
		
		try {
			EntityManager::deleteFileCategoryKeyword($keywordId, $reason);
			$result = ["status" => "SUCCESS","msg" => "<span class='success'>Successfully deleted keyword</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 36){
		// Test file categorization
		$filename = $_POST['FILENAME'] ?? '';
		
		if(empty($filename)) {
			echo json_encode(["status" => "ERROR", "msg" => "Filename is required"]);
			exit;
		}
		
		try {
			$fileAnalyzer = new FileAnalyzer();
			$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
			$mimeType = 'application/octet-stream'; // Default mime type for testing
			
			$analysis = $fileAnalyzer->analyzeFileContent($filename, $mimeType, $extension);
			
			$result = [
				"status" => "SUCCESS",
				"data" => $analysis,
				"msg" => "File analyzed successfully"
			];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}
	
	// CALL 37: Send Mobile Notification (for testing and real scenarios)
	else if($call == 37) {
		try {
			$data = $_POST['DATA'] ?? [];
			$title = trim($data['TITLE'] ?? "UASG Notification");
			$message = trim($data['MESSAGE'] ?? "");
			$type = trim($data['TYPE'] ?? "info"); // info, success, warning, error
			$url = trim($data['URL'] ?? "");
			$userId = $data['USER_ID'] ?? $_SESSION['user_id'];
			
			if(empty($message)) {
				throw new Exception("Notification message is required");
			}
			
			// Save notification to database for future push notification system
			$notification_data = [
				'user_id' => $userId,
				'title' => $title,
				'message' => $message,
				'type' => $type,
				'url' => $url,
				'sent_at' => date('Y-m-d H:i:s'),
				'read_status' => 0
			];
			
			// For now, we'll just return the notification data
			// In a full implementation, you'd save this to a notifications table
			// and use a push notification service
			
			$result = [
				"status" => "SUCCESS",
				"data" => [
					"notification" => $notification_data,
					"mobile_optimized" => true,
					"supports_vibration" => true,
					"supports_actions" => true
				],
				"msg" => "Mobile notification prepared successfully"
			];
			
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}
	
	// CALL 38: Test Mobile Notification Features
	else if($call == 38) {
		try {
			$data = $_POST['DATA'] ?? [];
			$test_type = trim($data['TEST_TYPE'] ?? "basic");
			
			$notifications = [];
			
			switch($test_type) {
				case 'file_upload':
					$notifications[] = [
						'title' => '📁 File Upload Complete',
						'message' => 'Your file "test-document.pdf" has been uploaded successfully to the Academic category.',
						'type' => 'success',
						'url' => '/uasg/admin/entry-module.php',
						'actions' => [
							['action' => 'view', 'title' => '👁️ View File'],
							['action' => 'dismiss', 'title' => '❌ Dismiss']
						]
					];
					break;
					
				case 'system_alert':
					$notifications[] = [
						'title' => '⚠️ System Maintenance',
						'message' => 'System maintenance scheduled for tonight at 11 PM. Please save your work.',
						'type' => 'warning',
						'url' => '/uasg/',
						'requireInteraction' => true
					];
					break;
					
				case 'approval_needed':
					$notifications[] = [
						'title' => '📋 Document Approval Required',
						'message' => 'A new document is waiting for your approval in the Admin panel.',
						'type' => 'info',
						'url' => '/uasg/admin/',
						'badge' => '1'
					];
					break;
					
				case 'mobile_features':
					$notifications[] = [
						'title' => '📱 Mobile Features Test',
						'message' => 'Testing vibration, sound, and mobile-specific notification features.',
						'type' => 'info',
						'vibrate' => [200, 100, 200, 100, 200],
						'requireInteraction' => true,
						'actions' => [
							['action' => 'test', 'title' => '✅ Test Passed'],
							['action' => 'dismiss', 'title' => '❌ Close']
						]
					];
					break;
					
				default:
					$notifications[] = [
						'title' => '🧪 Basic Test Notification',
						'message' => 'This is a basic test notification to verify mobile functionality.',
						'type' => 'info',
						'url' => '/uasg/mobile-notifications-test.html'
					];
			}
			
			$result = [
				"status" => "SUCCESS",
				"data" => [
					"notifications" => $notifications,
					"test_type" => $test_type,
					"mobile_optimized" => true,
					"timestamp" => time()
				],
				"msg" => "Test notifications prepared successfully"
			];
			
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 40){
		try {
			$taskManager = new TaskManager();
			$tasks = $taskManager->getTasks(); // fetch all tasks
			echo json_encode(['data' => $tasks]);
		} catch(Exception $e) {
			echo json_encode(['data' => [], 'error' => $e->getMessage()]);
		}
		exit;
	}else if($call == 42){
		try {
			$taskManager = new TaskManager();
			$taskId = $_POST['task_id'];
			$tasks = $taskManager->getTasks(); // returns all tasks
			$task = array_filter($tasks, fn($t) => $t['task_id'] == $taskId);
			$task = array_values($task)[0] ?? null;
			if (!$task) {
				echo json_encode(['status' => 'ERROR', 'msg' => 'Task not found']);
			} else {
				echo json_encode(['status' => 'SUCCESS', 'data' => $task]);
			}
		} catch(Exception $e) {
			echo json_encode(['status' => 'ERROR', 'msg' => $e->getMessage()]);
		}
		exit;
	}else if($call == 43){
		$data = [
			'task_id' => $_POST['task_id'] ?? null,
			'task_category_id' => $_POST['task_category_id'] ?? null,
			'task_title' => $_POST['task_title'] ?? null,
			'task_description' => $_POST['task_description'] ?? null,
			'task_deadline' => $_POST['task_deadline'] ?? null
		];

		try {
			$taskManager = new TaskManager();
			$result = $taskManager->updateTask($data);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 47){
		// Get active members (students)
		try {
			$conn = Database::getInstance()->getConnection();
			
			$stmt = $conn->prepare("
				SELECT 
					u.user_id,
					u.user_name,
					p.fname,
					p.mname,
					p.lname,
					p.auxname,
					p.email,
					p.contact_number,
					(SELECT COUNT(*) FROM task_submission_tbl ts 
					 JOIN file_upload_tbl f ON ts.file_upload_id = f.file_upload_id 
					 WHERE f.uploaded_by = u.user_id) as submission_count,
					(SELECT COUNT(*) FROM file_upload_tbl f WHERE f.uploaded_by = u.user_id) as upload_count
				FROM user_tbl u
				JOIN profile_tbl p ON u.profile_id = p.profile_id
				WHERE u.user_type = 'student' 
				AND (u.is_active = 1 OR u.is_active IS NULL)
				ORDER BY p.lname, p.fname
			");
			$stmt->execute();
			$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			echo json_encode(["data" => $members]);
		} catch(Exception $e) {
			echo json_encode(["data" => [], "error" => $e->getMessage()]);
		}
	}else if($call == 48){
		// Get inactive members (students)
		try {
			$conn = Database::getInstance()->getConnection();
			
			$stmt = $conn->prepare("
				SELECT 
					u.user_id,
					u.user_name,
					p.fname,
					p.mname,
					p.lname,
					p.auxname,
					p.email,
					p.contact_number,
					u.deactivated_at
				FROM user_tbl u
				JOIN profile_tbl p ON u.profile_id = p.profile_id
				WHERE u.user_type = 'student' 
				AND u.is_active = 0
				ORDER BY u.deactivated_at DESC
			");
			$stmt->execute();
			$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			echo json_encode(["data" => $members]);
		} catch(Exception $e) {
			echo json_encode(["data" => [], "error" => $e->getMessage()]);
		}
	}else if($call == 49){
		// Get member's task submissions
		$userId = $_POST['user_id'] ?? 0;
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			$stmt = $conn->prepare("
				SELECT 
					ts.task_submission_id,
					t.task_title,
					f.file_name,
					f.file_upload_id,
					ts.check_status,
					f.datetime_uploaded as submitted_at
				FROM task_submission_tbl ts
				JOIN task_tbl t ON ts.task_id = t.task_id
				JOIN file_upload_tbl f ON ts.file_upload_id = f.file_upload_id
				WHERE f.uploaded_by = :user_id
				ORDER BY f.datetime_uploaded DESC
			");
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			echo json_encode(["status" => "SUCCESS", "data" => $submissions]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 50){
		// Get member's all uploads
		$userId = $_POST['user_id'] ?? 0;
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			$stmt = $conn->prepare("
				SELECT 
					f.file_upload_id,
					f.file_name,
					fc.file_category as category_name,
					f.mime_type,
					f.datetime_uploaded
				FROM file_upload_tbl f
				LEFT JOIN file_category_tbl fc ON f.file_category_id = fc.file_category_id
				WHERE f.uploaded_by = :user_id
				ORDER BY f.datetime_uploaded DESC
			");
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			echo json_encode(["status" => "SUCCESS", "data" => $uploads]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 51){
		// Deactivate member
		$userId = $_POST['user_id'] ?? 0;
		$reason = $_POST['reason'] ?? '';
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			$stmt = $conn->prepare("
				UPDATE user_tbl 
				SET is_active = 0, 
					deactivated_at = NOW(),
					deactivation_reason = :reason
				WHERE user_id = :user_id AND user_type = 'student'
			");
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
			$stmt->execute();
			
			echo json_encode(["status" => "SUCCESS", "msg" => "Member deactivated successfully"]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 52){
		// Reactivate member
		$userId = $_POST['user_id'] ?? 0;
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			$stmt = $conn->prepare("
				UPDATE user_tbl 
				SET is_active = 1, 
					deactivated_at = NULL,
					deactivation_reason = NULL
				WHERE user_id = :user_id AND user_type = 'student'
			");
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			
			echo json_encode(["status" => "SUCCESS", "msg" => "Member reactivated successfully"]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 53){
		// Get tasks report with filters
		$filters = $_POST['filters'] ?? ['type' => 'all'];
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			$sql = "
				SELECT 
					t.task_id,
					tc.task_category as category_name,
					t.task_title,
					t.task_description,
					t.task_deadline,
					COUNT(ts.task_submission_id) as submission_count
				FROM task_tbl t
				LEFT JOIN task_category_tbl tc ON t.task_category_id = tc.task_category_id
				LEFT JOIN task_submission_tbl ts ON t.task_id = ts.task_id
			";
			
			$where = [];
			$params = [];
			
			if ($filters['type'] === 'month' && !empty($filters['month'])) {
				$where[] = "DATE_FORMAT(t.task_deadline, '%Y-%m') = :month";
				$params[':month'] = $filters['month'];
			} elseif ($filters['type'] === 'year' && !empty($filters['year'])) {
				$where[] = "YEAR(t.task_deadline) = :year";
				$params[':year'] = $filters['year'];
			} elseif ($filters['type'] === 'date' && !empty($filters['date_from']) && !empty($filters['date_to'])) {
				$where[] = "t.task_deadline BETWEEN :date_from AND :date_to";
				$params[':date_from'] = $filters['date_from'];
				$params[':date_to'] = $filters['date_to'];
			}
			
			if (!empty($where)) {
				$sql .= " WHERE " . implode(" AND ", $where);
			}
			
			$sql .= " GROUP BY t.task_id ORDER BY t.task_deadline DESC";
			
			$stmt = $conn->prepare($sql);
			foreach ($params as $key => $value) {
				$stmt->bindValue($key, $value);
			}
			$stmt->execute();
			$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			echo json_encode(["data" => $tasks]);
		} catch(Exception $e) {
			echo json_encode(["data" => [], "error" => $e->getMessage()]);
		}
	}else if($call == 54){
		// Get uploads report with filters
		$filters = $_POST['filters'] ?? ['type' => 'all'];
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			$sql = "
				SELECT 
					f.file_upload_id,
					f.file_name,
					fc.file_category as category_name,
					CONCAT(p.fname, ' ', p.lname) as uploader_name,
					f.datetime_uploaded,
					f.mime_type
				FROM file_upload_tbl f
				LEFT JOIN file_category_tbl fc ON f.file_category_id = fc.file_category_id
				LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id
				LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
			";
			
			$where = [];
			$params = [];
			
			if ($filters['type'] === 'month' && !empty($filters['month'])) {
				$where[] = "DATE_FORMAT(f.datetime_uploaded, '%Y-%m') = :month";
				$params[':month'] = $filters['month'];
			} elseif ($filters['type'] === 'year' && !empty($filters['year'])) {
				$where[] = "YEAR(f.datetime_uploaded) = :year";
				$params[':year'] = $filters['year'];
			} elseif ($filters['type'] === 'date' && !empty($filters['date_from']) && !empty($filters['date_to'])) {
				$where[] = "DATE(f.datetime_uploaded) BETWEEN :date_from AND :date_to";
				$params[':date_from'] = $filters['date_from'];
				$params[':date_to'] = $filters['date_to'];
			}
			
			if (!empty($where)) {
				$sql .= " WHERE " . implode(" AND ", $where);
			}
			
			$sql .= " ORDER BY f.datetime_uploaded DESC";
			
			$stmt = $conn->prepare($sql);
			foreach ($params as $key => $value) {
				$stmt->bindValue($key, $value);
			}
			$stmt->execute();
			$uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			echo json_encode(["data" => $uploads]);
		} catch(Exception $e) {
			echo json_encode(["data" => [], "error" => $e->getMessage()]);
		}
	}else if($call == 55){
		// Get submissions report with filters
		$filters = $_POST['filters'] ?? ['type' => 'all'];
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			$sql = "
				SELECT 
					ts.task_submission_id,
					t.task_title,
					CONCAT(p.fname, ' ', p.lname) as student_name,
					f.file_name,
					f.file_upload_id,
					f.datetime_uploaded as submitted_at,
					ts.check_status
				FROM task_submission_tbl ts
				JOIN task_tbl t ON ts.task_id = t.task_id
				JOIN file_upload_tbl f ON ts.file_upload_id = f.file_upload_id
				JOIN user_tbl u ON f.uploaded_by = u.user_id
				JOIN profile_tbl p ON u.profile_id = p.profile_id
			";
			
			$where = [];
			$params = [];
			
			if ($filters['type'] === 'month' && !empty($filters['month'])) {
				$where[] = "DATE_FORMAT(f.datetime_uploaded, '%Y-%m') = :month";
				$params[':month'] = $filters['month'];
			} elseif ($filters['type'] === 'year' && !empty($filters['year'])) {
				$where[] = "YEAR(f.datetime_uploaded) = :year";
				$params[':year'] = $filters['year'];
			} elseif ($filters['type'] === 'date' && !empty($filters['date_from']) && !empty($filters['date_to'])) {
				$where[] = "DATE(f.datetime_uploaded) BETWEEN :date_from AND :date_to";
				$params[':date_from'] = $filters['date_from'];
				$params[':date_to'] = $filters['date_to'];
			}
			
			if (!empty($where)) {
				$sql .= " WHERE " . implode(" AND ", $where);
			}
			
			$sql .= " ORDER BY f.datetime_uploaded DESC";
			
			$stmt = $conn->prepare($sql);
			foreach ($params as $key => $value) {
				$stmt->bindValue($key, $value);
			}
			$stmt->execute();
			$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			echo json_encode(["data" => $submissions]);
		} catch(Exception $e) {
			echo json_encode(["data" => [], "error" => $e->getMessage()]);
		}
	}else if($call == 56){
		// Get statistics for dashboard
		$filters = $_POST['filters'] ?? ['type' => 'all'];
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			// Total tasks
			$sql = "SELECT COUNT(*) as total FROM task_tbl";
			$params = [];
			
			if ($filters['type'] === 'month' && !empty($filters['month'])) {
				$sql .= " WHERE DATE_FORMAT(task_deadline, '%Y-%m') = :month";
				$params[':month'] = $filters['month'];
			} elseif ($filters['type'] === 'year' && !empty($filters['year'])) {
				$sql .= " WHERE YEAR(task_deadline) = :year";
				$params[':year'] = $filters['year'];
			} elseif ($filters['type'] === 'date' && !empty($filters['date_from']) && !empty($filters['date_to'])) {
				$sql .= " WHERE task_deadline BETWEEN :date_from AND :date_to";
				$params[':date_from'] = $filters['date_from'];
				$params[':date_to'] = $filters['date_to'];
			}
			
			$stmt = $conn->prepare($sql);
			foreach ($params as $key => $value) {
				$stmt->bindValue($key, $value);
			}
			$stmt->execute();
			$totalTasks = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
			
			// Total uploads
			$sql = "SELECT COUNT(*) as total FROM file_upload_tbl";
			$params = [];
			
			if ($filters['type'] === 'month' && !empty($filters['month'])) {
				$sql .= " WHERE DATE_FORMAT(datetime_uploaded, '%Y-%m') = :month";
				$params[':month'] = $filters['month'];
			} elseif ($filters['type'] === 'year' && !empty($filters['year'])) {
				$sql .= " WHERE YEAR(datetime_uploaded) = :year";
				$params[':year'] = $filters['year'];
			} elseif ($filters['type'] === 'date' && !empty($filters['date_from']) && !empty($filters['date_to'])) {
				$sql .= " WHERE DATE(datetime_uploaded) BETWEEN :date_from AND :date_to";
				$params[':date_from'] = $filters['date_from'];
				$params[':date_to'] = $filters['date_to'];
			}
			
			$stmt = $conn->prepare($sql);
			foreach ($params as $key => $value) {
				$stmt->bindValue($key, $value);
			}
			$stmt->execute();
			$totalUploads = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
			
			// Total submissions
			$sql = "SELECT COUNT(*) as total FROM task_submission_tbl ts JOIN file_upload_tbl f ON ts.file_upload_id = f.file_upload_id";
			$params = [];
			
			if ($filters['type'] === 'month' && !empty($filters['month'])) {
				$sql .= " WHERE DATE_FORMAT(f.datetime_uploaded, '%Y-%m') = :month";
				$params[':month'] = $filters['month'];
			} elseif ($filters['type'] === 'year' && !empty($filters['year'])) {
				$sql .= " WHERE YEAR(f.datetime_uploaded) = :year";
				$params[':year'] = $filters['year'];
			} elseif ($filters['type'] === 'date' && !empty($filters['date_from']) && !empty($filters['date_to'])) {
				$sql .= " WHERE DATE(f.datetime_uploaded) BETWEEN :date_from AND :date_to";
				$params[':date_from'] = $filters['date_from'];
				$params[':date_to'] = $filters['date_to'];
			}
			
			$stmt = $conn->prepare($sql);
			foreach ($params as $key => $value) {
				$stmt->bindValue($key, $value);
			}
			$stmt->execute();
			$totalSubmissions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
			
			// Active members
			$stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_tbl WHERE user_type = 'student' AND (is_active = 1 OR is_active IS NULL)");
			$stmt->execute();
			$activeMembers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
			
			// Tasks by category
			$stmt = $conn->prepare("SELECT tc.task_category as category, COUNT(t.task_id) as count FROM task_category_tbl tc LEFT JOIN task_tbl t ON tc.task_category_id = t.task_category_id GROUP BY tc.task_category_id");
			$stmt->execute();
			$tasksByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			// Uploads by category
			$stmt = $conn->prepare("SELECT fc.file_category as category, COUNT(f.file_upload_id) as count FROM file_category_tbl fc LEFT JOIN file_upload_tbl f ON fc.file_category_id = f.file_category_id GROUP BY fc.file_category_id");
			$stmt->execute();
			$uploadsByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			echo json_encode([
				"status" => "SUCCESS",
				"data" => [
					"total_tasks" => $totalTasks,
					"total_uploads" => $totalUploads,
					"total_submissions" => $totalSubmissions,
					"active_members" => $activeMembers,
					"tasks_by_category" => $tasksByCategory,
					"uploads_by_category" => $uploadsByCategory
				]
			]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 58){
		// Change password
		$userId = $_SESSION['user_id'];
		$currentPassword = $_POST['current_password'] ?? '';
		$newPassword = $_POST['new_password'] ?? '';
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			// Verify current password
			$stmt = $conn->prepare("SELECT pass_word FROM user_tbl WHERE user_id = :user_id");
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if (!$user || !password_verify($currentPassword, $user['pass_word'])) {
				echo json_encode(["status" => "ERROR", "msg" => "Current password is incorrect"]);
				exit;
			}
			
			// Update password
			$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
			$stmt = $conn->prepare("UPDATE user_tbl SET pass_word = :password WHERE user_id = :user_id");
			$stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			
			echo json_encode(["status" => "SUCCESS", "msg" => "Password updated successfully"]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 59){
		// Save security questions
		$userId = $_SESSION['user_id'];
		$question1 = $_POST['question1'] ?? '';
		$answer1 = password_hash($_POST['answer1'] ?? '', PASSWORD_DEFAULT);
		$question2 = $_POST['question2'] ?? '';
		$answer2 = password_hash($_POST['answer2'] ?? '', PASSWORD_DEFAULT);
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			// Check if security questions already exist
			$stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_security_tbl WHERE user_id = :user_id");
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
			
			if ($exists) {
				$stmt = $conn->prepare("UPDATE user_security_tbl SET question1 = :q1, answer1 = :a1, question2 = :q2, answer2 = :a2 WHERE user_id = :user_id");
			} else {
				$stmt = $conn->prepare("INSERT INTO user_security_tbl (user_id, question1, answer1, question2, answer2) VALUES (:user_id, :q1, :a1, :q2, :a2)");
			}
			
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':q1', $question1, PDO::PARAM_STR);
			$stmt->bindParam(':a1', $answer1, PDO::PARAM_STR);
			$stmt->bindParam(':q2', $question2, PDO::PARAM_STR);
			$stmt->bindParam(':a2', $answer2, PDO::PARAM_STR);
			$stmt->execute();
			
			echo json_encode(["status" => "SUCCESS", "msg" => "Security questions saved successfully"]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 60){
		// Save recovery email
		$userId = $_SESSION['user_id'];
		$recoveryEmail = $_POST['recovery_email'] ?? '';
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			// Check if record exists
			$stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_security_tbl WHERE user_id = :user_id");
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
			
			if ($exists) {
				$stmt = $conn->prepare("UPDATE user_security_tbl SET recovery_email = :email WHERE user_id = :user_id");
			} else {
				$stmt = $conn->prepare("INSERT INTO user_security_tbl (user_id, recovery_email) VALUES (:user_id, :email)");
			}
			
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':email', $recoveryEmail, PDO::PARAM_STR);
			$stmt->execute();
			
			echo json_encode(["status" => "SUCCESS", "msg" => "Recovery email updated successfully"]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 61){
		// Get security settings
		$userId = $_SESSION['user_id'];
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			$stmt = $conn->prepare("SELECT question1, answer1, question2, answer2, recovery_email FROM user_security_tbl WHERE user_id = :user_id");
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$settings = $stmt->fetch(PDO::FETCH_ASSOC);
			
			// Remove actual answers from response (only send questions)
			if ($settings) {
				unset($settings['answer1']);
				unset($settings['answer2']);
			}
			
			echo json_encode(["status" => "SUCCESS", "data" => $settings]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 62){
		// Get NLP analysis for a file
		$fileId = $_POST['file_id'] ?? 0;
		
		try {
			$fileManager = new FileManager();
			$analysis = $fileManager->getNLPAnalysis($fileId);
			
			if ($analysis) {
				echo json_encode(["status" => "SUCCESS", "data" => $analysis]);
			} else {
				echo json_encode(["status" => "ERROR", "msg" => "No NLP analysis found for this file"]);
			}
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}




	// Include permission management endpoints
	include_once('ajax_permissions.php');
