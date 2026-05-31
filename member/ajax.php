<?php
    // Prevent any output before JSON
	error_reporting(E_ALL);
	ini_set('display_errors', 0); // Don't display errors in output
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

// Handle GET download requests (file-explorer uses window.open)
if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['CALL']) && $_GET['CALL'] === 'download') {
    ob_end_clean();
    $fileId = $_GET['file_id'] ?? 0;
    $userId = $_SESSION['user_id'] ?? null;
    try {
        $fileManager = new FileManager();
        $fileManager->downloadFile($fileId, $userId);
    } catch(Exception $e) {
        header("Content-Type: application/json");
        echo json_encode(["status" => "ERROR", "msg" => "Download failed: " . $e->getMessage()]);
    }
    exit;
}


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

// Enforce 30-minute idle session timeout
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required', 'session_expired' => true]);
    exit;
}
$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode(['error' => 'Session expired', 'session_expired' => true]);
    exit;
}
$_SESSION['last_activity'] = time();

$call = $_POST["CALL"];
$result = [];

// Global search
if($call === 'global_search') {
	$userId = $_SESSION['user_id'] ?? null;
	$q = "%" . trim($_POST["query"] ?? "") . "%";
	$db = Database::getInstance();
	$results = [];
	$files = $db->select("SELECT file_upload_id, original_filename, category_tag FROM file_upload_tbl WHERE uploaded_by = ? AND original_filename LIKE ? LIMIT 5", [$userId, $q]);
	foreach($files as $f) $results[] = ["type"=>"file","id"=>$f["file_upload_id"],"icon"=>"📄","title"=>$f["original_filename"],"meta"=>$f["category_tag"]??"File"];
	$tasks = $db->select("SELECT t.task_id, t.task_title, t.task_deadline, (SELECT COUNT(*) FROM task_submission_tbl s WHERE s.task_id = t.task_id AND s.submitted_by = ? AND s.check_status != 'rejected') as has_submission FROM task_tbl t WHERE t.assigned_to = ? AND t.task_title LIKE ? AND t.task_status = 'active' LIMIT 5", [$userId, $userId, $q]);
	foreach($tasks as $t) {
		$deadline = strtotime($t['task_deadline']);
		$today = strtotime('today');
		$hasSub = (int)$t['has_submission'] > 0;
		if($hasSub) { $meta = 'Awaiting Review'; $taskType = 'task_submission'; }
		else if($deadline < $today) { $meta = 'Overdue'; $taskType = 'task_overdue'; }
		else { $meta = 'Pending'; $taskType = 'task_pending'; }
		$results[] = ["type"=>$taskType,"id"=>$t["task_id"],"icon"=>"📋","title"=>$t["task_title"],"meta"=>$meta];
	}
	echo json_encode(["success"=>true,"results"=>$results]);
	exit;
}


