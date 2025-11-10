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
        $entityManager = new EntityManager();
        $categories = $entityManager::getAllFileCategories();
        $result = ["status" => "SUCCESS", "data" => $categories];
    } catch(Exception $e) {
        $result = ["status" => "ERROR", "msg" => $e->getMessage()];
    }
    echo json_encode($result);
    
}else if($call == 21){
    // Upload file
    try {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Check permission
        if (!SubadminPermission::hasPermission($userId, 'file_management', 'create')) {
            throw new Exception("Permission denied: You cannot upload files");
        }
        
        // Handle file upload
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error");
        }
        
        $fileManager = new FileManager();
        
        // Prepare file data
        $fileData = [
            'file' => $_FILES['file'],
            'category_id' => $_POST['category_id'] ?? null,
            'file_title' => $_POST['file_title'] ?? null,
            'description' => $_POST['description'] ?? null,
            'uploaded_by' => $userId
        ];
        
        $result = $fileManager->uploadFile($fileData);
        
        // Log activity
        if ($result['status'] === 'SUCCESS') {
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
                  CONCAT(p.fname, ' ', p.lname) as uploaded_by_name
                  FROM file_upload_tbl fu
                  LEFT JOIN file_category_tbl fc ON fu.category_id = fc.file_category_id
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
?>
            
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
            
        default:
            $result = ["status" => "ERROR", "msg" => "Invalid call"];
            break;
    }
    
} catch(Exception $e) {
    error_log("Adviser AJAX Error: " . $e->getMessage());
    $result = ["status" => "ERROR", "msg" => "An error occurred: " . $e->getMessage()];
}

echo json_encode($result);
?>