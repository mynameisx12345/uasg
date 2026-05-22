<?php
$pageTitles = [
  'index.php' => 'Dashboard',
  'file-center.php' => 'File Center',
  'user-management.php' => 'Users',
  'task-management.php' => 'Tasks',
  'reports-page.php' => 'Reports',
  'settings.php' => 'Settings',
  'nlp-search.php' => 'NLP Search'
];
$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = $pageTitles[$currentPage] ?? 'Dashboard';
$adviserName = $currentUser['full_name'] ?? $_SESSION['username'] ?? 'Adviser';
?>
<header class="github-header">
  <div class="topbar-left">
    <button class="mobile-sidebar-toggle" onclick="toggleMobileSidebar()">☰</button>
    <h1 class="topbar-title"><?= $pageTitle ?></h1>
  </div>
  <div class="header-actions">
    <div class="global-search">
      <input type="text" id="globalSearchInput" placeholder="Search files, users, tasks..." autocomplete="off">
      <div id="globalSearchResults" class="global-search-results"></div>
    </div>
    <!-- Notification Bell -->
    <div class="notif-bell-wrap" style="position:relative;">
      <button id="notifBellBtn" onclick="toggleNotifDropdown()" style="background:none;border:none;cursor:pointer;color:#fff;font-size:20px;position:relative;padding:4px 6px;">
        🔔<span id="notifBadge" style="display:none;position:absolute;top:0;right:0;background:#e53e3e;color:#fff;font-size:10px;font-weight:700;border-radius:50%;min-width:16px;height:16px;line-height:16px;text-align:center;padding:0 3px;"></span>
      </button>
      <div id="notifDropdown" style="display:none;position:absolute;right:0;top:calc(100% + 8px);width:320px;background:#fff;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.15);z-index:9999;overflow:hidden;">
        <div style="padding:12px 16px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
          <strong style="font-size:13px;color:#1e293b;">Notifications</strong>
          <button onclick="markAllNotifsRead()" style="background:none;border:none;font-size:11px;color:#7b1228;cursor:pointer;font-weight:600;">Mark all read</button>
        </div>
        <div id="notifList" style="max-height:360px;overflow-y:auto;"></div>
      </div>
    </div>
    <div class="user-profile">
      <div class="topbar-avatar"><?= strtoupper(substr($adviserName, 0, 1)) ?></div>
      <span class="topbar-name"><?= htmlspecialchars($adviserName) ?></span>
    </div>
  </div>
</header>

<div id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

<style>
.topbar-left { display:flex; align-items:center; gap:12px; }
.header-actions { display:flex; align-items:center; gap:16px; }
.user-profile { display:flex; align-items:center; gap:8px; }
.mobile-sidebar-toggle { display:none; width:38px; height:38px; font-size:1.2rem; align-items:center; justify-content:center; cursor:pointer; }
.global-search { position:relative; width:350px; }
#globalSearchInput { width:100%; padding:10px 14px; font-size:14px; box-sizing:border-box; }
.global-search-results {
  display:none; position:absolute; top:100%; left:0; right:0; margin-top:6px;
  background:rgba(26,10,14,0.95); border:1px solid rgba(255,255,255,0.1); border-radius:10px;
  box-shadow:0 12px 32px rgba(0,0,0,0.4); max-height:320px; overflow-y:auto; z-index:999;
  backdrop-filter:blur(12px);
}
.gs-item {
  display:flex; align-items:center; gap:10px; padding:10px 14px;
  cursor:pointer; border-bottom:1px solid rgba(255,255,255,0.06); font-size:12px; color:#e2e8f0;
  transition:background 0.15s;
}
.gs-item:hover { background:rgba(255,255,255,0.08); }
.gs-item:last-child { border-bottom:none; }
.gs-item .gs-icon { font-size:16px; }
.gs-item .gs-meta { color:rgba(255,255,255,0.4); font-size:11px; }
.gs-empty { padding:20px; text-align:center; color:rgba(255,255,255,0.4); font-size:12px; }
</style>

<script>
function toggleMobileSidebar(){
  var s=document.querySelector('.github-sidebar'); var o=document.getElementById('sidebarOverlay');
  if(!s)return;
  if(s.classList.contains('mobile-open')){closeMobileSidebar();}
  else{s.classList.add('mobile-open');o.classList.add('visible');document.body.style.overflow='hidden';}
}
function closeMobileSidebar(){
  var s=document.querySelector('.github-sidebar'); var o=document.getElementById('sidebarOverlay');
  if(s)s.classList.remove('mobile-open'); if(o)o.classList.remove('visible');
  document.body.style.overflow='';
}
</script>

<script>
(function(){
  var SESSION_TIMEOUT=1800,WARNING_BEFORE=120,checkUrl='session-check.php',loginUrl='../index.php?reason=timeout';
  var lastActivity=Date.now(),warningShown=false;
  ['mousemove','keydown','click','scroll','touchstart'].forEach(function(e){
    document.addEventListener(e,function(){lastActivity=Date.now();warningShown=false;});
  });
  setInterval(function(){
    var idle=(Date.now()-lastActivity)/1000;
    if(idle>=SESSION_TIMEOUT-WARNING_BEFORE&&!warningShown){
      warningShown=true;
      if(typeof Swal!=='undefined')Swal.fire({icon:'warning',title:'Session Expiring',text:'Your session will expire in 2 minutes.',timer:10000,showConfirmButton:true});
    }
    if(typeof $!=='undefined')$.get(checkUrl,function(r){if(r&&r.status==='expired')window.location.href=loginUrl;},'json').fail(function(x){if(x.status===401)window.location.href=loginUrl;});
  },60000);
  if(typeof $!=='undefined')$(document).ajaxError(function(e,x){try{var r=JSON.parse(x.responseText);if(x.status===401||r.session_expired)window.location.href=loginUrl;}catch(err){}});
})();
</script>



