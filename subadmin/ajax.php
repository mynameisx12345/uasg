<?php
// Prevent any output before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ob_start();

session_start();
require_once("../resources/class.php");

// Check if this is a download request (GET allowed for downloads)
if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['CALL'])) {
    if ($_GET['CALL'] === 'download') {
        ob_end_clean();
        $fileId = $_GET['file_id'] ?? 0;
        $userId = $_SESSION['user_id'] ?? null;
        try {
            $fileManager = new FileManager();
            $fileManager->downloadFile($fileId, $userId);
            exit;
        } catch(Exception $e) {
            header("Content-Type: application/json");
            echo json_encode(["status" => "ERROR", "msg" => "Download failed: " . $e->getMessage()]);
            exit;
        }
    }
}



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

// Dashboard Statistics (CALL: 100)
if (isset($_POST['CALL']) && $_POST['CALL'] == 100) {
    try {
        $db = Database::getInstance();
        $totalFiles = $db->selectOne("SELECT COUNT(*) as count FROM file_upload_tbl")['count'] ?? 0;
        $totalCategories = $db->selectOne("SELECT COUNT(DISTINCT category_tag) as count FROM file_upload_tbl WHERE category_tag IS NOT NULL")['count'] ?? 0;
        $totalTaskCategories = $db->selectOne("SELECT COUNT(*) as count FROM task_category_tbl")['count'] ?? 0;
        $totalUsers = $db->selectOne("SELECT COUNT(*) as count FROM user_tbl WHERE user_type != 'admin'")['count'] ?? 0;
        $pendingTasks = $db->selectOne("SELECT COUNT(*) as count FROM task_tbl WHERE task_status = 'active' AND task_id NOT IN (SELECT task_id FROM task_submission_tbl)")['count'] ?? 0;
        $activeMembers = $db->selectOne("SELECT COUNT(*) as count FROM user_tbl WHERE user_type = 'student' AND is_active = 1")['count'] ?? 0;
        $totalAdvisers = $db->selectOne("SELECT COUNT(*) as count FROM user_tbl WHERE user_type = 'subadmin' AND is_active = 1")['count'] ?? 0;
        $totalSubmissions = $db->selectOne("SELECT COUNT(*) as count FROM task_tbl t WHERE t.task_status = 'active' AND EXISTS (SELECT 1 FROM task_submission_tbl ts WHERE ts.task_id = t.task_id AND ts.check_status = 'approved')")['count'] ?? 0;
        echo json_encode(['success' => true, 'data' => ['totalFiles' => $totalFiles, 'totalCategories' => $totalCategories, 'totalTaskCategories' => $totalTaskCategories, 'totalUsers' => $totalUsers, 'pendingTasks' => $pendingTasks, 'activeMembers' => $activeMembers, 'totalAdvisers' => $totalAdvisers, 'totalSubmissions' => $totalSubmissions]]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
    }
    exit;
}

// File Statistics by Category (CALL: 101)
if (isset($_POST['CALL']) && $_POST['CALL'] == 101) {
    try {
        $db = Database::getInstance();
        $stats = $db->select("SELECT COALESCE(fu.category_tag, 'Uncategorized') as category, COUNT(fu.file_upload_id) as count, SUM(fu.file_size) as total_size FROM file_upload_tbl fu WHERE fu.category_tag IS NOT NULL GROUP BY fu.category_tag ORDER BY count DESC") ?? [];
        echo json_encode(['success' => true, 'data' => $stats]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
    }
    exit;
}

// User Activity Statistics (CALL: 102)
if (isset($_POST['CALL']) && $_POST['CALL'] == 102) {
    try {
        $db = Database::getInstance();
        $stats = $db->select("SELECT CONCAT(p.fname, ' ', p.lname) as user_name, u.user_type, COUNT(fu.file_upload_id) as uploads, MAX(fu.datetime_uploaded) as last_upload FROM user_tbl u LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id LEFT JOIN file_upload_tbl fu ON u.user_id = fu.uploaded_by WHERE u.user_type != 'admin' AND u.is_active = 1 GROUP BY u.user_id, p.fname, p.lname, u.user_type ORDER BY uploads DESC LIMIT 10") ?? [];
        echo json_encode(['success' => true, 'data' => $stats]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
    }
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

if($call === 'global_search') {
    $q = "%" . trim($_POST["query"] ?? "") . "%";
    $db = Database::getInstance();
    $uid = $_SESSION['user_id'] ?? 0;
    $results = [];
    $files = $db->select("SELECT file_upload_id, original_filename, category_tag FROM file_upload_tbl WHERE original_filename LIKE ? LIMIT 5", [$q]);
    foreach($files as $f) $results[] = ["type"=>"file","id"=>$f["file_upload_id"],"icon"=>"📄","title"=>$f["original_filename"],"meta"=>$f["category_tag"]??"File"];
    if (SubadminPermission::hasPermission($uid, 'user_management', 'view')) {
        $users = $db->select("SELECT u.user_id, u.user_name, u.user_type FROM user_tbl u WHERE u.user_name LIKE ? LIMIT 5", [$q]);
        foreach($users as $u) $results[] = ["type"=>"user","id"=>$u["user_id"],"icon"=>"👤","title"=>$u["user_name"],"meta"=>ucfirst($u["user_type"])];
    }
    if (SubadminPermission::hasPermission($uid, 'task_management', 'view')) {
        $tasks = $db->select("SELECT task_id, task_title FROM task_tbl WHERE task_title LIKE ? LIMIT 5", [$q]);
        foreach($tasks as $t) $results[] = ["type"=>"task","id"=>$t["task_id"],"icon"=>"📋","title"=>$t["task_title"],"meta"=>"Task"];
    }
    echo json_encode(["success"=>true,"results"=>$results]);
    exit;
}

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

if($call === 'get_nlp_analysis'){
    try {
        $fileId = $_POST['file_id'] ?? 0;
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'file_management', 'view')) {
            echo json_encode(['status' => 'ERROR', 'msg' => 'Permission denied']);
            exit;
        }
        
        $fileManager = new FileManager();
        $result = $fileManager->getFileNLPAnalysis($fileId, $userId);
        
        echo json_encode($result);
        
        // Log activity
        SubadminPermission::logActivity($userId, 'view_nlp_analysis', 'file_management', 'Viewed NLP analysis for file ID: ' . $fileId);
        
    } catch(Exception $e) {
        error_log("Subadmin get NLP analysis error: " . $e->getMessage());
        echo json_encode(['status' => 'ERROR', 'msg' => $e->getMessage()]);
    }
    exit;
}

if($call === 'nlp_search_files'){
    try {
        $searchWord = $_POST['SEARCH_WORD'] ?? '';
        $categoryId = $_POST['CATEGORY_ID'] ?? '';
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'file_management', 'view')) {
            echo json_encode(['status' => 'ERROR', 'data' => [], 'msg' => 'Permission denied']);
            exit;
        }
        
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
            $searchData = ConversationalNLPService::processConversationalSearch($searchWord, $userId);
            
            echo json_encode([
                'status' => 'SUCCESS', 
                'data' => $searchData['results'],
                'message' => ConversationalNLPService::generateResponseMessage($searchData),
                'parsed' => $searchData['parsed_query']
            ]);
        } else {
            // Use traditional keyword search
            $fileManager = new FileManager();
            $results = $fileManager->searchFilesByContent($searchWord, $categoryId, $userId);
            
            echo json_encode(['status' => 'SUCCESS', 'data' => $results]);
        }
        
        // Log activity
        SubadminPermission::logActivity($userId, 'nlp_search', 'file_management', 'Performed content search: ' . $searchWord);
        
    } catch(Exception $e) {
        error_log("Subadmin NLP search error: " . $e->getMessage());
        echo json_encode(['status' => 'ERROR', 'data' => [], 'msg' => $e->getMessage()]);
    }
    exit;
}

