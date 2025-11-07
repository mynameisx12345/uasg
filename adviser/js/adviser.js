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
                    CALL: 'get_recent_activity'
                },
                dataSrc: 'data'
            },
            responsive: true,
            paging: true,
            pageLength: 10,
            columns: [
                { data: "date" },
                { data: "student" },
                { data: "action" },
                { data: "task" },
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
                CALL: 'get_dashboard_stats'
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'SUCCESS') {
                    $('#totalTasks').text(response.data.total_tasks);
                    $('#pendingReviews').text(response.data.pending_reviews);
                    $('#completedTasks').text(response.data.completed_tasks);
                    $('#activeMembers').text(response.data.active_members);
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
                            CALL: 'get_all_tasks',
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
                    { data: "status", defaultContent: "Active" },
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
                            CALL: 'get_all_submissions',
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
                    { data: "submission_id", visible: false },
                    { data: "task_title" },
                    { data: function(row) { return row.fname + ' ' + row.lname; } },
                    { data: "file_name" },
                    { data: "submission_date" },
                    { data: "check_status" },
                    {
                        data: 'submission_id',
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
                title: taskTitle,
                category: taskCategory,
                description: taskDescription,
                deadline: taskDeadline
            };
            
            saveData('create_task', taskData);
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
            
            saveData('change_password', passwordData);
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
    }
    
    function loadDropdownData() {
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            data: {
                CALL: 'get_dropdowns'
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
                CALL: 'get_dropdowns'
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'SUCCESS') {
                    // Populate task filter
                    const taskFilterSelect = $('#reportTaskFilter');
                    taskFilterSelect.empty().append('<option value="">All Tasks</option>');
                    response.data.tasks.forEach(function(task) {
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
                DATA: dataArr
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
                    CALL: 'delete_task',
                    DATA: {
                        task_id: taskId,
                        reason: reason
                    }
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
                CALL: 'review_submission',
                DATA: {
                    submission_id: submissionId,
                    status: status,
                    reason: reason
                }
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
        // Implementation for opening edit task modal
        console.log('Opening edit modal for task:', taskId);
        openModal("INFO", "Edit task functionality - to be implemented with modals");
    }
    
    function openViewSubmissionModal(submissionId) {
        // Implementation for opening view submission modal
        console.log('Opening view modal for submission:', submissionId);
        openModal("INFO", "View submission functionality - to be implemented with modals");
    }
    
    // Modal function (assuming it exists in the global scope)
    function openModal(status, message) {
        if(typeof window.openModal === 'function') {
            window.openModal(status, message);
        } else {
            alert(status + ': ' + message);
        }
    }
});