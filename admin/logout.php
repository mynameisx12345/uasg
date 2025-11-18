<?php
session_start();

// Store user type before destroying session for logging purposes
$user_type = $_SESSION['user_type'] ?? 'admin';
$user_id = $_SESSION['user_id'] ?? null;

// Optional: Log logout activity if needed
if ($user_id) {
    // Include database connection if you want to log activity
    // For now, we'll keep it simple without logging
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

// Redirect to login page with logout success message
header("Location: ../index.php?logout=success");
exit();
?>
