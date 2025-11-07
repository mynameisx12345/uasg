<!-- Task Creation/Edit Modal -->
<div id="taskModal" class="modal">
  <div class="modal-content modal-content-large">
    <span class="modal-close" onclick="closeTaskModal()">&times;</span>
    <h2 id="taskModalTitle">Create New Task</h2>
    
    <div class="compact-form">
      <div class="form-section">
        <h4>Task Information</h4>
        <div class="form-row">
          <div class="form-group">
            <label for="modalTaskTitle">Task Title</label>
            <input type="text" id="modalTaskTitle" name="modalTaskTitle" placeholder="Enter task title..." required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="modalTaskCategory">Category</label>
            <select id="modalTaskCategory" name="modalTaskCategory" required>
              <option value="">Select Category</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="modalTaskDescription">Description</label>
            <textarea id="modalTaskDescription" name="modalTaskDescription" rows="4" placeholder="Enter task description..."></textarea>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="modalTaskDeadline">Deadline</label>
            <input type="datetime-local" id="modalTaskDeadline" name="modalTaskDeadline" required>
          </div>
        </div>
      </div>
      
      <div class="form-actions">
        <button type="submit" class="btn-primary" id="saveModalTask">Save Task</button>
        <button type="button" class="btn-secondary" onclick="closeTaskModal()">Cancel</button>
      </div>
    </div>
  </div>
</div>

<!-- Submission View Modal -->
<div id="submissionModal" class="modal">
  <div class="modal-content modal-content-large">
    <span class="modal-close" onclick="closeSubmissionModal()">&times;</span>
    <h2>Submission Details</h2>
    
    <div class="compact-form">
      <div class="form-section">
        <h4>Submission Information</h4>
        <div class="form-row">
          <div class="form-group">
            <label>Task Title</label>
            <input type="text" id="submissionTaskTitle" readonly>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Student Name</label>
            <input type="text" id="submissionStudentName" readonly>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>File Name</label>
            <input type="text" id="submissionFileName" readonly>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Submission Date</label>
            <input type="text" id="submissionDate" readonly>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Status</label>
            <input type="text" id="submissionStatus" readonly>
          </div>
        </div>
        <div class="form-row" id="submissionReasonRow" style="display: none;">
          <div class="form-group">
            <label>Reason</label>
            <textarea id="submissionReason" rows="3" readonly></textarea>
          </div>
        </div>
      </div>
      
      <div class="form-actions">
        <button type="button" class="btn-primary" id="downloadSubmissionBtn">Download File</button>
        <button type="button" class="btn-secondary" onclick="closeSubmissionModal()">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Review Submission Modal -->
<div id="reviewModal" class="modal">
  <div class="modal-content modal-content-small">
    <span class="modal-close" onclick="closeReviewModal()">&times;</span>
    <h2>Review Submission</h2>
    
    <div class="compact-form">
      <div class="form-section">
        <h4>Review Details</h4>
        <div class="form-row">
          <div class="form-group">
            <label>Task</label>
            <input type="text" id="reviewTaskTitle" readonly>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Student</label>
            <input type="text" id="reviewStudentName" readonly>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="reviewStatus">Review Status</label>
            <select id="reviewStatus" required>
              <option value="">Select Status</option>
              <option value="approved">Approve</option>
              <option value="rejected">Reject</option>
            </select>
          </div>
        </div>
        <div class="form-row" id="reviewReasonRow" style="display: none;">
          <div class="form-group">
            <label for="reviewReason">Reason (Required for Rejection)</label>
            <textarea id="reviewReason" rows="3" placeholder="Please provide a reason..."></textarea>
          </div>
        </div>
      </div>
      
      <input type="hidden" id="reviewSubmissionId">
      
      <div class="form-actions">
        <button type="submit" class="btn-primary" id="submitReview">Submit Review</button>
        <button type="button" class="btn-secondary" onclick="closeReviewModal()">Cancel</button>
      </div>
    </div>
  </div>
</div>

<!-- General Notification Modal -->
<div id="notificationModal" class="modal">
  <div class="modal-content modal-content-small">
    <span class="modal-close" onclick="closeNotificationModal()">&times;</span>
    <h2 id="notificationTitle">Notification</h2>
    
    <div class="compact-form">
      <div class="form-section">
        <div class="notification-message" id="notificationMessage">
          <!-- Message content will be inserted here -->
        </div>
      </div>
      
      <div class="form-actions">
        <button type="button" class="btn-primary" onclick="closeNotificationModal()">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
// Modal management functions
function openTaskModal(taskData = null) {
  if (taskData) {
    // Edit mode
    $('#taskModalTitle').text('Edit Task');
    $('#modalTaskTitle').val(taskData.task_title);
    $('#modalTaskCategory').val(taskData.task_category_id);
    $('#modalTaskDescription').val(taskData.task_description);
    $('#modalTaskDeadline').val(taskData.task_deadline);
    $('#saveModalTask').data('task-id', taskData.task_id);
  } else {
    // Create mode
    $('#taskModalTitle').text('Create New Task');
    $('#modalTaskTitle').val('');
    $('#modalTaskCategory').val('');
    $('#modalTaskDescription').val('');
    $('#modalTaskDeadline').val('');
    $('#saveModalTask').removeData('task-id');
  }
  document.getElementById("taskModal").style.display = "flex";
}

function closeTaskModal() {
  document.getElementById("taskModal").style.display = "none";
}

