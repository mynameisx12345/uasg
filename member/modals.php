<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="modal">
    <div class="modal-content modal-content-small">
        <span class="modal-close" onclick="closeModal('logoutModal')">&times;</span>
        <h2><i class="fas fa-sign-out-alt" style="color: #2196F3;"></i> Confirm Logout</h2>
        <div style="padding: 20px 0;">
            <p>Are you sure you want to logout?</p>
            <p style="margin-top: 15px; padding: 10px; background: #e3f2fd; border-left: 4px solid #2196F3; border-radius: 4px;">
                <strong>Note:</strong> You will need to login again to access your account.
            </p>
        </div>
        <div class="form-actions">
            <button type="button" class="btn-secondary" onclick="closeModal('logoutModal')">Cancel</button>
            <button type="button" class="btn-primary" id="confirmLogoutBtn" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </div>
</div>

<!-- Delete File Confirmation Modal -->
<div id="deleteFileModal" class="modal">
    <div class="modal-content modal-content-small">
        <span class="modal-close" onclick="closeModal('deleteFileModal')">&times;</span>
        <h2><i class="fas fa-exclamation-triangle" style="color: #ff9800;"></i> Confirm Delete</h2>
        <div style="padding: 20px 0;">
            <p>Are you sure you want to delete this file?</p>
            <p style="margin-top: 10px;"><strong>File Name:</strong> <span id="deleteFileName" style="color: #2196F3;"></span></p>
            <p style="margin-top: 15px; padding: 10px; background: #fff3cd; border-left: 4px solid #ff9800; border-radius: 4px;">
                <strong>Warning:</strong> This action cannot be undone and will permanently remove the file from both the database and storage.
            </p>
        </div>
        <div class="form-actions">
            <button type="button" class="btn-secondary" onclick="closeModal('deleteFileModal')">Cancel</button>
            <button type="button" class="btn-danger" id="confirmDeleteBtn" style="background-color: #f44336;">
                <i class="fas fa-trash"></i> Delete File
            </button>
        </div>
    </div>
</div>

<!-- Notification/Message Modal -->
<div id="notificationModal" class="modal">
    <div class="modal-content modal-content-small">
        <span class="modal-close" onclick="closeNotificationModal()">&times;</span>
        <h2 id="notificationTitle">Notification</h2>
        <div id="notificationMessage" style="padding: 20px; text-align: center;">
            <!-- Message will be injected here -->
        </div>
        <div class="form-actions">
            <button type="button" class="btn-primary" onclick="closeNotificationModal()">OK</button>
        </div>
    </div>
</div>

<style>
.modal.success .modal-content-small { border-left: 5px solid #28a745; }
.modal.error .modal-content-small { border-left: 5px solid #dc3545; }
.modal.warning .modal-content-small { border-left: 5px solid #ffc107; }
.modal.info .modal-content-small { border-left: 5px solid #17a2b8; }
</style>

<script>
function openNotificationModal(message, type = 'info', title = '') {
    var modal = document.getElementById('notificationModal');
    var messageDiv = document.getElementById('notificationMessage');
    var titleEl = document.getElementById('notificationTitle');
    
    // Set title
    if (title) {
        titleEl.textContent = title;
    } else {
        titleEl.textContent = type === 'success' ? 'Success' :
                              type === 'error' ? 'Error' :
                              type === 'warning' ? 'Warning' : 'Notification';
    }
    
    // Set message
    messageDiv.innerHTML = message || 'No message.';
    
    // Set modal class for styling
    modal.className = 'modal ' + type;
    modal.style.display = 'flex';
}

function closeNotificationModal() {
    document.getElementById('notificationModal').style.display = 'none';
}

// Global openModal function for consistency
window.openModal = function(status, message) {
    const type = status.toLowerCase();
    openNotificationModal(message, type);
};
</script>
<!-- View Task Modal -->
<div id="viewTaskModal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Task Details</h3>
            <span class="close" onclick="closeModal('viewTaskModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div id="taskDetails">
                <!-- Task details will be loaded here -->
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('viewTaskModal')">Close</button>
            <button type="button" class="btn-primary btn-gold" id="submitFromViewBtn" onclick="submitFromView()" style="display: none;">Submit Task</button>
        </div>
    </div>
</div>

<!-- Loading modal -->
 <div id='loadingModal' class='modal'>
      <div class='modal-content modal-content-small'>
        <h2>Processing...</h2>
        <div style='text-align: center; margin: 20px 0;'>
          <i class='fas fa-spinner fa-spin' style='font-size: 48px; color: #2196F3;'></i>
        </div>
        <p style='text-align: center;'>Please wait while we process your request.</p>
      </div>
</div>

<!-- Submit Task Modal -->
<div class="modal" id="submitTaskModal">
    <div class="modal-content modal-content-large" style="max-width:480px;">
        <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid #eee;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:2rem;color:#2196F3;"><i class="fas fa-upload"></i></span>
                <span style="font-size:1.3rem;font-weight:600;">Submit Task</span>
            </div>
            <span class="close-modal" data-close style="font-size:1.5rem;cursor:pointer;">&times;</span>
        </div>
        <form id="submitTaskForm" enctype="multipart/form-data" style="margin-top:18px;">
            <input type="hidden" id="submit_task_id" name="task_id" />
            <div class="form-group" style="margin-bottom:18px;">
                <label for="submissionFile" style="font-weight:500;">File to Upload <span style="color:#f44336;">*</span></label>
                <input type="file" id="submissionFile" name="file" accept=".pdf,.doc,.docx,.zip,.jpg,.png" required style="margin-top:6px;">
                <small style="color:#888;font-size:12px;">Accepted: PDF, DOC, DOCX, ZIP, JPG, PNG</small>
            </div>
            <div class="form-group" style="margin-bottom:18px;">
                <label for="submissionNote" style="font-weight:500;">Notes <span style="color:#888;font-size:12px;">(optional)</span></label>
                <textarea id="submissionNote" name="notes" rows="3" placeholder="Remarks or explanation..." style="width:100%;padding:8px;border-radius:4px;border:1px solid #ddd;"></textarea>
            </div>
            <div id="submitProgress" style="display:none;margin-bottom:12px;">
                <div class="progress-bar" style="height:8px;background:#e3f2fd;border-radius:4px;overflow:hidden;">
                    <div class="progress-fill" style="width:0%;height:100%;background:#2196F3;transition:width 0.3s;"></div>
                </div>
                <span id="progressText" style="font-size:12px;color:#2196F3;">Uploading...</span>
            </div>
            <div class="form-actions" style="display:flex;justify-content:flex-end;gap:10px;margin-top:1rem;">
                <button type="submit" class="btn-primary btn-gold" style="min-width:120px;font-size:1rem;"><i class="fas fa-paper-plane"></i> Upload</button>
                <button type="button" class="btn-secondary" data-close style="min-width:80px;">Cancel</button>
            </div>
        </form>
    </div>
</div>
<style>
    #submitTaskModal .modal-content-large {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 24px rgba(33,150,243,0.08);
        padding: 32px 28px 24px 28px;
    }
    #submitTaskModal label {
        display: block;
        margin-bottom: 6px;
        color: #333;
    }
    #submitTaskModal input[type="file"] {
        width: 100%;
        padding: 6px;
        border-radius: 4px;
        border: 1px solid #ddd;
        background: #fafafa;
    }
    #submitTaskModal textarea {
        border: 1px solid #ddd;
        background: #fafafa;
        resize: vertical;
    }
    #submitTaskModal .form-actions button {
        border-radius: 4px;
        font-weight: 500;
    }
    #submitTaskModal .progress-bar {
        margin-bottom: 4px;
    }
