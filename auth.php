<?php
require_once("resources/session.php");

// Handle both GET and POST requests
$method = $_SERVER['REQUEST_METHOD'];
$action = '';

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
} elseif ($method === 'GET') {
    $action = $_GET['action'] ?? '';
}

// Handle logout (allow both GET and POST)
if ($action === 'logout') {
    handleLogout();
    exit;
}

// Set JSON header for other actions
header('Content-Type: application/json');

// For other actions, require POST
if ($method !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function handleLogin() {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required']);
        return;
    }
    
    if (authenticate($username, $password)) {
        $session = SessionManager::getInstance();
        $position = $session->getPosition();
        
        // Determine redirect URL based on user position
        $redirectUrls = [
            'System Administrator' => 'admin/users.php',
            'Adviser' => 'adviser/dashboard.php',
            'Student Government Member' => 'member/dashboard.php'
        ];
        
        $redirectUrl = $redirectUrls[$position] ?? 'account/profile.php';
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect_url' => $redirectUrl,
            'user_data' => $session->getUserData()
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }
}

function handleLogout() {
    $session = SessionManager::getInstance();
    $session->logout();
    
    // If it's a GET request (direct redirect), redirect to login page
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        header('Location: login.php');
        exit;
    }
    
    // For POST requests (AJAX), return JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully',
        'redirect_url' => 'login.php'
    ]);
}
?>