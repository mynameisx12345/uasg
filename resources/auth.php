<?php
require_once("objects/db_config.php");

class AuthenticationManager {
    private $db;
    private $maxLoginAttempts = 5;
    private $lockoutDuration = 900; // 15 minutes in seconds
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Authenticate user with enhanced security
     */
    public function authenticate($username, $password) {
        try {
            // Input validation
            if (empty($username) || empty($password)) {
                $this->logLoginAttempt($username, false, 'Empty credentials');
                return false;
            }
            
            // Sanitize username
            $username = trim($username);
            
            // Check if account is locked
            if ($this->isAccountLocked($username)) {
                $this->logLoginAttempt($username, false, 'Account locked');
                return false;
            }
            
            // Get user data with position information
            $query = "
                SELECT u.user_id, u.user_name, u.pass_word, u.user_type, u.auth_token,
                       p.fname, p.lname, p.email, p.profile_id,
                       pos.position, pos.access_restriction, pos.position_id
                FROM user_tbl u
                JOIN profile_tbl p ON u.profile_id = p.profile_id
                JOIN position_tbl pos ON u.position_id = pos.position_id
                WHERE u.user_name = ?
                LIMIT 1
            ";
            
            $userData = $this->db->select($query, [$username]);
            
            if (empty($userData)) {
                $this->logLoginAttempt($username, false, 'User not found');
                $this->recordFailedAttempt($username);
                return false;
            }
            
            $user = $userData[0];
            
            // Verify password
            if (!password_verify($password, $user['pass_word'])) {
                $this->logLoginAttempt($username, false, 'Invalid password');
                $this->recordFailedAttempt($username);
                return false;
            }
            
            // Generate new auth token for session security
            $authToken = $this->generateSecureToken();
            $this->updateAuthToken($user['user_id'], $authToken);
            
            // Clear failed attempts on successful login
            $this->clearFailedAttempts($username);
            
            // Log successful login
            $this->logLoginAttempt($username, true, 'Login successful');
            
            // Start secure session
            $this->initializeUserSession($user, $authToken);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Initialize user session with security measures
     */
    private function initializeUserSession($user, $authToken) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['user_name'];
        $rawType = strtolower(trim($user['user_type']));

        $map = [
            'student government member' => 'student',
            'student' => 'student',
            'student gov member' => 'student',
            'sgo member' => 'student',
            's.g.o member' => 'student',

            'adviser' => 'subadmin',
            'sgo adviser' => 'subadmin',
            'student gov adviser' => 'subadmin',
            'subadmin' => 'subadmin',

            'admin' => 'admin',
            'administrator' => 'admin'
        ];

        $_SESSION['user_type'] = $map[$rawType] ?? 'student';
        $_SESSION['position'] = $user['position'];
        $_SESSION['position_id'] = $user['position_id'];
        $_SESSION['access_level'] = $user['access_restriction'];
        $_SESSION['profile_id'] = $user['profile_id'];
        $_SESSION['full_name'] = trim($user['fname'] . ' ' . $user['lname']);
        $_SESSION['email'] = $user['email'];
        $_SESSION['auth_token'] = $authToken;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $this->getClientIP();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Note: Session security flags should be set before session_start()
        // They are now handled in the main auth.php file
    }
    
    /**
     * Validate existing session
     */
    public function validateSession() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['auth_token'])) {
            return false;
        }
        
        // Check session timeout (2 hours)
        $sessionTimeout = 7200;
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity']) > $sessionTimeout) {
            $this->destroySession();
            return false;
        }
        
        // Validate auth token
        $query = "SELECT auth_token FROM user_tbl WHERE user_id = ?";
        $result = $this->db->select($query, [$_SESSION['user_id']]);
        
        if (empty($result) || $result[0]['auth_token'] !== $_SESSION['auth_token']) {
            $this->destroySession();
            return false;
        }
        
        // Check for session hijacking
        if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $this->getClientIP()) {
            $this->destroySession();
            return false;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Check if account is locked due to failed attempts
     */
    private function isAccountLocked($username) {
        $query = "
            SELECT COUNT(*) as attempts, MAX(attempt_time) as last_attempt
            FROM login_attempts_tbl 
            WHERE username = ? AND success = 0 AND attempt_time > ?
        ";
        
        $cutoffTime = date('Y-m-d H:i:s', time() - $this->lockoutDuration);
        $result = $this->db->select($query, [$username, $cutoffTime]);
        
        if (!empty($result) && $result[0]['attempts'] >= $this->maxLoginAttempts) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Record failed login attempt
     */
    private function recordFailedAttempt($username) {
        $query = "
            INSERT INTO login_attempts_tbl (username, ip_address, user_agent, success, attempt_time) 
            VALUES (?, ?, ?, 0, NOW())
        ";
        
        $this->db->getConnection()->prepare($query)->execute([
            $username,
            $this->getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
    
    /**
     * Clear failed attempts on successful login
     */
    private function clearFailedAttempts($username) {
        $query = "DELETE FROM login_attempts_tbl WHERE username = ?";
        $this->db->getConnection()->prepare($query)->execute([$username]);
    }
    
    /**
     * Log login attempts for security monitoring
     */
    private function logLoginAttempt($username, $success, $details = '') {
        $query = "
            INSERT INTO login_attempts_tbl (username, ip_address, user_agent, success, details, attempt_time) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ";
        
        $this->db->getConnection()->prepare($query)->execute([
            $username,
            $this->getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $success ? 1 : 0,
            $details
        ]);
    }
    
    /**
     * Generate secure random token
     */
    private function generateSecureToken($length = 64) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Update user's auth token
     */
    private function updateAuthToken($userId, $token) {
        $query = "UPDATE user_tbl SET auth_token = ? WHERE user_id = ?";
        $this->db->getConnection()->prepare($query)->execute([$token, $userId]);
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Destroy session securely
     */
    public function destroySession() {
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
    
    /**
     * Change user password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get current password hash
            $query = "SELECT pass_word FROM user_tbl WHERE user_id = ?";
            $result = $this->db->select($query, [$userId]);
            
            if (empty($result)) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $result[0]['pass_word'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            // Validate new password strength
            $validation = $this->validatePassword($newPassword);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password and generate new auth token
            $newAuthToken = $this->generateSecureToken();
            $updateQuery = "UPDATE user_tbl SET pass_word = ?, auth_token = ? WHERE user_id = ?";
            $this->db->getConnection()->prepare($updateQuery)->execute([
                $hashedPassword, 
                $newAuthToken, 
                $userId
            ]);
            
            // Update session auth token
            $_SESSION['auth_token'] = $newAuthToken;
            
            return ['success' => true, 'message' => 'Password changed successfully'];
            
        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while changing password'];
        }
    }
    
    /**
     * Validate password strength
     */
    private function validatePassword($password) {
        if (strlen($password) < 8) {
            return ['valid' => false, 'message' => 'Password must be at least 8 characters long'];
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one uppercase letter'];
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one lowercase letter'];
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one number'];
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one special character'];
        }
        
        return ['valid' => true, 'message' => 'Password is strong'];
    }
    
    /**
     * Create user account with secure password
     */
    public function createUser($userData) {
        try {
            // Validate required fields
            $required = ['username', 'password', 'fname', 'lname', 'email', 'user_type', 'position_id'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    return ['success' => false, 'message' => "Missing required field: $field"];
                }
            }
            
            // Check if username already exists
            $checkQuery = "SELECT user_id FROM user_tbl WHERE user_name = ?";
            $existing = $this->db->select($checkQuery, [$userData['username']]);
            if (!empty($existing)) {
                return ['success' => false, 'message' => 'Username already exists'];
            }
            
            // Check if email already exists
            $emailQuery = "SELECT profile_id FROM profile_tbl WHERE email = ?";
            $existingEmail = $this->db->select($emailQuery, [$userData['email']]);
            if (!empty($existingEmail)) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            // Validate password
            $passwordValidation = $this->validatePassword($userData['password']);
            if (!$passwordValidation['valid']) {
                return ['success' => false, 'message' => $passwordValidation['message']];
            }
            
            $connection = $this->db->getConnection();
            $connection->beginTransaction();
            
            try {
                // Create profile
                $profileQuery = "
                    INSERT INTO profile_tbl (fname, mname, lname, auxname, gender, birthdate, contact_number, email) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ";
                $profileStmt = $connection->prepare($profileQuery);
                $profileStmt->execute([
                    $userData['fname'],
                    $userData['mname'] ?? '',
                    $userData['lname'],
                    $userData['auxname'] ?? '',
                    $userData['gender'] ?? 'Not specified',
                    $userData['birthdate'] ?? '1990-01-01',
                    $userData['contact_number'] ?? '',
                    $userData['email']
                ]);
                
                $profileId = $connection->lastInsertId();
                
                // Create user
                $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
                $authToken = $this->generateSecureToken();
                
                $userQuery = "
                    INSERT INTO user_tbl (user_name, pass_word, position_id, profile_id, user_type, auth_token) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ";
                $userStmt = $connection->prepare($userQuery);
                $userStmt->execute([
                    $userData['username'],
                    $hashedPassword,
                    $userData['position_id'],
                    $profileId,
                    $userData['user_type'],
                    $authToken
                ]);
                
                $connection->commit();
                
                return ['success' => true, 'message' => 'User created successfully'];
                
            } catch (Exception $e) {
                $connection->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("User creation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while creating user'];
        }
    }
}

// Global authenticate function for backward compatibility
function authenticate($username, $password) {
    $auth = new AuthenticationManager();
    return $auth->authenticate($username, $password);
}
?>