$(document).ready(function() {
    // Initialize member dashboard
    initializeMemberDashboard();
    
    // Load initial data
    loadDashboardStats();
    loadRecentActivity();
    
    // Initialize upload functionality
    initializeFileUpload();

    // Initialize tables
    //initializeDataTables();
    
    // Initialize forms
    initializeFormHandlers();
});

function initializeMemberDashboard() {
    console.log('Member Dashboard initialized');
}

/*function loadDashboardStats() {
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { CALL: 1 },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'SUCCESS') {
                $('#totalFiles').html(response.data.total_files);
                $('#activeTasks').html(response.data.active_tasks);
                $('#completedTasks').html(response.data.completed_tasks);
                $('#categoryCount').html(response.data.categories_used);
            }
        },
        error: function() {
            console.error('Failed to load dashboard stats');
        }
    });
}*/

function loadDashboardStats() {
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { CALL: 1 },
        dataType: 'json',
        success: function(response) {

            // Check actual "success" key
            if (response.success === true) {

                $('#totalFiles').html(response.data.totalFiles);
                $('#activeTasks').html(response.data.activeTasks);
                $('#completedTasks').html(response.data.completedTasks);
                $('#categoryCount').html(response.data.categoryCount);
            }
        },
        error: function(xhr) {
            console.error('Failed to load dashboard stats', xhr.responseText);
        }
    });
}

