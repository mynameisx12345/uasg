<?php
session_start();
require_once("../resources/objects/db_config.php");
require_once("../resources/objects/main_class.php");

// Simulate admin session for testing
if (!isset($_SESSION['user_id'])) {
    echo "Session not set. Need to login first.<br>";
    echo "<a href='../login.php'>Login</a>";
    exit;
}

echo "<h2>Testing Members AJAX Endpoints</h2>";
echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
echo "<p>User Type: " . ($_SESSION['user_type'] ?? 'not set') . "</p>";

// Test CALL 47 - Active Members
echo "<h3>Test CALL 47 - Active Members</h3>";
try {
    $conn = Database::getInstance()->getConnection();
    
    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.user_name,
            p.fname,
            p.mname,
            p.lname,
            p.auxname,
            p.email,
            p.contact_number,
            (SELECT COUNT(*) FROM task_submission_tbl ts 
             JOIN file_upload_tbl f ON ts.file_upload_id = f.file_upload_id 
             WHERE f.uploaded_by = u.user_id) as submission_count,
            (SELECT COUNT(*) FROM file_upload_tbl f WHERE f.uploaded_by = u.user_id) as upload_count
        FROM user_tbl u
        JOIN profile_tbl p ON u.profile_id = p.profile_id
        WHERE u.user_type = 'student' 
        AND (u.is_active = 1 OR u.is_active IS NULL)
        ORDER BY p.lname, p.fname
    ");
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Success! Found " . count($members) . " active members<br>";
    echo "<pre>" . json_encode(["data" => $members], JSON_PRETTY_PRINT) . "</pre>";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test CALL 48 - Inactive Members
echo "<h3>Test CALL 48 - Inactive Members</h3>";
try {
    $conn = Database::getInstance()->getConnection();
    
    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.user_name,
            p.fname,
            p.mname,
            p.lname,
            p.auxname,
            p.email,
            p.contact_number,
            u.deactivated_at
        FROM user_tbl u
        JOIN profile_tbl p ON u.profile_id = p.profile_id
        WHERE u.user_type = 'student' 
        AND u.is_active = 0
        ORDER BY u.deactivated_at DESC
    ");
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Success! Found " . count($members) . " inactive members<br>";
    echo "<pre>" . json_encode(["data" => $members], JSON_PRETTY_PRINT) . "</pre>";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
