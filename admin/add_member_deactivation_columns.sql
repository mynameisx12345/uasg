-- Add member deactivation columns to user_tbl
-- Run this script once to enable member deactivation functionality

ALTER TABLE `user_tbl` 
ADD COLUMN `is_active` TINYINT(1) DEFAULT 1 COMMENT '1=active, 0=deactivated',
ADD COLUMN `deactivated_at` DATETIME NULL DEFAULT NULL COMMENT 'When the user was deactivated',
ADD COLUMN `deactivation_reason` TEXT NULL DEFAULT NULL COMMENT 'Reason for deactivation';

-- Update existing records to be active by default
UPDATE `user_tbl` SET `is_active` = 1 WHERE `is_active` IS NULL;
