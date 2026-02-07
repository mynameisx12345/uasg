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
                      <input type="file" id="uploadFile" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.txt,.html,.rtf" required>
                    </div>
                  </div>
                  <div class="alert alert-info" style="margin-top: 10px; padding: 12px; background: #e3f2fd; border-left: 4px solid #2196F3; border-radius: 4px;">
                    <strong>🤖 AI-Powered Auto-Categorization</strong>
                    <p style="margin: 5px 0 0 0; font-size: 13px;">Files will be automatically categorized using your <strong>Trained ML Model</strong> for intelligent classification based on document content!</p>
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

    <!-- NLP ANALYSIS MODAL -->
    <div id="nlpAnalysisModal" class="modal">
      <div class="modal-content" style="max-width: 900px;">
        <span class="modal-close" onclick="closeNlpAnalysisModal()">&times;</span>
        <h2><i class="fas fa-brain"></i> NLP Analysis Report</h2>
        
        <div id="nlpAnalysisContent" style="padding: 20px 0;">
          <!-- Content will be populated by JavaScript -->
        </div>
        
        <div style="text-align: right; margin-top: 20px;">
          <button class="btn-secondary" onclick="closeNlpAnalysisModal()">Close</button>
        </div>
      </div>
    </div>

    <!-- REVOKE PERMISSION MODAL -->
    <div id="revokePermissionModal" class="modal">
      <div class="modal-content modal-content-small">
        <span class="modal-close" onclick="closeRevokePermissionModal()">&times;</span>
        <h2>Revoke Permission Confirmation</h2>
        
        <div class="compact-form">
          <input type="hidden" id="revokePositionId">
          <input type="hidden" id="revokeCategoryId">
          
          <div class="form-section">
            <h4>Permission Details</h4>
            <div class="form-row">
              <div class="form-group">
                <label>Position</label>
                <input type="text" id="revokePositionName" readonly>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>File Category</label>
                <input type="text" id="revokeCategoryName" readonly>
              </div>
            </div>
            <p style="margin-top: 15px; color: #f44336;">
              <i class="fas fa-exclamation-triangle"></i> 
              Are you sure you want to revoke this permission? Users with this position will no longer be able to access files in this category.
            </p>
          </div>
          
          <div class="form-actions">
            <button type="button" class="btn-primary" id="confirmRevokePermission">Confirm Revoke</button>
            <button type="button" class="btn-secondary" onclick="closeRevokePermissionModal()">Cancel</button>
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

    function closeRevokePermissionModal() {
      document.getElementById("revokePermissionModal").style.display = "none";
    }

    function openRevokePermissionModal() {
      document.getElementById("revokePermissionModal").style.display = "flex";
    }

    function closeViewFileModal() {
      document.getElementById("viewFileModal").style.display = "none";
    }

    function openViewFileModal() {
      document.getElementById("viewFileModal").style.display = "flex";
    }

    function closeNlpAnalysisModal() {
      document.getElementById("nlpAnalysisModal").style.display = "none";
    }

    function openNlpAnalysisModal() {
      document.getElementById("nlpAnalysisModal").style.display = "flex";
    }

    function loadNlpAnalysis(fileId, fileName) {
      $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: {
          CALL: 'get_nlp_analysis',
          file_id: fileId
        },
        dataType: 'json',
        beforeSend: function() {
          openLoadModal();
        },
        success: function(response) {
          closeLoadModal();
          
          if(response.status === 'SUCCESS' || response.status === 'WARNING') {
            displayNlpAnalysis(response, fileName);
            openNlpAnalysisModal();
          } else {
            showNotification(response.msg || 'Failed to load NLP analysis', 'error', 'Error');
          }
        },
        error: function(xhr, status, error) {
          closeLoadModal();
          console.error('NLP Analysis error:', xhr.responseText);
          showNotification('Failed to load NLP analysis. Please try again.', 'error', 'Error');
        }
      });
    }

    function displayNlpAnalysis(data, fileName) {
      const content = document.getElementById('nlpAnalysisContent');
      const file = data.file;
      const analysis = data.analysis;
      
      let html = `
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
          <h3 style="margin-top: 0; color: #333;">
            <i class="fas fa-file"></i> ${fileName || file.original_filename || file.file_name}
          </h3>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
            <div>
              <strong>Category:</strong> 
              <span class="badge badge-primary">${file.category_name || 'Uncategorized'}</span>
            </div>
            <div><strong>Uploaded:</strong> ${new Date(file.datetime_uploaded).toLocaleString()}</div>
          </div>
        </div>
      `;
      
      if(!analysis) {
        html += `
          <div style="text-align: center; padding: 40px; color: #666;">
            <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ffc107; margin-bottom: 15px;"></i>
            <p style="font-size: 16px;">No NLP analysis available for this file.</p>
            <p style="font-size: 14px; color: #999;">The file may have been uploaded before NLP processing was enabled.</p>
          </div>
        `;
      } else {
        // Confidence Score
        const confidence = parseFloat(analysis.category_confidence || 0);
        const confidenceColor = confidence >= 80 ? '#28a745' : confidence >= 60 ? '#ffc107' : '#dc3545';
        
        html += `
          <div style="margin-bottom: 25px;">
            <h4 style="color: #555; border-bottom: 2px solid #007bff; padding-bottom: 8px;">
              <i class="fas fa-chart-line"></i> Categorization Analysis
            </h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
              <div>
                <strong>Suggested Category:</strong><br>
                <span class="badge badge-primary" style="font-size: 14px; margin-top: 5px;">
                  ${analysis.suggested_category || 'N/A'}
                </span>
              </div>
              <div>
                <strong>Confidence Score:</strong><br>
                <div style="margin-top: 5px;">
                  <div style="background: #e9ecef; height: 25px; border-radius: 12px; overflow: hidden;">
                    <div style="background: ${confidenceColor}; width: ${confidence}%; height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">
                      ${confidence.toFixed(1)}%
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `;
        
        // Keywords
        if(analysis.keywords && analysis.keywords.length > 0) {
          html += `
            <div style="margin-bottom: 25px;">
              <h4 style="color: #555; border-bottom: 2px solid #007bff; padding-bottom: 8px;">
                <i class="fas fa-key"></i> Extracted Keywords
              </h4>
              <div style="margin-top: 15px; display: flex; flex-wrap: wrap; gap: 8px;">
          `;
          
          analysis.keywords.forEach(keyword => {
            html += `<span style="background: #e7f3ff; color: #0066cc; padding: 6px 12px; border-radius: 15px; font-size: 13px; border: 1px solid #b3d9ff;">${keyword}</span>`;
          });
          
          html += `</div></div>`;
        }
        
        // Entities
        if(analysis.entities && analysis.entities.length > 0) {
          html += `
            <div style="margin-bottom: 25px;">
              <h4 style="color: #555; border-bottom: 2px solid #007bff; padding-bottom: 8px;">
                <i class="fas fa-tags"></i> Named Entities
              </h4>
              <div style="margin-top: 15px; display: flex; flex-wrap: wrap; gap: 8px;">
          `;
          
          analysis.entities.forEach(entity => {
            const entityText = typeof entity === 'string' ? entity : (entity.name || entity.text || JSON.stringify(entity));
            const entityType = typeof entity === 'object' ? (entity.type || '') : '';
            html += `<span style="background: #fff3cd; color: #856404; padding: 6px 12px; border-radius: 15px; font-size: 13px; border: 1px solid #ffeaa7;">
              ${entityText}${entityType ? ` <em style="font-size: 11px;">(${entityType})</em>` : ''}
            </span>`;
          });
          
          html += `</div></div>`;
        }
        
        // Sentiment
        if(analysis.sentiment && Object.keys(analysis.sentiment).length > 0) {
          html += `
            <div style="margin-bottom: 25px;">
              <h4 style="color: #555; border-bottom: 2px solid #007bff; padding-bottom: 8px;">
                <i class="fas fa-smile"></i> Sentiment Analysis
              </h4>
              <div style="margin-top: 15px; background: #f1f3f5; padding: 12px; border-radius: 6px;">
                ${JSON.stringify(analysis.sentiment, null, 2).replace(/[{}"]/g, '').replace(/,/g, '<br>')}
              </div>
            </div>
          `;
        }
        
        // Statistics
        html += `
          <div style="margin-bottom: 25px;">
            <h4 style="color: #555; border-bottom: 2px solid #007bff; padding-bottom: 8px;">
              <i class="fas fa-info-circle"></i> Processing Statistics
            </h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-top: 15px;">
              <div style="background: #f8f9fa; padding: 12px; border-radius: 6px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #007bff;">${analysis.word_count || 0}</div>
                <div style="font-size: 12px; color: #666; margin-top: 5px;">Words Extracted</div>
              </div>
              <div style="background: #f8f9fa; padding: 12px; border-radius: 6px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #28a745;">${analysis.provider || 'N/A'}</div>
                <div style="font-size: 12px; color: #666; margin-top: 5px;">NLP Provider</div>
              </div>
              <div style="background: #f8f9fa; padding: 12px; border-radius: 6px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #ffc107;">${analysis.processing_time_ms || 0}ms</div>
                <div style="font-size: 12px; color: #666; margin-top: 5px;">Processing Time</div>
              </div>
            </div>
          </div>
        `;
        
        // Content Preview
        if(analysis.extracted_text) {
          const textPreview = analysis.extracted_text.length > 500 
            ? analysis.extracted_text.substring(0, 500) + '...' 
            : analysis.extracted_text;
          
          html += `
            <div>
              <h4 style="color: #555; border-bottom: 2px solid #007bff; padding-bottom: 8px;">
                <i class="fas fa-file-alt"></i> Extracted Text Preview
              </h4>
              <div style="margin-top: 15px; background: #f8f9fa; padding: 15px; border-radius: 6px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 13px; line-height: 1.6; white-space: pre-wrap;">
${textPreview}
              </div>
            </div>
          `;
        }
      }
      
      content.innerHTML = html;
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
        // Load file categories from category_tbl (NEW SYSTEM)
        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: { CALL: 5 }, // Get categories from category_tbl
          dataType: 'json',
          beforeSend: function(xhr) {
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
          },
          success: function(result) {
            console.log('Categories loaded:', result);
            if(result.data) {
              // Populate filter dropdown
              let filterOptions = '<option value="">All Categories</option>';
              // Populate permissions dropdown
              let permOptions = '<option value="">Select Category</option>';
              
              result.data.forEach(category => {
                // Show category name with file count for filter
                const displayName = `${category.file_category} (${category.file_count || 0} files)`;
                // Use category_id for filtering
                filterOptions += `<option value="${category.category_id}" data-slug="${category.category_slug}">${displayName}</option>`;
                // For permissions dropdown, show just category name
                permOptions += `<option value="${category.category_id}">${category.file_category}</option>`;
              });
              
              $('#categoryFilter').html(filterOptions);
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
                { 
                    data: "original_filename",
                    render: function(data, type, row) {
                        // Show original filename, fallback to system filename
                        return data || row.file_name;
                    }
                },
                { 
                    data: "file_category",
                    render: function(data, type, row) {
                        // Show category with confidence badge
                        const categoryName = data || row.category_tag || 'Uncategorized';
                        const confidence = row.category_score || 0;
                        const badgeColor = confidence >= 80 ? '#10b981' : confidence >= 50 ? '#f59e0b' : '#6b7280';
                        return `
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span>${categoryName}</span>
                                <span style="background: ${badgeColor}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                    ${confidence}%
                                </span>
                            </div>
                        `;
                    }
                },
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
                        const originalName = row.original_filename || row.file_name;
                        const displayName = originalName.length > 30 ? originalName.substring(0, 27) + '...' : originalName;
                        
                        return `
                            <div style="display: flex; gap: 5px; justify-content: center;">
                                <button class="btn-secondary viewBtn" 
                                        data-id="${row.file_upload_id}"
                                        data-name="${escapeHtml(originalName)}"
                                        data-category="${row.file_category || 'N/A'}"
                                        data-type="${row.mime_type}"
                                        data-uploaded="${row.datetime_uploaded}"
                                        data-uploader="${row.fname && row.lname ? row.fname + ' ' + row.lname : 'Unknown'}"
                                        title="View Details">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn-info viewNlpBtn" 
                                        data-id="${row.file_upload_id}"
                                        data-name="${escapeHtml(originalName)}"
                                        title="View NLP Analysis">
                                    <i class="fas fa-brain"></i> NLP Analysis
                                </button>
                                <button class="btn-success downloadBtn" 
                                        data-id="${row.file_upload_id}"
                                        data-name="${escapeHtml(originalName)}"
                                        data-path="${row.file_path}"
                                        title="Download ${escapeHtml(originalName)}">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button class="btn-danger deleteFileBtn" 
                                        data-id="${row.file_upload_id}" 
                                        data-name="${escapeHtml(originalName)}"
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
            { data: "permission_id" },
            { data: "position_name", defaultContent: "Unknown" },
            { data: "file_category", defaultContent: "Unknown" },
            { 
              data: null,
              orderable: false,
              render: function(data, type, row) {
                return `
                  <button class="btn-primary revokePermBtn" 
                          data-position="${row.position_id}" 
                          data-category="${row.category_id}"
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
              
              showNotification(notificationMsg, 'success', 'Upload Complete');
              clearUploadForm();
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

      // View NLP Analysis button
      $(document).on("click", ".viewNlpBtn", function() {
        const fileId = $(this).data('id');
        const fileName = $(this).data('name');
        
        if(fileId) {
          loadNlpAnalysis(fileId, fileName);
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
              category_id: categoryId
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
        const positionName = $(this).closest('tr').find('td').eq(0).text();
        const categoryName = $(this).closest('tr').find('td').eq(1).text();
        
        // Store data in modal and mark that it came from table (not button)
        $('#revokePositionId').val(positionId).data('fromButton', false);
        $('#revokeCategoryId').val(categoryId);
        $('#revokePositionName').val(positionName);
        $('#revokeCategoryName').val(categoryName);
        
        // Open confirmation modal
        openRevokePermissionModal();
      });

      // Confirm revoke permission from modal
      $('#confirmRevokePermission').click(function() {
        const positionId = $('#revokePositionId').val();
        const categoryId = $('#revokeCategoryId').val();
        const fromButton = $('#revokePositionId').data('fromButton'); // Track if from button or table
        
        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 19, // Remove permission
            DATA: {
              position_id: positionId,
              category_id: categoryId
            }
          },
          dataType: 'json',
          success: function(result) {
            closeRevokePermissionModal();
            if(result.status === "SUCCESS") {
              showNotification(result.message || result.msg || 'Permission revoked successfully', 'success', 'Permission Revoked');
              
              // Clear form fields if revoked from button
              if(fromButton) {
                $('#permissionPosition').val('');
                $('#permissionCategory').val('');
              }
              
              permissionsTable.ajax.reload();
            } else {
              showNotification(result.message || result.msg || 'Failed to revoke permission', 'error', 'Revoke Failed');
            }
          },
          error: function(xhr, status, error) {
            closeRevokePermissionModal();
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
        
        // Get selected option texts
        const positionName = $('#permissionPosition option:selected').text();
        const categoryName = $('#permissionCategory option:selected').text();
        
        // Store data in modal and mark that it came from button
        $('#revokePositionId').val(positionId).data('fromButton', true);
        $('#revokeCategoryId').val(categoryId);
        $('#revokePositionName').val(positionName);
        $('#revokeCategoryName').val(categoryName);
        
        // Open confirmation modal
        openRevokePermissionModal();
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