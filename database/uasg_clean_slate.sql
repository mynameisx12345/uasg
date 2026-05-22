-- UASG Clean Slate Database Script
-- Generated: 2026-05-23
-- Includes all migrations (001-004)
-- Run this on a fresh database

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- =============================================
-- CORE TABLES (no FK dependencies)
-- =============================================

DROP TABLE IF EXISTS `position_tbl`;
CREATE TABLE `position_tbl` (
  `position_id` int NOT NULL AUTO_INCREMENT,
  `position` varchar(250) NOT NULL,
  `access_restriction` int DEFAULT '1',
  PRIMARY KEY (`position_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `profile_tbl`;
CREATE TABLE `profile_tbl` (
  `profile_id` int NOT NULL AUTO_INCREMENT,
  `fname` varchar(250) NOT NULL,
  `mname` varchar(250) NOT NULL,
  `lname` varchar(250) NOT NULL,
  `auxname` varchar(20) DEFAULT NULL,
  `gender` varchar(20) NOT NULL,
  `birthdate` date NOT NULL,
  `contact_number` varchar(13) DEFAULT NULL,
  `email` varchar(250) NOT NULL,
  PRIMARY KEY (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `category_tbl`;
CREATE TABLE `category_tbl` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `category_slug` varchar(255) NOT NULL COMMENT 'URL-safe directory name',
  `description` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`),
  UNIQUE KEY `category_slug` (`category_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `task_category_tbl`;
CREATE TABLE `task_category_tbl` (
  `task_category_id` int NOT NULL AUTO_INCREMENT,
  `task_category` text NOT NULL,
  PRIMARY KEY (`task_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `deleted_record_tbl`;
CREATE TABLE `deleted_record_tbl` (
  `delete_id` int NOT NULL AUTO_INCREMENT,
  `data_deleted` text NOT NULL,
  `reason_for_deletion` text NOT NULL,
  `table_origin` varchar(250) NOT NULL,
  `datetime_deleted` datetime NOT NULL,
  PRIMARY KEY (`delete_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- USER TABLES
-- =============================================

DROP TABLE IF EXISTS `user_tbl`;
CREATE TABLE `user_tbl` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `user_name` varchar(250) NOT NULL,
  `pass_word` varchar(250) NOT NULL,
  `position_id` int NOT NULL,
  `profile_id` int NOT NULL,
  `user_type` varchar(250) NOT NULL,
  `auth_token` varchar(250) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `deactivated_at` datetime DEFAULT NULL,
  `deactivation_reason` text,
  PRIMARY KEY (`user_id`),
  KEY `position_id` (`position_id`),
  KEY `profile_id` (`profile_id`),
  CONSTRAINT `user_tbl_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `position_tbl` (`position_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_tbl_ibfk_2` FOREIGN KEY (`profile_id`) REFERENCES `profile_tbl` (`profile_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `user_security_tbl`;
CREATE TABLE `user_security_tbl` (
  `security_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `question1` varchar(255) DEFAULT NULL,
  `answer1` varchar(255) DEFAULT NULL,
  `question2` varchar(255) DEFAULT NULL,
  `answer2` varchar(255) DEFAULT NULL,
  `recovery_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`security_id`),
  UNIQUE KEY `unique_user` (`user_id`),
  CONSTRAINT `user_security_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `login_attempts_tbl`;
CREATE TABLE `login_attempts_tbl` (
  `attempt_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `success` tinyint(1) DEFAULT '0',
  `details` text,
  `attempt_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attempt_id`),
  KEY `idx_username_time` (`username`,`attempt_time`),
  KEY `idx_success_time` (`success`,`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- SUBADMIN TABLES
-- =============================================

DROP TABLE IF EXISTS `subadmin_permissions_tbl`;
CREATE TABLE `subadmin_permissions_tbl` (
  `permission_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `permission_name` varchar(255) NOT NULL,
  `can_view` tinyint(1) DEFAULT '0',
  `can_create` tinyint(1) DEFAULT '0',
  `can_edit` tinyint(1) DEFAULT '0',
  `can_delete` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `unique_user_permission` (`user_id`,`permission_key`),
  KEY `user_id` (`user_id`),
  KEY `permission_key` (`permission_key`),
  CONSTRAINT `fk_subadmin_permissions_user` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `subadmin_roles_tbl`;
CREATE TABLE `subadmin_roles_tbl` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `role` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `unique_user_role` (`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_subadmin_roles_user` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `subadmin_activity_log_tbl`;
CREATE TABLE `subadmin_activity_log_tbl` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(100) NOT NULL,
  `related_id` int DEFAULT NULL,
  `details` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- FILE TABLES
-- =============================================

DROP TABLE IF EXISTS `file_upload_tbl`;
CREATE TABLE `file_upload_tbl` (
  `file_upload_id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `category_tag` varchar(255) DEFAULT NULL,
  `category_score` decimal(5,2) DEFAULT NULL,
  `classification_method` varchar(50) DEFAULT NULL,
  `is_overridden` tinyint(1) NOT NULL DEFAULT 0,
  `original_category_tag` varchar(100) DEFAULT NULL,
  `ml_model_id` int DEFAULT NULL,
  `mime_type` varchar(250) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` text,
  `file_size` int unsigned NOT NULL DEFAULT '0',
  `drive_id` text,
  `datetime_uploaded` datetime NOT NULL,
  `uploaded_by` int NOT NULL,
  PRIMARY KEY (`file_upload_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `category_id` (`category_id`),
  KEY `idx_category_uploaded` (`category_id`,`datetime_uploaded`),
  KEY `idx_ml_model` (`ml_model_id`),
  CONSTRAINT `file_upload_tbl_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_file_category` FOREIGN KEY (`category_id`) REFERENCES `category_tbl` (`category_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `file_permission_tbl`;
CREATE TABLE `file_permission_tbl` (
  `file_permission_id` int NOT NULL AUTO_INCREMENT,
  `position_id` int NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`file_permission_id`),
  KEY `position_id` (`position_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `file_permission_tbl_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `position_tbl` (`position_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `file_permission_tbl_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category_tbl` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `file_nlp_analysis_tbl`;
CREATE TABLE `file_nlp_analysis_tbl` (
  `analysis_id` int NOT NULL AUTO_INCREMENT,
  `file_upload_id` int NOT NULL,
  `extracted_text` longtext,
  `word_count` int DEFAULT '0',
  `suggested_category` varchar(255) DEFAULT NULL,
  `category_confidence` decimal(5,2) DEFAULT NULL,
  `keywords` json DEFAULT NULL,
  `entities` json DEFAULT NULL,
  `sentiment` json DEFAULT NULL,
  `full_analysis` json DEFAULT NULL,
  `provider` varchar(50) DEFAULT 'nlpcloud',
  `processing_time_ms` int DEFAULT NULL,
  `analyzed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`analysis_id`),
  KEY `idx_file_upload` (`file_upload_id`),
  KEY `idx_suggested_category` (`suggested_category`),
  KEY `idx_analyzed_at` (`analyzed_at`),
  KEY `idx_provider` (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- TASK TABLES
-- =============================================

DROP TABLE IF EXISTS `task_tbl`;
CREATE TABLE `task_tbl` (
  `task_id` int NOT NULL AUTO_INCREMENT,
  `task_category_id` int NOT NULL,
  `task_title` text NOT NULL,
  `task_description` text NOT NULL,
  `task_deadline` date NOT NULL,
  `assigned_to` int DEFAULT NULL,
  `task_status` varchar(20) NOT NULL DEFAULT 'active',
  PRIMARY KEY (`task_id`),
  KEY `task_category_id` (`task_category_id`),
  KEY `assigned_to` (`assigned_to`),
  KEY `idx_task_status` (`task_status`),
  CONSTRAINT `task_tbl_ibfk_1` FOREIGN KEY (`task_category_id`) REFERENCES `task_category_tbl` (`task_category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `task_tbl_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `task_submission_tbl`;
CREATE TABLE `task_submission_tbl` (
  `task_submission_id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `file_upload_id` int NOT NULL,
  `check_status` varchar(250) NOT NULL,
  `submitted_by` int DEFAULT NULL,
  PRIMARY KEY (`task_submission_id`),
  KEY `task_id` (`task_id`),
  KEY `file_upload_id` (`file_upload_id`),
  KEY `submitted_by` (`submitted_by`),
  CONSTRAINT `task_submission_tbl_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `task_tbl` (`task_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `task_submission_tbl_ibfk_2` FOREIGN KEY (`file_upload_id`) REFERENCES `file_upload_tbl` (`file_upload_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `task_submission_tbl_ibfk_3` FOREIGN KEY (`submitted_by`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- ML TABLES
-- =============================================

DROP TABLE IF EXISTS `ml_training_datasets_tbl`;
CREATE TABLE `ml_training_datasets_tbl` (
  `dataset_id` int NOT NULL AUTO_INCREMENT,
  `dataset_name` varchar(255) NOT NULL,
  `file_path` text NOT NULL,
  `total_samples` int DEFAULT '0',
  `categories_count` int DEFAULT '0',
  `categories` json DEFAULT NULL,
  `uploaded_by` int NOT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `description` text,
  PRIMARY KEY (`dataset_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `fk_dataset_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `ml_models_tbl`;
CREATE TABLE `ml_models_tbl` (
  `model_id` int NOT NULL AUTO_INCREMENT,
  `model_name` varchar(255) NOT NULL,
  `dataset_id` int NOT NULL,
  `model_type` varchar(50) DEFAULT 'tfidf_svm',
  `model_path` text NOT NULL,
  `vectorizer_path` text NOT NULL,
  `accuracy_score` decimal(5,2) DEFAULT NULL,
  `precision_score` decimal(5,2) DEFAULT NULL,
  `recall_score` decimal(5,2) DEFAULT NULL,
  `f1_score` decimal(5,2) DEFAULT NULL,
  `categories` json DEFAULT NULL,
  `training_samples` int DEFAULT '0',
  `test_samples` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '0',
  `trained_by` int NOT NULL,
  `trained_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_used_at` datetime DEFAULT NULL,
  `usage_count` int DEFAULT '0',
  `is_incremental` tinyint(1) DEFAULT '0',
  `total_incremental_samples` int DEFAULT '0',
  `last_incremental_update` datetime DEFAULT NULL,
  PRIMARY KEY (`model_id`),
  KEY `dataset_id` (`dataset_id`),
  KEY `trained_by` (`trained_by`),
  KEY `idx_active` (`is_active`),
  KEY `idx_trained_at` (`trained_at`),
  KEY `idx_is_incremental` (`is_incremental`),
  CONSTRAINT `fk_model_dataset` FOREIGN KEY (`dataset_id`) REFERENCES `ml_training_datasets_tbl` (`dataset_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_model_trainer` FOREIGN KEY (`trained_by`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `ml_prediction_history_tbl`;
CREATE TABLE `ml_prediction_history_tbl` (
  `prediction_id` int NOT NULL AUTO_INCREMENT,
  `model_id` int NOT NULL,
  `file_upload_id` int DEFAULT NULL,
  `input_text` text,
  `predicted_category` varchar(255) DEFAULT NULL,
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `prediction_time_ms` int DEFAULT NULL,
  `was_accepted` tinyint(1) DEFAULT NULL,
  `actual_category` varchar(255) DEFAULT NULL,
  `predicted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`prediction_id`),
  KEY `model_id` (`model_id`),
  KEY `file_upload_id` (`file_upload_id`),
  KEY `idx_predicted_at` (`predicted_at`),
  CONSTRAINT `fk_prediction_file` FOREIGN KEY (`file_upload_id`) REFERENCES `file_upload_tbl` (`file_upload_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_prediction_model` FOREIGN KEY (`model_id`) REFERENCES `ml_models_tbl` (`model_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `ml_settings_tbl`;
CREATE TABLE `ml_settings_tbl` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_description` text,
  `updated_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `fk_ml_settings_user` FOREIGN KEY (`updated_by`) REFERENCES `user_tbl` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `ml_incremental_training_queue_tbl`;
CREATE TABLE `ml_incremental_training_queue_tbl` (
  `queue_id` int NOT NULL AUTO_INCREMENT,
  `file_upload_id` int DEFAULT NULL,
  `training_text` text NOT NULL,
  `confirmed_category` varchar(255) NOT NULL,
  `prediction_confidence` decimal(5,2) DEFAULT NULL,
  `status` enum('pending','processed','failed') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `processed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`queue_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_file_upload` (`file_upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- NOTIFICATIONS TABLE (migration 004)
-- =============================================

DROP TABLE IF EXISTS `notifications_tbl`;
CREATE TABLE `notifications_tbl` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(60) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `related_id` int DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `datetime_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `idx_user_read` (`user_id`, `is_read`),
  KEY `idx_created` (`datetime_created`),
  CONSTRAINT `notifications_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- SYSTEM SETTINGS TABLE (migration 003)
-- =============================================

DROP TABLE IF EXISTS `system_settings_tbl`;
CREATE TABLE `system_settings_tbl` (
  `setting_key` varchar(60) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `system_settings_tbl` (`setting_key`, `setting_value`) VALUES
('show_upload_disclaimer', '1'),
('allow_manual_override', '1'),
('override_roles_file_explorer', '["subadmin","member"]'),
('override_roles_batch_upload', '["subadmin","member"]');

-- =============================================
-- MIGRATIONS TRACKER
-- =============================================

DROP TABLE IF EXISTS `migrations_tbl`;
CREATE TABLE `migrations_tbl` (
  `id` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `ran_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filename` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Mark all migrations as already applied
INSERT INTO `migrations_tbl` (`filename`) VALUES
('001_fix_confidence_column.sql'),
('002_add_override_columns.sql'),
('003_create_system_settings.sql'),
('004_create_notifications_tbl.sql');

-- =============================================
-- DEFAULT DATA
-- =============================================

-- Default admin position and profile
INSERT INTO `position_tbl` (`position_id`, `position`, `access_restriction`) VALUES
(2, 'President', 2),
(3, 'Vice President', 2),
(5, 'Senator', 1),
(6, 'Secretary', 1),
(7, 'Treasurer', 1),
(8, 'Auditor', 1),
(10, 'P.I.O', 1),
(11, 'Adviser', 2);

INSERT INTO `profile_tbl` (`profile_id`, `fname`, `mname`, `lname`, `auxname`, `gender`, `birthdate`, `contact_number`, `email`) VALUES
(1, 'System', '', 'Admin', NULL, 'Male', '2000-01-01', NULL, 'admin@uasg.edu.ph');

-- Default admin user (password: admin123)
INSERT INTO `user_tbl` (`user_id`, `user_name`, `pass_word`, `position_id`, `profile_id`, `user_type`, `is_active`) VALUES
(4, 'admin', '$2y$12$En0QtO3TQ4/YsmtEObtzgO1FWRqABNWWaLC0tSY.HkFxtFm7KKVEC', 2, 1, 'admin', 1);

-- Default categories
INSERT INTO `category_tbl` (`category_name`, `category_slug`, `description`) VALUES
('Accomplishment Report', 'accomplishment-report', 'Accomplishment report documents'),
('Activity Design', 'activity-design', 'Activity design documents'),
('Letter', 'letter', 'Letter documents'),
('Resolution', 'resolution', 'Resolution documents');

-- Default ML settings
INSERT INTO `ml_settings_tbl` (`setting_key`, `setting_value`, `setting_description`) VALUES
('classification_method', 'ml_primary', 'Primary classification method'),
('confidence_threshold', '30', 'Minimum confidence to accept ML prediction'),
('nlp_fallback_enabled', '1', 'Use NLP Cloud as fallback when ML confidence is low'),
('incremental_learning_enabled', '1', 'Enable incremental model learning'),
('incremental_batch_size', '10', 'Batch size for incremental training');

SET FOREIGN_KEY_CHECKS = 1;
