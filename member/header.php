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
    <button class="header-btn primary" onclick="openQuickUpload()" title="Quick File Upload">
      <span>📤</span> Upload
    </button>
    
    <!-- Notifications -->
    <button class="header-btn" onclick="openNotifications()" title="Notifications">
      <span>🔔</span>
      <span class="notification-count" id="headerNotificationCount" style="display: none;">0</span>
    </button>
    
    <!-- User Profile -->
    <div class="user-profile">
      <div class="user-avatar-small">
        <?= strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1)) ?>
      </div>
      <span class="user-name"><?= htmlspecialchars($currentUser['full_name'] ?? 'Member') ?></span>
    </div>
  </div>
</header>

<script>
  function toggleMobileSidebar() {
    const sidebar = document.querySelector('.github-sidebar');
    sidebar.classList.toggle('mobile-open');
  }

  function openQuickUpload() {
    // Open file upload modal directly
    openModal('fileUploadModal');
  }

  function openNotifications() {
    // Implementation for opening notifications
    console.log('Opening notifications...');
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