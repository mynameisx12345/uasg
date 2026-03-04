<?php
// Member header with GitHub-inspired styling
?>
<header class="github-header">
  <div class="header-left">
    <button class="mobile-sidebar-toggle" onclick="toggleMobileSidebar()">☰</button>
    <div class="header-title">
      <h1>UASG Member Portal</h1>
      <div class="header-subtitle" id="currentSection">Dashboard</div>
    </div>
  </div>
  
  <div class="header-actions">
    <!-- Quick Upload Button -->
    <!--button class="header-btn primary" onclick="openQuickUpload()" title="Quick File Upload">
      <span>📤</span> Upload
    </button-->
    
    <!-- Notifications -->
    <button class="header-btn" onclick="toggleNotifications()" title="Notifications">
      <span>🔔</span>
      <span class="notification-count" id="headerNotificationCount" style="display: none;">0</span>
    </button>
    
    <!-- Notifications Dropdown Panel -->
    <div id="notificationsPanel" class="notifications-panel" style="display: none;">
      <div class="notifications-header">
        <h3>Notifications</h3>
        <button onclick="closeNotifications()" style="background: none; border: none; cursor: pointer; font-size: 18px;">&times;</button>
      </div>
      <div class="notifications-tabs">
        <button class="notif-tab active" data-notif-tab="tasks" onclick="switchNotifTab('tasks')">Pending Tasks</button>
        <button class="notif-tab" data-notif-tab="announcements" onclick="switchNotifTab('announcements')">Announcements</button>
      </div>
      <div class="notifications-body">
        <!-- Pending Tasks Tab -->
        <div id="notifTasksTab" class="notif-tab-content active">
          <div id="pendingTasksList" class="notifications-list">
            <div class="notification-item loading">Loading...</div>
          </div>
        </div>
        <!-- Announcements Tab -->
        <div id="notifAnnouncementsTab" class="notif-tab-content">
          <div id="announcementsList" class="notifications-list">
            <div class="notification-item loading">Loading...</div>
          </div>
        </div>
      </div>
      <div class="notifications-footer">
        <button onclick="viewAllNotifications()" class="btn-view-all">View All</button>
      </div>
    </div>
    
    <!-- User Profile -->
    <div class="user-profile">
      <div class="user-avatar-small">
        <?= strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1)) ?>
      </div>
      <span class="user-name"><?= htmlspecialchars($currentUser['full_name'] ?? 'Member') ?></span>
    </div>
  </div>
</header>

<!-- Mobile sidebar overlay -->
<div id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

<!-- Mobile bottom navigation bar -->
<nav id="mobileBottomNav" aria-label="Mobile navigation">
  <button class="bnav-item active" data-bnav="dashboard" onclick="mobileNavClick('dashboard',this)">
    <span class="bnav-icon">📊</span>
    <span>Dashboard</span>
  </button>
  <button class="bnav-item" data-bnav="file-upload" onclick="mobileNavClick('file-upload',this)">
    <span class="bnav-icon">📤</span>
    <span>Upload</span>
  </button>
  <button class="bnav-item" data-bnav="pending-tasks" onclick="mobileNavClick('pending-tasks',this)">
    <span class="bnav-icon">📋</span>
    <span>Tasks</span>
    <span class="bnav-badge" id="bnavTaskBadge" style="display:none">0</span>
  </button>
  <button class="bnav-item" data-bnav="my-files" onclick="mobileNavClick('my-files',this)">
    <span class="bnav-icon">📁</span>
    <span>My Files</span>
  </button>
  <button class="bnav-item" data-bnav="account-management" onclick="mobileNavClick('account-management',this)">
    <span class="bnav-icon">⚙️</span>
    <span>Account</span>
  </button>
</nav>

