-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 13, 2026 at 07:46 AM
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
-- Database: `uasg_copy_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `category_tbl`
--

DROP TABLE IF EXISTS `category_tbl`;
CREATE TABLE IF NOT EXISTS `category_tbl` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `category_slug` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'URL-safe directory name',
  `description` text COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`),
  UNIQUE KEY `category_slug` (`category_slug`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores document categories detected by NLP (auto-created on upload)';

--
-- Dumping data for table `category_tbl`
--

INSERT INTO `category_tbl` (`category_id`, `category_name`, `category_slug`, `description`, `created_at`, `updated_at`) VALUES
(18, 'Letter', 'letter', 'Auto-generated from ML dataset upload: Training Data', '2026-05-13 15:16:41', '2026-05-13 15:16:41'),
(19, 'Resolution', 'resolution', 'Auto-generated from ML dataset upload: Training Data', '2026-05-13 15:16:41', '2026-05-13 15:16:41'),
(20, 'Activity Design', 'activity-design', 'Auto-generated from ML dataset upload: Training Data', '2026-05-13 15:16:41', '2026-05-13 15:16:41');

-- --------------------------------------------------------

--
-- Table structure for table `deleted_record_tbl`
--

DROP TABLE IF EXISTS `deleted_record_tbl`;
CREATE TABLE IF NOT EXISTS `deleted_record_tbl` (
  `delete_id` int NOT NULL AUTO_INCREMENT,
  `data_deleted` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `reason_for_deletion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `table_origin` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `datetime_deleted` datetime NOT NULL,
  PRIMARY KEY (`delete_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deleted_record_tbl`
--

INSERT INTO `deleted_record_tbl` (`delete_id`, `data_deleted`, `reason_for_deletion`, `table_origin`, `datetime_deleted`) VALUES
(1, '{\"position_id\":1,\"position\":\"Adviser\",\"access_restriction\":2}', 'Duplicate', 'position_tbl', '2026-01-08 16:39:29'),
(2, '{\"file_category_key_id\":2,\"file_category_id\":1,\"keyword\":\"motion\",\"file_category\":\"Resolutions\"}', 'Not the right keyword', 'file_category_key_tbl', '2026-01-09 00:56:17'),
(3, '{\"task_category_id\":3,\"task_category\":\"Samples1\"}', 'Just a sample category', 'task_category_tbl', '2026-01-09 08:58:10'),
(4, '{\"task_id\":\"2\",\"task_title\":\"Amendment for School Projects\",\"task_category_id\":2,\"assigned_to\":null,\"action\":\"close\",\"new_status\":\"closed\",\"reason\":\"Finished Task\",\"actioned_by\":4,\"actioned_at\":\"2026-01-09 01:00:10\"}', 'Close Task: Finished Task', 'task_tbl', '2026-01-09 09:00:10'),
(5, '{\"task_id\":\"1\",\"task_title\":\"Resolution Draft\",\"task_category_id\":1,\"assigned_to\":null,\"action\":\"close\",\"new_status\":\"closed\",\"reason\":\"Finished Task\",\"actioned_by\":4,\"actioned_at\":\"2026-01-09 01:00:18\"}', 'Close Task: Finished Task', 'task_tbl', '2026-01-09 09:00:18'),
(6, '{\"task_id\":\"8\",\"task_title\":\"Sample\",\"old_assigned_to\":5,\"new_assigned_to\":\"8\",\"new_assignee_name\":\"Juan Perez\",\"transfer_reason\":\"Could not finish in time\",\"transferred_by\":4,\"transferred_at\":\"2026-01-09 01:06:36\"}', 'Task Transfer: Could not finish in time', 'task_tbl', '2026-01-09 09:06:36'),
(7, '{\"file_category_id\":5,\"file_category\":\"Sample Category\"}', 'This is just a sample', 'file_category_tbl', '2026-01-09 11:03:47'),
(8, '', '', '', '2026-03-17 02:08:28'),
(9, '{\"position_id\":4,\"position\":\"Guest User\",\"access_restriction\":0}', 'Not included', 'position_tbl', '2026-04-18 14:59:53'),
(10, '{\"position_id\":9,\"position\":\"Sample Position\",\"access_restriction\":1}', 'Just a sample', 'position_tbl', '2026-04-18 15:00:01'),
(11, '{\"task_id\":\"9\",\"task_title\":\"Create a Resolution\",\"old_assigned_to\":5,\"new_assigned_to\":\"8\",\"new_assignee_name\":\"Juan Perez\",\"transfer_reason\":\"Overdue date\",\"transferred_by\":4,\"transferred_at\":\"2026-04-18 08:04:52\"}', 'Task Transfer: Overdue date', 'task_tbl', '2026-04-18 16:04:52');

-- --------------------------------------------------------

--
-- Table structure for table `file_nlp_analysis_tbl`
--

DROP TABLE IF EXISTS `file_nlp_analysis_tbl`;
CREATE TABLE IF NOT EXISTS `file_nlp_analysis_tbl` (
  `analysis_id` int NOT NULL AUTO_INCREMENT,
  `file_upload_id` int NOT NULL,
  `extracted_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `word_count` int DEFAULT '0',
  `suggested_category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category_confidence` decimal(5,2) DEFAULT NULL COMMENT 'Confidence score 0-100',
  `keywords` json DEFAULT NULL COMMENT 'Keywords extracted or matched',
  `entities` json DEFAULT NULL COMMENT 'Entities extracted by NLP API',
  `sentiment` json DEFAULT NULL COMMENT 'Sentiment analysis results from NLP Cloud',
  `full_analysis` json DEFAULT NULL COMMENT 'Complete analysis result from NLP service',
  `provider` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'nlpcloud' COMMENT 'NLP service provider used (openai, google, nlpcloud)',
  `processing_time_ms` int DEFAULT NULL,
  `analyzed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`analysis_id`),
  KEY `idx_file_upload` (`file_upload_id`),
  KEY `idx_suggested_category` (`suggested_category`),
  KEY `idx_analyzed_at` (`analyzed_at`),
  KEY `idx_provider` (`provider`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores NLP analysis results from various NLP service providers';

--
-- Dumping data for table `file_nlp_analysis_tbl`
--

INSERT INTO `file_nlp_analysis_tbl` (`analysis_id`, `file_upload_id`, `extracted_text`, `word_count`, `suggested_category`, `category_confidence`, `keywords`, `entities`, `sentiment`, `full_analysis`, `provider`, `processing_time_ms`, `analyzed_at`) VALUES
(1, 9, 'RESOLUTION NO. 2025-001 WHEREAS the Student Council recognizes the need for improved facilities; WHEREAS this motion has been discussed in previous meetings; THEREFORE BE IT RESOLVED that this resolution be approved by vote.', 33, 'Resolution', 80.00, '[]', '[]', 'null', '{\"category\": \"Resolution\", \"entities\": [], \"keywords\": [], \"provider\": \"NLP Cloud\", \"sentiment\": [{\"label\": \"joy\", \"score\": 0.9849731922149658}, {\"label\": \"anger\", \"score\": 0.007649314124137163}, {\"label\": \"sadness\", \"score\": 0.0026756885927170515}, {\"label\": \"fear\", \"score\": 0.002059579594060778}, {\"label\": \"love\", \"score\": 0.0018820102559402585}, {\"label\": \"surprise\", \"score\": 0.0007601609104312956}], \"confidence\": 80}', 'nlpcloud', 2485, '2026-01-19 06:38:08'),
(2, 1, 'RESOLUTION NO. 2025-001 WHEREAS the Student Council recognizes the need for improved facilities; WHEREAS this motion has been discussed in previous meetings; THEREFORE BE IT RESOLVED that this resolution be approved by vote.', 33, 'Resolution', 80.00, '[]', '[]', 'null', '\"{\\\"success\\\":true,\\\"category_tag\\\":\\\"Resolution\\\",\\\"category_score\\\":80,\\\"extracted_text\\\":\\\"RESOLUTION NO. 2025-001 WHEREAS the Student Council recognizes the need for improved facilities; WHEREAS this motion has been discussed in previous meetings; THEREFORE BE IT RESOLVED that this resolution be approved by vote.\\\",\\\"word_count\\\":33,\\\"entities\\\":[],\\\"keywords\\\":[],\\\"full_analysis\\\":{\\\"category\\\":\\\"Resolution\\\",\\\"confidence\\\":80,\\\"keywords\\\":[],\\\"entities\\\":[],\\\"sentiment\\\":[{\\\"label\\\":\\\"joy\\\",\\\"score\\\":0.9849731922149658},{\\\"label\\\":\\\"anger\\\",\\\"score\\\":0.007649314124137163},{\\\"label\\\":\\\"sadness\\\",\\\"score\\\":0.0026756885927170515},{\\\"label\\\":\\\"fear\\\",\\\"score\\\":0.0020595795940607786},{\\\"label\\\":\\\"love\\\",\\\"score\\\":0.0018820102559402585},{\\\"label\\\":\\\"surprise\\\",\\\"score\\\":0.0007601609104312956}],\\\"provider\\\":\\\"Keyword Fallback\\\",\\\"method\\\":\\\"Pattern Matching\\\"},\\\"processing_time_ms\\\":9424}\"', 'nlpcloud', 0, '2026-01-26 05:45:13'),
(3, 2, 'Republic of the Philippines Department of Education (Region ___) (Schools Division Office of __________) (Name of School) (School Address) (Contact Number / Email) Date:  __________________ Supplier / Company Name:  __________________ Address:  ________________________________ Subject: Request for Quotation / Procurement of Supplies and Equipment Dear Sir/Madam: Greetings from the Department of Education. In line with the school’s operational requirements and pursuant to existing government procurement policies and guidelines, this office intends to procure the following supplies/equipment for official use: Description of Items: The purpose of this procurement is to support school operations and ensure the efficient delivery of services to learners, teachers, and other stakeholders. In this regard, may we respectfully request your good office to submit a quotation indicating the unit price, total cost, specifications, availability, and delivery terms for the above-mentioned items on or before __________________. Your prompt response will be highly appreciated. Thank you for your continued support to our educational programs. Very truly yours, ____________________________ (Name of School Head) School Head Noted: (Property Custodian / BAC Chairperson / Authorized Personnel)', 163, 'Memorandum', 20.00, '[]', '[]', 'null', '{\"method\": \"Pattern Matching\", \"category\": \"Memorandum\", \"entities\": [], \"keywords\": [], \"provider\": \"Keyword Fallback\", \"sentiment\": [], \"confidence\": 20}', 'nlpcloud', 8271, '2026-02-24 08:24:31'),
(4, 3, 'Republic of the Philippines Department of Education (Region ___) (Schools Division Office of __________) (Name of School) (School Address) (Contact Number / Email) Date:  __________________ Supplier / Company Name:  __________________ Address:  ________________________________ Subject: Request for Quotation / Procurement of Supplies and Equipment Dear Sir/Madam: Greetings from the Department of Education. In line with the school’s operational requirements and pursuant to existing government procurement policies and guidelines, this office intends to procure the following supplies/equipment for official use: Description of Items: The purpose of this procurement is to support school operations and ensure the efficient delivery of services to learners, teachers, and other stakeholders. In this regard, may we respectfully request your good office to submit a quotation indicating the unit price, total cost, specifications, availability, and delivery terms for the above-mentioned items on or before __________________. Your prompt response will be highly appreciated. Thank you for your continued support to our educational programs. Very truly yours, ____________________________ (Name of School Head) School Head Noted: (Property Custodian / BAC Chairperson / Authorized Personnel)', 163, 'Memorandum', 20.00, '[]', '[]', 'null', '{\"method\": \"Pattern Matching\", \"category\": \"Memorandum\", \"entities\": [], \"keywords\": [], \"provider\": \"Keyword Fallback\", \"sentiment\": [], \"confidence\": 20}', 'nlpcloud', 5841, '2026-02-24 08:28:36'),
(5, 4, 'Republic of the Philippines Department of Education (Region ___) (Schools Division Office of __________) (Name of School) (School Address) (Contact Number / Email) Date:  __________________ Supplier / Company Name:  __________________ Address:  ________________________________ Subject: Request for Quotation / Procurement of Supplies and Equipment Dear Sir/Madam: Greetings from the Department of Education. In line with the school’s operational requirements and pursuant to existing government procurement policies and guidelines, this office intends to procure the following supplies/equipment for official use: Description of Items: The purpose of this procurement is to support school operations and ensure the efficient delivery of services to learners, teachers, and other stakeholders. In this regard, may we respectfully request your good office to submit a quotation indicating the unit price, total cost, specifications, availability, and delivery terms for the above-mentioned items on or before __________________. Your prompt response will be highly appreciated. Thank you for your continued support to our educational programs. Very truly yours, ____________________________ (Name of School Head) School Head Noted: (Property Custodian / BAC Chairperson / Authorized Personnel)', 163, 'Memorandum', 20.00, '[]', '[]', 'null', '{\"method\": \"Pattern Matching\", \"category\": \"Memorandum\", \"entities\": [], \"keywords\": [], \"provider\": \"Keyword Fallback\", \"sentiment\": [], \"confidence\": 20}', 'nlpcloud', 7364, '2026-02-24 08:29:28'),
(6, 5, 'Republic of the Philippines Department of Education (Region ___) (Schools Division Office of __________) (Name of School) (School Address) (Contact Number / Email) Date:  __________________ Supplier / Company Name:  __________________ Address:  ________________________________ Subject: Request for Quotation / Procurement of Supplies and Equipment Dear Sir/Madam: Greetings from the Department of Education. In line with the school’s operational requirements and pursuant to existing government procurement policies and guidelines, this office intends to procure the following supplies/equipment for official use: Description of Items: The purpose of this procurement is to support school operations and ensure the efficient delivery of services to learners, teachers, and other stakeholders. In this regard, may we respectfully request your good office to submit a quotation indicating the unit price, total cost, specifications, availability, and delivery terms for the above-mentioned items on or before __________________. Your prompt response will be highly appreciated. Thank you for your continued support to our educational programs. Very truly yours, ____________________________ (Name of School Head) School Head Noted: (Property Custodian / BAC Chairperson / Authorized Personnel)', 163, 'Memorandum', 20.00, '[]', '[]', 'null', '{\"method\": \"Pattern Matching\", \"category\": \"Memorandum\", \"entities\": [], \"keywords\": [], \"provider\": \"Keyword Fallback\", \"sentiment\": [], \"confidence\": 20}', 'nlpcloud', 6159, '2026-02-24 08:33:10'),
(7, 6, 'Republic of the Philippines Department of Education (Region ___) (Schools Division Office of __________) (Name of School) (School Address) (Contact Number / Email) Date:  __________________ Supplier / Company Name:  __________________ Address:  ________________________________ Subject: Request for Quotation / Procurement of Supplies and Equipment Dear Sir/Madam: Greetings from the Department of Education. In line with the school’s operational requirements and pursuant to existing government procurement policies and guidelines, this office intends to procure the following supplies/equipment for official use: Description of Items: The purpose of this procurement is to support school operations and ensure the efficient delivery of services to learners, teachers, and other stakeholders. In this regard, may we respectfully request your good office to submit a quotation indicating the unit price, total cost, specifications, availability, and delivery terms for the above-mentioned items on or before __________________. Your prompt response will be highly appreciated. Thank you for your continued support to our educational programs. Very truly yours, ____________________________ (Name of School Head) School Head Noted: (Property Custodian / BAC Chairperson / Authorized Personnel)', 163, 'Memorandum', 20.00, '[]', '[]', 'null', '{\"method\": \"Pattern Matching\", \"category\": \"Memorandum\", \"entities\": [], \"keywords\": [], \"provider\": \"Keyword Fallback\", \"sentiment\": [], \"confidence\": 20}', 'nlpcloud', 7494, '2026-02-24 08:42:40'),
(8, 7, 'Republic of the Philippines Department of Education (Region ___) (Schools Division Office of __________) (Name of School) (School Address) (Contact Number / Email) Date:  __________________ Supplier / Company Name:  __________________ Address:  ________________________________ Subject: Request for Quotation / Procurement of Supplies and Equipment Dear Sir/Madam: Greetings from the Department of Education. In line with the school’s operational requirements and pursuant to existing government procurement policies and guidelines, this office intends to procure the following supplies/equipment for official use: Description of Items: The purpose of this procurement is to support school operations and ensure the efficient delivery of services to learners, teachers, and other stakeholders. In this regard, may we respectfully request your good office to submit a quotation indicating the unit price, total cost, specifications, availability, and delivery terms for the above-mentioned items on or before __________________. Your prompt response will be highly appreciated. Thank you for your continued support to our educational programs. Very truly yours, ____________________________ (Name of School Head) School Head Noted: (Property Custodian / BAC Chairperson / Authorized Personnel)', 163, 'Memorandum', 20.00, '[]', '[]', 'null', '{\"method\": \"Pattern Matching\", \"category\": \"Memorandum\", \"entities\": [], \"keywords\": [], \"provider\": \"Keyword Fallback\", \"sentiment\": [], \"confidence\": 20}', 'nlpcloud', 7318, '2026-02-24 08:48:49'),
(9, 8, 'Republic of the Philippines Department of Education (Region ___) (Schools Division Office of __________) (Name of School) (School Address) (Contact Number / Email) Date:  __________________ Supplier / Company Name:  __________________ Address:  ________________________________ Subject: Request for Quotation / Procurement of Supplies and Equipment Dear Sir/Madam: Greetings from the Department of Education. In line with the school’s operational requirements and pursuant to existing government procurement policies and guidelines, this office intends to procure the following supplies/equipment for official use: Description of Items: The purpose of this procurement is to support school operations and ensure the efficient delivery of services to learners, teachers, and other stakeholders. In this regard, may we respectfully request your good office to submit a quotation indicating the unit price, total cost, specifications, availability, and delivery terms for the above-mentioned items on or before __________________. Your prompt response will be highly appreciated. Thank you for your continued support to our educational programs. Very truly yours, ____________________________ (Name of School Head) School Head Noted: (Property Custodian / BAC Chairperson / Authorized Personnel)', 163, 'Memorandum', 20.00, '[]', '[]', 'null', '{\"method\": \"Pattern Matching\", \"category\": \"Memorandum\", \"entities\": [], \"keywords\": [], \"provider\": \"Keyword Fallback\", \"sentiment\": [], \"confidence\": 20}', 'nlpcloud', 6492, '2026-02-24 08:53:27'),
(10, 9, 'WHEREAS,  the organization incurs recurring expenses for utilities, services, subscriptions, and other operational requirements necessary for its continuous and efficient  operations; WHEREAS,  there is a need to authorize the proper processing and payment of billing obligations to avoid service interruptions and  penalties; NOW, THEREFORE, BE IT RESOLVED, that the organization hereby authorizes the processing and payment of all duly verified and approved billing statements, subject to existing accounting and auditing rules and  regulations; RESOLVED FURTHER, that the authorized signatory/ ies  are empowered to review, approve, and facilitate payment of such billing obligations in accordance with established financial policies.', 98, 'Resolution', 60.00, '[]', '[]', 'null', '{\"method\": \"Pattern Matching\", \"category\": \"Resolution\", \"entities\": [], \"keywords\": [], \"provider\": \"Keyword Fallback\", \"sentiment\": [], \"confidence\": 60}', 'nlpcloud', 7248, '2026-02-24 08:54:04'),
(11, 10, 'WHEREAS,  the organization incurs recurring expenses for utilities, services, subscriptions, and other operational requirements necessary for its continuous and efficient  operations; WHEREAS,  there is a need to authorize the proper processing and payment of billing obligations to avoid service interruptions and  penalties; NOW, THEREFORE, BE IT RESOLVED, that the organization hereby authorizes the processing and payment of all duly verified and approved billing statements, subject to existing accounting and auditing rules and  regulations; RESOLVED FURTHER, that the authorized signatory/ ies  are empowered to review, approve, and facilitate payment of such billing obligations in accordance with established financial policies.', 98, 'Resolution', 60.00, '[]', '[]', 'null', '{\"method\": \"Pattern Matching\", \"category\": \"Resolution\", \"entities\": [], \"keywords\": [], \"provider\": \"Keyword Fallback\", \"sentiment\": [], \"confidence\": 60}', 'nlpcloud', 8244, '2026-02-24 08:58:22'),
(12, 11, 'WHEREAS,  the organization incurs recurring expenses for utilities, services, subscriptions, and other operational requirements necessary for its continuous and efficient  operations; WHEREAS,  there is a need to authorize the proper processing and payment of billing obligations to avoid service interruptions and  penalties; NOW, THEREFORE, BE IT RESOLVED, that the organization hereby authorizes the processing and payment of all duly verified and approved billing statements, subject to existing accounting and auditing rules and  regulations; RESOLVED FURTHER, that the authorized signatory/ ies  are empowered to review, approve, and facilitate payment of such billing obligations in accordance with established financial policies.', 98, 'Resolution', 60.00, '[]', '[]', 'null', '{\"method\": \"Pattern Matching\", \"category\": \"Resolution\", \"entities\": [], \"keywords\": [], \"provider\": \"Keyword Fallback\", \"sentiment\": [], \"confidence\": 60}', 'nlpcloud', 8908, '2026-02-24 09:00:03'),
(13, 12, 'WHEREAS,  the organization incurs recurring expenses for utilities, services, subscriptions, and other operational requirements necessary for its continuous and efficient  operations; WHEREAS,  there is a need to authorize the proper processing and payment of billing obligations to avoid service interruptions and  penalties; NOW, THEREFORE, BE IT RESOLVED, that the organization hereby authorizes the processing and payment of all duly verified and approved billing statements, subject to existing accounting and auditing rules and  regulations; RESOLVED FURTHER, that the authorized signatory/ ies  are empowered to review, approve, and facilitate payment of such billing obligations in accordance with established financial policies.', 98, 'Resolution', 60.00, '[]', '[]', 'null', '{\"method\": \"Pattern Matching\", \"category\": \"Resolution\", \"entities\": [], \"keywords\": [], \"provider\": \"Keyword Fallback\", \"sentiment\": [], \"confidence\": 60}', 'nlpcloud', 7925, '2026-02-26 03:11:50');

-- --------------------------------------------------------

--
-- Table structure for table `file_permission_tbl`
--

DROP TABLE IF EXISTS `file_permission_tbl`;
CREATE TABLE IF NOT EXISTS `file_permission_tbl` (
  `file_permission_id` int NOT NULL AUTO_INCREMENT,
  `position_id` int NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`file_permission_id`),
  KEY `position_id` (`position_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `file_upload_tbl`
--

DROP TABLE IF EXISTS `file_upload_tbl`;
CREATE TABLE IF NOT EXISTS `file_upload_tbl` (
  `file_upload_id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `category_tag` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category_score` decimal(5,2) DEFAULT NULL,
  `classification_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Method used: nlp_cloud, google_nlp, custom_ml, hybrid',
  `ml_model_id` int DEFAULT NULL COMMENT 'ML model used for classification',
  `mime_type` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `original_filename` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Original uploaded filename',
  `file_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'System-generated filename',
  `file_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `file_size` int UNSIGNED NOT NULL DEFAULT '0',
  `drive_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `datetime_uploaded` datetime NOT NULL,
  `uploaded_by` int NOT NULL,
  PRIMARY KEY (`file_upload_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `category_id` (`category_id`),
  KEY `idx_category_uploaded` (`category_id`,`datetime_uploaded`),
  KEY `idx_ml_model` (`ml_model_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(82, 'rustom.caspillo', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 1, 'Login successful', '2025-12-08 09:03:04'),
(84, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 0, 'User not found', '2026-01-08 07:39:42'),
(85, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 0, NULL, '2026-01-08 07:39:42'),
(95, 'juan.perez', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'Login successful', '2026-01-19 07:17:00'),
(98, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 0, 'User not found', '2026-01-26 05:43:12'),
(99, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 0, NULL, '2026-01-26 05:43:12'),
(100, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 0, 'User not found', '2026-01-26 05:43:13'),
(101, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 0, NULL, '2026-01-26 05:43:13'),
(102, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 0, 'User not found', '2026-01-26 05:43:13'),
(103, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 0, NULL, '2026-01-26 05:43:13'),
(104, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 0, 'Account locked', '2026-01-26 05:43:16'),
(111, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, 'User not found', '2026-02-24 08:11:03'),
(112, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, NULL, '2026-02-24 08:11:03'),
(113, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, 'User not found', '2026-02-24 08:11:09'),
(114, 'admin123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, NULL, '2026-02-24 08:11:09'),
(116, 'userstaff123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, 'Login successful', '2026-02-24 08:14:06'),
(141, 'justin.abuela', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'Login successful', '2026-04-18 08:05:14'),
(155, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 1, 'Login successful', '2026-05-13 07:10:37');

-- --------------------------------------------------------

--
-- Table structure for table `ml_incremental_training_queue_tbl`
--

DROP TABLE IF EXISTS `ml_incremental_training_queue_tbl`;
CREATE TABLE IF NOT EXISTS `ml_incremental_training_queue_tbl` (
  `queue_id` int NOT NULL AUTO_INCREMENT,
  `file_upload_id` int DEFAULT NULL COMMENT 'Reference to uploaded file',
  `training_text` text COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Extracted text for training',
  `confirmed_category` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'User-confirmed category',
  `prediction_confidence` decimal(5,4) DEFAULT NULL COMMENT 'Original ML confidence (0-1)',
  `status` enum('pending','processed','failed') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `processed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`queue_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_file_upload` (`file_upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Queues training samples for incremental ML learning';

-- --------------------------------------------------------

--
-- Table structure for table `ml_models_tbl`
--

DROP TABLE IF EXISTS `ml_models_tbl`;
CREATE TABLE IF NOT EXISTS `ml_models_tbl` (
  `model_id` int NOT NULL AUTO_INCREMENT,
  `model_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `dataset_id` int NOT NULL,
  `model_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'tfidf_svm' COMMENT 'Algorithm: tfidf_svm, tfidf_nb, tfidf_rf',
  `model_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Path to saved .pkl model file',
  `vectorizer_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Path to TF-IDF vectorizer .pkl file',
  `accuracy_score` decimal(5,2) DEFAULT NULL COMMENT 'Training accuracy percentage',
  `precision_score` decimal(5,2) DEFAULT NULL COMMENT 'Precision score',
  `recall_score` decimal(5,2) DEFAULT NULL COMMENT 'Recall score',
  `f1_score` decimal(5,2) DEFAULT NULL COMMENT 'F1 score',
  `categories` json DEFAULT NULL COMMENT 'List of categories the model can predict',
  `training_samples` int DEFAULT '0' COMMENT 'Number of samples used for training',
  `test_samples` int DEFAULT '0' COMMENT 'Number of samples used for testing',
  `is_active` tinyint(1) DEFAULT '0' COMMENT '1=active model, 0=inactive',
  `trained_by` int NOT NULL,
  `trained_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_used_at` datetime DEFAULT NULL,
  `usage_count` int DEFAULT '0' COMMENT 'Number of times model was used for prediction',
  `is_incremental` tinyint(1) DEFAULT '0' COMMENT '1 if model supports incremental learning',
  `total_incremental_samples` int DEFAULT '0' COMMENT 'Total samples learned incrementally',
  `last_incremental_update` datetime DEFAULT NULL COMMENT 'Last time model was updated incrementally',
  PRIMARY KEY (`model_id`),
  KEY `dataset_id` (`dataset_id`),
  KEY `trained_by` (`trained_by`),
  KEY `idx_active` (`is_active`),
  KEY `idx_trained_at` (`trained_at`),
  KEY `idx_is_incremental` (`is_incremental`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores trained ML classification models';

--
-- Dumping data for table `ml_models_tbl`
--

INSERT INTO `ml_models_tbl` (`model_id`, `model_name`, `dataset_id`, `model_type`, `model_path`, `vectorizer_path`, `accuracy_score`, `precision_score`, `recall_score`, `f1_score`, `categories`, `training_samples`, `test_samples`, `is_active`, `trained_by`, `trained_at`, `last_used_at`, `usage_count`, `is_incremental`, `total_incremental_samples`, `last_incremental_update`) VALUES
(14, 'Initial Training Data Set', 15, 'svm', 'uploads/ml_models/model_6a04285949d75_1778657369.pkl', 'uploads/ml_models/model_6a04285949d75_1778657369_vectorizer.pkl', 1.00, 1.00, 1.00, 1.00, '[\"Letter\", \"Resolution\", \"Activity Design\"]', 51, 13, 1, 4, '2026-05-13 15:29:33', '2026-05-13 15:40:46', 8, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ml_prediction_history_tbl`
--

DROP TABLE IF EXISTS `ml_prediction_history_tbl`;
CREATE TABLE IF NOT EXISTS `ml_prediction_history_tbl` (
  `prediction_id` int NOT NULL AUTO_INCREMENT,
  `model_id` int NOT NULL,
  `file_upload_id` int DEFAULT NULL,
  `input_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'Text that was classified',
  `predicted_category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `prediction_time_ms` int DEFAULT NULL COMMENT 'Prediction time in milliseconds',
  `was_accepted` tinyint(1) DEFAULT NULL COMMENT '1=accepted, 0=rejected/changed by user',
  `actual_category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Final category chosen by user',
  `predicted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`prediction_id`),
  KEY `model_id` (`model_id`),
  KEY `file_upload_id` (`file_upload_id`),
  KEY `idx_predicted_at` (`predicted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks ML predictions for analytics and model improvement';

-- --------------------------------------------------------

--
-- Table structure for table `ml_settings_tbl`
--

DROP TABLE IF EXISTS `ml_settings_tbl`;
CREATE TABLE IF NOT EXISTS `ml_settings_tbl` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `setting_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `updated_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ML system configuration settings';

--
-- Dumping data for table `ml_settings_tbl`
--

INSERT INTO `ml_settings_tbl` (`setting_id`, `setting_key`, `setting_value`, `setting_description`, `updated_by`, `updated_at`) VALUES
(1, 'classification_method', 'custom_ml', 'Classification method: nlp_cloud, google_nlp, custom_ml, hybrid', 4, '2026-05-13 15:26:11'),
(2, 'active_ml_model_id', '14', 'ID of currently active ML model (NULL = no active model)', NULL, '2026-05-13 15:29:33'),
(3, 'ml_confidence_threshold', '30', 'Minimum confidence percentage to auto-categorize with ML', 4, '2026-05-13 15:26:11'),
(4, 'ml_fallback_enabled', '1', 'Enable fallback to NLP services if ML fails (1=yes, 0=no)', NULL, '2026-01-28 13:21:35'),
(5, 'ml_priority', '1', 'Try ML before NLP services in hybrid mode (1=yes, 0=no)', NULL, '2026-01-28 13:21:35'),
(6, 'incremental_learning_enabled', '0', 'Enable automatic incremental learning from uploaded files', NULL, '2026-02-08 15:29:00'),
(7, 'incremental_batch_size', '10', 'Number of samples to accumulate before incremental training', NULL, '2026-02-08 15:29:00'),
(8, 'incremental_auto_process', '1', 'Automatically process training queue when batch size is reached', NULL, '2026-02-08 15:29:00');

-- --------------------------------------------------------

--
-- Table structure for table `ml_training_datasets_tbl`
--

DROP TABLE IF EXISTS `ml_training_datasets_tbl`;
CREATE TABLE IF NOT EXISTS `ml_training_datasets_tbl` (
  `dataset_id` int NOT NULL AUTO_INCREMENT,
  `dataset_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Path to uploaded CSV file',
  `total_samples` int DEFAULT '0' COMMENT 'Number of training samples',
  `categories_count` int DEFAULT '0' COMMENT 'Number of unique categories',
  `categories` json DEFAULT NULL COMMENT 'List of category names in dataset',
  `uploaded_by` int NOT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`dataset_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores ML training datasets uploaded via CSV';

--
-- Dumping data for table `ml_training_datasets_tbl`
--

INSERT INTO `ml_training_datasets_tbl` (`dataset_id`, `dataset_name`, `file_path`, `total_samples`, `categories_count`, `categories`, `uploaded_by`, `uploaded_at`, `is_active`, `description`) VALUES
(15, 'CSV SET', 'uploads/ml_datasets/dataset_6a04277e097bf7.58478015_1778657150.csv', 64, 3, '[\"Letter\", \"Resolution\", \"Activity Design\"]', 4, '2026-05-13 15:25:50', 1, '');

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications_tbl`
--

INSERT INTO `notifications_tbl` (`notification_id`, `user_id`, `type`, `title`, `message`, `related_id`, `is_read`, `datetime_created`) VALUES
(1, 5, 'new_task', 'New Task Assigned', 'New task \'Resolution for Health Benefits\' has been assigned to you', 1, 1, '2025-11-19 17:02:03'),
(2, 5, 'new_task', 'New Task Assigned', 'New task \'Resolution Draft\' has been assigned to you', 5, 1, '2025-11-27 16:45:33'),
(3, 5, 'new_task', 'New Task Assigned', 'New task \'Amendment for School Projects\' has been assigned to you', 6, 1, '2025-12-07 11:58:21'),
(4, 5, 'new_task', 'New Task Assigned', 'New task \'Resolution for Donations\' has been assigned to you', 7, 1, '2025-12-07 12:08:52'),
(5, 5, 'new_task', 'New Task Assigned', 'New task \'Sample\' has been assigned to you', 8, 1, '2025-12-07 12:12:09'),
(6, 5, 'new_task', 'New Task Assigned', 'New task \'Create a Resolution\' has been assigned to you', 9, 0, '2026-01-09 08:59:53'),
(7, 8, 'new_task', 'New Task Assigned', 'New task \'Resolution for Youth\' has been assigned to you', 10, 0, '2026-01-19 15:16:23'),
(8, 5, 'new_task', 'New Task Assigned', 'New task \'Create a Resolution for Billing\' has been assigned to you', 11, 0, '2026-02-24 16:20:03'),
(9, 5, 'task_transferred', 'Task Transferred Away', 'Task \'Create a Resolution\' has been transferred to Juan Perez. Reason: Overdue date', 9, 0, '2026-04-18 16:04:52'),
(10, 8, 'task_transferred', 'Task Transferred to You', 'Task \'Create a Resolution\' has been transferred to you. Reason: Overdue date', 9, 0, '2026-04-18 16:04:52');

-- --------------------------------------------------------

--
-- Table structure for table `position_tbl`
--

DROP TABLE IF EXISTS `position_tbl`;
CREATE TABLE IF NOT EXISTS `position_tbl` (
  `position_id` int NOT NULL AUTO_INCREMENT,
  `position` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `access_restriction` int DEFAULT '1',
  PRIMARY KEY (`position_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `position_tbl`
--

INSERT INTO `position_tbl` (`position_id`, `position`, `access_restriction`) VALUES
(2, 'Student Government Member', 1),
(3, 'System Administrator', 3),
(5, 'Adviser', 2),
(6, 'President', 2),
(7, 'Vice-President', 2),
(8, 'Secretary', 2),
(10, 'System Staff', 2);

-- --------------------------------------------------------

--
-- Table structure for table `profile_tbl`
--

DROP TABLE IF EXISTS `profile_tbl`;
CREATE TABLE IF NOT EXISTS `profile_tbl` (
  `profile_id` int NOT NULL AUTO_INCREMENT,
  `fname` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mname` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lname` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `auxname` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gender` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `birthdate` date NOT NULL,
  `contact_number` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`profile_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profile_tbl`
--

INSERT INTO `profile_tbl` (`profile_id`, `fname`, `mname`, `lname`, `auxname`, `gender`, `birthdate`, `contact_number`, `email`) VALUES
(4, 'System', '', 'Administrator', '', 'Not specified', '1990-01-01', '', 'admin@system.local'),
(5, 'Justin', 'Arnaez', 'Abuela', '', 'Male', '1991-11-05', '09812682658', 'justinabuela@gmail.com'),
(6, 'Mika', 'Jay', 'Esparagoza', '', 'Female', '1996-07-12', '', 'mika.esparagoza@gmail.com'),
(7, 'Rustom', 'Pelaez', 'Caspillo', '', 'Male', '1987-07-22', '', 'rustom.caspillo@gmail.com'),
(8, 'Juan', 'Delos Santos', 'Perez', '', 'Male', '1994-05-11', '', 'juan.perez@gmail.com'),
(11, 'Juan', 'Santos', 'Perez', '', 'Male', '1990-11-11', '', 'userstaff@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `subadmin_activity_log_tbl`
--

DROP TABLE IF EXISTS `subadmin_activity_log_tbl`;
CREATE TABLE IF NOT EXISTS `subadmin_activity_log_tbl` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `related_id` int DEFAULT NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
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
  `permission_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `permission_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subadmin_permissions_tbl`
--

INSERT INTO `subadmin_permissions_tbl` (`permission_id`, `user_id`, `permission_key`, `permission_name`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`, `updated_at`) VALUES
(13, 11, 'file_management', 'File Management', 1, 1, 0, 0, '2026-02-24 08:13:51', '2026-02-24 08:13:51'),
(14, 11, 'user_management', 'User Management', 1, 0, 0, 0, '2026-02-24 08:13:51', '2026-02-24 08:13:51'),
(15, 11, 'entry_module', 'Entry Module', 1, 0, 0, 0, '2026-02-24 08:13:51', '2026-02-24 08:13:51'),
(16, 11, 'task_management', 'Task Management', 1, 1, 0, 0, '2026-02-24 08:13:51', '2026-02-24 08:13:51');

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subadmin_roles_tbl`
--

INSERT INTO `subadmin_roles_tbl` (`role_id`, `user_id`, `role`, `created_at`, `updated_at`) VALUES
(3, 11, 'Adviser', '2026-01-26 06:08:30', '2026-01-26 06:08:30');

-- --------------------------------------------------------

--
-- Table structure for table `task_category_tbl`
--

DROP TABLE IF EXISTS `task_category_tbl`;
CREATE TABLE IF NOT EXISTS `task_category_tbl` (
  `task_category_id` int NOT NULL AUTO_INCREMENT,
  `task_category` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`task_category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_category_tbl`
--

INSERT INTO `task_category_tbl` (`task_category_id`, `task_category`) VALUES
(1, 'Assignment'),
(2, 'Project'),
(4, 'Sample Task Category');

-- --------------------------------------------------------

--
-- Table structure for table `task_submission_tbl`
--

DROP TABLE IF EXISTS `task_submission_tbl`;
CREATE TABLE IF NOT EXISTS `task_submission_tbl` (
  `task_submission_id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `file_upload_id` int NOT NULL,
  `check_status` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `submitted_by` int DEFAULT NULL,
  PRIMARY KEY (`task_submission_id`),
  KEY `task_id` (`task_id`),
  KEY `file_upload_id` (`file_upload_id`),
  KEY `submitted_by` (`submitted_by`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_tbl`
--

DROP TABLE IF EXISTS `task_tbl`;
CREATE TABLE IF NOT EXISTS `task_tbl` (
  `task_id` int NOT NULL AUTO_INCREMENT,
  `task_category_id` int NOT NULL,
  `task_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `task_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `task_deadline` date NOT NULL,
  `assigned_to` int DEFAULT NULL,
  `task_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  PRIMARY KEY (`task_id`),
  KEY `task_category_id` (`task_category_id`),
  KEY `assigned_to` (`assigned_to`),
  KEY `idx_task_status` (`task_status`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_tbl`
--

INSERT INTO `task_tbl` (`task_id`, `task_category_id`, `task_title`, `task_description`, `task_deadline`, `assigned_to`, `task_status`) VALUES
(1, 1, 'Resolution Draft', 'Create a Resolution Draft for Health Benefits', '2025-12-05', NULL, 'closed'),
(2, 2, 'Amendment for School Projects', 'Create Amendments for school projects and proposals', '2025-12-12', NULL, 'closed'),
(9, 1, 'Create a Resolution', 'Create a resolution for the minutes yesterday', '2026-01-12', 8, 'transferred'),
(10, 1, 'Resolution for Youth', 'Give me a Resolution for Yesterday\'s Youth', '2026-01-30', 8, 'active'),
(11, 1, 'Create a Resolution for Billing', 'Please create a resolution for billing statements in the office', '2026-03-14', 5, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `user_security_tbl`
--

DROP TABLE IF EXISTS `user_security_tbl`;
CREATE TABLE IF NOT EXISTS `user_security_tbl` (
  `security_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `question1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `answer1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `question2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `answer2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `recovery_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
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
  `user_name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pass_word` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `position_id` int NOT NULL,
  `profile_id` int NOT NULL,
  `user_type` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `auth_token` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1' COMMENT '1=active, 0=deactivated',
  `deactivated_at` datetime DEFAULT NULL COMMENT 'When the user was deactivated',
  `deactivation_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'Reason for deactivation',
  PRIMARY KEY (`user_id`),
  KEY `position_id` (`position_id`),
  KEY `profile_id` (`profile_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_tbl`
--

INSERT INTO `user_tbl` (`user_id`, `user_name`, `pass_word`, `position_id`, `profile_id`, `user_type`, `auth_token`, `is_active`, `deactivated_at`, `deactivation_reason`) VALUES
(4, 'admin', '$2y$10$zXkd20LoOz6P2v.3jBC0UeA99alQrfRjfy8SqWurKmFExXby9jo02', 3, 4, 'admin', 'ff5b9205ec79d740847eabcb2dc27c75d32b024286fb833be5952555a2d6dd18', 1, NULL, NULL),
(5, 'justin.abuela', '$2y$10$TkAEVG9u9K7XiGEkoMivdOfAQGrOZLeHezEgk2YkGKRnRODZU.MzS', 2, 5, 'student', '47e8952725ada44b265690cf4615287b6ebc44e9ace5ad167f8d240e52e73a17', 1, NULL, NULL),
(8, 'juan.perez', '$2y$10$GMFRwhedcv2cNjcT6/jTLuReP3De0NjI393u8JqtfEj4mi1hNTkEa', 2, 8, 'student', '4df74ecefda40d16f25d9cc0f5485eb7e753a49348018dad48da6ad2156bb7f0', 1, NULL, NULL),
(11, 'userstaff123', '$2y$10$IvGGgxzVPujgYlBvC4NIsOTCkFl1TzeKUOCEoELBt876iGbfPMJjy', 5, 11, 'subadmin', '8455f9ebc7660893181179da8a62da974313473c8239232805da2b26727ebce0', 1, NULL, NULL);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `file_permission_tbl`
--
ALTER TABLE `file_permission_tbl`
  ADD CONSTRAINT `file_permission_tbl_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `position_tbl` (`position_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `file_permission_tbl_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category_tbl` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `file_upload_tbl`
--
ALTER TABLE `file_upload_tbl`
  ADD CONSTRAINT `file_upload_tbl_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_file_category` FOREIGN KEY (`category_id`) REFERENCES `category_tbl` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `ml_models_tbl`
--
ALTER TABLE `ml_models_tbl`
  ADD CONSTRAINT `fk_model_dataset` FOREIGN KEY (`dataset_id`) REFERENCES `ml_training_datasets_tbl` (`dataset_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_model_trainer` FOREIGN KEY (`trained_by`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ml_prediction_history_tbl`
--
ALTER TABLE `ml_prediction_history_tbl`
  ADD CONSTRAINT `fk_prediction_file` FOREIGN KEY (`file_upload_id`) REFERENCES `file_upload_tbl` (`file_upload_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_prediction_model` FOREIGN KEY (`model_id`) REFERENCES `ml_models_tbl` (`model_id`) ON DELETE CASCADE;

--
-- Constraints for table `ml_settings_tbl`
--
ALTER TABLE `ml_settings_tbl`
  ADD CONSTRAINT `fk_ml_settings_user` FOREIGN KEY (`updated_by`) REFERENCES `user_tbl` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `ml_training_datasets_tbl`
--
ALTER TABLE `ml_training_datasets_tbl`
  ADD CONSTRAINT `fk_dataset_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
