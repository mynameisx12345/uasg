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
    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .stat-card {
      background: white;
      padding: 25px;
      border-radius: 8px;
      border: 1px solid #e0e0e0;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      position: relative;
    }
    
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      border-color: #2196f3;
    }
    
    .stat-icon {
      font-size: 32px;
      opacity: 0.6;
      margin-bottom: 10px;
      display: block;
    }
    
    .stat-value {
      font-size: 36px;
      font-weight: 700;
      color: #2196f3;
      margin: 10px 0;
      line-height: 1;
    }
    
    .stat-label {
      font-size: 13px;
      color: #666;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 600;
      margin-bottom: 5px;
    }
    
    .stat-trend {
      font-size: 12px;
      color: #999;
      margin-top: 8px;
    }
    
    .chart-container {
      background: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      margin-bottom: 30px;
    }
    
    .chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f0f0f0;
    }
    
    .chart-title {
      font-size: 20px;
      font-weight: 600;
      color: #333;
    }
    
    .activity-item {
      display: flex;
      align-items: center;
      padding: 15px;
      border-bottom: 1px solid #f0f0f0;
      transition: background 0.2s ease;
    }
    
    .activity-item:hover {
      background: #f8f9fa;
    }
    
    .activity-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      font-size: 18px;
    }
    
    .activity-icon.upload {
      background: #e3f2fd;
      color: #2196f3;
    }
    
    .activity-details {
      flex: 1;
    }
    
    .activity-user {
      font-weight: 600;
      color: #333;
    }
    
    .activity-action {
      color: #666;
      font-size: 14px;
    }
    
    .activity-time {
      color: #999;
      font-size: 12px;
    }
    
    .category-bar {
      margin-bottom: 15px;
    }
    
    .category-label {
      display: flex;
      justify-content: space-between;
      margin-bottom: 5px;
      font-size: 14px;
    }
    
    .category-progress {
      height: 8px;
      background: #f0f0f0;
      border-radius: 4px;
      overflow: hidden;
    }
    
    .category-fill {
      height: 100%;
      background: #2196f3;
      transition: width 0.5s ease;
    }
    
    .loading-skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: loading 1.5s infinite;
      border-radius: 4px;
    }
    
    @keyframes loading {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }
    
    .user-activity-list {
      max-height: 400px;
      overflow-y: auto;
    }
    
    .user-activity-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px;
      border-bottom: 1px solid #f0f0f0;
    }
    
    .user-badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
    }
    
    .user-badge.student {
      background: #e3f2fd;
      color: #2196f3;
    }
    
    .user-badge.subadmin {
      background: #f3e5f5;
      color: #9c27b0;
    }
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
      <div class="page-header" style="margin-bottom: 30px;">
        <h1 style="font-size: 32px; font-weight: 700; color: #333;">
          <i class="fas fa-chart-line"></i> Dashboard Overview
        </h1>
        <p style="color: #666; margin-top: 5px;">Real-time system statistics and activity monitoring</p>
      </div>

      <!-- Overview Cards -->
      <div class="dashboard-grid">
        <div class="stat-card">
          <span class="stat-icon">📁</span>
          <div class="stat-label">Total Files</div>
          <div class="stat-value" id="totalFiles">
            <div class="loading-skeleton" style="width: 60px; height: 36px;"></div>
          </div>
          <div class="stat-trend">Uploaded to system</div>
        </div>
        
        <div class="stat-card">
          <span class="stat-icon">🗂️</span>
          <div class="stat-label">Categories</div>
          <div class="stat-value" id="totalCategories">
            <div class="loading-skeleton" style="width: 60px; height: 36px;"></div>
          </div>
          <div class="stat-trend">Active categories</div>
        </div>
        
        <div class="stat-card">
          <span class="stat-icon">👥</span>
          <div class="stat-label">Total Users</div>
          <div class="stat-value" id="totalUsers">
            <div class="loading-skeleton" style="width: 60px; height: 36px;"></div>
          </div>
          <div class="stat-trend">Registered members</div>
        </div>
        
        <div class="stat-card">
          <span class="stat-icon">⏳</span>
          <div class="stat-label">Pending Tasks</div>
          <div class="stat-value" id="pendingTasks">
            <div class="loading-skeleton" style="width: 60px; height: 36px;"></div>
          </div>
          <div class="stat-trend">Awaiting submission</div>
        </div>
        
        <div class="stat-card">
          <span class="stat-icon">🎓</span>
          <div class="stat-label">Active Members</div>
          <div class="stat-value" id="activeMembers">
            <div class="loading-skeleton" style="width: 60px; height: 36px;"></div>
          </div>
          <div class="stat-trend">Student accounts</div>
        </div>
        
        <div class="stat-card">
          <span class="stat-icon">👨‍🏫</span>
          <div class="stat-label">Advisers</div>
          <div class="stat-value" id="totalAdvisers">
            <div class="loading-skeleton" style="width: 60px; height: 36px;"></div>
          </div>
          <div class="stat-trend">Active subadmins</div>
        </div>
        
        <div class="stat-card">
          <span class="stat-icon">✅</span>
          <div class="stat-label">Submissions</div>
          <div class="stat-value" id="totalSubmissions">
            <div class="loading-skeleton" style="width: 60px; height: 36px;"></div>
          </div>
          <div class="stat-trend">Task submissions</div>
        </div>
        
        <div class="stat-card">
          <span class="stat-icon">�</span>
          <div class="stat-label">Total Storage</div>
          <div class="stat-value" id="totalStorage">
            <div class="loading-skeleton" style="width: 80px; height: 36px;"></div>
          </div>
          <div class="stat-trend">Files size</div>
        </div>
      </div>

      <!-- Charts Row -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        <!-- Files by Category -->
        <div class="chart-container">
          <div class="chart-header">
            <div class="chart-title">
              <i class="fas fa-chart-bar"></i> Files by Category
            </div>
          </div>
          <div id="categoryStats"></div>
        </div>
        
        <!-- Top Users -->
        <div class="chart-container">
          <div class="chart-header">
            <div class="chart-title">
              <i class="fas fa-users"></i> Most Active Users
            </div>
          </div>
          <div id="userStats" class="user-activity-list"></div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="chart-container">
        <div class="chart-header">
          <div class="chart-title">
            <i class="fas fa-history"></i> Recent Activity
          </div>
          <button onclick="refreshDashboard()" style="padding: 8px 16px; border: none; background: #2196f3; color: white; border-radius: 6px; cursor: pointer; font-size: 13px;">
            <i class="fas fa-sync-alt"></i> Refresh
          </button>
        </div>
        <div id="recentActivity"></div>
      </div>
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
        container.innerHTML = '<div style="padding: 40px; text-align: center; color: #999;">No recent activity found</div>';
        return;
      }
      
      let html = '';
      activities.forEach(activity => {
        const date = new Date(activity.date);
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
                ${activity.category ? ` • <span style="color: #2196f3;">${activity.category}</span>` : ''}
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
        container.innerHTML = '<div style="padding: 40px; text-align: center; color: #999;">No data available</div>';
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
        container.innerHTML = '<div style="padding: 40px; text-align: center; color: #999;">No user activity found</div>';
        return;
      }
      
      let html = '';
      stats.forEach(stat => {
        const lastUpload = stat.last_upload ? new Date(stat.last_upload).toLocaleDateString() : 'Never';
        
        html += `
          <div class="user-activity-item">
            <div>
              <div style="font-weight: 600; color: #333;">${stat.user_name}</div>
              <div style="font-size: 12px; color: #999; margin-top: 2px;">
                <span class="user-badge ${stat.user_type}">${stat.user_type}</span>
                Last upload: ${lastUpload}
              </div>
            </div>
            <div style="text-align: right;">
              <div style="font-size: 24px; font-weight: bold; color: #2196f3;">${stat.uploads}</div>
              <div style="font-size: 11px; color: #999;">uploads</div>
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