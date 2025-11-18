<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>File Management</title>
  <link rel="stylesheet" href="../resources/style.css?v=<?= time() ?>">
  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
  <script src='../js/all.js?v=<?= time() ?>'></script>
  <script src='../js/jquery.js?v=<?= time() ?>'></script>
  <script src='../js/datatable.js?v=<?= time() ?>'></script>
</head>
<body>
  <!-- HEADER -->
   <?php require_once("header.php");?>
   <header class="topbar">
    <h1>File Management</h1>
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
      <div class="card">
        <h2>File Management System</h2>

        <!-- TABS -->
        <div class="tabs">
          <button class="tab-link active" data-tab="files">File Library</button>
          <button class="tab-link" data-tab="upload">Upload Files</button>
          <button class="tab-link" data-tab="permissions">Permissions</button>
        </div>

        <!-- TAB CONTENT: FILE LIBRARY -->
        <div class="tab-content active" id="files">
          <div class="compact-form">
            <h3>File Filter & Search</h3>
            <div class="form-columns">
              <div class="form-column">
                <div class="form-section">
                  <h4>Filter Options</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="categoryFilter">Filter by Category</label>
                      <select id="categoryFilter">
                        <option value="">All Categories</option>
                      </select>
                    </div>
                    <div class="form-group form-group-small">
                      <label for="filterBtn">Actions</label>
                      <button type="button" class="btn-primary" id="applyFilter">Apply Filter</button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-column">
                <div class="form-section">
                  <h4>Quick Stats</h4>
                  <div class="form-row">
                    <div class="form-group form-group-small">
                      <label>Total Files</label>
                      <input type="text" id="totalFiles" readonly value="0">
                    </div>
                    <div class="form-group form-group-small">
                      <label>Categories</label>
                      <input type="text" id="totalCategories" readonly value="0">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="table-container">
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
          </div>
        </div>

        <!-- TAB CONTENT: UPLOAD FILES -->
        <div class="tab-content" id="upload">
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

        <!-- TAB CONTENT: PERMISSIONS -->
        <div class="tab-content" id="permissions">
          <div class="compact-form">
            <h3>Set File Category Permissions</h3>
            
            <div class="form-columns">
              <!-- Left Column -->
              <div class="form-column">
                <!-- Permission Settings -->
                <div class="form-section">
                  <h4>Grant Access</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="permissionPosition">Position</label>
                      <select id="permissionPosition" required>
                        <option value="">Select Position</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="permissionCategory">File Category</label>
                      <select id="permissionCategory" required>
                        <option value="">Select Category</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right Column -->
              <div class="form-column">
                <!-- Actions -->
                <div class="form-section">
                  <h4>Permission Actions</h4>
                  <div class="form-actions">
                    <button type="button" class="btn-primary" id="grantPermissionBtn">Grant Access</button>
                    <button type="button" class="btn-secondary" id="revokePermissionBtn">Revoke Access</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="table-container">
            <table class='data-table' id='permissionsTable'>
              <thead>
                <tr>
                  <th>Permission ID</th>
                  <th>Position</th>
                  <th>File Category</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- DELETE FILE MODAL -->
    <div id="deleteFileModal" class="modal">
      <div class="modal-content modal-content-small">
        <span class="modal-close" onclick="closeDeleteFileModal()">&times;</span>
        <h2>Delete File Confirmation</h2>
        
        <div class="compact-form">
          <input type="hidden" id="deleteFileId">
          
          <div class="form-section">
            <h4>Deletion Details</h4>
            <div class="form-row">
              <div class="form-group">
                <label for="deleteFileName">File to Delete</label>
                <input type="text" id="deleteFileName" readonly>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="deleteReason">Reason for Deletion</label>
                <textarea id="deleteReason" rows="4" placeholder="Enter reason for deletion..." required></textarea>
              </div>
            </div>
          </div>
          
          <div class="form-actions">
            <button type="button" class="btn-primary" id="confirmDeleteFile">Confirm Delete</button>
            <button type="button" class="btn-secondary" onclick="closeDeleteFileModal()">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <!-- DOWNLOAD/VIEW FILE MODAL -->
    <div id="viewFileModal" class="modal">
      <div class="modal-content">
        <span class="modal-close" onclick="closeViewFileModal()">&times;</span>
        <h2>File Information</h2>
        
        <div class="compact-form">
          <div class="form-section">
            <h4>File Details</h4>
            <div class="form-row">
              <div class="form-group">
                <label>File Name</label>
                <input type="text" id="viewFileName" readonly>
              </div>
              <div class="form-group">
                <label>Category</label>
                <input type="text" id="viewFileCategory" readonly>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Uploaded By</label>
                <input type="text" id="viewFileUploader" readonly>
              </div>
              <div class="form-group">
                <label>Upload Date</label>
                <input type="text" id="viewFileDate" readonly>
              </div>
            </div>
          </div>
          
          <div class="form-actions">
            <button type="button" class="btn-primary" id="downloadFile">Download File</button>
            <button type="button" class="btn-secondary" onclick="closeViewFileModal()">Close</button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    // Modal functions
    function closeDeleteFileModal() {
      document.getElementById("deleteFileModal").style.display = "none";
    }

    function openDeleteFileModal() {
      document.getElementById("deleteFileModal").style.display = "flex";
    }

    function closeViewFileModal() {
      document.getElementById("viewFileModal").style.display = "none";
    }

    function openViewFileModal() {
      document.getElementById("viewFileModal").style.display = "flex";
    }

    // Clear upload form
    function clearUploadForm() {
      document.getElementById('uploadFile').value = '';
      document.getElementById('fileName').value = '';
      document.getElementById('fileSize').value = '';
      document.getElementById('nlpPreview').style.display = 'none';
    }

    // Tab switcher
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

    // File input change handler
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
  </script>

  <?php require_once("modal.php");?>

  <script>
    $(document).ready(function(){
      let filesTable;
      let permissionsTable;
      const currentUserId = <?= $_SESSION['user_id'] ?? 0 ?>;

      console.log('Current User ID:', currentUserId);

      // Initialize dropdowns
      function loadDropdowns() {
        // Load file categories
        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: { CALL: 5 }, // Get file categories
          dataType: 'json',
          beforeSend: function(xhr) {
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
          },
          success: function(result) {
            console.log('Categories loaded:', result);
            if(result.data) {
              let options = '<option value="">All Categories</option>';
              let permOptions = '<option value="">Select Category</option>';
              
              result.data.forEach(category => {
                options += `<option value="${category.file_category_id}">${category.file_category}</option>`;
                permOptions += `<option value="${category.file_category_id}">${category.file_category}</option>`;
              });
              
              $('#categoryFilter').html(options);
              $('#permissionCategory').html(permOptions);
              $('#totalCategories').val(result.data.length);
            }
          },
          error: function(xhr, error, code) {
            console.error('Categories AJAX Error:', xhr.responseText);
          }
        });

        // Load positions
        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: { CALL: 4 }, // Get positions
          dataType: 'json',
          beforeSend: function(xhr) {
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
          },
          success: function(result) {
            console.log('Positions loaded:', result);
            if(result.data) {
              let options = '<option value="">Select Position</option>';
              result.data.forEach(position => {
                options += `<option value="${position.position_id}">${position.position}</option>`;
              });
              $('#permissionPosition').html(options);
            }
          },
          error: function(xhr, error, code) {
            console.error('Positions AJAX Error:', xhr.responseText);
          }
        });
      }

      // DataTable initialization for files
      function initFilesTable(){
        filesTable = $("#filesTable").DataTable({
          ajax:{
            url:'ajax.php',
            type:'post',
            data:{
              CALL: 14, // Get all files
              USER_ID: currentUserId
            },
            dataType:'json',
            dataSrc: 'data', // Tell DataTables where to find the array
            beforeSend: function(xhr) {
              // Ensure AJAX header is set
              xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            },
            error: function(xhr, error, code) {
              console.error('AJAX Error:', error);
              console.error('Status Code:', code);
              console.error('Response Text:', xhr.responseText);
              alert('Error loading files. Check console for details.');
            }
          },
          responsive: true,
          scroll: '50vh',
          scrollCollapse: true, 
          paging: true,
          columns: [
            { data: "file_upload_id" },
            { data: "file_name" },
            { data: "file_category", defaultContent: "Uncategorized" },
            { data: "mime_type", defaultContent: "Unknown" },
            { 
              data: null,
              render: function(data, type, row) {
                const fname = row.fname || '';
                const lname = row.lname || '';
                return fname && lname ? `${fname} ${lname}` : 'Unknown';
              }
            },
            { 
              data: "datetime_uploaded",
              render: function(data) {
                if(!data) return 'N/A';
                return new Date(data).toLocaleDateString();
              }
            },
            { 
              data: 'file_upload_id',
              orderable: false,
              render: function(data, type, row) {
                return `
                  <button class="btn-secondary viewBtn" data-id="${data}" title="View/Download">
                    <i class="fas fa-eye"></i> View
                  </button>
                  <button class="btn-primary deleteFileBtn" data-id="${data}" data-name="${escapeHtml(row.file_name)}" title="Delete File">
                    <i class="fas fa-trash"></i> Delete
                  </button>
                `;
              }
            }
          ],
          columnDefs: [
            { targets: 0, visible: false, searchable: false }
          ],
          language: {
            emptyTable: "No files uploaded yet",
            loadingRecords: "Loading files..."
          },
          initComplete: function(settings, json) {
            console.log('DataTable initialized with data:', json);
            if(json && json.data) {
              $('#totalFiles').val(json.data.length);
            }
          }
        });
      }
      
      // Helper function to escape HTML
      function escapeHtml(text) {
        if(!text) return '';
        const map = {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
      }

      // DataTable initialization for permissions
      function initPermissionsTable(){
        permissionsTable = $("#permissionsTable").DataTable({
          ajax:{
            url:'ajax.php',
            type:'post',
            data:{
              CALL: 20 // Get file permissions
            },
            dataType:'json',
            dataSrc: 'data', // Tell DataTables where to find the array
            beforeSend: function(xhr) {
              xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            },
            error: function(xhr, error, code) {
              console.error('Permissions AJAX Error:', error);
              console.error('Response Text:', xhr.responseText);
            }
          },
          responsive: true,
          scroll: '50vh',
          scrollCollapse: true, 
          paging: true,
          columns: [
            { data: "file_permission_id" },
            { data: "position", defaultContent: "Unknown" },
            { data: "file_category", defaultContent: "Unknown" },
            { 
              data: null,
              orderable: false,
              render: function(data, type, row) {
                return `
                  <button class="btn-primary revokePermBtn" 
                          data-position="${row.position_id}" 
                          data-category="${row.file_category_id}"
                          title="Revoke Permission">
                    <i class="fas fa-times"></i> Revoke
                  </button>
                `;
              }
            }
          ],
          columnDefs: [
            { targets: 0, visible: false, searchable: false }
          ],
          language: {
            emptyTable: "No permissions set",
            loadingRecords: "Loading permissions..."
          }
        });
      }

      // Initialize tables
      initFilesTable();
      initPermissionsTable();
      loadDropdowns();

      // Apply filter
      $('#applyFilter').click(function() {
        const categoryId = $('#categoryFilter').val();
        
        if(categoryId) {
          filesTable.destroy();
          filesTable = $("#filesTable").DataTable({
            ajax:{
              url:'ajax.php',
              type:'post',
              data:{
                CALL: 15, // Get files by category
                CATEGORY_ID: categoryId,
                USER_ID: currentUserId
              },
              dataType:'json',
              dataSrc: 'data',
              beforeSend: function(xhr) {
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
              },
              error: function(xhr, error, code) {
                console.error('Filter AJAX Error:', error);
                console.error('Response Text:', xhr.responseText);
              }
            },
            responsive: true,
            scroll: '50vh',
            scrollCollapse: true, 
            paging: true,
            columns: [
              { data: "file_upload_id" },
              { data: "file_name" },
              { data: "file_category", defaultContent: "Uncategorized" },
              { data: "mime_type", defaultContent: "Unknown" },
              { 
                data: null,
                render: function(data, type, row) {
                  const fname = row.fname || '';
                  const lname = row.lname || '';
                  return fname && lname ? `${fname} ${lname}` : 'Unknown';
                }
              },
              { 
                data: "datetime_uploaded",
                render: function(data) {
                  if(!data) return 'N/A';
                  return new Date(data).toLocaleDateString();
                }
              },
              { 
                data: 'file_upload_id',
                orderable: false,
                render: function(data, type, row) {
                  return `
                    <button class="btn-secondary viewBtn" data-id="${data}" title="View/Download">
                      <i class="fas fa-eye"></i> View
                    </button>
                    <button class="btn-primary deleteFileBtn" data-id="${data}" data-name="${escapeHtml(row.file_name)}" title="Delete File">
                      <i class="fas fa-trash"></i> Delete
                    </button>
                  `;
                }
              }
            ],
            columnDefs: [
              { targets: 0, visible: false, searchable: false }
            ],
            language: {
              emptyTable: "No files found in this category"
            }
          });
        } else {
          filesTable.ajax.reload();
        }
      });

      // File upload
      $('#uploadBtn').click(function() {
        const fileInput = document.getElementById('uploadFile');
        
        if(!fileInput.files[0]) {
          openModal("ERROR", "Please select a file to upload");
          return;
        }

        const file = fileInput.files[0];
        
        // Create FormData for actual file upload
        const formData = new FormData();
        formData.append('CALL', 16);
        formData.append('file', file);
        formData.append('uploaded_by', currentUserId);
        // No category_id - NLP will auto-categorize

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
            if(result.status === "SUCCESS") {
              let message = result.msg || result.message;
              if(result.nlp_analysis) {
                message += '\n\nAuto-categorized as: ' + (result.nlp_analysis.category || '-') +
                           '\nConfidence: ' + (result.nlp_analysis.confidence ? result.nlp_analysis.confidence.toFixed(1) + '%' : '-');
              }
              openModal(result.status, message);
              clearUploadForm();
              filesTable.ajax.reload();
              $('.tab-link[data-tab="files"]').click();
            } else {
              openModal(result.status, result.message || result.msg);
            }
          },
          error: function(xhr, status, error) {
            console.error('Upload error:', xhr.responseText);
            openModal("ERROR", "Upload failed. Please try again.\n" + xhr.responseText);
          },
          complete: function() {
            $('#uploadBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload File');
          }
        });
      });

      // View file button
      $(document).on("click", ".viewBtn", function() {
        const fileId = $(this).data('id');
        const row = $(this).closest("tr");
        const rowData = filesTable.row(row).data();
        
        if(!rowData) {
          openModal("ERROR", "Could not load file data");
          return;
        }
        
        $('#viewFileName').val(rowData.file_name || '');
        $('#viewFileCategory').val(rowData.file_category || 'Uncategorized');
        const fname = rowData.fname || '';
        const lname = rowData.lname || '';
        $('#viewFileUploader').val(fname && lname ? `${fname} ${lname}` : 'Unknown');
        $('#viewFileDate').val(rowData.datetime_uploaded ? new Date(rowData.datetime_uploaded).toLocaleDateString() : 'N/A');
        
        // Store file ID for download
        $('#downloadFile').data('file-id', fileId);
        
        openViewFileModal();
      });
      
      // Download file button
      $('#downloadFile').click(function() {
        const fileId = $(this).data('file-id');
        if(fileId) {
          // Redirect to download endpoint
          window.location.href = 'ajax.php?CALL=download&file_id=' + fileId;
        }
      });

      // Delete file button
      $(document).on("click", ".deleteFileBtn", function() {
        const fileId = $(this).data('id');
        const fileName = $(this).data('name');
        
        $('#deleteFileId').val(fileId);
        $('#deleteFileName').val(fileName);
        openDeleteFileModal();
      });

      // Confirm delete file
      $('#confirmDeleteFile').click(function() {
        const deleteData = {
          file_id: $('#deleteFileId').val(),
          user_id: currentUserId,
          reason: $('#deleteReason').val()
        };

        if(!deleteData.reason.trim()) {
          openModal("ERROR", "Please provide a reason for deletion");
          return;
        }

        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 17, // Delete file
            DATA: deleteData
          },
          dataType: 'json',
          success: function(result) {
            openModal(result.status, result.message || result.msg);
            if(result.status === "SUCCESS") {
              closeDeleteFileModal();
              $('#deleteReason').val('');
              filesTable.ajax.reload();
            }
          },
          error: function(xhr, status, error) {
            console.error('Delete error:', xhr.responseText);
            openModal("ERROR", "Failed to delete file. Please try again.");
          }
        });
      });

      // Grant permission
      $('#grantPermissionBtn').click(function() {
        const positionId = $('#permissionPosition').val();
        const categoryId = $('#permissionCategory').val();
        
        if(!positionId || !categoryId) {
          openModal("ERROR", "Please select both position and category");
          return;
        }

        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 18, // Set permission
            DATA: {
              position_id: positionId,
              file_category_id: categoryId
            }
          },
          dataType: 'json',
          success: function(result) {
            openModal(result.status, result.message || result.msg);
            if(result.status === "SUCCESS") {
              $('#permissionPosition').val('');
              $('#permissionCategory').val('');
              permissionsTable.ajax.reload();
            }
          },
          error: function(xhr, status, error) {
            console.error('Grant error:', xhr.responseText);
            openModal("ERROR", "Failed to grant permission. Please try again.");
          }
        });
      });

      // Revoke permission from table
      $(document).on("click", ".revokePermBtn", function() {
        const positionId = $(this).data('position');
        const categoryId = $(this).data('category');
        
        if(!confirm('Are you sure you want to revoke this permission?')) {
          return;
        }
        
        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 19, // Remove permission
            DATA: {
              position_id: positionId,
              file_category_id: categoryId
            }
          },
          dataType: 'json',
          success: function(result) {
            openModal(result.status, result.message || result.msg);
            if(result.status === "SUCCESS") {
              permissionsTable.ajax.reload();
            }
          },
          error: function(xhr, status, error) {
            console.error('Revoke error:', xhr.responseText);
            openModal("ERROR", "Failed to revoke permission. Please try again.");
          }
        });
      });

      // Revoke permission button
      $('#revokePermissionBtn').click(function() {
        const positionId = $('#permissionPosition').val();
        const categoryId = $('#permissionCategory').val();
        
        if(!positionId || !categoryId) {
          openModal("ERROR", "Please select both position and category");
          return;
        }
        
        if(!confirm('Are you sure you want to revoke this permission?')) {
          return;
        }

        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 19, // Remove permission
            DATA: {
              position_id: positionId,
              file_category_id: categoryId
            }
          },
          dataType: 'json',
          success: function(result) {
            openModal(result.status, result.message || result.msg);
            if(result.status === "SUCCESS") {
              $('#permissionPosition').val('');
              $('#permissionCategory').val('');
              permissionsTable.ajax.reload();
            }
          },
          error: function(xhr, status, error) {
            console.error('Revoke error:', xhr.responseText);
            openModal("ERROR", "Failed to revoke permission. Please try again.");
          }
        });
      });
    });
  </script>
</body>
</html>