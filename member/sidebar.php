<?php
// Member sidebar with navigation menu
?>
<aside class="github-sidebar">
  <div class="sidebar-header">
    <h3 class="sidebar-title">Member Portal</h3>
  </div>
  
  <nav class="sidebar-nav">
    <ul class="nav-list">
      <li class="nav-section">MAIN NAVIGATION</li>
      
      <li class="nav-item">
        <a href="#" class="nav-link active" data-tab="index">
          <span class="nav-icon">📊</span>
          <span class="nav-text">Dashboard</span>
        </a>
      </li>

      <!--li class="nav-item has-submenu">
        <a href="#" class="nav-link" onclick="toggleSubmenu(this)">
          <span class="nav-icon">📁</span>
          <span class="nav-text">File Management</span>
          <span class="nav-arrow">▶</span>
        </a>
        <ul class="submenu">
          <li><a href="#" class="nav-link" onclick="switchTab('file-upload')">
            <span class="nav-icon">📤</span>
            <span class="nav-text">Upload Files</span>
          </a></li>
          <li><a href="#" class="nav-link" onclick="switchTab('my-files')">
            <span class="nav-icon">📄</span>
            <span class="nav-text">My Files</span>
          </a></li>
          <li><a href="#" class="nav-link" onclick="openModal('fileCategoryModal')">
            <span class="nav-icon">🗂️</span>
            <span class="nav-text">File Categories</span>
          </a></li>
        </ul>
      </li>

      <li class="nav-item has-submenu">
        <a href="#" class="nav-link" onclick="toggleSubmenu(this)">
          <span class="nav-icon">📋</span>
          <span class="nav-text">Task Management</span>
          <span class="nav-arrow">▶</span>
        </a>
        <ul class="submenu">
          <li><a href="#" class="nav-link" onclick="switchTab('task-submissions')">
            <span class="nav-icon">✅</span>
            <span class="nav-text">My Tasks</span>
          </a></li>
          <li><a href="#" class="nav-link" onclick="openModal('submitTaskModal')">
            <span class="nav-icon">📝</span>
            <span class="nav-text">Submit Task</span>
          </a></li>
          <li><a href="#" class="nav-link" onclick="loadTaskProgress()">
            <span class="nav-icon">📈</span>
            <span class="nav-text">Track Progress</span>
          </a></li>
        </ul>
      </li>

      <li class="nav-item">
        <a href="#" class="nav-link" onclick="openNotifications()">
          <span class="nav-icon">🔔</span>
          <span class="nav-text">Notifications</span>
          <span class="notification-badge" id="sidebarNotificationBadge" style="display: none;">0</span>
        </a>
      </li>

      <li class="nav-section">ACCOUNT</li-->

      <!--li class="nav-item">
        <a href="#" class="nav-link" onclick="switchTab('account-management')">
          <span class="nav-icon">⚙️</span>
          <span class="nav-text">Settings</span>
        </a>
      </li>

      <li class="nav-item">
        <a href="#" class="nav-link" onclick="openHelpModal()">
          <span class="nav-icon">❓</span>
          <span class="nav-text">Help & Support</span>
        </a>
      </li>
    </ul-->
    
    <div class="sidebar-footer">
      <a href="logout.php" class="logout-link" onclick="return confirm('Are you sure you want to logout?')">
        <span class="nav-icon">🚪</span>
        <span class="nav-text">Logout</span>
      </a>
    </div>
  </nav>
</aside>

<script>
  function toggleSubmenu(element) {
    const submenu = element.nextElementSibling;
    const parent = element.parentElement;
    const arrow = element.querySelector('.nav-arrow');
    
    if (submenu.style.display === 'block') {
      submenu.style.display = 'none';
      parent.classList.remove('active');
      arrow.style.transform = 'rotate(0deg)';
    } else {
      // Close other submenus
      document.querySelectorAll('.submenu').forEach(sub => {
        sub.style.display = 'none';
        sub.parentElement.classList.remove('active');
        const arrow = sub.parentElement.querySelector('.nav-arrow');
        if (arrow) arrow.style.transform = 'rotate(0deg)';
      });
      
      submenu.style.display = 'block';
      parent.classList.add('active');
      arrow.style.transform = 'rotate(90deg)';
    }
  }

  function switchTab(tabName) {
    // Remove active class from all tabs and content
    document.querySelectorAll('.nav-link').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // Add active class to clicked tab and corresponding content
    const tabLink = document.querySelector(`[data-tab="${tabName}"]`);
    const tabContent = document.getElementById(tabName);
    
    if (tabLink) tabLink.classList.add('active');
    if (tabContent) tabContent.classList.add('active');
  }

  function openNotifications() {
    // Implementation for opening notifications
    console.log('Opening notifications...');
  }

  function openHelpModal() {
    // Implementation for opening help modal
    console.log('Opening help modal...');
  }

  function loadTaskProgress() {
    // Implementation for loading task progress
    console.log('Loading task progress...');
  }
</script>