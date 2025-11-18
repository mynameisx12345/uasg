<?php
// Debug Login Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Login Debug Script</h1>";

// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uasg_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch (PDOException $e) {
    die("<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>");
}

// Check if user exists
echo "<h2>1. Checking User in Database</h2>";
$query = "SELECT u.user_id, u.user_name, u.pass_word, u.user_type, 
          p.fname, p.lname, p.email,
          pos.position
          FROM user_tbl u
          JOIN profile_tbl p ON u.profile_id = p.profile_id
          JOIN position_tbl pos ON u.position_id = pos.position_id
          WHERE u.user_name = ?";

$stmt = $pdo->prepare($query);
$stmt->execute(['admin']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "<p style='color: green;'>✅ User 'admin' found in database</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    echo "<tr><td>user_id</td><td>{$user['user_id']}</td></tr>";
    echo "<tr><td>username</td><td>{$user['user_name']}</td></tr>";
    echo "<tr><td>user_type</td><td>{$user['user_type']}</td></tr>";
    echo "<tr><td>position</td><td>{$user['position']}</td></tr>";
    echo "<tr><td>full_name</td><td>{$user['fname']} {$user['lname']}</td></tr>";
    echo "<tr><td>email</td><td>{$user['email']}</td></tr>";
    echo "<tr><td>password_hash</td><td><code style='font-size: 10px;'>{$user['pass_word']}</code></td></tr>";
    echo "</table>";
} else {
    die("<p style='color: red;'>❌ User 'admin' NOT found in database!</p>");
}

// Test password verification
echo "<h2>2. Password Verification Test</h2>";

$testPasswords = [
    'admin',
    'admin123',
    'Admin123',
    'Admin123!',
    'password',
    '123456'
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Test Password</th><th>Verification Result</th></tr>";

foreach ($testPasswords as $testPass) {
    $result = password_verify($testPass, $user['pass_word']);
    $status = $result ? "<span style='color: green; font-weight: bold;'>✅ MATCH!</span>" : "<span style='color: red;'>❌ No match</span>";
    echo "<tr><td><strong>$testPass</strong></td><td>$status</td></tr>";
}

echo "</table>";

// Test custom password
echo "<h2>3. Test Your Password</h2>";
echo "<form method='post'>";
echo "Enter password to test: <input type='text' name='testpass' value='' />";
echo "<button type='submit' name='test'>Test Password</button>";
echo "</form>";

if (isset($_POST['test']) && !empty($_POST['testpass'])) {
    $customPass = $_POST['testpass'];
    $result = password_verify($customPass, $user['pass_word']);
    
    if ($result) {
        echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅✅✅ SUCCESS! Password '$customPass' is CORRECT!</p>";
        echo "<p>You can login with:</p>";
        echo "<ul>";
        echo "<li>Username: <strong>admin</strong></li>";
        echo "<li>Password: <strong>$customPass</strong></li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red; font-size: 18px;'>❌ Password '$customPass' is INCORRECT</p>";
    }
}

// Generate new password hash
echo "<h2>4. Generate New Password Hash</h2>";
echo "<form method='post'>";
echo "Enter NEW password: <input type='text' name='newpass' value='' />";
echo "<button type='submit' name='generate'>Generate Hash & Update SQL</button>";
echo "</form>";

if (isset($_POST['generate']) && !empty($_POST['newpass'])) {
    $newPass = $_POST['newpass'];
    $newHash = password_hash($newPass, PASSWORD_DEFAULT);
    
    echo "<p style='color: green;'>✅ Hash generated for password: <strong>$newPass</strong></p>";
    echo "<p><strong>Generated Hash:</strong></p>";
    echo "<code style='display: block; padding: 10px; background: #f5f5f5; word-break: break-all;'>$newHash</code>";
    
    echo "<p><strong>SQL to update database:</strong></p>";
    echo "<code style='display: block; padding: 10px; background: #f5f5f5;'>UPDATE user_tbl SET pass_word = '$newHash' WHERE user_name = 'admin';</code>";
    
    echo "<form method='post'>";
    echo "<input type='hidden' name='updatehash' value='$newHash' />";
    echo "<input type='hidden' name='updatepass' value='$newPass' />";
    echo "<button type='submit' name='update' style='background: red; color: white; padding: 10px; font-weight: bold;'>EXECUTE SQL UPDATE NOW</button>";
    echo "</form>";
}

// Execute update
if (isset($_POST['update']) && !empty($_POST['updatehash'])) {
    try {
        $updateQuery = "UPDATE user_tbl SET pass_word = ? WHERE user_name = 'admin'";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$_POST['updatehash']]);
        
        echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅✅✅ PASSWORD UPDATED SUCCESSFULLY!</p>";
        echo "<p>New login credentials:</p>";
        echo "<ul>";
        echo "<li>Username: <strong>admin</strong></li>";
        echo "<li>Password: <strong>{$_POST['updatepass']}</strong></li>";
        echo "</ul>";
        echo "<p><a href='index.php'>Click here to go to login page</a></p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Update failed: " . $e->getMessage() . "</p>";
    }
}

