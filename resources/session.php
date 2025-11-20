<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class SessionManager {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function login($userData) {
        $_SESSION['user_id'] = $userData['user_id'];
        $_SESSION['user_name'] = $userData['user_name'];
        $_SESSION['user_type'] = $userData['user_type'];
        $_SESSION['position_id'] = $userData['position_id'];
        $_SESSION['position'] = $userData['position'];
        $_SESSION['fname'] = $userData['fname'];
        $_SESSION['lname'] = $userData['lname'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        return true;
    }
    
    public function logout() {
        session_unset();
        session_destroy();
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public function getUserType() {
        return $_SESSION['user_type'] ?? null;
    }
    
    public function getPosition() {
        return $_SESSION['position'] ?? null;
    }
    
    public function getPositionId() {
        return $_SESSION['position_id'] ?? null;
    }
    
    public function getUserName() {
        return $_SESSION['user_name'] ?? null;
    }
    
    public function getFullName() {
        $fname = $_SESSION['fname'] ?? '';
        $lname = $_SESSION['lname'] ?? '';
        return trim($fname . ' ' . $lname);
    }
    
    public function getUserData() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'user_id' => $this->getUserId(),
            'user_name' => $this->getUserName(),
            'user_type' => $this->getUserType(),
            'position_id' => $this->getPositionId(),
            'position' => $this->getPosition(),
            'full_name' => $this->getFullName(),
            'email' => $_SESSION['email'] ?? ''
        ];
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ../login.php');
            exit;
        }
    }
    
    private $roleMap = [
        'student government member' => 'student',
        'student' => 'student',
        'sgo member' => 'student',
        'member' => 'student', // add this to map old 'member'
        'admin' => 'admin',
        'adviser' => 'adviser'
    ];

    public function requireRole($allowedRoles = []) {
        $userRole = strtolower($_SESSION['user_type'] ?? '');
        $allowed = array_map('strtolower', $allowedRoles);

        if (!in_array($userRole, $allowed)) {
            // redirect to login
            header('Location: ../index.php?error=access_denied');
            exit;
        }
    }
    
    public function checkPermission($requiredPosition) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $position = $this->getPosition();
        
        // Admin has access to everything
        if ($position === 'System Administrator') {
            return true;
        }
        
        return $position === $requiredPosition;
    }
    
    public function regenerateSession() {
        session_regenerate_id(true);
        return true;
    }
}

// Authentication functions
function authenticate($username, $password) {
    require_once("../resources/class.php");
    
    try {
        $db = Database::getInstance();
        
        // Get user with position and profile data
        $query = "SELECT u.*, pos.position, pos.access_restriction,
                         prof.fname, prof.lname, prof.email
                  FROM user_tbl u
                  JOIN position_tbl pos ON u.position_id = pos.position_id
                  JOIN profile_tbl prof ON u.profile_id = prof.profile_id
                  WHERE u.user_name = :username LIMIT 1";
        
        $result = $db->select($query, [':username' => $username]);
        
        if (empty($result)) {
            return false;
        }
        
        $user = $result[0];
        
        // Verify password (assuming plain text for now, should be hashed in production)
        if ($user['pass_word'] !== $password) {
            return false;
        }
        
        // Create session
        $sessionManager = SessionManager::getInstance();
        return $sessionManager->login($user);
        
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        return false;
    }
}

function redirectBasedOnRole() {
    $session = SessionManager::getInstance();
    
    if (!$session->isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
    
    $position = $session->getPosition();
    
    switch ($position) {
        case 'System Administrator':
            header('Location: ../admin/users.php');
            break;
        case 'Adviser':
            header('Location: ../adviser/dashboard.php');
            break;
        case 'Student Government Member':
            header('Location: ../member/dashboard.php');
            break;
        default:
            header('Location: ../account/profile.php');
            break;
    }
    exit;
}
?>