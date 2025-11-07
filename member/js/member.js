$(document).ready(function() {
    // Initialize member dashboard
    initializeMemberDashboard();
    
    // Load initial data
    loadDashboardStats();
    loadRecentActivity();
    loadFileCategories();
    loadActiveTasks();
    
    // Initialize upload functionality
    initializeFileUpload();
    
    // Initialize data tables
    initializeDataTables();
    
    // Initialize form handlers
    initializeFormHandlers();
});

function initializeMemberDashboard() {
    console.log('Member Dashboard initialized');
}

function loadDashboardStats() {
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { action: 'getDashboardStats' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#totalFiles').text(response.data.totalFiles);
                $('#activeTasks').text(response.data.activeTasks);
                $('#completedTasks').text(response.data.completedTasks);
                $('#categoryCount').text(response.data.categoryCount);
            }
        },
        error: function() {
            console.error('Failed to load dashboard stats');
        }
    });
}

function loadRecentActivity() {
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { action: 'getRecentActivity' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                populateRecentActivityTable(response.data);
            }
        },
        error: function() {
            console.error('Failed to load recent activity');
        }
    });
}

function populateRecentActivityTable(activities) {
    const tableBody = $('#recentActivityTable tbody');
    tableBody.empty();
    
    activities.forEach(function(activity) {
        const row = `
            <tr>
                <td>${formatDate(activity.date)}</td>
                <td>${activity.action}</td>
                <td>${activity.item}</td>
                <td>${activity.category || 'N/A'}</td>
                <td><span class="status-badge status-${activity.status}">${activity.status}</span></td>
            </tr>
        `;
        tableBody.append(row);
    });
    
    // Initialize DataTable if not already initialized
    if (!$.fn.DataTable.isDataTable('#recentActivityTable')) {
        $('#recentActivityTable').DataTable({
            pageLength: 5,
            searching: false,
            info: false,
            lengthChange: false
        });
    }
}

function loadFileCategories() {
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { action: 'getFileCategories' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                populateCategoryDropdowns(response.data);
            }
        },
        error: function() {
            console.error('Failed to load file categories');
        }
    });
}

function populateCategoryDropdowns(categories) {
    const dropdowns = [
        '#manualCategory',
        '#filesCategoryFilter',
        '#taskCategoryFilter'
    ];
    
    dropdowns.forEach(function(selector) {
        const dropdown = $(selector);
        const currentValue = dropdown.val();
        
        // Clear existing options (except first)
        dropdown.find('option:not(:first)').remove();
        
        // Add category options
        categories.forEach(function(category) {
            dropdown.append(`<option value="${category.id}">${category.category_name}</option>`);
        });
        
        // Restore selected value
        if (currentValue) {
            dropdown.val(currentValue);
        }
    });
}

function loadActiveTasks() {
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { action: 'getActiveTasks' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                populateTaskDropdown(response.data);
            }
        },
        error: function() {
            console.error('Failed to load active tasks');
        }
    });
}

function populateTaskDropdown(tasks) {
    const dropdown = $('#taskAssociation');
    
    // Clear existing options (except first)
    dropdown.find('option:not(:first)').remove();
    
    // Add task options
    tasks.forEach(function(task) {
        dropdown.append(`<option value="${task.id}">${task.title}</option>`);
    });
}

function initializeFileUpload() {
    // File input change handler for content analysis
    $('#uploadFile').on('change', function() {
        const file = this.files[0];
        if (file) {
            analyzeFileContent(file);
        } else {
            resetCategoryPreview();
        }
    });
    
    // Upload form submission
    $('#uploadFileBtn').on('click', function(e) {
        e.preventDefault();
        uploadFile();
    });
    
    // File drag and drop (optional enhancement)
    setupFileDragDrop();
}

function analyzeFileContent(file) {
    // Show loading state
    updateCategoryPreview('Analyzing...', '-', 'Detecting...', []);
    
    // Create FormData for file analysis
    const formData = new FormData();
    formData.append('file', file);
    formData.append('action', 'analyzeFileContent');
    
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                updateCategoryPreview(
                    data.detectedCategory,
                    data.confidence,
                    data.fileType,
                    data.keywords
                );
                
                // Set the detected category as default
                $('#manualCategory').val(data.categoryId);
                
                // Store detected category for upload
                $('#uploadFile').data('detectedCategory', data.categoryId);
            } else {
                updateCategoryPreview('Error', 'Low', 'Unknown', []);
            }
        },
        error: function() {
            updateCategoryPreview('Error', 'Low', 'Unknown', []);
        }
    });
}

function updateCategoryPreview(category, confidence, fileType, keywords) {
    $('#detectedCategory').text(category);
    $('#categoryConfidence').text(confidence);
    $('#detectedFileType').text(fileType);
    
    // Update keywords
    const keywordsContainer = $('#suggestedKeywords');
    keywordsContainer.empty();
    
    if (keywords && keywords.length > 0) {
        keywords.forEach(function(keyword) {
            keywordsContainer.append(`<span class="keyword-tag">${keyword}</span>`);
        });
    } else {
        keywordsContainer.append('<span class="no-keywords">No keywords detected</span>');
    }
}

