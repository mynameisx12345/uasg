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
  <title>Account Security - UASG</title>
  
  <link rel="stylesheet" href="../resources/style.css">
  <style>
    .security-container {
      max-width: 600px;
      margin: 0 auto;
    }
    .security-section {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      margin-bottom: 1.5rem;
    }
    .security-section h3 {
      margin-top: 0;
      margin-bottom: 1.5rem;
      color: #1877f2;
      border-bottom: 2px solid #f0f2f5;
      padding-bottom: 0.5rem;
    }
    .alert {
      padding: 1rem;
      border-radius: 4px;
      margin-bottom: 1rem;
    }
    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .alert-error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    .password-strength {
      margin-top: 0.5rem;
      height: 4px;
      background: #e0e0e0;
      border-radius: 2px;
      overflow: hidden;
    }
    .password-strength-bar {
      height: 100%;
      transition: width 0.3s, background-color 0.3s;
    }
    .strength-weak { background: #dc3545; }
    .strength-medium { background: #ffc107; }
    .strength-strong { background: #28a745; }
    .password-requirements {
      font-size: 0.85rem;
      color: #666;
      margin-top: 0.5rem;
    }
    .password-requirements li {
      margin: 0.25rem 0;
    }
    .password-requirements li.met {
      color: #28a745;
    }
  </style>
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
</head>
<body>
  <!-- HEADER -->
  <?php require_once("header.php");?>
  <header class="topbar">
    <h1>Account Security</h1>
    <div class="user-info">
      <span>Welcome, <?php echo $_SESSION['user_name'] ?? 'User'; ?></span>
    </div>
  </header>
  
  <!-- MAIN -->
  <main class="main">
    <!-- SIDEBAR -->
    <?php require_once("sidebar.php");?>

    <!-- CONTENT -->
    <section class="content">
      <div class="security-container">
        
        <!-- CHANGE PASSWORD SECTION -->
        <div class="security-section">
          <h3>🔒 Change Password</h3>
          <div id="passwordMessage"></div>
          
          <form id="changePasswordForm">
            <div class="form-group">
              <label for="currentPassword">Current Password *</label>
              <input type="password" id="currentPassword" name="current_password" required>
            </div>
            
            <div class="form-group">
              <label for="newPassword">New Password *</label>
              <input type="password" id="newPassword" name="new_password" required>
              <div class="password-strength">
                <div class="password-strength-bar" id="strengthBar"></div>
              </div>
              <ul class="password-requirements">
                <li id="req-length">At least 8 characters</li>
                <li id="req-uppercase">Contains uppercase letter</li>
                <li id="req-lowercase">Contains lowercase letter</li>
                <li id="req-number">Contains number</li>
              </ul>
            </div>
            
            <div class="form-group">
              <label for="confirmPassword">Confirm New Password *</label>
              <input type="password" id="confirmPassword" name="confirm_password" required>
            </div>
            
            <div class="form-actions">
              <button type="submit" class="btn-primary">Update Password</button>
            </div>
          </form>
        </div>

        <!-- SECURITY QUESTIONS SECTION -->
        <div class="security-section">
          <h3>🛡️ Security Questions</h3>
          <p style="color:#666;margin-bottom:1.5rem;">Set up security questions to help recover your account if you forget your password.</p>
          <div id="securityMessage"></div>
          
          <form id="securityQuestionsForm">
            <div class="form-group">
              <label for="question1">Security Question 1 *</label>
              <select id="question1" name="question1" required>
                <option value="">Select a question...</option>
                <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                <option value="What city were you born in?">What city were you born in?</option>
                <option value="What is your favorite book?">What is your favorite book?</option>
                <option value="What was your childhood nickname?">What was your childhood nickname?</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="answer1">Answer 1 *</label>
              <input type="text" id="answer1" name="answer1" required>
            </div>
            
            <div class="form-group">
              <label for="question2">Security Question 2 *</label>
              <select id="question2" name="question2" required>
                <option value="">Select a question...</option>
                <option value="What is the name of your favorite teacher?">What is the name of your favorite teacher?</option>
                <option value="What is your favorite movie?">What is your favorite movie?</option>
                <option value="What street did you grow up on?">What street did you grow up on?</option>
                <option value="What is your favorite food?">What is your favorite food?</option>
                <option value="What is your dream job?">What is your dream job?</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="answer2">Answer 2 *</label>
              <input type="text" id="answer2" name="answer2" required>
            </div>
            
            <div class="form-actions">
              <button type="submit" class="btn-primary">Save Security Questions</button>
            </div>
          </form>
        </div>

        <!-- RECOVERY EMAIL SECTION -->
        <div class="security-section">
          <h3>📧 Recovery Email</h3>
          <p style="color:#666;margin-bottom:1.5rem;">Add a recovery email address to help reset your password.</p>
          <div id="recoveryMessage"></div>
          
          <form id="recoveryEmailForm">
            <div class="form-group">
              <label for="recoveryEmail">Recovery Email Address *</label>
              <input type="email" id="recoveryEmail" name="recovery_email" required>
            </div>
            
            <div class="form-actions">
              <button type="submit" class="btn-primary">Update Recovery Email</button>
            </div>
          </form>
        </div>

      </div>
    </section>
  </main>

<script>
(function($) {
    
    // Password strength checker
    $('#newPassword').on('input', function() {
        const password = $(this).val();
        let strength = 0;
        
        // Check length
        if (password.length >= 8) {
            strength += 25;
            $('#req-length').addClass('met');
        } else {
            $('#req-length').removeClass('met');
        }
        
        // Check uppercase
        if (/[A-Z]/.test(password)) {
            strength += 25;
            $('#req-uppercase').addClass('met');
        } else {
            $('#req-uppercase').removeClass('met');
        }
        
        // Check lowercase
        if (/[a-z]/.test(password)) {
            strength += 25;
            $('#req-lowercase').addClass('met');
        } else {
            $('#req-lowercase').removeClass('met');
        }
        
        // Check number
        if (/[0-9]/.test(password)) {
            strength += 25;
            $('#req-number').addClass('met');
        } else {
            $('#req-number').removeClass('met');
        }
        
        // Update strength bar
        const $bar = $('#strengthBar');
        $bar.css('width', strength + '%');
        
        if (strength < 50) {
            $bar.removeClass('strength-medium strength-strong').addClass('strength-weak');
        } else if (strength < 100) {
            $bar.removeClass('strength-weak strength-strong').addClass('strength-medium');
        } else {
            $bar.removeClass('strength-weak strength-medium').addClass('strength-strong');
        }
    });

    // Change Password Form
    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();
        
        const newPassword = $('#newPassword').val();
        const confirmPassword = $('#confirmPassword').val();
        
        if (newPassword !== confirmPassword) {
            $('#passwordMessage').html('<div class="alert alert-error">Passwords do not match!</div>');
            return;
        }
        
        if (newPassword.length < 8) {
            $('#passwordMessage').html('<div class="alert alert-error">Password must be at least 8 characters long!</div>');
            return;
        }
        
        $.post('ajax.php', {
            CALL: 58,
            current_password: $('#currentPassword').val(),
            new_password: newPassword
        }, function(resp) {
            if (resp.status === 'SUCCESS') {
                $('#passwordMessage').html('<div class="alert alert-success">' + resp.msg + '</div>');
                $('#changePasswordForm')[0].reset();
                $('#strengthBar').css('width', '0%');
                $('.password-requirements li').removeClass('met');
            } else {
                $('#passwordMessage').html('<div class="alert alert-error">' + resp.msg + '</div>');
            }
        }, 'json');
    });

    // Security Questions Form
    $('#securityQuestionsForm').on('submit', function(e) {
        e.preventDefault();
        
        if ($('#question1').val() === $('#question2').val()) {
            $('#securityMessage').html('<div class="alert alert-error">Please select different security questions!</div>');
            return;
        }
        
        $.post('ajax.php', {
            CALL: 59,
            question1: $('#question1').val(),
            answer1: $('#answer1').val(),
            question2: $('#question2').val(),
            answer2: $('#answer2').val()
        }, function(resp) {
            if (resp.status === 'SUCCESS') {
                $('#securityMessage').html('<div class="alert alert-success">' + resp.msg + '</div>');
            } else {
                $('#securityMessage').html('<div class="alert alert-error">' + resp.msg + '</div>');
            }
        }, 'json');
    });

    // Recovery Email Form
    $('#recoveryEmailForm').on('submit', function(e) {
        e.preventDefault();
        
        $.post('ajax.php', {
            CALL: 60,
            recovery_email: $('#recoveryEmail').val()
        }, function(resp) {
            if (resp.status === 'SUCCESS') {
                $('#recoveryMessage').html('<div class="alert alert-success">' + resp.msg + '</div>');
            } else {
                $('#recoveryMessage').html('<div class="alert alert-error">' + resp.msg + '</div>');
            }
        }, 'json');
    });

    // Load existing security settings
    $.post('ajax.php', { CALL: 61 }, function(resp) {
        if (resp.status === 'SUCCESS' && resp.data) {
            if (resp.data.question1) {
                $('#question1').val(resp.data.question1);
                $('#answer1').val(resp.data.answer1);
            }
            if (resp.data.question2) {
                $('#question2').val(resp.data.question2);
                $('#answer2').val(resp.data.answer2);
            }
            if (resp.data.recovery_email) {
                $('#recoveryEmail').val(resp.data.recovery_email);
            }
        }
    }, 'json');

})(jQuery);
</script>
</body>
</html>
