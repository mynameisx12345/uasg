<!doctype html><!doctype html><!doctype html><!doctype html><!doctype html>

<html lang="en">

<head><html lang="en">

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0"><head><html lang="en">

  <title>Members Management - UASG</title>

    <meta charset="UTF-8">

  <!-- PWA Meta Tags -->

  <meta name="description" content="UASG Members Management - Manage Subadmins and Students">  <meta name="viewport" content="width=device-width, initial-scale=1.0"><head><html lang="en"><html lang="en">

  <meta name="theme-color" content="#2196F3">

  <meta name="apple-mobile-web-app-capable" content="yes">  <title>Members Management - UASG</title>

  <meta name="apple-mobile-web-app-status-bar-style" content="default">

  <meta name="apple-mobile-web-app-title" content="UASG Admin">    <meta charset="UTF-8">

  <meta name="msapplication-TileColor" content="#2196F3">

    <!-- PWA Meta Tags -->

  <!-- PWA Manifest -->

  <link rel="manifest" href="../manifest.json">  <meta name="description" content="UASG Members Management - Manage Subadmins and Students">  <meta name="viewport" content="width=device-width, initial-scale=1.0"><head><head>

  

  <!-- Favicon and Icons -->  <meta name="theme-color" content="#2196F3">

  <link rel="icon" type="image/png" sizes="32x32" href="../resources/icons/icon-32x32.png">

  <link rel="icon" type="image/png" sizes="16x16" href="../resources/icons/icon-16x16.png">  <meta name="apple-mobile-web-app-capable" content="yes">  <title>Members Management - UASG</title>

  <link rel="apple-touch-icon" href="../resources/icons/icon-152x152.png">

    <meta name="apple-mobile-web-app-status-bar-style" content="default">

  <link rel="stylesheet" href="../resources/style.css">

  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>  <meta name="apple-mobile-web-app-title" content="UASG Admin">  <link rel="stylesheet" href="../resources/style.css">  <meta charset="UTF-8">  <meta charset="UTF-8">

  <script src='../js/all.js'></script>

  <script src='../js/jquery.js'></script>  <meta name="msapplication-TileColor" content="#2196F3">

  <script src='../js/datatable.js'></script>

</head>    <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>

