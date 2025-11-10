-- Migration: Convert Adviser to Subadmin with Permissions System
-- Date: November 10, 2025
-- Description: This migration renames adviser role to subadmin and adds a granular permissions system

-- Step 1: Create subadmin_permissions table
CREATE TABLE IF NOT EXISTS `subadmin_permissions_tbl` (
  `permission_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `permission_key` VARCHAR(100) NOT NULL,
  `permission_name` VARCHAR(255) NOT NULL,
  `can_view` TINYINT(1) DEFAULT 0,
  `can_create` TINYINT(1) DEFAULT 0,
  `can_edit` TINYINT(1) DEFAULT 0,
  `can_delete` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`permission_id`),
  KEY `user_id` (`user_id`),
  KEY `permission_key` (`permission_key`),
  UNIQUE KEY `unique_user_permission` (`user_id`, `permission_key`),
  CONSTRAINT `fk_subadmin_permissions_user` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Step 2: Create permission_definitions table to store available permissions
CREATE TABLE IF NOT EXISTS `permission_definitions_tbl` (
  `definition_id` INT NOT NULL AUTO_INCREMENT,
  `permission_key` VARCHAR(100) NOT NULL UNIQUE,
  `permission_name` VARCHAR(255) NOT NULL,
  `permission_description` TEXT,
  `permission_category` VARCHAR(100) DEFAULT 'general',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`definition_id`),
  KEY `permission_key` (`permission_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Step 3: Insert default permission definitions
INSERT INTO `permission_definitions_tbl` (`permission_key`, `permission_name`, `permission_description`, `permission_category`) VALUES
('task_management', 'Task Management', 'Create, assign, and manage tasks for members', 'tasks'),
('task_submission_review', 'Task Submission Review', 'View and review task submissions from members', 'tasks'),
('file_management', 'File Management', 'Upload, organize, and manage files', 'files'),
('file_approval', 'File Approval', 'Approve or reject file uploads', 'files'),
('user_view', 'User Viewing', 'View user information and profiles', 'users'),
('reports_view', 'Reports Viewing', 'Access and view system reports', 'reports'),
('reports_generate', 'Reports Generation', 'Generate new reports and analytics', 'reports'),
('notifications_send', 'Send Notifications', 'Send notifications to users', 'notifications'),
('category_management', 'Category Management', 'Manage file and task categories', 'system'),
('dashboard_analytics', 'Dashboard Analytics', 'View dashboard statistics and analytics', 'dashboard');

-- Step 4: Update user_type from 'adviser' to 'subadmin' in user_tbl
UPDATE `user_tbl` 
SET `user_type` = 'subadmin' 
WHERE `user_type` = 'adviser' OR `user_type` = 'Adviser';

-- Step 5: Create subadmin_activity_log table for tracking subadmin actions
CREATE TABLE IF NOT EXISTS `subadmin_activity_log_tbl` (
  `activity_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `action` VARCHAR(255) NOT NULL,
  `permission_key` VARCHAR(100),
  `target_type` VARCHAR(100),
  `target_id` INT,
  `details` TEXT,
  `ip_address` VARCHAR(45),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`activity_id`),
  KEY `user_id` (`user_id`),
  KEY `permission_key` (`permission_key`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `fk_subadmin_activity_user` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Step 6: Grant default permissions to existing subadmins (previously advisers)
-- This gives full access to existing advisers during migration
INSERT INTO `subadmin_permissions_tbl` (`user_id`, `permission_key`, `permission_name`, `can_view`, `can_create`, `can_edit`, `can_delete`)
SELECT 
  u.user_id,
  pd.permission_key,
  pd.permission_name,
  1, -- can_view
  1, -- can_create
  1, -- can_edit
  1  -- can_delete
FROM `user_tbl` u
CROSS JOIN `permission_definitions_tbl` pd
WHERE u.user_type = 'subadmin'
AND NOT EXISTS (
  SELECT 1 FROM `subadmin_permissions_tbl` sp 
  WHERE sp.user_id = u.user_id AND sp.permission_key = pd.permission_key
);

-- Step 7: Add indexes for better performance
CREATE INDEX idx_user_type ON `user_tbl` (`user_type`);
CREATE INDEX idx_permission_active ON `permission_definitions_tbl` (`is_active`);

-- Migration complete
-- Note: Remember to update application code to use 'subadmin' instead of 'adviser'
