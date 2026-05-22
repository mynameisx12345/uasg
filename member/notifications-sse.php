<?php
session_start();
require_once("../resources/session.php");
$session = SessionManager::getInstance();
$session->requireRole(['student']);

require_once("../resources/objects/db_config.php");
require_once("../resources/objects/main_class.php");

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

$userId = $_SESSION['user_id'] ?? 0;
if (!$userId) { echo "data: {}\n\n"; exit; }

$nm = new NotificationManager();
$lastCount = -1;

$end = time() + 55;
while (time() < $end) {
    $count = (int)$nm->getUnreadCount($userId);
    if ($count !== $lastCount) {
        $lastCount = $count;
        echo "data: " . json_encode(['count' => $count]) . "\n\n";
        ob_flush(); flush();
    }
    sleep(3);
}
echo ": keep-alive\n\n";
ob_flush(); flush();
