<?php session_start(); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - UASG</title>
  
  <link rel="stylesheet" href="../resources/style.css">
  <link rel="stylesheet" href="../resources/theme-overrides.css">
  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
  <script src='../js/datatable.js'></script>
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
    .chart-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; padding-bottom:0; border-bottom:none; }
    .chart-title { font-size:15px; font-weight:600; color:#1e293b; }
    .activity-item { display:flex; align-items:center; padding:14px 16px; border-bottom:1px solid #f1f5f9; transition:all 0.15s; border-radius:8px; margin-bottom:2px; }
    .activity-item:hover { background:rgba(123,18,40,0.02); }
    .activity-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; margin-right:14px; font-size:16px; }
    .activity-icon.upload { background:rgba(123,18,40,0.06); color:#7b1228; }
    .activity-details { flex:1; }
    .activity-user { font-weight:600; color:#1e293b; font-size:13px; }
    .activity-action { color:#64748b; font-size:13px; }
    .activity-time { color:#94a3b8; font-size:11px; margin-top:3px; }
    .category-bar { margin-bottom:0; padding:12px 0; background:transparent; border-radius:0; border-left:none; border-top:1px solid #f1f5f9; }
    .category-bar:first-child { border-top:none; }
    .category-label { display:flex; justify-content:space-between; margin-bottom:6px; font-size:12px; color:#475569; }
    .category-progress { height:6px; background:#f1f5f9; border-radius:3px; overflow:hidden; }
    .category-fill { height:100%; background:linear-gradient(90deg,#7b1228,#c89b2e); border-radius:3px; transition:width 0.6s ease; }
    .loading-skeleton { background:linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%); background-size:200% 100%; animation:loading 1.5s infinite; border-radius:4px; }
    @keyframes loading { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
    .user-activity-list { max-height:400px; overflow-y:auto; }
    .user-activity-item { display:flex; justify-content:space-between; align-items:center; padding:12px 14px; margin-bottom:4px; background:#fafbfc; border-radius:8px; transition:all 0.15s; }
    .user-activity-item:hover { background:#f1f5f9; }
    .user-badge { display:inline-block; padding:3px 8px; border-radius:10px; font-size:10px; font-weight:600; text-transform:uppercase; }
    .user-badge.student { background:rgba(123,18,40,0.08); color:#7b1228; }
    .user-badge.subadmin { background:rgba(200,155,46,0.12); color:#b38712; }
  </style>
</head>
<body>
  <!-- HEADER -->
  <?php require_once("header.php");?>
  
<!-- MAIN -->
<main class="main">
  
    <!-- SIDEBAR -->
    <?php require_once("sidebar.php");?>

    <!-- CONTENT -->
    <section class="content">
    </section>
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
          <div style="position:relative;">
            <div id="catShadowTop" style="position:absolute;top:0;left:0;right:0;height:18px;background:linear-gradient(to bottom,rgba(255,255,255,0.95),transparent);pointer-events:none;opacity:0;transition:opacity 0.2s;z-index:1;"></div>
            <div id="categoryStats" style="max-height:280px;overflow-y:auto;" onscroll="catScrollShadow()"></div>
            <div id="catShadowBottom" style="position:absolute;bottom:0;left:0;right:0;height:18px;background:linear-gradient(to top,rgba(255,255,255,0.95),transparent);pointer-events:none;opacity:0;transition:opacity 0.2s;z-index:1;"></div>
          </div>
        </div>
        <div class="chart-container" style="border-top:2px solid #cbd5e1;padding-top:20px;">
          <div class="chart-header"><div class="chart-title">🏆 Most Active Users</div></div>
          <div style="position:relative;">
            <div id="userShadowTop" style="position:absolute;top:0;left:0;right:0;height:18px;background:linear-gradient(to bottom,rgba(255,255,255,0.95),transparent);pointer-events:none;opacity:0;transition:opacity 0.2s;z-index:1;"></div>
            <div id="userStats" class="user-activity-list" style="max-height:280px;overflow-y:auto;" onscroll="userScrollShadow()"></div>
            <div id="userShadowBottom" style="position:absolute;bottom:0;left:0;right:0;height:18px;background:linear-gradient(to top,rgba(255,255,255,0.95),transparent);pointer-events:none;opacity:0;transition:opacity 0.2s;z-index:1;"></div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div style="margin-top:20px;padding-top:16px;border-top:1px solid #e2e8f0;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
          <div style="font-size:14px;font-weight:600;color:#94a3b8;">Recent Activity</div>
          <button onclick="refreshDashboard()" style="padding:4px 10px;border:1px solid #e2e8f0;background:none;color:#94a3b8;border-radius:4px;cursor:pointer;font-size:10px;">↻ Refresh</button>
        </div>
        <div id="recentActivityWrap" style="position:relative;">
          <div id="raShadowTop" style="position:absolute;top:0;left:0;right:0;height:18px;background:linear-gradient(to bottom,rgba(241,245,249,0.9),transparent);pointer-events:none;opacity:0;transition:opacity 0.2s;z-index:1;border-radius:4px 4px 0 0;"></div>
          <div id="recentActivity" style="max-height:350px;overflow-y:auto;position:relative;" onscroll="raScrollShadow()"></div>
          <div id="raShadowBottom" style="position:absolute;bottom:0;left:0;right:0;height:18px;background:linear-gradient(to top,rgba(241,245,249,0.9),transparent);pointer-events:none;opacity:0;transition:opacity 0.2s;z-index:1;border-radius:0 0 4px 4px;"></div>
        </div>
      </div>
      <script>
      function raScrollShadow(){
        var el=document.getElementById('recentActivity');
        var top=document.getElementById('raShadowTop');
        var bot=document.getElementById('raShadowBottom');
        top.style.opacity=el.scrollTop>5?'1':'0';
        bot.style.opacity=(el.scrollHeight-el.scrollTop-el.clientHeight)>5?'1':'0';
      }
      function catScrollShadow(){
        var el=document.getElementById('categoryStats');
        var top=document.getElementById('catShadowTop');
        var bot=document.getElementById('catShadowBottom');
        top.style.opacity=el.scrollTop>5?'1':'0';
        bot.style.opacity=(el.scrollHeight-el.scrollTop-el.clientHeight)>5?'1':'0';
      }
      function userScrollShadow(){
        var el=document.getElementById('userStats');
        var top=document.getElementById('userShadowTop');
        var bot=document.getElementById('userShadowBottom');
        top.style.opacity=el.scrollTop>5?'1':'0';
        bot.style.opacity=(el.scrollHeight-el.scrollTop-el.clientHeight)>5?'1':'0';
      }
      setTimeout(function(){ raScrollShadow(); catScrollShadow(); userScrollShadow(); },1000);
      </script>
    </section>
  </main>

  <script>
    // Load Dashboard Data
    function loadDashboard() {
      $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { CALL: 100 },
        dataType: 'json',
        success: function(response) {
          if(response.success) {
            const data = response.data;
            
            // Update stat cards with animation
            animateValue('totalFiles', 0, data.totalFiles, 1000);
            animateValue('totalCategories', 0, data.totalCategories, 1000);
            animateValue('totalTaskCategories', 0, data.totalTaskCategories, 1000);
            animateValue('totalUsers', 0, data.totalUsers, 1000);
            animateValue('pendingTasks', 0, data.pendingTasks, 1000);
            animateValue('activeMembers', 0, data.activeMembers, 1000);
            animateValue('totalAdvisers', 0, data.totalAdvisers, 1000);
            animateValue('totalSubmissions', 0, data.totalSubmissions, 1000);
            
            // Display recent activity
            displayRecentActivity(data.recentActivity);
          }
        },
        error: function(xhr, status, error) {
          console.error('Error loading dashboard:', error);
        }
      });
      
      // Load category statistics
      $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { CALL: 101 },
        dataType: 'json',
        success: function(response) {
          if(response.success) {
            displayCategoryStats(response.data);
          }
        }
      });
      
      // Load user activity statistics
      $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { CALL: 102 },
        dataType: 'json',
        success: function(response) {
          if(response.success) {
            displayUserStats(response.data);
          }
        }
      });
    }
    
    // Animate number counting
    function animateValue(id, start, end, duration) {
      const element = document.getElementById(id);
      const range = end - start;
      const increment = range / (duration / 16);
      let current = start;
      
      const timer = setInterval(function() {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
          current = end;
          clearInterval(timer);
        }
        element.textContent = Math.floor(current);
      }, 16);
    }
    
    // Display Recent Activity
    function displayRecentActivity(activities) {
      const container = document.getElementById('recentActivity');
      
      if(activities.length === 0) {
        container.innerHTML = '<div style="padding: 40px; text-align: center; color: #94a3b8;">No recent activity found</div>';
        return;
      }
      
      let html = '';
      activities.forEach(activity => {
        const date = new Date(activity.date.replace(' ', 'T') + '+08:00');
        const timeAgo = getTimeAgo(date);
        
        html += `
          <div class="activity-item">
            <div class="activity-icon upload">
              <i class="fas fa-file-upload"></i>
            </div>
            <div class="activity-details">
              <div>
                <span class="activity-user">${activity.user_name || 'Unknown'}</span>
                <span class="activity-action"> ${activity.action} </span>
                <strong>${activity.item}</strong>
              </div>
              <div class="activity-time">
                <i class="far fa-clock"></i> ${timeAgo}
                ${activity.category ? ` • <span style="color: #1e293b;">${activity.category}</span>` : ''}
              </div>
            </div>
          </div>
        `;
      });
      
      container.innerHTML = html;
    }
    
    // Display Category Statistics
    function displayCategoryStats(stats) {
      const container = document.getElementById('categoryStats');
      
      if(stats.length === 0) {
        container.innerHTML = '<div style="padding: 40px; text-align: center; color: #94a3b8;">No data available</div>';
        return;
      }
      
      const maxCount = Math.max(...stats.map(s => s.count));
      let totalSize = 0;
      
      let html = '';
      stats.forEach(stat => {
        const percentage = maxCount > 0 ? (stat.count / maxCount * 100) : 0;
        const size = formatFileSize(stat.total_size || 0);
        totalSize += parseInt(stat.total_size || 0);
        
        html += `
          <div class="category-bar">
            <div class="category-label">
              <span><strong>${stat.category || 'Uncategorized'}</strong></span>
              <span>${stat.count} files (${size})</span>
            </div>
            <div class="category-progress">
              <div class="category-fill" style="width: ${percentage}%"></div>
            </div>
          </div>
        `;
      });
      
      // Update total storage
      document.getElementById('totalStorage').textContent = formatFileSize(totalSize);
      
      container.innerHTML = html;
    }
    
    // Display User Statistics
    function displayUserStats(stats) {
      const container = document.getElementById('userStats');
      
      if(stats.length === 0) {
        container.innerHTML = '<div style="padding: 40px; text-align: center; color: #94a3b8;">No user activity found</div>';
        return;
      }
      
      let html = '';
      stats.forEach(stat => {
        const lastUpload = stat.last_upload ? new Date(stat.last_upload).toLocaleDateString() : 'Never';
        
        html += `
          <div class="user-activity-item">
            <div>
              <div style="font-weight: 600; font-size: 13px; color: #1e293b;">${stat.user_name}</div>
              <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                <span class="user-badge ${stat.user_type}">${stat.user_type}</span>
                Last upload: ${lastUpload}
              </div>
            </div>
            <div style="text-align: right;">
              <div style="font-size: 18px; font-weight: 700; color: #7b1228;">${stat.uploads}</div>
              <div style="font-size: 11px; color: #94a3b8;">uploads</div>
            </div>
          </div>
        `;
      });
      
      container.innerHTML = html;
    }
    
    // Helper: Format file size
    function formatFileSize(bytes) {
      if(bytes === 0) return '0 B';
      const k = 1024;
      const sizes = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Helper: Get time ago
    function getTimeAgo(date) {
      const seconds = Math.floor((new Date() - date) / 1000);
      
      let interval = seconds / 31536000;
      if (interval > 1) return Math.floor(interval) + " years ago";
      
      interval = seconds / 2592000;
      if (interval > 1) return Math.floor(interval) + " months ago";
      
      interval = seconds / 86400;
      if (interval > 1) return Math.floor(interval) + " days ago";
      
      interval = seconds / 3600;
      if (interval > 1) return Math.floor(interval) + " hours ago";
      
      interval = seconds / 60;
      if (interval > 1) return Math.floor(interval) + " minutes ago";
      
      return "Just now";
    }
    
    // Refresh Dashboard
    function refreshDashboard() {
      loadDashboard();
    }
    
    // Load dashboard on page load
    $(document).ready(function() {
      loadDashboard();
      
      // Auto-refresh every 60 seconds
      setInterval(loadDashboard, 60000);
    });
  </script>
  
  <!-- PWA Scripts -->
  <script src="../js/pwa-helper.js"></script>
</body>
</html>