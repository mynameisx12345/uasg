<?php
session_start();
require_once("../resources/session.php");
require_once("../resources/objects/permission_class.php");

$session = SessionManager::getInstance();
$session->requireRole(['Adviser', 'adviser', 'Subadmin', 'subadmin']);
$currentUser = $session->getUserData();
$userId = $currentUser['user_id'] ?? null;

if (!$userId || !SubadminPermission::hasPermission($userId, 'user_management', 'view')) {
    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management - UASG</title>
  <link rel="stylesheet" href="../resources/style.css">
  <link rel="stylesheet" href="../resources/theme-overrides.css">
  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
  <script src='../js/datatable.js'></script>
  <style>
    .tab-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .tab-header .tabs { margin-bottom: 0; }
    .table-container { margin-top: 0.5rem; }
    #usersTable td { vertical-align: middle; font-size: 12px; padding: 10px 12px; }
    #usersTable th { font-size: 12px; padding: 10px 12px; }
    #usersTable thead th { background: #f8f9fa !important; color: #475569 !important; border-bottom: 2px solid #e2e8f0 !important; font-weight: 600; }
    #usersTable tbody tr { transition: background 0.15s, box-shadow 0.15s; }
    #usersTable tbody tr:hover { background: #f8fafc !important; box-shadow: inset 3px 0 0 #3b82f6; }
    .type-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .type-admin { background: linear-gradient(135deg, #7b1228, #a01535); color: #fff; }
    .type-subadmin { background: linear-gradient(135deg, #c89b2e, #d4a93a); color: #fff; }
    .type-student { background: linear-gradient(135deg, #17a2b8, #1dbfd4); color: #fff; }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <?php require_once("sidebar.php"); ?>
    <div class="main-content">
      <?php require_once("header.php"); ?>
      <div class="dashboard-content">
        <div class="card">
          <div class="tab-header">
            <div class="tabs"><button class="tab-btn active">👥 Users</button></div>
          </div>
          <div class="table-container">
            <table id="usersTable" class="data-table">
              <thead><tr><th>ID</th><th>Username</th><th>Full Name</th><th>User Type</th><th>Email</th><th>Contact</th></tr></thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
  (function($){
    const typeLabels = { admin: 'Admin', subadmin: 'Sub-admin', student: 'Member' };
    var table = $('#usersTable').DataTable({
      ajax: { url: 'ajax.php', type: 'POST', data: { CALL: 68 }, dataSrc: function(j){ return j.data||[]; } },
      columns: [
        { data: 'user_id' },
        { data: 'user_name' },
        { data: null, render: function(d,t,row){ return `${row.fname} ${row.mname?row.mname+' ':''}${row.lname}${row.auxname?' '+row.auxname:''}`; } },
        { data: 'user_type', render: function(val){ return `<span class="type-badge type-${val}">${typeLabels[val]||val}</span>`; } },
        { data: 'email' },
        { data: 'contact_number' }
      ],
      initComplete: function(){
        var q = new URLSearchParams(window.location.search).get('search');
        if(q) table.search(q).draw();
      }
    });
  })(jQuery);
  </script>

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
