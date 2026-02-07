<?php
session_start();
require_once("../resources/class.php");

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['position_id'] != 3) {
    header("Location: ../index.php");
    exit;
}

$db = Database::getInstance();
$em = new EntityManager($db);

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
</head>
<body>
    <div class="dashboard-container">
        <?php include("sidebar.php"); ?>
        
        <div class="main-content">
            <?php include("header.php"); ?>
            
            <div class="dashboard-content">
                <div class="card">
                    <h2>🤖 Machine Learning Classification</h2>
                    
                    <!-- ML Settings Form -->
                    <h3>⚙️ ML Settings</h3>
                    <form id="mlSettingsForm" class="compact-form">
                        <div class="form-columns">
                            <div class="form-column">
                                <div class="form-group">
                                    <label>Classification Method</label>
                                    <select class="form-control" name="classification_method" id="classification_method">
                                        <option value="nlpcloud" <?= ($settings['classification_method'] ?? '') == 'nlpcloud' ? 'selected' : '' ?>>NLP Cloud Only</option>
                                        <option value="google_nlp" <?= ($settings['classification_method'] ?? '') == 'google_nlp' ? 'selected' : '' ?>>Google NLP Only</option>
                                        <option value="custom_ml" <?= ($settings['classification_method'] ?? '') == 'custom_ml' ? 'selected' : '' ?>>Custom ML Only</option>
                                        <option value="hybrid" <?= ($settings['classification_method'] ?? '') == 'hybrid' ? 'selected' : '' ?>>Hybrid (ML + NLP Cloud)</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <strong>Hybrid:</strong> Try ML first, fallback to NLP Cloud if confidence is low
                                    </small>
                                </div>
                            </div>
                            <div class="form-column">
                                <div class="form-group">
                                    <label>ML Confidence Threshold (%)</label>
                                    <input type="number" class="form-control" name="ml_confidence_threshold" 
                                           value="<?= $settings['ml_confidence_threshold'] ?? 60 ?>" min="0" max="100">
                                    <small class="form-text text-muted">Minimum confidence to accept ML prediction</small>
                                </div>
                            </div>
                        </div>
                        <div class="form-columns">
                            <div class="form-column">
                                <div class="form-group">
                                    <label>Default ML Algorithm</label>
                                    <select class="form-control" name="default_ml_algorithm">
                                        <option value="svm" <?= ($settings['default_ml_algorithm'] ?? '') == 'svm' ? 'selected' : '' ?>>SVM (Support Vector Machine)</option>
                                        <option value="naive_bayes" <?= ($settings['default_ml_algorithm'] ?? '') == 'naive_bayes' ? 'selected' : '' ?>>Naive Bayes</option>
                                        <option value="random_forest" <?= ($settings['default_ml_algorithm'] ?? '') == 'random_forest' ? 'selected' : '' ?>>Random Forest</option>
                                    </select>
                                    <small class="form-text text-muted">Algorithm for training new models</small>
                                </div>
                            </div>
                            <div class="form-column">
                                <div class="form-group">
                                    <label>NLP Cloud Fallback</label>
                                    <select class="form-control" name="nlp_fallback_enabled">
                                        <option value="1" <?= ($settings['nlp_fallback_enabled'] ?? '1') == '1' ? 'selected' : '' ?>>Enabled</option>
                                        <option value="0" <?= ($settings['nlp_fallback_enabled'] ?? '1') == '0' ? 'selected' : '' ?>>Disabled</option>
                                    </select>
                                    <small class="form-text text-muted">Use NLP Cloud when ML confidence is low</small>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </div>
                    </form>
                                            <th>Categories</th>
                                            <th>Category List</th>
                                            <th>Uploaded By</th>
                                            <th>Uploaded At</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($datasets as $dataset): 
                                            $categories = json_decode($dataset['categories'], true) ?? [];
                                        ?>
                                        <tr>
                                            <td><?= $dataset['dataset_id'] ?></td>
                                            <td><strong><?= htmlspecialchars($dataset['dataset_name']) ?></strong></td>
                                            <td><span class="badge badge-info"><?= $dataset['total_samples'] ?></span></td>
                                            <td><span class="badge badge-primary"><?= $dataset['categories_count'] ?></span></td>
                                            <td>
                                                <small><?= htmlspecialchars(implode(', ', array_slice($categories, 0, 3))) ?>
                                                <?= count($categories) > 3 ? '...' : '' ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($dataset['uploader_name'] ?? 'Unknown') ?></td>
                                            <td><?= date('M d, Y H:i', strtotime($dataset['uploaded_at'])) ?></td>
                                            <td>
                                                <?php if ($dataset['is_active']): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-success train-model-btn" 
                                                        data-dataset-id="<?= $dataset['dataset_id'] ?>"
                                                        data-dataset-name="<?= htmlspecialchars($dataset['dataset_name']) ?>">
                                                    <i class="fas fa-cogs"></i> Train Model
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-dataset-btn" 
                                                        data-dataset-id="<?= $dataset['dataset_id'] ?>">
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

            <!-- Trained Models -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">🎯 Trained Models</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($models)): ?>
                                <p class="text-muted text-center py-4">No trained models yet. Upload a dataset and train your first model!</p>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Model Name</th>
                                            <th>Algorithm</th>
                                            <th>Dataset</th>
                                            <th>Accuracy</th>
                                            <th>Precision</th>
                                            <th>Recall</th>
                                            <th>F1 Score</th>
                                            <th>Categories</th>
                                            <th>Trained At</th>
                                            <th>Usage</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($models as $model): ?>
                                        <tr class="<?= $model['is_active'] ? 'table-success' : '' ?>">
                                            <td><?= $model['model_id'] ?></td>
                                            <td><strong><?= htmlspecialchars($model['model_name']) ?></strong></td>
                                            <td><span class="badge badge-secondary"><?= strtoupper($model['model_type']) ?></span></td>
                                            <td><?= htmlspecialchars($model['dataset_name'] ?? 'N/A') ?></td>
                                            <td><span class="badge badge-<?= $model['accuracy_score'] > 0.8 ? 'success' : 'warning' ?>"><?= number_format($model['accuracy_score'] * 100, 2) ?>%</span></td>
                                            <td><?= number_format($model['precision_score'] * 100, 2) ?>%</td>
                                            <td><?= number_format($model['recall_score'] * 100, 2) ?>%</td>
                                            <td><?= number_format($model['f1_score'] * 100, 2) ?>%</td>
                                            <td><span class="badge badge-info"><?= $model['categories_count'] ?></span></td>
                                            <td><?= date('M d, Y H:i', strtotime($model['trained_at'])) ?></td>
                                            <td><?= $model['usage_count'] ?> times</td>
                                            <td>
                                                <?php if ($model['is_active']): ?>
                                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!$model['is_active']): ?>
                                                    <button class="btn btn-sm btn-primary activate-model-btn" 
                                                            data-model-id="<?= $model['model_id'] ?>"
                                                            data-model-name="<?= htmlspecialchars($model['model_name']) ?>">
                                                        <i class="fas fa-power-off"></i> Activate
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-danger delete-model-btn" 
                                                        data-model-id="<?= $model['model_id'] ?>">
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
    </section>
