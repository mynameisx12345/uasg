<!doctype html><!doctype html><!doctype html>

<html lang="en">

<head><html lang="en"><html lang="en">

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0"><head><head>

  <title>Members Management - UASG</title>

  <link rel="stylesheet" href="../resources/style.css">  <meta charset="UTF-8">  <meta charset="UTF-8">

  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>

  <script src='../js/all.js'></script>  <meta name="viewport" content="width=device-width, initial-scale=1.0">  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <script src='../js/jquery.js'></script>

  <script src='../js/datatable.js'></script>  <title>User Management - UASG</title>  <title>User Management</title>

</head>

<body>  <link rel="stylesheet" href="../resources/style.css">  <link rel="stylesheet" href="../resources/style.css">

  <!-- HEADER -->

  <?php require_once("header.php");?>  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>

  

  <!-- MAIN -->  <script src='../js/all.js'></script>  <script src='../js/all.js'></script>

  <main class="main">

    <!-- SIDEBAR -->  <script src='../js/jquery.js'></script>  <script src='../js/jquery.js'></script>

    <?php require_once("sidebar.php");?>

  <script src='../js/datatable.js'></script>  <script src='../js/datatable.js'></script>

    <!-- CONTENT -->

    <section class="content"></head></head>

      <div class="card">

        <h2>Members Management</h2><body><body>



        <!-- TABS -->  <!-- HEADER -->  <!-- HEADER -->

        <div class="tabs">

          <button class="tab-link active" data-tab="subadmins">Subadmins</button>   <?php require_once("header.php");?>   <?php require_once("header.php");?>

          <button class="tab-link" data-tab="students">Student Members</button>

        </div>   <header class="topbar">   <header class="topbar">



        <!-- TAB CONTENT: SUBADMINS -->    <h1>Members Management</h1>    <h1>User Management</h1>

        <div class="tab-content active" id="subadmins">

          <div class="compact-form">    <div class="user-info">    <div class="user-info">

            <h3>Add New Subadmin</h3>

                  <span>Welcome, Admin</span>      <span>Welcome, Admin</span>

            <div class="form-columns">

              <!-- Left Column -->    </div>    </div>

              <div class="form-column">

                <!-- Account Information -->  </header>  </header>

                <div class="form-section">

                  <h4>Account Information</h4>  <!-- MAIN -->  <!-- MAIN -->

                  <div class="form-row">

                    <div class="form-group">  <main class="main">  <main class="main">

                      <label for="subadminUsername">Username</label>

                      <input type="text" id="subadminUsername" placeholder="Username" required>    <!-- SIDEBAR -->    <!-- SIDEBAR -->

                    </div>

                  </div>    <?php require_once("sidebar.php");?>    <?php require_once("sidebar.php");?>

                  <div class="form-row">

                    <div class="form-group">

                      <label for="subadminPassword">Password</label>

                      <input type="password" id="subadminPassword" placeholder="Password" required>    <!-- CONTENT -->    <!-- CONTENT -->

                    </div>

                  </div>    <section class="content">    <section class="content">

                </div>

      <div class="card">      <div class="card">

                <!-- Personal Information -->

                <div class="form-section">        <h2>Members Management</h2>        <h2>User Management</h2>

                  <h4>Personal Information</h4>

                  <div class="form-row">

                    <div class="form-group">

                      <label for="subadminFname">First Name</label>        <!-- TABS -->        <!-- TABS -->

                      <input type="text" id="subadminFname" placeholder="First Name" required>

                    </div>        <div class="tabs">        <div class="tabs">

                    <div class="form-group">

                      <label for="subadminMname">Middle Name</label>          <button class="tab-link active" data-tab="subadmins">Subadmins</button>          <button class="tab-link active" data-tab="subadmins">Subadmins</button>

                      <input type="text" id="subadminMname" placeholder="Middle Name">

                    </div>          <button class="tab-link" data-tab="students">Student Members</button>          <button class="tab-link" data-tab="students">Student Members</button>

                  </div>

                  <div class="form-row">        </div>        </div>

                    <div class="form-group">

                      <label for="subadminLname">Last Name</label>

                      <input type="text" id="subadminLname" placeholder="Last Name" required>

                    </div>        <!-- TAB CONTENT: SUBADMINS -->        <!-- TAB CONTENT: SUBADMINS -->

                    <div class="form-group form-group-small">

                      <label for="subadminAuxname">Suffix</label>        <div class="tab-content active" id="subadmins">        <div class="tab-content active" id="subadmins">

                      <input type="text" id="subadminAuxname" placeholder="Jr., Sr.">

                    </div>          <div class="compact-form">          <div class="compact-form">

                  </div>

                </div>            <h3>Add New Subadmin</h3>            <h3>Add New Administrator</h3>

              </div>

                        

              <!-- Right Column -->

              <div class="form-column">            <div class="form-columns">            <div class="form-columns">

                <!-- Personal Details -->

                <div class="form-section">              <!-- Left Column -->              <!-- Left Column -->

                  <h4>Personal Details</h4>

                  <div class="form-row">              <div class="form-column">              <div class="form-column">

                    <div class="form-group form-group-small">

                      <label for="subadminGender">Gender</label>                <!-- Account Information -->                <!-- Account Information -->

                      <select id="subadminGender" required>

                        <option value="">Select</option>                <div class="form-section">                <div class="form-section">

                        <option value="Male">Male</option>

                        <option value="Female">Female</option>                  <h4>Account Information</h4>                  <h4>Account Information</h4>

                      </select>

                    </div>                  <div class="form-row">                  <div class="form-row">

                    <div class="form-group">

                      <label for="subadminBirthdate">Birthdate</label>                    <div class="form-group">                    <div class="form-group">

                      <input type="date" id="subadminBirthdate" required>

                    </div>                      <label for="subadminUsername">Username</label>                      <label for="adminUsername">Username</label>

                  </div>

                </div>                      <input type="text" id="subadminUsername" placeholder="Username" required>                      <input type="text" id="adminUsername" placeholder="Username" required>



                <!-- Contact Information -->                    </div>                    </div>

                <div class="form-section">

                  <h4>Contact Information</h4>                  </div>                  </div>

                  <div class="form-row">

                    <div class="form-group">                  <div class="form-row">                  <div class="form-row">

                      <label for="subadminContact">Contact Number</label>

                      <input type="text" id="subadminContact" placeholder="Contact Number">                    <div class="form-group">                    <div class="form-group">

                    </div>

                  </div>                      <label for="subadminPassword">Password</label>                      <label for="adminPassword">Password</label>

                  <div class="form-row">

                    <div class="form-group">                      <input type="password" id="subadminPassword" placeholder="Password" required>                      <input type="password" id="adminPassword" placeholder="Password" required>

                      <label for="subadminEmail">Email</label>

                      <input type="email" id="subadminEmail" placeholder="Email Address" required>                    </div>                    </div>

                    </div>

                  </div>                  </div>                  </div>

                </div>

              </div>                </div>                </div>

            </div>



            <div class="form-actions">

              <button type="button" class="btn-primary" id="saveSubadmin">Save Subadmin</button>                <!-- Personal Information -->                <!-- Personal Information -->

              <button type="button" class="btn-secondary" onclick="clearForm('subadmin')">Clear</button>

            </div>                <div class="form-section">                <div class="form-section">

          </div>

                            <h4>Personal Information</h4>                  <h4>Personal Information</h4>

          <div class="table-container">

            <table class='data-table' id='subadminTable'>                  <div class="form-row">                  <div class="form-row">

              <thead>

                <tr>                    <div class="form-group">                    <div class="form-group">

                  <th>ID</th>

                  <th>Username</th>                      <label for="subadminFname">First Name</label>                      <label for="adminFname">First Name</label>

                  <th>Full Name</th>

                  <th>Email</th>                      <input type="text" id="subadminFname" placeholder="First Name" required>                      <input type="text" id="adminFname" placeholder="First Name" required>

                  <th>Contact</th>

                  <th>Actions</th>                    </div>                    </div>

                </tr>

              </thead>                    <div class="form-group">                    <div class="form-group">

              <tbody></tbody>

            </table>                      <label for="subadminMname">Middle Name</label>                      <label for="adminMname">Middle Name</label>

          </div>

        </div>                      <input type="text" id="subadminMname" placeholder="Middle Name">                      <input type="text" id="adminMname" placeholder="Middle Name">



        <!-- TAB CONTENT: STUDENTS -->                    </div>                    </div>

        <div class="tab-content" id="students">

          <div class="compact-form">                  </div>                  </div>

            <h3>Add New Student Member</h3>

                              <div class="form-row">                  <div class="form-row">

            <div class="form-columns">

              <!-- Left Column -->                    <div class="form-group">                    <div class="form-group">

              <div class="form-column">

                <!-- Account Information -->                      <label for="subadminLname">Last Name</label>                      <label for="adminLname">Last Name</label>

                <div class="form-section">

                  <h4>Account Information</h4>                      <input type="text" id="subadminLname" placeholder="Last Name" required>                      <input type="text" id="adminLname" placeholder="Last Name" required>

                  <div class="form-row">

                    <div class="form-group">                    </div>                    </div>

                      <label for="studentUsername">Username</label>

                      <input type="text" id="studentUsername" placeholder="Username" required>                    <div class="form-group form-group-small">                    <div class="form-group form-group-small">

                    </div>

                  </div>                      <label for="subadminAuxname">Suffix</label>                      <label for="adminAuxname">Suffix</label>

                  <div class="form-row">

                    <div class="form-group">                      <input type="text" id="subadminAuxname" placeholder="Jr., Sr.">                      <input type="text" id="adminAuxname" placeholder="Jr., Sr.">

                      <label for="studentPassword">Password</label>

                      <input type="password" id="studentPassword" placeholder="Password" required>                    </div>                    </div>

                    </div>

                  </div>                  </div>                  </div>

                </div>

                </div>                </div>

                <!-- Personal Information -->

                <div class="form-section">              </div>              </div>

                  <h4>Personal Information</h4>

                  <div class="form-row">

                    <div class="form-group">

                      <label for="studentFname">First Name</label>              <!-- Right Column -->              <!-- Right Column -->

                      <input type="text" id="studentFname" placeholder="First Name" required>

                    </div>              <div class="form-column">              <div class="form-column">

                    <div class="form-group">

                      <label for="studentMname">Middle Name</label>                <!-- Personal Details -->                <!-- Personal Details -->

                      <input type="text" id="studentMname" placeholder="Middle Name">

                    </div>                <div class="form-section">                <div class="form-section">

                  </div>

                  <div class="form-row">                  <h4>Personal Details</h4>                  <h4>Personal Details</h4>

                    <div class="form-group">

                      <label for="studentLname">Last Name</label>                  <div class="form-row">                  <div class="form-row">

                      <input type="text" id="studentLname" placeholder="Last Name" required>

                    </div>                    <div class="form-group form-group-small">                    <div class="form-group form-group-small">

                    <div class="form-group form-group-small">

                      <label for="studentAuxname">Suffix</label>                      <label for="subadminGender">Gender</label>                      <label for="adminGender">Gender</label>

                      <input type="text" id="studentAuxname" placeholder="Jr., Sr.">

                    </div>                      <select id="subadminGender" required>                      <select id="adminGender" required>

                  </div>

                </div>                        <option value="">Select</option>                        <option value="">Select</option>

              </div>

                        <option value="Male">Male</option>                        <option value="Male">Male</option>

              <!-- Right Column -->

              <div class="form-column">                        <option value="Female">Female</option>                        <option value="Female">Female</option>

                <!-- Personal Details -->

                <div class="form-section">                      </select>                      </select>

                  <h4>Personal Details</h4>

                  <div class="form-row">                    </div>                    </div>

                    <div class="form-group form-group-small">

                      <label for="studentGender">Gender</label>                    <div class="form-group">                    <div class="form-group">

                      <select id="studentGender" required>

                        <option value="">Select</option>                      <label for="subadminBirthdate">Birthdate</label>                      <label for="adminBirthdate">Birthdate</label>

                        <option value="Male">Male</option>

                        <option value="Female">Female</option>                      <input type="date" id="subadminBirthdate" required>                      <input type="date" id="adminBirthdate" required>

                      </select>

                    </div>                    </div>                    </div>

                    <div class="form-group">

                      <label for="studentBirthdate">Birthdate</label>                  </div>                  </div>

                      <input type="date" id="studentBirthdate" required>

                    </div>                </div>                </div>

                  </div>

                </div>



                <!-- Contact Information -->                <!-- Contact Information -->                <!-- Contact Information -->

                <div class="form-section">

                  <h4>Contact Information</h4>                <div class="form-section">                <div class="form-section">

                  <div class="form-row">

                    <div class="form-group">                  <h4>Contact Information</h4>                  <h4>Contact Information</h4>

                      <label for="studentContact">Contact Number</label>

                      <input type="text" id="studentContact" placeholder="Contact Number">                  <div class="form-row">                  <div class="form-row">

                    </div>

                  </div>                    <div class="form-group">                    <div class="form-group">

                  <div class="form-row">

                    <div class="form-group">                      <label for="subadminContact">Contact Number</label>                      <label for="adminContact">Contact Number</label>

                      <label for="studentEmail">Email</label>

                      <input type="email" id="studentEmail" placeholder="Email Address" required>                      <input type="text" id="subadminContact" placeholder="Contact Number">                      <input type="text" id="adminContact" placeholder="Contact Number">

                    </div>

                  </div>                    </div>                    </div>

                </div>

              </div>                  </div>                  </div>

            </div>

                  <div class="form-row">                  <div class="form-row">

            <div class="form-actions">

              <button type="button" class="btn-primary" id="saveStudent">Save Student Member</button>                    <div class="form-group">                    <div class="form-group">

              <button type="button" class="btn-secondary" onclick="clearForm('student')">Clear</button>

            </div>                      <label for="subadminEmail">Email</label>                      <label for="adminEmail">Email</label>

          </div>

                                <input type="email" id="subadminEmail" placeholder="Email Address" required>                      <input type="email" id="adminEmail" placeholder="Email Address" required>

          <div class="table-container">

            <table class='data-table' id='studentTable'>                    </div>                    </div>

              <thead>

                <tr>                  </div>                  </div>

                  <th>ID</th>

                  <th>Username</th>                </div>                </div>

                  <th>Full Name</th>

                  <th>Email</th>              </div>              </div>

                  <th>Contact</th>

                  <th>Actions</th>            </div>            </div>

                </tr>

              </thead>

              <tbody></tbody>

            </table>            <div class="form-actions">            <div class="form-actions">

          </div>

        </div>              <button type="button" class="btn-primary" id="saveSubadmin">Save Subadmin</button>              <button type="button" class="btn-primary" id="saveAdmin">Save Administrator</button>

      </div>

    </section>              <button type="button" class="btn-secondary" onclick="clearForm('subadmin')">Clear</button>              <button type="button" class="btn-secondary" onclick="clearForm('admin')">Clear</button>



    <!-- UPDATE MODAL -->            </div>            </div>

    <div id="updateUserModal" class="modal">

      <div class="modal-content">          </div>          </div>

        <span class="modal-close" onclick="closeUpdateModal()">&times;</span>

        <h2>Update User Information</h2>                    

        

        <div class="compact-form">          <div class="table-container">          <div class="table-container">

          <input type="hidden" id="updateUserId">

          <input type="hidden" id="updateUserType">            <table class='data-table' id='subadminTable'>            <table class='data-table' id='adminTable'>

          

          <div class="form-columns">              <thead>              <thead>

            <!-- Left Column -->

            <div class="form-column">                <tr>                <tr>

              <!-- Account Information -->

              <div class="form-section">                  <th>ID</th>                  <th>ID</th>

                <h4>Account Information</h4>

                <div class="form-row">                  <th>Username</th>                  <th>Username</th>

                  <div class="form-group">

                    <label for="updateUsername">Username</label>                  <th>Full Name</th>                  <th>Full Name</th>

                    <input type="text" id="updateUsername" required>

                  </div>                  <th>Email</th>                  <th>Email</th>

                </div>

                <div class="form-row">                  <th>Contact</th>                  <th>Contact</th>

                  <div class="form-group">

                    <label for="updatePassword">Password</label>                  <th>Actions</th>                  <th>Actions</th>

                    <input type="password" id="updatePassword" placeholder="Leave empty to keep current">

                  </div>                </tr>                </tr>

                </div>

              </div>              </thead>              </thead>

              

              <!-- Personal Information -->              <tbody></tbody>              <tbody></tbody>

              <div class="form-section">

                <h4>Personal Information</h4>            </table>            </table>

                <div class="form-row">

                  <div class="form-group">          </div>          </div>

                    <label for="updateFname">First Name</label>

                    <input type="text" id="updateFname" required>        </div>        </div>

                  </div>

                  <div class="form-group">

                    <label for="updateMname">Middle Name</label>

                    <input type="text" id="updateMname">        <!-- TAB CONTENT: STUDENTS -->        <!-- TAB CONTENT: ADVISERS -->

                  </div>

                </div>        <div class="tab-content" id="students">        <div class="tab-content" id="advisers">

                <div class="form-row">

                  <div class="form-group">          <div class="compact-form">          <div class="compact-form">

                    <label for="updateLname">Last Name</label>

                    <input type="text" id="updateLname" required>            <h3>Add New Student Member</h3>            <h3>Add New UASG Adviser</h3>

                  </div>

                  <div class="form-group form-group-small">                        

                    <label for="updateAuxname">Suffix</label>

                    <input type="text" id="updateAuxname">            <div class="form-columns">            <div class="form-columns">

                  </div>

                </div>              <!-- Left Column -->              <!-- Left Column -->

              </div>

            </div>              <div class="form-column">              <div class="form-column">

            

            <!-- Right Column -->                <!-- Account Information -->                <!-- Account Information -->

            <div class="form-column">

              <!-- Personal Details -->                <div class="form-section">                <div class="form-section">

              <div class="form-section">

                <h4>Personal Details</h4>                  <h4>Account Information</h4>                  <h4>Account Information</h4>

                <div class="form-row">

                  <div class="form-group form-group-small">                  <div class="form-row">                  <div class="form-row">

                    <label for="updateGender">Gender</label>

                    <select id="updateGender" required>                    <div class="form-group">                    <div class="form-group">

                      <option value="">Select</option>

                      <option value="Male">Male</option>                      <label for="studentUsername">Username</label>                      <label for="adviserUsername">Username</label>

                      <option value="Female">Female</option>

                    </select>                      <input type="text" id="studentUsername" placeholder="Username" required>                      <input type="text" id="adviserUsername" placeholder="Username" required>

                  </div>

                  <div class="form-group">                    </div>                    </div>

                    <label for="updateBirthdate">Birthdate</label>

                    <input type="date" id="updateBirthdate" required>                  </div>                  </div>

                  </div>

                </div>                  <div class="form-row">                  <div class="form-row">

              </div>

                                  <div class="form-group">                    <div class="form-group">

              <!-- Contact Information -->

              <div class="form-section">                      <label for="studentPassword">Password</label>                      <label for="adviserPassword">Password</label>

                <h4>Contact Information</h4>

                <div class="form-row">                      <input type="password" id="studentPassword" placeholder="Password" required>                      <input type="password" id="adviserPassword" placeholder="Password" required>

                  <div class="form-group">

                    <label for="updateContact">Contact Number</label>                    </div>                    </div>

                    <input type="text" id="updateContact">

                  </div>                  </div>                  </div>

                </div>

                <div class="form-row">                </div>                </div>

                  <div class="form-group">

                    <label for="updateEmail">Email</label>

                    <input type="email" id="updateEmail" required>

                  </div>                <!-- Personal Information -->                <!-- Personal Information -->

                </div>

              </div>                <div class="form-section">                <div class="form-section">

            </div>

          </div>                  <h4>Personal Information</h4>                  <h4>Personal Information</h4>

          

          <div class="form-actions">                  <div class="form-row">                  <div class="form-row">

            <button type="button" class="btn-primary" id="confirmUpdate">Update User</button>

            <button type="button" class="btn-secondary" onclick="closeUpdateModal()">Cancel</button>                    <div class="form-group">                    <div class="form-group">

          </div>

        </div>                      <label for="studentFname">First Name</label>                      <label for="adviserFname">First Name</label>

      </div>

    </div>                      <input type="text" id="studentFname" placeholder="First Name" required>                      <input type="text" id="adviserFname" placeholder="First Name" required>



    <!-- DELETE MODAL -->                    </div>                    </div>

    <div id="deleteUserModal" class="modal">

      <div class="modal-content modal-content-small">                    <div class="form-group">                    <div class="form-group">

        <span class="modal-close" onclick="closeDeleteModal()">&times;</span>

        <h2>Delete User Confirmation</h2>                      <label for="studentMname">Middle Name</label>                      <label for="adviserMname">Middle Name</label>

        

        <div class="compact-form">                      <input type="text" id="studentMname" placeholder="Middle Name">                      <input type="text" id="adviserMname" placeholder="Middle Name">

          <input type="hidden" id="deleteUserId">

          <input type="hidden" id="deleteUserType">                    </div>                    </div>

          

          <div class="form-group">                  </div>                  </div>

            <label for="deleteUserInfo">User to Delete</label>

            <input type='text' id='deleteUserInfo' readonly>                  <div class="form-row">                  <div class="form-row">

          </div>

                              <div class="form-group">                    <div class="form-group">

          <div class="form-group">

            <label for="deleteReason">Reason for Deletion</label>                      <label for="studentLname">Last Name</label>                      <label for="adviserLname">Last Name</label>

            <textarea id='deleteReason' name='deleteReason' rows='4' placeholder="Enter reason for deletion..." required></textarea>

          </div>                      <input type="text" id="studentLname" placeholder="Last Name" required>                      <input type="text" id="adviserLname" placeholder="Last Name" required>

          

          <div class="form-actions">                    </div>                    </div>

            <button type="button" class="btn-primary" id="confirmDelete">Confirm Delete</button>

            <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>                    <div class="form-group form-group-small">                    <div class="form-group form-group-small">

          </div>

        </div>                      <label for="studentAuxname">Suffix</label>                      <label for="adviserAuxname">Suffix</label>

      </div>

    </div>                      <input type="text" id="studentAuxname" placeholder="Jr., Sr.">                      <input type="text" id="adviserAuxname" placeholder="Jr., Sr.">

  </main>

                    </div>                    </div>

  <?php require_once("modal.php");?>

                  </div>                  </div>

  <script>

    // Modal functions                </div>                </div>

    function openUpdateModal() {

      document.getElementById("updateUserModal").style.display = "flex";              </div>              </div>

    }

    

    function closeUpdateModal() {

      document.getElementById("updateUserModal").style.display = "none";              <!-- Right Column -->              <!-- Right Column -->

    }

              <div class="form-column">              <div class="form-column">

    function openDeleteModal(){

      document.getElementById("deleteUserModal").style.display = "flex";                <!-- Personal Details -->                <!-- Personal Details -->

    }

                <div class="form-section">                <div class="form-section">

    function closeDeleteModal(){

      document.getElementById("deleteUserModal").style.display = "none";                  <h4>Personal Details</h4>                  <h4>Personal Details</h4>

    }

                  <div class="form-row">                  <div class="form-row">

    // Clear form function

    function clearForm(type) {                    <div class="form-group form-group-small">                    <div class="form-group form-group-small">

      const fields = ['Username', 'Password', 'Fname', 'Mname', 'Lname', 'Auxname', 'Gender', 'Birthdate', 'Contact', 'Email'];

      fields.forEach(field => {                      <label for="studentGender">Gender</label>                      <label for="adviserGender">Gender</label>

        const element = document.getElementById(type + field);

        if (element) {                      <select id="studentGender" required>                      <select id="adviserGender" required>

          element.value = '';

        }                        <option value="">Select</option>                        <option value="">Select</option>

      });

    }                        <option value="Male">Male</option>                        <option value="Male">Male</option>



    // Tab switcher                        <option value="Female">Female</option>                        <option value="Female">Female</option>

    const tabLinks = document.querySelectorAll(".tab-link");

    const tabContents = document.querySelectorAll(".tab-content");                      </select>                      </select>



    tabLinks.forEach(link => {                    </div>                    </div>

      link.addEventListener("click", () => {

        // Remove active class from all tabs and contents                    <div class="form-group">                    <div class="form-group">

        tabLinks.forEach(l => l.classList.remove("active"));

        tabContents.forEach(c => c.classList.remove("active"));                      <label for="studentBirthdate">Birthdate</label>                      <label for="adviserBirthdate">Birthdate</label>



        // Add active class to clicked tab and corresponding content                      <input type="date" id="studentBirthdate" required>                      <input type="date" id="adviserBirthdate" required>

        link.classList.add("active");

        const targetTab = document.getElementById(link.dataset.tab);                    </div>                    </div>

        if (targetTab) {

          targetTab.classList.add("active");                  </div>                  </div>

        }

      });                </div>                </div>

    });

  </script>



  <script>                <!-- Contact Information -->                <!-- Contact Information -->

    $(document).ready(function(){

      let subadminTable;                <div class="form-section">                <div class="form-section">

      let studentTable;

                  <h4>Contact Information</h4>                  <h4>Contact Information</h4>

      // DataTable initialization for Subadmins

      function initSubadminTable(){                  <div class="form-row">                  <div class="form-row">

        subadminTable = $("#subadminTable").DataTable({

          ajax:{                    <div class="form-group">                    <div class="form-group">

            url:'ajax.php',

            type:'post',                      <label for="studentContact">Contact Number</label>                      <label for="adviserContact">Contact Number</label>

            data:{

              CALL: 8 // Get subadmin users (previously adviser)                      <input type="text" id="studentContact" placeholder="Contact Number">                      <input type="text" id="adviserContact" placeholder="Contact Number">

            },

            dataSrc: 'data',                    </div>                    </div>

            dataType:'json',

            error: function(xhr, error, thrown) {                  </div>                  </div>

              console.log('Subadmin DataTable AJAX Error:', error);

              console.log('Response:', xhr.responseText);                  <div class="form-row">                  <div class="form-row">

              openModal("ERROR", "Failed to load subadmin users: " + error);

            }                    <div class="form-group">                    <div class="form-group">

          },

          responsive: true,                      <label for="studentEmail">Email</label>                      <label for="adviserEmail">Email</label>

          scroll: '50vh',

          scrollCollapse: true,                       <input type="email" id="studentEmail" placeholder="Email Address" required>                      <input type="email" id="adviserEmail" placeholder="Email Address" required>

          paging: true,

          columns: [                    </div>                    </div>

            { data: "user_id" },

            { data: "user_name" },                  </div>                  </div>

            { 

              data: null,                </div>                </div>

              render: function(data, type, row) {

                return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;              </div>              </div>

              }

            },            </div>            </div>

            { data: "email" },

            { data: "contact_number" },

            { 

              data: 'user_id',            <div class="form-actions">            <div class="form-actions">

              render: function(data, type, row) {

                return `              <button type="button" class="btn-primary" id="saveStudent">Save Student Member</button>              <button type="button" class="btn-primary" id="saveAdviser">Save Adviser</button>

                  <button class="btn-secondary updateBtn" data-id="${data}" data-type="subadmin">Edit</button>

                  <button class="btn-primary deleteBtn" data-id="${data}" data-type="subadmin">Delete</button>              <button type="button" class="btn-secondary" onclick="clearForm('student')">Clear</button>              <button type="button" class="btn-secondary" onclick="clearForm('adviser')">Clear</button>

                `;

              }            </div>            </div>

            }

          ],          </div>          </div>

          columnDefs: [

            { targets: 0, visible: false, searchable: false }                     

          ]

        });          <div class="table-container">          <div class="table-container">

      }

            <table class='data-table' id='studentTable'>            <table class='data-table' id='adviserTable'>

      // DataTable initialization for Students

      function initStudentTable(){              <thead>              <thead>

        studentTable = $("#studentTable").DataTable({

          ajax:{                <tr>                <tr>

            url:'ajax.php',

            type:'post',                  <th>ID</th>                  <th>ID</th>

            data:{

              CALL: 9 // Get student users                  <th>Username</th>                  <th>Username</th>

            },

            dataSrc: 'data',                  <th>Full Name</th>                  <th>Full Name</th>

            dataType:'json',

            error: function(xhr, error, thrown) {                  <th>Email</th>                  <th>Email</th>

              console.log('Student DataTable AJAX Error:', error);

              console.log('Response:', xhr.responseText);                  <th>Contact</th>                  <th>Contact</th>

              openModal("ERROR", "Failed to load student users: " + error);

            }                  <th>Actions</th>                  <th>Actions</th>

          },

          responsive: true,                </tr>                </tr>

          scroll: '50vh',

          scrollCollapse: true,               </thead>              </thead>

          paging: true,

          columns: [              <tbody></tbody>              <tbody></tbody>

            { data: "user_id" },

            { data: "user_name" },            </table>            </table>

            { 

              data: null,          </div>          </div>

              render: function(data, type, row) {

                return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;        </div>        </div>

              }

            },      </div>

            { data: "email" },

            { data: "contact_number" },    </section>        <!-- TAB CONTENT: STUDENTS -->

            { 

              data: 'user_id',        <div class="tab-content" id="students">

              render: function(data, type, row) {

                return `    <!-- UPDATE MODAL -->          <div class="compact-form">

                  <button class="btn-secondary updateBtn" data-id="${data}" data-type="student">Edit</button>

                  <button class="btn-primary deleteBtn" data-id="${data}" data-type="student">Delete</button>    <div id="updateUserModal" class="modal">            <h3>Add New Student Member</h3>

                `;

              }      <div class="modal-content">            

            }

          ],        <span class="modal-close" onclick="closeUpdateModal()">&times;</span>            <div class="form-columns">

          columnDefs: [

            { targets: 0, visible: false, searchable: false }         <h2>Update User Information</h2>              <!-- Left Column -->

          ]

        });                      <div class="form-column">

      }

        <div class="compact-form">                <!-- Account Information -->

      // Initialize all tables

      initSubadminTable();          <input type="hidden" id="updateUserId">                <div class="form-section">

      initStudentTable();

          <input type="hidden" id="updateUserType">                  <h4>Account Information</h4>

      // Reload all tables

      function reloadAllTables(){                            <div class="form-row">

        subadminTable.ajax.reload(null, false);

        studentTable.ajax.reload(null, false);          <div class="form-columns">                    <div class="form-group">

      }

            <!-- Left Column -->                      <label for="studentUsername">Username</label>

      // Save user function

      function saveUser(call, userData, userType) {            <div class="form-column">                      <input type="text" id="studentUsername" placeholder="Username" required>

        $.ajax({

          url: 'ajax.php',              <!-- Account Information -->                    </div>

          type: 'post',

          data: {              <div class="form-section">                  </div>

            CALL: call,

            DATA: userData                <h4>Account Information</h4>                  <div class="form-row">

          },

          dataType: 'json',                <div class="form-row">                    <div class="form-group">

          success: function(result) {

            openModal(result.status, result.msg);                  <div class="form-group">                      <label for="studentPassword">Password</label>

            if(result.status == "SUCCESS") {

              clearForm(userType);                    <label for="updateUsername">Username</label>                      <input type="password" id="studentPassword" placeholder="Password" required>

            }

          },                    <input type="text" id="updateUsername" required>                    </div>

          complete: function() {

            reloadAllTables();                  </div>                  </div>

          }

        });                </div>                </div>

      }

                <div class="form-row">

      // Update and Delete button handlers

      $(document).on("click", ".updateBtn", function() {                  <div class="form-group">                <!-- Personal Information -->

        const userId = $(this).data('id');

        const userType = $(this).data('type');                    <label for="updatePassword">Password</label>                <div class="form-section">

        

        // Get user data for update                    <input type="password" id="updatePassword" placeholder="Leave empty to keep current">                  <h4>Personal Information</h4>

        $.ajax({

          url: 'ajax.php',                  </div>                  <div class="form-row">

          type: 'post',

          data: {                </div>                    <div class="form-group">

            CALL: 13, // Get single user

            USER_ID: userId              </div>                      <label for="studentFname">First Name</label>

          },

          dataType: 'json',                                    <input type="text" id="studentFname" placeholder="First Name" required>

          success: function(result) {

            if(result.status === "SUCCESS") {              <!-- Personal Information -->                    </div>

              const user = result.data;

              $("#updateUserId").val(user.user_id);              <div class="form-section">                    <div class="form-group">

              $("#updateUserType").val(userType);

              $("#updateUsername").val(user.user_name);                <h4>Personal Information</h4>                      <label for="studentMname">Middle Name</label>

              $("#updateFname").val(user.fname);

              $("#updateMname").val(user.mname);                <div class="form-row">                      <input type="text" id="studentMname" placeholder="Middle Name">

              $("#updateLname").val(user.lname);

              $("#updateAuxname").val(user.auxname);                  <div class="form-group">                    </div>

              $("#updateGender").val(user.gender);

              $("#updateBirthdate").val(user.birthdate);                    <label for="updateFname">First Name</label>                  </div>

              $("#updateContact").val(user.contact_number);

              $("#updateEmail").val(user.email);                    <input type="text" id="updateFname" required>                  <div class="form-row">

              openUpdateModal();

            }                  </div>                    <div class="form-group">

          }

        });                  <div class="form-group">                      <label for="studentLname">Last Name</label>

      });

                    <label for="updateMname">Middle Name</label>                      <input type="text" id="studentLname" placeholder="Last Name" required>

      $(document).on("click", ".deleteBtn", function() {

        const userId = $(this).data('id');                    <input type="text" id="updateMname">                    </div>

        const userType = $(this).data('type');

        const userName = $(this).closest("tr").find("td").eq(1).text();                  </div>                    <div class="form-group form-group-small">

        const fullName = $(this).closest("tr").find("td").eq(2).text();

                        </div>                      <label for="studentAuxname">Suffix</label>

        $("#deleteUserId").val(userId);

        $("#deleteUserType").val(userType);                <div class="form-row">                      <input type="text" id="studentAuxname" placeholder="Jr., Sr.">

        $("#deleteUserInfo").val(`${userName} (${fullName})`);

        openDeleteModal();                  <div class="form-group">                    </div>

      });

                    <label for="updateLname">Last Name</label>                  </div>

      // Confirm update

      $("#confirmUpdate").click(function() {                    <input type="text" id="updateLname" required>                </div>

        const updateData = {

          user_id: $("#updateUserId").val(),                  </div>              </div>

          user_name: $("#updateUsername").val(),

          password: $("#updatePassword").val(),                  <div class="form-group form-group-small">

          fname: $("#updateFname").val(),

          mname: $("#updateMname").val(),                    <label for="updateAuxname">Suffix</label>              <!-- Right Column -->

          lname: $("#updateLname").val(),

          auxname: $("#updateAuxname").val(),                    <input type="text" id="updateAuxname">              <div class="form-column">

          gender: $("#updateGender").val(),

          birthdate: $("#updateBirthdate").val(),                  </div>                <!-- Personal Details -->

          contact_number: $("#updateContact").val(),

          email: $("#updateEmail").val()                </div>                <div class="form-section">

        };

              </div>                  <h4>Personal Details</h4>

        $.ajax({

          url: 'ajax.php',            </div>                  <div class="form-row">

          type: 'post',

          data: {                                <div class="form-group form-group-small">

            CALL: 11, // Update user

            DATA: updateData            <!-- Right Column -->                      <label for="studentGender">Gender</label>

          },

          dataType: 'json',            <div class="form-column">                      <select id="studentGender" required>

          success: function(result) {

            openModal(result.status, result.msg);              <!-- Personal Details -->                        <option value="">Select</option>

            if(result.status === "SUCCESS") {

              closeUpdateModal();              <div class="form-section">                        <option value="Male">Male</option>

            }

          },                <h4>Personal Details</h4>                        <option value="Female">Female</option>

          complete: function() {

            reloadAllTables();                <div class="form-row">                      </select>

          }

        });                  <div class="form-group form-group-small">                    </div>

      });

                    <label for="updateGender">Gender</label>                    <div class="form-group">

      // Confirm delete

      $("#confirmDelete").click(function() {                    <select id="updateGender" required>                      <label for="studentBirthdate">Birthdate</label>

        const deleteData = {

          user_id: $("#deleteUserId").val(),                      <option value="">Select</option>                      <input type="date" id="studentBirthdate" required>

          reason: $("#deleteReason").val()

        };                      <option value="Male">Male</option>                    </div>



        if(!deleteData.reason.trim()) {                      <option value="Female">Female</option>                  </div>

          openModal("ERROR", "Please provide a reason for deletion");

          return;                    </select>                </div>

        }

                  </div>

        $.ajax({

          url: 'ajax.php',                  <div class="form-group">                <!-- Contact Information -->

          type: 'post',

          data: {                    <label for="updateBirthdate">Birthdate</label>                <div class="form-section">

            CALL: 12, // Delete user

            DATA: deleteData                    <input type="date" id="updateBirthdate" required>                  <h4>Contact Information</h4>

          },

          dataType: 'json',                  </div>                  <div class="form-row">

          success: function(result) {

            openModal(result.status, result.msg);                </div>                    <div class="form-group">

            if(result.status === "SUCCESS") {

              closeDeleteModal();              </div>                      <label for="studentContact">Contact Number</label>

              $("#deleteReason").val('');

            }                                    <input type="text" id="studentContact" placeholder="Contact Number">

          },

          complete: function() {              <!-- Contact Information -->                    </div>

            reloadAllTables();

          }              <div class="form-section">                  </div>

        });

      });                <h4>Contact Information</h4>                  <div class="form-row">



      // Save Subadmin button handler                <div class="form-row">                    <div class="form-group">

      $("#saveSubadmin").click(function() {

        const subadminData = {                  <div class="form-group">                      <label for="studentEmail">Email</label>

          user_name: $("#subadminUsername").val(),

          password: $("#subadminPassword").val(),                    <label for="updateContact">Contact Number</label>                      <input type="email" id="studentEmail" placeholder="Email Address" required>

          fname: $("#subadminFname").val(),

          mname: $("#subadminMname").val(),                    <input type="text" id="updateContact">                    </div>

          lname: $("#subadminLname").val(),

          auxname: $("#subadminAuxname").val(),                  </div>                  </div>

          gender: $("#subadminGender").val(),

          birthdate: $("#subadminBirthdate").val(),                </div>                </div>

          contact_number: $("#subadminContact").val(),

          email: $("#subadminEmail").val(),                <div class="form-row">              </div>

          user_type: 'adviser' // Backend still uses 'adviser' for subadmins

        };                  <div class="form-group">            </div>



        // Basic validation                    <label for="updateEmail">Email</label>

        if(!subadminData.user_name.trim() || !subadminData.password.trim() || !subadminData.fname.trim() || !subadminData.lname.trim() || !subadminData.email.trim()) {

          openModal("ERROR", "Please fill in all required fields");                    <input type="email" id="updateEmail" required>            <div class="form-actions">

          return;

        }                  </div>              <button type="button" class="btn-primary" id="saveStudent">Save Student Member</button>



        saveUser(10, subadminData, 'subadmin'); // CALL 10 for creating users                </div>              <button type="button" class="btn-secondary" onclick="clearForm('student')">Clear</button>

      });

              </div>            </div>

      // Save Student button handler

      $("#saveStudent").click(function() {            </div>          </div>

        const studentData = {

          user_name: $("#studentUsername").val(),          </div>          

          password: $("#studentPassword").val(),

          fname: $("#studentFname").val(),                    <div class="table-container">

          mname: $("#studentMname").val(),

          lname: $("#studentLname").val(),          <div class="form-actions">            <table class='data-table' id='studentTable'>

          auxname: $("#studentAuxname").val(),

          gender: $("#studentGender").val(),            <button type="button" class="btn-primary" id="confirmUpdate">Update User</button>              <thead>

          birthdate: $("#studentBirthdate").val(),

          contact_number: $("#studentContact").val(),            <button type="button" class="btn-secondary" onclick="closeUpdateModal()">Cancel</button>                <tr>

          email: $("#studentEmail").val(),

          user_type: 'student'          </div>                  <th>ID</th>

        };

        </div>                  <th>Username</th>

        // Basic validation

        if(!studentData.user_name.trim() || !studentData.password.trim() || !studentData.fname.trim() || !studentData.lname.trim() || !studentData.email.trim()) {      </div>                  <th>Full Name</th>

          openModal("ERROR", "Please fill in all required fields");

          return;    </div>                  <th>Email</th>

        }

                  <th>Contact</th>

        saveUser(10, studentData, 'student');

      });    <!-- DELETE MODAL -->                  <th>Actions</th>

    });

  </script>    <div id="deleteUserModal" class="modal">                </tr>