// NLP analysis before upload (for preview)
if($call === 'nlp_analyze') {
		require_once '../resources/objects/ml_service_incremental.php';
		require_once '../resources/objects/text_extractor.php';
		
		if(isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
			try {
			$file = $_FILES['file'];
			$tmpPath = $file['tmp_name'];
			$mimeType = $file['type'];

			// Extract text from file
			$textExtractor = new TextExtractor();
			$extractResult = $textExtractor->extractText($tmpPath, $mimeType);
			
			// Handle both array and string responses from TextExtractor
			if (is_array($extractResult)) {
				if (!$extractResult['success']) {
					throw new Exception($extractResult['error'] ?? "Could not extract text from file");
				}
				$extractedText = $extractResult['text'] ?? '';
			} else {
				$extractedText = $extractResult;
			}
			
			if (empty($extractedText)) {
				throw new Exception("Could not extract text from file");
			}

			// Use ML model for classification
			$mlService = new IncrementalMLService();
			$prediction = $mlService->predict($extractedText);

			if ($prediction['success']) {
				// Check confidence threshold - read from saved settings
				$confidence = $prediction['confidence'] ?? 0;
				$confidenceThreshold = floatval($mlService->getSetting('ml_confidence_threshold') ?? 60) / 100;
				
				if ($confidence < $confidenceThreshold) {
					// Low confidence - fallback to "Others"
					$category = 'Others';
					$score = round($confidence * 100, 2);
					$fallbackReason = 'Low confidence prediction';
				} else {
					// Good confidence - use ML prediction
					$category = $prediction['category'] ?? 'Others';
					$score = round($confidence * 100, 2);
					$fallbackReason = null;
				}
				
				$result = [
					'status' => 'SUCCESS',
					'category' => $category,
					'score' => $score,
					'confidence' => $score,
					'original_prediction' => $prediction['category'] ?? null,
					'all_scores' => $prediction['all_scores'] ?? [],
					'top_keywords' => $prediction['top_keywords'] ?? [],
					'confidence_threshold' => round($confidenceThreshold * 100, 2),
					'fallback_used' => $category === 'Others',
					'fallback_reason' => $fallbackReason,
					'keywords' => [], // ML doesn't extract keywords
					'entities' => [], // ML doesn't extract entities
					'sentiment' => [], // ML doesn't analyze sentiment
					'word_count' => str_word_count($extractedText),
					'extracted_text' => substr($extractedText, 0, 1000), // First 1000 chars
					'nlp_analysis' => [
						'provider' => 'ML Model',
						'model_id' => $prediction['model_id'] ?? null,
						'suggested_category' => $category,
						'category_confidence' => $score,
						'original_prediction' => $prediction['category'] ?? null,
						'fallback_used' => $category === 'Others',
						'word_count' => str_word_count($extractedText),
						'extracted_text' => substr($extractedText, 0, 1000)
					]
				];
			} else {
				// ML prediction completely failed - use "Others"
				$result = [
					'status' => 'SUCCESS', // Still success, but with fallback
					'category' => 'Others',
					'score' => 0,
					'confidence' => 0,
					'fallback_used' => true,
					'fallback_reason' => 'ML prediction failed: ' . ($prediction['error'] ?? 'Unknown error'),
					'keywords' => [],
					'entities' => [],
					'sentiment' => [],
					'word_count' => str_word_count($extractedText),
					'extracted_text' => substr($extractedText, 0, 1000),
					'nlp_analysis' => [
						'provider' => 'ML Model (Fallback)',
						'model_id' => null,
						'suggested_category' => 'Others',
						'category_confidence' => 0,
						'fallback_used' => true,
						'error' => $prediction['error'] ?? 'ML model error',
						'word_count' => str_word_count($extractedText),
						'extracted_text' => substr($extractedText, 0, 1000)
					]
				];
			}
			} catch(Exception $e) {
				$result = ["status" => "ERROR", "msg" => "ML analysis failed: " . $e->getMessage()];
			}
		} else {
			$result = ["status" => "ERROR", "msg" => "No file uploaded for ML analysis."];
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
    // Get task submissions with optional status/category filters
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $filters = [
            'status'   => $_POST['status']   ?? '',
            'category' => $_POST['category'] ?? ''
        ];
        $taskManager = new TaskManager();
        $managerResult = $taskManager->getMemberTaskSubmissions($memberId, $filters);
        
        if (isset($managerResult['data'])) {
            $result = $managerResult;
        } else if (is_array($managerResult)) {
            $result = ['data' => $managerResult];
        } else {
            $result = ['data' => []];
        }
    } catch(Exception $e) {
        $result = ['data' => [], 'error' => $e->getMessage()];
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
        if (!empty($result['success'])) {
            $db = Database::getInstance();
            $_st = $db->selectOne("SELECT t.task_title,t.task_description,u.user_id as admin_id FROM task_tbl t JOIN user_tbl u ON u.user_type='admin' WHERE t.task_id=? LIMIT 1", [$data['task_id']]);
            $_mn = $db->selectOne("SELECT CONCAT(p.fname,' ',p.lname) as full_name FROM profile_tbl p JOIN user_tbl u ON u.profile_id=p.profile_id WHERE u.user_id=?", [$memberId]);
            if ($_st && !empty($_st['admin_id'])) (new NotificationManager())->createNotification(['user_id'=>$_st['admin_id'],'type'=>'task_submitted','title'=>'Task Submitted','message'=>($_mn['full_name']??"Member")." submitted '{$_st['task_title']}'. {$_st['task_description']}",'related_id'=>$data['task_id']]);
        }
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

        // SERVER-SIDE: Validate file against task BEFORE submitting — blocks if invalid
        try {
            require_once __DIR__ . '/../resources/objects/task_file_validator.php';
            require_once __DIR__ . '/../resources/objects/ml_service_incremental.php';

            $validator  = new TaskFileValidator();
            $validation = $validator->validateFileForTask($data['task_id'], $upload['path'], $upload['type'] ?? null);

            // Hard block: if validation explicitly fails, delete the uploaded file and reject
            if (!empty($validation['success']) && empty($validation['is_valid'])) {
                // Remove the already-uploaded file to keep storage clean
                $uploadedFilePath = __DIR__ . '/../' . $upload['path'];
                if (file_exists($uploadedFilePath)) {
                    @unlink($uploadedFilePath);
                }
                // Also remove DB record of the file
                try {
                    $db = Database::getInstance();
                    $db->execute("DELETE FROM file_upload_tbl WHERE file_upload_id = ?", [$file_id]);
                } catch (Exception $cleanupEx) {
                    error_log('Failed to clean up invalid file record: ' . $cleanupEx->getMessage());
                }

                $rejectMsg = $validation['recommendation']['message'] ?? 'File does not match task requirements.';
                $reasons   = $validation['recommendation']['reasons'] ?? [];
                if (!empty($reasons)) {
                    $rejectMsg .= ' ' . implode(' ', $reasons);
                }
                throw new Exception($rejectMsg);
            }

            // Validation passed — queue for incremental learning
            if (!empty($validation['success']) && !empty($validation['is_valid'])) {
                $expectedType  = $validation['validation_details']['expected_document_type'] ?? ($validation['task_info']['category'] ?? null);
                $extractedText = $validation['file_info']['preview'] ?? '';
                $fileConf      = $validation['validation_details']['file_confidence'] ?? 0;

                if (!empty($expectedType) && !empty($extractedText)) {
                    $mlInc = new IncrementalMLService();
                    $mlInc->learnFromUpload($file_id, $extractedText, $expectedType, $fileConf / 100);
                }
            }
        } catch (Exception $e) {
            // Re-throw validation/block exceptions; only swallow incremental learning errors
            if (strpos($e->getMessage(), 'Incremental learning') === false) {
                throw $e;
            }
            error_log('Incremental learning hook failed: ' . $e->getMessage());
        }

        $taskManager = new TaskManager();
        $result = $taskManager->submitMemberTaskFile($data);
        if (!empty($result['success'])) {
            $db = Database::getInstance();
            $_st = $db->selectOne("SELECT t.task_title,t.task_description,u.user_id as admin_id FROM task_tbl t JOIN user_tbl u ON u.user_type='admin' WHERE t.task_id=? LIMIT 1", [$data['task_id']]);
            $_mn = $db->selectOne("SELECT CONCAT(p.fname,' ',p.lname) as full_name FROM profile_tbl p JOIN user_tbl u ON u.profile_id=p.profile_id WHERE u.user_id=?", [$memberId]);
            if ($_st && !empty($_st['admin_id'])) { $nm = new NotificationManager(); $nm->createNotification(['user_id'=>$_st['admin_id'],'type'=>'task_submitted','title'=>'Task Submitted','message'=>($_mn['full_name']??'Member')." submitted '{$_st['task_title']}'. {$_st['task_description']}",'related_id'=>$data['task_id']]); $_subs = $db->select("SELECT user_id FROM user_tbl WHERE user_type='subadmin'"); foreach($_subs as $_sub) $nm->createNotification(['user_id'=>$_sub['user_id'],'type'=>'task_submitted','title'=>'Task Submitted','message'=>($_mn['full_name']??'Member')." submitted '{$_st['task_title']}'. {$_st['task_description']}",'related_id'=>$data['task_id']]); }
        }

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
    
}else if($call == 16){
    if(isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $data = [
            'file' => $_FILES['file'],
            'uploaded_by' => $_POST['uploaded_by'] ?? ($_SESSION['user_id'] ?? 0),
            'category_tag' => $_POST['category_tag'] ?? 'Uncategorized',
            'category_score' => $_POST['category_score'] ?? 0,
            'nlp_analysis' => $_POST['nlp_analysis'] ?? null,
            'manual_override' => isset($_POST['manual_override']) && $_POST['manual_override'] == '1',
            'original_category' => $_POST['original_category'] ?? null,
            'file_path' => null
        ];
        try {
            $fileManager = new Main('file_upload_tbl');
            $uploadResult = $fileManager->uploadFile($data);
            $result = $uploadResult;
            if(!$uploadResult['success']) {
                $result = ["success" => false, "msg" => "File upload failed: " . ($uploadResult['error'] ?? 'Unknown error')];
            }
        } catch(Exception $e) {
            $result = ["success" => false, "msg" => $e->getMessage()];
        }
    } else {
        // Mark all notifications as read
        try {
            $memberId = $_SESSION['user_id'] ?? 0;
            Database::getInstance()->execute("UPDATE notifications_tbl SET is_read=1 WHERE user_id=? AND is_read=0", [$memberId]);
            $result = ["status" => "SUCCESS"];
        } catch(Exception $e) {
            $result = ["status" => "ERROR"];
        }
    }
    echo json_encode($result);

}else if($call == 15){
    // Get unread notification count
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $count = (new NotificationManager())->getUnreadCount($memberId);
        $result = ["count" => $count];
    } catch(Exception $e) {
        $result = ["count" => 0];
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
    if(isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $data = [
            'file' => $_FILES['file'],
            'uploaded_by' => $_SESSION['user_id'] ?? 0,
            'category_tag' => $_POST['category_tag'] ?? 'Uncategorized',
            'category_score' => $_POST['category_score'] ?? 0,
            'nlp_analysis' => $_POST['nlp_analysis'] ?? null,
            'manual_override' => isset($_POST['manual_override']) && $_POST['manual_override'] == '1',
            'original_category' => $_POST['original_category'] ?? null,
            'file_path' => null
        ];
        try {
            $fileManager = new Main('file_upload_tbl');
            $result = $fileManager->uploadFile($data);
        } catch(Exception $e) {
            $result = ["success" => false, "msg" => $e->getMessage()];
        }
    } else {
        try {
            $memberId = $_SESSION['user_id'] ?? 0;
            $data = $_POST['DATA'] ?? [];
            $data['updated_by'] = $memberId;
            $fileManager = new FileManager();
            $result = $fileManager->updateMemberFileInfo($data);
        } catch(Exception $e) {
            $result = ["status" => "ERROR", "msg" => $e->getMessage()];
        }
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
    ob_end_clean(); // End and discard the output buffer (removes Content-Type: application/json)
    $memberId = $_SESSION['user_id'] ?? 0;
    $fileId = $_POST['file_id'] ?? 0;
    
    $fileManager = new FileManager();
    $result = $fileManager->getMemberFileDetails($memberId, $fileId);
    
    if(!$result['success'] || empty($result['data'])){
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(["status" => "ERROR", "msg" => "File not found or unauthorized."]);
        exit;
    }
    
    $fileInfo = $result['data'];
    $filePath = '../' . $fileInfo['file_path'];
    
    if(!file_exists($filePath)){
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(["status" => "ERROR", "msg" => "Physical file not found."]);
        exit;
    }
    
    // Clear all previous headers and output
    header('Content-Description: File Transfer');
    header('Content-Type: ' . ($fileInfo['mime_type'] ?? 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . basename($fileInfo['original_filename'] ?: $fileInfo['file_name']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    
    // Output file
    readfile($filePath);
    exit;
    
}else if($call == 21){
    // Get distinct category_tags from files uploaded by this member (for taskCategoryFilter)
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $db = Database::getInstance();
        $categories = $db->select("
            SELECT category_tag, COUNT(file_upload_id) as file_count
            FROM file_upload_tbl
            WHERE uploaded_by = ?
              AND category_tag IS NOT NULL
              AND TRIM(category_tag) != ''
            GROUP BY category_tag
            ORDER BY category_tag ASC
        ", [$memberId]);
        $result = ["status" => "SUCCESS", "data" => $categories ?: []];
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "data" => [], "msg" => $e->getMessage()];
    }
    echo json_encode($result);

}else if($call == 22){
    // Get overdue tasks (active but past deadline, no non-rejected submission)
    $memberId = $_SESSION['user_id'] ?? 0;
    $taskManager = new TaskManager();
    $tasks = $taskManager->getMemberOverdueTasks($memberId);
    echo json_encode(isset($tasks['data']) ? $tasks : ['data' => []], JSON_UNESCAPED_UNICODE);
    exit;

}else if($call === 'get_categories'){
    // Get file categories for member
    try {
        $categories = EntityManager::getAllFileCategories();
        $result = ["status" => "SUCCESS", "data" => $categories];
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage(), "data" => []];
    }
    echo json_encode($result);
    
}else if($call === 'get_nlp_analysis'){
    // Get NLP analysis for a file
    try {
        $fileId = $_POST['file_id'] ?? 0;
        $memberId = $_SESSION['user_id'] ?? 0;
        
        $fileManager = new FileManager();
        $result = $fileManager->getFileNLPAnalysis($fileId, $memberId);
        
        echo json_encode($result);
        
    } catch(Exception $e) {
        error_log("Member get NLP analysis error: " . $e->getMessage());
        echo json_encode(['status' => 'ERROR', 'msg' => $e->getMessage()]);
    }
    
}else if($call === 'nlp_search_files'){
    // NLP-based file search for members - search file contents
    try {
        $searchWord = $_POST['SEARCH_WORD'] ?? '';
        $categoryId = $_POST['CATEGORY_ID'] ?? '';
        $memberId = $_SESSION['user_id'] ?? 0;
        
        // Check if conversational mode is enabled
        $useConversational = !empty($searchWord) && (
            stripos($searchWord, 'show') !== false || 
            stripos($searchWord, 'find') !== false || 
            stripos($searchWord, 'get') !== false ||
            stripos($searchWord, 'all') !== false ||
            str_word_count($searchWord) > 3 // Likely a question/sentence
        );
        
        if ($useConversational && empty($categoryId)) {
            // Use conversational NLP processing
            require_once(__DIR__ . '/../resources/objects/conversational_nlp_service.php');
            $searchData = ConversationalNLPService::processConversationalSearch($searchWord, $memberId);
            
            echo json_encode([
                'status' => 'SUCCESS', 
                'data' => $searchData['results'],
                'message' => ConversationalNLPService::generateResponseMessage($searchData),
                'parsed' => $searchData['parsed_query']
            ]);
        } else {
            // Use traditional keyword search
            $fileManager = new FileManager();
            $results = $fileManager->searchFilesByContent($searchWord, $categoryId, $memberId);
            
            echo json_encode(['status' => 'SUCCESS', 'data' => $results]);
        }
        
    } catch(Exception $e) {
        error_log("Member NLP search error: " . $e->getMessage());
        echo json_encode(['status' => 'ERROR', 'data' => [], 'msg' => $e->getMessage()]);
    }
    
}else if($call === 'validate_task_file'){
    // NEW: Validate if uploaded file matches task requirements
    try {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error');
        }
        
        $taskId = $_POST['task_id'] ?? 0;
        if (!$taskId) {
            throw new Exception('Task ID required');
        }
        
        require_once '../resources/objects/task_file_validator.php';
        
        $file = $_FILES['file'];
        $tmpPath = $file['tmp_name'];
        $mimeType = $file['type'];
        
        $validator = new TaskFileValidator();
        $validationResult = $validator->validateFileForTask($taskId, $tmpPath, $mimeType);
        
        echo json_encode($validationResult);
        
    } catch(Exception $e) {
        error_log("Task file validation error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    


}else if($call === 'conversational_smart_search') {
	$message = trim($_POST["message"] ?? "");
	$history = json_decode($_POST["history"] ?? "[]", true);
	if(strlen($message) < 2) { echo json_encode(["success"=>false,"error"=>"Message too short"]); exit; }
	$msg = strtolower($message);
	$db = Database::getInstance();
	$stopWords = ["the","is","it","a","an","and","or","but","in","on","at","to","for","of","with","by","from","this","that","these","those","was","were","be","been","being","have","has","had","do","does","did","will","would","could","should","may","might","shall","can","are","am","not","no","so","if","then","than","also","just","only","very","too","its","my","your","our","their","his","her","we","they","he","she","i","you","me","us","them","all","each","every","both","few","more","most","other","some","such","find","show","get","search","look","any","files","file","documents","document","give","list","about","related","regarding"];
	$catRows = $db->select("SELECT category_name, category_slug FROM category_tbl");
	$categoryMap = [];
	foreach($catRows as $row) {
		$name = strtolower($row["category_name"]); $slug = strtolower($row["category_slug"]);
		$categoryMap[$name] = $slug; $categoryMap[$slug] = $slug;
		if(substr($name, -1) !== 's') $categoryMap[$name . "s"] = $slug;
		if(substr($slug, -1) !== 's') $categoryMap[$slug . "s"] = $slug;
	}
	$detectedCategory = null;
	foreach($categoryMap as $term=>$cat) { if(strpos($msg, $term) !== false) { $detectedCategory = $cat; break; } }
	$dateFilter = null;
	if(preg_match("/today/", $msg)) $dateFilter = "today";
	if(preg_match("/yesterday/", $msg)) $dateFilter = "yesterday";
	if(preg_match("/this week/", $msg)) $dateFilter = "this_week";
	if(preg_match("/last week/", $msg)) $dateFilter = "last_week";
	if(preg_match("/this month|recent|latest/", $msg)) $dateFilter = "this_month";
	if(preg_match("/last month/", $msg)) $dateFilter = "last_month";
	$uploaderFilter = null;
	if(preg_match("/(?:uploaded?\s+by|by|from)\s+(\w+)/", $msg, $um)) $uploaderFilter = $um[1];
	$mimeFilter = null;
	$mimeMap = ["pdf"=>"pdf","word"=>"word","docx"=>"word","doc"=>"word","text"=>"text","txt"=>"text"];
	foreach($mimeMap as $term=>$mime) { if(preg_match("/\b".$term."\b/", $msg)) { $mimeFilter = $mime; break; } }
	$words = preg_split("/\s+/", $msg);
	$catTerms = array_keys($categoryMap);
	$excludeTerms = array_merge($catTerms, ["today","yesterday","week","month","last","recent","latest","uploaded","upload","pdf","word","docx","doc","text","txt","admin","subadmin","member"]);
	if($uploaderFilter) $excludeTerms[] = $uploaderFilter;
	$keywords = array_values(array_filter($words, function($w) use ($stopWords, $excludeTerms) {
		if(strlen($w) < 3 || in_array($w, $stopWords) || in_array($w, $excludeTerms)) return false;
		$singular = rtrim($w, 's');
		if(in_array($singular, $excludeTerms)) return false;
		return true;
	}));
	$hasFilters = $detectedCategory || $uploaderFilter || $mimeFilter || $dateFilter;
	if(empty($keywords) && !$hasFilters && !empty($history)) {
		$lastUser = array_filter($history, function($h){ return $h["role"]==="user"; });
		$lastMsg = end($lastUser);
		if($lastMsg) { $prevWords = preg_split("/\s+/", strtolower($lastMsg["text"])); $keywords = array_values(array_filter($prevWords, function($w) use ($stopWords) { return strlen($w) >= 3 && !in_array($w, $stopWords); })); }
	}
	$sql = "SELECT f.file_upload_id, f.original_filename, f.file_path, f.file_size, f.mime_type, f.category_tag, f.datetime_uploaded, u.user_name as uploaded_by, n.extracted_text FROM file_upload_tbl f LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id LEFT JOIN file_nlp_analysis_tbl n ON f.file_upload_id = n.file_upload_id WHERE 1=1";
	$params = [];
	if(!empty($keywords)) $sql .= " AND n.extracted_text IS NOT NULL";
	if($detectedCategory) { $catSearch = str_replace("-", " ", $detectedCategory); $sql .= " AND (LOWER(f.category_tag) LIKE ? OR LOWER(f.category_tag) LIKE ?)"; $params[] = "%".$catSearch."%"; $params[] = "%".str_replace(" ","-",$catSearch)."%"; }
	if($dateFilter === "today") $sql .= " AND DATE(f.datetime_uploaded) = CURDATE()";
	elseif($dateFilter === "yesterday") $sql .= " AND DATE(f.datetime_uploaded) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
	elseif($dateFilter === "this_week") $sql .= " AND f.datetime_uploaded >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
	elseif($dateFilter === "last_week") $sql .= " AND f.datetime_uploaded >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND f.datetime_uploaded < DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
	elseif($dateFilter === "this_month") $sql .= " AND f.datetime_uploaded >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
	elseif($dateFilter === "last_month") $sql .= " AND f.datetime_uploaded >= DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND f.datetime_uploaded < DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
	if($uploaderFilter) { $sql .= " AND (LOWER(u.user_name) LIKE ? OR LOWER(u.user_type) LIKE ?)"; $params[] = "%".$uploaderFilter."%"; $params[] = "%".$uploaderFilter."%"; }
	if($mimeFilter) { $sql .= " AND f.mime_type LIKE ?"; $params[] = "%".$mimeFilter."%"; }
	$sql .= " ORDER BY f.datetime_uploaded DESC LIMIT 100";
	try { $allFiles = $db->select($sql, $params); } catch(Exception $e) { echo json_encode(["success"=>false,"error"=>"DB Error: ".$e->getMessage()]); exit; }
	$results = [];
	foreach($allFiles as $file) {
		$text = strtolower(substr($file["extracted_text"] ?? "", 0, 10000));
		$fname = strtolower($file["original_filename"]);
		$matchedWords = [];
		foreach($keywords as $kw) { if(strpos($text, $kw) !== false || strpos($fname, $kw) !== false) $matchedWords[] = $kw; }
		$hasFilters = $detectedCategory || $uploaderFilter || $mimeFilter || $dateFilter;
		if(!empty($matchedWords) || (empty($keywords) && $hasFilters)) {
			$file["matched_keywords"] = $matchedWords;
			$file["match_count"] = empty($keywords) ? 1 : count($matchedWords);
			$file["total_keywords"] = empty($keywords) ? 1 : count($keywords);
			$rawText = $file["extracted_text"] ?? "";
			$idx = !empty($matchedWords) ? (strpos(strtolower($rawText), $matchedWords[0]) ?: 0) : 0;
			$file["snippet"] = substr($rawText, max(0, $idx - 60), 200);
			unset($file["extracted_text"]);
			$results[] = $file;
		}
	}
	usort($results, function($a,$b){ return $b["match_count"] - $a["match_count"]; });
	$results = array_slice($results, 0, 10);
	$count = count($results);
	if($count > 0) {
		$context = [];
		if($detectedCategory) $context[] = "in " . ucwords(str_replace("-"," ",$detectedCategory));
		if($uploaderFilter) $context[] = "uploaded by " . ucfirst($uploaderFilter);
		if($dateFilter) { $dateLabels = ["today"=>"today","yesterday"=>"yesterday","this_week"=>"this week","last_week"=>"last week","this_month"=>"this month","last_month"=>"last month"]; $context[] = $dateLabels[$dateFilter] ?? ""; }
		if($mimeFilter) $context[] = strtoupper($mimeFilter) . " files";
		$ctxStr = !empty($context) ? " (" . implode(", ", $context) . ")" : "";
		$reply = !empty($keywords) ? "Found {$count} file" . ($count>1?"s":"") . "{$ctxStr} matching \"" . implode(", ", $keywords) . "\":" : "Here are {$count} file" . ($count>1?"s":"") . "{$ctxStr}:";
	} else {
		$reply = "I couldn't find any files matching that. Try different keywords, or filter by category, uploader, date (yesterday, this week), or doc type (pdf, docx).";
	}
	echo json_encode(["success"=>true, "reply"=>$reply, "files"=>$results]);
	exit;

}else if($call === 'file_explorer') {
	$action = $_POST["action"] ?? "";
	$db = Database::getInstance();
	if ($action === "get_folders") {
		$folders = $db->select("SELECT c.category_id, c.category_name, COUNT(f.file_upload_id) as file_count FROM category_tbl c LEFT JOIN file_upload_tbl f ON c.category_id = f.category_id GROUP BY c.category_id, c.category_name ORDER BY c.category_name");
		$result = ["success" => true, "folders" => $folders];
	} elseif ($action === "get_files") {
		$categoryId = intval($_POST["category_id"] ?? 0);
		$files = $db->select("SELECT f.file_upload_id, f.original_filename, f.file_name, f.file_path, f.file_size, f.mime_type, f.category_tag, f.datetime_uploaded, f.is_overridden, f.original_category_tag, f.is_signed_document, u.user_name as uploaded_by FROM file_upload_tbl f LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id WHERE f.category_id = ? ORDER BY f.datetime_uploaded DESC", [$categoryId]);
		$result = ["success" => true, "files" => $files];
	} elseif ($action === "search") {
		$query = "%" . ($_POST["query"] ?? "") . "%";
		$overriddenOnly = ($_POST["overridden_only"] ?? "0") === "1";
		$where = "f.original_filename LIKE ?";
		if ($overriddenOnly) $where .= " AND f.is_overridden = 1";
		$files = $db->select("SELECT f.file_upload_id, f.original_filename, f.file_name, f.file_path, f.file_size, f.mime_type, f.category_tag, f.datetime_uploaded, f.is_overridden, f.original_category_tag, f.is_signed_document, u.user_name as uploaded_by FROM file_upload_tbl f LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id WHERE $where ORDER BY f.datetime_uploaded DESC LIMIT 50", [$query]);
		$result = ["success" => true, "files" => $files];
	} elseif ($action === "get_overridden") {
		$files = $db->select("SELECT f.file_upload_id, f.original_filename, f.file_name, f.file_path, f.file_size, f.mime_type, f.category_tag, f.datetime_uploaded, f.is_overridden, f.original_category_tag, f.is_signed_document, u.user_name as uploaded_by FROM file_upload_tbl f LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id WHERE f.is_overridden = 1 ORDER BY f.datetime_uploaded DESC");
		$result = ["success" => true, "files" => $files];
	} elseif ($action === "get_approved") {
		$files = $db->select("SELECT f.file_upload_id, f.original_filename, f.file_name, f.file_path, f.file_size, f.mime_type, f.category_tag, f.datetime_uploaded, f.is_overridden, f.original_category_tag, f.is_signed_document, u.user_name as uploaded_by FROM file_upload_tbl f LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id WHERE f.is_signed_document = 1 ORDER BY f.datetime_uploaded DESC");
		$result = ["success" => true, "files" => $files];
	} elseif ($action === "delete_file") {
		$result = ["success" => false, "error" => "Members cannot delete files"];
	} elseif ($action === "convert_docx") {
		$fileId = intval($_POST["file_id"] ?? 0);
		if(!$fileId) { $result = ["success"=>false,"error"=>"Invalid file ID"]; }
		else {
			$file = $db->selectOne("SELECT file_path, original_filename FROM file_upload_tbl WHERE file_upload_id = ?", [$fileId]);
			if(!$file) { $result = ["success"=>false,"error"=>"File not found"]; }
			else {
				$srcPath = realpath(__DIR__ . '/../' . $file['file_path']);
				$cacheDir = __DIR__ . '/../uploads/files/pdf_cache/';
				if(!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);
				$pdfName = pathinfo($file['file_path'], PATHINFO_FILENAME) . '.pdf';
				$pdfPath = $cacheDir . $pdfName;
				if(file_exists($pdfPath)) {
					$result = ["success"=>true,"pdf_url"=>"uploads/files/pdf_cache/".$pdfName];
				} else {
					$soffice = '';
					if (PHP_OS_FAMILY === 'Windows') {
						$paths = [
							'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
							'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
							'D:\\Program Files\\LibreOffice\\program\\soffice.exe',
						];
						foreach ($paths as $p) { if (file_exists($p)) { $soffice = '"'.$p.'"'; break; } }
					} else {
						if (file_exists('/Applications/LibreOffice.app/Contents/MacOS/soffice')) $soffice = '/Applications/LibreOffice.app/Contents/MacOS/soffice';
						else { $which = trim(shell_exec('which soffice 2>/dev/null') ?? ''); $soffice = $which ?: ''; }
					}
					if (!$soffice) { $result = ["success"=>false,"error"=>"libreoffice_not_found"]; echo json_encode($result); exit; }
					$cmd = $soffice . ' --headless --convert-to pdf --outdir ' . escapeshellarg($cacheDir) . ' ' . escapeshellarg($srcPath) . ' 2>&1';
					exec($cmd, $output, $returnCode);
					if($returnCode === 0 && file_exists($pdfPath)) {
						$result = ["success"=>true,"pdf_url"=>"uploads/files/pdf_cache/".$pdfName];
					} else {
						$result = ["success"=>false,"error"=>"libreoffice_not_found"];
					}
				}
			}
		}
	} else {
		$result = ["success" => false, "error" => "Invalid action"];
	}
	echo json_encode($result);
	exit;

}else if($call === 'reclassify_file') {
	$fileId = intval($_POST['file_id'] ?? 0);
	$newCategory = trim($_POST['new_category'] ?? '');
	if(!$fileId || !$newCategory) { echo json_encode(['success'=>false,'error'=>'Missing parameters']); exit; }
	$db = Database::getInstance();
	$_ovr = $db->selectOne("SELECT setting_value FROM system_settings_tbl WHERE setting_key = 'allow_manual_override'");
	if(($_ovr['setting_value'] ?? '1') !== '1') { echo json_encode(['success'=>false,'error'=>'Manual override is currently disabled']); exit; }
	$_roles = $db->selectOne("SELECT setting_value FROM system_settings_tbl WHERE setting_key = 'override_roles_file_explorer'");
	if(!in_array('member', json_decode($_roles['setting_value'] ?? '[]', true) ?: [])) { echo json_encode(['success'=>false,'error'=>'Members are not allowed to override classification']); exit; }
	$file = $db->selectOne("SELECT * FROM file_upload_tbl WHERE file_upload_id = ?", [$fileId]);
	if(!$file) { echo json_encode(['success'=>false,'error'=>'File not found']); exit; }
	$oldCategory = $file['category_tag'];
	$cat = $db->selectOne("SELECT category_id FROM category_tbl WHERE category_name = ?", [$newCategory]);
	$categoryId = $cat ? $cat['category_id'] : $file['category_id'];
	$originalTag = $file['is_overridden'] ? $file['original_category_tag'] : $oldCategory;
	$isOverridden = ($newCategory !== $originalTag) ? 1 : 0;
	$db->execute("UPDATE file_upload_tbl SET category_id = ?, category_tag = ?, is_overridden = ?, original_category_tag = ? WHERE file_upload_id = ?", [$categoryId, $newCategory, $isOverridden, $originalTag, $fileId]);
	echo json_encode(['success'=>true]);
	exit;

}else{
    echo json_encode(["status" => "ERROR", "msg" => "Invalid call"]);
}
// Direct file download for DataTable/JS (CALL: 20)

?>