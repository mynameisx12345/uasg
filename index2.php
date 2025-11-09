<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SG File Manager</title>
  
  <!-- PWA Meta Tags -->
  <meta name="description" content="University Student Government File Management System">
  <meta name="theme-color" content="#2196F3">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="UASG">
  <meta name="msapplication-TileColor" content="#2196F3">
  
  <!-- PWA Manifest -->
  <link rel="manifest" href="manifest.json">
  
  <!-- Favicon and Icons -->
  <link rel="icon" type="image/png" sizes="32x32" href="resources/icons/icon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="resources/icons/icon-16x16.png">
  <link rel="apple-touch-icon" href="resources/icons/icon-152x152.png">
  <link rel="apple-touch-icon" sizes="72x72" href="resources/icons/icon-72x72.png">
  <link rel="apple-touch-icon" sizes="96x96" href="resources/icons/icon-96x96.png">
  <link rel="apple-touch-icon" sizes="128x128" href="resources/icons/icon-128x128.png">
  <link rel="apple-touch-icon" sizes="144x144" href="resources/icons/icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="resources/icons/icon-152x152.png">
  <link rel="apple-touch-icon" sizes="192x192" href="resources/icons/icon-192x192.png">
  <link rel="apple-touch-icon" sizes="384x384" href="resources/icons/icon-384x384.png">
  <link rel="apple-touch-icon" sizes="512x512" href="resources/icons/icon-512x512.png">
  
  <!-- Stylesheets -->
  <link rel="stylesheet" href="resources/style.css">
</head>
<body>
  <!-- HEADER -->
  <header>
    <div class="logo">SG File Manager</div>
    <nav>
      <a href="#">Dashboard</a>
      <a href="#">Upload</a>
      <a href="#">Reports</a>
      <a href="#">Settings</a>
    </nav>
  </header>

  <!-- MAIN -->
  <main>
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <h3>Navigation</h3>
      <ul>
        <li class="has-sub">
          <a href="#">📂 Files</a>
          <ul class="submenu">
            <li><a href="#">All Files</a></li>
            <li><a href="#">Submitted</a></li>
            <li><a href="#">Approved</a></li>
            <li><a href="#">Rejected</a></li>
          </ul>
        </li>
        <li class="has-sub">
          <a href="#">👥 Members</a>
          <ul class="submenu">
            <li><a href="#">Student Officers</a></li>
            <li><a href="#">Faculty Advisers</a></li>
          </ul>
        </li>
        <li><a href="#">⚙️ Settings</a></li>
      </ul>
    </aside>

    <!-- CONTENT -->
    <section class="content">
      <div class="card">
        <h2>Recent Uploads</h2>
        <div class="file-grid">
          <div class="file-item">📄 Constitution.pdf</div>
          <div class="file-item">📄 FinancialReport.xlsx</div>
          <div class="file-item">📄 Minutes.docx</div>
          <div class="file-item">📄 ProjectPlan.pdf</div>
        </div>
      </div>

      <div class="card">
        <h2>Pending Approvals</h2>
        <p>No pending files at the moment 🎉</p>
      </div>
    </section>
  </main>
  
  <!-- PWA Scripts -->
  <script src="js/pwa-helper.js"></script>
</body>
</html>