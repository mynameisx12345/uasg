<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<aside class="github-sidebar">
  <div class="sidebar-brand">
    <img src="../images/uasglogo.png" alt="UASG Logo">
    <span class="sidebar-brand-text">UASG Member</span>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="dashboard.php" class="nav-item <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"><span class="nav-icon">📊</span> Dashboard</a>
    <a href="file-center.php" class="nav-item <?= $currentPage === 'file-center.php' ? 'active' : '' ?>"><span class="nav-icon">📂</span> File Center</a>
    <a href="tasks.php" class="nav-item <?= $currentPage === 'tasks.php' ? 'active' : '' ?>"><span class="nav-icon">📝</span> Tasks <span id="navTaskBadge" style="display:none;background:#c0392b;color:#fff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:10px;margin-left:4px;"></span></a>

    <div class="nav-section-label">System</div>
    <a href="security.php" class="nav-item <?= $currentPage === 'security.php' ? 'active' : '' ?>"><span class="nav-icon">🔒</span> Security</a>

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

<script>
function updateNavTaskBadge(){
  if(typeof $==='undefined') return;
  $.post('ajax.php',{CALL:9},function(r){
    var p=(r.data||[]).length;
    $.post('ajax.php',{CALL:22},function(r2){
      var total=p+(r2.data||[]).length;
      var badge=$('#navTaskBadge');
      if(total>0) badge.text(total).show(); else badge.hide();
    },'json');
  },'json');
}
$(function(){ updateNavTaskBadge(); });
</script>
