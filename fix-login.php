<?php
// SIMPLE DIRECT LOGIN TEST
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Direct Login Test</h1>";

// Test the exact login process
$username = 'justin.abuela';
$password = 'Cristenmabanforever22@@';

echo "<p>Testing login with:</p>";
echo "<ul>";
echo "<li>Username: <strong>$username</strong></li>";
echo "<li>Password: <strong>$password</strong></li>";
echo "</ul>";

// Step 1: Database connection
echo "<h2>Step 1: Database Connection</h2>";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uasg_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Connected to database</p>";
} catch (PDOException $e) {
    die("<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>");
}

// Step 2: Get user from database
echo "<h2>Step 2: Fetch User Data</h2>";
$query = "SELECT u.user_id, u.user_name, u.pass_word, u.user_type FROM user_tbl u WHERE u.user_name = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("<p style='color: red;'>❌ User '$username' not found!</p>");
}

echo "<p style='color: green;'>✅ User found in database</p>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Value</th></tr>";
echo "<tr><td>user_id</td><td>{$user['user_id']}</td></tr>";
echo "<tr><td>user_name</td><td>{$user['user_name']}</td></tr>";
echo "<tr><td>user_type</td><td>{$user['user_type']}</td></tr>";
echo "<tr><td>password_hash (first 50 chars)</td><td>" . substr($user['pass_word'], 0, 50) . "...</td></tr>";
echo "</table>";

// Step 3: Verify password
echo "<h2>Step 3: Password Verification</h2>";
echo "<p>Stored hash: <code style='font-size: 10px;'>{$user['pass_word']}</code></p>";
echo "<p>Testing password: <strong>$password</strong></p>";

$verified = password_verify($password, $user['pass_word']);

if ($verified) {
    echo "<p style='color: green; font-size: 20px; font-weight: bold;'>✅✅✅ PASSWORD VERIFIED! Login should work!</p>";
    echo "<hr>";
    echo "<h2>✅ LOGIN SUCCESSFUL</h2>";
    echo "<p>You should be able to login with these credentials on the main login page.</p>";
    echo "<p><a href='index.php' style='font-size: 18px; background: green; color: white; padding: 10px; text-decoration: none; display: inline-block;'>Go to Login Page</a></p>";
} else {
    echo "<p style='color: red; font-size: 20px; font-weight: bold;'>❌ PASSWORD VERIFICATION FAILED!</p>";
    echo "<p>The password 'Admin123!@#' does NOT match the hash in the database.</p>";
    
    echo "<hr>";
    echo "<h2>Let's Fix This</h2>";
    echo "<p>I'll now set the password to 'Admin123!@#' for you:</p>";
    
    // Generate new hash
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    echo "<p>New hash: <code style='font-size: 10px;'>$newHash</code></p>";
    
    // Update database
    try {
        $updateQuery = "UPDATE user_tbl SET pass_word = ? WHERE user_name = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$newHash, $username]);
        
        echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅✅✅ PASSWORD UPDATED SUCCESSFULLY!</p>";
        
        // Verify the new password works
        $verifyNew = password_verify($password, $newHash);
        if ($verifyNew) {
            echo "<p style='color: green;'>✅ New password verified successfully!</p>";
            echo "<p><strong>You can now login with:</strong></p>";
            echo "<ul style='font-size: 18px;'>";
            echo "<li>Username: <strong>admin</strong></li>";
            echo "<li>Password: <strong>Admin123!@#</strong></li>";
            echo "</ul>";
            
            // Clear failed login attempts
            $pdo->exec("DELETE FROM login_attempts_tbl WHERE username = 'admin'");
            echo "<p style='color: green;'>✅ Cleared all failed login attempts</p>";
            
            echo "<p><a href='index.php' style='font-size: 18px; background: green; color: white; padding: 10px; text-decoration: none; display: inline-block;'>Go to Login Page Now</a></p>";
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Update failed: " . $e->getMessage() . "</p>";
    }
}

// Step 4: Check login attempts and lockout
echo "<hr>";
echo "<h2>Step 4: Check Login Attempts</h2>";

$attemptsQuery = "SELECT COUNT(*) as failed_attempts FROM login_attempts_tbl WHERE username = ? AND success = 0 AND attempt_time > ?";
$cutoff = date('Y-m-d H:i:s', time() - 900);
$attemptsStmt = $pdo->prepare($attemptsQuery);
$attemptsStmt->execute([$username, $cutoff]);
$failedAttempts = $attemptsStmt->fetch(PDO::FETCH_ASSOC)['failed_attempts'];

if ($failedAttempts >= 5) {
    echo "<p style='color: red; font-weight: bold;'>⚠️ ACCOUNT IS LOCKED! ($failedAttempts failed attempts in last 15 minutes)</p>";
    echo "<p>Clearing failed attempts now...</p>";
    $pdo->exec("DELETE FROM login_attempts_tbl WHERE username = 'admin'");
    echo "<p style='color: green;'>✅ Account unlocked!</p>";
} else {
    echo "<p style='color: green;'>✅ Account is not locked ($failedAttempts failed attempts)</p>";
}

// Show recent attempts
$recentQuery = "SELECT * FROM login_attempts_tbl WHERE username = ? ORDER BY attempt_time DESC LIMIT 5";
$recentStmt = $pdo->prepare($recentQuery);
$recentStmt->execute([$username]);
$recent = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

if ($recent) {
    echo "<h3>Recent Login Attempts:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Time</th><th>Success</th><th>Details</th></tr>";
    foreach ($recent as $attempt) {
        $success = $attempt['success'] ? '<span style="color: green;">✅</span>' : '<span style="color: red;">❌</span>';
        echo "<tr><td>{$attempt['attempt_time']}</td><td>$success</td><td>{$attempt['details']}</td></tr>";
    }
    echo "</table>";
}
?>
