<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - UASG</title>
  
  <link rel="stylesheet" href="../resources/style.css">
</head>
<body>
  <!-- HEADER -->
  <?php require_once("header.php");?>
  <header class="topbar">
    <h1>Dashboard</h1>
    <div class="user-info">
      <span>Welcome, Admin</span>
    </div>
  </header>
<!-- MAIN -->
<main class="main">
  
    <!-- SIDEBAR -->
    <?php require_once("sidebar.php");?>

    <!-- CONTENT -->
   

    <section class="content">
      <!-- Overview Cards -->
      <div class="card-grid">
        <div class="card stat-card">
          <h2>Files</h2>
          <p class="stat">125</p>
          <span class="sub">Total Uploaded</span>
        </div>
        <div class="card stat-card">
          <h2>Categories</h2>
          <p class="stat">12</p>
          <span class="sub">Active Categories</span>
        </div>
        <div class="card stat-card">
          <h2>Users</h2>
          <p class="stat">56</p>
          <span class="sub">Registered Members</span>
        </div>
        <div class="card stat-card">
          <h2>Pending</h2>
          <p class="stat">8</p>
          <span class="sub">Files for Review</span>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="card">
        <h2>Recent Activity</h2>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>User</th>
                <th>Action</th>
                <th>File</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>2025-09-28</td>
                <td>Maria Santos</td>
                <td>Uploaded</td>
                <td>Constitution.pdf</td>
              </tr>
              <tr>
                <td>2025-09-27</td>
                <td>John Cruz</td>
                <td>Edited Category</td>
                <td>Finance</td>
              </tr>
              <tr>
                <td>2025-09-25</td>
                <td>Ana Lopez</td>
                <td>Approved</td>
                <td>Minutes_Sept.pdf</td>
              </tr>
              <tr>
                <td>2025-09-24</td>
                <td>Mark Reyes</td>
                <td>Added Position</td>
                <td>Secretary</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  
  <!-- PWA Scripts -->
  <script src="../js/pwa-helper.js"></script>
</body>
</html>