<body>

  <!-- HEADER -->  <!-- PWA Manifest -->

   <?php require_once("header.php");?>

   <header class="topbar">  <link rel="manifest" href="../manifest.json">  <script src='../js/all.js'></script>  <meta name="viewport" content="width=device-width, initial-scale=1.0">  <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <h1>Members Management</h1>

    <div class="user-info">  

      <span>Welcome, Admin</span>

    </div>  <!-- Favicon and Icons -->  <script src='../js/jquery.js'></script>

  </header>

  <!-- MAIN -->  <link rel="icon" type="image/png" sizes="32x32" href="../resources/icons/icon-32x32.png">

  <main class="main">

    <!-- SIDEBAR -->  <link rel="icon" type="image/png" sizes="16x16" href="../resources/icons/icon-16x16.png">  <script src='../js/datatable.js'></script>  <title>User Management - UASG</title>  <title>User Management</title>

    <?php require_once("sidebar.php");?>

  <link rel="apple-touch-icon" href="../resources/icons/icon-152x152.png">

    <!-- CONTENT -->

    <section class="content">  </head>

      <div class="card">

        <h2>Members Management</h2>  <link rel="stylesheet" href="../resources/style.css">



        <!-- TABS -->  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'><body>  <link rel="stylesheet" href="../resources/style.css">  <link rel="stylesheet" href="../resources/style.css">

        <div class="tabs">

          <button class="tab-link active" data-tab="subadmins">Subadmins</button>  <script src='../js/all.js'></script>

          <button class="tab-link" data-tab="students">Student Members</button>

        </div>  <script src='../js/jquery.js'></script>  <!-- HEADER -->



        <!-- TAB CONTENT: SUBADMINS -->  <script src='../js/datatable.js'></script>

        <div class="tab-content active" id="subadmins">

          <div class="compact-form"></head>  <?php require_once("header.php");?>  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>

            <h3>Add New Subadmin</h3>

            <div class="form-columns"><body>

              <div class="form-column">

                <div class="form-section">  <!-- HEADER -->  

                  <h4>Account Information</h4>

                  <div class="form-row">  <?php require_once("header.php");?>

                    <div class="form-group">

                      <label for="subadminUsername">Username</label>  <header class="topbar">  <!-- MAIN -->  <script src='../js/all.js'></script>  <script src='../js/all.js'></script>

                      <input type="text" id="subadminUsername" placeholder="Username" required>

                    </div>    <h1>Members Management</h1>

                  </div>

                  <div class="form-row">    <div class="user-info">  <main class="main">

                    <div class="form-group">

                      <label for="subadminPassword">Password</label>      <span>Welcome, Admin</span>

                      <input type="password" id="subadminPassword" placeholder="Password" required>

                    </div>    </div>    <!-- SIDEBAR -->  <script src='../js/jquery.js'></script>  <script src='../js/jquery.js'></script>

                  </div>

                </div>  </header>



                <div class="form-section">  <!-- MAIN -->    <?php require_once("sidebar.php");?>

                  <h4>Personal Information</h4>

                  <div class="form-row">  <main class="main">

                    <div class="form-group">

                      <label for="subadminFname">First Name</label>    <!-- SIDEBAR -->  <script src='../js/datatable.js'></script>  <script src='../js/datatable.js'></script>

                      <input type="text" id="subadminFname" placeholder="First Name" required>

                    </div>    <?php require_once("sidebar.php");?>

                    <div class="form-group">

                      <label for="subadminMname">Middle Name</label>    <!-- CONTENT -->

                      <input type="text" id="subadminMname" placeholder="Middle Name">

                    </div>    <!-- CONTENT -->

                  </div>

                  <div class="form-row">    <section class="content">    <section class="content"></head></head>

                    <div class="form-group">

                      <label for="subadminLname">Last Name</label>      <div class="card">

                      <input type="text" id="subadminLname" placeholder="Last Name" required>

                    </div>        <h2>Members Management</h2>      <div class="card">

                    <div class="form-group form-group-small">

                      <label for="subadminAuxname">Suffix</label>

                      <input type="text" id="subadminAuxname" placeholder="Jr., Sr.">

                    </div>        <!-- TABS -->        <h2>Members Management</h2><body><body>

                  </div>

                </div>        <div class="tabs">



                <div class="form-section">          <button class="tab-link active" data-tab="subadmins">Subadmins</button>

                  <h4>Personal Details</h4>

                  <div class="form-row">          <button class="tab-link" data-tab="students">Student Members</button>

                    <div class="form-group form-group-small">

                      <label for="subadminGender">Gender</label>        </div>        <!-- TABS -->  <!-- HEADER -->  <!-- HEADER -->

                      <select id="subadminGender" required>

                        <option value="">Select</option>

                        <option value="Male">Male</option>

                        <option value="Female">Female</option>        <!-- TAB CONTENT: SUBADMINS -->        <div class="tabs">

                      </select>

                    </div>        <div class="tab-content active" id="subadmins">

                    <div class="form-group">

                      <label for="subadminBirthdate">Birthdate</label>          <div class="compact-form">          <button class="tab-link active" data-tab="subadmins">Subadmins</button>   <?php require_once("header.php");?>   <?php require_once("header.php");?>

                      <input type="date" id="subadminBirthdate" required>

                    </div>            <h3>Add New Subadmin</h3>

                  </div>

                </div>            <div class="form-columns">          <button class="tab-link" data-tab="students">Student Members</button>

              </div>

              <div class="form-column">

              <div class="form-column">

                <div class="form-section">                <div class="form-section">        </div>   <header class="topbar">   <header class="topbar">

                  <h4>Contact Information</h4>

                  <div class="form-row">                  <h4>Account Information</h4>

                    <div class="form-group">

                      <label for="subadminContact">Contact Number</label>                  <div class="form-row">

                      <input type="text" id="subadminContact" placeholder="Contact Number">

                    </div>                    <div class="form-group">

                  </div>

                  <div class="form-row">                      <label for="subadminUsername">Username</label>        <!-- TAB CONTENT: SUBADMINS -->    <h1>Members Management</h1>    <h1>User Management</h1>

                    <div class="form-group">

                      <label for="subadminEmail">Email</label>                      <input type="text" id="subadminUsername" placeholder="Username" required>

                      <input type="email" id="subadminEmail" placeholder="Email Address" required>

                    </div>                    </div>        <div class="tab-content active" id="subadmins">

                  </div>

                </div>                  </div>



                <div class="form-section">                  <div class="form-row">          <div class="compact-form">    <div class="user-info">    <div class="user-info">

                  <h4>Actions</h4>

                  <div class="form-actions">                    <div class="form-group">

                    <button type="button" class="btn-primary" id="saveSubadmin">Save Subadmin</button>

                    <button type="button" class="btn-secondary" onclick="clearForm('subadmin')">Clear</button>                      <label for="subadminPassword">Password</label>            <h3>Add New Subadmin</h3>

                  </div>

                </div>                      <input type="password" id="subadminPassword" placeholder="Password" required>

              </div>

            </div>                    </div>                  <span>Welcome, Admin</span>      <span>Welcome, Admin</span>

          </div>

          <br/>                  </div>

          <div class="table-container">

            <table class='data-table' id='subadminTable'>                </div>            <div class="form-columns">

              <thead>

                <tr>

                  <th>ID</th>

                  <th>Username</th>                <div class="form-section">              <!-- Left Column -->    </div>    </div>

                  <th>Full Name</th>

                  <th>Email</th>                  <h4>Personal Information</h4>

                  <th>Contact</th>

                  <th>Actions</th>                  <div class="form-row">              <div class="form-column">

                </tr>

              </thead>                    <div class="form-group">

              <tbody></tbody>

            </table>                      <label for="subadminFname">First Name</label>                <!-- Account Information -->  </header>  </header>

          </div>

        </div>                      <input type="text" id="subadminFname" placeholder="First Name" required>



        <!-- TAB CONTENT: STUDENTS -->                    </div>                <div class="form-section">

        <div class="tab-content" id="students">

          <div class="compact-form">                    <div class="form-group">

            <h3>Add New Student Member</h3>

            <div class="form-columns">                      <label for="subadminMname">Middle Name</label>                  <h4>Account Information</h4>  <!-- MAIN -->  <!-- MAIN -->

              <div class="form-column">

                <div class="form-section">                      <input type="text" id="subadminMname" placeholder="Middle Name">

                  <h4>Account Information</h4>

                  <div class="form-row">                    </div>                  <div class="form-row">

                    <div class="form-group">

                      <label for="studentUsername">Username</label>                  </div>

                      <input type="text" id="studentUsername" placeholder="Username" required>

                    </div>                  <div class="form-row">                    <div class="form-group">  <main class="main">  <main class="main">

                  </div>

                  <div class="form-row">                    <div class="form-group">

                    <div class="form-group">

                      <label for="studentPassword">Password</label>                      <label for="subadminLname">Last Name</label>                      <label for="subadminUsername">Username</label>

                      <input type="password" id="studentPassword" placeholder="Password" required>

                    </div>                      <input type="text" id="subadminLname" placeholder="Last Name" required>

                  </div>

                </div>                    </div>                      <input type="text" id="subadminUsername" placeholder="Username" required>    <!-- SIDEBAR -->    <!-- SIDEBAR -->



                <div class="form-section">                    <div class="form-group form-group-small">

                  <h4>Personal Information</h4>

                  <div class="form-row">                      <label for="subadminAuxname">Suffix</label>                    </div>

                    <div class="form-group">

                      <label for="studentFname">First Name</label>                      <input type="text" id="subadminAuxname" placeholder="Jr., Sr.">

                      <input type="text" id="studentFname" placeholder="First Name" required>

                    </div>                    </div>                  </div>    <?php require_once("sidebar.php");?>    <?php require_once("sidebar.php");?>

                    <div class="form-group">

                      <label for="studentMname">Middle Name</label>                  </div>

                      <input type="text" id="studentMname" placeholder="Middle Name">

                    </div>                </div>                  <div class="form-row">

                  </div>

                  <div class="form-row">              </div>

                    <div class="form-group">

                      <label for="studentLname">Last Name</label>                    <div class="form-group">

                      <input type="text" id="studentLname" placeholder="Last Name" required>

                    </div>              <div class="form-column">

                    <div class="form-group form-group-small">

                      <label for="studentAuxname">Suffix</label>                <div class="form-section">                      <label for="subadminPassword">Password</label>

                      <input type="text" id="studentAuxname" placeholder="Jr., Sr.">

                    </div>                  <h4>Personal Details</h4>

                  </div>

                </div>                  <div class="form-row">                      <input type="password" id="subadminPassword" placeholder="Password" required>    <!-- CONTENT -->    <!-- CONTENT -->



                <div class="form-section">                    <div class="form-group form-group-small">

                  <h4>Personal Details</h4>

                  <div class="form-row">                      <label for="subadminGender">Gender</label>                    </div>

                    <div class="form-group form-group-small">

                      <label for="studentGender">Gender</label>                      <select id="subadminGender" required>

                      <select id="studentGender" required>

                        <option value="">Select</option>                        <option value="">Select</option>                  </div>    <section class="content">    <section class="content">

                        <option value="Male">Male</option>

                        <option value="Female">Female</option>                        <option value="Male">Male</option>

                      </select>

                    </div>                        <option value="Female">Female</option>                </div>

                    <div class="form-group">

                      <label for="studentBirthdate">Birthdate</label>                      </select>

                      <input type="date" id="studentBirthdate" required>

                    </div>                    </div>      <div class="card">      <div class="card">

                  </div>

                </div>                    <div class="form-group">

              </div>

                      <label for="subadminBirthdate">Birthdate</label>                <!-- Personal Information -->

              <div class="form-column">

                <div class="form-section">                      <input type="date" id="subadminBirthdate" required>

                  <h4>Contact Information</h4>

                  <div class="form-row">                    </div>                <div class="form-section">        <h2>Members Management</h2>        <h2>User Management</h2>

                    <div class="form-group">

                      <label for="studentContact">Contact Number</label>                  </div>

                      <input type="text" id="studentContact" placeholder="Contact Number">

                    </div>                </div>                  <h4>Personal Information</h4>

                  </div>

                  <div class="form-row">

                    <div class="form-group">

                      <label for="studentEmail">Email</label>                <div class="form-section">                  <div class="form-row">

                      <input type="email" id="studentEmail" placeholder="Email Address" required>

                    </div>                  <h4>Contact Information</h4>

                  </div>

                </div>                  <div class="form-row">                    <div class="form-group">



                <div class="form-section">                    <div class="form-group">

                  <h4>Actions</h4>

                  <div class="form-actions">                      <label for="subadminContact">Contact Number</label>                      <label for="subadminFname">First Name</label>        <!-- TABS -->        <!-- TABS -->

                    <button type="button" class="btn-primary" id="saveStudent">Save Student Member</button>

                    <button type="button" class="btn-secondary" onclick="clearForm('student')">Clear</button>                      <input type="text" id="subadminContact" placeholder="Contact Number">

                  </div>

                </div>                    </div>                      <input type="text" id="subadminFname" placeholder="First Name" required>

              </div>

            </div>                  </div>

          </div>

          <br/>                  <div class="form-row">                    </div>        <div class="tabs">        <div class="tabs">

          <div class="table-container">

            <table class='data-table' id='studentTable'>                    <div class="form-group">

              <thead>

                <tr>                      <label for="subadminEmail">Email</label>                    <div class="form-group">

                  <th>ID</th>

                  <th>Username</th>                      <input type="email" id="subadminEmail" placeholder="Email Address" required>

                  <th>Full Name</th>

                  <th>Email</th>                    </div>                      <label for="subadminMname">Middle Name</label>          <button class="tab-link active" data-tab="subadmins">Subadmins</button>          <button class="tab-link active" data-tab="subadmins">Subadmins</button>

                  <th>Contact</th>

                  <th>Actions</th>                  </div>

                </tr>

              </thead>                </div>                      <input type="text" id="subadminMname" placeholder="Middle Name">

              <tbody></tbody>

            </table>

          </div>

        </div>                <div class="form-section">                    </div>          <button class="tab-link" data-tab="students">Student Members</button>          <button class="tab-link" data-tab="students">Student Members</button>

      </div>

    </section>                  <h4>Actions</h4>



    <!-- UPDATE MODAL -->                  <div class="form-actions">                  </div>

    <div id="updateUserModal" class="modal">

      <div class="modal-content">                    <button type="button" class="btn-primary" id="saveSubadmin">Save Subadmin</button>

        <span class="modal-close" onclick="closeUpdateModal()">&times;</span>

        <h2>Update User Information</h2>                    <button type="button" class="btn-secondary" onclick="clearForm('subadmin')">Clear</button>                  <div class="form-row">        </div>        </div>

        

        <div class="compact-form">                  </div>

          <input type="hidden" id="updateUserId">

          <input type="hidden" id="updateUserType">                </div>                    <div class="form-group">

          

          <div class="form-section">              </div>

            <h4>Account Information</h4>

            <div class="form-row">            </div>                      <label for="subadminLname">Last Name</label>

              <div class="form-group">

                <label for="updateUsername">Username</label>          </div>

                <input type="text" id="updateUsername" required>

              </div>          <br/>                      <input type="text" id="subadminLname" placeholder="Last Name" required>

            </div>

            <div class="form-row">          <div class="table-container">

              <div class="form-group">

                <label for="updatePassword">Password</label>            <table class='data-table' id='subadminTable'>                    </div>        <!-- TAB CONTENT: SUBADMINS -->        <!-- TAB CONTENT: SUBADMINS -->

                <input type="password" id="updatePassword" placeholder="Leave empty to keep current">

              </div>              <thead>

            </div>

          </div>                <tr>                    <div class="form-group form-group-small">

          

          <div class="form-section">                  <th>ID</th>

            <h4>Personal Information</h4>

            <div class="form-row">                  <th>Username</th>                      <label for="subadminAuxname">Suffix</label>        <div class="tab-content active" id="subadmins">        <div class="tab-content active" id="subadmins">

              <div class="form-group">

                <label for="updateFname">First Name</label>                  <th>Full Name</th>

                <input type="text" id="updateFname" required>

              </div>                  <th>Email</th>                      <input type="text" id="subadminAuxname" placeholder="Jr., Sr.">

              <div class="form-group">

                <label for="updateMname">Middle Name</label>                  <th>Contact</th>

                <input type="text" id="updateMname">

              </div>                  <th>Actions</th>                    </div>          <div class="compact-form">          <div class="compact-form">

            </div>

            <div class="form-row">                </tr>

              <div class="form-group">

                <label for="updateLname">Last Name</label>              </thead>                  </div>

                <input type="text" id="updateLname" required>

              </div>              <tbody></tbody>

              <div class="form-group form-group-small">

                <label for="updateAuxname">Suffix</label>            </table>                </div>            <h3>Add New Subadmin</h3>            <h3>Add New Administrator</h3>

                <input type="text" id="updateAuxname">

              </div>          </div>

            </div>

          </div>        </div>              </div>

          

          <div class="form-section">

            <h4>Personal Details</h4>

            <div class="form-row">        <!-- TAB CONTENT: STUDENTS -->                        

              <div class="form-group form-group-small">

                <label for="updateGender">Gender</label>        <div class="tab-content" id="students">

                <select id="updateGender" required>

                  <option value="">Select</option>          <div class="compact-form">              <!-- Right Column -->

                  <option value="Male">Male</option>

                  <option value="Female">Female</option>            <h3>Add New Student Member</h3>

                </select>

              </div>            <div class="form-columns">              <div class="form-column">            <div class="form-columns">            <div class="form-columns">

              <div class="form-group">

                <label for="updateBirthdate">Birthdate</label>              <div class="form-column">

                <input type="date" id="updateBirthdate" required>

              </div>                <div class="form-section">                <!-- Personal Details -->

            </div>

          </div>                  <h4>Account Information</h4>

          

          <div class="form-section">                  <div class="form-row">                <div class="form-section">              <!-- Left Column -->              <!-- Left Column -->

            <h4>Contact Information</h4>

            <div class="form-row">                    <div class="form-group">

              <div class="form-group">

                <label for="updateContact">Contact Number</label>                      <label for="studentUsername">Username</label>                  <h4>Personal Details</h4>

                <input type="text" id="updateContact">

              </div>                      <input type="text" id="studentUsername" placeholder="Username" required>

            </div>

            <div class="form-row">                    </div>                  <div class="form-row">              <div class="form-column">              <div class="form-column">

              <div class="form-group">

                <label for="updateEmail">Email</label>                  </div>

                <input type="email" id="updateEmail" required>

              </div>                  <div class="form-row">                    <div class="form-group form-group-small">

            </div>

          </div>                    <div class="form-group">

          

          <div class="form-actions">                      <label for="studentPassword">Password</label>                      <label for="subadminGender">Gender</label>                <!-- Account Information -->                <!-- Account Information -->

            <button type="button" class="btn-primary" id="confirmUpdate">Update User</button>

            <button type="button" class="btn-secondary" onclick="closeUpdateModal()">Cancel</button>                      <input type="password" id="studentPassword" placeholder="Password" required>

          </div>

        </div>                    </div>                      <select id="subadminGender" required>

      </div>

    </div>                  </div>



    <!-- DELETE MODAL -->                </div>                        <option value="">Select</option>                <div class="form-section">                <div class="form-section">

    <div id="deleteUserModal" class="modal">

      <div class="modal-content modal-content-small">

        <span class="modal-close" onclick="closeDeleteModal()">&times;</span>

        <h2>Delete User Confirmation</h2>                <div class="form-section">                        <option value="Male">Male</option>

        

        <div class="compact-form">                  <h4>Personal Information</h4>

          <input type="hidden" id="deleteUserId">

          <input type="hidden" id="deleteUserType">                  <div class="form-row">                        <option value="Female">Female</option>                  <h4>Account Information</h4>                  <h4>Account Information</h4>

          

          <div class="form-section">                    <div class="form-group">

            <div class="form-row">

              <div class="form-group">                      <label for="studentFname">First Name</label>                      </select>

                <label for="deleteUserInfo">User to Delete</label>

                <input type='text' id='deleteUserInfo' readonly>                      <input type="text" id="studentFname" placeholder="First Name" required>

              </div>

            </div>                    </div>                    </div>                  <div class="form-row">                  <div class="form-row">

            <div class="form-row">

              <div class="form-group">                    <div class="form-group">

                <label for="deleteReason">Reason for Deletion</label>

                <textarea id='deleteReason' name='deleteReason' rows='4' placeholder="Enter reason for deletion..." required></textarea>                      <label for="studentMname">Middle Name</label>                    <div class="form-group">

              </div>

            </div>                      <input type="text" id="studentMname" placeholder="Middle Name">

          </div>

                              </div>                      <label for="subadminBirthdate">Birthdate</label>                    <div class="form-group">                    <div class="form-group">

          <div class="form-actions">

            <button type="button" class="btn-primary" id="confirmDelete">Confirm Delete</button>                  </div>

            <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>

          </div>                  <div class="form-row">                      <input type="date" id="subadminBirthdate" required>

        </div>

      </div>                    <div class="form-group">

    </div>

  </main>                      <label for="studentLname">Last Name</label>                    </div>                      <label for="subadminUsername">Username</label>                      <label for="adminUsername">Username</label>



  <script>                      <input type="text" id="studentLname" placeholder="Last Name" required>

    // Modal functions

    function openUpdateModal() {                    </div>                  </div>

      document.getElementById("updateUserModal").style.display = "flex";

    }                    <div class="form-group form-group-small">

    

    function closeUpdateModal() {                      <label for="studentAuxname">Suffix</label>                </div>                      <input type="text" id="subadminUsername" placeholder="Username" required>                      <input type="text" id="adminUsername" placeholder="Username" required>

      document.getElementById("updateUserModal").style.display = "none";

    }                      <input type="text" id="studentAuxname" placeholder="Jr., Sr.">



    function openDeleteModal(){                    </div>

      document.getElementById("deleteUserModal").style.display = "flex";

    }                  </div>



    function closeDeleteModal(){                </div>                <!-- Contact Information -->                    </div>                    </div>

      document.getElementById("deleteUserModal").style.display = "none";

    }              </div>



    // Clear form function                <div class="form-section">

    function clearForm(type) {

      const fields = ['Username', 'Password', 'Fname', 'Mname', 'Lname', 'Auxname', 'Gender', 'Birthdate', 'Contact', 'Email'];              <div class="form-column">

      fields.forEach(field => {

        const element = document.getElementById(type + field);                <div class="form-section">                  <h4>Contact Information</h4>                  </div>                  </div>

        if (element) {

          element.value = '';                  <h4>Personal Details</h4>

        }

      });                  <div class="form-row">                  <div class="form-row">

    }

                    <div class="form-group form-group-small">

    // Tab switcher

    const tabLinks = document.querySelectorAll(".tab-link");                      <label for="studentGender">Gender</label>                    <div class="form-group">                  <div class="form-row">                  <div class="form-row">

    const tabContents = document.querySelectorAll(".tab-content");

                      <select id="studentGender" required>

    tabLinks.forEach(link => {

      link.addEventListener("click", () => {                        <option value="">Select</option>                      <label for="subadminContact">Contact Number</label>

        tabLinks.forEach(l => l.classList.remove("active"));

        tabContents.forEach(c => c.classList.remove("active"));                        <option value="Male">Male</option>



        link.classList.add("active");                        <option value="Female">Female</option>                      <input type="text" id="subadminContact" placeholder="Contact Number">                    <div class="form-group">                    <div class="form-group">

        document.getElementById(link.dataset.tab).classList.add("active");

      });                      </select>

    });

  </script>                    </div>                    </div>



  <?php require_once("modal.php");?>                    <div class="form-group">



  <script>                      <label for="studentBirthdate">Birthdate</label>                  </div>                      <label for="subadminPassword">Password</label>                      <label for="adminPassword">Password</label>

    $(document).ready(function(){

      let subadminTable;                      <input type="date" id="studentBirthdate" required>

      let studentTable;

                    </div>                  <div class="form-row">

      // DataTable initialization for Subadmins

      function initSubadminTable(){                  </div>

        subadminTable = $("#subadminTable").DataTable({

          ajax:{                </div>                    <div class="form-group">                      <input type="password" id="subadminPassword" placeholder="Password" required>                      <input type="password" id="adminPassword" placeholder="Password" required>

            url:'ajax.php',

            type:'post',

            data:{

              CALL: 8                <div class="form-section">                      <label for="subadminEmail">Email</label>

            },

            dataSrc: 'data',                  <h4>Contact Information</h4>

            dataType:'json',

            error: function(xhr, error, thrown) {                  <div class="form-row">                      <input type="email" id="subadminEmail" placeholder="Email Address" required>                    </div>                    </div>

              console.log('Subadmin DataTable AJAX Error:', error);

              console.log('Response:', xhr.responseText);                    <div class="form-group">

              openModal("ERROR", "Failed to load subadmin users: " + error);

            }                      <label for="studentContact">Contact Number</label>                    </div>

          },

          responsive: true,                      <input type="text" id="studentContact" placeholder="Contact Number">

          scroll: '50vh',

          scrollCollapse: true,                     </div>                  </div>                  </div>                  </div>

          paging: true,

          columns: [                  </div>

            { data: "user_id" },

            { data: "user_name" },                  <div class="form-row">                </div>

            { 

              data: null,                    <div class="form-group">

              render: function(data, type, row) {

                return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;                      <label for="studentEmail">Email</label>              </div>                </div>                </div>

              }

            },                      <input type="email" id="studentEmail" placeholder="Email Address" required>

            { data: "email" },

            { data: "contact_number" },                    </div>            </div>

            { 

              data: 'user_id',                  </div>

              render: function(data, type, row) {

                return `                </div>

                  <button class="btn-secondary updateBtn" data-id="${data}" data-type="subadmin">Edit</button>

                  <button class="btn-primary deleteBtn" data-id="${data}" data-type="subadmin">Delete</button>

                `;

              }                <div class="form-section">            <div class="form-actions">

            }

          ],                  <h4>Actions</h4>

          columnDefs: [

            { targets: 0, visible: false, searchable: false }                   <div class="form-actions">              <button type="button" class="btn-primary" id="saveSubadmin">Save Subadmin</button>                <!-- Personal Information -->                <!-- Personal Information -->

          ]

        });                    <button type="button" class="btn-primary" id="saveStudent">Save Student Member</button>

      }

                    <button type="button" class="btn-secondary" onclick="clearForm('student')">Clear</button>              <button type="button" class="btn-secondary" onclick="clearForm('subadmin')">Clear</button>

      // DataTable initialization for Students

      function initStudentTable(){                  </div>

        studentTable = $("#studentTable").DataTable({

          ajax:{                </div>            </div>                <div class="form-section">                <div class="form-section">

            url:'ajax.php',

            type:'post',              </div>

            data:{

              CALL: 9            </div>          </div>

            },

            dataSrc: 'data',          </div>

            dataType:'json',

            error: function(xhr, error, thrown) {          <br/>                            <h4>Personal Information</h4>                  <h4>Personal Information</h4>

              console.log('Student DataTable AJAX Error:', error);

              console.log('Response:', xhr.responseText);          <div class="table-container">

              openModal("ERROR", "Failed to load student users: " + error);

            }            <table class='data-table' id='studentTable'>          <div class="table-container">

          },

          responsive: true,              <thead>

          scroll: '50vh',

          scrollCollapse: true,                 <tr>            <table class='data-table' id='subadminTable'>                  <div class="form-row">                  <div class="form-row">

          paging: true,

          columns: [                  <th>ID</th>

            { data: "user_id" },

            { data: "user_name" },                  <th>Username</th>              <thead>

            { 

              data: null,                  <th>Full Name</th>

              render: function(data, type, row) {

                return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;                  <th>Email</th>                <tr>                    <div class="form-group">                    <div class="form-group">

              }

            },                  <th>Contact</th>

            { data: "email" },

            { data: "contact_number" },                  <th>Actions</th>                  <th>ID</th>

            { 

              data: 'user_id',                </tr>

              render: function(data, type, row) {

                return `              </thead>                  <th>Username</th>                      <label for="subadminFname">First Name</label>                      <label for="adminFname">First Name</label>

                  <button class="btn-secondary updateBtn" data-id="${data}" data-type="student">Edit</button>

                  <button class="btn-primary deleteBtn" data-id="${data}" data-type="student">Delete</button>              <tbody></tbody>

                `;

              }            </table>                  <th>Full Name</th>

            }

          ],          </div>

          columnDefs: [

            { targets: 0, visible: false, searchable: false }         </div>                  <th>Email</th>                      <input type="text" id="subadminFname" placeholder="First Name" required>                      <input type="text" id="adminFname" placeholder="First Name" required>

          ]

        });      </div>

      }

    </section>                  <th>Contact</th>

      // Initialize all tables

      initSubadminTable();

      initStudentTable();

    <!-- UPDATE MODAL -->                  <th>Actions</th>                    </div>                    </div>

      // Reload all tables

      function reloadAllTables(){    <div id="updateUserModal" class="modal">

        subadminTable.ajax.reload(null, false);

        studentTable.ajax.reload(null, false);      <div class="modal-content">                </tr>

      }

        <span class="modal-close" onclick="closeUpdateModal()">&times;</span>

      // Save user function

      function saveUser(call, userData, userType) {        <h2>Update User Information</h2>              </thead>                    <div class="form-group">                    <div class="form-group">

        $.ajax({

          url: 'ajax.php',        

          type: 'post',

          data: {        <div class="compact-form">              <tbody></tbody>

            CALL: call,

            DATA: userData          <input type="hidden" id="updateUserId">

          },

          dataType: 'json',          <input type="hidden" id="updateUserType">            </table>                      <label for="subadminMname">Middle Name</label>                      <label for="adminMname">Middle Name</label>

          success: function(result) {

            openModal(result.status, result.msg);          

            if(result.status == "SUCCESS") {

              clearForm(userType);          <div class="form-columns">          </div>

            }

          },            <div class="form-column">

          complete: function() {

            reloadAllTables();              <div class="form-section">        </div>                      <input type="text" id="subadminMname" placeholder="Middle Name">                      <input type="text" id="adminMname" placeholder="Middle Name">

          }

        });                <h4>Account Information</h4>

      }

                <div class="form-row">

      // Update and Delete button handlers

      $(document).on("click", ".updateBtn", function() {                  <div class="form-group">

        const userId = $(this).data('id');

        const userType = $(this).data('type');                    <label for="updateUsername">Username</label>        <!-- TAB CONTENT: STUDENTS -->                    </div>                    </div>

        

        $.ajax({                    <input type="text" id="updateUsername" required>

          url: 'ajax.php',

          type: 'post',                  </div>        <div class="tab-content" id="students">

          data: {

            CALL: 13,                </div>

            USER_ID: userId

          },                <div class="form-row">          <div class="compact-form">                  </div>                  </div>

          dataType: 'json',

          success: function(result) {                  <div class="form-group">

            if(result.status === "SUCCESS") {

              const user = result.data;                    <label for="updatePassword">Password</label>            <h3>Add New Student Member</h3>

              $("#updateUserId").val(user.user_id);

              $("#updateUserType").val(userType);                    <input type="password" id="updatePassword" placeholder="Leave empty to keep current">

              $("#updateUsername").val(user.user_name);

              $("#updateFname").val(user.fname);                  </div>                              <div class="form-row">                  <div class="form-row">

              $("#updateMname").val(user.mname);

              $("#updateLname").val(user.lname);                </div>

              $("#updateAuxname").val(user.auxname);

              $("#updateGender").val(user.gender);              </div>            <div class="form-columns">

              $("#updateBirthdate").val(user.birthdate);

              $("#updateContact").val(user.contact_number);              

              $("#updateEmail").val(user.email);

              openUpdateModal();              <div class="form-section">              <!-- Left Column -->                    <div class="form-group">                    <div class="form-group">

            }

          }                <h4>Personal Information</h4>

        });

      });                <div class="form-row">              <div class="form-column">



      $(document).on("click", ".deleteBtn", function() {                  <div class="form-group">

        const userId = $(this).data('id');

        const userType = $(this).data('type');                    <label for="updateFname">First Name</label>                <!-- Account Information -->                      <label for="subadminLname">Last Name</label>                      <label for="adminLname">Last Name</label>

        const userName = $(this).closest("tr").find("td").eq(1).text();

        const fullName = $(this).closest("tr").find("td").eq(2).text();                    <input type="text" id="updateFname" required>

        

        $("#deleteUserId").val(userId);                  </div>                <div class="form-section">

        $("#deleteUserType").val(userType);

        $("#deleteUserInfo").val(`${userName} (${fullName})`);                  <div class="form-group">

        openDeleteModal();

      });                    <label for="updateMname">Middle Name</label>                  <h4>Account Information</h4>                      <input type="text" id="subadminLname" placeholder="Last Name" required>                      <input type="text" id="adminLname" placeholder="Last Name" required>



      // Confirm update                    <input type="text" id="updateMname">

      $("#confirmUpdate").click(function() {

        const updateData = {                  </div>                  <div class="form-row">

          user_id: $("#updateUserId").val(),

          user_name: $("#updateUsername").val(),                </div>

          password: $("#updatePassword").val(),

          fname: $("#updateFname").val(),                <div class="form-row">                    <div class="form-group">                    </div>                    </div>

          mname: $("#updateMname").val(),

          lname: $("#updateLname").val(),                  <div class="form-group">

          auxname: $("#updateAuxname").val(),

          gender: $("#updateGender").val(),                    <label for="updateLname">Last Name</label>                      <label for="studentUsername">Username</label>

          birthdate: $("#updateBirthdate").val(),

          contact_number: $("#updateContact").val(),                    <input type="text" id="updateLname" required>

          email: $("#updateEmail").val()

        };                  </div>                      <input type="text" id="studentUsername" placeholder="Username" required>                    <div class="form-group form-group-small">                    <div class="form-group form-group-small">



        $.ajax({                  <div class="form-group form-group-small">

          url: 'ajax.php',

          type: 'post',                    <label for="updateAuxname">Suffix</label>                    </div>

          data: {

            CALL: 11,                    <input type="text" id="updateAuxname">

            DATA: updateData

          },                  </div>                  </div>                      <label for="subadminAuxname">Suffix</label>                      <label for="adminAuxname">Suffix</label>

          dataType: 'json',

          success: function(result) {                </div>

            openModal(result.status, result.msg);

            if(result.status === "SUCCESS") {              </div>                  <div class="form-row">

              closeUpdateModal();

            }            </div>

          },

          complete: function() {                                <div class="form-group">                      <input type="text" id="subadminAuxname" placeholder="Jr., Sr.">                      <input type="text" id="adminAuxname" placeholder="Jr., Sr.">

            reloadAllTables();

          }            <div class="form-column">

        });

      });              <div class="form-section">                      <label for="studentPassword">Password</label>



      // Confirm delete                <h4>Personal Details</h4>

      $("#confirmDelete").click(function() {

        const deleteData = {                <div class="form-row">                      <input type="password" id="studentPassword" placeholder="Password" required>                    </div>                    </div>

          user_id: $("#deleteUserId").val(),

          reason: $("#deleteReason").val()                  <div class="form-group form-group-small">

        };

                    <label for="updateGender">Gender</label>                    </div>

        if(!deleteData.reason.trim()) {

          openModal("ERROR", "Please provide a reason for deletion");                    <select id="updateGender" required>

          return;

        }                      <option value="">Select</option>                  </div>                  </div>                  </div>



        $.ajax({                      <option value="Male">Male</option>

          url: 'ajax.php',

          type: 'post',                      <option value="Female">Female</option>                </div>

          data: {

            CALL: 12,                    </select>

            DATA: deleteData

          },                  </div>                </div>                </div>

          dataType: 'json',

          success: function(result) {                  <div class="form-group">

            openModal(result.status, result.msg);

            if(result.status === "SUCCESS") {                    <label for="updateBirthdate">Birthdate</label>                <!-- Personal Information -->

              closeDeleteModal();

              $("#deleteReason").val('');                    <input type="date" id="updateBirthdate" required>

            }

          },                  </div>                <div class="form-section">              </div>              </div>

          complete: function() {

            reloadAllTables();                </div>

          }

        });              </div>                  <h4>Personal Information</h4>

      });

              

      // Save Subadmin button handler

      $("#saveSubadmin").click(function() {              <div class="form-section">                  <div class="form-row">

        const subadminData = {

          user_name: $("#subadminUsername").val(),                <h4>Contact Information</h4>

          password: $("#subadminPassword").val(),

          fname: $("#subadminFname").val(),                <div class="form-row">                    <div class="form-group">

          mname: $("#subadminMname").val(),

          lname: $("#subadminLname").val(),                  <div class="form-group">

          auxname: $("#subadminAuxname").val(),

          gender: $("#subadminGender").val(),                    <label for="updateContact">Contact Number</label>                      <label for="studentFname">First Name</label>              <!-- Right Column -->              <!-- Right Column -->

          birthdate: $("#subadminBirthdate").val(),

          contact_number: $("#subadminContact").val(),                    <input type="text" id="updateContact">

          email: $("#subadminEmail").val(),

          user_type: 'adviser'                  </div>                      <input type="text" id="studentFname" placeholder="First Name" required>

        };

                </div>

        if(!subadminData.user_name.trim() || !subadminData.password.trim() || !subadminData.fname.trim() || !subadminData.lname.trim() || !subadminData.email.trim()) {

          openModal("ERROR", "Please fill in all required fields");                <div class="form-row">                    </div>              <div class="form-column">              <div class="form-column">

          return;

        }                  <div class="form-group">



        saveUser(10, subadminData, 'subadmin');                    <label for="updateEmail">Email</label>                    <div class="form-group">

      });

                    <input type="email" id="updateEmail" required>

      // Save Student button handler

      $("#saveStudent").click(function() {                  </div>                      <label for="studentMname">Middle Name</label>                <!-- Personal Details -->                <!-- Personal Details -->

        const studentData = {

          user_name: $("#studentUsername").val(),                </div>

          password: $("#studentPassword").val(),

          fname: $("#studentFname").val(),              </div>                      <input type="text" id="studentMname" placeholder="Middle Name">

          mname: $("#studentMname").val(),

          lname: $("#studentLname").val(),            </div>

          auxname: $("#studentAuxname").val(),

          gender: $("#studentGender").val(),          </div>                    </div>                <div class="form-section">                <div class="form-section">

          birthdate: $("#studentBirthdate").val(),

          contact_number: $("#studentContact").val(),          

          email: $("#studentEmail").val(),

          user_type: 'student'          <div class="form-actions">                  </div>

        };

            <button type="button" class="btn-primary" id="confirmUpdate">Update User</button>

        if(!studentData.user_name.trim() || !studentData.password.trim() || !studentData.fname.trim() || !studentData.lname.trim() || !studentData.email.trim()) {

          openModal("ERROR", "Please fill in all required fields");            <button type="button" class="btn-secondary" onclick="closeUpdateModal()">Cancel</button>                  <div class="form-row">                  <h4>Personal Details</h4>                  <h4>Personal Details</h4>

          return;

        }          </div>



        saveUser(10, studentData, 'student');        </div>                    <div class="form-group">

      });

    });      </div>

  </script>

      </div>                      <label for="studentLname">Last Name</label>                  <div class="form-row">                  <div class="form-row">

  <!-- PWA Scripts -->

  <script src="../js/pwa-helper.js"></script>

