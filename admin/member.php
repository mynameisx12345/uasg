<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UASG Member Dashboard</title>
  <link rel="stylesheet" href="../resources/style.css">
  <link rel="stylesheet" href="../resources/theme-overrides.css">
  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
  <script src='../js/datatable.js'></script>
</head>
<body>
  <!-- HEADER -->
   <?php require_once("header.php");?>
   <header class="topbar">
    <h1>UASG Member Dashboard</h1>
    <div class="user-info">
      <span>Welcome, Member</span>
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
        <h2>My Tasks & Submissions</h2>

        <!-- TABS -->
        <div class="tabs">
          <button class="tab-link active" data-tab="dashboard">Dashboard</button>
          <button class="tab-link" data-tab="tasks">My Tasks</button>
          <button class="tab-link" data-tab="submit">Submit Work</button>
          <button class="tab-link" data-tab="submissions">My Submissions</button>
          <button class="tab-link" data-tab="notifications">Notifications</button>
        </div>

        <!-- TAB CONTENT: DASHBOARD -->
        <div class="tab-content active" id="dashboard">
          <div class="compact-form">
            <h3>Task Overview</h3>
            <div class="stats-grid">
              <div class="stat-card">
                <h4>Assigned Tasks</h4>
                <div class="stat-number" id="assignedTasks">0</div>
              </div>
              <div class="stat-card">
                <h4>Submitted</h4>
                <div class="stat-number" id="submittedTasks">0</div>
              </div>
              <div class="stat-card">
                <h4>Approved</h4>
                <div class="stat-number" id="approvedTasks">0</div>
              </div>
              <div class="stat-card">
                <h4>Overdue</h4>
                <div class="stat-number" id="overdueTasks">0</div>
              </div>
            </div>
          </div>

          <div class="table-container">
            <h3>Recent Tasks</h3>
            <table class='data-table' id='recentTasksTable'>
              <thead>
                <tr>
                  <th>Task ID</th>
                  <th>Title</th>
                  <th>Category</th>
                  <th>Deadline</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>

        <!-- TAB CONTENT: MY TASKS -->
        <div class="tab-content" id="tasks">
          <div class="compact-form">
            <h3>Filter Tasks</h3>
            <div class="form-row">
              <div class="form-group">
                <label for="taskStatusFilter">Status</label>
                <select id="taskStatusFilter">
                  <option value="">All Tasks</option>
                  <option value="not_submitted">Not Submitted</option>
                  <option value="submitted">Submitted</option>
                  <option value="approved">Approved</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>
              <div class="form-group">
                <label for="taskCategoryFilter">Category</label>
                <select id="taskCategoryFilter">
                  <option value="">All Categories</option>
                </select>
              </div>
              <div class="form-group">
                <button type="button" id="applyTaskFilter" class="btn-secondary">Apply Filter</button>
              </div>
            </div>
          </div>
          
          <div class="table-container">
            <table class='data-table' id='allTasksTable'>
              <thead>
                <tr>
                  <th>Task ID</th>
                  <th>Title</th>
                  <th>Description</th>
                  <th>Category</th>
                  <th>Deadline</th>
                  <th>Status</th>
                  <th>Submission Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>

        <!-- TAB CONTENT: SUBMIT WORK -->
        <div class="tab-content" id="submit">
          <div class="compact-form">
            <h3>Submit Task</h3>
            <div class="form-columns">
              <div class="form-column">
                <div class="form-group">
                  <label for="submitTaskSelect">Select Task</label>
                  <select id="submitTaskSelect" required>
                    <option value="">Choose a task</option>
                  </select>
                </div>
                
                <div class="form-group">
                  <label for="submitFile">Select File</label>
                  <input type="file" id="submitFile" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.png" required>
                </div>
                
                <div class="form-group">
                  <label for="fileCategory">File Category</label>
                  <select id="fileCategory" required>
                    <option value="">Select Category</option>
                  </select>
                </div>
              </div>
              
              <div class="form-column">
                <div class="form-group">
                  <label for="taskDetails">Task Details</label>
                  <textarea id="taskDetails" rows="4" readonly></textarea>
                </div>
                
                <div class="form-group">
                  <label for="taskDeadlineInfo">Deadline</label>
                  <input type="text" id="taskDeadlineInfo" readonly>
                </div>
              </div>
            </div>
            
            <div class="form-actions">
              <button type="button" id="submitTaskBtn" class="btn-primary">Submit Task</button>
              <button type="button" onclick="clearSubmitForm()" class="btn-secondary">Clear Form</button>
            </div>
          </div>
        </div>

        <!-- TAB CONTENT: MY SUBMISSIONS -->
        <div class="tab-content" id="submissions">
          <div class="compact-form">
            <h3>Filter Submissions</h3>
            <div class="form-row">
              <div class="form-group">
                <label for="submissionStatusFilter">Status</label>
                <select id="submissionStatusFilter">
                  <option value="">All Statuses</option>
                  <option value="pending">Pending Review</option>
                  <option value="approved">Approved</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>
              <div class="form-group">
                <button type="button" id="filterMySubmissions" class="btn-secondary">Apply Filter</button>
              </div>
            </div>
          </div>
          
          <div class="table-container">
            <table class='data-table' id='mySubmissionsTable'>
              <thead>
                <tr>
                  <th>Submission ID</th>
                  <th>Task Title</th>
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

    <!-- VIEW TASK MODAL -->
    <div id="viewTaskModal" class="modal">
      <div class="modal-content">
        <span class="modal-close" onclick="closeViewTaskModal()">&times;</span>
        <h2>Task Details</h2>
        
        <div class="compact-form">
          <div class="form-section">
            <div class="form-row">
              <div class="form-group">
                <label for="viewTaskTitle">Task Title</label>
                <input type="text" id="viewTaskTitle" readonly>
              </div>
              <div class="form-group">
                <label for="viewTaskCategory">Category</label>
                <input type="text" id="viewTaskCategory" readonly>
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="viewTaskDeadline">Deadline</label>
                <input type="text" id="viewTaskDeadline" readonly>
              </div>
              <div class="form-group">
                <label for="viewTaskStatus">Status</label>
                <input type="text" id="viewTaskStatus" readonly>
              </div>
            </div>
            
            <div class="form-group">
              <label for="viewTaskDescription">Description</label>
              <textarea id="viewTaskDescription" rows="4" readonly></textarea>
            </div>
          </div>
          
          <div class="form-actions">
            <button type="button" onclick="closeViewTaskModal()" class="btn-secondary">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- RESUBMIT MODAL -->
    <div id="resubmitModal" class="modal">
      <div class="modal-content modal-content-small">
        <span class="modal-close" onclick="closeResubmitModal()">&times;</span>
        <h2>Resubmit Task</h2>
        
        <div class="compact-form">
          <input type="hidden" id="resubmitTaskId">
          
          <div class="form-section">
            <div class="form-group">
              <label for="resubmitTaskTitle">Task</label>
              <input type="text" id="resubmitTaskTitle" readonly>
            </div>
            
            <div class="form-group">
              <label for="resubmitFile">New File</label>
              <input type="file" id="resubmitFile" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.png" required>
            </div>
          </div>
          
          <div class="form-actions">
            <button type="button" id="confirmResubmit" class="btn-primary">Resubmit</button>
            <button type="button" onclick="closeResubmitModal()" class="btn-secondary">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    // Modal functions
    function closeViewTaskModal() {
      document.getElementById("viewTaskModal").style.display = "none";
    }

    function openViewTaskModal() {
      document.getElementById("viewTaskModal").style.display = "flex";
    }

    function closeResubmitModal() {
      document.getElementById("resubmitModal").style.display = "none";
    }

    function openResubmitModal() {
      document.getElementById("resubmitModal").style.display = "flex";
    }

    // Clear submit form
    function clearSubmitForm() {
      document.getElementById('submitTaskSelect').value = '';
      document.getElementById('submitFile').value = '';
      document.getElementById('fileCategory').value = '';
      document.getElementById('taskDetails').value = '';
      document.getElementById('taskDeadlineInfo').value = '';
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
      let recentTasksTable;
      let allTasksTable;
      let mySubmissionsTable;
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
              $('#taskCategoryFilter').empty().append('<option value="">All Categories</option>');
              result.data.forEach(function(category) {
                $('#taskCategoryFilter').append(`<option value="${category.task_category_id}">${category.task_category}</option>`);
              });
            }
          }
        });

        // Load file categories
        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: { CALL: 5 }, // Get file categories
          dataType: 'json',
          success: function(result) {
            if(result.data) {
              $('#fileCategory').empty().append('<option value="">Select Category</option>');
              result.data.forEach(function(category) {
                $('#fileCategory').append(`<option value="${category.file_category_id}">${category.file_category}</option>`);
              });
            }
          }
        });

        // Load unsubmitted tasks for submission dropdown
        loadTasksForSubmission();
      }

      // Load tasks for submission dropdown
      function loadTasksForSubmission() {
        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: { 
            CALL: 24, // Get tasks for member
            USER_ID: currentUserId 
          },
          dataType: 'json',
          success: function(result) {
            if(result.data) {
              $('#submitTaskSelect').empty().append('<option value="">Choose a task</option>');
              result.data.forEach(function(task) {
                if (!task.task_submission_id || task.check_status === 'rejected') {
                  $('#submitTaskSelect').append(`<option value="${task.task_id}" 
                    data-description="${task.task_description}" 
                    data-deadline="${task.task_deadline}">${task.task_title}</option>`);
                }
              });
            }
          }
        });
      }

      // DataTable initialization for recent tasks
      function initRecentTasksTable(){
        recentTasksTable = $("#recentTasksTable").DataTable({
          ajax:{
            url: 'ajax.php',
            type: 'post',
            data: { 
              CALL: 24, // Get tasks for member
              USER_ID: currentUserId 
            },
            dataType:'json',
          },
          responsive: true,
          scroll: '50vh',
          scrollCollapse: true, 
          paging: true,
          pageLength: 5,
          columns: [
            { data: "task_id" },
            { data: "task_title" },
            { data: "task_category" },
            { 
              data: "task_deadline",
              render: function(data) {
                const deadline = new Date(data);
                const today = new Date();
                const isOverdue = deadline < today;
                const deadlineStr = deadline.toLocaleDateString();
                return isOverdue ? `<span class="text-danger">${deadlineStr} (Overdue)</span>` : deadlineStr;
              }
            },
            { 
              data: null,
              render: function(data, type, row) {
                if (!row.task_submission_id) {
                  return '<span class="status warning">Not Submitted</span>';
                } else {
                  const statusClass = row.check_status === 'approved' ? 'success' : 
                                    row.check_status === 'rejected' ? 'error' : 'warning';
                  return `<span class="status ${statusClass}">${row.check_status.toUpperCase()}</span>`;
                }
              }
            },
            { 
              data: 'task_id',
              render: function(data, type, row) {
                let buttons = `<button class="btn-small viewTaskBtn" data-id="${data}" 
                              data-title="${row.task_title}" data-category="${row.task_category}"
                              data-deadline="${row.task_deadline}" data-description="${row.task_description}"
                              data-status="${row.check_status || 'Not Submitted'}">
                              View Details
                              </button>`;
                
                if (!row.task_submission_id || row.check_status === 'rejected') {
                  buttons += ` <button class="btn-small btn-primary submitForTaskBtn" data-id="${data}">
                              Submit
                              </button>`;
                }
                
                return buttons;
              }
            }
          ],
          columnDefs: [
            { targets: 0, visible: false, searchable: false }
          ],
          initComplete: function(settings, json) {
            updateDashboardStats(json.data);
          }
        });
      }

      // DataTable initialization for all tasks
      function initAllTasksTable(){
        allTasksTable = $("#allTasksTable").DataTable({
          ajax:{
            url: 'ajax.php',
            type: 'post',
            data: { 
              CALL: 24, // Get tasks for member
              USER_ID: currentUserId 
            },
            dataType:'json',
          },
          responsive: true,
          scroll: '50vh',
          scrollCollapse: true, 
          paging: true,
          columns: [
            { data: "task_id" },
            { data: "task_title" },
            { data: "task_description" },
            { data: "task_category" },
            { 
              data: "task_deadline",
              render: function(data) {
                const deadline = new Date(data);
                const today = new Date();
                const isOverdue = deadline < today;
                const deadlineStr = deadline.toLocaleDateString();
                return isOverdue ? `<span class="text-danger">${deadlineStr}</span>` : deadlineStr;
              }
            },
            { 
              data: null,
              render: function(data, type, row) {
                if (!row.task_submission_id) {
                  return '<span class="status warning">Not Submitted</span>';
                } else {
                  const statusClass = row.check_status === 'approved' ? 'success' : 
                                    row.check_status === 'rejected' ? 'error' : 'warning';
                  return `<span class="status ${statusClass}">${row.check_status.toUpperCase()}</span>`;
                }
              }
            },
            { 
              data: "submission_date",
              render: function(data) {
                return data ? new Date(data).toLocaleDateString() : 'Not submitted';
              }
            },
            { 
              data: 'task_id',
              render: function(data, type, row) {
                let buttons = `<button class="btn-small viewTaskBtn" data-id="${data}" 
                              data-title="${row.task_title}" data-category="${row.task_category}"
                              data-deadline="${row.task_deadline}" data-description="${row.task_description}"
                              data-status="${row.check_status || 'Not Submitted'}">
                              View
                              </button>`;
                
                if (row.task_submission_id && row.check_status === 'rejected') {
                  buttons += ` <button class="btn-small resubmitBtn" data-id="${data}" data-title="${row.task_title}">
                              Resubmit
                              </button>`;
                } else if (!row.task_submission_id) {
                  buttons += ` <button class="btn-small btn-primary submitForTaskBtn" data-id="${data}">
                              Submit
                              </button>`;
                }
                
                return buttons;
              }
            }
          ],
          columnDefs: [
            { targets: 0, visible: false, searchable: false }
          ]
        });
      }

      // DataTable initialization for my submissions
      function initMySubmissionsTable(){
        mySubmissionsTable = $("#mySubmissionsTable").DataTable({
          ajax:{
            url: 'ajax.php',
            type: 'post',
            data: { 
              CALL: 27, // Get submissions with user filter
              USER_ID: currentUserId
            },
            dataType:'json',
          },
          responsive: true,
          scroll: '50vh',
          scrollCollapse: true, 
          paging: true,
          columns: [
            { data: "task_submission_id" },
            { data: "task_title" },
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
                  <button class="btn-small downloadBtn" data-file="${row.file_name}">
                    Download
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

      // Update dashboard statistics
      function updateDashboardStats(data) {
        let assigned = data.length;
        let submitted = 0;
        let approved = 0;
        let overdue = 0;
        const today = new Date();
        
        data.forEach(task => {
          if (task.task_submission_id) {
            submitted++;
            if (task.check_status === 'approved') {
              approved++;
            }
          } else {
            const deadline = new Date(task.task_deadline);
            if (deadline < today) {
              overdue++;
            }
          }
        });
        
        $('#assignedTasks').text(assigned);
        $('#submittedTasks').text(submitted);
        $('#approvedTasks').text(approved);
        $('#overdueTasks').text(overdue);
      }

      // Initialize tables and dropdowns
      initRecentTasksTable();
      initAllTasksTable();
      initMySubmissionsTable();
      loadDropdowns();
      loadNotifications();
      updateNotificationCount();

      // Task selection change for submission
      $('#submitTaskSelect').change(function() {
        const selectedOption = $(this).find('option:selected');
        const description = selectedOption.data('description');
        const deadline = selectedOption.data('deadline');
        
        $('#taskDetails').val(description || '');
        $('#taskDeadlineInfo').val(deadline ? new Date(deadline).toLocaleDateString() : '');
      });

      // Submit task
      $('#submitTaskBtn').click(function() {
        const taskId = $('#submitTaskSelect').val();
        const fileInput = document.getElementById('submitFile');
        const categoryId = $('#fileCategory').val();

        if (!taskId || !fileInput.files[0] || !categoryId) {
          openModal("ERROR", "Please fill in all fields and select a file");
          return;
        }

        // First upload the file
        const file = fileInput.files[0];
        const uploadData = {
          file_category_id: categoryId,
          mime_type: file.type,
          file_name: file.name,
          drive_id: 'temp_' + Date.now(), // Temporary ID
          uploaded_by: currentUserId
        };

        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 14, // Upload file
            DATA: uploadData
          },
          dataType: 'json',
          success: function(result) {
            if (result.status === "SUCCESS") {
              // Now submit the task
              const submissionData = {
                task_id: taskId,
                file_upload_id: result.file_id,
                uploaded_by: currentUserId
              };

              $.ajax({
                url: 'ajax.php',
                type: 'post',
                data: {
                  CALL: 25, // Submit task
                  DATA: submissionData
                },
                dataType: 'json',
                success: function(result) {
                  openModal(result.status, result.msg);
                  if (result.status === "SUCCESS") {
                    clearSubmitForm();
                    recentTasksTable.ajax.reload();
                    allTasksTable.ajax.reload();
                    mySubmissionsTable.ajax.reload();
                    loadTasksForSubmission();
                  }
                }
              });
            } else {
              openModal(result.status, result.msg);
            }
          }
        });
      });

      // View task button
      $(document).on("click", ".viewTaskBtn", function() {
        const taskId = $(this).data('id');
        const title = $(this).data('title');
        const category = $(this).data('category');
        const deadline = $(this).data('deadline');
        const description = $(this).data('description');
        const status = $(this).data('status');
        
        $('#viewTaskTitle').val(title);
        $('#viewTaskCategory').val(category);
        $('#viewTaskDeadline').val(new Date(deadline).toLocaleDateString());
        $('#viewTaskDescription').val(description);
        $('#viewTaskStatus').val(status);
        
        openViewTaskModal();
      });

      // Submit for task button (from table)
      $(document).on("click", ".submitForTaskBtn", function() {
        const taskId = $(this).data('id');
        
        // Switch to submit tab and pre-select the task
        tabLinks.forEach(l => l.classList.remove("active"));
        tabContents.forEach(c => c.classList.remove("active"));
        
        document.querySelector('[data-tab="submit"]').classList.add("active");
        document.getElementById("submit").classList.add("active");
        
        $('#submitTaskSelect').val(taskId).trigger('change');
      });

      // Resubmit button
      $(document).on("click", ".resubmitBtn", function() {
        const taskId = $(this).data('id');
        const taskTitle = $(this).data('title');
        
        $('#resubmitTaskId').val(taskId);
        $('#resubmitTaskTitle').val(taskTitle);
        openResubmitModal();
      });

      // Confirm resubmit
      $('#confirmResubmit').click(function() {
        const taskId = $('#resubmitTaskId').val();
        const fileInput = document.getElementById('resubmitFile');

        if (!fileInput.files[0]) {
          openModal("ERROR", "Please select a file to resubmit");
          return;
        }

        // Similar process as submit - upload file then submit task
        const file = fileInput.files[0];
        const uploadData = {
          file_category_id: 1, // Default category, you might want to get this from the original task
          mime_type: file.type,
          file_name: file.name,
          drive_id: 'temp_' + Date.now(),
          uploaded_by: currentUserId
        };

        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 14, // Upload file
            DATA: uploadData
          },
          dataType: 'json',
          success: function(result) {
            if (result.status === "SUCCESS") {
              const submissionData = {
                task_id: taskId,
                file_upload_id: result.file_id,
                uploaded_by: currentUserId
              };

              $.ajax({
                url: 'ajax.php',
                type: 'post',
                data: {
                  CALL: 25, // Submit task
                  DATA: submissionData
                },
                dataType: 'json',
                success: function(result) {
                  openModal(result.status, result.msg);
                  if (result.status === "SUCCESS") {
                    closeResubmitModal();
                    recentTasksTable.ajax.reload();
                    allTasksTable.ajax.reload();
                    mySubmissionsTable.ajax.reload();
                  }
                }
              });
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