function resetCategoryPreview() {
    updateCategoryPreview('Select a file to analyze', '-', '-', []);
    $('#manualCategory').val('');
    $('#uploadFile').removeData('detectedCategory');
}

function uploadFile() {
    const fileInput = $('#uploadFile')[0];
    const file = fileInput.files[0];
    
    if (!file) {
        showAlert('Please select a file to upload', 'error');
        return;
    }
    
    const description = $('#fileDescription').val();
    const manualCategory = $('#manualCategory').val();
    const taskAssociation = $('#taskAssociation').val();
    const detectedCategory = $('#uploadFile').data('detectedCategory');
    
    // Use manual category if selected, otherwise use detected category
    const categoryId = manualCategory || detectedCategory;
    
    if (!categoryId) {
        showAlert('Unable to determine file category. Please select manually.', 'error');
        return;
    }
    
    // Show upload progress
    showUploadProgress();
    
    // Create FormData
    const formData = new FormData();
    formData.append('file', file);
    formData.append('action', 'uploadFile');
    formData.append('description', description);
    formData.append('category_id', categoryId);
    if (taskAssociation) {
        formData.append('task_id', taskAssociation);
    }
    
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    updateUploadProgress(percentComplete);
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            hideUploadProgress();
            
            if (response.success) {
                showAlert(response.message, 'success');
                resetUploadForm();
                loadDashboardStats();
                loadRecentActivity();
                refreshMyFilesTable();
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function() {
            hideUploadProgress();
            showAlert('Upload failed. Please try again.', 'error');
        }
    });
}

function showUploadProgress() {
    $('#uploadProgress').show();
    updateUploadProgress(0);
    $('#progressStatus').text('Uploading file...');
}

function updateUploadProgress(percent) {
    $('#progressFill').css('width', percent + '%');
    $('#progressPercent').text(Math.round(percent) + '%');
    
    if (percent >= 100) {
        $('#progressStatus').text('Processing file...');
    }
}

function hideUploadProgress() {
    $('#uploadProgress').hide();
}

function resetUploadForm() {
    $('#uploadFile').val('');
    $('#fileDescription').val('');
    $('#manualCategory').val('');
    $('#taskAssociation').val('');
    resetCategoryPreview();
}

function setupFileDragDrop() {
    const uploadArea = $('#uploadFile').parent();
    
    uploadArea.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('drag-over');
    });
    
    uploadArea.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
    });
    
    uploadArea.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            $('#uploadFile')[0].files = files;
            analyzeFileContent(files[0]);
        }
    });
}

