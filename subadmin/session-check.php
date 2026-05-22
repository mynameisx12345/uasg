<?php
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$timeout = 1800; // 30 minutes

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'expired']);
    exit;
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    echo json_encode(['status' => 'expired']);
    exit;
}

$_SESSION['last_activity'] = time();
echo json_encode(['status' => 'active']);
