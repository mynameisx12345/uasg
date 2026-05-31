<?php
session_start();
require_once("../resources/class.php");

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once("../resources/objects/db_config.php");
$db = Database::getInstance();
$_setting = $db->selectOne("SELECT setting_value FROM system_settings_tbl WHERE setting_key = 'show_upload_disclaimer'");
$showDisclaimer = ($_setting['setting_value'] ?? '1') === '1';
$_setting2 = $db->selectOne("SELECT setting_value FROM system_settings_tbl WHERE setting_key = 'allow_manual_override'");
$allowOverride = ($_setting2['setting_value'] ?? '1') === '1';

// Role-based override: admin always allowed; subadmin/member checked against settings
$userType = strtolower($_SESSION['user_type'] ?? 'admin');
$allowOverrideExplorer = $allowOverride;
$allowOverrideBatch = $allowOverride;
if ($allowOverride && $userType !== 'admin') {
    $_rolesExplorer = $db->selectOne("SELECT setting_value FROM system_settings_tbl WHERE setting_key = 'override_roles_file_explorer'");
    $allowOverrideExplorer = in_array($userType, json_decode($_rolesExplorer['setting_value'] ?? '[]', true) ?: []);
    $_rolesBatch = $db->selectOne("SELECT setting_value FROM system_settings_tbl WHERE setting_key = 'override_roles_batch_upload'");
    $allowOverrideBatch = in_array($userType, json_decode($_rolesBatch['setting_value'] ?? '[]', true) ?: []);
}
$_settingView = $db->selectOne("SELECT setting_value FROM system_settings_tbl WHERE setting_key = 'file_view_validity_date'");
$fileViewValidityDate = $_settingView['setting_value'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>File Center - UASG</title>
  <link rel="stylesheet" href="../resources/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../resources/theme-overrides.css">
  <script src='../js/all.js?v=<?= time() ?>'></script>
  <script src='../js/jquery.js?v=<?= time() ?>'></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .fc-tabs { display:flex; gap:0; border-bottom:2px solid #e9ecef; margin-bottom:20px; }
    .fc-tab { padding:12px 24px; border:none; background:none; font-size:14px; font-weight:500; color:#666; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all 0.2s; }
    .fc-tab:hover { color:#007bff; }
    .fc-tab.active { color:#007bff; border-bottom-color:#007bff; }
    .fc-tab-content { display:none; }
    .fc-tab-content.active { display:block; }
    .swal2-container { z-index: 99999 !important; }
  </style>
</head>
<body>
  <?php require_once("header.php");?>
  <header class="topbar">
    <h1>File Center</h1>
    <div class="user-info">
      <span>Welcome, Admin</span>
    </div>
  </header>

  <main class="main">
    <?php require_once("sidebar.php");?>

    <section class="content">
      <div class="card">
        <div class="fc-tabs">
          <button class="fc-tab active" data-tab="browse">📁 Browse Files</button>
          <button class="fc-tab" data-tab="search">🔍 Smart Search</button>
          <button class="fc-tab" data-tab="upload">⬆️ Upload Files</button>
        </div>

        <div class="fc-tab-content active" id="fc-browse">
          <script>window.allowManualOverride = <?= $allowOverrideExplorer ? 'true' : 'false' ?>; window.allowManualOverrideBatch = <?= $allowOverrideBatch ? 'true' : 'false' ?>; window.fileViewValidityDate = '<?= $fileViewValidityDate ?>'; window.userType = '<?= $userType ?>';</script>
          <?php include("../resources/components/file-explorer.php"); ?>
        </div>

        <div class="fc-tab-content" id="fc-search">
          <?php include("../resources/components/smart-search.php"); ?>
        </div>
        <div class="fc-tab-content" id="fc-upload">
          <?php include("../resources/components/batch-upload.php"); ?>
        </div>
      </div>
    </section>
  </main>

  <script>
    $(function(){
      var mlDisclaimerAccepted = <?= $showDisclaimer ? 'false' : 'true' ?>;
      $('.fc-tab').click(function(){
        var tab = $(this).data('tab');
        if (tab === 'upload' && !mlDisclaimerAccepted) {
          $('#mlDisclaimerModal').css('display','flex');
          return;
        }
        $('.fc-tab').removeClass('active');
        $(this).addClass('active');
        $('.fc-tab-content').removeClass('active');
        $('#fc-' + tab).addClass('active');
      });
      $('#mlDisclaimerCheckbox').on('change', function(){
        $('#mlDisclaimerAgreeBtn').prop('disabled', !this.checked);
      });
      $('#mlDisclaimerAgreeBtn').on('click', function(){
        mlDisclaimerAccepted = true;
        $('#mlDisclaimerModal').hide();
        $('.fc-tab[data-tab="upload"]').click();
      });
    });
  </script>

<!-- ML Classification Disclaimer Modal -->
<style>
.ml-disclaimer-modal{max-width:560px;border-radius:8px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.3);max-height:90vh;overflow-y:auto}
.ml-disclaimer-header{background:linear-gradient(135deg,#1565c0,#1976d2);color:#fff;padding:20px 24px}
.ml-disclaimer-title-row{display:flex;align-items:center;gap:12px}
.ml-disclaimer-icon{background:rgba(255,255,255,.15);border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;font-size:20px}
.ml-disclaimer-warning{background:#fff3e0;border:1px solid #ffe0b2;border-radius:6px;padding:14px 16px;margin-bottom:18px;display:flex;gap:10px;align-items:flex-start;font-size:13px;color:#e65100;line-height:1.5}
.ml-disclaimer-stats{background:#e8f5e9;border:1px solid #c8e6c9;border-radius:6px;padding:14px 16px;margin-bottom:20px;font-size:13px;color:#2e7d32;display:flex;align-items:flex-start;gap:10px}
.ml-disclaimer-checkbox-label{display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 14px;border:1px solid #e0e0e0;border-radius:6px;transition:border-color .2s;font-size:13px;color:#333}
.ml-disclaimer-checkbox-label:hover{border-color:#1976d2}
.ml-disclaimer-checkbox-label input[type="checkbox"]{width:18px;height:18px;cursor:pointer;flex-shrink:0}
.ml-disclaimer-agree-btn{padding:10px 28px;font-size:14px;font-weight:600;border-radius:6px;opacity:.5;cursor:not-allowed;transition:opacity .2s}
.ml-disclaimer-agree-btn:not(:disabled){opacity:1;cursor:pointer}
</style>
<div id="mlDisclaimerModal" class="modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:99999;align-items:center;justify-content:center;">
  <div class="modal-content ml-disclaimer-modal" style="background:#fff;margin:auto;">
    <div class="modal-header ml-disclaimer-header">
      <div class="ml-disclaimer-title-row">
        <div class="ml-disclaimer-icon">🤖</div>
        <div>
          <h2 style="margin:0;font-size:18px;font-weight:700;">AI Classification Notice</h2>
          <p style="margin:4px 0 0;font-size:12px;opacity:.85;">Machine Learning Model Disclaimer</p>
        </div>
      </div>
    </div>
    <div style="padding:24px;">
      <div class="ml-disclaimer-warning">
        <span style="font-size:20px;">⚠️</span>
        <div><strong>Important:</strong> The ML classification model is currently in its early learning phase with limited training samples. Classification accuracy will improve over time as more documents are processed.</div>
      </div>
      <div style="font-size:13px;color:#424242;line-height:1.7;margin-bottom:18px;">
        <p style="margin:0 0 12px;">Please be aware of the following:</p>
        <ul style="margin:0;padding-left:20px;">
          <li style="margin-bottom:8px;"><strong>Classifications may be inaccurate</strong> — the model may assign incorrect categories to your uploaded documents.</li>
          <li style="margin-bottom:8px;"><strong>Help the model learn</strong> — if a classification is incorrect, please update it to the correct category. Each correction improves future predictions.</li>
          <li style="margin-bottom:8px;"><strong>Classifications are monitored</strong> — the administrator can view and monitor all category classifications and any manual changes made.</li>
        </ul>
      </div>
      <div class="ml-disclaimer-stats">
        <span style="font-size:18px;">📊</span>
        <div>
          <strong>Model Accuracy Improves With Data</strong>
          <p style="margin:6px 0 0;line-height:1.5;">The model requires approximately <strong>50+ documents per category</strong> to achieve reliable classification. Your uploads directly contribute to improving the system's accuracy.</p>
        </div>
      </div>
      <label class="ml-disclaimer-checkbox-label">
        <input type="checkbox" id="mlDisclaimerCheckbox">
        <span>I understand that classifications may be inaccurate and I will help correct misclassifications when identified.</span>
      </label>
    </div>
    <div style="padding:16px 24px;border-top:1px solid #e0e0e0;display:flex;justify-content:flex-end;">
      <button type="button" id="mlDisclaimerAgreeBtn" class="btn-primary ml-disclaimer-agree-btn" disabled>I Agree &amp; Continue</button>
    </div>
  </div>
</div>
<!-- File Properties Modal -->
<div id="fePropsModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:10px;width:92%;max-width:550px;max-height:85vh;overflow-y:auto;padding:0;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
    <div style="position:sticky;top:0;background:#fff;padding:16px 20px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;border-radius:10px 10px 0 0;z-index:1;">
      <h3 style="margin:0;font-size:15px;">📋 File Properties</h3>
      <button onclick="$('#fePropsModal').hide();" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#999;">&times;</button>
    </div>
    <div id="fePropsContent" style="padding:20px;"></div>
  </div>
</div>

<!-- File Viewer Modal (Full-screen) -->
<div id="feViewerModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.85);z-index:10000;flex-direction:column;">
  <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 20px;background:rgba(0,0,0,0.4);">
    <span id="feViewerTitle" style="color:#fff;font-size:14px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70%;"></span>
    <button onclick="$('#feViewerModal').hide().find('#feViewerBody').html('');" style="background:none;border:none;color:#fff;font-size:28px;cursor:pointer;padding:0 8px;">&times;</button>
  </div>
  <div id="feViewerBody" style="flex:1;overflow:auto;display:flex;align-items:center;justify-content:center;padding:10px;"></div>
</div>

<!-- Subscription Message Modal -->
<div id="feSubMsgModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:10000;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:12px;padding:40px 30px;max-width:420px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
    <div style="font-size:48px;margin-bottom:16px;">🔒</div>
    <h3 style="margin:0 0 10px;font-size:18px;color:#1e293b;">Viewing Disabled</h3>
    <p style="color:#64748b;font-size:14px;line-height:1.6;margin:0 0 20px;">File viewing is disabled due to incomplete subscription. Please contact your system administrator.</p>
    <button onclick="$('#feSubMsgModal').hide();" style="padding:10px 28px;border:none;border-radius:8px;background:#7b1228;color:#fff;font-size:14px;font-weight:600;cursor:pointer;">OK</button>
  </div>
</div>

</body>
</html>
