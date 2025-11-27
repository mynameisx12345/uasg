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
                      <input type="file" id="uploadFile" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.html,.rtf" required>
                    </div>
                  </div>
                  <div class="alert alert-info" style="margin-top: 10px; padding: 12px; background: #e3f2fd; border-left: 4px solid #2196F3; border-radius: 4px;">
                    <strong>🤖 Auto-Categorization Enabled</strong>
                    <p style="margin: 5px 0 0 0; font-size: 13px;">Files will be automatically categorized using Google Cloud NLP based on their content and keywords.</p>
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
          
          <!-- Files Table -->
          <!--div class="table-container">
            <table class='data-table' id='filesTable'>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>File Name</th>
                  <th>Category</th>
                  <th>Type</th>
                  <th>Uploaded By</th>
                  <th>Date Uploaded</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div-->
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
              nlpCategoryConfidence = result.nlp_analysis['confidence'];
              nlpAnalysisData = result.nlp_analysis;
              // Show NLP preview and ask for confirmation
              $('#nlpPreview').show();
              $('#suggestedCategory').text(nlpSuggestedCategory || 'Uncategorized');
              $('#categoryConfidence').text(nlpCategoryConfidence ? nlpCategoryConfidence + '%' : 'N/A');
              // Show confirmation dialog
              //confirmUploadWithCategory(file, nlpSuggestedCategory, nlpCategoryConfidence, nlpAnalysisData);
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
          // No category_id - NLP will auto-categorize
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
                
                if(result.nlp_result) {
                  message += '\n\nAuto-categorized as: ' + (result.nlp_result.suggested_category_name || '-') +
                            '\nConfidence: ' + (result.nlp_result['confidence'] || 'N/A') + '%';
                }
                openNotificationModal("SUCCESS! Successfully uploaded file!");
                //clearUploadForm();
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

      // Upload button click: only allow after NLP analysis
      /*document.getElementById('uploadFileBtn').addEventListener('click', function(e) {
        e.preventDefault();
        const fileInput = document.getElementById('uploadFile');
        if(!fileInput.files[0]) {
          alert('Please select a file to upload');
          return;
        }
        if(!nlpSuggestedCategory) {
          alert('Please wait for category analysis to complete');
          return;
        }
        const file = fileInput.files[0];
        const description = document.getElementById('fileDescription').value;
        const taskId = document.getElementById('taskAssociation').value;
        const manualCategory = document.getElementById('manualCategory').value;
        openLoadModal();
        const formData = new FormData();
        formData.append('CALL', 3); // Actual upload
        formData.append('file', file);
        formData.append('uploaded_by', window.currentUser.user_id);
        formData.append('category_tag', manualCategory || nlpSuggestedCategory || 'Uncategorized');
        formData.append('category_score', nlpCategoryConfidence || '0');
        formData.append('nlp_analysis', JSON.stringify(nlpAnalysisData));
        formData.append('description', description);
        formData.append('task_id', taskId);
        $.ajax({
          url: 'ajax.php',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function(result) {
            let message = result.msg || result.message;
            if(result.success === true) {
              alert('File uploaded successfully!');
              document.getElementById('uploadFile').value = '';
              document.getElementById('fileDescription').value = '';
              document.getElementById('detectedCategory').textContent = 'Select a file to analyze';
              document.getElementById('categoryConfidence').textContent = '-';
              document.getElementById('detectedFileType').textContent = '-';
              document.getElementById('suggestedKeywords').textContent = '';
              // TODO: Reload files table if present
            } else {
              alert('Upload failed: ' + message);
            }
          },
          error: function(xhr, status, error) {
            alert('Upload failed. Please try again.');
          },
          complete: function() {
            closeLoadModal();
          }
        });
      });*/
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