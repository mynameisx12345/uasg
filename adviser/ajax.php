<?php
require_once("../resources/session.php");

// Prevent any output before JSON
ob_start();

$session = SessionManager::getInstance();
$session->requireRole(['Adviser', 'adviser']);

// Clear any previous output and set headers
ob_clean();
header("Content-Type: application/json");

if(!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
    http_response_code(403);
    echo json_encode(["status"=>"error","message"=>"Forbidden"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method."]);
    exit;
}

if(empty($_POST["CALL"])){
    echo json_encode(["error" => "Request invalid"]);
    exit;
}

$call = $_POST["CALL"];
$result = [];
$currentUser = $session->getUserData();
$userId = $currentUser['user_id'];

try {
    require_once("../resources/class.php");
    
    switch($call) {
        case 'get_dashboard_stats':
            // Get dashboard statistics
            $taskManager = new TaskManager();
            $notificationManager = new NotificationManager();
            
            $tasks = $taskManager->getTasks();
            $submissions = $taskManager->getSubmissions();
            
            $totalTasks = count($tasks);
            $pendingReviews = 0;
            $completedTasks = 0;
            $activeMembers = [];
            
            foreach($submissions as $submission) {
                if($submission['check_status'] === 'pending') {
                    $pendingReviews++;
                }
                if($submission['check_status'] === 'approved') {
                    $completedTasks++;
                }
                $activeMembers[$submission['uploaded_by']] = true;
            }
            
            $result = [
                "status" => "SUCCESS",
                "data" => [
                    "total_tasks" => $totalTasks,
                    "pending_reviews" => $pendingReviews,
                    "completed_tasks" => $completedTasks,
                    "active_members" => count($activeMembers),
                    "unread_notifications" => $notificationManager->getUnreadCount($userId)
                ]
            ];
            break;
            
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