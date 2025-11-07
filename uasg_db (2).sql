-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 07, 2025 at 05:29 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uasg_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `deleted_record_tbl`
--

DROP TABLE IF EXISTS `deleted_record_tbl`;
CREATE TABLE IF NOT EXISTS `deleted_record_tbl` (
  `delete_id` int NOT NULL AUTO_INCREMENT,
  `data_deleted` text COLLATE utf8mb4_general_ci NOT NULL,
  `reason_for_deletion` text COLLATE utf8mb4_general_ci NOT NULL,
  `table_origin` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `datetime_deleted` datetime NOT NULL,
  PRIMARY KEY (`delete_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `file_category_tbl`
--

DROP TABLE IF EXISTS `file_category_tbl`;
CREATE TABLE IF NOT EXISTS `file_category_tbl` (
  `file_category_id` int NOT NULL AUTO_INCREMENT,
  `file_category` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`file_category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `file_category_tbl`
--

INSERT INTO `file_category_tbl` (`file_category_id`, `file_category`) VALUES
(1, 'Resolutions'),
(2, 'Amendments');

-- --------------------------------------------------------

--
-- Table structure for table `file_permission_tbl`
--

DROP TABLE IF EXISTS `file_permission_tbl`;
CREATE TABLE IF NOT EXISTS `file_permission_tbl` (
  `file_permission_id` int NOT NULL AUTO_INCREMENT,
  `position_id` int NOT NULL,
  `file_category_id` int NOT NULL,
  PRIMARY KEY (`file_permission_id`),
  KEY `position_id` (`position_id`),
  KEY `file_category_id` (`file_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `file_upload_tbl`
--

DROP TABLE IF EXISTS `file_upload_tbl`;
CREATE TABLE IF NOT EXISTS `file_upload_tbl` (
  `file_upload_id` int NOT NULL AUTO_INCREMENT,
  `file_category_id` int NOT NULL,
  `mime_type` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `file_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `drive_id` text COLLATE utf8mb4_general_ci NOT NULL,
  `datetime_uploaded` datetime NOT NULL,
  `uploaded_by` int NOT NULL,
  PRIMARY KEY (`file_upload_id`),
  KEY `file_category_id` (`file_category_id`),
  KEY `uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts_tbl`
--

DROP TABLE IF EXISTS `login_attempts_tbl`;
CREATE TABLE IF NOT EXISTS `login_attempts_tbl` (
  `attempt_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `success` tinyint(1) DEFAULT '0',
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attempt_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attempt_id`),
  KEY `idx_username_time` (`username`,`attempt_time`),
  KEY `idx_success_time` (`success`,`attempt_time`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_attempts_tbl`
--

INSERT INTO `login_attempts_tbl` (`attempt_id`, `username`, `ip_address`, `user_agent`, `success`, `details`, `attempt_time`) VALUES
(3, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 1, 'Login successful', '2025-11-07 03:22:56'),
(6, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 0, 'Invalid password', '2025-11-07 03:30:45'),
(7, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 0, NULL, '2025-11-07 03:30:45'),
(8, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 0, 'Invalid password', '2025-11-07 03:34:09'),
(9, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 0, NULL, '2025-11-07 03:34:09'),
(12, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 1, 'Login successful', '2025-11-07 03:40:30');

-- --------------------------------------------------------

--
-- Table structure for table `notifications_tbl`
--

DROP TABLE IF EXISTS `notifications_tbl`;
CREATE TABLE IF NOT EXISTS `notifications_tbl` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `related_id` int DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `datetime_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `datetime_created` (`datetime_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `position_tbl`
--

DROP TABLE IF EXISTS `position_tbl`;
CREATE TABLE IF NOT EXISTS `position_tbl` (
  `position_id` int NOT NULL AUTO_INCREMENT,
  `position` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `access_restriction` int DEFAULT '1',
  PRIMARY KEY (`position_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `position_tbl`
--

INSERT INTO `position_tbl` (`position_id`, `position`, `access_restriction`) VALUES
(1, 'Adviser', 2),
(2, 'Student Government Member', 1),
(3, 'System Administrator', 3),
(4, 'Guest User', 0);

-- --------------------------------------------------------

--
-- Table structure for table `profile_tbl`
--

DROP TABLE IF EXISTS `profile_tbl`;
CREATE TABLE IF NOT EXISTS `profile_tbl` (
  `profile_id` int NOT NULL AUTO_INCREMENT,
  `fname` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `mname` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `lname` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `auxname` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gender` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `birthdate` date NOT NULL,
  `contact_number` varchar(13) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`profile_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profile_tbl`
--

INSERT INTO `profile_tbl` (`profile_id`, `fname`, `mname`, `lname`, `auxname`, `gender`, `birthdate`, `contact_number`, `email`) VALUES
(4, 'System', '', 'Administrator', '', 'Not specified', '1990-01-01', '', 'admin@system.local');

-- --------------------------------------------------------

--
-- Table structure for table `task_category_tbl`
--

DROP TABLE IF EXISTS `task_category_tbl`;
CREATE TABLE IF NOT EXISTS `task_category_tbl` (
  `task_category_id` int NOT NULL AUTO_INCREMENT,
  `task_category` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`task_category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_category_tbl`
--

INSERT INTO `task_category_tbl` (`task_category_id`, `task_category`) VALUES
(1, 'Assignment'),
(2, 'Project');

-- --------------------------------------------------------

--
-- Table structure for table `task_submission_tbl`
--

DROP TABLE IF EXISTS `task_submission_tbl`;
CREATE TABLE IF NOT EXISTS `task_submission_tbl` (
  `task_submission_id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `file_upload_id` int NOT NULL,
  `check_status` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`task_submission_id`),
  KEY `task_id` (`task_id`),
  KEY `file_upload_id` (`file_upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_tbl`
--

DROP TABLE IF EXISTS `task_tbl`;
CREATE TABLE IF NOT EXISTS `task_tbl` (
  `task_id` int NOT NULL AUTO_INCREMENT,
  `task_category_id` int NOT NULL,
  `task_title` text COLLATE utf8mb4_general_ci NOT NULL,
  `task_description` text COLLATE utf8mb4_general_ci NOT NULL,
  `task_deadline` date NOT NULL,
  PRIMARY KEY (`task_id`),
  KEY `task_category_id` (`task_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_tbl`
--

DROP TABLE IF EXISTS `user_tbl`;
CREATE TABLE IF NOT EXISTS `user_tbl` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `user_name` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `pass_word` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `position_id` int NOT NULL,
  `profile_id` int NOT NULL,
  `user_type` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `auth_token` varchar(250) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `position_id` (`position_id`),
  KEY `profile_id` (`profile_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_tbl`
--

INSERT INTO `user_tbl` (`user_id`, `user_name`, `pass_word`, `position_id`, `profile_id`, `user_type`, `auth_token`) VALUES
(4, 'admin', '$2y$10$oDB5bY0WJheUhZFZO9jo/uACNilbIOv3C6jxl9fbRvd8BXAbZI/Qi', 3, 4, 'admin', '7a27c2d2b5c2d491e9cd225fbca92c1de1347befb6cb4fc2334c98fb9e8f0c78');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `file_permission_tbl`
--
ALTER TABLE `file_permission_tbl`
  ADD CONSTRAINT `file_permission_tbl_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `position_tbl` (`position_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `file_permission_tbl_ibfk_2` FOREIGN KEY (`file_category_id`) REFERENCES `file_category_tbl` (`file_category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `file_upload_tbl`
--
ALTER TABLE `file_upload_tbl`
  ADD CONSTRAINT `file_upload_tbl_ibfk_1` FOREIGN KEY (`file_category_id`) REFERENCES `file_category_tbl` (`file_category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `file_upload_tbl_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications_tbl`
--
ALTER TABLE `notifications_tbl`
  ADD CONSTRAINT `notifications_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task_submission_tbl`
--
ALTER TABLE `task_submission_tbl`
  ADD CONSTRAINT `task_submission_tbl_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `task_tbl` (`task_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `task_submission_tbl_ibfk_2` FOREIGN KEY (`file_upload_id`) REFERENCES `file_upload_tbl` (`file_upload_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task_tbl`
--
ALTER TABLE `task_tbl`
  ADD CONSTRAINT `task_tbl_ibfk_1` FOREIGN KEY (`task_category_id`) REFERENCES `task_category_tbl` (`task_category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_tbl`
--
ALTER TABLE `user_tbl`
  ADD CONSTRAINT `user_tbl_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `position_tbl` (`position_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_tbl_ibfk_2` FOREIGN KEY (`profile_id`) REFERENCES `profile_tbl` (`profile_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