</body>

</html>    <!-- DELETE MODAL -->                      <input type="text" id="studentLname" placeholder="Last Name" required>


    <div id="deleteUserModal" class="modal">

      <div class="modal-content modal-content-small">                    </div>                    <div class="form-group form-group-small">                    <div class="form-group form-group-small">

        <span class="modal-close" onclick="closeDeleteModal()">&times;</span>

        <h2>Delete User Confirmation</h2>                    <div class="form-group form-group-small">

        

        <div class="compact-form">                      <label for="studentAuxname">Suffix</label>                      <label for="subadminGender">Gender</label>                      <label for="adminGender">Gender</label>

          <input type="hidden" id="deleteUserId">

          <input type="hidden" id="deleteUserType">                      <input type="text" id="studentAuxname" placeholder="Jr., Sr.">

          

          <div class="form-section">                    </div>                      <select id="subadminGender" required>                      <select id="adminGender" required>

            <div class="form-row">

              <div class="form-group">                  </div>

                <label for="deleteUserInfo">User to Delete</label>

                <input type='text' id='deleteUserInfo' readonly>                </div>                        <option value="">Select</option>                        <option value="">Select</option>

              </div>

            </div>              </div>

            

            <div class="form-row">                        <option value="Male">Male</option>                        <option value="Male">Male</option>

              <div class="form-group">

                <label for="deleteReason">Reason for Deletion</label>              <!-- Right Column -->

                <textarea id='deleteReason' name='deleteReason' rows='4' placeholder="Enter reason for deletion..." required></textarea>

              </div>              <div class="form-column">                        <option value="Female">Female</option>                        <option value="Female">Female</option>

            </div>

          </div>                <!-- Personal Details -->

          

          <div class="form-actions">                <div class="form-section">                      </select>                      </select>

            <button type="button" class="btn-primary" id="confirmDelete">Confirm Delete</button>

            <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>                  <h4>Personal Details</h4>

          </div>

        </div>                  <div class="form-row">                    </div>                    </div>

      </div>

    </div>                    <div class="form-group form-group-small">

  </main>

                      <label for="studentGender">Gender</label>                    <div class="form-group">                    <div class="form-group">

  <script>

    // Modal functions                      <select id="studentGender" required>

    function openUpdateModal() {

      document.getElementById("updateUserModal").style.display = "flex";                        <option value="">Select</option>                      <label for="subadminBirthdate">Birthdate</label>                      <label for="adminBirthdate">Birthdate</label>

    }

                            <option value="Male">Male</option>

    function closeUpdateModal() {

      document.getElementById("updateUserModal").style.display = "none";                        <option value="Female">Female</option>                      <input type="date" id="subadminBirthdate" required>                      <input type="date" id="adminBirthdate" required>

    }

                      </select>

    function openDeleteModal(){

      document.getElementById("deleteUserModal").style.display = "flex";                    </div>                    </div>                    </div>

    }

                    <div class="form-group">

    function closeDeleteModal(){

      document.getElementById("deleteUserModal").style.display = "none";                      <label for="studentBirthdate">Birthdate</label>                  </div>                  </div>

    }

                      <input type="date" id="studentBirthdate" required>

    // Clear form function

    function clearForm(type) {                    </div>                </div>                </div>

      const fields = ['Username', 'Password', 'Fname', 'Mname', 'Lname', 'Auxname', 'Gender', 'Birthdate', 'Contact', 'Email'];

      fields.forEach(field => {                  </div>

        const element = document.getElementById(type + field);

        if (element) {                </div>

          element.value = '';

        }

      });

    }                <!-- Contact Information -->                <!-- Contact Information -->                <!-- Contact Information -->



    // Tab switcher                <div class="form-section">

    const tabLinks = document.querySelectorAll(".tab-link");

    const tabContents = document.querySelectorAll(".tab-content");                  <h4>Contact Information</h4>                <div class="form-section">                <div class="form-section">



    tabLinks.forEach(link => {                  <div class="form-row">

      link.addEventListener("click", () => {

        tabLinks.forEach(l => l.classList.remove("active"));                    <div class="form-group">                  <h4>Contact Information</h4>                  <h4>Contact Information</h4>

        tabContents.forEach(c => c.classList.remove("active"));

                      <label for="studentContact">Contact Number</label>

        link.classList.add("active");

        const targetTab = document.getElementById(link.dataset.tab);                      <input type="text" id="studentContact" placeholder="Contact Number">                  <div class="form-row">                  <div class="form-row">

        if (targetTab) {

          targetTab.classList.add("active");                    </div>

        }

      });                  </div>                    <div class="form-group">                    <div class="form-group">

    });

  </script>                  <div class="form-row">



  <?php require_once("modal.php");?>                    <div class="form-group">                      <label for="subadminContact">Contact Number</label>                      <label for="adminContact">Contact Number</label>



  <script>                      <label for="studentEmail">Email</label>

    $(document).ready(function(){

      let subadminTable;                      <input type="email" id="studentEmail" placeholder="Email Address" required>                      <input type="text" id="subadminContact" placeholder="Contact Number">                      <input type="text" id="adminContact" placeholder="Contact Number">

      let studentTable;

                    </div>

      // DataTable initialization for Subadmins

      function initSubadminTable(){                  </div>                    </div>                    </div>

        subadminTable = $("#subadminTable").DataTable({

          ajax:{                </div>

            url:'ajax.php',

            type:'post',              </div>                  </div>                  </div>

            data:{

              CALL: 8            </div>

            },

            dataSrc: 'data',                  <div class="form-row">                  <div class="form-row">

            dataType:'json',

            error: function(xhr, error, thrown) {            <div class="form-actions">

              console.log('Subadmin DataTable AJAX Error:', error);

              console.log('Response:', xhr.responseText);              <button type="button" class="btn-primary" id="saveStudent">Save Student Member</button>                    <div class="form-group">                    <div class="form-group">

              openModal("ERROR", "Failed to load subadmin users: " + error);

            }              <button type="button" class="btn-secondary" onclick="clearForm('student')">Clear</button>

          },

          responsive: true,            </div>                      <label for="subadminEmail">Email</label>                      <label for="adminEmail">Email</label>

          scroll: '50vh',

          scrollCollapse: true,           </div>

          paging: true,

          columns: [                                <input type="email" id="subadminEmail" placeholder="Email Address" required>                      <input type="email" id="adminEmail" placeholder="Email Address" required>

            { data: "user_id" },

            { data: "user_name" },          <div class="table-container">

            { 

              data: null,            <table class='data-table' id='studentTable'>                    </div>                    </div>

              render: function(data, type, row) {

                return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;              <thead>

              }

            },                <tr>                  </div>                  </div>

            { data: "email" },

            { data: "contact_number" },                  <th>ID</th>

            { 

              data: 'user_id',                  <th>Username</th>                </div>                </div>

              render: function(data, type, row) {

                return `                  <th>Full Name</th>

                  <button class="btn-secondary updateBtn" data-id="${data}" data-type="subadmin">Edit</button>

                  <button class="btn-primary deleteBtn" data-id="${data}" data-type="subadmin">Delete</button>                  <th>Email</th>              </div>              </div>

                `;

              }                  <th>Contact</th>

            }

          ],                  <th>Actions</th>            </div>            </div>

          columnDefs: [

            { targets: 0, visible: false, searchable: false }                 </tr>

          ]

        });              </thead>

      }

              <tbody></tbody>

      // DataTable initialization for Students

      function initStudentTable(){            </table>            <div class="form-actions">            <div class="form-actions">

        studentTable = $("#studentTable").DataTable({

          ajax:{          </div>

            url:'ajax.php',

            type:'post',        </div>              <button type="button" class="btn-primary" id="saveSubadmin">Save Subadmin</button>              <button type="button" class="btn-primary" id="saveAdmin">Save Administrator</button>

            data:{

              CALL: 9      </div>

            },

            dataSrc: 'data',    </section>              <button type="button" class="btn-secondary" onclick="clearForm('subadmin')">Clear</button>              <button type="button" class="btn-secondary" onclick="clearForm('admin')">Clear</button>

            dataType:'json',

            error: function(xhr, error, thrown) {

              console.log('Student DataTable AJAX Error:', error);

              console.log('Response:', xhr.responseText);    <!-- UPDATE MODAL -->            </div>            </div>

              openModal("ERROR", "Failed to load student users: " + error);

            }    <div id="updateUserModal" class="modal">

          },

          responsive: true,      <div class="modal-content">          </div>          </div>

          scroll: '50vh',

          scrollCollapse: true,         <span class="modal-close" onclick="closeUpdateModal()">&times;</span>

          paging: true,

          columns: [        <h2>Update User Information</h2>                    

            { data: "user_id" },

            { data: "user_name" },        

            { 

              data: null,        <div class="compact-form">          <div class="table-container">          <div class="table-container">

              render: function(data, type, row) {

                return `${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}${row.auxname ? ' ' + row.auxname : ''}`;          <input type="hidden" id="updateUserId">

              }

            },          <input type="hidden" id="updateUserType">            <table class='data-table' id='subadminTable'>            <table class='data-table' id='adminTable'>

            { data: "email" },

            { data: "contact_number" },          

            { 

              data: 'user_id',          <div class="form-columns">              <thead>              <thead>

              render: function(data, type, row) {

                return `            <!-- Left Column -->

                  <button class="btn-secondary updateBtn" data-id="${data}" data-type="student">Edit</button>

                  <button class="btn-primary deleteBtn" data-id="${data}" data-type="student">Delete</button>            <div class="form-column">                <tr>                <tr>

                `;

              }              <!-- Account Information -->

            }

          ],              <div class="form-section">                  <th>ID</th>                  <th>ID</th>

          columnDefs: [

            { targets: 0, visible: false, searchable: false }                 <h4>Account Information</h4>

          ]

        });                <div class="form-row">                  <th>Username</th>                  <th>Username</th>

      }

                  <div class="form-group">

      // Initialize all tables

      initSubadminTable();                    <label for="updateUsername">Username</label>                  <th>Full Name</th>                  <th>Full Name</th>

      initStudentTable();

                    <input type="text" id="updateUsername" required>

      // Reload all tables

      function reloadAllTables(){                  </div>                  <th>Email</th>                  <th>Email</th>

        subadminTable.ajax.reload(null, false);

        studentTable.ajax.reload(null, false);                </div>

      }

                <div class="form-row">                  <th>Contact</th>                  <th>Contact</th>

      // Save user function

      function saveUser(call, userData, userType) {                  <div class="form-group">

        $.ajax({

          url: 'ajax.php',                    <label for="updatePassword">Password</label>                  <th>Actions</th>                  <th>Actions</th>

          type: 'post',

          data: {                    <input type="password" id="updatePassword" placeholder="Leave empty to keep current">

            CALL: call,

            DATA: userData                  </div>                </tr>                </tr>

          },

          dataType: 'json',                </div>

          success: function(result) {

            openModal(result.status, result.msg);              </div>              </thead>              </thead>

            if(result.status == "SUCCESS") {

              clearForm(userType);              

            }

          },              <!-- Personal Information -->              <tbody></tbody>              <tbody></tbody>

          complete: function() {

            reloadAllTables();              <div class="form-section">

          }

        });                <h4>Personal Information</h4>            </table>            </table>

      }

                <div class="form-row">

      // Update and Delete button handlers

      $(document).on("click", ".updateBtn", function() {                  <div class="form-group">          </div>          </div>

        const userId = $(this).data('id');

        const userType = $(this).data('type');                    <label for="updateFname">First Name</label>

        

        $.ajax({                    <input type="text" id="updateFname" required>        </div>        </div>

          url: 'ajax.php',

          type: 'post',                  </div>

          data: {

            CALL: 13,                  <div class="form-group">

            USER_ID: userId

          },                    <label for="updateMname">Middle Name</label>

          dataType: 'json',

          success: function(result) {                    <input type="text" id="updateMname">        <!-- TAB CONTENT: STUDENTS -->        <!-- TAB CONTENT: ADVISERS -->

            if(result.status === "SUCCESS") {

              const user = result.data;                  </div>

              $("#updateUserId").val(user.user_id);

              $("#updateUserType").val(userType);                </div>        <div class="tab-content" id="students">        <div class="tab-content" id="advisers">

              $("#updateUsername").val(user.user_name);

              $("#updateFname").val(user.fname);                <div class="form-row">

              $("#updateMname").val(user.mname);

              $("#updateLname").val(user.lname);                  <div class="form-group">          <div class="compact-form">          <div class="compact-form">

              $("#updateAuxname").val(user.auxname);

              $("#updateGender").val(user.gender);                    <label for="updateLname">Last Name</label>

              $("#updateBirthdate").val(user.birthdate);

              $("#updateContact").val(user.contact_number);                    <input type="text" id="updateLname" required>            <h3>Add New Student Member</h3>            <h3>Add New UASG Adviser</h3>

              $("#updateEmail").val(user.email);

              openUpdateModal();                  </div>

            }

          }                  <div class="form-group form-group-small">                        

        });

      });                    <label for="updateAuxname">Suffix</label>



      $(document).on("click", ".deleteBtn", function() {                    <input type="text" id="updateAuxname">            <div class="form-columns">            <div class="form-columns">

        const userId = $(this).data('id');

        const userType = $(this).data('type');                  </div>

        const userName = $(this).closest("tr").find("td").eq(1).text();

        const fullName = $(this).closest("tr").find("td").eq(2).text();                </div>              <!-- Left Column -->              <!-- Left Column -->

        

        $("#deleteUserId").val(userId);              </div>

        $("#deleteUserType").val(userType);

        $("#deleteUserInfo").val(`${userName} (${fullName})`);            </div>              <div class="form-column">              <div class="form-column">

        openDeleteModal();

      });            



      // Confirm update            <!-- Right Column -->                <!-- Account Information -->                <!-- Account Information -->

      $("#confirmUpdate").click(function() {

        const updateData = {            <div class="form-column">

          user_id: $("#updateUserId").val(),

          user_name: $("#updateUsername").val(),              <!-- Personal Details -->                <div class="form-section">                <div class="form-section">

          password: $("#updatePassword").val(),

          fname: $("#updateFname").val(),              <div class="form-section">

          mname: $("#updateMname").val(),

          lname: $("#updateLname").val(),                <h4>Personal Details</h4>                  <h4>Account Information</h4>                  <h4>Account Information</h4>

          auxname: $("#updateAuxname").val(),

          gender: $("#updateGender").val(),                <div class="form-row">

          birthdate: $("#updateBirthdate").val(),

          contact_number: $("#updateContact").val(),                  <div class="form-group form-group-small">                  <div class="form-row">                  <div class="form-row">

          email: $("#updateEmail").val()

        };                    <label for="updateGender">Gender</label>



        $.ajax({                    <select id="updateGender" required>                    <div class="form-group">                    <div class="form-group">

          url: 'ajax.php',

          type: 'post',                      <option value="">Select</option>

          data: {

            CALL: 11,                      <option value="Male">Male</option>                      <label for="studentUsername">Username</label>                      <label for="adviserUsername">Username</label>

            DATA: updateData

          },                      <option value="Female">Female</option>

          dataType: 'json',

          success: function(result) {                    </select>                      <input type="text" id="studentUsername" placeholder="Username" required>                      <input type="text" id="adviserUsername" placeholder="Username" required>

            openModal(result.status, result.msg);

            if(result.status === "SUCCESS") {                  </div>

              closeUpdateModal();

            }                  <div class="form-group">                    </div>                    </div>

          },

          complete: function() {                    <label for="updateBirthdate">Birthdate</label>

            reloadAllTables();

          }                    <input type="date" id="updateBirthdate" required>                  </div>                  </div>

        });

      });                  </div>



      // Confirm delete                </div>                  <div class="form-row">                  <div class="form-row">

      $("#confirmDelete").click(function() {

        const deleteData = {              </div>

          user_id: $("#deleteUserId").val(),

          reason: $("#deleteReason").val()                                  <div class="form-group">                    <div class="form-group">

        };

              <!-- Contact Information -->

        if(!deleteData.reason.trim()) {

          openModal("ERROR", "Please provide a reason for deletion");              <div class="form-section">                      <label for="studentPassword">Password</label>                      <label for="adviserPassword">Password</label>

          return;

        }                <h4>Contact Information</h4>



        $.ajax({                <div class="form-row">                      <input type="password" id="studentPassword" placeholder="Password" required>                      <input type="password" id="adviserPassword" placeholder="Password" required>

          url: 'ajax.php',

          type: 'post',                  <div class="form-group">

          data: {

            CALL: 12,                    <label for="updateContact">Contact Number</label>                    </div>                    </div>

            DATA: deleteData

          },                    <input type="text" id="updateContact">

          dataType: 'json',

          success: function(result) {                  </div>                  </div>                  </div>

            openModal(result.status, result.msg);

            if(result.status === "SUCCESS") {                </div>

              closeDeleteModal();

              $("#deleteReason").val('');                <div class="form-row">                </div>                </div>

            }

          },                  <div class="form-group">

          complete: function() {

            reloadAllTables();                    <label for="updateEmail">Email</label>

          }

        });                    <input type="email" id="updateEmail" required>

      });

                  </div>                <!-- Personal Information -->                <!-- Personal Information -->

      // Save Subadmin button handler

      $("#saveSubadmin").click(function() {                </div>

        const subadminData = {

          user_name: $("#subadminUsername").val(),              </div>                <div class="form-section">                <div class="form-section">

          password: $("#subadminPassword").val(),

          fname: $("#subadminFname").val(),            </div>

          mname: $("#subadminMname").val(),

          lname: $("#subadminLname").val(),          </div>                  <h4>Personal Information</h4>                  <h4>Personal Information</h4>

          auxname: $("#subadminAuxname").val(),

          gender: $("#subadminGender").val(),          

          birthdate: $("#subadminBirthdate").val(),

          contact_number: $("#subadminContact").val(),          <div class="form-actions">                  <div class="form-row">                  <div class="form-row">

          email: $("#subadminEmail").val(),

          user_type: 'adviser'            <button type="button" class="btn-primary" id="confirmUpdate">Update User</button>

        };

            <button type="button" class="btn-secondary" onclick="closeUpdateModal()">Cancel</button>                    <div class="form-group">                    <div class="form-group">

        if(!subadminData.user_name.trim() || !subadminData.password.trim() || !subadminData.fname.trim() || !subadminData.lname.trim() || !subadminData.email.trim()) {

          openModal("ERROR", "Please fill in all required fields");          </div>

          return;

        }        </div>                      <label for="studentFname">First Name</label>                      <label for="adviserFname">First Name</label>



        saveUser(10, subadminData, 'subadmin');      </div>

      });

    </div>                      <input type="text" id="studentFname" placeholder="First Name" required>                      <input type="text" id="adviserFname" placeholder="First Name" required>

      // Save Student button handler

      $("#saveStudent").click(function() {

        const studentData = {

          user_name: $("#studentUsername").val(),    <!-- DELETE MODAL -->                    </div>                    </div>

          password: $("#studentPassword").val(),

          fname: $("#studentFname").val(),    <div id="deleteUserModal" class="modal">

          mname: $("#studentMname").val(),

          lname: $("#studentLname").val(),      <div class="modal-content modal-content-small">                    <div class="form-group">                    <div class="form-group">

          auxname: $("#studentAuxname").val(),

          gender: $("#studentGender").val(),        <span class="modal-close" onclick="closeDeleteModal()">&times;</span>

          birthdate: $("#studentBirthdate").val(),

          contact_number: $("#studentContact").val(),        <h2>Delete User Confirmation</h2>                      <label for="studentMname">Middle Name</label>                      <label for="adviserMname">Middle Name</label>

          email: $("#studentEmail").val(),

          user_type: 'student'        

        };

        <div class="compact-form">                      <input type="text" id="studentMname" placeholder="Middle Name">                      <input type="text" id="adviserMname" placeholder="Middle Name">

        if(!studentData.user_name.trim() || !studentData.password.trim() || !studentData.fname.trim() || !studentData.lname.trim() || !studentData.email.trim()) {

          openModal("ERROR", "Please fill in all required fields");          <input type="hidden" id="deleteUserId">

          return;

        }          <input type="hidden" id="deleteUserType">                    </div>                    </div>



        saveUser(10, studentData, 'student');          

      });

    });          <div class="form-group">                  </div>                  </div>

  </script>

              <label for="deleteUserInfo">User to Delete</label>

  <!-- PWA Scripts -->

  <script src="../js/pwa-helper.js"></script>            <input type='text' id='deleteUserInfo' readonly>                  <div class="form-row">                  <div class="form-row">

</body>

</html>          </div>


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