// Adviser AJAX Calls
if($call == 1){
    // Get dashboard stats
    try {
        $adviserId = $_SESSION['user_id'] ?? 0;
        $dashboardManager = new DashboardManager();
        $result = $dashboardManager->getAdviserDashboardStats($adviserId);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 2){
    // Get recent activity
    try {
        $adviserId = $_SESSION['user_id'] ?? 0;
        $dashboardManager = new DashboardManager();
        $result = $dashboardManager->getAdviserRecentActivity($adviserId);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 3){
    // Change password
    $data = $_POST['DATA'] ?? [];
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        $userManager = new UserManager();
        $result = $userManager->changeMemberPassword($userId, $data);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 4){
    // Get recent tasks
    try {
        $adviserId = $_SESSION['user_id'] ?? 0;
        $taskManager = new TaskManager();
        $result = $taskManager->getAdviserRecentTasks($adviserId);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 5){
    // Get pending submissions
    try {
        $adviserId = $_SESSION['user_id'] ?? 0;
        $taskManager = new TaskManager();
        $managerResult = $taskManager->getAdviserPendingSubmissions($adviserId);
        
        // Extract data array for DataTables format
        if (isset($managerResult['data'])) {
            $result = ["data" => $managerResult['data']];
        } else if (is_array($managerResult)) {
            $result = ["data" => $managerResult];
        } else {
            $result = ["data" => []];
        }
    } catch(Exception $e) {
        $result = ["data" => [], "error" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 6){
    // Get task categories
    try {
        $taskCategories = EntityManager::getAllTaskCategories() ?: [];
        $result = ["data" => $taskCategories];
    } catch(Exception $e) {
        $result = ["data" => [], "error" => $e->getMessage()];
    }
    echo json_encode($result);
    exit;
    
}else if($call == '6_filtered'){
    // Get all tasks with filters
    try {
        $adviserId = $_SESSION['user_id'] ?? 0;
        $filters = [
            'category' => $_POST['category'] ?? '',
            'status' => $_POST['status'] ?? '',
            'date_from' => $_POST['date_from'] ?? '',
            'date_to' => $_POST['date_to'] ?? ''
        ];
        $taskManager = new TaskManager();
        $managerResult = $taskManager->getAdviserTasksWithFilters($adviserId, $filters);
        
        // Extract data array for DataTables format
        if (isset($managerResult['data'])) {
            $result = ["data" => $managerResult['data']];
        } else if (is_array($managerResult)) {
            $result = ["data" => $managerResult];
        } else {
            $result = ["data" => []];
        }
    } catch(Exception $e) {
        $result = ["data" => [], "error" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 7){
    // Create new task
    $data = $_POST['DATA'] ?? [];
    try {
        $data['created_by'] = $_SESSION['user_id'] ?? 0;
        $taskManager = new TaskManager();
        $result = $taskManager->createTask($data);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 8){
    // Update task
    $data = $_POST['DATA'] ?? [];
    try {
        $adviserId = $_SESSION['user_id'] ?? 0;
        $taskManager = new TaskManager();
        $result = $taskManager->updateAdviserTask($adviserId, $data);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 9){
    // Delete task
    $data = $_POST['DATA'] ?? [];
    try {
        $adviserId = $_SESSION['user_id'] ?? 0;
        $taskManager = new TaskManager();
        $result = $taskManager->deleteAdviserTask($adviserId, $data);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 10){
    // Get all submissions with filters
    try {
        $adviserId = $_SESSION['user_id'] ?? 0;
        $filters = [
            'task_id' => $_POST['task_id'] ?? null,
            'status' => $_POST['status'] ?? '',
            'student' => $_POST['student'] ?? ''
        ];
        $taskManager = new TaskManager();
        $managerResult = $taskManager->getAdviserSubmissionsWithFilters($adviserId, $filters);
        
        // Extract data array for DataTables format
        if (isset($managerResult['data'])) {
            $result = ["data" => $managerResult['data']];
        } else if (is_array($managerResult)) {
            $result = ["data" => $managerResult];
        } else {
            $result = ["data" => []];
        }
    } catch(Exception $e) {
        $result = ["data" => [], "error" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 11){
    // Review submission
    $data = $_POST['DATA'] ?? [];
    try {
        $data['reviewed_by'] = $_SESSION['user_id'] ?? 0;
        $taskManager = new TaskManager();
        $result = $taskManager->reviewSubmission($data);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 12){
    // Get accessible files
    try {
        $adviserId = $_SESSION['user_id'] ?? 0;
        $filters = [
            'category_id' => $_POST['category_id'] ?? null,
            'file_type' => $_POST['file_type'] ?? ''
        ];
        $fileManager = new FileManager();
        $managerResult = $fileManager->getAdviserAccessibleFiles($adviserId, $filters);
        
        // Extract data array for DataTables format
        if (isset($managerResult['data'])) {
            $result = ["data" => $managerResult['data']];
        } else if (is_array($managerResult)) {
            $result = ["data" => $managerResult];
        } else {
            $result = ["data" => []];
        }
    } catch(Exception $e) {
        $result = ["data" => [], "error" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 13){
    // Upload file
    $data = $_POST['DATA'] ?? [];
    try {
        $data['uploaded_by'] = $_SESSION['user_id'] ?? 0;
        $fileManager = new FileManager();
        $result = $fileManager->uploadFile($data);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 14){
    // Delete file
    $data = $_POST['DATA'] ?? [];
    try {
        $adviserId = $_SESSION['user_id'] ?? 0;
        $fileManager = new FileManager();
        $result = $fileManager->deleteAdviserFile($adviserId, $data);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 15){
    // Get notifications
    try {
        $adviserId = $_SESSION['user_id'] ?? 0;
        $unreadOnly = $_POST['unread_only'] ?? false;
        $notificationManager = new NotificationManager();
        $result = $notificationManager->getNotifications($adviserId, $unreadOnly);
        $result = ["status" => "SUCCESS", "data" => $result];
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
        try {
            $notificationId = $_POST['notification_id'] ?? 0;
            $notificationManager = new NotificationManager();
            $success = $notificationManager->markAsRead($notificationId);
            $result = ["status" => $success ? "SUCCESS" : "ERROR", "msg" => $success ? "Notification marked as read" : "Failed to mark notification"];
        } catch(Exception $e) {
            $result = ["status" => "ERROR", "msg" => $e->getMessage()];
        }
    }
    echo json_encode($result);
    
}else if($call == 17){
    // Get notification count
    try {
        $adviserId = $_SESSION['user_id'] ?? 0;
        $notificationManager = new NotificationManager();
        $count = $notificationManager->getUnreadCount($adviserId);
        $result = ["status" => "SUCCESS", "count" => $count];
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 18){
    // Get dropdown data
    try {
        $entityManager = new EntityManager();
        $result = [
            "status" => "SUCCESS",
            "data" => [
                "task_categories" => $entityManager::getAllTaskCategories(),
                "file_categories" => $entityManager::getAllFileCategories(),
                "positions" => $entityManager::getAllPositions()
            ]
        ];
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 19){
    // Get subadmin accessible files with filters
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        $filters = $_POST['DATA'] ?? [];
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'file_management', 'view')) {
            throw new Exception("Permission denied");
        }
        
        $fileManager = new FileManager();
        $managerResult = $fileManager->getAdviserAccessibleFiles($userId, $filters);
        
        // Extract data array for DataTables format
        if (isset($managerResult['data'])) {
            $result = ["status" => "SUCCESS", "data" => $managerResult['data']];
        } else if (is_array($managerResult)) {
            $result = ["status" => "SUCCESS", "data" => $managerResult];
        } else {
            $result = ["status" => "SUCCESS", "data" => []];
        }
        
        // Log activity
        SubadminPermission::logActivity($userId, 'view_files', 'file_management', 'Viewed file list');
        
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage(), "data" => []];
    }
    echo json_encode($result);
    
}else if($call == 20){
    // Get file categories
    try {
        $categories = EntityManager::getAllFileCategories();
        $result = ["status" => "SUCCESS", "data" => $categories];
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 21){
    // Upload file with Google NLP auto-categorization and user-confirmed category
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        if (!SubadminPermission::hasPermission($userId, 'file_management', 'create')) {
            throw new Exception("Permission denied: You cannot upload files");
        }
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error");
        }
        $fileManager = new FileManager();
        $fileData = [
            'file' => $_FILES['file'],
            'uploaded_by' => $userId,
            'category_tag' => $_POST['category_tag'] ?? 'Uncategorized',
            'category_score' => $_POST['category_score'] ?? 0,
            'nlp_analysis' => $_POST['nlp_analysis'] ?? null,
            'file_path' => null
        ];
        $uploadResult = $fileManager->uploadFile($fileData);
        $result = $uploadResult;
        if ($uploadResult['status'] === 'SUCCESS') {
            SubadminPermission::logActivity($userId, 'upload_file', 'file_management', 'Uploaded file: ' . $_FILES['file']['name']);
        }
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 22){
    // Update file information
    $data = $_POST['DATA'] ?? [];
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'file_management', 'edit')) {
            throw new Exception("Permission denied: You cannot edit files");
        }
        
        if (empty($data['file_id'])) {
            throw new Exception("File ID is required");
        }
        
        $fileManager = new FileManager();
        $result = $fileManager->updateMemberFileInfo($data);
        
        // Log activity
        if ($result['status'] === 'SUCCESS') {
            SubadminPermission::logActivity($userId, 'update_file', 'file_management', 'Updated file ID: ' . $data['file_id']);
        }
        
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 'create_task'){
    // Create new task
    $data = $_POST['DATA'] ?? [];
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'task_management', 'create')) {
            throw new Exception("Permission denied: You cannot create tasks");
        }
        
        $taskManager = new TaskManager();
        $result = $taskManager->createTask($data);
        
        // Log activity
        if ($result['status'] === 'SUCCESS') {
            SubadminPermission::logActivity($userId, 'create_task', 'task_management', 'Created task: ' . ($data['task_title'] ?? 'Unknown'));
        }
        
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 23){
    // Get file details
    $data = $_POST['DATA'] ?? [];
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'file_management', 'view')) {
            throw new Exception("Permission denied");
        }
        
        if (empty($data['file_id'])) {
            throw new Exception("File ID is required");
        }
        
        $db = Database::getInstance()->getConnection();
        $query = "SELECT fu.*, fu.category_tag as category_name, 
                  fu.mime_type as file_type,
                  CONCAT(p.fname, ' ', p.lname) as uploaded_by_name
                  FROM file_upload_tbl fu
                  LEFT JOIN user_tbl u ON fu.uploaded_by = u.user_id
                  LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
                  WHERE fu.file_upload_id = :file_id";
        
        $stmt = $db->prepare($query);
        $stmt->execute([':file_id' => $data['file_id']]);
        $fileDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($fileDetails) {
            $result = ["status" => "SUCCESS", "data" => $fileDetails];
        } else {
            $result = ["status" => "ERROR", "msg" => "File not found"];
        }
        
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 24){
    // Delete file
    $data = $_POST['DATA'] ?? [];
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'file_management', 'delete')) {
            throw new Exception("Permission denied: You cannot delete files");
        }
        
        if (empty($data['file_id']) || empty($data['reason'])) {
            throw new Exception("File ID and reason are required");
        }
        
        $fileManager = new FileManager();
        $result = $fileManager->deleteFile($data['file_id'], $userId, $data['reason']);
        
        // Log activity
        if ($result['status'] === 'SUCCESS') {
            SubadminPermission::logActivity($userId, 'delete_file', 'file_management', 'Deleted file ID: ' . $data['file_id'] . ' - Reason: ' . $data['reason']);
        }
        
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 40){
    // Get all tasks for DataTable
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'task_management', 'view')) {
            throw new Exception("Permission denied");
        }
        
        $db = Database::getInstance()->getConnection();
        $query = "SELECT t.*, tc.task_category,
                  (SELECT COUNT(*) FROM task_submission_tbl ts WHERE ts.task_id = t.task_id) as submission_count,
                  (SELECT COUNT(*) FROM task_submission_tbl ts WHERE ts.task_id = t.task_id AND LOWER(ts.check_status) = 'approved') as approved_count,
                  (SELECT COUNT(*) FROM task_submission_tbl ts WHERE ts.task_id = t.task_id AND LOWER(ts.check_status) = 'rejected') as rejected_count,
                  CONCAT(p.fname, ' ', p.lname) as assigned_member_name
                  FROM task_tbl t
                  LEFT JOIN task_category_tbl tc ON t.task_category_id = tc.task_category_id
                  LEFT JOIN user_tbl u ON t.assigned_to = u.user_id
                  LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
                  ORDER BY t.task_id DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = ["status" => "SUCCESS", "data" => $tasks];
        
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "data" => [], "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 41){
    // Get all submissions for DataTable
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'task_management', 'view')) {
            throw new Exception("Permission denied");
        }
        
        $db = Database::getInstance()->getConnection();
        $query = "SELECT ts.*, t.task_title, t.task_status, t.task_deadline,
                  (SELECT COUNT(*) FROM task_submission_tbl x WHERE x.task_id = t.task_id) as submission_count,
                  (SELECT COUNT(*) FROM task_submission_tbl x WHERE x.task_id = t.task_id AND LOWER(x.check_status) = 'approved') as approved_count,
                  (SELECT COUNT(*) FROM task_submission_tbl x WHERE x.task_id = t.task_id AND LOWER(x.check_status) = 'rejected') as rejected_count,                  CONCAT(p.fname, ' ', p.lname) as student_name,
                  COALESCE(NULLIF(fu.original_filename,''), fu.file_name) as file_name, fu.original_filename, fu.file_upload_id, fu.datetime_uploaded as submitted_at
                  FROM task_submission_tbl ts
                  LEFT JOIN task_tbl t ON ts.task_id = t.task_id
                  LEFT JOIN user_tbl u ON ts.submitted_by = u.user_id
                  LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
                  LEFT JOIN file_upload_tbl fu ON ts.file_upload_id = fu.file_upload_id
                  ORDER BY fu.datetime_uploaded DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = ["status" => "SUCCESS", "data" => $submissions];
        
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "data" => [], "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 42){
    // Get single task details
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'task_management', 'view')) {
            throw new Exception("Permission denied");
        }
        
        if (empty($_POST['task_id'])) {
            throw new Exception("Task ID is required");
        }
        
        $db = Database::getInstance()->getConnection();
        $query = "SELECT * FROM task_tbl WHERE task_id = :task_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':task_id' => $_POST['task_id']]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($task) {
            $result = ["status" => "SUCCESS", "data" => $task];
        } else {
            $result = ["status" => "ERROR", "msg" => "Task not found"];
        }
        
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 43){
    // Update task
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'task_management', 'edit')) {
            throw new Exception("Permission denied: You cannot edit tasks");
        }
        
        if (empty($_POST['task_id'])) {
            throw new Exception("Task ID is required");
        }
        
        $db = Database::getInstance()->getConnection();
        $query = "UPDATE task_tbl SET 
                  task_category_id = :category_id,
                  task_title = :title,
                  task_description = :description,
                  task_deadline = :deadline
                  WHERE task_id = :task_id";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':category_id' => $_POST['task_category_id'],
            ':title' => $_POST['task_title'],
            ':description' => $_POST['task_description'],
            ':deadline' => $_POST['task_deadline'],
            ':task_id' => $_POST['task_id']
        ]);
        
        // Log activity
        SubadminPermission::logActivity($userId, 'edit_task', 'task_management', 'Updated task ID: ' . $_POST['task_id']);

        // Notify assigned member
        $_t = Database::getInstance()->selectOne("SELECT assigned_to,task_title,task_description FROM task_tbl WHERE task_id=?",[$_POST['task_id']]);
        if ($_t && !empty($_t['assigned_to'])) (new NotificationManager())->createNotification(['user_id'=>$_t['assigned_to'],'type'=>'task_updated','title'=>'Task Updated','message'=>"Task '{$_t['task_title']}' has been updated. {$_t['task_description']}",'related_id'=>$_POST['task_id']]);
        
        $result = ["status" => "SUCCESS", "msg" => "Task updated successfully"];
        
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 44){
    // Delete task
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'task_management', 'delete')) {
            throw new Exception("Permission denied: You cannot delete tasks");
        }
        
        if (empty($_POST['task_id'])) {
            throw new Exception("Task ID is required");
        }
        
        $db = Database::getInstance()->getConnection();

        // Get task info before deletion for notification
        $_td = Database::getInstance()->selectOne("SELECT assigned_to,task_title FROM task_tbl WHERE task_id=?",[$_POST['task_id']]);
        
        // Delete submissions first
        $query = "DELETE FROM task_submission_tbl WHERE task_id = :task_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':task_id' => $_POST['task_id']]);
        
        // Delete task
        $query = "DELETE FROM task_tbl WHERE task_id = :task_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':task_id' => $_POST['task_id']]);
        
        // Log activity
        SubadminPermission::logActivity($userId, 'delete_task', 'task_management', 'Deleted task ID: ' . $_POST['task_id']);
        if ($_td && !empty($_td['assigned_to'])) (new NotificationManager())->createNotification(['user_id'=>$_td['assigned_to'],'type'=>'task_deleted','title'=>'Task Deleted','message'=>"Task '{$_td['task_title']}' has been deleted.",'related_id'=>$_POST['task_id']]);
        
        $result = ["status" => "SUCCESS", "msg" => "Task deleted successfully"];
        
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 45){
    // Get submission details
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'task_management', 'view')) {
            throw new Exception("Permission denied");
        }
        
        if (empty($_POST['submission_id'])) {
            throw new Exception("Submission ID is required");
        }
        
        $db = Database::getInstance()->getConnection();
        $query = "SELECT ts.*, t.task_title, t.task_status, t.task_deadline,
                  (SELECT COUNT(*) FROM task_submission_tbl x WHERE x.task_id = t.task_id) as submission_count,
                  (SELECT COUNT(*) FROM task_submission_tbl x WHERE x.task_id = t.task_id AND LOWER(x.check_status) = 'approved') as approved_count,
                  (SELECT COUNT(*) FROM task_submission_tbl x WHERE x.task_id = t.task_id AND LOWER(x.check_status) = 'rejected') as rejected_count,                  CONCAT(p.fname, ' ', p.lname) as student_name,
                  COALESCE(NULLIF(fu.original_filename,''), fu.file_name) as file_name, fu.original_filename, fu.file_upload_id, fu.datetime_uploaded as submitted_at
                  FROM task_submission_tbl ts
                  LEFT JOIN task_tbl t ON ts.task_id = t.task_id
                  LEFT JOIN user_tbl u ON ts.submitted_by = u.user_id
                  LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
                  LEFT JOIN file_upload_tbl fu ON ts.file_upload_id = fu.file_upload_id
                  WHERE ts.task_submission_id = :submission_id";
        
        $stmt = $db->prepare($query);
        $stmt->execute([':submission_id' => $_POST['submission_id']]);
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($submission) {
            $result = ["status" => "SUCCESS", "data" => $submission];
        } else {
            $result = ["status" => "ERROR", "msg" => "Submission not found"];
        }
        
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 46){
    // Approve submission
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'task_management', 'edit')) {
            throw new Exception("Permission denied: You cannot approve submissions");
        }
        
        $db = Database::getInstance()->getConnection();
        
        if (!empty($_POST['approve_by_task']) && !empty($_POST['task_id'])) {
            $query = "UPDATE task_submission_tbl SET check_status = 'Approved' WHERE task_id = :task_id AND LOWER(check_status) = 'pending'";
            $stmt = $db->prepare($query);
            $stmt->execute([':task_id' => $_POST['task_id']]);
            SubadminPermission::logActivity($userId, 'approve_submission', 'task_management', 'Approved submissions for task ID: ' . $_POST['task_id']);
            $_at = Database::getInstance()->selectOne("SELECT assigned_to,task_title,task_description FROM task_tbl WHERE task_id=?",[$_POST['task_id']]);
            if ($_at && !empty($_at['assigned_to'])) (new NotificationManager())->createNotification(['user_id'=>$_at['assigned_to'],'type'=>'task_approved','title'=>'Task Approved','message'=>"Your submission for '{$_at['task_title']}' has been approved. {$_at['task_description']}",'related_id'=>$_POST['task_id']]);
        } else {
            if (empty($_POST['submission_id'])) {
                throw new Exception("Submission ID is required");
            }
            $query = "UPDATE task_submission_tbl SET check_status = 'Approved' WHERE task_submission_id = :submission_id";
            $stmt = $db->prepare($query);
            $stmt->execute([':submission_id' => $_POST['submission_id']]);
            SubadminPermission::logActivity($userId, 'approve_submission', 'task_management', 'Approved submission ID: ' . $_POST['submission_id']);
            $_as = Database::getInstance()->selectOne("SELECT ts.task_id, t.assigned_to, t.task_title, t.task_description FROM task_submission_tbl ts JOIN task_tbl t ON ts.task_id=t.task_id WHERE ts.task_submission_id=?",[$_POST['submission_id']]);
            if ($_as && !empty($_as['assigned_to'])) (new NotificationManager())->createNotification(['user_id'=>$_as['assigned_to'],'type'=>'task_approved','title'=>'Task Approved','message'=>"Your submission for '{$_as['task_title']}' has been approved. {$_as['task_description']}",'related_id'=>$_as['task_id']]);
        }
        
        $result = ["status" => "SUCCESS", "msg" => "Submission approved successfully"];        
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 'get_members'){
    // Get all members for task assignment
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'task_management', 'view')) {
            throw new Exception("Permission denied");
        }
        
        $db = Database::getInstance()->getConnection();
        $query = "SELECT u.user_id, CONCAT(p.fname, ' ', p.lname) as full_name, pos.position
                  FROM user_tbl u
                  LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
                  LEFT JOIN position_tbl pos ON u.position_id = pos.position_id
                  WHERE u.user_type = 'student' AND u.is_active = 1
                  ORDER BY p.fname, p.lname";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = ["status" => "SUCCESS", "data" => $members];
        
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "data" => [], "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 'transfer_task'){
    // Transfer task to another member
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'task_management', 'edit')) {
            throw new Exception("Permission denied: You cannot transfer tasks");
        }
        
        $taskId = $_POST['task_id'] ?? 0;
        $newAssignedTo = $_POST['new_assigned_to'] ?? 0;
        $transferReason = $_POST['transfer_reason'] ?? '';
        
        if(!$taskId || !$newAssignedTo) {
            throw new Exception("Task ID and new assignee are required");
        }
        
        if(empty($transferReason)) {
            throw new Exception("Transfer reason is required");
        }
        
        $db = Database::getInstance();
        
        // Get current task info
        $task = $db->selectOne("SELECT * FROM task_tbl WHERE task_id = ?", [$taskId]);
        if(!$task) {
            throw new Exception("Task not found");
        }
        
        // Get new assignee info
        $newMember = $db->selectOne("
            SELECT u.user_id, p.fname, p.lname 
            FROM user_tbl u 
            JOIN profile_tbl p ON u.profile_id = p.profile_id 
            WHERE u.user_id = ?
        ", [$newAssignedTo]);
        
        if(!$newMember) {
            throw new Exception("New assignee not found");
        }
        
        // Update task with new assignee and status
        $updated = $db->execute("
            UPDATE task_tbl 
            SET assigned_to = ?
            WHERE task_id = ?
        ", [$newAssignedTo, $taskId]);
        
        if($updated) {
            // Log the transfer
            $logData = json_encode([
                'task_id' => $taskId,
                'task_title' => $task['task_title'],
                'old_assigned_to' => $task['assigned_to'],
                'new_assigned_to' => $newAssignedTo,
                'new_assignee_name' => $newMember['fname'] . ' ' . $newMember['lname'],
                'transfer_reason' => $transferReason,
                'transferred_by' => $userId,
                'transferred_at' => date('Y-m-d H:i:s')
            ]);
            
		$db->execute("
                INSERT INTO deleted_record_tbl (data_deleted, reason_for_deletion, table_origin, datetime_deleted)
                VALUES (?, ?, 'task_tbl', NOW())
            ", [$logData, 'Task Transfer: ' . $transferReason]);
            
            // Send notifications
            $notificationManager = new NotificationManager();
            $newMemberFullName = $newMember['fname'] . ' ' . $newMember['lname'];
            
            // Notify the old assignee that their task was transferred away
            if (!empty($task['assigned_to'])) {
                $notificationManager->createNotification([
                    'user_id' => $task['assigned_to'],
                    'type' => 'task_transferred',
                    'title' => 'Task Transferred Away',
                    'message' => "Task '{$task['task_title']}' has been transferred to {$newMemberFullName}. Reason: {$transferReason}",
                    'related_id' => $taskId
                ]);
            }
            
            // Notify the new assignee that a task was transferred to them
            $notificationManager->createNotification([
                'user_id' => $newAssignedTo,
                'type' => 'task_transferred',
                'title' => 'Task Transferred to You',
                'message' => "Task '{$task['task_title']}' has been transferred to you. Reason: {$transferReason}",
                'related_id' => $taskId
            ]);
            
            // Log activity
            SubadminPermission::logActivity($userId, 'transfer_task', 'task_management', 
                "Transferred task ID {$taskId} to " . $newMemberFullName);
            
            $result = ["status" => "SUCCESS", "msg" => "Task transferred successfully to " . $newMemberFullName];
        } else {
            throw new Exception("Failed to transfer task");
        }
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);}else if($call == 'close_cancel_task'){
    // Close or cancel a task
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'task_management', 'edit')) {
            throw new Exception("Permission denied: You cannot close/cancel tasks");
        }
        
        $taskId = $_POST['task_id'] ?? 0;
        $action = $_POST['action'] ?? ''; // 'close' or 'cancel'
        $reason = $_POST['reason'] ?? '';
        
        if(!$taskId) {
            throw new Exception("Task ID is required");
        }
        
        if(!in_array($action, ['close', 'cancel'])) {
            throw new Exception("Invalid action. Must be 'close' or 'cancel'");
        }
        
        if(empty($reason)) {
            throw new Exception("Reason is required");
        }
        
        $db = Database::getInstance();
        
        // Get task info
        $task = $db->selectOne("SELECT * FROM task_tbl WHERE task_id = ?", [$taskId]);
        if(!$task) {
            throw new Exception("Task not found");
        }
        
        // Determine new status
        $newStatus = $action === 'close' ? 'closed' : 'cancelled';
        
        // Update task status
        $updated = $db->execute("
            UPDATE task_tbl 
            SET task_status = ?
            WHERE task_id = ?
        ", [$newStatus, $taskId]);
        
        if($updated) {
            // Log the action
            $logData = json_encode([
                'task_id' => $taskId,
                'task_title' => $task['task_title'],
                'task_category_id' => $task['task_category_id'],
                'assigned_to' => $task['assigned_to'],
                'action' => $action,
                'new_status' => $newStatus,
                'reason' => $reason,
                'actioned_by' => $userId,
                'actioned_at' => date('Y-m-d H:i:s')
            ]);
            
            $db->execute("
                INSERT INTO deleted_record_tbl (data_deleted, reason_for_deletion, table_origin, datetime_deleted)
                VALUES (?, ?, 'task_tbl', NOW())
            ", [$logData, ucfirst($action) . ' Task: ' . $reason]);
            
            // Log activity
            SubadminPermission::logActivity($userId, $action . '_task', 'task_management', 
                ucfirst($action) . "d task ID {$taskId}: {$reason}");
            
            $actionText = $action === 'close' ? 'closed' : 'cancelled';
            $result = ["status" => "SUCCESS", "msg" => "Task {$actionText} successfully"];
            if (!empty($task['assigned_to'])) (new NotificationManager())->createNotification(['user_id'=>$task['assigned_to'],'type'=>'task_'.$action,'title'=>'Task '.ucfirst($actionText),'message'=>"Task '{$task['task_title']}' has been {$actionText}. Reason: {$reason}",'related_id'=>$taskId]);
        } else {
            throw new Exception("Failed to update task status");
        }
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    


}else if($call == 57){
    // View task details with submissions
    $taskId = $_POST['task_id'] ?? 0;
    try {
        $db = Database::getInstance();
        $task = $db->selectOne("SELECT t.*, tc.task_category, CONCAT(p.fname, ' ', p.lname) as assigned_member_name FROM task_tbl t LEFT JOIN task_category_tbl tc ON t.task_category_id = tc.task_category_id LEFT JOIN user_tbl u ON t.assigned_to = u.user_id LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id WHERE t.task_id = ?", [$taskId]);
        if (!$task) { echo json_encode(['status' => 'ERROR', 'msg' => 'Task not found']); exit; }
        $submissions = $db->select("SELECT ts.task_submission_id, ts.check_status, COALESCE(NULLIF(f.original_filename, ''), f.file_name) as file_name, f.file_upload_id, f.datetime_uploaded as submitted_at, CONCAT(sp.fname, ' ', sp.lname) as student_name FROM task_submission_tbl ts INNER JOIN file_upload_tbl f ON ts.file_upload_id = f.file_upload_id INNER JOIN user_tbl su ON ts.submitted_by = su.user_id INNER JOIN profile_tbl sp ON su.profile_id = sp.profile_id WHERE ts.task_id = ? ORDER BY f.datetime_uploaded DESC", [$taskId]) ?: [];
        $task['submissions'] = $submissions;
        echo json_encode(['status' => 'SUCCESS', 'data' => $task]);
    } catch(Exception $e) {
        echo json_encode(['status' => 'ERROR', 'msg' => $e->getMessage()]);
    }
    exit;

}else if($call == 63){
    // Get members for task assignment
    try {
        $db = Database::getInstance();
        $members = $db->select("SELECT u.user_id, CONCAT(p.fname, ' ', p.lname) as full_name, pos.position FROM user_tbl u INNER JOIN profile_tbl p ON u.profile_id = p.profile_id INNER JOIN position_tbl pos ON u.position_id = pos.position_id WHERE u.user_type = 'student' ORDER BY p.fname, p.lname") ?: [];
        echo json_encode(["status" => "SUCCESS", "data" => $members]);
    } catch(Exception $e) {
        echo json_encode(["status" => "ERROR", "msg" => $e->getMessage(), "data" => []]);
    }
    exit;

}else if($call == 'extend_deadline'){
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        $taskId = intval($_POST['task_id'] ?? 0);
        $newDeadline = trim($_POST['new_deadline'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        if (!$taskId || !$newDeadline) throw new Exception("Task ID and new deadline are required");
        if (empty($reason)) throw new Exception("Reason is required");
        if ($newDeadline <= date('Y-m-d')) throw new Exception("New deadline must be in the future");
        $db = Database::getInstance();
        $task = $db->selectOne("SELECT * FROM task_tbl WHERE task_id = ?", [$taskId]);
        if (!$task) throw new Exception("Task not found");
        $db->execute("UPDATE task_tbl SET task_deadline = ?, task_status = 'active' WHERE task_id = ?", [$newDeadline, $taskId]);
        $logData = json_encode(['task_id'=>$taskId,'task_title'=>$task['task_title'],'old_deadline'=>$task['task_deadline'],'new_deadline'=>$newDeadline,'reason'=>$reason,'extended_by'=>$userId,'extended_at'=>date('Y-m-d H:i:s')]);
        $db->execute("INSERT INTO deleted_record_tbl (data_deleted, reason_for_deletion, table_origin, datetime_deleted) VALUES (?, ?, 'task_tbl', NOW())", [$logData, 'Deadline Extended: ' . $reason]);
        if (!empty($task['assigned_to'])) {
            $notificationManager = new NotificationManager();
            $notificationManager->createNotification(['user_id'=>$task['assigned_to'],'type'=>'task_deadline_extended','title'=>'Task Deadline Extended','message'=>"The deadline for '{$task['task_title']}' has been extended to {$newDeadline}. Reason: {$reason}",'related_id'=>$taskId]);
        }
        echo json_encode(["status" => "SUCCESS", "msg" => "Deadline extended to {$newDeadline}"]);
    } catch(Exception $e) {
        echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
    }
    exit;

}else if($call == 'reject_submission_by_task'){
    $taskId = $_POST['task_id'] ?? 0;
    try {
        $db = Database::getInstance();
        $_rj = $db->selectOne("SELECT t.assigned_to, t.task_title, t.task_description FROM task_tbl t WHERE t.task_id = ?", [$taskId]);
        $db->execute("DELETE FROM task_submission_tbl WHERE task_id = ? AND LOWER(check_status) = 'pending'", [$taskId]);
        if ($_rj && !empty($_rj['assigned_to'])) {
            (new NotificationManager())->createNotification(['user_id'=>$_rj['assigned_to'],'type'=>'submission_rejected','title'=>'Submission Moved to Pending','message'=>"Your submission for '{$_rj['task_title']}' has been moved back to pending.",'related_id'=>$taskId]);
        }
        echo json_encode(['status' => 'SUCCESS', 'msg' => 'Submission rejected, task moved to pending']);
    } catch(Exception $e) {
        echo json_encode(['status' => 'ERROR', 'msg' => $e->getMessage()]);
    }
    exit;

}else if($call == 'revert_to_pending'){
    $taskId = $_POST['task_id'] ?? 0;
    try {
        $db = Database::getInstance();
        $_rv = $db->selectOne("SELECT t.assigned_to, t.task_title FROM task_tbl t WHERE t.task_id = ?", [$taskId]);
        $db->execute("UPDATE task_submission_tbl SET check_status = 'pending' WHERE task_id = ? AND LOWER(check_status) = 'approved'", [$taskId]);
        if ($_rv && !empty($_rv['assigned_to'])) {
            (new NotificationManager())->createNotification(['user_id'=>$_rv['assigned_to'],'type'=>'task_reverted','title'=>'Task Moved to Awaiting Review','message'=>"Task '{$_rv['task_title']}' has been moved back to awaiting review.",'related_id'=>$taskId]);
        }
        echo json_encode(['status' => 'SUCCESS', 'msg' => 'Reverted to awaiting review']);
    } catch(Exception $e) {
        echo json_encode(['status' => 'ERROR', 'msg' => $e->getMessage()]);
    }
    exit;

}else if($call === 'get_categories'){
    try {
        $categories = EntityManager::getAllFileCategories();
        echo json_encode(["status" => "SUCCESS", "data" => $categories]);
    } catch(Exception $e) {
        echo json_encode(["status" => "ERROR", "msg" => $e->getMessage(), "data" => []]);
    }
    exit;

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
        $name = strtolower($row["category_name"]);
        $slug = strtolower($row["category_slug"]);
        $categoryMap[$name] = $slug;
        $categoryMap[$slug] = $slug;
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
    if(preg_match("/(?:uploaded?\s+by|by|from)\s+(\w+)/", $msg, $um)) { $uploaderFilter = $um[1]; }

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
        if($lastMsg) {
            $prevWords = preg_split("/\s+/", strtolower($lastMsg["text"]));
            $keywords = array_values(array_filter($prevWords, function($w) use ($stopWords) { return strlen($w) >= 3 && !in_array($w, $stopWords); }));
        }
    }

    $sql = "SELECT f.file_upload_id, f.original_filename, f.file_path, f.file_size, f.mime_type, f.category_tag, f.datetime_uploaded, u.user_name as uploaded_by, n.extracted_text FROM file_upload_tbl f LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id LEFT JOIN file_nlp_analysis_tbl n ON f.file_upload_id = n.file_upload_id WHERE 1=1";
    $params = [];
    if(!empty($keywords)) { $sql .= " AND n.extracted_text IS NOT NULL"; }

    if($detectedCategory) {
        $catSearch = str_replace("-", " ", $detectedCategory);
        $sql .= " AND (LOWER(f.category_tag) LIKE ? OR LOWER(f.category_tag) LIKE ?)";
        $params[] = "%".$catSearch."%";
        $params[] = "%".str_replace(" ","-",$catSearch)."%";
    }
    if($dateFilter === "today") { $sql .= " AND DATE(f.datetime_uploaded) = CURDATE()"; }
    elseif($dateFilter === "yesterday") { $sql .= " AND DATE(f.datetime_uploaded) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)"; }
    elseif($dateFilter === "this_week") { $sql .= " AND f.datetime_uploaded >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"; }
    elseif($dateFilter === "last_week") { $sql .= " AND f.datetime_uploaded >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND f.datetime_uploaded < DATE_SUB(CURDATE(), INTERVAL 7 DAY)"; }
    elseif($dateFilter === "this_month") { $sql .= " AND f.datetime_uploaded >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"; }
    elseif($dateFilter === "last_month") { $sql .= " AND f.datetime_uploaded >= DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND f.datetime_uploaded < DATE_SUB(CURDATE(), INTERVAL 30 DAY)"; }
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
            $start = max(0, $idx - 60);
            $file["snippet"] = substr($rawText, $start, 200);
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
        if(!empty($keywords)) { $reply = "Found {$count} file" . ($count>1?"s":"") . "{$ctxStr} matching \"" . implode(", ", $keywords) . "\":"; }
        else { $reply = "Here are {$count} file" . ($count>1?"s":"") . "{$ctxStr}:"; }
    } else {
        $reply = "I couldn't find any files matching that. Try different keywords, or filter by category, uploader, date (yesterday, this week), or doc type (pdf, docx).";
    }

    echo json_encode(["success"=>true, "reply"=>$reply, "files"=>$results, "debug"=>["category"=>$detectedCategory,"uploader"=>$uploaderFilter,"date"=>$dateFilter,"mime"=>$mimeFilter,"keywords"=>$keywords]]);
    exit;

}else if($call === 'file_explorer') {
    $action = $_POST["action"] ?? "";
    $db = Database::getInstance();
    if ($action === "get_folders") {
        $folders = $db->select("SELECT c.category_id, c.category_name, COUNT(f.file_upload_id) as file_count FROM category_tbl c LEFT JOIN file_upload_tbl f ON c.category_id = f.category_id GROUP BY c.category_id, c.category_name ORDER BY c.category_name");
        echo json_encode(["success" => true, "folders" => $folders]);
    } elseif ($action === "get_files") {
        $categoryId = intval($_POST["category_id"] ?? 0);
        $files = $db->select("SELECT f.file_upload_id, f.original_filename, f.file_name, f.file_path, f.file_size, f.mime_type, f.category_tag, f.datetime_uploaded, f.is_overridden, f.original_category_tag, f.is_signed_document, u.user_name as uploaded_by FROM file_upload_tbl f LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id WHERE f.category_id = ? ORDER BY f.datetime_uploaded DESC", [$categoryId]);
        echo json_encode(["success" => true, "files" => $files]);
    } elseif ($action === "search") {
        $query = "%" . ($_POST["query"] ?? "") . "%";
        $overriddenOnly = ($_POST["overridden_only"] ?? "0") === "1";
        $where = "f.original_filename LIKE ?";
        if ($overriddenOnly) $where .= " AND f.is_overridden = 1";
        $files = $db->select("SELECT f.file_upload_id, f.original_filename, f.file_name, f.file_path, f.file_size, f.mime_type, f.category_tag, f.datetime_uploaded, f.is_overridden, f.original_category_tag, f.is_signed_document, u.user_name as uploaded_by FROM file_upload_tbl f LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id WHERE $where ORDER BY f.datetime_uploaded DESC LIMIT 50", [$query]);
        echo json_encode(["success" => true, "files" => $files]);
    } elseif ($action === "get_overridden") {
        $files = $db->select("SELECT f.file_upload_id, f.original_filename, f.file_name, f.file_path, f.file_size, f.mime_type, f.category_tag, f.datetime_uploaded, f.is_overridden, f.original_category_tag, f.is_signed_document, u.user_name as uploaded_by FROM file_upload_tbl f LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id WHERE f.is_overridden = 1 ORDER BY f.datetime_uploaded DESC");
        echo json_encode(["success" => true, "files" => $files]);
    } elseif ($action === "get_approved") {
        $files = $db->select("SELECT f.file_upload_id, f.original_filename, f.file_name, f.file_path, f.file_size, f.mime_type, f.category_tag, f.datetime_uploaded, f.is_overridden, f.original_category_tag, f.is_signed_document, u.user_name as uploaded_by FROM file_upload_tbl f LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id WHERE f.is_signed_document = 1 ORDER BY f.datetime_uploaded DESC");
        echo json_encode(["success" => true, "files" => $files]);
    } elseif ($action === "delete_file") {
        $fileId = intval($_POST["file_id"] ?? 0);
        $db->execute("DELETE FROM file_upload_tbl WHERE file_upload_id = ?", [$fileId]);
        echo json_encode(["success" => true]);
    } elseif ($action === "convert_docx") {
        $fileId = intval($_POST["file_id"] ?? 0);
        if(!$fileId) { echo json_encode(["success"=>false,"error"=>"Invalid file ID"]); }
        else {
            $file = $db->selectOne("SELECT file_path, original_filename FROM file_upload_tbl WHERE file_upload_id = ?", [$fileId]);
            if(!$file) { echo json_encode(["success"=>false,"error"=>"File not found"]); }
            else {
                $srcPath = realpath(__DIR__ . '/../' . $file['file_path']);
                $cacheDir = __DIR__ . '/../uploads/files/pdf_cache/';
                if(!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);
                $pdfName = pathinfo($file['file_path'], PATHINFO_FILENAME) . '.pdf';
                $pdfPath = $cacheDir . $pdfName;
                if(file_exists($pdfPath)) {
                    echo json_encode(["success"=>true,"pdf_url"=>"uploads/files/pdf_cache/".$pdfName]);
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
                    if (!$soffice) { echo json_encode(["success"=>false,"error"=>"libreoffice_not_found"]); exit; }
                    $cmd = $soffice . ' --headless --convert-to pdf --outdir ' . escapeshellarg($cacheDir) . ' ' . escapeshellarg($srcPath) . ' 2>&1';
                    exec($cmd, $output, $returnCode);
                    if($returnCode === 0 && file_exists($pdfPath)) {
                        echo json_encode(["success"=>true,"pdf_url"=>"uploads/files/pdf_cache/".$pdfName]);
                    } else {
                        echo json_encode(["success"=>false,"error"=>"libreoffice_not_found"]);
                    }
                }
            }
        }
    } else {
        echo json_encode(["success" => false, "error" => "Invalid action"]);
    }
    exit;

}else if($call === 'reclassify_file') {
    $fileId = intval($_POST['file_id'] ?? 0);
    $newCategory = trim($_POST['new_category'] ?? '');
    if(!$fileId || !$newCategory) { echo json_encode(['success'=>false,'error'=>'Missing parameters']); exit; }
    $db = Database::getInstance();
    $_ovr = $db->selectOne("SELECT setting_value FROM system_settings_tbl WHERE setting_key = 'allow_manual_override'");
    if(($_ovr['setting_value'] ?? '1') !== '1') { echo json_encode(['success'=>false,'error'=>'Manual override is currently disabled']); exit; }
    $_roles = $db->selectOne("SELECT setting_value FROM system_settings_tbl WHERE setting_key = 'override_roles_file_explorer'");
    if(!in_array('subadmin', json_decode($_roles['setting_value'] ?? '[]', true) ?: [])) { echo json_encode(['success'=>false,'error'=>'Sub-admins are not allowed to override classification']); exit; }
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

}else if($call == 68){
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        if (!SubadminPermission::hasPermission($userId, 'user_management', 'view')) {
            throw new Exception("Permission denied");
        }
        $userManager = new UserManager();
        $users = $userManager->getAllUsers();
        echo json_encode(["data" => $users ?: []]);
    } catch(Exception $e) {
        echo json_encode(["data" => []]);
    }
}else if($call == 69){
    // Mark all notifications as read
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        Database::getInstance()->execute("UPDATE notifications_tbl SET is_read=1 WHERE user_id=? AND is_read=0", [$userId]);
        echo json_encode(["status" => "SUCCESS"]);
    } catch(Exception $e) {
        echo json_encode(["status" => "ERROR"]);
    }
}else{
    echo json_encode(["status" => "ERROR", "msg" => "Invalid call"]);
}

