<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UASG - Sign in</title>
    
    <!-- PWA Meta Tags -->
    <meta name="description" content="Sign in to University Student Government File Management System">
    <meta name="theme-color" content="#2196F3">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="UASG">
    <meta name="msapplication-TileColor" content="#2196F3">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Favicon and Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="resources/icons/icon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="resources/icons/icon-16x16.png">
    <link rel="apple-touch-icon" href="resources/icons/icon-152x152.png">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="resources/style.css">
    <script src="js/jquery.js"></script>
</head>
<body>
    <div class="github-login-container">
        <div class="github-login-box">
            <div class="github-logo">
                <h1>UASG</h1>
                <p>University Academic Student Government</p>
            </div>
            
            <div class="github-login-form">
                <h2>Sign in to your account</h2>
                
                <form id="loginForm">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required autocomplete="username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" required autocomplete="current-password">
                            <button type="button" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 14px; color: #8b949e; width: auto; padding: 0;">👁️</button>
                        </div>
                        <label style="font-weight: normal; margin-top: 8px; font-size: 12px; color: #7d8590;">
                            <input type="checkbox" id="showPassword" style="width: auto; margin-right: 5px;"> Show password
                        </label>
                    </div>
                    
                    <div id="loginError" class="github-error-message" style="display: none;"></div>
                    
                    <button type="submit" class="github-btn-primary">Sign in</button>
                </form>
            </div>
            
            <div class="github-footer">
                <p>© <?= date('Y') ?> UASG Portal</p>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Password visibility toggle
            $('#showPassword, #togglePassword').on('change click', function() {
                const passwordField = $('#password');
                const showPassword = $('#showPassword').is(':checked');
                
                if (showPassword || $(this).attr('id') === 'togglePassword') {
                    if (passwordField.attr('type') === 'password') {
                        passwordField.attr('type', 'text');
                        $('#togglePassword').text('🙈');
                        $('#showPassword').prop('checked', true);
                    } else {
                        passwordField.attr('type', 'password');
                        $('#togglePassword').text('👁️');
                        $('#showPassword').prop('checked', false);
                    }
                }
            });
            
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                const username = $('#username').val().trim();
                const password = $('#password').val();
                
                if (!username || !password) {
                    showError('Please enter both username and password');
                    return;
                }
                
                // Show loading state
                $('.github-btn-primary').text('Signing in...').prop('disabled', true);
                
                // Debug: Show what we're sending
                console.log('Login attempt:', {username: username, password: password});
                
                $.ajax({
                    url: 'auth.php',
                    type: 'POST',
                    data: {
                        action: 'login',
                        username: username,
                        password: password
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Login response:', response);
                        if (response.success) {
                            window.location.href = response.redirect_url;
                        } else {
                            showError(response.message || 'Incorrect username or password.');
                            $('.github-btn-primary').text('Sign in').prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error:', xhr.responseText);
                        showError('Unable to sign in. Please try again. Check console for details.');
                        $('.github-btn-primary').text('Sign in').prop('disabled', false);
                    }
                });
            });
            
            function showError(message) {
                $('#loginError').text(message).show();
                setTimeout(function() {
                    $('#loginError').fadeOut();
                }, 5000);
            }
        });
    </script>
    
    <!-- PWA Scripts -->
    <script src="js/pwa-helper.js"></script>
</body>
</html>