</style>

<!-- Resubmit Task Modal -->
<div id="resubmitTaskModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Resubmit Task</h3>
            <span class="close" onclick="closeModal('resubmitTaskModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="alert alert-info">
                <strong>Note:</strong> Resubmitting will replace your previous submission and reset the review status to pending.
            </div>
            
            <form id="resubmitTaskForm" enctype="multipart/form-data">
                <input type="hidden" id="resubmitSubmissionId" name="submission_id">
                
                <div class="form-group">
                    <label for="resubmitFile">Upload New File *</label>
                    <input type="file" id="resubmitFile" name="file" required>
                    <small class="form-text">Maximum file size: 50MB</small>
                </div>
                
                <div class="form-group">
                    <label for="resubmissionNotes">Updated Notes (Optional)</label>
                    <textarea id="resubmissionNotes" name="submission_notes" rows="4" placeholder="Add any notes about your resubmission..."></textarea>
                </div>
            </form>
            
            <div id="resubmitProgress" class="progress-container" style="display: none;">
                <div class="progress-bar">
                    <div id="resubmitProgressBar" class="progress-fill"></div>
                </div>
                <div id="resubmitProgressText" class="progress-text">0%</div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('resubmitTaskModal')">Cancel</button>
            <button type="button" class="btn-warning" onclick="resubmitTask()">Resubmit Task</button>
        </div>
    </div>
</div>

<!-- View Submission Modal -->
<div id="viewSubmissionModal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Submission Details</h3>
            <span class="close" onclick="closeModal('viewSubmissionModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div id="submissionDetails">
                <!-- Submission details will be loaded here -->
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('viewSubmissionModal')">Close</button>
            <button type="button" class="btn-primary" id="downloadSubmissionBtn" onclick="downloadCurrentSubmission()">Download File</button>
            <button type="button" class="btn-warning" id="resubmitFromViewBtn" onclick="resubmitFromView()" style="display: none;">Resubmit</button>
        </div>
    </div>
</div>

<!-- View File Modal -->
<div id="viewFileModal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>File Details</h3>
            <span class="close" onclick="closeModal('viewFileModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div id="fileDetails">
                <!-- File details will be loaded here -->
            </div>
            
            <div id="filePreview">
                <!-- File preview will be shown here -->
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('viewFileModal')">Close</button>
            <button type="button" class="btn-primary" onclick="downloadCurrentFile()">Download</button>
        </div>
    </div>
</div>

