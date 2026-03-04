<?php
require_once __DIR__ . '/../resources/objects/db_config.php';
require_once __DIR__ . '/../resources/objects/main_class.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Task Management - UASG</title>
  
  <link rel="stylesheet" href="../resources/style.css">
  <link rel="stylesheet" href="../resources/theme-overrides.css">
  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
  <style>
    .actions {
      display: flex;
      gap: 0.5rem;
    }
    .btn-sm {
      padding: 0.25rem 0.5rem;
      font-size: 0.85rem;
    }
    
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
      min-width: 160px;
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
    .dropdown-item.edit { color: #28a745; }
    .dropdown-item.approve { color: #28a745; }
    .dropdown-item.delete { color: #dc3545; }
    .dropdown-item.deactivate { color: #dc3545; }
    .dropdown-item:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    .modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }
    .modal-content {
      background: #fff;
      padding: 1.5rem;
      border-radius: 8px;
      min-width: 500px;
      max-width: 600px;
      max-height: 90vh;
      overflow-y: auto;
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      font-weight: 600;
      font-size: 1.2rem;
    }
    .close-modal {
      cursor: pointer;
      font-size: 1.5rem;
      color: #666;
    }
    .status-badge {
      padding: 0.25rem 0.5rem;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    .status-pending { background: #ffc107; color: #000; }
    .status-submitted { background: #17a2b8; color: white; }
    .status-completed { background: #28a745; color: white; }
    .status-overdue { background: #dc3545; color: white; }
    .submission-details {
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 1rem;
      margin-top: 1rem;
    }
    .submission-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.5rem;
      border-bottom: 1px solid #eee;
    }
    .submission-item:last-child {
      border-bottom: none;
    }
  </style>
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
  <script src='../js/datatable.js'></script>
</head>
<body>
  <!-- HEADER -->
   <?php require_once("header.php");?>
   <header class="topbar">
    <h1>Task Management</h1>
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
        <h2>Task Management</h2>

        <!-- TABS -->
        <div class="tabs">
          <button class="tab-link active" data-tab="create">Create Task</button>
          <button class="tab-link" data-tab="all-tasks">All Tasks</button>
          <button class="tab-link" data-tab="submissions">Submissions</button>
        </div>

        <!-- CREATE TASK TAB -->
        <div id="create" class="tab-content active">
          <div class="compact-form">
            <h3>Create New Task</h3>
            <form id="createTaskForm">
              <div class="form-columns">
                <div class="form-column">
                  <div class="form-section">
                    <h4>Task Information</h4>
                    
                    <div class="form-group">
                      <label for="taskCategory">Task Category *</label>
                      <select id="taskCategory" name="task_category_id" required>
                        <option value="">Select Category</option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label for="taskTitle">Task Title *</label>
                      <input type="text" id="taskTitle" name="task_title" placeholder="Enter task title..." required>
                    </div>
                    
                    <div class="form-group">
                      <label for="taskDescription">Task Description *</label>
                      <textarea id="taskDescription" name="task_description" rows="4" placeholder="Enter task description..." required></textarea>
                    </div>
                    <div class="form-group">
                      <label for="assignTo">Assign To *</label>
                      <select id="assignTo" name="assigned_to" required>
                          <option value="">Select a member...</option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label for="taskDeadline">Deadline *</label>
                      <input type="date" id="taskDeadline" name="task_deadline" required>
                    </div>
                  </div>
                </div>
                
                <div class="form-column">
                  <div class="form-section">
                    <h4>Actions</h4>
                    <div class="form-actions">
                      <button type="submit" class="btn-primary">Create Task</button>
                      <button type="reset" class="btn-secondary">Clear</button>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- ALL TASKS TAB -->
        <div id="all-tasks" class="tab-content">
          <div class="table-container">
            <table id="tasksTable" class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Category</th>
                  <th>Title</th>
                  <th>Description</th>
                  <th>Assigned To</th>
                  <th>Deadline</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>

        <!-- SUBMISSIONS TAB -->
        <div id="submissions" class="tab-content">
          <div class="table-container">
            <table id="submissionsTable" class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Task</th>
                  <th>Student</th>
                  <th>File</th>
                  <th>Submitted</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- EDIT TASK MODAL -->
  <div class="modal" id="editTaskModal">
    <div class="modal-content">
      <div class="modal-header">
        <span>Edit Task</span>
        <span class="close-modal" data-close>&times;</span>
      </div>
      <form id="editTaskForm">
        <input type="hidden" id="editTaskId" name="task_id" />
        
        <div class="form-group">
          <label for="editTaskCategory">Task Category *</label>
          <select id="editTaskCategory" name="task_category_id" required>
            <option value="">Select Category</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="editTaskTitle">Task Title *</label>
          <input type="text" id="editTaskTitle" name="task_title" required>
        </div>
        
        <div class="form-group">
          <label for="editTaskDescription">Task Description *</label>
          <textarea id="editTaskDescription" name="task_description" rows="4" required></textarea>
        </div>
        
        <div class="form-group">
          <label for="editTaskDeadline">Deadline *</label>
          <input type="date" id="editTaskDeadline" name="task_deadline" required>
        </div>
        
        <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
          <button type="submit" class="btn-primary">Update Task</button>
          <button type="button" class="btn-secondary" data-close>Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- DELETE TASK MODAL -->
  <div class="modal" id="deleteTaskModal">
    <div class="modal-content">
      <div class="modal-header">
        <span>Delete Task</span>
        <span class="close-modal" data-close>&times;</span>
      </div>
      <div id="deleteTaskMessage">Are you sure you want to delete this task?</div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
        <button id="confirmDeleteTask" class="btn-danger">Delete</button>
        <button class="btn-secondary" data-close>Cancel</button>
      </div>
    </div>
  </div>

  <!-- VIEW SUBMISSION MODAL -->
  <div class="modal" id="viewSubmissionModal">
    <div class="modal-content">
      <div class="modal-header">
        <span>Submission Details</span>
        <span class="close-modal" data-close>&times;</span>
      </div>
      <div id="submissionContent"></div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
        <button class="btn-secondary" data-close>Close</button>
      </div>
    </div>
  </div>

  <!-- APPROVE SUBMISSION MODAL -->
  <div class="modal" id="approveSubmissionModal">
    <div class="modal-content">
      <div class="modal-header">
        <span>Approve Submission</span>
        <span class="close-modal" data-close>&times;</span>
      </div>
      <div id="approveSubmissionMessage">
        <p>Are you sure you want to approve this submission?</p>
        <p style="margin-top: 10px; color: #666;">
          <i class="fas fa-info-circle"></i> 
          The submission will be marked as "Approved" and the linked file will be protected from deletion by non-admin users.
        </p>
      </div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
        <button id="confirmApproveSubmission" class="btn-success">Approve</button>
        <button class="btn-secondary" data-close>Cancel</button>
      </div>
    </div>
  </div>

  <!-- TRANSFER TASK MODAL -->
  <div class="modal" id="transferTaskModal">
    <div class="modal-content">
      <div class="modal-header">
        <span>Transfer Task</span>
        <span class="close-modal" data-close>&times;</span>
      </div>
      <form id="transferTaskForm">
        <input type="hidden" id="transferTaskId" name="task_id" />
        
        <div class="form-group">
          <label for="transferTaskTitle">Task Title</label>
          <input type="text" id="transferTaskTitle" name="task_title" readonly style="background:#f0f0f0;">
        </div>
        
        <div class="form-group">
          <label for="transferToMember">Transfer To *</label>
          <select id="transferToMember" name="new_assigned_to" required>
            <option value="">Select a member...</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="transferReason">Reason for Transfer *</label>
          <textarea id="transferReason" name="transfer_reason" rows="3" placeholder="Enter reason for transferring this task..." required></textarea>
        </div>
        
        <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
          <button type="submit" class="btn-primary">Transfer Task</button>
          <button type="button" class="btn-secondary" data-close>Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- CLOSE/CANCEL TASK MODAL -->
  <div class="modal" id="closeTaskModal">
    <div class="modal-content">
      <div class="modal-header">
        <span id="closeTaskModalTitle">Close Task</span>
        <span class="close-modal" data-close>&times;</span>
      </div>
      <form id="closeTaskForm">
        <input type="hidden" id="closeTaskId" name="task_id" />
        <input type="hidden" id="closeTaskAction" name="action" />
        
        <div class="form-group">
          <label for="closeTaskTitle">Task Title</label>
          <input type="text" id="closeTaskTitle" name="task_title" readonly style="background:#f0f0f0;">
        </div>
        
        <div class="form-group">
          <label for="closeTaskReason">Reason *</label>
          <textarea id="closeTaskReason" name="reason" rows="3" placeholder="Enter reason for closing/cancelling this task..." required></textarea>
        </div>
        
        <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
          <button type="submit" class="btn-primary" id="confirmCloseTask">Confirm</button>
          <button type="button" class="btn-secondary" data-close>Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- NOTIFICATION MODAL -->
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

<script>
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

<style>
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
</style>

<script>
(function($) {
    let deleteTaskId = null;
    let approveSubmissionId = null;
    
    // Tab switching
    $('.tab-link').on('click', function() {
        const target = $(this).data('tab');
        $('.tab-link').removeClass('active');
        $('.tab-content').removeClass('active');
        $(this).addClass('active');
        $('#' + target).addClass('active');
    });

    // Load task categories
    function loadTaskCategories() {
        $.post('ajax.php', { CALL: 6 }, function(resp) {
            if (resp.data) {
                const options = resp.data.map(cat => 
                    `<option value="${cat.task_category_id}">${cat.task_category}</option>`
                ).join('');
                $('#taskCategory, #editTaskCategory').append(options);
            }
        }, 'json');
    }

    // Initialize DataTables
    const tasksTable = $('#tasksTable').DataTable({
        ajax: {
            url: 'ajax.php',
            type: 'POST',
            data: { CALL: 40 },
            dataSrc: function(json) { return json.data || []; }
        },
        columns: [
            { data: 'task_id' },
            { data: 'task_category' }, // changed from category_name
            { data: 'task_title' },
            { 
                data: 'task_description',
                render: function(data) {
                    return data.length > 50 ? data.substring(0, 50) + '...' : data;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return row.assigned_member_name || '<span style="color: #999;">Unassigned</span>';
                }
            },
            {
                data: 'task_deadline',
                render: function(data, type, row) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0); // Reset time to compare dates only
                    const deadline = new Date(data);
                    deadline.setHours(0, 0, 0, 0);
                    
                    if (deadline < today) {
                        // Deadline has passed - show in red with warning icon
                        return `<span style="color: #dc3545; font-weight: 600;">
                            <i class="fas fa-exclamation-triangle"></i> ${data}
                            <br><small style="font-size: 0.75em;">(Overdue)</small>
                        </span>`;
                    } else if (deadline.getTime() === today.getTime()) {
                        // Deadline is today - show in orange
                        return `<span style="color: #ff9800; font-weight: 600;">
                            <i class="fas fa-clock"></i> ${data}
                            <br><small style="font-size: 0.75em;">(Due Today)</small>
                        </span>`;
                    } else {
                        // Future deadline - show normally
                        return data;
                    }
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const deadline = new Date(row.task_deadline);
                    deadline.setHours(0, 0, 0, 0);
                    const hasSubmissions = row.submission_count > 0;

                    if (deadline < today && !hasSubmissions) {
                        return '<span class="status-badge status-overdue">Overdue</span>';
                    } else if (hasSubmissions) {
                        return '<span class="status-badge status-submitted">Has Submissions</span>';
                    } else {
                        return '<span class="status-badge status-pending">Pending</span>';
                    }
                }
            },
            { 
                data: null, 
                orderable: false,
                render: function(data, type, row) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const deadline = new Date(row.task_deadline);
                    deadline.setHours(0, 0, 0, 0);
                    const isOverdue = deadline < today;
                    const hasSubmissions = row.submission_count > 0;
                    const taskStatus = row.task_status || 'active';
                    
                    // Don't show actions for closed or cancelled tasks
                    if (taskStatus === 'closed' || taskStatus === 'cancelled') {
                        return `<span class="status-badge" style="background: #6c757d;">Task ${taskStatus}</span>`;
                    }
                    
                    let actions = `
                        <div class="dropdown-container">
                            <button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
                            <div class="dropdown-menu">`;
                    
                    // Edit button (always available for active tasks)
                    actions += `
                                <button class="dropdown-item edit editTaskBtn" data-id="${row.task_id}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>`;
                    
                    // Transfer button (only if overdue and assigned to someone)
                    if (isOverdue && row.assigned_to) {
                        actions += `
                                <button class="dropdown-item transferTaskBtn" style="color: #ff9800;" data-id="${row.task_id}" data-title="${row.task_title}">
                                    <i class="fas fa-exchange-alt"></i> Transfer
                                </button>`;
                    }
                    
                    // Close button (mark as closed/completed)
                    actions += `
                                <button class="dropdown-item closeTaskBtn" style="color: #28a745;" data-id="${row.task_id}" data-title="${row.task_title}" data-action="close">
                                    <i class="fas fa-check-circle"></i> Close Task
                                </button>`;
                    
                    // Cancel button (mark as cancelled)
                    actions += `
                                <button class="dropdown-item cancelTaskBtn" style="color: #ffc107;" data-id="${row.task_id}" data-title="${row.task_title}" data-action="cancel">
                                    <i class="fas fa-times-circle"></i> Cancel Task
                                </button>`;
                    
                    // Delete button
                    actions += `
                                <button class="dropdown-item delete deleteTaskBtn" data-id="${row.task_id}">
                                    <i class="fas fa-trash"></i> Delete
                                </button>`;
                    
                    actions += `
                            </div>
                        </div>
                    `;
                    
                    return actions;
                }
            }
        ]
    });

    const submissionsTable = $('#submissionsTable').DataTable({
        ajax: {
            url: 'ajax.php',
            type: 'POST',
            data: { CALL: 41 }, // Get all submissions
            dataSrc: function(json) { return json.data || []; }
        },
        columns: [
            { data: 'task_submission_id' },
            { data: 'task_title' },
            { data: 'student_name' },
            { data: 'file_name' },
            { data: 'submitted_at' },
            {
                data: 'check_status',
                render: function(data) {
                    const statusClass = data === 'Approved' ? 'status-completed' : 
                                      data === 'Pending' ? 'status-pending' : 'status-submitted';
                    return `<span class="status-badge ${statusClass}">${data}</span>`;
                }
            },
            { 
                data: null, 
                orderable: false,
                render: function(data, type, row) {
                    const isApproved = row.check_status === 'Approved';
                    
                    return `
                        <div class="dropdown-container">
                            <button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
                            <div class="dropdown-menu">
                                <button class="dropdown-item view viewSubmissionBtn" data-id="${row.task_submission_id}">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="dropdown-item approve approveBtn" data-id="${row.task_submission_id}" ${isApproved ? 'disabled' : ''}>
                                    <i class="fas fa-check-circle"></i> ${isApproved ? 'Approved ✓' : 'Approve'}
                                </button>
                            </div>
                        </div>
                    `;
                }
            }
        ]
    });

    // Modal helpers
    function openModal(id) { $('#' + id).css('display', 'flex'); }
    function closeModal() { $('.modal').hide(); }
    $(document).on('click', '[data-close]', closeModal);
    $(document).on('click', '.modal', function(e) { if (e.target === this) closeModal(); });

    // Create task form submission
    $('#createTaskForm').on('submit', function(e) {
        e.preventDefault();

        const payload = {
            CALL: 22, // Create task
            DATA: {
                task_category_id: $('#taskCategory').val(),
                task_title: $('#taskTitle').val(),
                task_description: $('#taskDescription').val(),
                task_deadline: $('#taskDeadline').val(),
                assigned_to: $('#assignTo').val() // NEW
            }
        };

         $.post('ajax.php', payload, function(resp) {
            if (resp.status === 'SUCCESS') {
                showNotification('Task created successfully', 'success', 'Task Created');
                $('#createTaskForm')[0].reset();
                tasksTable.ajax.reload(); // <-- reload table
            } else {
                showNotification(resp.msg || 'Failed to create task', 'error', 'Creation Failed');
            }
        }, 'json');
    });

      function loadMembers() {
          $.post('ajax.php', { CALL: 63 }, function(resp) {
              if (resp.status === 'SUCCESS' && resp.data && Array.isArray(resp.data)) {
                  const options = resp.data.map(m =>
                      `<option value="${m.user_id}">${m.full_name} - ${m.position}</option>`
                  ).join('');
                  $('#assignTo').append(options);
              } else {
                  console.error('Failed to load members:', resp.msg || 'Unknown error');
              }
          }, 'json').fail(function(xhr, status, error) {
              console.error('AJAX error loading members:', error);
          });
      }

      loadMembers(); // call it

    // Edit task button
    $(document).on('click', '.editTaskBtn', function() {
        const taskId = $(this).data('id');
        
        $.post('ajax.php', { CALL: 42, task_id: taskId }, function(resp) {
            if (resp.status === 'SUCCESS') {
                const task = resp.data;
                $('#editTaskId').val(task.task_id);
                $('#editTaskCategory').val(task.task_category_id);
                $('#editTaskTitle').val(task.task_title);
                $('#editTaskDescription').val(task.task_description);
                $('#editTaskDeadline').val(task.task_deadline);
                openModal('editTaskModal');
            } else {
                showNotification('Unable to fetch task data', 'error', 'Error');
            }
        }, 'json');
    });

    // Edit task form submission
    $('#editTaskForm').on('submit', function(e) {
        e.preventDefault();
        
        const payload = {
            CALL: 43, // Update task
            task_id: $('#editTaskId').val(),
            task_category_id: $('#editTaskCategory').val(),
            task_title: $('#editTaskTitle').val(),
            task_description: $('#editTaskDescription').val(),
            task_deadline: $('#editTaskDeadline').val()
        };
        
        $.post('ajax.php', payload, function(resp) {
            if (resp.status === 'SUCCESS') {
                showNotification('Task updated successfully', 'success', 'Task Updated');
                closeModal();
                tasksTable.ajax.reload();
            } else {
                showNotification(resp.msg || 'Failed to update task', 'error', 'Update Failed');
            }
        }, 'json');
    });

    // Delete task button
    $(document).on('click', '.deleteTaskBtn', function() {
        deleteTaskId = $(this).data('id');
        $('#deleteTaskMessage').text('Are you sure you want to delete this task? All submissions will also be deleted.');
        openModal('deleteTaskModal');
    });

    // Confirm delete task
    $('#confirmDeleteTask').on('click', function() {
        if (!deleteTaskId) return;
        
        $.post('ajax.php', { CALL: 44, task_id: deleteTaskId }, function(resp) {
            if (resp.status === 'SUCCESS') {
                showNotification('Task deleted successfully', 'success', 'Task Deleted');
                closeModal();
                tasksTable.ajax.reload();
                submissionsTable.ajax.reload();
            } else {
                showNotification(resp.msg || 'Failed to delete task', 'error', 'Deletion Failed');
            }
        }, 'json');
    });

    // View submission button
    $(document).on('click', '.viewSubmissionBtn', function() {
        const submissionId = $(this).data('id');
        
        $.post('ajax.php', { CALL: 45, submission_id: submissionId }, function(resp) {
            if (resp.status === 'SUCCESS') {
                const sub = resp.data;
                const content = `
                    <div class="submission-details">
                        <h4>${sub.task_title}</h4>
                        <p><strong>Student:</strong> ${sub.student_name}</p>
                        <p><strong>Submitted:</strong> ${sub.submitted_at}</p>
                        <p><strong>File:</strong> ${sub.file_name}</p>
                        <p><strong>Status:</strong> <span class="status-badge ${sub.check_status === 'Approved' ? 'status-completed' : 'status-pending'}">${sub.check_status}</span></p>
                        <div style="margin-top:1rem;">
                            <a href="ajax.php?CALL=download&file_id=${sub.file_upload_id}" class="btn-primary" target="_blank">Download File</a>
                        </div>
                    </div>
                `;
                $('#submissionContent').html(content);
                openModal('viewSubmissionModal');
            } else {
                showNotification('Unable to fetch submission data', 'error', 'Error');
            }
        }, 'json');
    });

    // Approve submission button
    $(document).on('click', '.approveBtn', function() {
        approveSubmissionId = $(this).data('id');
        openModal('approveSubmissionModal');
    });

    // Confirm approve submission
    $('#confirmApproveSubmission').on('click', function() {
        if (!approveSubmissionId) return;
        
        $.post('ajax.php', { CALL: 46, submission_id: approveSubmissionId }, function(resp) {
            if (resp.status === 'SUCCESS') {
                showNotification('Submission has been approved successfully', 'success', 'Approved');
                closeModal();
                submissionsTable.ajax.reload();
                approveSubmissionId = null;
            } else {
                showNotification(resp.msg || 'Failed to approve submission', 'error', 'Approval Failed');
            }
        }, 'json');
    });

    // Transfer task button
    $(document).on('click', '.transferTaskBtn', function() {
        const taskId = $(this).data('id');
        const taskTitle = $(this).data('title');
        
        $('#transferTaskId').val(taskId);
        $('#transferTaskTitle').val(taskTitle);
        $('#transferToMember').val('');
        $('#transferReason').val('');
        
        // Load members for transfer dropdown
        $.post('ajax.php', { CALL: 63 }, function(resp) {
            if (resp.status === 'SUCCESS' && resp.data && Array.isArray(resp.data)) {
                const options = '<option value="">Select a member...</option>' + resp.data.map(m =>
                    `<option value="${m.user_id}">${m.full_name} - ${m.position}</option>`
                ).join('');
                $('#transferToMember').html(options);
            }
        }, 'json');
        
        openModal('transferTaskModal');
    });

    // Transfer task form submission
    $('#transferTaskForm').on('submit', function(e) {
        e.preventDefault();
        
        const payload = {
            CALL: 'transfer_task',
            task_id: $('#transferTaskId').val(),
            new_assigned_to: $('#transferToMember').val(),
            transfer_reason: $('#transferReason').val()
        };
        
        $.post('ajax.php', payload, function(resp) {
            if (resp.status === 'SUCCESS') {
                showNotification('Task transferred successfully', 'success', 'Task Transferred');
                closeModal();
                tasksTable.ajax.reload();
            } else {
                showNotification(resp.msg || 'Failed to transfer task', 'error', 'Transfer Failed');
            }
        }, 'json');
    });

    // Close task button
    $(document).on('click', '.closeTaskBtn', function() {
        const taskId = $(this).data('id');
        const taskTitle = $(this).data('title');
        
        $('#closeTaskId').val(taskId);
        $('#closeTaskTitle').val(taskTitle);
        $('#closeTaskAction').val('close');
        $('#closeTaskModalTitle').text('Close Task');
        $('#closeTaskReason').val('');
        $('#confirmCloseTask').text('Close Task').removeClass('btn-warning').addClass('btn-primary');
        
        openModal('closeTaskModal');
    });

    // Cancel task button
    $(document).on('click', '.cancelTaskBtn', function() {
        const taskId = $(this).data('id');
        const taskTitle = $(this).data('title');
        
        $('#closeTaskId').val(taskId);
        $('#closeTaskTitle').val(taskTitle);
        $('#closeTaskAction').val('cancel');
        $('#closeTaskModalTitle').text('Cancel Task');
        $('#closeTaskReason').val('');
        $('#confirmCloseTask').text('Cancel Task').removeClass('btn-primary').addClass('btn-warning');
        
        openModal('closeTaskModal');
    });

    // Close/Cancel task form submission
    $('#closeTaskForm').on('submit', function(e) {
        e.preventDefault();
        
        const action = $('#closeTaskAction').val();
        const payload = {
            CALL: 'close_cancel_task',
            task_id: $('#closeTaskId').val(),
            action: action,
            reason: $('#closeTaskReason').val()
        };
        
        $.post('ajax.php', payload, function(resp) {
            if (resp.status === 'SUCCESS') {
                const message = action === 'close' ? 'Task closed successfully' : 'Task cancelled successfully';
                const title = action === 'close' ? 'Task Closed' : 'Task Cancelled';
                showNotification(message, 'success', title);
                closeModal();
                tasksTable.ajax.reload();
            } else {
                showNotification(resp.msg || 'Failed to update task', 'error', 'Update Failed');
            }
        }, 'json');
    });

    // Initialize
    loadTaskCategories();

})(jQuery);

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