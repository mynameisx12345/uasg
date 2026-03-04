<?php
session_start();
require_once("../resources/class.php");

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['position_id'] != 3) {
    header("Location: ../index.php");
    exit;
}

$db = Database::getInstance();

// Get ML settings
$mlSettings = $db->select("SELECT * FROM ml_settings_tbl");
$settings = [];
foreach ($mlSettings as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

// Get all datasets
$datasets = $db->select("
    SELECT d.*, u.user_name as uploader_name 
    FROM ml_training_datasets_tbl d
    LEFT JOIN user_tbl u ON d.uploaded_by = u.user_id
    ORDER BY d.uploaded_at DESC
");

// Get all models
$models = $db->select("
    SELECT m.*, d.dataset_name, u.user_name as trainer_name
    FROM ml_models_tbl m
    LEFT JOIN ml_training_datasets_tbl d ON m.dataset_id = d.dataset_id
    LEFT JOIN user_tbl u ON m.trained_by = u.user_id
    ORDER BY m.trained_at DESC
");

// Get active model
$activeModel = $db->selectOne("SELECT * FROM ml_models_tbl WHERE is_active = 1");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ML Classification Management - UASG</title>
  
  <link rel="stylesheet" href="../resources/style.css">
  <link rel="stylesheet" href="../resources/theme-overrides.css">
  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
  <style>
    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 4px;
      border-left: 4px solid;
    }
    .alert-success {
      background: #d4edda;
      border-left-color: #28a745;
      color: #155724;
    }
    .alert-warning {
      background: #fff3cd;
      border-left-color: #ffc107;
      color: #856404;
    }
    .alert h4 {
      margin: 0 0 10px 0;
    }
    .alert p {
      margin: 0;
    }
    .btn-group {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }
  </style>
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
  <script src='../js/datatable.js'></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <!-- HEADER -->
  <?php require_once("header.php");?>
  <header class="topbar">
    <h1>Machine Learning Classification</h1>
    <div class="user-info">
      <span>Welcome, Admin</span>
    </div>
  </header>
  
  <!-- MAIN -->
  <main class="main">
    <!-- SIDEBAR -->
    <?php require_once("sidebar.php");?>

    <!-- CONTENT -->
    <section class="content">
      <div class="card">
        <h2>🤖 ML Classification Management</h2>

        <!-- Active Model Status -->
        <?php if ($activeModel): ?>
        <div class="alert alert-success">
          <h4>✅ Active Model: <?= htmlspecialchars($activeModel['model_name']) ?></h4>
          <p>
            <strong>Accuracy:</strong> <?= number_format(($activeModel['accuracy_score'] ?? 0) * 100, 2) ?>% |
            <strong>F1 Score:</strong> <?= number_format(($activeModel['f1_score'] ?? 0) * 100, 2) ?>% |
            <strong>Categories:</strong> <?= $activeModel['categories_count'] ?? 0 ?> |
            <strong>Used:</strong> <?= $activeModel['usage_count'] ?? 0 ?> times
          </p>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
          <h4>⚠️ No Active Model</h4>
          <p>Upload training data and train a model to enable custom ML classification.</p>
        </div>
        <?php endif; ?>

        <!-- TABS -->
        <div class="tabs">
          <button class="tab-link active" data-tab="settings">⚙️ Settings</button>
          <button class="tab-link" data-tab="datasets">📊 Datasets</button>
          <button class="tab-link" data-tab="models">🎯 Models</button>
        </div>

        <!-- TAB CONTENT: SETTINGS -->
        <div class="tab-content active" id="settings">
          <div class="compact-form">
            <h3>ML Classification Settings</h3>
            <div class="form-columns">
              <div class="form-column">
                <div class="form-section">
                  <h4>Classification Method</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="classificationMethod">Method</label>
                      <select id="classificationMethod" name="classification_method">
                        <option value="nlpcloud" <?= ($settings['classification_method'] ?? '') == 'nlpcloud' ? 'selected' : '' ?>>NLP Cloud Only</option>
                        <option value="google_nlp" <?= ($settings['classification_method'] ?? '') == 'google_nlp' ? 'selected' : '' ?>>Google NLP Only</option>
                        <option value="custom_ml" <?= ($settings['classification_method'] ?? '') == 'custom_ml' ? 'selected' : '' ?>>Custom ML Only</option>
                        <option value="hybrid" <?= ($settings['classification_method'] ?? '') == 'hybrid' ? 'selected' : '' ?>>Hybrid (ML + NLP)</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="defaultAlgorithm">Default Algorithm</label>
                      <select id="defaultAlgorithm" name="default_ml_algorithm">
                        <option value="svm" <?= ($settings['default_ml_algorithm'] ?? '') == 'svm' ? 'selected' : '' ?>>SVM</option>
                        <option value="naive_bayes" <?= ($settings['default_ml_algorithm'] ?? '') == 'naive_bayes' ? 'selected' : '' ?>>Naive Bayes</option>
                        <option value="random_forest" <?= ($settings['default_ml_algorithm'] ?? '') == 'random_forest' ? 'selected' : '' ?>>Random Forest</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-column">
                <div class="form-section">
                  <h4>Confidence Settings</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="confidenceThreshold">ML Confidence Threshold (%)</label>
                      <input type="number" id="confidenceThreshold" name="ml_confidence_threshold"
                             value="<?= $settings['ml_confidence_threshold'] ?? 60 ?>" min="0" max="100">
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="nlpFallback">NLP Fallback</label>
                      <select id="nlpFallback" name="nlp_fallback_enabled">
                        <option value="1" <?= ($settings['nlp_fallback_enabled'] ?? '1') == '1' ? 'selected' : '' ?>>Enabled</option>
                        <option value="0" <?= ($settings['nlp_fallback_enabled'] ?? '1') == '0' ? 'selected' : '' ?>>Disabled</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-actions">
                    <button type="button" id="saveSettings" class="btn-primary">💾 Save Settings</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB CONTENT: DATASETS -->
        <div class="tab-content" id="datasets">
          <div class="compact-form">
            <h3>Training Datasets</h3>
            <div class="btn-group">
              <button type="button" class="btn-primary" id="uploadDatasetBtn">
                📤 Upload Dataset
              </button>
              <button type="button" class="btn-secondary" id="downloadTemplateBtn">
                📥 Download Template
              </button>
            </div>
          </div>
          
          <div class="table-container">
            <table class="data-table" id="datasetsTable">
              <thead>
                <tr>
                  <th>Dataset ID</th>
                  <th>Dataset Name</th>
                  <th>Uploaded By</th>
                  <th>Upload Date</th>
                  <th>File Size</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($datasets as $dataset): ?>
                <tr>
                  <td><?= $dataset['dataset_id'] ?></td>
                  <td><?= htmlspecialchars($dataset['dataset_name']) ?></td>
                  <td><?= htmlspecialchars($dataset['uploader_name'] ?? 'Unknown') ?></td>
                  <td><?= date('M d, Y', strtotime($dataset['uploaded_at'])) ?></td>
                  <td><?= isset($dataset['file_size']) ? number_format($dataset['file_size'] / 1024, 2) . ' KB' : 'N/A' ?></td>
                  <td>
                    <?php if (($dataset['status'] ?? 'ready') == 'ready'): ?>
                      <span style="color: green;">✓ Ready</span>
                    <?php else: ?>
                      <span style="color: orange;">⏳ Processing</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <button class="btn-primary trainBtn" data-id="<?= $dataset['dataset_id'] ?>" title="Train Model">
                      🎓 Train
                    </button>
                    <button class="btn-secondary deleteDatasetBtn" data-id="<?= $dataset['dataset_id'] ?>" title="Delete Dataset">
                      🗑️ Delete
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- TAB CONTENT: MODELS -->
        <div class="tab-content" id="models">
          <div class="compact-form">
            <h3>Trained Models</h3>
          </div>
          
          <div class="table-container">
            <table class="data-table" id="modelsTable">
              <thead>
                <tr>
                  <th>Model ID</th>
                  <th>Model Name</th>
                  <th>Dataset</th>
                  <th>Algorithm</th>
                  <th>Accuracy</th>
                  <th>F1 Score</th>
                  <th>Trained By</th>
                  <th>Trained Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($models as $model): ?>
                <tr>
                  <td><?= $model['model_id'] ?></td>
                  <td><?= htmlspecialchars($model['model_name']) ?></td>
                  <td><?= htmlspecialchars($model['dataset_name'] ?? 'N/A') ?></td>
                  <td><?= strtoupper($model['algorithm'] ?? $model['model_type'] ?? 'N/A') ?></td>
                  <td><?= number_format(($model['accuracy_score'] ?? 0) * 100, 2) ?>%</td>
                  <td><?= number_format(($model['f1_score'] ?? 0) * 100, 2) ?>%</td>
                  <td><?= htmlspecialchars($model['trainer_name'] ?? 'Unknown') ?></td>
                  <td><?= date('M d, Y', strtotime($model['trained_at'])) ?></td>
                  <td>
                    <?php if ($model['is_active']): ?>
                      <span style="color: green; font-weight: bold;">✓ Active</span>
                    <?php else: ?>
                      <span style="color: gray;">Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!$model['is_active']): ?>
                      <button class="btn-primary activateBtn" data-id="<?= $model['model_id'] ?>" title="Activate Model">
                        ⚡ Activate
                      </button>
                    <?php endif; ?>
                    <button class="btn-secondary deleteModelBtn" data-id="<?= $model['model_id'] ?>" title="Delete Model">
                      🗑️ Delete
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </section>
  </main>

  <!-- Upload Dataset Modal -->
  <div id="uploadDatasetModal" class="modal" style="display: none;">
    <div class="modal-content">
      <span class="close" onclick="$('#uploadDatasetModal').hide();">&times;</span>
      <h3>Upload Training Dataset</h3>
      <form id="uploadDatasetForm" enctype="multipart/form-data">
        <input type="hidden" name="CALL" value="ml_operations">
        <div class="form-group">
          <label>Dataset Name</label>
          <input type="text" id="datasetName" name="dataset_name" required>
        </div>
        <div class="form-group">
          <label>CSV File (text,category format)</label>
          <input type="file" id="datasetFile" name="csv_file" accept=".csv" required>
          <small style="color: #666;">Upload a CSV file with columns: text, category</small>
        </div>
        <div class="form-group">
          <button type="submit" class="btn-primary">Upload</button>
          <button type="button" class="btn-secondary" onclick="$('#uploadDatasetModal').hide();">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Train Model Modal -->
  <div id="trainModelModal" class="modal" style="display: none;">
    <div class="modal-content">
      <span class="close" onclick="$('#trainModelModal').hide();">&times;</span>
      <h3>Train ML Model</h3>
      <form id="trainModelForm">
        <input type="hidden" id="trainDatasetId" name="dataset_id">
        <div class="form-group">
          <label>Model Name</label>
          <input type="text" id="modelName" name="model_name" required>
        </div>
        <div class="form-group">
          <label>Algorithm</label>
          <select id="trainAlgorithm" name="algorithm">
            <option value="svm">SVM</option>
            <option value="naive_bayes">Naive Bayes</option>
            <option value="random_forest">Random Forest</option>
          </select>
        </div>
        <div class="form-group">
          <button type="submit" class="btn-primary">Train Model</button>
          <button type="button" class="btn-secondary" onclick="$('#trainModelModal').hide();">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Tab switcher (same as entry-module.php)
    const tabLinks = document.querySelectorAll(".tab-link");
    const tabContents = document.querySelectorAll(".tab-content");

    tabLinks.forEach(link => {
      link.addEventListener("click", () => {
        tabLinks.forEach(l => l.classList.remove("active"));
        tabContents.forEach(c => c.classList.remove("active"));

        link.classList.add("active");
        document.getElementById(link.dataset.tab).classList.add("active");
      });
    });

    // Initialize DataTables
    $(document).ready(function() {
      let datasetsTable = $('#datasetsTable').DataTable();
      let modelsTable = $('#modelsTable').DataTable();

      // Save Settings
      $('#saveSettings').click(function() {
        $.ajax({
          url: 'ajax.php',
          type: 'POST',
          data: {
            CALL: 'ml_operations',
            action: 'save_ml_settings',
            classification_method: $('#classificationMethod').val(),
            ml_confidence_threshold: $('#confidenceThreshold').val(),
            default_ml_algorithm: $('#defaultAlgorithm').val(),
            nlp_fallback_enabled: $('#nlpFallback').val()
          },
          dataType: 'json',
          success: function(result) {
            if (result && result.success) {
              Swal.fire({
                icon: 'success',
                title: 'Success',
                text: result.message || 'Settings saved successfully',
                timer: 2000
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to save settings',
                timer: 2000
              });
            }
          }
        });
      });

      // Upload Dataset Button
      $('#uploadDatasetBtn').click(function() {
        $('#uploadDatasetModal').show();
      });

      // Upload Dataset Form
      $('#uploadDatasetForm').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'upload_ml_dataset');

        // Show loading
        Swal.fire({
          title: 'Uploading Dataset...',
          text: 'Please wait while we process your file.',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        $.ajax({
          url: 'ajax.php',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          beforeSend: function(xhr) {
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
          },
          success: function(response, status, xhr) {
            // Try to parse as JSON if it's a string
            let result;
            if (typeof response === 'string') {
              try {
                result = JSON.parse(response);
              } catch (e) {
                // Response is not JSON, show the raw response
                Swal.fire({
                  icon: 'error',
                  title: 'Server Error',
                  html: '<pre style="text-align: left; max-height: 300px; overflow: auto;">' + 
                        response.substring(0, 1000) + '</pre>',
                  width: '600px'
                });
                return;
              }
            } else {
              result = response;
            }

            if (result && result.success) {
              Swal.fire({
                icon: 'success',
                title: 'Success',
                text: result.message || 'Dataset uploaded successfully',
                timer: 2000
              }).then(() => {
                $('#uploadDatasetModal').hide();
                location.reload();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || result.error || 'Upload failed',
                timer: 3000
              });
            }
          },
          error: function(xhr, status, error) {
            let errorMsg = 'Upload failed: ' + error;
            if (xhr.responseText) {
              errorMsg = xhr.responseText.substring(0, 500);
            }
            Swal.fire({
              icon: 'error',
              title: 'Upload Failed',
              html: '<pre style="text-align: left; max-height: 300px; overflow: auto;">' + errorMsg + '</pre>',
              width: '600px'
            });
          }
        });
      });

      // Train Model Button
      $(document).on('click', '.trainBtn', function() {
        const datasetId = $(this).data('id');
        $('#trainDatasetId').val(datasetId);
        $('#trainModelModal').show();
      });

      // Train Model Form
      $('#trainModelForm').submit(function(e) {
        e.preventDefault();

        Swal.fire({
          title: 'Training Model...',
          text: 'This may take a few minutes. Please wait.',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        $.ajax({
          url: 'ajax.php',
          type: 'POST',
          data: {
            CALL: 'ml_operations',
            action: 'train_ml_model',
            dataset_id: $('#trainDatasetId').val(),
            model_name: $('#modelName').val(),
            algorithm: $('#trainAlgorithm').val()
          },
          beforeSend: function(xhr) {
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
          },
          success: function(response, status, xhr) {
            // Try to parse as JSON if it's a string
            let result;
            if (typeof response === 'string') {
              try {
                result = JSON.parse(response);
              } catch (e) {
                // Response is not JSON, show the raw response
                Swal.fire({
                  icon: 'error',
                  title: 'Server Error',
                  html: '<pre style="text-align: left; max-height: 400px; overflow: auto; font-size: 12px;">' + 
                        response.substring(0, 2000) + '</pre>',
                  width: '700px'
                });
                return;
              }
            } else {
              result = response;
            }

            if (result && result.success) {
              Swal.fire({
                icon: 'success',
                title: 'Success',
                text: result.message || 'Model trained successfully',
                timer: 3000
              }).then(() => {
                $('#trainModelModal').hide();
                location.reload();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || result.error || 'Training failed',
                timer: 3000
              });
            }
          },
          error: function(xhr, status, error) {
            let errorMsg = 'Training failed: ' + error;
            if (xhr.responseText) {
              errorMsg = xhr.responseText.substring(0, 1000);
            }
            Swal.fire({
              icon: 'error',
              title: 'Training Failed',
              html: '<pre style="text-align: left; max-height: 400px; overflow: auto; font-size: 12px;">' + errorMsg + '</pre>',
              width: '700px'
            });
          }
        });
      });

      // Activate Model
      $(document).on('click', '.activateBtn', function() {
        const modelId = $(this).data('id');
        
        Swal.fire({
          title: 'Activate Model?',
          text: 'This will deactivate the current model.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, activate it!'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: 'ajax.php',
              type: 'POST',
              data: {
                CALL: 'ml_operations',
                action: 'activate_ml_model',
                model_id: modelId
              },
              dataType: 'json',
              success: function(result) {
                if (result && result.success) {
                  Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: result.message || 'Model activated successfully',
                    timer: 2000
                  }).then(() => {
                    location.reload();
                  });
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Activation failed',
                    timer: 2000
                  });
                }
              }
            });
          }
        });
      });

      // Delete Dataset
      $(document).on('click', '.deleteDatasetBtn', function() {
        const datasetId = $(this).data('id');
        
        Swal.fire({
          title: 'Delete Dataset?',
          text: 'This action cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: 'ajax.php',
              type: 'POST',
              data: {
                CALL: 'ml_operations',
                action: 'delete_ml_dataset',
                dataset_id: datasetId
              },
              dataType: 'json',
              success: function(result) {
                if (result && result.success) {
                  Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: result.message || 'Dataset deleted successfully',
                    timer: 2000
                  }).then(() => {
                    location.reload();
                  });
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Deletion failed',
                    timer: 2000
                  });
                }
              }
            });
          }
        });
      });

      // Delete Model
      $(document).on('click', '.deleteModelBtn', function() {
        const modelId = $(this).data('id');
        
        Swal.fire({
          title: 'Delete Model?',
          text: 'This action cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: 'ajax.php',
              type: 'POST',
              data: {
                CALL: 'ml_operations',
                action: 'delete_ml_model',
                model_id: modelId
              },
              dataType: 'json',
              success: function(result) {
                if (result && result.success) {
                  Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: result.message || 'Model deleted successfully',
                    timer: 2000
                  }).then(() => {
                    location.reload();
                  });
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Deletion failed',
                    timer: 2000
                  });
                }
              }
            });
          }
        });
      });

      // Download Template
      $('#downloadTemplateBtn').click(function() {
        window.location.href = 'ajax.php?CALL=download_template';
      });
    });
  </script>
</body>
</html>
