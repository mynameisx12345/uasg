-- Add table for sub-admin roles
CREATE TABLE IF NOT EXISTS `subadmin_roles_tbl` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `role` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `unique_user_role` (`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_subadmin_roles_user` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Update position table to include the sub-admin roles
INSERT INTO `position_tbl` (`position`, `access_restriction`) VALUES
('Adviser', 2),
('President', 2),
('Vice-President', 2),
('Secretary', 2)
ON DUPLICATE KEY UPDATE access_restriction = VALUES(access_restriction);