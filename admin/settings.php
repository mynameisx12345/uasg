<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings - UASG Admin</title>
  <link rel="stylesheet" href="../resources/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../resources/theme-overrides.css">
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
  <style>
    .tab-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .tab-header .tabs { margin-bottom: 0; }
    .settings-iframe { width: 100%; border: none; height: calc(100vh - 180px); border-radius: 8px; }
    .card .tab-content { display: block !important; height: 0; overflow: hidden; }
    .card .tab-content.active { height: auto; overflow: visible; }
  </style>
</head>
<body>
  <?php require_once("header.php");?>
  <header class="topbar">
    <h1>Settings</h1>
    <div class="user-info"><span>Welcome, Admin</span></div>
  </header>
  <main class="main">
    <?php require_once("sidebar.php");?>
    <section class="content">
      <div class="card">
        <div class="tab-header">
          <div class="tabs">
            <button class="tab-btn active" data-tab="general">⚙️ General</button>
            <button class="tab-btn" data-tab="ml-classification">🤖 ML Classification</button>
            <button class="tab-btn" data-tab="security">🔒 Security</button>
          </div>
        </div>

        <div id="general" class="tab-content active">
          <iframe src="settings-general.php" class="settings-iframe" id="generalFrame"></iframe>
        </div>
        <div id="ml-classification" class="tab-content">
          <iframe src="ml-management.php?embed=1" class="settings-iframe" id="mlFrame"></iframe>
        </div>
        <div id="security" class="tab-content">
          <iframe src="settings-security.php" class="settings-iframe" id="securityFrame"></iframe>
        </div>
      </div>
    </section>
  </main>
  <script>
  (function($){
    $('.tab-btn').on('click', function(){
      $('.tab-btn').removeClass('active');
      $('.tab-content').removeClass('active');
      $(this).addClass('active');
      $('#' + $(this).data('tab')).addClass('active');
    });
    // Auto-resize iframes
    function resizeIframe(iframe) {
      try {
        iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 40 + 'px';
      } catch(e) {}
    }
    $('iframe.settings-iframe').on('load', function(){ resizeIframe(this); });
  })(jQuery);
  </script>
</body>
</html>
