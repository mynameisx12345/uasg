<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management</title>
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
          <button class="tab-link active" data-tab="admins">Administrators</button>
          <button class="tab-link" data-tab="subadmins">Subadmins</button>
          <button class="tab-link" data-tab="students">Student Members</button>
        </div>

        <!-- TAB CONTENT: ADMINISTRATORS -->
        <div class="tab-content active" id="admins">
          <div class="compact-form">
            <h3>Add New Administrator</h3>
            
            <div class="form-columns">
              <!-- Left Column -->
              <div class="form-column">
                <!-- Account Information -->
                <div class="form-section">
                  <h4>Account Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="adminUsername">Username</label>
                      <input type="text" id="adminUsername" placeholder="Username" required>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="adminPassword">Password</label>
                      <input type="password" id="adminPassword" placeholder="Password" required>
                    </div>
                  </div>
                </div>

                <!-- Personal Information -->
                <div class="form-section">
                  <h4>Personal Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="adminFname">First Name</label>
                      <input type="text" id="adminFname" placeholder="First Name" required>
                    </div>
                    <div class="form-group">
                      <label for="adminMname">Middle Name</label>
                      <input type="text" id="adminMname" placeholder="Middle Name">
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="adminLname">Last Name</label>
                      <input type="text" id="adminLname" placeholder="Last Name" required>
                    </div>
                    <div class="form-group form-group-small">
                      <label for="adminAuxname">Suffix</label>
                      <input type="text" id="adminAuxname" placeholder="Jr., Sr.">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right Column -->
              <div class="form-column">
                <!-- Personal Details -->
                <div class="form-section">
                  <h4>Personal Details</h4>
                  <div class="form-row">
                    <div class="form-group form-group-small">
                      <label for="adminGender">Gender</label>
                      <select id="adminGender" required>
                        <option value="">Select</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="adminBirthdate">Birthdate</label>
                      <input type="date" id="adminBirthdate" required>
                    </div>
                  </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section">
                  <h4>Contact Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="adminContact">Contact Number</label>
                      <input type="text" id="adminContact" placeholder="Contact Number">
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="adminEmail">Email</label>
                      <input type="email" id="adminEmail" placeholder="Email Address" required>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-actions">
              <button type="button" class="btn-primary" id="saveAdmin">Save Administrator</button>
              <button type="button" class="btn-secondary" onclick="clearForm('admin')">Clear</button>
            </div>
          </div>
          
          <div class="table-container">
            <table class='data-table' id='adminTable'>
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

        <!-- TAB CONTENT: ADVISERS -->
        <div class="tab-content" id="advisers">
          <div class="compact-form">
            <h3>Add New UASG Adviser</h3>
            
            <div class="form-columns">
              <!-- Left Column -->
              <div class="form-column">
                <!-- Account Information -->
                <div class="form-section">
                  <h4>Account Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="adviserUsername">Username</label>
                      <input type="text" id="adviserUsername" placeholder="Username" required>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="adviserPassword">Password</label>
                      <input type="password" id="adviserPassword" placeholder="Password" required>
                    </div>
                  </div>
                </div>

                <!-- Personal Information -->
                <div class="form-section">
                  <h4>Personal Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="adviserFname">First Name</label>
                      <input type="text" id="adviserFname" placeholder="First Name" required>
                    </div>
                    <div class="form-group">
                      <label for="adviserMname">Middle Name</label>
                      <input type="text" id="adviserMname" placeholder="Middle Name">
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="adviserLname">Last Name</label>
                      <input type="text" id="adviserLname" placeholder="Last Name" required>
                    </div>
                    <div class="form-group form-group-small">
                      <label for="adviserAuxname">Suffix</label>
                      <input type="text" id="adviserAuxname" placeholder="Jr., Sr.">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right Column -->
              <div class="form-column">
                <!-- Personal Details -->
                <div class="form-section">
                  <h4>Personal Details</h4>
                  <div class="form-row">
                    <div class="form-group form-group-small">
                      <label for="adviserGender">Gender</label>
                      <select id="adviserGender" required>
                        <option value="">Select</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="adviserBirthdate">Birthdate</label>
                      <input type="date" id="adviserBirthdate" required>
                    </div>
                  </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section">
                  <h4>Contact Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="adviserContact">Contact Number</label>
                      <input type="text" id="adviserContact" placeholder="Contact Number">
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="adviserEmail">Email</label>
                      <input type="email" id="adviserEmail" placeholder="Email Address" required>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-actions">
              <button type="button" class="btn-primary" id="saveAdviser">Save Adviser</button>
              <button type="button" class="btn-secondary" onclick="clearForm('adviser')">Clear</button>
            </div>
          </div>
          
          <div class="table-container">
            <table class='data-table' id='adviserTable'>
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

        <!-- TAB CONTENT: STUDENTS -->
        <div class="tab-content" id="students">
          <div class="compact-form">
            <h3>Add New Student Member</h3>
            
            <div class="form-columns">
              <!-- Left Column -->
              <div class="form-column">
                <!-- Account Information -->
                <div class="form-section">
                  <h4>Account Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="studentUsername">Username</label>
                      <input type="text" id="studentUsername" placeholder="Username" required>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="studentPassword">Password</label>
                      <input type="password" id="studentPassword" placeholder="Password" required>
                    </div>
                  </div>
                </div>

                <!-- Personal Information -->
                <div class="form-section">
                  <h4>Personal Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="studentFname">First Name</label>
                      <input type="text" id="studentFname" placeholder="First Name" required>
                    </div>
                    <div class="form-group">
                      <label for="studentMname">Middle Name</label>
                      <input type="text" id="studentMname" placeholder="Middle Name">
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="studentLname">Last Name</label>
                      <input type="text" id="studentLname" placeholder="Last Name" required>
                    </div>
                    <div class="form-group form-group-small">
                      <label for="studentAuxname">Suffix</label>
                      <input type="text" id="studentAuxname" placeholder="Jr., Sr.">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right Column -->
              <div class="form-column">
                <!-- Personal Details -->
                <div class="form-section">
                  <h4>Personal Details</h4>
                  <div class="form-row">
                    <div class="form-group form-group-small">
                      <label for="studentGender">Gender</label>
                      <select id="studentGender" required>
                        <option value="">Select</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="studentBirthdate">Birthdate</label>
                      <input type="date" id="studentBirthdate" required>
                    </div>
                  </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section">
                  <h4>Contact Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="studentContact">Contact Number</label>
                      <input type="text" id="studentContact" placeholder="Contact Number">
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="studentEmail">Email</label>
                      <input type="email" id="studentEmail" placeholder="Email Address" required>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-actions">
              <button type="button" class="btn-primary" id="saveStudent">Save Student Member</button>
              <button type="button" class="btn-secondary" onclick="clearForm('student')">Clear</button>
            </div>
          </div>
          
          <div class="table-container">
            <table class='data-table' id='studentTable'>
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

    <!-- UPDATE MODAL -->
    <div id="updateUserModal" class="modal">
      <div class="modal-content">
        <span class="modal-close" onclick="closeUpdateModal()">&times;</span>
        <h2>Update User Information</h2>
        
        <div class="compact-form">
          <input type="hidden" id="updateUserId">
          <input type="hidden" id="updateUserType">
          
          <div class="form-columns">
            <!-- Left Column -->
            <div class="form-column">
              <!-- Account Information -->
              <div class="form-section">
                <h4>Account Information</h4>
                <div class="form-row">
                  <div class="form-group">
                    <label for="updateUsername">Username</label>
                    <input type="text" id="updateUsername" required>
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label for="updatePassword">Password</label>
                    <input type="password" id="updatePassword" placeholder="Leave empty to keep current">
                  </div>
                </div>
              </div>
              
              <!-- Personal Information -->
              <div class="form-section">
                <h4>Personal Information</h4>
                <div class="form-row">
                  <div class="form-group">
                    <label for="updateFname">First Name</label>
                    <input type="text" id="updateFname" required>
                  </div>
                  <div class="form-group">
                    <label for="updateMname">Middle Name</label>
                    <input type="text" id="updateMname">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label for="updateLname">Last Name</label>
                    <input type="text" id="updateLname" required>
                  </div>
                  <div class="form-group form-group-small">
                    <label for="updateAuxname">Suffix</label>
                    <input type="text" id="updateAuxname">
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Right Column -->
            <div class="form-column">
              <!-- Personal Details -->
              <div class="form-section">
                <h4>Personal Details</h4>
                <div class="form-row">
                  <div class="form-group form-group-small">
                    <label for="updateGender">Gender</label>
                    <select id="updateGender" required>
                      <option value="">Select</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="updateBirthdate">Birthdate</label>
                    <input type="date" id="updateBirthdate" required>
                  </div>
                </div>
              </div>
              
              <!-- Contact Information -->
              <div class="form-section">
                <h4>Contact Information</h4>
                <div class="form-row">
                  <div class="form-group">
                    <label for="updateContact">Contact Number</label>
                    <input type="text" id="updateContact">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label for="updateEmail">Email</label>
                    <input type="email" id="updateEmail" required>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="form-actions">
            <button type="button" class="btn-primary" id="confirmUpdate">Update User</button>
            <button type="button" class="btn-secondary" onclick="closeUpdateModal()">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <!-- DELETE MODAL -->
    <div id="deleteUserModal" class="modal">
      <div class="modal-content modal-content-small">
        <span class="modal-close" onclick="closeDeleteModal()">&times;</span>
        <h2>Delete User Confirmation</h2>
        
        <div class="compact-form">
          <input type="hidden" id="deleteUserId">
          <input type="hidden" id="deleteUserType">
          
          <div class="form-group">
            <label for="deleteUserInfo">User to Delete</label>
            <input type='text' id='deleteUserInfo' readonly>
          </div>
          
          <div class="form-group">
            <label for="deleteReason">Reason for Deletion</label>
            <textarea id='deleteReason' name='deleteReason' rows='4' placeholder="Enter reason for deletion..." required></textarea>
          </div>
          
          <div class="form-actions">
            <button type="button" class="btn-primary" id="confirmDelete">Confirm Delete</button>
            <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    // Modal functions
    function openUpdateModal() {
      document.getElementById("updateUserModal").style.display = "flex";
    }
    
    function closeUpdateModal() {
      document.getElementById("updateUserModal").style.display = "none";
    }

    function openDeleteModal(){
      document.getElementById("deleteUserModal").style.display = "flex";
    }

    function closeDeleteModal(){
      document.getElementById("deleteUserModal").style.display = "none";
    }

    // Clear form function
    function clearForm(type) {
      const fields = ['Username', 'Password', 'Fname', 'Mname', 'Lname', 'Auxname', 'Gender', 'Birthdate', 'Contact', 'Email'];
      fields.forEach(field => {
        const element = document.getElementById(type + field);
        if (element) {
          element.value = '';
        }
      });
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
      let adminTable;
      let adviserTable;
      let studentTable;

      // DataTable initialization
      function initAdminTable(){
        adminTable = $("#adminTable").DataTable({
          ajax:{
            url:'ajax.php',
            type:'post',
            data:{
              CALL: 7 // Get admin users
            },
            dataType:'json',
            error: function(xhr, error, thrown) {
              console.log('DataTable AJAX Error:', error);
              console.log('Response:', xhr.responseText);
              openModal("ERROR", "Failed to load admin users: " + error);
            }
          },
          responsive: true,
          scroll: '50vh',
          scrollCollapse: true, 
          paging: true,
          columns: [
            { data: "user_id" },
            { data: "user_name" },
            { 
              data: null,
              render: function(data, type, row) {
                return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;
              }
            },
            { data: "email" },
            { data: "contact_number" },
            { 
              data: 'user_id',
              render: function(data, type, row) {
                return `
                  <button class="btn-secondary updateBtn" data-id="${data}" data-type="admin">Edit</button>
                  <button class="btn-primary deleteBtn" data-id="${data}" data-type="admin">Delete</button>
                `;
              }
            }
          ],
          columnDefs: [
            { targets: 0, visible: false, searchable: false } 
          ]
        });
      }

      function initAdviserTable(){
        adviserTable = $("#adviserTable").DataTable({
          ajax:{
            url:'ajax.php',
            type:'post',
            data:{
              CALL: 8 // Get adviser users
            },
            dataType:'json',
            error: function(xhr, error, thrown) {
              console.log('Adviser DataTable AJAX Error:', error);
              console.log('Response:', xhr.responseText);
              openModal("ERROR", "Failed to load adviser users: " + error);
            }
          },
          responsive: true,
          scroll: '50vh',
          scrollCollapse: true, 
          paging: true,
          columns: [
            { data: "user_id" },
            { data: "user_name" },
            { 
              data: null,
              render: function(data, type, row) {
                return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;
              }
            },
            { data: "email" },
            { data: "contact_number" },
            { 
              data: 'user_id',
              render: function(data, type, row) {
                return `
                  <button class="btn-secondary updateBtn" data-id="${data}" data-type="adviser">Edit</button>
                  <button class="btn-primary deleteBtn" data-id="${data}" data-type="adviser">Delete</button>
                `;
              }
            }
          ],
          columnDefs: [
            { targets: 0, visible: false, searchable: false } 
          ]
        });
      }

      function initStudentTable(){
        studentTable = $("#studentTable").DataTable({
          ajax:{
            url:'ajax.php',
            type:'post',
            data:{
              CALL: 9 // Get student users
            },
            dataType:'json',
            error: function(xhr, error, thrown) {
              console.log('Student DataTable AJAX Error:', error);
              console.log('Response:', xhr.responseText);
              openModal("ERROR", "Failed to load student users: " + error);
            }
          },
          responsive: true,
          scroll: '50vh',
          scrollCollapse: true, 
          paging: true,
          columns: [
            { data: "user_id" },
            { data: "user_name" },
            { 
              data: null,
              render: function(data, type, row) {
                return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;
              }
            },
            { data: "email" },
            { data: "contact_number" },
            { 
              data: 'user_id',
              render: function(data, type, row) {
                return `
                  <button class="btn-secondary updateBtn" data-id="${data}" data-type="student">Edit</button>
                  <button class="btn-primary deleteBtn" data-id="${data}" data-type="student">Delete</button>
                `;
              }
            }
          ],
          columnDefs: [
            { targets: 0, visible: false, searchable: false } 
          ]
        });
      }

      // Initialize all tables
      initAdminTable();
      initAdviserTable();
      initStudentTable();

      // Reload all tables
      function reloadAllTables(){
        adminTable.ajax.reload(null, false);
        adviserTable.ajax.reload(null, false);
        studentTable.ajax.reload(null, false);
      }

      // Save user function
      function saveUser(call, userData, userType) {
        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: call,
            DATA: userData
          },
          dataType: 'json',
          success: function(result) {
            openModal(result.status, result.msg);
            if(result.status == "SUCCESS") {
              clearForm(userType);
            }
          },
          complete: function() {
            reloadAllTables();
          }
        });
      }

      // Update and Delete button handlers
      $(document).on("click", ".updateBtn", function() {
        const userId = $(this).data('id');
        const userType = $(this).data('type');
        
        // Get user data for update
        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 13, // Get single user
            USER_ID: userId
          },
          dataType: 'json',
          success: function(result) {
            if(result.status === "SUCCESS") {
              const user = result.data;
              $("#updateUserId").val(user.user_id);
              $("#updateUserType").val(userType);
              $("#updateUsername").val(user.user_name);
              $("#updateFname").val(user.fname);
              $("#updateMname").val(user.mname);
              $("#updateLname").val(user.lname);
              $("#updateAuxname").val(user.auxname);
              $("#updateGender").val(user.gender);
              $("#updateBirthdate").val(user.birthdate);
              $("#updateContact").val(user.contact_number);
              $("#updateEmail").val(user.email);
              openUpdateModal();
            }
          }
        });
      });

      $(document).on("click", ".deleteBtn", function() {
        const userId = $(this).data('id');
        const userType = $(this).data('type');
        const userName = $(this).closest("tr").find("td").eq(1).text();
        const fullName = $(this).closest("tr").find("td").eq(2).text();
        
        $("#deleteUserId").val(userId);
        $("#deleteUserType").val(userType);
        $("#deleteUserInfo").val(`${userName} (${fullName})`);
        openDeleteModal();
      });

      // Confirm update
      $("#confirmUpdate").click(function() {
        const updateData = {
          user_id: $("#updateUserId").val(),
          user_name: $("#updateUsername").val(),
          password: $("#updatePassword").val(),
          fname: $("#updateFname").val(),
          mname: $("#updateMname").val(),
          lname: $("#updateLname").val(),
          auxname: $("#updateAuxname").val(),
          gender: $("#updateGender").val(),
          birthdate: $("#updateBirthdate").val(),
          contact_number: $("#updateContact").val(),
          email: $("#updateEmail").val()
        };

        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 11, // Update user
            DATA: updateData
          },
          dataType: 'json',
          success: function(result) {
            openModal(result.status, result.msg);
            if(result.status === "SUCCESS") {
              closeUpdateModal();
            }
          },
          complete: function() {
            reloadAllTables();
          }
        });
      });

      // Confirm delete
      $("#confirmDelete").click(function() {
        const deleteData = {
          user_id: $("#deleteUserId").val(),
          reason: $("#deleteReason").val()
        };

        if(!deleteData.reason.trim()) {
          openModal("ERROR", "Please provide a reason for deletion");
          return;
        }

        $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {
            CALL: 12, // Delete user
            DATA: deleteData
          },
          dataType: 'json',
          success: function(result) {
            openModal(result.status, result.msg);
            if(result.status === "SUCCESS") {
              closeDeleteModal();
              $("#deleteReason").val('');
            }
          },
          complete: function() {
            reloadAllTables();
          }
        });
      });

      // Save button handlers
      $("#saveAdmin").click(function() {
        const adminData = {
          user_name: $("#adminUsername").val(),
          password: $("#adminPassword").val(),
          fname: $("#adminFname").val(),
          mname: $("#adminMname").val(),
          lname: $("#adminLname").val(),
          auxname: $("#adminAuxname").val(),
          gender: $("#adminGender").val(),
          birthdate: $("#adminBirthdate").val(),
          contact_number: $("#adminContact").val(),
          email: $("#adminEmail").val(),
          user_type: 'admin'
        };

        // Basic validation
        if(!adminData.user_name.trim() || !adminData.password.trim() || !adminData.fname.trim() || !adminData.lname.trim() || !adminData.email.trim()) {
          openModal("ERROR", "Please fill in all required fields");
          return;
        }

        saveUser(10, adminData, 'admin'); // CALL 10 for creating users
      });

      $("#saveAdviser").click(function() {
        const adviserData = {
          user_name: $("#adviserUsername").val(),
          password: $("#adviserPassword").val(),
          fname: $("#adviserFname").val(),
          mname: $("#adviserMname").val(),
          lname: $("#adviserLname").val(),
          auxname: $("#adviserAuxname").val(),
          gender: $("#adviserGender").val(),
          birthdate: $("#adviserBirthdate").val(),
          contact_number: $("#adviserContact").val(),
          email: $("#adviserEmail").val(),
          user_type: 'adviser'
        };

        // Basic validation
        if(!adviserData.user_name.trim() || !adviserData.password.trim() || !adviserData.fname.trim() || !adviserData.lname.trim() || !adviserData.email.trim()) {
          openModal("ERROR", "Please fill in all required fields");
          return;
        }

        saveUser(10, adviserData, 'adviser');
      });

      $("#saveStudent").click(function() {
        const studentData = {
          user_name: $("#studentUsername").val(),
          password: $("#studentPassword").val(),
          fname: $("#studentFname").val(),
          mname: $("#studentMname").val(),
          lname: $("#studentLname").val(),
          auxname: $("#studentAuxname").val(),
          gender: $("#studentGender").val(),
          birthdate: $("#studentBirthdate").val(),
          contact_number: $("#studentContact").val(),
          email: $("#studentEmail").val(),
          user_type: 'student'
        };

        // Basic validation
        if(!studentData.user_name.trim() || !studentData.password.trim() || !studentData.fname.trim() || !studentData.lname.trim() || !studentData.email.trim()) {
          openModal("ERROR", "Please fill in all required fields");
          return;
        }

        saveUser(10, studentData, 'student');
      });
    });
  </script>
</body>
</html>