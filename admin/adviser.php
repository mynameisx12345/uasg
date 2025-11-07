<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Adviser Dashboard</title>
  <link rel="stylesheet" href="../resources/style.css">
  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
  <script src='../js/datatable.js'></script>
</head>
<body>
  <!-- HEADER -->
   <?php require_once("header.php");?>
   <header class="topbar">
    <h1>Adviser Dashboard</h1>
    <div class="user-info">
      <span>Welcome, Adviser</span>
      <div class="notification-bell" id="notificationBell">
        🔔 <span id="notificationCount" class="notification-count">0</span>
      </div>
    </div>
  </header>
  <!-- MAIN -->
  <main class="main">
    <!-- SIDEBAR -->
    <?php require_once("sidebar.php");?>

    <!-- CONTENT -->
    <section class="content">
      <div class="card">
        <h2>Adviser Task Management</h2>

        <!-- TABS -->
        <div class="tabs">
          <button class="tab-link active" data-tab="tasks">Task Overview</button>
          <button class="tab-link" data-tab="create">Create Task</button>
          <button class="tab-link" data-tab="submissions">Review Submissions</button>
          <button class="tab-link" data-tab="notifications">Notifications</button>
        </div>

        <!-- TAB CONTENT: TASK OVERVIEW -->
        <div class="tab-content active" id="tasks">
          <div class="compact-form">
            <h3>Task Statistics</h3>
            <div class="stats-grid">
              <div class="stat-card">
                <h4>Total Tasks</h4>
                <div class="stat-number" id="totalTasks">0</div>
              </div>
              <div class="stat-card">
                <h4>Pending Submissions</h4>
                <div class="stat-number" id="pendingSubmissions">0</div>
              </div>
              <div class="stat-card">
                <h4>Approved Submissions</h4>
                <div class="stat-number" id="approvedSubmissions">0</div>
              </div>
            </div>
          </div>
          
          <div class="table-container">
            <h3>All Tasks</h3>
            <table class='data-table' id='tasksTable'>
              <thead>
                <tr>
                  <th>Task ID</th>
                  <th>Title</th>
                  <th>Category</th>
                  <th>Deadline</th>
                  <th>Submissions</th>
                  <th>Approved</th>
                  <th>Pending</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>

        <!-- TAB CONTENT: CREATE TASK -->
        <div class="tab-content" id="create">
          <div class="compact-form">
            <h3>Create New Task</h3>
            <div class="form-columns">
              <div class="form-column">
                <div class="form-group">
                  <label for="taskTitle">Task Title</label>
                  <input type="text" id="taskTitle" name="taskTitle" required>
                </div>
                
                <div class="form-group">
                  <label for="taskCategory">Task Category</label>
                  <select id="taskCategory" name="taskCategory" required>
                    <option value="">Select Category</option>
                  </select>
                </div>
                
                <div class="form-group">
                  <label for="taskDeadline">Deadline</label>
                  <input type="date" id="taskDeadline" name="taskDeadline" required>
                </div>
              </div>
              
              <div class="form-column">
                <div class="form-group">
                  <label for="taskDescription">Task Description</label>
                  <textarea id="taskDescription" name="taskDescription" rows="6" required></textarea>
                </div>
              </div>
            </div>
            
            <div class="form-actions">
              <button type="button" id="createTaskBtn" class="btn-primary">Create Task</button>
              <button type="button" onclick="clearTaskForm()" class="btn-secondary">Clear Form</button>
            </div>
          </div>
        </div>

        <!-- TAB CONTENT: REVIEW SUBMISSIONS -->
        <div class="tab-content" id="submissions">
          <div class="compact-form">
            <h3>Filter Submissions</h3>
            <div class="form-row">
              <div class="form-group">
                <label for="submissionStatus">Status</label>
                <select id="submissionStatus">
                  <option value="">All Statuses</option>
                  <option value="pending">Pending</option>
                  <option value="approved">Approved</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>
              <div class="form-group">
                <label for="submissionTask">Task</label>
                <select id="submissionTask">
                  <option value="">All Tasks</option>
                </select>
              </div>
              <div class="form-group">
                <button type="button" id="filterSubmissions" class="btn-secondary">Apply Filter</button>
              </div>
            </div>
          </div>
          
          <div class="table-container">
            <table class='data-table' id='submissionsTable'>
              <thead>
                <tr>
                  <th>Submission ID</th>
                  <th>Task Title</th>
                  <th>Student</th>
                  <th>File Name</th>
                  <th>Submitted Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>

        <!-- TAB CONTENT: NOTIFICATIONS -->
        <div class="tab-content" id="notifications">
          <div class="compact-form">
            <h3>Notification Center</h3>
            <div class="form-row">
              <div class="form-group">
                <button type="button" id="markAllRead" class="btn-secondary">Mark All Read</button>
                <button type="button" id="refreshNotifications" class="btn-primary">Refresh</button>
              </div>
            </div>
          </div>
          
          <div class="notification-list" id="notificationList">
            <!-- Notifications will be loaded here -->
          </div>
        </div>
      </div>
    </section>

    <!-- DELETE TASK MODAL -->
    <div id="deleteTaskModal" class="modal">
      <div class="modal-content modal-content-small">
        <span class="modal-close" onclick="closeDeleteTaskModal()">&times;</span>
        <h2>Delete Task Confirmation</h2>
        
        <div class="compact-form">
          <input type="hidden" id="deleteTaskId">
          
          <div class="form-section">
            <div class="form-group">
              <label for="deleteTaskTitle">Task to Delete</label>
              <input type="text" id="deleteTaskTitle" readonly>
            </div>
            
            <div class="form-group">
              <label for="deleteTaskReason">Reason for Deletion</label>
              <textarea id="deleteTaskReason" rows="3" required></textarea>
            </div>
          </div>
          
          <div class="form-actions">
            <button type="button" id="confirmDeleteTask" class="btn-primary">Confirm Delete</button>
            <button type="button" onclick="closeDeleteTaskModal()" class="btn-secondary">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <!-- REVIEW SUBMISSION MODAL -->
    <div id="reviewModal" class="modal">
      <div class="modal-content">
        <span class="modal-close" onclick="closeReviewModal()">&times;</span>
        <h2>Review Submission</h2>
        
        <div class="compact-form">
          <input type="hidden" id="reviewSubmissionId">
          
          <div class="form-section">
            <div class="form-row">
              <div class="form-group">
                <label for="reviewTaskTitle">Task</label>
                <input type="text" id="reviewTaskTitle" readonly>
              </div>
              <div class="form-group">
                <label for="reviewStudent">Student</label>
                <input type="text" id="reviewStudent" readonly>
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="reviewFileName">File Name</label>
                <input type="text" id="reviewFileName" readonly>
              </div>
              <div class="form-group">
                <label for="reviewSubmissionDate">Submission Date</label>
                <input type="text" id="reviewSubmissionDate" readonly>
              </div>
            </div>
            
            <div class="form-group">
              <label for="reviewStatus">Review Decision</label>
              <select id="reviewStatus" required>
                <option value="">Select Decision</option>
                <option value="approved">Approve</option>
                <option value="rejected">Reject</option>
                <option value="pending">Mark as Pending</option>
              </select>
            </div>
          </div>
          
          <div class="form-actions">
            <button type="button" id="confirmReview" class="btn-primary">Submit Review</button>
            <button type="button" onclick="closeReviewModal()" class="btn-secondary">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    // Modal functions
    function closeDeleteTaskModal() {
      document.getElementById("deleteTaskModal").style.display = "none";
    }

    function openDeleteTaskModal() {
      document.getElementById("deleteTaskModal").style.display = "flex";
    }

    function closeReviewModal() {
      document.getElementById("reviewModal").style.display = "none";
    }

    function openReviewModal() {
      document.getElementById("reviewModal").style.display = "flex";
    }

    // Clear task form
    function clearTaskForm() {
      document.getElementById('taskTitle').value = '';
      document.getElementById('taskCategory').value = '';
      document.getElementById('taskDeadline').value = '';
      document.getElementById('taskDescription').value = '';
    }

    // Tab switcher
    const tabLinks = document.querySelectorAll(".tab-link");
    const tabContents = document.querySelectorAll(".tab-content");

    tabLinks.forEach(link => {
      link.addEventListener("click", () => {
        tabLinks.forEach(l => l.classList.remove("active"));
        tabContents.forEach(c => c.classList.remove("active"));

        link.classList.add("active");
        document.getElementById(link.dataset.tab).classList.add("active");
      });
    });
  </script>

  <?php require_once("modal.php");?>

  <script>
    $(document).ready(function(){
      let tasksTable;
      let submissionsTable;
      const currentUserId = 1; // TODO: Get from session

      // Initialize dropdowns
      function loadDropdowns() {
        // Load task categories
        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: { CALL: 6 }, // Get task categories
          dataType: 'json',
          success: function(result) {
            if(result.data) {
              $('#taskCategory, #submissionTask').empty().append('<option value="">Select Category</option>');
              result.data.forEach(function(category) {
                $('#taskCategory').append(`<option value="${category.task_category_id}">${category.task_category}</option>`);
                $('#submissionTask').append(`<option value="${category.task_category_id}">${category.task_category}</option>`);
              });
            }
          }
        });
      }

      // DataTable initialization for tasks
      function initTasksTable(){
        tasksTable = $("#tasksTable").DataTable({
          ajax:{
            url: 'ajax.php',
            type: 'post',
            data: { CALL: 23 }, // Get all tasks
            dataType:'json',
          },
          responsive: true,
          scroll: '50vh',
          scrollCollapse: true, 
          paging: true,
          columns: [
            { data: "task_id" },
            { data: "task_title" },
            { data: "task_category" },
            { data: "task_deadline" },
            { data: "submission_count" },
            { data: "approved_count" },
            { data: "pending_count" },
            { 
              data: 'task_id',
              render: function(data, type, row) {
                return `
                  <button class="btn-small viewSubmissionsBtn" data-id="${data}" data-title="${row.task_title}">
                    View Submissions
                  </button>
                  <button class="btn-small btn-danger deleteTaskBtn" data-id="${data}" data-title="${row.task_title}">
                    Delete
                  </button>
                `;
              }
            }
          ],
          columnDefs: [
            { targets: 0, visible: false, searchable: false }
          ],
          initComplete: function(settings, json) {
            updateTaskStats(json.data);
          }
        });
      }

      // DataTable initialization for submissions
      function initSubmissionsTable(){
        submissionsTable = $("#submissionsTable").DataTable({
          ajax:{
            url: 'ajax.php',
            type: 'post',
            data: { CALL: 27 }, // Get all submissions
            dataType:'json',
          },
          responsive: true,
          scroll: '50vh',
          scrollCollapse: true, 
          paging: true,
          columns: [
            { data: "task_submission_id" },
            { data: "task_title" },
            { 
              data: null,
              render: function(data, type, row) {
                return `${row.fname} ${row.lname}`;
              }
            },
            { data: "file_name" },
            { 
              data: "datetime_uploaded",
              render: function(data) {
                return new Date(data).toLocaleDateString();
              }
            },
            { 
              data: "check_status",
              render: function(data) {
                const statusClass = data === 'approved' ? 'success' : 
                                  data === 'rejected' ? 'error' : 'warning';
                return `<span class="status ${statusClass}">${data.toUpperCase()}</span>`;
              }
            },
            { 
              data: 'task_submission_id',
              render: function(data, type, row) {
                return `
                  <button class="btn-small reviewBtn" data-id="${data}" 
                          data-task="${row.task_title}" data-student="${row.fname} ${row.lname}"
                          data-file="${row.file_name}" data-date="${row.datetime_uploaded}"
                          data-status="${row.check_status}">
                    Review
                  </button>
                `;
              }
            }
          ],
          columnDefs: [
            { targets: 0, visible: false, searchable: false }
          ]
        });
      }

      // Update task statistics
      function updateTaskStats(data) {
        const totalTasks = data.length;
        let totalPending = 0;
        let totalApproved = 0;
        
        data.forEach(task => {
          totalPending += parseInt(task.pending_count || 0);
          totalApproved += parseInt(task.approved_count || 0);
        });
        
        $('#totalTasks').text(totalTasks);
        $('#pendingSubmissions').text(totalPending);
        $('#approvedSubmissions').text(totalApproved);
      }

      // Initialize tables and dropdowns
      initTasksTable();
      initSubmissionsTable();
      loadDropdowns();
      loadNotifications();
      updateNotificationCount();

      // Create task
      $('#createTaskBtn').click(function() {
        const taskData = {
          task_title: $('#taskTitle').val(),
          task_category_id: $('#taskCategory').val(),
          task_deadline: $('#taskDeadline').val(),
          task_description: $('#taskDescription').val()
        };

        // Validation
        if (!taskData.task_title.trim() || !taskData.task_category_id || 
            !taskData.task_deadline || !taskData.task_description.trim()) {
          openModal("ERROR", "Please fill in all fields");
          return;
        }

        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 22, // Create task
            DATA: taskData
          },
          dataType: 'json',
          success: function(result) {
            openModal(result.status, result.msg);
            if (result.status === "SUCCESS") {
              clearTaskForm();
              tasksTable.ajax.reload();
              submissionsTable.ajax.reload();
            }
          }
        });
      });

      // Delete task button
      $(document).on("click", ".deleteTaskBtn", function() {
        const taskId = $(this).data('id');
        const taskTitle = $(this).data('title');
        
        $('#deleteTaskId').val(taskId);
        $('#deleteTaskTitle').val(taskTitle);
        openDeleteTaskModal();
      });

      // Confirm delete task
      $('#confirmDeleteTask').click(function() {
        const deleteData = {
          task_id: $('#deleteTaskId').val(),
          reason: $('#deleteTaskReason').val()
        };

        if (!deleteData.reason.trim()) {
          openModal("ERROR", "Please provide a reason for deletion");
          return;
        }

        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 28, // Delete task
            DATA: deleteData
          },
          dataType: 'json',
          success: function(result) {
            openModal(result.status, result.msg);
            if (result.status === "SUCCESS") {
              closeDeleteTaskModal();
              tasksTable.ajax.reload();
              submissionsTable.ajax.reload();
            }
          }
        });
      });

      // Review submission button
      $(document).on("click", ".reviewBtn", function() {
        const submissionId = $(this).data('id');
        const taskTitle = $(this).data('task');
        const student = $(this).data('student');
        const fileName = $(this).data('file');
        const date = $(this).data('date');
        const status = $(this).data('status');
        
        $('#reviewSubmissionId').val(submissionId);
        $('#reviewTaskTitle').val(taskTitle);
        $('#reviewStudent').val(student);
        $('#reviewFileName').val(fileName);
        $('#reviewSubmissionDate').val(new Date(date).toLocaleDateString());
        $('#reviewStatus').val(status);
        
        openReviewModal();
      });

      // Confirm review
      $('#confirmReview').click(function() {
        const reviewData = {
          task_submission_id: $('#reviewSubmissionId').val(),
          check_status: $('#reviewStatus').val()
        };

        if (!reviewData.check_status) {
          openModal("ERROR", "Please select a review decision");
          return;
        }

        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 26, // Review submission
            DATA: reviewData
          },
          dataType: 'json',
          success: function(result) {
            openModal(result.status, result.msg);
            if (result.status === "SUCCESS") {
              closeReviewModal();
              submissionsTable.ajax.reload();
            }
          }
        });
      });

      // Load notifications
      function loadNotifications() {
        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 29, // Get notifications
            USER_ID: currentUserId
          },
          dataType: 'json',
          success: function(result) {
            if (result.data) {
              displayNotifications(result.data);
            }
          }
        });
      }

      // Display notifications
      function displayNotifications(notifications) {
        const notificationList = $('#notificationList');
        notificationList.empty();

        if (notifications.length === 0) {
          notificationList.append('<div class="no-notifications">No notifications</div>');
          return;
        }

        notifications.forEach(function(notification) {
          const isRead = notification.is_read == 1;
          const notificationClass = isRead ? 'notification-item read' : 'notification-item unread';
          
          notificationList.append(`
            <div class="${notificationClass}" data-id="${notification.notification_id}">
              <div class="notification-title">${notification.title}</div>
              <div class="notification-message">${notification.message}</div>
              <div class="notification-date">${new Date(notification.datetime_created).toLocaleString()}</div>
              ${!isRead ? '<button class="btn-small mark-read-btn">Mark Read</button>' : ''}
            </div>
          `);
        });
      }

      // Update notification count
      function updateNotificationCount() {
        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 31, // Get unread count
            USER_ID: currentUserId
          },
          dataType: 'json',
          success: function(result) {
            const count = result.count || 0;
            $('#notificationCount').text(count);
            $('#notificationCount').toggle(count > 0);
          }
        });
      }

      // Mark notification as read
      $(document).on('click', '.mark-read-btn', function() {
        const notificationId = $(this).closest('.notification-item').data('id');
        
        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 30, // Mark as read
            NOTIFICATION_ID: notificationId
          },
          dataType: 'json',
          success: function(result) {
            loadNotifications();
            updateNotificationCount();
          }
        });
      });

      // Refresh notifications
      $('#refreshNotifications').click(function() {
        loadNotifications();
        updateNotificationCount();
      });

      // Notification bell click
      $('#notificationBell').click(function() {
        // Switch to notifications tab
        tabLinks.forEach(l => l.classList.remove("active"));
        tabContents.forEach(c => c.classList.remove("active"));
        
        document.querySelector('[data-tab="notifications"]').classList.add("active");
        document.getElementById("notifications").classList.add("active");
        
        loadNotifications();
      });

      // Auto-refresh notifications every 30 seconds
      setInterval(function() {
        updateNotificationCount();
      }, 30000);
    });
  </script>
</body>
</html>