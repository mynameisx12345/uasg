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
          <button class="tab-btn active" data-tab="dashboard">Dashboard</button>
          <button class="tab-btn" data-tab="file-upload">File Upload</button>
          <button class="tab-btn" data-tab="my-files">My Files</button>
          <button class="tab-btn" data-tab="pending-tasks">Pending Tasks <span id="pendingTasksBadge" style="display:none;" class="badge"></span></button>
          <button class="tab-btn" data-tab="task-submissions">Task Submissions</button>
          <button class="tab-btn" data-tab="account-management">Account Management</button>
      <!-- TAB CONTENT: PENDING TASKS -->
        </div>
      <div class="tab-content" id="pending-tasks">
        <div class="card">
          <h2>Pending Tasks</h2>
          <div class="table-container">
            <table id="pendingTasksTable" class="display" style="width:100%">
              <thead>
                <tr>
                  <th>Task</th>
                  <th>Category</th>
                  <th>Deadline</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
  <!-- Comply Task Modal -->
  <div id="complyTaskModal" class="modal">
    <div class="modal-content modal-content-large">
      <h2>📋 Comply with Task</h2>
      <form id="complyTaskForm" enctype="multipart/form-data">
        <input type="hidden" id="complyTaskId" name="task_id" />
        
        <div class="form-group">
          <label for="complyFile">📎 Select File to Upload</label>
          <input type="file" id="complyFile" name="file" accept=".pdf,.doc,.docx,.txt,.xls,.xlsx,.csv,.ppt,.pptx" required />
          <small style="display: block; margin-top: 5px; color: #666;">
            Supported formats: PDF, Word, Excel, PowerPoint, Text (Max: 50MB)
          </small>
        </div>
        
        <div class="alert alert-info" style="margin-top: 15px; padding: 12px; background: #e3f2fd; border-left: 4px solid #2196F3; border-radius: 4px;">
          <strong>🤖 AI-Powered Classification</strong>
          <p style="margin: 5px 0 0 0; font-size: 13px;">Your file will be analyzed using your <strong>Trained ML Model</strong> for intelligent classification based on document content!</p>
        </div>
        
        <div id="complyNlpPreview" style="display:none; margin-top: 15px; padding: 15px; background: #f5f5f5; border-radius: 4px; border-left: 4px solid #4CAF50;">
          <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #333;">📊 Classification Preview</h4>
          <div style="font-size: 13px; line-height: 1.8;">
            <div style="margin: 5px 0;">
              <strong>🏷️ Suggested Category:</strong> 
              <span id="complySuggestedCategory" style="color: #2196F3; font-weight: 600;">-</span>
            </div>
            <div style="margin: 5px 0;">
              <strong>📈 Confidence Score:</strong> 
              <span id="complyCategoryConfidence" style="color: #4CAF50; font-weight: 600;">-</span>
            </div>
            <div id="complySentimentPreview" style="margin: 5px 0; display: none;">
              <strong>😊 Sentiment:</strong> 
              <span id="complySentiment" style="font-weight: 600;">-</span>
            </div>
          </div>
        </div>
        
        <div class="form-actions" style="margin-top: 25px; display: flex; gap: 10px; justify-content: flex-end;">
          <button type="button" class="btn-secondary" onclick="closeModal('complyTaskModal')" style="padding: 10px 20px;">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button type="button" class="btn-primary btn-gold" id="complyUploadBtn" style="padding: 10px 20px;">
            <i class="fas fa-upload"></i> Submit Task
          </button>
        </div>
      </form>
    </div>
  </div>
        <!-- TAB CONTENT: DASHBOARD -->
        <div class="tab-content active" id="dashboard">
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
          <div class="compact-form">
            <h3>Upload New File</h3>
            
            <div class="form-columns">
              <!-- Left Column -->
              <div class="form-column">
                <!-- File Information -->
                <div class="form-section">
                  <h4>File Details</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="uploadFile">Select File</label>
                      <input type="file" id="uploadFile" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.txt,.html,.rtf" required>
                    </div>
                  </div>
                  <div class="alert alert-info" style="margin-top: 10px; padding: 12px; background: #e3f2fd; border-left: 4px solid #2196F3; border-radius: 4px;">
                    <strong>🤖 AI-Powered Classification</strong>
                    <p style="margin: 5px 0 0 0; font-size: 13px;">Files will be categorized using your <strong>Trained ML Model</strong> for intelligent classification based on document content!</p>
                  </div>
                </div>
              </div>

              <!-- Right Column -->
              <div class="form-column">
                <!-- Upload Information -->
                <div class="form-section">
                  <h4>Upload Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="fileName">File Name</label>
                      <input type="text" id="fileName" placeholder="Auto-filled from selected file" readonly>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="fileSize">File Size</label>
                      <input type="text" id="fileSize" placeholder="Auto-detected" readonly>
                    </div>
                  </div>
                  <!-- NLP Preview Section -->
                  <div id="nlpPreview" style="display: none; margin-top: 15px; padding: 12px; background: #f5f5f5; border-radius: 4px;">
                    <h5 style="margin: 0 0 10px 0; font-size: 14px; color: #555;">📊 Classification Preview</h5>
                    <div style="font-size: 13px;">
                      <div style="margin: 5px 0;">
                        <strong>Suggested Category:</strong> 
                        <span id="suggestedCategory" style="color: #2196F3;">Analyzing...</span>
                      </div>
                      <div style="margin: 5px 0;">
                        <strong>Confidence:</strong> 
                        <span id="categoryConfidence">-</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-actions">
              <button type="button" class="btn-primary" id="uploadBtn">Upload File</button>
              <button type="button" class="btn-secondary" onclick="clearUploadForm()">Clear</button>
            </div>
          </div>
        </div>
      <!-- Loading Modal -->
      <div id="loadingModal" class="modal" style="display:none;">
        <div class="modal-content modal-content-small">
          <h2>Processing...</h2>
          <div style="text-align: center; margin: 20px 0;">
            <i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #2196F3;"></i>
          </div>
          <p style="text-align: center;">Please wait while we analyze your file.</p>
        </div>
      </div>
    </div>

      <!-- TAB CONTENT: MY FILES -->
      <div class="tab-content" id="my-files">
        <div class="card">
          <h2>My Uploaded Files</h2>
          
          <!-- File Filters -->
          <!--div class="compact-form">
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
          </div-->
          
          <br/>
          
          <div class="table-container">
            <table class='data-table' id='myFilesTable'>
              <thead>
                <tr>
                  <th>File Name</th>
                  <th>Category</th>
                  <th>Type</th>
                  <th>Size</th>
                  <th>Uploaded</th>
                  <th>Date Uploaded</th>
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
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
            <script>
            $(document).ready(function() {
              // Initialize Task Submissions Table
              if (!$.fn.DataTable.isDataTable('#taskSubmissionsTable')) {
                window.taskSubmissionsTable = $('#taskSubmissionsTable').DataTable({
                  ajax: {
                    url: 'ajax.php',
                    type: 'POST',
                    data: { CALL: 10 },
                    dataSrc: function(json) {
                      return json.data || [];
                    }
                  },
                  columns: [
                    { data: 'task_title' },
                    { data: 'task_category' },
                    { data: 'task_deadline', render: function(data) { return data ? new Date(data).toLocaleDateString() : 'N/A'; } },
                    { data: 'check_status', render: function(data) { return `<span class="status-badge status-${data}">${data || 'Not submitted'}</span>`; } },
                    { data: 'file_name', render: function(data) { return data || 'No file submitted'; } },
                    //{ data: 'grade', render: function(data) { return data || '-'; } },
                    { data: null, render: function(data, type, row) {
                        let actions = '';
                        if (!row.task_submission_id || row.check_status === 'rejected') {
                          actions += `<button class="btn-sm btn-primary" onclick="openSubmitTaskModal(${row.task_id})">Submit/Resubmit</button>`;
                        }
                        if (row.file_upload_id) {
                          actions += ` <button class="btn-sm btn-secondary" onclick="downloadFile(${row.file_upload_id})">Download</button>`;
                        }
                        return actions || 'No actions available';
                      }, orderable: false }
                  ],
                  pageLength: 10,
                  order: [[2, 'asc']]
                });
              }
            });
            </script>
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
  <?php require_once('modals.php'); ?>

  <script>
    
    // GitHub-style tab switcher
    const tabBtns = document.querySelectorAll(".tab-btn");
    const tabPanes = document.querySelectorAll(".tab-content");

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
  $(document).ready(function() {
      // --- Update Header Notification Count for Pending Tasks ---
      window.updatePendingTasksNotification = function() {
        $.ajax({
          url: 'ajax.php',
          type: 'POST',
          data: { CALL: 9 },
          dataType: 'json',
          success: function(json) {
            let count = 0;
            if(json && json.data && Array.isArray(json.data)) {
              count = json.data.filter(t => t.task_status === 'active').length;
            } else if(Array.isArray(json)) {
              count = json.filter(t => t.task_status === 'active').length;
            }
            const notificationBadge = document.getElementById('headerNotificationCount');
            if(count > 0) {
              notificationBadge.textContent = count;
              notificationBadge.style.display = 'inline-block';
            } else {
              notificationBadge.textContent = '';
              notificationBadge.style.display = 'none';
            }
          }
        });
      }
      
      // Update notification count on page load
      updatePendingTasksNotification();
      
      // Refresh notification count every 30 seconds
      setInterval(updatePendingTasksNotification, 30000);
      
      // --- Pending Tasks Badge ---
      function updatePendingTasksBadge() {
        $.ajax({
          url: 'ajax.php',
          type: 'POST',
          data: { CALL: 9 },
          dataType: 'json',
          success: function(json) {
            let count = 0;
            if(json && json.data && Array.isArray(json.data)) {
              count = json.data.filter(t => t.task_status === 'active').length;
            } else if(Array.isArray(json)) {
              count = json.filter(t => t.task_status === 'active').length;
            }
            const badge = document.getElementById('pendingTasksBadge');
            if(count > 0) {
              badge.textContent = count;
              badge.style.display = 'inline-block';
            } else {
              badge.textContent = '';
              badge.style.display = 'none';
            }
          }
        });
      }
      // Update badge on page load and when switching tabs
      updatePendingTasksBadge();
      document.querySelector("[data-tab='pending-tasks']").addEventListener('click', function() {
        updatePendingTasksBadge();
      });
    // --- Pending Tasks Table ---
    let pendingTasksTable;
    function loadPendingTasks() {
      pendingTasksTable = $('#pendingTasksTable').DataTable({
        ajax: {
          url: 'ajax.php',
          type: 'POST',
          data: { CALL: 9 },
          dataSrc: function(json) { return json.data || []; }
        },
        destroy: true,
        columns: [
          { data: 'task_title' },
          { data: 'task_category' },
          { data: 'task_deadline' },
          { data: 'task_status', render: function(data) { return data === 'active' ? 'Pending' : data; } },
          { data: null, render: function(data, type, row) {
              if(row.task_status === 'active') {
                return `<button class='btn btn-primary btn-sm' onclick='openComplyTaskModal(${row.task_id})'>Comply</button>`;
              } else {
                return '-';
              }
            }
          }
        ],
        language: { emptyTable: 'No pending tasks found' }
      });
    }
    // Load pending tasks on tab show
      document.querySelector("[data-tab='pending-tasks']").addEventListener('click', function() {
        loadPendingTasks();
        updatePendingTasksBadge();
        updatePendingTasksNotification();
      });
    // --- Comply Task Modal Logic ---
    window.openComplyTaskModal = function(taskId) {
      document.getElementById('complyTaskId').value = taskId;
      document.getElementById('complyFile').value = '';
      document.getElementById('complyNlpPreview').style.display = 'none';
      document.getElementById('complySuggestedCategory').textContent = '-';
      document.getElementById('complyCategoryConfidence').textContent = '-';
      document.getElementById('complySentimentPreview').style.display = 'none';
      document.getElementById('complySentiment').textContent = '-';
      openModal('complyTaskModal');
    };

    document.getElementById('complyFile').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if(file) {
        // Step 1: Request NLP analysis
        const formData = new FormData();
        formData.append('CALL', 'nlp_analyze');
        formData.append('file', file);
        openLoadModal();
        $.ajax({
          url: 'ajax.php',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function(result) {
            if(result.status === 'SUCCESS') {
              document.getElementById('complyNlpPreview').style.display = 'block';
              document.getElementById('complySuggestedCategory').textContent = result.category || 'Uncategorized';
              document.getElementById('complyCategoryConfidence').textContent = (result.score || '0') + '%';
              
              // Show sentiment if available
              if(result.sentiment && result.sentiment.length > 0) {
                const topEmotion = result.sentiment[0];
                const emotionIcon = topEmotion.label === 'joy' ? '😊' : 
                                   topEmotion.label === 'anger' ? '😠' : 
                                   topEmotion.label === 'sadness' ? '😢' : 
                                   topEmotion.label === 'fear' ? '😨' : 
                                   topEmotion.label === 'love' ? '❤️' : '😮';
                document.getElementById('complySentimentPreview').style.display = 'block';
                document.getElementById('complySentiment').textContent = emotionIcon + ' ' + topEmotion.label + ' (' + (topEmotion.score * 100).toFixed(1) + '%)';
              }
              
              // Store for upload
              document.getElementById('complyFile').dataset.suggestedCategory = result.category || 'Uncategorized';
              document.getElementById('complyFile').dataset.categoryScore = result.score || '0';
              document.getElementById('complyFile').dataset.nlpAnalysis = JSON.stringify(result.nlp_analysis || {});
            } else {
              openNotificationModal('NLP analysis failed: ' + (result.msg || 'Unknown error'));
            }
          },
          error: function(xhr) {
            openNotificationModal('NLP analysis failed. Please try again.');
          },complete:function(){
            closeLoadModal();
          }
        });
      }
    });

    document.getElementById('complyUploadBtn').addEventListener('click', function() {
      const fileInput = document.getElementById('complyFile');
      const file = fileInput.files[0];
      if(!file) {
        openNotificationModal('Please select a file to upload.');
        return;
      }
      const taskId = document.getElementById('complyTaskId').value;
      // Step A: Validate file against task requirements before uploading
      const validationForm = new FormData();
      validationForm.append('CALL', 'validate_task_file');
      validationForm.append('task_id', taskId);
      validationForm.append('file', file);

      openLoadModal();
      document.getElementById('complyUploadBtn').disabled = true;
      document.getElementById('complyUploadBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Validating...';

      $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: validationForm,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(validationResult) {
          // Close load modal but keep button disabled until flow completes
          closeLoadModal();

          if(!validationResult || validationResult.success === false) {
            const err = validationResult?.error || validationResult?.msg || 'Validation failed. Upload prevented.';
            openNotificationModal('Validation error: ' + err, 'error');
            document.getElementById('complyUploadBtn').disabled = false;
            document.getElementById('complyUploadBtn').innerHTML = '<i class="fas fa-upload"></i> Submit Task';
            return;
          }

          if(!validationResult.is_valid) {
            // Show reason(s) and prevent upload
            let msg = 'This file does not comply with task requirements.';
            if(validationResult.recommendation && validationResult.recommendation.message) {
              msg += '\n\nRecommendation: ' + validationResult.recommendation.message;
            }
            if(validationResult.validation_details) {
              msg += '\n\nDetails: ' + JSON.stringify(validationResult.validation_details);
            }
            openNotificationModal(msg, 'warning');
            document.getElementById('complyUploadBtn').disabled = false;
            document.getElementById('complyUploadBtn').innerHTML = '<i class="fas fa-upload"></i> Submit Task';
            return;
          }

          // Passed validation — proceed to final upload (server will re-validate as well)
          const formData = new FormData();
          formData.append('CALL', 11); // Submit task file
          formData.append('task_id', taskId);
          formData.append('file', file);
          formData.append('category_tag', fileInput.dataset.suggestedCategory || 'Uncategorized');
          formData.append('category_score', fileInput.dataset.categoryScore || '0');
          formData.append('nlp_analysis', fileInput.dataset.nlpAnalysis || '');

          // Show uploading state
          openLoadModal();
          document.getElementById('complyUploadBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

          $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(result) {
              if(result.status === 'SUCCESS' || result.success) {
                let notificationMsg = 'Task file uploaded successfully!';
                if(result.category_tag || result.nlp_result) {
                  const category = result.category_tag || result.nlp_result?.category_tag || 'Uncategorized';
                  const score = result.category_score || result.nlp_result?.category_score || 0;
                  notificationMsg += '\n\n🤖 Auto-categorized as: ' + category;
                  notificationMsg += '\n📊 Confidence: ' + score + '%';
                }
                openNotificationModal(notificationMsg, 'success');
                closeModal('complyTaskModal');
                loadPendingTasks();
                updatePendingTasksBadge();
                updatePendingTasksNotification();
              } else {
                openNotificationModal('Upload failed: ' + (result.msg || 'Unknown error'), 'error');
              }
            },
            error: function(xhr) {
              openNotificationModal('Upload failed. Please try again.', 'error');
            },
            complete: function() {
              closeLoadModal();
              document.getElementById('complyUploadBtn').disabled = false;
              document.getElementById('complyUploadBtn').innerHTML = '<i class="fas fa-upload"></i> Submit Task';
            }
          });
        },
        error: function(xhr) {
          closeLoadModal();
          openNotificationModal('Task submission failed. File Category does not comply with the task.', 'error');
          document.getElementById('complyUploadBtn').disabled = false;
          document.getElementById('complyUploadBtn').innerHTML = '<i class="fas fa-upload"></i> Submit Task';
        }
      });
    });

    // Initialize current user data
    window.currentUser = <?= json_encode($currentUser) ?>;
    let filesTable;
    // Initialize dashboard on page load
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof refreshDashboard === 'function') {
        refreshDashboard();
      }
      if (typeof initializeDashboardTables === 'function') {
        initializeDashboardTables();
      }
    });
      // --- File Upload Logic (Admin-style) ---
      let nlpSuggestedCategory = null;
      let nlpCategoryConfidence = null;
      let nlpAnalysisData = null;

      function openLoadModal(){
        document.getElementById('loadingModal').style.display = 'flex';
      }
      function closeLoadModal(){
        document.getElementById('loadingModal').style.display = 'none';
      }

      function clearUploadForm() {
        document.getElementById('uploadFile').value = '';
        document.getElementById('fileName').value = '';
        document.getElementById('fileSize').value = '';
        document.getElementById('nlpPreview').style.display = 'none';
      }

      document.getElementById('uploadFile').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if(file) {
          document.getElementById('fileName').value = file.name;
          document.getElementById('fileSize').value = (file.size / 1024 / 1024).toFixed(2) + ' MB';
          
          // Show NLP preview placeholder
          document.getElementById('nlpPreview').style.display = 'block';
          document.getElementById('suggestedCategory').textContent = 'Will be detected on upload';
          document.getElementById('categoryConfidence').textContent = 'TBD';
        }
      });

      $('#uploadFile').on("change",function() {
        const fileInput = document.getElementById('uploadFile');
        if(!fileInput.files[0]) {
          openNotificationModal("ERROR! Please select a file to upload");
          return;
        }
        const file = fileInput.files[0];
        // Step 1: Request NLP analysis
        const formData = new FormData();
        formData.append('CALL', 'nlp_analyze');
        formData.append('file', file);
        openLoadModal();
        $.ajax({
          url: 'ajax.php',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          dataType: 'json',
          beforeSend: function() {
            $('#uploadBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Analyzing...');
          },
          success: function(result) {
            if(result.status === "SUCCESS") {
              nlpSuggestedCategory = result.category;
              nlpCategoryConfidence = result.score;
              nlpAnalysisData = result.nlp_analysis;
              
              // Show NLP preview
              $('#nlpPreview').show();
              $('#suggestedCategory').text(nlpSuggestedCategory || 'Uncategorized');
              $('#categoryConfidence').text(nlpCategoryConfidence ? nlpCategoryConfidence + '%' : 'N/A');
              
              // Display additional NLP data if available
              if (result.keywords && result.keywords.length > 0) {
                $('#suggestedCategory').text(nlpSuggestedCategory + ' 🔑 ' + result.keywords.slice(0, 3).join(', '));
              }
              
              // Show sentiment if available
              if (result.sentiment && result.sentiment.length > 0) {
                const topEmotion = result.sentiment[0];
                const emotionIcon = topEmotion.label === 'joy' ? '😊' : 
                                   topEmotion.label === 'anger' ? '😠' : 
                                   topEmotion.label === 'sadness' ? '😢' : 
                                   topEmotion.label === 'fear' ? '😨' : 
                                   topEmotion.label === 'love' ? '❤️' : '😮';
                $('#categoryConfidence').text(nlpCategoryConfidence + '% ' + emotionIcon + ' ' + topEmotion.label);
              }
              
            } else {
              openNotificationModal("ERROR! NLP analysis failed. Please try again.");
            }
          },
          error: function(xhr, status, error) {
            console.error('NLP error:', xhr.responseText);
            openNotificationModal("ERROR! NLP analysis failed. Please try again.\n" + xhr.responseText);
          },
          complete: function() {
            closeLoadModal();
            $('#uploadBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload File');
          }
        });
      });

      const currentUserId = <?= $_SESSION['user_id'] ?? 0 ?>;

      if ($.fn.DataTable.isDataTable('#myFilesTable')) {
          $('#myFilesTable').DataTable().destroy();
      }

      function initFilesTable() {
        filesTable = $("#myFilesTable").DataTable({
            ajax: {
                url: "ajax.php",
                type: "POST",
                data: {
                    CALL: 4, // or 15 if you want filter applied
                    USER_ID: currentUserId
                },
                dataType: "json",
                dataSrc: "data",
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                }
            },
            responsive: true,
            scrollY: "50vh",
            scrollCollapse: true,
            paging: true,
            columns: [
                { data: "file_upload_id" },
                { data: "file_name" },
                { data: "file_category" },
                { data: "mime_type" },
                { 
                    data: null,
                    render: function(data) {
                        return data.fname && data.lname ? data.fname + " " + data.lname : "Unknown";
                    }
                },
                { 
                    data: "datetime_uploaded",
                    render: d => d ? new Date(d).toLocaleDateString() : "N/A"
                },
                {
                    data: "file_upload_id",
                    render: function(id, type, row) {
                        return `
                            <button class="btn-secondary viewBtn" data-id="${id}">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn-primary deleteFileBtn" 
                                    data-id="${id}" 
                                    data-name="${row.file_name}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        `;
                    }
                },
                { data: "category_tag"},
                { data: "category_score" },
            ],
            columnDefs: [{ targets: 0, visible: false }],
            language: {
                emptyTable: "No files found"
            },
            // Update total files after every AJAX load
            initComplete: function(settings, json) {
                $('#totalFiles').val(json.data ? json.data.length : 0);
            }
        });

        // Also update total files on every reload (filter apply, refresh)
        filesTable.on('xhr', function(e, settings, json, xhr) {
            $('#totalFiles').val(json.data ? json.data.length : 0);
        });
      }
      initFilesTable();

      // View File Button Handler
      $(document).on('click', '.viewBtn', function() {
        const fileId = $(this).data('id');
        const row = filesTable.row($(this).closest('tr')).data();
        
        if(row && row.file_path) {
          // Open file in new tab for viewing
          const fileUrl = '../' + row.file_path;
          window.open(fileUrl, '_blank');
        } else {
          openNotificationModal('File path not found. Cannot view file.');
        }
      });

      // Delete File Button Handler
      $(document).on('click', '.deleteFileBtn', function() {
        const fileId = $(this).data('id');
        const fileName = $(this).data('name');
        
        // Open delete confirmation modal
        $('#deleteFileName').text(fileName);
        $('#confirmDeleteBtn').data('file-id', fileId);
        openModal('deleteFileModal');
      });

      // Confirm Delete Button Handler
      $(document).on('click', '#confirmDeleteBtn', function() {
        const fileId = $(this).data('file-id');
        
        // Send delete request
        $.ajax({
          url: 'ajax.php',
          type: 'POST',
          data: {
            CALL: 7,
            file_id: fileId
          },
          dataType: 'json',
          beforeSend: function() {
            closeModal('deleteFileModal');
            openLoadModal();
          },
          success: function(result) {
            if(result.status === 'SUCCESS' || result.success) {
              openNotificationModal('File deleted successfully!');
              filesTable.ajax.reload(); // Reload table data
              updatePendingTasksNotification(); // Update notification count if needed
            } else {
              openNotificationModal('Failed to delete file: ' + (result.msg || result.message || 'Unknown error'));
            }
          },
          error: function(xhr, status, error) {
            console.error('Delete error:', xhr.responseText);
            openNotificationModal('Error deleting file. Please try again.');
          },
          complete: function() {
            closeLoadModal();
          }
        });
      });

      $('#uploadBtn').click(function() {
          const fileInput = document.getElementById('uploadFile');
          
          if(!fileInput.files[0]) {
            openNotificationModal("ERROR! Please select a file to upload");
            return;
          }

          const file = fileInput.files[0];
          
          // Create FormData for actual file upload
          const formData = new FormData();
          formData.append('CALL', 3);
          formData.append('file', file);
          formData.append('uploaded_by', currentUserId);
          
          // ⭐ REUSE CACHED NLP RESULTS - No duplicate API call!
          if(nlpSuggestedCategory && nlpCategoryConfidence !== null && nlpAnalysisData) {
            formData.append('category_tag', nlpSuggestedCategory);
            formData.append('category_score', nlpCategoryConfidence);
            formData.append('nlp_analysis', JSON.stringify(nlpAnalysisData));
          }
          
          openLoadModal();
          $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
              $('#uploadBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
            },
            success: function(result) {
              let message = result.msg || result.message;
              if(result.success === true) {
                let notificationMsg = 'File uploaded successfully!';
                
                // Display NLP results
                if(result.category_tag || result.nlp_result) {
                  const category = result.category_tag || result.nlp_result?.category_tag || 'Uncategorized';
                  const score = result.category_score || result.nlp_result?.category_score || 0;
                  
                  notificationMsg += '\n\n🤖 Auto-categorized as: ' + category;
                  notificationMsg += '\n📊 Confidence: ' + score + '%';
                  
                  // Add sentiment if available
                  if(result.nlp_result?.sentiment && result.nlp_result.sentiment.length > 0) {
                    const topEmotion = result.nlp_result.sentiment[0];
                    const emotionIcon = topEmotion.label === 'joy' ? '😊' : 
                                       topEmotion.label === 'anger' ? '😠' : 
                                       topEmotion.label === 'sadness' ? '😢' : 
                                       topEmotion.label === 'fear' ? '😨' : 
                                       topEmotion.label === 'love' ? '❤️' : '😮';
                    notificationMsg += '\n' + emotionIcon + ' Sentiment: ' + topEmotion.label + ' (' + (topEmotion.score * 100).toFixed(1) + '%)';
                  }
                }
                
                openNotificationModal("SUCCESS! " + notificationMsg);
                $('.tab-link[data-tab="files"]').click();
              } else {
                openNotificationModal("FAILED! Failed to upload file. Please double check file size and file extension");
              }
            },
            error: function(xhr, status, error) {
              console.error('Upload error:', xhr.responseText);
              openNotificationModal("ERROR! Upload failed. Please try again.\n" + xhr.responseText);
            },
            complete: function() {
              closeLoadModal();
              filesTable.ajax.reload();
              $('#uploadBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload File');
            }
          });
        });

      // Confirmation dialog for upload
      function confirmUploadWithCategory(file, suggestedCategory, confidence, analysisData) {
        // Use a simple prompt for now; can be replaced with a modal for better UX
        let userCategory = prompt(
          `Suggested Category: ${suggestedCategory || 'Uncategorized'}\nConfidence: ${confidence || 'N/A'}%\n\nEnter category to use (or leave blank to accept suggestion):`,
          suggestedCategory || ''
        );
        if(userCategory === null) {
          // User cancelled
          return;
        }
        // Step 2: Finalize upload with selected category
        const formData = new FormData();
        formData.append('CALL', 3); // Actual upload
        formData.append('file', file);
        formData.append('uploaded_by', currentUserId);
        formData.append('category_tag', userCategory || suggestedCategory || 'Uncategorized');
        formData.append('category_score', confidence || '0');
        formData.append('nlp_analysis', JSON.stringify(analysisData));
        $.ajax({
          url: 'ajax.php',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          dataType: 'json',
          beforeSend: function() {
            $('#uploadBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
          },
          success: function(result) {
            let message = result.msg || result.message;
            if(result.success == true) {
              
              if(result.nlp_result) {
                message += '\n\nAuto-categorized as: ' + (result.nlp_analysis.suggested_category || '-') +
                           '\nConfidence: ' + (result.nlp_result['confidence'] || 'N/A') + '%';
              }
              openNotificationModal("SUCCESS! Successfully uploaded file!");
              //clearUploadForm();
              filesTable.ajax.reload();
              $('.tab-link[data-tab="files"]').click();
            } else {
              openNotificationModal("FAILED! " + message);
            }
          },
          error: function(xhr, status, error) {
            console.error('Upload error:', xhr.responseText);
            openNotificationModal("ERROR! Upload failed. Please try again.\n" + xhr.responseText);
          },
          complete: function() {
            $('#uploadBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload File');
          }
        });
      }

      // Clear form
      window.resetUploadForm = function() {
        document.getElementById('uploadFile').value = '';
        document.getElementById('fileDescription').value = '';
        document.getElementById('detectedCategory').textContent = 'Select a file to analyze';
        document.getElementById('categoryConfidence').textContent = '-';
        document.getElementById('detectedFileType').textContent = '-';
        document.getElementById('suggestedKeywords').textContent = '';
        nlpSuggestedCategory = null;
        nlpCategoryConfidence = null;
        nlpAnalysisData = null;
      }
  });
  </script>
  
  <!-- PWA Scripts -->
  <!--script src="../js/pwa-helper.js"></script-->
</body>
</html>