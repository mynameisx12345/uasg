<?php
session_start();

// Include the database connection
require_once '../resources/objects/main_class.php';

// Log logout activity if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'] ?? 'adviser';
    
    // Log the logout activity (if user_activity table exists)
    try {
        $db = Database::getInstance()->getConnection();
        $logout_time = date('Y-m-d H:i:s');
        $sql = "INSERT INTO user_activity (user_id, activity_type, activity_description, activity_timestamp) 
                VALUES (?, 'logout', ?, ?)";
        $stmt = $db->prepare($sql);
        $description = "User logged out from {$user_type} dashboard";
        $stmt->execute([$user_id, $description, $logout_time]);
    } catch (Exception $e) {
        // If logging fails, continue with logout anyway
        error_log("Logout logging failed: " . $e->getMessage());
    }
}

// Clear all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: ../index.php?logout=success");
exit();
?>