</div>

<!-- Upload Dataset Modal -->
<div class="modal fade" id="uploadDatasetModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title"><i class="fas fa-upload"></i> Upload Training Dataset</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="uploadDatasetForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6><i class="icon fas fa-info-circle"></i> CSV Format Requirements:</h6>
                        <ul class="mb-0">
                            <li>File must have two columns: <code>text</code> and <code>category</code></li>
                            <li>First row must be headers: <code>text,category</code></li>
                            <li>Text column: Document content or description</li>
                            <li>Category column: The category label for that document</li>
                            <li>Minimum 10 samples recommended for each category</li>
                        </ul>
                    </div>
                    
                    <div class="form-group">
                        <label>Dataset Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="dataset_name" required 
                               placeholder="e.g., UASG Documents 2025">
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3" 
                                  placeholder="Optional: Describe this dataset..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>CSV File <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="csvFile" name="csv_file" 
                                   accept=".csv" required>
                            <label class="custom-file-label" for="csvFile">Choose CSV file...</label>
                        </div>
                        <small class="form-text text-muted">Maximum file size: 10MB</small>
                    </div>
                    
                    <div id="uploadProgress" style="display:none;">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Upload Dataset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Train Model Modal -->
<div class="modal fade" id="trainModelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title"><i class="fas fa-cogs"></i> Train ML Model</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="trainModelForm">
                <input type="hidden" name="dataset_id" id="train_dataset_id">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="icon fas fa-clock"></i> Training may take a few minutes depending on dataset size.
                    </div>
                    
                    <div class="form-group">
                        <label>Model Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="model_name" id="train_model_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Algorithm</label>
                        <select class="form-control" name="algorithm">
                            <option value="svm">SVM (Support Vector Machine) - Recommended</option>
                            <option value="naive_bayes">Naive Bayes - Fast Training</option>
                            <option value="random_forest">Random Forest - High Accuracy</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Test Split (%)</label>
                        <input type="number" class="form-control" name="test_size" value="20" min="10" max="40">
                        <small class="form-text text-muted">Percentage of data used for testing (default: 20%)</small>
                    </div>
                    
                    <div id="trainingProgress" style="display:none;">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                 role="progressbar" style="width: 100%"></div>
                        </div>
                        <p class="text-center mt-2"><i class="fas fa-spinner fa-spin"></i> Training model...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-cogs"></i> Train Model
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Update file input label
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
    
    // Download CSV template
    $('#downloadTemplateBtn').click(function() {
        let csvContent = "text,category\n";
        csvContent += '"RESOLUTION NO. 2025-001 WHEREAS the University Academic Senate...",Resolution\n';
        csvContent += '"AMENDMENT to Article III Section 2 of the Constitution...",Amendment\n';
        csvContent += '"MEMORANDUM regarding the implementation of new policies...",Memorandum\n';
        csvContent += '"LETTER addressed to the Board of Regents concerning...",Letter\n';
        csvContent += '"REPORT on the annual budget allocation for fiscal year...",Report\n';
        
        let blob = new Blob([csvContent], { type: 'text/csv' });
        let url = window.URL.createObjectURL(blob);
        let a = document.createElement('a');
        a.href = url;
        a.download = 'ml_training_template.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    });
    
    // Upload dataset form
    $('#uploadDatasetForm').submit(function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        formData.append('CALL', 'ml_operations');
        formData.append('action', 'upload_ml_dataset');
        
        $('#uploadProgress').show();
        $('.progress-bar').css('width', '50%');
        
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('.progress-bar').css('width', '100%');
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Dataset Uploaded!',
                        html: `<strong>${response.dataset_name}</strong><br>
                               Samples: ${response.total_samples}<br>
                               Categories: ${response.categories_count}`,
                        timer: 3000
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Upload failed', 'error');
                    $('#uploadProgress').hide();
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to upload dataset', 'error');
                $('#uploadProgress').hide();
            }
        });
    });
    
    // Train model button
    $('.train-model-btn').click(function() {
        let datasetId = $(this).data('dataset-id');
        let datasetName = $(this).data('dataset-name');
        
        $('#train_dataset_id').val(datasetId);
        $('#train_model_name').val(datasetName + ' - Model');
        $('#trainModelModal').modal('show');
    });
    
    // Train model form
    $('#trainModelForm').submit(function(e) {
        e.preventDefault();
        
        let formData = $(this).serialize() + '&CALL=ml_operations&action=train_ml_model';
        
        $('#trainingProgress').show();
        $(this).find('button[type="submit"]').prop('disabled', true);
        
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Model Trained Successfully!',
                        html: `<strong>Accuracy:</strong> ${(response.accuracy * 100).toFixed(2)}%<br>
                               <strong>F1 Score:</strong> ${(response.f1_score * 100).toFixed(2)}%<br>
                               <strong>Categories:</strong> ${response.categories_count}`,
                        timer: 5000
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Training failed', 'error');
                    $('#trainingProgress').hide();
                    $('#trainModelForm').find('button[type="submit"]').prop('disabled', false);
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to train model', 'error');
                $('#trainingProgress').hide();
                $('#trainModelForm').find('button[type="submit"]').prop('disabled', false);
            }
        });
    });
    
    // Activate model
    $('.activate-model-btn').click(function() {
        let modelId = $(this).data('model-id');
        let modelName = $(this).data('model-name');
        
        Swal.fire({
            title: 'Activate Model?',
            html: `Activate <strong>${modelName}</strong>?<br>This will deactivate the current model.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Activate'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('ajax.php', {
                    CALL: 'ml_operations',
                    action: 'activate_ml_model',
                    model_id: modelId
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Activated!', 'Model is now active', 'success').then(() => {
                            location.reload();
                        });
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
            text: 'This will also delete all models trained on this dataset!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('ajax.php', {
                    CALL: 'ml_operations',
                    action: 'delete_ml_dataset',
                    dataset_id: datasetId
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', 'Dataset removed', 'success').then(() => {
                            location.reload();
                        });
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
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('ajax.php', {
                    CALL: 'ml_operations',
                    action: 'delete_ml_model',
                    model_id: modelId
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', 'Model removed', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                });
            }
        });
    });
    
    // Save ML settings
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

<?php include("../resources/components/footer.php"); ?>
