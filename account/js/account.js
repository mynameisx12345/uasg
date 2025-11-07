$(document).ready(function() {
    // Initialize the application
    initializeApp();
    
    function initializeApp() {
        loadUserPreferences();
        initializeEventHandlers();
    }
    
    function initializeEventHandlers() {
        // Form validation for password confirmation
        $('#confirmPassword').on('input', function() {
            const newPassword = $('#newPassword').val();
            const confirmPassword = $(this).val();
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // New password validation
        $('#newPassword').on('input', function() {
            const password = $(this).val();
            if (password.length < 6) {
                this.setCustomValidity('Password must be at least 6 characters long');
            } else {
                this.setCustomValidity('');
                // Trigger confirm password validation
                $('#confirmPassword').trigger('input');
            }
        });
    }
    
    // Profile management functions
    window.editProfile = function() {
        $('#profileDisplay').hide();
        $('#profileEditForm').show();
    };
    
    window.cancelProfileEdit = function() {
        $('#profileEditForm').hide();
        $('#profileDisplay').show();
        
        // Reset form to original values
        resetProfileForm();
    };
    
    function resetProfileForm() {
        // Reset form fields to current display values
        $('#editFirstName').val($('#displayFirstName').text());
        $('#editLastName').val($('#displayLastName').text());
        $('#editEmail').val($('#displayEmail').text());
        $('#editContact').val($('#displayContact').text() === 'Not set' ? '' : $('#displayContact').text());
        $('#editAddress').val($('#displayAddress').text() === 'Not set' ? '' : $('#displayAddress').text());
        $('#editDepartment').val($('#displayDepartment').text() === 'Not set' ? '' : $('#displayDepartment').text());
    }
    
    window.saveProfile = function() {
        const form = document.getElementById('profileEditForm');
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const formData = new FormData(form);
        const profileData = {};
        formData.forEach((value, key) => {
            profileData[key] = value;
        });
        
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: {
                CALL: 'update_profile',
                DATA: profileData
            },
            dataType: 'json',
            success: function(response) {
                showNotification(response.msg, response.status.toLowerCase());
                
                if (response.status === 'SUCCESS') {
                    // Update display values
                    $('#displayFirstName').text(profileData.fname);
                    $('#displayLastName').text(profileData.lname);
                    $('#displayEmail').text(profileData.email);
                    $('#displayContact').text(profileData.contact || 'Not set');
                    $('#displayAddress').text(profileData.address || 'Not set');
                    $('#displayDepartment').text(profileData.department || 'Not set');
                    
                    // Update avatar and user name in header
                    $('.user-avatar').text(profileData.fname.charAt(0).toUpperCase());
                    $('.user-name').text(profileData.fname);
                    
                    // Hide form and show display
                    cancelProfileEdit();
                }
            },
            error: function() {
                showNotification('Error updating profile. Please try again.', 'error');
            }
        });
    };
    
    window.changeAvatar = function() {
        showNotification('Avatar change functionality - to be implemented', 'info');
    };
    
    // Security functions
    window.changePassword = function() {
        const form = document.getElementById('changePasswordForm');
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const currentPassword = $('#currentPassword').val();
        const newPassword = $('#newPassword').val();
        const confirmPassword = $('#confirmPassword').val();
        
        // Verify passwords match
        if (newPassword !== confirmPassword) {
            showNotification('New passwords do not match', 'error');
            return;
        }
        
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: {
                CALL: 'change_password',
                DATA: {
                    current_password: currentPassword,
                    new_password: newPassword
                }
            },
            dataType: 'json',
            success: function(response) {
                showNotification(response.msg, response.status.toLowerCase());
                
                if (response.status === 'SUCCESS') {
                    // Clear form
                    form.reset();
                }
            },
            error: function() {
                showNotification('Error changing password. Please try again.', 'error');
            }
        });
    };
    
    window.loadLoginActivity = function() {
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: { CALL: 'get_login_activity' },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'SUCCESS') {
                    displayLoginActivity(response.data);
                } else {
                    showNotification('No login activity data available', 'info');
                }
            },
            error: function() {
                showNotification('Error loading login activity', 'error');
            }
        });
    };
    
    function displayLoginActivity(activities) {
        const container = $('#loginActivityList');
        
        // Keep current session item and add historical data
        const currentSession = container.find('.activity-item:first');
        container.empty().append(currentSession);
        
        if (activities && activities.length > 0) {
            activities.forEach(function(activity) {
                const activityDate = new Date(activity.login_time);
                const isCurrentSession = activity.is_current;
                
                container.append(`
                    <div class="activity-item">
                        <div class="activity-info">
                            <strong>${isCurrentSession ? 'Current Session' : 'Previous Session'}</strong>
                            <div class="activity-details">
                                <span>Browser: ${activity.user_agent || 'Unknown'}</span>
                                <span>IP: ${activity.ip_address || 'Unknown'}</span>
                                <span>Started: ${activityDate.toLocaleString()}</span>
                                ${activity.logout_time ? `<span>Ended: ${new Date(activity.logout_time).toLocaleString()}</span>` : ''}
                            </div>
                        </div>
                        <div class="activity-status">
                            <span class="status ${isCurrentSession ? 'success' : 'warning'}">
                                ${isCurrentSession ? 'Active' : 'Ended'}
                            </span>
                        </div>
                    </div>
                `);
            });
        }
    }
    
    // Preferences functions
    function loadUserPreferences() {
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: { CALL: 'get_user_preferences' },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'SUCCESS' && response.data) {
                    const prefs = response.data;
                    
                    // Set notification preferences
                    $('#emailNotifications').prop('checked', prefs.email_notifications == 1);
                    $('#taskReminders').prop('checked', prefs.task_reminders == 1);
                    $('#submissionUpdates').prop('checked', prefs.submission_updates == 1);
                    $('#reminderTime').val(prefs.reminder_time || '48');
                    
                    // Set dashboard preferences
                    $('#itemsPerPage').val(prefs.items_per_page || '10');
                    $('#defaultView').val(prefs.default_view || 'dashboard');
                    $('#autoRefresh').prop('checked', prefs.auto_refresh == 1);
                }
            }
        });
    }
    
    window.savePreferences = function() {
        const preferencesData = {
            email_notifications: $('#emailNotifications').is(':checked') ? 1 : 0,
            task_reminders: $('#taskReminders').is(':checked') ? 1 : 0,
            submission_updates: $('#submissionUpdates').is(':checked') ? 1 : 0,
            reminder_time: $('#reminderTime').val()
        };
        
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: {
                CALL: 'save_preferences',
                DATA: preferencesData
            },
            dataType: 'json',
            success: function(response) {
                showNotification(response.msg, response.status.toLowerCase());
            },
            error: function() {
                showNotification('Error saving preferences. Please try again.', 'error');
            }
        });
    };
    
    window.saveDashboardSettings = function() {
        const dashboardData = {
            items_per_page: $('#itemsPerPage').val(),
            default_view: $('#defaultView').val(),
            auto_refresh: $('#autoRefresh').is(':checked') ? 1 : 0
        };
        
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: {
                CALL: 'save_dashboard_settings',
                DATA: dashboardData
            },
            dataType: 'json',
            success: function(response) {
                showNotification(response.msg, response.status.toLowerCase());
            },
            error: function() {
                showNotification('Error saving dashboard settings. Please try again.', 'error');
            }
        });
    };
    
    // Utility functions
    function showNotification(message, type) {
        // Simple notification system
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 'alert-info';
        
        const notification = $(`
            <div class="alert ${alertClass} notification-toast">
                ${message}
                <button type="button" class="close" onclick="$(this).parent().remove()">
                    <span>&times;</span>
                </button>
            </div>
        `);
        
        $('body').append(notification);
        
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
});

// Global utility functions
window.showNotification = function(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 'alert-info';
    
    const notification = $(`
        <div class="alert ${alertClass} notification-toast">
            ${message}
            <button type="button" class="close" onclick="$(this).parent().remove()">
                <span>&times;</span>
            </button>
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(function() {
        notification.fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
};