<script>
(function(){
  var timer,results=document.getElementById('globalSearchResults'),input=document.getElementById('globalSearchInput');
  input.addEventListener('input',function(){
    clearTimeout(timer); var q=this.value.trim();
    if(q.length<2){results.style.display='none';return;}
    timer=setTimeout(function(){doSearch(q);},300);
  });
  input.addEventListener('focus',function(){if(results.innerHTML&&this.value.trim().length>=2)results.style.display='block';});
  document.addEventListener('click',function(e){if(!e.target.closest('.global-search'))results.style.display='none';});
  function doSearch(q){
    $.post('ajax.php',{CALL:'global_search',query:q},function(r){
      if(!r.success||!r.results.length){results.innerHTML='<div class="gs-empty">No results found</div>';results.style.display='block';return;}
      var h='';r.results.forEach(function(i){h+='<div class="gs-item" onclick="gsNav(\''+i.type+'\','+i.id+')"><span class="gs-icon">'+i.icon+'</span><div><div>'+i.title+'</div><div class="gs-meta">'+i.meta+'</div></div></div>';});
      results.innerHTML=h;results.style.display='block';
    },'json');
  }
  window.gsNav=function(type,id){
    results.style.display='none';
    var q=input.value.trim();
    if(type==='file')window.location.href='file-center.php?search='+encodeURIComponent(q);
    else if(type==='user')window.location.href='user-management.php?search='+encodeURIComponent(q);
    else if(type==='task')window.location.href='task-management.php?search='+encodeURIComponent(q);
    input.value='';
  };
})();
</script>

<script>
(function(){
  var CALL_GET=15, CALL_MARK=16, CALL_COUNT=17, CALL_MARK_ALL=69;
  var USER_ID=<?= (int)($_SESSION['user_id'] ?? 0) ?>;
  var open=false;

  function fetchCount(){
    $.post('ajax.php',{CALL:CALL_COUNT},function(r){
      var n=parseInt(r.count)||0;
      $('#notifBadge').text(n>99?'99+':n).toggle(n>0);
    },'json');
  }

  function fetchList(){
    $.post('ajax.php',{CALL:CALL_GET},function(r){
      var items=r.data||[];
      if(!items.length){$('#notifList').html('<div style="padding:20px;text-align:center;color:#94a3b8;font-size:12px;">No notifications</div>');return;}
      var h='';
      items.forEach(function(n){
        var unread=n.is_read=='0'||n.is_read===0;
        var bg=unread?'#fef9f0':'#fff';
        var dot=unread?'<span style="width:8px;height:8px;border-radius:50%;background:#e53e3e;display:inline-block;margin-right:6px;flex-shrink:0;"></span>':'<span style="width:8px;margin-right:6px;display:inline-block;"></span>';
        var time=n.datetime_created?new Date(n.datetime_created).toLocaleString('en-US',{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}):'';
        h+='<div class="notif-item" data-id="'+n.notification_id+'" onclick="readNotif('+n.notification_id+')" style="padding:12px 16px;border-bottom:1px solid #f5f5f5;cursor:pointer;background:'+bg+';display:flex;align-items:flex-start;gap:4px;transition:background 0.15s;">'
          +dot+'<div style="flex:1;min-width:0;">'
          +'<div style="font-size:12px;font-weight:'+(unread?'600':'400')+';color:#1e293b;margin-bottom:2px;">'+n.title+'</div>'
          +'<div style="font-size:11px;color:#64748b;line-height:1.4;word-break:break-word;">'+n.message+'</div>'
          +'<div style="font-size:10px;color:#94a3b8;margin-top:4px;">'+time+'</div>'
          +'</div></div>';
      });
      $('#notifList').html(h);
    },'json');
  }

  window.toggleNotifDropdown=function(){
    open=!open;
    $('#notifDropdown').toggle(open);
    if(open) fetchList();
  };

  window.readNotif=function(id){
    $.post('ajax.php',{CALL:CALL_MARK,notification_id:id},function(){
      fetchCount();
      var onTaskPage = window.location.pathname.indexOf('task-management.php') !== -1;
      if (onTaskPage) {
        $('#notifList .notif-item[data-id="'+id+'"]').css('background','#fff').find('span:first').css('background','transparent');
        if(typeof tasksTable!=='undefined'&&tasksTable) tasksTable.ajax.reload();
        if(typeof submissionsTable!=='undefined'&&submissionsTable) submissionsTable.ajax.reload();
        if(typeof renderKanban==='function') renderKanban();
      } else {
        window.location.href = 'task-management.php';
      }
    },'json');
  };

  window.markAllNotifsRead=function(){
    $.post('ajax.php',{CALL:CALL_MARK_ALL},function(){
      $('#notifBadge').hide();
      fetchList();
    },'json');
  };

  $(document).on('click',function(e){if(open&&!$(e.target).closest('.notif-bell-wrap').length){open=false;$('#notifDropdown').hide();}});

  fetchCount();
  setInterval(fetchCount, 15000);
})();
</script>