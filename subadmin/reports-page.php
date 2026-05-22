<?php
session_start();
require_once("../resources/session.php");
require_once("../resources/objects/permission_class.php");

$session = SessionManager::getInstance();
$session->requireRole(['Adviser', 'adviser', 'Subadmin', 'subadmin']);
$currentUser = $session->getUserData();
$userId = $currentUser['user_id'] ?? null;

if (!$userId || !SubadminPermission::hasPermission($userId, 'reports', 'view')) {
    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports - UASG</title>
  <link rel="stylesheet" href="../resources/style.css">
  <link rel="stylesheet" href="../resources/theme-overrides.css">
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
  <style>
    .reports-iframe { width: 100%; border: none; height: calc(100vh - 120px); }
    .main-content .reports-wrap { padding: 24px; }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <?php require_once("sidebar.php"); ?>
    <div class="main-content">
      <?php require_once("header.php"); ?>
      <div class="reports-wrap">
        <iframe src="../admin/reports-page.php?embed=1" class="reports-iframe"></iframe>
      </div>
    </div>
  </div>

<!-- Logout Modal -->
<div id="logoutModal" class="modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:99999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:90%;text-align:center;">
    <h3 style="margin:0 0 12px;">Confirm Logout</h3>
    <p style="color:#666;margin-bottom:20px;">Are you sure you want to logout?</p>
    <div style="display:flex;gap:10px;justify-content:center;">
      <button onclick="closeModal('logoutModal')" style="padding:8px 20px;border:1px solid #ddd;border-radius:6px;background:#fff;cursor:pointer;">Cancel</button>
      <button onclick="window.location.href='logout.php'" style="padding:8px 20px;border:none;border-radius:6px;background:#7b1228;color:#fff;cursor:pointer;">Logout</button>
    </div>
  </div>
</div>
<script>
function openModal(id){document.getElementById(id).style.display='flex';}
function closeModal(id){document.getElementById(id).style.display='none';}
</script>
</body>
</html>
