<?php
// Main Authentication Handler
// Start output buffering to catch any warnings/errors
ob_start();

// Set session security flags before starting session
if (session_status() === PHP_SESSION_NONE) {
    // Suppress session ini_set warnings in development
    @ini_set('session.cookie_httponly', 1);
    @ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
    @ini_set('session.use_strict_mode', 1);
    session_start();
}
require_once("resources/auth.php");

// Only set JSON header for AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // Clean any previous output (warnings, etc.) and set JSON header
    ob_clean();
    header('Content-Type: application/json');
} else {
    // For non-AJAX requests, set content-type after clearing buffer
    ob_clean();
    header('Content-Type: application/json');
}

$auth = new AuthenticationManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            handleLogin();
            break;
            
        case 'logout':
            handleLogout();
            break;
            
        case 'change_password':
            handleChangePassword();
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'logout') {
        handleLogout();
    } else if ($action === 'validate') {
        echo json_encode(['valid' => $auth->validateSession()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
}

function handleLogin() {
    global $auth;
    
    try {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Debug logging
        error_log("Login attempt for username: $username");
        
        if (empty($username) || empty($password)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Username and password are required'
            ]);
            return;
        }
        
        // Try authentication
        $authResult = $auth->authenticate($username, $password);
        error_log("Authentication result: " . ($authResult ? 'SUCCESS' : 'FAILED'));
        
        if ($authResult) {
            // Check if session was properly set
            if (!isset($_SESSION['user_type'])) {
                error_log("Session not properly set after authentication");
                echo json_encode([
                    'success' => false,
                    'message' => 'Session initialization failed'
                ]);
                return;
            }
            
            // Determine redirect URL based on user type
            $redirectUrl = getRedirectUrl($_SESSION['user_type']);
            error_log("Redirecting to: $redirectUrl");
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'redirect_url' => $redirectUrl,
                'user' => [
                    'username' => $_SESSION['username'],
                    'user_type' => $_SESSION['user_type'],
                    'full_name' => $_SESSION['full_name'] ?? '',
                    'position' => $_SESSION['position'] ?? ''
                ],
                'debug' => [
                    'session_set' => isset($_SESSION['user_id']),
                    'redirect_url' => $redirectUrl,
                    'user_type' => $_SESSION['user_type']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid username or password. Please try again.'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred during login. Please check the error log.',
            'debug' => $e->getMessage()
        ]);
    }
}

function handleLogout() {
    global $auth;
    
    try {
        $auth->destroySession();
        
        // If it's a GET request (direct redirect), redirect to login page
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Location: login.php');
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Logged out successfully',
            'redirect_url' => 'login.php'
        ]);
        
    } catch (Exception $e) {
        error_log("Logout error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred during logout'
        ]);
    }
}

function handleChangePassword() {
    global $auth;
    
    try {
        // Validate session
        if (!$auth->validateSession()) {
            echo json_encode([
                'success' => false,
                'message' => 'Session expired. Please login again.'
            ]);
            return;
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            echo json_encode([
                'success' => false,
                'message' => 'All password fields are required'
            ]);
            return;
        }
        
        if ($newPassword !== $confirmPassword) {
            echo json_encode([
                'success' => false,
                'message' => 'New password and confirmation do not match'
            ]);
            return;
        }
        
        $result = $auth->changePassword($_SESSION['user_id'], $currentPassword, $newPassword);
        echo json_encode($result);
        
    } catch (Exception $e) {
        error_log("Password change error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while changing password'
        ]);
    }
}

function getRedirectUrl($userType) {
    switch (strtolower($userType)) {
        case 'admin':
            return 'admin/index.php';
        case 'adviser':
            return 'subadmin/index.php';
        case 'member':
            return 'member/index.php';
        default:
            return 'index.php';
    }
}

// Security check function for protected pages
function requireAuth($allowedTypes = []) {
    global $auth;
    
    if (!$auth->validateSession()) {
        if (isAjaxRequest()) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'Session expired']);
            exit;
        } else {
            header('Location: ../login.php');
            exit;
        }
    }
    
    if (!empty($allowedTypes) && !in_array($_SESSION['user_type'], $allowedTypes)) {
        if (isAjaxRequest()) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        } else {
            header('Location: ../login.php?error=access_denied');
            exit;
        }
    }
}

function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}
?>