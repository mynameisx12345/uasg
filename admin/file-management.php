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
    <div id='loadingModal' class='modal'>
      <div class='modal-content modal-content-small'>
        <h2>Processing...</h2>
        <div style='text-align: center; margin: 20px 0;'>
          <i class='fas fa-spinner fa-spin' style='font-size: 48px; color: #2196F3;'></i>
        </div>
        <p style='text-align: center;'>Please wait while we process your request.</p>
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

    function openLoadModal(){
      document.getElementById('loadingModal').style.display = 'flex';
    }

    function closeLoadModal(){
      document.getElementById('loadingModal').style.display = 'none';
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

      let nlpSuggestedCategory = null;
      let nlpCategoryConfidence = null;
      let nlpAnalysisData = null;

      $('#uploadFile').on("change",function() {
        const fileInput = document.getElementById('uploadFile');
        if(!fileInput.files[0]) {
          showNotification("Please select a file to upload", 'warning', 'No File Selected');
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
              showNotification("NLP analysis failed: " + (result.message || result.msg || 'Unknown error'), 'error', 'Analysis Failed');
            }
          },
          error: function(xhr, status, error) {
            console.error('NLP error:', xhr.responseText);
            showNotification("NLP analysis failed. Please try again.", 'error', 'Analysis Error');
          },
          complete: function() {
            closeLoadModal();
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
        formData.append('CALL', 16); // Actual upload
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
            if(result.success === true) {
              let notificationMsg = 'File uploaded successfully';
              if(result.nlp_result) {
                notificationMsg += '\nAuto-categorized as: ' + (result.nlp_analysis.suggested_category || '-') +
                           '\nConfidence: ' + (result.nlp_result['confidence'] || 'N/A') + '%';
              }
              showNotification(notificationMsg, 'success', 'Upload Complete');
              filesTable.ajax.reload();
              $('.tab-link[data-tab="files"]').click();
            } else {
              showNotification(message || 'Upload failed', 'error', 'Upload Failed');
            }
          },
          error: function(xhr, status, error) {
            console.error('Upload error:', xhr.responseText);
            showNotification("Upload failed. Please try again.", 'error', 'Upload Error');
          },
          complete: function() {
            $('#uploadBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload File');
          }
        });
      }

      //Datatable initialization for filesTable

      function initFilesTable() {
        filesTable = $("#filesTable").DataTable({
            ajax: {
                url: "ajax.php",
                type: "POST",
                data: {
                    CALL: 14 // Admin sees all files
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
                    data: null,
                    render: function(data, type, row) {
                        return `
                            <div style="display: flex; gap: 5px; justify-content: center;">
                                <button class="btn-secondary viewBtn" 
                                        data-id="${row.file_upload_id}"
                                        data-name="${row.file_name}"
                                        data-category="${row.file_category || 'N/A'}"
                                        data-type="${row.mime_type}"
                                        data-uploaded="${row.datetime_uploaded}"
                                        data-uploader="${row.fname && row.lname ? row.fname + ' ' + row.lname : 'Unknown'}"
                                        title="View Details">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn-success downloadBtn" 
                                        data-id="${row.file_upload_id}"
                                        data-name="${row.file_name}"
                                        title="Download File">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button class="btn-danger deleteFileBtn" 
                                        data-id="${row.file_upload_id}" 
                                        data-name="${row.file_name}"
                                        title="Delete File">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        `;
                    }
                }
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

      //escape html
      function escapeHtml(text) {
          if (!text) return '';
          return text
              .replace(/&/g, "&amp;")
              .replace(/</g, "&lt;")
              .replace(/>/g, "&gt;")
              .replace(/"/g, "&quot;")
              .replace(/'/g, "&#039;");
      }

      // Apply filter
      $('#applyFilter').click(function() {
        let categoryId = $('#categoryFilter').val();
        categoryId = categoryId ? categoryId.trim() : '';

        //if(categoryId !== '') {
            // Filter by selected category
            filesTable.destroy();
            filesTable = $("#filesTable").DataTable({
                ajax: {
                    url: 'ajax.php',
                    type: 'post',
                    data: {
                        CALL: 15,
                        CATEGORY_ID: categoryId,
                        USER_ID: currentUserId
                    },
                    dataType: 'json',
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
                scrollY: '50vh',
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
                            return data ? new Date(data).toLocaleDateString() : 'N/A';
                        }
                    },
                    { 
                        data: 'file_upload_id',
                        orderable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="dropdown-container">
                                    <button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
                                    <div class="dropdown-menu">
                                        <button class="dropdown-item view viewBtn" data-id="${data}">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="dropdown-item delete deleteFileBtn" data-id="${data}" data-name="${escapeHtml(row.file_name)}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
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
        /*} else {
            // No category selected → reload all
            filesTable.ajax.reload();
        }*/
    });

      // File upload
      $('#uploadBtn').click(function() {
        const fileInput = document.getElementById('uploadFile');
        
        if(!fileInput.files[0]) {
          showNotification("Please select a file to upload", 'warning', 'No File Selected');
          return;
        }

        const file = fileInput.files[0];
        
        // Create FormData for actual file upload
        const formData = new FormData();
        formData.append('CALL', 16);
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
              let notificationMsg = 'File uploaded successfully';
              if(result.nlp_result) {
                notificationMsg += '\nAuto-categorized as: ' + (result.nlp_result.suggested_category_name || '-') +
                           '\nConfidence: ' + (result.nlp_result['confidence'] || 'N/A') + '%';
              }
              showNotification(notificationMsg, 'success', 'Upload Complete');
              $('.tab-link[data-tab="files"]').click();
            } else {
              showNotification(message || 'Upload failed', 'error', 'Upload Failed');
            }
          },
          error: function(xhr, status, error) {
            console.error('Upload error:', xhr.responseText);
            showNotification("Upload failed. Please try again.", 'error', 'Upload Error');
          },
          complete: function() {
            closeLoadModal();
            filesTable.ajax.reload();
            $('#uploadBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload File');
          }
        });
      });

      // View file button
      $(document).on("click", ".viewBtn", function() {
        const fileId = $(this).data('id');
        const fileName = $(this).data('name');
        const category = $(this).data('category');
        const type = $(this).data('type');
        const uploaded = $(this).data('uploaded');
        const uploader = $(this).data('uploader');
        
        // Populate modal with file details
        $('#viewFileName').val(fileName || 'N/A');
        $('#viewFileCategory').val(category || 'Uncategorized');
        $('#viewFileUploader').val(uploader || 'Unknown');
        
        // Format the date
        if(uploaded) {
          const date = new Date(uploaded);
          $('#viewFileDate').val(date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          }));
        } else {
          $('#viewFileDate').val('N/A');
        }
        
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

      // Download file button from table
      $(document).on("click", ".downloadBtn", function() {
        const fileId = $(this).data('id');
        const fileName = $(this).data('name');
        
        if(fileId) {
          // Show download notification
          showNotification(`Downloading file: ${fileName}`, 'info', 'Download Started');
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
          showNotification("Please provide a reason for deletion", 'warning', 'Missing Information');
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
            if(result.status === "SUCCESS") {
              showNotification(result.message || result.msg || 'File deleted successfully', 'success', 'File Deleted');
              closeDeleteFileModal();
              $('#deleteReason').val('');
              filesTable.ajax.reload();
            } else {
              showNotification(result.message || result.msg || 'Failed to delete file', 'error', 'Delete Failed');
            }
          },
          error: function(xhr, status, error) {
            console.error('Delete error:', xhr.responseText);
            showNotification("Failed to delete file. Please try again.", 'error', 'Error');
          }
        });
      });

      // Grant permission
      $('#grantPermissionBtn').click(function() {
        const positionId = $('#permissionPosition').val();
        const categoryId = $('#permissionCategory').val();
        
        if(!positionId || !categoryId) {
          showNotification("Please select both position and category", 'warning', 'Missing Information');
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
            if(result.status === "SUCCESS") {
              showNotification(result.message || result.msg || 'Permission granted successfully', 'success', 'Permission Granted');
              $('#permissionPosition').val('');
              $('#permissionCategory').val('');
              permissionsTable.ajax.reload();
            } else {
              showNotification(result.message || result.msg || 'Failed to grant permission', 'error', 'Grant Failed');
            }
          },
          error: function(xhr, status, error) {
            console.error('Grant error:', xhr.responseText);
            showNotification("Failed to grant permission. Please try again.", 'error', 'Error');
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
            if(result.status === "SUCCESS") {
              showNotification(result.message || result.msg || 'Permission revoked successfully', 'success', 'Permission Revoked');
              permissionsTable.ajax.reload();
            } else {
              showNotification(result.message || result.msg || 'Failed to revoke permission', 'error', 'Revoke Failed');
            }
          },
          error: function(xhr, status, error) {
            console.error('Revoke error:', xhr.responseText);
            showNotification("Failed to revoke permission. Please try again.", 'error', 'Error');
          }
        });
      });

      // Revoke permission button
      $('#revokePermissionBtn').click(function() {
        const positionId = $('#permissionPosition').val();
        const categoryId = $('#permissionCategory').val();
        
        if(!positionId || !categoryId) {
          showNotification("Please select both position and category", 'warning', 'Missing Information');
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
            if(result.status === "SUCCESS") {
              showNotification(result.message || result.msg || 'Permission revoked successfully', 'success', 'Permission Revoked');
              $('#permissionPosition').val('');
              $('#permissionCategory').val('');
              permissionsTable.ajax.reload();
            } else {
              showNotification(result.message || result.msg || 'Failed to revoke permission', 'error', 'Revoke Failed');
            }
          },
          error: function(xhr, status, error) {
            console.error('Revoke error:', xhr.responseText);
            showNotification("Failed to revoke permission. Please try again.", 'error', 'Error');
          }
        });
      });
  });

  // Notification Modal System
  function showNotification(message, type = 'info', title = '') {
    const modal = document.getElementById('notificationModal');
    const modalTitle = document.getElementById('notificationTitle');
    const modalMessage = document.getElementById('notificationMessage');
    const modalIcon = document.getElementById('notificationIcon');
    
    // Set icon and title based on type
    const config = {
      success: { icon: 'fa-check-circle', defaultTitle: 'Success', color: '#10b981' },
      error: { icon: 'fa-exclamation-circle', defaultTitle: 'Error', color: '#ef4444' },
      warning: { icon: 'fa-exclamation-triangle', defaultTitle: 'Warning', color: '#f59e0b' },
      info: { icon: 'fa-info-circle', defaultTitle: 'Information', color: '#3b82f6' }
    };
    
    const typeConfig = config[type] || config.info;
    modalIcon.className = `fas ${typeConfig.icon}`;
    modalIcon.style.color = typeConfig.color;
    modalTitle.textContent = title || typeConfig.defaultTitle;
    modalMessage.textContent = message;
    
    // Show modal
    modal.style.display = 'flex';
    modal.classList.add('notification-show');
    
    // Auto-close after 3 seconds
    setTimeout(() => {
      closeNotification();
    }, 3000);
  }
  
  function closeNotification() {
    const modal = document.getElementById('notificationModal');
    modal.classList.remove('notification-show');
    setTimeout(() => {
      modal.style.display = 'none';
    }, 300);
  }
  </script>

  <!-- Notification Modal -->
  <div id="notificationModal" class="notification-modal">
    <div class="notification-content">
      <i id="notificationIcon" class="fas fa-info-circle"></i>
      <div class="notification-text">
        <h3 id="notificationTitle">Notification</h3>
        <p id="notificationMessage"></p>
      </div>
      <button class="notification-close" onclick="closeNotification()">&times;</button>
    </div>
  </div>

  <style>
    /* Dropdown Menu Styles */
    .dropdown-container {
      position: relative;
      display: inline-block;
    }
    .dropdown-btn {
      background: #6c757d;
      color: white;
      border: none;
      padding: 0.4rem 0.6rem;
      font-size: 1.2rem;
      cursor: pointer;
      border-radius: 4px;
      line-height: 1;
    }
    .dropdown-btn:hover {
      background: #5a6268;
    }
    .dropdown-menu {
      display: none;
      position: absolute;
      right: 0;
      top: 100%;
      background: white;
      min-width: 140px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      border-radius: 4px;
      z-index: 1000;
      margin-top: 4px;
    }
    .dropdown-menu.show {
      display: block;
    }
    .dropdown-item {
      display: block;
      width: 100%;
      padding: 0.5rem 1rem;
      text-align: left;
      border: none;
      background: none;
      cursor: pointer;
      font-size: 0.9rem;
      transition: background 0.2s;
      border-bottom: 1px solid #f0f0f0;
    }
    .dropdown-item:last-child {
      border-bottom: none;
    }
    .dropdown-item:hover {
      background: #f8f9fa;
    }
    .dropdown-item.view { color: #007bff; }
    .dropdown-item.delete { color: #dc3545; }
    
    /* Notification Modal Styles */
    .notification-modal {
      display: none;
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 10000;
      animation: slideIn 0.3s ease-out;
    }
    
    .notification-modal.notification-show .notification-content {
      animation: slideIn 0.3s ease-out;
    }
    
    .notification-content {
      display: flex;
      align-items: center;
      gap: 15px;
      background: white;
      padding: 20px 25px;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.15);
      min-width: 350px;
      max-width: 500px;
      border-left: 5px solid #3b82f6;
    }
    
    .notification-content i {
      font-size: 28px;
      flex-shrink: 0;
    }
    
    .notification-text {
      flex: 1;
    }
    
    .notification-text h3 {
      margin: 0 0 5px 0;
      font-size: 16px;
      font-weight: 600;
      color: #1e293b;
    }
    
    .notification-text p {
      margin: 0;
      font-size: 14px;
      color: #64748b;
      line-height: 1.5;
    }
    
    .notification-close {
      background: none;
      border: none;
      font-size: 24px;
      color: #94a3b8;
      cursor: pointer;
      padding: 0;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      transition: all 0.2s;
      flex-shrink: 0;
    }
    
    .notification-close:hover {
      background: #f1f5f9;
      color: #475569;
    }
    
    @keyframes slideIn {
      from {
        transform: translateX(400px);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    
    /* Success button style */
    .btn-success {
      background: #10b981;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    
    .btn-success:hover {
      background: #059669;
    }
    
    .btn-danger {
      background: #ef4444;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    
    .btn-danger:hover {
      background: #dc2626;
    }
  </style>
  
  <script>
    // Dropdown menu toggle function (global scope)
    function toggleDropdown(event) {
        event.stopPropagation();
        const btn = event.target;
        const menu = btn.nextElementSibling;
        const allMenus = document.querySelectorAll('.dropdown-menu');
        
        // Close all other dropdowns
        allMenus.forEach(m => {
            if (m !== menu) m.classList.remove('show');
        });
        
        // Toggle current dropdown
        menu.classList.toggle('show');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.matches('.dropdown-btn')) {
            const dropdowns = document.querySelectorAll('.dropdown-menu');
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });
  </script>
</body>
</html>