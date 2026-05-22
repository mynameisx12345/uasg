<?php
session_start();
require_once("../resources/session.php");
require_once("../resources/objects/permission_class.php");

$session = SessionManager::getInstance();
$session->requireRole(['Adviser', 'adviser', 'Subadmin', 'subadmin']);

$currentUser = $session->getUserData();
$userId = $currentUser['user_id'] ?? null;

if (!$userId) {
    header("Location: ../index.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Adviser Dashboard - UASG</title>
  <link rel="stylesheet" href="../resources/style.css">
  <link rel="stylesheet" href="../resources/theme-overrides.css">
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
  <style>
    .dashboard-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:20px; margin-bottom:30px; }
    .stat-card { background:#fff; padding:20px; border-radius:12px; border:none; box-shadow:0 1px 3px rgba(0,0,0,0.04); transition:transform 0.2s,box-shadow 0.2s; position:relative; overflow:hidden; }
    .stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#7b1228,#c89b2e); opacity:0.8; }
    .stat-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(123,18,40,0.08); }
    .stat-icon { font-size:28px; margin-bottom:8px; display:block; }
    .stat-value { font-size:32px; font-weight:700; color:#1e293b; margin:8px 0; line-height:1; }
    .stat-label { font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.8px; font-weight:600; }
    .stat-trend { font-size:11px; color:#b0b8c4; margin-top:6px; }
    .chart-container { background:transparent; padding:16px; border-radius:12px; box-shadow:none; border:none; margin-bottom:30px; }
    .chart-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; }
    .chart-title { font-size:15px; font-weight:600; color:#1e293b; }
    .category-bar { margin-bottom:0; padding:12px 0; background:transparent; border-radius:0; border-top:1px solid #f1f5f9; }
    .category-bar:first-child { border-top:none; }
    .category-label { display:flex; justify-content:space-between; margin-bottom:6px; font-size:12px; color:#475569; }
    .category-progress { height:6px; background:#f1f5f9; border-radius:3px; overflow:hidden; }
    .category-fill { height:100%; background:linear-gradient(90deg,#7b1228,#c89b2e); border-radius:3px; transition:width 0.6s ease; }
    .user-activity-list { max-height:400px; overflow-y:auto; }
    .user-activity-item { display:flex; justify-content:space-between; align-items:center; padding:12px 14px; margin-bottom:4px; background:#fafbfc; border-radius:8px; transition:all 0.15s; }
    .user-activity-item:hover { background:#f1f5f9; }
    .user-badge { display:inline-block; padding:3px 8px; border-radius:10px; font-size:10px; font-weight:600; text-transform:uppercase; }
    .user-badge.student { background:rgba(123,18,40,0.08); color:#7b1228; }
    .user-badge.subadmin { background:rgba(200,155,46,0.12); color:#b38712; }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <?php require_once("sidebar.php");?>
    <div class="main-content">
      <?php require_once("header.php");?>
      <div class="dashboard-content">

      <!-- Stat Cards -->
      <div class="dashboard-grid" style="grid-template-columns:repeat(4,1fr);gap:15px;margin-bottom:20px;">
        <div class="stat-card"><span class="stat-icon">📁</span><div class="stat-label">Files</div><div class="stat-value" id="totalFiles">—</div><div class="stat-trend">Uploaded to system</div></div>
        <div class="stat-card"><span class="stat-icon">🗂️</span><div class="stat-label">Categories</div><div class="stat-value" id="totalCategories">—</div><div class="stat-trend">Distinct file types</div></div>
        <div class="stat-card"><span class="stat-icon">⏳</span><div class="stat-label">Pending</div><div class="stat-value" id="pendingTasks">—</div><div class="stat-trend">Awaiting submission</div></div>
        <div class="stat-card"><span class="stat-icon">✅</span><div class="stat-label">Approved</div><div class="stat-value" id="totalSubmissions">—</div><div class="stat-trend">Approved tasks</div></div>
        <div class="stat-card"><span class="stat-icon">💾</span><div class="stat-label">Storage</div><div class="stat-value" id="totalStorage">—</div><div class="stat-trend">Total file size</div></div>
        <div class="stat-card"><span class="stat-icon">📋</span><div class="stat-label">Task Types</div><div class="stat-value" id="totalTaskCategories">—</div><div class="stat-trend">Classification types</div></div>
        <div class="stat-card"><span class="stat-icon">🎓</span><div class="stat-label">Members</div><div class="stat-value" id="activeMembers">—</div><div class="stat-trend">Student accounts</div></div>
        <div class="stat-card"><span class="stat-icon">👨‍🏫</span><div class="stat-label">Advisers</div><div class="stat-value" id="totalAdvisers">—</div><div class="stat-trend">Active subadmins</div></div>
      </div>

      <!-- Middle Row: Category + Active Users -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
        <div class="chart-container" style="border-top:2px solid #cbd5e1;padding-top:20px;">
          <div class="chart-header"><div class="chart-title">📊 Files by Category</div></div>
          <div id="categoryStats" style="max-height:280px;overflow-y:auto;"></div>
        </div>
        <div class="chart-container" style="border-top:2px solid #cbd5e1;padding-top:20px;">
          <div class="chart-header"><div class="chart-title">🏆 Most Active Users</div></div>
          <div id="userStats" class="user-activity-list" style="max-height:280px;overflow-y:auto;"></div>
        </div>
      </div>

      </div>
    </div>
  </div>

  <script>
    function loadDashboard() {
      $.post('ajax.php', {CALL: 100}, function(r) {
        if(r.success) {
          var d = r.data;
          $('#totalFiles').text(d.totalFiles);
          $('#totalCategories').text(d.totalCategories);
          $('#pendingTasks').text(d.pendingTasks);
          $('#totalSubmissions').text(d.totalSubmissions);
          $('#totalTaskCategories').text(d.totalTaskCategories);
          $('#activeMembers').text(d.activeMembers);
          $('#totalAdvisers').text(d.totalAdvisers);
        }
      }, 'json');

      $.post('ajax.php', {CALL: 101}, function(r) {
        if(r.success) displayCategoryStats(r.data);
      }, 'json');

      $.post('ajax.php', {CALL: 102}, function(r) {
        if(r.success) displayUserStats(r.data);
      }, 'json');
    }

    function displayCategoryStats(stats) {
      var el = $('#categoryStats');
      if(!stats.length) { el.html('<div style="padding:40px;text-align:center;color:#94a3b8;">No data</div>'); return; }
      var max = Math.max.apply(null, stats.map(function(s){return s.count;}));
      var totalSize = 0, h = '';
      stats.forEach(function(s) {
        var pct = max > 0 ? (s.count / max * 100) : 0;
        var size = formatFileSize(s.total_size || 0);
        totalSize += parseInt(s.total_size || 0);
        h += '<div class="category-bar"><div class="category-label"><span><strong>'+(s.category||'Uncategorized')+'</strong></span><span>'+s.count+' files ('+size+')</span></div><div class="category-progress"><div class="category-fill" style="width:'+pct+'%"></div></div></div>';
      });
      $('#totalStorage').text(formatFileSize(totalSize));
      el.html(h);
    }

    function displayUserStats(stats) {
      var el = $('#userStats');
      if(!stats.length) { el.html('<div style="padding:40px;text-align:center;color:#94a3b8;">No user activity</div>'); return; }
      var h = '';
      stats.forEach(function(s) {
        var last = s.last_upload ? new Date(s.last_upload).toLocaleDateString() : 'Never';
        h += '<div class="user-activity-item"><div><div style="font-weight:600;font-size:13px;color:#1e293b;">'+s.user_name+'</div><div style="font-size:12px;color:#94a3b8;margin-top:2px;"><span class="user-badge '+s.user_type+'">'+s.user_type+'</span> Last upload: '+last+'</div></div><div style="text-align:right;"><div style="font-size:18px;font-weight:700;color:#7b1228;">'+s.uploads+'</div><div style="font-size:11px;color:#94a3b8;">uploads</div></div></div>';
      });
      el.html(h);
    }

    function formatFileSize(bytes) {
      if(!bytes) return '0 B';
      var k=1024, sizes=['B','KB','MB','GB'], i=Math.floor(Math.log(bytes)/Math.log(k));
      return parseFloat((bytes/Math.pow(k,i)).toFixed(2))+' '+sizes[i];
    }

    $(document).ready(function() {
      loadDashboard();
      setInterval(loadDashboard, 60000);
    });
  </script>
</body>
</html>
