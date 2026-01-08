<?php
// Prevent any output before JSON
ob_start();

session_start();
require_once("../resources/class.php");

// Check if this is a download request (GET allowed for downloads)
if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['CALL'])) {
    if ($_GET['CALL'] === 'download') {
        ob_end_clean();
        $fileId = $_GET['file_id'] ?? 0;
        try {
            $fileManager = new FileManager();
            $fileManager->downloadFile($fileId);
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

if($call === 'nlp_search_files'){
    try {
        require_once '../config/google_nlp_config.php';
        require_once '../resources/objects/google_nlp_service.php';
        
        $searchWord = $_POST['SEARCH_WORD'] ?? '';
        $categoryId = $_POST['CATEGORY_ID'] ?? '';
        
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'file_management', 'view')) {
            echo json_encode(['data' => [], 'error' => 'Permission denied']);
            exit;
        }
        
        // Subadmins with file_management permission see all files
        $query = "SELECT fu.*, fc.file_category, p.fname, p.lname
                  FROM file_upload_tbl fu
                  LEFT JOIN file_category_tbl fc ON fu.file_category_id = fc.file_category_id
                  LEFT JOIN user_tbl u ON fu.uploaded_by = u.user_id
                  LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($categoryId)) {
            $query .= " AND fu.file_category_id = ?";
            $params[] = $categoryId;
        }
        
        $query .= " ORDER BY fu.datetime_uploaded DESC";
        
        $allFiles = $db->select($query, $params);
        
        // If no search word, return all files
        if (empty($searchWord)) {
            echo json_encode(['data' => $allFiles ?: []]);
            exit;
        }
        
        // Filter by NLP/keyword matching
        $config = include('../config/google_nlp_config.php');
        $apiKey = $config['api_key'] ?? '';
        $nlp = new GoogleNLPService($apiKey);
        
        $matchedFiles = [];
        foreach ($allFiles as $file) {
            $filePath = '../' . $file['file_path'];
            if (!file_exists($filePath)) continue;
            
            try {
                // Extract text and check for keyword
                $result = $nlp->extractTextFromFile($filePath, $file['mime_type']);
                if ($result['success'] && !empty($result['text'])) {
                    if (stripos($result['text'], $searchWord) !== false) {
                        $matchedFiles[] = $file;
                    }
                }
            } catch (Exception $e) {
                error_log("NLP search error for file {$file['file_name']}: " . $e->getMessage());
            }
        }
        
        echo json_encode(['data' => $matchedFiles]);
        
        // Log activity
        SubadminPermission::logActivity($userId, 'nlp_search', 'file_management', 'Performed NLP search: ' . $searchWord);
        
    } catch(Exception $e) {
        error_log("Subadmin NLP search error: " . $e->getMessage());
        echo json_encode(['data' => [], 'error' => $e->getMessage()]);
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
        $result = $userManager->changeUserPassword($userId, $data);
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
    // Mark notification as read
    try {
        $notificationId = $_POST['notification_id'] ?? 0;
        $notificationManager = new NotificationManager();
        $success = $notificationManager->markAsRead($notificationId);
        $result = ["status" => $success ? "SUCCESS" : "ERROR", "msg" => $success ? "Notification marked as read" : "Failed to mark notification"];
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
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
        $query = "SELECT fu.*, fc.file_category as category_name, 
                  fu.mime_type as file_type,
                  CONCAT(p.fname, ' ', p.lname) as uploaded_by_name
                  FROM file_upload_tbl fu
                  LEFT JOIN file_category_tbl fc ON fu.file_category_id = fc.file_category_id
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
        $query = "SELECT ts.*, t.task_title, 
                  CONCAT(p.fname, ' ', p.lname) as student_name,
                  fu.file_name, fu.file_upload_id, fu.datetime_uploaded as submitted_at
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
        $query = "SELECT ts.*, t.task_title, 
                  CONCAT(p.fname, ' ', p.lname) as student_name,
                  fu.file_name, fu.file_upload_id, fu.datetime_uploaded as submitted_at
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
        
        if (empty($_POST['submission_id'])) {
            throw new Exception("Submission ID is required");
        }
        
        $db = Database::getInstance()->getConnection();
        
        // Update submission status
        $query = "UPDATE task_submission_tbl SET check_status = 'Approved' WHERE task_submission_id = :submission_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':submission_id' => $_POST['submission_id']]);
        
        // Log activity
        SubadminPermission::logActivity($userId, 'approve_submission', 'task_management', 'Approved submission ID: ' . $_POST['submission_id']);
        
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
            SET assigned_to = ?, task_status = 'transferred'
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
            
            // Log activity
            SubadminPermission::logActivity($userId, 'transfer_task', 'task_management', 
                "Transferred task ID {$taskId} to " . $newMember['fname'] . ' ' . $newMember['lname']);
            
            $result = ["status" => "SUCCESS", "msg" => "Task transferred successfully to " . $newMember['fname'] . ' ' . $newMember['lname']];
        } else {
            throw new Exception("Failed to transfer task");
        }
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 'close_cancel_task'){
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
        } else {
            throw new Exception("Failed to update task status");
        }
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
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
        
        // Log download activity
        SubadminPermission::logActivity($userId, 'download_file', 'file_management', 'Downloaded file: ' . $file['file_name']);
        
        // Set headers for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file['file_name'] . '"');
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
                
                $query = "SELECT f.*, c.file_category, 
                         CONCAT(p.fname, ' ', p.lname) as uploader_name
                         FROM file_upload_tbl f
                         LEFT JOIN file_category_tbl c ON f.file_category_id = c.file_category_id
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
            
        case 'nlp_search_files':
            // NLP-powered file search (permission-checked)
            try {
                $userId = $_SESSION['user_id'] ?? 0;
                
                // Check permission
                if (!SubadminPermission::hasPermission($userId, 'file_management', 'view')) {
                    throw new Exception("Permission denied");
                }
                
                $searchQuery = $_POST['search_query'] ?? '';
                
                if (empty($searchQuery)) {
                    echo json_encode(['data' => []]);
                    exit;
                }
                
                $db = Database::getInstance()->getConnection();
                
                // Search in file names, categories, and NLP tags
                $query = "SELECT f.*, c.file_category,
                         CONCAT(p.fname, ' ', p.lname) as uploader_name
                         FROM file_upload_tbl f
                         LEFT JOIN file_category_tbl c ON f.file_category_id = c.file_category_id
                         LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id
                         LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
                         WHERE f.file_name LIKE :query
                            OR c.file_category LIKE :query
                            OR f.category_tag LIKE :query
                         ORDER BY f.category_score DESC, f.datetime_uploaded DESC";
                
                $stmt = $db->prepare($query);
                $stmt->execute([':query' => "%{$searchQuery}%"]);
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

