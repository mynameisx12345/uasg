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
  <title>Tasks - UASG Member</title>
  <link rel="stylesheet" href="../resources/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../resources/theme-overrides.css">
  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
  <style>@keyframes pulse{0%,100%{opacity:1}50%{opacity:.7}}
  #pendingTasksTable td,#pendingTasksTable th,#overdueTasksTable td,#overdueTasksTable th,#taskSubmissionsTable td,#taskSubmissionsTable th{font-size:12px;padding:6px 10px;}
  </style>
  <script src='../js/all.js?v=<?= time() ?>'></script>
  <script src='../js/jquery.js?v=<?= time() ?>'></script>
  <script src='../js/datatable.js?v=<?= time() ?>'></script>
</head>
<body>
  <div class="dashboard-container">
    <?php require_once("sidebar.php");?>
    <div class="main-content">
      <?php $pageTitle = 'Tasks'; require_once("header.php");?>
      <div class="dashboard-content">
      <div class="card">
        <!-- TABS -->
        <div class="tabs">
          <button class="tab-btn active" data-tab="pending-tasks">📋 Pending Tasks <span id="pendingBadge" class="badge" style="display:none;"></span></button>
          <button class="tab-btn" data-tab="task-submissions">📄 Task Submissions</button>
        </div>

        <!-- TAB: PENDING TASKS -->
        <div class="tab-content active" id="pending-tasks">
          <!-- Summary Stats -->
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
            <div style="background:#f1f5f9;border-radius:10px;padding:16px;border:1px solid #e2e8f0;text-align:center;">
              <div style="font-size:24px;font-weight:700;color:#64748b;" id="statPending">0</div>
              <div style="font-size:12px;color:#64748b;margin-top:2px;">Pending</div>
            </div>
            <div style="background:#fee2e2;border-radius:10px;padding:16px;border:1px solid #fecaca;text-align:center;">
              <div style="font-size:24px;font-weight:700;color:#991b1b;" id="statOverdue">0</div>
              <div style="font-size:12px;color:#991b1b;margin-top:2px;">Overdue</div>
            </div>
            <div style="background:#fef3c7;border-radius:10px;padding:16px;border:1px solid #fde68a;text-align:center;">
              <div style="font-size:24px;font-weight:700;color:#92400e;" id="statUrgent">0</div>
              <div style="font-size:12px;color:#92400e;margin-top:2px;">Due within 3 days</div>
            </div>
          </div>

          <!-- Overdue Section -->
          <div id="overdueSection" style="margin-bottom:20px;border-radius:10px;border:1px solid #fecaca;background:#fdf2f2;padding:18px;">
            <h3 style="color:#c0392b;margin:0 0 4px;font-size:15px;">🚨 Overdue Tasks</h3>
            <p style="color:#94a3b8;font-size:12px;margin:0 0 12px;">These tasks have passed their deadline but still require submission.</p>
            <div class="table-container">
              <table id="overdueTasksTable" class="display" style="width:100%">
                <thead><tr><th>Task ID</th><th>Task</th><th>Category</th><th>Deadline</th><th>Action</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
          <!-- Pending Section -->
          <div style="border-radius:10px;border:1px solid #e2e8f0;background:#fff;padding:18px;">
            <h3 style="margin:0 0 4px;font-size:15px;">📌 Upcoming Tasks</h3>
            <p style="color:#94a3b8;font-size:12px;margin:0 0 12px;">Tasks awaiting your submission — sorted by urgency.</p>
            <div class="table-container">
              <table id="pendingTasksTable" class="display" style="width:100%">
                <thead><tr><th>Task ID</th><th>Task</th><th>Category</th><th>Deadline</th><th>Action</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- TAB: TASK SUBMISSIONS -->
        <div class="tab-content" id="task-submissions">
          <!-- Submission Stats -->
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:20px;">
            <div style="background:#fef3c7;border-radius:10px;padding:14px;border:1px solid #fde68a;text-align:center;">
              <div style="font-size:20px;font-weight:700;color:#92400e;" id="statSubmitted">0</div>
              <div style="font-size:11px;color:#92400e;">Awaiting Review</div>
            </div>
            <div style="background:#d1fae5;border-radius:10px;padding:14px;border:1px solid #bbf7d0;text-align:center;">
              <div style="font-size:20px;font-weight:700;color:#065f46;" id="statApproved">0</div>
              <div style="font-size:11px;color:#065f46;">Approved</div>
            </div>
          </div>

          <div style="border-radius:10px;border:1px solid #e2e8f0;background:#fff;padding:18px;">
            <!-- Inline Filters -->
            <div style="display:flex;gap:10px;align-items:center;margin-bottom:14px;flex-wrap:wrap;">
              <select id="taskStatusFilter" style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="submitted">Submitted</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
              </select>
              <select id="taskCategoryFilter" style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;">
                <option value="">All Categories</option>
              </select>
              <button id="applyTaskFilters" style="padding:7px 14px;border:none;background:#7b1228;color:#fff;border-radius:6px;font-size:12px;font-weight:500;cursor:pointer;">Filter</button>
              <button id="clearTaskFilters" style="padding:7px 14px;border:1px solid #e2e8f0;background:#fff;color:#475569;border-radius:6px;font-size:12px;cursor:pointer;">Clear</button>
            </div>
            <!-- Table -->
            <div class="table-container">
              <table id="taskSubmissionsTable" class="display" style="width:100%">
                <thead>
                  <tr>
                    <th>Task ID</th>
                    <th>Task Title</th>
                    <th>Category</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>File</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>

  <!-- Comply Task Modal -->
  <div id="complyTaskModal" class="modal">
    <div class="modal-content modal-content-large">
      <h2>📋 Comply with Task</h2>
      <form id="complyTaskForm" enctype="multipart/form-data">
        <input type="hidden" id="complyTaskId" name="task_id" />
        <div class="form-group">
          <label for="complyFile">📎 Select File to Upload</label>
          <input type="file" id="complyFile" name="file" accept=".pdf,.doc,.docx,.txt,.xls,.xlsx,.csv,.ppt,.pptx" required />
          <small style="display:block;margin-top:5px;color:#666;">Supported: PDF, Word, Excel, PowerPoint, Text (Max: 50MB)</small>
        </div>
        <div class="alert alert-info" style="margin-top:15px;padding:12px;background:#e3f2fd;border-left:4px solid #2196F3;border-radius:4px;">
          <strong>🤖 AI-Powered Classification</strong>
          <p style="margin:5px 0 0;font-size:13px;">Your file will be analyzed for intelligent classification based on document content.</p>
        </div>
        <div id="complyNlpPreview" style="display:none;margin-top:15px;padding:15px;background:#f5f5f5;border-radius:4px;border-left:4px solid #4CAF50;">
          <h4 style="margin:0 0 10px;font-size:14px;">📊 Classification Preview</h4>
          <div style="font-size:13px;line-height:1.8;">
            <div><strong>🏷️ Suggested Category:</strong> <span id="complySuggestedCategory">-</span></div>
            <div><strong>📈 Confidence:</strong> <span id="complyCategoryConfidence">-</span></div>
            <div id="complySentimentPreview" style="display:none;"><strong>😊 Sentiment:</strong> <span id="complySentiment">-</span></div>
          </div>
        </div>
        <div class="form-actions" style="margin-top:25px;display:flex;gap:10px;justify-content:flex-end;">
          <button type="button" class="btn-secondary" onclick="closeModal('complyTaskModal')">Cancel</button>
          <button type="button" class="btn-primary btn-gold" id="complyUploadBtn">📤 Submit Task</button>
        </div>
      </form>
    </div>
  </div>

  <?php require_once('modals.php'); ?>

  <script>
  $(function(){
    // Tab switching
    $('.tab-btn').click(function(){
      $('.tab-btn').removeClass('active');
      $('.tab-content').removeClass('active');
      $(this).addClass('active');
      $('#' + $(this).data('tab')).addClass('active');
    });

    // --- Pending & Overdue Tables ---
    window.pendingTasksTable = $('#pendingTasksTable').DataTable({
      ajax: { url:'ajax.php', type:'POST', data:{CALL:9}, dataSrc:function(j){
        var data=j.data||[];
        var urgent=data.filter(function(t){ var d=new Date(t.task_deadline),today=new Date();today.setHours(0,0,0,0); return Math.ceil((d-today)/86400000)<=3; }).length;
        $('#statPending').text(data.length);
        $('#statUrgent').text(urgent);
        return data;
      }},
      responsive:true, order:[[3,'asc']],
      columns: [
        { data:'task_id' },
        { data:'task_title', render:function(data){ return '<strong style="color:#1e293b;">'+data+'</strong>'; }},
        { data:'task_category', render:function(data){ return '<span style="background:#f1f5f9;color:#475569;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:500;">'+data+'</span>'; }},
        { data:'task_deadline', render:function(data){
            if(!data) return '-';
            var d=new Date(data),today=new Date();today.setHours(0,0,0,0);
            var diff=Math.ceil((d-today)/(86400000));
            var fmt=d.toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'});
            if(diff<=1) return '<span style="color:#c0392b;font-weight:600;">'+fmt+'</span><span style="display:block;font-size:10px;color:#c0392b;font-weight:700;">⚡ Due '+(diff<=0?'today':'tomorrow')+'</span>';
            if(diff<=3) return '<span style="color:#d97706;font-weight:600;">'+fmt+'</span><span style="display:block;font-size:10px;color:#d97706;">'+diff+' days left</span>';
            return fmt+'<span style="display:block;font-size:10px;color:#94a3b8;">'+diff+' days left</span>';
        }},
        { data:null, render:function(d,t,row){
            var deadline=new Date(row.task_deadline),today=new Date();today.setHours(0,0,0,0);
            var diff=Math.ceil((deadline-today)/86400000);
            var cls=diff<=3?'background:#c0392b;':'background:#7b1228;';
            return '<button style="background:#f1f5f9;border:1px solid #e2e8f0;padding:8px 12px;border-radius:6px;font-size:12px;cursor:pointer;margin-right:6px;" onclick="viewTask('+row.task_id+')">👁 View</button><button style="'+cls+'color:#fff;border:none;padding:8px 16px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;" onclick="openComplyTaskModal('+row.task_id+')">Submit →</button>';
        }, orderable:false }
      ],
      language:{emptyTable:'<div style="padding:20px;text-align:center;"><span style="font-size:32px;">🎉</span><br><span style="color:#64748b;">No pending tasks — you\'re all caught up!</span></div>'},
      createdRow:function(row,data){
        var d=new Date(data.task_deadline),today=new Date();today.setHours(0,0,0,0);
        var diff=Math.ceil((d-today)/86400000);
        if(diff<=1) $(row).css('background','#fef2f2');
        else if(diff<=3) $(row).css('background','#fffbeb');
      }
    });

    window.overdueTasksTable = $('#overdueTasksTable').DataTable({
      ajax: { url:'ajax.php', type:'POST', data:{CALL:22}, dataSrc:function(j){
        var data=j.data||[];
        $('#statOverdue').text(data.length);
        $('#overdueSection').toggle(data.length>0);
        return data;
      }},
      responsive:true,
      columns: [
        { data:'task_id' },
        { data:'task_title', render:function(data){ return '<strong style="color:#c0392b;">'+data+'</strong>'; }},
        { data:'task_category', render:function(data){ return '<span style="background:#fef2f2;color:#c0392b;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:500;">'+data+'</span>'; }},
        { data:'task_deadline', render:function(data){
            if(!data) return '-';
            var d=new Date(data),today=new Date();today.setHours(0,0,0,0);
            var over=Math.ceil((today-d)/(86400000));
            var fmt=d.toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'});
            return '<span style="color:#c0392b;font-weight:600;">'+fmt+'</span><span style="display:block;font-size:10px;color:#c0392b;font-weight:700;">🔥 '+over+' day'+(over>1?'s':'')+' overdue</span>';
        }},
        { data:null, render:function(d,t,row){ return '<button style="background:#f1f5f9;border:1px solid #e2e8f0;padding:8px 12px;border-radius:6px;font-size:12px;cursor:pointer;margin-right:6px;" onclick="viewTask('+row.task_id+')">👁 View</button><button style="background:#c0392b;color:#fff;border:none;padding:8px 16px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;animation:pulse 2s infinite;" onclick="openComplyTaskModal('+row.task_id+')">Submit Now ⚡</button>'; }, orderable:false }
      ],
      language:{emptyTable:'No overdue tasks'}
    });

    // --- Task Submissions Table ---
    window.taskSubmissionsTable = $('#taskSubmissionsTable').DataTable({
      ajax: { url:'ajax.php', type:'POST', data:function(d){ return {CALL:10, status:$('#taskStatusFilter').val()||'', category:$('#taskCategoryFilter').val()||''}; }, dataSrc:function(j){
        var data=(j.data||[]).filter(function(r){ var s=(r.check_status||'').toLowerCase(), ts=(r.task_status||'').toLowerCase(); return (s==='pending'||s==='approved')&&ts!=='closed'&&ts!=='cancelled'; });
        var submitted=0,approved=0;
        data.forEach(function(r){ var s=(r.check_status||'').toLowerCase(); if(s==='pending')submitted++; else if(s==='approved')approved++; });
        $('#statSubmitted').text(submitted); $('#statApproved').text(approved);
        return data;
      }},
      responsive:true,
      columns: [
        { data:'task_id' },
        { data:'task_title', render:function(data){ return '<strong style="font-size:12px;color:#1e293b;">'+data+'</strong>'; }},
        { data:'task_category', render:function(data){ return '<span style="background:#f1f5f9;color:#475569;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:500;">'+data+'</span>'; }},
        { data:'task_deadline', render:function(data){ if(!data)return'-'; return '<span style="font-size:11px;">'+new Date(data).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})+'</span>'; }},
        { data:'check_status', render:function(data,type,row){
            var s=(data||'').toLowerCase(), ts=(row.task_status||'').toLowerCase(), label='', bg='#f1f5f9', color='#64748b';
            if(ts==='cancelled'){bg='#f1f5f9';color='#64748b';label='🚫 Cancelled';}
            else if(ts==='closed'){bg='#e2e8f0';color='#334155';label='🔒 Closed';}
            else if(s==='pending'){bg='#fef3c7';color='#92400e';label='⏳ Awaiting Review';}
            else if(s==='approved'){bg='#d1fae5';color='#065f46';label='✅ Approved';}
            else if(s==='rejected'){bg='#fee2e2';color='#991b1b';label='❌ Rejected';}
            else if(!row.task_submission_id && ts==='overdue'){bg='#fee2e2';color='#991b1b';label='🔥 Overdue';}
            else if(!row.task_submission_id){bg='#e0e7ff';color='#3730a3';label='📝 Pending';}
            else {label=data||'Unknown';}
            return '<span style="background:'+bg+';color:'+color+';padding:3px 10px;border-radius:10px;font-size:10px;font-weight:600;">'+label+'</span>';
        }},
        { data:'file_name', render:function(data){ return data?'<span style="font-size:11px;color:#475569;">📎 '+data+'</span>':'<span style="font-size:11px;color:#94a3b8;">—</span>'; }},
        { data:null, render:function(d,t,row){
            var ts=(row.task_status||'').toLowerCase();
            var canSubmit=(!row.task_submission_id||row.check_status==='rejected')&&ts!=='cancelled'&&ts!=='closed';
            var canDownload=!!row.file_upload_id;
            var btns='<button style="background:#f1f5f9;border:1px solid #e2e8f0;padding:5px 10px;border-radius:5px;font-size:11px;cursor:pointer;margin-right:4px;" onclick="viewTask('+row.task_id+','+(row.file_upload_id||'null')+')">👁 View</button>';
            if(canSubmit) btns+='<button style="background:#7b1228;color:#fff;border:none;padding:5px 12px;border-radius:5px;font-size:11px;font-weight:500;cursor:pointer;" onclick="openComplyTaskModal('+row.task_id+')">'+(row.check_status==='rejected'?'Resubmit':'Submit')+'</button>';
            return btns;
        }, orderable:false }
      ],
      pageLength:10, order:[[3,'asc']],
      language:{emptyTable:'<div style="padding:16px;text-align:center;color:#94a3b8;">No submissions yet</div>'}
    });

    // Filters
    $('#applyTaskFilters').click(function(){ taskSubmissionsTable.ajax.reload(); });
    $('#clearTaskFilters').click(function(){ $('#taskStatusFilter,#taskCategoryFilter').val(''); taskSubmissionsTable.ajax.reload(); });

    // Handle URL params for tab switching and search
    var params = new URLSearchParams(window.location.search);
    if(params.get('tab')){
      $('.tab-btn').removeClass('active');
      $('.tab-content').removeClass('active');
      $('.tab-btn[data-tab="'+params.get('tab')+'"]').addClass('active');
      $('#'+params.get('tab')).addClass('active');
    }
    if(params.get('search')){
      var tbl = params.get('table');
      if(tbl==='pending') pendingTasksTable.search(params.get('search')).draw();
      else if(tbl==='overdue') overdueTasksTable.search(params.get('search')).draw();
      else if(params.get('tab')==='task-submissions') taskSubmissionsTable.search(params.get('search')).draw();
    }

    // Load category filter options
    $.post('ajax.php',{CALL:21},function(r){
      if(r.status==='SUCCESS'&&r.data){
        var opts='<option value="">All Categories</option>';
        r.data.forEach(function(c){ opts+='<option value="'+c.category_tag+'">'+c.category_tag+' ('+c.file_count+')</option>'; });
        $('#taskCategoryFilter').html(opts);
      }
    },'json');

    // Badge count
    function updateTabBadge(){
      $.post('ajax.php',{CALL:9},function(r){ var p=(r.data||[]).length; $.post('ajax.php',{CALL:22},function(r2){ var total=p+(r2.data||[]).length; if(total>0)$('#pendingBadge').text(total).show(); else $('#pendingBadge').hide(); },'json'); },'json');
    }
    updateTabBadge();

    // --- Comply Task Modal ---
    window.viewTask = function(taskId, fileId){
      $.post('ajax.php',{CALL:18,task_id:taskId},function(r){
        if(r.success&&r.data){
          displayTaskDetails(r.data);
          var dlBtn=$('#downloadFromViewBtn');
          if(fileId){dlBtn.show().off('click').on('click',function(){downloadFile(fileId);});}else{dlBtn.hide();}
          openModal('viewTaskModal');
        } else { openNotificationModal('Could not load task details.','error'); }
      },'json');
    };

    window.openComplyTaskModal = function(taskId){
      $('#complyTaskId').val(taskId);
      $('#complyFile').val('');
      $('#complyNlpPreview').hide();
      $('#complySuggestedCategory,#complyCategoryConfidence,#complySentiment').text('-');
      $('#complySentimentPreview').hide();
      openModal('complyTaskModal');
    };

    $('#complyFile').on('change', function(){
      var file = this.files[0];
      if(!file) return;
      var fd = new FormData();
      fd.append('CALL','nlp_analyze');
      fd.append('file', file);
      openLoadModal();
      $.ajax({ url:'ajax.php', type:'POST', data:fd, processData:false, contentType:false, dataType:'json',
        success:function(r){
          if(r.status==='SUCCESS'){
            $('#complyNlpPreview').show();
            $('#complySuggestedCategory').text(r.category||'Uncategorized');
            $('#complyCategoryConfidence').text((r.score||'0')+'%');
            if(r.sentiment&&r.sentiment.length>0){
              var e=r.sentiment[0], icon=e.label==='joy'?'😊':e.label==='anger'?'😠':e.label==='sadness'?'😢':e.label==='fear'?'😨':e.label==='love'?'❤️':'😮';
              $('#complySentimentPreview').show();
              $('#complySentiment').text(icon+' '+e.label+' ('+(e.score*100).toFixed(1)+'%)');
            }
            $('#complyFile').data('suggestedCategory', r.category||'Uncategorized');
            $('#complyFile').data('categoryScore', r.score||'0');
            $('#complyFile').data('nlpAnalysis', JSON.stringify(r.nlp_analysis||{}));
          } else { openNotificationModal('NLP analysis failed: '+(r.msg||'Unknown error'),'error'); }
        },
        error:function(){ openNotificationModal('NLP analysis failed.','error'); },
        complete:function(){ closeLoadModal(); }
      });
    });

    $('#complyUploadBtn').on('click', function(){
      var file=$('#complyFile')[0].files[0];
      if(!file){ openNotificationModal('Please select a file.','warning'); return; }
      var taskId=$('#complyTaskId').val();
      var btn=$(this);
      // Validate
      var vfd=new FormData();
      vfd.append('CALL','validate_task_file'); vfd.append('task_id',taskId); vfd.append('file',file);
      openLoadModal(); btn.prop('disabled',true).html('⏳ Validating...');
      $.ajax({ url:'ajax.php', type:'POST', data:vfd, processData:false, contentType:false, dataType:'json',
        success:function(vr){
          closeLoadModal();
          if(!vr||vr.success===false){ openNotificationModal('Validation error: '+(vr?.error||vr?.msg||'Failed'),'error'); btn.prop('disabled',false).html('📤 Submit Task'); return; }
          if(!vr.is_valid){
            var msg='File does not comply with task requirements.';
            if(vr.recommendation&&vr.recommendation.message) msg+='\n\n'+vr.recommendation.message;
            if(vr.recommendation&&vr.recommendation.reasons) msg+='\n\n• '+vr.recommendation.reasons.join('\n• ');
            openNotificationModal(msg,'warning'); btn.prop('disabled',false).html('📤 Submit Task'); return;
          }
          // Upload
          var ufd=new FormData();
          ufd.append('CALL',11); ufd.append('task_id',taskId); ufd.append('file',file);
          ufd.append('category_tag',$('#complyFile').data('suggestedCategory')||'Uncategorized');
          ufd.append('category_score',$('#complyFile').data('categoryScore')||'0');
          ufd.append('nlp_analysis',$('#complyFile').data('nlpAnalysis')||'');
          openLoadModal(); btn.html('⬆️ Uploading...');
          $.ajax({ url:'ajax.php', type:'POST', data:ufd, processData:false, contentType:false, dataType:'json',
            success:function(r){
              if(r.status==='SUCCESS'||r.success){
                closeModal('complyTaskModal');
                var catHtml=r.category_tag?'<div style="margin-top:10px;background:#f1f5f9;padding:8px 12px;border-radius:8px;font-size:12px;color:#475569;">🤖 Category: <strong>'+r.category_tag+'</strong> ('+( r.category_score||0)+'%)</div>':'';
                $('#notificationTitle').text('');
                $('#notificationMessage').html('<div style="text-align:center;padding:20px 0;"><div style="font-size:48px;margin-bottom:12px;">🎉</div><h3 style="margin:0 0 8px;color:#065f46;font-size:18px;">Task Submitted!</h3><p style="color:#64748b;font-size:13px;margin:0;">Your file has been submitted and is now awaiting review.</p>'+catHtml+'</div>');
                document.getElementById('notificationModal').className='modal success';
                document.getElementById('notificationModal').style.display='flex';
                window.pendingTasksTable.ajax.reload(); window.overdueTasksTable.ajax.reload(); window.taskSubmissionsTable.ajax.reload();
                if(typeof updateNavTaskBadge==='function') updateNavTaskBadge();
                updateTabBadge();
              } else { openNotificationModal('Upload failed: '+(r.msg||'Unknown error'),'error'); }
            },
            error:function(){ openNotificationModal('Upload failed.','error'); },
            complete:function(){ closeLoadModal(); btn.prop('disabled',false).html('📤 Submit Task'); }
          });
        },
        error:function(){ closeLoadModal(); openNotificationModal('Validation failed.','error'); btn.prop('disabled',false).html('📤 Submit Task'); }
      });
    });

    window.downloadFile = function(id){ window.open('ajax.php?CALL=download&file_id='+id,'_blank'); };
  });
  </script>
</body>
</html>
