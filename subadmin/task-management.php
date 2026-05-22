<?php
session_start();
require_once("../resources/session.php");
require_once("../resources/objects/permission_class.php");

$session = SessionManager::getInstance();
$session->requireRole(['Adviser', 'adviser', 'Subadmin', 'subadmin']);

$currentUser = $session->getUserData();
$userId = $currentUser['user_id'] ?? null;

if (!$userId) {
    header("Location: ../index.php");
    exit;
}

$canView = SubadminPermission::hasPermission($userId, 'task_management', 'view');
$canCreate = SubadminPermission::hasPermission($userId, 'task_management', 'create');
$canEdit = SubadminPermission::hasPermission($userId, 'task_management', 'edit');
$canDelete = SubadminPermission::hasPermission($userId, 'task_management', 'delete');

if (!$canView) {
    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Task Management - UASG Subadmin</title>
  <link rel="stylesheet" href="../resources/style.css">
  <link rel="stylesheet" href="../resources/theme-overrides.css">
  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
  <style>
    .tab-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .tab-header .tabs { margin-bottom: 0; }
    .btn-create { background: #007bff; color: #fff; border: none; padding: 0.5rem 1.2rem; border-radius: 6px; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 0.4rem; font-weight: 500; }
    .btn-create:hover { background: #0056b3; }
    .dropdown-container { position: relative; display: inline-block; }
    .dropdown-btn { background: #6c757d; color: white; border: none; padding: 0.4rem 0.6rem; font-size: 1.2rem; cursor: pointer; border-radius: 4px; line-height: 1; }
    .dropdown-btn:hover { background: #5a6268; }
    .dropdown-menu { display: none; position: absolute; right: 0; top: 100%; background: white; min-width: 170px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 6px; z-index: 1000; margin-top: 4px; overflow: hidden; }
    .dropdown-menu.show { display: block; }
    .dropdown-item { display: flex; align-items: center; gap: 0.5rem; width: 100%; padding: 0.55rem 1rem; text-align: left; border: none; background: none; cursor: pointer; font-size: 0.85rem; transition: background 0.2s; border-bottom: 1px solid #f0f0f0; }
    .dropdown-item:last-child { border-bottom: none; }
    .dropdown-item:hover { background: #f8f9fa; }
    .dropdown-item.edit { color: #28a745; }
    .dropdown-item.view { color: #007bff; }
    .dropdown-item.delete { color: #dc3545; }
    .dropdown-item:disabled { opacity: 0.5; cursor: not-allowed; }
    .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
    .modal-content { background: #fff; padding: 1.5rem; border-radius: 10px; min-width: 500px; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; font-weight: 600; font-size: 1.15rem; }
    .close-modal { cursor: pointer; font-size: 1.5rem; color: #666; }
    .status-badge { padding: 0.25rem 0.6rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; white-space: nowrap; display: inline-block; }
    .status-pending { background: #f1f5f9; color: #64748b; }
    .status-submitted { background: #fef3c7; color: #92400e; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-overdue { background: #fee2e2; color: #991b1b; }
    .status-rejected { background: #fee2e2; color: #991b1b; }
    .status-closed { background: #dbeafe; color: #1e40af; }
    .status-cancelled { background: #e2e3e5; color: #383d41; }
    .wrap-col { white-space: normal; word-break: break-word; min-width: 160px; }
    .table-container { margin-top: 0.5rem; }
    #tasksTable td, #submissionsTable td { vertical-align: middle; font-size: 12px; padding: 10px 12px; }
    #tasksTable th, #submissionsTable th { font-size: 12px; padding: 10px 12px; }
    #tasksTable thead th, #submissionsTable thead th { background: #f8f9fa !important; color: #475569 !important; border-bottom: 2px solid #e2e8f0 !important; font-weight: 600; }
    #tasksTable tbody tr { transition: background 0.15s, box-shadow 0.15s; }
    #tasksTable tbody tr:hover { background: #f8fafc !important; box-shadow: inset 3px 0 0 #3b82f6; }
    .view-toggle { display: inline-flex; background: #f1f5f9; border-radius: 8px; padding: 3px; gap: 2px; margin-left: 12px; }
    .view-toggle-btn { border: none; background: none; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 500; cursor: pointer; color: #64748b; transition: all 0.2s; }
    .view-toggle-btn.active { background: #fff; color: #1e293b; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .kanban-board { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; min-height: 400px; }
    .kanban-column { background: #f8fafc; border-radius: 10px; padding: 12px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; min-height: 300px; }
    .kanban-column-header { display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; margin-bottom: 10px; border-radius: 6px; font-size: 13px; font-weight: 600; }
    .kanban-column-header .count { background: rgba(0,0,0,0.08); padding: 2px 8px; border-radius: 10px; font-size: 11px; }
    .kanban-column[data-status="pending"] .kanban-column-header { background: #f1f5f9; color: #475569; border-top: 3px solid #94a3b8; }
    .kanban-column[data-status="awaiting_review"] .kanban-column-header { background: #fef3c7; color: #92400e; border-top: 3px solid #f59e0b; }
    .kanban-column[data-status="approved"] .kanban-column-header { background: #d1fae5; color: #065f46; border-top: 3px solid #10b981; }
    .kanban-column[data-status="closed"] .kanban-column-header { background: #dbeafe; color: #1e40af; border-top: 3px solid #3b82f6; }
    .kanban-cards { flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; min-height: 50px; padding: 4px; position: relative; }
    .kanban-card { background: #fff; border-radius: 8px; padding: 12px; border: 1px solid #e2e8f0; cursor: grab; transition: box-shadow 0.2s, transform 0.15s; position: relative; overflow: visible; z-index: 1; }
    .kanban-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-1px); }
    .kanban-card.dropdown-open { z-index: 100; }
    .kanban-card.dragging { opacity: 0.5; transform: rotate(2deg); }
    .kanban-card .card-title { font-size: 12px; font-weight: 600; color: #1e293b; margin-bottom: 6px; line-height: 1.3; }
    .kanban-card .card-category { font-size: 10px; background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 10px; display: inline-block; margin-bottom: 6px; }
    .kanban-card .card-meta { font-size: 11px; color: #64748b; display: flex; flex-direction: column; gap: 3px; }
    .kanban-card.card-overdue { border-left: 3px solid #ef4444; }
    .kanban-card .overdue-indicator { font-size: 10px; color: #dc2626; font-weight: 600; }
    .kanban-card .card-actions { position: absolute; top: 8px; right: 8px; z-index: 10; }
    .kanban-card .card-actions .dropdown-btn { font-size: 0.9rem; padding: 2px 5px; background: transparent; color: #94a3b8; }
    .kanban-card .card-actions .dropdown-btn:hover { color: #475569; background: #f1f5f9; }
    .kanban-card .card-actions .dropdown-menu { z-index: 1100; }
    .kanban-column.drag-valid { border: 2px dashed #10b981; background: #f0fdf4; }
    .kanban-column.drag-invalid { opacity: 0.4; }
    .kanban-column.drag-over { background: #ecfdf5; border-color: #059669; }
    .notification-modal { display: none; position: fixed; top: 20px; right: 20px; z-index: 10000; }
    .notification-modal.notification-show .notification-content { animation: slideIn 0.3s ease-out; }
    .notification-content { display: flex; align-items: center; gap: 15px; background: white; padding: 20px 25px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); min-width: 350px; max-width: 500px; border-left: 5px solid #3b82f6; }
    .notification-content i { font-size: 28px; flex-shrink: 0; }
    .notification-text { flex: 1; }
    .notification-text h3 { margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1e293b; }
    .notification-text p { margin: 0; font-size: 14px; color: #64748b; line-height: 1.5; }
    .notification-close { background: none; border: none; font-size: 24px; color: #94a3b8; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.2s; flex-shrink: 0; }
    .notification-close:hover { background: #f1f5f9; color: #475569; }
    @keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
  </style>
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
  <script src='../js/datatable.js'></script>
</head>
<body>
  <div class="dashboard-container">
    <?php require_once("sidebar.php"); ?>
    <div class="main-content">
      <?php require_once("header.php"); ?>
      <div class="dashboard-content">
        <div class="card">
          <div class="tab-header">
            <div class="tabs">
              <button class="tab-btn active" data-tab="all-tasks">📋 All Tasks</button>
              <button class="tab-btn" data-tab="submissions">📄 Submissions</button>
            </div>
            <?php if ($canCreate): ?>
            <button class="btn-create" id="openCreateTaskModal"><i class="fas fa-plus"></i> Create Task</button>
            <?php endif; ?>
          </div>

          <!-- ALL TASKS TAB -->
          <div id="all-tasks" class="tab-content active">
            <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:12px;">
              <div style="background:#fff;border-radius:10px;padding:14px;border:1px solid #f0f0f0;text-align:center;">
                <div style="font-size:22px;font-weight:700;color:#1e293b;" id="statTotalTasks">0</div>
                <div style="font-size:11px;color:#64748b;margin-top:2px;">Total</div>
              </div>
              <div style="background:#f1f5f9;border-radius:10px;padding:14px;border:1px solid #e2e8f0;text-align:center;">
                <div style="font-size:22px;font-weight:700;color:#64748b;" id="statPendingTasks">0</div>
                <div style="font-size:11px;color:#64748b;margin-top:2px;">Pending</div>
              </div>
              <div style="background:#fee2e2;border-radius:10px;padding:14px;border:1px solid #fecaca;text-align:center;">
                <div style="font-size:22px;font-weight:700;color:#991b1b;" id="statOverdueTasks">0</div>
                <div style="font-size:11px;color:#991b1b;margin-top:2px;">Overdue</div>
              </div>
              <div style="background:#fef3c7;border-radius:10px;padding:14px;border:1px solid #fde68a;text-align:center;">
                <div style="font-size:22px;font-weight:700;color:#92400e;" id="statWithSubmissions">0</div>
                <div style="font-size:11px;color:#92400e;margin-top:2px;">Awaiting Review</div>
              </div>
              <div style="background:#e2e8f0;border-radius:10px;padding:14px;border:1px solid #cbd5e1;text-align:center;">
                <div style="font-size:22px;font-weight:700;color:#334155;" id="statClosedTasks">0</div>
                <div style="font-size:11px;color:#334155;margin-top:2px;">Closed / Cancelled</div>
              </div>
            </div>

            <div style="margin-bottom:14px;text-align:right;">
              <div class="view-toggle">
                <button class="view-toggle-btn" data-view="table">📋 Table</button>
                <button class="view-toggle-btn active" data-view="board">🗂️ Board</button>
              </div>
            </div>

            <!-- Table View -->
            <div id="table-view" style="display:none;border-radius:10px;border:1px solid #e2e8f0;background:#fff;padding:18px;">
              <div class="table-container">
                <table id="tasksTable" class="data-table">
                  <thead><tr><th>ID</th><th>Category</th><th>Title</th><th>Description</th><th>Assigned To</th><th>Deadline</th><th>Status</th><th>Actions</th></tr></thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>

            <!-- Kanban Board View -->
            <div id="board-view">
              <div style="margin-bottom:12px;">
                <input type="text" id="kanbanSearch" placeholder="🔍 Search tasks..." style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;box-sizing:border-box;">
              </div>
              <div class="kanban-board">
                <div class="kanban-column" data-status="pending">
                  <div class="kanban-column-header">Pending <span class="count" id="kanbanCountPending">0</span></div>
                  <div class="kanban-cards" id="kanbanPending"></div>
                </div>
                <div class="kanban-column" data-status="awaiting_review">
                  <div class="kanban-column-header">Awaiting Review <span class="count" id="kanbanCountReview">0</span></div>
                  <div class="kanban-cards" id="kanbanReview"></div>
                </div>
                <div class="kanban-column" data-status="approved">
                  <div class="kanban-column-header">Approved <span class="count" id="kanbanCountApproved">0</span></div>
                  <div class="kanban-cards" id="kanbanApproved"></div>
                </div>
                <div class="kanban-column" data-status="closed">
                  <div class="kanban-column-header">Closed / Cancelled <span class="count" id="kanbanCountClosed">0</span></div>
                  <div class="kanban-cards" id="kanbanClosed"></div>
                  <button id="kanbanLoadMoreClosed" style="display:none;width:100%;padding:8px;border:1px dashed #cbd5e1;background:none;border-radius:6px;color:#64748b;font-size:12px;cursor:pointer;margin-top:6px;">Load 10 more...</button>
                </div>
              </div>
            </div>
          </div>

          <!-- SUBMISSIONS TAB -->
          <div id="submissions" class="tab-content">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
              <div style="background:#fff;border-radius:10px;padding:16px;border:1px solid #f0f0f0;text-align:center;">
                <div style="font-size:24px;font-weight:700;color:#1e293b;" id="statTotalSubs">0</div>
                <div style="font-size:12px;color:#64748b;margin-top:2px;">Total Submissions</div>
              </div>
              <div style="background:#fef3c7;border-radius:10px;padding:16px;border:1px solid #fde68a;text-align:center;">
                <div style="font-size:24px;font-weight:700;color:#92400e;" id="statPendingSubs">0</div>
                <div style="font-size:12px;color:#92400e;margin-top:2px;">Awaiting Review</div>
              </div>
              <div style="background:#d1fae5;border-radius:10px;padding:16px;border:1px solid #bbf7d0;text-align:center;">
                <div style="font-size:24px;font-weight:700;color:#065f46;" id="statApprovedSubs">0</div>
                <div style="font-size:12px;color:#065f46;margin-top:2px;">Approved</div>
              </div>
            </div>
            <div style="border-radius:10px;border:1px solid #e2e8f0;background:#fff;padding:18px;">
              <div class="table-container">
                <table id="submissionsTable" class="data-table">
                  <thead><tr><th>ID</th><th>Task</th><th>Student</th><th>File</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- CREATE TASK MODAL -->
  <?php if ($canCreate): ?>
  <div class="modal" id="createTaskModal">
    <div class="modal-content">
      <div class="modal-header"><span><i class="fas fa-plus-circle"></i> Create New Task</span><span class="close-modal" data-close>&times;</span></div>
      <form id="createTaskForm">
        <div class="form-group"><label for="taskCategory">Task Category *</label><select id="taskCategory" name="task_category_id" required><option value="">Select Category</option></select></div>
        <div class="form-group"><label for="taskTitle">Task Title *</label><input type="text" id="taskTitle" name="task_title" placeholder="Enter task title..." required></div>
        <div class="form-group"><label for="taskDescription">Task Description *</label><textarea id="taskDescription" name="task_description" rows="4" placeholder="Enter task description..." required></textarea></div>
        <div class="form-group"><label for="assignTo">Assign To *</label><select id="assignTo" name="assigned_to" required><option value="">Select a member...</option></select></div>
        <div class="form-group"><label for="taskDeadline">Deadline *</label><input type="date" id="taskDeadline" name="task_deadline" required></div>
        <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;"><button type="submit" class="btn-primary">Create Task</button><button type="button" class="btn-secondary" data-close>Cancel</button></div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- EDIT TASK MODAL -->
  <div class="modal" id="editTaskModal">
    <div class="modal-content">
      <div class="modal-header"><span>Edit Task</span><span class="close-modal" data-close>&times;</span></div>
      <form id="editTaskForm">
        <input type="hidden" id="editTaskId" name="task_id" />
        <div class="form-group"><label for="editTaskCategory">Task Category *</label><select id="editTaskCategory" name="task_category_id" required><option value="">Select Category</option></select></div>
        <div class="form-group"><label for="editTaskTitle">Task Title *</label><input type="text" id="editTaskTitle" name="task_title" required></div>
        <div class="form-group"><label for="editTaskDescription">Task Description *</label><textarea id="editTaskDescription" name="task_description" rows="4" required></textarea></div>
        <div class="form-group"><label for="editTaskDeadline">Deadline *</label><input type="date" id="editTaskDeadline" name="task_deadline" required></div>
        <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;"><button type="submit" class="btn-primary">Update Task</button><button type="button" class="btn-secondary" data-close>Cancel</button></div>
      </form>
    </div>
  </div>

  <!-- DELETE TASK MODAL -->
  <div class="modal" id="deleteTaskModal">
    <div class="modal-content">
      <div class="modal-header"><span><i class="fas fa-trash-alt" style="color:#dc3545;"></i> Delete Task</span><span class="close-modal" data-close>&times;</span></div>
      <div id="deleteTaskMessage" style="background:#fee2e2;border:1px solid #fecaca;border-radius:8px;padding:14px;color:#991b1b;font-size:13px;line-height:1.6;"><i class="fas fa-exclamation-triangle"></i> Are you sure you want to delete this task? All submissions will also be deleted. This action cannot be undone.</div>
      <div class="form-actions" style="display:flex;gap:10px;justify-content:flex-end;margin-top:1.5rem;">
        <button class="btn-secondary" data-close>Cancel</button>
        <button id="confirmDeleteTask" style="background:#dc3545;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;"><i class="fas fa-trash"></i> Delete Task</button>
      </div>
    </div>
  </div>

  <!-- VIEW SUBMISSION MODAL -->
  <div class="modal" id="viewSubmissionModal">
    <div class="modal-content">
      <div class="modal-header"><span>Submission Details</span><span class="close-modal" data-close>&times;</span></div>
      <div id="submissionContent"></div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;"><button class="btn-secondary" data-close>Close</button></div>
    </div>
  </div>

  <!-- APPROVE SUBMISSION MODAL -->
  <div class="modal" id="approveSubmissionModal">
    <div class="modal-content">
      <div class="modal-header"><span>Approve Submission</span><span class="close-modal" data-close>&times;</span></div>
      <div id="approveSubmissionMessage"><p>Are you sure you want to approve this submission?</p><p style="margin-top:10px;color:#666;"><i class="fas fa-info-circle"></i> The submission will be marked as "Approved" and the linked file will be protected from deletion.</p></div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;"><button id="confirmApproveSubmission" class="btn-success">Approve</button><button class="btn-secondary" data-close>Cancel</button></div>
    </div>
  </div>

  <!-- TRANSFER TASK MODAL -->
  <div class="modal" id="transferTaskModal">
    <div class="modal-content">
      <div class="modal-header"><span>Transfer Task</span><span class="close-modal" data-close>&times;</span></div>
      <form id="transferTaskForm">
        <input type="hidden" id="transferTaskId" name="task_id" />
        <div class="form-group"><label>Task Title</label><input type="text" id="transferTaskTitle" readonly style="background:#f0f0f0;"></div>
        <div class="form-group"><label for="transferToMember">Transfer To *</label><select id="transferToMember" name="new_assigned_to" required><option value="">Select a member...</option></select></div>
        <div class="form-group"><label for="transferReason">Reason for Transfer *</label><textarea id="transferReason" name="transfer_reason" rows="3" placeholder="Enter reason..." required></textarea></div>
        <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;"><button type="submit" class="btn-primary">Transfer Task</button><button type="button" class="btn-secondary" data-close>Cancel</button></div>
      </form>
    </div>
  </div>

  <!-- EXTEND DEADLINE MODAL -->
  <div class="modal" id="extendDeadlineModal">
    <div class="modal-content">
      <div class="modal-header"><span>Extend Task Deadline</span><span class="close-modal" data-close>&times;</span></div>
      <form id="extendDeadlineForm">
        <input type="hidden" id="extendTaskId" name="task_id" />
        <div class="form-group"><label>Task Title</label><input type="text" id="extendTaskTitle" readonly style="background:#f0f0f0;"></div>
        <div class="form-group"><label>Current Deadline</label><input type="text" id="extendCurrentDeadline" readonly style="background:#f0f0f0;color:#dc3545;font-weight:600;"></div>
        <div class="form-group"><label for="extendNewDeadline">New Deadline *</label><input type="date" id="extendNewDeadline" name="new_deadline" required></div>
        <div class="form-group"><label for="extendReason">Reason for Extension *</label><textarea id="extendReason" name="reason" rows="3" placeholder="Enter reason..." required></textarea></div>
        <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;"><button type="submit" class="btn-primary"><i class="fas fa-calendar-plus"></i> Extend Deadline</button><button type="button" class="btn-secondary" data-close>Cancel</button></div>
      </form>
    </div>
  </div>

  <!-- CLOSE/CANCEL TASK MODAL -->
  <div class="modal" id="closeTaskModal">
    <div class="modal-content">
      <div class="modal-header"><span id="closeTaskModalTitle"><i class="fas fa-times-circle" style="color:#f59e0b;"></i> Cancel Task</span><span class="close-modal" data-close>&times;</span></div>
      <div id="closeTaskWarning" style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:14px;color:#92400e;font-size:13px;line-height:1.6;margin-bottom:1rem;"><i class="fas fa-exclamation-triangle"></i> This task will be cancelled. Please provide a reason below.</div>
      <form id="closeTaskForm">
        <input type="hidden" id="closeTaskId" name="task_id" />
        <input type="hidden" id="closeTaskAction" name="action" />
        <div class="form-group"><label>Task Title</label><input type="text" id="closeTaskTitle" readonly style="background:#f0f0f0;border-radius:6px;"></div>
        <div class="form-group"><label for="closeTaskReason">Reason *</label><textarea id="closeTaskReason" name="reason" rows="3" placeholder="Enter reason..." required></textarea></div>
        <div class="form-actions" style="display:flex;gap:10px;justify-content:flex-end;margin-top:1.5rem;">
          <button type="button" class="btn-secondary" data-close>Cancel</button>
          <button type="submit" id="confirmCloseTask" style="background:#f59e0b;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;"><i class="fas fa-times-circle"></i> Cancel Task</button>
        </div>
      </form>
    </div>
  </div>

  <!-- VIEW TASK MODAL -->
  <div class="modal" id="viewTaskModal">
    <div class="modal-content">
      <div class="modal-header"><span><i class="fas fa-eye"></i> Task Details</span><span class="close-modal" data-close>&times;</span></div>
      <div id="viewTaskContent"></div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;"><button class="btn-secondary" data-close>Close</button></div>
    </div>
  </div>

  <!-- NOTIFICATION MODAL -->
  <div id="notificationModal" class="notification-modal">
    <div class="notification-content">
      <i id="notificationIcon" class="fas fa-info-circle"></i>
      <div class="notification-text"><h3 id="notificationTitle">Notification</h3><p id="notificationMessage"></p></div>
      <button class="notification-close" onclick="closeNotification()">&times;</button>
    </div>
  </div>

<script>
function showNotification(message, type = 'info', title = '') {
    const modal = document.getElementById('notificationModal');
    const config = { success:{icon:'fa-check-circle',defaultTitle:'Success',color:'#10b981'}, error:{icon:'fa-exclamation-circle',defaultTitle:'Error',color:'#ef4444'}, warning:{icon:'fa-exclamation-triangle',defaultTitle:'Warning',color:'#f59e0b'}, info:{icon:'fa-info-circle',defaultTitle:'Information',color:'#3b82f6'} };
    const c = config[type] || config.info;
    document.getElementById('notificationIcon').className = `fas ${c.icon}`;
    document.getElementById('notificationIcon').style.color = c.color;
    document.getElementById('notificationTitle').textContent = title || c.defaultTitle;
    document.getElementById('notificationMessage').textContent = message;
    modal.style.display = 'flex';
    modal.classList.add('notification-show');
    setTimeout(() => closeNotification(), 3000);
}
function closeNotification() {
    const modal = document.getElementById('notificationModal');
    modal.classList.remove('notification-show');
    setTimeout(() => { modal.style.display = 'none'; }, 300);
}
function toggleDropdown(event) {
    event.stopPropagation();
    const btn = event.target.closest('.dropdown-btn');
    const menu = btn.nextElementSibling;
    document.querySelectorAll('.dropdown-menu').forEach(m => { if (m !== menu) m.classList.remove('show'); });
    document.querySelectorAll('.kanban-card.dropdown-open').forEach(c => c.classList.remove('dropdown-open'));
    menu.classList.toggle('show');
    const card = btn.closest('.kanban-card');
    if (card && menu.classList.contains('show')) card.classList.add('dropdown-open');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown-btn')) {
        document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.remove('show'));
        document.querySelectorAll('.kanban-card.dropdown-open').forEach(c => c.classList.remove('dropdown-open'));
    }
});
</script>

<script>
(function($) {
    let deleteTaskId = null;
    let approveSubmissionId = null;

    // Tab switching
    $('.tab-btn').on('click', function() {
        const target = $(this).data('tab');
        $('.tab-btn').removeClass('active');
        $('.tab-content').removeClass('active');
        $(this).addClass('active');
        $('#' + target).addClass('active');
        $('#openCreateTaskModal').toggle(target === 'all-tasks');
    });

    // Modal helpers
    function openModal(id) { $('#' + id).css('display', 'flex'); }
    function closeModal() { $('.modal').hide(); }
    $(document).on('click', '[data-close]', closeModal);
    $(document).on('click', '.modal', function(e) { if (e.target === this) closeModal(); });

    $('#openCreateTaskModal').on('click', function() { openModal('createTaskModal'); });

    // Load categories & members
    function loadTaskCategories() {
        $.post('ajax.php', { CALL: 6 }, function(resp) {
            if (resp.data) {
                const opts = resp.data.map(c => `<option value="${c.task_category_id}">${c.task_category}</option>`).join('');
                $('#taskCategory, #editTaskCategory').append(opts);
            }
        }, 'json');
    }
    function loadMembers() {
        $.post('ajax.php', { CALL: 63 }, function(resp) {
            if (resp.status === 'SUCCESS' && resp.data) {
                const opts = resp.data.map(m => `<option value="${m.user_id}">${m.full_name} - ${m.position}</option>`).join('');
                $('#assignTo').append(opts);
            }
        }, 'json');
    }

    // All Tasks DataTable
    const tasksTable = $('#tasksTable').DataTable({
        ajax: {
            url: 'ajax.php', type: 'POST', data: { CALL: 40 },
            dataSrc: function(json) {
                var data = json.data || [];
                var pending=0, overdue=0, withSubs=0, closed=0;
                var today = new Date(); today.setHours(0,0,0,0);
                data.forEach(function(row) {
                    var status = row.task_status || 'active';
                    if (status==='closed'||status==='cancelled') { closed++; return; }
                    var hasApproved = parseInt(row.approved_count)>0;
                    var hasSubmissions = row.submission_count>0;
                    var deadline = new Date(row.task_deadline); deadline.setHours(0,0,0,0);
                    if (hasApproved) {}
                    else if (hasSubmissions) withSubs++;
                    else if (deadline < today) overdue++;
                    else pending++;
                });
                $('#statTotalTasks').text(data.length);
                $('#statPendingTasks').text(pending);
                $('#statOverdueTasks').text(overdue);
                $('#statWithSubmissions').text(withSubs);
                $('#statClosedTasks').text(closed);
                return data;
            }
        },
        columns: [
            { data: 'task_id' },
            { data: 'task_category', render: function(d) { return '<span style="background:#f1f5f9;color:#475569;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:500;">'+(d||'')+'</span>'; } },
            { data: 'task_title', render: function(d) { return '<strong style="color:#1e293b;">'+(d||'')+'</strong>'; } },
            { data: 'task_description' },
            { data: null, render: function(d,t,row) { return row.assigned_member_name ? '<span style="font-size:11px;">'+row.assigned_member_name+'</span>' : '<span style="color:#94a3b8;font-size:11px;">Unassigned</span>'; } },
            { data: 'task_deadline', render: function(d,t,row) {
                var today=new Date();today.setHours(0,0,0,0);var dl=new Date(d);dl.setHours(0,0,0,0);
                var ts=row.task_status||'active';var fmt=dl.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
                if(ts==='closed'||ts==='cancelled') return '<span style="color:#6c757d;font-size:11px;">'+fmt+'</span>';
                var diff=Math.ceil((dl-today)/86400000);
                if(diff<0) return '<span style="color:#c0392b;font-weight:600;font-size:11px;">'+fmt+'</span><span style="display:block;font-size:10px;color:#c0392b;font-weight:700;">🔥 '+Math.abs(diff)+' day'+(Math.abs(diff)>1?'s':'')+' overdue</span>';
                if(diff===0) return '<span style="color:#d97706;font-weight:600;font-size:11px;">'+fmt+'</span><span style="display:block;font-size:10px;color:#d97706;">⚡ Due today</span>';
                if(diff<=3) return '<span style="color:#d97706;font-weight:600;font-size:11px;">'+fmt+'</span><span style="display:block;font-size:10px;color:#d97706;">'+diff+' day'+(diff>1?'s':'')+' left</span>';
                return '<span style="font-size:11px;">'+fmt+'</span><span style="display:block;font-size:10px;color:#94a3b8;">'+diff+' days left</span>';
            }},
            { data: null, render: function(d,t,row) {
                var today=new Date();today.setHours(0,0,0,0);var dl=new Date(row.task_deadline);dl.setHours(0,0,0,0);
                var hasSub=row.submission_count>0;var ts=row.task_status||'active';
                if(ts==='closed'||ts==='cancelled'){var cls=ts==='closed'?'status-closed':'status-cancelled';return `<span class="status-badge ${cls}">Task ${ts.charAt(0).toUpperCase()+ts.slice(1)}</span>`;}
                if(hasSub&&parseInt(row.approved_count)>0) return '<span class="status-badge status-completed">✅ Approved</span>';
                if(hasSub&&parseInt(row.rejected_count)>0) return '<span class="status-badge status-rejected">❌ Rejected</span>';
                if(dl<today&&!hasSub) return '<span class="status-badge status-overdue">Overdue</span>';
                if(hasSub) return '<span class="status-badge" style="background:#fef3c7;color:#92400e;">Awaiting Review</span>';
                return '<span class="status-badge status-pending">Pending</span>';
            }},
            { data: null, orderable: false, render: function(d,t,row) {
                var today=new Date();today.setHours(0,0,0,0);var dl=new Date(row.task_deadline);dl.setHours(0,0,0,0);
                var isOverdue=dl<today;var ts=row.task_status||'active';
                if(ts==='closed'||ts==='cancelled') return `<button class="dropdown-item view viewTaskBtn" data-id="${row.task_id}" style="border:none;background:none;color:#007bff;cursor:pointer;font-size:12px;"><i class="fas fa-eye"></i> View</button>`;
                var hasApproved=parseInt(row.approved_count)>0;var hasRejected=parseInt(row.rejected_count)>0;var hasSub=row.submission_count>0;
                var a=`<div class="dropdown-container"><button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button><div class="dropdown-menu">`;
                a+=`<button class="dropdown-item view viewTaskBtn" data-id="${row.task_id}"><i class="fas fa-eye"></i> View</button>`;
                if(!hasApproved&&!hasRejected) a+=`<button class="dropdown-item edit editTaskBtn" data-id="${row.task_id}"><i class="fas fa-edit"></i> Edit</button>`;
                if(!hasApproved&&!hasRejected&&!hasSub&&row.assigned_to) a+=`<button class="dropdown-item transferTaskBtn" style="color:#ff9800;" data-id="${row.task_id}" data-title="${row.task_title}"><i class="fas fa-exchange-alt"></i> Transfer</button>`;
                if(isOverdue&&!hasSub) a+=`<button class="dropdown-item extendDeadlineBtn" style="color:#17a2b8;" data-id="${row.task_id}" data-title="${row.task_title}" data-deadline="${row.task_deadline}"><i class="fas fa-calendar-plus"></i> Extend Deadline</button>`;
                if(hasApproved){a+=`<button class="dropdown-item revertTaskBtn" style="color:#92400e;" data-id="${row.task_id}"><i class="fas fa-undo"></i> Move to Awaiting Review</button>`;a+=`<button class="dropdown-item closeTaskBtn" style="color:#28a745;" data-id="${row.task_id}" data-title="${row.task_title}" data-action="close"><i class="fas fa-check-circle"></i> Close Task</button>`;}
                if(hasSub&&!hasApproved&&!hasRejected){a+=`<button class="dropdown-item approveTaskBtn" style="color:#065f46;" data-id="${row.task_id}"><i class="fas fa-check-circle"></i> Approve</button>`;a+=`<button class="dropdown-item rejectTaskBtn" style="color:#991b1b;" data-id="${row.task_id}"><i class="fas fa-undo"></i> Move to Pending</button>`;}
                if(!hasSub&&!hasApproved&&!hasRejected) a+=`<button class="dropdown-item cancelTaskBtn" style="color:#ffc107;" data-id="${row.task_id}" data-title="${row.task_title}" data-action="cancel"><i class="fas fa-times-circle"></i> Cancel Task</button>`;
                a+=`<button class="dropdown-item delete deleteTaskBtn" data-id="${row.task_id}"><i class="fas fa-trash"></i> Delete</button>`;
                a+=`</div></div>`;return a;
            }}
        ],
        columnDefs: [{ targets: 3, width: '220px', className: 'wrap-col' }]
    });

    // Submissions DataTable
    const submissionsTable = $('#submissionsTable').DataTable({
        ajax: {
            url: 'ajax.php', type: 'POST', data: { CALL: 41 },
            dataSrc: function(json) {
                var data = (json.data||[]).filter(r => {var ts=(r.task_status||'').toLowerCase();return ts!=='closed'&&ts!=='cancelled';});
                var pending=0,approved=0;
                data.forEach(r => { if((r.check_status||'').toLowerCase()==='approved') approved++; else pending++; });
                $('#statTotalSubs').text(data.length);$('#statPendingSubs').text(pending);$('#statApprovedSubs').text(approved);
                return data;
            }
        },
        columns: [
            { data: 'task_submission_id' },
            { data: 'task_title', render: function(d){return '<strong style="font-size:12px;color:#1e293b;">'+(d||'')+'</strong>';} },
            { data: 'student_name', render: function(d){return '<span style="font-size:11px;color:#475569;">'+(d||'')+'</span>';} },
            { data: 'file_name', render: function(d){return d?'<span style="font-size:11px;color:#475569;">📎 '+d+'</span>':'<span style="color:#94a3b8;font-size:11px;">—</span>';} },
            { data: 'submitted_at', render: function(d){if(!d)return '-';var dt=new Date(d);return '<span style="font-size:11px;">'+dt.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})+'</span>';} },
            { data: 'check_status', render: function(d,t,row){
                var ts=(row.task_status||'').toLowerCase();
                if(ts==='closed') return '<span style="background:#e2e8f0;color:#334155;padding:3px 10px;border-radius:10px;font-size:10px;font-weight:600;">🔒 Closed</span>';
                if(ts==='cancelled') return '<span style="background:#f1f5f9;color:#64748b;padding:3px 10px;border-radius:10px;font-size:10px;font-weight:600;">🚫 Cancelled</span>';
                var s=(d||'').toLowerCase(),label=d,bg='#f1f5f9',color='#64748b';
                if(s==='approved'){bg='#d1fae5';color='#065f46';label='✅ Approved';}
                else if(s==='rejected'){bg='#fee2e2';color='#991b1b';label='❌ Rejected';}
                else if(s==='pending'){bg='#fef3c7';color='#92400e';label='⏳ Awaiting Review';}
                return '<span style="background:'+bg+';color:'+color+';padding:3px 10px;border-radius:10px;font-size:10px;font-weight:600;">'+label+'</span>';
            }},
            { data: null, orderable: false, render: function(d,t,row){
                var hasApproved=parseInt(row.approved_count)>0;var hasRejected=parseInt(row.rejected_count)>0;var hasSub=row.submission_count>0;
                var isOverdue=new Date(row.task_deadline)<new Date(new Date().toDateString());
                var a=`<div class="dropdown-container"><button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button><div class="dropdown-menu">`;
                a+=`<button class="dropdown-item view viewTaskBtn" data-id="${row.task_id}"><i class="fas fa-eye"></i> View</button>`;
                if(!hasApproved&&!hasRejected) a+=`<button class="dropdown-item edit editTaskBtn" data-id="${row.task_id}"><i class="fas fa-edit"></i> Edit</button>`;
                if(hasSub&&!hasApproved&&!hasRejected){a+=`<button class="dropdown-item approveTaskBtn" style="color:#065f46;" data-id="${row.task_id}"><i class="fas fa-check-circle"></i> Approve</button>`;a+=`<button class="dropdown-item rejectTaskBtn" style="color:#991b1b;" data-id="${row.task_id}"><i class="fas fa-undo"></i> Move to Pending</button>`;}
                if(hasApproved){a+=`<button class="dropdown-item revertTaskBtn" style="color:#92400e;" data-id="${row.task_id}"><i class="fas fa-undo"></i> Move to Awaiting Review</button>`;a+=`<button class="dropdown-item closeTaskBtn" style="color:#28a745;" data-id="${row.task_id}" data-title="${row.task_title}" data-action="close"><i class="fas fa-check-circle"></i> Close Task</button>`;}
                a+=`<button class="dropdown-item delete deleteTaskBtn" data-id="${row.task_id}"><i class="fas fa-trash"></i> Delete</button>`;
                a+=`</div></div>`;return a;
            }}
        ]
    });

    // Create task
    $('#createTaskForm').on('submit', function(e) {
        e.preventDefault();
        $.post('ajax.php', { CALL: 'create_task', DATA: { task_category_id:$('#taskCategory').val(), task_title:$('#taskTitle').val(), task_description:$('#taskDescription').val(), task_deadline:$('#taskDeadline').val(), assigned_to:$('#assignTo').val() } }, function(resp) {
            if(resp.status==='SUCCESS'){showNotification('Task created successfully','success','Task Created');$('#createTaskForm')[0].reset();closeModal();tasksTable.ajax.reload();renderKanban();}
            else showNotification(resp.msg||'Failed to create task','error','Creation Failed');
        }, 'json');
    });

    // View task details
    $(document).on('click', '.viewTaskBtn', function() {
        var taskId = $(this).data('id');
        $.post('ajax.php', { CALL: 57, task_id: taskId }, function(resp) {
            if(resp.status==='SUCCESS'){
                var t=resp.data;var deadline=new Date(t.task_deadline).toLocaleDateString('en-US',{month:'long',day:'numeric',year:'numeric'});
                var html=`<div style="line-height:2;font-size:13px;"><p><strong>Title:</strong> ${t.task_title}</p><p><strong>Category:</strong> ${t.task_category||'—'}</p><p><strong>Description:</strong> ${t.task_description||'—'}</p><p><strong>Assigned To:</strong> ${t.assigned_member_name||'Unassigned'}</p><p><strong>Deadline:</strong> ${deadline}</p><p><strong>Status:</strong> ${t.task_status||'active'}</p>`;
                if(t.submissions&&t.submissions.length>0){html+=`<hr style="margin:12px 0;"><h4 style="margin:0 0 8px;font-size:14px;">📎 Submissions</h4>`;t.submissions.forEach(function(s){var sd=new Date(s.submitted_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});html+=`<div style="background:#f8f9fa;padding:10px;border-radius:6px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;"><div><strong>${s.student_name}</strong><br><span style="font-size:11px;color:#64748b;">${s.file_name} • ${sd} • ${s.check_status}</span></div><a href="ajax.php?CALL=download&file_id=${s.file_upload_id}" class="btn-primary" style="font-size:11px;padding:5px 12px;text-decoration:none;border-radius:5px;background:#007bff;color:#fff;" target="_blank">Download</a></div>`;});}
                else html+=`<hr style="margin:12px 0;"><p style="color:#94a3b8;">No submissions yet.</p>`;
                html+=`</div>`;$('#viewTaskContent').html(html);openModal('viewTaskModal');
            } else showNotification(resp.msg||'Failed to load task','error');
        }, 'json');
    });

    // Approve task
    $(document).on('click', '.approveTaskBtn', function() {
        var taskId=$(this).data('id');
        $.post('ajax.php',{CALL:46,task_id:taskId,approve_by_task:1},function(resp){
            if(resp.status==='SUCCESS'){showNotification('Task approved','success','Approved');tasksTable.ajax.reload();submissionsTable.ajax.reload();renderKanban();}
            else showNotification(resp.msg||'Failed','error');
        },'json');
    });

    // Revert to awaiting review
    $(document).on('click', '.revertTaskBtn', function() {
        var taskId=$(this).data('id');
        $.post('ajax.php',{CALL:'revert_to_pending',task_id:taskId},function(resp){
            if(resp.status==='SUCCESS'){showNotification('Moved back to Awaiting Review','success','Reverted');tasksTable.ajax.reload();submissionsTable.ajax.reload();renderKanban();}
            else showNotification(resp.msg||'Failed','error');
        },'json');
    });

    // Reject submission (move to pending)
    $(document).on('click', '.rejectTaskBtn', function() {
        var taskId=$(this).data('id');
        $.post('ajax.php',{CALL:'reject_submission_by_task',task_id:taskId},function(resp){
            if(resp.status==='SUCCESS'){showNotification('Moved back to Pending','success','Reverted');tasksTable.ajax.reload();submissionsTable.ajax.reload();renderKanban();}
            else showNotification(resp.msg||'Failed','error');
        },'json');
    });

    // Edit task
    $(document).on('click', '.editTaskBtn', function() {
        var taskId=$(this).data('id');
        $.post('ajax.php',{CALL:42,task_id:taskId},function(resp){
            if(resp.status==='SUCCESS'){var t=resp.data;$('#editTaskId').val(t.task_id);$('#editTaskCategory').val(t.task_category_id);$('#editTaskTitle').val(t.task_title);$('#editTaskDescription').val(t.task_description);$('#editTaskDeadline').val(t.task_deadline);openModal('editTaskModal');}
            else showNotification('Unable to fetch task','error');
        },'json');
    });
    $('#editTaskForm').on('submit', function(e) {
        e.preventDefault();
        $.post('ajax.php',{CALL:43,task_id:$('#editTaskId').val(),task_category_id:$('#editTaskCategory').val(),task_title:$('#editTaskTitle').val(),task_description:$('#editTaskDescription').val(),task_deadline:$('#editTaskDeadline').val()},function(resp){
            if(resp.status==='SUCCESS'){showNotification('Task updated','success','Updated');closeModal();tasksTable.ajax.reload();submissionsTable.ajax.reload();renderKanban();}
            else showNotification(resp.msg||'Failed','error');
        },'json');
    });

    // Delete task
    $(document).on('click', '.deleteTaskBtn', function() { deleteTaskId=$(this).data('id');openModal('deleteTaskModal'); });
    $('#confirmDeleteTask').on('click', function() {
        if(!deleteTaskId)return;
        $.post('ajax.php',{CALL:44,task_id:deleteTaskId},function(resp){
            if(resp.status==='SUCCESS'){showNotification('Task deleted','success','Deleted');closeModal();tasksTable.ajax.reload();submissionsTable.ajax.reload();renderKanban();deleteTaskId=null;}
            else showNotification(resp.msg||'Failed','error');
        },'json');
    });

    // View submission
    $(document).on('click', '.viewSubmissionBtn', function() {
        var id=$(this).data('id');
        $.post('ajax.php',{CALL:45,submission_id:id},function(resp){
            if(resp.status==='SUCCESS'){var s=resp.data;$('#submissionContent').html(`<div class="submission-details"><h4>${s.task_title}</h4><p><strong>Student:</strong> ${s.student_name}</p><p><strong>Submitted:</strong> ${s.submitted_at}</p><p><strong>File:</strong> ${s.file_name}</p><p><strong>Status:</strong> <span class="status-badge ${s.check_status==='Approved'?'status-completed':'status-pending'}">${s.check_status}</span></p><div style="margin-top:1rem;"><a href="ajax.php?CALL=download&file_id=${s.file_upload_id}" class="btn-primary" target="_blank">Download</a></div></div>`);openModal('viewSubmissionModal');}
            else showNotification('Unable to fetch submission','error');
        },'json');
    });

    // Approve submission
    $(document).on('click', '.approveBtn', function() { approveSubmissionId=$(this).data('id');openModal('approveSubmissionModal'); });
    $('#confirmApproveSubmission').on('click', function() {
        if(!approveSubmissionId)return;
        $.post('ajax.php',{CALL:46,submission_id:approveSubmissionId},function(resp){
            if(resp.status==='SUCCESS'){showNotification('Approved','success');closeModal();submissionsTable.ajax.reload();approveSubmissionId=null;}
            else showNotification(resp.msg||'Failed','error');
        },'json');
    });

    // Transfer task
    $(document).on('click', '.transferTaskBtn', function() {
        var id=$(this).data('id'),title=$(this).data('title');
        $('#transferTaskId').val(id);$('#transferTaskTitle').val(title);$('#transferToMember').val('');$('#transferReason').val('');
        $.post('ajax.php',{CALL:63},function(resp){if(resp.status==='SUCCESS'&&resp.data)$('#transferToMember').html('<option value="">Select a member...</option>'+resp.data.map(m=>`<option value="${m.user_id}">${m.full_name} - ${m.position}</option>`).join(''));},'json');
        openModal('transferTaskModal');
    });
    $('#transferTaskForm').on('submit', function(e) {
        e.preventDefault();
        $.post('ajax.php',{CALL:'transfer_task',task_id:$('#transferTaskId').val(),new_assigned_to:$('#transferToMember').val(),transfer_reason:$('#transferReason').val()},function(resp){
            if(resp.status==='SUCCESS'){showNotification('Task transferred','success','Transferred');closeModal();tasksTable.ajax.reload();submissionsTable.ajax.reload();renderKanban();}
            else showNotification(resp.msg||'Failed','error');
        },'json');
    });

    // Extend deadline
    $(document).on('click', '.extendDeadlineBtn', function() {
        $('#extendTaskId').val($(this).data('id'));$('#extendTaskTitle').val($(this).data('title'));$('#extendCurrentDeadline').val($(this).data('deadline'));
        var tmr=new Date();tmr.setDate(tmr.getDate()+1);$('#extendNewDeadline').attr('min',tmr.toISOString().split('T')[0]).val('');$('#extendReason').val('');
        openModal('extendDeadlineModal');
    });
    $('#extendDeadlineForm').on('submit', function(e) {
        e.preventDefault();
        $.post('ajax.php',{CALL:'extend_deadline',task_id:$('#extendTaskId').val(),new_deadline:$('#extendNewDeadline').val(),reason:$('#extendReason').val()},function(resp){
            if(resp.status==='SUCCESS'){showNotification('Deadline extended','success');closeModal();tasksTable.ajax.reload();submissionsTable.ajax.reload();renderKanban();}
            else showNotification(resp.msg||'Failed','error');
        },'json');
    });

    // Close task
    $(document).on('click', '.closeTaskBtn', function() {
        $('#closeTaskId').val($(this).data('id'));$('#closeTaskTitle').val($(this).data('title'));$('#closeTaskAction').val('close');
        $('#closeTaskModalTitle').html('<i class="fas fa-check-circle" style="color:#28a745;"></i> Close Task');
        $('#closeTaskWarning').css({background:'#d1fae5',border:'1px solid #bbf7d0',color:'#065f46'}).html('<i class="fas fa-info-circle"></i> This task will be marked as closed.');
        $('#confirmCloseTask').attr('style','background:#28a745;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;').html('<i class="fas fa-check-circle"></i> Close Task');
        $('#closeTaskReason').val('');openModal('closeTaskModal');
    });
    // Cancel task
    $(document).on('click', '.cancelTaskBtn', function() {
        $('#closeTaskId').val($(this).data('id'));$('#closeTaskTitle').val($(this).data('title'));$('#closeTaskAction').val('cancel');
        $('#closeTaskModalTitle').html('<i class="fas fa-times-circle" style="color:#f59e0b;"></i> Cancel Task');
        $('#closeTaskWarning').css({background:'#fef3c7',border:'1px solid #fde68a',color:'#92400e'}).html('<i class="fas fa-exclamation-triangle"></i> This task will be cancelled.');
        $('#confirmCloseTask').attr('style','background:#f59e0b;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;').html('<i class="fas fa-times-circle"></i> Cancel Task');
        $('#closeTaskReason').val('');openModal('closeTaskModal');
    });
    $('#closeTaskForm').on('submit', function(e) {
        e.preventDefault();var action=$('#closeTaskAction').val();
        $.post('ajax.php',{CALL:'close_cancel_task',task_id:$('#closeTaskId').val(),action:action,reason:$('#closeTaskReason').val()},function(resp){
            if(resp.status==='SUCCESS'){showNotification(action==='close'?'Task closed':'Task cancelled','success');closeModal();tasksTable.ajax.reload();submissionsTable.ajax.reload();renderKanban();}
            else showNotification(resp.msg||'Failed','error');
        },'json');
    });

    // Initialize
    loadTaskCategories();
    loadMembers();
    renderKanban();

    // ===== VIEW TOGGLE & KANBAN =====
    let kanbanData = [];
    const dragRules = { pending:['awaiting_review','closed'], awaiting_review:['pending','approved'], approved:['awaiting_review','closed'], closed:[] };

    $('#kanbanSearch').on('input', function() { var q=$(this).val();tasksTable.search(q).draw();filterKanbanCards(q); });
    function filterKanbanCards(q) { var t=(q||'').toLowerCase();$('#board-view .kanban-card').each(function(){$(this).toggle(!t||$(this).text().toLowerCase().includes(t));}); }

    $('.view-toggle-btn').on('click', function() {
        var view=$(this).data('view');$('.view-toggle-btn').removeClass('active');$(this).addClass('active');
        if(view==='table'){$('#table-view').show();$('#board-view').hide();}else{$('#table-view').hide();$('#board-view').show();renderKanban();}
    });

    function getTaskStatus(row) {
        var ts=row.task_status||'active';if(ts==='closed'||ts==='cancelled')return 'closed';
        if(parseInt(row.approved_count)>0)return 'approved';if(row.submission_count>0)return 'awaiting_review';return 'pending';
    }
    function isOverdue(row){var t=new Date();t.setHours(0,0,0,0);var d=new Date(row.task_deadline);d.setHours(0,0,0,0);return d<t;}

    function renderKanban() {
        $.post('ajax.php',{CALL:40},function(resp){
            kanbanData=resp.data||[];
            var groups={pending:[],awaiting_review:[],approved:[],closed:[]};
            kanbanData.forEach(r=>groups[getTaskStatus(r)].push(r));
            $('#kanbanCountPending').text(groups.pending.length);$('#kanbanCountReview').text(groups.awaiting_review.length);
            $('#kanbanCountApproved').text(groups.approved.length);$('#kanbanCountClosed').text(groups.closed.length);
            ['pending','awaiting_review','approved'].forEach(s=>{
                var c=$({pending:'#kanbanPending',awaiting_review:'#kanbanReview',approved:'#kanbanApproved'}[s]);c.empty();
                groups[s].forEach(r=>c.append(buildCard(r,s)));
            });
            closedTasks=groups.closed.sort((a,b)=>new Date(b.task_deadline)-new Date(a.task_deadline));closedShown=0;$('#kanbanClosed').empty();$('#kanbanLoadMoreClosed').hide();loadMoreClosed();
            initDragAndDrop();filterKanbanCards($('#kanbanSearch').val());
        },'json');
    }

    let closedTasks=[],closedShown=0;
    $(document).on('click','#kanbanLoadMoreClosed',function(){loadMoreClosed();});
    function loadMoreClosed(){var next=closedTasks.slice(closedShown,closedShown+10);next.forEach(r=>$('#kanbanClosed').append(buildCard(r,'closed')));closedShown+=next.length;if(closedShown>=closedTasks.length)$('#kanbanLoadMoreClosed').hide();else $('#kanbanLoadMoreClosed').show().text('Load 10 more ('+(closedTasks.length-closedShown)+' remaining)');}

    function buildCard(row, status) {
        var overdue=isOverdue(row)&&status!=='closed';var today=new Date();today.setHours(0,0,0,0);var dl=new Date(row.task_deadline);dl.setHours(0,0,0,0);
        var diff=Math.ceil((dl-today)/86400000);var fmt=dl.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
        var deadlineHtml=`<span>${fmt}</span>`;
        if(status!=='closed'){if(diff<0)deadlineHtml=`<span class="overdue-indicator">🔥 ${fmt} (${Math.abs(diff)}d overdue)</span>`;else if(diff===0)deadlineHtml=`<span style="color:#d97706;font-weight:600;">⚡ Due today</span>`;else if(diff<=3)deadlineHtml=`<span style="color:#d97706;">${fmt} (${diff}d left)</span>`;}
        var actions=buildCardActions(row,status);var draggable=status!=='closed'?'draggable="true"':'';
        return `<div class="kanban-card ${overdue?'card-overdue':''}" ${draggable} data-task-id="${row.task_id}" data-status="${status}"><div class="card-actions"><div class="dropdown-container"><button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button><div class="dropdown-menu">${actions}</div></div></div><div class="card-title">#${row.task_id} — ${row.task_title||''}</div><span class="card-category">${row.task_category||''}</span><div class="card-meta"><span>👤 ${row.assigned_member_name||'Unassigned'}</span><span>📅 ${deadlineHtml}</span></div></div>`;
    }

    function buildCardActions(row, status) {
        var a=`<button class="dropdown-item view viewTaskBtn" data-id="${row.task_id}"><i class="fas fa-eye"></i> View</button>`;
        if(status==='closed')return a;
        var hasApproved=parseInt(row.approved_count)>0,hasRejected=parseInt(row.rejected_count)>0,hasSub=row.submission_count>0,overdue=isOverdue(row);
        if(status==='pending'){
            if(!hasApproved&&!hasRejected)a+=`<button class="dropdown-item edit editTaskBtn" data-id="${row.task_id}"><i class="fas fa-edit"></i> Edit</button>`;
            if(!hasApproved&&!hasRejected&&!hasSub&&row.assigned_to)a+=`<button class="dropdown-item transferTaskBtn" style="color:#ff9800;" data-id="${row.task_id}" data-title="${row.task_title}"><i class="fas fa-exchange-alt"></i> Transfer</button>`;
            if(overdue)a+=`<button class="dropdown-item extendDeadlineBtn" style="color:#17a2b8;" data-id="${row.task_id}" data-title="${row.task_title}" data-deadline="${row.task_deadline}"><i class="fas fa-calendar-plus"></i> Extend Deadline</button>`;
            a+=`<button class="dropdown-item cancelTaskBtn" style="color:#ffc107;" data-id="${row.task_id}" data-title="${row.task_title}" data-action="cancel"><i class="fas fa-times-circle"></i> Cancel</button>`;
        } else if(status==='awaiting_review'){
            a+=`<button class="dropdown-item approveTaskBtn" style="color:#065f46;" data-id="${row.task_id}"><i class="fas fa-check-circle"></i> Approve</button>`;
            a+=`<button class="dropdown-item rejectTaskBtn" style="color:#991b1b;" data-id="${row.task_id}"><i class="fas fa-undo"></i> Move to Pending</button>`;
        } else if(status==='approved'){
            a+=`<button class="dropdown-item revertTaskBtn" style="color:#92400e;" data-id="${row.task_id}"><i class="fas fa-undo"></i> Move to Awaiting Review</button>`;
            a+=`<button class="dropdown-item closeTaskBtn" style="color:#28a745;" data-id="${row.task_id}" data-title="${row.task_title}" data-action="close"><i class="fas fa-check-circle"></i> Close Task</button>`;
        }
        a+=`<button class="dropdown-item delete deleteTaskBtn" data-id="${row.task_id}"><i class="fas fa-trash"></i> Delete</button>`;return a;
    }

    var columnsInitialized = false;
    function initDragAndDrop() {
        var cards=document.querySelectorAll('.kanban-card[draggable="true"]');var columns=document.querySelectorAll('.kanban-column');
        cards.forEach(card=>{
            card.addEventListener('dragstart',function(e){this.classList.add('dragging');e.dataTransfer.setData('text/plain',this.dataset.taskId);e.dataTransfer.setData('source-status',this.dataset.status);var allowed=dragRules[this.dataset.status]||[];columns.forEach(col=>{if(allowed.includes(col.dataset.status))col.classList.add('drag-valid');else if(col.dataset.status!==this.dataset.status)col.classList.add('drag-invalid');});});
            card.addEventListener('dragend',function(){this.classList.remove('dragging');columns.forEach(col=>col.classList.remove('drag-valid','drag-invalid','drag-over'));});
        });
        if(!columnsInitialized){
            columnsInitialized=true;
            columns.forEach(col=>{
                col.addEventListener('dragover',function(e){if(this.classList.contains('drag-valid')){e.preventDefault();this.classList.add('drag-over');}});
                col.addEventListener('dragleave',function(){this.classList.remove('drag-over');});
                col.addEventListener('drop',function(e){e.preventDefault();this.classList.remove('drag-over');var taskId=e.dataTransfer.getData('text/plain');var from=e.dataTransfer.getData('source-status');var to=this.dataset.status;if(!dragRules[from]||!dragRules[from].includes(to))return;handleStatusChange(taskId,from,to);});
            });
        }
    }

    function handleStatusChange(taskId, from, to) {
        var call,data={task_id:taskId};
        if(from==='pending'&&to==='awaiting_review'){showNotification('A submission must be made by the assigned member.','warning','Action Required');return;}
        else if(from==='pending'&&to==='closed'){$('#closeTaskId').val(taskId);var row=kanbanData.find(r=>r.task_id==taskId);$('#closeTaskTitle').val(row?row.task_title:'');$('#closeTaskAction').val('cancel');$('#closeTaskModalTitle').text('Cancel Task');$('#closeTaskReason').val('');openModal('closeTaskModal');return;}
        else if(from==='awaiting_review'&&to==='pending') call='reject_submission_by_task';
        else if(from==='awaiting_review'&&to==='approved'){call=46;data.approve_by_task=1;}
        else if(from==='approved'&&to==='awaiting_review') call='revert_to_pending';
        else if(from==='approved'&&to==='closed'){$('#closeTaskId').val(taskId);var row=kanbanData.find(r=>r.task_id==taskId);$('#closeTaskTitle').val(row?row.task_title:'');$('#closeTaskAction').val('close');$('#closeTaskModalTitle').text('Close Task');$('#closeTaskReason').val('');openModal('closeTaskModal');return;}
        if(call){data.CALL=call;$.post('ajax.php',data,function(resp){if(resp.status==='SUCCESS'){showNotification('Status updated','success');tasksTable.ajax.reload();submissionsTable.ajax.reload();renderKanban();}else showNotification(resp.msg||'Failed','error');},'json');}
    }

    window.tasksTable=tasksTable;window.submissionsTable=submissionsTable;window.renderKanban=renderKanban;

    // Apply search from URL param (global search navigation)
    var urlSearch = new URLSearchParams(window.location.search).get('search');
    if(urlSearch){ $('#kanbanSearch').val(urlSearch); tasksTable.search(urlSearch).draw(); filterKanbanCards(urlSearch); }
})(jQuery);
</script>

<?php require_once('modals.php'); ?>
</body>
</html>
