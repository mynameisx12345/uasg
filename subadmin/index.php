<?php
session_start();
require_once("../resources/session.php");
require_once("../resources/objects/permission_class.php");

// Require subadmin role
$session = SessionManager::getInstance();
$session->requireRole(['Adviser', 'adviser', 'Subadmin', 'subadmin']);

$currentUser = $session->getUserData();
$userId = $currentUser['user_id'] ?? null;

if (!$userId) {
    header("Location: ../index.php");
    exit;
}

// Get user permissions
$permissions = [
    'task_management' => [
        'view' => (bool)SubadminPermission::hasPermission($userId, 'task_management', 'view'),
        'create' => (bool)SubadminPermission::hasPermission($userId, 'task_management', 'create'),
        'edit' => (bool)SubadminPermission::hasPermission($userId, 'task_management', 'edit'),
        'delete' => (bool)SubadminPermission::hasPermission($userId, 'task_management', 'delete')
    ],
    'file_management' => [
        'view' => (bool)SubadminPermission::hasPermission($userId, 'file_management', 'view'),
        'create' => (bool)SubadminPermission::hasPermission($userId, 'file_management', 'create'),
        'edit' => (bool)SubadminPermission::hasPermission($userId, 'file_management', 'edit'),
        'delete' => (bool)SubadminPermission::hasPermission($userId, 'file_management', 'delete')
    ],
    'user_management' => [
        'view' => (bool)SubadminPermission::hasPermission($userId, 'user_management', 'view'),
        'create' => (bool)SubadminPermission::hasPermission($userId, 'user_management', 'create'),
        'edit' => (bool)SubadminPermission::hasPermission($userId, 'user_management', 'edit'),
        'delete' => (bool)SubadminPermission::hasPermission($userId, 'user_management', 'delete')
    ],
    'entry_module' => [
        'view' => (bool)SubadminPermission::hasPermission($userId, 'entry_module', 'view'),
        'create' => (bool)SubadminPermission::hasPermission($userId, 'entry_module', 'create'),
        'edit' => (bool)SubadminPermission::hasPermission($userId, 'entry_module', 'edit'),
        'delete' => (bool)SubadminPermission::hasPermission($userId, 'entry_module', 'delete')
    ]
];

// Debug: Ensure permissions structure is correct
if (!is_array($permissions)) {
    error_log("CRITICAL: Permissions is not an array: " . print_r($permissions, true));
    $permissions = [];
}

