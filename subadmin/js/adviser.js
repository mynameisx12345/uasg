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
                    data: function(d) {
                        return {
                            CALL: 8, // Get all tasks
                            category: $('#taskCategory').val(),
                            status: $('#taskStatusFilter').val(),
                            date_from: $('#taskDateFrom').val(),
                            date_to: $('#taskDateTo').val()
                        };
                    },
                    dataSrc: 'data'
                },
                responsive: true,
                paging: true,
                pageLength: 10,
                columns: [
                    { data: "task_id", visible: false },
                    { data: "task_title" },
                    { data: "task_category" },
                    { data: "task_description" },
                    { data: "task_deadline" },
                    { data: "submission_count", defaultContent: "0" },
                    { data: "approved_count", defaultContent: "0" },
                    { data: "pending_count", defaultContent: "0" },
                    {
                        data: 'task_id',
                        render: function(id) {
                            return '<button class="btn-primary editTaskBtn" data-id="'+id+'" title="Edit Task">Edit</button> ' +
                                   '<button class="btn-secondary deleteTaskBtn" data-id="'+id+'" title="Delete Task">Delete</button>';
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
                            CALL: 11, // Get all submissions
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
                    { data: "task_submission_id", visible: false },
                    { data: "task_title" },
                    { data: function(row) { return row.fname + ' ' + row.lname; } },
                    { data: "file_name" },
                    { data: "datetime_uploaded" },
                    { data: "check_status" },
                    {
                        data: 'task_submission_id',
                        render: function(id, type, row) {
                            let buttons = '<button class="btn-primary viewSubmissionBtn" data-id="'+id+'" title="View Submission">View</button> ';
                            if(row.check_status === 'pending') {
                                buttons += '<button class="btn-success approveBtn" data-id="'+id+'" title="Approve">Approve</button> ';
                                buttons += '<button class="btn-danger rejectBtn" data-id="'+id+'" title="Reject">Reject</button>';
                            }
                            return buttons;
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
            
            saveData(7, taskData); // Create task
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
            openEditTaskModal(taskId);
        });
        
        $(document).on('click', '.deleteTaskBtn', function() {
            const taskId = $(this).data('id');
            if(confirm('Are you sure you want to delete this task?')) {
                deleteTask(taskId);
            }
        });
        
        // Submission Action Handlers
        $(document).on('click', '.viewSubmissionBtn', function() {
            const submissionId = $(this).data('id');
            openViewSubmissionModal(submissionId);
        });
        
        $(document).on('click', '.approveBtn', function() {
            const submissionId = $(this).data('id');
            reviewSubmission(submissionId, 'approved');
        });
        
        $(document).on('click', '.rejectBtn', function() {
            const submissionId = $(this).data('id');
            const reason = prompt('Please provide a reason for rejection:');
            if(reason) {
                reviewSubmission(submissionId, 'rejected', reason);
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
            alert(status + ': ' + message);
        }
    }
    
    // Initialize notifications on page load
    loadNotifications();
});