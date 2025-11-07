<?php
// Member sidebar with navigation menu
?>
<aside class="sidebar">
  <div class="sidebar-menu">
    <ul class="menu-list">
      <li class="menu-header">MAIN NAVIGATION</li>
      
      <li class="menu-item">
        <a href="dashboard.php" class="menu-link">
          <i class="menu-icon">📊</i>
          <span class="menu-text">Dashboard</span>
        </a>
      </li>

      <li class="menu-item has-submenu">
        <a href="#" class="menu-link" onclick="toggleSubmenu(this)">
          <i class="menu-icon">📁</i>
          <span class="menu-text">File Management</span>
          <i class="menu-arrow">›</i>
        </a>
        <ul class="submenu">
          <li><a href="#" onclick="switchTab('file-upload')">Upload Files</a></li>
          <li><a href="#" onclick="switchTab('my-files')">My Files</a></li>
          <li><a href="#" onclick="openModal('categoryManagementModal')">File Categories</a></li>
        </ul>
      </li>

      <li class="menu-item has-submenu">
        <a href="#" class="menu-link" onclick="toggleSubmenu(this)">
          <i class="menu-icon">📋</i>
          <span class="menu-text">Task Management</span>
          <i class="menu-arrow">›</i>
        </a>
        <ul class="submenu">
          <li><a href="#" onclick="switchTab('task-submissions')">My Tasks</a></li>
          <li><a href="#" onclick="openModal('submitTaskModal')">Submit Task</a></li>
          <li><a href="#" onclick="openModal('taskProgressModal')">Track Progress</a></li>
        </ul>
      </li>

      <li class="menu-item">
        <a href="#" class="menu-link" onclick="openModal('notificationsModal')">
          <i class="menu-icon">🔔</i>
          <span class="menu-text">Notifications</span>
          <span class="notification-badge" id="sidebarNotificationBadge" style="display: none;">0</span>
        </a>
      </li>

      <li class="menu-header">ACCOUNT</li>

      <li class="menu-item">
        <a href="#" class="menu-link" onclick="switchTab('account-management')">
          <i class="menu-icon">⚙️</i>
          <span class="menu-text">Settings</span>
        </a>
      </li>

      <li class="menu-item">
        <a href="#" class="menu-link" onclick="openModal('helpModal')">
          <i class="menu-icon">❓</i>
          <span class="menu-text">Help & Support</span>
        </a>
      </li>

      <li class="menu-item logout-item">
        <a href="#" class="menu-link" onclick="logout()">
          <i class="menu-icon">🚪</i>
          <span class="menu-text">Logout</span>
        </a>
      </li>
    </ul>
  </div>
</aside>

<script>
  function toggleSubmenu(element) {
    const submenu = element.nextElementSibling;
    const parent = element.parentElement;
    const arrow = element.querySelector('.menu-arrow');
    
    if (submenu.style.display === 'block') {
      submenu.style.display = 'none';
      parent.classList.remove('active');
      arrow.style.transform = 'rotate(0deg)';
    } else {
      // Close other submenus
      document.querySelectorAll('.submenu').forEach(sub => {
        sub.style.display = 'none';
        sub.parentElement.classList.remove('active');
        sub.parentElement.querySelector('.menu-arrow').style.transform = 'rotate(0deg)';
      });
      
      submenu.style.display = 'block';
      parent.classList.add('active');
      arrow.style.transform = 'rotate(90deg)';
    }
  }

  function switchTab(tabName) {
    // Remove active class from all tabs and content
    document.querySelectorAll('.tab-link').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // Add active class to clicked tab and corresponding content
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    document.getElementById(tabName).classList.add('active');
  }
</script>