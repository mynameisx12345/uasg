<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SG File Manager</title>
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
</body>
</html>