<!-- File Upload Modal -->
<div id="fileUploadModal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Upload Files</h3>
            <span class="close" onclick="closeModal('fileUploadModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="fileUploadForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="uploadFiles">Select Files *</label>
                    <div class="file-upload-area" id="fileUploadArea">
                        <input type="file" id="uploadFiles" name="files[]" multiple accept="*/*" style="display: none;">
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <p>Drop files here or click to browse</p>
                            <p class="text-muted">Maximum file size: 50MB per file</p>
                        </div>
                    </div>
                </div>
                
                <div id="selectedFiles" class="selected-files" style="display: none;">
                    <h5>Selected Files:</h5>
                    <div id="fileList"></div>
                </div>
                
                <div id="contentAnalysis" class="content-analysis" style="display: none;">
                    <h5>Content Analysis & Categorization:</h5>
                    <div id="analysisResults"></div>
                </div>
                
                <div class="form-group">
                    <label for="uploadCategory">Override Category (Optional)</label>
                    <select id="uploadCategory" name="category" class="form-control">
                        <option value="">Use AI-suggested category</option>
                        <option value="academic_records">Academic Records</option>
                        <option value="financial_documents">Financial Documents</option>
                        <option value="meeting_minutes">Meeting Minutes</option>
                        <option value="reports">Reports</option>
                        <option value="event_documentation">Event Documentation</option>
                        <option value="legal_documents">Legal Documents</option>
                        <option value="proposals">Proposals</option>
                        <option value="other">Other</option>
                    </select>
                    <small class="form-text">Leave blank to use AI-suggested categories based on content analysis</small>
                </div>
                
                <div class="form-group">
                    <label for="fileDescription">Description (Optional)</label>
                    <textarea id="fileDescription" name="description" rows="3" class="form-control" placeholder="Add a description for these files..."></textarea>
                </div>
            </form>
            
            <div id="uploadProgress" class="upload-progress" style="display: none;">
                <h5>Upload Progress:</h5>
                <div id="progressList"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('fileUploadModal')">Cancel</button>
            <button type="button" class="btn-primary" id="startUploadBtn" onclick="startFileUpload()" disabled>
                <i class="fas fa-upload"></i> Upload Files
            </button>
        </div>
    </div>
</div>

<!-- Edit File Modal -->
<div id="editFileModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit File Information</h3>
            <span class="close" onclick="closeModal('editFileModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editFileForm">
                <input type="hidden" id="editFileId" name="file_id">
                
                <div class="form-group">
                    <label for="editFileName">File Name</label>
                    <input type="text" id="editFileName" name="file_name" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label for="editFileCategory">Category</label>
                    <select id="editFileCategory" name="category" class="form-control" required>
                        <option value="academic_records">Academic Records</option>
                        <option value="financial_documents">Financial Documents</option>
                        <option value="meeting_minutes">Meeting Minutes</option>
                        <option value="reports">Reports</option>
                        <option value="event_documentation">Event Documentation</option>
                        <option value="legal_documents">Legal Documents</option>
                        <option value="proposals">Proposals</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="editFileDescription">Description</label>
                    <textarea id="editFileDescription" name="description" rows="4" class="form-control" placeholder="Add a description for this file..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="editFileTags">Tags</label>
                    <input type="text" id="editFileTags" name="tags" class="form-control" placeholder="Enter tags separated by commas">
                    <small class="form-text">Example: urgent, review-needed, quarterly-report</small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('editFileModal')">Cancel</button>
            <button type="button" class="btn-primary" onclick="updateFileInfo()">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<!-- File Category Modal -->
<div id="fileCategoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Manage File Categories</h3>
            <span class="close" onclick="closeModal('fileCategoryModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="category-management">
                <div class="add-category-section">
                    <h5>Add New Category</h5>
                    <form id="addCategoryForm">
                        <div class="form-group">
                            <label for="newCategoryName">Category Name</label>
                            <input type="text" id="newCategoryName" name="category_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="newCategoryDescription">Description</label>
                            <textarea id="newCategoryDescription" name="description" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="newCategoryKeywords">Keywords (for auto-categorization)</label>
                            <input type="text" id="newCategoryKeywords" name="keywords" class="form-control" placeholder="keyword1, keyword2, keyword3">
                            <small class="form-text">Comma-separated keywords that help identify files for this category</small>
                        </div>
                        <button type="button" class="btn-primary" onclick="addCategory()">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    </form>
                </div>
                
                <hr>
                
                <div class="existing-categories">
                    <h5>Existing Categories</h5>
                    <div id="categoryList">
                        <!-- Categories will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('fileCategoryModal')">Close</button>
        </div>
    </div>
</div>

<!-- Confirm Delete Modal -->
<div id="confirmDeleteModal" class="modal">
    <div class="modal-content small">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <span class="close" onclick="closeModal('confirmDeleteModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Warning:</strong> This action cannot be undone.
            </div>
            <p id="deleteMessage">Are you sure you want to delete this item?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('confirmDeleteModal')">Cancel</button>
            <button type="button" class="btn-danger" id="confirmDeleteBtn" onclick="confirmDelete()">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    </div>
</div>

<script>

function openLoadModal(){
    document.getElementById('loadingModal').style.display = 'flex';
}

function closeLoadModal(){
    document.getElementById('loadingModal').style.display = 'none';
}


// Modal management functions
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
    document.body.classList.add('modal-open');
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.classList.remove('modal-open');
    
    // Clear form data
    const form = document.querySelector(`#${modalId} form`);
    if (form) {
        form.reset();
    }
    
    // Hide progress bars
    $(`#${modalId} .progress-container`).hide();
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        closeModal(event.target.id);
    }
}

