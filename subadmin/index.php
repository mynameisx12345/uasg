<?php
require_once("../resources/session.php");

// Require adviser role
$session = SessionManager::getInstance();
$session->requireRole(['Adviser', 'adviser']);

$currentUser = $session->getUserData();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Adviser Dashboard - UASG</title>
  
  <!-- PWA Meta Tags -->
  <meta name="description" content="UASG Adviser Dashboard - Task Management and Student Oversight">
  <meta name="theme-color" content="#2196F3">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="UASG Adviser">
  <meta name="msapplication-TileColor" content="#2196F3">
  
  <!-- PWA Manifest -->
  <link rel="manifest" href="../manifest.json">
  
  <!-- Favicon and Icons -->
  <link rel="icon" type="image/png" sizes="32x32" href="../resources/icons/icon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../resources/icons/icon-16x16.png">
  <link rel="apple-touch-icon" href="../resources/icons/icon-152x152.png">
  
  <link rel="stylesheet" href="../resources/style.css">
  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
  <script src='../js/datatable.js'></script>
</head>
<body>
  <!-- HEADER -->
  <?php require_once("header.php");?>
  <header class="topbar">
    <h1>Adviser Dashboard</h1>
    <div class="user-info">
      <span>Welcome, <?= htmlspecialchars($currentUser['full_name']) ?></span>
    </div>
  </header>
  
  <!-- MAIN -->
  <main class="main">
    <!-- SIDEBAR -->
    <?php require_once("sidebar.php");?>

    <!-- CONTENT -->
    <section class="content">
      <!-- TABS -->
      <div class="tabs">
        <button class="tab-link active" data-tab="dashboard">Dashboard</button>
        <button class="tab-link" data-tab="task-management">Task Management</button>
        <button class="tab-link" data-tab="reports">Reports</button>
        <button class="tab-link" data-tab="account-management">Account Management</button>
      </div>

      <!-- TAB CONTENT: DASHBOARD -->
      <div class="tab-content active" id="dashboard">
        <!-- Overview Cards -->
        <div class="card-grid">
          <div class="card stat-card">
            <h2>Total Tasks</h2>
            <p class="stat" id="totalTasks">0</p>
            <span class="sub">Created by me</span>
          </div>
          <div class="card stat-card">
            <h2>Pending Reviews</h2>
            <p class="stat" id="pendingReviews">0</p>
            <span class="sub">Need attention</span>
          </div>
          <div class="card stat-card">
            <h2>Completed Tasks</h2>
            <p class="stat" id="completedTasks">0</p>
            <span class="sub">Approved submissions</span>
          </div>
          <div class="card stat-card">
            <h2>Active Members</h2>
            <p class="stat" id="activeMembers">0</p>
            <span class="sub">With submissions</span>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
          <h2>Recent Activity</h2>
          <div class="table-container">
            <table class="data-table" id="recentActivityTable">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Student</th>
                  <th>Action</th>
                  <th>Task</th>
                  <th>Status</th>
                    <th>NLP Category</th>
                    <th>NLP Score</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB CONTENT: TASK MANAGEMENT -->
      <div class="tab-content" id="task-management">
        <div class="card">
          <h2>Task Management</h2>
          
          <!-- Task Creation Form -->
          <div class="compact-form">
            <h3>Create New Task</h3>
            <div class="form-columns">
              <div class="form-column">
                <div class="form-section">
                  <h4>Task Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="taskTitle">Task Title</label>
                      <input type="text" id="taskTitle" name="taskTitle" placeholder="Enter task title..." required>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="taskCategory">Category</label>
                      <select id="taskCategory" name="taskCategory" required>
                        <option value="">Select Category</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="taskDescription">Description</label>
                      <textarea id="taskDescription" name="taskDescription" rows="4" placeholder="Enter task description..."></textarea>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="taskDeadline">Deadline</label>
                      <input type="datetime-local" id="taskDeadline" name="taskDeadline" required>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-column">
                <div class="form-section">
                  <h4>Actions</h4>
                  <div class="form-actions">
                    <button type="submit" class="btn-primary" id="saveTask">Create Task</button>
                    <button type="reset" class="btn-secondary">Clear</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <br/>
          
          <!-- Task List -->
          <h3>My Tasks</h3>
          <div class="table-container">
            <table class='data-table' id='tasksTable'>
              <thead>
                <tr>
                  <th>Task ID</th>
                  <th>Title</th>
                  <th>Category</th>
                  <th>Description</th>
                  <th>Deadline</th>
                  <th>Submissions</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB CONTENT: REPORTS -->
      <div class="tab-content" id="reports">
        <div class="card">
          <h2>Reports & Submissions</h2>
          
          <!-- Filters -->
          <div class="compact-form">
            <h3>Filter Reports</h3>
            <div class="form-columns">
              <div class="form-column">
                <div class="form-section">
                  <div class="form-row">
                    <div class="form-group">
                      <label for="reportTaskFilter">Task</label>
                      <select id="reportTaskFilter">
                        <option value="">All Tasks</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="reportStatusFilter">Status</label>
                      <select id="reportStatusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-column">
                <div class="form-section">
                  <div class="form-actions">
                    <button class="btn-secondary" id="applyReportFilters">Apply Filters</button>
                    <button class="btn-secondary" id="clearReportFilters">Clear</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <br/>
          
          <!-- Reports Table -->
          <div class="table-container">
            <table class='data-table' id='reportsTable'>
              <thead>
                <tr>
                  <th>Submission ID</th>
                  <th>Task Title</th>
                  <th>Student Name</th>
                  <th>File Name</th>
                  <th>Submitted Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB CONTENT: ACCOUNT MANAGEMENT -->
      <div class="tab-content" id="account-management">
        <div class="card">
          <h2>Account Management</h2>
          
          <!-- Password Change Form -->
          <div class="compact-form">
            <h3>Change Password</h3>
            <div class="form-columns">
              <div class="form-column">
                <div class="form-section">
                  <h4>Password Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="currentPassword">Current Password</label>
                      <input type="password" id="currentPassword" name="currentPassword" placeholder="Enter current password..." required>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="newPassword">New Password</label>
                      <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password..." required>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="confirmPassword">Confirm New Password</label>
                      <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password..." required>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-column">
                <div class="form-section">
                  <h4>Actions</h4>
                  <div class="form-actions">
                    <button type="submit" class="btn-primary" id="changePassword">Change Password</button>
                    <button type="reset" class="btn-secondary">Clear</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- MODALS -->
  <?php include('modals.php'); ?>

  <script>
    // Simple tab switcher
    const tabLinks = document.querySelectorAll(".tab-link");
    const tabContents = document.querySelectorAll(".tab-content");

    tabLinks.forEach(link => {
      link.addEventListener("click", () => {
        tabLinks.forEach(l => l.classList.remove("active"));
        tabContents.forEach(c => c.classList.remove("active"));

        link.classList.add("active");
        document.getElementById(link.dataset.tab).classList.add("active");
      });
    });
  </script>

  <script src="js/adviser.js"></script>
  <script>
    // Initialize current user data
    window.currentUser = <?= json_encode($currentUser) ?>;
    
    function logout() {
      if (confirm('Are you sure you want to logout?')) {
        $.ajax({
          url: '../auth.php',
          type: 'POST',
          data: { action: 'logout' },
          dataType: 'json',
          success: function(response) {
            window.location.href = '../login.php';
          },
          error: function() {
            // Redirect to login even if logout fails
            window.location.href = '../login.php';
          }
        });
      }
    }
  </script>
  
  <!-- PWA Scripts -->
  <script src="../js/pwa-helper.js"></script>
</body>
</html>