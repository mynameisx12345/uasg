/**
 * File Uploads Management - Subadmin Module
 * 
 * This module provides permission-based file upload management for subadmins.
 * Permissions are checked both server-side (PHP) and client-side (JS) for better UX.
 * 
 * Permission Levels:
 * - view: View file list and details
 * - create: Upload new files
 * - edit: Modify file information
 * - delete: Remove files from system
 * 
 * AJAX Calls:
 * CALL 19: Get files with filters (requires view permission)
 * CALL 20: Get file categories
 * CALL 21: Upload file (requires create permission)
 * CALL 22: Update file info (requires edit permission)
 * CALL 23: Get file details (requires view permission)
 * CALL 24: Delete file (requires delete permission)
 * CALL 25: Download file (GET request, requires view permission)
 * 
 * All actions are logged in subadmin_activity_log_tbl for audit purposes.
 */

$(document).ready(function(){
    // Initialize DataTables variable
    let filesTable;
    let currentFileId = null;
    let currentFileName = null;
    
    // Initialize page
    initializeFilesPage();
    
    // Initialize Tab Switching
    initializeTabSwitching();
    
    // Initialize File Upload
    initializeFileUpload();
    
    function initializeFilesPage() {
        // Load dropdown data
        loadCategories();
        
        // Initialize Files Table
        initializeFilesTable();
    }
    
    function initializeTabSwitching() {
        $(document).on('click', '.tab-link', function(e) {
            e.preventDefault();
            
            // Remove active classes
            $('.tab-link').removeClass('active');
            $('.tab-content').removeClass('active');
            
            // Add active class to clicked tab
            $(this).addClass('active');
            
            // Get target tab
            const targetTab = $(this).data('tab');
            $('#' + targetTab).addClass('active');
            
            // Reload data if needed
            if (targetTab === 'file-list' && filesTable) {
                filesTable.ajax.reload();
            }
        });
    }
    
    function initializeFilesTable() {
        filesTable = $("#filesTable").DataTable({
            ajax: {
                url: 'ajax.php',
                type: 'POST',
                data: function(d) {
                    return {
                        CALL: 19, // Get subadmin files
                        DATA: {
                            category_id: $('#filterCategory').val(),
                            date_from: $('#filterDateFrom').val(),
                            date_to: $('#filterDateTo').val()
                        }
                    };
                },
                dataSrc: function(json) {
                    if (json.status === 'ERROR') {
                        console.error('Error loading files:', json.msg);
                        return [];
                    }
                    return json.data || [];
                }
            },
            responsive: true,
            paging: true,
            pageLength: 25,
            columns: [
                { 
                    data: "file_upload_id",
                    width: "80px"
                },
                { 
                    data: "file_name",
                    render: function(data, type, row) {
                        return '<span title="' + data + '">' + truncateText(data, 30) + '</span>';
                    }
                },
                { 
                    data: "file_category",
                    defaultContent: "Uncategorized"
                },
                { 
                    data: "mime_type",
                    render: function(data) {
                        if (!data) return 'N/A';
                        // Extract the main type from mime type (e.g., "application/pdf" -> "PDF")
                        const parts = data.split('/');
                        const subtype = parts[1] || parts[0];
                        return subtype.toUpperCase();
                    }
                },
                { 
                    data: "file_size",
                    render: function(data) {
                        return formatFileSize(data);
                    }
                },
                { 
                    data: null,
                    render: function(data, type, row) {
                        if (row.fname && row.lname) {
                            return row.fname + ' ' + row.lname;
                        }
                        return 'Unknown';
                    }
                },
                { 
                    data: "datetime_uploaded",
                    render: function(data) {
                        return formatDateTime(data);
                    }
                },
                { 
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        let actions = '<div class="action-buttons">';
                        
                        // View button (always available if user has view permission)
                        if (window.userPermissions.canView) {
                            actions += '<button class="btn-sm btn-info" onclick="viewFile(' + row.file_upload_id + ')" title="View Details">' +
                                      '<i class="fa fa-eye"></i></button> ';
                        }
                        
                        // Download button
                        actions += '<button class="btn-sm btn-success" onclick="downloadFileById(' + row.file_upload_id + ')" title="Download">' +
                                  '<i class="fa fa-download"></i></button> ';
                        
                        // Edit button (only if user has edit permission)
                        if (window.userPermissions.canEdit) {
                            actions += '<button class="btn-sm btn-warning" onclick="editFile(' + row.file_upload_id + ')" title="Edit">' +
                                      '<i class="fa fa-edit"></i></button> ';
                        }
                        
                        // Delete button (only if user has delete permission)
                        if (window.userPermissions.canDelete) {
                            actions += '<button class="btn-sm btn-danger" onclick="deleteFile(' + row.file_upload_id + ', \'' + 
                                      escapeHtml(row.file_name) + '\')" title="Delete">' +
                                      '<i class="fa fa-trash"></i></button>';
                        }
                        
                        actions += '</div>';
                        return actions;
                    }
                },
                {
                    data: "category_tag",
                    defaultContent: "N/A",
                    render: function(data) {
                        return data ? escapeHtml(data) : 'N/A';
                    }
                },
                {
                    data: "category_score",
                    defaultContent: "N/A",
                    render: function(data) {
                        if (!data || data == 0) return 'N/A';
                        const score = parseFloat(data);
                        let color = '#dc3545'; // red
                        if (score >= 80) color = '#10b981'; // green
                        else if (score >= 50) color = '#f59e0b'; // orange
                        return '<span style="color: ' + color + '; font-weight: 600;">' + score.toFixed(1) + '%</span>';
                    }
                }
            ],
            order: [[6, 'desc']], // Sort by upload date descending
            language: {
                emptyTable: "No files found",
                loadingRecords: "Loading files..."
            }
        });
    }
    
    function loadCategories() {
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: {
                CALL: 20, // Get file categories
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'SUCCESS' && response.data) {
                    const categories = response.data;
                    
                    // Populate filter dropdown
                    let filterOptions = '<option value="">All Categories</option>';
                    categories.forEach(cat => {
                        filterOptions += '<option value="' + cat.file_category_id + '">' + 
                                       escapeHtml(cat.file_category) + '</option>';
                    });
                    $('#filterCategory').html(filterOptions);
                    
                    // Populate edit dropdown (category can be changed manually after upload)
                    let editOptions = '<option value="">Select Category</option>';
                    categories.forEach(cat => {
                        editOptions += '<option value="' + cat.file_category_id + '">' + 
                                       escapeHtml(cat.file_category) + '</option>';
                    });
                    $('#editFileCategory').html(editOptions);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading categories:', error);
            }
        });
    }
    
    function initializeFileUpload() {
        // Drag and drop functionality
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        
        if (uploadArea && fileInput) {
            uploadArea.addEventListener('click', () => fileInput.click());
            
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragging');
            });
            
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragging');
            });
            
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragging');
                
                if (e.dataTransfer.files.length > 0) {
                    fileInput.files = e.dataTransfer.files;
                    displayFilePreview(e.dataTransfer.files[0]);
                }
            });
            
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    displayFilePreview(e.target.files[0]);
                    // Trigger NLP analysis immediately on file selection
                    analyzeFileWithNLP(e.target.files[0]);
                }
            });
        }
        
        // NLP Analysis function (matching admin algorithm)
        function analyzeFileWithNLP(file) {
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
                beforeSend: function() {
                    $('#suggestedCategory').html('<i class="fa fa-spinner fa-spin"></i> Analyzing...');
                    $('#categoryConfidence').text('Analyzing...');
                    $('#nlpPreview').show();
                },
                success: function(result) {
                    if (result.status === 'SUCCESS') {
                        const category = result.category || 'Uncategorized';
                        const score = result.score || 0;
                        
                        $('#suggestedCategory').html('<strong style="color: #2196F3;">' + escapeHtml(category) + '</strong>');
                        $('#categoryConfidence').html(getConfidenceBadge(score));
                        
                        // Store NLP data for upload
                        $('#uploadForm').data('nlp-category', category);
                        $('#uploadForm').data('nlp-score', score);
                        $('#uploadForm').data('nlp-analysis', JSON.stringify(result.nlp_analysis || {}));
                    } else {
                        $('#suggestedCategory').html('<span style="color: #dc3545;">Analysis failed</span>');
                        $('#categoryConfidence').html('<span style="color: #dc3545;">N/A</span>');
                        console.error('NLP Error:', result.msg);
                    }
                },
                error: function(xhr, status, error) {
                    $('#suggestedCategory').html('<span style="color: #dc3545;">Error</span>');
                    $('#categoryConfidence').html('<span style="color: #dc3545;">Failed</span>');
                    console.error('NLP AJAX Error:', error);
                }
            });
        }
        
        function getConfidenceBadge(score) {
            let color = '#dc3545'; // red for low confidence
            let label = 'Low';
            
            if (score >= 80) {
                color = '#10b981'; // green
                label = 'High';
            } else if (score >= 50) {
                color = '#f59e0b'; // orange
                label = 'Medium';
            }
            
            return '<span style="color: ' + color + '; font-weight: 600;">' + 
                   score.toFixed(1) + '% (' + label + ')</span>';
        }
        
        // Upload form submission with Google NLP auto-categorization (matching admin algorithm)
        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();
            
            const file = fileInput.files[0];
            if (!file) {
                openModal('ERROR', 'Please select a file to upload');
                return;
            }
            
            // Validate file size (10MB max)
            if (file.size > 10 * 1024 * 1024) {
                openModal('ERROR', 'File size exceeds 10MB limit!');
                return;
            }
            
            const formData = new FormData();
            formData.append('CALL', 21); // Upload file call (was 16 in admin)
            formData.append('file', file);
            formData.append('uploaded_by', window.userPermissions.userId);
            
            // Include NLP analysis data
            const nlpCategory = $(this).data('nlp-category') || 'Uncategorized';
            const nlpScore = $(this).data('nlp-score') || 0;
            const nlpAnalysis = $(this).data('nlp-analysis') || '{}';
            
            formData.append('category_tag', nlpCategory);
            formData.append('category_score', nlpScore);
            formData.append('nlp_analysis', nlpAnalysis);
            
            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('button[type="submit"]').prop('disabled', true)
                        .html('<i class="fa fa-spinner fa-spin"></i> Uploading...');
                },
                success: function(response) {
                    if (response.status === 'SUCCESS') {
                        openModal('SUCCESS', 
                              'File uploaded successfully!<br><br>' +
                              '<strong>🤖 Auto-categorized as:</strong> ' + nlpCategory + '<br>' +
                              '<strong>📊 Confidence:</strong> ' + nlpScore.toFixed(1) + '%');
                        
                        resetUploadForm();
                        filesTable.ajax.reload();
                        
                        // Switch to file list tab
                        $('.tab-link[data-tab="file-list"]').click();
                    } else {
                        openModal('ERROR', 'Upload failed: ' + (response.msg || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Upload error:', error);
                    openModal('ERROR', 'Upload failed. Please try again.');
                },
                complete: function() {
                    $('button[type="submit"]').prop('disabled', false)
                        .html('<i class="fa fa-upload"></i> Upload File');
                }
            });
        });
        
        // Edit form submission
        $('#editFileForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                file_id: $('#editFileId').val(),
                file_title: $('#editFileTitle').val(),
                category_id: $('#editFileCategory').val(),
                description: $('#editFileDescription').val()
            };
            
            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                data: {
                    CALL: 22, // Update file
                    DATA: formData
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'SUCCESS') {
                        openModal('SUCCESS', 'File updated successfully!');
                        closeEditModal();
                        filesTable.ajax.reload();
                    } else {
                        openModal('ERROR', 'Update failed: ' + (response.msg || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Update error:', error);
                    openModal('ERROR', 'Update failed. Please try again.');
                }
            });
        });
    }
    
    function displayFilePreview(file) {
        $('#fileName').text(file.name);
        $('#fileSize').text(formatFileSize(file.size));
        $('#filePreview').show();
    }
    
    // Global functions for actions
    window.viewFile = function(fileId) {
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: {
                CALL: 23, // Get file details
                DATA: { file_id: fileId }
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'SUCCESS' && response.data) {
                    const file = response.data;
                    currentFileId = fileId;
                    
                    let details = '<div class="file-detail-grid">';
                    details += '<p><strong>File Name:</strong> ' + escapeHtml(file.file_name) + '</p>';
                    details += '<p><strong>Category:</strong> ' + escapeHtml(file.category_name || 'Uncategorized') + '</p>';
                    details += '<p><strong>File Type:</strong> ' + escapeHtml(file.file_type || 'N/A') + '</p>';
                    details += '<p><strong>File Size:</strong> ' + formatFileSize(file.file_size) + '</p>';
                    details += '<p><strong>Uploaded By:</strong> ' + escapeHtml(file.uploaded_by_name || 'Unknown') + '</p>';
                    details += '<p><strong>Upload Date:</strong> ' + formatDateTime(file.datetime_uploaded) + '</p>';
                    if (file.description) {
                        details += '<p><strong>Description:</strong> ' + escapeHtml(file.description) + '</p>';
                    }
                    if (file.google_drive_link) {
                        details += '<p><strong>Google Drive:</strong> <a href="' + file.google_drive_link + '" target="_blank">View in Drive</a></p>';
                    }
                    details += '</div>';
                    
                    $('#fileDetails').html(details);
                    $('#viewFileModal').show();
                } else {
                    openModal('ERROR', 'Failed to load file details');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading file details:', error);
                openModal('ERROR', 'Failed to load file details');
            }
        });
    };
    
    window.editFile = function(fileId) {
        if (!window.userPermissions.canEdit) {
            openModal('ERROR', 'You do not have permission to edit files');
            return;
        }
        
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: {
                CALL: 23, // Get file details
                DATA: { file_id: fileId }
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'SUCCESS' && response.data) {
                    const file = response.data;
                    
                    $('#editFileId').val(file.file_upload_id);
                    $('#editFileTitle').val(file.file_title || file.file_name);
                    $('#editFileCategory').val(file.category_id || '');
                    $('#editFileDescription').val(file.description || '');
                    
                    $('#editFileModal').show();
                } else {
                    openModal('ERROR', 'Failed to load file details');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading file details:', error);
                openModal('ERROR', 'Failed to load file details');
            }
        });
    };
    
    window.deleteFile = function(fileId, fileName) {
        if (!window.userPermissions.canDelete) {
            openModal('ERROR', 'You do not have permission to delete files');
            return;
        }
        
        currentFileId = fileId;
        currentFileName = fileName;
        
        $('#deleteFileName').text(fileName);
        $('#deleteReason').val('');
        $('#deleteFileModal').show();
    };
    
    window.confirmDelete = function() {
        const reason = $('#deleteReason').val().trim();
        
        if (!reason) {
            openModal('ERROR', 'Please provide a reason for deletion');
            return;
        }
        
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: {
                CALL: 24, // Delete file
                DATA: {
                    file_id: currentFileId,
                    reason: reason,
                    user_id: window.userPermissions.userId
                }
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'SUCCESS') {
                    openModal('SUCCESS', 'File deleted successfully');
                    closeDeleteModal();
                    filesTable.ajax.reload();
                } else {
                    openModal('ERROR', 'Delete failed: ' + (response.msg || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Delete error:', error);
                openModal('ERROR', 'Delete failed. Please try again.');
            }
        });
    };
    
    window.downloadFileById = function(fileId) {
        window.location.href = 'ajax.php?CALL=download&file_id=' + fileId;
    };
    
    window.downloadFile = function() {
        if (currentFileId) {
            window.location.href = 'ajax.php?CALL=download&file_id=' + currentFileId;
        }
    };
    
    window.applyFileFilters = function() {
        filesTable.ajax.reload();
    };
    
    window.clearFileFilters = function() {
        $('#filterCategory').val('');
        $('#filterDateFrom').val('');
        $('#filterDateTo').val('');
        filesTable.ajax.reload();
    };
    
    window.resetUploadForm = function() {
        $('#uploadForm')[0].reset();
        $('#filePreview').hide();
        $('#nlpPreview').hide();
        $('#fileName').text('');
        $('#fileSize').text('');
        $('#suggestedCategory').text('Will be detected on upload');
        $('#categoryConfidence').text('TBD');
    };
    
    // Modal functions
    window.closeViewModal = function() {
        $('#viewFileModal').hide();
        currentFileId = null;
    };
    
    window.closeEditModal = function() {
        $('#editFileModal').hide();
        $('#editFileForm')[0].reset();
    };
    
    window.closeDeleteModal = function() {
        $('#deleteFileModal').hide();
        currentFileId = null;
        currentFileName = null;
    };
    
    // Utility functions
    function formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    }
    
    function formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        
        const date = new Date(dateString);
        const options = { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        
        return date.toLocaleDateString('en-US', options);
    }
    
    function truncateText(text, maxLength) {
        if (!text) return '';
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    // Close modals when clicking outside
    $(window).on('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeViewModal();
            closeEditModal();
            closeDeleteModal();
        }
    });
});