// Helper function to safely check permissions
function checkPermission($permissions, $module, $action = 'view') {
    return isset($permissions[$module][$action]) && $permissions[$module][$action] === true;
}
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
  <link rel="stylesheet" href="../resources/theme-overrides.css">
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
          <button class="tab-link active" data-tab="dashboard">Dashboard</button>
          <?php if (checkPermission($permissions, 'file_management', 'view')): ?>
          <button class="tab-link" data-tab="file-management">File Management</button>
          <?php endif; ?>
          <?php if (checkPermission($permissions, 'file_management', 'view')): ?>
          <button class="tab-link" data-tab="nlp-search">NLP Search</button>
          <?php endif; ?>
          <?php if (checkPermission($permissions, 'task_management', 'view')): ?>
          <button class="tab-link" data-tab="reports">Reports</button>
          <?php endif; ?>
          <button class="tab-link" data-tab="account-management">Account Settings</button>
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

      <!-- TAB CONTENT: FILE MANAGEMENT -->
      <?php if (checkPermission($permissions, 'file_management', 'view')): ?>
      <div class="tab-content" id="file-management">
        <div class="card">
          <h2>File Management</h2>
          
          <!-- File Upload Section -->
          <?php if (checkPermission($permissions, 'file_management', 'create')): ?>
          <div class="compact-form">
            <h3>Upload New File</h3>
            
            <div class="form-columns">
              <div class="form-column">
                <div class="form-section">
                  <h4>File Information</h4>
                  <div class="alert alert-info" style="margin-bottom: 15px; padding: 12px; background: #e3f2fd; border-left: 4px solid #2196F3; border-radius: 4px;">
                    <strong>🤖 Auto-Categorization Enabled</strong>
                    <p style="margin: 5px 0 0 0; font-size: 13px;">Files will be automatically categorized using Google Cloud NLP based on their content.</p>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="uploadFile">Select File</label>
                      <input type="file" id="uploadFile" name="file" accept=".pdf,.doc,.docx,.txt" required>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="fileName">File Name</label>
                      <input type="text" id="fileName" readonly>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="fileSize">File Size</label>
                      <input type="text" id="fileSize" readonly>
                    </div>
                  </div>
                  
                  <!-- NLP Preview -->
                  <div id="nlpPreview" style="display: none; margin-top: 15px; padding: 12px; background: #f5f5f5; border-radius: 4px;">
                    <h5 style="margin: 0 0 10px 0; font-size: 14px; color: #555;">📊 NLP Analysis Preview</h5>
                    <div style="font-size: 13px;">
                      <div style="margin: 5px 0;">
                        <strong>Detected Category:</strong> 
                        <span id="detectedCategory" style="color: #2196F3;">Analyzing...</span>
                      </div>
                      <div style="margin: 5px 0;">
                        <strong>Confidence Score:</strong> 
                        <span id="categoryConfidence">-</span>
                      </div>
                      <div style="margin: 5px 0;">
                        <strong>File Type:</strong> 
                        <span id="detectedFileType">-</span>
                      </div>
                      <div style="margin: 5px 0;">
                        <strong>Keywords:</strong> 
                        <span id="suggestedKeywords" style="font-size: 12px;">-</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="form-column">
                <div class="form-section">
                  <h4>Actions</h4>
                  <div class="form-actions">
                    <button type="button" class="btn-primary" id="uploadBtn">Upload File</button>
                    <button type="button" class="btn-secondary" onclick="resetUploadForm()">Clear</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>
          
          <br/>
          
          <!-- Files Table -->
          <h3>All Uploaded Files</h3>
          <div class="table-container">
            <table class='data-table' id='allFilesTable'>
              <thead>
                <tr>
                  <th>File ID</th>
                  <th>File Name</th>
                  <th>Category</th>
                  <th>NLP Category</th>
                  <th>Score</th>
                  <th>Uploaded By</th>
                  <th>Upload Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- TAB CONTENT: NLP SEARCH -->
      <?php if (checkPermission($permissions, 'file_management', 'view')): ?>
      <div class="tab-content" id="nlp-search">
        <div class="card">
          <h2>🔍 NLP-Powered File Search</h2>
          <p>Search files using natural language and keyword matching powered by Google Cloud NLP</p>
          
          <div class="compact-form">
            <div class="form-columns">
              <div class="form-column">
                <div class="form-section">
                  <div class="form-row">
                    <div class="form-group">
                      <label for="nlpSearchQuery">Search Query</label>
                      <input type="text" id="nlpSearchQuery" placeholder="Enter keywords to search (e.g., resolution, minutes, meeting)" style="width: 100%; padding: 10px;">
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-column">
                <div class="form-section">
                  <div class="form-actions">
                    <button type="button" class="btn-primary" id="nlpSearchBtn">🔍 Search</button>
                    <button type="button" class="btn-secondary" onclick="clearNlpSearch()">Clear</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <br/>
          
          <div class="table-container">
            <table class='data-table' id='nlpSearchTable'>
              <thead>
                <tr>
                  <th>File Name</th>
                  <th>Category</th>
                  <th>NLP Category</th>
                  <th>Score</th>
                  <th>Uploader</th>
                  <th>Upload Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- TAB CONTENT: REPORTS -->
      <?php if (checkPermission($permissions, 'task_management', 'view')): ?>
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
      <?php endif; ?>

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
  <?php require_once('modals.php'); ?>

  <script>
    // GitHub-style tab switcher
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
    // Initialize current user data and permissions
    window.currentUser = <?= json_encode($currentUser) ?>;
    window.userPermissions = <?= json_encode($permissions) ?>;
    
    $(document).ready(function() {
      <?php if (checkPermission($permissions, 'file_management', 'view')): ?>
      // Initialize All Files Table
      const allFilesTable = $('#allFilesTable').DataTable({
        ajax: {
          url: 'ajax.php',
          type: 'POST',
          data: { CALL: 'get_all_files' },
          dataSrc: 'data'
        },
        columns: [
          { data: 'file_upload_id' },
          { data: 'file_name' },
          { data: 'file_category' },
          { data: 'category_tag' },
          { data: 'category_score' },
          { data: 'uploader_name' },
          { data: 'datetime_uploaded' },
          {
            data: null,
            render: function(data, type, row) {
              let actions = `
                <div class="dropdown-container">
                  <button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
                  <div class="dropdown-menu">
                    <button class="dropdown-item view" onclick="viewFileDetails(${row.file_upload_id})">View Details</button>
                    <button class="dropdown-item view" onclick="downloadFile('${row.file_path}', '${row.file_name}')">Download</button>
                    <?php if (checkPermission($permissions, 'file_management', 'edit')): ?>
                    <button class="dropdown-item edit" onclick="editFile(${row.file_upload_id})">Edit</button>
                    <?php endif; ?>
                    <?php if (checkPermission($permissions, 'file_management', 'delete')): ?>
                    <button class="dropdown-item delete" onclick="deleteFile(${row.file_upload_id}, '${row.file_name}')">Delete</button>
                    <?php endif; ?>
                  </div>
                </div>
              `;
              return actions;
            }
          }
        ]
      });
      
      // Initialize NLP Search Table
      let nlpSearchTable = $('#nlpSearchTable').DataTable({
        data: [],
        columns: [
          { data: 'file_name' },
          { data: 'file_category' },
          { data: 'category_tag' },
          { data: 'category_score' },
          { data: 'uploader_name' },
          { data: 'datetime_uploaded' },
          {
            data: null,
            render: function(data, type, row) {
              const fileId = row.file_upload_id || '';
              const filePath = row.file_path || '';
              return `
                <div class="dropdown-container">
                  <button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
                  <div class="dropdown-menu">
                    <button class="dropdown-item view" onclick="showFileDetails(${JSON.stringify(row).replace(/"/g, '&quot;')})">View Details</button>
                    <button class="dropdown-item view" onclick="downloadNlpFile('${fileId}', '${filePath}')">Download</button>
                  </div>
                </div>
              `;
            }
          }
        ]
      });
      
      // NLP Search Handler
      $('#nlpSearchBtn').click(function() {
        const query = $('#nlpSearchQuery').val().trim();
        if (!query) {
          showNotification('Please enter a search query', 'warning');
          return;
        }
        
        $.ajax({
          url: 'ajax.php',
          type: 'POST',
          data: {
            CALL: 'nlp_search_files',
            search_query: query
          },
          dataType: 'json',
          success: function(response) {
            if (response.data) {
              nlpSearchTable.clear().rows.add(response.data).draw();
              showNotification(`Found ${response.data.length} matching files`, 'success');
            } else {
              nlpSearchTable.clear().draw();
              showNotification('No files found matching your query', 'info');
            }
          },
          error: function() {
            showNotification('Error performing search', 'error');
          }
        });
      });
      
      // Clear NLP Search
      window.clearNlpSearch = function() {
        $('#nlpSearchQuery').val('');
        nlpSearchTable.clear().draw();
      };
      
      // File Download Functions
      window.downloadFile = function(filePath, fileName) {
        window.location.href = `../downloads.php?file=${encodeURIComponent(filePath)}`;
      };
      
      window.downloadNlpFile = function(fileId, filePath) {
        if (fileId) {
          window.location.href = `ajax.php?CALL=download_file&file_id=${fileId}`;
        } else {
          window.location.href = `ajax.php?CALL=download_by_path&file_path=${encodeURIComponent(filePath)}`;
        }
      };
      
      // Show File Details
      window.showFileDetails = function(fileData) {
        const detailsHtml = `
          <div style="padding: 15px;">
            <table style="width: 100%; border-collapse: collapse;">
              <tr><td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>File Name:</strong></td><td style="padding: 8px; border-bottom: 1px solid #ddd;">${fileData.file_name || 'N/A'}</td></tr>
              <tr><td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Category:</strong></td><td style="padding: 8px; border-bottom: 1px solid #ddd;">${fileData.file_category || 'N/A'}</td></tr>
              <tr><td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>NLP Category:</strong></td><td style="padding: 8px; border-bottom: 1px solid #ddd;">${fileData.category_tag || 'N/A'}</td></tr>
              <tr><td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Confidence Score:</strong></td><td style="padding: 8px; border-bottom: 1px solid #ddd;">${fileData.category_score || 'N/A'}%</td></tr>
              <tr><td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>File Size:</strong></td><td style="padding: 8px; border-bottom: 1px solid #ddd;">${formatFileSize(fileData.file_size)}</td></tr>
              <tr><td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Uploaded By:</strong></td><td style="padding: 8px; border-bottom: 1px solid #ddd;">${fileData.uploader_name || 'Unknown'}</td></tr>
              <tr><td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Upload Date:</strong></td><td style="padding: 8px; border-bottom: 1px solid #ddd;">${fileData.datetime_uploaded || 'N/A'}</td></tr>
            </table>
          </div>
        `;
        
        // Create and show modal
        const modal = $('<div class="modal" style="display: flex;"><div class="modal-content"><div class="modal-header"><h2>File Details</h2><button onclick="$(this).closest(\'.modal\').remove()" style="background:none;border:none;font-size:24px;cursor:pointer;">&times;</button></div>' + detailsHtml + '</div></div>');
        $('body').append(modal);
      };
      
      function formatFileSize(bytes) {
        if (!bytes) return 'N/A';
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
      }
      <?php endif; ?>
    });
    
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