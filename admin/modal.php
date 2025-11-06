<div id="modal" class="modal">
  <div class="modal-content">
    <span class="modal-close" onclick="closeModal()">&times;</span>
    <h2 id="modal-title">Modal Title</h2>
    <p id="modal-message">Modal message goes here...</p>
    <div class="form-actions">
      <button class="btn-primary" onclick="closeModal()">OK</button>
    </div>
  </div>
</div>
<script>
  function openModal(title, message) {
    document.getElementById("modal-title").innerText = title;
    document.getElementById("modal-message").innerHTML = message;
    document.getElementById("modal").style.display = "flex";
  }

  function closeModal() {
    document.getElementById("modal").style.display = "none";
  }

  // Close modal when clicking outside content
  window.onclick = function(event) {
    const modal = document.getElementById("modal");
    if (event.target === modal) {
      closeModal();
    }
  };
</script>