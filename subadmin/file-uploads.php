<?php
session_start(); // Start session first
require_once("../resources/session.php");

// Require subadmin role
$session = SessionManager::getInstance();
$session->requireRole(['Adviser', 'adviser', 'Subadmin', 'subadmin']);

$currentUser = $session->getUserData();
$userId = $currentUser['user_id'] ?? null;

if (!$userId) {
    header("Location: ../index.php");
    exit;
}

// Check if user has permission to view file uploads
require_once("../resources/objects/permission_class.php");
$canView = SubadminPermission::hasPermission($userId, 'file_management', 'view');
$canCreate = SubadminPermission::hasPermission($userId, 'file_management', 'create');
$canEdit = SubadminPermission::hasPermission($userId, 'file_management', 'edit');
$canDelete = SubadminPermission::hasPermission($userId, 'file_management', 'delete');

if (!$canView) {
    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>File Uploads - UASG Subadmin</title>
  
  <!-- PWA Meta Tags -->
  <meta name="description" content="UASG Subadmin - File Upload Management">
  <meta name="theme-color" content="#2196F3">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="UASG Subadmin">
  <meta name="msapplication-TileColor" content="#2196F3">
  
  <!-- PWA Manifest -->
  <!--link rel="manifest" href="../manifest.json"-->
  
  <!-- Favicon and Icons -->
  <link rel="icon" type="image/png" sizes="32x32" href="../resources/icons/icon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../resources/icons/icon-16x16.png">
  <link rel="apple-touch-icon" href="../resources/icons/icon-152x152.png">
  
  <link rel="stylesheet" href="../resources/style.css">
  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
  <script src='../js/datatable.js'></script>
  
  <style>
    .permission-badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
      margin-right: 5px;
      margin-bottom: 5px;
    }
    .badge-view { background: #e3f2fd; color: #1976d2; }
    .badge-create { background: #e8f5e9; color: #388e3c; }
    .badge-edit { background: #fff3e0; color: #f57c00; }
    .badge-delete { background: #ffebee; color: #d32f2f; }
    
    .file-preview {
      max-width: 100%;
      max-height: 300px;
      border-radius: 8px;
      margin-top: 10px;
    }
    
    .upload-area {
      border: 2px dashed #ccc;
      border-radius: 8px;
      padding: 30px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .upload-area:hover {
      border-color: #2196F3;
      background: #f5f5f5;
    }
    
    .upload-area.dragging {
      border-color: #2196F3;
      background: #e3f2fd;
    }
    
    /* Tab content visibility */
    .tab-content {
      display: none;
    }
    
    .tab-content.active {
      display: block;
    }
    
    /* Alert info styling */
    .alert {
      padding: 12px 16px;
      border-radius: 4px;
      margin-bottom: 15px;
    }
    
    .alert-info {
      background: #e3f2fd;
      border-left: 4px solid #2196F3;
      color: #0d47a1;
    }
    
    .alert-info p {
      margin: 5px 0 0 0;
      font-size: 13px;
      color: #1565c0;
    }
    
    /* Form improvements */
    .form-row {
      margin-bottom: 15px;
    }
    
    .form-row label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
      color: #333;
    }
    
    .form-control {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }
    
    .form-control:focus {
      outline: none;
      border-color: #2196F3;
      box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
    }
    
    textarea.form-control {
      resize: vertical;
      min-height: 80px;
    }
  </style>
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
          <button class="tab-link active" data-tab="file-list">File List</button>
          <?php if ($canCreate): ?>
          <button class="tab-link" data-tab="upload-files">Upload Files</button>
          <?php endif; ?>
        </div>

        <!-- TAB CONTENT: FILE LIST -->
        <div class="tab-content active" id="file-list">
          <div class="card">
            <h2>Uploaded Files</h2>
          
          <!-- Filters -->
          <div class="compact-form">
            <h3>Filter Files</h3>
            <div class="form-columns">
              <div class="form-column">
                <div class="form-section">
                  <div class="form-row">
                    <label for="filterCategory">Category:</label>
                    <select id="filterCategory" class="form-control">
                      <option value="">All Categories</option>
                    </select>
                  </div>
                  <div class="form-row">
                    <label for="filterDateFrom">Date From:</label>
                    <input type="date" id="filterDateFrom" class="form-control">
                  </div>
                </div>
              </div>
              <div class="form-column">
                <div class="form-section">
                  <div class="form-row">
                    <label for="filterDateTo">Date To:</label>
                    <input type="date" id="filterDateTo" class="form-control">
                  </div>
                  <div class="form-row">
                    <label>&nbsp;</label>
                    <button type="button" class="btn-primary" onclick="applyFileFilters()">Apply Filters</button>
                    <button type="button" class="btn-secondary" onclick="clearFileFilters()">Clear</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <br/>
          
          <!-- Files Table -->
          <div class="table-container">
            <table class='data-table' id='filesTable'>
              <thead>
                <tr>
                  <th>File ID</th>
                  <th>File Name</th>
                  <th>Category</th>
                  <th>File Type</th>
                  <th>File Size</th>
                  <th>Uploaded By</th>
                  <th>Upload Date</th>
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

      <!-- TAB CONTENT: UPLOAD FILES -->
      <?php if ($canCreate): ?>
      <div class="tab-content" id="upload-files">
        <div class="card">
          <h2>Upload Files</h2>
          
          <!-- Upload Form -->
          <div class="compact-form">
            <form id="uploadForm" enctype="multipart/form-data">
              <div class="form-columns">
                <div class="form-column">
                  <div class="form-section">
                    <h4>File Information</h4>
                    <div class="alert alert-info" style="margin-bottom: 15px; padding: 12px; background: #e3f2fd; border-left: 4px solid #2196F3; border-radius: 4px;">
                      <strong>🤖 AI-Powered Classification</strong>
                      <p style="margin: 5px 0 0 0; font-size: 13px;">Files will be categorized using your <strong>Trained ML Model</strong> for intelligent classification based on document content!</p>
                    </div>
                    <div class="form-row">
                      <label for="uploadTitle">File Title:</label>
                      <input type="text" id="uploadTitle" name="file_title" class="form-control" placeholder="Optional: Custom file title">
                    </div>
                    <div class="form-row">
                      <label for="uploadDescription">Description:</label>
                      <textarea id="uploadDescription" name="description" class="form-control" rows="3" placeholder="Optional: File description"></textarea>
                    </div>
                    <!-- NLP Preview -->
                    <div id="nlpPreview" style="display: none; margin-top: 15px; padding: 15px; background: #f5f5f5; border-radius: 4px; border-left: 4px solid #4CAF50;">
                      <h5 style="margin: 0 0 10px 0; font-size: 14px; color: #333;">📊 Classification Preview</h5>
                      <div style="font-size: 13px; line-height: 1.8;">
                        <div style="margin: 5px 0;">
                          <strong>🏷️ Suggested Category:</strong> 
                          <span id="suggestedCategory" style="color: #2196F3; font-weight: 600;">Will be detected on upload</span>
                        </div>
                        <div style="margin: 5px 0;">
                          <strong>📈 Confidence Score:</strong> 
                          <span id="categoryConfidence" style="color: #4CAF50; font-weight: 600;">TBD</span>
                        </div>
                        <div id="sentimentPreview" style="margin: 5px 0; display: none;">
                          <strong>😊 Sentiment:</strong> 
                          <span id="sentiment" style="font-weight: 600;">-</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-column">
                  <div class="form-section">
                    <h4>File Upload</h4>
                    <div class="form-row">
                      <label>Select File: *</label>
                      <div class="upload-area" id="uploadArea">
                        <input type="file" id="fileInput" name="file" style="display: none;" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.txt,.html,.rtf" required>
                        <p>📁 Click or drag file here to upload</p>
                        <p style="font-size: 12px; color: #666; margin-top: 10px;">Allowed: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, HTML, RTF</p>
                        <p style="font-size: 12px; color: #666;">Max size: 10MB</p>
                      </div>
                      <div id="filePreview" style="display: none;">
                        <p><strong>Selected file:</strong> <span id="fileName"></span></p>
                        <p><strong>Size:</strong> <span id="fileSize"></span></p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="form-actions">
                <button type="submit" class="btn-primary">
                  <i class="fa fa-upload"></i> Upload File
                </button>
                <button type="reset" class="btn-secondary" onclick="resetUploadForm()">
                  <i class="fa fa-times"></i> Clear
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- MODALS -->
  
  <!-- Edit File Modal -->
  <?php if ($canEdit): ?>
  <div id="editFileModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Edit File Information</h2>
        <span class="close" onclick="closeEditModal()">&times;</span>
      </div>
      <div class="modal-body">
        <form id="editFileForm">
          <input type="hidden" id="editFileId" name="file_id">
          
          <div class="form-row">
            <label for="editFileTitle">File Title:</label>
            <input type="text" id="editFileTitle" name="file_title" class="form-control">
          </div>
          
          <div class="form-row">
            <label for="editFileCategory">Category:</label>
            <select id="editFileCategory" name="category_id" class="form-control">
              <option value="">Select Category</option>
            </select>
          </div>
          
          <div class="form-row">
            <label for="editFileDescription">Description:</label>
            <textarea id="editFileDescription" name="description" class="form-control" rows="4"></textarea>
          </div>
          
          <div class="form-actions">
            <button type="submit" class="btn-primary">Save Changes</button>
            <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>
  
  <!-- View File Modal -->
  <div id="viewFileModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>File Details</h2>
        <span class="close" onclick="closeViewModal()">&times;</span>
      </div>
      <div class="modal-body">
        <div id="fileDetails"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-primary" onclick="downloadFile()">Download</button>
        <button type="button" class="btn-secondary" onclick="closeViewModal()">Close</button>
      </div>
    </div>
  </div>
  
  <!-- Delete Confirmation Modal -->
  <?php if ($canDelete): ?>
  <div id="deleteFileModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Confirm Deletion</h2>
        <span class="close" onclick="closeDeleteModal()">&times;</span>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this file?</p>
        <p><strong>File:</strong> <span id="deleteFileName"></span></p>
        
        <div class="form-row">
          <label for="deleteReason">Reason for deletion: *</label>
          <textarea id="deleteReason" class="form-control" rows="3" placeholder="Please provide a reason for deleting this file" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-danger" onclick="confirmDelete()">Delete File</button>
        <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <script>
    // Pass PHP permissions to JavaScript
    window.userPermissions = {
      canView: <?= $canView ? 'true' : 'false' ?>,
      canCreate: <?= $canCreate ? 'true' : 'false' ?>,
      canEdit: <?= $canEdit ? 'true' : 'false' ?>,
      canDelete: <?= $canDelete ? 'true' : 'false' ?>,
      userId: <?= $userId ?>
    };
    
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
            window.location.href = '../login.php';
          }
        });
      }
    }
  </script>

  <script>
    // Tab switcher (matching index.php style)
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

  <script src="js/file-uploads.js"></script>
  
  <!-- Additional Upload Enhancement Script -->
  <script>
  $(document).ready(function() {
      // Enhanced file input handling (matching admin implementation)
      const uploadFileInput = document.getElementById('fileInput');
      
      if (uploadFileInput) {
          // Auto-analyze file on selection
          uploadFileInput.addEventListener('change', function(e) {
              const file = e.target.files[0];
              if (file) {
                  // Display file info
                  document.getElementById('fileName').textContent = file.name;
                  document.getElementById('fileSize').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                  document.getElementById('filePreview').style.display = 'block';
                  
                  // Show NLP preview
                  document.getElementById('nlpPreview').style.display = 'block';
                  document.getElementById('suggestedCategory').textContent = 'Analyzing...';
                  document.getElementById('categoryConfidence').textContent = 'Please wait...';
                  
                  // Trigger NLP analysis
                  analyzeFile(file);
              }
          });
      }
      
      // NLP Analysis function
      function analyzeFile(file) {
          const formData = new FormData();
          formData.append('CALL', 'nlp_analyze');
          formData.append('file', file);
          
          $.ajax({
              url: 'ajax.php',
              type: 'POST',
              data: formData,
              processData: false,
              contentType: false,
              dataType: 'json',
              success: function(result) {
                  if (result.status === 'SUCCESS') {
                      const category = result.category || 'Uncategorized';
                      const score = parseFloat(result.score) || 0;
                      
                      document.getElementById('suggestedCategory').innerHTML = 
                          '<strong style="color: #2196F3;">' + category + '</strong>';
                      
                      let confidenceColor = '#dc3545';
                      let confidenceLabel = 'Low';
                      if (score >= 80) {
                          confidenceColor = '#10b981';
                          confidenceLabel = 'High';
                      } else if (score >= 50) {
                          confidenceColor = '#f59e0b';
                          confidenceLabel = 'Medium';
                      }
                      
                      document.getElementById('categoryConfidence').innerHTML = 
                          '<span style="color: ' + confidenceColor + '; font-weight: 600;">' +
                          score.toFixed(1) + '% (' + confidenceLabel + ')</span>';
                      
                      // Show sentiment if available
                      if (result.sentiment && result.sentiment.length > 0) {
                          const topEmotion = result.sentiment[0];
                          const emotionIcon = topEmotion.label === 'joy' ? '😊' : 
                                             topEmotion.label === 'anger' ? '😠' : 
                                             topEmotion.label === 'sadness' ? '😢' : 
                                             topEmotion.label === 'fear' ? '😨' : 
                                             topEmotion.label === 'love' ? '❤️' : '😮';
                          document.getElementById('sentimentPreview').style.display = 'block';
                          document.getElementById('sentiment').innerHTML = 
                              emotionIcon + ' <strong>' + topEmotion.label + '</strong> (' + (topEmotion.score * 100).toFixed(1) + '%)';
                      }
                      
                      // Store NLP data in form
                      $('#uploadForm').data('nlp-category', category);
                      $('#uploadForm').data('nlp-score', score);
                      $('#uploadForm').data('nlp-analysis', JSON.stringify(result.nlp_analysis || {}));
                  } else {
                      document.getElementById('suggestedCategory').innerHTML = 
                          '<span style="color: #dc3545;">Analysis failed</span>';
                      document.getElementById('categoryConfidence').innerHTML = 
                          '<span style="color: #dc3545;">N/A</span>';
                  }
              },
              error: function(xhr, status, error) {
                  document.getElementById('suggestedCategory').innerHTML = 
                      '<span style="color: #dc3545;">Error</span>';
                  document.getElementById('categoryConfidence').innerHTML = 
                      '<span style="color: #dc3545;">Failed</span>';
                  console.error('NLP Analysis Error:', error);
              }
          });
      }
      
      // Upload area click handler
      const uploadArea = document.getElementById('uploadArea');
      if (uploadArea) {
          uploadArea.addEventListener('click', function() {
              uploadFileInput.click();
          });
      }
  });
  
  // Reset upload form function
  function resetUploadForm() {
      document.getElementById('uploadForm').reset();
      document.getElementById('filePreview').style.display = 'none';
      document.getElementById('nlpPreview').style.display = 'none';
      document.getElementById('sentimentPreview').style.display = 'none';
      document.getElementById('fileName').textContent = '';
      document.getElementById('fileSize').textContent = '';
      document.getElementById('suggestedCategory').textContent = 'Will be detected on upload';
      document.getElementById('categoryConfidence').textContent = 'TBD';
      document.getElementById('sentiment').textContent = '-';
      $('#uploadForm').removeData('nlp-category');
      $('#uploadForm').removeData('nlp-score');
      $('#uploadForm').removeData('nlp-analysis');
  }
  </script>
  
  <!-- Notification Modal -->
  <div id="notificationModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 id="notificationTitle">Notification</h2>
        <span class="close" onclick="closeNotificationModal()">&times;</span>
      </div>
      <div class="modal-body">
        <p id="notificationMessage"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-primary" onclick="closeNotificationModal()">OK</button>
      </div>
    </div>
  </div>

  <script>
    // Notification Modal Functions
    function openNotificationModal(title, message, type = 'info') {
      document.getElementById('notificationTitle').textContent = title;
      document.getElementById('notificationMessage').innerHTML = message;
      
      // Add type-based styling
      const modal = document.getElementById('notificationModal');
      modal.className = 'modal ' + type;
      modal.style.display = 'flex';
    }

    function closeNotificationModal() {
      document.getElementById('notificationModal').style.display = 'none';
    }

    // Global openModal function for compatibility
    window.openModal = function(status, message) {
      const title = status === 'SUCCESS' ? 'Success' : 
                    status === 'ERROR' ? 'Error' : 
                    status === 'WARNING' ? 'Warning' : 'Information';
      const type = status.toLowerCase();
      openNotificationModal(title, message, type);
    };

    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('notificationModal');
      if (event.target == modal) {
        closeNotificationModal();
      }
    };
  </script>
  
  <!-- Include modals -->
  <?php require_once('modals.php'); ?>
  
  <!-- PWA Scripts -->
  <!--script src="../js/pwa-helper.js"></script-->
</body>
</html>
