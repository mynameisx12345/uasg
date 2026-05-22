<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
require_once("../resources/objects/db_config.php");
$db = Database::getInstance();

$settings = [];
$rows = $db->select("SELECT setting_key, setting_value FROM system_settings_tbl");
foreach ($rows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$showDisclaimer = ($settings['show_upload_disclaimer'] ?? '1') === '1';
$allowOverride = ($settings['allow_manual_override'] ?? '1') === '1';
$overrideRolesExplorer = json_decode($settings['override_roles_file_explorer'] ?? '["subadmin","member"]', true);
$overrideRolesBatch = json_decode($settings['override_roles_batch_upload'] ?? '["subadmin","member"]', true);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../resources/style.css?v=<?= time() ?>">
  <script src='../js/jquery.js'></script>
  <style>
    body { background: transparent; padding: 20px; margin: 0; }
    .settings-card{background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:20px}
    .settings-card h3{margin:0 0 6px;font-size:16px;color:#1e293b}
    .settings-card p{margin:0 0 16px;font-size:13px;color:#64748b}
    .toggle-row{display:flex;align-items:center;justify-content:space-between;padding:14px 0;border-bottom:1px solid #f1f5f9}
    .toggle-row:last-child{border-bottom:none}
    .toggle-label{font-size:14px;font-weight:500;color:#334155}
    .toggle-desc{font-size:12px;color:#94a3b8;margin-top:2px}
    .toggle-switch{position:relative;width:44px;height:24px;flex-shrink:0}
    .toggle-switch input{opacity:0;width:0;height:0}
    .toggle-slider{position:absolute;inset:0;background:#cbd5e1;border-radius:24px;cursor:pointer;transition:.2s}
    .toggle-slider:before{content:'';position:absolute;width:18px;height:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s}
    .toggle-switch input:checked+.toggle-slider{background:#7b1228}
    .toggle-switch input:checked+.toggle-slider:before{transform:translateX(20px)}
    .save-status{font-size:13px;color:#16a34a;margin-left:12px;opacity:0;transition:opacity .3s}
    .save-status.show{opacity:1}
  </style>
</head>
<body>
  <div class="settings-card">
    <h3>Upload & Classification</h3>
    <p>Configure file upload behavior and ML classification settings.</p>
    <div class="toggle-row">
      <div>
        <div class="toggle-label">Show ML Disclaimer on Upload</div>
        <div class="toggle-desc">Display a disclaimer modal warning users about ML classification accuracy before uploading files.</div>
      </div>
      <div style="display:flex;align-items:center;">
        <label class="toggle-switch">
          <input type="checkbox" id="showUploadDisclaimer" <?= $showDisclaimer ? 'checked' : '' ?>>
          <span class="toggle-slider"></span>
        </label>
        <span class="save-status" id="saveStatus">✓ Saved</span>
      </div>
    </div>
    <div class="toggle-row">
      <div>
        <div class="toggle-label">Allow Manual Override of Classification</div>
        <div class="toggle-desc">Allow users to manually reclassify files in File Center and Batch Upload.</div>
      </div>
      <div style="display:flex;align-items:center;">
        <label class="toggle-switch">
          <input type="checkbox" id="allowManualOverride" <?= $allowOverride ? 'checked' : '' ?>>
          <span class="toggle-slider"></span>
        </label>
        <span class="save-status" id="saveStatusOverride">✓ Saved</span>
      </div>
    </div>
    <div id="overrideRolesSection" style="<?= $allowOverride ? '' : 'display:none;' ?>padding-left:24px;border-left:3px solid #e2e8f0;margin:12px 0 0 12px;">
      <div class="toggle-row" style="flex-direction:column;align-items:flex-start;gap:10px;">
        <div>
          <div class="toggle-label">File Explorer — Allowed Roles</div>
          <div class="toggle-desc">Which user types can override classification in File Explorer.</div>
        </div>
        <div style="display:flex;gap:16px;align-items:center;">
          <label style="font-size:13px;cursor:pointer;display:flex;align-items:center;gap:6px;">
            <input type="checkbox" class="role-cb-explorer" value="subadmin" <?= in_array('subadmin', $overrideRolesExplorer) ? 'checked' : '' ?>> Subadmin
          </label>
          <label style="font-size:13px;cursor:pointer;display:flex;align-items:center;gap:6px;">
            <input type="checkbox" class="role-cb-explorer" value="member" <?= in_array('member', $overrideRolesExplorer) ? 'checked' : '' ?>> Member
          </label>
          <span class="save-status" id="saveStatusRolesExplorer">✓ Saved</span>
        </div>
      </div>
      <div class="toggle-row" style="flex-direction:column;align-items:flex-start;gap:10px;">
        <div>
          <div class="toggle-label">Batch Upload — Allowed Roles</div>
          <div class="toggle-desc">Which user types can override classification in Batch Upload.</div>
        </div>
        <div style="display:flex;gap:16px;align-items:center;">
          <label style="font-size:13px;cursor:pointer;display:flex;align-items:center;gap:6px;">
            <input type="checkbox" class="role-cb-batch" value="subadmin" <?= in_array('subadmin', $overrideRolesBatch) ? 'checked' : '' ?>> Subadmin
          </label>
          <label style="font-size:13px;cursor:pointer;display:flex;align-items:center;gap:6px;">
            <input type="checkbox" class="role-cb-batch" value="member" <?= in_array('member', $overrideRolesBatch) ? 'checked' : '' ?>> Member
          </label>
          <span class="save-status" id="saveStatusRolesBatch">✓ Saved</span>
        </div>
      </div>
    </div>
  </div>
  <script>
  $('#showUploadDisclaimer').on('change', function(){
    $.post('ajax.php', {CALL:'save_setting', key:'show_upload_disclaimer', value: this.checked?'1':'0'}, function(r){
      if(r.status==='SUCCESS'){ $('#saveStatus').addClass('show'); setTimeout(function(){ $('#saveStatus').removeClass('show'); }, 2000); }
    },'json');
  });
  $('#allowManualOverride').on('change', function(){
    $('#overrideRolesSection').toggle(this.checked);
    $.post('ajax.php', {CALL:'save_setting', key:'allow_manual_override', value: this.checked?'1':'0'}, function(r){
      if(r.status==='SUCCESS'){ $('#saveStatusOverride').addClass('show'); setTimeout(function(){ $('#saveStatusOverride').removeClass('show'); }, 2000); }
    },'json');
  });
  $('.role-cb-explorer').on('change', function(){
    var roles=[]; $('.role-cb-explorer:checked').each(function(){ roles.push($(this).val()); });
    $.post('ajax.php', {CALL:'save_setting', key:'override_roles_file_explorer', value: JSON.stringify(roles)}, function(r){
      if(r.status==='SUCCESS'){ $('#saveStatusRolesExplorer').addClass('show'); setTimeout(function(){ $('#saveStatusRolesExplorer').removeClass('show'); }, 2000); }
    },'json');
  });
  $('.role-cb-batch').on('change', function(){
    var roles=[]; $('.role-cb-batch:checked').each(function(){ roles.push($(this).val()); });
    $.post('ajax.php', {CALL:'save_setting', key:'override_roles_batch_upload', value: JSON.stringify(roles)}, function(r){
      if(r.status==='SUCCESS'){ $('#saveStatusRolesBatch').addClass('show'); setTimeout(function(){ $('#saveStatusRolesBatch').removeClass('show'); }, 2000); }
    },'json');
  });
  </script>
</body>
</html>
