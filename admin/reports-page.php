<?php
require_once __DIR__ . '/../resources/objects/db_config.php';
require_once __DIR__ . '/../resources/objects/main_class.php';

session_start();
$embedMode = isset($_GET['embed']);
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
  <title>Reports & Analytics - UASG</title>
  
  <link rel="stylesheet" href="../resources/style.css">
  <link rel="stylesheet" href="../resources/theme-overrides.css">
  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
  <style>
    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    .stat-card {
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .stat-card h3 {
      margin: 0 0 0.5rem 0;
      font-size: 0.9rem;
      color: #666;
      font-weight: 600;
      text-transform: uppercase;
    }
    .stat-value {
      font-size: 2.5rem;
      font-weight: bold;
      color: #1877f2;
      margin: 0;
    }
    .stat-label {
      font-size: 0.85rem;
      color: #999;
      margin-top: 0.25rem;
    }
    .filter-section {
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      margin-bottom: 1.5rem;
    }
    .filter-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1rem;
    }
    .chart-container {
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      margin-bottom: 1.5rem;
    }
    .actions {
      display: flex;
      gap: 0.5rem;
    }
    .btn-sm {
      padding: 0.25rem 0.5rem;
      font-size: 0.85rem;
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
  </style>
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
  <script src='../js/datatable.js'></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body<?php if($embedMode) echo ' style="padding:0;margin:0;background:transparent;"'; ?>>
  <?php if(!$embedMode): ?>
  <?php require_once("header.php");?>
  <header class="topbar">
    <h1>Reports & Analytics</h1>
    <div class="user-info">
      <span>Welcome, Admin</span>
    </div>
  </header>
  
  <!-- MAIN -->
  <main class="main">
    <!-- SIDEBAR -->
    <?php require_once("sidebar.php");?>

    <?php endif; ?>
    <section class="content">
      <!-- STATISTICS CARDS -->
      <div class="dashboard-grid">
        <div class="stat-card">
          <h3>Total Tasks</h3>
          <p class="stat-value" id="totalTasks">0</p>
          <p class="stat-label">All time</p>
        </div>
        <div class="stat-card">
          <h3>Total Uploads</h3>
          <p class="stat-value" id="totalUploads">0</p>
          <p class="stat-label">All time</p>
        </div>
        <div class="stat-card">
          <h3>Total Submissions</h3>
          <p class="stat-value" id="totalSubmissions">0</p>
          <p class="stat-label">All time</p>
        </div>
        <div class="stat-card">
          <h3>Active Members</h3>
          <p class="stat-value" id="activeMembers">0</p>
          <p class="stat-label">Students</p>
        </div>
      </div>

      <!-- FILTERS -->
      <div class="filter-section">
        <h3>Filter Reports</h3>
        <div class="filter-grid">
          <div class="form-group">
            <label for="filterType">Filter Type</label>
            <select id="filterType">
              <option value="all">All Time</option>
              <option value="month">By Month</option>
              <option value="year">By Year</option>
              <option value="date">By Date Range</option>
            </select>
          </div>
          <div class="form-group" id="monthFilter" style="display:none;">
            <label for="filterMonth">Month</label>
            <input type="month" id="filterMonth">
          </div>
          <div class="form-group" id="yearFilter" style="display:none;">
            <label for="filterYear">Year</label>
            <input type="number" id="filterYear" min="2020" max="2030" value="2025">
          </div>
          <div class="form-group" id="dateFromFilter" style="display:none;">
            <label for="filterDateFrom">From Date</label>
            <input type="date" id="filterDateFrom">
          </div>
          <div class="form-group" id="dateToFilter" style="display:none;">
            <label for="filterDateTo">To Date</label>
            <input type="date" id="filterDateTo">
          </div>
        </div>
        <div class="form-actions">
          <button id="applyFilter" class="btn-primary">Apply Filter</button>
          <button id="resetFilter" class="btn-secondary">Reset</button>
          <button id="exportReport" class="btn-secondary">Export to CSV</button>
        </div>
      </div>

      <!-- CHARTS -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        <div class="chart-container" style="margin-bottom: 0;">
          <h3>Tasks Overview</h3>
          <canvas id="tasksChart" height="100"></canvas>
        </div>

        <div class="chart-container" style="margin-bottom: 0;">
          <h3>Uploads by Category</h3>
          <canvas id="uploadsChart" height="100"></canvas>
        </div>
      </div>

      <!-- TABS -->
      <div class="card">
        <div class="tabs">
          <button class="tab-link active" data-tab="tasks-report">Tasks Report</button>
          <button class="tab-link" data-tab="uploads-report">Uploads Report</button>
          <button class="tab-link" data-tab="submissions-report">Submissions Report</button>
        </div>

        <!-- TASKS REPORT TAB -->
        <div id="tasks-report" class="tab-content active">
          <div class="table-container">
            <table id="tasksReportTable" class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Category</th>
                  <th>Title</th>
                  <th>Description</th>
                  <th>Deadline</th>
                  <th>Submissions</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>

        <!-- UPLOADS REPORT TAB -->
        <div id="uploads-report" class="tab-content">
          <div class="table-container">
            <table id="uploadsReportTable" class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>File Name</th>
                  <th>Category</th>
                  <th>Uploaded By</th>
                  <th>Upload Date</th>
                  <th>File Type</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>

        <!-- SUBMISSIONS REPORT TAB -->
        <div id="submissions-report" class="tab-content">
          <div class="table-container">
            <table id="submissionsReportTable" class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Task</th>
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
      </div>
    <?php if(!$embedMode): ?></section><?php endif; ?>
  <?php if(!$embedMode): ?></main><?php endif; ?>

<script>
(function($) {
    let tasksChart, uploadsChart;
    let currentFilters = { type: 'all' };

    // Tab switching
    $('.tab-link').on('click', function() {
        const target = $(this).data('tab');
        $('.tab-link').removeClass('active');
        $('.tab-content').removeClass('active');
        $(this).addClass('active');
        $('#' + target).addClass('active');
    });

    // Filter type change
    $('#filterType').on('change', function() {
        const type = $(this).val();
        $('#monthFilter, #yearFilter, #dateFromFilter, #dateToFilter').hide();
        
        if (type === 'month') {
            $('#monthFilter').show();
        } else if (type === 'year') {
            $('#yearFilter').show();
        } else if (type === 'date') {
            $('#dateFromFilter, #dateToFilter').show();
        }
    });

    // Initialize DataTables
    const tasksReportTable = $('#tasksReportTable').DataTable({
        ajax: {
            url: 'ajax.php',
            type: 'POST',
            data: function(d) {
                d.CALL = 53; // Get tasks report
                d.filters = currentFilters;
                return d;
            },
            dataSrc: function(json) { return json.data || []; },
            error: function(xhr, error, code) {
                console.error('AJAX Error for tasks report:', error, code);
            }
        },
        columns: [
            { data: 'task_id' },
            { data: 'category_name' },
            { data: 'task_title' },
            { 
                data: 'task_description',
                render: function(data) {
                    return data.length > 50 ? data.substring(0, 50) + '...' : data;
                }
            },
            { data: 'task_deadline' },
            { data: 'submission_count' },
            {
                data: null,
                render: function(data, type, row) {
                    const today = new Date();
                    const deadline = new Date(row.task_deadline);
                    const hasSubmissions = row.submission_count > 0;
                    
                    if (deadline < today && row.submission_count == 0) {
                        return '<span class="status-badge status-overdue">Overdue</span>';
                    } else if (hasSubmissions) {
                        return '<span class="status-badge status-submitted">Active</span>';
                    } else {
                        return '<span class="status-badge status-pending">Pending</span>';
                    }
                }
            }
        ]
    });

    const uploadsReportTable = $('#uploadsReportTable').DataTable({
        ajax: {
            url: 'ajax.php',
            type: 'POST',
            data: function(d) {
                d.CALL = 54; // Get uploads report
                d.filters = currentFilters;
                return d;
            },
            dataSrc: function(json) { return json.data || []; },
            error: function(xhr, error, code) {
                console.error('AJAX Error for uploads report:', error, code);
            }
        },
        columns: [
            { data: 'file_upload_id' },
            { data: 'file_name' },
            { data: 'category_name' },
            { data: 'uploader_name' },
            { data: 'datetime_uploaded' },
            { data: 'mime_type' },
            { 
                data: null, 
                orderable: false,
                render: function(data, type, row) {
                    return `<a href="ajax.php?CALL=download&file_id=${row.file_upload_id}" class="btn-sm btn-primary">Download</a>`;
                }
            }
        ]
    });

    const submissionsReportTable = $('#submissionsReportTable').DataTable({
        ajax: {
            url: 'ajax.php',
            type: 'POST',
            data: function(d) {
                d.CALL = 55; // Get submissions report
                d.filters = currentFilters;
                return d;
            },
            dataSrc: function(json) { return json.data || []; },
            error: function(xhr, error, code) {
                console.error('AJAX Error for submissions report:', error, code);
            }
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
                    return `<a href="ajax.php?CALL=download&file_id=${row.file_upload_id}" class="btn-sm btn-primary">Download</a>`;
                }
            }
        ]
    });

    // Load statistics
    function loadStatistics() {
        $.post('ajax.php', { CALL: 56, filters: currentFilters }, function(resp) {
            if (resp.status === 'SUCCESS') {
                $('#totalTasks').text(resp.data.total_tasks);
                $('#totalUploads').text(resp.data.total_uploads);
                $('#totalSubmissions').text(resp.data.total_submissions);
                $('#activeMembers').text(resp.data.active_members);
                
                updateCharts(resp.data);
            }
        }, 'json');
    }

    // Update charts
    function updateCharts(data) {
        // Tasks Chart
        if (tasksChart) tasksChart.destroy();
        const tasksCtx = document.getElementById('tasksChart').getContext('2d');
        tasksChart = new Chart(tasksCtx, {
            type: 'bar',
            data: {
                labels: data.tasks_by_category.map(item => item.category),
                datasets: [{
                    label: 'Tasks',
                    data: data.tasks_by_category.map(item => item.count),
                    backgroundColor: '#1877f2'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Uploads Chart
        if (uploadsChart) uploadsChart.destroy();
        const uploadsCtx = document.getElementById('uploadsChart').getContext('2d');
        uploadsChart = new Chart(uploadsCtx, {
            type: 'doughnut',
            data: {
                labels: data.uploads_by_category.map(item => item.category),
                datasets: [{
                    data: data.uploads_by_category.map(item => item.count),
                    backgroundColor: ['#1877f2', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });
    }

    // Apply filter
    $('#applyFilter').on('click', function() {
        const type = $('#filterType').val();
        currentFilters = { type: type };
        
        if (type === 'month') {
            currentFilters.month = $('#filterMonth').val();
        } else if (type === 'year') {
            currentFilters.year = $('#filterYear').val();
        } else if (type === 'date') {
            currentFilters.date_from = $('#filterDateFrom').val();
            currentFilters.date_to = $('#filterDateTo').val();
        }
        
        // Reload all data
        tasksReportTable.ajax.reload();
        uploadsReportTable.ajax.reload();
        submissionsReportTable.ajax.reload();
        loadStatistics();
    });

    // Reset filter
    $('#resetFilter').on('click', function() {
        $('#filterType').val('all').trigger('change');
        $('#filterMonth, #filterYear, #filterDateFrom, #filterDateTo').val('');
        currentFilters = { type: 'all' };
        
        tasksReportTable.ajax.reload();
        uploadsReportTable.ajax.reload();
        submissionsReportTable.ajax.reload();
        loadStatistics();
    });

    // Export to CSV
    $('#exportReport').on('click', function() {
        window.open('ajax.php?CALL=57&filters=' + encodeURIComponent(JSON.stringify(currentFilters)), '_blank');
    });

    // Initialize
    loadStatistics();

})(jQuery);
</script>
</body>
</html>
