<?php
session_start();
require_once("../resources/session.php");
$session = SessionManager::getInstance();
$session->requireRole(['student']);
$currentUser = $session->getUserData();
$pageTitle = 'Dashboard';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - UASG</title>
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
    .activity-item { display:flex; align-items:center; padding:14px 16px; border-bottom:1px solid #f1f5f9; transition:all 0.15s; border-radius:8px; margin-bottom:2px; }
    .activity-item:hover { background:rgba(123,18,40,0.02); }
    .activity-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; margin-right:14px; font-size:16px; }
    .activity-icon.upload { background:rgba(123,18,40,0.06); color:#7b1228; }
    .activity-details { flex:1; }
    .activity-user { font-weight:600; color:#1e293b; font-size:13px; }
    .activity-action { color:#64748b; font-size:13px; }
    .activity-time { color:#94a3b8; font-size:11px; margin-top:3px; }
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
          <div class="stat-card"><span class="stat-icon">📁</span><div class="stat-label">My Files</div><div class="stat-value" id="totalFiles">—</div><div class="stat-trend">Total uploaded</div></div>
          <div class="stat-card"><span class="stat-icon">⏳</span><div class="stat-label">Pending Tasks</div><div class="stat-value" id="activeTasks">—</div><div class="stat-trend">Awaiting submission</div></div>
          <div class="stat-card"><span class="stat-icon">✅</span><div class="stat-label">Completed</div><div class="stat-value" id="completedTasks">—</div><div class="stat-trend">Submitted tasks</div></div>
          <div class="stat-card"><span class="stat-icon">🗂️</span><div class="stat-label">Categories</div><div class="stat-value" id="categoryCount">—</div><div class="stat-trend">File categories used</div></div>
        </div>

        <!-- Recent Activity -->
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid #e2e8f0;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <div style="font-size:14px;font-weight:600;color:#94a3b8;">Recent Activity</div>
            <button onclick="loadRecentActivity()" style="padding:4px 10px;border:1px solid #e2e8f0;background:none;color:#94a3b8;border-radius:4px;cursor:pointer;font-size:10px;">↻ Refresh</button>
          </div>
          <div style="position:relative;">
            <div id="raShadowTop" style="position:absolute;top:0;left:0;right:0;height:18px;background:linear-gradient(to bottom,rgba(255,255,255,0.95),transparent);pointer-events:none;opacity:0;transition:opacity 0.2s;z-index:1;"></div>
            <div id="recentActivity" style="max-height:350px;overflow-y:auto;" onscroll="raScrollShadow()"></div>
            <div id="raShadowBottom" style="position:absolute;bottom:0;left:0;right:0;height:18px;background:linear-gradient(to top,rgba(255,255,255,0.95),transparent);pointer-events:none;opacity:0;transition:opacity 0.2s;z-index:1;"></div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script>
  function raScrollShadow(){
    var el=document.getElementById('recentActivity');
    document.getElementById('raShadowTop').style.opacity=el.scrollTop>5?'1':'0';
    document.getElementById('raShadowBottom').style.opacity=(el.scrollHeight-el.scrollTop-el.clientHeight)>5?'1':'0';
  }

  function animateValue(id, start, end, duration) {
    var el = document.getElementById(id);
    var range = end - start;
    var increment = range / (duration / 16);
    var current = start;
    var timer = setInterval(function() {
      current += increment;
      if (current >= end) { current = end; clearInterval(timer); }
      el.textContent = Math.floor(current);
    }, 16);
  }

  function getTimeAgo(date) {
    var seconds = Math.floor((new Date() - date) / 1000);
    var interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + " days ago";
    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + " hours ago";
    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + " minutes ago";
    return "Just now";
  }

  function loadRecentActivity() {
    $.ajax({
      url: 'ajax.php',
      type: 'POST',
      data: { CALL: 2 },
      dataType: 'json',
      success: function(response) {
        var container = document.getElementById('recentActivity');
        if (response.status !== 'SUCCESS' || !response.data.length) {
          container.innerHTML = '<div style="padding:40px;text-align:center;color:#94a3b8;">No recent activity found</div>';
          return;
        }
        var html = '';
        response.data.forEach(function(a) {
          var timeAgo = a.date ? getTimeAgo(new Date(a.date.replace(' ', 'T') + '+08:00')) : '';
          html += '<div class="activity-item">' +
            '<div class="activity-icon upload"><i class="fas fa-file-upload"></i></div>' +
            '<div class="activity-details">' +
              '<div><span class="activity-action">' + a.activity_type + '</span> <strong>' + a.item_name + '</strong></div>' +
              '<div class="activity-time"><i class="far fa-clock"></i> ' + timeAgo +
                (a.category ? ' • <span style="color:#1e293b;">' + a.category + '</span>' : '') +
              '</div>' +
            '</div>' +
          '</div>';
        });
        container.innerHTML = html;
        setTimeout(raScrollShadow, 100);
      }
    });
  }

  $(document).ready(function() {
    // Load stats
    $.ajax({
      url: 'ajax.php',
      type: 'POST',
      data: { CALL: 1 },
      dataType: 'json',
      success: function(response) {
        if (response.success === true) {
          animateValue('totalFiles', 0, response.data.totalFiles || 0, 1000);
          animateValue('activeTasks', 0, response.data.activeTasks || 0, 1000);
          animateValue('completedTasks', 0, response.data.completedTasks || 0, 1000);
          animateValue('categoryCount', 0, response.data.categoryCount || 0, 1000);
        }
      }
    });

    // Load activity
    loadRecentActivity();
  });
  </script>
</body>
</html>
