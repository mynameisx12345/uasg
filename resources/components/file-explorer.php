<!-- File Explorer Tab Content -->
<style>
  .fe-toolbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; gap:10px; flex-wrap:wrap; }
  .fe-search { padding:10px 14px; border:1px solid #e2e8f0; border-radius:10px; width:260px; font-size:13px; background:#f8fafc; transition:all 0.2s; }
  .fe-search:focus { outline:none; border-color:#c89b2e; box-shadow:0 0 0 3px rgba(200,155,46,0.15); background:#fff; }
  .fe-view-toggle { display:flex; gap:0; }
  .fe-view-toggle button { padding:8px 12px; border:1px solid #e2e8f0; background:#fff; cursor:pointer; font-size:14px; transition:all 0.15s; }
  .fe-view-toggle button.active { background:#7b1228; color:#fff; border-color:#7b1228; }
  .fe-view-toggle button:first-child { border-radius:8px 0 0 8px; }
  .fe-view-toggle button:last-child { border-radius:0 8px 8px 0; }
  .fe-breadcrumb { display:flex; align-items:center; gap:8px; padding:12px 16px; font-size:13px; color:#64748b; margin-bottom:14px; background:#f8fafc; border-radius:8px; border:1px solid #f1f5f9; }
  .fe-breadcrumb a { color:#7b1228; text-decoration:none; cursor:pointer; font-weight:500; }
  .fe-breadcrumb a:hover { text-decoration:underline; }
  .fe-breadcrumb span { color:#94a3b8; }
  .fe-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(170px, 1fr)); gap:14px; }
  .fe-card { background:#fff; border:1px solid #f1f5f9; border-radius:12px; padding:20px 14px; text-align:center; cursor:pointer; transition:all 0.2s; position:relative; }
  .fe-card:hover { border-color:#7b1228; box-shadow:0 4px 16px rgba(123,18,40,0.08); transform:translateY(-3px); }
  .fe-card-icon { font-size:40px; margin-bottom:10px; }
  .fe-card-name { font-size:12px; color:#1e293b; word-break:break-word; line-height:1.4; font-weight:500; }
  .fe-card-meta { font-size:10px; color:#94a3b8; margin-top:6px; }
  .fe-list { width:100%; }
  .fe-list-row { display:flex; align-items:center; padding:12px 14px; border-bottom:1px solid #f8fafc; cursor:pointer; transition:all 0.15s; border-radius:8px; margin-bottom:2px; }
  .fe-list-row:hover { background:linear-gradient(135deg, rgba(123,18,40,0.02), rgba(200,155,46,0.02)); border-color:transparent; }
  .fe-list-icon { font-size:22px; margin-right:14px; width:32px; text-align:center; }
  .fe-list-name { flex:1; font-size:13px; color:#1e293b; font-weight:500; }
  .fe-list-meta { font-size:11px; color:#94a3b8; width:120px; text-align:right; flex-shrink:0; margin-right:12px; }
  .fe-list-size { font-size:11px; color:#94a3b8; width:80px; text-align:right; margin-right:12px; flex-shrink:0; }
  .fe-list-actions { width:150px; text-align:right; display:flex; gap:2px; justify-content:flex-end; flex-shrink:0; }
  .fe-list-actions button { background:none; border:none; cursor:pointer; padding:6px 8px; border-radius:6px; font-size:14px; transition:background 0.15s; }
  .fe-list-actions button:hover { background:#f1f5f9; }
  .fe-empty { text-align:center; padding:60px 20px; color:#94a3b8; }
  .fe-empty-icon { font-size:52px; margin-bottom:16px; display:block; opacity:0.5; }
  .fe-empty-text { font-size:14px; }
  .fe-folder-count { background:rgba(123,18,40,0.08); color:#7b1228; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:600; margin-left:6px; }
  .fe-btn { padding:9px 16px; border:none; border-radius:8px; cursor:pointer; font-size:13px; font-weight:500; transition:all 0.2s; display:inline-flex; align-items:center; gap:5px; }
  .fe-btn-upload { background:#7b1228; color:#fff; }
  .fe-btn-upload:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(123,18,40,0.2); }
  .fe-btn-smart { background:#c89b2e; color:#fff; }
  .fe-btn-smart:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(200,155,46,0.2); }
  .fe-props-section { margin-top:16px; padding-top:16px; border-top:1px solid #f0f0f0; }
  .fe-props-section h4 { margin:0 0 10px 0; font-size:13px; color:#333; }
  .fe-confidence-bar { background:#f0f0f0; height:20px; border-radius:3px; position:relative; margin:4px 0; }
  .fe-confidence-fill { height:100%; border-radius:3px; }
  .fe-confidence-label { position:absolute; right:6px; top:2px; font-size:10px; font-weight:bold; color:#333; }
  .fe-keyword-tag { display:inline-block; background:rgba(123,18,40,0.08); padding:3px 8px; border-radius:12px; font-size:11px; margin:2px; color:#7b1228; }
</style>

<div class="fe-toolbar">
  <div style="display:flex;align-items:center;gap:12px;">
    <h3 style="margin:0;font-size:16px;font-weight:600;color:#1e293b;">File Explorer</h3>
  </div>
  <div style="display:flex;align-items:center;gap:10px;">
    <button id="feUploadBtn" class="fe-btn fe-btn-upload">⬆️ Upload</button>
    <button id="feApprovedFilter" class="fe-btn" style="background:#ecfdf5;color:#065f46;border:1px solid #bbf7d0;">✓ Approved</button>
    <button id="feOverriddenFilter" class="fe-btn" style="background:#fff3e0;color:#e65100;border:1px solid #ffe0b2;">✎ Overridden</button>
    <input type="text" id="feSearch" class="fe-search" placeholder="🔍 Search files...">
    <button id="feSmartSearchBtn" class="fe-btn fe-btn-smart">🧠 Smart Search</button>
    <div class="fe-view-toggle">
      <button id="feGridView" title="Grid view">▦</button>
      <button id="feListView" class="active" title="List view">☰</button>
    </div>
  </div>
</div>

<div class="fe-breadcrumb" id="feBreadcrumb">
  <a onclick="feGoHome()">📁 Home</a>
</div>

<div id="feContent"></div>

<script>
$(function(){
  var feView = 'list';
  var feCurrentFolder = null;
  var feFolders = [];
  window.feFilesCache = [];
  var feCategories = [];

  // Load categories for override dropdown
  $.post('ajax.php',{CALL:'get_categories'},function(r){ if(r.status==='SUCCESS'&&r.data) feCategories=r.data; },'json');

  feLoadFolders();

  var urlSearch = new URLSearchParams(window.location.search).get('search');
  if(urlSearch){ $('#feSearch').val(urlSearch); setTimeout(function(){ feSearchFiles(urlSearch); },500); }

  $('#feGridView').click(function(){ feView='grid'; $(this).addClass('active'); $('#feListView').removeClass('active'); feRender(); });
  $('#feListView').click(function(){ feView='list'; $(this).addClass('active'); $('#feGridView').removeClass('active'); feRender(); });

  var feOverriddenActive = false;
  var feApprovedActive = false;

  var feSearchTimer;
  $('#feSearch').on('input', function(){
    var q = $(this).val().trim();
    clearTimeout(feSearchTimer);
    if (q.length >= 2) { feSearchTimer = setTimeout(function(){ feSearchFiles(q); }, 300); }
    else if (q.length === 0) { if(feOverriddenActive) feFilterOverridden(); else { feCurrentFolder = null; feRender(); } }
  });

  window.feGoHome = function(){ feCurrentFolder = null; feOverriddenActive = false; feApprovedActive = false; $('#feSearch').val(''); $('#feBreadcrumb').html('<a onclick="feGoHome()">📁 Home</a>'); $('#feOverriddenFilter').css({'background':'#fff3e0','color':'#e65100','border-color':'#ffe0b2'}); $('#feApprovedFilter').css({'background':'#ecfdf5','color':'#065f46','border-color':'#bbf7d0'}); feLoadFolders(); };
  window.feOpenFolder = function(id, name){ feCurrentFolder = {id:id, name:name}; $('#feBreadcrumb').html('<a onclick="feGoHome()">📁 Home</a> <span>›</span> <span>📁 ' + name + '</span>'); feLoadFiles(id); };
  window.feDownloadFile = function(p, e, id){ if(e) e.stopPropagation(); if(id) window.open("ajax.php?CALL=download&file_id="+id,"_blank"); else window.open("../"+p,"_blank"); };

  window.feShowProps = function(idx, e){
    if(e) e.stopPropagation();
    var f = window.feFilesCache[idx]; if(!f) return;
    var icon = feGetIcon(f.mime_type);
    var html = '<div style="text-align:center;padding:10px 0;"><span style="font-size:48px;">' + icon + '</span><div style="font-size:14px;font-weight:bold;margin-top:8px;">' + f.original_filename + '</div></div>';
    html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px;">';
    html += '<div style="background:#f8f9fa;padding:10px;border-radius:6px;"><div style="font-size:10px;color:#666;text-transform:uppercase;">Category</div><div style="font-size:13px;margin-top:3px;"><span style="background:rgba(123,18,40,0.08);color:#7b1228;padding:2px 8px;border-radius:3px;">' + (f.category_tag||'Uncategorized') + '</span></div></div>';
    html += '<div style="background:#f8f9fa;padding:10px;border-radius:6px;"><div style="font-size:10px;color:#666;text-transform:uppercase;">Size</div><div style="font-size:13px;margin-top:3px;font-weight:bold;">' + feFormatSize(f.file_size) + '</div></div>';
    html += '<div style="background:#f8f9fa;padding:10px;border-radius:6px;"><div style="font-size:10px;color:#666;text-transform:uppercase;">Uploaded By</div><div style="font-size:13px;margin-top:3px;">' + (f.uploaded_by||'Unknown') + '</div></div>';
    html += '<div style="background:#f8f9fa;padding:10px;border-radius:6px;"><div style="font-size:10px;color:#666;text-transform:uppercase;">Date</div><div style="font-size:13px;margin-top:3px;">' + feFormatDate(f.datetime_uploaded) + '</div></div></div>';
    // Category override section
    if(window.allowManualOverride){
    html += '<div class="fe-props-section"><h4>✏️ Reclassify</h4>';
    html += '<div style="display:flex;gap:8px;align-items:center;">';
    html += '<select id="feOverrideCat" style="flex:1;padding:8px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;">';
    feCategories.forEach(function(c){ html += '<option value="'+c.file_category+'"'+(c.file_category===f.category_tag?' selected':'')+'>'+c.file_category+'</option>'; });
    html += '</select>';
    html += '<button onclick="feReclassify('+f.file_upload_id+',\''+( f.category_tag||'').replace(/'/g,"\\'")+'\')" style="padding:8px 14px;border:none;border-radius:6px;background:#7b1228;color:#fff;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;">Save</button>';
    html += '</div>';
    if(f.is_overridden==1 && f.original_category_tag && f.original_category_tag!==f.category_tag) html += '<div style="margin-top:6px;font-size:11px;color:#c89b2e;">⚠️ Previously overridden (original: '+(f.original_category_tag||'unknown')+')</div>';
    html += '</div>';
    }
    if(!window.allowManualOverride && f.is_overridden==1 && f.original_category_tag && f.original_category_tag!==f.category_tag) html += '<div class="fe-props-section"><div style="font-size:11px;color:#c89b2e;">⚠️ Previously overridden (original: '+(f.original_category_tag||'unknown')+')</div></div>';
    html += '<div class="fe-props-section" id="feNlpSection"><h4>🧠 Classification Analysis</h4><div style="text-align:center;padding:15px;color:#999;">Loading...</div></div>';
    html += '<div style="text-align:center;margin-top:16px;display:flex;gap:10px;justify-content:center;"><button onclick="feViewFile('+idx+');$(\'#fePropsModal\').hide();" style="padding:10px 24px;border-radius:8px;border:none;cursor:pointer;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;font-weight:600;font-size:13px;">👁️ View</button><button onclick="feDownloadFile(null,null,'+f.file_upload_id+')" style="padding:10px 24px;border-radius:8px;border:none;cursor:pointer;background:linear-gradient(135deg,#7b1228,#9c1530);color:#fff;font-weight:600;font-size:13px;">⬇️ Download</button></div>';
    $('#fePropsContent').html(html); $('#fePropsModal').css('display','flex');
    $.post('ajax.php',{CALL:'get_nlp_analysis',file_id:f.file_upload_id},function(r){
      var h='<h4>🧠 Classification Analysis</h4>';
      if(r.status==='SUCCESS'&&r.analysis){
        var a=r.analysis,conf=parseFloat(a.category_confidence||0),cc=conf>=70?'#16a34a':conf>=40?'#c89b2e':'#dc2626';
        h+='<div style="margin-bottom:10px;"><span style="background:'+cc+'15;color:'+cc+';padding:4px 10px;border-radius:4px;font-size:12px;font-weight:bold;">'+( a.provider||'Unknown')+'</span></div>';
        h+='<div style="margin-bottom:12px;"><div style="font-size:11px;color:#666;margin-bottom:4px;">Confidence: '+conf.toFixed(1)+'%</div><div class="fe-confidence-bar"><div class="fe-confidence-fill" style="width:'+Math.max(conf,2)+'%;background:'+cc+';"></div><span class="fe-confidence-label">'+conf.toFixed(1)+'%</span></div></div>';
        if(a.keywords){var kw=typeof a.keywords==='string'?JSON.parse(a.keywords):a.keywords;if(kw&&kw.length){h+='<div style="margin-bottom:10px;"><div style="font-size:11px;color:#666;margin-bottom:4px;">Keywords</div>';kw.forEach(function(k){h+='<span class="fe-keyword-tag">'+k+'</span>';});h+='</div>';}}
        if(a.word_count) h+='<div style="font-size:11px;color:#666;">Word count: <strong>'+a.word_count+'</strong></div>';
      } else { h+='<div style="text-align:center;padding:10px;color:#94a3b8;font-size:12px;">No analysis available.</div>'; }
      $('#feNlpSection').html(h);
    },'json').fail(function(){$('#feNlpSection').html('<h4>🧠 Classification Analysis</h4><div style="color:#94a3b8;font-size:12px;">Could not load.</div>');});
  };

  $('#fePropsModal').click(function(e){ if(e.target===this) $(this).hide(); });

  function feLoadFolders(){ $.post('ajax.php',{CALL:'file_explorer',action:'get_folders'},function(r){ if(r.success){feFolders=r.folders;feRender();} },'json'); }
  function feLoadFiles(id){ $.post('ajax.php',{CALL:'file_explorer',action:'get_files',category_id:id},function(r){ if(r.success){window.feFilesCache=r.files;feRenderFiles(r.files);} },'json'); }
  function feSearchFiles(q){ $.post('ajax.php',{CALL:'file_explorer',action:'search',query:q,overridden_only:feOverriddenActive?'1':'0'},function(r){ if(r.success){window.feFilesCache=r.files;var label='🔍 "'+q+'"'+(feOverriddenActive?' + ✎ Overridden':'');$('#feBreadcrumb').html('<a onclick="feGoHome()">📁 Home</a> <span>›</span> <span>'+label+'</span>');feRenderFiles(r.files);} },'json'); }
  function feRender(){ if(feCurrentFolder) feLoadFiles(feCurrentFolder.id); else feRenderFolders(); }

  function feRenderFolders(){
    var visibleFolders = feFolders.filter(function(f){ return parseInt(f.file_count) > 0; });
    if(!visibleFolders.length){ $('#feContent').html('<div class="fe-empty"><span class="fe-empty-icon">📂</span><div class="fe-empty-text">No categories found</div></div>'); return; }
    var html='';
    if(feView==='grid'){
      html='<div class="fe-grid">';
      visibleFolders.forEach(function(f){ html+='<div class="fe-card" onclick="feOpenFolder('+f.category_id+',\''+f.category_name.replace(/'/g,"\\'")+'\')"><div class="fe-card-icon">📁</div><div class="fe-card-name">'+f.category_name+'</div><div class="fe-card-meta">'+f.file_count+' file'+(f.file_count!=1?'s':'')+'</div></div>'; });
      html+='</div>';
    } else {
      html='<div class="fe-list">';
      visibleFolders.forEach(function(f){ html+='<div class="fe-list-row" onclick="feOpenFolder('+f.category_id+',\''+f.category_name.replace(/'/g,"\\'")+'\')"><div class="fe-list-icon">📁</div><div class="fe-list-name">'+f.category_name+' <span class="fe-folder-count">'+f.file_count+'</span></div><div class="fe-list-meta"></div></div>'; });
      html+='</div>';
    }
    $('#feContent').html(html);
  }

  function feRenderFiles(files){
    if(!files.length){ $('#feContent').html('<div class="fe-empty"><span class="fe-empty-icon">📄</span><div class="fe-empty-text">No files in this folder</div></div>'); return; }
    var html='';
    if(feView==='grid'){
      html='<div class="fe-grid">';
      files.forEach(function(f,i){ var ab=f.is_signed_document==1?'<div style="margin-top:4px;"><span style="background:#d1fae5;color:#065f46;padding:1px 8px;border-radius:10px;font-size:10px;font-weight:600;">✓ Approved</span></div>':''; html+='<div class="fe-card" onclick="feShowProps('+i+')"><div style="position:absolute;top:8px;right:8px;"><button onclick="feViewFile('+i+',event)" title="View" style="background:rgba(0,0,0,0.05);border:none;border-radius:6px;padding:4px 6px;cursor:pointer;font-size:12px;">👁️</button></div><div class="fe-card-icon">'+feGetIcon(f.mime_type)+'</div><div class="fe-card-name">'+f.original_filename+ab+'</div><div class="fe-card-meta">'+feFormatSize(f.file_size)+' • '+feFormatDate(f.datetime_uploaded)+'</div></div>'; });
      html+='</div>';
    } else {
      html='<div class="fe-list">';
      files.forEach(function(f,i){
        var approvedBadge = f.is_signed_document==1 ? ' <span style="background:#d1fae5;color:#065f46;padding:1px 8px;border-radius:10px;font-size:10px;font-weight:600;">✓ Approved</span>' : '';
        html+='<div class="fe-list-row" onclick="feShowProps('+i+')"><div class="fe-list-icon">'+feGetIcon(f.mime_type)+'</div><div class="fe-list-name">'+f.original_filename+(f.category_tag?' <span style="background:rgba(123,18,40,0.06);color:#7b1228;padding:1px 8px;border-radius:10px;font-size:10px;font-weight:500;">'+f.category_tag+'</span>':'')+approvedBadge+'</div><div class="fe-list-size">'+feFormatSize(f.file_size)+'</div><div class="fe-list-meta">'+feFormatDate(f.datetime_uploaded)+'</div><div class="fe-list-actions"><button onclick="feViewFile('+i+',event)" title="View">👁️</button><button onclick="feShowProps('+i+',event)" title="Properties">ℹ️</button><button onclick="feDownloadFile(null,event,'+f.file_upload_id+')" title="Download">⬇️</button><button onclick="feDeleteFile('+f.file_upload_id+',event)" title="Delete" style="color:#dc3545;">🗑️</button></div></div>';
      });
      html+='</div>';
    }
    $('#feContent').html(html);
  }

  function feGetIcon(m){ if(!m)return'📄'; if(m.includes('pdf'))return'📕'; if(m.includes('word')||m.includes('document'))return'📘'; if(m.includes('sheet')||m.includes('excel'))return'📗'; if(m.includes('presentation')||m.includes('powerpoint'))return'📙'; if(m.includes('text'))return'📝'; return'📄'; }
  function feFormatSize(b){ if(!b)return'—'; b=parseInt(b); if(b<1024)return b+' B'; if(b<1048576)return(b/1024).toFixed(1)+' KB'; return(b/1048576).toFixed(1)+' MB'; }
  function feFormatDate(d){ if(!d)return'—'; return new Date(d).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}); }

  window.feDeleteFile = function(id,e){
    if(e)e.stopPropagation();
    Swal.fire({title:'Delete file?',text:'This cannot be undone.',icon:'warning',showCancelButton:true,confirmButtonColor:'#7b1228',confirmButtonText:'Delete'}).then(function(r){
      if(r.isConfirmed){ $.post('ajax.php',{CALL:'file_explorer',action:'delete_file',file_id:id},function(r){ if(r.success){Swal.fire('Deleted','File removed.','success');$('#fePropsModal').hide();if(feCurrentFolder)feLoadFiles(feCurrentFolder.id);else feLoadFolders();}else{Swal.fire('Error',r.error||'Failed.','error');} },'json'); }
    });
  };

  window.feReclassify = function(fileId, currentCat){
    var newCat = $('#feOverrideCat').val();
    if(newCat === currentCat){ Swal.fire('No change','Category is already '+currentCat+'.','info'); return; }
    Swal.fire({title:'Reclassify file?',html:'Move from <b>'+currentCat+'</b> to <b>'+newCat+'</b>?<br><small>File will be moved and model will learn from this correction.</small>',icon:'question',showCancelButton:true,confirmButtonColor:'#7b1228',confirmButtonText:'Reclassify'}).then(function(r){
      if(r.isConfirmed){
        $.post('ajax.php',{CALL:'reclassify_file',file_id:fileId,new_category:newCat},function(r){
          if(r.success){ Swal.fire('Done','File reclassified to '+newCat+'.','success'); $('#fePropsModal').hide(); if(feCurrentFolder)feLoadFiles(feCurrentFolder.id); else feLoadFolders(); }
          else { Swal.fire('Error',r.error||'Failed.','error'); }
        },'json');
      }
    });
  };

  function feFilterOverridden(){
    $('#feBreadcrumb').html('<a onclick="feGoHome()">📁 Home</a> <span>›</span> <span>✎ Overridden Files</span>');
    $.post('ajax.php',{CALL:'file_explorer',action:'get_overridden'},function(r){ if(r.success){window.feFilesCache=r.files;feRenderFiles(r.files);} },'json');
  }

  $("#feSmartSearchBtn").click(function(){ $(".fc-tab[data-tab=\"search\"]").click(); });
  $("#feUploadBtn").click(function(){ $(".fc-tab[data-tab=\"upload\"]").click(); });
  $("#feOverriddenFilter").click(function(){
    feOverriddenActive = !feOverriddenActive;
    if(feOverriddenActive){
      $(this).css({'background':'#e65100','color':'#fff','border-color':'#e65100'});
    } else {
      $(this).css({'background':'#fff3e0','color':'#e65100','border-color':'#ffe0b2'});
    }
    var q = $('#feSearch').val().trim();
    if(q.length >= 2) { feSearchFiles(q); }
    else if(feOverriddenActive) { feFilterOverridden(); }
    else { feGoHome(); }
  });

  $("#feApprovedFilter").click(function(){
    feApprovedActive = !feApprovedActive;
    if(feApprovedActive){
      $(this).css({'background':'#065f46','color':'#fff','border-color':'#065f46'});
    } else {
      $(this).css({'background':'#ecfdf5','color':'#065f46','border-color':'#bbf7d0'});
    }
    var q = $('#feSearch').val().trim();
    if(q.length >= 2) { feSearchFiles(q); }
    else if(feApprovedActive) { feFilterApproved(); }
    else { feGoHome(); }
  });

  function feFilterApproved(){
    $('#feBreadcrumb').html('<a onclick="feGoHome()">📁 Home</a> <span>›</span> <span>✓ Approved Files</span>');
    $.post('ajax.php',{CALL:'file_explorer',action:'get_approved'},function(r){ if(r.success){window.feFilesCache=r.files;feRenderFiles(r.files);} },'json');
  }

  // File Viewer
  window.feViewFile = function(idx, e){
    if(e) e.stopPropagation();
    // Validity check
    var vd = window.fileViewValidityDate;
    if(vd){
      var today = new Date(); today.setHours(0,0,0,0);
      var valid = new Date(vd); valid.setHours(23,59,59,999);
      if(today > valid){ $('#feSubMsgModal').css('display','flex'); return; }
    }
    var f = window.feFilesCache[idx]; if(!f) return;
    var url = '../' + f.file_path;
    var mime = (f.mime_type||'').toLowerCase();
    var ext = f.original_filename.split('.').pop().toLowerCase();
    $('#feViewerTitle').text(f.original_filename);
    var body = $('#feViewerBody');
    body.html('<div style="color:#fff;">Loading...</div>');
    $('#feViewerModal').css('display','flex');

    if(mime.includes('pdf') || ext==='pdf'){
      body.html('<iframe src="'+url+'" style="width:100%;height:100%;border:none;border-radius:8px;background:#fff;"></iframe>');
    } else if(mime.includes('image') || ['jpg','jpeg','png','gif','bmp','webp'].indexOf(ext)!==-1){
      body.html('<img src="'+url+'" style="max-width:100%;max-height:100%;object-fit:contain;border-radius:8px;">');
    } else if(ext==='txt'){
      $.get(url,function(text){ body.html('<pre style="background:#fff;padding:20px;border-radius:8px;width:90%;max-height:100%;overflow:auto;font-size:13px;white-space:pre-wrap;">'+$('<span>').text(text).html()+'</pre>'); },'text');
    } else if(ext==='docx'||ext==='doc'){
      body.html('<div style="color:#fff;font-size:14px;">Converting document...</div>');
      $.post('ajax.php',{CALL:'file_explorer',action:'convert_docx',file_id:f.file_upload_id},function(r){
        if(r.success){
          body.html('<iframe src="../'+r.pdf_url+'" style="width:100%;height:100%;border:none;border-radius:8px;background:#fff;"></iframe>');
        } else if(r.error==='libreoffice_not_found'){
          var msg = window.userType==='admin'
            ? '<p style="font-size:14px;margin-bottom:16px;">LibreOffice is required to preview Word documents.</p><a href="https://www.libreoffice.org/download/download-libreoffice/" target="_blank" style="display:inline-block;padding:10px 20px;background:#2563eb;color:#fff;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;margin-bottom:12px;">⬇️ Download LibreOffice</a><p style="font-size:12px;color:#94a3b8;">Install it on the server, then restart the application.</p>'
            : '<p style="font-size:14px;margin-bottom:16px;">Document preview is currently unavailable.</p><p style="font-size:13px;color:#94a3b8;">Please contact your system administrator to enable this feature.</p>';
          body.html('<div style="text-align:center;color:#fff;"><div style="font-size:48px;margin-bottom:16px;">📄</div>'+msg+'<div style="margin-top:16px;"><button onclick="feDownloadFile(null,null,'+f.file_upload_id+')" style="padding:10px 24px;border:none;border-radius:8px;background:#7b1228;color:#fff;font-size:13px;font-weight:600;cursor:pointer;">⬇️ Download Instead</button></div></div>');
        } else {
          body.html('<div style="text-align:center;color:#fff;"><div style="font-size:48px;margin-bottom:16px;">⚠️</div><p style="font-size:14px;">Failed to convert document.</p><button onclick="feDownloadFile(null,null,'+f.file_upload_id+')" style="margin-top:12px;padding:10px 24px;border:none;border-radius:8px;background:#7b1228;color:#fff;font-size:13px;font-weight:600;cursor:pointer;">⬇️ Download Instead</button></div>');
        }
      },'json');
    } else if(ext==='xlsx'||ext==='xls'){
      if(typeof XLSX==='undefined'){
        var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js';s.onload=function(){renderXlsx(url,body);};document.head.appendChild(s);
      } else { renderXlsx(url,body); }
    } else {
      body.html('<div style="text-align:center;color:#fff;"><div style="font-size:48px;margin-bottom:16px;">📄</div><p style="font-size:14px;margin-bottom:16px;">Preview not available for this file type.</p><button onclick="feDownloadFile(null,null,'+f.file_upload_id+')" style="padding:10px 24px;border:none;border-radius:8px;background:#7b1228;color:#fff;font-size:13px;font-weight:600;cursor:pointer;">⬇️ Download Instead</button></div>');
    }
  };

  function renderXlsx(url, body){
    var xhr=new XMLHttpRequest(); xhr.open('GET',url,true); xhr.responseType='arraybuffer';
    xhr.onload=function(){
      var wb=XLSX.read(xhr.response,{type:'array'});
      var html='<div style="background:#fff;padding:20px;border-radius:8px;width:95%;max-height:100%;overflow:auto;">';
      wb.SheetNames.forEach(function(name){
        var ws=wb.Sheets[name];
        html+='<h4 style="margin:10px 0 6px;font-size:13px;color:#333;">'+name+'</h4>';
        html+=XLSX.utils.sheet_to_html(ws,{editable:false});
      });
      html+='</div>';
      body.html(html);
      body.find('table').css({width:'100%','border-collapse':'collapse','font-size':'12px'});
      body.find('td,th').css({border:'1px solid #e2e8f0',padding:'6px 8px'});
    };
    xhr.send();
  }
});
</script>
