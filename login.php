<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UASG - Sign in</title>
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
                        <input type="password" id="password" name="password" required autocomplete="current-password">
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
                        if (response.success) {
                            window.location.href = response.redirect_url;
                        } else {
                            showError(response.message || 'Incorrect username or password.');
                            $('.github-btn-primary').text('Sign in').prop('disabled', false);
                        }
                    },
                    error: function() {
                        showError('Unable to sign in. Please try again.');
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
</body>
</html>