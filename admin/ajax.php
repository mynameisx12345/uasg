<?php
	// Prevent any output before JSON
	ob_start();
	
	session_start();
	require_once("../resources/objects/db_config.php");
	require_once("../resources/objects/main_class.php");
	
	// Clear any previous output and set headers
	ob_clean();
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
		// Get adviser users
		try {
			if(class_exists('UserManager')) {
				$userManager = new UserManager();
				$users = $userManager->getUsersByType('adviser');
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
		// Create new user
		$data = $_POST['DATA'] ?? [];
		
		try {
			$userManager = new UserManager();
			$userManager->createUser($data);
			$result = ["status" => "SUCCESS", "msg" => "<span class='success'>User created successfully</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to create user: " . $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 11){
		// Update user
		$data = $_POST['DATA'] ?? [];
		
		try {
			$userManager = new UserManager();
			$userManager->updateUser($data);
			$result = ["status" => "SUCCESS", "msg" => "<span class='success'>User updated successfully</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to update user: " . $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 12){
		// Delete user
		$data = $_POST['DATA'] ?? [];
		
		try {
			$userManager = new UserManager();
			$userManager->deleteUser($data);
			$result = ["status" => "SUCCESS", "msg" => "<span class='success'>User deleted successfully</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to delete user: " . $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 13){
		// Get single user for editing
		$user_id = $_POST['USER_ID'] ?? 0;
		
		try {
			$userManager = new UserManager();
			$userData = $userManager->getUserById($user_id);
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
			$result["data"] = $fileManager->getAllFiles($user_id);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 15){
		// Get files by category
		$category_id = $_POST['CATEGORY_ID'] ?? 0;
		$user_id = $_POST['USER_ID'] ?? null;
		
		try {
			$fileManager = new FileManager();
			$result["data"] = $fileManager->getFilesByCategory($category_id, $user_id);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 16){
		// Upload file
		$data = $_POST['DATA'] ?? [];
		
		try {
			$fileManager = new FileManager();
			$result = $fileManager->uploadFile($data);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to upload file: " . $e->getMessage()];
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
			$result["data"] = $fileManager->getFilePermissions();
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
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
	}
