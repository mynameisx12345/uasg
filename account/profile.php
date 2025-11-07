<?php
require_once '../resources/session.php';
require_once '../resources/objects/main_class.php';

$session = new SessionManager();

// Require authenticated user (any role can access account management)
if (!$session->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$userData = $session->getUserData();
$mainClass = new main_class();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management - UASG</title>
    <link rel="stylesheet" href="../resources/style.css">
    <script src="../js/jquery.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>Account Management</h1>
                <div class="subtitle">Manage your profile and account settings</div>
            </div>
            
            <div class="dashboard-nav">
                <button class="nav-link active" data-section="profile">
                    <i class="fas fa-user"></i> Profile
                </button>
                <button class="nav-link" data-section="security">
                    <i class="fas fa-shield-alt"></i> Security
                </button>
                <button class="nav-link" data-section="preferences">
                    <i class="fas fa-cog"></i> Preferences
                </button>
                
                <div class="user-dropdown">
                    <div class="user-avatar"><?php echo strtoupper(substr($userData['fname'], 0, 1)); ?></div>
                    <span class="user-name"><?php echo htmlspecialchars($userData['fname']); ?></span>
                    <button class="logout-btn" onclick="logout()">Logout</button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="dashboard-content">
            <!-- Profile Section -->
            <div id="profile" class="content-section active">
                <div class="section-header">
                    <h2 class="section-title">Profile Information</h2>
                </div>

                <div class="data-card">
                    <div class="card-header">
                        <h3 class="card-title">Personal Information</h3>
                        <div class="card-actions">
                            <button class="btn btn-primary" onclick="editProfile()">Edit Profile</button>
                        </div>
                    </div>
                    
                    <div id="profileDisplay">
                        <div class="profile-info">
                            <div class="profile-avatar">
                                <div class="avatar-large"><?php echo strtoupper(substr($userData['fname'], 0, 1)); ?></div>
                                <button class="btn btn-secondary btn-small" onclick="changeAvatar()">Change Avatar</button>
                            </div>
                            
                            <div class="profile-details">
                                <div class="info-grid">
                                    <div class="info-item">
                                        <strong>First Name:</strong>
                                        <span id="displayFirstName"><?php echo htmlspecialchars($userData['fname']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <strong>Last Name:</strong>
                                        <span id="displayLastName"><?php echo htmlspecialchars($userData['lname']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <strong>Email:</strong>
                                        <span id="displayEmail"><?php echo htmlspecialchars($userData['email']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <strong>Username:</strong>
                                        <span id="displayUsername"><?php echo htmlspecialchars($userData['username']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <strong>User Type:</strong>
                                        <span class="status success"><?php echo strtoupper($userData['user_type']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <strong>Contact Number:</strong>
                                        <span id="displayContact"><?php echo htmlspecialchars($userData['contact'] ?? 'Not set'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <strong>Address:</strong>
                                        <span id="displayAddress"><?php echo htmlspecialchars($userData['address'] ?? 'Not set'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <strong>Department:</strong>
                                        <span id="displayDepartment"><?php echo htmlspecialchars($userData['department'] ?? 'Not set'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Edit Form (hidden by default) -->
                    <form id="profileEditForm" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="editFirstName">First Name *</label>
                                <input type="text" id="editFirstName" name="fname" value="<?php echo htmlspecialchars($userData['fname']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="editLastName">Last Name *</label>
                                <input type="text" id="editLastName" name="lname" value="<?php echo htmlspecialchars($userData['lname']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="editEmail">Email *</label>
                                <input type="email" id="editEmail" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="editContact">Contact Number</label>
                                <input type="text" id="editContact" name="contact" value="<?php echo htmlspecialchars($userData['contact'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="editAddress">Address</label>
                            <textarea id="editAddress" name="address" rows="3"><?php echo htmlspecialchars($userData['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="editDepartment">Department</label>
                            <input type="text" id="editDepartment" name="department" value="<?php echo htmlspecialchars($userData['department'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="cancelProfileEdit()">Cancel</button>
                            <button type="button" class="btn-primary" onclick="saveProfile()">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Section -->
            <div id="security" class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Security Settings</h2>
                </div>

                <!-- Change Password -->
                <div class="data-card">
                    <div class="card-header">
                        <h3 class="card-title">Change Password</h3>
                    </div>
                    
                    <form id="changePasswordForm">
                        <div class="form-group">
                            <label for="currentPassword">Current Password *</label>
                            <input type="password" id="currentPassword" name="current_password" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="newPassword">New Password *</label>
                                <input type="password" id="newPassword" name="new_password" required minlength="6">
                                <small class="form-text">Minimum 6 characters</small>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm New Password *</label>
                                <input type="password" id="confirmPassword" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn-primary" onclick="changePassword()">Change Password</button>
                        </div>
                    </form>
                </div>

                <!-- Login Activity -->
                <div class="data-card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Login Activity</h3>
                        <div class="card-actions">
                            <button class="btn btn-secondary" onclick="loadLoginActivity()">Refresh</button>
                        </div>
                    </div>
                    
                    <div id="loginActivityList">
                        <div class="activity-item">
                            <div class="activity-info">
                                <strong>Current Session</strong>
                                <div class="activity-details">
                                    <span>Browser: <?php echo $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'; ?></span>
                                    <span>IP: <?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?></span>
                                    <span>Started: <?php echo date('Y-m-d H:i:s'); ?></span>
                                </div>
                            </div>
                            <div class="activity-status">
                                <span class="status success">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preferences Section -->
            <div id="preferences" class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Preferences</h2>
                </div>

                <!-- Notification Settings -->
                <div class="data-card">
                    <div class="card-header">
                        <h3 class="card-title">Notification Settings</h3>
                    </div>
                    
                    <form id="preferencesForm">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="emailNotifications" name="email_notifications" checked>
                                Email Notifications
                            </label>
                            <small class="form-text">Receive email notifications for important updates</small>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="taskReminders" name="task_reminders" checked>
                                Task Deadline Reminders
                            </label>
                            <small class="form-text">Get reminded about upcoming task deadlines</small>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="submissionUpdates" name="submission_updates" checked>
                                Submission Status Updates
                            </label>
                            <small class="form-text">Notifications when submissions are reviewed</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="reminderTime">Reminder Time (hours before deadline)</label>
                            <select id="reminderTime" name="reminder_time">
                                <option value="24">24 hours</option>
                                <option value="48" selected>48 hours</option>
                                <option value="72">72 hours</option>
                                <option value="168">1 week</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn-primary" onclick="savePreferences()">Save Preferences</button>
                        </div>
                    </form>
                </div>

                <!-- Dashboard Settings -->
                <div class="data-card">
                    <div class="card-header">
                        <h3 class="card-title">Dashboard Settings</h3>
                    </div>
                    
                    <form id="dashboardSettingsForm">
                        <div class="form-group">
                            <label for="itemsPerPage">Items per page in tables</label>
                            <select id="itemsPerPage" name="items_per_page">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="defaultView">Default dashboard view</label>
                            <select id="defaultView" name="default_view">
                                <option value="dashboard" selected>Dashboard</option>
                                <option value="tasks">Tasks</option>
                                <option value="submissions">Submissions</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="autoRefresh" name="auto_refresh">
                                Auto-refresh dashboard data
                            </label>
                            <small class="form-text">Automatically refresh data every 30 seconds</small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn-primary" onclick="saveDashboardSettings()">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="js/account.js"></script>
    <script>
        // Global functions for PHP integration
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                $.ajax({
                    url: '../auth.php',
                    type: 'POST',
                    data: { action: 'logout' },
                    dataType: 'json',
                    success: function(response) {
                        window.location.href = '../login.php';
                    },
                    error: function() {
                        // Redirect to login even if logout fails
                        window.location.href = '../login.php';
                    }
                });
            }
        }

        // Navigation handling
        function showSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(sectionName).classList.add('active');
            document.querySelector(`.nav-link[data-section="${sectionName}"]`).classList.add('active');
        }

        // Navigation click handlers
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const section = this.getAttribute('data-section');
                    if (section) {
                        showSection(section);
                    }
                });
            });
        });
    </script>
</body>
</html>