</body>

</html>      <div class="modal-content modal-content-small">              </thead>


        <span class="modal-close" onclick="closeDeleteModal()">&times;</span>              <tbody></tbody>

        <h2>Delete User Confirmation</h2>            </table>

                  </div>

        <div class="compact-form">        </div>

          <input type="hidden" id="deleteUserId">      </div>

          <input type="hidden" id="deleteUserType">    </section>

          

          <div class="form-group">    <!-- UPDATE MODAL -->

            <label for="deleteUserInfo">User to Delete</label>    <div id="updateUserModal" class="modal">

            <input type='text' id='deleteUserInfo' readonly>      <div class="modal-content">

          </div>        <span class="modal-close" onclick="closeUpdateModal()">&times;</span>

                  <h2>Update User Information</h2>

          <div class="form-group">        

            <label for="deleteReason">Reason for Deletion</label>        <div class="compact-form">

            <textarea id='deleteReason' name='deleteReason' rows='4' placeholder="Enter reason for deletion..." required></textarea>          <input type="hidden" id="updateUserId">

          </div>          <input type="hidden" id="updateUserType">

                    

          <div class="form-actions">          <div class="form-columns">

            <button type="button" class="btn-primary" id="confirmDelete">Confirm Delete</button>            <!-- Left Column -->

            <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>            <div class="form-column">

          </div>              <!-- Account Information -->

        </div>              <div class="form-section">

      </div>                <h4>Account Information</h4>

    </div>                <div class="form-row">

  </main>                  <div class="form-group">

                    <label for="updateUsername">Username</label>

  <script>                    <input type="text" id="updateUsername" required>

    // Modal functions                  </div>

    function openUpdateModal() {                </div>

      document.getElementById("updateUserModal").style.display = "flex";                <div class="form-row">

    }                  <div class="form-group">

                        <label for="updatePassword">Password</label>

    function closeUpdateModal() {                    <input type="password" id="updatePassword" placeholder="Leave empty to keep current">

      document.getElementById("updateUserModal").style.display = "none";                  </div>

    }                </div>

              </div>

    function openDeleteModal(){              

      document.getElementById("deleteUserModal").style.display = "flex";              <!-- Personal Information -->

    }              <div class="form-section">

                <h4>Personal Information</h4>

    function closeDeleteModal(){                <div class="form-row">

      document.getElementById("deleteUserModal").style.display = "none";                  <div class="form-group">

    }                    <label for="updateFname">First Name</label>

                    <input type="text" id="updateFname" required>

    // Clear form function                  </div>

    function clearForm(type) {                  <div class="form-group">

      const fields = ['Username', 'Password', 'Fname', 'Mname', 'Lname', 'Auxname', 'Gender', 'Birthdate', 'Contact', 'Email'];                    <label for="updateMname">Middle Name</label>

      fields.forEach(field => {                    <input type="text" id="updateMname">

        const element = document.getElementById(type + field);                  </div>

        if (element) {                </div>

          element.value = '';                <div class="form-row">

        }                  <div class="form-group">

      });                    <label for="updateLname">Last Name</label>

    }                    <input type="text" id="updateLname" required>

                  </div>

    // Tab switcher                  <div class="form-group form-group-small">

    const tabLinks = document.querySelectorAll(".tab-link");                    <label for="updateAuxname">Suffix</label>

    const tabContents = document.querySelectorAll(".tab-content");                    <input type="text" id="updateAuxname">

                  </div>

    tabLinks.forEach(link => {                </div>

      link.addEventListener("click", () => {              </div>

        tabLinks.forEach(l => l.classList.remove("active"));            </div>

        tabContents.forEach(c => c.classList.remove("active"));            

            <!-- Right Column -->

        link.classList.add("active");            <div class="form-column">

        document.getElementById(link.dataset.tab).classList.add("active");              <!-- Personal Details -->

      });              <div class="form-section">

    });                <h4>Personal Details</h4>

  </script>                <div class="form-row">

                  <div class="form-group form-group-small">

  <?php require_once("modal.php");?>                    <label for="updateGender">Gender</label>

                    <select id="updateGender" required>

  <script>                      <option value="">Select</option>

    $(document).ready(function(){                      <option value="Male">Male</option>

      let subadminTable;                      <option value="Female">Female</option>

      let studentTable;                    </select>

                  </div>

      // DataTable initialization for Subadmins                  <div class="form-group">

      function initSubadminTable(){                    <label for="updateBirthdate">Birthdate</label>

        subadminTable = $("#subadminTable").DataTable({                    <input type="date" id="updateBirthdate" required>

          ajax:{                  </div>

            url:'ajax.php',                </div>

            type:'post',              </div>

            data:{              

              CALL: 8 // Get subadmin users (previously adviser)              <!-- Contact Information -->

            },              <div class="form-section">

            dataSrc: 'data',                <h4>Contact Information</h4>

            dataType:'json',                <div class="form-row">

            error: function(xhr, error, thrown) {                  <div class="form-group">

              console.log('Subadmin DataTable AJAX Error:', error);                    <label for="updateContact">Contact Number</label>

              console.log('Response:', xhr.responseText);                    <input type="text" id="updateContact">

              openModal("ERROR", "Failed to load subadmin users: " + error);                  </div>

            }                </div>

          },                <div class="form-row">

          responsive: true,                  <div class="form-group">

          scroll: '50vh',                    <label for="updateEmail">Email</label>

          scrollCollapse: true,                     <input type="email" id="updateEmail" required>

          paging: true,                  </div>

          columns: [                </div>

            { data: "user_id" },              </div>

            { data: "user_name" },            </div>

            {           </div>

              data: null,          

              render: function(data, type, row) {          <div class="form-actions">

                return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;            <button type="button" class="btn-primary" id="confirmUpdate">Update User</button>

              }            <button type="button" class="btn-secondary" onclick="closeUpdateModal()">Cancel</button>

            },          </div>

            { data: "email" },        </div>

            { data: "contact_number" },      </div>

            {     </div>

              data: 'user_id',

              render: function(data, type, row) {    <!-- DELETE MODAL -->

                return `    <div id="deleteUserModal" class="modal">

                  <button class="btn-secondary updateBtn" data-id="${data}" data-type="subadmin">Edit</button>      <div class="modal-content modal-content-small">

                  <button class="btn-primary deleteBtn" data-id="${data}" data-type="subadmin">Delete</button>        <span class="modal-close" onclick="closeDeleteModal()">&times;</span>

                `;        <h2>Delete User Confirmation</h2>

              }        

            }        <div class="compact-form">

          ],          <input type="hidden" id="deleteUserId">

          columnDefs: [          <input type="hidden" id="deleteUserType">

            { targets: 0, visible: false, searchable: false }           

          ]          <div class="form-group">

        });            <label for="deleteUserInfo">User to Delete</label>

      }            <input type='text' id='deleteUserInfo' readonly>

          </div>

      // DataTable initialization for Students          

      function initStudentTable(){          <div class="form-group">

        studentTable = $("#studentTable").DataTable({            <label for="deleteReason">Reason for Deletion</label>

          ajax:{            <textarea id='deleteReason' name='deleteReason' rows='4' placeholder="Enter reason for deletion..." required></textarea>

            url:'ajax.php',          </div>

            type:'post',          

            data:{          <div class="form-actions">

              CALL: 9 // Get student users            <button type="button" class="btn-primary" id="confirmDelete">Confirm Delete</button>

            },            <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>

            dataSrc: 'data',          </div>

            dataType:'json',        </div>

            error: function(xhr, error, thrown) {      </div>

              console.log('Student DataTable AJAX Error:', error);    </div>

              console.log('Response:', xhr.responseText);  </main>

              openModal("ERROR", "Failed to load student users: " + error);

            }  <script>

          },    // Modal functions

          responsive: true,    function openUpdateModal() {

          scroll: '50vh',      document.getElementById("updateUserModal").style.display = "flex";

          scrollCollapse: true,     }

          paging: true,    

          columns: [    function closeUpdateModal() {

            { data: "user_id" },      document.getElementById("updateUserModal").style.display = "none";

            { data: "user_name" },    }

            { 

              data: null,    function openDeleteModal(){

              render: function(data, type, row) {      document.getElementById("deleteUserModal").style.display = "flex";

                return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;    }

              }

            },    function closeDeleteModal(){

            { data: "email" },      document.getElementById("deleteUserModal").style.display = "none";

            { data: "contact_number" },    }

            { 

              data: 'user_id',    // Clear form function

              render: function(data, type, row) {    function clearForm(type) {

                return `      const fields = ['Username', 'Password', 'Fname', 'Mname', 'Lname', 'Auxname', 'Gender', 'Birthdate', 'Contact', 'Email'];

                  <button class="btn-secondary updateBtn" data-id="${data}" data-type="student">Edit</button>      fields.forEach(field => {

                  <button class="btn-primary deleteBtn" data-id="${data}" data-type="student">Delete</button>        const element = document.getElementById(type + field);

                `;        if (element) {

              }          element.value = '';

            }        }

          ],      });

          columnDefs: [    }

            { targets: 0, visible: false, searchable: false } 

          ]    // Tab switcher

        });    const tabLinks = document.querySelectorAll(".tab-link");

      }    const tabContents = document.querySelectorAll(".tab-content");



      // Initialize all tables    tabLinks.forEach(link => {

      initSubadminTable();      link.addEventListener("click", () => {

      initStudentTable();        tabLinks.forEach(l => l.classList.remove("active"));

        tabContents.forEach(c => c.classList.remove("active"));

      // Reload all tables

      function reloadAllTables(){        link.classList.add("active");

        subadminTable.ajax.reload(null, false);        document.getElementById(link.dataset.tab).classList.add("active");

        studentTable.ajax.reload(null, false);      });

      }    });

  </script>

      // Save user function

      function saveUser(call, userData, userType) {  <?php require_once("modal.php");?>

        $.ajax({

          url: 'ajax.php',  <script>

          type: 'post',    $(document).ready(function(){

          data: {      let adminTable;

            CALL: call,      let adviserTable;

            DATA: userData      let studentTable;

          },

          dataType: 'json',      // DataTable initialization

          success: function(result) {      function initAdminTable(){

            openModal(result.status, result.msg);        adminTable = $("#adminTable").DataTable({

            if(result.status == "SUCCESS") {          ajax:{

              clearForm(userType);            url:'ajax.php',

            }            type:'post',

          },            data:{

          complete: function() {              CALL: 7 // Get admin users

            reloadAllTables();            },

          }            dataType:'json',

        });            error: function(xhr, error, thrown) {

      }              console.log('DataTable AJAX Error:', error);

              console.log('Response:', xhr.responseText);

      // Update and Delete button handlers              openModal("ERROR", "Failed to load admin users: " + error);

      $(document).on("click", ".updateBtn", function() {            }

        const userId = $(this).data('id');          },

        const userType = $(this).data('type');          responsive: true,

                  scroll: '50vh',

        // Get user data for update          scrollCollapse: true, 

        $.ajax({          paging: true,

          url: 'ajax.php',          columns: [

          type: 'post',            { data: "user_id" },

          data: {            { data: "user_name" },

            CALL: 13, // Get single user            { 

            USER_ID: userId              data: null,

          },              render: function(data, type, row) {

          dataType: 'json',                return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;

          success: function(result) {              }

            if(result.status === "SUCCESS") {            },

              const user = result.data;            { data: "email" },

              $("#updateUserId").val(user.user_id);            { data: "contact_number" },

              $("#updateUserType").val(userType);            { 

              $("#updateUsername").val(user.user_name);              data: 'user_id',

              $("#updateFname").val(user.fname);              render: function(data, type, row) {

              $("#updateMname").val(user.mname);                return `

              $("#updateLname").val(user.lname);                  <button class="btn-secondary updateBtn" data-id="${data}" data-type="admin">Edit</button>

              $("#updateAuxname").val(user.auxname);                  <button class="btn-primary deleteBtn" data-id="${data}" data-type="admin">Delete</button>

              $("#updateGender").val(user.gender);                `;

              $("#updateBirthdate").val(user.birthdate);              }

              $("#updateContact").val(user.contact_number);            }

              $("#updateEmail").val(user.email);          ],

              openUpdateModal();          columnDefs: [

            }            { targets: 0, visible: false, searchable: false } 

          }          ]

        });        });

      });      }



      $(document).on("click", ".deleteBtn", function() {      function initAdviserTable(){

        const userId = $(this).data('id');        adviserTable = $("#adviserTable").DataTable({

        const userType = $(this).data('type');          ajax:{

        const userName = $(this).closest("tr").find("td").eq(1).text();            url:'ajax.php',

        const fullName = $(this).closest("tr").find("td").eq(2).text();            type:'post',

                    data:{

        $("#deleteUserId").val(userId);              CALL: 8 // Get adviser users

        $("#deleteUserType").val(userType);            },

        $("#deleteUserInfo").val(`${userName} (${fullName})`);            dataType:'json',

        openDeleteModal();            error: function(xhr, error, thrown) {

      });              console.log('Adviser DataTable AJAX Error:', error);

              console.log('Response:', xhr.responseText);

      // Confirm update              openModal("ERROR", "Failed to load adviser users: " + error);

      $("#confirmUpdate").click(function() {            }

        const updateData = {          },

          user_id: $("#updateUserId").val(),          responsive: true,

          user_name: $("#updateUsername").val(),          scroll: '50vh',

          password: $("#updatePassword").val(),          scrollCollapse: true, 

          fname: $("#updateFname").val(),          paging: true,

          mname: $("#updateMname").val(),          columns: [

          lname: $("#updateLname").val(),            { data: "user_id" },

          auxname: $("#updateAuxname").val(),            { data: "user_name" },

          gender: $("#updateGender").val(),            { 

          birthdate: $("#updateBirthdate").val(),              data: null,

          contact_number: $("#updateContact").val(),              render: function(data, type, row) {

          email: $("#updateEmail").val()                return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;

        };              }

            },

        $.ajax({            { data: "email" },

          url: 'ajax.php',            { data: "contact_number" },

          type: 'post',            { 

          data: {              data: 'user_id',

            CALL: 11, // Update user              render: function(data, type, row) {

            DATA: updateData                return `

          },                  <button class="btn-secondary updateBtn" data-id="${data}" data-type="adviser">Edit</button>

          dataType: 'json',                  <button class="btn-primary deleteBtn" data-id="${data}" data-type="adviser">Delete</button>

          success: function(result) {                `;

            openModal(result.status, result.msg);              }

            if(result.status === "SUCCESS") {            }

              closeUpdateModal();          ],

            }          columnDefs: [

          },            { targets: 0, visible: false, searchable: false } 

          complete: function() {          ]

            reloadAllTables();        });

          }      }

        });

      });      function initStudentTable(){

        studentTable = $("#studentTable").DataTable({

      // Confirm delete          ajax:{

      $("#confirmDelete").click(function() {            url:'ajax.php',

        const deleteData = {            type:'post',

          user_id: $("#deleteUserId").val(),            data:{

          reason: $("#deleteReason").val()              CALL: 9 // Get student users

        };            },

            dataType:'json',

        if(!deleteData.reason.trim()) {            error: function(xhr, error, thrown) {

          openModal("ERROR", "Please provide a reason for deletion");              console.log('Student DataTable AJAX Error:', error);

          return;              console.log('Response:', xhr.responseText);

        }              openModal("ERROR", "Failed to load student users: " + error);

            }

        $.ajax({          },

          url: 'ajax.php',          responsive: true,

          type: 'post',          scroll: '50vh',

          data: {          scrollCollapse: true, 

            CALL: 12, // Delete user          paging: true,

            DATA: deleteData          columns: [

          },            { data: "user_id" },

          dataType: 'json',            { data: "user_name" },

          success: function(result) {            { 

            openModal(result.status, result.msg);              data: null,

            if(result.status === "SUCCESS") {              render: function(data, type, row) {

              closeDeleteModal();                return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;

              $("#deleteReason").val('');              }

            }            },

          },            { data: "email" },

          complete: function() {            { data: "contact_number" },

            reloadAllTables();            { 

          }              data: 'user_id',

        });              render: function(data, type, row) {

      });                return `

                  <button class="btn-secondary updateBtn" data-id="${data}" data-type="student">Edit</button>

      // Save Subadmin button handler                  <button class="btn-primary deleteBtn" data-id="${data}" data-type="student">Delete</button>

      $("#saveSubadmin").click(function() {                `;

        const subadminData = {              }

          user_name: $("#subadminUsername").val(),            }

          password: $("#subadminPassword").val(),          ],

          fname: $("#subadminFname").val(),          columnDefs: [

          mname: $("#subadminMname").val(),            { targets: 0, visible: false, searchable: false } 

          lname: $("#subadminLname").val(),          ]

          auxname: $("#subadminAuxname").val(),        });

          gender: $("#subadminGender").val(),      }

          birthdate: $("#subadminBirthdate").val(),

          contact_number: $("#subadminContact").val(),      // Initialize all tables

          email: $("#subadminEmail").val(),      initAdminTable();

          user_type: 'adviser' // Backend still uses 'adviser' for subadmins      initAdviserTable();

        };      initStudentTable();



        // Basic validation      // Reload all tables

        if(!subadminData.user_name.trim() || !subadminData.password.trim() || !subadminData.fname.trim() || !subadminData.lname.trim() || !subadminData.email.trim()) {      function reloadAllTables(){

          openModal("ERROR", "Please fill in all required fields");        adminTable.ajax.reload(null, false);

          return;        adviserTable.ajax.reload(null, false);

        }        studentTable.ajax.reload(null, false);

      }

        saveUser(10, subadminData, 'subadmin'); // CALL 10 for creating users

      });      // Save user function

      function saveUser(call, userData, userType) {

      // Save Student button handler        $.ajax({

      $("#saveStudent").click(function() {          url: 'ajax.php',

        const studentData = {          type: 'post',

          user_name: $("#studentUsername").val(),          data: {

          password: $("#studentPassword").val(),            CALL: call,

          fname: $("#studentFname").val(),            DATA: userData

          mname: $("#studentMname").val(),          },

          lname: $("#studentLname").val(),          dataType: 'json',

          auxname: $("#studentAuxname").val(),          success: function(result) {

          gender: $("#studentGender").val(),            openModal(result.status, result.msg);

          birthdate: $("#studentBirthdate").val(),            if(result.status == "SUCCESS") {

          contact_number: $("#studentContact").val(),              clearForm(userType);

          email: $("#studentEmail").val(),            }

          user_type: 'student'          },

        };          complete: function() {

            reloadAllTables();

        // Basic validation          }

        if(!studentData.user_name.trim() || !studentData.password.trim() || !studentData.fname.trim() || !studentData.lname.trim() || !studentData.email.trim()) {        });

          openModal("ERROR", "Please fill in all required fields");      }

          return;

        }      // Update and Delete button handlers

      $(document).on("click", ".updateBtn", function() {

        saveUser(10, studentData, 'student');        const userId = $(this).data('id');

      });        const userType = $(this).data('type');

    });        

  </script>        // Get user data for update

</body>        $.ajax({

</html>          url: 'ajax.php',

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