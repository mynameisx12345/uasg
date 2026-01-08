-- Add task_status column to task_tbl
-- This allows tracking of task lifecycle: active, transferred, closed, cancelled

ALTER TABLE `task_tbl` 
ADD COLUMN `task_status` VARCHAR(20) NOT NULL DEFAULT 'active' AFTER `assigned_to`;

-- Update existing tasks to have 'active' status
UPDATE `task_tbl` SET `task_status` = 'active' WHERE `task_status` IS NULL OR `task_status` = '';

-- Create index for faster queries
CREATE INDEX `idx_task_status` ON `task_tbl` (`task_status`);
