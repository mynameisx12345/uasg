<?php
require_once '../resources/session.php';
require_once '../resources/objects/main_class.php';

// Initialize session and check authentication
$session = new SessionManager();
if (!$session->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 'ERROR', 'msg' => 'Unauthorized']);
    exit;
}

$userData = $session->getUserData();
$mainClass = new main_class();

// Get the AJAX call
$call = $_POST['CALL'] ?? '';

// Set JSON header
header('Content-Type: application/json');

try {
    switch ($call) {
        case 'update_profile':
            echo json_encode(updateProfile($mainClass, $userData['user_id'], $_POST['DATA'] ?? []));
            break;
            
        case 'change_password':
            echo json_encode(changePassword($mainClass, $userData['user_id'], $_POST['DATA'] ?? []));
            break;
            
        case 'get_login_activity':
            echo json_encode(getLoginActivity($mainClass, $userData['user_id']));
            break;
            
        case 'get_user_preferences':
            echo json_encode(getUserPreferences($mainClass, $userData['user_id']));
            break;
            
        case 'save_preferences':
            echo json_encode(savePreferences($mainClass, $userData['user_id'], $_POST['DATA'] ?? []));
            break;
            
        case 'save_dashboard_settings':
            echo json_encode(saveDashboardSettings($mainClass, $userData['user_id'], $_POST['DATA'] ?? []));
            break;
            
        default:
            echo json_encode([
                'status' => 'ERROR',
                'msg' => 'Invalid AJAX call'
            ]);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'ERROR',
        'msg' => 'Server error: ' . $e->getMessage()
    ]);
}

// Update user profile
function updateProfile($mainClass, $userId, $data) {
    try {
        // Validate required fields
        if (empty($data['fname']) || empty($data['lname']) || empty($data['email'])) {
            return [
                'status' => 'ERROR',
                'msg' => 'First name, last name, and email are required'
            ];
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => 'ERROR',
                'msg' => 'Invalid email format'
            ];
        }
        
        // Check if email is already taken by another user
        $emailCheck = $mainClass->db->query("
            SELECT user_id FROM user_member 
            WHERE email = ? AND user_id != ?
        ", [$data['email'], $userId]);
        
        if ($emailCheck->num_rows > 0) {
            return [
                'status' => 'ERROR',
                'msg' => 'Email address is already in use by another account'
            ];
        }
        
        // Update user profile
        $updateQuery = "
            UPDATE user_member SET 
                fname = ?, 
                lname = ?, 
                email = ?, 
                contact = ?, 
                address = ?, 
                department = ?
            WHERE user_id = ?
        ";
        
        $mainClass->db->query($updateQuery, [
            $data['fname'],
            $data['lname'],
            $data['email'],
            $data['contact'] ?? null,
            $data['address'] ?? null,
            $data['department'] ?? null,
            $userId
        ]);
        
        // Update session data
        $session = new SessionManager();
        $session->updateUserData([
            'fname' => $data['fname'],
            'lname' => $data['lname'],
            'email' => $data['email'],
            'contact' => $data['contact'] ?? null,
            'address' => $data['address'] ?? null,
            'department' => $data['department'] ?? null
        ]);
        
        return [
            'status' => 'SUCCESS',
            'msg' => 'Profile updated successfully'
        ];
    } catch (Exception $e) {
        return [
            'status' => 'ERROR',
            'msg' => 'Failed to update profile: ' . $e->getMessage()
        ];
    }
}

