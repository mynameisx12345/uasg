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
  <title>User Management - UASG</title>
  
  <link rel="stylesheet" href="../resources/style.css">
  <link rel="stylesheet" href="../resources/theme-overrides.css">
  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
  <style>
    .actions { display: flex; gap: 0.5rem; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.85rem; }
    .dropdown-container { position: relative; display: inline-block; }
    .dropdown-btn { background: transparent; color: #555; border: 1px solid #e0e0e0; padding: 0.35rem 0.55rem; font-size: 1.1rem; cursor: pointer; border-radius: 6px; line-height: 1; transition: all 0.2s; }
    .dropdown-btn:hover { background: #f0f0f0; color: #333; border-color: #ccc; }
    .dropdown-menu { display: none; position: absolute; right: 0; top: 100%; background: white; min-width: 170px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); border-radius: 8px; z-index: 1000; margin-top: 6px; border: 1px solid #eee; overflow: hidden; }
    .dropdown-menu.show { display: block; }
    .dropdown-item { display: flex; align-items: center; gap: 8px; width: 100%; padding: 0.6rem 1rem; text-align: left; border: none; background: none; cursor: pointer; font-size: 0.85rem; transition: background 0.15s; }
    .dropdown-item:hover { background: #f5f7fa; }
    .dropdown-item.edit { color: #28a745; }
    .dropdown-item.delete { color: #dc3545; }
    .dropdown-item.view { color: #007bff; }
    .dropdown-item.submissions { color: #17a2b8; }
    .dropdown-item.uploads { color: #6c757d; }
    .dropdown-item.deactivate { color: #dc3545; }
    .dropdown-item.reactivate { color: #28a745; }

    .sub-tab-link { background: none; border: none; padding: 0.6rem 1.2rem; cursor: pointer; font-size: 0.85rem; border-bottom: 2px solid transparent; transition: all 0.2s; color: #666; }
    .sub-tab-link:hover { color: #7b1228; }
    .sub-tab-link.active { border-bottom-color: #7b1228; color: #7b1228; font-weight: 600; }

    .access-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: 0.3px; }
    .access-superadmin { background: #7b1228; color: #fff; }
    .access-subadmin { background: #c89b2e; color: #fff; }
    .access-member { background: #17a2b8; color: #fff; }
    .access-hint { font-size: 12px; color: #666; margin-top: 4px; display: block; }

    .member-details { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
    .detail-item { padding: 0.75rem; background: #f8f9fa; border-radius: 8px; border: 1px solid #eef0f2; }
    .detail-item label { font-weight: 600; font-size: 0.8rem; color: #888; display: block; margin-bottom: 0.3rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .submission-list, .upload-list { list-style: none; padding: 0; }
    .submission-list li, .upload-list li { display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1rem; border: 1px solid #eee; background: #fafbfc; margin-bottom: 0.5rem; border-radius: 8px; transition: box-shadow 0.2s; }
    .submission-list li:hover, .upload-list li:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    
    .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(3px); align-items: center; justify-content: center; z-index: 1000; }
    .modal-content { background: #fff; padding: 2rem; border-radius: 12px; min-width: 500px; max-width: 620px; max-height: 90vh; overflow-y: auto; box-shadow: 0 24px 48px rgba(0,0,0,0.15); }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; font-weight: 700; font-size: 1.2rem; color: #1a1a1a; }
    .close-modal { cursor: pointer; font-size: 1.5rem; color: #999; transition: color 0.2s; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; }
    .close-modal:hover { color: #333; background: #f0f0f0; }

    .permission-group { border: 1px solid #e8e8e8; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; background: #fafbfc; }
    .permission-header { font-weight: 600; margin-bottom: 0.5rem; color: #333; font-size: 0.9rem; }
    .permission-checks { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 0.5rem; }
    .permission-check { display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; }

    .perm-dropdown { position: relative; }
    .perm-dropdown-btn { padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; color: #94a3b8; cursor: pointer; transition: border-color 0.2s; }
    .perm-dropdown-btn:hover { border-color: #cbd5e1; }
    .perm-dropdown-menu { display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #e2e8f0; border-radius: 8px; margin-top: 4px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); z-index: 100; overflow: hidden; }
    .perm-dropdown-menu.show { display: block; }
    .perm-option { display: flex; align-items: center; gap: 10px; padding: 10px 14px; cursor: pointer; font-size: 13px; transition: background 0.15s; }
    .perm-option:hover { background: #f8fafc; }
    .perm-option input { accent-color: #3b82f6; width: 16px; height: 16px; }
    .perm-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .perm-chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; background: #eff6ff; color: #1e40af; border-radius: 20px; font-size: 12px; font-weight: 500; }
    .perm-chip-remove { cursor: pointer; font-size: 14px; color: #93c5fd; transition: color 0.2s; }
    .perm-chip-remove:hover { color: #1e40af; }

    .type-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: 0.3px; }
    .type-admin { background: linear-gradient(135deg, #7b1228, #a01535); color: #fff; }
    .type-subadmin { background: linear-gradient(135deg, #c89b2e, #d4a93a); color: #fff; }
    .type-student { background: linear-gradient(135deg, #17a2b8, #1dbfd4); color: #fff; }

    .password-field-wrapper { position: relative; }
    .password-controls { display: flex; gap: 8px; margin-top: 8px; }
    .password-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #999; background: none; border: none; padding: 5px; font-size: 16px; transition: color 0.2s; }
    .password-toggle:hover { color: #2196f3; }
    .btn-generate { padding: 8px 14px; background: #4CAF50; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500; transition: all 0.2s; }
    .btn-generate:hover { background: #45a049; transform: translateY(-1px); }
    .btn-copy { padding: 8px 14px; background: #2196f3; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500; transition: all 0.2s; }
    .btn-copy:hover { background: #0b7dda; transform: translateY(-1px); }

    .notification-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 10000; animation: fadeIn 0.2s ease; }
    .notification-modal.show { display: flex; }
    .notification-content { background: white; border-radius: 16px; padding: 0; min-width: 400px; max-width: 500px; box-shadow: 0 24px 64px rgba(0,0,0,0.2); animation: slideIn 0.3s ease; overflow: hidden; }
    .notification-header { padding: 20px 24px; border-bottom: 1px solid #e0e0e0; display: flex; align-items: center; gap: 12px; }
    .notification-header.success { background: #e8f5e9; color: #2e7d32; }
    .notification-header.error { background: #ffebee; color: #c62828; }
    .notification-header.warning { background: #fff3e0; color: #f57c00; }
    .notification-header.info { background: #e3f2fd; color: #1976d2; }
    .notification-icon { font-size: 28px; }
    .notification-title { font-size: 18px; font-weight: 600; flex: 1; }
    .notification-body { padding: 24px; font-size: 15px; line-height: 1.6; color: #333; white-space: pre-wrap; }
    .notification-footer { padding: 16px 24px; background: #f8f9fa; display: flex; justify-content: flex-end; gap: 10px; }
    .notification-btn { padding: 10px 24px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .notification-btn.primary { background: #7b1228; color: white; }
    .notification-btn.primary:hover { background: #5e0e1f; transform: translateY(-1px); }

    /* Table enhancements */
    .table-container { margin-top: 0.5rem; }
    #usersTable td, #activeMembersTable td, #inactiveMembersTable td { vertical-align: middle; font-size: 12px; padding: 10px 12px; }
    #usersTable th, #activeMembersTable th, #inactiveMembersTable th { font-size: 12px; padding: 10px 12px; }
    #usersTable thead, #activeMembersTable thead, #inactiveMembersTable thead { background: #f8f9fa !important; }
    #usersTable thead th, #activeMembersTable thead th, #inactiveMembersTable thead th { background: #f8f9fa !important; color: #475569 !important; border-bottom: 2px solid #e2e8f0 !important; font-weight: 600; }
    #usersTable tbody tr, #activeMembersTable tbody tr, #inactiveMembersTable tbody tr { transition: background 0.15s, box-shadow 0.15s; }
    #usersTable tbody tr:hover, #activeMembersTable tbody tr:hover, #inactiveMembersTable tbody tr:hover { background: #f8fafc !important; box-shadow: inset 3px 0 0 #3b82f6; }
    #usersTable tbody tr:nth-child(even), #activeMembersTable tbody tr:nth-child(even), #inactiveMembersTable tbody tr:nth-child(even), #positiontable tbody tr:nth-child(even) { background: #fafbfc; }

    /* Positions table */
    #positiontable td { vertical-align: middle; font-size: 12px; padding: 10px 12px; }
    #positiontable th { font-size: 12px; padding: 10px 12px; }
    #positiontable thead th { background: #f8f9fa !important; color: #475569 !important; border-bottom: 2px solid #e2e8f0 !important; font-weight: 600; }
    #positiontable tbody tr { transition: background 0.15s, box-shadow 0.15s; }
    #positiontable tbody tr:hover { background: #f8fafc !important; box-shadow: inset 3px 0 0 #3b82f6; }

    /* Tab header layout */
    .tab-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .tab-header .tabs { margin-bottom: 0; }
    .btn-create { background: #007bff; color: #fff; border: none; padding: 0.5rem 1.2rem; border-radius: 6px; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 0.4rem; font-weight: 500; }
    .btn-create:hover { background: #0056b3; }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideIn { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  </style>
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
  <script src='../js/datatable.js'></script>
</head>
<body>
  <?php require_once("header.php");?>
  <header class="topbar">
    <h1>User Management</h1>
    <div class="user-info"><span>Welcome, Admin</span></div>
  </header>
  
  <main class="main">
    <?php require_once("sidebar.php");?>

    <section class="content">
      <div class="card">
        <div class="tab-header">
          <div class="tabs">
            <button class="tab-btn active" data-tab="users">👥 Users</button>
            <button class="tab-btn" data-tab="member-activity">📊 Member Activity</button>
            <button class="tab-btn" data-tab="positions">🏷️ Positions</button>
          </div>
          <button class="btn-create" id="openCreateUser"><i class="fas fa-plus"></i> Add User</button>
        </div>

        <!-- USERS TAB -->
        <div id="users" class="tab-content active">
          <div class="table-container">
            <table id="usersTable" class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Username</th>
                  <th>Full Name</th>
                  <th>User Type</th>
                  <th>Role</th>
                  <th>Email</th>
                  <th>Contact</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>

        <!-- MEMBER ACTIVITY TAB -->
        <div id="member-activity" class="tab-content">
          <div class="tabs" style="margin-bottom:1rem;">
            <button class="sub-tab-link active" data-subtab="active-members">Active Members</button>
            <button class="sub-tab-link" data-subtab="inactive-members">Inactive Members</button>
          </div>
          <div id="active-members" class="sub-tab-content active">
            <div class="table-container">
              <table id="activeMembersTable" class="data-table">
                <thead>
                  <tr>
                    <th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Contact</th><th>Submissions</th><th>Uploads</th><th>Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
          <div id="inactive-members" class="sub-tab-content" style="display:none;">
            <div class="table-container">
              <table id="inactiveMembersTable" class="data-table">
                <thead>
                  <tr>
                    <th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Contact</th><th>Deactivated</th><th>Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- POSITIONS TAB -->
        <div id="positions" class="tab-content">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <div>
              <h3 style="margin:0;font-size:15px;color:#1e293b;">Positions</h3>
              <p style="margin:4px 0 0;font-size:12px;color:#64748b;">Manage user roles and access levels</p>
            </div>
            <button class="btn-create" id="openAddPositionModal"><i class="fas fa-plus"></i> Add Position</button>
          </div>
          <div class="table-container">
            <table class="data-table" id="positiontable">
              <thead>
                <tr><th>Position ID</th><th>Position</th><th>Access Restriction</th><th>Actions</th></tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- VIEW MEMBER DETAILS MODAL -->
  <div class="modal" id="viewMemberModal">
    <div class="modal-content">
      <div class="modal-header"><span>Member Details</span><span class="close-modal" data-close>&times;</span></div>
      <div id="memberDetailsContent"></div>
    </div>
  </div>

  <!-- VIEW SUBMISSIONS MODAL -->
  <div class="modal" id="viewSubmissionsModal">
    <div class="modal-content">
      <div class="modal-header"><span>Task Submissions</span><span class="close-modal" data-close>&times;</span></div>
      <div id="submissionsContent"></div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;"><button class="btn-secondary" data-close>Close</button></div>
    </div>
  </div>

  <!-- VIEW UPLOADS MODAL -->
  <div class="modal" id="viewUploadsModal">
    <div class="modal-content">
      <div class="modal-header"><span>All Uploads</span><span class="close-modal" data-close>&times;</span></div>
      <div id="uploadsContent"></div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;"><button class="btn-secondary" data-close>Close</button></div>
    </div>
  </div>

  <!-- DEACTIVATE MEMBER MODAL -->
  <div class="modal" id="deactivateMemberModal">
    <div class="modal-content">
      <div class="modal-header"><span>Deactivate Member</span><span class="close-modal" data-close>&times;</span></div>
      <p>Are you sure you want to deactivate this member?</p>
      <div class="form-group"><label for="deactivateReason">Reason for deactivation *</label><textarea id="deactivateReason" rows="3" placeholder="Enter reason..." required></textarea></div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
        <button id="confirmDeactivate" class="btn-danger">Deactivate</button>
        <button class="btn-secondary" data-close>Cancel</button>
      </div>
    </div>
  </div>

  <!-- REACTIVATE MEMBER MODAL -->
  <div class="modal" id="reactivateMemberModal">
    <div class="modal-content">
      <div class="modal-header"><span>Reactivate Member</span><span class="close-modal" data-close>&times;</span></div>
      <p>Are you sure you want to reactivate this member?</p>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
        <button id="confirmReactivate" class="btn-success">Reactivate</button>
        <button class="btn-secondary" data-close>Cancel</button>
      </div>
    </div>
  </div>

  <!-- POSITION UPDATE MODAL -->
  <!-- ADD POSITION MODAL -->
  <div class="modal" id="addPositionModal">
    <div class="modal-content">
      <div class="modal-header"><span>Add Position</span><span class="close-modal" data-close>&times;</span></div>
      <div class="form-group"><label for="positionName">Position Name *</label><input type="text" id="positionName" placeholder="Enter position name..." required></div>
      <div class="form-group">
        <label for="positionAccess">Access Restriction *</label>
        <select id="positionAccess" required>
          <option value="">Select Access Level</option>
          <option value="1">1 — Superadmin</option>
          <option value="2">2 — Sub-admin</option>
          <option value="3">3 — Member</option>
        </select>
        <span class="access-hint"><strong>1</strong> = System Administrator | <strong>2</strong> = Sub-admin | <strong>3</strong> = Member</span>
      </div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
        <button type="button" class="btn-primary" id="saveposition">Save Position</button>
        <button class="btn-secondary" data-close>Cancel</button>
      </div>
    </div>
  </div>

  <div id="updatePositionModal" class="modal">
    <div class="modal-content">
      <div class="modal-header"><span>Update Position</span><span class="close-modal" data-close>&times;</span></div>
      <div class="form-group"><label>Position Name</label><input type="text" id="updatePositionName" placeholder="Enter updated position" required></div>
      <div class="form-group"><label>Access Restriction</label>
        <select id="updatePositionAccess"><option value="">Select Access Level</option><option value="1">1 — Superadmin</option><option value="2">2 — Sub-admin</option><option value="3">3 — Member</option></select>
      </div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
        <button type="button" class="btn-primary" id="confirmUpdatePosition">Save Changes</button>
        <button class="btn-secondary" data-close>Cancel</button>
      </div>
    </div>
  </div>

  <!-- POSITION DELETE MODAL -->
  <div id="deletePositionModal" class="modal">
    <div class="modal-content">
      <div class="modal-header"><span>Delete Position</span><span class="close-modal" data-close>&times;</span></div>
      <p>Are you sure you want to delete position: <strong id="deletePositionValue"></strong>?</p>
      <div class="form-group"><label>Reason for Deletion</label><textarea id="deletePositionReason" rows="3" placeholder="Enter reason for deletion..."></textarea></div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
        <button type="button" class="btn-danger" id="confirmDeletePosition">Confirm Delete</button>
        <button class="btn-secondary" data-close>Cancel</button>
      </div>
    </div>
  </div>

  <!-- CREATE/EDIT USER MODAL -->
  <div class="modal" id="userModal">
    <div class="modal-content">
      <div class="modal-header"><span id="modalTitle">Create User</span><span class="close-modal" data-close>&times;</span></div>
      <form id="userForm">
        <input type="hidden" name="user_id" id="userId" />
        <input type="hidden" name="action" id="formAction" value="create" />
        
        <div class="form-group">
          <label for="userTypeSelect">User Type *</label>
          <select id="userTypeSelect" name="user_type" required>
            <option value="">Select User Type</option>
            <option value="admin">Admin</option>
            <option value="subadmin">Sub-admin</option>
            <option value="student">Member</option>
          </select>
        </div>

        <div class="form-group"><label for="username">Username *</label><input type="text" id="username" name="username" required /></div>
        
        <div class="form-group">
          <label for="password">Password *</label>
          <div class="password-field-wrapper">
            <input type="password" id="password" name="password" required style="padding-right: 40px;" />
            <button type="button" class="password-toggle" onclick="togglePassword()" title="Show/Hide Password"><i class="fas fa-eye" id="passwordToggleIcon"></i></button>
          </div>
          <div class="password-controls">
            <button type="button" class="btn-generate" onclick="generatePassword()"><i class="fas fa-key"></i> Auto Generate</button>
            <button type="button" class="btn-copy" onclick="copyPassword()" title="Copy password to clipboard"><i class="fas fa-copy"></i> Copy</button>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group"><label for="fname">First Name *</label><input type="text" id="fname" name="fname" required /></div>
          <div class="form-group"><label for="mname">Middle Name</label><input type="text" id="mname" name="mname" /></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label for="lname">Last Name *</label><input type="text" id="lname" name="lname" required /></div>
          <div class="form-group"><label for="auxname">Suffix</label><input type="text" id="auxname" name="auxname" placeholder="Jr., Sr., III, etc." /></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label for="gender">Gender *</label>
            <select id="gender" name="gender" required><option value="">Select Gender</option><option value="Male">Male</option><option value="Female">Female</option><option value="Not specified">Prefer not to say</option></select>
          </div>
          <div class="form-group"><label for="birthdate">Birthdate *</label><input type="date" id="birthdate" name="birthdate" required /></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label for="contact">Contact Number</label><input type="text" id="contact" name="contact" placeholder="09XXXXXXXXX" /></div>
          <div class="form-group"><label for="email">Email *</label><input type="email" id="email" name="email" required /></div>
        </div>

        <!-- SUB-ADMIN SPECIFIC FIELDS -->
        <div id="subadminFields" style="display:none;">
          <div class="form-group">
            <label for="subadminRole">Sub-admin Role *</label>
            <select id="subadminRole" name="subadmin_role"><option value="">Select Role</option></select>
            <input type="hidden" id="subadminPositionId" name="position_id">
          </div>
          <div id="permissionsSection">
            <label style="font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;display:block;">Permissions</label>
            <div class="perm-dropdown" id="permDropdown">
              <div class="perm-dropdown-btn" id="permDropdownBtn">Select permissions...</div>
              <div class="perm-dropdown-menu" id="permDropdownMenu">
                <label class="perm-option"><input type="checkbox" value="user_management"> User Management</label>
                <label class="perm-option"><input type="checkbox" value="task_management"> Task Management</label>
                <label class="perm-option"><input type="checkbox" value="reports"> Reports</label>
              </div>
            </div>
            <div class="perm-chips" id="permChips"></div>
          </div>
        </div>
        
        <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
          <button type="submit" class="btn-primary">Save</button>
          <button type="button" class="btn-secondary" data-close>Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- DELETE CONFIRMATION MODAL -->
  <div class="modal" id="deleteModal">
    <div class="modal-content">
      <div class="modal-header"><span>Delete Confirmation</span><span class="close-modal" data-close>&times;</span></div>
      <div id="deleteMessage">Are you sure you want to delete this user?</div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
        <button id="confirmDelete" class="btn-danger">Delete</button>
        <button class="btn-secondary" data-close>Cancel</button>
      </div>
    </div>
  </div>

  <!-- NOTIFICATION MODAL -->
  <div class="notification-modal" id="notificationModal">
    <div class="notification-content">
      <div class="notification-header" id="notificationHeader">
        <span class="notification-icon" id="notificationIcon"></span>
        <span class="notification-title" id="notificationTitle"></span>
      </div>
      <div class="notification-body" id="notificationBody"></div>
      <div class="notification-footer"><button class="notification-btn primary" onclick="closeNotification()">OK</button></div>
    </div>
  </div>

<script>
// Global Notification System
function showNotification(message, type = 'info', title = '') {
    const modal = document.getElementById('notificationModal');
    const header = document.getElementById('notificationHeader');
    const icon = document.getElementById('notificationIcon');
    const titleEl = document.getElementById('notificationTitle');
    const body = document.getElementById('notificationBody');
    header.className = 'notification-header';
    const types = { success: ['success','✓','Success'], error: ['error','✕','Error'], warning: ['warning','⚠','Warning'], info: ['info','ℹ','Information'] };
    const [cls, ico, def] = types[type] || types.info;
    header.classList.add(cls);
    icon.innerHTML = ico;
    titleEl.textContent = title || def;
    body.textContent = message;
    modal.classList.add('show');
}
function closeNotification() { document.getElementById('notificationModal').classList.remove('show'); }
document.addEventListener('click', function(e) { if (e.target === document.getElementById('notificationModal')) closeNotification(); });
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeNotification(); });

function togglePassword() {
    const p = document.getElementById('password'), i = document.getElementById('passwordToggleIcon');
    if (p.type === 'password') { p.type = 'text'; i.classList.replace('fa-eye','fa-eye-slash'); }
    else { p.type = 'password'; i.classList.replace('fa-eye-slash','fa-eye'); }
}
function generatePassword() {
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let pw = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random()*26)] + 'abcdefghijklmnopqrstuvwxyz'[Math.floor(Math.random()*26)] + '0123456789'[Math.floor(Math.random()*10)] + '!@#$%^&*'[Math.floor(Math.random()*8)];
    for (let i = 4; i < 12; i++) pw += charset[Math.floor(Math.random()*charset.length)];
    pw = pw.split('').sort(() => Math.random()-0.5).join('');
    const p = document.getElementById('password'), ic = document.getElementById('passwordToggleIcon');
    p.value = pw; p.type = 'text'; ic.classList.replace('fa-eye','fa-eye-slash');
    showNotification('Password generated: ' + pw + '\n\nMake sure to copy it before saving.', 'success', 'Password Generated');
}
function copyPassword() {
    const p = document.getElementById('password');
    if (!p.value) { showNotification('Please enter or generate a password first.', 'warning', 'No Password'); return; }
    const t = document.createElement('input'); t.value = p.value; document.body.appendChild(t); t.select(); document.execCommand('copy'); document.body.removeChild(t);
    showNotification('Password copied to clipboard!', 'success', 'Copied');
}
function toggleDropdown(event) {
    event.stopPropagation();
    const menu = event.target.nextElementSibling;
    document.querySelectorAll('.dropdown-menu').forEach(m => { if (m !== menu) m.classList.remove('show'); });
    menu.classList.toggle('show');
}
document.addEventListener('click', function(e) { if (!e.target.matches('.dropdown-btn')) document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.remove('show')); });
</script>

<script>
(function($) {
    let deleteTarget = { id: null, type: null };
    const typeLabels = { admin: 'Admin', subadmin: 'Sub-admin', student: 'Member' };

    // Tab switching
    $('.tab-btn').on('click', function() {
        const target = $(this).data('tab');
        $('.tab-btn').removeClass('active');
        $('.tab-content').removeClass('active');
        $(this).addClass('active');
        $('#' + target).addClass('active');
    });

    // Show/hide subadmin fields based on user type selection
    $('#userTypeSelect').on('change', function() {
        if ($(this).val() === 'subadmin') {
            $('#subadminFields').show();
            $('#subadminRole').prop('required', true);
            loadSubadminPositions();
        } else {
            $('#subadminFields').hide();
            $('#subadminRole').prop('required', false);
        }
    });

    // Load subadmin positions
    function loadSubadminPositions(selectedRole) {
        $.post('ajax.php', { CALL: 67 }, function(resp) {
            var $select = $('#subadminRole');
            $select.find('option:not(:first)').remove();
            if (resp.status === 'SUCCESS' && resp.data.length) {
                $.each(resp.data, function(i, pos) {
                    var opt = $('<option>').val(pos.position).text(pos.position).attr('data-position-id', pos.position_id);
                    if (selectedRole && pos.position === selectedRole) { opt.prop('selected', true); $('#subadminPositionId').val(pos.position_id); }
                    $select.append(opt);
                });
            }
        }, 'json');
    }

    $(document).on('change', '#subadminRole', function() {
        $('#subadminPositionId').val($(this).find('option:selected').attr('data-position-id') || '');
    });

    // Unified Users DataTable
    const usersTable = $('#usersTable').DataTable({
        ajax: {
            url: 'ajax.php', type: 'POST', data: { CALL: 68 },
            dataSrc: function(json) { return json.data || []; }
        },
        columns: [
            { data: 'user_id' },
            { data: 'user_name' },
            { data: null, render: function(d,t,row) { return `${row.fname} ${row.mname?row.mname+' ':''}${row.lname}${row.auxname?' '+row.auxname:''}`; } },
            { data: 'user_type', render: function(val) {
                const cls = 'type-' + val;
                const label = typeLabels[val] || val;
                return `<span class="type-badge ${cls}">${label}</span>`;
            }},
            { data: 'subadmin_role', render: function(val) { return val || '—'; } },
            { data: 'email' },
            { data: 'contact_number' },
            { data: null, orderable: false, render: function(d,t,row) {
                return `<div class="dropdown-container"><button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button><div class="dropdown-menu">
                    <button class="dropdown-item edit editBtn" data-id="${row.user_id}" data-type="${row.user_type}"><i class="fas fa-edit"></i> Edit</button>
                    <button class="dropdown-item delete deleteBtn" data-id="${row.user_id}" data-type="${row.user_type}"><i class="fas fa-trash"></i> Delete</button>
                </div></div>`;
            }}
        ],
        initComplete: function(){
            var q = new URLSearchParams(window.location.search).get('search');
            if(q) usersTable.search(q).draw();
        }
    });

    // Modal helpers
    function openModal(id) { $('#' + id).css('display', 'flex'); }
    function closeModal() { $('.modal').hide(); }
    $(document).on('click', '[data-close]', closeModal);
    $(document).on('click', '.modal', function(e) { if (e.target === this) closeModal(); });

    // Open create modal
    $('#openCreateUser').on('click', function() {
        $('#userForm')[0].reset();
        $('#userId').val('');
        $('#formAction').val('create');
        $('#modalTitle').text('Create User');
        $('#userTypeSelect').prop('disabled', false);
        $('#subadminFields').hide();
        $('#subadminRole').prop('required', false);
        $('#password').prop('required', true);
        $('#permDropdownMenu input').prop('checked', false);
        renderPermChips();
        openModal('userModal');
    });

    // Edit button
    $(document).on('click', '.editBtn', function() {
        const id = $(this).data('id'), type = $(this).data('type');
        $.post('ajax.php', { CALL: 13, user_id: id }, function(resp) {
            if (resp.status === 'SUCCESS') {
                const user = resp.data;
                $('#userId').val(user.user_id);
                $('#formAction').val('edit');
                $('#userTypeSelect').val(type).prop('disabled', true);
                $('#username').val(user.user_name);
                $('#password').val('').prop('required', false);
                $('#fname').val(user.fname);
                $('#mname').val(user.mname);
                $('#lname').val(user.lname);
                $('#auxname').val(user.auxname);
                $('#gender').val(user.gender);
                $('#birthdate').val(user.birthdate);
                $('#contact').val(user.contact_number);
                $('#email').val(user.email);
                if (type === 'subadmin') {
                    $('#subadminFields').show();
                    $('#subadminRole').prop('required', true);
                    loadSubadminPositions(user.subadmin_role || '');
                    $('#subadminPositionId').val(user.position_id || '');
                    loadUserPermissions(user.user_id);
                } else {
                    $('#subadminFields').hide();
                    $('#subadminRole').prop('required', false);
                }
                $('#modalTitle').text('Edit ' + (typeLabels[type] || type));
                openModal('userModal');
            } else {
                showNotification(resp.message || 'Unable to fetch user data', 'error');
            }
        }, 'json');
    });

    // Form submission
    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        const action = $('#formAction').val();
        const permissions = {};
        $('#permDropdownMenu input:checked').each(function() {
            permissions[$(this).val()] = { view: 1, create: 1, edit: 1, delete: 1 };
        });
        const payload = {
            CALL: action === 'create' ? 10 : 11,
            user_id: $('#userId').val(),
            user_type: $('#userTypeSelect').val(),
            user_name: $('#username').val(),
            password: $('#password').val(),
            fname: $('#fname').val(),
            mname: $('#mname').val(),
            lname: $('#lname').val(),
            auxname: $('#auxname').val(),
            gender: $('#gender').val(),
            birthdate: $('#birthdate').val(),
            contact_number: $('#contact').val(),
            email: $('#email').val(),
            subadmin_role: $('#subadminRole').val(),
            position_id: $('#subadminPositionId').val(),
            permissions: permissions
        };
        $.post('ajax.php', payload, function(resp) {
            if (resp.status === 'SUCCESS') { closeModal(); usersTable.ajax.reload(null, false); showNotification(resp.message || `User ${action}d successfully`, 'success'); }
            else { showNotification(resp.message || `Failed to ${action} user`, 'error'); }
        }, 'json');
    });

    // Delete
    $(document).on('click', '.deleteBtn', function() {
        deleteTarget.id = $(this).data('id');
        deleteTarget.type = $(this).data('type');
        $('#deleteMessage').text('Are you sure you want to delete this user?');
        openModal('deleteModal');
    });
    $('#confirmDelete').on('click', function() {
        if (!deleteTarget.id) return;
        $.post('ajax.php', { CALL: 12, user_id: deleteTarget.id, user_type: deleteTarget.type }, function(resp) {
            if (resp.status === 'SUCCESS') { closeModal(); usersTable.ajax.reload(null, false); showNotification(resp.message || 'User deleted', 'success'); }
            else { showNotification(resp.message || 'Failed to delete user', 'error'); }
        }, 'json');
    });

    function loadUserPermissions(userId) {
        $.post('ajax.php', { CALL: 69, user_id: userId }, function(resp) {
            if (resp.status === 'SUCCESS' && resp.data.length) {
                $('#permDropdownMenu input').prop('checked', false);
                resp.data.forEach(function(perm) {
                    $('#permDropdownMenu input[value="' + perm.permission_key + '"]').prop('checked', true);
                });
                renderPermChips();
            }
        }, 'json');
    }

    // Permission dropdown logic
    const permLabels = { user_management: 'User Management', task_management: 'Task Management', reports: 'Reports' };
    $('#permDropdownBtn').on('click', function(e) { e.stopPropagation(); $('#permDropdownMenu').toggleClass('show'); });
    $(document).on('click', function(e) { if (!$(e.target).closest('#permDropdown').length) $('#permDropdownMenu').removeClass('show'); });
    $(document).on('change', '#permDropdownMenu input', function() { renderPermChips(); });
    function renderPermChips() {
        var html = '';
        $('#permDropdownMenu input:checked').each(function() {
            var val = $(this).val();
            html += '<span class="perm-chip">' + (permLabels[val] || val) + ' <span class="perm-chip-remove" data-val="' + val + '">&times;</span></span>';
        });
        $('#permChips').html(html);
        var count = $('#permDropdownMenu input:checked').length;
        $('#permDropdownBtn').text(count ? count + ' permission(s) selected' : 'Select permissions...');
    }
    $(document).on('click', '.perm-chip-remove', function() {
        var val = $(this).data('val');
        $('#permDropdownMenu input[value="' + val + '"]').prop('checked', false);
        renderPermChips();
    });
})(jQuery);
</script>

<script>
(function($) {
    // Sub-tab switching
    $(document).on('click', '.sub-tab-link', function() {
        $('.sub-tab-link').removeClass('active');
        $('.sub-tab-content').hide();
        $(this).addClass('active');
        $('#' + $(this).data('subtab')).show();
    });

    let activeMembersTable, inactiveMembersTable, positiontable;
    let selectedMemberId = null;
    let memberTablesInitialized = false, positionTableInitialized = false;

    // Member Activity tab init
    $(document).on('click', '.tab-btn[data-tab="member-activity"]', function() {
        if (memberTablesInitialized) return;
        memberTablesInitialized = true;
        activeMembersTable = $('#activeMembersTable').DataTable({
            ajax: { url: 'ajax.php', type: 'POST', data: { CALL: 47 }, dataSrc: function(j) { return j.data||[]; } },
            columns: [
                { data: 'user_id' }, { data: 'user_name' },
                { data: null, render: function(d,t,row) { return `${row.fname} ${row.mname?row.mname+' ':''}${row.lname}${row.auxname?' '+row.auxname:''}`; } },
                { data: 'email' }, { data: 'contact_number' },
                { data: 'submission_count', render: function(d) { return d||'0'; } },
                { data: 'upload_count', render: function(d) { return d||'0'; } },
                { data: null, orderable: false, render: function(d,t,row) {
                    return `<div class="dropdown-container"><button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button><div class="dropdown-menu">
                        <button class="dropdown-item view viewMemberBtn" data-id="${row.user_id}"><i class="fas fa-eye"></i> View</button>
                        <button class="dropdown-item submissions viewSubmissionsBtn" data-id="${row.user_id}" data-name="${row.fname} ${row.lname}"><i class="fas fa-tasks"></i> Submissions</button>
                        <button class="dropdown-item uploads viewUploadsBtn" data-id="${row.user_id}" data-name="${row.fname} ${row.lname}"><i class="fas fa-file-upload"></i> Uploads</button>
                        <button class="dropdown-item deactivate deactivateBtn" data-id="${row.user_id}"><i class="fas fa-user-slash"></i> Deactivate</button>
                    </div></div>`;
                }}
            ]
        });
        inactiveMembersTable = $('#inactiveMembersTable').DataTable({
            ajax: { url: 'ajax.php', type: 'POST', data: { CALL: 48 }, dataSrc: function(j) { return j.data||[]; } },
            columns: [
                { data: 'user_id' }, { data: 'user_name' },
                { data: null, render: function(d,t,row) { return `${row.fname} ${row.mname?row.mname+' ':''}${row.lname}${row.auxname?' '+row.auxname:''}`; } },
                { data: 'email' }, { data: 'contact_number' }, { data: 'deactivated_at' },
                { data: null, orderable: false, render: function(d,t,row) {
                    return `<div class="dropdown-container"><button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button><div class="dropdown-menu">
                        <button class="dropdown-item view viewMemberBtn" data-id="${row.user_id}"><i class="fas fa-eye"></i> View</button>
                        <button class="dropdown-item reactivate reactivateBtn" data-id="${row.user_id}"><i class="fas fa-user-check"></i> Reactivate</button>
                    </div></div>`;
                }}
            ]
        });
    });

    // Positions tab init
    $(document).on('click', '.tab-btn[data-tab="positions"]', function() {
        if (positionTableInitialized) return;
        positionTableInitialized = true;
        positiontable = $('#positiontable').DataTable({
            ajax: { url: 'ajax.php', type: 'post', data: { CALL: 4 }, dataType: 'json' },
            paging: true,
            columns: [
                { data: "position_id" }, { data: "position" },
                { data: "access_restriction", render: function(val) {
                    var map = {1:{label:'Superadmin',cls:'access-superadmin'},2:{label:'Sub-admin',cls:'access-subadmin'},3:{label:'Member',cls:'access-member'}};
                    var info = map[val]||{label:'Unknown',cls:''};
                    return '<span class="access-badge '+info.cls+'">'+val+' — '+info.label+'</span>';
                }},
                { data: 'position_id', render: function(id, type, row) {
                    return `<div class="dropdown-container"><button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button><div class="dropdown-menu">
                        <button class="dropdown-item edit updatePositionBtn" data-id="${id}" data-access="${row.access_restriction}"><i class="fas fa-edit"></i> Edit</button>
                        <button class="dropdown-item delete deletePositionBtn" data-id="${id}"><i class="far fa-trash-alt"></i> Delete</button>
                    </div></div>`;
                }}
            ],
            columnDefs: [{ targets: 0, visible: false, searchable: false }]
        });
    });

    // Position CRUD
    // Open Add Position modal
    $(document).on('click', '#openAddPositionModal', function() {
        $('#positionName').val(''); $('#positionAccess').val('');
        $('#addPositionModal').css('display','flex');
    });
    $(document).on('click', '#saveposition', function() {
        var pos = $('#positionName').val().trim(), access = $('#positionAccess').val();
        if (!pos) { showNotification('Please enter position name', 'error'); return; }
        if (!access) { showNotification('Please select an access restriction level', 'error'); return; }
        $.post('ajax.php', { CALL: 1, DATA: { NAME: pos, ACCESS_RESTRICTION: access } }, function(r) {
            showNotification(r.msg, r.status==='SUCCESS'?'success':'error');
            if (r.status==='SUCCESS') { $('#addPositionModal').hide(); $('#positionName').val(''); $('#positionAccess').val(''); positiontable.ajax.reload(null,false); }
        }, 'json');
    });
    $(document).on('click', '.updatePositionBtn', function() {
        var id=$(this).data('id'), access=$(this).data('access'), name=$(this).closest('tr').find('td').eq(0).text();
        $('#updatePositionName').val(name); $('#updatePositionAccess').val(access);
        $('#updatePositionModal').data('id',id).css('display','flex');
    });
    $(document).on('click', '#confirmUpdatePosition', function() {
        var val=$('#updatePositionName').val().trim(), access=$('#updatePositionAccess').val(), id=$('#updatePositionModal').data('id');
        if (!val) { showNotification('Please enter a value','error'); return; }
        if (!access) { showNotification('Please select access level','error'); return; }
        $.post('ajax.php', { CALL: 64, DATA: { id:id, table:'position_tbl', value:val, title:'Position', access_restriction:access } }, function(r) {
            showNotification(r.msg, r.status==='SUCCESS'?'success':'error');
            if (r.status==='SUCCESS') { $('#updatePositionModal').hide(); positiontable.ajax.reload(null,false); }
        }, 'json');
    });
    $(document).on('click', '.deletePositionBtn', function() {
        var id=$(this).data('id'), name=$(this).closest('tr').find('td').eq(0).text();
        $('#deletePositionValue').text(name); $('#deletePositionModal').data('id',id).css('display','flex');
    });
    $(document).on('click', '#confirmDeletePosition', function() {
        var id=$('#deletePositionModal').data('id'), reason=$('#deletePositionReason').val().trim();
        if (!reason) { showNotification('Please enter a reason','error'); return; }
        $.post('ajax.php', { CALL: 65, DATA: { id:id, table:'position_tbl', reason:reason, title:'Position' } }, function(r) {
            showNotification(r.msg, r.status==='SUCCESS'?'success':'error');
            if (r.status==='SUCCESS') { $('#deletePositionModal').hide(); $('#deletePositionReason').val(''); positiontable.ajax.reload(null,false); }
        }, 'json');
    });

    // Member Activity actions
    $(document).on('click', '.viewMemberBtn', function() {
        $.post('ajax.php', { CALL: 13, user_id: $(this).data('id') }, function(resp) {
            if (resp.status==='SUCCESS') {
                var m=resp.data;
                $('#memberDetailsContent').html(`<div class="member-details">
                    <div class="detail-item"><label>Username</label><span>${m.user_name}</span></div>
                    <div class="detail-item"><label>Email</label><span>${m.email}</span></div>
                    <div class="detail-item"><label>Full Name</label><span>${m.fname} ${m.mname||''} ${m.lname} ${m.auxname||''}</span></div>
                    <div class="detail-item"><label>Contact</label><span>${m.contact_number||'N/A'}</span></div>
                    <div class="detail-item"><label>Gender</label><span>${m.gender}</span></div>
                    <div class="detail-item"><label>Birthdate</label><span>${m.birthdate}</span></div>
                </div><div class="form-actions"><button class="btn-secondary" data-close>Close</button></div>`);
                $('#viewMemberModal').css('display','flex');
            }
        }, 'json');
    });
    $(document).on('click', '.viewSubmissionsBtn', function() {
        var userId=$(this).data('id'), name=$(this).data('name');
        $.post('ajax.php', { CALL: 49, user_id: userId }, function(resp) {
            if (resp.status==='SUCCESS') {
                var subs=resp.data, html=`<h4>Submissions by ${name}</h4>`;
                if (!subs.length) html+='<p>No submissions found.</p>';
                else { html+='<ul class="submission-list">'; subs.forEach(function(s){ html+=`<li><div><h5>${s.task_title}</h5><small>File: ${s.file_name} • ${s.submitted_at} • ${s.check_status}</small></div><a href="ajax.php?CALL=download&file_id=${s.file_upload_id}" class="btn-sm btn-primary">Download</a></li>`; }); html+='</ul>'; }
                $('#submissionsContent').html(html); $('#viewSubmissionsModal').css('display','flex');
            }
        }, 'json');
    });
    $(document).on('click', '.viewUploadsBtn', function() {
        var userId=$(this).data('id'), name=$(this).data('name');
        $.post('ajax.php', { CALL: 50, user_id: userId }, function(resp) {
            if (resp.status==='SUCCESS') {
                var uploads=resp.data, html=`<h4>Uploads by ${name}</h4>`;
                if (!uploads.length) html+='<p>No uploads found.</p>';
                else { html+='<ul class="upload-list">'; uploads.forEach(function(u){ html+=`<li><div><h5>${u.file_name}</h5><small>Category: ${u.category_name} • ${u.datetime_uploaded} • ${u.mime_type}</small></div><a href="ajax.php?CALL=download&file_id=${u.file_upload_id}" class="btn-sm btn-primary">Download</a></li>`; }); html+='</ul>'; }
                $('#uploadsContent').html(html); $('#viewUploadsModal').css('display','flex');
            }
        }, 'json');
    });
    $(document).on('click', '.deactivateBtn', function() {
        selectedMemberId=$(this).data('id'); $('#deactivateReason').val(''); $('#deactivateMemberModal').css('display','flex');
    });
    $('#confirmDeactivate').on('click', function() {
        var reason=$('#deactivateReason').val().trim();
        if (!reason) { showNotification('Please provide a reason','error'); return; }
        $.post('ajax.php', { CALL: 51, user_id: selectedMemberId, reason: reason }, function(resp) {
            showNotification(resp.msg||(resp.status==='SUCCESS'?'Member deactivated':'Failed'), resp.status==='SUCCESS'?'success':'error');
            if (resp.status==='SUCCESS') { $('.modal').hide(); activeMembersTable.ajax.reload(); inactiveMembersTable.ajax.reload(); }
        }, 'json');
    });
    $(document).on('click', '.reactivateBtn', function() {
        selectedMemberId=$(this).data('id'); $('#reactivateMemberModal').css('display','flex');
    });
    $('#confirmReactivate').on('click', function() {
        $.post('ajax.php', { CALL: 52, user_id: selectedMemberId }, function(resp) {
            showNotification(resp.msg||(resp.status==='SUCCESS'?'Member reactivated':'Failed'), resp.status==='SUCCESS'?'success':'error');
            if (resp.status==='SUCCESS') { $('.modal').hide(); activeMembersTable.ajax.reload(); inactiveMembersTable.ajax.reload(); }
        }, 'json');
    });
})(jQuery);
</script>
</body>
</html>
