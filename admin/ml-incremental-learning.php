<?php
session_start();
require_once '../resources/session.php';
require_once '../resources/objects/ml_service_incremental.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Admin') {
    header('Location: ../index.php');
    exit;
}

$mlService = new IncrementalMLService();
$stats = $mlService->getIncrementalStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incremental ML Learning - UASG</title>
    <link rel="stylesheet" href="../resources/style.css">
    <link rel="stylesheet" href="../resources/datatable.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    
    <section class="content">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-brain"></i> Incremental ML Learning</h2>
                <p>Monitor continuous learning from uploaded files</p>
            </div>
            
            <?php if ($stats['success']): ?>
            <div class="card-body">
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card gradient-blue">
                        <div class="stat-icon"><i class="fas fa-database"></i></div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_samples']); ?></h3>
                            <p>Total Training Samples</p>
                            <small><?php echo number_format($stats['initial_samples']); ?> initial + <?php echo number_format($stats['incremental_samples']); ?> learned</small>
                        </div>
                    </div>
                    
                    <div class="stat-card gradient-green">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['processed_samples']); ?></h3>
                            <p>Processed Samples</p>
                            <small>Successfully learned</small>
                        </div>
                    </div>
                    
                    <div class="stat-card gradient-orange">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['pending_samples']); ?></h3>
                            <p>Pending Samples</p>
                            <small><?php echo (10 - $stats['pending_samples']); ?> more to trigger learning</small>
                        </div>
                    </div>
                    
                    <div class="stat-card gradient-purple">
                        <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $stats['last_update'] ? date('M d, Y', strtotime($stats['last_update'])) : 'Never'; ?></h3>
                            <p>Last Update</p>
                            <small><?php echo $stats['last_update'] ? date('h:i A', strtotime($stats['last_update'])) : 'No updates yet'; ?></small>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons" style="margin: 20px 0; display: flex; gap: 10px;">
                    <button class="btn btn-primary" onclick="processQueue()">
                        <i class="fas fa-sync"></i> Process Pending Queue Now
                    </button>
                    <button class="btn btn-secondary" onclick="viewQueue()">
                        <i class="fas fa-list"></i> View Training Queue
                    </button>
                    <button class="btn btn-info" onclick="refreshStats()">
                        <i class="fas fa-refresh"></i> Refresh Statistics
                    </button>
                </div>
                
                <!-- Category Distribution Chart -->
                <div class="chart-container" style="margin-top: 30px;">
                    <h3><i class="fas fa-chart-pie"></i> Category Distribution (Learned Samples)</h3>
                    <div class="category-stats">
                        <?php if (!empty($stats['category_distribution'])): ?>
                            <?php 
                            $total = array_sum(array_column($stats['category_distribution'], 'count'));
                            foreach ($stats['category_distribution'] as $cat): 
                                $percentage = ($cat['count'] / $total) * 100;
                            ?>
                            <div class="category-bar">
                                <div class="category-label">
                                    <span class="category-name"><?php echo htmlspecialchars($cat['confirmed_category']); ?></span>
                                    <span class="category-count"><?php echo $cat['count']; ?> samples (<?php echo round($percentage, 1); ?>%)</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-data">No learned samples yet. Upload and confirm files to start learning!</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Information Section -->
                <div class="info-section" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                    <h3><i class="fas fa-info-circle"></i> How Incremental Learning Works</h3>
                    <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 15px;">
                        <div class="info-item">
                            <h4><i class="fas fa-upload"></i> 1. Upload File</h4>
                            <p>User uploads a document through the system</p>
                        </div>
                        <div class="info-item">
                            <h4><i class="fas fa-robot"></i> 2. ML Predicts</h4>
                            <p>Model predicts the document category</p>
                        </div>
                        <div class="info-item">
                            <h4><i class="fas fa-user-check"></i> 3. User Confirms</h4>
                            <p>User confirms or corrects the category</p>
                        </div>
                        <div class="info-item">
                            <h4><i class="fas fa-brain"></i> 4. Model Learns</h4>
                            <p>System queues sample for incremental training</p>
                        </div>
                        <div class="info-item">
                            <h4><i class="fas fa-layer-group"></i> 5. Batch Processing</h4>
                            <p>After 10 samples, model updates automatically</p>
                        </div>
                        <div class="info-item">
                            <h4><i class="fas fa-chart-line"></i> 6. Improved Accuracy</h4>
                            <p>Model gets smarter with each upload!</p>
                        </div>
                    </div>
                </div>
                
                <!-- Settings Section -->
                <div class="settings-section" style="margin-top: 30px;">
                    <h3><i class="fas fa-cog"></i> Learning Settings</h3>
                    <table class="settings-table">
                        <tr>
                            <td><strong>Model Name:</strong></td>
                            <td><?php echo htmlspecialchars($stats['model_name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Batch Size:</strong></td>
                            <td>10 samples (trains when queue reaches 10)</td>
                        </tr>
                        <tr>
                            <td><strong>Auto-Processing:</strong></td>
                            <td><span class="badge badge-success">Enabled</span></td>
                        </tr>
                        <tr>
                            <td><strong>Learning Status:</strong></td>
                            <td><span class="badge badge-success">Active</span></td>
                        </tr>
                    </table>
                </div>
                
            </div>
            <?php else: ?>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Incremental Learning Not Available</strong>
                    <p><?php echo htmlspecialchars($stats['error']); ?></p>
                    <p>To enable incremental learning:</p>
                    <ol>
                        <li>Run the database migration: <code>add_incremental_learning.sql</code></li>
                        <li>Train an initial incremental model in ML Management</li>
                        <li>Enable incremental learning in ML settings</li>
                    </ol>
                    <a href="ml-management.php" class="btn btn-primary">Go to ML Management</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <script src="../js/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function processQueue() {
            Swal.fire({
                title: 'Processing Training Queue',
                text: 'Model is learning from pending samples...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: 'ajax.php',
                method: 'POST',
                data: { action: 'process_ml_queue' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Queue Processed!',
                            html: `
                                <p>Samples processed: <strong>${response.samples_processed}</strong></p>
                                <p>Total samples trained: <strong>${response.total_samples_trained || 'N/A'}</strong></p>
                                ${response.new_categories && response.new_categories.length > 0 ? 
                                    `<p>New categories learned: <strong>${response.new_categories.join(', ')}</strong></p>` : ''}
                            `,
                            timer: 3000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'No Samples to Process',
                            text: response.message || response.error,
                            timer: 2000
                        });
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to process queue', 'error');
                }
            });
        }
        
        function viewQueue() {
            window.location.href = 'ml-training-queue.php';
        }
        
        function refreshStats() {
            location.reload();
        }
    </script>
    
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            border-radius: 10px;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .gradient-blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-green { background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: #333; }
        .gradient-orange { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .gradient-purple { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .stat-info h3 {
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .stat-info p {
            margin: 5px 0 0 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .stat-info small {
            font-size: 0.75rem;
            opacity: 0.7;
        }
        
        .category-bar {
            margin-bottom: 15px;
        }
        
        .category-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .category-name {
            font-weight: bold;
        }
        
        .progress-bar {
            width: 100%;
            height: 25px;
            background: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            transition: width 0.3s ease;
        }
        
        .settings-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .settings-table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        
        .badge-success {
            background: #28a745;
            color: white;
        }
        
        .action-buttons {
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .info-item h4 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .info-item p {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</body>
</html>
