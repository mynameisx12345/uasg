<!-- Conversational Smart Search -->
<style>
.chat-search-container { display:flex; flex-direction:column; height:calc(100vh - 280px); min-height:300px; }
.chat-messages { flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:12px; background:#f8f9fa; border-radius:8px; }
.chat-msg { max-width:80%; padding:10px 14px; border-radius:12px; font-size:13px; line-height:1.5; word-wrap:break-word; }
.chat-msg.bot { align-self:flex-start; background:#fff; border:1px solid #e0e0e0; }
.chat-msg.user { align-self:flex-end; background:#007bff; color:#fff; }
.chat-msg .msg-files { margin-top:8px; }
.chat-msg .file-card { background:#f0f7ff; border:1px solid #cce0ff; border-radius:6px; padding:8px 10px; margin-top:6px; cursor:pointer; transition:background 0.15s; display:flex; justify-content:space-between; align-items:center; }
.chat-msg .file-card:hover { background:#e0efff; }
.chat-msg .file-card .fc-name { font-weight:600; font-size:12px; color:#333; }
.chat-msg .file-card .fc-meta { font-size:11px; color:#666; }
.chat-input-area { display:flex; gap:8px; padding-top:12px; border-top:1px solid #eee; margin-top:12px; }
.chat-input-area input { flex:1; padding:12px 16px; border:2px solid #e0e0e0; border-radius:20px; font-size:14px; outline:none; transition:border-color 0.2s; }
.chat-input-area input:focus { border-color:#007bff; }
.chat-input-area button { padding:10px 20px; background:#007bff; color:#fff; border:none; border-radius:20px; font-size:14px; cursor:pointer; font-weight:500; }
.chat-input-area button:hover { background:#0056b3; }
.chat-input-area button:disabled { background:#ccc; cursor:not-allowed; }
.chat-typing { font-size:12px; color:#999; padding:4px 0; min-height:20px; }
.chat-suggestions { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
.chat-suggestions button { background:#e8f4fd; border:1px solid #b8daff; color:#0056b3; padding:5px 12px; border-radius:14px; font-size:11px; cursor:pointer; }
.chat-suggestions button:hover { background:#cce5ff; }
</style>

<div>
  <h3 style="margin:0 0 5px 0;">Smart Search</h3>
  <p style="color:#666;font-size:13px;margin-bottom:15px;">Ask questions about your files in natural language. I'll find what you need.</p>
</div>

<div class="chat-search-container">
  <div class="chat-messages" id="chatMessages">
    <div class="chat-msg bot">
      👋 Hi! I can help you find files. Try asking things like:
      <div class="chat-suggestions">
        <button onclick="csAsk(this.textContent)">Show all letters</button>
        <button onclick="csAsk(this.textContent)">Show all activity designs</button>
        <button onclick="csAsk(this.textContent)">Show all resolutions</button>
      </div>
    </div>
  </div>
  <div class="chat-typing" id="chatTyping"></div>
  <div class="chat-input-area">
    <input type="text" id="chatInput" placeholder="Ask about your files...">
    <button type="button" id="chatSendBtn">Send</button>
  </div>
</div>

<script>
$(function(){
  var chatHistory = [];

  $('#chatSendBtn').click(function(){ sendChat(); });
  $('#chatInput').keypress(function(e){ if(e.which===13) sendChat(); });

  window.csAsk = function(text){
    $('#chatInput').val(text);
    sendChat();
  };

  function sendChat(){
    var msg = $('#chatInput').val().trim();
    if(!msg) return;
    $('#chatInput').val('');
    appendMsg('user', msg);
    chatHistory.push({role:'user', text:msg});
    $('#chatSendBtn').prop('disabled',true);
    $('#chatTyping').text('Searching...');

    $.post('ajax.php', {
      CALL:'conversational_smart_search',
      message: msg,
      history: JSON.stringify(chatHistory.slice(-6))
    }, function(r){
      $('#chatSendBtn').prop('disabled',false);
      $('#chatTyping').text('');
      if(r.success){
        var reply = r.reply || 'No results found.';
        var files = r.files || [];
        if(r.debug) reply += ' [Filters: cat=' + (r.debug.category||'none') + ', uploader=' + (r.debug.uploader||'none') + ', date=' + (r.debug.date||'none') + ', mime=' + (r.debug.mime||'none') + ', keywords=' + (r.debug.keywords||[]).join(',') + ']';
        chatHistory.push({role:'bot', text:reply});
        appendMsg('bot', reply, files);
      } else {
        appendMsg('bot', r.error || 'Something went wrong. Try rephrasing your question.');
      }
    },'json').fail(function(){
      $('#chatSendBtn').prop('disabled',false);
      $('#chatTyping').text('');
      appendMsg('bot','Request failed. Please try again.');
    });
  }

  function appendMsg(role, text, files){
    var html = '<div class="chat-msg '+role+'">';
    html += escHtml(text);
    if(files && files.length){
      var msgId = 'msg_' + Date.now();
      window._csFileSets = window._csFileSets || {};
      window._csFileSets[msgId] = files;
      html += '<div class="msg-files">';
      files.forEach(function(f,i){
        html += '<div class="file-card" onclick="csOpenFile(\''+msgId+'\','+i+')">';
        html += '<div><div class="fc-name">'+getIcon(f.mime_type)+' '+escHtml(f.original_filename)+'</div>';
        html += '<div class="fc-meta">'+(f.category_tag||'Uncategorized')+' • '+(f.uploaded_by||'')+' • '+fmtDate(f.datetime_uploaded)+'</div></div>';
        html += '<div class="fc-meta">'+f.match_count+'/'+f.total_keywords+' match</div>';
        html += '</div>';
      });
      html += '</div>';
    }
    html += '</div>';
    $('#chatMessages').append(html);
    $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);
  }

  window.csOpenFile = function(msgId, idx){
    var files = (window._csFileSets || {})[msgId];
    if(files){
      window.feFilesCache = files;
      if(typeof feShowProps === "function") feShowProps(idx);
    }
  };

  function escHtml(t){ return $('<span>').text(t).html(); }
  function getIcon(m){ if(!m)return'📄'; if(m.includes('pdf'))return'📕'; if(m.includes('word')||m.includes('document'))return'📘'; return'📄'; }
  function fmtDate(d){ if(!d)return''; return new Date(d).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}); }
});
</script>
