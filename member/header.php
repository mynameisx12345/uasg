<?php
// Member header with professional styling
?>
<header class="main-header">
  <div class="header-content">
    <div class="header-left">
      <div class="logo-section">
        <h1 class="app-title">UASG</h1>
        <span class="app-subtitle">Member Portal</span>
      </div>
    </div>
    
    <div class="header-center">
      <div class="breadcrumb">
        <span class="breadcrumb-item">Member</span>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-item active" id="currentSection">Dashboard</span>
      </div>
    </div>
    
    <div class="header-right">
      <div class="header-actions">
        <!-- Quick Upload Button -->
        <button class="quick-action-btn" onclick="openQuickUpload()" title="Quick File Upload">
          <i class="action-icon">📤</i>
        </button>
        
        <!-- Notifications -->
        <div class="notification-wrapper">
          <button class="notification-btn" onclick="openModal('notificationsModal')" title="Notifications">
            <i class="notification-icon">🔔</i>
            <span class="notification-count" id="headerNotificationCount" style="display: none;">0</span>
          </button>
        </div>
        
        <!-- User Profile -->
        <div class="user-profile">
          <div class="user-avatar">
            <?= strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1)) ?>
          </div>
          <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($currentUser['full_name'] ?? 'Member') ?></span>
            <span class="user-role">Member</span>
          </div>
          <button class="profile-dropdown-btn" onclick="toggleProfileDropdown()">
            <i class="dropdown-icon">▼</i>
          </button>
          
          <div class="profile-dropdown" id="profileDropdown" style="display: none;">
            <a href="#" onclick="switchTab('account-management')" class="dropdown-item">
              <i class="item-icon">⚙️</i>
              Account Settings
            </a>
            <a href="#" onclick="openModal('helpModal')" class="dropdown-item">
              <i class="item-icon">❓</i>
              Help & Support
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" onclick="logout()" class="dropdown-item logout">
              <i class="item-icon">🚪</i>
              Logout
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>

<script>
  function toggleProfileDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    if (dropdown.style.display === 'none') {
      dropdown.style.display = 'block';
    } else {
      dropdown.style.display = 'none';
    }
  }

  function openQuickUpload() {
    switchTab('file-upload');
    // Focus on file input
    setTimeout(() => {
      document.getElementById('uploadFile').focus();
    }, 100);
  }

  // Close dropdown when clicking outside
  document.addEventListener('click', function(event) {
    const profileDropdown = document.getElementById('profileDropdown');
    const profileButton = event.target.closest('.profile-dropdown-btn');
    
    if (!profileButton && profileDropdown.style.display === 'block') {
      profileDropdown.style.display = 'none';
    }
  });

  // Update breadcrumb based on active tab
  function updateBreadcrumb(sectionName) {
    document.getElementById('currentSection').textContent = sectionName;
  }

  // Listen for tab changes to update breadcrumb
  document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('.tab-link');
    tabLinks.forEach(link => {
      link.addEventListener('click', function() {
        const tabName = this.dataset.tab;
        const breadcrumbNames = {
          'dashboard': 'Dashboard',
          'file-upload': 'File Upload',
          'my-files': 'My Files',
          'task-submissions': 'Task Submissions',
          'account-management': 'Account Settings'
        };
        updateBreadcrumb(breadcrumbNames[tabName] || 'Dashboard');
      });
    });
  });
</script>