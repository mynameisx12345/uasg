-- =====================================================
-- Incremental ML Learning Database Migration
-- Adds support for continuous learning from uploaded files
-- =====================================================

-- Table for queuing training samples from file uploads
CREATE TABLE IF NOT EXISTS `ml_incremental_training_queue_tbl` (
  `queue_id` INT NOT NULL AUTO_INCREMENT,
  `file_upload_id` INT DEFAULT NULL COMMENT 'Reference to uploaded file',
  `training_text` TEXT NOT NULL COMMENT 'Extracted text for training',
  `confirmed_category` VARCHAR(255) NOT NULL COMMENT 'User-confirmed category',
  `prediction_confidence` DECIMAL(5,4) DEFAULT NULL COMMENT 'Original ML confidence (0-1)',
  `status` ENUM('pending', 'processed', 'failed') DEFAULT 'pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `processed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`queue_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_file_upload` (`file_upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Queues training samples for incremental ML learning';

-- Add incremental learning columns to ml_models_tbl (only if they don't exist)
SET @col1_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'ml_models_tbl' AND column_name = 'is_incremental');
SET @col2_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'ml_models_tbl' AND column_name = 'total_incremental_samples');
SET @col3_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'ml_models_tbl' AND column_name = 'last_incremental_update');

SET @sql1 = IF(@col1_exists = 0, 
    'ALTER TABLE `ml_models_tbl` ADD COLUMN `is_incremental` TINYINT(1) DEFAULT 0 COMMENT ''1 if model supports incremental learning''', 
    'SELECT "Column is_incremental already exists"');
PREPARE stmt1 FROM @sql1; EXECUTE stmt1; DEALLOCATE PREPARE stmt1;

SET @sql2 = IF(@col2_exists = 0, 
    'ALTER TABLE `ml_models_tbl` ADD COLUMN `total_incremental_samples` INT DEFAULT 0 COMMENT ''Total samples learned incrementally''', 
    'SELECT "Column total_incremental_samples already exists"');
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

SET @sql3 = IF(@col3_exists = 0, 
    'ALTER TABLE `ml_models_tbl` ADD COLUMN `last_incremental_update` DATETIME DEFAULT NULL COMMENT ''Last time model was updated incrementally''', 
    'SELECT "Column last_incremental_update already exists"');
PREPARE stmt3 FROM @sql3; EXECUTE stmt3; DEALLOCATE PREPARE stmt3;

-- Add index for faster queries (only if it doesn't exist)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE table_schema = DATABASE() AND table_name = 'ml_models_tbl' AND index_name = 'idx_is_incremental');
SET @sql4 = IF(@idx_exists = 0, 
    'ALTER TABLE `ml_models_tbl` ADD KEY `idx_is_incremental` (`is_incremental`)', 
    'SELECT "Index idx_is_incremental already exists"');
PREPARE stmt4 FROM @sql4; EXECUTE stmt4; DEALLOCATE PREPARE stmt4;

-- Add setting for enabling/disabling incremental learning
INSERT INTO `ml_settings_tbl` (`setting_key`, `setting_value`, `setting_description`) 
VALUES ('incremental_learning_enabled', '0', 'Enable automatic incremental learning from uploaded files')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Add setting for batch size
INSERT INTO `ml_settings_tbl` (`setting_key`, `setting_value`, `setting_description`) 
VALUES ('incremental_batch_size', '10', 'Number of samples to accumulate before incremental training')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Add setting for auto-processing
INSERT INTO `ml_settings_tbl` (`setting_key`, `setting_value`, `setting_description`) 
VALUES ('incremental_auto_process', '1', 'Automatically process training queue when batch size is reached')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Add index to file_upload_tbl for faster lookups (only if it doesn't exist)
SET @index_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE table_schema = DATABASE() 
    AND table_name = 'file_upload_tbl' 
    AND index_name = 'idx_category_created');

SET @sql = IF(@index_exists = 0, 
    'ALTER TABLE `file_upload_tbl` ADD KEY `idx_category_created` (`category`, `created_at`)', 
    'SELECT "Index already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- Notes:
-- =====================================================
-- 1. After running this migration, train an initial incremental model
-- 2. Enable incremental learning in ML settings
-- 3. Model will automatically learn from each file upload
-- 4. Training happens in batches (default: 10 samples)
-- 5. No need for manual retraining - model learns continuously
