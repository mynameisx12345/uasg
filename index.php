<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UASG - Sign in</title>
    
    <link rel="stylesheet" href="resources/style.css">
    <link rel="stylesheet" href="resources/theme-overrides.css">
    <script src="js/jquery.js"></script>
    <style>
      .login-page {
        min-height:100vh; display:flex; align-items:center; justify-content:center;
        background: linear-gradient(135deg, #1a0a0e 0%, #2d0f18 30%, #4a1525 60%, #1e293b 100%);
        position:relative; overflow:hidden; padding:20px;
      }
      .login-page::before {
        content:''; position:absolute; inset:0;
        background: radial-gradient(ellipse at 20% 50%, rgba(123,18,40,0.15) 0%, transparent 60%),
                    radial-gradient(ellipse at 80% 20%, rgba(200,155,46,0.08) 0%, transparent 50%);
        animation: bgShift 12s ease-in-out infinite alternate;
      }
      @keyframes bgShift {
        0% { opacity:0.7; transform:scale(1); }
        100% { opacity:1; transform:scale(1.05); }
      }
      .login-card {
        position:relative; z-index:1; width:100%; max-width:380px;
        background: rgba(255,255,255,0.06);
        backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius:20px; padding:40px 36px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.1);
        animation: cardIn 0.6s cubic-bezier(0.16,1,0.3,1) both;
      }
      @keyframes cardIn {
        from { opacity:0; transform:translateY(30px) scale(0.96); }
        to { opacity:1; transform:translateY(0) scale(1); }
      }
      .login-logo { text-align:center; margin-bottom:28px; }
      .login-logo img {
        width:100%; max-width:180px; max-height:120px; object-fit:contain;
        filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
        animation: logoFloat 4s ease-in-out infinite;
      }
      @keyframes logoFloat {
        0%,100% { transform:translateY(0); }
        50% { transform:translateY(-4px); }
      }
      .login-logo p {
        color:rgba(255,255,255,0.7); font-size:13px; margin-top:8px; font-weight:400;
        letter-spacing:0.5px;
      }
      .login-card h2 {
        color:#fff; font-size:22px; font-weight:600; text-align:center;
        margin:0 0 24px; letter-spacing:-0.3px;
      }
      .login-field { margin-bottom:18px; }
      .login-field label {
        display:block; font-size:13px; font-weight:500; color:rgba(255,255,255,0.75);
        margin-bottom:6px;
      }
      .login-field input {
        width:100%; padding:12px 14px; font-size:14px; color:#fff;
        background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.15);
        border-radius:10px; outline:none; transition:all 0.25s;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
      }
      .login-field input::placeholder { color:rgba(255,255,255,0.35); }
      .login-field input:focus {
        border-color:rgba(200,155,46,0.6);
        box-shadow: 0 0 0 3px rgba(200,155,46,0.15), inset 0 1px 3px rgba(0,0,0,0.2);
        background:rgba(255,255,255,0.1);
      }
      .password-wrap { position:relative; }
      .password-wrap .toggle-pw {
        position:absolute; right:12px; top:50%; transform:translateY(-50%);
        background:none; border:none; cursor:pointer; font-size:16px; color:rgba(255,255,255,0.5);
        padding:0; width:auto; transition:color 0.2s;
      }
      .password-wrap .toggle-pw:hover { color:rgba(255,255,255,0.8); }
      .show-pw-label {
        display:flex; align-items:center; gap:6px; margin-top:8px;
        font-size:12px; color:rgba(255,255,255,0.5); cursor:pointer; font-weight:400;
      }
      .show-pw-label input { width:auto; margin:0; }
      .login-error {
        background:rgba(248,81,73,0.15); border:1px solid rgba(248,81,73,0.4);
        border-radius:8px; color:#ff8a84; font-size:13px; padding:10px 14px;
        margin:14px 0; display:none;
      }
      .login-success {
        background:rgba(46,160,67,0.12); border:1px solid rgba(46,160,67,0.35);
        border-radius:8px; color:#7ee787; font-size:13px; padding:10px 14px; margin-bottom:16px;
      }
      .login-btn {
        width:100%; padding:13px; font-size:15px; font-weight:600; color:#fff; border:none;
        border-radius:10px; cursor:pointer; margin-top:6px;
        background: linear-gradient(135deg, #7b1228 0%, #9c1530 50%, #7b1228 100%);
        background-size:200% 200%; animation: btnGlow 3s ease infinite;
        box-shadow: 0 4px 15px rgba(123,18,40,0.4);
        transition: transform 0.2s, box-shadow 0.2s;
      }
      .login-btn:hover:not(:disabled) {
        transform:translateY(-2px);
        box-shadow: 0 8px 25px rgba(123,18,40,0.5);
      }
      .login-btn:active:not(:disabled) { transform:translateY(0); }
      .login-btn:disabled { opacity:0.6; cursor:not-allowed; transform:none; }
      @keyframes btnGlow {
        0%,100% { background-position:0% 50%; }
        50% { background-position:100% 50%; }
      }
      .login-footer {
        text-align:center; margin-top:24px; padding-top:18px;
        border-top:1px solid rgba(255,255,255,0.08);
      }
      .login-footer p { font-size:12px; color:rgba(255,255,255,0.4); margin:0; }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-card">
            <div class="login-logo">
                <img src="images/uasglogo.png" alt="UASG Logo">
                <p>UASG Repository System</p>
            </div>
            
            <h2>Sign in to your account</h2>
            
            <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
            <div class="login-success">✓ You have been successfully logged out.</div>
            <?php endif; ?>
            
            <form id="loginForm">
                <div class="login-field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="username">
                </div>
                
                <div class="login-field">
                    <label for="password">Password</label>
                    <div class="password-wrap">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                        <button type="button" class="toggle-pw" id="togglePassword">👁️</button>
                    </div>
                    <label class="show-pw-label">
                        <input type="checkbox" id="showPassword"> Show password
                    </label>
                </div>
                
                <div id="loginError" class="login-error"></div>
                
                <button type="submit" class="login-btn">Sign in</button>
            </form>
            
            <div class="login-footer">
                <p>&copy; <?= date('Y') ?> UASG Portal</p>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#showPassword, #togglePassword').on('change click', function() {
                const pw = $('#password');
                if (pw.attr('type') === 'password') {
                    pw.attr('type', 'text');
                    $('#togglePassword').text('🙈');
                    $('#showPassword').prop('checked', true);
                } else {
                    pw.attr('type', 'password');
                    $('#togglePassword').text('👁️');
                    $('#showPassword').prop('checked', false);
                }
            });
            
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                var username = $('#username').val().trim();
                var password = $('#password').val();
                if (!username || !password) { showError('Please enter both username and password'); return; }
                
                $('.login-btn').text('Signing in...').prop('disabled', true);
                
                $.ajax({
                    url: 'auth.php', type: 'POST',
                    data: { action: 'login', username: username, password: password },
                    dataType: 'json',
                    success: function(r) {
                        if (r.success) { window.location.href = r.redirect_url; }
                        else { showError(r.message || 'Incorrect username or password.'); $('.login-btn').text('Sign in').prop('disabled', false); }
                    },
                    error: function() {
                        showError('Unable to sign in. Please try again.');
                        $('.login-btn').text('Sign in').prop('disabled', false);
                    }
                });
            });
            
            function showError(msg) {
                $('#loginError').text(msg).show();
                setTimeout(function() { $('#loginError').fadeOut(); }, 5000);
            }
        });
    </script>
</body>
</html>
