<aside class="github-sidebar">
  <div class="sidebar-header">
    <h3 class="sidebar-title">Navigation</h3>
  </div>
  
  <nav class="sidebar-nav">
    <ul class="nav-list">
      <li class="nav-item">
        <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
          <span class="nav-icon">📊</span>
          <span class="nav-text">Dashboard</span>
        </a>
      </li>
      
      <li class="nav-item has-submenu">
        <a href="#" class="nav-link">
          <span class="nav-icon">📝</span>
          <span class="nav-text">Task Management</span>
          <span class="nav-arrow">▶</span>
        </a>
        <ul class="submenu">
          <li><a href="dashboard.php#task-management" class="nav-link">
            <span class="nav-icon">📋</span>
            <span class="nav-text">Create Tasks</span>
          </a></li>
          <li><a href="dashboard.php#task-management" class="nav-link">
            <span class="nav-icon">👁️</span>
            <span class="nav-text">View Tasks</span>
          </a></li>
        </ul>
      </li>

      <li class="nav-item">
        <a href="file-uploads.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'file-uploads.php' ? 'active' : '' ?>">
          <span class="nav-icon">�</span>
          <span class="nav-text">File Uploads</span>
        </a>
      </li>

      <li class="nav-item">
        <a href="dashboard.php#reports" class="nav-link">
          <span class="nav-icon">📊</span>
          <span class="nav-text">Reports</span>
        </a>
      </li>
      
      <li class="nav-item">
        <a href="dashboard.php#account-management" class="nav-link">
          <span class="nav-icon">⚙️</span>
          <span class="nav-text">Account Management</span>
        </a>
      </li>
    </ul>
    
    <div class="sidebar-footer">
      <a href="logout.php" class="logout-link" onclick="return confirm('Are you sure you want to logout?');">
        <span class="nav-icon">🚪</span>
        <span class="nav-text">Logout</span>
      </a>
    </div>
  </nav>
</aside>