<?php
// Prevent any output before JSON
ob_start();

session_start();
require_once("../resources/class.php");

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
    if(isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        try {
            require_once '../resources/objects/nlp_helper.php'; // Helper for Google NLP
            $nlp = new NLPHelper();
            $file = $_FILES['file'];
            $text = $nlp->extractTextFromFile($file['tmp_name'], $file['type']);
            $analysis = $nlp->analyzeText($text);
            $suggestedCategory = $analysis['suggested_category'] ?? 'Uncategorized';
            $categoryConfidence = $analysis['category_confidence'] ?? 0;
            $result = [
                'status' => 'SUCCESS',
                'nlp_analysis' => [
                    'suggested_category' => $suggestedCategory,
                    'category_confidence' => $categoryConfidence,
                    'keywords' => $analysis['keywords'] ?? [],
                    'entities' => $analysis['entities'] ?? [],
                    'full_analysis' => $analysis
                ]
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
    // Upload file with Google NLP auto-categorization and user-confirmed category
    if(isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $data = [
            'file' => $_FILES['file'],
            'uploaded_by' => $_SESSION['user_id'] ?? 0,
            'category_tag' => $_POST['category_tag'] ?? 'Uncategorized',
            'category_score' => $_POST['category_score'] ?? 0,
            'nlp_analysis' => $_POST['nlp_analysis'] ?? null,
            'file_path' => null
        ];
    } else {
        $data = $_POST['DATA'] ?? [];
        $data['uploaded_by'] = $_SESSION['user_id'] ?? 0;
    }
    try {
        $fileManager = new FileManager();
        $result = $fileManager->uploadMemberFile($data);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 4){
    // Analyze file content
    try {
        $fileAnalyzer = new FileAnalyzer();
        $files = $_POST['files'] ?? '[]';
        $filesData = json_decode($files, true);
        $result = $fileAnalyzer->analyzeMultipleFiles($filesData);
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
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
    // Get active tasks
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $taskManager = new TaskManager();
        $managerResult = $taskManager->getMemberActiveTasks($memberId);
        
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
    
}else if($call == 10){
    // Get task submissions
    try {
        $memberId = $_SESSION['user_id'] ?? 0;
        $taskManager = new TaskManager();
        $managerResult = $taskManager->getMemberTaskSubmissions($memberId);
        
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
    // Submit task file
    try {
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
    
}else{
    echo json_encode(["status" => "ERROR", "msg" => "Invalid call"]);
}
?>

function getDashboardStats() {
    global $mainClass, $currentUser;
    
    $memberId = $currentUser['id'];
    
    // Get total files uploaded by member
    $totalFiles = $mainClass->query("SELECT COUNT(*) as count FROM file_upload_tbl WHERE uploaded_by = ?", [$memberId])->fetch()['count'];
    
    // Get active tasks assigned to member
    $activeTasks = $mainClass->query("
        SELECT COUNT(*) as count 
        FROM task_tbl t 
        WHERE t.assigned_to = ? 
        AND t.status != 'completed'
        AND t.deadline >= CURDATE()
    ", [$memberId])->fetch()['count'];
    
    // Get completed tasks
    $completedTasks = $mainClass->query("
        SELECT COUNT(*) as count 
        FROM task_submission_tbl ts
        JOIN task_tbl t ON ts.task_id = t.id
        WHERE ts.submitted_by = ?
        AND ts.status = 'approved'
    ", [$memberId])->fetch()['count'];
    
    // Get distinct categories used
    $categoryCount = $mainClass->query("
        SELECT COUNT(DISTINCT fc.id) as count
        FROM file_upload_tbl fu
        JOIN file_category_tbl fc ON fu.category_id = fc.id
        WHERE fu.uploaded_by = ?
    ", [$memberId])->fetch()['count'];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'totalFiles' => $totalFiles,
            'activeTasks' => $activeTasks,
            'completedTasks' => $completedTasks,
            'categoryCount' => $categoryCount
        ]
    ]);
}

function getRecentActivity() {
    global $mainClass, $currentUser;
    
    $memberId = $currentUser['id'];
    
    $activities = $mainClass->query("
        SELECT 
            'file_upload' as type,
            fu.upload_date as date,
            CONCAT('Uploaded file: ', fu.file_name) as action,
            fu.file_name as item,
            fc.category_name as category,
            'completed' as status
        FROM file_upload_tbl fu
        LEFT JOIN file_category_tbl fc ON fu.category_id = fc.id
        WHERE fu.uploaded_by = ?
        
        UNION ALL
        
        SELECT 
            'task_submission' as type,
            ts.submission_date as date,
            CONCAT('Submitted task: ', t.title) as action,
            t.title as item,
            tc.category_name as category,
            ts.status
        FROM task_submission_tbl ts
        JOIN task_tbl t ON ts.task_id = t.id
        LEFT JOIN task_category_tbl tc ON t.category_id = tc.id
        WHERE ts.submitted_by = ?
        
        ORDER BY date DESC
        LIMIT 10
    ", [$memberId, $memberId])->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $activities]);
}

function analyzeFileContent() {
    if (!isset($_FILES['file'])) {
        echo json_encode(['success' => false, 'message' => 'No file provided']);
        return;
    }
    
    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileType = $file['type'];
    $fileSize = $file['size'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Intelligent content categorization based on file type and name
    $analysis = performContentAnalysis($fileName, $fileType, $fileExtension);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'detectedCategory' => $analysis['category'],
            'confidence' => $analysis['confidence'],
            'fileType' => $analysis['fileType'],
            'keywords' => $analysis['keywords'],
            'categoryId' => $analysis['categoryId']
        ]
    ]);
}

function performContentAnalysis($fileName, $fileType, $extension) {
    global $mainClass;
    
    // Get available categories
    $categories = $mainClass->query("SELECT * FROM file_category_tbl ORDER BY category_name")->fetchAll();
    
    $fileName = strtolower($fileName);
    $analysis = [
        'category' => 'General Documents',
        'confidence' => 'Low',
        'fileType' => ucfirst($extension),
        'keywords' => [],
        'categoryId' => 1 // Default category
    ];
    
    // Define keyword patterns for different categories
    $categoryPatterns = [
        'Academic Records' => [
            'keywords' => ['transcript', 'grade', 'academic', 'report', 'semester', 'gpa', 'course'],
            'extensions' => ['pdf', 'doc', 'docx'],
            'confidence' => 'High'
        ],
        'Financial Documents' => [
            'keywords' => ['budget', 'financial', 'expense', 'receipt', 'invoice', 'payment', 'fund'],
            'extensions' => ['pdf', 'xls', 'xlsx', 'csv'],
            'confidence' => 'High'
        ],
        'Meeting Minutes' => [
            'keywords' => ['meeting', 'minutes', 'agenda', 'discussion', 'motion', 'resolution'],
            'extensions' => ['doc', 'docx', 'pdf'],
            'confidence' => 'High'
        ],
        'Reports' => [
            'keywords' => ['report', 'analysis', 'summary', 'findings', 'conclusion', 'recommendation'],
            'extensions' => ['pdf', 'doc', 'docx', 'ppt', 'pptx'],
            'confidence' => 'Medium'
        ],
        'Event Documentation' => [
            'keywords' => ['event', 'activity', 'program', 'ceremony', 'celebration', 'photo'],
            'extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
            'confidence' => 'Medium'
        ],
        'Legal Documents' => [
            'keywords' => ['legal', 'contract', 'agreement', 'constitution', 'bylaw', 'policy'],
            'extensions' => ['pdf', 'doc', 'docx'],
            'confidence' => 'High'
        ],
        'Proposals' => [
            'keywords' => ['proposal', 'project', 'plan', 'initiative', 'strategy', 'recommendation'],
            'extensions' => ['pdf', 'doc', 'docx', 'ppt', 'pptx'],
            'confidence' => 'Medium'
        ]
    ];
    
    // Analyze file name against patterns
    $bestMatch = null;
    $highestScore = 0;
    
    foreach ($categoryPatterns as $categoryName => $pattern) {
        $score = 0;
        $foundKeywords = [];
        
        // Check keywords in filename
        foreach ($pattern['keywords'] as $keyword) {
            if (strpos($fileName, $keyword) !== false) {
                $score += 2;
                $foundKeywords[] = $keyword;
            }
        }
        
        // Check file extension
        if (in_array($extension, $pattern['extensions'])) {
            $score += 1;
        }
        
        // Update best match if this category has higher score
        if ($score > $highestScore) {
            $highestScore = $score;
            $bestMatch = [
                'name' => $categoryName,
                'confidence' => $score >= 3 ? 'High' : ($score >= 2 ? 'Medium' : 'Low'),
                'keywords' => $foundKeywords
            ];
        }
    }
    
    // Find category ID from database
    if ($bestMatch) {
        foreach ($categories as $cat) {
            if (strtolower($cat['category_name']) === strtolower($bestMatch['name'])) {
                $analysis['categoryId'] = $cat['id'];
                break;
            }
        }
        
        $analysis['category'] = $bestMatch['name'];
        $analysis['confidence'] = $bestMatch['confidence'];
        $analysis['keywords'] = $bestMatch['keywords'];
    }
    
    // Determine file type description
    $typeDescriptions = [
        'pdf' => 'PDF Document',
        'doc' => 'Word Document',
        'docx' => 'Word Document',
        'xls' => 'Excel Spreadsheet',
        'xlsx' => 'Excel Spreadsheet',
        'ppt' => 'PowerPoint Presentation',
        'pptx' => 'PowerPoint Presentation',
        'jpg' => 'JPEG Image',
        'jpeg' => 'JPEG Image',
        'png' => 'PNG Image',
        'gif' => 'GIF Image',
        'zip' => 'ZIP Archive',
        'rar' => 'RAR Archive'
    ];
    
    $analysis['fileType'] = $typeDescriptions[$extension] ?? ucfirst($extension) . ' File';
    
    return $analysis;
}

function uploadFile() {
    global $mainClass, $currentUser;
    
    if (!isset($_FILES['file'])) {
        echo json_encode(['success' => false, 'message' => 'No file provided']);
        return;
    }
    
    $file = $_FILES['file'];
    $description = $_POST['description'] ?? '';
    $taskId = $_POST['task_id'] ?? null;
    
    // Validate file
    $maxSize = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB limit']);
        return;
    }
    
    $allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/zip',
        'application/x-rar-compressed'
    ];
    
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'File type not allowed']);
        return;
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = '../uploads/member_files/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueFileName = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = $uploadDir . $uniqueFileName;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // If no category provided, analyze content
        if (!$categoryId) {
            $analysis = performContentAnalysis($file['name'], $file['type'], $extension);
            $categoryId = $analysis['categoryId'];
        }
        
        // Insert file record
        $result = $mainClass->query("
            INSERT INTO file_upload_tbl 
            (file_name, original_name, file_path, file_type, file_size, category_id, description, uploaded_by, upload_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ", [
            $uniqueFileName,
            $file['name'],
            $uploadPath,
            $file['type'],
            $file['size'],
            $categoryId,
            $description,
            $currentUser['id']
        ]);
        
        if ($result) {
            $fileId = $mainClass->getConnection()->lastInsertId();
            
            // If associated with task, create task submission
            if ($taskId) {
                $mainClass->query("
                    INSERT INTO task_submission_tbl 
                    (task_id, submitted_by, file_id, submission_date, status) 
                    VALUES (?, ?, ?, NOW(), 'pending')
                ", [$taskId, $currentUser['id'], $fileId]);
            }
            
            echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
        } else {
            // Delete uploaded file if database insert fails
            unlink($uploadPath);
            echo json_encode(['success' => false, 'message' => 'Failed to save file record']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
    }
}

function getFileCategories() {
    global $mainClass;
    
    $categories = $mainClass->query("SELECT * FROM file_category_tbl ORDER BY category_name")->fetchAll();
    echo json_encode(['success' => true, 'data' => $categories]);
}

function getMyFiles() {
    global $mainClass, $currentUser;
    
    $categoryFilter = $_GET['category'] ?? '';
    $typeFilter = $_GET['type'] ?? '';
    
    $whereConditions = ["fu.uploaded_by = ?"];
    $params = [$currentUser['id']];
    
    if ($categoryFilter) {
        $whereConditions[] = "fu.category_id = ?";
        $params[] = $categoryFilter;
    }
    
    if ($typeFilter) {
        $whereConditions[] = "fu.file_type LIKE ?";
        $params[] = $typeFilter . '%';
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $files = $mainClass->query("
        SELECT 
            fu.*,
            fc.category_name,
            t.title as task_title
        FROM file_upload_tbl fu
        LEFT JOIN file_category_tbl fc ON fu.category_id = fc.id
        LEFT JOIN task_submission_tbl ts ON fu.id = ts.file_id
        LEFT JOIN task_tbl t ON ts.task_id = t.id
        WHERE $whereClause
        ORDER BY fu.upload_date DESC
    ", $params)->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $files]);
}

function deleteFile() {
    global $mainClass, $currentUser;
    
    $fileId = $_POST['file_id'] ?? '';
    
    // Get file info and verify ownership
    $file = $mainClass->query("
        SELECT * FROM file_upload_tbl 
        WHERE id = ? AND uploaded_by = ?
    ", [$fileId, $currentUser['id']])->fetch();
    
    if (!$file) {
        echo json_encode(['success' => false, 'message' => 'File not found or unauthorized']);
        return;
    }
    
    // Check if file is associated with task submissions
    $submissions = $mainClass->query("
        SELECT COUNT(*) as count FROM task_submission_tbl WHERE file_id = ?
    ", [$fileId])->fetch()['count'];
    
    if ($submissions > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete file - it is associated with task submissions']);
        return;
    }
    
    // Delete file from database
    $result = $mainClass->query("DELETE FROM file_upload_tbl WHERE id = ?", [$fileId]);
    
    if ($result) {
        // Delete physical file
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }
        echo json_encode(['success' => true, 'message' => 'File deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete file']);
    }
}

function downloadFile() {
    global $mainClass, $currentUser;
    
    $fileId = $_GET['file_id'] ?? '';
    
    // Get file info and verify ownership
    $file = $mainClass->query("
        SELECT * FROM file_upload_tbl 
        WHERE id = ? AND uploaded_by = ?
    ", [$fileId, $currentUser['id']])->fetch();
    
    if (!$file || !file_exists($file['file_path'])) {
        http_response_code(404);
        echo "File not found";
        return;
    }
    
    // Set headers for download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
    header('Content-Length: ' . filesize($file['file_path']));
    
    // Output file
    readfile($file['file_path']);
}

function getActiveTasks() {
    global $mainClass, $currentUser;
    
    $tasks = $mainClass->query("
        SELECT 
            t.*,
            tc.category_name,
            ts.status as submission_status,
            ts.submission_date,
            fu.original_name as submitted_file
        FROM task_tbl t
        LEFT JOIN task_category_tbl tc ON t.category_id = tc.id
        LEFT JOIN task_submission_tbl ts ON t.id = ts.task_id AND ts.submitted_by = ?
        LEFT JOIN file_upload_tbl fu ON ts.file_id = fu.id
        WHERE t.assigned_to = ? OR t.assigned_to IS NULL
        ORDER BY t.deadline ASC
    ", [$currentUser['id'], $currentUser['id']])->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $tasks]);
}

function getTaskSubmissions() {
    global $mainClass, $currentUser;
    
    $submissions = $mainClass->query("
        SELECT 
            ts.*,
            t.title as task_title,
            t.deadline,
            tc.category_name,
            fu.original_name as file_name,
            fu.file_path
        FROM task_submission_tbl ts
        JOIN task_tbl t ON ts.task_id = t.id
        LEFT JOIN task_category_tbl tc ON t.category_id = tc.id
        LEFT JOIN file_upload_tbl fu ON ts.file_id = fu.id
        WHERE ts.submitted_by = ?
        ORDER BY ts.submission_date DESC
    ", [$currentUser['id']])->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $submissions]);
}

function submitTaskFile() {
    global $mainClass, $currentUser;
    
    $taskId = $_POST['task_id'] ?? '';
    $fileId = $_POST['file_id'] ?? '';
    
    // Verify task exists and user has permission
    $task = $mainClass->query("
        SELECT * FROM task_tbl 
        WHERE id = ? AND (assigned_to = ? OR assigned_to IS NULL)
    ", [$taskId, $currentUser['id']])->fetch();
    
    if (!$task) {
        echo json_encode(['success' => false, 'message' => 'Task not found or unauthorized']);
        return;
    }
    
    // Verify file exists and belongs to user
    $file = $mainClass->query("
        SELECT * FROM file_upload_tbl 
        WHERE id = ? AND uploaded_by = ?
    ", [$fileId, $currentUser['id']])->fetch();
    
    if (!$file) {
        echo json_encode(['success' => false, 'message' => 'File not found or unauthorized']);
        return;
    }
    
    // Check if already submitted
    $existing = $mainClass->query("
        SELECT * FROM task_submission_tbl 
        WHERE task_id = ? AND submitted_by = ?
    ", [$taskId, $currentUser['id']])->fetch();
    
    if ($existing) {
        // Update existing submission
        $result = $mainClass->query("
            UPDATE task_submission_tbl 
            SET file_id = ?, submission_date = NOW(), status = 'pending'
            WHERE task_id = ? AND submitted_by = ?
        ", [$fileId, $taskId, $currentUser['id']]);
    } else {
        // Create new submission
        $result = $mainClass->query("
            INSERT INTO task_submission_tbl 
            (task_id, submitted_by, file_id, submission_date, status) 
            VALUES (?, ?, ?, NOW(), 'pending')
        ", [$taskId, $currentUser['id'], $fileId]);
    }
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Task submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit task']);
    }
}

function getNotifications() {
    global $mainClass, $currentUser;
    
    $notifications = $mainClass->query("
        SELECT * FROM notification_tbl 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 20
    ", [$currentUser['id']])->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $notifications]);
}

function markNotificationRead() {
    global $mainClass, $currentUser;
    
    $notificationId = $_POST['notification_id'] ?? '';
    
    $result = $mainClass->query("
        UPDATE notification_tbl 
        SET is_read = 1 
        WHERE id = ? AND user_id = ?
    ", [$notificationId, $currentUser['id']]);
    
    echo json_encode(['success' => $result !== false]);
}

function changePassword() {
    global $mainClass, $currentUser;
    
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
        return;
    }
    
    if (strlen($newPassword) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
        return;
    }
    
    // Verify current password
    $user = $mainClass->query("SELECT * FROM user_tbl WHERE id = ?", [$currentUser['id']])->fetch();
    
    if (!password_verify($currentPassword, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        return;
    }
    
    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $result = $mainClass->query("
        UPDATE user_tbl 
        SET password = ? 
        WHERE id = ?
    ", [$hashedPassword, $currentUser['id']]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password']);
    }
}
?>