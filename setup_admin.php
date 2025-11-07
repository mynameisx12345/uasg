<?php
// Admin Account Setup Script
// Run this file once to create an admin account
require_once("resources/objects/db_config.php");
require_once("resources/auth.php");

// Configuration
$ADMIN_CONFIG = [
    'username' => 'admin',
    'password' => 'Admin123!',  // Change this to a secure password
    'fname' => 'System',
    'lname' => 'Administrator',
    'email' => 'admin@system.local',
    'user_type' => 'admin',
    'position_id' => 1  // Assuming position_id 1 is for admin
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Account Setup</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
            background-color: #0d1117;
            color: #e6edf3;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 6px;
            padding: 32px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 16px 32px rgba(1, 4, 9, 0.85);
        }
        h1 {
            margin: 0 0 24px 0;
            font-size: 24px;
            font-weight: 600;
            text-align: center;
            color: #f0f6fc;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #f0f6fc;
        }
        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 8px 12px;
            background: #0d1117;
            border: 1px solid #30363d;
            border-radius: 6px;
            color: #e6edf3;
            font-size: 14px;
            box-sizing: border-box;
        }
        input[type="text"]:focus, input[type="password"]:focus, input[type="email"]:focus {
            outline: none;
            border-color: #1f6feb;
            box-shadow: 0 0 0 3px rgba(31, 111, 235, 0.3);
        }
        .btn {
            background: #238636;
            color: #fff;
            border: 1px solid rgba(240, 246, 252, 0.1);
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background: #2ea043;
        }
        .btn:disabled {
            background: #21262d;
            color: #7d8590;
            cursor: not-allowed;
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            border: 1px solid;
        }
        .alert-success {
            background: rgba(46, 160, 67, 0.15);
            border-color: #2ea043;
            color: #4ac26b;
        }
        .alert-error {
            background: rgba(248, 81, 73, 0.15);
            border-color: #f85149;
            color: #ff7b72;
        }
        .alert-warning {
            background: rgba(187, 128, 9, 0.15);
            border-color: #bb8009;
            color: #ffdf5d;
        }
        .requirements {
            background: #0d1117;
            border: 1px solid #30363d;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 16px;
            font-size: 12px;
        }
        .requirements h4 {
            margin: 0 0 8px 0;
            color: #f0f6fc;
        }
        .requirements ul {
            margin: 0;
            padding-left: 16px;
        }
        .requirements li {
            margin-bottom: 4px;
            color: #7d8590;
        }
        .info {
            background: rgba(31, 111, 235, 0.15);
            border: 1px solid #1f6feb;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 16px;
            color: #79c0ff;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Admin Account Setup</h1>
        
        <?php
        $message = '';
        $messageType = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Create login attempts table first
                $db = Database::getInstance();
                $connection = $db->getConnection();
                
                // Create login_attempts_tbl if it doesn't exist
                $createTableQuery = "
                    CREATE TABLE IF NOT EXISTS login_attempts_tbl (
                        attempt_id INT PRIMARY KEY AUTO_INCREMENT,
                        username VARCHAR(100) NOT NULL,
                        ip_address VARCHAR(45) NOT NULL,
                        user_agent TEXT,
                        success TINYINT(1) DEFAULT 0,
                        details TEXT,
                        attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_username_time (username, attempt_time),
                        INDEX idx_success_time (success, attempt_time)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ";
                
                $connection->exec($createTableQuery);
                
                // Get form data
                $adminData = [
                    'username' => trim($_POST['username']),
                    'password' => $_POST['password'],
                    'fname' => trim($_POST['fname']),
                    'lname' => trim($_POST['lname']),
                    'email' => trim($_POST['email']),
                    'user_type' => 'admin',
                    'position_id' => intval($_POST['position_id'])
                ];
                
                // Create admin account
                $auth = new AuthenticationManager();
                $result = $auth->createUser($adminData);
                
                if ($result['success']) {
                    $message = "✅ Admin account created successfully!<br><br>
                              <strong>Username:</strong> " . htmlspecialchars($adminData['username']) . "<br>
                              <strong>Email:</strong> " . htmlspecialchars($adminData['email']) . "<br><br>
                              You can now <a href='login.php' style='color: #79c0ff;'>login to the system</a>.";
                    $messageType = 'success';
                } else {
                    $message = "❌ " . $result['message'];
                    $messageType = 'error';
                }
                
            } catch (Exception $e) {
                $message = "❌ Database error: " . $e->getMessage();
                $messageType = 'error';
                error_log("Admin setup error: " . $e->getMessage());
            }
        }
        
        // Check if admin already exists
        $adminExists = false;
        try {
            $db = Database::getInstance();
            $checkQuery = "SELECT COUNT(*) as count FROM user_tbl WHERE user_type = 'admin'";
            $result = $db->select($checkQuery);
            $adminExists = $result[0]['count'] > 0;
        } catch (Exception $e) {
            // Database might not be set up yet
        }
        ?>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($adminExists && $messageType !== 'success'): ?>
            <div class="alert alert-warning">
                ⚠️ An admin account already exists in the system. This will create an additional admin account.
            </div>
        <?php endif; ?>
        
        <div class="info">
            📋 This script will create a secure admin account for your system. Make sure to use a strong password and delete this file after setup.
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($ADMIN_CONFIG['username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($ADMIN_CONFIG['password']); ?>" required>
                <div class="requirements">
                    <h4>Password Requirements:</h4>
                    <ul>
                        <li>At least 8 characters long</li>
                        <li>Contains uppercase letter (A-Z)</li>
                        <li>Contains lowercase letter (a-z)</li>
                        <li>Contains number (0-9)</li>
                        <li>Contains special character (!@#$%^&*)</li>
                    </ul>
                </div>
            </div>
            
            <div class="form-group">
                <label for="fname">First Name:</label>
                <input type="text" id="fname" name="fname" value="<?php echo htmlspecialchars($ADMIN_CONFIG['fname']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="lname">Last Name:</label>
                <input type="text" id="lname" name="lname" value="<?php echo htmlspecialchars($ADMIN_CONFIG['lname']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($ADMIN_CONFIG['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="position_id">Position ID:</label>
                <input type="text" id="position_id" name="position_id" value="<?php echo $ADMIN_CONFIG['position_id']; ?>" required>
                <small style="color: #7d8590; font-size: 12px;">Enter the position_id from position_tbl for admin role</small>
            </div>
            
            <button type="submit" class="btn">🚀 Create Admin Account</button>
        </form>
        
        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #30363d; text-align: center; font-size: 12px; color: #7d8590;">
            🔒 Security Features: Password hashing, session tokens, login attempt tracking, IP validation
        </div>
    </div>
</body>
</html>