<?php
session_start();
require_once("../resources/session.php");

// Require member role
$session = SessionManager::getInstance();
$session->requireRole(['student']);

$currentUser = $session->getUserData();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Member Dashboard - UASG</title>
  
  <!-- PWA Meta Tags -->
  <meta name="description" content="UASG Member Dashboard - File Management and Task Submissions">
  <meta name="theme-color" content="#2196F3">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="UASG Member">
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
  <!-- MAIN LAYOUT -->
  <div class="dashboard-container">
    <!-- SIDEBAR -->
    <?php require_once("sidebar.php");?>

    <!-- MAIN CONTENT -->
    <div class="main-content">
      <!-- HEADER -->
      <?php require_once("header.php");?>
      
      <!-- CONTENT -->
      <div class="dashboard-content">
        <!-- NAVIGATION TABS -->
        <div class="tab-nav">
          <button class="tab-btn active" data-tab="dashboard">Dashboard</button>
          <button class="tab-btn" data-tab="file-upload">File Upload</button>
          <button class="tab-btn" data-tab="my-files">My Files</button>
          <button class="tab-btn" data-tab="task-submissions">Task Submissions</button>
          <button class="tab-btn" data-tab="account-management">Account Management</button>
        </div>

        <!-- TAB CONTENT: DASHBOARD -->
        <div class="tab-pane active" id="dashboard">
        <!-- Overview Cards -->
        <div class="card-grid">
          <div class="card stat-card">
            <h2>My Files</h2>
            <p class="stat" id="totalFiles">0</p>
            <span class="sub">Total Uploaded</span>
          </div>
          <div class="card stat-card">
            <h2>Active Tasks</h2>
            <p class="stat" id="activeTasks">0</p>
            <span class="sub">Pending Submission</span>
          </div>
          <div class="card stat-card">
            <h2>Completed</h2>
            <p class="stat" id="completedTasks">0</p>
            <span class="sub">Submitted Tasks</span>
          </div>
          <div class="card stat-card">
            <h2>Categories</h2>
            <p class="stat" id="categoryCount">0</p>
            <span class="sub">File Categories Used</span>
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
                  <th>Action</th>
                  <th>File/Task</th>
                  <th>Category</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB CONTENT: FILE UPLOAD -->
      <div class="tab-content" id="file-upload">
        <div class="card">
          <h2>Intelligent File Upload</h2>
          
          <!-- File Upload Form -->
          <div class="compact-form">
            <h3>Upload File</h3>
            <div class="form-columns">
              <div class="form-column">
                <div class="form-section">
                  <h4>File Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="uploadFile">Select File</label>
                      <input type="file" id="uploadFile" name="uploadFile" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar" required>
                      <small>Supported: PDF, DOC, XLS, PPT, Images, Archives</small>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="fileDescription">Description (Optional)</label>
                      <textarea id="fileDescription" name="fileDescription" rows="3" placeholder="Brief description of the file content..."></textarea>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="taskAssociation">Associate with Task (Optional)</label>
                      <select id="taskAssociation" name="taskAssociation">
                        <option value="">No Task Association</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-column">
                <div class="form-section">
                  <h4>Auto-Categorization Preview</h4>
                  <div id="categoryPreview" class="category-preview">
                    <div class="preview-item">
                      <strong>Detected Category:</strong>
                      <span id="detectedCategory">Select a file to analyze</span>
                    </div>
                    <div class="preview-item">
                      <strong>Confidence:</strong>
                      <span id="categoryConfidence">-</span>
                    </div>
                    <div class="preview-item">
                      <strong>File Type:</strong>
                      <span id="detectedFileType">-</span>
                    </div>
                    <div class="preview-item">
                      <strong>Suggested Keywords:</strong>
                      <div id="suggestedKeywords" class="keyword-tags"></div>
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <label for="manualCategory">Manual Category Override</label>
                    <select id="manualCategory" name="manualCategory">
                      <option value="">Use Auto-Detection</option>
                    </select>
                  </div>
                  
                  <div class="form-actions">
                    <button type="submit" class="btn-primary" id="uploadFileBtn">Upload File</button>
                    <button type="reset" class="btn-secondary" onclick="resetUploadForm()">Clear</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Upload Progress -->
          <div id="uploadProgress" class="upload-progress" style="display: none;">
            <div class="progress-header">
              <h4>Uploading File...</h4>
              <span id="progressPercent">0%</span>
            </div>
            <div class="progress-bar">
              <div id="progressFill" class="progress-fill"></div>
            </div>
            <div class="progress-status">
              <span id="progressStatus">Preparing upload...</span>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB CONTENT: MY FILES -->
      <div class="tab-content" id="my-files">
        <div class="card">
          <h2>My Uploaded Files</h2>
          
          <!-- File Filters -->
          <div class="compact-form">
            <h3>Filter Files</h3>
            <div class="form-columns">
              <div class="form-column">
                <div class="form-section">
                  <div class="form-row">
                    <div class="form-group">
                      <label for="filesCategoryFilter">Category</label>
                      <select id="filesCategoryFilter">
                        <option value="">All Categories</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="filesTypeFilter">File Type</label>
                      <select id="filesTypeFilter">
                        <option value="">All Types</option>
                        <option value="application/pdf">PDF</option>
                        <option value="application/msword">Word Document</option>
                        <option value="application/vnd.ms-excel">Excel</option>
                        <option value="application/vnd.ms-powerpoint">PowerPoint</option>
                        <option value="image/">Images</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-column">
                <div class="form-section">
                  <div class="form-actions">
                    <button class="btn-secondary" id="applyFileFilters">Apply Filters</button>
                    <button class="btn-secondary" id="clearFileFilters">Clear</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <br/>
          
          <!-- Files Table -->
          <div class="table-container">
            <table class='data-table' id='myFilesTable'>
              <thead>
                <tr>
                  <th>File Name</th>
                  <th>Category</th>
                  <th>Type</th>
                  <th>Size</th>
                  <th>Uploaded</th>
                  <th>Task Association</th>
                  <th>Actions</th>
                    <th>NLP Category</th>
                    <th>NLP Score</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB CONTENT: TASK SUBMISSIONS -->
      <div class="tab-content" id="task-submissions">
        <div class="card">
          <h2>Task Submissions</h2>
          
          <!-- Task Filters -->
          <div class="compact-form">
            <h3>Filter Tasks</h3>
            <div class="form-columns">
              <div class="form-column">
                <div class="form-section">
                  <div class="form-row">
                    <div class="form-group">
                      <label for="taskStatusFilter">Status</label>
                      <select id="taskStatusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending Submission</option>
                        <option value="submitted">Submitted</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="taskCategoryFilter">Category</label>
                      <select id="taskCategoryFilter">
                        <option value="">All Categories</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-column">
                <div class="form-section">
                  <div class="form-actions">
                    <button class="btn-secondary" id="applyTaskFilters">Apply Filters</button>
                    <button class="btn-secondary" id="clearTaskFilters">Clear</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <br/>
          
          <!-- Tasks Table -->
          <div class="table-container">
            <table class='data-table' id='taskSubmissionsTable'>
              <thead>
                <tr>
                  <th>Task Title</th>
                  <th>Category</th>
                  <th>Deadline</th>
                  <th>Submission Status</th>
                  <th>Submitted File</th>
                  <th>Grade/Feedback</th>
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
    </div>
  </div>

  <!-- MODALS -->
  <?php include('modals.php'); ?>

  <script>
    // GitHub-style tab switcher
    const tabBtns = document.querySelectorAll(".tab-btn");
    const tabPanes = document.querySelectorAll(".tab-pane");

    tabBtns.forEach(btn => {
      btn.addEventListener("click", () => {
        tabBtns.forEach(b => b.classList.remove("active"));
        tabPanes.forEach(p => p.classList.remove("active"));

        btn.classList.add("active");
        document.getElementById(btn.dataset.tab).classList.add("active");
      });
    });
  </script>

  <script src="js/member.js"></script>
  <script>
    // Initialize current user data
    window.currentUser = <?= json_encode($currentUser) ?>;
    
    // Initialize dashboard on page load
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof refreshDashboard === 'function') {
        refreshDashboard();
      }
      if (typeof initializeDashboardTables === 'function') {
        initializeDashboardTables();
      }
    });
  </script>
  
  <!-- PWA Scripts -->
  <script src="../js/pwa-helper.js"></script>
</body>
</html>