// Change user password
function changePassword($mainClass, $userId, $data) {
    try {
        // Validate required fields
        if (empty($data['current_password']) || empty($data['new_password'])) {
            return [
                'status' => 'ERROR',
                'msg' => 'Current password and new password are required'
            ];
        }
        
        // Validate new password length
        if (strlen($data['new_password']) < 6) {
            return [
                'status' => 'ERROR',
                'msg' => 'New password must be at least 6 characters long'
            ];
        }
        
        // Get current user data
        $user = $mainClass->db->query("
            SELECT password FROM user_member WHERE user_id = ?
        ", [$userId])->fetch_assoc();
        
        if (!$user) {
            return [
                'status' => 'ERROR',
                'msg' => 'User not found'
            ];
        }
        
        // Verify current password
        if (!password_verify($data['current_password'], $user['password'])) {
            return [
                'status' => 'ERROR',
                'msg' => 'Current password is incorrect'
            ];
        }
        
        // Hash new password
        $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
        
        // Update password
        $mainClass->db->query("
            UPDATE user_member SET password = ? WHERE user_id = ?
        ", [$hashedPassword, $userId]);
        
        return [
            'status' => 'SUCCESS',
            'msg' => 'Password changed successfully'
        ];
    } catch (Exception $e) {
        return [
            'status' => 'ERROR',
            'msg' => 'Failed to change password: ' . $e->getMessage()
        ];
    }
}

// Get login activity (mock implementation)
function getLoginActivity($mainClass, $userId) {
    try {
        // This is a mock implementation since login activity tracking would need to be implemented
        // In a real system, you would have a login_logs table
        
        // Mock data for demonstration
        $activities = [
            [
                'login_time' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'logout_time' => date('Y-m-d H:i:s', strtotime('-23 hours')),
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'is_current' => false
            ],
            [
                'login_time' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'logout_time' => date('Y-m-d H:i:s', strtotime('-3 days +2 hours')),
                'ip_address' => '192.168.1.101',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'is_current' => false
            ]
        ];
        
        return [
            'status' => 'SUCCESS',
            'data' => $activities
        ];
    } catch (Exception $e) {
        return [
            'status' => 'ERROR',
            'msg' => 'Failed to load login activity'
        ];
    }
}

// Get user preferences
function getUserPreferences($mainClass, $userId) {
    try {
        // Check if user_preferences table exists, if not return defaults
        $tableExists = $mainClass->db->query("
            SHOW TABLES LIKE 'user_preferences'
        ")->num_rows > 0;
        
        if (!$tableExists) {
            // Return default preferences
            return [
                'status' => 'SUCCESS',
                'data' => [
                    'email_notifications' => 1,
                    'task_reminders' => 1,
                    'submission_updates' => 1,
                    'reminder_time' => 48,
                    'items_per_page' => 10,
                    'default_view' => 'dashboard',
                    'auto_refresh' => 0
                ]
            ];
        }
        
        $preferences = $mainClass->db->query("
            SELECT * FROM user_preferences WHERE user_id = ?
        ", [$userId])->fetch_assoc();
        
        if (!$preferences) {
            // Create default preferences for user
            $defaultPrefs = [
                'email_notifications' => 1,
                'task_reminders' => 1,
                'submission_updates' => 1,
                'reminder_time' => 48,
                'items_per_page' => 10,
                'default_view' => 'dashboard',
                'auto_refresh' => 0
            ];
            
            $insertQuery = "
                INSERT INTO user_preferences (
                    user_id, email_notifications, task_reminders, submission_updates,
                    reminder_time, items_per_page, default_view, auto_refresh
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ";
            
            $mainClass->db->query($insertQuery, [
                $userId,
                $defaultPrefs['email_notifications'],
                $defaultPrefs['task_reminders'],
                $defaultPrefs['submission_updates'],
                $defaultPrefs['reminder_time'],
                $defaultPrefs['items_per_page'],
                $defaultPrefs['default_view'],
                $defaultPrefs['auto_refresh']
            ]);
            
            return [
                'status' => 'SUCCESS',
                'data' => $defaultPrefs
            ];
        }
        
        return [
            'status' => 'SUCCESS',
            'data' => $preferences
        ];
    } catch (Exception $e) {
        // Return defaults on error
        return [
            'status' => 'SUCCESS',
            'data' => [
                'email_notifications' => 1,
                'task_reminders' => 1,
                'submission_updates' => 1,
                'reminder_time' => 48,
                'items_per_page' => 10,
                'default_view' => 'dashboard',
                'auto_refresh' => 0
            ]
        ];
    }
}

// Save user preferences
function savePreferences($mainClass, $userId, $data) {
    try {
        // Ensure user_preferences table exists
        createUserPreferencesTable($mainClass);
        
        // Check if preferences exist for user
        $existing = $mainClass->db->query("
            SELECT user_id FROM user_preferences WHERE user_id = ?
        ", [$userId])->fetch_assoc();
        
        if ($existing) {
            // Update existing preferences
            $updateQuery = "
                UPDATE user_preferences SET 
                    email_notifications = ?, 
                    task_reminders = ?, 
                    submission_updates = ?, 
                    reminder_time = ?
                WHERE user_id = ?
            ";
            
            $mainClass->db->query($updateQuery, [
                $data['email_notifications'],
                $data['task_reminders'],
                $data['submission_updates'],
                $data['reminder_time'],
                $userId
            ]);
        } else {
            // Insert new preferences
            $insertQuery = "
                INSERT INTO user_preferences (
                    user_id, email_notifications, task_reminders, 
                    submission_updates, reminder_time
                ) VALUES (?, ?, ?, ?, ?)
            ";
            
            $mainClass->db->query($insertQuery, [
                $userId,
                $data['email_notifications'],
                $data['task_reminders'],
                $data['submission_updates'],
                $data['reminder_time']
            ]);
        }
        
        return [
            'status' => 'SUCCESS',
            'msg' => 'Preferences saved successfully'
        ];
    } catch (Exception $e) {
        return [
            'status' => 'ERROR',
            'msg' => 'Failed to save preferences: ' . $e->getMessage()
        ];
    }
}

// Save dashboard settings
function saveDashboardSettings($mainClass, $userId, $data) {
    try {
        // Ensure user_preferences table exists
        createUserPreferencesTable($mainClass);
        
        // Check if preferences exist for user
        $existing = $mainClass->db->query("
            SELECT user_id FROM user_preferences WHERE user_id = ?
        ", [$userId])->fetch_assoc();
        
        if ($existing) {
            // Update existing preferences
            $updateQuery = "
                UPDATE user_preferences SET 
                    items_per_page = ?, 
                    default_view = ?, 
                    auto_refresh = ?
                WHERE user_id = ?
            ";
            
            $mainClass->db->query($updateQuery, [
                $data['items_per_page'],
                $data['default_view'],
                $data['auto_refresh'],
                $userId
            ]);
        } else {
            // Insert new preferences with defaults
            $insertQuery = "
                INSERT INTO user_preferences (
                    user_id, items_per_page, default_view, auto_refresh,
                    email_notifications, task_reminders, submission_updates, reminder_time
                ) VALUES (?, ?, ?, ?, 1, 1, 1, 48)
            ";
            
            $mainClass->db->query($insertQuery, [
                $userId,
                $data['items_per_page'],
                $data['default_view'],
                $data['auto_refresh']
            ]);
        }
        
        return [
            'status' => 'SUCCESS',
            'msg' => 'Dashboard settings saved successfully'
        ];
    } catch (Exception $e) {
        return [
            'status' => 'ERROR',
            'msg' => 'Failed to save dashboard settings: ' . $e->getMessage()
        ];
    }
}

// Helper function to create user_preferences table if it doesn't exist
function createUserPreferencesTable($mainClass) {
    try {
        $createTableQuery = "
            CREATE TABLE IF NOT EXISTS user_preferences (
                preference_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                email_notifications TINYINT(1) DEFAULT 1,
                task_reminders TINYINT(1) DEFAULT 1,
                submission_updates TINYINT(1) DEFAULT 1,
                reminder_time INT DEFAULT 48,
                items_per_page INT DEFAULT 10,
                default_view VARCHAR(50) DEFAULT 'dashboard',
                auto_refresh TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES user_member(user_id) ON DELETE CASCADE,
                UNIQUE KEY unique_user_preferences (user_id)
            )
        ";
        
        $mainClass->db->query($createTableQuery);
    } catch (Exception $e) {
        // Table creation failed, but we can continue with default preferences
        error_log("Failed to create user_preferences table: " . $e->getMessage());
    }
}
?>