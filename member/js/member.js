$(document).ready(function() {
    // Initialize member dashboard
    initializeMemberDashboard();
    
    // Load initial data
    loadDashboardStats();
    loadRecentActivity();
    
    // Initialize upload functionality
    initializeFileUpload();

    // Initialize forms
    initializeFormHandlers();

    // Populate task category filter from user's own uploaded files
    loadTaskCategoryFilter();
});

function loadTaskCategoryFilter() {
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { CALL: 21 },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'SUCCESS' && response.data.length > 0) {
                var opts = '<option value="">All Categories</option>';
                response.data.forEach(function(cat) {
                    opts += '<option value="' + $('<div>').text(cat.category_tag).html() + '">'
                          + $('<div>').text(cat.category_tag).html()
                          + ' (' + cat.file_count + ')</option>';
                });
                $('#taskCategoryFilter').html(opts);
            }
        }
    });
}

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
            lengthChange: false,
            responsive: true
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
                        CALL: 10,
                        status:   $('#taskStatusFilter').val()   || '',
                        category: $('#taskCategoryFilter').val() || ''
                    };
                },
                dataSrc: function(json) {
                    return json.data || [];
                }
            },
            responsive: true,
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
                        return `<span class="status-badge status-${(data||'').toLowerCase()}">${data || 'Not submitted'}</span>`;
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
                        const canSubmit = !row.task_submission_id || row.check_status === 'rejected';
                        const canDownload = !!row.file_upload_id;

                        if (!canSubmit && !canDownload) {
                            return 'No actions available';
                        }

                        let menuItems = '';
                        if (canSubmit) {
                            menuItems += `<button onclick="openSubmitTaskModal(${row.task_id})"><i class="fas fa-upload"></i> Submit / Resubmit</button>`;
                        }
                        if (canDownload) {
                            menuItems += `<button onclick="downloadFile(${row.file_upload_id})"><i class="fas fa-download"></i> Download</button>`;
                        }

                        return `
                            <div class="dt-action-dropdown">
                                <button class="dt-action-toggle"><i class="fas fa-ellipsis-v"></i> Actions ▾</button>
                                <div class="dt-action-menu">${menuItems}</div>
                            </div>`;
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

    // Eye-toggle: show/hide password for all toggle buttons
    $(document).on('click', '.pwd-toggle-btn', function() {
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        const isPassword = input.attr('type') === 'password';
        input.attr('type', isPassword ? 'text' : 'password');
        $(this).find('.eye-show').toggle(!isPassword);
        $(this).find('.eye-hide').toggle(isPassword);
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
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'ajax.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.responseType = 'blob';

    xhr.onload = function() {
        if (xhr.status !== 200) {
            if(typeof openNotificationModal === 'function') {
                openNotificationModal('Failed to download file. Please try again.', 'error');
            }
            return;
        }

        // Detect if server returned a JSON error instead of a file
        var contentType = xhr.getResponseHeader('Content-Type') || '';
        if (contentType.indexOf('application/json') !== -1) {
            var reader = new FileReader();
            reader.onload = function() {
                try {
                    var err = JSON.parse(reader.result);
                    if(typeof openNotificationModal === 'function') {
                        openNotificationModal(err.msg || 'Download failed.', 'error');
                    }
                } catch(e) {}
            };
            reader.readAsText(xhr.response);
            return;
        }

        var blob = xhr.response;
        var url = URL.createObjectURL(blob);

        // Extract filename from Content-Disposition header
        var disposition = xhr.getResponseHeader('Content-Disposition') || '';
        var filename = 'download';
        var match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
        if (match && match[1]) {
            filename = match[1].replace(/['"]/g, '');
        }

        var a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        setTimeout(function() {
            URL.revokeObjectURL(url);
            document.body.removeChild(a);
        }, 200);
    };

    xhr.onerror = function() {
        if(typeof openNotificationModal === 'function') {
            openNotificationModal('Failed to download file. Please try again.', 'error');
        }
    };

    xhr.send('CALL=20&file_id=' + encodeURIComponent(fileId));
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
    // Delegate to the fully-featured modal defined in modals.php
    if (typeof openSubmissionModal === 'function') {
        openSubmissionModal(taskId);
    } else {
        // Fallback: set the hidden input and open directly
        var el = document.getElementById('submit_task_id');
        if (el) el.value = taskId;
        openModal('submitTaskModal');
    }
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
            CALL: 14,
            current_password: currentPassword,
            new_password: newPassword,
            confirm_password: confirmPassword
        },
        dataType: 'json',
        success: function(response) {
            if (response.success === true || response.status === 'SUCCESS') {
                showAlert(response.msg || 'Password changed successfully', 'success');
                $('#currentPassword, #newPassword, #confirmPassword').val('');
            } else {
                showAlert(response.msg || 'Failed to change password', 'error');
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
    // Use the notification modal instead of inline alerts
    openNotificationModal(message, type);
}

function openModal(modalId) {
    $('#' + modalId).show();
}

function closeModal(modalId) {
    $('#' + modalId).hide();
}
