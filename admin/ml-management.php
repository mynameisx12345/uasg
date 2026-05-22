<?php
session_start();
require_once("../resources/class.php");
$embedMode = isset($_GET["embed"]);

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

$categories = $db->select("SELECT category_id, category_name FROM category_tbl ORDER BY category_name");
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
  <script src='../js/all.js'></script>
  <style>
    .swal2-container { z-index: 99999 !important; }
    .alert { padding: 16px 20px; margin-bottom: 20px; border-radius: 10px; border-left: 4px solid; }
    .alert-success { background: #ecfdf5; border-left-color: #10b981; color: #065f46; }
    .alert-warning { background: #fffbeb; border-left-color: #f59e0b; color: #92400e; }
    .alert h4 { margin: 0 0 6px 0; font-size: 14px; }
    .alert p { margin: 0; font-size: 13px; opacity: 0.85; }
    .btn-group { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }

    .dropdown-container { position: relative; display: inline-block; }
    .dropdown-btn { background: transparent; color: #64748b; border: 1px solid #e2e8f0; padding: 0.35rem 0.55rem; font-size: 1.1rem; cursor: pointer; border-radius: 6px; line-height: 1; transition: all 0.2s; }
    .dropdown-btn:hover { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
    .dropdown-menu { display: none; position: absolute; right: 0; top: 100%; background: white; min-width: 160px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); border-radius: 8px; z-index: 1050; margin-top: 6px; border: 1px solid #e2e8f0; overflow: hidden; }
    .dropdown-menu.show { display: block; }
    .dropdown-item { display: flex; align-items: center; gap: 8px; width: 100%; padding: 0.55rem 1rem; text-align: left; border: none; background: none; cursor: pointer; font-size: 0.82rem; transition: background 0.15s; }
    .dropdown-item:hover { background: #f8fafc; }
    .dropdown-item.delete { color: #dc3545; }

    .tabs { border-bottom: 2px solid #e2e8f0; margin-bottom: 1.5rem; }
    .tab-link { padding: 10px 18px; background: none; border: none; color: #64748b; cursor: pointer; font-weight: 500; font-size: 13px; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s; border-radius: 6px 6px 0 0; }
    .tab-link:hover { background: #f8fafc; color: #334155; }
    .tab-link.active { color: #1e40af; border-bottom-color: #3b82f6; background: #eff6ff; }

    .table-container { margin-top: 0.5rem; }
    #datasetsTable td, #modelsTable td { vertical-align: middle; font-size: 12px; padding: 10px 12px; }
    #datasetsTable th, #modelsTable th { font-size: 12px; padding: 10px 12px; }
    #datasetsTable thead th, #modelsTable thead th { background: #f8f9fa !important; color: #475569 !important; border-bottom: 2px solid #e2e8f0 !important; font-weight: 600; }
    #datasetsTable tbody tr, #modelsTable tbody tr { transition: background 0.15s, box-shadow 0.15s; }
    #datasetsTable tbody tr:hover, #modelsTable tbody tr:hover { background: #f8fafc !important; box-shadow: inset 3px 0 0 #3b82f6; }

    .compact-form h3 { font-size: 15px; font-weight: 600; color: #1e293b; margin-bottom: 16px; }
    .form-section h4 { font-size: 13px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9; }

    .btn-primary { transition: all 0.2s; }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .btn-secondary { transition: all 0.2s; }
    .btn-secondary:hover { transform: translateY(-1px); }

    .modal { backdrop-filter: blur(3px); }
    .modal-content { border-radius: 12px; box-shadow: 0 24px 48px rgba(0,0,0,0.15); }
  </style>
  <script src='../js/jquery.js'></script>
  <script src='../js/datatable.js'></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body<?php if($embedMode) echo ' style="padding:20px;margin:0;background:transparent;"'; ?>>
  <?php if(!$embedMode): ?><!-- HEADER -->
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
    <?php endif; ?>
    <?php if(!$embedMode): ?><section class="content"><?php endif; ?>
      <div class="card">
        <?php if(!$embedMode): ?><h2>🤖 ML Classification Management</h2><?php endif; ?>

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
                        <option value="custom_ml" <?= ($settings["classification_method"] ?? "") == "custom_ml" ? "selected" : "" ?>>ML Classification (Primary)</option>
                        <option value="nlpcloud" <?= ($settings["classification_method"] ?? "") == "nlpcloud" ? "selected" : "" ?>>NLP Service (Fallback)</option>
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
                  <div class="form-row" style="display:flex;gap:1rem;">
                    <div class="form-group" style="flex:1;">
                      <label for="incrementalLearning">Incremental Learning</label>
                      <select id="incrementalLearning" name="incremental_learning_enabled">
                        <option value="1" <?= ($settings["incremental_learning_enabled"] ?? "0") == "1" ? "selected" : "" ?>>Enabled</option>
                        <option value="0" <?= ($settings["incremental_learning_enabled"] ?? "0") == "0" ? "selected" : "" ?>>Disabled</option>
                      </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                      <label for="incrementalBatchSize">Batch Size</label>
                      <input type="number" id="incrementalBatchSize" name="incremental_batch_size"
                             value="<?= $settings["incremental_batch_size"] ?? 10 ?>" min="1" max="100">
                    </div>
                  </div>
                  <div class="form-actions">
                    <button type="button" id="saveSettings" class="btn-primary">💾 Save Settings</button>
                    <button type="button" id="viewQueueBtn" class="btn-secondary" style="margin-left:0.5rem;">📋 View Queue</button>
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
              <button type="button" class="btn-secondary" id="generateFromFilesBtn">
                📂 Generate from Files
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
                    <div class="dropdown-container">
                      <button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
                      <div class="dropdown-menu">
                        <button class="dropdown-item viewDatasetBtn" data-id="<?= $dataset['dataset_id'] ?>" data-path="<?= $dataset['file_path'] ?>">👁️ View</button>
                        <button class="dropdown-item trainBtn" data-id="<?= $dataset['dataset_id'] ?>">🎓 Train Model</button>
                        <button class="dropdown-item delete deleteDatasetBtn" data-id="<?= $dataset['dataset_id'] ?>">🗑️ Delete</button>
                        <a class="dropdown-item" href="ajax.php?CALL=ml_operations&action=download_dataset&dataset_id=<?= $dataset['dataset_id'] ?>" style="text-decoration:none;color:#2563eb;">📥 Download</a>
                      </div>
                    </div>
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
                    <div class="dropdown-container">
                      <button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
                      <div class="dropdown-menu">
                        <?php if (!$model['is_active']): ?>
                          <button class="dropdown-item activateBtn" data-id="<?= $model['model_id'] ?>">⚡ Activate</button>
                        <?php endif; ?>
                        <button class="dropdown-item delete deleteModelBtn" data-id="<?= $model['model_id'] ?>">🗑️ Delete</button>
                      </div>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    <?php if(!$embedMode): ?></section><?php endif; ?>
  <?php if(!$embedMode): ?></main><?php endif; ?>

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
            nlp_fallback_enabled: $('#nlpFallback').val(),
            incremental_learning_enabled: $('#incrementalLearning').val(),
            incremental_batch_size: $('#incrementalBatchSize').val()
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

    // Dropdown toggle (shared)
    function toggleDropdown(event) {
      event.stopPropagation();
      const menu = event.currentTarget.nextElementSibling;
      document.querySelectorAll('.dropdown-menu').forEach(m => { if (m !== menu) m.classList.remove('show'); });
      menu.classList.toggle('show');
    }
    document.addEventListener('click', function() {
      document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show'));
    });
  </script>
<!-- Incremental Queue Modal -->
<div id="queueModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:8px;width:90%;max-width:800px;max-height:80vh;display:flex;flex-direction:column;padding:1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
      <h3 style="margin:0;">Incremental Learning Queue</h3>
      <button id="closeQueueModal" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
    </div>
    <div style="margin-bottom:1rem;display:flex;gap:0.5rem;">
      <select id="queueFilterStatus" style="padding:0.4rem;border-radius:4px;border:1px solid #ccc;">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="processed">Processed</option>
        <option value="failed">Failed</option>
      </select>
      <select id="queueFilterCategory" style="padding:0.4rem;border-radius:4px;border:1px solid #ccc;">
        <option value="">All Categories</option>
      </select>
      <select id="queueRowsPerPage" style="padding:0.4rem;border-radius:4px;border:1px solid #ccc;">
        <option value="10">10 per page</option>
        <option value="25">25 per page</option>
        <option value="50">50 per page</option>
        <option value="100">100 per page</option>
      </select>
    </div>
    <div style="overflow-y:auto;flex:1;">
      <table class="data-table" style="width:100%;">
        <thead><tr><th>#</th><th>File ID</th><th>Category</th><th>Confidence</th><th>Status</th><th>Date</th></tr></thead>
        <tbody id="queueTableBody"></tbody>
      </table>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.5rem;padding-top:0.5rem;border-top:1px solid #eee;">
      <span id="queueInfo" style="font-size:13px;color:#666;"></span>
      <div id="queuePages" style="display:flex;gap:4px;"></div>
    </div>
</div>
  </div>
<script>
$(function(){
  var queueData=[],queuePage=1;
  $("#viewQueueBtn").click(function(){queuePage=1;loadQueue();$("#queueModal").css("display","flex");});
  $("#closeQueueModal").click(function(){$("#queueModal").hide();});
  $("#queueModal").click(function(e){if(e.target===this)$(this).hide();});
  $("#queueFilterStatus,#queueFilterCategory").change(function(){queuePage=1;loadQueue();});
  $("#queueRowsPerPage").change(function(){queuePage=1;renderQueue();});
  function loadQueue(){
    $.post("ajax.php",{CALL:"ml_operations",action:"get_incremental_queue",filter_status:$("#queueFilterStatus").val(),filter_category:$("#queueFilterCategory").val()},function(r){
      if(r.success){
        queueData=r.data;
        var cats=[];
        r.data.forEach(function(row){if(cats.indexOf(row.confirmed_category)===-1)cats.push(row.confirmed_category);});
        var sel=$("#queueFilterCategory").val();
        $("#queueFilterCategory").html("<option value=\"\">" + "All Categories</option>");
        cats.forEach(function(c){$("#queueFilterCategory").append("<option value=\""+c+"\""+(c===sel?" selected":"")+">"+c+"</option>");});
        renderQueue();
      }
    },"json");
  }
  function renderQueue(){
    var perPage=parseInt($("#queueRowsPerPage").val())||10;
    var total=queueData.length,pages=Math.ceil(total/perPage);
    if(queuePage>pages)queuePage=pages||1;
    var start=(queuePage-1)*perPage,slice=queueData.slice(start,start+perPage),html="";
    slice.forEach(function(row,i){
      var badge=row.status==="processed"?"background:#d4edda;color:#155724":row.status==="pending"?"background:#fff3cd;color:#856404":"background:#f8d7da;color:#721c24";
      html+="<tr><td>"+(start+i+1)+"</td><td>"+row.file_upload_id+"</td><td>"+row.confirmed_category+"</td><td>"+(row.prediction_confidence||"-")+"%</td><td><span style=\"padding:2px 8px;border-radius:4px;font-size:12px;"+badge+"\">"+row.status+"</span></td><td>"+row.created_at+"</td></tr>";
    });
    $("#queueTableBody").html(html||"<tr><td colspan=6 style=\"text-align:center\">No records</td></tr>");
    $("#queueInfo").text(total?"Showing "+(start+1)+"-"+Math.min(start+perPage,total)+" of "+total:"No records");
    var ph="";
    var ph="";var btnStyle="padding:4px 8px;border:1px solid #ccc;border-radius:4px;cursor:pointer;";
    ph+="<button onclick=\"setQueuePage("+(queuePage>1?queuePage-1:1)+")\" style=\""+btnStyle+(queuePage===1?"opacity:0.5;":"")+"\">← Prev</button>";
    var startP=Math.max(1,queuePage-2),endP=Math.min(pages,startP+4);if(endP-startP<4)startP=Math.max(1,endP-4);
    for(var p=startP;p<=endP;p++){ph+="<button onclick=\"setQueuePage("+p+")\" style=\""+btnStyle+(p===queuePage?"background:#007bff;color:#fff;":"background:#fff;")+"\">"+p+"</button>";}
    ph+="<button onclick=\"setQueuePage("+(queuePage<pages?queuePage+1:pages)+")\" style=\""+btnStyle+(queuePage===pages?"opacity:0.5;":"")+"\">Next →</button>";
    $("#queuePages").html(ph);
  }
  window.setQueuePage=function(p){queuePage=p;renderQueue();};
});
</script>
<!-- Generate from Files Modal -->
<div id="genFilesModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9998;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:8px;width:90%;max-width:900px;max-height:85vh;display:flex;flex-direction:column;padding:1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
      <h3 style="margin:0;">Generate Dataset from Files</h3>
      <button id="closeGenFilesModal" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
    </div>
    <div style="margin-bottom:1rem;display:flex;gap:0.5rem;align-items:center;">
      <label class="btn-primary" style="cursor:pointer;padding:0.5rem 1rem;border-radius:4px;font-size:14px;">
        📎 Add Files <input type="file" id="genFilesInput" multiple accept=".docx,.pdf,.txt,.doc" style="display:none;">
      </label>
      <span id="genFilesCount" style="font-size:13px;color:#666;">No files added</span>
    </div>
    <div style="overflow-y:auto;flex:1;margin-bottom:1rem;">
      <table class="data-table" style="width:100%;">
        <thead><tr><th>File Name</th><th>Category</th><th>Status</th><th>Action</th></tr></thead>
        <tbody id="genFilesTableBody"><tr><td colspan="4" style="text-align:center;color:#999;">Add files to begin</td></tr></tbody>
      </table>
    </div>
    <div style="display:flex;gap:0.5rem;justify-content:flex-end;border-top:1px solid #eee;padding-top:1rem;">
      <select id="genFilesDatasetSelect" style="padding:0.4rem;border-radius:4px;border:1px solid #ccc;display:none;"></select>
      <button type="button" id="genFilesAppendBtn" class="btn-secondary" style="display:none;">➕ Add to Selected Dataset</button>
      <button type="button" id="genFilesNewBtn" class="btn-primary">📄 Create New Dataset</button>
    </div>
  </div>
</div>
<script>
$(function(){
  var genFiles=[];
  var categories=<?= json_encode($categories) ?>;
  var datasets=<?= json_encode($db->select("SELECT dataset_id, dataset_name FROM ml_training_datasets_tbl ORDER BY dataset_id DESC")) ?>;
  if(datasets.length){$("#genFilesAppendBtn,#genFilesDatasetSelect").show();var opts="";datasets.forEach(function(d){opts+="<option value=\""+d.dataset_id+"\">"+d.dataset_name+"</option>";});$("#genFilesDatasetSelect").html(opts);}
  $("#generateFromFilesBtn").click(function(){genFiles=[];renderGenTable();$("#genFilesModal").css("display","flex");});
  $("#closeGenFilesModal").click(function(){$("#genFilesModal").hide();});
  $("#genFilesModal").click(function(e){if(e.target===this)$(this).hide();});
  $("#genFilesInput").change(function(){
    var files=this.files;
    for(var i=0;i<files.length;i++){genFiles.push({file:files[i],category:"",status:"pending"});}
    renderGenTable();
    this.value="";
  });
  function renderGenTable(){
    $("#genFilesCount").text(genFiles.length?genFiles.length+" file(s) added":"No files added");
    if(!genFiles.length){$("#genFilesTableBody").html("<tr><td colspan=4 style=\"text-align:center;color:#999\">Add files to begin</td></tr>");return;}
    var html="";
    genFiles.forEach(function(f,i){
      var opts="<option value=\"\">-- Select --</option>";
      categories.forEach(function(c){opts+="<option value=\""+c.category_name+"\""+(c.category_name===f.category?" selected":"")+">"+c.category_name+"</option>";});
      var statusBadge=f.status==="pending"?"⏳":f.status==="done"?"✅":"❌";
      html+="<tr><td>"+f.file.name+"</td><td><select onchange=\"setGenCat("+i+",this.value)\" style=\"padding:4px;border-radius:4px;border:1px solid #ccc;\">"+opts+"</select></td><td>"+statusBadge+" "+f.status+"</td><td><button onclick=\"removeGenFile("+i+")\" style=\"background:#dc3545;color:#fff;border:none;padding:4px 8px;border-radius:4px;cursor:pointer;\">✕</button></td></tr>";
    });
    $("#genFilesTableBody").html(html);
    var hasEmpty=!genFiles.length||genFiles.some(function(f){return !f.category;});
    $("#genFilesNewBtn,#genFilesAppendBtn").prop("disabled",hasEmpty).css("opacity",hasEmpty?"0.5":"1");
  }
  window.setGenCat=function(i,v){genFiles[i].category=v;renderGenTable();};
  window.removeGenFile=function(i){genFiles.splice(i,1);renderGenTable();};
  $("#genFilesNewBtn").click(function(){submitGenFiles("new");});
  $("#genFilesAppendBtn").click(function(){submitGenFiles("append");});
  function submitGenFiles(mode){
    if(!genFiles.length){Swal.fire({icon:"warning",title:"No Files",text:"Add files first.",customClass:{popup:"swal-above-modal"}});return;}
    var formData=new FormData();
    formData.append("CALL","ml_operations");
    formData.append("action","generate_dataset_from_files");
    formData.append("mode",mode);
    if(mode==="append")formData.append("dataset_id",$("#genFilesDatasetSelect").val());
    genFiles.forEach(function(f,i){
      formData.append("files[]",f.file);
      formData.append("categories[]",f.category);
    });
    Swal.fire({title:"Processing...",text:"Extracting text and generating dataset",allowOutsideClick:false,didOpen:function(){Swal.showLoading();}});
    $.ajax({
      url:"ajax.php",type:"POST",data:formData,processData:false,contentType:false,dataType:"json",
      success:function(r){
        if(r.success){
          Swal.fire({icon:"success",title:"Dataset Generated",text:r.message||"Dataset created with "+r.total_samples+" samples.",timer:3000});
          genFiles=[];renderGenTable();setTimeout(function(){location.reload();},2000);
        }else{Swal.fire({icon:"error",title:"Error",text:r.error||"Failed to generate dataset."});}
      },
      error:function(){Swal.fire({icon:"error",title:"Error",text:"Request failed."});}
    });
  }
});
</script>
<!-- View Dataset Modal -->
<div id="viewDatasetModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9998;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:8px;width:90%;max-width:900px;max-height:85vh;display:flex;flex-direction:column;padding:1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
      <h3 style="margin:0;" id="viewDatasetTitle">Dataset Contents</h3>
      <button id="closeViewDatasetModal" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
    </div>
    <div style="margin-bottom:0.5rem;display:flex;gap:0.5rem;align-items:center;">
      <select id="dsFilterCategory" style="padding:0.4rem;border-radius:4px;border:1px solid #ccc;"><option value="">All Categories</option></select>
      <select id="dsRowsPerPage" style="padding:0.4rem;border-radius:4px;border:1px solid #ccc;"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select>
      <span id="dsInfo" style="font-size:13px;color:#666;margin-left:auto;"></span>
    </div>
    <div style="overflow-y:auto;flex:1;">
      <table class="data-table" style="width:100%;">
        <thead><tr><th style="width:40px;">#</th><th>Text (preview)</th><th style="width:140px;">Category</th></tr></thead>
        <tbody id="dsTableBody"></tbody>
      </table>
    </div>
    <div style="display:flex;justify-content:center;gap:4px;margin-top:0.5rem;padding-top:0.5rem;border-top:1px solid #eee;" id="dsPages"></div>
  </div>
</div>
<script>
$(function(){
  var dsData=[],dsPage=1;
  $(document).on("click",".viewDatasetBtn",function(){
    var path=$(this).data("path");
    dsPage=1;
    Swal.fire({title:"Loading...",allowOutsideClick:false,didOpen:function(){Swal.showLoading();}});
    $.post("ajax.php",{CALL:"ml_operations",action:"view_dataset",file_path:path},function(r){
      Swal.close();
      if(r.success){
        dsData=r.data;
        var cats=[];
        r.data.forEach(function(row){if(cats.indexOf(row.category)===-1)cats.push(row.category);});
        $("#dsFilterCategory").html("<option value=\"\">" + "All Categories</option>");
        cats.forEach(function(c){$("#dsFilterCategory").append("<option value=\""+c+"\">"+c+"</option>");});
        $("#viewDatasetTitle").text("Dataset Contents ("+r.data.length+" samples)");
        renderDs();
        $("#viewDatasetModal").css("display","flex");
      }else{Swal.fire({icon:"error",title:"Error",text:r.error||"Failed to load dataset"});}
    },"json");
  });
  $("#closeViewDatasetModal").click(function(){$("#viewDatasetModal").hide();});
  $("#viewDatasetModal").click(function(e){if(e.target===this)$(this).hide();});
  $("#dsFilterCategory").change(function(){dsPage=1;renderDs();});
  $("#dsRowsPerPage").change(function(){dsPage=1;renderDs();});
  function renderDs(){
    var filter=$("#dsFilterCategory").val();
    var filtered=filter?dsData.filter(function(r){return r.category===filter;}):dsData;
    var perPage=parseInt($("#dsRowsPerPage").val())||10;
    var total=filtered.length,pages=Math.ceil(total/perPage);
    if(dsPage>pages)dsPage=pages||1;
    var start=(dsPage-1)*perPage,slice=filtered.slice(start,start+perPage),html="";
    slice.forEach(function(row,i){
      var preview=row.text.length>120?row.text.substring(0,120)+"...":row.text;
      html+="<tr><td>"+(start+i+1)+"</td><td style=\"font-size:12px;\">"+$("<div>").text(preview).html()+"</td><td><span style=\"padding:2px 8px;border-radius:4px;font-size:12px;background:#e2e8f0;\">"+row.category+"</span></td></tr>";
    });
    $("#dsTableBody").html(html||"<tr><td colspan=3 style=\"text-align:center\">No data</td></tr>");
    $("#dsInfo").text(total?"Showing "+(start+1)+"-"+Math.min(start+perPage,total)+" of "+total:"No records");
    var ph="",btnS="padding:4px 8px;border:1px solid #ccc;border-radius:4px;cursor:pointer;";
    ph+="<button onclick=\"setDsPage("+(dsPage>1?dsPage-1:1)+")\" style=\""+btnS+(dsPage===1?"opacity:0.5;":"")+"\">← Prev</button>";
    var sp=Math.max(1,dsPage-2),ep=Math.min(pages,sp+4);if(ep-sp<4)sp=Math.max(1,ep-4);
    for(var p=sp;p<=ep;p++){ph+="<button onclick=\"setDsPage("+p+")\" style=\""+btnS+(p===dsPage?"background:#007bff;color:#fff;":"background:#fff;")+"\">"+p+"</button>";}
    ph+="<button onclick=\"setDsPage("+(dsPage<pages?dsPage+1:pages)+")\" style=\""+btnS+(dsPage>=pages?"opacity:0.5;":"")+"\">Next →</button>";
    $("#dsPages").html(ph);
  }
  window.setDsPage=function(p){dsPage=p;renderDs();};
});
</script>
</div>
</body>
</html>
