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
		require_once '../resources/objects/ml_service.php';
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
			$mlService = new MLClassificationService();
			$prediction = $mlService->predict($extractedText);

			if ($prediction['success']) {
				// Check confidence threshold - if too low, use "Others" category
				$confidence = $prediction['confidence'] ?? 0;
				$confidenceThreshold = 0.50; // 50% minimum confidence
				
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
    
}else{
    echo json_encode(["status" => "ERROR", "msg" => "Invalid call"]);
}
// Direct file download for DataTable/JS (CALL: 20)

?>