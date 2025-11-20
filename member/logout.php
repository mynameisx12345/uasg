<?php
session_start();

// Include the main class for database connection
include_once '../resources/objects/main_class.php';
require_once '../resources/objects/db_config.php';

// Create instance of main class
$main = new Main('user_tbl');

// Log logout activity if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'] ?? 'student';
    $db = Database::getInstance()->getConnection();
    // Log the logout activity
    $logout_time = date('Y-m-d H:i:s');
    //$sql = "INSERT INTO user_activity (user_id, activity_type, activity_description, activity_timestamp) 
    //        VALUES (?, 'logout', 'User logged out from ? dashboard', ?)";
    //$stmt = $db->prepare($sql);
    //$stmt->bind_param("iss", $user_id, $user_type, $logout_time);
    //$stmt->execute();
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