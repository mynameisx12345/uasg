<?php
// Subadmin header with GitHub-inspired styling
?>
<header class="github-header">
  <div class="header-left">
    <button class="mobile-sidebar-toggle" onclick="toggleMobileSidebar()">☰</button>
    <div class="header-title">
      <h1>UASG Subadmin Portal</h1>
      <div class="header-subtitle" id="currentSection">Dashboard</div>
    </div>
  </div>
  
  <div class="header-actions">
    <!-- Notifications -->
    <!--button class="header-btn" onclick="toggleNotifications()" title="Notifications">
      <span>🔔</span>
      <span class="notification-count" id="headerNotificationCount" style="display: none;">0</span>
    </button-->
    
    <!-- Notifications Dropdown Panel -->
    <div id="notificationsPanel" class="notifications-panel" style="display: none;">
      <div class="notifications-header">
        <h3>Notifications</h3>
        <button onclick="closeNotifications()" style="background: none; border: none; cursor: pointer; font-size: 18px;">&times;</button>
      </div>
      <div class="notifications-tabs">
        <button class="notif-tab active" data-notif-tab="submissions" onclick="switchNotifTab('submissions')">Pending Submissions</button>
        <button class="notif-tab" data-notif-tab="tasks" onclick="switchNotifTab('tasks')">My Tasks</button>
      </div>
      <div class="notifications-body">
        <!-- Pending Submissions Tab -->
        <div id="notifSubmissionsTab" class="notif-tab-content active">
          <div id="pendingSubmissionsList" class="notifications-list">
            <div class="notification-item loading">Loading...</div>
          </div>
        </div>
        <!-- My Tasks Tab -->
        <div id="notifTasksTab" class="notif-tab-content">
          <div id="myTasksList" class="notifications-list">
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
        <?= strtoupper(substr($currentUser['full_name'] ?? 'A', 0, 1)) ?>
      </div>
      <span class="user-name"><?= htmlspecialchars($currentUser['full_name'] ?? 'Adviser') ?></span>
    </div>
  </div>
</header>

<script>
  function toggleMobileSidebar() {
    const sidebar = document.querySelector('.github-sidebar');
    if (sidebar) {
      sidebar.classList.toggle('mobile-open');
    }
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
    const activeTab = document.querySelector(`[data-notif-tab="${tabName}"]`);
    if (activeTab) {
      activeTab.classList.add('active');
    }
    
    // Update tab content
    document.querySelectorAll('.notif-tab-content').forEach(content => {
      content.classList.remove('active');
    });
    const activeContent = document.getElementById(`notif${tabName.charAt(0).toUpperCase() + tabName.slice(1)}Tab`);
    if (activeContent) {
      activeContent.classList.add('active');
    }
  }

  function loadNotifications() {
    // Load pending submissions (reports that need review)
    fetch('ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: 'CALL=get_pending_submissions'
    })
    .then(response => response.json())
    .then(data => {
      const submissionsList = document.getElementById('pendingSubmissionsList');
      const submissions = data.data || [];
      const pendingSubmissions = submissions.filter(s => s.check_status === 'Pending');
      
      if (pendingSubmissions.length === 0) {
        submissionsList.innerHTML = '<div class="notification-item empty">No pending submissions</div>';
      } else {
        submissionsList.innerHTML = pendingSubmissions.map(sub => `
          <div class="notification-item" onclick="goToReports()">
            <div class="notif-icon">📄</div>
            <div class="notif-content">
              <div class="notif-title">${sub.task_title || 'Task Submission'}</div>
              <div class="notif-message">From: ${sub.student_name || 'Student'}</div>
              <div class="notif-meta">${new Date(sub.datetime_uploaded).toLocaleDateString()}</div>
            </div>
          </div>
        `).join('');
      }
      
      // Update notification count
      const count = pendingSubmissions.length;
      const countBadge = document.getElementById('headerNotificationCount');
      if (count > 0) {
        countBadge.textContent = count;
        countBadge.style.display = 'inline-block';
      } else {
        countBadge.style.display = 'none';
      }
    })
    .catch(error => {
      document.getElementById('pendingSubmissionsList').innerHTML = '<div class="notification-item error">Failed to load submissions</div>';
    });

    // Load my tasks
    fetch('ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: 'CALL=get_my_tasks'
    })
    .then(response => response.json())
    .then(data => {
      const tasksList = document.getElementById('myTasksList');
      const tasks = data.data || [];
      
      if (tasks.length === 0) {
        tasksList.innerHTML = '<div class="notification-item empty">No tasks created</div>';
      } else {
        tasksList.innerHTML = tasks.map(task => `
          <div class="notification-item" onclick="goToTaskManagement()">
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
      document.getElementById('myTasksList').innerHTML = '<div class="notification-item error">Failed to load tasks</div>';
    });
  }

  function goToReports() {
    closeNotifications();
    const reportsTab = document.querySelector("[data-tab='reports']");
    if (reportsTab) {
      reportsTab.click();
    }
  }

  function goToTaskManagement() {
    closeNotifications();
    const taskTab = document.querySelector("[data-tab='task-management']");
    if (taskTab) {
      taskTab.click();
    }
  }

  function viewAllNotifications() {
    closeNotifications();
    goToReports();
  }

  // Close notifications panel when clicking outside
  document.addEventListener('click', function(event) {
    const panel = document.getElementById('notificationsPanel');
    const button = event.target.closest('.header-btn');
    
    if (panel && panel.style.display === 'block' && !panel.contains(event.target) && !button) {
      panel.style.display = 'none';
    }
  });

  // Update section title based on active tab
  function updateSectionTitle(sectionName) {
    const sectionEl = document.getElementById('currentSection');
    if (sectionEl) {
      sectionEl.textContent = sectionName;
    }
  }

  // Listen for tab changes to update section title
  document.addEventListener('DOMContentLoaded', function() {
    const observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
          const target = mutation.target;
          if (target.classList.contains('tab-link') && target.classList.contains('active')) {
            const tabName = target.dataset.tab;
            const sectionNames = {
              'dashboard': 'Dashboard',
              'task-management': 'Task Management',
              'reports': 'Reports & Submissions',
              'account-management': 'Account Settings'
            };
            updateSectionTitle(sectionNames[tabName] || 'Dashboard');
          }
        }
      });
    });

    // Observe all tab buttons for class changes
    document.querySelectorAll('.tab-link').forEach(btn => {
      observer.observe(btn, { attributes: true });
    });
  });
</script>