function loadRecentActivity() {
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { CALL: 2 },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'SUCCESS') {
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
                <td>${activity.activity_type}</td>
                <td>${activity.item_name}</td>
                <td>${activity.category || 'N/A'}</td>
                <td><span class="status-badge status-${activity.status}">${activity.status}</span></td>
            </tr>
        `;
        tableBody.append(row);
    });

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
        data: { CALL: 6 },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'SUCCESS') {
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
        
        dropdown.find('option:not(:first)').remove();
        
        categories.forEach(function(category) {
            dropdown.append(`<option value="${category.file_category_id}">${category.file_category}</option>`);
        });

        if (currentValue) {
            dropdown.val(currentValue);
        }
    });
}

function loadActiveTasks() {
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { CALL: 10 },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'SUCCESS') {
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
    dropdown.find('option:not(:first)').remove();

    tasks.forEach(function(task) {
        dropdown.append(`<option value="${task.task_id}">${task.task_title}</option>`);
    });
}

function initializeFileUpload() {
    $('#uploadFileBtn').on('click', function(e) {
        e.preventDefault();
        uploadFile();
    });

    $('#uploadMultipleBtn').on('click', function(e) {
        e.preventDefault();
        uploadMultipleFiles();
    });
}

function updateCategoryPreview(category, confidence, fileType, keywords) {
    $('#detectedCategory').text(category);
    $('#categoryConfidence').text(confidence);
    $('#detectedFileType').text(fileType);

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

/* ===========================================================
    FILE UPLOAD
   =========================================================== */
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

    const categoryId = manualCategory || detectedCategory || null;

    showUploadProgress();

    const formData = new FormData();
    formData.append('file', file);
    formData.append('CALL', 3);
    formData.append('description', description);

    if (categoryId) formData.append('category_id', categoryId);
    if (taskAssociation) formData.append('task_id', taskAssociation);

    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        xhr: function() {
            const xhr = new XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    updateUploadProgress((e.loaded / e.total) * 100);
                }
            });
            return xhr;
        },
        success: function(response) {
            hideUploadProgress();

            if (response.status === 'SUCCESS') {
                let message = response.msg;

                if (response.nlp_analysis) {
                    message += '\n\n🤖 Auto-categorized as: ' + response.nlp_analysis.category +
                               '\n📊 Confidence: ' + response.nlp_analysis.confidence.toFixed(1) + '%';
                }

                showAlert(message, 'success');
                resetUploadForm();
                loadDashboardStats();
                loadRecentActivity();
                refreshMyFilesTable();
            } else {
                showAlert(response.msg, 'error');
            }
        },
        error: function() {
            hideUploadProgress();
            showAlert('Upload failed. Please try again.', 'error');
        }
    });
}

function uploadMultipleFiles() {
    const fileInput = $('#multipleFiles')[0];
    const files = fileInput.files;

    if (!files || files.length === 0) {
        showAlert('Please select files to upload', 'error');
        return;
    }

    const description = $('#multipleDescription').val();
    const categoryId = $('#multipleCategory').val();

    showUploadProgress();

    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    formData.append('CALL', 15);
    formData.append('description', description);
    formData.append('category', categoryId);

    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            hideUploadProgress();

            if (response.status === 'SUCCESS') {
                showAlert(response.msg, 'success');
                $('#multipleFiles').val('');
                $('#multipleDescription').val('');
                $('#multipleCategory').val('');
                loadDashboardStats();
                loadRecentActivity();
                refreshMyFilesTable();
            } else {
                showAlert(response.msg, 'error');
            }
        },
        error: function() {
            hideUploadProgress();
            showAlert('Multiple file upload failed. Please try again.', 'error');
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

/* ===========================================================
    DATATABLES
   =========================================================== */
function initializeDataTables() {

    // MY FILES TABLE
    if (!$.fn.DataTable.isDataTable('#myFilesTable')) {
        window.myFilesTable = $('#myFilesTable').DataTable({
            ajax: {
                url: 'ajax.php',
                type: 'POST',
                data: function(d) {
                    return {
                        CALL: 7,
                        category: $('#filesCategoryFilter').val(),
                        type: $('#filesTypeFilter').val()
                    };
                },
                dataSrc: function(json) {
                    return json.status === 'SUCCESS' ? json.data : [];
                }
            },
            columns: [
                {
                    data: 'original_name',
                    render: function(data, type, row) {
                        return `<span class="file-name" title="${data}">${row.file_name}</span>`;
                    }
                },
                { data: 'file_category' },
                {
                    data: 'mime_type',
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
                    data: 'datetime_uploaded',
                    render: function(data) {
                        return formatDate(data);
                    }
                },
                {
                    data: 'description',
                    render: function(data) {
                        return data || 'No description';
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                            <button class="btn-sm btn-primary" onclick="downloadFile(${row.file_upload_id})">📥</button>
                            <button class="btn-sm btn-secondary" onclick="editFileInfo(${row.file_upload_id})">✏️</button>
                            <button class="btn-sm btn-danger" onclick="deleteFile(${row.file_upload_id})">🗑️</button>
                        `;
                    },
                    orderable: false
                }
            ],
            pageLength: 10,
            order: [[4, 'desc']]
        });
    }

    // TASK SUBMISSIONS TABLE
    if (!$.fn.DataTable.isDataTable('#taskSubmissionsTable')) {
        window.taskSubmissionsTable = $('#taskSubmissionsTable').DataTable({
            ajax: {
                url: 'ajax.php',
                type: 'POST',
                data: function(d) {
                    return {
                        CALL: 12,
                        status: $('#taskStatusFilter').val(),
                        category: $('#taskCategoryFilter').val()
                    };
                },
                dataSrc: function(json) {
                    return json.status === 'SUCCESS' ? json.data : [];
                }
            },
            columns: [
                { data: 'task_title' },
                { data: 'task_category' },
                {
                    data: 'task_deadline',
                    render: function(data) {
                        return formatDate(data);
                    }
                },
                {
                    data: 'check_status',
                    render: function(data) {
                        return `<span class="status-badge status-${data}">${data || 'Not submitted'}</span>`;
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
                        let actions = '';
                        if (!row.task_submission_id || row.check_status === 'rejected') {
                            actions += `<button class="btn-sm btn-primary" onclick="openSubmitTaskModal(${row.task_id})">Submit/Resubmit</button>`;
                        }
                        if (row.file_upload_id) {
                            actions += ` <button class="btn-sm btn-secondary" onclick="downloadFile(${row.file_upload_id})">Download</button>`;
                        }
                        return actions || 'No actions available';
                    },
                    orderable: false
                }
            ],
            pageLength: 10,
            order: [[2, 'asc']]
        });
    }
}

/* ===========================================================
    FILTER HANDLERS
   =========================================================== */
function initializeFormHandlers() {
    $('#applyFileFilters').on('click', function() {
        if (window.myFilesTable) window.myFilesTable.ajax.reload();
    });

    $('#clearFileFilters').on('click', function() {
        $('#filesCategoryFilter').val('');
        $('#filesTypeFilter').val('');
        if (window.myFilesTable) window.myFilesTable.ajax.reload();
    });

    $('#applyTaskFilters').on('click', function() {
        if (window.taskSubmissionsTable) window.taskSubmissionsTable.ajax.reload();
    });

    $('#clearTaskFilters').on('click', function() {
        $('#taskStatusFilter').val('');
        $('#taskCategoryFilter').val('');
        if (window.taskSubmissionsTable) window.taskSubmissionsTable.ajax.reload();
    });

    $('#changePassword').on('click', function(e) {
        e.preventDefault();
        changePassword();
    });
}