// Task detail functions
function displayTaskDetails(task) {
    const deadline = new Date(task.task_deadline);
    const isOverdue = deadline < new Date();
    
    const detailsHtml = `
        <div class="task-info">
            <h4>${task.task_title}</h4>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Category:</strong> ${task.task_category}
                </div>
                <div class="info-item">
                    <strong>Deadline:</strong> 
                    <span class="${isOverdue ? 'text-danger' : ''}">${deadline.toLocaleString()}</span>
                </div>
                <div class="info-item">
                    <strong>Priority:</strong> 
                    <span class="priority ${task.priority}">${task.priority}</span>
                </div>
                <div class="info-item">
                    <strong>Status:</strong> 
                    <span class="status ${task.task_status}">${task.task_status}</span>
                </div>
            </div>
            <div class="description">
                <strong>Description:</strong>
                <p>${task.task_description}</p>
            </div>
            ${task.task_instructions ? `
                <div class="instructions">
                    <strong>Instructions:</strong>
                    <p>${task.task_instructions}</p>
                </div>
            ` : ''}
        </div>
    `;
    
    document.getElementById('taskDetails').innerHTML = detailsHtml;
    
    // Show submit button if task can be submitted
    const submitBtn = document.getElementById('submitFromViewBtn');
    if (task.task_status === 'active' && !isOverdue) {
        submitBtn.style.display = 'inline-block';
        submitBtn.onclick = function() {
            closeModal('viewTaskModal');
            openSubmissionModal(task.task_id);
        };
    } else {
        submitBtn.style.display = 'none';
    }
}

// Submission functions
window.openSubmissionModal = function(taskId) {
    document.getElementById('submitTaskId').value = taskId;
    openModal('submitTaskModal');
};

