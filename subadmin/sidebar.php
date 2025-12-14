<?php
// Get user permissions
require_once(__DIR__ . '/../resources/objects/permission_class.php');

$userId = $_SESSION['user_id'] ?? 0;
$permissions = [
    'task_management' => SubadminPermission::hasPermission($userId, 'task_management', 'view'),
    'file_management' => SubadminPermission::hasPermission($userId, 'file_management', 'view'),
    'user_management' => SubadminPermission::hasPermission($userId, 'user_management', 'view'),
    'entry_module' => SubadminPermission::hasPermission($userId, 'entry_module', 'view')
];
?>
<aside class="github-sidebar">
  <div class="sidebar-header">
    <h3 class="sidebar-title">Navigation</h3>
  </div>
  
  <nav class="sidebar-nav">
    <ul class="nav-list">
      <li class="nav-item">
        <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
          <span class="nav-icon">📊</span>
          <span class="nav-text">Dashboard</span>
        </a>
      </li>
      
      <?php if ($permissions['task_management']): ?>
      <li class="nav-item">
        <a href="task-management.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'task-management.php' ? 'active' : '' ?>">
          <span class="nav-icon">📋</span>
          <span class="nav-text">Task Management</span>
        </a>
      </li>
      <?php endif; ?>

      <?php if ($permissions['file_management']): ?>
      <li class="nav-item">
        <a href="file-uploads.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'file-uploads.php' ? 'active' : '' ?>">
          <span class="nav-icon">📁</span>
          <span class="nav-text">File Uploads</span>
        </a>
      </li>
      
      <li class="nav-item">
        <a href="nlp-search.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'nlp-search.php' ? 'active' : '' ?>">
          <span class="nav-icon">🔍</span>
          <span class="nav-text">NLP File Search</span>
        </a>
      </li>
      <?php endif; ?>

      <?php if ($permissions['task_management']): ?>
      <li class="nav-item">
        <a href="index.php#reports" class="nav-link" data-tab="reports">
          <span class="nav-icon">📊</span>
          <span class="nav-text">Reports</span>
        </a>
      </li>
      <?php endif; ?>
      
      <li class="nav-item">
        <a href="index.php#account-management" class="nav-link" data-tab="account-management">
          <span class="nav-icon">⚙️</span>
          <span class="nav-text">Account Settings</span>
        </a>
      </li>
    </ul>
    
    <div class="sidebar-footer">
      <a href="#" class="logout-link" onclick="openModal('logoutModal'); return false;">
        <span class="nav-icon">🚪</span>
        <span class="nav-text">Logout</span>
      </a>
    </div>
  </nav>
</aside>

<script>
// Handle sidebar tab clicks to switch main content tabs
document.addEventListener('DOMContentLoaded', function() {
  const sidebarLinks = document.querySelectorAll('.sidebar-nav .nav-link[data-tab]');
  
  sidebarLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      
      const tabName = this.dataset.tab;
      const tabLink = document.querySelector(`.tab-link[data-tab="${tabName}"]`);
      
      if (tabLink) {
        tabLink.click();
      }
    });
  });
});
</script>