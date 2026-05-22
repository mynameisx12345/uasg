<?php
require_once(__DIR__ . '/../resources/objects/permission_class.php');
$userId = $_SESSION['user_id'] ?? 0;
$permissions = [
    'task_management' => SubadminPermission::hasPermission($userId, 'task_management', 'view'),
    'file_management' => SubadminPermission::hasPermission($userId, 'file_management', 'view'),
    'user_management' => SubadminPermission::hasPermission($userId, 'user_management', 'view'),
    'entry_module' => SubadminPermission::hasPermission($userId, 'entry_module', 'view'),
    'reports' => SubadminPermission::hasPermission($userId, 'reports', 'view')
];
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="github-sidebar">
  <div class="sidebar-brand">
    <img src="../images/uasglogo.png" alt="UASG Logo">
    <span class="sidebar-brand-text">UASG <?= $_SESSION['position'] ?? 'Adviser' ?></span>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="index.php" class="nav-item <?= $currentPage == 'index.php' ? 'active' : '' ?>"><span class="nav-icon">📊</span> Dashboard</a>
    <a href="file-center.php" class="nav-item <?= $currentPage == 'file-center.php' ? 'active' : '' ?>"><span class="nav-icon">📂</span> File Center</a>

    <?php if ($permissions['user_management'] || $permissions['task_management']): ?>
    <div class="nav-section-label">Management</div>
    <?php endif; ?>

    <?php if ($permissions['user_management']): ?>
    <a href="user-management.php" class="nav-item <?= $currentPage == 'user-management.php' ? 'active' : '' ?>"><span class="nav-icon">👤</span> Users</a>
    <?php endif; ?>

    <?php if ($permissions['task_management']): ?>
    <a href="task-management.php" class="nav-item <?= $currentPage == 'task-management.php' ? 'active' : '' ?>"><span class="nav-icon">📝</span> Tasks</a>
    <?php endif; ?>

    <div class="nav-section-label">System</div>
    <?php if ($permissions['reports']): ?>
    <a href="reports-page.php" class="nav-item <?= $currentPage == 'reports-page.php' ? 'active' : '' ?>"><span class="nav-icon">📊</span> Reports</a>
    <?php endif; ?>
    <a href="settings.php" class="nav-item <?= $currentPage == 'settings.php' ? 'active' : '' ?>"><span class="nav-icon">⚙️</span> Settings</a>

    <div class="sidebar-footer">
      <a href="#" id="logoutLink" class="nav-item nav-logout"><span class="nav-icon">🚪</span> Logout</a>
    </div>
  </nav>
</aside>

<!-- Logout Modal -->
<div id="logoutModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1200;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
  <div style="background:#fff;padding:28px;border-radius:14px;max-width:360px;width:90%;text-align:center;box-shadow:0 20px 50px rgba(0,0,0,0.3);">
    <p style="font-size:18px;font-weight:600;margin-bottom:8px;color:#1a0a0e;">Confirm Logout</p>
    <p style="color:#64748b;font-size:14px;margin-bottom:20px;">Are you sure you want to logout?</p>
    <div style="display:flex;gap:10px;justify-content:center;">
      <button onclick="window.location.href='logout.php'" style="padding:10px 22px;background:linear-gradient(135deg,#7b1228,#5f0e20);color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:14px;">Logout</button>
      <button onclick="document.getElementById('logoutModal').style.display='none'" style="padding:10px 22px;background:#f1f5f9;color:#333;border:none;border-radius:8px;cursor:pointer;font-weight:500;font-size:14px;">Cancel</button>
    </div>
  </div>
</div>

<script>
document.getElementById('logoutLink').addEventListener('click', function(e){
  e.preventDefault();
  document.getElementById('logoutModal').style.display = 'flex';
});
document.getElementById('logoutModal').addEventListener('click', function(e){
  if(e.target === this) this.style.display = 'none';
});
</script>


