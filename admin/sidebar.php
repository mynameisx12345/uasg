<aside class="sidebar">
  <h3>Navigation</h3>
  <ul>
    <li><a href="index.php">📊 Dashboard</a></li>
    <li><a href="nlp-search.php">🔍 NLP File Search</a></li>
    <li><a href="entry-module.php">📋 Entry Module</a></li>
    <li><a href="user-management.php">👤 User Management</a></li>
    <li><a href="task-management.php">📝 Task Management</a></li>
    <li><a href="file-management.php">📁 File Management</a></li>
    <li><a href="members-management.php">👥 Members</a></li>
    <li><a href="reports-page.php">📊 Reports & Analytics</a></li>
    <li><a href="account-security.php">🔒 Account Security</a></li>
    <li><a href="#" id="logoutLink" style="color: #e74c3c;">🚪 Logout</a></li>
  </ul>
</aside>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="modal" style="display: none;">
  <div class="modal-content" style="max-width: 400px;">
    <div class="modal-header">
      <span>Confirm Logout</span>
      <span class="close-modal" onclick="closeLogoutModal()">&times;</span>
    </div>
    <div style="padding: 20px 0;">
      <p><i class="fas fa-sign-out-alt" style="color: #e74c3c; font-size: 48px;"></i></p>
      <p style="font-size: 16px; margin-top: 15px;">Are you sure you want to logout?</p>
    </div>
    <div class="form-actions" style="justify-content: flex-end; margin-top: 1.5rem;">
      <button onclick="confirmLogout()" class="btn-danger">Yes, Logout</button>
      <button onclick="closeLogoutModal()" class="btn-secondary">Cancel</button>
    </div>
  </div>
</div>

<style>
  .modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }
  .modal-content {
    background: #fff;
    padding: 1.5rem;
    border-radius: 8px;
    min-width: 400px;
    max-width: 600px;
  }
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    font-weight: 600;
    font-size: 1.2rem;
  }
  .close-modal {
    cursor: pointer;
    font-size: 1.5rem;
    color: #666;
  }
  .form-actions {
    display: flex;
    gap: 0.5rem;
  }
  .btn-danger {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
  }
  .btn-danger:hover {
    background: #c0392b;
  }
  .btn-secondary {
    background: #95a5a6;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
  }
  .btn-secondary:hover {
    background: #7f8c8d;
  }
</style>

<script>
  // Open logout modal
  document.getElementById('logoutLink').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('logoutModal').style.display = 'flex';
  });

  // Close logout modal
  function closeLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
  }

  // Confirm logout
  function confirmLogout() {
    window.location.href = 'logout.php';
  }

  // Close modal when clicking outside
  document.getElementById('logoutModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeLogoutModal();
    }
  });
</script>