function initializeDataTables() {
    // My Files Table
    if (!$.fn.DataTable.isDataTable('#myFilesTable')) {
        window.myFilesTable = $('#myFilesTable').DataTable({
            ajax: {
                url: 'ajax.php',
                type: 'POST',
                data: function(d) {
                    return {
                        action: 'getMyFiles',
                        category: $('#filesCategoryFilter').val(),
                        type: $('#filesTypeFilter').val()
                    };
                },
                dataSrc: function(json) {
                    return json.success ? json.data : [];
                }
            },
            columns: [
                {
                    data: 'original_name',
                    render: function(data, type, row) {
                        return `<span class="file-name" title="${data}">${data}</span>`;
                    }
                },
                { data: 'category_name' },
                {
                    data: 'file_type',
                    render: function(data) {
                        return getFileTypeIcon(data) + ' ' + getReadableFileType(data);
                    }
                },
                {
                    data: 'file_size',
                    render: function(data) {
                        return formatFileSize(data);
                    }
                },
                {
                    data: 'upload_date',
                    render: function(data) {
                        return formatDate(data);
                    }
                },
                {
                    data: 'task_title',
                    render: function(data) {
                        return data || 'None';
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                            <button class="btn-sm btn-primary" onclick="downloadFile(${row.id})" title="Download">
                                📥
                            </button>
                            <button class="btn-sm btn-danger" onclick="deleteFile(${row.id})" title="Delete">
                                🗑️
                            </button>
                        `;
                    },
                    orderable: false
                }
            ],
            pageLength: 10,
            order: [[4, 'desc']] // Sort by upload date descending
        });
    }
    
    // Task Submissions Table
    if (!$.fn.DataTable.isDataTable('#taskSubmissionsTable')) {
        window.taskSubmissionsTable = $('#taskSubmissionsTable').DataTable({
            ajax: {
                url: 'ajax.php',
                type: 'POST',
                data: function(d) {
                    return {
                        action: 'getTaskSubmissions',
                        status: $('#taskStatusFilter').val(),
                        category: $('#taskCategoryFilter').val()
                    };
                },
                dataSrc: function(json) {
                    return json.success ? json.data : [];
                }
            },
            columns: [
                { data: 'task_title' },
                { data: 'category_name' },
                {
                    data: 'deadline',
                    render: function(data) {
                        return formatDate(data);
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        return `<span class="status-badge status-${data}">${data}</span>`;
                    }
                },
                {
                    data: 'file_name',
                    render: function(data) {
                        return data || 'No file submitted';
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        let feedback = row.feedback || 'No feedback';
                        let grade = row.grade || 'Not graded';
                        return `${feedback}<br><strong>Grade:</strong> ${grade}`;
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        let actions = '';
                        if (row.status === 'pending' || row.status === 'rejected') {
                            actions += `<button class="btn-sm btn-primary" onclick="openSubmitTaskModal(${row.task_id})">Submit/Resubmit</button>`;
                        }
                        if (row.file_path) {
                            actions += ` <button class="btn-sm btn-secondary" onclick="downloadSubmission('${row.file_path}')">Download</button>`;
                        }
                        return actions || 'No actions';
                    },
                    orderable: false
                }
            ],
            pageLength: 10,
            order: [[2, 'asc']] // Sort by deadline ascending
        });
    }
}

function initializeFormHandlers() {
    // File filters
    $('#applyFileFilters').on('click', function() {
        if (window.myFilesTable) {
            window.myFilesTable.ajax.reload();
        }
    });
    
    $('#clearFileFilters').on('click', function() {
        $('#filesCategoryFilter').val('');
        $('#filesTypeFilter').val('');
        if (window.myFilesTable) {
            window.myFilesTable.ajax.reload();
        }
    });
    
    // Task filters
    $('#applyTaskFilters').on('click', function() {
        if (window.taskSubmissionsTable) {
            window.taskSubmissionsTable.ajax.reload();
        }
    });
    
    $('#clearTaskFilters').on('click', function() {
        $('#taskStatusFilter').val('');
        $('#taskCategoryFilter').val('');
        if (window.taskSubmissionsTable) {
            window.taskSubmissionsTable.ajax.reload();
        }
    });
    
    // Password change form
    $('#changePassword').on('click', function(e) {
        e.preventDefault();
        changePassword();
    });
}

function refreshMyFilesTable() {
    if (window.myFilesTable) {
        window.myFilesTable.ajax.reload();
    }
}

function downloadFile(fileId) {
    window.open(`ajax.php?action=downloadFile&file_id=${fileId}`, '_blank');
}

function deleteFile(fileId) {
    if (confirm('Are you sure you want to delete this file?')) {
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: {
                action: 'deleteFile',
                file_id: fileId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    refreshMyFilesTable();
                    loadDashboardStats();
                } else {
                    showAlert(response.message, 'error');
                }
            },
            error: function() {
                showAlert('Failed to delete file', 'error');
            }
        });
    }
}

function changePassword() {
    const currentPassword = $('#currentPassword').val();
    const newPassword = $('#newPassword').val();
    const confirmPassword = $('#confirmPassword').val();
    
    if (!currentPassword || !newPassword || !confirmPassword) {
        showAlert('All password fields are required', 'error');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showAlert('New passwords do not match', 'error');
        return;
    }
    
    if (newPassword.length < 6) {
        showAlert('Password must be at least 6 characters long', 'error');
        return;
    }
    
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: {
            action: 'changePassword',
            current_password: currentPassword,
            new_password: newPassword,
            confirm_password: confirmPassword
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                $('#currentPassword, #newPassword, #confirmPassword').val('');
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function() {
            showAlert('Failed to change password', 'error');
        }
    });
}

// Utility Functions
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function getFileTypeIcon(mimeType) {
    if (mimeType.includes('pdf')) return '📄';
    if (mimeType.includes('word') || mimeType.includes('document')) return '📝';
    if (mimeType.includes('sheet') || mimeType.includes('excel')) return '📊';
    if (mimeType.includes('presentation') || mimeType.includes('powerpoint')) return '📊';
    if (mimeType.includes('image')) return '🖼️';
    if (mimeType.includes('zip') || mimeType.includes('rar')) return '📦';
    return '📎';
}

function getReadableFileType(mimeType) {
    const types = {
        'application/pdf': 'PDF',
        'application/msword': 'Word',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'Word',
        'application/vnd.ms-excel': 'Excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'Excel',
        'application/vnd.ms-powerpoint': 'PowerPoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'PowerPoint',
        'image/jpeg': 'JPEG',
        'image/png': 'PNG',
        'image/gif': 'GIF',
        'application/zip': 'ZIP',
        'application/x-rar-compressed': 'RAR'
    };
    
    return types[mimeType] || 'File';
}

function showAlert(message, type) {
    // Create alert element
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    const alertHtml = `
        <div class="alert ${alertClass}" style="position: fixed; top: 20px; right: 20px; z-index: 1000; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            ${message}
            <button type="button" class="close" onclick="$(this).parent().fadeOut()">×</button>
        </div>
    `;
    
    $('body').append(alertHtml);
    
    // Auto remove after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

function openModal(modalId) {
    $('#' + modalId).show();
}

function closeModal(modalId) {
    $('#' + modalId).hide();
}