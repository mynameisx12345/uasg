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
                      <label for="assignTo">Assign To</label>
                      <select id="assignTo" name="assigned_to">
                          <option value="">All Student Government Members</option>
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

<script>
(function($) {
    let deleteTaskId = null;
    
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
            { data: 'task_deadline' },
            {
                data: null,
                render: function(data, type, row) {
                    const today = new Date();
                    const deadline = new Date(row.task_deadline);
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
                    return `<div class='actions'>
                        <button class='btn-sm btn-secondary editTaskBtn' data-id='${row.task_id}'>Edit</button>
                        <button class='btn-sm btn-danger deleteTaskBtn' data-id='${row.task_id}'>Delete</button>
                    </div>`;
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
                    return `<div class='actions'>
                        <button class='btn-sm btn-primary viewSubmissionBtn' data-id='${row.task_submission_id}'>View</button>
                        <button class='btn-sm btn-success approveBtn' data-id='${row.task_submission_id}'>Approve</button>
                    </div>`;
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
                alert('Task created successfully');
                $('#createTaskForm')[0].reset();
                tasksTable.ajax.reload(); // <-- reload table
            } else {
                alert(resp.msg || 'Failed to create task');
            }
        }, 'json');
    });

      function loadMembers() {
          $.post('ajax.php', { CALL: 100 }, function(resp) {
              if (resp.data) {
                  const options = resp.data.map(m =>
                      `<option value="${m.user_id}">${m.full_name}</option>`
                  ).join('');
                  $('#assignTo').append(options);
              }
          }, 'json');
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
                alert('Unable to fetch task data');
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
                alert('Task updated successfully');
                closeModal();
                tasksTable.ajax.reload();
            } else {
                alert(resp.msg || 'Failed to update task');
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
                alert('Task deleted successfully');
                closeModal();
                tasksTable.ajax.reload();
                submissionsTable.ajax.reload();
            } else {
                alert(resp.msg || 'Failed to delete task');
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
                        <p><strong>Status:</strong> <span class="status-badge">${sub.check_status}</span></p>
                        <div style="margin-top:1rem;">
                            <a href="ajax.php?CALL=download&file_id=${sub.file_upload_id}" class="btn-primary" download>Download File</a>
                        </div>
                    </div>
                `;
                $('#submissionContent').html(content);
                openModal('viewSubmissionModal');
            } else {
                alert('Unable to fetch submission data');
            }
        }, 'json');
    });

    // Approve submission button
    $(document).on('click', '.approveBtn', function() {
        const submissionId = $(this).data('id');
        
        if (confirm('Approve this submission?')) {
            $.post('ajax.php', { CALL: 46, submission_id: submissionId }, function(resp) {
                if (resp.status === 'SUCCESS') {
                    alert('Submission approved');
                    submissionsTable.ajax.reload();
                } else {
                    alert(resp.msg || 'Failed to approve submission');
                }
            }, 'json');
        }
    });

    // Initialize
    loadTaskCategories();

})(jQuery);
</script>
</body>
</html>