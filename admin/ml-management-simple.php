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
    ORDER BY d.uploaded_at DESC
");

// Get active model
$activeModel = $db->selectOne("SELECT * FROM ml_models_tbl WHERE is_active = 1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ML Management - UASG</title>
    
    <link rel="stylesheet" href="../resources/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <script src="../js/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .ml-settings-section, .ml-datasets-section, .ml-models-section {
            margin-bottom: 2rem;
        }
        .btn-group {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .stat-card h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 4px;
            margin: 0 0.25rem;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-info { background: #17a2b8; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-secondary { background: #6c757d; color: white; }
        .table-container {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include("sidebar.php"); ?>
        
        <div class="main-content">
            <?php include("header.php"); ?>
            
            <div class="dashboard-content">
                <div class="card">
                    <h2>🤖 Machine Learning Classification</h2>
                    
                    <!-- Active Model Status -->
                    <?php if ($activeModel): ?>
                    <div class="stat-card" style="background: #d4edda; border-left: 4px solid #28a745;">
                        <h4>✅ Active Model: <?= htmlspecialchars($activeModel['model_name']) ?></h4>
                        <p>
                            <strong>Accuracy:</strong> <?= number_format($activeModel['accuracy_score'] * 100, 2) ?>% |
                            <strong>F1 Score:</strong> <?= number_format($activeModel['f1_score'] * 100, 2) ?>% |
                            <strong>Categories:</strong> <?= $activeModel['categories_count'] ?> |
                            <strong>Used:</strong> <?= $activeModel['usage_count'] ?> times
                        </p>
                    </div>
                    <?php else: ?>
                    <div class="stat-card" style="background: #fff3cd; border-left: 4px solid #ffc107;">
                        <h4>⚠️ No Active Model</h4>
                        <p>Upload training data and train a model to enable custom ML classification.</p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- ML Settings -->
                    <div class="ml-settings-section">
                        <h3>⚙️ ML Settings</h3>
                        <form id="mlSettingsForm" class="compact-form">
                            <div class="form-columns">
                                <div class="form-column">
                                    <div class="form-group">
                                        <label>Classification Method</label>
                                        <select name="classification_method" class="form-control">
                                            <option value="nlpcloud" <?= ($settings['classification_method'] ?? '') == 'nlpcloud' ? 'selected' : '' ?>>NLP Cloud Only</option>
                                            <option value="google_nlp" <?= ($settings['classification_method'] ?? '') == 'google_nlp' ? 'selected' : '' ?>>Google NLP Only</option>
                                            <option value="custom_ml" <?= ($settings['classification_method'] ?? '') == 'custom_ml' ? 'selected' : '' ?>>Custom ML Only</option>
                                            <option value="hybrid" <?= ($settings['classification_method'] ?? '') == 'hybrid' ? 'selected' : '' ?>>Hybrid (ML + NLP)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-column">
                                    <div class="form-group">
                                        <label>ML Confidence Threshold (%)</label>
                                        <input type="number" name="ml_confidence_threshold" class="form-control"
                                               value="<?= $settings['ml_confidence_threshold'] ?? 60 ?>" min="0" max="100">
                                    </div>
                                </div>
                            </div>
                            <div class="form-columns">
                                <div class="form-column">
                                    <div class="form-group">
                                        <label>Default Algorithm</label>
                                        <select name="default_ml_algorithm" class="form-control">
                                            <option value="svm" <?= ($settings['default_ml_algorithm'] ?? '') == 'svm' ? 'selected' : '' ?>>SVM</option>
                                            <option value="naive_bayes" <?= ($settings['default_ml_algorithm'] ?? '') == 'naive_bayes' ? 'selected' : '' ?>>Naive Bayes</option>
                                            <option value="random_forest" <?= ($settings['default_ml_algorithm'] ?? '') == 'random_forest' ? 'selected' : '' ?>>Random Forest</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-column">
                                    <div class="form-group">
                                        <label>NLP Fallback</label>
                                        <select name="nlp_fallback_enabled" class="form-control">
                                            <option value="1" <?= ($settings['nlp_fallback_enabled'] ?? '1') == '1' ? 'selected' : '' ?>>Enabled</option>
                                            <option value="0" <?= ($settings['nlp_fallback_enabled'] ?? '1') == '0' ? 'selected' : '' ?>>Disabled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-save"></i> Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Training Datasets -->
                    <div class="ml-datasets-section">
                        <h3>📊 Training Datasets</h3>
                        <div class="btn-group" style="margin-bottom: 1rem;">
                            <button type="button" class="btn-primary" onclick="$('#uploadDatasetModal').show()">
                                <i class="fas fa-upload"></i> Upload Dataset
                            </button>
                            <button type="button" class="btn-secondary" id="downloadTemplateBtn">
                                <i class="fas fa-download"></i> Download Template
                            </button>
                        </div>
                        
                        <?php if (empty($datasets)): ?>
                            <p style="color: #666; padding: 2rem; text-align: center;">No datasets uploaded yet.</p>
                        <?php else: ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Samples</th>
                                        <th>Categories</th>
                                        <th>Uploaded By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($datasets as $d): ?>
                                    <tr>
                                        <td><?= $d['dataset_id'] ?></td>
                                        <td><strong><?= htmlspecialchars($d['dataset_name']) ?></strong></td>
                                        <td><span class="badge badge-info"><?= $d['total_samples'] ?></span></td>
                                        <td><span class="badge badge-info"><?= $d['categories_count'] ?></span></td>
                                        <td><?= htmlspecialchars($d['uploader_name'] ?? 'Unknown') ?></td>
                                        <td><?= date('M d, Y', strtotime($d['uploaded_at'])) ?></td>
                                        <td>
                                            <button class="btn-sm btn-primary train-model-btn" 
                                                    data-dataset-id="<?= $d['dataset_id'] ?>" 
                                                    data-dataset-name="<?= htmlspecialchars($d['dataset_name']) ?>">
                                                <i class="fas fa-cogs"></i> Train
                                            </button>
                                            <button class="btn-sm btn-danger delete-dataset-btn" 
                                                    data-dataset-id="<?= $d['dataset_id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Trained Models -->
                    <div class="ml-models-section">
                        <h3>🎯 Trained Models</h3>
                        <?php if (empty($models)): ?>
                            <p style="color: #666; padding: 2rem; text-align: center;">No models trained yet.</p>
                        <?php else: ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Algorithm</th>
                                        <th>Accuracy</th>
                                        <th>F1 Score</th>
                                        <th>Categories</th>
                                        <th>Trained</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($models as $m): ?>
                                    <tr style="<?= $m['is_active'] ? 'background: #d4edda;' : '' ?>">
                                        <td><?= $m['model_id'] ?></td>
                                        <td><strong><?= htmlspecialchars($m['model_name']) ?></strong></td>
                                        <td><span class="badge badge-secondary"><?= strtoupper($m['model_type']) ?></span></td>
                                        <td><span class="badge badge-success"><?= number_format($m['accuracy_score'] * 100, 1) ?>%</span></td>
                                        <td><?= number_format($m['f1_score'] * 100, 1) ?>%</td>
                                        <td><span class="badge badge-info"><?= $m['categories_count'] ?></span></td>
                                        <td><?= date('M d, Y', strtotime($m['trained_at'])) ?></td>
                                        <td>
                                            <?php if ($m['is_active']): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!$m['is_active']): ?>
                                            <button class="btn-sm btn-primary activate-model-btn" 
                                                    data-model-id="<?= $m['model_id'] ?>" 
                                                    data-model-name="<?= htmlspecialchars($m['model_name']) ?>">
                                                <i class="fas fa-check"></i> Activate
                                            </button>
                                            <?php endif; ?>
                                            <button class="btn-sm btn-danger delete-model-btn" 
                                                    data-model-id="<?= $m['model_id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Dataset Modal (simplified) -->
    <div id="uploadDatasetModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-upload"></i> Upload Training Dataset</h2>
                <span class="close" onclick="$('#uploadDatasetModal').hide()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="uploadDatasetForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Dataset Name</label>
                        <input type="text" name="dataset_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>CSV File (text,category format)</label>
                        <input type="file" name="dataset_file" class="form-control" accept=".csv" required>
                    </div>
                    <div id="uploadProgress" style="display:none;">
                        <div class="progress-bar"></div>
                        <p style="text-align:center; margin-top: 0.5rem;">Uploading...</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="$('#uploadDatasetModal').hide()">Cancel</button>
                <button type="button" class="btn-primary" onclick="$('#uploadDatasetForm').submit()">Upload</button>
            </div>
        </div>
    </div>

    <!-- Train Model Modal (simplified) -->
    <div id="trainModelModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-cogs"></i> Train Model</h2>
                <span class="close" onclick="$('#trainModelModal').hide()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="trainModelForm">
                    <input type="hidden" name="dataset_id" id="train_dataset_id">
                    <div class="form-group">
                        <label>Model Name</label>
                        <input type="text" name="model_name" id="train_model_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Algorithm</label>
                        <select name="algorithm" class="form-control">
                            <option value="svm">SVM (Recommended)</option>
                            <option value="naive_bayes">Naive Bayes (Fast)</option>
                            <option value="random_forest">Random Forest (Accurate)</option>
                        </select>
                    </div>
                    <div id="trainingProgress" style="display:none;">
                        <div class="progress-bar"></div>
                        <p style="text-align:center; margin-top: 0.5rem;">Training model...</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="$('#trainModelModal').hide()">Cancel</button>
                <button type="button" class="btn-primary" onclick="$('#trainModelForm').submit()">Train</button>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Download CSV template
        $('#downloadTemplateBtn').click(function() {
            let csv = "text,category\n";
            csv += '"RESOLUTION NO. 2025-001 regarding budget allocation",Resolution\n';
            csv += '"AMENDMENT to Article III Section 2",Amendment\n';
            csv += '"MEMORANDUM on new policy implementation",Memorandum\n';
            let blob = new Blob([csv], { type: 'text/csv' });
            let url = URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = 'ml_training_template.csv';
            a.click();
        });
        
        // Upload dataset
        $('#uploadDatasetForm').submit(function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            formData.append('CALL', 'ml_operations');
            formData.append('action', 'upload_ml_dataset');
            $('#uploadProgress').show();
            
            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', 'Dataset uploaded', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', response.message, 'error');
                        $('#uploadProgress').hide();
                    }
                }
            });
        });
        
        // Train model button
        $('.train-model-btn').click(function() {
            $('#train_dataset_id').val($(this).data('dataset-id'));
            $('#train_model_name').val($(this).data('dataset-name') + ' - Model');
            $('#trainModelModal').show();
        });
        
        // Train model
        $('#trainModelForm').submit(function(e) {
            e.preventDefault();
            let data = $(this).serialize() + '&CALL=ml_operations&action=train_ml_model';
            $('#trainingProgress').show();
            
            $.post('ajax.php', data, function(response) {
                if (response.success) {
                    Swal.fire('Success!', 'Model trained!', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message, 'error');
                    $('#trainingProgress').hide();
                }
            });
        });
        
        // Activate model
        $('.activate-model-btn').click(function() {
            let modelId = $(this).data('model-id');
            Swal.fire({
                title: 'Activate Model?',
                text: 'This will deactivate the current model',
                icon: 'question',
                showCancelButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('ajax.php', {
                        CALL: 'ml_operations',
                        action: 'activate_ml_model',
                        model_id: modelId
                    }, function(response) {
                        if (response.success) {
                            Swal.fire('Activated!', '', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    });
                }
            });
        });
        
        // Delete dataset
        $('.delete-dataset-btn').click(function() {
            let datasetId = $(this).data('dataset-id');
            Swal.fire({
                title: 'Delete Dataset?',
                text: 'This will also delete all models trained on it!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('ajax.php', {
                        CALL: 'ml_operations',
                        action: 'delete_ml_dataset',
                        dataset_id: datasetId
                    }, function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', '', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    });
                }
            });
        });
        
        // Delete model
        $('.delete-model-btn').click(function() {
            let modelId = $(this).data('model-id');
            Swal.fire({
                title: 'Delete Model?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('ajax.php', {
                        CALL: 'ml_operations',
                        action: 'delete_ml_model',
                        model_id: modelId
                    }, function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', '', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    });
                }
            });
        });
        
        // Save settings
        $('#mlSettingsForm').submit(function(e) {
            e.preventDefault();
            $.post('ajax.php', $(this).serialize() + '&CALL=ml_operations&action=save_ml_settings', function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Settings Saved!',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            });
        });
    });
    </script>

</body>
</html>