// Handle file download (GET request)
if (isset($_GET['CALL']) && $_GET['CALL'] == 25 && isset($_GET['file_id'])) {
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'file_management', 'view')) {
            throw new Exception("Permission denied");
        }
        
        $fileId = $_GET['file_id'];
        $db = Database::getInstance()->getConnection();
        
        $query = "SELECT * FROM file_upload_tbl WHERE file_upload_id = :file_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':file_id' => $fileId]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$file) {
            throw new Exception("File not found");
        }
        
        $filePath = '../' . $file['file_path'];
        
        if (!file_exists($filePath)) {
            throw new Exception("File does not exist on server");
        }
        
        $displayName = !empty($file['original_filename']) ? $file['original_filename'] : $file['file_name'];
        // Log download activity
        SubadminPermission::logActivity($userId, 'download_file', 'file_management', 'Downloaded file: ' . $displayName);
        
        // Set headers for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $displayName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // Output file
        readfile($filePath);
        exit;
        
    } catch(Exception $e) {
        echo "Download error: " . $e->getMessage();
        exit;
    }
}
/*
            
        case 'get_recent_activity':
            // Get recent activity for dashboard table
            $taskManager = new TaskManager();
            $submissions = $taskManager->getSubmissions();
            
            // Sort by submission date and limit to 10 recent activities
            usort($submissions, function($a, $b) {
                return strtotime($b['submission_date']) - strtotime($a['submission_date']);
            });
            
            $recentActivity = [];
            foreach(array_slice($submissions, 0, 10) as $submission) {
                $recentActivity[] = [
                    'date' => $submission['submission_date'],
                    'student' => $submission['fname'] . ' ' . $submission['lname'],
                    'action' => 'Submitted',
                    'task' => $submission['task_title'],
                    'status' => ucfirst($submission['check_status'])
                ];
            }
            
            $result = [
                "status" => "SUCCESS",
                "data" => $recentActivity
            ];
            break;
            
        case 'change_password':
            // Change user password
            $data = $_POST['DATA'] ?? [];
            $currentPassword = $data['current_password'] ?? '';
            $newPassword = $data['new_password'] ?? '';
            $confirmPassword = $data['confirm_password'] ?? '';
            
            if(empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $result = ["status" => "ERROR", "msg" => "All password fields are required"];
                break;
            }
            
            if($newPassword !== $confirmPassword) {
                $result = ["status" => "ERROR", "msg" => "New passwords do not match"];
                break;
            }
            
            if(strlen($newPassword) < 6) {
                $result = ["status" => "ERROR", "msg" => "Password must be at least 6 characters long"];
                break;
            }
            
            // Verify current password and update
            $userManager = new UserManager();
            $success = $userManager->changePassword($userId, $currentPassword, $newPassword);
            
            if($success) {
                $result = ["status" => "SUCCESS", "msg" => "Password changed successfully"];
            } else {
                $result = ["status" => "ERROR", "msg" => "Current password is incorrect"];
            }
            break;
            
        case 'get_recent_tasks':
            // Get recent tasks with limited results
            $taskManager = new TaskManager();
            $tasks = $taskManager->getTasks();
            
            // Sort by deadline and limit to 5
            usort($tasks, function($a, $b) {
                return strtotime($a['task_deadline']) - strtotime($b['task_deadline']);
            });
            
            $result = [
                "status" => "SUCCESS",
                "data" => array_slice($tasks, 0, 5)
            ];
            break;
            
        case 'get_pending_submissions':
            // Get pending submissions only
            $taskManager = new TaskManager();
            $submissions = $taskManager->getSubmissions();
            
            $pendingSubmissions = array_filter($submissions, function($submission) {
                return $submission['check_status'] === 'pending';
            });
            
            $result = [
                "status" => "SUCCESS", 
                "data" => array_values($pendingSubmissions)
            ];
            break;
            
        case 'get_all_tasks':
            // Get all tasks with filters
            $taskManager = new TaskManager();
            $category = $_POST['category'] ?? '';
            $status = $_POST['status'] ?? '';
            $dateFrom = $_POST['date_from'] ?? '';
            $dateTo = $_POST['date_to'] ?? '';
            
            $tasks = $taskManager->getTasks();
            
            // Apply filters
            if(!empty($category)) {
                $tasks = array_filter($tasks, function($task) use ($category) {
                    return $task['task_category_id'] == $category;
                });
            }
            
            if(!empty($dateFrom)) {
                $tasks = array_filter($tasks, function($task) use ($dateFrom) {
                    return strtotime($task['task_deadline']) >= strtotime($dateFrom);
                });
            }
            
            if(!empty($dateTo)) {
                $tasks = array_filter($tasks, function($task) use ($dateTo) {
                    return strtotime($task['task_deadline']) <= strtotime($dateTo);
                });
            }
            
            $result = [
                "status" => "SUCCESS",
                "data" => array_values($tasks)
            ];
            break;
            
        case 'create_task':
            // Create new task
            $data = $_POST['DATA'] ?? [];
            $taskManager = new TaskManager();
            $result = $taskManager->createTask($data);
            break;
            
        case 'update_task':
            // Update existing task
            $data = $_POST['DATA'] ?? [];
            // Implementation for task update
            $result = ["status" => "SUCCESS", "msg" => "Task updated successfully"];
            break;
            
        case 'delete_task':
            // Delete task
            $data = $_POST['DATA'] ?? [];
            $taskManager = new TaskManager();
            $result = $taskManager->deleteTask($data['task_id'], $data['reason']);
            break;
            
        case 'get_all_submissions':
            // Get all submissions with filters
            $taskManager = new TaskManager();
            $taskId = $_POST['task_id'] ?? null;
            $status = $_POST['status'] ?? '';
            $student = $_POST['student'] ?? '';
            
            $submissions = $taskManager->getSubmissions($taskId);
            
            // Apply filters
            if(!empty($status)) {
                $submissions = array_filter($submissions, function($submission) use ($status) {
                    return $submission['check_status'] === $status;
                });
            }
            
            if(!empty($student)) {
                $submissions = array_filter($submissions, function($submission) use ($student) {
                    $fullName = $submission['fname'] . ' ' . $submission['lname'];
                    return stripos($fullName, $student) !== false;
                });
            }
            
            $result = [
                "status" => "SUCCESS",
                "data" => array_values($submissions)
            ];
            break;
            
        case 'review_submission':
            // Review submission (approve/reject)
            $data = $_POST['DATA'] ?? [];
            $taskManager = new TaskManager();
            $result = $taskManager->reviewSubmission($data);
            break;
            
        case 'get_accessible_files':
            // Get files accessible to adviser
            $fileManager = new FileManager();
            $categoryId = $_POST['category_id'] ?? null;
            $fileType = $_POST['file_type'] ?? '';
            
            if($categoryId) {
                $files = $fileManager->getFilesByCategory($categoryId, $userId);
            } else {
                $files = $fileManager->getAllFiles($userId);
            }
            
            // Filter by file type if specified
            if(!empty($fileType)) {
                $files = array_filter($files, function($file) use ($fileType) {
                    return stripos($file['mime_type'], $fileType) !== false;
                });
            }
            
            $result = [
                "status" => "SUCCESS",
                "data" => array_values($files)
            ];
            break;
            
        case 'upload_file':
            // Upload file
            $data = $_POST['DATA'] ?? [];
            $data['uploaded_by'] = $userId;
            $fileManager = new FileManager();
            $result = $fileManager->uploadFile($data);
            break;
            
        case 'delete_file':
            // Delete file
            $data = $_POST['DATA'] ?? [];
            $fileManager = new FileManager();
            $result = $fileManager->deleteFile($data['file_id'], $userId, $data['reason']);
            break;
            
        case 'get_notifications':
            // Get notifications
            $unreadOnly = $_POST['unread_only'] ?? false;
            $notificationManager = new NotificationManager();
            $notifications = $notificationManager->getNotifications($userId, $unreadOnly);
            
            $result = [
                "status" => "SUCCESS",
                "data" => $notifications
            ];
            break;
            
        case 'mark_notification_read':
            // Mark notification as read
            $notificationId = $_POST['notification_id'] ?? 0;
            $notificationManager = new NotificationManager();
            $success = $notificationManager->markAsRead($notificationId);
            
            $result = [
                "status" => $success ? "SUCCESS" : "ERROR",
                "msg" => $success ? "Notification marked as read" : "Failed to mark notification"
            ];
            break;
            
        case 'get_notification_count':
            // Get unread notification count
            $notificationManager = new NotificationManager();
            $count = $notificationManager->getUnreadCount($userId);
            
            $result = [
                "status" => "SUCCESS",
                "count" => $count
            ];
            break;
            
        case 'get_dropdowns':
            // Get dropdown data for filters
            $taskCategories = EntityManager::getAllTaskCategories();
            $fileCategories = EntityManager::getAllFileCategories();
            $taskManager = new TaskManager();
            $tasks = $taskManager->getTasks();
            
            $result = [
                "status" => "SUCCESS",
                "data" => [
                    "task_categories" => $taskCategories,
                    "file_categories" => $fileCategories,
                    "tasks" => $tasks
                ]
            ];
            break;
            
        case 'get_my_tasks':
            // Get tasks created by this adviser
            $taskManager = new TaskManager();
            $tasks = $taskManager->getTasks(); // Get all tasks
            
            $result = [
                "status" => "SUCCESS",
                "data" => $tasks
            ];
            break;
        
        case 'get_all_files':
            // Get all uploaded files (permission-checked)
            try {
                $userId = $_SESSION['user_id'] ?? 0;
                
                // Check permission
                if (!SubadminPermission::hasPermission($userId, 'file_management', 'view')) {
                    throw new Exception("Permission denied");
                }
                
                $db = Database::getInstance()->getConnection();
                
                $query = "SELECT f.*, f.category_tag as file_category, 
                         CONCAT(p.fname, ' ', p.lname) as uploader_name
                         FROM file_upload_tbl f
                         LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id
                         LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
                         ORDER BY f.datetime_uploaded DESC";
                
                $stmt = $db->query($query);
                $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['data' => $files]);
            } catch(Exception $e) {
                echo json_encode(['data' => [], 'error' => $e->getMessage()]);
            }
            exit;
            
        case '40':
            // Get all tasks (for task table)
            try {
                $taskManager = new TaskManager();
                $tasks = $taskManager->getTasks();
                echo json_encode(['data' => $tasks]);
            } catch(Exception $e) {
                echo json_encode(['data' => [], 'error' => $e->getMessage()]);
            }
            exit;
            
        case '41':
            // Get all submissions (for reports table)
            try {
                $taskManager = new TaskManager();
                $submissions = $taskManager->getAllSubmissions();
                echo json_encode(['data' => $submissions]);
            } catch(Exception $e) {
                echo json_encode(['data' => [], 'error' => $e->getMessage()]);
            }
            exit;
            
        case '42':
            // Get single task details
            try {
                $taskManager = new TaskManager();
                $taskId = $_POST['task_id'];
                $tasks = $taskManager->getTasks();
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
            
        case '43':
            // Update task
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
            exit;
            
        case '44':
            // Delete task
            try {
                $taskManager = new TaskManager();
                $taskId = $_POST['task_id'] ?? null;
                $result = $taskManager->deleteTask($taskId);
            } catch(Exception $e) {
                $result = ["status" => "ERROR", "msg" => $e->getMessage()];
            }
            echo json_encode($result);
            exit;
            
        case '45':
            // Get submission details
            try {
                $taskManager = new TaskManager();
                $submissionId = $_POST['submission_id'] ?? null;
                $submission = $taskManager->getSubmissionDetails($submissionId);
                if (!$submission) {
                    echo json_encode(['status' => 'ERROR', 'msg' => 'Submission not found']);
                } else {
                    echo json_encode(['status' => 'SUCCESS', 'data' => $submission]);
                }
            } catch(Exception $e) {
                echo json_encode(['status' => 'ERROR', 'msg' => $e->getMessage()]);
            }
            exit;
            
        case '46':
            // Approve submission
            try {
                $taskManager = new TaskManager();
                $submissionId = $_POST['submission_id'] ?? null;
                $result = $taskManager->approveSubmission($submissionId);
            } catch(Exception $e) {
                $result = ["status" => "ERROR", "msg" => $e->getMessage()];
            }
            echo json_encode($result);
            exit;
            
        default:
            $result = ["status" => "ERROR", "msg" => "Invalid call"];
            break;
    }
    
} catch(Exception $e) {
    error_log("Adviser AJAX Error: " . $e->getMessage());
    $result = ["status" => "ERROR", "msg" => "An error occurred: " . $e->getMessage()];
}

echo json_encode($result);*/
?>

