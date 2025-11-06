<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>File Management</title>
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
                      <input type="file" id="uploadFile" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png" required>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="uploadCategory">File Category</label>
                      <select id="uploadCategory" required>
                        <option value="">Select Category</option>
                      </select>
                    </div>
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
      document.getElementById('uploadCategory').value = '';
      document.getElementById('fileName').value = '';
      document.getElementById('fileSize').value = '';
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
      }
    });
  </script>

  <?php require_once("modal.php");?>

  <script>
    $(document).ready(function(){
      let filesTable;
      let permissionsTable;
      const currentUserId = 1; // TODO: Get from session

      // Initialize dropdowns
      function loadDropdowns() {
        // Load file categories
        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: { CALL: 5 }, // Get file categories
          dataType: 'json',
          success: function(result) {
            if(result.data) {
              let options = '<option value="">All Categories</option>';
              let uploadOptions = '<option value="">Select Category</option>';
              let permOptions = '<option value="">Select Category</option>';
              
              result.data.forEach(category => {
                options += `<option value="${category.file_category_id}">${category.file_category}</option>`;
                uploadOptions += `<option value="${category.file_category_id}">${category.file_category}</option>`;
                permOptions += `<option value="${category.file_category_id}">${category.file_category}</option>`;
              });
              
              $('#categoryFilter').html(options);
              $('#uploadCategory').html(uploadOptions);
              $('#permissionCategory').html(permOptions);
              $('#totalCategories').val(result.data.length);
            }
          }
        });

        // Load positions
        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: { CALL: 4 }, // Get positions
          dataType: 'json',
          success: function(result) {
            if(result.data) {
              let options = '<option value="">Select Position</option>';
              result.data.forEach(position => {
                options += `<option value="${position.position_id}">${position.position}</option>`;
              });
              $('#permissionPosition').html(options);
            }
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
          },
          responsive: true,
          scroll: '50vh',
          scrollCollapse: true, 
          paging: true,
          columns: [
            { data: "file_upload_id" },
            { data: "file_name" },
            { data: "file_category" },
            { data: "mime_type" },
            { 
              data: null,
              render: function(data, type, row) {
                return `${row.fname} ${row.lname}`;
              }
            },
            { 
              data: "datetime_uploaded",
              render: function(data) {
                return new Date(data).toLocaleDateString();
              }
            },
            { 
              data: 'file_upload_id',
              render: function(data, type, row) {
                return `
                  <button class="btn-secondary viewBtn" data-id="${data}" title="View/Download">
                    <i class="fas fa-eye"></i> View
                  </button>
                  <button class="btn-primary deleteFileBtn" data-id="${data}" data-name="${row.file_name}" title="Delete File">
                    <i class="fas fa-trash"></i> Delete
                  </button>
                `;
              }
            }
          ],
          columnDefs: [
            { targets: 0, visible: false, searchable: false }
          ],
          initComplete: function(settings, json) {
            if(json.data) {
              $('#totalFiles').val(json.data.length);
            }
          }
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
          },
          responsive: true,
          scroll: '50vh',
          scrollCollapse: true, 
          paging: true,
          columns: [
            { data: "file_permission_id" },
            { data: "position" },
            { data: "file_category" },
            { 
              data: null,
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
          ]
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
            },
            responsive: true,
            scroll: '50vh',
            scrollCollapse: true, 
            paging: true,
            columns: [
              { data: "file_upload_id" },
              { data: "file_name" },
              { data: "file_category" },
              { data: "mime_type" },
              { 
                data: null,
                render: function(data, type, row) {
                  return `${row.fname} ${row.lname}`;
                }
              },
              { 
                data: "datetime_uploaded",
                render: function(data) {
                  return new Date(data).toLocaleDateString();
                }
              },
              { 
                data: 'file_upload_id',
                render: function(data, type, row) {
                  return `
                    <button class="btn-secondary viewBtn" data-id="${data}" title="View/Download">
                      <i class="fas fa-eye"></i> View
                    </button>
                    <button class="btn-primary deleteFileBtn" data-id="${data}" data-name="${row.file_name}" title="Delete File">
                      <i class="fas fa-trash"></i> Delete
                    </button>
                  `;
                }
              }
            ],
            columnDefs: [
              { targets: 0, visible: false, searchable: false }
            ]
          });
        } else {
          filesTable.ajax.reload();
        }
      });

      // File upload
      $('#uploadBtn').click(function() {
        const fileInput = document.getElementById('uploadFile');
        const categoryId = $('#uploadCategory').val();
        
        if(!fileInput.files[0]) {
          openModal("ERROR", "Please select a file to upload");
          return;
        }
        
        if(!categoryId) {
          openModal("ERROR", "Please select a file category");
          return;
        }

        const file = fileInput.files[0];
        const uploadData = {
          file_category_id: categoryId,
          mime_type: file.type,
          file_name: file.name,
          drive_id: 'temp_' + Date.now(), // Temporary ID, replace with actual Google Drive integration
          uploaded_by: currentUserId
        };

        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 16, // Upload file
            DATA: uploadData
          },
          dataType: 'json',
          success: function(result) {
            openModal(result.status, result.message || result.msg);
            if(result.status === "SUCCESS") {
              clearUploadForm();
              filesTable.ajax.reload();
            }
          }
        });
      });

      // View file button
      $(document).on("click", ".viewBtn", function() {
        const fileId = $(this).data('id');
        const row = $(this).closest("tr");
        const rowData = filesTable.row(row).data();
        
        $('#viewFileName').val(rowData.file_name);
        $('#viewFileCategory').val(rowData.file_category);
        $('#viewFileUploader').val(`${rowData.fname} ${rowData.lname}`);
        $('#viewFileDate').val(new Date(rowData.datetime_uploaded).toLocaleDateString());
        
        openViewFileModal();
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
          }
        });
      });

      // Revoke permission from table
      $(document).on("click", ".revokePermBtn", function() {
        const positionId = $(this).data('position');
        const categoryId = $(this).data('category');
        
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
          }
        });
      });
    });
  </script>
</body>
</html>