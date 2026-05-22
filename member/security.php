<?php
session_start();
require_once("../resources/session.php");
$session = SessionManager::getInstance();
$session->requireRole(['student']);
$currentUser = $session->getUserData();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Security - UASG Member</title>
  <link rel="stylesheet" href="../resources/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../resources/theme-overrides.css">
  <script src='../js/all.js?v=<?= time() ?>'></script>
  <script src='../js/jquery.js?v=<?= time() ?>'></script>
  <style>
    .security-card{background:#fff;border-radius:12px;padding:28px;box-shadow:0 2px 12px rgba(0,0,0,.04);max-width:520px;}
    .security-card h3{margin:0 0 4px;font-size:16px;color:#1e293b;}
    .security-card p{margin:0 0 20px;font-size:13px;color:#94a3b8;}
    .sec-field{margin-bottom:16px;}
    .sec-field label{display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;}
    .sec-input-wrap{position:relative;}
    .sec-input-wrap input{width:100%;padding:10px 40px 10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;transition:border-color .2s;}
    .sec-input-wrap input:focus{outline:none;border-color:#7b1228;box-shadow:0 0 0 3px rgba(123,18,40,.08);}
    .sec-toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;font-size:16px;}
    .sec-btn{background:#7b1228;color:#fff;border:none;padding:11px 24px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;width:100%;margin-top:8px;}
    .sec-btn:hover{background:#5f0e20;transform:translateY(-1px);box-shadow:0 4px 12px rgba(123,18,40,.2);}
    .sec-btn:disabled{opacity:.5;cursor:not-allowed;transform:none;box-shadow:none;}
    .sec-msg{padding:10px 14px;border-radius:8px;font-size:12px;margin-bottom:16px;display:none;}
    .sec-msg.success{display:block;background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;}
    .sec-msg.error{display:block;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;}
    .sec-strength{height:4px;border-radius:2px;background:#e2e8f0;margin-top:6px;overflow:hidden;}
    .sec-strength-bar{height:100%;width:0;border-radius:2px;transition:width .3s,background .3s;}
    .sec-strength-text{font-size:10px;color:#94a3b8;margin-top:4px;}
  </style>
</head>
<body>
  <div class="dashboard-container">
    <?php require_once("sidebar.php");?>
    <div class="main-content">
      <?php $pageTitle = 'Security'; require_once("header.php");?>
      <div class="dashboard-content">
        <div class="security-card">
          <h3>🔒 Change Password</h3>
          <p>Keep your account secure by using a strong, unique password.</p>

          <div id="secMsg" class="sec-msg"></div>

          <div class="sec-field">
            <label>Current Password</label>
            <div class="sec-input-wrap">
              <input type="password" id="currentPassword" placeholder="Enter current password">
              <button type="button" class="sec-toggle" onclick="togglePwd(this)">👁</button>
            </div>
          </div>

          <div class="sec-field">
            <label>New Password</label>
            <div class="sec-input-wrap">
              <input type="password" id="newPassword" placeholder="Enter new password" oninput="checkStrength(this.value)">
              <button type="button" class="sec-toggle" onclick="togglePwd(this)">👁</button>
            </div>
            <div class="sec-strength"><div id="strengthBar" class="sec-strength-bar"></div></div>
            <div id="strengthText" class="sec-strength-text"></div>
          </div>

          <div class="sec-field">
            <label>Confirm New Password</label>
            <div class="sec-input-wrap">
              <input type="password" id="confirmPassword" placeholder="Confirm new password">
              <button type="button" class="sec-toggle" onclick="togglePwd(this)">👁</button>
            </div>
          </div>

          <button class="sec-btn" id="changePasswordBtn">Update Password</button>
        </div>
      </div>
    </div>
  </div>

  <?php require_once('modals.php'); ?>

  <script>
  function togglePwd(btn){
    var input=$(btn).siblings('input');
    var isPass=input.attr('type')==='password';
    input.attr('type',isPass?'text':'password');
    $(btn).text(isPass?'🙈':'👁');
  }

  function checkStrength(val){
    var score=0,text='',color='';
    if(val.length>=6) score++;
    if(val.length>=10) score++;
    if(/[A-Z]/.test(val)) score++;
    if(/[0-9]/.test(val)) score++;
    if(/[^A-Za-z0-9]/.test(val)) score++;
    if(score<=1){text='Weak';color='#dc2626';}
    else if(score<=3){text='Medium';color='#d97706';}
    else{text='Strong';color='#16a34a';}
    $('#strengthBar').css({width:(score/5*100)+'%',background:color});
    $('#strengthText').text(val?text:'').css('color',color);
  }

  function showMsg(msg,type){
    $('#secMsg').text(msg).removeClass('success error').addClass(type).show();
    if(type==='success') setTimeout(function(){$('#secMsg').fadeOut();},4000);
  }

  $('#changePasswordBtn').on('click',function(){
    var cur=$('#currentPassword').val(), np=$('#newPassword').val(), cp=$('#confirmPassword').val();
    if(!cur||!np||!cp){showMsg('All fields are required.','error');return;}
    if(np!==cp){showMsg('New passwords do not match.','error');return;}
    if(np.length<6){showMsg('Password must be at least 6 characters.','error');return;}
    var btn=$(this);
    btn.prop('disabled',true).text('Updating...');
    $.post('ajax.php',{CALL:14,current_password:cur,new_password:np,confirm_password:cp},function(r){
      if(r.success||r.status==='SUCCESS'){
        showMsg(r.msg||'Password updated successfully!','success');
        $('#currentPassword,#newPassword,#confirmPassword').val('');
        $('#strengthBar').css('width','0');$('#strengthText').text('');
      } else { showMsg(r.msg||'Failed to change password.','error'); }
    },'json').fail(function(){showMsg('Request failed.','error');}).always(function(){btn.prop('disabled',false).text('Update Password');});
  });
  </script>
</body>
</html>
