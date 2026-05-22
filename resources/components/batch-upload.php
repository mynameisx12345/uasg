<!-- Batch Upload Tab Content -->
<style>
  .upload-zone {
    border:2px dashed rgba(123,18,40,0.25); border-radius:14px; padding:40px 20px;
    text-align:center; background:linear-gradient(135deg, rgba(123,18,40,0.03), rgba(200,155,46,0.03));
    transition:all 0.3s ease; cursor:pointer; margin-bottom:20px;
  }
  .upload-zone:hover, .upload-zone.dragover {
    border-color:#7b1228; background:linear-gradient(135deg, rgba(123,18,40,0.06), rgba(200,155,46,0.06));
    transform:translateY(-2px); box-shadow:0 8px 25px rgba(123,18,40,0.1);
  }
  .upload-zone-icon { font-size:48px; margin-bottom:12px; opacity:0.7; }
  .upload-zone-title { font-size:16px; font-weight:600; color:#1e293b; margin-bottom:6px; }
  .upload-zone-sub { font-size:13px; color:#64748b; }
  .upload-actions { display:flex; align-items:center; gap:12px; margin-bottom:20px; flex-wrap:wrap; }
  .upload-actions .btn-upload {
    padding:10px 20px; border-radius:8px; border:none; font-weight:600; font-size:13px;
    cursor:pointer; transition:all 0.2s; display:inline-flex; align-items:center; gap:6px;
  }
  .btn-upload-primary { background:#7b1228; color:#fff; }
  .btn-upload-primary:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 4px 12px rgba(123,18,40,0.2); }
  .btn-upload-primary:disabled { opacity:0.5; cursor:not-allowed; transform:none; }
  .btn-upload-secondary { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
  .btn-upload-secondary:hover:not(:disabled) { background:#e2e8f0; }
  .btn-upload-secondary:disabled { opacity:0.5; cursor:not-allowed; }
  .upload-file-count { font-size:13px; color:#64748b; font-weight:500; }
  .upload-table { width:100%; border-collapse:collapse; }
  .upload-table thead th { padding:10px 12px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; color:#64748b; border-bottom:2px solid #e2e8f0; text-align:left; }
  .upload-table tbody td { padding:12px; font-size:13px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
  .upload-table tbody tr:hover { background:#fafbfc; }
  .upload-table .file-name { font-weight:500; color:#1e293b; }
  .upload-cat-badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:600; background:rgba(123,18,40,0.08); color:#7b1228; }
  .upload-status { font-size:12px; font-weight:500; }
  .upload-status.analyzing { color:#c89b2e; }
  .upload-status.ready { color:#16a34a; }
  .upload-status.uploading { color:#c89b2e; }
  .upload-status.done { color:#16a34a; }
  .upload-status.error { color:#dc2626; }
  .upload-action-btn { padding:4px 10px; border:none; border-radius:6px; font-size:11px; font-weight:500; cursor:pointer; transition:all 0.15s; }
  .upload-action-btn.view { background:#f0e6f6; color:#7c3aed; }
  .upload-action-btn.view:hover { background:#e4d4f4; }
  .upload-action-btn.remove { background:#fef2f2; color:#dc2626; }
  .upload-action-btn.remove:hover { background:#fee2e2; }
  .upload-action-btn:disabled { opacity:0.4; cursor:not-allowed; }
  .upload-cat-select { padding:4px 8px; border-radius:6px; border:1px solid #e2e8f0; font-size:11px; font-weight:500; background:#fff; color:#1e293b; cursor:pointer; max-width:150px; }
  .upload-cat-select.overridden { border-color:#c89b2e; background:rgba(200,155,46,0.06); }
  .override-badge { display:inline-block; font-size:9px; color:#c89b2e; font-weight:600; margin-left:4px; }
  .upload-progress-wrap { margin-top:16px; padding:14px 18px; background:linear-gradient(135deg,#f8fafc,#f1f5f9); border-radius:10px; border:1px solid #e2e8f0; }
  .upload-progress-bar-bg { background:#e2e8f0; height:6px; border-radius:3px; overflow:hidden; margin-top:10px; }
  .upload-progress-bar-fill { background:linear-gradient(90deg,#7b1228,#c89b2e); height:100%; width:0%; transition:width 0.4s ease; border-radius:3px; }
  .upload-empty { padding:40px; text-align:center; color:#94a3b8; font-size:14px; }
</style>

<div class="upload-zone" id="uploadDropZone">
  <div class="upload-zone-icon">📄</div>
  <div class="upload-zone-title">Drop files here or click to browse</div>
  <div class="upload-zone-sub">Supports .docx, .pdf, .txt, .doc — files will be auto-classified by ML</div>
</div>
<input type="file" id="batchFileInput" multiple accept=".docx,.pdf,.txt,.doc" style="display:none;">

<div class="upload-actions">
  <button type="button" id="batchUploadAllBtn" class="btn-upload btn-upload-primary" disabled>⬆️ Upload All</button>
  <button type="button" id="batchClearBtn" class="btn-upload btn-upload-secondary" disabled>🗑️ Clear</button>
  <span id="batchFileCount" class="upload-file-count">No files selected</span>
</div>

<table class="upload-table" id="batchTable">
  <thead>
    <tr>
      <th>#</th>
      <th>File Name</th>
      <th>Category</th>
      <th>Confidence</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody id="batchTableBody">
    <tr><td colspan="6" class="upload-empty">Select files to begin</td></tr>
  </tbody>
</table>

<div id="batchProgress" class="upload-progress-wrap" style="display:none;">
  <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:500;color:#475569;">
    <span id="batchProgressText">Processing...</span>
    <span id="batchProgressCount">0/0</span>
  </div>
  <div class="upload-progress-bar-bg">
    <div id="batchProgressBar" class="upload-progress-bar-fill"></div>
  </div>
</div>

<script>
$(function(){
  var batchFiles = [];
  var analyzing = 0;
  var availableCategories = [];

  // Load categories for override dropdown
  $.ajax({url:'ajax.php',type:'POST',data:{CALL:'get_categories'},dataType:'json',success:function(r){
    if(r.status==='SUCCESS'&&r.data) availableCategories=r.data.map(function(c){return c.file_category;});
  }});

  // Click to browse
  $('#uploadDropZone').on('click', function(){ $('#batchFileInput').trigger('click'); });

  // Drag and drop
  var zone = document.getElementById('uploadDropZone');
  ['dragenter','dragover'].forEach(function(e){ zone.addEventListener(e, function(ev){ ev.preventDefault(); zone.classList.add('dragover'); }); });
  ['dragleave','drop'].forEach(function(e){ zone.addEventListener(e, function(ev){ ev.preventDefault(); zone.classList.remove('dragover'); }); });
  zone.addEventListener('drop', function(ev){ var files = ev.dataTransfer.files; if(files.length) handleFiles(files); });

  $('#batchFileInput').change(function(){ if(this.files.length) handleFiles(this.files); this.value=''; });

  function handleFiles(files){
    var startIdx = batchFiles.length;
    for(var i=0;i<files.length;i++){
      batchFiles.push({file:files[i],category:null,confidence:null,status:'analyzing',error:null,analysisResult:null,manualOverride:false});
    }
    renderBatchTable();
    $('#batchClearBtn').prop('disabled',false);
    for(var j=startIdx;j<batchFiles.length;j++) analyzeFile(j);
  }

  function analyzeFile(idx){
    analyzing++; updateUploadBtn();
    var f=batchFiles[idx], formData=new FormData();
    formData.append('CALL','nlp_analyze'); formData.append('file',f.file);
    $.ajax({
      url:'ajax.php',type:'POST',data:formData,processData:false,contentType:false,dataType:'json',
      success:function(r){
        if(r.status==='SUCCESS'){f.category=r.category;f.confidence=r.score;f.nlpAnalysis=r.nlp_analysis;f.analysisResult=r;f.status='ready';}
        else{f.category='Others';f.confidence=0;f.status='ready';}
        analyzing--;updateUploadBtn();renderBatchTable();
      },
      error:function(){f.status='error';f.error='Analysis failed';analyzing--;updateUploadBtn();renderBatchTable();}
    });
  }

  function updateUploadBtn(){
    var hasReady=batchFiles.some(function(f){return f.status==='ready';});
    $('#batchUploadAllBtn').prop('disabled',analyzing>0||!hasReady);
  }

  $('#batchClearBtn').click(function(){batchFiles=[];analyzing=0;renderBatchTable();$('#batchUploadAllBtn,#batchClearBtn').prop('disabled',true);});

  window.removeBatchFile=function(idx){batchFiles.splice(idx,1);renderBatchTable();if(!batchFiles.length)$('#batchUploadAllBtn,#batchClearBtn').prop('disabled',true);updateUploadBtn();};
  window.viewBatchDetail=function(idx){
    var f=batchFiles[idx]; if(!f||!f.analysisResult)return;
    var r=f.analysisResult, threshold=r.confidence_threshold||30, html="";
    if(r.fallback_used){html+="<div style=\"background:#fff3cd;border:1px solid #ffc107;border-radius:6px;padding:10px;margin-bottom:12px;\">⚠️ <strong>Fell back to Others</strong> — "+(r.original_prediction||"Unknown")+" scored "+r.score+"%, below "+threshold+"% threshold.</div>";}
    else{html+="<div style=\"background:#d4edda;border:1px solid #28a745;border-radius:6px;padding:10px;margin-bottom:12px;\">✅ <strong>Classified as "+r.category+"</strong> ("+r.score+"%) — above "+threshold+"% threshold.</div>";}
    var source=(r.nlp_analysis&&r.nlp_analysis.provider)?r.nlp_analysis.provider:"Unknown";
    html+="<div style=\"display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;\"><div style=\"background:#f8f9fa;padding:10px;border-radius:6px;\"><div style=\"font-size:10px;color:#666;\">SOURCE</div><div style=\"font-size:13px;margin-top:3px;font-weight:bold;\">"+source+"</div></div><div style=\"background:#f8f9fa;padding:10px;border-radius:6px;\"><div style=\"font-size:10px;color:#666;\">WORD COUNT</div><div style=\"font-size:13px;margin-top:3px;font-weight:bold;\">"+(r.word_count||"N/A")+"</div></div></div>";
    if(r.all_scores&&Object.keys(r.all_scores).length>0){
      html+="<div style=\"font-size:12px;font-weight:bold;margin-bottom:8px;\">📊 Confidence per Category</div>";
      Object.entries(r.all_scores).sort(function(a,b){return b[1]-a[1];}).forEach(function(e){
        var cat=e[0],score=e[1],isW=cat===(r.original_prediction||r.category),c=isW?"#7b1228":"#cbd5e1";
        html+="<div style=\"display:flex;align-items:center;margin:4px 0;\"><span style=\"width:140px;font-size:11px;font-weight:"+(isW?"bold":"normal")+";\">"+cat+"</span><div style=\"flex:1;background:#f0f0f0;border-radius:3px;height:20px;position:relative;\"><div style=\"width:"+Math.max(score,1)+"%;background:"+c+";height:100%;border-radius:3px;\"></div><div style=\"position:absolute;left:"+threshold+"%;top:0;height:100%;border-left:2px dashed #dc2626;\"></div><span style=\"position:absolute;right:4px;top:2px;font-size:10px;font-weight:bold;\">"+score.toFixed(1)+"%</span></div></div>";
      });
    }
    if(r.top_keywords&&r.top_keywords.length>0){
      html+="<div style=\"margin-top:12px;font-size:12px;font-weight:bold;margin-bottom:6px;\">🔑 Key Terms</div>";
      r.top_keywords.forEach(function(k){html+="<span style=\"display:inline-block;background:rgba(123,18,40,0.08);padding:3px 8px;border-radius:12px;margin:2px;font-size:11px;color:#7b1228;\">"+k+"</span>";});
    }
    if(r.extracted_text){
      var p=r.extracted_text.length>200?r.extracted_text.substring(0,200)+"...":r.extracted_text;
      html+="<div style=\"margin-top:12px;background:#f8f9fa;padding:10px;border-radius:6px;\"><div style=\"font-size:11px;font-weight:bold;margin-bottom:4px;\">📄 Text Preview</div><div style=\"font-size:11px;color:#555;\">"+$("<span>").text(p).html()+"</div></div>";
    }
    $("#fePropsContent").html(html);$("#fePropsModal").css("display","flex");
  };

  window.overrideCategory=function(idx,val){
    var f=batchFiles[idx];
    f.category=val;
    f.manualOverride=(val!==(f.analysisResult?f.analysisResult.category||'Others':'Others'));
    renderBatchTable();
  };

  function renderBatchTable(){
    $('#batchFileCount').text(batchFiles.length?batchFiles.length+' file(s) selected':'No files selected');
    if(!batchFiles.length){$('#batchTableBody').html('<tr><td colspan="6" class="upload-empty">Select files to begin</td></tr>');return;}
    var html='';
    batchFiles.forEach(function(f,i){
      var status='<span class="upload-status '+f.status+'">';
      switch(f.status){
        case 'analyzing':status+='⏳ Analyzing...';break;
        case 'ready':status+='✓ Ready';break;
        case 'uploading':status+='⬆️ Uploading';break;
        case 'done':status+='✅ Done';break;
        case 'error':status+='❌ '+(f.error||'Error');break;
      }
      status+='</span>';
      var cat;
      if(f.status==='analyzing'){cat='<span style="color:#94a3b8;">...</span>';}
      else if(f.status==='done'||f.status==='uploading'){cat='<span class="upload-cat-badge">'+f.category+'</span>'+(f.manualOverride?'<span class="override-badge">✎ manual</span>':'');}
      else{
        // Build dropdown for override
        if(window.allowManualOverrideBatch){
          var opts='<option value="'+(f.category||'Others')+'" selected>'+(f.category||'Others')+'</option>';
          availableCategories.forEach(function(c){if(c!==f.category)opts+='<option value="'+c+'">'+c+'</option>';});
          cat='<select class="upload-cat-select'+(f.manualOverride?' overridden':'')+'" onchange="overrideCategory('+i+',this.value)">'+opts+'</select>';
          if(f.manualOverride) cat+='<span class="override-badge">✎ manual</span>';
        } else {
          cat='<span class="upload-cat-badge">'+(f.category||'Others')+'</span>';
        }
      }
      var conf=f.status==='analyzing'?'<span style="color:#94a3b8;">...</span>':(f.confidence!==null?f.confidence+'%':'—');
      var actions='';
      if(f.analysisResult) actions+='<button onclick="viewBatchDetail('+i+')" class="upload-action-btn view">Details</button> ';
      actions+='<button onclick="removeBatchFile('+i+')" class="upload-action-btn remove" '+(f.status==='done'||f.status==='uploading'||f.status==='analyzing'?'disabled':'')+'>✕</button>';
      html+='<tr><td>'+(i+1)+'</td><td class="file-name">'+f.file.name+'</td><td>'+cat+'</td><td>'+conf+'</td><td>'+status+'</td><td>'+actions+'</td></tr>';
    });
    $('#batchTableBody').html(html);
  }

  $('#batchUploadAllBtn').click(function(){
    if(!batchFiles.length)return;
    $('#batchUploadAllBtn,#batchClearBtn,#batchFileInput').prop('disabled',true);
    $('#batchProgress').show(); uploadNext(0);
  });

  function uploadNext(idx){
    while(idx<batchFiles.length&&batchFiles[idx].status!=='ready')idx++;
    if(idx>=batchFiles.length){
      var done=batchFiles.filter(function(f){return f.status==='done';}).length;
      $('#batchProgressText').text('Complete! '+done+'/'+batchFiles.length+' uploaded.');
      $('#batchProgressBar').css('width','100%');
      $('#batchUploadAllBtn').prop('disabled',true);$('#batchClearBtn,#batchFileInput').prop('disabled',false);
      Swal.fire({icon:'success',title:'Upload Complete',text:done+' of '+batchFiles.length+' files uploaded.',timer:3000});
      if(typeof feGoHome==='function')feGoHome();
      return;
    }
    var f=batchFiles[idx],done=batchFiles.filter(function(f){return f.status==='done';}).length;
    $('#batchProgressCount').text((done+1)+'/'+batchFiles.length);
    $('#batchProgressBar').css('width',((done/batchFiles.length)*100)+'%');
    var r=f.analysisResult;
    if(r&&r.fallback_used&&r.original_prediction){
      Swal.fire({icon:"question",title:f.file.name,html:"ML predicted <strong>"+r.original_prediction+"</strong> but confidence is below threshold.<br><br>Save as <strong>"+r.original_prediction+"</strong> or keep as <strong>Others</strong>?",confirmButtonText:"Use "+r.original_prediction,denyButtonText:"Keep as Others",showDenyButton:true,showCancelButton:false}).then(function(result){if(result.isConfirmed)f.category=r.original_prediction;renderBatchTable();doUpload(idx,f);});
      return;
    }
    doUpload(idx,f);
  }

  function doUpload(idx,f){
    f.status='uploading';renderBatchTable();
    var fd=new FormData();
    fd.append('CALL',16);fd.append('file',f.file);fd.append('uploaded_by',<?=$_SESSION['user_id']??0?>);
    fd.append('category_tag',f.category||'Others');fd.append('category_score',f.confidence||0);
    var origCat=(f.analysisResult?f.analysisResult.category:null)||'Others';
    fd.append('original_category',origCat);
    if(f.manualOverride) fd.append('manual_override','1');
    if(f.nlpAnalysis)fd.append('nlp_analysis',JSON.stringify(f.nlpAnalysis));
    $.ajax({
      url:'ajax.php',type:'POST',data:fd,processData:false,contentType:false,dataType:'json',
      success:function(r){f.status=r.success?'done':'error';if(!r.success)f.error=r.msg||'Failed';renderBatchTable();uploadNext(idx+1);},
      error:function(){f.status='error';f.error='Upload failed';renderBatchTable();uploadNext(idx+1);}
    });
  }
});
</script>
