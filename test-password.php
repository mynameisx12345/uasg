<?php
// Test password verification
$hashedPassword = '$2y$10$oDB5bY0WJheUhZFZO9jo/uACNilbIOv3C6jxl9fbRvd8BXAbZI/Qi';

// Test various passwords
$testPasswords = [
    'admin123',
    'Admin123',
    'Admin123!',
    'admin',
    '123456'
];

echo "<h2>Password Hash Test</h2>";
echo "<p><strong>Stored Hash:</strong> $hashedPassword</p>";
echo "<h3>Testing Passwords:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Password</th><th>Result</th></tr>";

foreach ($testPasswords as $password) {
    $result = password_verify($password, $hashedPassword);
    $status = $result ? '✅ MATCH' : '❌ NO MATCH';
    echo "<tr><td>$password</td><td>$status</td></tr>";
}

echo "</table>";

// Also show how to generate a new hash
echo "<h3>Generate New Hash:</h3>";
echo "<form method='post'>";
echo "Enter password: <input type='text' name='newpass' />";
echo "<button type='submit'>Generate Hash</button>";
echo "</form>";

if (isset($_POST['newpass'])) {
    $newHash = password_hash($_POST['newpass'], PASSWORD_DEFAULT);
    echo "<p><strong>Generated Hash:</strong><br><code>$newHash</code></p>";
    echo "<p>Use this SQL to update:<br>";
    echo "<code>UPDATE user_tbl SET pass_word = '$newHash' WHERE user_name = 'admin';</code></p>";
}
?>
