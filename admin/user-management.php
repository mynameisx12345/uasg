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
    .dropdown-item.edit { color: #28a745; }
    .dropdown-item.delete { color: #dc3545; }
    .dropdown-item.permission { color: #007bff; }
    
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

    .permission-group {
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 1rem;
      margin-bottom: 1rem;
    }
    .permission-header {
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #333;
    }
    .permission-checks {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
      gap: 0.5rem;
    }
    .permission-check {
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }
    .inline-create {
      margin: 1rem 0;
    }
    .role-badge {
      padding: 0.25rem 0.5rem;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    .role-adviser { background: #28a745; color: white; }
    .role-president { background: #dc3545; color: white; }
    .role-vice-president { background: #fd7e14; color: white; }
    .role-secretary { background: #6f42c1; color: white; }
    .role-student { background: #17a2b8; color: white; }
    
    .password-field-wrapper {
      position: relative;
    }
    
    .password-controls {
      display: flex;
      gap: 8px;
      margin-top: 8px;
    }
    
    .password-toggle {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #666;
      background: none;
      border: none;
      padding: 5px;
      font-size: 16px;
    }
    
    .password-toggle:hover {
      color: #2196f3;
    }
    
    .btn-generate {
      padding: 8px 12px;
      background: #4CAF50;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 13px;
      transition: background 0.2s;
    }
    
    .btn-generate:hover {
      background: #45a049;
    }
    
    .btn-copy {
      padding: 8px 12px;
      background: #2196f3;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 13px;
      transition: background 0.2s;
    }
    
    .btn-copy:hover {
      background: #0b7dda;
    }
    
    /* Notification Modal Styles */
    .notification-modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.6);
      align-items: center;
      justify-content: center;
      z-index: 10000;
      animation: fadeIn 0.2s ease;
    }
    
    .notification-modal.show {
      display: flex;
    }
    
    .notification-content {
      background: white;
      border-radius: 12px;
      padding: 0;
      min-width: 400px;
      max-width: 500px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      animation: slideIn 0.3s ease;
      overflow: hidden;
    }
    
    .notification-header {
      padding: 20px 24px;
      border-bottom: 1px solid #e0e0e0;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .notification-header.success {
      background: #e8f5e9;
      color: #2e7d32;
    }
    
    .notification-header.error {
      background: #ffebee;
      color: #c62828;
    }
    
    .notification-header.warning {
      background: #fff3e0;
      color: #f57c00;
    }
    
    .notification-header.info {
      background: #e3f2fd;
      color: #1976d2;
    }
    
    .notification-icon {
      font-size: 28px;
    }
    
    .notification-title {
      font-size: 18px;
      font-weight: 600;
      flex: 1;
    }
    
    .notification-body {
      padding: 24px;
      font-size: 15px;
      line-height: 1.6;
      color: #333;
      white-space: pre-wrap;
    }
    
    .notification-footer {
      padding: 16px 24px;
      background: #f5f5f5;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
    
    .notification-btn {
      padding: 10px 24px;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .notification-btn.primary {
      background: #2196f3;
      color: white;
    }
    
    .notification-btn.primary:hover {
      background: #1976d2;
    }
    
    .notification-btn.secondary {
      background: #e0e0e0;
      color: #333;
    }
    
    .notification-btn.secondary:hover {
      background: #bdbdbd;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    @keyframes slideIn {
      from { 
        transform: translateY(-50px);
        opacity: 0;
      }
      to { 
        transform: translateY(0);
        opacity: 1;
      }
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
    <h1>User Management</h1>
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
        <h2>User Management</h2>

        <!-- TABS -->
        <div class="tabs">
          <button class="tab-link active" data-tab="admin">Admin</button>
          <button class="tab-link" data-tab="subadmin">Sub-admin</button>
          <button class="tab-link" data-tab="members">Members</button>
        </div>

        <!-- ADMIN TAB -->
        <div id="admin" class="tab-content active">
          <div class="inline-create">
            <button id="openCreateAdmin" class="btn-primary">Add Admin</button>
          </div>
          <div class="table-container">
            <table id="adminTable" class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Username</th>
                  <th>Full Name</th>
                  <th>Email</th>
                  <th>Contact</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>

        <!-- SUB-ADMIN TAB -->
        <div id="subadmin" class="tab-content">
          <div class="inline-create">
            <button id="openCreateSubadmin" class="btn-primary">Add Sub-admin</button>
          </div>
          <div class="table-container">
            <table id="subadminTable" class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Username</th>
                  <th>Full Name</th>
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

        <!-- MEMBERS TAB -->
        <div id="members" class="tab-content">
          <div class="inline-create">
            <button id="openCreateMember" class="btn-primary">Add Member</button>
          </div>
          <div class="table-container">
            <table id="membersTable" class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Username</th>
                  <th>Full Name</th>
                  <th>Email</th>
                  <th>Contact</th>
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

  <!-- CREATE/EDIT USER MODAL -->
  <div class="modal" id="userModal">
    <div class="modal-content">
      <div class="modal-header">
        <span id="modalTitle">Create User</span>
        <span class="close-modal" data-close>&times;</span>
      </div>
      <form id="userForm">
        <input type="hidden" name="user_id" id="userId" />
        <input type="hidden" name="user_type" id="userType" />
        <input type="hidden" name="action" id="formAction" value="create" />
        
        <div class="form-group">
          <label for="username">Username *</label>
          <input type="text" id="username" name="username" required />
        </div>
        
        <div class="form-group">
          <label for="password">Password *</label>
          <div class="password-field-wrapper">
            <input type="password" id="password" name="password" required style="padding-right: 40px;" />
            <button type="button" class="password-toggle" onclick="togglePassword()" title="Show/Hide Password">
              <i class="fas fa-eye" id="passwordToggleIcon"></i>
            </button>
          </div>
          <div class="password-controls">
            <button type="button" class="btn-generate" onclick="generatePassword()">
              <i class="fas fa-key"></i> Auto Generate
            </button>
            <button type="button" class="btn-copy" onclick="copyPassword()" title="Copy password to clipboard">
              <i class="fas fa-copy"></i> Copy
            </button>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="fname">First Name *</label>
            <input type="text" id="fname" name="fname" required />
          </div>
          
          <div class="form-group">
            <label for="mname">Middle Name</label>
            <input type="text" id="mname" name="mname" />
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="lname">Last Name *</label>
            <input type="text" id="lname" name="lname" required />
          </div>
          
          <div class="form-group">
            <label for="auxname">Suffix</label>
            <input type="text" id="auxname" name="auxname" placeholder="Jr., Sr., III, etc." />
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="gender">Gender *</label>
            <select id="gender" name="gender" required>
              <option value="">Select Gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Not specified">Prefer not to say</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="birthdate">Birthdate *</label>
            <input type="date" id="birthdate" name="birthdate" required />
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="contact">Contact Number</label>
            <input type="text" id="contact" name="contact" placeholder="09XXXXXXXXX" />
          </div>
          
          <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required />
          </div>
        </div>

        <!-- SUB-ADMIN SPECIFIC FIELDS -->
        <div id="subadminFields" style="display:none;">
          <div class="form-group">
            <label for="subadminRole">Sub-admin Role *</label>
            <select id="subadminRole" name="subadmin_role">
              <option value="">Select Role</option>
              <option value="Adviser">Adviser</option>
              <option value="President">President</option>
              <option value="Vice-President">Vice-President</option>
              <option value="Secretary">Secretary</option>
            </select>
          </div>

          <!-- PERMISSIONS SECTION -->
          <div id="permissionsSection">
            <h3>Permissions</h3>
            
            <div class="permission-group">
              <div class="permission-header">File Management</div>
              <div class="permission-checks">
                <label class="permission-check">
                  <input type="checkbox" name="permissions[file_management][view]" value="1"> View
                </label>
                <label class="permission-check">
                  <input type="checkbox" name="permissions[file_management][create]" value="1"> Create
                </label>
                <label class="permission-check">
                  <input type="checkbox" name="permissions[file_management][edit]" value="1"> Edit
                </label>
                <label class="permission-check">
                  <input type="checkbox" name="permissions[file_management][delete]" value="1"> Delete
                </label>
              </div>
            </div>

            <div class="permission-group">
              <div class="permission-header">User Management</div>
              <div class="permission-checks">
                <label class="permission-check">
                  <input type="checkbox" name="permissions[user_management][view]" value="1"> View
                </label>
                <label class="permission-check">
                  <input type="checkbox" name="permissions[user_management][create]" value="1"> Create
                </label>
                <label class="permission-check">
                  <input type="checkbox" name="permissions[user_management][edit]" value="1"> Edit
                </label>
                <label class="permission-check">
                  <input type="checkbox" name="permissions[user_management][delete]" value="1"> Delete
                </label>
              </div>
            </div>

            <div class="permission-group">
              <div class="permission-header">Entry Module</div>
              <div class="permission-checks">
                <label class="permission-check">
                  <input type="checkbox" name="permissions[entry_module][view]" value="1"> View
                </label>
                <label class="permission-check">
                  <input type="checkbox" name="permissions[entry_module][create]" value="1"> Create
                </label>
                <label class="permission-check">
                  <input type="checkbox" name="permissions[entry_module][edit]" value="1"> Edit
                </label>
                <label class="permission-check">
                  <input type="checkbox" name="permissions[entry_module][delete]" value="1"> Delete
                </label>
              </div>
            </div>

            <div class="permission-group">
              <div class="permission-header">Task Management</div>
              <div class="permission-checks">
                <label class="permission-check">
                  <input type="checkbox" name="permissions[task_management][view]" value="1"> View
                </label>
                <label class="permission-check">
                  <input type="checkbox" name="permissions[task_management][create]" value="1"> Create
                </label>
                <label class="permission-check">
                  <input type="checkbox" name="permissions[task_management][edit]" value="1"> Edit
                </label>
                <label class="permission-check">
                  <input type="checkbox" name="permissions[task_management][delete]" value="1"> Delete
                </label>
              </div>
            </div>
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
      <div class="modal-header">
        <span>Delete Confirmation</span>
        <span class="close-modal" data-close>&times;</span>
      </div>
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
      <div class="notification-footer">
        <button class="notification-btn primary" onclick="closeNotification()">OK</button>
      </div>
    </div>
  </div>

  <!-- PERMISSION MODAL -->
  <div class="modal" id="permissionModal">
    <div class="modal-content">
      <div class="modal-header">
        <span>Manage Permissions</span>
        <span class="close-modal" data-close>&times;</span>
      </div>
      <form id="permissionForm">
        <input type="hidden" id="permissionUserId" name="user_id" />
        <div id="permissionContent"></div>
        <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
          <button type="submit" class="btn-primary">Save Permissions</button>
          <button type="button" class="btn-secondary" data-close>Cancel</button>
        </div>
      </form>
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
    
    // Reset classes
    header.className = 'notification-header';
    
    // Set type-specific styling and icon
    let iconHtml = '';
    let defaultTitle = '';
    
    switch(type) {
        case 'success':
            header.classList.add('success');
            iconHtml = '✓';
            defaultTitle = 'Success';
            break;
        case 'error':
            header.classList.add('error');
            iconHtml = '✕';
            defaultTitle = 'Error';
            break;
        case 'warning':
            header.classList.add('warning');
            iconHtml = '⚠';
            defaultTitle = 'Warning';
            break;
        case 'info':
        default:
            header.classList.add('info');
            iconHtml = 'ℹ';
            defaultTitle = 'Information';
            break;
    }
    
    icon.innerHTML = iconHtml;
    titleEl.textContent = title || defaultTitle;
    body.textContent = message;
    
    modal.classList.add('show');
}

function closeNotification() {
    const modal = document.getElementById('notificationModal');
    modal.classList.remove('show');
}

// Close on outside click
document.addEventListener('click', function(e) {
    const modal = document.getElementById('notificationModal');
    if (e.target === modal) {
        closeNotification();
    }
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNotification();
    }
});

// Global Password Management Functions (must be outside jQuery closure for inline onclick)
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('passwordToggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

function generatePassword() {
    const length = 12;
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    
    // Ensure at least one of each type
    password += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random() * 26)]; // uppercase
    password += 'abcdefghijklmnopqrstuvwxyz'[Math.floor(Math.random() * 26)]; // lowercase
    password += '0123456789'[Math.floor(Math.random() * 10)]; // number
    password += '!@#$%^&*'[Math.floor(Math.random() * 8)]; // special char
    
    // Fill remaining length
    for (let i = password.length; i < length; i++) {
        password += charset[Math.floor(Math.random() * charset.length)];
    }
    
    // Shuffle the password
    password = password.split('').sort(() => Math.random() - 0.5).join('');
    
    // Set password and show it
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('passwordToggleIcon');
    
    passwordInput.value = password;
    passwordInput.type = 'text';
    toggleIcon.classList.remove('fa-eye');
    toggleIcon.classList.add('fa-eye-slash');
    
    // Show success notification
    showNotification(
        'Password generated successfully!\n\nPassword: ' + password + '\n\nMake sure to copy it before saving.',
        'success',
        'Password Generated'
    );
}

function copyPassword() {
    const passwordInput = document.getElementById('password');
    
    if (!passwordInput.value) {
        showNotification('Please enter or generate a password first.', 'warning', 'No Password');
        return;
    }
    
    // Create temporary input to copy
    const tempInput = document.createElement('input');
    tempInput.value = passwordInput.value;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
    
    // Show feedback
    showNotification('Password copied to clipboard!', 'success', 'Copied');
}

(function($) {
    let currentUserType = 'admin';
    let deleteTarget = { id: null, type: null };
    
    // Tab switching
    $('.tab-link').on('click', function() {
        const target = $(this).data('tab');
        $('.tab-link').removeClass('active');
        $('.tab-content').removeClass('active');
        $(this).addClass('active');
        $('#' + target).addClass('active');
        currentUserType = target;
    });

    // DataTables initialization
    const adminTable = $('#adminTable').DataTable({
        ajax: {
            url: 'ajax.php',
            type: 'POST',
            data: { CALL: 7 }, // Get admin users
            dataSrc: function(json) { return json.data || []; }
        },
        columns: [
            { data: 'user_id' },
            { data: 'user_name' },
            { 
                data: null,
                render: function(data, type, row) {
                    return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;
                }
            },
            { data: 'email' },
            { data: 'contact_number' },
            { 
                data: null, 
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="dropdown-container">
                            <button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
                            <div class="dropdown-menu">
                                <button class="dropdown-item edit editBtn" data-id="${row.user_id}" data-type="admin">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="dropdown-item delete deleteBtn" data-id="${row.user_id}" data-type="admin">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    `;
                }
            }
        ]
    });

    const subadminTable = $('#subadminTable').DataTable({
        ajax: {
            url: 'ajax.php',
            type: 'POST',
            data: { CALL: 8 }, // Get subadmin users
            dataSrc: function(json) { return json.data || []; }
        },
        columns: [
            { data: 'user_id' },
            { data: 'user_name' },
            { 
                data: null,
                render: function(data, type, row) {
                    return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;
                }
            },
            { 
                data: 'subadmin_role',
                render: function(data, type, row) {
                    if (!data) return '<span class="role-badge">Not Set</span>';
                    const roleClass = `role-${data.toLowerCase().replace('-', '')}`;
                    return `<span class="role-badge ${roleClass}">${data}</span>`;
                }
            },
            { data: 'email' },
            { data: 'contact_number' },
            { 
                data: null, 
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="dropdown-container">
                            <button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
                            <div class="dropdown-menu">
                                <button class="dropdown-item edit editBtn" data-id="${row.user_id}" data-type="subadmin">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="dropdown-item permission permissionBtn" data-id="${row.user_id}" data-name="${row.fname} ${row.lname}">
                                    <i class="fas fa-shield-alt"></i> Permissions
                                </button>
                                <button class="dropdown-item delete deleteBtn" data-id="${row.user_id}" data-type="subadmin">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    `;
                }
            }
        ]
    });

    const membersTable = $('#membersTable').DataTable({
        ajax: {
            url: 'ajax.php',
            type: 'POST',
            data: { CALL: 9 }, // Get student/member users
            dataSrc: function(json) { return json.data || []; }
        },
        columns: [
            { data: 'user_id' },
            { data: 'user_name' },
            { 
                data: null,
                render: function(data, type, row) {
                    return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;
                }
            },
            { data: 'email' },
            { data: 'contact_number' },
            { 
                data: null, 
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="dropdown-container">
                            <button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
                            <div class="dropdown-menu">
                                <button class="dropdown-item edit editBtn" data-id="${row.user_id}" data-type="student">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="dropdown-item delete deleteBtn" data-id="${row.user_id}" data-type="student">
                                    <i class="fas fa-trash"></i> Delete
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

    // Open create modals
    $('#openCreateAdmin').on('click', function() {
        openCreateModal('admin');
    });

    $('#openCreateSubadmin').on('click', function() {
        openCreateModal('subadmin');
    });

    $('#openCreateMember').on('click', function() {
        openCreateModal('student');
    });

    function openCreateModal(type) {
        $('#userForm')[0].reset();
        $('#userId').val('');
        $('#userType').val(type);
        $('#formAction').val('create');
        $('#modalTitle').text(`Create ${type === 'subadmin' ? 'Sub-admin' : (type === 'admin' ? 'Admin' : 'Member')}`);
        
        if (type === 'subadmin') {
            $('#subadminFields').show();
            $('#subadminRole').prop('required', true);
        } else {
            $('#subadminFields').hide();
            $('#subadminRole').prop('required', false);
        }
        
        openModal('userModal');
    }

    // Edit button
    $(document).on('click', '.editBtn', function() {
        const id = $(this).data('id');
        const type = $(this).data('type');
        
        $.post('ajax.php', { CALL: 13, user_id: id }, function(resp) {
            if (resp.status === 'SUCCESS') {
                const user = resp.data;
                $('#userId').val(user.user_id);
                $('#userType').val(type);
                $('#formAction').val('edit');
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
                    $('#subadminRole').val(user.subadmin_role || '').prop('required', true);
                    loadUserPermissions(user.user_id);
                } else {
                    $('#subadminFields').hide();
                    $('#subadminRole').prop('required', false);
                }
                
                $('#modalTitle').text(`Edit ${type === 'subadmin' ? 'Sub-admin' : (type === 'admin' ? 'Admin' : 'Member')}`);
                openModal('userModal');
            } else {
                showNotification(resp.message || 'Unable to fetch user data', 'error', 'Error');
            }
        }, 'json');
    });

    // Form submission
    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const action = $('#formAction').val();
        
        // Convert permissions to proper format
        const permissions = {};
        $('input[name^="permissions["]').each(function() {
            const name = $(this).attr('name');
            const match = name.match(/permissions\[([^\]]+)\]\[([^\]]+)\]/);
            if (match && $(this).is(':checked')) {
                const module = match[1];
                const permission = match[2];
                if (!permissions[module]) permissions[module] = {};
                permissions[module][permission] = 1;
            }
        });
        
        const payload = {
            CALL: action === 'create' ? 10 : 11,
            user_id: $('#userId').val(),
            user_type: $('#userType').val(),
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
            permissions: permissions
        };
        
        $.post('ajax.php', payload, function(resp) {
            if (resp.status === 'SUCCESS') {
                closeModal();
                reloadTables();
                showNotification(resp.message || `User ${action}d successfully`, 'success', 'Success');
            } else {
                showNotification(resp.message || `Failed to ${action} user`, 'error', 'Error');
            }
        }, 'json');
    });

    // Delete button
    $(document).on('click', '.deleteBtn', function() {
        deleteTarget.id = $(this).data('id');
        deleteTarget.type = $(this).data('type');
        $('#deleteMessage').text(`Are you sure you want to delete this ${deleteTarget.type}?`);
        openModal('deleteModal');
    });

    // Confirm delete
    $('#confirmDelete').on('click', function() {
        if (!deleteTarget.id) return;
        
        $.post('ajax.php', { 
            CALL: 12, 
            user_id: deleteTarget.id,
            user_type: deleteTarget.type
        }, function(resp) {
            if (resp.status === 'SUCCESS') {
                closeModal();
                reloadTables();
                showNotification(resp.message || 'User deleted successfully', 'success', 'Deleted');
            } else {
                showNotification(resp.message || 'Failed to delete user', 'error', 'Error');
            }
        }, 'json');
    });

    // Permission button
    $(document).on('click', '.permissionBtn', function() {
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        
        $('#permissionUserId').val(userId);
        loadPermissionModal(userId, userName);
        openModal('permissionModal');
    });

    function loadUserPermissions(userId) {
        $.post('ajax.php', { CALL: 15, user_id: userId }, function(resp) {
            if (resp.status === 'SUCCESS') {
                const permissions = resp.data;
                // Clear all checkboxes first
                $('input[name^="permissions["]').prop('checked', false);
                
                // Set permissions
                permissions.forEach(function(perm) {
                    const selector = `input[name="permissions[${perm.permission_key}][view]"]`;
                    if (perm.can_view) $(selector.replace('[view]', '[view]')).prop('checked', true);
                    if (perm.can_create) $(selector.replace('[view]', '[create]')).prop('checked', true);
                    if (perm.can_edit) $(selector.replace('[view]', '[edit]')).prop('checked', true);
                    if (perm.can_delete) $(selector.replace('[view]', '[delete]')).prop('checked', true);
                });
            }
        }, 'json');
    }

    function loadPermissionModal(userId, userName) {
        const content = `
            <h4>Permissions for: ${userName}</h4>
            <div class="permission-group">
                <div class="permission-header">File Management</div>
                <div class="permission-checks">
                    <label class="permission-check">
                        <input type="checkbox" name="perm_file_management_view" value="1"> View
                    </label>
                    <label class="permission-check">
                        <input type="checkbox" name="perm_file_management_create" value="1"> Create
                    </label>
                    <label class="permission-check">
                        <input type="checkbox" name="perm_file_management_edit" value="1"> Edit
                    </label>
                    <label class="permission-check">
                        <input type="checkbox" name="perm_file_management_delete" value="1"> Delete
                    </label>
                </div>
            </div>
            <!-- Add more permission groups as needed -->
        `;
        $('#permissionContent').html(content);
        
        // Load existing permissions
        $.post('ajax.php', { CALL: 15, user_id: userId }, function(resp) {
            if (resp.status === 'SUCCESS') {
                const permissions = resp.data;
                permissions.forEach(function(perm) {
                    $(`input[name="perm_${perm.permission_key}_view"]`).prop('checked', perm.can_view);
                    $(`input[name="perm_${perm.permission_key}_create"]`).prop('checked', perm.can_create);
                    $(`input[name="perm_${perm.permission_key}_edit"]`).prop('checked', perm.can_edit);
                    $(`input[name="perm_${perm.permission_key}_delete"]`).prop('checked', perm.can_delete);
                });
            }
        }, 'json');
    }

    // Permission form submission
    $('#permissionForm').on('submit', function(e) {
        e.preventDefault();
        const userId = $('#permissionUserId').val();
        const permissions = {};
        
        $('#permissionContent input[type="checkbox"]').each(function() {
            const name = $(this).attr('name');
            const match = name.match(/perm_([^_]+)_(.+)/);
            if (match && $(this).is(':checked')) {
                const module = match[1];
                const permission = match[2];
                if (!permissions[module]) permissions[module] = {};
                permissions[module][permission] = 1;
            }
        });
        
        $.post('ajax.php', { 
            CALL: 69, 
            user_id: userId,
            permissions: permissions
        }, function(resp) {
            if (resp.status === 'SUCCESS') {
                closeModal();
                showNotification('Permissions updated successfully', 'success', 'Success');
            } else {
                showNotification(resp.message || 'Failed to update permissions', 'error', 'Error');
            }
        }, 'json');
    });

    function reloadTables() {
        adminTable.ajax.reload(null, false);
        subadminTable.ajax.reload(null, false);
        membersTable.ajax.reload(null, false);
    }

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