function openSubmissionModal(submissionData) {
  $('#submissionTaskTitle').val(submissionData.task_title);
  $('#submissionStudentName').val(submissionData.fname + ' ' + submissionData.lname);
  $('#submissionFileName').val(submissionData.file_name);
  $('#submissionDate').val(new Date(submissionData.submission_date).toLocaleString());
  $('#submissionStatus').val(submissionData.check_status.toUpperCase());
  
  if (submissionData.reason) {
    $('#submissionReason').val(submissionData.reason);
    $('#submissionReasonRow').show();
  } else {
    $('#submissionReasonRow').hide();
  }
  
  $('#downloadSubmissionBtn').data('file-name', submissionData.file_name);
  document.getElementById("submissionModal").style.display = "flex";
}

function closeSubmissionModal() {
  document.getElementById("submissionModal").style.display = "none";
}

function openReviewModal(submissionData) {
  $('#reviewTaskTitle').val(submissionData.task_title);
  $('#reviewStudentName').val(submissionData.fname + ' ' + submissionData.lname);
  $('#reviewSubmissionId').val(submissionData.submission_id);
  $('#reviewStatus').val('');
  $('#reviewReason').val('');
  $('#reviewReasonRow').hide();
  
  document.getElementById("reviewModal").style.display = "flex";
}

function closeReviewModal() {
  document.getElementById("reviewModal").style.display = "none";
}

function openNotificationModal(title, message, type = 'info') {
  $('#notificationTitle').text(title);
  $('#notificationMessage').html(message);
  
  // Add type-based styling
  const modal = $('#notificationModal');
  modal.removeClass('success error warning info');
  modal.addClass(type);
  
  document.getElementById("notificationModal").style.display = "flex";
}

function closeNotificationModal() {
  document.getElementById("notificationModal").style.display = "none";
}

// Global openModal function for compatibility
window.openModal = function(status, message) {
  const title = status === 'SUCCESS' ? 'Success' : 
                status === 'ERROR' ? 'Error' : 
                status === 'WARNING' ? 'Warning' : 'Information';
  const type = status.toLowerCase();
  openNotificationModal(title, message, type);
};

// Event handlers for modal forms
$(document).ready(function() {
  // Review status change handler
  $('#reviewStatus').change(function() {
    if ($(this).val() === 'rejected') {
      $('#reviewReasonRow').show();
      $('#reviewReason').prop('required', true);
    } else {
      $('#reviewReasonRow').hide();
      $('#reviewReason').prop('required', false);
    }
  });
  
  // Task modal save handler
  $('#saveModalTask').click(function() {
    const taskTitle = $('#modalTaskTitle').val().trim();
    const taskCategory = $('#modalTaskCategory').val();
    const taskDescription = $('#modalTaskDescription').val().trim();
    const taskDeadline = $('#modalTaskDeadline').val();
    
    if (!taskTitle || !taskCategory || !taskDeadline) {
      openNotificationModal('Error', 'Please fill in all required fields', 'error');
      return;
    }
    
    const taskData = {
      title: taskTitle,
      category: taskCategory,
      description: taskDescription,
      deadline: taskDeadline
    };
    
    const taskId = $(this).data('task-id');
    const call = taskId ? 'update_task' : 'create_task';
    
    if (taskId) {
      taskData.task_id = taskId;
    }
    
    $.ajax({
      url: 'ajax.php',
      type: 'post',
      data: {
        CALL: call,
        DATA: taskData
      },
      dataType: 'json',
      success: function(result) {
        openNotificationModal(
          result.status === 'SUCCESS' ? 'Success' : 'Error',
          result.msg,
          result.status.toLowerCase()
        );
        
        if (result.status === "SUCCESS") {
          closeTaskModal();
          // Reload tables
          if (typeof reloadAllTables === 'function') {
            reloadAllTables();
          }
        }
      },
      error: function() {
        openNotificationModal('Error', 'An error occurred while saving the task', 'error');
      }
    });
  });
  
  // Review submission handler
  $('#submitReview').click(function() {
    const submissionId = $('#reviewSubmissionId').val();
    const status = $('#reviewStatus').val();
    const reason = $('#reviewReason').val();
    
    if (!status) {
      openNotificationModal('Error', 'Please select a review status', 'error');
      return;
    }
    
    if (status === 'rejected' && !reason.trim()) {
      openNotificationModal('Error', 'Please provide a reason for rejection', 'error');
      return;
    }
    
    $.ajax({
      url: 'ajax.php',
      type: 'post',
      data: {
        CALL: 'review_submission',
        DATA: {
          submission_id: submissionId,
          status: status,
          reason: reason
        }
      },
      dataType: 'json',
      success: function(result) {
        openNotificationModal(
          result.status === 'SUCCESS' ? 'Success' : 'Error',
          result.msg,
          result.status.toLowerCase()
        );
        
        if (result.status === "SUCCESS") {
          closeReviewModal();
          // Reload tables
          if (typeof reloadAllTables === 'function') {
            reloadAllTables();
          }
        }
      },
      error: function() {
        openNotificationModal('Error', 'An error occurred while reviewing the submission', 'error');
      }
    });
  });
  
  // Download submission file handler
  $('#downloadSubmissionBtn').click(function() {
    const fileName = $(this).data('file-name');
    if (fileName) {
      // Implementation for file download
      window.location.href = '../uploads/' + fileName;
    }
  });
});
</script>