/* ===========================================================
    FILE ACTIONS
   =========================================================== */
function refreshMyFilesTable() {
    if (window.myFilesTable) {
        window.myFilesTable.ajax.reload();
    }
}

function downloadFile(fileId) {
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: {
            CALL: 9,
            file_id: fileId
        },
        xhrFields: { responseType: 'blob' },
        success: function(data, status, xhr) {
            const blob = new Blob([data]);
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = '';
            document.body.appendChild(a);
            a.click();
            URL.revokeObjectURL(url);
        },
        error: function() {
            showAlert('Failed to download file', 'error');
        }
    });
}

function editFileInfo(fileId) {
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { CALL: 8, file_id: fileId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'SUCCESS') {
                const file = response.data;
                $('#editFileId').val(file.file_upload_id);
                $('#editFileCategory').val(file.file_category_id);
                $('#editFileDescription').val(file.description);
                $('#editFileTags').val(file.tags);
                $('#editFileModal').show();
            }
        }
    });
}

function saveFileInfo() {
    const fileId = $('#editFileId').val();
    const category = $('#editFileCategory').val();
    const description = $('#editFileDescription').val();
    const tags = $('#editFileTags').val();

    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: {
            CALL: 14,
            file_id: fileId,
            category: category,
            description: description,
            tags: tags
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'SUCCESS') {
                showAlert(response.msg, 'success');
                $('#editFileModal').hide();
                refreshMyFilesTable();
            } else {
                showAlert(response.msg, 'error');
            }
        },
        error: function() {
            showAlert('Failed to update file information', 'error');
        }
    });
}

function deleteFile(fileId) {
    if (!confirm('Are you sure you want to delete this file?')) return;

    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { CALL: 13, file_id: fileId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'SUCCESS') {
                showAlert(response.msg, 'success');
                refreshMyFilesTable();
                loadDashboardStats();
            } else {
                showAlert(response.msg, 'error');
            }
        },
        error: function() {
            showAlert('Failed to delete file', 'error');
        }
    });
}

/* ===========================================================
    TASK SUBMISSION
   =========================================================== */
function openSubmitTaskModal(taskId) {
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { CALL: 7, suitable_for_task: taskId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'SUCCESS') {
                const fileSelect = $('#submitFileSelect');
                fileSelect.empty().append('<option value="">Select a file</option>');

                response.data.forEach(function(file) {
                    fileSelect.append(`<option value="${file.file_upload_id}">${file.file_name}</option>`);
                });

                $('#submitTaskId').val(taskId);
                $('#submitTaskModal').show();
            }
        }
    });
}

function submitTaskFile() {
    const taskId = $('#submitTaskId').val();
    const fileId = $('#submitFileSelect').val();

    if (!fileId) {
        showAlert('Please select a file to submit', 'error');
        return;
    }

    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: {
            CALL: 11,
            task_id: taskId,
            file_id: fileId
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'SUCCESS') {
                showAlert(response.msg, 'success');
                $('#submitTaskModal').hide();
                if (window.taskSubmissionsTable) {
                    window.taskSubmissionsTable.ajax.reload();
                }
            } else {
                showAlert(response.msg, 'error');
            }
        },
        error: function() {
            showAlert('Failed to submit task', 'error');
        }
    });
}

/* ===========================================================
    PASSWORD CHANGE
   =========================================================== */
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
            CALL: 18,
            current_password: currentPassword,
            new_password: newPassword,
            confirm_password: confirmPassword
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'SUCCESS') {
                showAlert(response.msg, 'success');
                $('#currentPassword, #newPassword, #confirmPassword').val('');
            } else {
                showAlert(response.msg, 'error');
            }
        },
        error: function() {
            showAlert('Failed to change password', 'error');
        }
    });
}

/* ===========================================================
    UTILITIES
   =========================================================== */
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
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    const alertHtml = `
        <div class="alert ${alertClass}" style="position: fixed; top: 20px; right: 20px; z-index: 1000; padding: 15px; border-radius: 4px;">
            ${message}
            <button type="button" class="close" onclick="$(this).parent().fadeOut()">×</button>
        </div>
    `;

    $('body').append(alertHtml);
    setTimeout(() => $('.alert').fadeOut(), 5000);
}

function openModal(modalId) {
    $('#' + modalId).show();
}

function closeModal(modalId) {
    $('#' + modalId).hide();
}
