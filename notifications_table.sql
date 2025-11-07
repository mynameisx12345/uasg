-- SQL script to add notifications table to the UASG database
-- Run this script to enable the notification system

-- Create notifications table
CREATE TABLE IF NOT EXISTS `notifications_tbl` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `related_id` int DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `datetime_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `datetime_created` (`datetime_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add foreign key constraint
ALTER TABLE `notifications_tbl`
  ADD CONSTRAINT `notifications_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Sample data for testing (optional - you can remove this section)
-- INSERT INTO `notifications_tbl` (`user_id`, `type`, `title`, `message`, `related_id`, `is_read`) VALUES
-- (1, 'new_task', 'New Task Assigned', 'New task "Monthly Report" has been assigned to you', 1, 0),
-- (2, 'new_submission', 'New Task Submission', 'Student John Doe submitted "Monthly Report"', 1, 0);