window.submitTask = function() {
    const form = document.getElementById('submitTaskForm');
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // STEP 1: Validate if file matches task requirements (NEW!)
    const taskId = document.getElementById('submit_task_id').value;
    const fileInput = document.getElementById('submissionFile');
    const file = fileInput.files[0];
    
    if (!file) {
        openNotificationModal('Please select a file to upload.', 'error');
        return;
    }
    
    // Show validation progress
    Swal.fire({
        title: 'Validating File...',
        html: `
            <p>🤖 AI is analyzing if your file matches the task requirements...</p>
            <div class="swal-spinner" style="margin: 20px auto;">
                <i class="fas fa-spinner fa-spin" style="font-size: 40px; color: #2196F3;"></i>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Create FormData for validation
    const validationData = new FormData();
    validationData.append('CALL', 'validate_task_file');
    validationData.append('task_id', taskId);
    validationData.append('file', file);
    
    // Validate file against task
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: validationData,
        processData: false,
        contentType: false,
        success: function(validationResult) {
            const result = typeof validationResult === 'string' ? JSON.parse(validationResult) : validationResult;
            
            if (!result.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Failed',
                    text: result.error || 'Could not validate file. Please try again.',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Check if file is valid for this task
            if (!result.is_valid) {
                // File does NOT match task - show warning and block upload
                Swal.fire({
                    icon: 'error',
                    title: '❌ File Does Not Match Task!',
                    html: `
                        <div style="text-align: left; padding: 15px;">
                            <p style="margin-bottom: 15px;"><strong>This file does not appear to match the task requirements.</strong></p>
                            
                            <div style="background: #f8d7da; padding: 12px; border-radius: 6px; margin-bottom: 15px;">
                                <strong>📋 Task:</strong> ${result.task_info.title}<br>
                                <strong>📁 Required Category:</strong> ${result.task_info.category}<br>
                                <strong>🤖 File Detected As:</strong> ${result.validation_details.category_match.predicted_category}
                            </div>
                            
                            <p style="margin-bottom: 10px;"><strong>Issues Found:</strong></p>
                            <ul style="text-align: left; color: #721c24;">
                                ${result.recommendation.reasons.map(r => `<li>${r}</li>`).join('')}
                            </ul>
                            
                            <p style="margin-top: 15px; padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                                <strong>💡 Suggestion:</strong> ${result.recommendation.suggestion || 'Please upload the correct document for this task.'}
                            </p>
                            
                            <p style="margin-top: 15px; font-size: 13px; color: #666;">
                                <strong>Match Score:</strong> ${result.overall_score}% (minimum: 60%)
                            </p>
                        </div>
                    `,
                    confirmButtonText: 'Choose Different File',
                    confirmButtonColor: '#dc3545',
                    showCancelButton: false,
                    width: 600
                });
                return;
            }
            
            // File IS valid - show success and proceed with upload
            let confirmMessage = `
                <div style="text-align: left; padding: 15px;">
                    <p style="margin-bottom: 15px;"><strong>✓ File validated successfully!</strong></p>
                    
                    <div style="background: #d4edda; padding: 12px; border-radius: 6px; margin-bottom: 15px;">
                        <strong>📋 Task:</strong> ${result.task_info.title}<br>
                        <strong>📁 Category:</strong> ${result.task_info.category}<br>
                        <strong>🤖 AI Confidence:</strong> ${result.confidence}
                    </div>
                    
                    <p style="margin-bottom: 10px;"><strong>Validation Results:</strong></p>
                    <ul style="text-align: left; color: #155724;">
                        <li>✓ Category Match: ${result.validation_details.category_match.score}%</li>
                        <li>✓ Keyword Match: ${result.validation_details.keyword_match.score}%</li>
                        <li>✓ Content Relevance: ${result.validation_details.relevance_score.score}%</li>
                    </ul>
                    
                    <p style="margin-top: 15px; font-size: 13px; color: #666;">
                        <strong>Overall Match Score:</strong> ${result.overall_score}%
                    </p>
                    
                    <p style="margin-top: 15px; padding: 10px; background: #e3f2fd; border-left: 4px solid #2196F3; border-radius: 4px;">
                        ${result.recommendation.message}
                    </p>
                </div>
            `;
            
            Swal.fire({
                icon: 'success',
                title: '✓ File Matches Task!',
                html: confirmMessage,
                confirmButtonText: 'Proceed with Upload',
                confirmButtonColor: '#28a745',
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                width: 600
            }).then((confirmResult) => {
                if (confirmResult.isConfirmed) {
                    // STEP 2: Proceed with actual upload
                    proceedWithTaskSubmission(form);
                }
            });
            
        },
        error: function() {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Unavailable',
                text: 'Could not validate file automatically. Proceed with caution.',
                confirmButtonText: 'Upload Anyway',
                showCancelButton: true,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    proceedWithTaskSubmission(form);
                }
            });
        }
    });
};

// Separate function to handle the actual upload after validation
function proceedWithTaskSubmission(form) {
    const formData = new FormData(form);
    formData.append('CALL', '11'); // Use the existing submit task endpoint
    
    // Show progress
    document.getElementById('submitProgress').style.display = 'block';
    
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    const percentComplete = (evt.loaded / evt.total) * 100;
                    document.getElementById('submitProgressBar').style.width = percentComplete + '%';
                    document.getElementById('submitProgressText').textContent = Math.round(percentComplete) + '%';
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            const result = typeof response === 'string' ? JSON.parse(response) : response;
            openNotificationModal(result.msg, result.status === 'SUCCESS' ? 'success' : 'error');
            if (result.status === 'SUCCESS') {
                closeModal('submitTaskModal');
                // Reload relevant tables
                if (window.dashboardTables && window.dashboardTables.recentTasks) {
                    window.dashboardTables.recentTasks.ajax.reload();
                }
                if (window.allTables && window.allTables.tasks) {
                    window.allTables.tasks.ajax.reload();
                }
                // Reload dashboard stats
                if (typeof refreshDashboard === 'function') {
                    refreshDashboard();
                }
            }
            document.getElementById('submitProgress').style.display = 'none';
        },
        error: function() {
            openNotificationModal('Error submitting task. Please try again.', 'error');
            document.getElementById('submitProgress').style.display = 'none';
        }
    });
};

window.openResubmissionModal = function(submissionId) {
    document.getElementById('resubmitSubmissionId').value = submissionId;
    openModal('resubmitTaskModal');
};

window.resubmitTask = function() {
    const form = document.getElementById('resubmitTaskForm');
    const formData = new FormData(form);
    formData.append('CALL', 'resubmit_task');
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    if (!confirm('Are you sure you want to resubmit? This will replace your previous submission.')) {
        return;
    }
    
    // Show progress
    document.getElementById('resubmitProgress').style.display = 'block';
    
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    const percentComplete = (evt.loaded / evt.total) * 100;
                    document.getElementById('resubmitProgressBar').style.width = percentComplete + '%';
                    document.getElementById('resubmitProgressText').textContent = Math.round(percentComplete) + '%';
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            const result = typeof response === 'string' ? JSON.parse(response) : response;
            openNotificationModal(result.msg, result.status === 'SUCCESS' ? 'success' : 'error');
            if (result.status === 'SUCCESS') {
                closeModal('resubmitTaskModal');
                // Reload relevant tables
                if (window.dashboardTables && window.dashboardTables.recentSubmissions) {
                    window.dashboardTables.recentSubmissions.ajax.reload();
                }
                if (window.allTables && window.allTables.submissions) {
                    window.allTables.submissions.ajax.reload();
                }
                // Reload dashboard stats
                if (typeof refreshDashboard === 'function') {
                    refreshDashboard();
                }
            }
            document.getElementById('resubmitProgress').style.display = 'none';
        },
        error: function() {
            openNotificationModal('Error resubmitting task. Please try again.', 'error');
            document.getElementById('resubmitProgress').style.display = 'none';
        }
    });
};

// Submission detail functions
function displaySubmissionDetails(submission) {
    const submissionDate = new Date(submission.datetime_uploaded);
    const deadline = new Date(submission.task_deadline);
    const isLate = submissionDate > deadline;
    
    const detailsHtml = `
        <div class="submission-info">
            <h4>${submission.task_title}</h4>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Category:</strong> ${submission.task_category}
                </div>
                <div class="info-item">
                    <strong>File:</strong> ${submission.file_name}
                </div>
                <div class="info-item">
                    <strong>Submitted:</strong> 
                    <span class="${isLate ? 'text-danger' : ''}">${submissionDate.toLocaleString()}</span>
                    ${isLate ? '<span class="text-danger">(Late)</span>' : ''}
                </div>
                <div class="info-item">
                    <strong>Deadline:</strong> ${deadline.toLocaleString()}
                </div>
                <div class="info-item">
                    <strong>Status:</strong> 
                    <span class="status ${submission.check_status}">${submission.check_status.toUpperCase().replace('_', ' ')}</span>
                </div>
                ${submission.grade ? `
                    <div class="info-item">
                        <strong>Grade:</strong> ${submission.grade}
                    </div>
                ` : ''}
            </div>
            <div class="description">
                <strong>Task Description:</strong>
                <p>${submission.task_description}</p>
            </div>
            ${submission.submission_notes ? `
                <div class="notes">
                    <strong>Your Notes:</strong>
                    <p>${submission.submission_notes}</p>
                </div>
            ` : ''}
            ${submission.feedback ? `
                <div class="feedback">
                    <strong>Feedback:</strong>
                    <div class="feedback-content">
                        <p>${submission.feedback}</p>
                    </div>
                </div>
            ` : ''}
        </div>
    `;
    
    document.getElementById('submissionDetails').innerHTML = detailsHtml;
    
    // Store submission data for actions
    window.currentSubmissionFile = submission.file_name;
    window.currentSubmissionId = submission.task_submission_id;
    
    // Show resubmit button if applicable
    const resubmitBtn = document.getElementById('resubmitFromViewBtn');
    if (submission.check_status === 'rejected' || submission.check_status === 'revision_requested') {
        resubmitBtn.style.display = 'inline-block';
        resubmitBtn.onclick = function() {
            closeModal('viewSubmissionModal');
            openResubmissionModal(submission.task_submission_id);
        };
    } else {
        resubmitBtn.style.display = 'none';
    }
}

window.downloadCurrentSubmission = function() {
    if (window.currentSubmissionFile) {
        downloadFile(window.currentSubmissionFile);
    }
};

// File functions
window.viewFile = function(fileId) {
    // Load file details via AJAX
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: {
            CALL: 'get_file_details',
            file_id: fileId
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'SUCCESS') {
                displayFileDetails(response.data);
                openModal('viewFileModal');
            } else {
                openNotificationModal(response.msg || 'Error loading file details', 'error');
            }
        },
        error: function() {
            openNotificationModal('Error loading file details', 'error');
        }
    });
};

function displayFileDetails(file) {
    const uploadDate = new Date(file.datetime_uploaded);
    const fileSize = formatFileSize(file.file_size);
    
    const detailsHtml = `
        <div class="file-info">
            <h4>${file.file_name}</h4>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Category:</strong> ${file.category_name || file.category}
                </div>
                <div class="info-item">
                    <strong>Size:</strong> ${fileSize}
                </div>
                <div class="info-item">
                    <strong>Uploaded:</strong> ${uploadDate.toLocaleString()}
                </div>
                <div class="info-item">
                    <strong>Type:</strong> ${file.file_type}
                </div>
                <div class="info-item">
                    <strong>Status:</strong> 
                    <span class="status ${file.status}">${file.status}</span>
                </div>
                ${file.tags ? `
                    <div class="info-item">
                        <strong>Tags:</strong> 
                        <div class="file-tags">
                            ${file.tags.split(',').map(tag => `<span class="tag">${tag.trim()}</span>`).join('')}
                        </div>
                    </div>
                ` : ''}
            </div>
            ${file.description ? `
                <div class="description">
                    <strong>Description:</strong>
                    <p>${file.description}</p>
                </div>
            ` : ''}
            ${file.ai_analysis ? `
                <div class="ai-analysis">
                    <strong>AI Analysis:</strong>
                    <div class="analysis-content">
                        <p><strong>Confidence:</strong> ${file.confidence_level}</p>
                        <p><strong>Suggested Category:</strong> ${file.suggested_category}</p>
                        ${file.analysis_notes ? `<p><strong>Notes:</strong> ${file.analysis_notes}</p>` : ''}
                    </div>
                </div>
            ` : ''}
        </div>
    `;
    
    document.getElementById('fileDetails').innerHTML = detailsHtml;
    window.currentFileName = file.file_name;
    window.currentFileId = file.file_id;
}

// File upload functions
window.openFileUploadModal = function() {
    resetUploadForm();
    openModal('fileUploadModal');
};

function resetUploadForm() {
    document.getElementById('fileUploadForm').reset();
    document.getElementById('uploadFiles').value = '';
    document.getElementById('selectedFiles').style.display = 'none';
    document.getElementById('contentAnalysis').style.display = 'none';
    document.getElementById('uploadProgress').style.display = 'none';
    document.getElementById('startUploadBtn').disabled = true;
    document.getElementById('fileList').innerHTML = '';
    document.getElementById('analysisResults').innerHTML = '';
    document.getElementById('progressList').innerHTML = '';
}

// File upload area drag and drop
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('uploadFiles');
    
    if (uploadArea && fileInput) {
        uploadArea.addEventListener('click', () => fileInput.click());
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('drag-over');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
            const files = e.dataTransfer.files;
            handleFileSelection(files);
        });
        
        fileInput.addEventListener('change', (e) => {
            handleFileSelection(e.target.files);
        });
    }
});

function handleFileSelection(files) {
    if (files.length === 0) return;
    
    displaySelectedFiles(files);
    //analyzeFileContent(files);
    document.getElementById('startUploadBtn').disabled = false;
}

function displaySelectedFiles(files) {
    const fileList = document.getElementById('fileList');
    fileList.innerHTML = '';
    
    Array.from(files).forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.innerHTML = `
            <div class="file-info">
                <i class="fas fa-file"></i>
                <span class="file-name">${file.name}</span>
                <span class="file-size">(${formatFileSize(file.size)})</span>
            </div>
            <button type="button" class="btn-remove" onclick="removeFile(${index})">
                <i class="fas fa-times"></i>
            </button>
        `;
        fileList.appendChild(fileItem);
    });
    
    document.getElementById('selectedFiles').style.display = 'block';
}

/*function analyzeFileContent(files) {
    const analysisResults = document.getElementById('analysisResults');
    analysisResults.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Analyzing content...</div>';
    document.getElementById('contentAnalysis').style.display = 'block';
    
    const fileData = Array.from(files).map(file => ({
        name: file.name,
        size: file.size,
        type: file.type
    }));
    
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: {
            CALL: 'analyze_file_content',
            files: JSON.stringify(fileData)
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'SUCCESS') {
                displayAnalysisResults(response.data);
            } else {
                analysisResults.innerHTML = '<div class="error">Error analyzing content</div>';
            }
        },
        error: function() {
            analysisResults.innerHTML = '<div class="error">Error analyzing content</div>';
        }
    });
}*/

function displayAnalysisResults(results) {
    const analysisResults = document.getElementById('analysisResults');
    let html = '';
    
    results.forEach((result, index) => {
        const confidence = result.confidence_level;
        const confidenceClass = confidence === 'High' ? 'success' : confidence === 'Medium' ? 'warning' : 'secondary';
        
        html += `
            <div class="analysis-item">
                <div class="file-analysis">
                    <strong>${result.file_name}</strong>
                    <div class="analysis-details">
                        <span class="badge badge-${confidenceClass}">${confidence} Confidence</span>
                        <span class="suggested-category">→ ${result.suggested_category}</span>
                    </div>
                    ${result.keywords_found.length > 0 ? `
                        <div class="keywords-found">
                            <small>Keywords found: ${result.keywords_found.join(', ')}</small>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    });
    
    analysisResults.innerHTML = html;
}

window.startFileUpload = function() {
    const fileInput = document.getElementById('uploadFiles');
    const files = fileInput.files;
    
    if (files.length === 0) {
        openNotificationModal('Please select files to upload', 'warning');
        return;
    }
    
    document.getElementById('uploadProgress').style.display = 'block';
    document.getElementById('startUploadBtn').disabled = true;
    
    uploadFiles(files);
};

function uploadFiles(files) {
    const formData = new FormData();
    const category = document.getElementById('uploadCategory').value;
    const description = document.getElementById('fileDescription').value;
    
    formData.append('CALL', 'upload_files');
    formData.append('category', category);
    formData.append('description', description);
    
    Array.from(files).forEach((file, index) => {
        formData.append(`files[${index}]`, file);
    });
    
    const progressList = document.getElementById('progressList');
    progressList.innerHTML = '';
    
    // Create progress items for each file
    Array.from(files).forEach((file, index) => {
        const progressItem = document.createElement('div');
        progressItem.className = 'progress-item';
        progressItem.id = `progress-${index}`;
        progressItem.innerHTML = `
            <div class="file-progress">
                <span class="file-name">${file.name}</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 0%"></div>
                </div>
                <span class="progress-text">0%</span>
            </div>
        `;
        progressList.appendChild(progressItem);
    });
    
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    const percentComplete = (evt.loaded / evt.total) * 100;
                    // Update overall progress
                    document.querySelectorAll('.progress-fill').forEach(bar => {
                        bar.style.width = percentComplete + '%';
                    });
                    document.querySelectorAll('.progress-text').forEach(text => {
                        text.textContent = Math.round(percentComplete) + '%';
                    });
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            const result = typeof response === 'string' ? JSON.parse(response) : response;
            
            if (result.status === 'SUCCESS') {
                openNotificationModal('Files uploaded successfully!', 'success');
                closeModal('fileUploadModal');
                
                // Reload relevant tables
                if (window.dashboardTables && window.dashboardTables.recentFiles) {
                    window.dashboardTables.recentFiles.ajax.reload();
                }
                if (window.allTables && window.allTables.files) {
                    window.allTables.files.ajax.reload();
                }
                if (typeof refreshDashboard === 'function') {
                    refreshDashboard();
                }
            } else {
                openNotificationModal(result.msg || 'Error uploading files', 'error');
                document.getElementById('startUploadBtn').disabled = false;
            }
        },
        error: function() {
            openNotificationModal('Error uploading files', 'error');
            document.getElementById('startUploadBtn').disabled = false;
        }
    });
}

// Edit file functions
window.editFile = function(fileId) {
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: {
            CALL: 'get_file_details',
            file_id: fileId
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'SUCCESS') {
                populateEditForm(response.data);
                openModal('editFileModal');
            } else {
                openNotificationModal(response.msg || 'Error loading file details', 'error');
            }
        },
        error: function() {
            openNotificationModal('Error loading file details', 'error');
        }
    });
};

function populateEditForm(file) {
    document.getElementById('editFileId').value = file.file_id;
    document.getElementById('editFileName').value = file.file_name;
    document.getElementById('editFileCategory').value = file.category;
    document.getElementById('editFileDescription').value = file.description || '';
    document.getElementById('editFileTags').value = file.tags || '';
}

window.updateFileInfo = function() {
    const form = document.getElementById('editFileForm');
    const formData = new FormData(form);
    formData.append('CALL', 'update_file_info');
    
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            const result = typeof response === 'string' ? JSON.parse(response) : response;
            openNotificationModal(result.msg, result.status === 'SUCCESS' ? 'success' : 'error');
            
            if (result.status === 'SUCCESS') {
                closeModal('editFileModal');
                
                // Reload tables
                if (window.allTables && window.allTables.files) {
                    window.allTables.files.ajax.reload();
                }
            }
        },
        error: function() {
            openNotificationModal('Error updating file information', 'error');
        }
    });
};

// Delete confirmation
window.showDeleteConfirm = function(type, id, name) {
    document.getElementById('deleteMessage').textContent = 
        `Are you sure you want to delete this ${type}: "${name}"?`;
    
    document.getElementById('confirmDeleteBtn').onclick = function() {
        performDelete(type, id);
    };
    
    openModal('confirmDeleteModal');
};

function performDelete(type, id) {
    const action = type === 'file' ? 'delete_file' : 'delete_category';
    
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: {
            CALL: action,
            id: id
        },
        dataType: 'json',
        success: function(response) {
            const result = typeof response === 'string' ? JSON.parse(response) : response;
            openNotificationModal(result.msg, result.status === 'SUCCESS' ? 'success' : 'error');
            
            if (result.status === 'SUCCESS') {
                closeModal('confirmDeleteModal');
                
                // Reload appropriate tables
                if (type === 'file' && window.allTables && window.allTables.files) {
                    window.allTables.files.ajax.reload();
                } else if (type === 'category') {
                    loadCategories();
                }
            }
        },
        error: function() {
            openNotificationModal(`Error deleting ${type}`, 'error');
        }
    });
}

window.downloadCurrentFile = function() {
    if (window.currentFileName) {
        downloadFile(window.currentFileName);
    }
};

// Utility functions
window.submitFromView = function() {
    // This function is set dynamically in displayTaskDetails
};

window.resubmitFromView = function() {
    // This function is set dynamically in displaySubmissionDetails
};
</script>

<style>
/* Additional styles for member modals */
.task-info, .submission-info {
    margin-bottom: 20px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin: 15px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-item strong {
    color: #495057;
    font-size: 0.9rem;
}

.description, .instructions, .notes, .feedback {
    margin: 15px 0;
    padding: 15px;
    border-left: 4px solid var(--fb-blue);
    background: #f8f9fa;
    border-radius: 0 8px 8px 0;
}

.feedback-content {
    margin-top: 10px;
    padding: 10px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.form-text {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 5px;
}

/* File upload styles */
.file-upload-area {
    border: 2px dashed #007bff;
    border-radius: 12px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.file-upload-area:hover,
.file-upload-area.drag-over {
    border-color: #0056b3;
    background: #e3f2fd;
}

.upload-placeholder p {
    margin: 5px 0;
    font-size: 1.1rem;
}

.upload-placeholder .text-muted {
    font-size: 0.9rem;
}

.selected-files, .content-analysis, .upload-progress {
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.file-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    margin-bottom: 8px;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
}

.file-item .file-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.file-item .file-name {
    font-weight: 500;
}

.file-item .file-size {
    color: #6c757d;
    font-size: 0.9rem;
}

.btn-remove {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.8rem;
}

.btn-remove:hover {
    background: #c82333;
}

.analysis-item {
    margin-bottom: 15px;
    padding: 12px;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
}

.file-analysis {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.analysis-details {
    display: flex;
    align-items: center;
    gap: 12px;
}

.badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-success { background: #28a745; color: white; }
.badge-warning { background: #ffc107; color: #212529; }
.badge-secondary { background: #6c757d; color: white; }

.suggested-category {
    color: #007bff;
    font-weight: 500;
}

.keywords-found {
    margin-top: 5px;
}

.keywords-found small {
    color: #6c757d;
    font-style: italic;
}

.progress-item {
    margin-bottom: 15px;
    padding: 12px;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
}

.file-progress {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #007bff, #0056b3);
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.9rem;
    color: #6c757d;
    text-align: right;
}

.file-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.tag {
    background: #e9ecef;
    color: #495057;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
}

.ai-analysis {
    margin: 15px 0;
    padding: 15px;
    background: #e3f2fd;
    border: 1px solid #bbdefb;
    border-radius: 8px;
}

.analysis-content {
    margin-top: 10px;
}

.analysis-content p {
    margin: 5px 0;
}

.category-management {
    max-height: 500px;
    overflow-y: auto;
}

.add-category-section {
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.category-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    margin-bottom: 8px;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
}

.category-info {
    flex: 1;
}

.category-info h6 {
    margin: 0 0 5px 0;
    color: #495057;
}

.category-info p {
    margin: 0;
    font-size: 0.9rem;
    color: #6c757d;
}

.category-keywords {
    font-size: 0.8rem;
    color: #28a745;
    margin-top: 3px;
}

.category-actions {
    display: flex;
    gap: 5px;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 0.8rem;
    border-radius: 4px;
}

.alert {
    padding: 12px 15px;
    margin-bottom: 15px;
    border: 1px solid transparent;
    border-radius: 6px;
}

.alert-info {
    background: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.alert-warning {
    background: #fff3cd;
    border-color: #ffeeba;
    color: #856404;
}

.loading {
    text-align: center;
    padding: 20px;
    color: #6c757d;
}

.error {
    text-align: center;
    padding: 20px;
    color: #dc3545;
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .analysis-details {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .file-item {
        flex-direction: column;
        gap: 10px;
    }
    
    .category-item {
        flex-direction: column;
        gap: 10px;
    }
    
    .category-actions {
        width: 100%;
        justify-content: flex-end;
    }
}

/* Modal size variants */
.modal-content.small {
    max-width: 400px;
}

.modal-content.large {
    max-width: 900px;
}
</style>