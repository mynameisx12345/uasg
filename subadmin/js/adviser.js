$(document).ready(function(){
    // Initialize DataTables variables
    let recentActivityTable;
    let tasksTable;
    let reportsTable;
    
    // Initialize Dashboard
    initializeDashboard();
    
    // Initialize Tab Switching
    initializeTabSwitching();
    
    // Initialize Form Handlers
    initializeFormHandlers();
    
    // Initialize Filter Handlers
    initializeFilterHandlers();
    
    function initializeDashboard() {
        // Load dashboard statistics
        loadDashboardStats();
        
        // Initialize Recent Activity Table
        recentActivityTable = $("#recentActivityTable").DataTable({
            ajax: {
                url: 'ajax.php',
                type: 'post',
                data: {
                    CALL: 2 // Get recent activity
                },
                dataSrc: 'data'
            },
            responsive: true,
            paging: true,
            pageLength: 10,
            columns: [
                { data: "date" },
                { data: "student_name" },
                { data: "action" },
                { data: "task_title" },
                { data: "status" }
            ],
            order: [[0, 'desc']],
            language: {
                emptyTable: "No recent activity found"
            }
        });
    }
    
    function initializeTabSwitching() {
        // Enhanced tab switching with data loading
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
            
            // Load tab-specific data
            loadTabData(targetTab);
        });
    }
    
    function loadTabData(tab) {
        switch(tab) {
            case 'dashboard':
                loadDashboardStats();
                if(recentActivityTable) {
                    recentActivityTable.ajax.reload();
                }
                break;
                
            case 'task-management':
                initializeTasksTable();
                loadDropdownData();
                break;
                
            case 'reports':
                initializeReportsTable();
                loadReportFilters();
                break;
                
            case 'account-management':
                // Account management doesn't need additional data loading
                break;
        }
    }
    
    function loadDashboardStats() {
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            data: {
                CALL: 1 // Get dashboard stats
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'SUCCESS') {
                    $('#totalTasks').text(response.data.total_tasks);
                    $('#pendingReviews').text(response.data.pending_reviews);
                    $('#completedTasks').text(response.data.completed_tasks);
                    $('#activeMembers').text(response.data.active_members);
                    $('#unreadNotifications').text(response.data.unread_notifications);
                }
            },
            error: function() {
                console.error('Failed to load dashboard statistics');
            }
        });
    }
    
    function initializeTasksTable() {
        if(!tasksTable) {
            tasksTable = $("#tasksTable").DataTable({
                ajax: {
                    url: 'ajax.php',
                    type: 'post',
                    data: {
                        CALL: 40 // Get all tasks
                    },
                    dataSrc: 'data'
                },
                destroy: true,
                responsive: true,
                paging: true,
                pageLength: 10,
                columns: [
                    { data: "task_id" },
                    { data: "task_title" },
                    { data: "task_category" },
                    { data: "task_description" },
                    { data: "task_deadline" },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return (row.submission_count || 0) + ' / ' + (row.approved_count || 0);
                        }
                    },
                    { 
                        data: "task_status",
                        render: function(data) {
                            return data === 'active' ? '<span class="status-badge status-pending">Active</span>' : 
                                   '<span class="status-badge status-completed">Completed</span>';
                        }
                    },
                    {
                        data: 'task_id',
                        render: function(id) {
                            return `
                                <div class="dropdown-container">
                                    <button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
                                    <div class="dropdown-menu">
                                        <button class="dropdown-item edit editTaskBtn" data-id="${id}">Edit</button>
                                        <button class="dropdown-item delete deleteTaskBtn" data-id="${id}">Delete</button>
                                    </div>
                                </div>
                            `;
                        }
                    }
                ],
                language: {
                    emptyTable: "No tasks found"
                }
            });
        } else {
            tasksTable.ajax.reload();
        }
    }
    
    function initializeReportsTable() {
        if(!reportsTable) {
            reportsTable = $("#reportsTable").DataTable({
                ajax: {
                    url: 'ajax.php',
                    type: 'post',
                    data: function(d) {
                        return {
                            CALL: 41, // Get all submissions
                            task_id: $('#reportTaskFilter').val(),
                            status: $('#reportStatusFilter').val()
                        };
                    },
                    dataSrc: 'data'
                },
                responsive: true,
                paging: true,
                pageLength: 10,
                columns: [
                    { data: "task_submission_id" },
                    { data: "task_title" },
                    { data: "student_name" },
                    { data: "file_name" },
                    { data: "submitted_at" },
                    { 
                        data: "check_status",
                        render: function(status) {
                            const statusMap = {
                                'Pending': '<span class="status-badge status-pending">Pending</span>',
                                'Approved': '<span class="status-badge status-completed">Approved</span>',
                                'Rejected': '<span class="status-badge status-overdue">Rejected</span>'
                            };
                            return statusMap[status] || status;
                        }
                    },
                    {
                        data: 'task_submission_id',
                        render: function(id, type, row) {
                            return `
                                <div class="dropdown-container">
                                    <button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
                                    <div class="dropdown-menu">
                                        <button class="dropdown-item view viewSubmissionBtn" data-id="${id}">View Details</button>
                                        ${row.check_status === 'Pending' ? `
                                            <button class="dropdown-item approve approveBtn" data-id="${id}">Approve</button>
                                        ` : ''}
                                    </div>
                                </div>
                            `;
                        }
                    }
                ],
                language: {
                    emptyTable: "No submissions found"
                }
            });
        } else {
            reportsTable.ajax.reload();
        }
    }
    
    function initializeFormHandlers() {
        // Task Creation Form
        $('#saveTask').click(function() {
            const taskTitle = $('#taskTitle').val().trim();
            const taskCategory = $('#taskCategory').val();
            const taskDescription = $('#taskDescription').val().trim();
            const taskDeadline = $('#taskDeadline').val();
            
            if(!taskTitle || !taskCategory || !taskDeadline) {
                openModal("ERROR", "Please fill in all required fields");
                return;
            }
            
            const taskData = {
                task_title: taskTitle,
                task_category_id: taskCategory,
                task_description: taskDescription,
                task_deadline: taskDeadline
            };
            
            $.ajax({
                url: 'ajax.php',
                type: 'post',
                data: {
                    CALL: 'create_task',
                    DATA: taskData
                },
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'SUCCESS') {
                        showNotification('Task created successfully', 'success');
                        $('#createTaskForm')[0].reset();
                        if(tasksTable) {
                            tasksTable.ajax.reload();
                        }
                    } else {
                        showNotification(response.msg || 'Failed to create task', 'error');
                    }
                },
                error: function() {
                    showNotification('Error creating task', 'error');
                }
            });
        });
        
        // Password Change Form
        $('#changePassword').click(function() {
            const currentPassword = $('#currentPassword').val();
            const newPassword = $('#newPassword').val();
            const confirmPassword = $('#confirmPassword').val();
            
            if(!currentPassword || !newPassword || !confirmPassword) {
                openModal("ERROR", "Please fill in all password fields");
                return;
            }
            
            if(newPassword !== confirmPassword) {
                openModal("ERROR", "New passwords do not match");
                return;
            }
            
            if(newPassword.length < 6) {
                openModal("ERROR", "Password must be at least 6 characters long");
                return;
            }
            
            const passwordData = {
                current_password: currentPassword,
                new_password: newPassword,
                confirm_password: confirmPassword
            };
            
            saveData(18, passwordData); // Change password
        });
        
        // Task Action Handlers
        $(document).on('click', '.editTaskBtn', function() {
            const taskId = $(this).data('id');
            
            $.ajax({
                url: 'ajax.php',
                type: 'post',
                data: {
                    CALL: 42,
                    task_id: taskId
                },
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'SUCCESS') {
                        // Populate edit form (you can create an edit modal)
                        showNotification('Edit functionality coming soon', 'info');
                    } else {
                        showNotification(response.msg || 'Failed to load task', 'error');
                    }
                },
                error: function() {
                    showNotification('Error loading task details', 'error');
                }
            });
        });
        
        $(document).on('click', '.deleteTaskBtn', function() {
            const taskId = $(this).data('id');
            if(confirm('Are you sure you want to delete this task? All submissions will also be deleted.')) {
                $.ajax({
                    url: 'ajax.php',
                    type: 'post',
                    data: {
                        CALL: 44,
                        task_id: taskId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status === 'SUCCESS') {
                            showNotification('Task deleted successfully', 'success');
                            if(tasksTable) {
                                tasksTable.ajax.reload();
                            }
                            if(reportsTable) {
                                reportsTable.ajax.reload();
                            }
                        } else {
                            showNotification(response.msg || 'Failed to delete task', 'error');
                        }
                    },
                    error: function() {
                        showNotification('Error deleting task', 'error');
                    }
                });
            }
        });
        
        // Submission Action Handlers
        $(document).on('click', '.viewSubmissionBtn', function() {
            const submissionId = $(this).data('id');
            
            $.ajax({
                url: 'ajax.php',
                type: 'post',
                data: {
                    CALL: 45,
                    submission_id: submissionId
                },
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'SUCCESS') {
                        const data = response.data;
                        const content = `
                            <div class="submission-details">
                                <p><strong>Task:</strong> ${data.task_title}</p>
                                <p><strong>Student:</strong> ${data.student_name}</p>
                                <p><strong>File:</strong> ${data.file_name}</p>
                                <p><strong>Submitted:</strong> ${data.submitted_at}</p>
                                <p><strong>Status:</strong> ${data.check_status}</p>
                                ${data.file_path ? `<p><a href="../${data.file_path}" target="_blank" class="btn-primary">Download File</a></p>` : ''}
                            </div>
                        `;
                        showNotification('View Details: Check console for data', 'info');
                        console.log(data);
                    } else {
                        showNotification(response.msg || 'Failed to load submission', 'error');
                    }
                },
                error: function() {
                    showNotification('Error loading submission details', 'error');
                }
            });
        });
        
        $(document).on('click', '.approveBtn', function() {
            const submissionId = $(this).data('id');
            if(confirm('Are you sure you want to approve this submission?')) {
                $.ajax({
                    url: 'ajax.php',
                    type: 'post',
                    data: {
                        CALL: 46,
                        submission_id: submissionId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status === 'SUCCESS') {
                            showNotification('Submission approved successfully', 'success');
                            if(reportsTable) {
                                reportsTable.ajax.reload();
                            }
                        } else {
                            showNotification(response.msg || 'Failed to approve submission', 'error');
                        }
                    },
                    error: function() {
                        showNotification('Error approving submission', 'error');
                    }
                });
            }
        });
    }
    
    function initializeFilterHandlers() {
        // Report Filters
        $('#applyReportFilters').click(function() {
            if(reportsTable) {
                reportsTable.ajax.reload();
            }
        });
        
        $('#clearReportFilters').click(function() {
            $('#reportTaskFilter').val('');
            $('#reportStatusFilter').val('');
            if(reportsTable) {
                reportsTable.ajax.reload();
            }
        });
        
        // Task Filters
        $('#applyTaskFilters').click(function() {
            if(tasksTable) {
                tasksTable.ajax.reload();
            }
        });
        
        $('#clearTaskFilters').click(function() {
            $('#taskCategory').val('');
            $('#taskStatusFilter').val('');
            $('#taskDateFrom').val('');
            $('#taskDateTo').val('');
            if(tasksTable) {
                tasksTable.ajax.reload();
            }
        });
    }
    
    function loadDropdownData() {
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            data: {
                CALL: 5 // Get dropdowns
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'SUCCESS') {
                    // Populate task categories
                    const taskCategorySelect = $('#taskCategory');
                    taskCategorySelect.empty().append('<option value="">Select Category</option>');
                    response.data.task_categories.forEach(function(category) {
                        taskCategorySelect.append('<option value="'+category.task_category_id+'">'+category.task_category+'</option>');
                    });
                }
            }
        });
    }
    
    function loadReportFilters() {
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            data: {
                CALL: 6 // Get tasks for filter
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'SUCCESS') {
                    // Populate task filter
                    const taskFilterSelect = $('#reportTaskFilter');
                    taskFilterSelect.empty().append('<option value="">All Tasks</option>');
                    response.data.forEach(function(task) {
                        taskFilterSelect.append('<option value="'+task.task_id+'">'+task.task_title+'</option>');
                    });
                }
            }
        });
    }
    
    function saveData(call, dataArr = {}) {
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            data: {
                CALL: call,
                ...dataArr
            },
            dataType: 'json',
            success: function(result) {
                openModal(result.status, result.msg);
                if(result.status === "SUCCESS") {
                    // Clear form fields
                    $("input[type='text'], input[type='password'], textarea, select").val('');
                    
                    // Reload relevant tables
                    reloadAllTables();
                }
            },
            error: function() {
                openModal("ERROR", "An error occurred while processing your request");
            }
        });
    }
    
    function deleteTask(taskId) {
        const reason = prompt('Please provide a reason for deletion:');
        if(reason) {
            $.ajax({
                url: 'ajax.php',
                type: 'post',
                data: {
                    CALL: 9, // Delete task
                    task_id: taskId,
                    reason: reason
                },
                dataType: 'json',
                success: function(result) {
                    openModal(result.status, result.msg);
                    if(result.status === "SUCCESS") {
                        reloadAllTables();
                    }
                },
                error: function() {
                    openModal("ERROR", "An error occurred while deleting the task");
                }
            });
        }
    }
    
    function reviewSubmission(submissionId, status, reason = '') {
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            data: {
                CALL: 12, // Review submission
                task_submission_id: submissionId,
                check_status: status,
                reason: reason
            },
            dataType: 'json',
            success: function(result) {
                openModal(result.status, result.msg);
                if(result.status === "SUCCESS") {
                    reloadAllTables();
                }
            },
            error: function() {
                openModal("ERROR", "An error occurred while reviewing the submission");
            }
        });
    }
    
    function reloadAllTables() {
        if(recentActivityTable) recentActivityTable.ajax.reload(null, false);
        if(tasksTable) tasksTable.ajax.reload(null, false);
        if(reportsTable) reportsTable.ajax.reload(null, false);
        
        // Reload dashboard stats
        loadDashboardStats();
    }
    
    function openEditTaskModal(taskId) {
        // Get task details first
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            data: {
                CALL: 10, // Get task details
                task_id: taskId
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'SUCCESS') {
                    const task = response.data;
                    $('#editTaskTitle').val(task.task_title);
                    $('#editTaskCategory').val(task.task_category_id);
                    $('#editTaskDescription').val(task.task_description);
                    $('#editTaskDeadline').val(task.task_deadline);
                    $('#editTaskId').val(task.task_id);
                    
                    // Show edit modal (assuming modal exists)
                    $('#editTaskModal').show();
                }
            }
        });
    }
    
    function openViewSubmissionModal(submissionId) {
        // Get submission details
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            data: {
                CALL: 13, // Get submission details
                submission_id: submissionId
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'SUCCESS') {
                    const submission = response.data;
                    // Populate modal with submission details
                    console.log('Opening view modal for submission:', submission);
                    // TODO: Implement modal display logic
                    openModal("INFO", "View submission functionality - to be implemented with modals");
                }
            }
        });
    }
    
    // Notification handlers
    function loadNotifications() {
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            data: {
                CALL: 16 // Get notifications
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'SUCCESS') {
                    displayNotifications(response.data);
                }
            }
        });
    }
    
    function markNotificationAsRead(notificationId) {
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            data: {
                CALL: 17, // Mark notification as read
                notification_id: notificationId
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'SUCCESS') {
                    loadDashboardStats(); // Refresh unread count
                }
            }
        });
    }
    
    function displayNotifications(notifications) {
        const container = $('#notificationsContainer');
        container.empty();
        
        notifications.forEach(function(notification) {
            const notificationHtml = `
                <div class="notification ${notification.is_read ? '' : 'unread'}" data-id="${notification.notification_id}">
                    <h4>${notification.title}</h4>
                    <p>${notification.message}</p>
                    <small>${formatDate(notification.datetime_created)}</small>
                    ${!notification.is_read ? '<button onclick="markNotificationAsRead('+notification.notification_id+')">Mark as Read</button>' : ''}
                </div>
            `;
            container.append(notificationHtml);
        });
    }
    
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }
    
    // Modal function (assuming it exists in the global scope)
    function openModal(status, message) {
        if(typeof window.openModal === 'function') {
            window.openModal(status, message);
        } else {
            // Fallback to console if modal not available
            console.log(status + ': ' + message);
        }
    }
    
    // Initialize notifications on page load
    loadNotifications();
});

// Global notification function
function showNotification(message, type = 'info', title = '') {
    const modal = document.getElementById('notificationModal');
    if (!modal) return;
    
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
    if (modalTitle) modalTitle.textContent = title || typeConfig.defaultTitle;
    if (modalMessage) modalMessage.textContent = message;
    
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
    if (!modal) return;
    
    modal.classList.remove('notification-show');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

// Global dropdown toggle function
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