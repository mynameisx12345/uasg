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
                    data: "category_name",
                    defaultContent: "Uncategorized"
                },
                { 
                    data: "file_type",
                    render: function(data) {
                        return data ? data.toUpperCase() : 'N/A';
                    }
                },
                { 
                    data: "file_size",
                    render: function(data) {
                        return formatFileSize(data);
                    }
                },
                { 
                    data: "uploaded_by_name",
                    defaultContent: "Unknown"
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
                    
                    // Populate upload dropdown
                    let uploadOptions = '<option value="">Select Category</option>';
                    categories.forEach(cat => {
                        uploadOptions += '<option value="' + cat.file_category_id + '">' + 
                                       escapeHtml(cat.file_category) + '</option>';
                    });
                    $('#uploadCategory, #editFileCategory').html(uploadOptions);
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
                }
            });
        }
        
        // Upload form submission
        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('CALL', 21); // Upload file call
            formData.append('uploaded_by', window.userPermissions.userId);
            
            // Validate file size (10MB max)
            const file = fileInput.files[0];
            if (file && file.size > 10 * 1024 * 1024) {
                alert('File size exceeds 10MB limit!');
                return;
            }
            
            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Uploading...');
                },
                success: function(response) {
                    if (response.status === 'SUCCESS') {
                        alert('File uploaded successfully!');
                        resetUploadForm();
                        filesTable.ajax.reload();
                        
                        // Switch to file list tab
                        $('.tab-link[data-tab="file-list"]').click();
                    } else {
                        alert('Upload failed: ' + (response.msg || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Upload error:', error);
                    alert('Upload failed. Please try again.');
                },
                complete: function() {
                    $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-upload"></i> Upload File');
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
                        alert('File updated successfully!');
                        closeEditModal();
                        filesTable.ajax.reload();
                    } else {
                        alert('Update failed: ' + (response.msg || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Update error:', error);
                    alert('Update failed. Please try again.');
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
                    alert('Failed to load file details');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading file details:', error);
                alert('Failed to load file details');
            }
        });
    };
    
    window.editFile = function(fileId) {
        if (!window.userPermissions.canEdit) {
            alert('You do not have permission to edit files');
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
                    alert('Failed to load file details');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading file details:', error);
                alert('Failed to load file details');
            }
        });
    };
    
    window.deleteFile = function(fileId, fileName) {
        if (!window.userPermissions.canDelete) {
            alert('You do not have permission to delete files');
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
            alert('Please provide a reason for deletion');
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
                    alert('File deleted successfully');
                    closeDeleteModal();
                    filesTable.ajax.reload();
                } else {
                    alert('Delete failed: ' + (response.msg || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Delete error:', error);
                alert('Delete failed. Please try again.');
            }
        });
    };
    
    window.downloadFileById = function(fileId) {
        window.location.href = 'ajax.php?CALL=25&file_id=' + fileId;
    };
    
    window.downloadFile = function() {
        if (currentFileId) {
            window.location.href = 'ajax.php?CALL=25&file_id=' + currentFileId;
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
        $('#fileName').text('');
        $('#fileSize').text('');
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
