<?php
// Simple test script to check AJAX responses
session_start();
require_once("../resources/objects/db_config.php");
require_once("../resources/objects/main_class.php");

// Simulate admin session
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'admin';

header("Content-Type: application/json");

$call = $_GET['call'] ?? 14;

try {
    $fileManager = new FileManager();
    
    switch($call) {
        case 14:
            $files = $fileManager->getAllFiles(null);
            $response = ["data" => $files ?: [], "count" => count($files ?: [])];
            break;
            
        case 20:
            $permissions = $fileManager->getFilePermissions();
            $response = ["data" => $permissions ?: [], "count" => count($permissions ?: [])];
            break;
            
        default:
            $response = ["error" => "Unknown call"];
    }
    
} catch(Exception $e) {
    $response = [
        "error" => $e->getMessage(),
        "trace" => $e->getTraceAsString()
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
