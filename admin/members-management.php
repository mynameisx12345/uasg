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
  <title>Members Management - UASG</title>
  
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
    .dropdown-item.submissions { color: #17a2b8; }
    .dropdown-item.uploads { color: #6c757d; }
    .dropdown-item.deactivate { color: #dc3545; }
    .dropdown-item.reactivate { color: #28a745; }
    
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
      min-width: 600px;
      max-width: 800px;
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
    .status-active { background: #28a745; color: white; }
    .status-inactive { background: #6c757d; color: white; }
    .member-details {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    .detail-item {
      padding: 0.5rem;
      background: #f8f9fa;
      border-radius: 4px;
    }
    .detail-item label {
      font-weight: 600;
      font-size: 0.85rem;
      color: #666;
      display: block;
      margin-bottom: 0.25rem;
    }
    .detail-item span {
      font-size: 0.95rem;
      color: #333;
    }
    .submission-list, .upload-list {
      list-style: none;
      padding: 0;
    }
    .submission-list li, .upload-list li {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem;
      border-bottom: 1px solid #eee;
      background: #fafbfc;
      margin-bottom: 0.5rem;
      border-radius: 4px;
    }
    .submission-info, .upload-info {
      flex: 1;
    }
    .submission-info h5, .upload-info h5 {
      margin: 0 0 0.25rem 0;
      font-size: 0.95rem;
    }
    .submission-info small, .upload-info small {
      color: #666;
      font-size: 0.8rem;
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
    <h1>Members Management</h1>
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
        <h2>Members (Students)</h2>

        <!-- TABS -->
        <div class="tabs">
          <button class="tab-link active" data-tab="active-members">Active Members</button>
          <button class="tab-link" data-tab="inactive-members">Inactive Members</button>
        </div>

        <!-- ACTIVE MEMBERS TAB -->
        <div id="active-members" class="tab-content active">
          <div class="table-container">
            <table id="activeMembersTable" class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Username</th>
                  <th>Full Name</th>
                  <th>Email</th>
                  <th>Contact</th>
                  <th>Submissions</th>
                  <th>Uploads</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>

        <!-- INACTIVE MEMBERS TAB -->
        <div id="inactive-members" class="tab-content">
          <div class="table-container">
            <table id="inactiveMembersTable" class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Username</th>
                  <th>Full Name</th>
                  <th>Email</th>
                  <th>Contact</th>
                  <th>Deactivated</th>
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

  <!-- VIEW MEMBER DETAILS MODAL -->
  <div class="modal" id="viewMemberModal">
    <div class="modal-content">
      <div class="modal-header">
        <span>Member Details</span>
        <span class="close-modal" data-close>&times;</span>
      </div>
      <div id="memberDetailsContent"></div>
    </div>
  </div>

  <!-- VIEW SUBMISSIONS MODAL -->
  <div class="modal" id="viewSubmissionsModal">
    <div class="modal-content">
      <div class="modal-header">
        <span>Task Submissions</span>
        <span class="close-modal" data-close>&times;</span>
      </div>
      <div id="submissionsContent"></div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
        <button class="btn-secondary" data-close>Close</button>
      </div>
    </div>
  </div>

  <!-- VIEW UPLOADS MODAL -->
  <div class="modal" id="viewUploadsModal">
    <div class="modal-content">
      <div class="modal-header">
        <span>All Uploads</span>
        <span class="close-modal" data-close>&times;</span>
      </div>
      <div id="uploadsContent"></div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
        <button class="btn-secondary" data-close>Close</button>
      </div>
    </div>
  </div>

  <!-- DEACTIVATE MEMBER MODAL -->
  <div class="modal" id="deactivateMemberModal">
    <div class="modal-content">
      <div class="modal-header">
        <span>Deactivate Member</span>
        <span class="close-modal" data-close>&times;</span>
      </div>
      <p>Are you sure you want to deactivate this member?</p>
      <div class="form-group">
        <label for="deactivateReason">Reason for deactivation *</label>
        <textarea id="deactivateReason" rows="3" placeholder="Enter reason..." required></textarea>
      </div>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
        <button id="confirmDeactivate" class="btn-danger">Deactivate</button>
        <button class="btn-secondary" data-close>Cancel</button>
      </div>
    </div>
  </div>

  <!-- REACTIVATE MEMBER MODAL -->
  <div class="modal" id="reactivateMemberModal">
    <div class="modal-content">
      <div class="modal-header">
        <span>Reactivate Member</span>
        <span class="close-modal" data-close>&times;</span>
      </div>
      <p>Are you sure you want to reactivate this member?</p>
      <div class="form-actions" style="justify-content:flex-end;margin-top:1.5rem;">
        <button id="confirmReactivate" class="btn-success">Reactivate</button>
        <button class="btn-secondary" data-close>Cancel</button>
      </div>
    </div>
  </div>

<script>
(function($) {
    let selectedMemberId = null;
    
    // Tab switching
    $('.tab-link').on('click', function() {
        const target = $(this).data('tab');
        $('.tab-link').removeClass('active');
        $('.tab-content').removeClass('active');
        $(this).addClass('active');
        $('#' + target).addClass('active');
    });

    // Initialize DataTables
    const activeMembersTable = $('#activeMembersTable').DataTable({
        ajax: {
            url: 'ajax.php',
            type: 'POST',
            data: { CALL: 47 }, // Get active members
            dataSrc: function(json) { return json.data || []; },
            error: function(xhr, error, code) {
                console.error('AJAX Error for active members:', error, code);
                console.error('Response:', xhr.responseText);
            }
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
                data: 'submission_count',
                render: function(data) {
                    return data || '0';
                }
            },
            { 
                data: 'upload_count',
                render: function(data) {
                    return data || '0';
                }
            },
            { 
                data: null, 
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="dropdown-container">
                            <button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
                            <div class="dropdown-menu">
                                <button class="dropdown-item view viewMemberBtn" data-id="${row.user_id}">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="dropdown-item submissions viewSubmissionsBtn" data-id="${row.user_id}" data-name="${row.fname} ${row.lname}">
                                    <i class="fas fa-tasks"></i> Submissions
                                </button>
                                <button class="dropdown-item uploads viewUploadsBtn" data-id="${row.user_id}" data-name="${row.fname} ${row.lname}">
                                    <i class="fas fa-file-upload"></i> Uploads
                                </button>
                                <button class="dropdown-item deactivate deactivateBtn" data-id="${row.user_id}">
                                    <i class="fas fa-user-slash"></i> Deactivate
                                </button>
                            </div>
                        </div>
                    `;
                }
            }
        ]
    });

    const inactiveMembersTable = $('#inactiveMembersTable').DataTable({
        ajax: {
            url: 'ajax.php',
            type: 'POST',
            data: { CALL: 48 }, // Get inactive members
            dataSrc: function(json) { return json.data || []; },
            error: function(xhr, error, code) {
                console.error('AJAX Error for inactive members:', error, code);
                console.error('Response:', xhr.responseText);
            }
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
            { data: 'deactivated_at' },
            { 
                data: null, 
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="dropdown-container">
                            <button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
                            <div class="dropdown-menu">
                                <button class="dropdown-item view viewMemberBtn" data-id="${row.user_id}">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="dropdown-item reactivate reactivateBtn" data-id="${row.user_id}">
                                    <i class="fas fa-user-check"></i> Reactivate
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

    // View member details button
    $(document).on('click', '.viewMemberBtn', function() {
        const userId = $(this).data('id');
        
        $.post('ajax.php', { CALL: 13, user_id: userId }, function(resp) {
            if (resp.status === 'SUCCESS') {
                const member = resp.data;
                const content = `
                    <div class="member-details">
                        <div class="detail-item">
                            <label>Username</label>
                            <span>${member.user_name}</span>
                        </div>
                        <div class="detail-item">
                            <label>Email</label>
                            <span>${member.email}</span>
                        </div>
                        <div class="detail-item">
                            <label>Full Name</label>
                            <span>${member.fname} ${member.mname || ''} ${member.lname} ${member.auxname || ''}</span>
                        </div>
                        <div class="detail-item">
                            <label>Contact Number</label>
                            <span>${member.contact_number || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <label>Gender</label>
                            <span>${member.gender}</span>
                        </div>
                        <div class="detail-item">
                            <label>Birthdate</label>
                            <span>${member.birthdate}</span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button class="btn-secondary" data-close>Close</button>
                    </div>
                `;
                $('#memberDetailsContent').html(content);
                openModal('viewMemberModal');
            } else {
                alert('Unable to fetch member data');
            }
        }, 'json');
    });

    // View submissions button
    $(document).on('click', '.viewSubmissionsBtn', function() {
        const userId = $(this).data('id');
        const memberName = $(this).data('name');
        
        $.post('ajax.php', { CALL: 49, user_id: userId }, function(resp) {
            if (resp.status === 'SUCCESS') {
                const submissions = resp.data;
                let content = `<h4>Submissions by ${memberName}</h4>`;
                
                if (submissions.length === 0) {
                    content += '<p>No submissions found.</p>';
                } else {
                    content += '<ul class="submission-list">';
                    submissions.forEach(function(sub) {
                        content += `
                            <li>
                                <div class="submission-info">
                                    <h5>${sub.task_title}</h5>
                                    <small>File: ${sub.file_name}</small><br>
                                    <small>Submitted: ${sub.submitted_at}</small><br>
                                    <small>Status: <span class="status-badge">${sub.check_status}</span></small>
                                </div>
                                <a href="ajax.php?CALL=download&file_id=${sub.file_upload_id}" class="btn-sm btn-primary" download>Download</a>
                            </li>
                        `;
                    });
                    content += '</ul>';
                }
                
                $('#submissionsContent').html(content);
                openModal('viewSubmissionsModal');
            } else {
                alert('Unable to fetch submissions');
            }
        }, 'json');
    });

    // View uploads button
    $(document).on('click', '.viewUploadsBtn', function() {
        const userId = $(this).data('id');
        const memberName = $(this).data('name');
        
        $.post('ajax.php', { CALL: 50, user_id: userId }, function(resp) {
            if (resp.status === 'SUCCESS') {
                const uploads = resp.data;
                let content = `<h4>All Uploads by ${memberName}</h4>`;
                
                if (uploads.length === 0) {
                    content += '<p>No uploads found.</p>';
                } else {
                    content += '<ul class="upload-list">';
                    uploads.forEach(function(upload) {
                        content += `
                            <li>
                                <div class="upload-info">
                                    <h5>${upload.file_name}</h5>
                                    <small>Category: ${upload.category_name}</small><br>
                                    <small>Uploaded: ${upload.datetime_uploaded}</small><br>
                                    <small>Type: ${upload.mime_type}</small>
                                </div>
                                <a href="ajax.php?CALL=download&file_id=${upload.file_upload_id}" class="btn-sm btn-primary" download>Download</a>
                            </li>
                        `;
                    });
                    content += '</ul>';
                }
                
                $('#uploadsContent').html(content);
                openModal('viewUploadsModal');
            } else {
                alert('Unable to fetch uploads');
            }
        }, 'json');
    });

    // Deactivate member button
    $(document).on('click', '.deactivateBtn', function() {
        selectedMemberId = $(this).data('id');
        $('#deactivateReason').val('');
        openModal('deactivateMemberModal');
    });

    // Confirm deactivate
    $('#confirmDeactivate').on('click', function() {
        const reason = $('#deactivateReason').val().trim();
        
        if (!reason) {
            alert('Please provide a reason for deactivation');
            return;
        }
        
        $.post('ajax.php', { 
            CALL: 51, 
            user_id: selectedMemberId,
            reason: reason
        }, function(resp) {
            if (resp.status === 'SUCCESS') {
                alert('Member deactivated successfully');
                closeModal();
                activeMembersTable.ajax.reload();
                inactiveMembersTable.ajax.reload();
            } else {
                alert(resp.msg || 'Failed to deactivate member');
            }
        }, 'json');
    });

    // Reactivate member button
    $(document).on('click', '.reactivateBtn', function() {
        selectedMemberId = $(this).data('id');
        openModal('reactivateMemberModal');
    });

    // Confirm reactivate
    $('#confirmReactivate').on('click', function() {
        $.post('ajax.php', { 
            CALL: 52, 
            user_id: selectedMemberId
        }, function(resp) {
            if (resp.status === 'SUCCESS') {
                alert('Member reactivated successfully');
                closeModal();
                activeMembersTable.ajax.reload();
                inactiveMembersTable.ajax.reload();
            } else {
                alert(resp.msg || 'Failed to reactivate member');
            }
        }, 'json');
    });

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
