-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 07, 2025 at 05:15 AM
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
-- Table structure for table `file_category_key_tbl`
--

DROP TABLE IF EXISTS `file_category_key_tbl`;
CREATE TABLE IF NOT EXISTS `file_category_key_tbl` (
  `file_category_key_id` int NOT NULL AUTO_INCREMENT,
  `file_category_id` int NOT NULL,
  `keyword` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`file_category_key_id`),
  KEY `file_category_id` (`file_category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `file_category_key_tbl`
--

INSERT INTO `file_category_key_tbl` (`file_category_key_id`, `file_category_id`, `keyword`) VALUES
(1, 1, 'resolution'),
(2, 1, 'motion'),
(3, 1, 'vote'),
(4, 1, 'council'),
(5, 2, 'amendment'),
(6, 2, 'change'),
(7, 2, 'constitution'),
(8, 2, 'bylaw'),
(9, 4, 'dear'),
(10, 1, 'resolution'),
(11, 1, 'motion'),
(12, 1, 'whereas'),
(13, 1, 'vote'),
(14, 1, 'council'),
(15, 2, 'minutes'),
(16, 2, 'meeting'),
(17, 2, 'attendance'),
(18, 2, 'agenda');

-- --------------------------------------------------------

--
-- Table structure for table `file_category_tbl`
--

DROP TABLE IF EXISTS `file_category_tbl`;
CREATE TABLE IF NOT EXISTS `file_category_tbl` (
  `file_category_id` int NOT NULL AUTO_INCREMENT,
  `file_category` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`file_category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `file_category_tbl`
--

INSERT INTO `file_category_tbl` (`file_category_id`, `file_category`) VALUES
(1, 'Resolutions'),
(2, 'Amendments'),
(3, 'Minutes'),
(4, 'Letters');

-- --------------------------------------------------------

--
-- Table structure for table `file_nlp_analysis_tbl`
--

DROP TABLE IF EXISTS `file_nlp_analysis_tbl`;
CREATE TABLE IF NOT EXISTS `file_nlp_analysis_tbl` (
  `analysis_id` int NOT NULL AUTO_INCREMENT,
  `file_upload_id` int NOT NULL,
  `extracted_text` longtext COLLATE utf8mb4_general_ci,
  `word_count` int DEFAULT '0',
  `suggested_category` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category_confidence` decimal(5,2) DEFAULT NULL COMMENT 'Confidence score 0-100',
  `keywords` json DEFAULT NULL COMMENT 'Keywords that matched from file_category_key_tbl',
  `entities` json DEFAULT NULL COMMENT 'Entities extracted by Google NLP API',
  `full_analysis` json DEFAULT NULL COMMENT 'Complete analysis result from NLP service',
  `processing_time_ms` int DEFAULT NULL,
  `analyzed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`analysis_id`),
  KEY `idx_file_upload` (`file_upload_id`),
  KEY `idx_suggested_category` (`suggested_category`),
  KEY `idx_analyzed_at` (`analyzed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores NLP analysis results from Google Cloud Natural Language API';

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `file_permission_tbl`
--

INSERT INTO `file_permission_tbl` (`file_permission_id`, `position_id`, `file_category_id`) VALUES
(1, 1, 2),
(2, 1, 1),
(3, 1, 4),
(4, 2, 2),
(5, 2, 4),
(6, 2, 3),
(7, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `file_upload_tbl`
--

DROP TABLE IF EXISTS `file_upload_tbl`;
CREATE TABLE IF NOT EXISTS `file_upload_tbl` (
  `file_upload_id` int NOT NULL AUTO_INCREMENT,
  `file_category_id` int DEFAULT NULL,
  `category_tag` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category_score` decimal(5,2) DEFAULT NULL,
  `mime_type` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `file_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `file_path` text COLLATE utf8mb4_general_ci,
  `file_size` int UNSIGNED NOT NULL DEFAULT '0',
  `drive_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `datetime_uploaded` datetime NOT NULL,
  `uploaded_by` int NOT NULL,
  PRIMARY KEY (`file_upload_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `file_category_id` (`file_category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `file_upload_tbl`
--

INSERT INTO `file_upload_tbl` (`file_upload_id`, `file_category_id`, `category_tag`, `category_score`, `mime_type`, `file_name`, `file_path`, `file_size`, `drive_id`, `datetime_uploaded`, `uploaded_by`) VALUES
(1, 2, 'Amendments', 6.25, 'application/pdf', '6927f67d752bc_1764226685.pdf', 'uploads/files/6927f67d752bc_1764226685.pdf', 215043, NULL, '2025-11-27 06:58:08', 5),
(3, 1, 'Resolutions', 100.00, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '6929364a52346_1764308554.docx', 'uploads/files/6929364a52346_1764308554.docx', 13728, NULL, '2025-11-28 05:42:38', 5),
(4, 1, 'Resolutions', 100.00, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '6934fbdc25735_1765080028.docx', 'uploads/files/6934fbdc25735_1765080028.docx', 13728, NULL, '2025-12-07 04:00:31', 5);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts_tbl`
--

DROP TABLE IF EXISTS `login_attempts_tbl`;
CREATE TABLE IF NOT EXISTS `login_attempts_tbl` (
  `attempt_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `success` tinyint(1) DEFAULT '0',
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `attempt_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attempt_id`),
  KEY `idx_username_time` (`username`,`attempt_time`),
  KEY `idx_success_time` (`success`,`attempt_time`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts_tbl`
--

INSERT INTO `login_attempts_tbl` (`attempt_id`, `username`, `ip_address`, `user_agent`, `success`, `details`, `attempt_time`) VALUES
(3, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 1, 'Login successful', '2025-11-07 03:22:56'),
(6, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 0, 'Invalid password', '2025-11-07 03:30:45'),
(7, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 0, NULL, '2025-11-07 03:30:45'),
(8, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 0, 'Invalid password', '2025-11-07 03:34:09'),
(9, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 0, NULL, '2025-11-07 03:34:09'),
(15, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 0, 'User not found', '2025-11-09 07:24:57'),
(16, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 0, NULL, '2025-11-09 07:24:57'),
(17, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 0, 'User not found', '2025-11-09 07:25:02'),
(18, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 0, NULL, '2025-11-09 07:25:02'),
(19, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 0, 'User not found', '2025-11-09 07:25:05'),
(20, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 0, NULL, '2025-11-09 07:25:05'),
(73, 'juan.perez', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 1, 'Login successful', '2025-12-06 13:09:18'),
(76, 'justin.abuela', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 1, 'Login successful', '2025-12-07 03:59:39'),
(78, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 1, 'Login successful', '2025-12-07 05:05:55'),
(79, 'rustom.caspillo', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 1, 'Login successful', '2025-12-07 05:14:35');

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications_tbl`
--

INSERT INTO `notifications_tbl` (`notification_id`, `user_id`, `type`, `title`, `message`, `related_id`, `is_read`, `datetime_created`) VALUES
(1, 5, 'new_task', 'New Task Assigned', 'New task \'Resolution for Health Benefits\' has been assigned to you', 1, 0, '2025-11-19 17:02:03'),
(2, 5, 'new_task', 'New Task Assigned', 'New task \'Resolution Draft\' has been assigned to you', 5, 0, '2025-11-27 16:45:33'),
(3, 5, 'new_task', 'New Task Assigned', 'New task \'Amendment for School Projects\' has been assigned to you', 6, 0, '2025-12-07 11:58:21'),
(4, 5, 'new_task', 'New Task Assigned', 'New task \'Resolution for Donations\' has been assigned to you', 7, 0, '2025-12-07 12:08:52'),
(5, 5, 'new_task', 'New Task Assigned', 'New task \'Sample\' has been assigned to you', 8, 0, '2025-12-07 12:12:09');

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `position_tbl`
--

INSERT INTO `position_tbl` (`position_id`, `position`, `access_restriction`) VALUES
(1, 'Adviser', 2),
(2, 'Student Government Member', 1),
(3, 'System Administrator', 3),
(4, 'Guest User', 0),
(5, 'Adviser', 2),
(6, 'President', 2),
(7, 'Vice-President', 2),
(8, 'Secretary', 2);

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profile_tbl`
--

INSERT INTO `profile_tbl` (`profile_id`, `fname`, `mname`, `lname`, `auxname`, `gender`, `birthdate`, `contact_number`, `email`) VALUES
(4, 'System', '', 'Administrator', '', 'Not specified', '1990-01-01', '', 'admin@system.local'),
(5, 'Justin', 'Arnaez', 'Abuela', '', 'Male', '1991-11-05', '09812682658', 'justinabuela@gmail.com'),
(6, 'Mika', 'Jay', 'Esparagoza', '', 'Female', '1996-07-12', '', 'mika.esparagoza@gmail.com'),
(7, 'Rustom', 'Pelaez', 'Caspillo', '', 'Male', '1987-07-22', '', 'rustom.caspillo@gmail.com'),
(8, 'Juan', 'Delos Santos', 'Perez', '', 'Male', '1994-05-11', '', 'juan.perez@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `subadmin_activity_log_tbl`
--

DROP TABLE IF EXISTS `subadmin_activity_log_tbl`;
CREATE TABLE IF NOT EXISTS `subadmin_activity_log_tbl` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `related_id` int DEFAULT NULL,
  `details` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subadmin_permissions_tbl`
--

DROP TABLE IF EXISTS `subadmin_permissions_tbl`;
CREATE TABLE IF NOT EXISTS `subadmin_permissions_tbl` (
  `permission_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `permission_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `permission_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `can_view` tinyint(1) DEFAULT '0',
  `can_create` tinyint(1) DEFAULT '0',
  `can_edit` tinyint(1) DEFAULT '0',
  `can_delete` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `unique_user_permission` (`user_id`,`permission_key`),
  KEY `user_id` (`user_id`),
  KEY `permission_key` (`permission_key`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subadmin_permissions_tbl`
--

INSERT INTO `subadmin_permissions_tbl` (`permission_id`, `user_id`, `permission_key`, `permission_name`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`, `updated_at`) VALUES
(1, 6, 'file_management', 'File Management', 1, 1, 0, 0, '2025-11-20 00:52:38', '2025-11-20 00:52:38'),
(2, 6, 'user_management', 'User Management', 1, 0, 0, 0, '2025-11-20 00:52:38', '2025-11-20 00:52:38'),
(3, 6, 'entry_module', 'Entry Module', 1, 0, 0, 0, '2025-11-20 00:52:38', '2025-11-20 00:52:38'),
(4, 6, 'task_management', 'Task Management', 1, 1, 1, 1, '2025-11-20 00:52:38', '2025-11-20 00:52:38'),
(5, 7, 'file_management', 'File Management', 1, 0, 0, 0, '2025-12-01 03:30:17', '2025-12-01 03:30:17'),
(6, 7, 'user_management', 'User Management', 1, 0, 0, 0, '2025-12-01 03:30:17', '2025-12-01 03:30:17'),
(7, 7, 'entry_module', 'Entry Module', 1, 0, 0, 0, '2025-12-01 03:30:17', '2025-12-01 03:30:17'),
(8, 7, 'task_management', 'Task Management', 1, 1, 1, 0, '2025-12-01 03:30:17', '2025-12-01 03:30:17');

-- --------------------------------------------------------

--
-- Table structure for table `subadmin_roles_tbl`
--

DROP TABLE IF EXISTS `subadmin_roles_tbl`;
CREATE TABLE IF NOT EXISTS `subadmin_roles_tbl` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `role` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `unique_user_role` (`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subadmin_roles_tbl`
--

INSERT INTO `subadmin_roles_tbl` (`role_id`, `user_id`, `role`, `created_at`, `updated_at`) VALUES
(1, 6, 'Adviser', '2025-11-20 00:52:38', '2025-11-20 00:52:38'),
(2, 7, 'Adviser', '2025-12-01 03:30:17', '2025-12-01 03:30:17');

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
  `submitted_by` int DEFAULT NULL,
  PRIMARY KEY (`task_submission_id`),
  KEY `task_id` (`task_id`),
  KEY `file_upload_id` (`file_upload_id`),
  KEY `submitted_by` (`submitted_by`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_submission_tbl`
--

INSERT INTO `task_submission_tbl` (`task_submission_id`, `task_id`, `file_upload_id`, `check_status`, `submitted_by`) VALUES
(1, 1, 3, 'Approved', 5),
(2, 2, 4, 'Approved', 5);

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
  `assigned_to` int DEFAULT NULL,
  PRIMARY KEY (`task_id`),
  KEY `task_category_id` (`task_category_id`),
  KEY `assigned_to` (`assigned_to`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_tbl`
--

INSERT INTO `task_tbl` (`task_id`, `task_category_id`, `task_title`, `task_description`, `task_deadline`, `assigned_to`) VALUES
(1, 1, 'Resolution Draft', 'Create a Resolution Draft for Health Benefits', '2025-12-05', NULL),
(2, 2, 'Amendment for School Projects', 'Create Amendments for school projects and proposals', '2025-12-12', NULL),
(8, 1, 'Sample', 'Sample', '2025-12-12', 5);

-- --------------------------------------------------------

--
-- Table structure for table `user_security_tbl`
--

DROP TABLE IF EXISTS `user_security_tbl`;
CREATE TABLE IF NOT EXISTS `user_security_tbl` (
  `security_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `question1` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `answer1` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `question2` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `answer2` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `recovery_email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`security_id`),
  UNIQUE KEY `unique_user` (`user_id`)
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
  `is_active` tinyint(1) DEFAULT '1' COMMENT '1=active, 0=deactivated',
  `deactivated_at` datetime DEFAULT NULL COMMENT 'When the user was deactivated',
  `deactivation_reason` text COLLATE utf8mb4_general_ci COMMENT 'Reason for deactivation',
  PRIMARY KEY (`user_id`),
  KEY `position_id` (`position_id`),
  KEY `profile_id` (`profile_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_tbl`
--

INSERT INTO `user_tbl` (`user_id`, `user_name`, `pass_word`, `position_id`, `profile_id`, `user_type`, `auth_token`, `is_active`, `deactivated_at`, `deactivation_reason`) VALUES
(4, 'admin', '$2y$10$zXkd20LoOz6P2v.3jBC0UeA99alQrfRjfy8SqWurKmFExXby9jo02', 3, 4, 'admin', 'b220c06e2cf8c30e71a13029ebb6d491e714346d3474975721f6a7bcbd5e4c5b', 1, NULL, NULL),
(5, 'justin.abuela', '$2y$10$e4Ae3o.eXQ7QlfQmtV9BNOMa1Nn5yrANtz.tMYGnUvxX1m1zMvVLG', 2, 5, 'student', '50eefd0e8e7e83b86065354918478b3933ef0a2db0c1459c8d0dcd76c86ec53c', 1, NULL, NULL),
(6, 'subadmin', '$2y$10$7bWzg2Z35BdwqNK2N3.t8uMNHnr0VhSuf22j2V4RYlXyPEhU2sy8K', 1, 6, 'subadmin', NULL, 1, NULL, NULL),
(7, 'rustom.caspillo', '$2y$10$KTHIEVoZBU7CIBFBuF8/z.jguBk45aqXPqeEY1QTUWZ.OKcwIC5/2', 1, 7, 'subadmin', '22a733a1079e726efc9c42ade2e50c94408c12a05eab5ef8ef3d927e92370050', 1, NULL, NULL),
(8, 'juan.perez', '$2y$10$7of4kjUtHSQ.pMdMiceVxO3Hj5nMyC.tRpawLQLsapJsiReBz1cLG', 2, 8, 'student', '4ee50ac42696756b0d902d95784769bb4c66c60a7cee205cb8722907ec2132d6', 1, NULL, NULL);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `file_category_key_tbl`
--
ALTER TABLE `file_category_key_tbl`
  ADD CONSTRAINT `file_category_key_tbl_ibfk_1` FOREIGN KEY (`file_category_id`) REFERENCES `file_category_tbl` (`file_category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `file_nlp_analysis_tbl`
--
ALTER TABLE `file_nlp_analysis_tbl`
  ADD CONSTRAINT `file_nlp_analysis_tbl_ibfk_1` FOREIGN KEY (`file_upload_id`) REFERENCES `file_upload_tbl` (`file_upload_id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `file_upload_tbl_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `file_upload_tbl_ibfk_3` FOREIGN KEY (`file_category_id`) REFERENCES `file_category_tbl` (`file_category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications_tbl`
--
ALTER TABLE `notifications_tbl`
  ADD CONSTRAINT `notifications_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `subadmin_permissions_tbl`
--
ALTER TABLE `subadmin_permissions_tbl`
  ADD CONSTRAINT `fk_subadmin_permissions_user` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `subadmin_roles_tbl`
--
ALTER TABLE `subadmin_roles_tbl`
  ADD CONSTRAINT `fk_subadmin_roles_user` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `task_submission_tbl`
--
ALTER TABLE `task_submission_tbl`
  ADD CONSTRAINT `task_submission_tbl_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `task_tbl` (`task_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `task_submission_tbl_ibfk_2` FOREIGN KEY (`file_upload_id`) REFERENCES `file_upload_tbl` (`file_upload_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `task_submission_tbl_ibfk_3` FOREIGN KEY (`submitted_by`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task_tbl`
--
ALTER TABLE `task_tbl`
  ADD CONSTRAINT `task_tbl_ibfk_1` FOREIGN KEY (`task_category_id`) REFERENCES `task_category_tbl` (`task_category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `task_tbl_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_security_tbl`
--
ALTER TABLE `user_security_tbl`
  ADD CONSTRAINT `user_security_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE;

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
