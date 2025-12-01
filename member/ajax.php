<?php
    // Prevent any output before JSON
	error_reporting(E_ALL);
	ini_set('display_errors', 1); // Don't display errors in output
	ini_set('log_errors', 1); // Log errors instead
	
	// Prevent caching of AJAX responses
	header("Cache-Control: no-cache, no-store, must-revalidate");
	header("Pragma: no-cache");
	header("Expires: 0");
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

if(empty($_POST["CALL"])){
    echo json_encode(["error" => "Request invalid"]);
    exit;
}

$call = $_POST["CALL"];
$result = [];

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

// Member AJAX Calls
if($call == 1){
    // Get dashboard stats
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $dashboardManager = new DashboardManager();
        $result = $dashboardManager->getMemberDashboardStats($memberId);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 2){
    // Get recent activity
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $dashboardManager = new DashboardManager();
        $result = $dashboardManager->getMemberRecentActivity($memberId);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 3){
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
    
}else if($call == 4){
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
    
}else if($call == 5){
    // Get file categories
    try {
        $entityManager = new EntityManager();
        $result = ["data" => $entityManager::getAllFileCategories() ?: []];
    } catch(Exception $e) {
        $result = ["data" => [], "error" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 6){
    // Get my files
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $filters = [
            'category' => $_POST['category'] ?? '',
            'type' => $_POST['type'] ?? ''
        ];
        $fileManager = new FileManager();
        $managerResult = $fileManager->getMemberFiles($memberId, $filters);
        
        // Extract data array for DataTables format
        if (isset($managerResult['data'])) {
            $result = ["data" => $managerResult['data']];
        } else {
            $result = ["data" => []];
        }
    } catch(Exception $e) {
        $result = ["data" => [], "error" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 7){
    // Delete file
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $fileId = $_POST['file_id'] ?? 0;
        $fileManager = new FileManager();
        $result = $fileManager->deleteMemberFile($memberId, $fileId);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 8){
    // Download file
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $fileId = $_POST['file_id'] ?? 0;
        $fileManager = new FileManager();
        $result = $fileManager->downloadMemberFile($memberId, $fileId);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 9){
    /*// Get active tasks
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $taskManager = new TaskManager();
        $managerResult = $taskManager->getMemberActiveTasks($memberId);
        
        // Extract data array for DataTables format
        if (isset($managerResult['data'])) {
            $result = $managerResult;//["data" => $managerResult['data']];
        } else if (is_array($managerResult)) {
            $result = $managerResult;//["data" => $managerResult];
        } else {
            $result = ["data" => []];
        }
    } catch(Exception $e) {
        $result = ["data" => [], "error" => $e->getMessage()];
    }
    echo json_encode($result);*/

    $memberId = $_SESSION['user_id'] ?? 0;
    $taskManager = new TaskManager();
    $tasks = $taskManager->getMemberActiveTasks($memberId);
    // Ensure output is always { data: [...] }
    if (isset($tasks['data'])) {
        echo json_encode($tasks, JSON_UNESCAPED_UNICODE);
    } else if (is_array($tasks)) {
        echo json_encode($tasks, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode($tasks, JSON_UNESCAPED_UNICODE);
    }
    exit;
    
}else if($call == 10){
    // Get task submissions
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $taskManager = new TaskManager();
        $managerResult = $taskManager->getMemberTaskSubmissions($memberId);
        
        // Extract data array for DataTables format
        if (isset($managerResult['data'])) {
            $result = $managerResult;//["data" => $managerResult['data']];
        } else if (is_array($managerResult)) {
            $result = $managerResult;//["data" => $managerResult];
        } else {
            $result = ["data" => []];
        }
    } catch(Exception $e) {
        $result = ["data" => [], "error" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 11){
    // Submit task file
    /*try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $data = [
            'task_id' => $_POST['task_id'] ?? 0,
            'file_id' => $_POST['file_id'] ?? 0,
            'notes' => $_POST['notes'] ?? '',
            'submitted_by' => $memberId
        ];
        $taskManager = new TaskManager();
        $result = $taskManager->submitMemberTaskFile($data);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);*/
    
    try {

        $memberId = $_SESSION['user_id'];

        // Step 1: Upload file first (CORRECT FORMAT)
        $fileManager = new FileManager();
        $upload = $fileManager->uploadFile([
            'file' => $_FILES['file'],
            'uploaded_by' => $memberId
        ]);

        if (empty($upload['success']) || empty($upload['file_id'])) {
            $err = $upload['error'] ?? 'File upload failed.';
            throw new Exception($err);
        }

        $file_id = $upload['file_id'];

        // Step 2: Submit task
        $data = [
            'task_id'     => $_POST['task_id'] ?? 0,
            'file_id'     => $file_id,
            'submitted_by'=> $memberId,
        ];

        $taskManager = new TaskManager();
        $result = $taskManager->submitMemberTaskFile($data);

    } catch (Exception $e) {
        $result = ['success' => false, 'msg' => $e->getMessage()];
    }

    echo json_encode($result);
    
}else if($call == 12){
    // Get notifications
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $unreadOnly = $_POST['unread_only'] ?? false;
        $notificationManager = new NotificationManager();
        $result = ["data" => $notificationManager->getNotifications($memberId, $unreadOnly) ?: []];
    } catch(Exception $e) {
        $result = ["data" => [], "error" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 13){
    // Mark notification as read
    try {
        $notificationId = $_POST['notification_id'] ?? 0;
        $notificationManager = new NotificationManager();
        $success = $notificationManager->markAsRead($notificationId);
        $result = ["status" => $success ? "SUCCESS" : "ERROR"];
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 14){
    // Change password
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $data = [
            'current_password' => $_POST['current_password'] ?? '',
            'new_password' => $_POST['new_password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? ''
        ];
        $userManager = new UserManager();
        $result = $userManager->changeMemberPassword($memberId, $data);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 15){
    // Get file details
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $fileId = $_POST['file_id'] ?? 0;
        $fileManager = new FileManager();
        $result = $fileManager->getMemberFileDetails($memberId, $fileId);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 16){
    // Update file info
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $data = $_POST['DATA'] ?? [];
        $data['updated_by'] = $memberId;
        $fileManager = new FileManager();
        $result = $fileManager->updateMemberFileInfo($data);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 17){
    // Upload multiple files
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $data = $_POST['DATA'] ?? [];
        $data['uploaded_by'] = $memberId;
        $fileManager = new FileManager();
        $result = $fileManager->uploadMultipleMemberFiles($data);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 18){
    // Get task details
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $taskId = $_POST['task_id'] ?? 0;
        $taskManager = new TaskManager();
        $result = $taskManager->getMemberTaskDetails($memberId, $taskId);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
}else if($call == 20){
    // Download file (returns file as binary, not JSON)
    ob_clean(); // Clear any previous output
    $memberId = $_SESSION['user_id'] ?? 0;
    $fileId = $_POST['file_id'] ?? 0;
    
    $fileManager = new FileManager();
    $result = $fileManager->getMemberFileDetails($memberId, $fileId);
    
    if(!$result['success'] || empty($result['data'])){
        http_response_code(404);
        echo "File not found or unauthorized.";
        exit;
    }
    
    $fileInfo = $result['data'];
    $filePath = '../' . $fileInfo['file_path'];
    
    if(!file_exists($filePath)){
        http_response_code(404);
        echo "Physical file not found at: " . htmlspecialchars($filePath);
        exit;
    }
    
    // Clear all previous headers and output
    header_remove();
    header('Content-Description: File Transfer');
    header('Content-Type: ' . ($fileInfo['mime_type'] ?? 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . basename($fileInfo['file_name']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    
    // Output file
    readfile($filePath);
    exit;
}else{
    echo json_encode(["status" => "ERROR", "msg" => "Invalid call"]);
}
// Direct file download for DataTable/JS (CALL: 20)

?>