<script>
  function toggleMobileSidebar() {
    const sidebar = document.querySelector('.github-sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const isOpen = sidebar.classList.contains('mobile-open');
    if (isOpen) {
      closeMobileSidebar();
    } else {
      sidebar.classList.add('mobile-open');
      overlay.classList.add('visible');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeMobileSidebar() {
    const sidebar = document.querySelector('.github-sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('visible');
    document.body.style.overflow = '';
  }

  function mobileNavClick(tabName, btn) {
    // Switch tab content
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    const target = document.getElementById(tabName);
    if (target) target.classList.add('active');
    // Update tab-nav bar buttons
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    const tabBtn = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
    if (tabBtn) tabBtn.classList.add('active');
    // Update bottom nav active state
    document.querySelectorAll('#mobileBottomNav .bnav-item').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    // Update header subtitle
    const names = { 'dashboard':'Dashboard','file-upload':'File Upload','my-files':'My Files','pending-tasks':'Pending Tasks','task-submissions':'Task Submissions','account-management':'Account Settings' };
    const sub = document.getElementById('currentSection');
    if (sub) sub.textContent = names[tabName] || tabName;
    // Close sidebar if open
    closeMobileSidebar();
  }

  // Keep bottom nav in sync when desktop tab-btns are clicked
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const tab = this.dataset.tab;
        document.querySelectorAll('#mobileBottomNav .bnav-item').forEach(b => b.classList.remove('active'));
        const bnavBtn = document.querySelector(`#mobileBottomNav .bnav-item[data-bnav="${tab}"]`);
        if (bnavBtn) bnavBtn.classList.add('active');
      });
    });
    // Sync task badge with sidebar badge
    const obs = new MutationObserver(() => {
      const src = document.getElementById('pendingTasksBadge');
      const dest = document.getElementById('bnavTaskBadge');
      if (src && dest) {
        const txt = src.textContent;
        dest.textContent = txt;
        dest.style.display = (src.style.display === 'none' || !txt) ? 'none' : '';
      }
    });
    const badge = document.getElementById('pendingTasksBadge');
    if (badge) obs.observe(badge, { childList: true, attributes: true });
  });

  function openQuickUpload() {
    // Open file upload modal directly
    openModal('fileUploadModal');
  }

  function toggleNotifications() {
    const panel = document.getElementById('notificationsPanel');
    if (panel.style.display === 'none' || panel.style.display === '') {
      panel.style.display = 'block';
      loadNotifications();
    } else {
      panel.style.display = 'none';
    }
  }

  function closeNotifications() {
    document.getElementById('notificationsPanel').style.display = 'none';
  }

  function switchNotifTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.notif-tab').forEach(tab => {
      tab.classList.remove('active');
    });
    document.querySelector(`[data-notif-tab="${tabName}"]`).classList.add('active');
    
    // Update tab content
    document.querySelectorAll('.notif-tab-content').forEach(content => {
      content.classList.remove('active');
    });
    document.getElementById(`notif${tabName.charAt(0).toUpperCase() + tabName.slice(1)}Tab`).classList.add('active');
  }

  function loadNotifications() {
    // Load pending tasks
    fetch('ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: 'CALL=9'
    })
    .then(response => response.json())
    .then(data => {
      const tasksList = document.getElementById('pendingTasksList');
      const tasks = data.data || [];
      const pendingTasks = tasks.filter(t => t.task_status === 'active');
      
      if (pendingTasks.length === 0) {
        tasksList.innerHTML = '<div class="notification-item empty">No pending tasks</div>';
      } else {
        tasksList.innerHTML = pendingTasks.map(task => `
          <div class="notification-item" onclick="goToPendingTasks()">
            <div class="notif-icon">📋</div>
            <div class="notif-content">
              <div class="notif-title">${task.task_title}</div>
              <div class="notif-meta">Due: ${new Date(task.task_deadline).toLocaleDateString()}</div>
            </div>
          </div>
        `).join('');
      }
    })
    .catch(error => {
      document.getElementById('pendingTasksList').innerHTML = '<div class="notification-item error">Failed to load tasks</div>';
    });

    // Load announcements
    fetch('ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: 'CALL=12&unread_only=false'
    })
    .then(response => response.json())
    .then(data => {
      const announcementsList = document.getElementById('announcementsList');
      const announcements = data.data || [];
      
      if (announcements.length === 0) {
        announcementsList.innerHTML = '<div class="notification-item empty">No announcements</div>';
      } else {
        announcementsList.innerHTML = announcements.map(notif => `
          <div class="notification-item ${notif.is_read ? '' : 'unread'}" onclick="markAsRead(${notif.notification_id})">
            <div class="notif-icon">${getNotifIcon(notif.type)}</div>
            <div class="notif-content">
              <div class="notif-title">${notif.title}</div>
              <div class="notif-message">${notif.message}</div>
              <div class="notif-meta">${formatNotifDate(notif.datetime_created)}</div>
            </div>
          </div>
        `).join('');
      }
    })
    .catch(error => {
      document.getElementById('announcementsList').innerHTML = '<div class="notification-item error">Failed to load announcements</div>';
    });
  }

  function getNotifIcon(type) {
    const icons = {
      'new_task': '📋',
      'task_approved': '✅',
      'task_rejected': '❌',
      'announcement': '📢',
      'reminder': '⏰',
      'default': '🔔'
    };
    return icons[type] || icons['default'];
  }

  function formatNotifDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} min${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    return date.toLocaleDateString();
  }

  function goToPendingTasks() {
    closeNotifications();
    document.querySelector("[data-tab='pending-tasks']").click();
  }

  function markAsRead(notificationId) {
    fetch('ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: `CALL=13&notification_id=${notificationId}`
    })
    .then(() => {
      loadNotifications();
    });
  }

  function viewAllNotifications() {
    closeNotifications();
    document.querySelector("[data-tab='pending-tasks']").click();
  }

  // Close notifications panel when clicking outside
  document.addEventListener('click', function(event) {
    const panel = document.getElementById('notificationsPanel');
    const button = event.target.closest('.header-btn');
    
    if (panel && panel.style.display === 'block' && !panel.contains(event.target) && !button) {
      panel.style.display = 'none';
    }
  });

  function openNotifications() {
    // Fallback function for compatibility
    toggleNotifications();
  }

  // Update section title based on active tab
  function updateSectionTitle(sectionName) {
    document.getElementById('currentSection').textContent = sectionName;
  }

  // Listen for tab changes to update section title
  document.addEventListener('DOMContentLoaded', function() {
    const observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
          const target = mutation.target;
          if (target.classList.contains('tab-btn') && target.classList.contains('active')) {
            const tabName = target.dataset.tab;
            const sectionNames = {
              'dashboard': 'Dashboard',
              'file-upload': 'File Upload',
              'my-files': 'My Files', 
              'task-submissions': 'Task Submissions',
              'account-management': 'Account Settings'
            };
            updateSectionTitle(sectionNames[tabName] || 'Dashboard');
          }
        }
      });
    });

    // Observe all tab buttons for class changes
    document.querySelectorAll('.tab-btn').forEach(btn => {
      observer.observe(btn, { attributes: true });
    });
  });
</script>