// Check login attempts
echo "<h2>5. Recent Login Attempts</h2>";
$attemptsQuery = "SELECT * FROM login_attempts_tbl WHERE username = 'admin' ORDER BY attempt_time DESC LIMIT 10";
$attemptsStmt = $pdo->prepare($attemptsQuery);
$attemptsStmt->execute();
$attempts = $attemptsStmt->fetchAll(PDO::FETCH_ASSOC);

if ($attempts) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Time</th><th>Success</th><th>Details</th><th>IP</th></tr>";
    foreach ($attempts as $attempt) {
        $success = $attempt['success'] ? '<span style="color: green;">✅ Success</span>' : '<span style="color: red;">❌ Failed</span>';
        echo "<tr>";
        echo "<td>{$attempt['attempt_time']}</td>";
        echo "<td>$success</td>";
        echo "<td>{$attempt['details']}</td>";
        echo "<td>{$attempt['ip_address']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No login attempts found</p>";
}

// Check for account lockout
echo "<h2>6. Account Lockout Status</h2>";
$lockoutQuery = "
    SELECT COUNT(*) as attempts, MAX(attempt_time) as last_attempt
    FROM login_attempts_tbl 
    WHERE username = 'admin' AND success = 0 AND attempt_time > ?
";
$cutoffTime = date('Y-m-d H:i:s', time() - 900); // 15 minutes
$lockoutStmt = $pdo->prepare($lockoutQuery);
$lockoutStmt->execute([$cutoffTime]);
$lockout = $lockoutStmt->fetch(PDO::FETCH_ASSOC);

if ($lockout['attempts'] >= 5) {
    echo "<p style='color: red; font-weight: bold;'>⚠️ ACCOUNT IS LOCKED! {$lockout['attempts']} failed attempts in last 15 minutes</p>";
    echo "<p>Last failed attempt: {$lockout['last_attempt']}</p>";
    echo "<form method='post'>";
    echo "<button type='submit' name='clearattempts' style='background: orange; color: white; padding: 10px;'>Clear Failed Attempts & Unlock Account</button>";
    echo "</form>";
} else {
    echo "<p style='color: green;'>✅ Account is NOT locked ({$lockout['attempts']} failed attempts in last 15 minutes)</p>";
}

// Clear failed attempts
if (isset($_POST['clearattempts'])) {
    $clearQuery = "DELETE FROM login_attempts_tbl WHERE username = 'admin'";
    $pdo->exec($clearQuery);
    echo "<p style='color: green; font-weight: bold;'>✅ All failed login attempts cleared! Account unlocked!</p>";
    echo "<script>setTimeout(function(){ location.reload(); }, 1000);</script>";
}
?>
