<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once("../resources/class.php");

$db = Database::getInstance();

// Get filter parameters
$confidenceFilter = $_GET['confidence'] ?? 'all';
$categoryFilter = $_GET['category'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'pending';
$dateRange = $_GET['date_range'] ?? '30';

// Build query for predictions
$query = "
    SELECT 
        mlp.*,
        f.original_filename,
        f.file_path,
        f.category_tag as actual_category,
        f.mime_type,
        f.datetime_uploaded,
        nlp.extracted_text,
        nlp.word_count,
        m.model_name,
        p.fname, p.lname
    FROM ml_prediction_history_tbl mlp
    JOIN file_upload_tbl f ON mlp.file_upload_id = f.file_upload_id
    LEFT JOIN file_nlp_analysis_tbl nlp ON f.file_upload_id = nlp.file_upload_id
    LEFT JOIN ml_models_tbl m ON mlp.model_id = m.model_id
    LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id
    LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
    WHERE 1=1
";

$params = [];

// Date range filter
if ($dateRange != 'all') {
    $query .= " AND mlp.predicted_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";
    $params[':days'] = intval($dateRange);
}

// Confidence filter
if ($confidenceFilter == 'high') {
    $query .= " AND mlp.confidence_score >= 0.80";
} elseif ($confidenceFilter == 'medium') {
    $query .= " AND mlp.confidence_score >= 0.60 AND mlp.confidence_score < 0.80";
} elseif ($confidenceFilter == 'low') {
    $query .= " AND mlp.confidence_score < 0.60";
}

// Category filter
if ($categoryFilter != 'all') {
    $query .= " AND mlp.predicted_category = :category";
    $params[':category'] = $categoryFilter;
}

// Status filter
if ($statusFilter == 'accepted') {
    $query .= " AND mlp.was_accepted = 1";
} elseif ($statusFilter == 'rejected') {
    $query .= " AND mlp.was_accepted = 0";
} elseif ($statusFilter == 'pending') {
    $query .= " AND mlp.was_accepted IS NULL";
}

$query .= " ORDER BY mlp.predicted_at DESC LIMIT 100";

$stmt = $db->getConnection()->prepare($query);
$stmt->execute($params);
$predictions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats = $db->selectOne("
    SELECT 
        COUNT(*) as total_predictions,
        SUM(CASE WHEN was_accepted = 1 THEN 1 ELSE 0 END) as accepted,
        SUM(CASE WHEN was_accepted = 0 THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN was_accepted IS NULL THEN 1 ELSE 0 END) as pending,
        AVG(confidence_score) as avg_confidence
    FROM ml_prediction_history_tbl
    WHERE predicted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");

// Get all categories for filter
$categories = $db->select("
    SELECT DISTINCT predicted_category 
    FROM ml_prediction_history_tbl 
    WHERE predicted_category IS NOT NULL
    ORDER BY predicted_category
");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ML Feedback Review - UASG</title>
  <link rel="stylesheet" href="../resources/style.css?v=<?= time() ?>">
  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
  <script src='../js/all.js?v=<?= time() ?>'></script>
  <script src='../js/jquery.js?v=<?= time() ?>'></script>
  <script src='../js/datatable.js?v=<?= time() ?>'></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <!-- HEADER -->
  <?php require_once("header.php");?>
  <header class="topbar">
    <h1>🔍 ML Prediction Feedback</h1>
    <div class="user-info">
      <span>Welcome, <?= htmlspecialchars($_SESSION['fname'] ?? 'Admin') ?></span>
    </div>
  </header>

  <!-- MAIN -->
  <main class="main">
    <!-- SIDEBAR -->
    <?php require_once("sidebar.php");?>

    <!-- CONTENT -->
    <section class="content">
      <div class="card">
        <h2>🔍 ML Prediction Feedback</h2>

        <!-- STATISTICS CARDS -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
          <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 28px; font-weight: bold;"><?= $stats['total_predictions'] ?? 0 ?></div>
            <div style="opacity: 0.9;">Total Predictions</div>
            <small style="opacity: 0.8;">Last 30 days</small>
          </div>

          <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 28px; font-weight: bold;"><?= $stats['accepted'] ?? 0 ?></div>
            <div style="opacity: 0.9;">Accepted</div>
            <small style="opacity: 0.8;">
              <?= $stats['total_predictions'] > 0 ? number_format(($stats['accepted'] / $stats['total_predictions']) * 100, 1) : 0 ?>% acceptance
            </small>
          </div>

          <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 28px; font-weight: bold;"><?= $stats['pending'] ?? 0 ?></div>
            <div style="opacity: 0.9;">Pending Review</div>
            <small style="opacity: 0.8;">Awaiting feedback</small>
          </div>

          <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 28px; font-weight: bold;"><?= number_format(($stats['avg_confidence'] ?? 0) * 100, 1) ?>%</div>
            <div style="opacity: 0.9;">Avg Confidence</div>
            <small style="opacity: 0.8;">Model accuracy</small>
          </div>
        </div>

        <!-- TABS -->
        <div class="tabs">
          <button class="tab-link active" data-tab="all">📋 All Predictions</button>
          <button class="tab-link" data-tab="pending">⏳ Pending (<?= $stats['pending'] ?? 0 ?>)</button>
          <button class="tab-link" data-tab="accepted">✅ Accepted (<?= $stats['accepted'] ?? 0 ?>)</button>
          <button class="tab-link" data-tab="rejected">❌ Rejected (<?= $stats['rejected'] ?? 0 ?>)</button>
        </div>

        <!-- TAB CONTENT: ALL PREDICTIONS -->
        <div class="tab-content active" id="all">
          <!-- FILTERS -->
          <div class="compact-form" style="margin-bottom: 20px;">
            <h3>Filters</h3>
            <form method="GET" id="filterForm">
              <div class="form-columns">
                <div class="form-column">
                  <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control" name="status" id="status" onchange="this.form.submit()">
                      <option value="all" <?= $statusFilter == 'all' ? 'selected' : '' ?>>📋 All</option>
                      <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : '' ?>>⏳ Pending Review</option>
                      <option value="accepted" <?= $statusFilter == 'accepted' ? 'selected' : '' ?>>✅ Accepted</option>
                      <option value="rejected" <?= $statusFilter == 'rejected' ? 'selected' : '' ?>>❌ Rejected</option>
                    </select>
                  </div>
                </div>

                <div class="form-column">
                  <div class="form-group">
                    <label for="confidence">Confidence</label>
                    <select class="form-control" name="confidence" id="confidence" onchange="this.form.submit()">
                      <option value="all" <?= $confidenceFilter == 'all' ? 'selected' : '' ?>>All Levels</option>
                      <option value="high" <?= $confidenceFilter == 'high' ? 'selected' : '' ?>>🟢 High (≥80%)</option>
                      <option value="medium" <?= $confidenceFilter == 'medium' ? 'selected' : '' ?>>🟡 Medium (60-80%)</option>
                      <option value="low" <?= $confidenceFilter == 'low' ? 'selected' : '' ?>>🔴 Low (<60%)</option>
                    </select>
                  </div>
                </div>

                <div class="form-column">
                  <div class="form-group">
                    <label for="category">Category</label>
                    <select class="form-control" name="category" id="category" onchange="this.form.submit()">
                      <option value="all">All Categories</option>
                      <?php foreach (($categories ?? []) as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['predicted_category']) ?>" 
                                <?= $categoryFilter == $cat['predicted_category'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($cat['predicted_category']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div class="form-column">
                  <div class="form-group">
                    <label for="date_range">Date Range</label>
                    <select class="form-control" name="date_range" id="date_range" onchange="this.form.submit()">
                      <option value="7" <?= $dateRange == '7' ? 'selected' : '' ?>>Last 7 Days</option>
                      <option value="30" <?= $dateRange == '30' ? 'selected' : '' ?>>Last 30 Days</option>
                      <option value="90" <?= $dateRange == '90' ? 'selected' : '' ?>>Last 90 Days</option>
                      <option value="all" <?= $dateRange == 'all' ? 'selected' : '' ?>>All Time</option>
                    </select>
                  </div>
                </div>
              </div>
            </form>

            <div class="form-actions" style="margin-top: 15px; gap: 10px; display: flex; flex-wrap: wrap;">
              <button class="btn-primary" id="exportAcceptedBtn">
                <i class="fas fa-download"></i> Export Accepted
              </button>
              <button class="btn-success" id="retrainWithFeedbackBtn">
                <i class="fas fa-sync"></i> Retrain Model
              </button>
              <button class="btn-info" id="acceptAllHighConfidenceBtn">
                <i class="fas fa-check-double"></i> Accept High Confidence
              </button>
              <a href="ml-management.php" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to ML Management
              </a>
            </div>
          </div>

          <!-- PREDICTIONS LIST -->
          <div class="table-container">
            <h3>Predictions (<?= count($predictions) ?> results)</h3>
            <?php if (empty($predictions)): ?>
              <div style="text-align: center; padding: 60px 20px; color: #999;">
                <i class="fas fa-inbox" style="font-size: 64px; margin-bottom: 20px;"></i>
                <p style="font-size: 18px; margin: 0;">No predictions found with current filters.</p>
                <small>Try adjusting your filter criteria</small>
              </div>
            <?php else: ?>
              <?php foreach ($predictions as $pred): 
                $uploaderName = trim(($pred['fname'] ?? '') . ' ' . ($pred['lname'] ?? '')) ?: 'Unknown';
                $confidencePercent = number_format(($pred['confidence_score'] ?? 0) * 100, 2);
                $confidenceBadge = $pred['confidence_score'] >= 0.8 ? 'success' : ($pred['confidence_score'] >= 0.6 ? 'warning' : 'danger');
              ?>
                <div class="prediction-item" data-prediction-id="<?= $pred['prediction_id'] ?>" style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 15px; background: white; transition: all 0.3s;">
                  <div style="display: grid; grid-template-columns: 1fr auto; gap: 20px;">
                    <!-- File Info -->
                    <div>
                      <h4 style="margin: 0 0 10px 0; color: #333;">
                        <i class="fas fa-file-alt" style="color: #007bff;"></i>
                        <?= htmlspecialchars($pred['original_filename'] ?? 'Unknown File') ?>
                      </h4>
                      
                      <div style="margin-bottom: 12px;">
                        <span style="background: #007bff; color: white; padding: 4px 10px; border-radius: 12px; font-size: 13px; font-weight: 600;">
                          <?= htmlspecialchars($pred['predicted_category'] ?? 'N/A') ?>
                        </span>
                        <span style="background: <?= $confidenceBadge == 'success' ? '#28a745' : ($confidenceBadge == 'warning' ? '#ffc107' : '#dc3545') ?>; color: white; padding: 4px 10px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-left: 5px;">
                          <?= $confidencePercent ?>%
                        </span>
                        <span style="background: #6c757d; color: white; padding: 4px 10px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-left: 5px;">
                          <?= htmlspecialchars($pred['model_name'] ?? 'Unknown Model') ?>
                        </span>
                      </div>

                      <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                        <i class="fas fa-user"></i> <strong>Uploaded by:</strong> <?= htmlspecialchars($uploaderName) ?> 
                        <span style="margin-left: 15px;">
                          <i class="fas fa-calendar"></i> <?= date('M d, Y H:i', strtotime($pred['datetime_uploaded'])) ?>
                        </span>
                      </p>

                      <?php if (!empty($pred['extracted_text'])): ?>
                        <div style="background: #f8f9fa; padding: 12px; border-radius: 6px; max-height: 100px; overflow-y: auto;">
                          <small style="color: #666; line-height: 1.6;">
                            <?php 
                            $text = $pred['extracted_text'];
                            echo htmlspecialchars(substr($text, 0, 300));
                            echo strlen($text) > 300 ? '...' : '';
                            ?>
                          </small>
                        </div>
                      <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div style="text-align: right; min-width: 150px;">
                      <?php if ($pred['was_accepted'] === null): ?>
                        <!-- Pending -->
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                          <button class="btn-success accept-btn" 
                                  data-id="<?= $pred['prediction_id'] ?>"
                                  data-category="<?= htmlspecialchars($pred['predicted_category']) ?>"
                                  style="padding: 8px 16px; font-size: 14px;">
                            <i class="fas fa-check"></i> Accept
                          </button>
                          <button class="btn-danger reject-btn" 
                                  data-id="<?= $pred['prediction_id'] ?>"
                                  style="padding: 8px 16px; font-size: 14px;">
                            <i class="fas fa-times"></i> Reject
                          </button>
                          <button class="btn-warning edit-category-btn" 
                                  data-id="<?= $pred['prediction_id'] ?>"
                                  data-file-id="<?= $pred['file_upload_id'] ?>"
                                  data-current="<?= htmlspecialchars($pred['predicted_category']) ?>"
                                  style="padding: 8px 16px; font-size: 14px;">
                            <i class="fas fa-edit"></i> Edit
                          </button>
                        </div>
                      <?php elseif ($pred['was_accepted'] == 1): ?>
                        <!-- Accepted -->
                        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; border: 1px solid #c3e6cb;">
                          <i class="fas fa-check-circle"></i> <strong>Accepted</strong>
                          <?php if ($pred['actual_category'] != $pred['predicted_category']): ?>
                            <br><small style="color: #856404;">Corrected to: <?= htmlspecialchars($pred['actual_category']) ?></small>
                          <?php endif; ?>
                        </div>
                      <?php else: ?>
                        <!-- Rejected -->
                        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; border: 1px solid #f5c6cb;">
                          <i class="fas fa-times-circle"></i> <strong>Rejected</strong>
                          <?php if ($pred['actual_category']): ?>
                            <br><small>Actual: <?= htmlspecialchars($pred['actual_category']) ?></small>
                          <?php endif; ?>
                        </div>
                      <?php endif; ?>

                      <div style="margin-top: 12px; font-size: 12px; color: #999;">
                        <div>ID: <?= $pred['prediction_id'] ?></div>
                        <div>Time: <?= $pred['prediction_time_ms'] ?? 0 ?>ms</div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- TAB CONTENT: PENDING -->
        <div class="tab-content" id="pending">
          <p>Switch to "Pending" filter to see only pending predictions.</p>
          <button class="btn-primary" onclick="window.location.href='?status=pending'">View Pending Predictions</button>
        </div>

        <!-- TAB CONTENT: ACCEPTED -->
        <div class="tab-content" id="accepted">
          <p>Switch to "Accepted" filter to see accepted predictions.</p>
          <button class="btn-success" onclick="window.location.href='?status=accepted'">View Accepted Predictions</button>
        </div>

        <!-- TAB CONTENT: REJECTED -->
        <div class="tab-content" id="rejected">
          <p>Switch to "Rejected" filter to see rejected predictions.</p>
          <button class="btn-danger" onclick="window.location.href='?status=rejected'">View Rejected Predictions</button>
        </div>

      </div>
    </section>
  </main>

  <!-- Edit Category Modal -->
  <div id="editCategoryModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
      <span class="modal-close" onclick="closeEditModal()">&times;</span>
      <h2><i class="fas fa-edit"></i> Edit Category</h2>
      
      <div style="background: #e3f2fd; padding: 15px; border-left: 4px solid #2196F3; border-radius: 4px; margin: 20px 0;">
        <p style="margin: 0; color: #0d47a1;">
          <i class="fas fa-info-circle"></i> 
          Correct the category if the ML prediction was wrong. This will mark the prediction as accepted with the corrected category.
        </p>
      </div>

      <form id="editCategoryForm" class="compact-form">
        <input type="hidden" id="edit_prediction_id">
        <input type="hidden" id="edit_file_upload_id">
        
        <div class="form-group">
          <label>ML Predicted:</label>
          <input type="text" class="form-control" id="edit_predicted_category" readonly>
        </div>
        
        <div class="form-group">
          <label>Correct Category <span style="color: red;">*</span></label>
          <input type="text" class="form-control" id="edit_correct_category" 
                 placeholder="Enter the correct category" required>
          <small style="color: #666;">This will be used for retraining</small>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-success">
            <i class="fas fa-save"></i> Save & Accept
          </button>
          <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <?php require_once("modal.php");?>

  <script>
  function closeEditModal() {
    document.getElementById('editCategoryModal').style.display = 'none';
  }

  $(document).ready(function() {
    // Tab switching functionality
    $('.tab-link').click(function() {
      const tabId = $(this).data('tab');
      
      $('.tab-link').removeClass('active');
      $(this).addClass('active');
      
      $('.tab-content').removeClass('active');
      $('#' + tabId).addClass('active');
    });

    // Hover effect for prediction items
    $('.prediction-item').hover(
      function() { $(this).css('box-shadow', '0 4px 12px rgba(0,0,0,0.15)'); },
      function() { $(this).css('box-shadow', 'none'); }
    );

    // Accept prediction
    $('.accept-btn').click(function() {
      const predictionId = $(this).data('id');
      const category = $(this).data('category');
      
      Swal.fire({
        title: 'Accept this prediction?',
        text: `Category: ${category}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, Accept'
      }).then((result) => {
        if (result.isConfirmed) {
          $.post('ajax.php', {
            CALL: 'ml_operations',
            action: 'accept_prediction',
            prediction_id: predictionId,
            actual_category: category
          }, function(response) {
            if (response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Accepted!',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
              });
              location.reload();
            } else {
              Swal.fire('Error', response.message || 'Failed to accept', 'error');
            }
          });
        }
      });
    });
    
    // Reject prediction
    $('.reject-btn').click(function() {
      const predictionId = $(this).data('id');
      
      Swal.fire({
        title: 'Reject this prediction?',
        text: 'This will not be included in training data',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, Reject'
      }).then((result) => {
        if (result.isConfirmed) {
          $.post('ajax.php', {
            CALL: 'ml_operations',
            action: 'reject_prediction',
            prediction_id: predictionId
          }, function(response) {
            if (response.success) {
              Swal.fire('Rejected!', '', 'success');
              location.reload();
            } else {
              Swal.fire('Error', response.message || 'Failed to reject', 'error');
            }
          });
        }
      });
    });
    
    // Edit category
    $('.edit-category-btn').click(function() {
      const predictionId = $(this).data('id');
      const fileId = $(this).data('file-id');
      const currentCategory = $(this).data('current');
      
      $('#edit_prediction_id').val(predictionId);
      $('#edit_file_upload_id').val(fileId);
      $('#edit_predicted_category').val(currentCategory);
      $('#edit_correct_category').val(currentCategory);
      
      document.getElementById('editCategoryModal').style.display = 'flex';
    });
    
    // Submit category edit
    $('#editCategoryForm').submit(function(e) {
      e.preventDefault();
      
      $.post('ajax.php', {
        CALL: 'ml_operations',
        action: 'edit_prediction_category',
        prediction_id: $('#edit_prediction_id').val(),
        file_upload_id: $('#edit_file_upload_id').val(),
        correct_category: $('#edit_correct_category').val()
      }, function(response) {
        if (response.success) {
          Swal.fire('Saved!', 'Category corrected and accepted', 'success');
          closeEditModal();
          location.reload();
        } else {
          Swal.fire('Error', response.message || 'Failed to save', 'error');
        }
      });
    });
    
    // Export accepted predictions
    $('#exportAcceptedBtn').click(function() {
      window.location.href = 'ajax.php?CALL=ml_operations&action=export_accepted_predictions';
    });
    
    // Retrain with feedback
    $('#retrainWithFeedbackBtn').click(function() {
      Swal.fire({
        title: 'Retrain with Accepted Data?',
        html: 'This will create a new model using accepted predictions as additional training data.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Retrain',
        showLoaderOnConfirm: true,
        preConfirm: () => {
          return $.post('ajax.php', {
            CALL: 'ml_operations',
            action: 'retrain_with_feedback'
          });
        }
      }).then((result) => {
        if (result.isConfirmed && result.value.success) {
          Swal.fire({
            icon: 'success',
            title: 'Model Retrained!',
            html: `<strong>New Accuracy:</strong> ${(result.value.accuracy * 100).toFixed(2)}%<br>
                   <strong>Training Samples:</strong> ${result.value.training_samples}`,
            timer: 5000
          }).then(() => {
            window.location.href = 'ml-management.php';
          });
        } else if (result.isConfirmed) {
          Swal.fire('Error', result.value.message || 'Retraining failed', 'error');
        }
      });
    });
    
    // Accept all high confidence
    $('#acceptAllHighConfidenceBtn').click(function() {
      Swal.fire({
        title: 'Accept All High Confidence?',
        text: 'This will accept all pending predictions with ≥90% confidence',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Accept All'
      }).then((result) => {
        if (result.isConfirmed) {
          $.post('ajax.php', {
            CALL: 'ml_operations',
            action: 'accept_all_high_confidence'
          }, function(response) {
            if (response.success) {
              Swal.fire('Success!', `Accepted ${response.count} predictions`, 'success');
              location.reload();
            } else {
              Swal.fire('Error', response.message || 'Failed to accept', 'error');
            }
          });
        }
      });
    });
  });
  </script>

  <style>
  .prediction-item {
    transition: all 0.3s ease;
  }
  .prediction-item:hover {
    transform: translateY(-2px);
  }
  </style>
</body>
</html>
