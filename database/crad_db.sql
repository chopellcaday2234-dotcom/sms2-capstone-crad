-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 22, 2026 at 11:30 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crad_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_terms`
--

CREATE TABLE `academic_terms` (
  `id` int(10) UNSIGNED NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` enum('1st Semester','2nd Semester','Summer') NOT NULL,
  `term_code` varchar(40) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('Draft','Active','Closed') NOT NULL DEFAULT 'Draft',
  `created_by_user` int(10) UNSIGNED DEFAULT NULL,
  `closed_by_user` int(10) UNSIGNED DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chapter_evaluations`
--

CREATE TABLE `chapter_evaluations` (
  `id` int(10) UNSIGNED NOT NULL,
  `submission_id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `evaluator_user_id` int(10) UNSIGNED NOT NULL,
  `evaluator_name` varchar(150) NOT NULL DEFAULT '',
  `content_score` decimal(5,2) NOT NULL,
  `methodology_score` decimal(5,2) NOT NULL,
  `references_score` decimal(5,2) NOT NULL,
  `format_score` decimal(5,2) NOT NULL,
  `content_remarks` text DEFAULT NULL,
  `methodology_remarks` text DEFAULT NULL,
  `references_remarks` text DEFAULT NULL,
  `format_remarks` text DEFAULT NULL,
  `overall_feedback` text DEFAULT NULL,
  `result` enum('APPROVED','APPROVED WITH REVISION') NOT NULL,
  `overall_score` decimal(5,2) DEFAULT NULL,
  `evaluated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chapter_evaluations`
--

INSERT INTO `chapter_evaluations` (`id`, `submission_id`, `research_group_id`, `evaluator_user_id`, `evaluator_name`, `content_score`, `methodology_score`, `references_score`, `format_score`, `content_remarks`, `methodology_remarks`, `references_remarks`, `format_remarks`, `overall_feedback`, `result`, `overall_score`, `evaluated_at`, `created_at`) VALUES
(1, 3, 32, 475, 'Grammarian', 65.00, 65.00, 65.00, 65.00, '', '', '', '', 'asdasd', 'APPROVED WITH REVISION', 65.00, '2026-08-14 11:36:16', '2026-08-14 11:36:16'),
(2, 5, 33, 475, 'Grammarian', 100.00, 100.00, 100.00, 99.97, '', '', '', '', '', 'APPROVED', 99.99, '2026-08-14 12:16:27', '2026-08-14 12:16:27'),
(3, 6, 33, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', 'sada', 'APPROVED', 100.00, '2026-08-14 12:16:37', '2026-08-14 12:16:37'),
(4, 8, 35, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', '', 'APPROVED', 100.00, '2026-08-14 12:48:05', '2026-08-14 12:48:05'),
(5, 9, 35, 475, 'Grammarian', 100.00, 100.00, 100.00, 99.98, '', '', '', '', '', 'APPROVED', 100.00, '2026-08-14 12:48:15', '2026-08-14 12:48:15'),
(6, 10, 35, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', 'qdas', 'APPROVED', 100.00, '2026-08-14 12:48:26', '2026-08-14 12:48:26'),
(7, 11, 37, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', 'adasdasdas', 'APPROVED', 100.00, '2026-08-14 13:46:02', '2026-08-14 13:46:02'),
(8, 12, 37, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', 'asdasdas', 'APPROVED', 100.00, '2026-08-14 13:46:13', '2026-08-14 13:46:13'),
(9, 13, 37, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', 'asdasdas', 'APPROVED', 100.00, '2026-08-14 13:46:22', '2026-08-14 13:46:22'),
(10, 14, 49, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', '', 'APPROVED', 100.00, '2026-08-14 16:42:19', '2026-08-14 16:42:19'),
(11, 15, 50, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', '100', 'APPROVED', 100.00, '2026-08-14 17:36:32', '2026-08-14 17:36:32'),
(12, 16, 51, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', 'dasdas', 'APPROVED', 100.00, '2026-08-14 21:51:29', '2026-08-14 21:51:29'),
(13, 17, 52, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', '', 'APPROVED', 100.00, '2026-08-15 16:48:46', '2026-08-15 16:48:46'),
(14, 18, 52, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', '', 'APPROVED', 100.00, '2026-08-15 16:48:55', '2026-08-15 16:48:55'),
(15, 19, 52, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', '', 'APPROVED', 100.00, '2026-08-15 16:49:04', '2026-08-15 16:49:04'),
(16, 20, 53, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', '', 'APPROVED', 100.00, '2026-08-15 22:50:09', '2026-08-15 22:50:09'),
(17, 21, 53, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', '', 'APPROVED', 100.00, '2026-08-15 22:50:18', '2026-08-15 22:50:18'),
(18, 22, 53, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', '', 'APPROVED', 100.00, '2026-08-15 22:50:27', '2026-08-15 22:50:27'),
(19, 23, 54, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', '', 'APPROVED', 100.00, '2026-08-16 15:01:21', '2026-08-16 15:01:21'),
(20, 24, 54, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', '', 'APPROVED', 100.00, '2026-08-16 15:01:31', '2026-08-16 15:01:31'),
(21, 25, 54, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', '', 'APPROVED', 100.00, '2026-08-16 15:01:40', '2026-08-16 15:01:40'),
(22, 26, 57, 475, 'Grammarian', 100.00, 100.00, 100.00, 99.96, '', '', '', '', '', 'APPROVED', 99.99, '2026-08-16 21:43:06', '2026-08-16 21:43:06'),
(23, 27, 57, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', '', 'APPROVED', 100.00, '2026-08-16 21:43:15', '2026-08-16 21:43:15'),
(24, 28, 57, 475, 'Grammarian', 100.00, 100.00, 100.00, 100.00, '', '', '', '', '', 'APPROVED', 100.00, '2026-08-16 21:43:25', '2026-08-16 21:43:25'),
(25, 29, 58, 729, 'Grammarian', 98.00, 98.00, 98.00, 98.00, '231', '123123', '92', '98', '23123123123', 'APPROVED', 98.00, '2026-08-21 15:56:34', '2026-08-21 15:56:34'),
(26, 30, 58, 729, 'Grammarian', 99.00, 99.00, 99.00, 99.00, '99', '99', '99', '99', '99', 'APPROVED', 99.00, '2026-08-21 16:31:44', '2026-08-21 16:31:44'),
(27, 31, 58, 729, 'Grammarian', 99.00, 99.00, 99.00, 99.00, '99', '99', '99', '99', '99', 'APPROVED', 99.00, '2026-08-21 16:32:07', '2026-08-21 16:32:07');

-- --------------------------------------------------------

--
-- Table structure for table `chapter_evaluation_notifications`
--

CREATE TABLE `chapter_evaluation_notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `event_key` varchar(120) NOT NULL,
  `recipient_user_id` int(10) UNSIGNED DEFAULT NULL,
  `recipient_role` varchar(60) NOT NULL DEFAULT '',
  `recipient_email` varchar(190) NOT NULL DEFAULT '',
  `submission_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(60) NOT NULL,
  `title` varchar(180) NOT NULL,
  `body` text NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chapter_evaluation_notifications`
--

INSERT INTO `chapter_evaluation_notifications` (`id`, `event_key`, `recipient_user_id`, `recipient_role`, `recipient_email`, `submission_id`, `type`, `title`, `body`, `url`, `is_read`, `created_at`) VALUES
(1, 'evaluator:new:1:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 1, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 1 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=1', 0, '2026-08-14 11:24:27'),
(2, 'evaluator:new:2:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 2, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 2 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=2', 0, '2026-08-14 11:24:31'),
(3, 'evaluator:new:3:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 3, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 3 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=3', 1, '2026-08-14 11:24:36'),
(4, 'student:under_review:1', 9, 'student', 's230000001@bestlink.edu.ph', 1, 'under_review', 'Chapter 1 is under review', 'Chapter 1 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 11:24:57'),
(5, 'student:under_review:3', 9, 'student', 's230000001@bestlink.edu.ph', 3, 'under_review', 'Chapter 3 is under review', 'Chapter 3 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 11:36:01'),
(6, 'student:needs_revision:3', 9, 'student', 's230000001@bestlink.edu.ph', 3, 'needs_revision', 'Chapter 3 needs revision', 'Chapter 3 Version 1 is now Needs Revision.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 1, '2026-08-14 11:36:16'),
(7, 'evaluator:new:4:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 4, 'new_submission', 'Revised Chapter Submitted', 'Group 01 submitted Chapter 3 Version 2 for re-evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=4', 1, '2026-08-14 11:36:41'),
(8, 'evaluator:new:5:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 5, 'new_submission', 'New Chapter Submission', 'Group 33 submitted Chapter 1 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=5', 1, '2026-08-14 12:07:57'),
(9, 'evaluator:new:6:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 6, 'new_submission', 'New Chapter Submission', 'Group 33 submitted Chapter 2 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=6', 0, '2026-08-14 12:08:00'),
(10, 'evaluator:new:7:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 7, 'new_submission', 'New Chapter Submission', 'Group 33 submitted Chapter 3 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=7', 1, '2026-08-14 12:08:03'),
(11, 'student:under_review:5', 9, 'student', 's230000001@bestlink.edu.ph', 5, 'under_review', 'Chapter 1 is under review', 'Chapter 1 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 12:16:20'),
(12, 'student:accepted:5', 9, 'student', 's230000001@bestlink.edu.ph', 5, 'accepted', 'Chapter 1 accepted', 'Chapter 1 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 12:16:27'),
(13, 'student:under_review:6', 9, 'student', 's230000001@bestlink.edu.ph', 6, 'under_review', 'Chapter 2 is under review', 'Chapter 2 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 12:16:32'),
(14, 'student:accepted:6', 9, 'student', 's230000001@bestlink.edu.ph', 6, 'accepted', 'Chapter 2 accepted', 'Chapter 2 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 12:16:37'),
(15, 'evaluator:new:8:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 8, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 1 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=8', 0, '2026-08-14 12:46:13'),
(16, 'evaluator:new:9:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 9, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 3 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=9', 0, '2026-08-14 12:46:17'),
(17, 'evaluator:new:10:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 10, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 2 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=10', 0, '2026-08-14 12:46:20'),
(18, 'student:under_review:8', 9, 'student', 's230000001@bestlink.edu.ph', 8, 'under_review', 'Chapter 1 is under review', 'Chapter 1 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 12:47:57'),
(19, 'student:accepted:8', 9, 'student', 's230000001@bestlink.edu.ph', 8, 'accepted', 'Chapter 1 accepted', 'Chapter 1 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 12:48:05'),
(20, 'student:under_review:9', 9, 'student', 's230000001@bestlink.edu.ph', 9, 'under_review', 'Chapter 3 is under review', 'Chapter 3 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 12:48:09'),
(21, 'student:accepted:9', 9, 'student', 's230000001@bestlink.edu.ph', 9, 'accepted', 'Chapter 3 accepted', 'Chapter 3 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 12:48:15'),
(22, 'student:under_review:10', 9, 'student', 's230000001@bestlink.edu.ph', 10, 'under_review', 'Chapter 2 is under review', 'Chapter 2 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 12:48:20'),
(23, 'student:accepted:10', 9, 'student', 's230000001@bestlink.edu.ph', 10, 'accepted', 'Chapter 2 accepted', 'Chapter 2 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 12:48:26'),
(24, 'evaluator:new:11:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 11, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 1 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=11', 0, '2026-08-14 13:23:21'),
(25, 'evaluator:new:12:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 12, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 2 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=12', 0, '2026-08-14 13:23:25'),
(26, 'evaluator:new:13:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 13, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 3 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=13', 0, '2026-08-14 13:23:28'),
(27, 'student:under_review:11', 9, 'student', 's230000001@bestlink.edu.ph', 11, 'under_review', 'Chapter 1 is under review', 'Chapter 1 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 13:45:53'),
(28, 'student:accepted:11', 9, 'student', 's230000001@bestlink.edu.ph', 11, 'accepted', 'Chapter 1 accepted', 'Chapter 1 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 13:46:02'),
(29, 'student:under_review:12', 9, 'student', 's230000001@bestlink.edu.ph', 12, 'under_review', 'Chapter 2 is under review', 'Chapter 2 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 13:46:08'),
(30, 'student:accepted:12', 9, 'student', 's230000001@bestlink.edu.ph', 12, 'accepted', 'Chapter 2 accepted', 'Chapter 2 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 13:46:13'),
(31, 'student:under_review:13', 9, 'student', 's230000001@bestlink.edu.ph', 13, 'under_review', 'Chapter 3 is under review', 'Chapter 3 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 13:46:16'),
(32, 'student:accepted:13', 9, 'student', 's230000001@bestlink.edu.ph', 13, 'accepted', 'Chapter 3 accepted', 'Chapter 3 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 13:46:22'),
(33, 'evaluator:new:14:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 14, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 1 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=14', 0, '2026-08-14 16:40:44'),
(34, 'student:under_review:14', 9, 'student', 's230000001@bestlink.edu.ph', 14, 'under_review', 'Chapter 1 is under review', 'Chapter 1 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 16:42:06'),
(35, 'student:accepted:14', 9, 'student', 's230000001@bestlink.edu.ph', 14, 'accepted', 'Chapter 1 accepted', 'Chapter 1 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 1, '2026-08-14 16:42:19'),
(36, 'evaluator:new:15:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 15, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 1 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=15', 0, '2026-08-14 17:36:01'),
(37, 'student:under_review:15', 9, 'student', 's230000001@bestlink.edu.ph', 15, 'under_review', 'Chapter 1 is under review', 'Chapter 1 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 17:36:17'),
(38, 'student:accepted:15', 9, 'student', 's230000001@bestlink.edu.ph', 15, 'accepted', 'Chapter 1 accepted', 'Chapter 1 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 1, '2026-08-14 17:36:32'),
(39, 'evaluator:new:16:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 16, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 1 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=16', 0, '2026-08-14 21:50:29'),
(40, 'student:under_review:16', 9, 'student', 's230000001@bestlink.edu.ph', 16, 'under_review', 'Chapter 1 is under review', 'Chapter 1 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 21:51:10'),
(41, 'student:accepted:16', 9, 'student', 's230000001@bestlink.edu.ph', 16, 'accepted', 'Chapter 1 accepted', 'Chapter 1 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-14 21:51:29'),
(42, 'evaluator:new:17:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 17, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 1 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=17', 1, '2026-08-15 16:43:36'),
(43, 'evaluator:new:18:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 18, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 2 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=18', 1, '2026-08-15 16:43:40'),
(44, 'evaluator:new:19:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 19, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 3 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=19', 1, '2026-08-15 16:43:43'),
(45, 'student:under_review:17', 9, 'student', 's230000001@bestlink.edu.ph', 17, 'under_review', 'Chapter 1 is under review', 'Chapter 1 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-15 16:48:40'),
(46, 'student:accepted:17', 9, 'student', 's230000001@bestlink.edu.ph', 17, 'accepted', 'Chapter 1 accepted', 'Chapter 1 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-15 16:48:46'),
(47, 'student:under_review:18', 9, 'student', 's230000001@bestlink.edu.ph', 18, 'under_review', 'Chapter 2 is under review', 'Chapter 2 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-15 16:48:51'),
(48, 'student:accepted:18', 9, 'student', 's230000001@bestlink.edu.ph', 18, 'accepted', 'Chapter 2 accepted', 'Chapter 2 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-15 16:48:55'),
(49, 'student:under_review:19', 9, 'student', 's230000001@bestlink.edu.ph', 19, 'under_review', 'Chapter 3 is under review', 'Chapter 3 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-15 16:49:00'),
(50, 'student:accepted:19', 9, 'student', 's230000001@bestlink.edu.ph', 19, 'accepted', 'Chapter 3 accepted', 'Chapter 3 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-15 16:49:04'),
(51, 'evaluator:new:20:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 20, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 1 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=20', 0, '2026-08-15 22:49:29'),
(52, 'evaluator:new:21:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 21, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 2 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=21', 0, '2026-08-15 22:49:33'),
(53, 'evaluator:new:22:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 22, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 3 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=22', 0, '2026-08-15 22:49:38'),
(54, 'student:under_review:20', 9, 'student', 's230000001@bestlink.edu.ph', 20, 'under_review', 'Chapter 1 is under review', 'Chapter 1 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-15 22:50:04'),
(55, 'student:accepted:20', 9, 'student', 's230000001@bestlink.edu.ph', 20, 'accepted', 'Chapter 1 accepted', 'Chapter 1 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-15 22:50:09'),
(56, 'student:under_review:21', 9, 'student', 's230000001@bestlink.edu.ph', 21, 'under_review', 'Chapter 2 is under review', 'Chapter 2 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-15 22:50:13'),
(57, 'student:accepted:21', 9, 'student', 's230000001@bestlink.edu.ph', 21, 'accepted', 'Chapter 2 accepted', 'Chapter 2 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-15 22:50:18'),
(58, 'student:under_review:22', 9, 'student', 's230000001@bestlink.edu.ph', 22, 'under_review', 'Chapter 3 is under review', 'Chapter 3 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-15 22:50:21'),
(59, 'student:accepted:22', 9, 'student', 's230000001@bestlink.edu.ph', 22, 'accepted', 'Chapter 3 accepted', 'Chapter 3 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-15 22:50:27'),
(60, 'evaluator:new:23:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 23, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 1 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=23', 0, '2026-08-16 15:00:39'),
(61, 'evaluator:new:24:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 24, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 2 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=24', 0, '2026-08-16 15:00:43'),
(62, 'evaluator:new:25:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 25, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 3 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=25', 0, '2026-08-16 15:00:47'),
(63, 'student:under_review:23', 9, 'student', 's230000001@bestlink.edu.ph', 23, 'under_review', 'Chapter 1 is under review', 'Chapter 1 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-16 15:01:13'),
(64, 'student:accepted:23', 9, 'student', 's230000001@bestlink.edu.ph', 23, 'accepted', 'Chapter 1 accepted', 'Chapter 1 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-16 15:01:21'),
(65, 'student:under_review:24', 9, 'student', 's230000001@bestlink.edu.ph', 24, 'under_review', 'Chapter 2 is under review', 'Chapter 2 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-16 15:01:26'),
(66, 'student:accepted:24', 9, 'student', 's230000001@bestlink.edu.ph', 24, 'accepted', 'Chapter 2 accepted', 'Chapter 2 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 1, '2026-08-16 15:01:31'),
(67, 'student:under_review:25', 9, 'student', 's230000001@bestlink.edu.ph', 25, 'under_review', 'Chapter 3 is under review', 'Chapter 3 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-16 15:01:35'),
(68, 'student:accepted:25', 9, 'student', 's230000001@bestlink.edu.ph', 25, 'accepted', 'Chapter 3 accepted', 'Chapter 3 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 1, '2026-08-16 15:01:40'),
(69, 'evaluator:new:26:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 26, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 1 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=26', 0, '2026-08-16 21:42:19'),
(70, 'evaluator:new:27:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 27, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 2 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=27', 0, '2026-08-16 21:42:22'),
(71, 'evaluator:new:28:u475', 475, 'grammarian', 'grammarian@bestlink.edu.ph', 28, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 3 Version 1 for evaluation.', '/SMS2_system/modules/faculty/pages/evaluation-scoring.php?id=28', 0, '2026-08-16 21:42:25'),
(72, 'student:under_review:26', 9, 'student', 's230000001@bestlink.edu.ph', 26, 'under_review', 'Chapter 1 is under review', 'Chapter 1 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-16 21:42:53'),
(73, 'student:accepted:26', 9, 'student', 's230000001@bestlink.edu.ph', 26, 'accepted', 'Chapter 1 accepted', 'Chapter 1 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-16 21:43:06'),
(74, 'student:under_review:27', 9, 'student', 's230000001@bestlink.edu.ph', 27, 'under_review', 'Chapter 2 is under review', 'Chapter 2 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-16 21:43:11'),
(75, 'student:accepted:27', 9, 'student', 's230000001@bestlink.edu.ph', 27, 'accepted', 'Chapter 2 accepted', 'Chapter 2 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-16 21:43:15'),
(76, 'student:under_review:28', 9, 'student', 's230000001@bestlink.edu.ph', 28, 'under_review', 'Chapter 3 is under review', 'Chapter 3 Version 1 is now under review.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-16 21:43:22'),
(77, 'student:accepted:28', 9, 'student', 's230000001@bestlink.edu.ph', 28, 'accepted', 'Chapter 3 accepted', 'Chapter 3 Version 1 is now Accepted.', '/SMS2_system/modules/student-portal/pages/submission-status.php', 0, '2026-08-16 21:43:25'),
(78, 'evaluator:new:29:u729', 729, 'grammarian', 'grammarian@bestlink.edu.ph', 29, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 3 Version 1 for evaluation.', '/modules/faculty/pages/evaluation-scoring.php?id=29', 0, '2026-08-21 15:54:57'),
(79, 'student:under_review:29', 9, 'student', 's230000001@bestlink.edu.ph', 29, 'under_review', 'Chapter 3 is under review', 'Chapter 3 Version 1 is now under review.', '/modules/student-portal/pages/submission-status.php', 0, '2026-08-21 15:55:56'),
(80, 'student:accepted:29', 9, 'student', 's230000001@bestlink.edu.ph', 29, 'accepted', 'Chapter 3 accepted', 'Chapter 3 Version 1 is now Accepted.', '/modules/student-portal/pages/submission-status.php', 0, '2026-08-21 15:56:34'),
(81, 'evaluator:new:30:u729', 729, 'grammarian', 'grammarian@bestlink.edu.ph', 30, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 1 Version 1 for evaluation.', '/modules/faculty/pages/evaluation-scoring.php?id=30', 0, '2026-08-21 16:30:48'),
(82, 'evaluator:new:31:u729', 729, 'grammarian', 'grammarian@bestlink.edu.ph', 31, 'new_submission', 'New Chapter Submission', 'Group 01 submitted Chapter 2 Version 1 for evaluation.', '/modules/faculty/pages/evaluation-scoring.php?id=31', 0, '2026-08-21 16:30:57'),
(83, 'student:under_review:30', 9, 'student', 's230000001@bestlink.edu.ph', 30, 'under_review', 'Chapter 1 is under review', 'Chapter 1 Version 1 is now under review.', '/modules/student-portal/pages/submission-status.php', 0, '2026-08-21 16:31:33'),
(84, 'student:accepted:30', 9, 'student', 's230000001@bestlink.edu.ph', 30, 'accepted', 'Chapter 1 accepted', 'Chapter 1 Version 1 is now Accepted.', '/modules/student-portal/pages/submission-status.php', 0, '2026-08-21 16:31:44'),
(85, 'student:under_review:31', 9, 'student', 's230000001@bestlink.edu.ph', 31, 'under_review', 'Chapter 2 is under review', 'Chapter 2 Version 1 is now under review.', '/modules/student-portal/pages/submission-status.php', 0, '2026-08-21 16:31:56'),
(86, 'student:accepted:31', 9, 'student', 's230000001@bestlink.edu.ph', 31, 'accepted', 'Chapter 2 accepted', 'Chapter 2 Version 1 is now Accepted.', '/modules/student-portal/pages/submission-status.php', 0, '2026-08-21 16:32:07');

-- --------------------------------------------------------

--
-- Table structure for table `chapter_submissions`
--

CREATE TABLE `chapter_submissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `research_plan_id` int(10) UNSIGNED DEFAULT NULL,
  `chapter_number` tinyint(3) UNSIGNED NOT NULL,
  `version_number` int(10) UNSIGNED NOT NULL,
  `status` enum('Submitted','Under Review','Needs Revision','Accepted') NOT NULL DEFAULT 'Submitted',
  `submitted_by_user` int(10) UNSIGNED DEFAULT NULL,
  `submitted_by_name` varchar(150) NOT NULL DEFAULT '',
  `submitted_by_email` varchar(190) NOT NULL DEFAULT '',
  `submission_notes` text DEFAULT NULL,
  `original_name` varchar(255) NOT NULL DEFAULT '',
  `stored_subdir` varchar(180) NOT NULL DEFAULT '',
  `stored_name` varchar(120) NOT NULL DEFAULT '',
  `file_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `file_mime` varchar(120) NOT NULL DEFAULT '',
  `submission_token` varchar(64) NOT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `review_started_at` datetime DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chapter_submissions`
--

INSERT INTO `chapter_submissions` (`id`, `research_group_id`, `research_plan_id`, `chapter_number`, `version_number`, `status`, `submitted_by_user`, `submitted_by_name`, `submitted_by_email`, `submission_notes`, `original_name`, `stored_subdir`, `stored_name`, `file_size`, `file_mime`, `submission_token`, `submitted_at`, `review_started_at`, `reviewed_at`, `updated_at`) VALUES
(1, 32, 4, 1, 1, 'Under Review', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', 'd701d806c9562e421e68041533ec87f5.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'f84ccb4c48dc39ef87ff8e7158a01a1e79a8b5e35416ef6cc9fd52693a39c48a', '2026-08-14 11:24:27', '2026-08-14 11:24:57', NULL, '2026-08-22 15:38:28'),
(2, 32, 4, 2, 1, 'Submitted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', '19ed7ba46d4caa1521e45b38b487c7ed.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '90b72a115a04f4d05c9d26551aef19f1c4f1d46896201343acd549071294865c', '2026-08-14 11:24:31', NULL, NULL, '2026-08-22 15:38:28'),
(3, 32, 4, 3, 1, 'Needs Revision', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', '51db87a2f87cf9170760cdb98ea3bd97.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'beb4b8b880b05e7711db5de83e9725e96ead21a019e1cac892f9019dfadb5a2d', '2026-08-14 11:24:36', '2026-08-14 11:36:01', '2026-08-14 11:36:16', '2026-08-22 15:38:28'),
(4, 32, 4, 3, 2, 'Submitted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', '4ba3d3bad009e0c487e578a5093460c3.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '88d7c8258b1e860786c29e225114cf1b0fa226396e297a75961c1fbdd9100075', '2026-08-14 11:36:41', NULL, NULL, '2026-08-22 15:38:28'),
(5, 33, 5, 1, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', 'a2e6c39b6d6008d28709960f87529479.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'ab9c344b88677bede5792e4af27accc0e1ad546e543c6e4cb6c3ac971e6bb4d2', '2026-08-14 12:07:57', '2026-08-14 12:16:20', '2026-08-14 12:16:27', '2026-08-22 15:38:28'),
(6, 33, 5, 2, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', '714ddea8d0b6da05f3f738930cb4d6dd.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'e0d19b00c8be43e1ff3ecfb92bcaa5e3cf21e9c9a6035abc5f66df184f3caa6a', '2026-08-14 12:08:00', '2026-08-14 12:16:32', '2026-08-14 12:16:37', '2026-08-22 15:38:28'),
(7, 33, 5, 3, 1, 'Submitted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', '3bff4745452b00a73bb080461ee97a61.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '98bc4707a9e028f90b6651f00218ab970d4aabbd3770d73b1e1ef43c17c13f16', '2026-08-14 12:08:03', NULL, NULL, '2026-08-22 15:38:28'),
(8, 35, 6, 1, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', 'ced4fa0e2abc442cf0973a1801a1e3c1.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '87202dc271b459eac090ecaae5da951bbb7bbb58c9049b6865f6deee77ea5ee2', '2026-08-14 12:46:13', '2026-08-14 12:47:57', '2026-08-14 12:48:05', '2026-08-22 15:38:28'),
(9, 35, 6, 3, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', '2a7b3dfa4002e9623638ccb3e48f93d0.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '7ea4f80f8e6b9b254116f81f864e3909c16dc6c14a8cf0726354d7ff73325617', '2026-08-14 12:46:17', '2026-08-14 12:48:09', '2026-08-14 12:48:15', '2026-08-22 15:38:28'),
(10, 35, 6, 2, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', 'af17f61436575f4da202112e45034a11.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'b4f2e4a77f95ef6d9a40cd91928ae832d86504eb48d9579fe6018319a77e8e36', '2026-08-14 12:46:20', '2026-08-14 12:48:20', '2026-08-14 12:48:26', '2026-08-22 15:38:28'),
(11, 37, 7, 1, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', '5d7ad37097da3d2ee1b957ba18000b7d.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '0fb0bdbca6479b7d90dc5a6558acd93e228a8c5991c70636588e5bd5c3bd9b89', '2026-08-14 13:23:21', '2026-08-14 13:45:53', '2026-08-14 13:46:02', '2026-08-22 15:38:28'),
(12, 37, 7, 2, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', 'eedeb4f49d08aac9fe3d63af55ac38e1.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'c3de065fbb5af9dde867e24b7641a720006fd5bb5d111e63bb35bfdcab038585', '2026-08-14 13:23:25', '2026-08-14 13:46:08', '2026-08-14 13:46:13', '2026-08-22 15:38:28'),
(13, 37, 7, 3, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', '9a23f04751f97957448e32b4f2235439.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '37cbba11a700831ca9f63ea342206646d20abf0b22504b0ee59d741ba7baa4ba', '2026-08-14 13:23:28', '2026-08-14 13:46:16', '2026-08-14 13:46:22', '2026-08-22 15:38:28'),
(14, 49, 10, 1, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', 'aa5fe7a11ebfe16ba2b809f8a7479a71.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'b8c8a5b1bad132d6db5631c8c15b4ead23d032bc60a504c9e759aad8ce4469ae', '2026-08-14 16:40:44', '2026-08-14 16:42:06', '2026-08-14 16:42:19', '2026-08-22 15:38:28'),
(15, 50, 11, 1, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', 'asdas', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', '2ad04cb26f4d2846fe1d46f78dae664e.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'eecea3c1b9326302e7153ebc617e279cc7a14120c964aebac2845798347dd058', '2026-08-14 17:36:01', '2026-08-14 17:36:17', '2026-08-14 17:36:32', '2026-08-22 15:38:28'),
(16, 51, 12, 1, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', 'Demo submission', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', '7bb8e80537bb3ed711a73497e2de5e7b.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '209c3cbefba8e61189fc2323164243b46b3154608c4299eba40356f0aea5281d', '2026-08-14 21:50:29', '2026-08-14 21:51:10', '2026-08-14 21:51:29', '2026-08-22 15:44:13'),
(17, 52, 13, 1, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', 'e1fddffd99adb14a06a7fa5ea798b43c.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '8fc7d5dd260dd0defe532888932b803afedfcbbb8c7502457f8cf7c778fd03aa', '2026-08-15 16:43:36', '2026-08-15 16:48:40', '2026-08-15 16:48:46', '2026-08-22 15:38:28'),
(18, 52, 13, 2, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', 'f018f85d1cf7c0e52ab1cc8e1fa39947.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'd9d172fd39bea1a42fe389be1fc44640fa71b9a27ed9aa759fbd94a9e78afa65', '2026-08-15 16:43:40', '2026-08-15 16:48:51', '2026-08-15 16:48:55', '2026-08-22 15:38:28'),
(19, 52, 13, 3, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', '25b7aadf2d5ae76b52f0757e879226b6.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '7a2716e6e740b5fd8348b02e468f5565094bebc4266ca82f6365d46f7cba3686', '2026-08-15 16:43:43', '2026-08-15 16:49:00', '2026-08-15 16:49:04', '2026-08-22 15:38:28'),
(20, 53, 14, 1, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', 'a45b6a089b27032c4149b22213f1666a.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '01321083f820b1b6d6fc915998af5abb7e54719b9ab78b286f28e2e3aba7ecb2', '2026-08-15 22:49:29', '2026-08-15 22:50:04', '2026-08-15 22:50:09', '2026-08-22 15:38:28'),
(21, 53, 14, 2, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', 'e06673a0b1c2eeae1046a1e1a3eed44d.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'cdf46b4e187a7190e3c81ef628f87d3a253e744b0382669a68092e74e89aab41', '2026-08-15 22:49:33', '2026-08-15 22:50:13', '2026-08-15 22:50:18', '2026-08-22 15:38:28'),
(22, 53, 14, 3, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', 'ab24584a5daf1304f15af0e4ab452c4c.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'd26ae569e6bc25967790118e5bb73bd06cb7412ceda8003415d9bb0b9e478013', '2026-08-15 22:49:38', '2026-08-15 22:50:21', '2026-08-15 22:50:27', '2026-08-22 15:38:28'),
(23, 54, 15, 1, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', 'ddd8455e207282f3368080dcddb947f9.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'efdea4955fcb87b97c19965e5153a6154d99b2e3913d34ab62b38115d3c7df1a', '2026-08-16 15:00:39', '2026-08-16 15:01:13', '2026-08-16 15:01:21', '2026-08-22 15:38:28'),
(24, 54, 15, 2, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', 'b9349fa8d6015674e8c5558539e0d3d1.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'ace403328568a5ae8483b01fca607df90b6a6df080dc02b32008cd1d2e49bec8', '2026-08-16 15:00:43', '2026-08-16 15:01:26', '2026-08-16 15:01:31', '2026-08-22 15:38:28'),
(25, 54, 15, 3, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', 'bfe5747918dc0008d8a943e21971e798.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '9dba7fa49a06ab91da5ca4ffecad14d25b1718cd1c025ff52f5d1d8e4ed5c6fa', '2026-08-16 15:00:47', '2026-08-16 15:01:35', '2026-08-16 15:01:40', '2026-08-22 15:38:28'),
(26, 57, 18, 1, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', '6c8f872d67614549e48ca00d6619c864.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '11699bc5a49974e2a0f7a8557152f4bb18e27c5064461b6967161888792ad55e', '2026-08-16 21:42:19', '2026-08-16 21:42:53', '2026-08-16 21:43:06', '2026-08-22 15:38:28'),
(27, 57, 18, 2, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', '67816ad9f3c14c89d0c520c2938757eb.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '0e3e8cd0f35558eb65ff0ac1dfee4261a8143d6bb2e9eeee056695648b72a2f2', '2026-08-16 21:42:22', '2026-08-16 21:43:11', '2026-08-16 21:43:15', '2026-08-22 15:38:28'),
(28, 57, 18, 3, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'student_chapters/u9', '5174db7646f2cab2a4a17b7bf51ecfe3.docx', 350940, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '3b315ca7036dd639ae30d3a923824528d01ea587c7f1f36dc69fc8b753e54ef2', '2026-08-16 21:42:25', '2026-08-16 21:43:22', '2026-08-16 21:43:25', '2026-08-22 15:38:28'),
(29, 58, 19, 3, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', 'aSAsaS', 'index.docx', 'student_chapters/u9', '0353b661cc5cd4d4f635abae65eb5e1f.docx', 10129, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '35e1aa1760959af76f4c6735f0f0dc53aa82a2d1530c77569b24519badbfdb17', '2026-08-21 15:54:57', '2026-08-21 15:55:56', '2026-08-21 15:56:34', '2026-08-22 15:38:28'),
(30, 58, 19, 1, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', '', 'index (1).docx', 'student_chapters/u9', 'f018ea5f7c9ee42967fc3ca75c296030.docx', 10129, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '7c0c16353aff30fe216e538ec562e00e1fed9af0b0b53b019699823ef31145d3', '2026-08-21 16:30:48', '2026-08-21 16:31:33', '2026-08-21 16:31:44', '2026-08-22 15:38:28'),
(31, 58, 19, 2, 1, 'Accepted', 9, 'Student User', 's230000001@bestlink.edu.ph', 's', 'index (1).docx', 'student_chapters/u9', '537eb81238468a40d9a5e81cf78360ce.docx', 10129, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'adba7e6a1f5e74acb78adc3649b4273a710c382e34443e17d4e53e1d0d9c8f1f', '2026-08-21 16:30:57', '2026-08-21 16:31:56', '2026-08-21 16:32:07', '2026-08-22 15:38:28');

-- --------------------------------------------------------

--
-- Table structure for table `chapter_submission_history`
--

CREATE TABLE `chapter_submission_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `submission_id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `chapter_number` tinyint(3) UNSIGNED NOT NULL,
  `version_number` int(10) UNSIGNED NOT NULL,
  `status` varchar(40) NOT NULL,
  `event_type` varchar(60) NOT NULL,
  `actor_user_id` int(10) UNSIGNED DEFAULT NULL,
  `actor_name` varchar(150) NOT NULL DEFAULT '',
  `actor_role` varchar(60) NOT NULL DEFAULT '',
  `detail` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chapter_submission_history`
--

INSERT INTO `chapter_submission_history` (`id`, `submission_id`, `research_group_id`, `chapter_number`, `version_number`, `status`, `event_type`, `actor_user_id`, `actor_name`, `actor_role`, `detail`, `created_at`) VALUES
(1, 1, 32, 1, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-14 11:24:27'),
(2, 2, 32, 2, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-14 11:24:31'),
(3, 3, 32, 3, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-14 11:24:36'),
(4, 1, 32, 1, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-14 11:24:57'),
(5, 3, 32, 3, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-14 11:36:01'),
(6, 3, 32, 3, 1, 'Needs Revision', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED WITH REVISION', '2026-08-14 11:36:16'),
(7, 4, 32, 3, 2, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-14 11:36:41'),
(8, 5, 33, 1, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-14 12:07:57'),
(9, 6, 33, 2, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-14 12:08:00'),
(10, 7, 33, 3, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-14 12:08:03'),
(11, 5, 33, 1, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-14 12:16:20'),
(12, 5, 33, 1, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-14 12:16:27'),
(13, 6, 33, 2, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-14 12:16:32'),
(14, 6, 33, 2, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-14 12:16:37'),
(15, 8, 35, 1, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-14 12:46:13'),
(16, 9, 35, 3, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-14 12:46:17'),
(17, 10, 35, 2, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-14 12:46:20'),
(18, 8, 35, 1, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-14 12:47:57'),
(19, 8, 35, 1, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-14 12:48:05'),
(20, 9, 35, 3, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-14 12:48:09'),
(21, 9, 35, 3, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-14 12:48:15'),
(22, 10, 35, 2, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-14 12:48:20'),
(23, 10, 35, 2, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-14 12:48:26'),
(24, 11, 37, 1, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-14 13:23:21'),
(25, 12, 37, 2, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-14 13:23:25'),
(26, 13, 37, 3, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-14 13:23:28'),
(27, 11, 37, 1, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-14 13:45:53'),
(28, 11, 37, 1, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-14 13:46:02'),
(29, 12, 37, 2, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-14 13:46:08'),
(30, 12, 37, 2, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-14 13:46:13'),
(31, 13, 37, 3, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-14 13:46:16'),
(32, 13, 37, 3, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-14 13:46:22'),
(33, 14, 49, 1, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-14 16:40:44'),
(34, 14, 49, 1, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-14 16:42:06'),
(35, 14, 49, 1, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-14 16:42:19'),
(36, 15, 50, 1, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', 'asdas', '2026-08-14 17:36:01'),
(37, 15, 50, 1, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-14 17:36:17'),
(38, 15, 50, 1, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-14 17:36:32'),
(39, 16, 51, 1, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', 'Demo submission', '2026-08-14 21:50:29'),
(40, 16, 51, 1, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-14 21:51:10'),
(41, 16, 51, 1, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-14 21:51:29'),
(42, 17, 52, 1, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-15 16:43:36'),
(43, 18, 52, 2, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-15 16:43:40'),
(44, 19, 52, 3, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-15 16:43:43'),
(45, 17, 52, 1, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-15 16:48:40'),
(46, 17, 52, 1, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-15 16:48:46'),
(47, 18, 52, 2, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-15 16:48:51'),
(48, 18, 52, 2, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-15 16:48:55'),
(49, 19, 52, 3, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-15 16:49:00'),
(50, 19, 52, 3, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-15 16:49:04'),
(51, 20, 53, 1, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-15 22:49:29'),
(52, 21, 53, 2, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-15 22:49:33'),
(53, 22, 53, 3, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-15 22:49:38'),
(54, 20, 53, 1, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-15 22:50:04'),
(55, 20, 53, 1, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-15 22:50:09'),
(56, 21, 53, 2, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-15 22:50:13'),
(57, 21, 53, 2, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-15 22:50:18'),
(58, 22, 53, 3, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-15 22:50:21'),
(59, 22, 53, 3, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-15 22:50:27'),
(60, 23, 54, 1, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-16 15:00:39'),
(61, 24, 54, 2, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-16 15:00:43'),
(62, 25, 54, 3, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-16 15:00:47'),
(63, 23, 54, 1, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-16 15:01:13'),
(64, 23, 54, 1, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-16 15:01:21'),
(65, 24, 54, 2, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-16 15:01:26'),
(66, 24, 54, 2, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-16 15:01:31'),
(67, 25, 54, 3, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-16 15:01:35'),
(68, 25, 54, 3, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-16 15:01:40'),
(69, 26, 57, 1, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-16 21:42:19'),
(70, 27, 57, 2, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-16 21:42:22'),
(71, 28, 57, 3, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-16 21:42:25'),
(72, 26, 57, 1, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-16 21:42:53'),
(73, 26, 57, 1, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-16 21:43:06'),
(74, 27, 57, 2, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-16 21:43:11'),
(75, 27, 57, 2, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-16 21:43:15'),
(76, 28, 57, 3, 1, 'Under Review', 'review_started', 475, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-16 21:43:22'),
(77, 28, 57, 3, 1, 'Accepted', 'evaluated', 475, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-16 21:43:25'),
(78, 29, 58, 3, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', 'aSAsaS', '2026-08-21 15:54:57'),
(79, 29, 58, 3, 1, 'Under Review', 'review_started', 729, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-21 15:55:56'),
(80, 29, 58, 3, 1, 'Accepted', 'evaluated', 729, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-21 15:56:34'),
(81, 30, 58, 1, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', '', '2026-08-21 16:30:48'),
(82, 31, 58, 2, 1, 'Submitted', 'submitted', 9, 'Student User', 'student', 's', '2026-08-21 16:30:57'),
(83, 30, 58, 1, 1, 'Under Review', 'review_started', 729, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-21 16:31:33'),
(84, 30, 58, 1, 1, 'Accepted', 'evaluated', 729, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-21 16:31:44'),
(85, 31, 58, 2, 1, 'Under Review', 'review_started', 729, 'Grammarian', 'grammarian', 'Grammarian started review.', '2026-08-21 16:31:56'),
(86, 31, 58, 2, 1, 'Accepted', 'evaluated', 729, 'Grammarian', 'grammarian', 'APPROVED', '2026-08-21 16:32:07');

-- --------------------------------------------------------

--
-- Table structure for table `crad_data_integrity_archive`
--

CREATE TABLE `crad_data_integrity_archive` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(80) NOT NULL,
  `entity_id` bigint(20) UNSIGNED NOT NULL,
  `payload_json` longtext DEFAULT NULL,
  `reason` varchar(190) NOT NULL,
  `archived_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `crad_data_integrity_archive`
--

INSERT INTO `crad_data_integrity_archive` (`id`, `entity_type`, `entity_id`, `payload_json`, `reason`, `archived_at`) VALUES
(1, 'research_panel_assignment', 1, '{\"research_group_id\": 52, \"panel_user_id\": 491, \"panel_name\": \"Dr. Jobert Valentino\", \"defense_phase\": \"Pre-Oral Defense\", \"assignment_status\": \"Assigned\"}', 'Orphaned assignment: research group no longer exists', '2026-08-22 15:35:41'),
(2, 'research_panel_assignment', 2, '{\"research_group_id\": 52, \"panel_user_id\": 492, \"panel_name\": \"Dr. Jonathan Estrada\", \"defense_phase\": \"Pre-Oral Defense\", \"assignment_status\": \"Assigned\"}', 'Orphaned assignment: research group no longer exists', '2026-08-22 15:35:41'),
(3, 'research_panel_assignment', 3, '{\"research_group_id\": 52, \"panel_user_id\": 493, \"panel_name\": \"Dr. Michelle Guevarra\", \"defense_phase\": \"Pre-Oral Defense\", \"assignment_status\": \"Assigned\"}', 'Orphaned assignment: research group no longer exists', '2026-08-22 15:35:41'),
(4, 'research_panel_assignment', 4, '{\"research_group_id\": 53, \"panel_user_id\": 491, \"panel_name\": \"Dr. Jobert Valentino\", \"defense_phase\": \"Pre-Oral Defense\", \"assignment_status\": \"Assigned\"}', 'Orphaned assignment: research group no longer exists', '2026-08-22 15:35:41'),
(5, 'research_panel_assignment', 5, '{\"research_group_id\": 53, \"panel_user_id\": 492, \"panel_name\": \"Dr. Jonathan Estrada\", \"defense_phase\": \"Pre-Oral Defense\", \"assignment_status\": \"Assigned\"}', 'Orphaned assignment: research group no longer exists', '2026-08-22 15:35:41'),
(6, 'research_panel_assignment', 6, '{\"research_group_id\": 53, \"panel_user_id\": 493, \"panel_name\": \"Dr. Michelle Guevarra\", \"defense_phase\": \"Pre-Oral Defense\", \"assignment_status\": \"Assigned\"}', 'Orphaned assignment: research group no longer exists', '2026-08-22 15:35:41'),
(7, 'research_panel_assignment', 7, '{\"research_group_id\": 54, \"panel_user_id\": 491, \"panel_name\": \"Dr. Jobert Valentino\", \"defense_phase\": \"Pre-Oral Defense\", \"assignment_status\": \"Assigned\"}', 'Orphaned assignment: research group no longer exists', '2026-08-22 15:35:41'),
(8, 'research_panel_assignment', 8, '{\"research_group_id\": 54, \"panel_user_id\": 492, \"panel_name\": \"Dr. Jonathan Estrada\", \"defense_phase\": \"Pre-Oral Defense\", \"assignment_status\": \"Assigned\"}', 'Orphaned assignment: research group no longer exists', '2026-08-22 15:35:41'),
(9, 'research_panel_assignment', 9, '{\"research_group_id\": 54, \"panel_user_id\": 493, \"panel_name\": \"Dr. Michelle Guevarra\", \"defense_phase\": \"Pre-Oral Defense\", \"assignment_status\": \"Assigned\"}', 'Orphaned assignment: research group no longer exists', '2026-08-22 15:35:41'),
(10, 'research_panel_assignment', 10, '{\"research_group_id\": 57, \"panel_user_id\": 491, \"panel_name\": \"Dr. Jobert Valentino\", \"defense_phase\": \"Pre-Oral Defense\", \"assignment_status\": \"Assigned\"}', 'Orphaned assignment: research group no longer exists', '2026-08-22 15:35:41'),
(11, 'research_panel_assignment', 11, '{\"research_group_id\": 57, \"panel_user_id\": 492, \"panel_name\": \"Dr. Jonathan Estrada\", \"defense_phase\": \"Pre-Oral Defense\", \"assignment_status\": \"Assigned\"}', 'Orphaned assignment: research group no longer exists', '2026-08-22 15:35:41'),
(12, 'research_panel_assignment', 12, '{\"research_group_id\": 57, \"panel_user_id\": 493, \"panel_name\": \"Dr. Michelle Guevarra\", \"defense_phase\": \"Pre-Oral Defense\", \"assignment_status\": \"Assigned\"}', 'Orphaned assignment: research group no longer exists', '2026-08-22 15:35:41');

-- --------------------------------------------------------

--
-- Table structure for table `crad_schema_migrations`
--

CREATE TABLE `crad_schema_migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration_key` varchar(120) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `applied_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `crad_schema_migrations`
--

INSERT INTO `crad_schema_migrations` (`id`, `migration_key`, `description`, `applied_at`) VALUES
(1, '2026.08.21.4', 'CRAD second-semester workflow through official Final Defense result aggregation', '2026-08-21 06:56:51'),
(121, '2026.08.22.1', 'CRAD second-semester workflow with synchronized stages and append-only final approval history', '2026-08-22 15:35:41');

-- --------------------------------------------------------

--
-- Table structure for table `defense_attempts`
--

CREATE TABLE `defense_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `academic_term_id` int(10) UNSIGNED NOT NULL,
  `defense_type` enum('Pre-Oral Defense','Final Defense','Re-Defense') NOT NULL,
  `attempt_number` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `readiness_check_id` int(10) UNSIGNED DEFAULT NULL,
  `final_draft_submission_id` int(10) UNSIGNED DEFAULT NULL,
  `defense_schedule_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('Ready for Scheduling','Scheduled','Completed','For Revision','Passed','Failed','Cancelled') NOT NULL DEFAULT 'Ready for Scheduling',
  `created_by_user` int(10) UNSIGNED DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `defense_official_results`
--

CREATE TABLE `defense_official_results` (
  `id` int(10) UNSIGNED NOT NULL,
  `defense_attempt_id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `defense_schedule_id` int(10) UNSIGNED DEFAULT NULL,
  `assigned_panel_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `submitted_evaluation_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `average_score` decimal(5,2) DEFAULT NULL,
  `official_result` enum('UNRESOLVED','PASSED','PASSED WITH REVISIONS','FAILED','VOID') NOT NULL DEFAULT 'UNRESOLVED',
  `result_basis_json` longtext DEFAULT NULL,
  `finalized_by_user` int(10) UNSIGNED DEFAULT NULL,
  `finalized_by_name` varchar(150) NOT NULL DEFAULT '',
  `finalized_at` datetime DEFAULT NULL,
  `supersedes_result_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `defense_revision_items`
--

CREATE TABLE `defense_revision_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `defense_attempt_id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `source_evaluation_id` int(10) UNSIGNED DEFAULT NULL,
  `requested_by_user` int(10) UNSIGNED DEFAULT NULL,
  `requested_by_name` varchar(150) NOT NULL DEFAULT '',
  `revision_text` text NOT NULL,
  `status` enum('Open','Submitted','Complied','Rejected','Waived') NOT NULL DEFAULT 'Open',
  `verified_by_user` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `verification_remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `defense_revision_submissions`
--

CREATE TABLE `defense_revision_submissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `defense_attempt_id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `version_number` int(10) UNSIGNED NOT NULL,
  `submitted_by_user` int(10) UNSIGNED DEFAULT NULL,
  `submitted_by_name` varchar(150) NOT NULL DEFAULT '',
  `response_notes` text DEFAULT NULL,
  `original_name` varchar(255) NOT NULL DEFAULT '',
  `stored_subdir` varchar(180) NOT NULL DEFAULT '',
  `stored_name` varchar(180) NOT NULL DEFAULT '',
  `file_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `file_mime` varchar(120) NOT NULL DEFAULT '',
  `file_checksum` char(64) NOT NULL DEFAULT '',
  `status` enum('Submitted','Under Review','For Resubmission','Complied','Superseded') NOT NULL DEFAULT 'Submitted',
  `reviewed_by_user` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_remarks` text DEFAULT NULL,
  `submission_token` varchar(64) NOT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `final_defense_evaluations`
--

CREATE TABLE `final_defense_evaluations` (
  `id` int(10) UNSIGNED NOT NULL,
  `defense_schedule_id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED DEFAULT NULL,
  `panel_user_id` int(10) UNSIGNED NOT NULL,
  `panel_name` varchar(150) NOT NULL DEFAULT '',
  `content_score` decimal(5,2) NOT NULL,
  `methodology_score` decimal(5,2) NOT NULL,
  `references_score` decimal(5,2) NOT NULL,
  `format_score` decimal(5,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `result` enum('APPROVED','APPROVED WITH REVISION','FAILED') NOT NULL,
  `overall_score` decimal(5,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Submitted',
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `defense_attempt_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `final_defense_recommendations`
--

CREATE TABLE `final_defense_recommendations` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `group_number` varchar(40) NOT NULL DEFAULT '',
  `adviser_user_id` int(10) UNSIGNED DEFAULT NULL,
  `adviser_name` varchar(150) NOT NULL DEFAULT '',
  `status` enum('Not Ready','Recommended') NOT NULL DEFAULT 'Not Ready',
  `remarks` text DEFAULT NULL,
  `recommended_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `academic_term_id` int(10) UNSIGNED DEFAULT NULL,
  `readiness_check_id` int(10) UNSIGNED DEFAULT NULL,
  `final_draft_submission_id` int(10) UNSIGNED DEFAULT NULL,
  `revoked_by_user` int(10) UNSIGNED DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `revocation_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `final_draft_reviews`
--

CREATE TABLE `final_draft_reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `submission_id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `academic_term_id` int(10) UNSIGNED NOT NULL,
  `adviser_user_id` int(10) UNSIGNED NOT NULL,
  `adviser_name` varchar(150) NOT NULL DEFAULT '',
  `chapter_1_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `chapter_2_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `chapter_3_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `chapter_4_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `chapter_5_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `formatting_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `citations_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `decision` enum('Revision Requested','Endorsed','Endorsement Revoked') NOT NULL,
  `remarks` text DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `reviewed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `superseded_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `final_draft_submissions`
--

CREATE TABLE `final_draft_submissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `academic_term_id` int(10) UNSIGNED NOT NULL,
  `version_number` int(10) UNSIGNED NOT NULL,
  `supersedes_submission_id` int(10) UNSIGNED DEFAULT NULL,
  `submitted_by_user` int(10) UNSIGNED DEFAULT NULL,
  `submitted_by_name` varchar(150) NOT NULL DEFAULT '',
  `original_name` varchar(255) NOT NULL DEFAULT '',
  `stored_subdir` varchar(180) NOT NULL DEFAULT '',
  `stored_name` varchar(180) NOT NULL DEFAULT '',
  `file_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `file_mime` varchar(120) NOT NULL DEFAULT '',
  `file_checksum` char(64) NOT NULL DEFAULT '',
  `status` enum('Submitted','Under Adviser Review','Revision Requested','Endorsed','Superseded') NOT NULL DEFAULT 'Submitted',
  `adviser_user_id` int(10) UNSIGNED DEFAULT NULL,
  `adviser_name` varchar(150) NOT NULL DEFAULT '',
  `adviser_remarks` text DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `submission_token` varchar(64) NOT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `submission_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `final_manuscript_approvals`
--

CREATE TABLE `final_manuscript_approvals` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `defense_schedule_id` int(10) UNSIGNED DEFAULT NULL,
  `approved_by_user` int(10) UNSIGNED DEFAULT NULL,
  `approved_by_name` varchar(150) NOT NULL DEFAULT '',
  `status` enum('Pending','Approved','Returned') NOT NULL DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `manuscript_submission_id` int(10) UNSIGNED DEFAULT NULL,
  `defense_attempt_id` int(10) UNSIGNED DEFAULT NULL,
  `revision_cycle_id` int(10) UNSIGNED DEFAULT NULL,
  `file_checksum` char(64) NOT NULL DEFAULT '',
  `approval_token` varchar(64) NOT NULL DEFAULT '',
  `superseded_by_approval_id` int(10) UNSIGNED DEFAULT NULL,
  `superseded_at` datetime DEFAULT NULL,
  `revision_submission_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `final_manuscript_approval_history`
--

CREATE TABLE `final_manuscript_approval_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `approval_id` int(10) UNSIGNED DEFAULT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `decision_sequence` int(10) UNSIGNED NOT NULL,
  `decision_status` enum('Pending','Approved','Returned') NOT NULL,
  `actor_user_id` int(10) UNSIGNED DEFAULT NULL,
  `actor_name` varchar(150) NOT NULL DEFAULT '',
  `remarks` text DEFAULT NULL,
  `decision_at` datetime NOT NULL,
  `defense_schedule_id` int(10) UNSIGNED DEFAULT NULL,
  `manuscript_submission_id` int(10) UNSIGNED DEFAULT NULL,
  `revision_submission_id` int(10) UNSIGNED DEFAULT NULL,
  `defense_attempt_id` int(10) UNSIGNED DEFAULT NULL,
  `revision_cycle_id` int(10) UNSIGNED DEFAULT NULL,
  `file_checksum` char(64) NOT NULL DEFAULT '',
  `approval_token` varchar(64) NOT NULL DEFAULT '',
  `event_source` varchar(60) NOT NULL DEFAULT 'WORKFLOW',
  `source_updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `final_readiness_checks`
--

CREATE TABLE `final_readiness_checks` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `academic_term_id` int(10) UNSIGNED NOT NULL,
  `checked_by_user` int(10) UNSIGNED DEFAULT NULL,
  `checked_by_name` varchar(150) NOT NULL DEFAULT '',
  `chapters_complete` tinyint(1) NOT NULL DEFAULT 0,
  `adviser_endorsed` tinyint(1) NOT NULL DEFAULT 0,
  `ethics_clearance_complete` tinyint(1) NOT NULL DEFAULT 0,
  `similarity_check_complete` tinyint(1) NOT NULL DEFAULT 0,
  `required_documents_complete` tinyint(1) NOT NULL DEFAULT 0,
  `overall_status` enum('Incomplete','Ready','Revoked') NOT NULL DEFAULT 'Incomplete',
  `remarks` text DEFAULT NULL,
  `checked_at` datetime NOT NULL DEFAULT current_timestamp(),
  `revoked_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `final_draft_submission_id` int(10) UNSIGNED DEFAULT NULL,
  `adviser_review_id` int(10) UNSIGNED DEFAULT NULL,
  `chapter_1_complete` tinyint(1) NOT NULL DEFAULT 0,
  `chapter_2_complete` tinyint(1) NOT NULL DEFAULT 0,
  `chapter_3_complete` tinyint(1) NOT NULL DEFAULT 0,
  `chapter_4_complete` tinyint(1) NOT NULL DEFAULT 0,
  `chapter_5_complete` tinyint(1) NOT NULL DEFAULT 0,
  `checklist_version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `superseded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grant_applications`
--

CREATE TABLE `grant_applications` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_number` varchar(30) DEFAULT NULL,
  `version_number` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `grant_opportunity_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → grant_opportunities.id',
  `research_group_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK → research_groups.id (nullable for non-capstone applicants)',
  `group_number` varchar(30) DEFAULT NULL,
  `research_title` varchar(500) DEFAULT NULL,
  `applicant_name` varchar(200) NOT NULL DEFAULT '',
  `college` varchar(150) DEFAULT NULL,
  `department` varchar(150) DEFAULT NULL,
  `college_dept` varchar(200) DEFAULT NULL COMMENT 'Academic college / department of the lead proponent',
  `requested_budget` decimal(14,2) DEFAULT NULL COMMENT 'Budget requested by the proponent; must not exceed grant max_funding_cap',
  `abstract` text DEFAULT NULL COMMENT 'Executive abstract of the research proposal',
  `objectives` text DEFAULT NULL COMMENT 'Research objectives',
  `proposal_pdf` varchar(255) DEFAULT NULL COMMENT 'Stored filename of the uploaded proposal PDF/DOC under storage/uploads/grant_proposals/',
  `proposal_pdf_original` varchar(300) DEFAULT NULL COMMENT 'Original filename of the uploaded proposal document',
  `supporting_docs` varchar(255) DEFAULT NULL COMMENT 'Stored filename of optional supporting documents',
  `supporting_docs_original` varchar(300) DEFAULT NULL COMMENT 'Original filename of optional supporting documents',
  `ethics_doc` varchar(255) DEFAULT NULL COMMENT 'Stored filename of optional ethics clearance document',
  `ethics_doc_original` varchar(300) DEFAULT NULL COMMENT 'Original filename of optional ethics clearance document',
  `applicant_user_id` int(10) UNSIGNED DEFAULT NULL,
  `application_notes` text DEFAULT NULL,
  `status` enum('Draft','Submitted','Under Review','Revision Required','Resubmitted','Recommended for Approval','Rejected','Adviser Approved','Department Chair Approved','Dean Approved','Research Office Approved','VPAA Approved','Approved & Funded','Withdrawn') NOT NULL DEFAULT 'Submitted',
  `submission_token` varchar(64) DEFAULT NULL COMMENT 'One-time token for duplicate-submission prevention',
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grant_opportunities`
--

CREATE TABLE `grant_opportunities` (
  `id` int(10) UNSIGNED NOT NULL,
  `funding_title` varchar(300) NOT NULL,
  `max_funding_cap` decimal(14,2) NOT NULL DEFAULT 0.00,
  `application_deadline` date NOT NULL,
  `eligibility` varchar(100) NOT NULL DEFAULT 'Open',
  `college_program` varchar(200) DEFAULT NULL COMMENT 'Populated when eligibility = Specific College/Program',
  `status` enum('Draft','Open for Application','Closed','Expired') NOT NULL DEFAULT 'Draft',
  `created_by_user_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by_name` varchar(150) NOT NULL DEFAULT '',
  `published_by_user_id` int(10) UNSIGNED DEFAULT NULL,
  `published_by_name` varchar(150) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grant_proposal_versions`
--

CREATE TABLE `grant_proposal_versions` (
  `id` int(10) UNSIGNED NOT NULL,
  `grant_application_id` int(10) UNSIGNED NOT NULL,
  `version_number` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `requested_budget` decimal(14,2) DEFAULT NULL,
  `abstract` text DEFAULT NULL,
  `objectives` text DEFAULT NULL,
  `proposal_pdf` varchar(255) DEFAULT NULL,
  `proposal_pdf_original` varchar(300) DEFAULT NULL,
  `supporting_docs` varchar(255) DEFAULT NULL,
  `supporting_docs_original` varchar(300) DEFAULT NULL,
  `ethics_doc` varchar(255) DEFAULT NULL,
  `ethics_doc_original` varchar(300) DEFAULT NULL,
  `version_status` varchar(40) NOT NULL DEFAULT 'Submitted',
  `submitted_by_user_id` int(10) UNSIGNED DEFAULT NULL,
  `submitted_by_name` varchar(200) NOT NULL DEFAULT '',
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grant_status_history`
--

CREATE TABLE `grant_status_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `grant_application_id` int(10) UNSIGNED NOT NULL,
  `from_status` varchar(50) DEFAULT NULL,
  `to_status` varchar(50) NOT NULL,
  `action_key` varchar(60) NOT NULL,
  `actor_user_id` int(10) UNSIGNED DEFAULT NULL,
  `actor_name` varchar(200) NOT NULL DEFAULT '',
  `actor_role` varchar(60) NOT NULL DEFAULT '',
  `remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manuscript_evaluations`
--

CREATE TABLE `manuscript_evaluations` (
  `id` int(10) UNSIGNED NOT NULL,
  `submission_id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `evaluator_user_id` int(10) UNSIGNED NOT NULL,
  `evaluator_name` varchar(150) NOT NULL DEFAULT '',
  `content_score` decimal(5,2) NOT NULL,
  `methodology_score` decimal(5,2) NOT NULL,
  `results_score` decimal(5,2) NOT NULL,
  `conclusions_score` decimal(5,2) NOT NULL,
  `recommendations_score` decimal(5,2) NOT NULL,
  `references_score` decimal(5,2) NOT NULL,
  `formatting_score` decimal(5,2) NOT NULL,
  `compliance_score` decimal(5,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `result` enum('APPROVED','FOR REVISION') NOT NULL,
  `overall_score` decimal(5,2) NOT NULL,
  `evaluated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manuscript_submissions`
--

CREATE TABLE `manuscript_submissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `version_number` int(10) UNSIGNED NOT NULL,
  `status` enum('Submitted','Under Review','For Revision','Approved') NOT NULL DEFAULT 'Submitted',
  `submitted_by_user` int(10) UNSIGNED DEFAULT NULL,
  `submitted_by_name` varchar(150) NOT NULL DEFAULT '',
  `submitted_by_email` varchar(190) NOT NULL DEFAULT '',
  `submission_notes` text DEFAULT NULL,
  `original_name` varchar(255) NOT NULL DEFAULT '',
  `stored_subdir` varchar(180) NOT NULL DEFAULT '',
  `stored_name` varchar(120) NOT NULL DEFAULT '',
  `file_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `file_mime` varchar(120) NOT NULL DEFAULT '',
  `submission_token` varchar(64) NOT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `academic_term_id` int(10) UNSIGNED DEFAULT NULL,
  `purpose` enum('Final Defense','Post-Defense Revision','Final Approved Copy') NOT NULL DEFAULT 'Final Defense',
  `supersedes_submission_id` int(10) UNSIGNED DEFAULT NULL,
  `file_checksum` char(64) NOT NULL DEFAULT '',
  `locked_at` datetime DEFAULT NULL,
  `locked_by_user` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `panel_assignment_notifications`
--

CREATE TABLE `panel_assignment_notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `event_key` varchar(140) NOT NULL,
  `recipient_user_id` int(10) UNSIGNED NOT NULL,
  `recipient_role` varchar(60) NOT NULL DEFAULT 'panel',
  `recipient_email` varchar(190) NOT NULL DEFAULT '',
  `panel_assignment_id` int(10) UNSIGNED DEFAULT NULL,
  `research_group_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(160) NOT NULL DEFAULT '',
  `body` text DEFAULT NULL,
  `url` varchar(500) NOT NULL DEFAULT '',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `panel_assignment_notifications`
--

INSERT INTO `panel_assignment_notifications` (`id`, `event_key`, `recipient_user_id`, `recipient_role`, `recipient_email`, `panel_assignment_id`, `research_group_id`, `title`, `body`, `url`, `is_read`, `created_at`) VALUES
(7, 'preoral-panel-assignment:54:u491', 491, 'panel', 'jobertvalentino@bestlink.edu.ph', 7, 54, 'Pre-Oral Panel Assignment', 'You have been assigned as a Panel Member for Group 01\nDEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS\nDefense Phase: Pre-Oral Defense', '/SMS2_system/modules/faculty/pages/assigned-defenses.php?group=RG-2026-001', 0, '2026-08-16 15:02:09'),
(8, 'preoral-panel-assignment:54:u492', 492, 'panel', 'jonathanestrada@bestlink.edu.ph', 8, 54, 'Pre-Oral Panel Assignment', 'You have been assigned as a Panel Member for Group 01\nDEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS\nDefense Phase: Pre-Oral Defense', '/SMS2_system/modules/faculty/pages/assigned-defenses.php?group=RG-2026-001', 0, '2026-08-16 15:02:09'),
(9, 'preoral-panel-assignment:54:u493', 493, 'panel', 'michelleguevarra@bestlink.edu.ph', 9, 54, 'Pre-Oral Panel Assignment', 'You have been assigned as a Panel Member for Group 01\nDEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS\nDefense Phase: Pre-Oral Defense', '/SMS2_system/modules/faculty/pages/assigned-defenses.php?group=RG-2026-001', 0, '2026-08-16 15:02:09'),
(10, 'preoral-defense-finalized:s23:u491', 491, 'panel', 'jobertvalentino@bestlink.edu.ph', 7, 54, 'Pre-Oral Defense Scheduled', 'RG-2026-001\nDEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS\nDate/Time: Aug 16, 2026 07:00 PM - 08:00 PM\nVenue: Computer Laboratory 1', '/SMS2_system/modules/faculty/pages/defense-details.php?id=23', 0, '2026-08-16 15:15:51'),
(11, 'preoral-defense-finalized:s23:u492', 492, 'panel', 'jonathanestrada@bestlink.edu.ph', 8, 54, 'Pre-Oral Defense Scheduled', 'RG-2026-001\nDEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS\nDate/Time: Aug 16, 2026 07:00 PM - 08:00 PM\nVenue: Computer Laboratory 1', '/SMS2_system/modules/faculty/pages/defense-details.php?id=23', 0, '2026-08-16 15:15:51'),
(12, 'preoral-defense-finalized:s23:u493', 493, 'panel', 'michelleguevarra@bestlink.edu.ph', 9, 54, 'Pre-Oral Defense Scheduled', 'RG-2026-001\nDEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS\nDate/Time: Aug 16, 2026 07:00 PM - 08:00 PM\nVenue: Computer Laboratory 1', '/SMS2_system/modules/faculty/pages/defense-details.php?id=23', 0, '2026-08-16 15:15:51'),
(13, 'preoral-panel-assignment:57:u491', 491, 'panel', 'jobertvalentino@bestlink.edu.ph', 10, 57, 'Pre-Oral Panel Assignment', 'You have been assigned as a Panel Member for Group 01\nDEVELOPMENT OF AI ANALYSIS\nDefense Phase: Pre-Oral Defense', '/SMS2_system/modules/faculty/pages/assigned-defenses.php?group=RG-2026-001', 0, '2026-08-16 21:44:21'),
(14, 'preoral-panel-assignment:57:u492', 492, 'panel', 'jonathanestrada@bestlink.edu.ph', 11, 57, 'Pre-Oral Panel Assignment', 'You have been assigned as a Panel Member for Group 01\nDEVELOPMENT OF AI ANALYSIS\nDefense Phase: Pre-Oral Defense', '/SMS2_system/modules/faculty/pages/assigned-defenses.php?group=RG-2026-001', 0, '2026-08-16 21:44:21'),
(15, 'preoral-panel-assignment:57:u493', 493, 'panel', 'michelleguevarra@bestlink.edu.ph', 12, 57, 'Pre-Oral Panel Assignment', 'You have been assigned as a Panel Member for Group 01\nDEVELOPMENT OF AI ANALYSIS\nDefense Phase: Pre-Oral Defense', '/SMS2_system/modules/faculty/pages/assigned-defenses.php?group=RG-2026-001', 0, '2026-08-16 21:44:21'),
(16, 'preoral-defense-finalized:s26:u491', 491, 'panel', 'jobertvalentino@bestlink.edu.ph', 10, 57, 'Pre-Oral Defense Scheduled', 'RG-2026-001\nDEVELOPMENT OF AI ANALYSIS\nDate/Time: Aug 17, 2026 11:00 AM - 12:00 PM\nVenue: Computer Laboratory 1', '/SMS2_system/modules/faculty/pages/defense-details.php?id=26', 0, '2026-08-16 21:46:14'),
(17, 'preoral-defense-finalized:s26:u492', 492, 'panel', 'jonathanestrada@bestlink.edu.ph', 11, 57, 'Pre-Oral Defense Scheduled', 'RG-2026-001\nDEVELOPMENT OF AI ANALYSIS\nDate/Time: Aug 17, 2026 11:00 AM - 12:00 PM\nVenue: Computer Laboratory 1', '/SMS2_system/modules/faculty/pages/defense-details.php?id=26', 0, '2026-08-16 21:46:14'),
(18, 'preoral-defense-finalized:s26:u493', 493, 'panel', 'michelleguevarra@bestlink.edu.ph', 12, 57, 'Pre-Oral Defense Scheduled', 'RG-2026-001\nDEVELOPMENT OF AI ANALYSIS\nDate/Time: Aug 17, 2026 11:00 AM - 12:00 PM\nVenue: Computer Laboratory 1', '/SMS2_system/modules/faculty/pages/defense-details.php?id=26', 0, '2026-08-16 21:46:14'),
(19, 'preoral-panel-assignment:58:u491', 491, 'panel', 'jobertvalentino@bestlink.edu.ph', 13, 58, 'Pre-Oral Panel Assignment', 'You have been assigned as a Panel Member for Group 01\nCRAD\nDefense Phase: Pre-Oral Defense', '/modules/faculty/pages/assigned-defenses.php?group=RG-2026-001', 0, '2026-08-21 16:37:54'),
(20, 'preoral-panel-assignment:58:u492', 492, 'panel', 'jonathanestrada@bestlink.edu.ph', 14, 58, 'Pre-Oral Panel Assignment', 'You have been assigned as a Panel Member for Group 01\nCRAD\nDefense Phase: Pre-Oral Defense', '/modules/faculty/pages/assigned-defenses.php?group=RG-2026-001', 0, '2026-08-21 16:37:54'),
(21, 'preoral-panel-assignment:58:u493', 493, 'panel', 'michelleguevarra@bestlink.edu.ph', 15, 58, 'Pre-Oral Panel Assignment', 'You have been assigned as a Panel Member for Group 01\nCRAD\nDefense Phase: Pre-Oral Defense', '/modules/faculty/pages/assigned-defenses.php?group=RG-2026-001', 0, '2026-08-21 16:37:54'),
(22, 'preoral-defense-finalized:s29:u491', 491, 'panel', 'jobertvalentino@bestlink.edu.ph', 13, 58, 'Pre-Oral Defense Scheduled', 'RG-2026-001\nCRAD\nDate/Time: Oct 30, 2026 10:30 PM - 11:30 PM\nVenue: CRAD Conference Room', '/modules/faculty/pages/defense-details.php?id=29', 0, '2026-08-21 16:47:27'),
(23, 'preoral-defense-finalized:s29:u492', 492, 'panel', 'jonathanestrada@bestlink.edu.ph', 14, 58, 'Pre-Oral Defense Scheduled', 'RG-2026-001\nCRAD\nDate/Time: Oct 30, 2026 10:30 PM - 11:30 PM\nVenue: CRAD Conference Room', '/modules/faculty/pages/defense-details.php?id=29', 0, '2026-08-21 16:47:27'),
(24, 'preoral-defense-finalized:s29:u493', 493, 'panel', 'michelleguevarra@bestlink.edu.ph', 15, 58, 'Pre-Oral Defense Scheduled', 'RG-2026-001\nCRAD\nDate/Time: Oct 30, 2026 10:30 PM - 11:30 PM\nVenue: CRAD Conference Room', '/modules/faculty/pages/defense-details.php?id=29', 0, '2026-08-21 16:47:27');

-- --------------------------------------------------------

--
-- Table structure for table `panel_member_availability`
--

CREATE TABLE `panel_member_availability` (
  `id` int(10) UNSIGNED NOT NULL,
  `panel_user_id` int(10) UNSIGNED NOT NULL,
  `availability_status` varchar(40) NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `panel_member_availability`
--

INSERT INTO `panel_member_availability` (`id`, `panel_user_id`, `availability_status`, `notes`, `updated_at`, `created_at`) VALUES
(1, 491, 'Available', '', '2026-08-21 16:36:47', '2026-08-15 17:57:51'),
(2, 492, 'Available', '', '2026-08-15 18:20:38', '2026-08-15 18:20:38'),
(3, 493, 'Available', '', '2026-08-15 18:29:23', '2026-08-15 18:26:39');

-- --------------------------------------------------------

--
-- Table structure for table `preoral_defense_evaluations`
--

CREATE TABLE `preoral_defense_evaluations` (
  `id` int(10) UNSIGNED NOT NULL,
  `defense_schedule_id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED DEFAULT NULL,
  `panel_user_id` int(10) UNSIGNED NOT NULL,
  `panel_name` varchar(150) NOT NULL DEFAULT '',
  `content_score` decimal(5,2) NOT NULL,
  `methodology_score` decimal(5,2) NOT NULL,
  `references_score` decimal(5,2) NOT NULL,
  `format_score` decimal(5,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `result` enum('APPROVED','APPROVED WITH REVISION','FAILED') NOT NULL,
  `overall_score` decimal(5,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Submitted',
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `preoral_defense_evaluations`
--

INSERT INTO `preoral_defense_evaluations` (`id`, `defense_schedule_id`, `research_group_id`, `panel_user_id`, `panel_name`, `content_score`, `methodology_score`, `references_score`, `format_score`, `remarks`, `result`, `overall_score`, `status`, `submitted_at`, `created_at`) VALUES
(23, 29, 58, 491, 'Dr. Jobert Valentino', 99.00, 99.00, 99.00, 99.00, '99', 'APPROVED', 99.00, 'Submitted', '2026-08-21 16:53:13', '2026-08-21 16:53:13'),
(24, 29, 58, 492, 'Dr. Jonathan Estrada', 99.00, 99.00, 99.00, 99.00, '99', 'APPROVED', 99.00, 'Submitted', '2026-08-21 16:55:30', '2026-08-21 16:55:30'),
(25, 29, 58, 493, 'Dr. Michelle Guevarra', 99.00, 99.00, 99.00, 99.00, '99', 'APPROVED', 99.00, 'Submitted', '2026-08-21 16:56:20', '2026-08-21 16:56:20');

-- --------------------------------------------------------

--
-- Table structure for table `proposal_documents`
--

CREATE TABLE `proposal_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `proposal_id` int(10) UNSIGNED NOT NULL,
  `doc_key` varchar(60) NOT NULL COMMENT 'Slot key: manuscript, approval, abstract, etc.',
  `doc_title` varchar(200) NOT NULL,
  `original_name` varchar(300) NOT NULL,
  `stored_name` varchar(300) NOT NULL,
  `file_size` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Bytes',
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposal_drafts`
--

CREATE TABLE `proposal_drafts` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK to sms2_db users (optional)',
  `form_type` varchar(30) NOT NULL DEFAULT 'document',
  `revision_ref` varchar(30) NOT NULL DEFAULT '' COMMENT 'Returned proposal ref when draft is for revision',
  `draft_data` longtext NOT NULL COMMENT 'JSON encoded draft form fields except upload files',
  `signature_data` mediumtext DEFAULT NULL COMMENT 'Base64 PNG of representative signature draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposal_members`
--

CREATE TABLE `proposal_members` (
  `id` int(10) UNSIGNED NOT NULL,
  `proposal_id` int(10) UNSIGNED NOT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1 = lead member',
  `student_id` varchar(50) NOT NULL,
  `student_name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `contact` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposal_status_logs`
--

CREATE TABLE `proposal_status_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `proposal_id` int(10) UNSIGNED NOT NULL,
  `old_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) NOT NULL,
  `changed_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK to sms2_db users',
  `remarks` text DEFAULT NULL,
  `changed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `publications`
--

CREATE TABLE `publications` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(500) NOT NULL DEFAULT '',
  `authors` text DEFAULT NULL,
  `publication_outlet` varchar(255) NOT NULL DEFAULT '',
  `publication_date` date DEFAULT NULL,
  `doi_link` varchar(500) NOT NULL DEFAULT '',
  `status` enum('Draft','For Publication','Published','Archived') NOT NULL DEFAULT 'Draft',
  `notes` text DEFAULT NULL,
  `created_by_user` int(10) UNSIGNED DEFAULT NULL,
  `created_by_name` varchar(150) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `final_approval_id` int(10) UNSIGNED DEFAULT NULL,
  `abstract` longtext DEFAULT NULL,
  `keywords` text DEFAULT NULL,
  `department` varchar(160) NOT NULL DEFAULT '',
  `adviser_name` varchar(150) NOT NULL DEFAULT '',
  `access_level` enum('Public','Campus Only','Restricted','Embargoed') NOT NULL DEFAULT 'Campus Only',
  `embargo_until` date DEFAULT NULL,
  `license_name` varchar(120) NOT NULL DEFAULT '',
  `repository_identifier` varchar(120) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `research_adviser_assignments`
--

CREATE TABLE `research_adviser_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED DEFAULT NULL,
  `proposal_id` int(10) UNSIGNED DEFAULT NULL,
  `proposal_number` varchar(30) DEFAULT NULL,
  `group_number` varchar(40) DEFAULT NULL,
  `adviser_name` varchar(150) NOT NULL DEFAULT '',
  `adviser_email` varchar(190) NOT NULL DEFAULT '',
  `adviser_user_id` int(10) UNSIGNED DEFAULT NULL,
  `expertise` varchar(255) NOT NULL DEFAULT '',
  `availability_status` varchar(40) NOT NULL DEFAULT 'Pending',
  `assignment_status` varchar(40) NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `assigned_by` int(10) UNSIGNED DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notification_sent_at` datetime DEFAULT NULL,
  `notification_sent_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `research_adviser_assignments`
--

INSERT INTO `research_adviser_assignments` (`id`, `research_group_id`, `proposal_id`, `proposal_number`, `group_number`, `adviser_name`, `adviser_email`, `adviser_user_id`, `expertise`, `availability_status`, `assignment_status`, `notes`, `assigned_by`, `assigned_at`, `created_at`, `updated_at`, `notification_sent_at`, `notification_sent_by`) VALUES
(107, 58, NULL, 'TAP-2026-00047', 'RG-2026-001', 'Dr. Roberto M. Santos', 'rsantos@bestlink.edu.ph', 54, 'Artificial Intelligence / Machine Learning / Data Analytics / Data Analysis', 'Available', 'Assigned', 'Synced from fully approved research record.', 40, '2026-08-21 15:41:45', '2026-08-14 12:45:37', '2026-08-21 17:12:12', '2026-08-21 15:41:45', 40);

-- --------------------------------------------------------

--
-- Table structure for table `research_coordinator_assignments`
--

CREATE TABLE `research_coordinator_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED DEFAULT NULL,
  `proposal_id` int(10) UNSIGNED DEFAULT NULL,
  `title_approval_id` int(10) UNSIGNED DEFAULT NULL,
  `proposal_number` varchar(30) DEFAULT NULL,
  `group_number` varchar(40) DEFAULT NULL,
  `group_name` varchar(120) NOT NULL DEFAULT '',
  `research_title` varchar(255) NOT NULL DEFAULT '',
  `coordinator_user_id` int(10) UNSIGNED DEFAULT NULL,
  `coordinator_name` varchar(200) NOT NULL DEFAULT '',
  `coordinator_email` varchar(200) NOT NULL DEFAULT '',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `assigned_by` int(10) UNSIGNED DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `research_coordinator_assignments`
--

INSERT INTO `research_coordinator_assignments` (`id`, `research_group_id`, `proposal_id`, `title_approval_id`, `proposal_number`, `group_number`, `group_name`, `research_title`, `coordinator_user_id`, `coordinator_name`, `coordinator_email`, `status`, `assigned_by`, `assigned_at`, `created_at`, `updated_at`) VALUES
(35, 58, NULL, 47, 'TAP-2026-00047', 'RG-2026-001', 'Group 01', 'CRAD', 40, 'Mrs. Kris Guevarra', 'researchcoordinator@bestlink.edu.ph', 'Active', 3, '2026-08-21 15:44:34', '2026-08-21 15:44:34', '2026-08-21 15:44:34');

-- --------------------------------------------------------

--
-- Table structure for table `research_defense_schedules`
--

CREATE TABLE `research_defense_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED DEFAULT NULL,
  `proposal_id` int(10) UNSIGNED DEFAULT NULL,
  `proposal_number` varchar(30) DEFAULT NULL,
  `group_number` varchar(40) NOT NULL,
  `research_group` varchar(120) NOT NULL,
  `research_title` varchar(255) NOT NULL,
  `adviser_name` varchar(160) DEFAULT NULL,
  `panel_members` text DEFAULT NULL,
  `panel_chair` varchar(160) DEFAULT NULL,
  `venue` varchar(120) DEFAULT NULL,
  `venue_id` int(10) UNSIGNED DEFAULT NULL,
  `defense_datetime` datetime DEFAULT NULL,
  `defense_end_datetime` datetime DEFAULT NULL,
  `defense_type` varchar(40) NOT NULL DEFAULT 'Pre-Oral',
  `status` varchar(40) NOT NULL DEFAULT 'Ready for Scheduling',
  `recorded_by` int(10) UNSIGNED DEFAULT NULL,
  `finalized_by` int(10) UNSIGNED DEFAULT NULL,
  `finalized_at` datetime DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `academic_term_id` int(10) UNSIGNED DEFAULT NULL,
  `defense_attempt_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `research_groups`
--

CREATE TABLE `research_groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `proposal_id` int(10) UNSIGNED DEFAULT NULL,
  `title_approval_id` int(10) UNSIGNED DEFAULT NULL,
  `proposal_number` varchar(30) DEFAULT NULL,
  `group_number` varchar(40) NOT NULL,
  `group_name` varchar(40) NOT NULL DEFAULT '',
  `research_title` varchar(255) NOT NULL DEFAULT '',
  `college_dept` varchar(120) NOT NULL DEFAULT '',
  `adviser` varchar(120) NOT NULL DEFAULT '',
  `academic_year` varchar(20) NOT NULL DEFAULT '',
  `leader_name` varchar(120) NOT NULL DEFAULT '',
  `leader_id` varchar(40) NOT NULL DEFAULT '',
  `leader_email` varchar(120) NOT NULL DEFAULT '',
  `leader_contact` varchar(40) NOT NULL DEFAULT '',
  `status` varchar(40) NOT NULL DEFAULT 'Approved',
  `date_assigned` date NOT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `current_phase` varchar(80) NOT NULL DEFAULT 'Research Development',
  `current_phase_started_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `research_groups`
--

INSERT INTO `research_groups` (`id`, `proposal_id`, `title_approval_id`, `proposal_number`, `group_number`, `group_name`, `research_title`, `college_dept`, `adviser`, `academic_year`, `leader_name`, `leader_id`, `leader_email`, `leader_contact`, `status`, `date_assigned`, `created_by`, `created_at`, `current_phase`, `current_phase_started_at`) VALUES
(58, NULL, 47, 'TAP-2026-00047', 'RG-2026-001', 'Group 01', 'CRAD', 'College of Computer Studies', 'Dr. Roberto M. Santos', '2026-2027', 'Student User', 'S230000001', '', '', 'Approved', '2026-08-21', 3, '2026-08-21 07:39:18', 'First Semester Completion', NULL);

--
-- Triggers `research_groups`
--
DELIMITER $$
CREATE TRIGGER `trg_research_groups_panel_notifications_after_delete` AFTER DELETE ON `research_groups` FOR EACH ROW BEGIN
                DELETE FROM panel_assignment_notifications
                WHERE research_group_id = OLD.id;
            END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_research_groups_preoral_evals_after_delete` AFTER DELETE ON `research_groups` FOR EACH ROW BEGIN
                DELETE FROM preoral_defense_evaluations
                WHERE research_group_id = OLD.id;
            END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_research_groups_preoral_evaluations_after_delete` AFTER DELETE ON `research_groups` FOR EACH ROW BEGIN
                DELETE FROM preoral_defense_evaluations
                WHERE research_group_id = OLD.id;
            END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `research_group_terms`
--

CREATE TABLE `research_group_terms` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `academic_term_id` int(10) UNSIGNED NOT NULL,
  `source_group_term_id` int(10) UNSIGNED DEFAULT NULL,
  `enrollment_type` enum('New','Continuing','Carry-over','Repeat') NOT NULL DEFAULT 'Continuing',
  `starting_phase` varchar(80) NOT NULL DEFAULT 'Second Semester Intake',
  `current_phase` varchar(80) NOT NULL DEFAULT 'Second Semester Intake',
  `status` enum('Active','Completed','On Hold','Withdrawn') NOT NULL DEFAULT 'Active',
  `transition_confirmed_by` int(10) UNSIGNED DEFAULT NULL,
  `transition_confirmed_at` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `research_milestones`
--

CREATE TABLE `research_milestones` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_plan_id` int(10) UNSIGNED NOT NULL,
  `milestone_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `milestone_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `progress_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `weight` decimal(5,2) NOT NULL DEFAULT 1.00 COMMENT 'For weighted progress calculation',
  `status` enum('Not Started','In Progress','Submitted for Review','Revision Requested','Approved','Completed') NOT NULL DEFAULT 'Not Started',
  `start_date` date DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `researcher_notes` text DEFAULT NULL,
  `adviser_remarks` text DEFAULT NULL,
  `panel_remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `research_milestones`
--

INSERT INTO `research_milestones` (`id`, `research_plan_id`, `milestone_name`, `description`, `milestone_order`, `progress_percentage`, `weight`, `status`, `start_date`, `target_date`, `completed_at`, `researcher_notes`, `adviser_remarks`, `panel_remarks`, `created_at`, `updated_at`) VALUES
(43, 8, 'Chapter 1', 'Introduction and Background', 1, 0.00, 1.00, 'Submitted for Review', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 14:17:49', '2026-08-14 14:32:48'),
(44, 8, 'Chapter 2', 'Review of Related Literature', 2, 0.00, 1.00, 'Approved', NULL, NULL, NULL, NULL, 'Progress approved.', NULL, '2026-08-14 14:17:49', '2026-08-14 14:33:34'),
(45, 8, 'Chapter 3', 'Methodology', 3, 0.00, 1.00, 'Submitted for Review', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 14:17:49', '2026-08-14 14:31:46'),
(46, 8, 'System Development', 'System Implementation', 6, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 14:17:49', '2026-08-16 20:35:09'),
(47, 8, 'Testing', 'Testing and Quality Assurance', 7, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 14:17:49', '2026-08-16 20:35:09'),
(48, 8, 'Documentation', 'Final Documentation and Report', 8, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 14:17:49', '2026-08-16 20:35:09'),
(49, 9, 'Chapter 1', 'Introduction and Background', 1, 0.00, 1.00, 'In Progress', NULL, NULL, NULL, NULL, 'palitan mo', NULL, '2026-08-14 14:46:11', '2026-08-14 15:06:58'),
(50, 9, 'Chapter 2', 'Review of Related Literature', 2, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 14:46:11', '2026-08-14 14:46:11'),
(51, 9, 'Chapter 3', 'Methodology', 3, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 14:46:11', '2026-08-14 14:46:11'),
(52, 9, 'System Development', 'System Implementation', 6, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 14:46:11', '2026-08-16 20:35:09'),
(53, 9, 'Testing', 'Testing and Quality Assurance', 7, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 14:46:11', '2026-08-16 20:35:09'),
(54, 9, 'Documentation', 'Final Documentation and Report', 8, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 14:46:11', '2026-08-16 20:35:09'),
(55, 10, 'Chapter 1', 'Introduction and Background', 1, 100.00, 1.00, 'Approved', NULL, NULL, NULL, NULL, 'Progress approved.', NULL, '2026-08-14 15:36:58', '2026-08-14 15:49:19'),
(56, 10, 'Chapter 2', 'Review of Related Literature', 2, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 15:36:58', '2026-08-14 15:36:58'),
(57, 10, 'Chapter 3', 'Methodology', 3, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 15:36:58', '2026-08-14 15:36:58'),
(58, 10, 'System Development', 'System Implementation', 6, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 15:36:58', '2026-08-16 20:35:09'),
(59, 10, 'Testing', 'Testing and Quality Assurance', 7, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 15:36:58', '2026-08-16 20:35:09'),
(60, 10, 'Documentation', 'Final Documentation and Report', 8, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 15:36:58', '2026-08-16 20:35:09'),
(61, 11, 'Chapter 1', 'Introduction and Background', 1, 100.00, 1.00, 'Approved', NULL, NULL, NULL, NULL, 'done', NULL, '2026-08-14 17:34:42', '2026-08-14 17:35:40'),
(62, 11, 'Chapter 2', 'Review of Related Literature', 2, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 17:34:42', '2026-08-14 17:34:42'),
(63, 11, 'Chapter 3', 'Methodology', 3, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 17:34:42', '2026-08-14 17:34:42'),
(64, 11, 'System Development', 'System Implementation', 6, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 17:34:42', '2026-08-16 20:35:09'),
(65, 11, 'Testing', 'Testing and Quality Assurance', 7, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 17:34:42', '2026-08-16 20:35:09'),
(66, 11, 'Documentation', 'Final Documentation and Report', 8, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 17:34:42', '2026-08-16 20:35:09'),
(67, 12, 'Chapter 1', 'Introduction and Background', 1, 0.00, 1.00, 'Approved', NULL, NULL, NULL, NULL, 'Progress approved.', NULL, '2026-08-14 21:42:50', '2026-08-14 21:47:39'),
(68, 12, 'Chapter 2', 'Review of Related Literature', 2, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 21:42:50', '2026-08-14 21:42:50'),
(69, 12, 'Chapter 3', 'Methodology', 3, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 21:42:50', '2026-08-14 21:42:50'),
(70, 12, 'System Development', 'System Implementation', 6, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 21:42:50', '2026-08-16 20:35:09'),
(71, 12, 'Testing', 'Testing and Quality Assurance', 7, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 21:42:50', '2026-08-16 20:35:09'),
(72, 12, 'Documentation', 'Final Documentation and Report', 8, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-14 21:42:50', '2026-08-16 20:35:09'),
(73, 13, 'Chapter 1', 'Introduction and Background', 1, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-15 16:42:19', NULL, 'done', NULL, '2026-08-15 16:32:53', '2026-08-15 16:42:19'),
(74, 13, 'Chapter 2', 'Review of Related Literature', 2, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-15 16:43:22', NULL, 'Progress approved.', NULL, '2026-08-15 16:32:53', '2026-08-15 16:43:22'),
(75, 13, 'Chapter 3', 'Methodology', 3, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-15 16:43:28', NULL, 'Progress approved.', NULL, '2026-08-15 16:32:53', '2026-08-15 16:43:28'),
(76, 13, 'System Development', 'System Implementation', 6, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-15 16:32:53', '2026-08-16 20:35:09'),
(77, 13, 'Testing', 'Testing and Quality Assurance', 7, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-15 16:32:53', '2026-08-16 20:35:09'),
(78, 13, 'Documentation', 'Final Documentation and Report', 8, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-15 16:32:53', '2026-08-16 20:35:09'),
(79, 14, 'Chapter 1', 'Introduction and Background', 1, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-15 22:45:33', NULL, 'done', NULL, '2026-08-15 22:44:01', '2026-08-15 22:45:33'),
(80, 14, 'Chapter 2', 'Review of Related Literature', 2, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-15 22:45:27', NULL, 'done', NULL, '2026-08-15 22:44:01', '2026-08-15 22:45:27'),
(81, 14, 'Chapter 3', 'Methodology', 3, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-15 22:49:13', NULL, 'Progress approved.', NULL, '2026-08-15 22:44:01', '2026-08-15 22:49:13'),
(82, 14, 'System Development', 'System Implementation', 6, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-15 22:44:01', '2026-08-16 20:35:09'),
(83, 14, 'Testing', 'Testing and Quality Assurance', 7, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-15 22:44:01', '2026-08-16 20:35:09'),
(84, 14, 'Documentation', 'Final Documentation and Report', 8, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-15 22:44:01', '2026-08-16 20:35:09'),
(85, 15, 'Chapter 1', 'Introduction and Background', 1, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-16 15:00:10', NULL, 'Progress approved.', NULL, '2026-08-16 14:58:04', '2026-08-16 16:17:40'),
(86, 15, 'Chapter 2', 'Review of Related Literature', 2, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-16 15:00:23', NULL, 'Progress approved.', NULL, '2026-08-16 14:58:04', '2026-08-16 16:17:47'),
(87, 15, 'Chapter 3', 'Methodology', 3, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-16 15:00:28', NULL, 'Progress approved.', NULL, '2026-08-16 14:58:04', '2026-08-16 16:17:55'),
(88, 15, 'System Development', 'System Implementation', 6, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 14:58:04', '2026-08-16 20:35:09'),
(89, 15, 'Testing', 'Testing and Quality Assurance', 7, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 14:58:04', '2026-08-16 20:35:09'),
(90, 15, 'Documentation', 'Final Documentation and Report', 8, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 14:58:04', '2026-08-16 20:35:09'),
(91, 16, 'Chapter 1', 'Introduction and Background', 1, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 17:59:41', '2026-08-16 17:59:41'),
(92, 16, 'Chapter 2', 'Review of Related Literature', 2, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 17:59:41', '2026-08-16 17:59:41'),
(93, 16, 'Chapter 3', 'Methodology', 3, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 17:59:41', '2026-08-16 17:59:41'),
(94, 16, 'System Development', 'System Implementation', 6, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 17:59:41', '2026-08-16 20:35:09'),
(95, 16, 'Testing', 'Testing and Quality Assurance', 7, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 17:59:41', '2026-08-16 20:35:09'),
(96, 16, 'Documentation', 'Final Documentation and Report', 8, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 17:59:41', '2026-08-16 20:35:09'),
(97, 17, 'Chapter 1', 'Introduction and Background', 1, 0.00, 1.00, 'Submitted for Review', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 19:13:56', '2026-08-16 21:23:33'),
(98, 17, 'Chapter 2', 'Review of Related Literature', 2, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 19:13:56', '2026-08-16 19:13:56'),
(99, 17, 'Chapter 3', 'Methodology', 3, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 19:13:56', '2026-08-16 19:13:56'),
(100, 17, 'System Development', 'System Implementation', 6, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 19:13:56', '2026-08-16 20:30:47'),
(101, 17, 'Testing', 'Testing and Quality Assurance', 7, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 19:13:56', '2026-08-16 20:30:47'),
(102, 17, 'Documentation', 'Final Documentation and Report', 8, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 19:13:56', '2026-08-16 20:30:47'),
(103, 17, 'Chapter 4', 'Results / System Design and Development', 4, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:30:47', '2026-08-16 20:30:47'),
(104, 17, 'Chapter 5', 'Summary, Conclusions and Recommendations', 5, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:30:47', '2026-08-16 20:30:47'),
(125, 8, 'Chapter 4', 'Results / System Design and Development', 4, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(126, 8, 'Chapter 5', 'Summary, Conclusions and Recommendations', 5, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(127, 9, 'Chapter 4', 'Results / System Design and Development', 4, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(128, 9, 'Chapter 5', 'Summary, Conclusions and Recommendations', 5, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(129, 10, 'Chapter 4', 'Results / System Design and Development', 4, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(130, 10, 'Chapter 5', 'Summary, Conclusions and Recommendations', 5, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(131, 11, 'Chapter 4', 'Results / System Design and Development', 4, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(132, 11, 'Chapter 5', 'Summary, Conclusions and Recommendations', 5, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(133, 12, 'Chapter 4', 'Results / System Design and Development', 4, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(134, 12, 'Chapter 5', 'Summary, Conclusions and Recommendations', 5, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(135, 13, 'Chapter 4', 'Results / System Design and Development', 4, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(136, 13, 'Chapter 5', 'Summary, Conclusions and Recommendations', 5, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(137, 14, 'Chapter 4', 'Results / System Design and Development', 4, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(138, 14, 'Chapter 5', 'Summary, Conclusions and Recommendations', 5, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(139, 15, 'Chapter 4', 'Results / System Design and Development', 4, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(140, 15, 'Chapter 5', 'Summary, Conclusions and Recommendations', 5, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(141, 16, 'Chapter 4', 'Results / System Design and Development', 4, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(142, 16, 'Chapter 5', 'Summary, Conclusions and Recommendations', 5, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 20:35:09', '2026-08-16 20:35:09'),
(143, 18, 'Chapter 1', 'Introduction and Background', 1, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-16 21:41:59', NULL, 'Progress approved.', 'Approved by Panel.', '2026-08-16 21:28:11', '2026-08-16 22:52:24'),
(144, 18, 'Chapter 2', 'Review of Related Literature', 2, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-16 21:42:07', NULL, 'Progress approved.', 'Approved by Panel.', '2026-08-16 21:28:11', '2026-08-16 22:52:24'),
(145, 18, 'Chapter 3', 'Methodology', 3, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-16 21:42:13', NULL, 'Progress approved.', 'Approved by Panel.', '2026-08-16 21:28:11', '2026-08-16 22:52:24'),
(146, 18, 'Chapter 4', 'Results / System Design and Development', 4, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 21:28:11', '2026-08-16 21:28:11'),
(147, 18, 'Chapter 5', 'Summary, Conclusions and Recommendations', 5, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 21:28:11', '2026-08-16 21:28:11'),
(148, 18, 'System Development', 'System Implementation', 6, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 21:28:11', '2026-08-16 21:28:11'),
(149, 18, 'Testing', 'Testing and Quality Assurance', 7, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 21:28:11', '2026-08-16 21:28:11'),
(150, 18, 'Documentation', 'Final Documentation and Report', 8, 0.00, 1.00, 'Not Started', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-16 21:28:11', '2026-08-16 21:28:11'),
(151, 19, 'Chapter 1', 'Introduction and Background', 1, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-21 16:59:08', NULL, 'Progress approved.', '99\n99\n99', '2026-08-21 15:47:21', '2026-08-21 16:59:08'),
(152, 19, 'Chapter 2', 'Review of Related Literature', 2, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-21 16:59:08', NULL, 'Progress approved.', '99\n99\n99', '2026-08-21 15:47:21', '2026-08-21 16:59:08'),
(153, 19, 'Chapter 3', 'Methodology', 3, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-21 16:59:08', NULL, 'Progress approved.', '99\n99\n99', '2026-08-21 15:47:21', '2026-08-21 16:59:08'),
(154, 19, 'Chapter 4', 'Results / System Design and Development', 4, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-21 16:19:33', NULL, 'Progress approved.', NULL, '2026-08-21 15:47:21', '2026-08-21 16:19:33'),
(155, 19, 'Chapter 5', 'Summary, Conclusions and Recommendations', 5, 100.00, 1.00, 'Approved', NULL, NULL, '2026-08-21 16:19:42', NULL, 'Progress approved.', NULL, '2026-08-21 15:47:21', '2026-08-21 16:19:42'),
(156, 19, 'System Development', 'System Implementation', 6, 0.00, 1.00, 'Submitted for Review', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 15:47:21', '2026-08-21 17:00:00'),
(157, 19, 'Testing', 'Testing and Quality Assurance', 7, 100.00, 1.00, 'Submitted for Review', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 15:47:21', '2026-08-21 17:00:14'),
(158, 19, 'Documentation', 'Final Documentation and Report', 8, 100.00, 1.00, 'Submitted for Review', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 15:47:21', '2026-08-21 17:00:27');

-- --------------------------------------------------------

--
-- Table structure for table `research_panel_assignments`
--

CREATE TABLE `research_panel_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `defense_schedule_id` int(10) UNSIGNED DEFAULT NULL,
  `proposal_id` int(10) UNSIGNED DEFAULT NULL,
  `title_approval_id` int(10) UNSIGNED DEFAULT NULL,
  `proposal_number` varchar(30) DEFAULT NULL,
  `group_number` varchar(40) NOT NULL DEFAULT '',
  `research_title` varchar(255) NOT NULL DEFAULT '',
  `panel_user_id` int(10) UNSIGNED NOT NULL,
  `panel_name` varchar(150) NOT NULL DEFAULT '',
  `panel_email` varchar(190) NOT NULL DEFAULT '',
  `expertise` varchar(255) NOT NULL DEFAULT '',
  `availability_status` varchar(40) NOT NULL DEFAULT 'Pending',
  `assignment_status` varchar(40) NOT NULL DEFAULT 'Assigned',
  `defense_phase` varchar(60) NOT NULL DEFAULT 'Pre-Oral Defense',
  `assigned_by` int(10) UNSIGNED DEFAULT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `research_panel_assignments`
--

INSERT INTO `research_panel_assignments` (`id`, `research_group_id`, `defense_schedule_id`, `proposal_id`, `title_approval_id`, `proposal_number`, `group_number`, `research_title`, `panel_user_id`, `panel_name`, `panel_email`, `expertise`, `availability_status`, `assignment_status`, `defense_phase`, `assigned_by`, `assigned_at`, `created_at`, `updated_at`) VALUES
(13, 58, 29, NULL, 47, 'TAP-2026-00047', 'RG-2026-001', 'CRAD', 491, 'Dr. Jobert Valentino', 'jobertvalentino@bestlink.edu.ph', '', 'Available', 'Assigned', 'Pre-Oral Defense', 40, '2026-08-21 16:37:54', '2026-08-21 16:37:54', '2026-08-21 16:47:27'),
(14, 58, 29, NULL, 47, 'TAP-2026-00047', 'RG-2026-001', 'CRAD', 492, 'Dr. Jonathan Estrada', 'jonathanestrada@bestlink.edu.ph', '', 'Available', 'Assigned', 'Pre-Oral Defense', 40, '2026-08-21 16:37:54', '2026-08-21 16:37:54', '2026-08-21 16:47:27'),
(15, 58, 29, NULL, 47, 'TAP-2026-00047', 'RG-2026-001', 'CRAD', 493, 'Dr. Michelle Guevarra', 'michelleguevarra@bestlink.edu.ph', '', 'Available', 'Assigned', 'Pre-Oral Defense', 40, '2026-08-21 16:37:54', '2026-08-21 16:37:54', '2026-08-21 16:47:27');

-- --------------------------------------------------------

--
-- Table structure for table `research_plans`
--

CREATE TABLE `research_plans` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK to research_groups; nullable to preserve history if group is removed',
  `research_title` varchar(500) NOT NULL DEFAULT '',
  `group_number` varchar(40) NOT NULL DEFAULT '',
  `adviser_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK to sms2_db users (adviser)',
  `adviser_name` varchar(150) NOT NULL DEFAULT '',
  `adviser_email` varchar(190) NOT NULL DEFAULT '',
  `start_date` date DEFAULT NULL,
  `target_completion_date` date DEFAULT NULL,
  `current_stage` varchar(100) NOT NULL DEFAULT 'Planning',
  `overall_progress` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Auto-calculated from milestones',
  `status` enum('Active','Completed','On Hold','Cancelled') NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `research_plans`
--

INSERT INTO `research_plans` (`id`, `research_group_id`, `research_title`, `group_number`, `adviser_id`, `adviser_name`, `adviser_email`, `start_date`, `target_completion_date`, `current_stage`, `overall_progress`, `status`, `created_at`, `updated_at`) VALUES
(8, NULL, 'DEVELOPMENT OF AI ASSISTED', 'RG-2026-043', NULL, 'Dr. Roberto M. Santos', '', '2026-08-14', NULL, 'Planning', 0.00, 'Active', '2026-08-14 14:17:49', '2026-08-14 14:32:48'),
(9, NULL, 'AI TECHNOLOGY DOCUMENT ANALYSIS', 'RG-2026-045', NULL, 'Dr. Roberto M. Santos', '', '2026-08-14', NULL, 'Planning', 0.00, 'Active', '2026-08-14 14:46:11', '2026-08-14 15:06:58'),
(10, NULL, 'DEVELOPLENT OF AI ASSISTED', 'RG-2026-001', NULL, 'Dr. Roberto M. Santos', '', '2026-08-14', NULL, 'Planning', 16.67, 'Active', '2026-08-14 15:36:58', '2026-08-14 15:49:19'),
(11, NULL, 'DEVELOPMENT OF AI ANALYSIS DOCUMENT', 'RG-2026-001', NULL, 'Dr. Roberto M. Santos', '', '2026-08-14', NULL, 'Planning', 16.67, 'Active', '2026-08-14 17:34:42', '2026-08-14 17:35:40'),
(12, NULL, 'DEVELOPMENT OF AI ASSISTED DOCUMENT', 'RG-2026-001', NULL, 'Dr. Roberto M. Santos', '', '2026-08-14', NULL, 'Planning', 0.00, 'Active', '2026-08-14 21:42:50', '2026-08-14 21:47:39'),
(13, NULL, 'DEVELOPMENT OF AI ASSISTED OPEN AI GPT 5,5', 'RG-2026-001', NULL, 'Dr. Roberto M. Santos', '', '2026-08-15', NULL, 'Planning', 50.00, 'Active', '2026-08-15 16:32:53', '2026-08-15 16:43:28'),
(14, NULL, 'DEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS', 'RG-2026-001', 54, 'Dr. Roberto M. Santos', '', '2026-08-15', NULL, 'Planning', 50.00, 'Active', '2026-08-15 22:44:01', '2026-08-15 23:39:51'),
(15, NULL, 'DEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS', 'RG-2026-001', 54, 'Dr. Roberto M. Santos', '', '2026-08-16', NULL, 'Planning', 50.00, 'Active', '2026-08-16 14:58:04', '2026-08-16 16:17:55'),
(16, NULL, 'DEVELOMENT OF AI ANALYSIS DOCUMENT ASSISTED', 'RG-2026-001', 54, 'Dr. Roberto M. Santos', '', '2026-08-16', NULL, 'Planning', 0.00, 'Active', '2026-08-16 17:59:41', '2026-08-16 17:59:41'),
(17, NULL, 'DEVELOPMENT OF AI ASSISTED ANALYSIS', 'RG-2026-001', 54, 'Dr. Roberto M. Santos', '', '2026-08-16', NULL, 'Planning', 0.00, 'Active', '2026-08-16 19:13:56', '2026-08-16 21:23:33'),
(18, NULL, 'DEVELOPMENT OF AI ANALYSIS', 'RG-2026-001', 54, 'Dr. Roberto M. Santos', '', '2026-08-16', NULL, 'Planning', 37.50, 'Active', '2026-08-16 21:28:11', '2026-08-16 22:52:24'),
(19, 58, 'CRAD', 'RG-2026-001', 54, 'Dr. Roberto M. Santos', '', '2026-08-21', NULL, 'Planning', 87.50, 'Active', '2026-08-21 15:47:21', '2026-08-21 17:00:27');

-- --------------------------------------------------------

--
-- Table structure for table `research_progress_activity_logs`
--

CREATE TABLE `research_progress_activity_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_plan_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK to sms2_db users',
  `user_name` varchar(150) NOT NULL DEFAULT '',
  `user_role` varchar(40) NOT NULL DEFAULT '',
  `action` varchar(100) NOT NULL COMMENT 'milestone_created, progress_updated, feedback_added, etc',
  `entity_type` varchar(50) NOT NULL DEFAULT '' COMMENT 'milestone, progress_update, feedback, etc',
  `entity_id` int(10) UNSIGNED DEFAULT NULL,
  `old_value` text DEFAULT NULL COMMENT 'JSON or text of previous state',
  `new_value` text DEFAULT NULL COMMENT 'JSON or text of new state',
  `description` varchar(500) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `research_progress_activity_logs`
--

INSERT INTO `research_progress_activity_logs` (`id`, `research_plan_id`, `user_id`, `user_name`, `user_role`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `description`, `created_at`) VALUES
(67, 18, 9, 'Student User', 'student', 'progress_updated', 'progress_update', 37, NULL, NULL, 'Progress updated to 0%', '2026-08-16 21:28:17'),
(68, 18, 9, 'Student User', 'student', 'progress_updated', 'progress_update', 38, NULL, NULL, 'Progress updated to 0%', '2026-08-16 21:35:03'),
(69, 18, 9, 'Student User', 'student', 'progress_updated', 'progress_update', 39, NULL, NULL, 'Progress updated to 0%', '2026-08-16 21:41:45'),
(70, 18, 54, 'Dr. Roberto M. Santos', 'adviser', 'progress_approved', 'feedback', 29, NULL, NULL, 'Adviser approved progress', '2026-08-16 21:41:59'),
(71, 18, 54, 'Dr. Roberto M. Santos', 'adviser', 'progress_approved', 'feedback', 30, NULL, NULL, 'Adviser approved progress', '2026-08-16 21:42:07'),
(72, 18, 54, 'Dr. Roberto M. Santos', 'adviser', 'progress_approved', 'feedback', 31, NULL, NULL, 'Adviser approved progress', '2026-08-16 21:42:13'),
(73, 19, 9, 'Student User', 'student', 'progress_updated', 'progress_update', 40, NULL, NULL, 'Progress updated to 0%', '2026-08-21 15:48:26'),
(74, 19, 9, 'Student User', 'student', 'progress_updated', 'progress_update', 41, NULL, NULL, 'Progress updated to 0%', '2026-08-21 15:49:12'),
(75, 19, 9, 'Student User', 'student', 'progress_updated', 'progress_update', 42, NULL, NULL, 'Progress updated to 0%', '2026-08-21 15:49:51'),
(76, 19, 54, 'Dr. Roberto M. Santos', 'adviser', 'progress_approved', 'feedback', 32, NULL, NULL, 'Adviser approved progress', '2026-08-21 15:53:36'),
(77, 19, 9, 'Student User', 'student', 'progress_updated', 'progress_update', 43, NULL, NULL, 'Progress updated to 91%', '2026-08-21 16:12:15'),
(78, 19, 9, 'Student User', 'student', 'progress_updated', 'progress_update', 44, NULL, NULL, 'Progress updated to 0%', '2026-08-21 16:12:36'),
(79, 19, 54, 'Dr. Roberto M. Santos', 'adviser', 'progress_approved', 'feedback', 33, NULL, NULL, 'Adviser approved progress', '2026-08-21 16:18:56'),
(80, 19, 54, 'Dr. Roberto M. Santos', 'adviser', 'progress_approved', 'feedback', 34, NULL, NULL, 'Adviser approved progress', '2026-08-21 16:19:14'),
(81, 19, 54, 'Dr. Roberto M. Santos', 'adviser', 'progress_approved', 'feedback', 35, NULL, NULL, 'Adviser approved progress', '2026-08-21 16:19:33'),
(82, 19, 54, 'Dr. Roberto M. Santos', 'adviser', 'progress_approved', 'feedback', 36, NULL, NULL, 'Adviser approved progress', '2026-08-21 16:19:42'),
(83, 19, 9, 'Student User', 'student', 'progress_updated', 'progress_update', 45, NULL, NULL, 'Progress updated to 0%', '2026-08-21 17:00:00'),
(84, 19, 9, 'Student User', 'student', 'progress_updated', 'progress_update', 46, NULL, NULL, 'Progress updated to 100%', '2026-08-21 17:00:14'),
(85, 19, 9, 'Student User', 'student', 'progress_updated', 'progress_update', 47, NULL, NULL, 'Progress updated to 100%', '2026-08-21 17:00:27');

-- --------------------------------------------------------

--
-- Table structure for table `research_progress_attachments`
--

CREATE TABLE `research_progress_attachments` (
  `id` int(10) UNSIGNED NOT NULL,
  `progress_update_id` int(10) UNSIGNED NOT NULL,
  `file_name` varchar(300) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(100) NOT NULL DEFAULT '',
  `file_size` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Bytes',
  `uploaded_by` int(10) UNSIGNED NOT NULL COMMENT 'FK to sms2_db users',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `research_progress_attachments`
--

INSERT INTO `research_progress_attachments` (`id`, `progress_update_id`, `file_name`, `file_path`, `file_type`, `file_size`, `uploaded_by`, `created_at`) VALUES
(1, 10, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-14 15:04:33'),
(2, 11, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-14 15:06:49'),
(3, 12, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-14 15:06:58'),
(4, 13, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-14 15:43:06'),
(5, 14, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-14 15:46:56'),
(6, 15, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-14 15:49:11'),
(7, 16, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-14 17:35:29'),
(8, 17, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-14 21:44:56'),
(9, 18, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-15 16:38:35'),
(10, 19, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-15 16:42:40'),
(11, 20, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-15 16:42:50'),
(12, 21, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-15 16:43:16'),
(13, 22, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-15 22:44:47'),
(14, 23, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-15 22:44:59'),
(15, 24, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-15 22:45:11'),
(16, 25, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-16 14:58:45'),
(17, 26, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-16 14:58:56'),
(18, 27, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-16 14:59:06'),
(19, 28, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-16 14:59:38'),
(20, 29, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-16 14:59:57'),
(21, 30, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-16 15:00:05'),
(22, 31, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-16 16:17:10'),
(23, 32, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-16 16:17:23'),
(24, 33, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-16 16:17:33'),
(25, 34, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-16 21:08:05'),
(26, 35, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-16 21:22:52'),
(27, 36, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-16 21:23:33'),
(28, 37, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-16 21:28:17'),
(29, 38, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-16 21:35:03'),
(30, 39, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'storage/uploads/demo/OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 350940, 9, '2026-08-16 21:41:45'),
(31, 40, 'Online-Activity-August-18-2026-.docx 389173289127383871.docx', 'storage/uploads/demo/Online-Activity-August-18-2026-.docx 389173289127383871.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 20470, 9, '2026-08-21 15:48:26'),
(32, 41, 'index.docx', 'storage/uploads/demo/index.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 10129, 9, '2026-08-21 15:49:12'),
(33, 42, 'index.docx', 'storage/uploads/demo/index.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 10129, 9, '2026-08-21 15:49:51'),
(34, 43, 'index.docx', 'storage/uploads/demo/index.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 10129, 9, '2026-08-21 16:12:15'),
(35, 44, 'index.docx', 'storage/uploads/demo/index.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 10129, 9, '2026-08-21 16:12:36'),
(36, 45, 'index (1).docx', 'storage/uploads/demo/index (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 10129, 9, '2026-08-21 17:00:00'),
(37, 46, 'index (1).docx', 'storage/uploads/demo/index (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 10129, 9, '2026-08-21 17:00:14'),
(38, 47, 'index (1).docx', 'storage/uploads/demo/index (1).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 10129, 9, '2026-08-21 17:00:27');

-- --------------------------------------------------------

--
-- Table structure for table `research_progress_feedback`
--

CREATE TABLE `research_progress_feedback` (
  `id` int(10) UNSIGNED NOT NULL,
  `progress_update_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Can be NULL for general milestone feedback',
  `milestone_id` int(10) UNSIGNED DEFAULT NULL,
  `research_plan_id` int(10) UNSIGNED NOT NULL,
  `adviser_user_id` int(10) UNSIGNED NOT NULL,
  `adviser_name` varchar(200) NOT NULL DEFAULT '',
  `feedback_text` text NOT NULL,
  `new_milestone_status` varchar(60) DEFAULT NULL,
  `submission_token` varchar(64) DEFAULT NULL,
  `feedback_type` enum('Comment','Revision Request','Approval','Progress Approved') NOT NULL DEFAULT 'Comment',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `research_progress_feedback`
--

INSERT INTO `research_progress_feedback` (`id`, `progress_update_id`, `milestone_id`, `research_plan_id`, `adviser_user_id`, `adviser_name`, `feedback_text`, `new_milestone_status`, `submission_token`, `feedback_type`, `created_at`, `updated_at`) VALUES
(7, 7, 44, 8, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', '817f56711833eca3353b39fa45b35410', 'Progress Approved', '2026-08-14 14:33:34', '2026-08-14 14:33:34'),
(8, 10, 49, 9, 54, 'Dr. Roberto M. Santos', 'Palitan mo', NULL, '7712a12e71e0cd36667b6306275b965f', 'Comment', '2026-08-14 15:05:55', '2026-08-14 15:05:55'),
(9, 10, 49, 9, 54, 'Dr. Roberto M. Santos', 'palitan mo', 'Revision Requested', 'bce06f71bee90f35596880f84627f4e6', 'Revision Request', '2026-08-14 15:06:01', '2026-08-14 15:06:01'),
(10, 13, 55, 10, 54, 'Dr. Roberto M. Santos', 'revise mo', 'Revision Requested', '0755a79d8bdeed5b023ae31bfd0ef8d8', 'Revision Request', '2026-08-14 15:46:18', '2026-08-14 15:46:18'),
(11, 15, 55, 10, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', 'e1c895aea49dfe8445570e9d1de0cf65', 'Progress Approved', '2026-08-14 15:49:19', '2026-08-14 15:49:19'),
(12, 16, 61, 11, 54, 'Dr. Roberto M. Santos', 'done', 'Approved', 'be76219549bee9eabfffc575339f7f50', 'Progress Approved', '2026-08-14 17:35:40', '2026-08-14 17:35:40'),
(13, 17, 67, 12, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', '3851796a7729edf19fae881d285874f2', 'Progress Approved', '2026-08-14 21:47:39', '2026-08-14 21:47:39'),
(14, 18, 73, 13, 54, 'Dr. Roberto M. Santos', 'done', 'Approved', 'cca91b25fa67b2232d77d731f0ef1adb', 'Progress Approved', '2026-08-15 16:42:19', '2026-08-15 16:42:19'),
(15, 21, 74, 13, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', 'c9f89dfe324a80d84cfd552ba45280e8', 'Progress Approved', '2026-08-15 16:43:22', '2026-08-15 16:43:22'),
(16, 20, 75, 13, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', '9cc6c4f7b3612c62cf4fca6ff41b1d72', 'Progress Approved', '2026-08-15 16:43:28', '2026-08-15 16:43:28'),
(17, 23, 80, 14, 54, 'Dr. Roberto M. Santos', 'done', 'Approved', 'c5a0d0054230ff1a8b774a3717021701', 'Progress Approved', '2026-08-15 22:45:27', '2026-08-15 22:45:27'),
(18, 22, 79, 14, 54, 'Dr. Roberto M. Santos', 'done', 'Approved', '20cdeda8bb4399761a5524f12dd348bd', 'Progress Approved', '2026-08-15 22:45:33', '2026-08-15 22:45:33'),
(19, 24, 81, 14, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', '814036937e8cf29a4df57e3aeea2d8a3', 'Progress Approved', '2026-08-15 22:49:13', '2026-08-15 22:49:13'),
(20, 28, 85, 15, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', '0940ce87e5f0d2bfaef23e0bc3936f97', 'Progress Approved', '2026-08-16 15:00:10', '2026-08-16 15:00:10'),
(21, 29, 86, 15, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', 'f4fcbb604896cdb665c5bf62f95ade5c', 'Progress Approved', '2026-08-16 15:00:23', '2026-08-16 15:00:23'),
(22, 30, 87, 15, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', '371d752d9613b324f90b3c5256df449e', 'Progress Approved', '2026-08-16 15:00:28', '2026-08-16 15:00:28'),
(23, 30, 87, 15, 54, 'Dr. Roberto M. Santos', 'REVISE', 'Revision Requested', 'd1eef63e77e187e26798326e728ac26e', 'Revision Request', '2026-08-16 16:14:24', '2026-08-16 16:14:24'),
(24, 29, 86, 15, 54, 'Dr. Roberto M. Santos', 'REVISE', 'Revision Requested', '3a32c903095eba6fb182e8f6f4425aba', 'Revision Request', '2026-08-16 16:14:28', '2026-08-16 16:14:28'),
(25, 28, 85, 15, 54, 'Dr. Roberto M. Santos', 'REVISE', 'Revision Requested', '7d0a18a9ae60d6c863812615561fa9be', 'Revision Request', '2026-08-16 16:14:31', '2026-08-16 16:14:31'),
(26, 31, 85, 15, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', '82f2903c4e0b1d7c9202474b7014391c', 'Progress Approved', '2026-08-16 16:17:40', '2026-08-16 16:17:40'),
(27, 32, 86, 15, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', 'ea4fabe6e614c6b7d407f592d74729c8', 'Progress Approved', '2026-08-16 16:17:47', '2026-08-16 16:17:47'),
(28, 33, 87, 15, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', '109f6a8e453c420f00ab6e191a0ce305', 'Progress Approved', '2026-08-16 16:17:55', '2026-08-16 16:17:55'),
(29, 37, 143, 18, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', '495b2bc2fd2219057b3c1cd0359a8a67', 'Progress Approved', '2026-08-16 21:41:59', '2026-08-16 21:41:59'),
(30, 38, 144, 18, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', 'ccce77ca022bbaab8e637ea2829880c8', 'Progress Approved', '2026-08-16 21:42:07', '2026-08-16 21:42:07'),
(31, 39, 145, 18, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', '25aae8c02b9abae00d5cba664bb3f477', 'Progress Approved', '2026-08-16 21:42:13', '2026-08-16 21:42:13'),
(32, 42, 153, 19, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', 'a4061b8dc429b3a409eeb16a46fff6b7', 'Progress Approved', '2026-08-21 15:53:36', '2026-08-21 15:53:36'),
(33, 40, 151, 19, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', '66e576eac7247f1b19a8eac85e2b98b8', 'Progress Approved', '2026-08-21 16:18:56', '2026-08-21 16:18:56'),
(34, 41, 152, 19, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', 'a566455225d72230557bece8a5ff68cb', 'Progress Approved', '2026-08-21 16:19:14', '2026-08-21 16:19:14'),
(35, 43, 154, 19, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', '2a90153095ce233f91995fbd579247e5', 'Progress Approved', '2026-08-21 16:19:33', '2026-08-21 16:19:33'),
(36, 44, 155, 19, 54, 'Dr. Roberto M. Santos', 'Progress approved.', 'Approved', '2e2808cd8e0e0f4e96ad2a09bae5bf21', 'Progress Approved', '2026-08-21 16:19:42', '2026-08-21 16:19:42');

-- --------------------------------------------------------

--
-- Table structure for table `research_progress_notifications`
--

CREATE TABLE `research_progress_notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `recipient_user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK to sms2_db.users.id (NULL = role-based)',
  `recipient_email` varchar(200) NOT NULL DEFAULT '',
  `recipient_role` varchar(40) NOT NULL DEFAULT '',
  `batch_key` varchar(100) NOT NULL DEFAULT '' COMMENT 'Unique key per event for deduplication',
  `notification_type` varchar(60) NOT NULL DEFAULT 'progress_update',
  `title` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `related_entity_type` varchar(60) NOT NULL DEFAULT '',
  `related_entity_id` int(10) UNSIGNED DEFAULT NULL,
  `action_url` varchar(500) DEFAULT NULL,
  `status` enum('unread','read') NOT NULL DEFAULT 'unread',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `research_progress_notifications`
--

INSERT INTO `research_progress_notifications` (`id`, `recipient_user_id`, `recipient_email`, `recipient_role`, `batch_key`, `notification_type`, `title`, `body`, `related_entity_type`, `related_entity_id`, `action_url`, `status`, `created_at`, `read_at`) VALUES
(1, 9, '', 'student', 'approval:1', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 1, NULL, 'read', '2026-08-14 13:15:45', '2026-08-14 16:02:09'),
(2, 9, '', 'student', 'approval:2', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 2, NULL, 'read', '2026-08-14 13:40:03', '2026-08-14 16:02:43'),
(3, 9, '', 'student', 'approval:3', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 3, NULL, 'read', '2026-08-14 13:48:02', '2026-08-14 16:02:41'),
(4, 9, '', 'student', 'approval:4', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 4, NULL, 'read', '2026-08-14 13:51:49', '2026-08-14 16:01:44'),
(5, 9, '', 'student', 'approval:5', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 5, NULL, 'read', '2026-08-14 13:51:52', '2026-08-14 16:02:39'),
(6, 9, '', 'student', 'approval:6', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 6, NULL, 'read', '2026-08-14 13:53:50', '2026-08-14 16:02:38'),
(7, 9, '', 'student', 'approval:7', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 7, NULL, 'read', '2026-08-14 14:33:34', '2026-08-14 16:02:36'),
(8, 9, '', 'student', 'feedback:8', 'adviser_feedback', 'New Adviser Feedback', 'Your adviser commented on your progress update', 'feedback', 8, '/SMS2_system/modules/crad/modules/student-portal/pages/research-progress.php', 'read', '2026-08-14 15:05:55', '2026-08-14 16:01:55'),
(9, 9, '', 'student', 'revision:9', 'revision_requested', 'Revision Requested', 'Your adviser requested revisions on your progress update', 'feedback', 9, NULL, 'read', '2026-08-14 15:06:01', '2026-08-14 16:02:04'),
(10, 9, '', 'student', 'revision:10', 'revision_requested', 'Revision Requested', 'Your adviser requested revisions on your progress update', 'feedback', 10, NULL, 'read', '2026-08-14 15:46:18', '2026-08-14 16:01:53'),
(11, 9, '', 'student', 'approval:11', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 11, NULL, 'read', '2026-08-14 15:49:19', '2026-08-14 16:01:46'),
(12, 9, '', 'student', 'approval:12', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 12, NULL, 'unread', '2026-08-14 17:35:40', NULL),
(13, 9, '', 'student', 'approval:13', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 13, NULL, 'unread', '2026-08-14 21:47:39', NULL),
(14, 9, '', 'student', 'approval:14', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 14, NULL, 'unread', '2026-08-15 16:42:19', NULL),
(15, 9, '', 'student', 'approval:15', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 15, NULL, 'unread', '2026-08-15 16:43:22', NULL),
(16, 9, '', 'student', 'approval:16', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 16, NULL, 'unread', '2026-08-15 16:43:28', NULL),
(17, 9, '', 'student', 'approval:17', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 17, NULL, 'unread', '2026-08-15 22:45:27', NULL),
(18, 9, '', 'student', 'approval:18', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 18, NULL, 'unread', '2026-08-15 22:45:33', NULL),
(19, 9, '', 'student', 'approval:19', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 19, NULL, 'unread', '2026-08-15 22:49:13', NULL),
(26, 9, '', 'student', 'approval:20', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 20, NULL, 'unread', '2026-08-16 15:00:10', NULL),
(27, 9, '', 'student', 'approval:21', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 21, NULL, 'unread', '2026-08-16 15:00:23', NULL),
(28, 9, '', 'student', 'approval:22', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 22, NULL, 'unread', '2026-08-16 15:00:28', NULL),
(29, 9, '', 'student', 'revision:23', 'revision_requested', 'Revision Requested', 'Your adviser requested revisions on your progress update', 'feedback', 23, NULL, 'unread', '2026-08-16 16:14:24', NULL),
(30, 9, '', 'student', 'revision:24', 'revision_requested', 'Revision Requested', 'Your adviser requested revisions on your progress update', 'feedback', 24, NULL, 'unread', '2026-08-16 16:14:28', NULL),
(31, 9, '', 'student', 'revision:25', 'revision_requested', 'Revision Requested', 'Your adviser requested revisions on your progress update', 'feedback', 25, NULL, 'unread', '2026-08-16 16:14:31', NULL),
(32, 9, '', 'student', 'approval:26', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 26, NULL, 'unread', '2026-08-16 16:17:40', NULL),
(33, 9, '', 'student', 'approval:27', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 27, NULL, 'unread', '2026-08-16 16:17:47', NULL),
(34, 9, '', 'student', 'approval:28', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 28, NULL, 'unread', '2026-08-16 16:17:55', NULL),
(35, 9, '', 'student', 'approval:29', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 29, NULL, 'unread', '2026-08-16 21:41:59', NULL),
(36, 9, '', 'student', 'approval:30', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 30, NULL, 'unread', '2026-08-16 21:42:07', NULL),
(37, 9, '', 'student', 'approval:31', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 31, NULL, 'unread', '2026-08-16 21:42:13', NULL),
(38, 9, '', 'student', 'approval:32', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 32, NULL, 'unread', '2026-08-21 15:53:36', NULL),
(39, 9, '', 'student', 'approval:33', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 33, NULL, 'unread', '2026-08-21 16:18:56', NULL),
(40, 9, '', 'student', 'approval:34', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 34, NULL, 'unread', '2026-08-21 16:19:14', NULL),
(41, 9, '', 'student', 'approval:35', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 35, NULL, 'unread', '2026-08-21 16:19:33', NULL),
(42, 9, '', 'student', 'approval:36', 'progress_approved', 'Progress Approved', 'Your adviser approved your progress update', 'feedback', 36, NULL, 'unread', '2026-08-21 16:19:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `research_progress_updates`
--

CREATE TABLE `research_progress_updates` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_plan_id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `milestone_id` int(10) UNSIGNED DEFAULT NULL,
  `submitted_by_user_id` int(10) UNSIGNED NOT NULL,
  `submitted_by_name` varchar(200) NOT NULL DEFAULT '',
  `update_title` varchar(300) NOT NULL,
  `accomplishments` text DEFAULT NULL,
  `problems_blockers` text DEFAULT NULL,
  `next_planned_activity` text DEFAULT NULL,
  `attachment_path` varchar(500) DEFAULT NULL,
  `attachment_original_name` varchar(300) DEFAULT NULL,
  `submission_token` varchar(64) DEFAULT NULL,
  `previous_progress` decimal(5,2) DEFAULT NULL,
  `new_progress` decimal(5,2) NOT NULL,
  `milestone_status` varchar(60) NOT NULL DEFAULT 'In Progress',
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `research_progress_updates`
--

INSERT INTO `research_progress_updates` (`id`, `research_plan_id`, `research_group_id`, `milestone_id`, `submitted_by_user_id`, `submitted_by_name`, `update_title`, `accomplishments`, `problems_blockers`, `next_planned_activity`, `attachment_path`, `attachment_original_name`, `submission_token`, `previous_progress`, `new_progress`, `milestone_status`, `submitted_at`, `updated_at`) VALUES
(6, 8, 43, 43, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED', 'asd', 'asdasd', 'asasd', NULL, NULL, 'abdea8bf390883c3d8245261f87a3f73', 0.00, 0.00, 'In Progress', '2026-08-14 14:31:03', '2026-08-14 14:31:03'),
(7, 8, 43, 44, 9, 'Student User', 'asdas', 'dasd', 'asd', 'asd', NULL, NULL, '07ad355d7ba621068160797fc7f80915', 0.00, 0.00, 'Submitted for Review', '2026-08-14 14:31:21', '2026-08-14 14:31:21'),
(8, 8, 43, 45, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED', 'asdasd', 'asd', 'as', NULL, NULL, '722049913609fc5a1287afc52f385c3a', 0.00, 0.00, 'Submitted for Review', '2026-08-14 14:31:46', '2026-08-14 14:31:46'),
(9, 8, 43, 43, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED', 'asdas', 'asdas', 'das', NULL, NULL, 'e509c2f91d27435ddae5bb52a0ee16fa', 0.00, 0.00, 'Submitted for Review', '2026-08-14 14:32:48', '2026-08-14 14:32:48'),
(10, 9, 45, 49, 9, 'Student User', 'AI TECHNOLOGY DOCUMENT ANALYSIS', 'adsdas', 'dasd', 'asdas', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '74d22b3ad4991f39bd6bf5a7a932ba63', 0.00, 0.00, 'Revision Requested', '2026-08-14 15:04:33', '2026-08-22 15:36:53'),
(11, 9, 45, 49, 9, 'Student User', 'AI TECHNOLOGY DOCUMENT ANALYSIS', 'asdas', 'asdasd', 'asdas', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '133bb9b5ff70ff84adda28ebf66ebddb', 0.00, 0.00, 'In Progress', '2026-08-14 15:06:49', '2026-08-22 15:36:53'),
(12, 9, 45, 49, 9, 'Student User', 'AI TECHNOLOGY DOCUMENT ANALYSIS', 'asdasd', 'asd', 'asd', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'cbda864be8fdf7bd8d91893f47fcf366', 0.00, 0.00, 'In Progress', '2026-08-14 15:06:58', '2026-08-22 15:36:53'),
(13, 10, 49, 55, 9, 'Student User', 'DEVELOPLENT OF AI ASSISTED', 'asdas', 'asdas', 'adas', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '434710c1c02c4d925ac327d3456ac25f', 0.00, 0.00, 'Revision Requested', '2026-08-14 15:43:06', '2026-08-22 15:36:53'),
(14, 10, 49, 55, 9, 'Student User', 'DEVELOPLENT OF AI ASSISTED', 'adas', 'dasdas', 'ddas', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'c39e64c299020e120585f492af84f52d', 0.00, 100.00, 'In Progress', '2026-08-14 15:46:56', '2026-08-22 15:36:53'),
(15, 10, 49, 55, 9, 'Student User', 'DEVELOPLENT OF AI ASSISTED', 'Done', '', 'Done', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '70bbd49e9b9a347394f4acb7aebbff78', 100.00, 100.00, 'Approved', '2026-08-14 15:49:11', '2026-08-22 15:36:53'),
(16, 11, 50, 61, 9, 'Student User', 'DEVELOPMENT OF AI ANALYSIS DOCUMENT', 'DONE', 'N/A', 'DEVELOPMENT OF AI ANALYSIS DOCUMENT', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'd4c6d42ea0575c38ef7e22ba39dfa210', 0.00, 100.00, 'Approved', '2026-08-14 17:35:29', '2026-08-22 15:36:53'),
(17, 12, 51, 67, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED DOCUMENT', 'dasdas', 'asdas', 'das', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'eebd2bed160e4557d1ba59c4d791587c', 0.00, 0.00, 'Approved', '2026-08-14 21:44:56', '2026-08-22 15:36:53'),
(18, 13, 52, 73, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED OPEN AI GPT 5,5', 'asdas', '', 'done', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '376eb29995ad97d950178ebbdc1646c8', 0.00, 0.00, 'Approved', '2026-08-15 16:38:35', '2026-08-22 15:36:53'),
(19, 13, 52, 74, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED OPEN AI GPT 5,5', 'adasdasd', '', 'adas', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '636bd24a62f74796bbd4e8b94c5088e7', 0.00, 0.00, 'Not Started', '2026-08-15 16:42:40', '2026-08-22 15:36:53'),
(20, 13, 52, 75, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED OPEN AI GPT 5,5', 'asdasd', '', 'asd', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'ccae172cf424df7c8d61cb539cf4a896', 0.00, 0.00, 'Approved', '2026-08-15 16:42:50', '2026-08-22 15:36:53'),
(21, 13, 52, 74, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED OPEN AI GPT 5,5', 'dasas', '', 'asdas', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '8a336c77741dfa747ca6ab61daeeb59c', 0.00, 0.00, 'Approved', '2026-08-15 16:43:16', '2026-08-22 15:36:53'),
(22, 14, 53, 79, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS', 'asdasd', '', 'asd', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '0102b3961db8182862129affee46e2a8', 0.00, 0.00, 'Approved', '2026-08-15 22:44:47', '2026-08-22 15:36:53'),
(23, 14, 53, 80, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS', 'sadas', '', 'asd', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'd5ecc285e47082cb35311c0bab0f8d9c', 0.00, 0.00, 'Approved', '2026-08-15 22:44:59', '2026-08-22 15:36:53'),
(24, 14, 53, 81, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS', 'adsas', '', 'asdas', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'a7f599e3c76991e840566e06001e7bfa', 0.00, 0.00, 'Approved', '2026-08-15 22:45:11', '2026-08-22 15:36:53'),
(25, 15, 54, 85, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS', 'ASDAS', '', 'DSAD', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '9b4479183af2fee0cbaab5998498c645', 0.00, 0.00, 'Not Started', '2026-08-16 14:58:45', '2026-08-22 15:36:53'),
(26, 15, 54, 86, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS', 'ASDAS', '', 'DAS', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'cc65626a55bb356bd9cfe0e7575f26ca', 0.00, 0.00, 'Not Started', '2026-08-16 14:58:56', '2026-08-22 15:36:53'),
(27, 15, 54, 87, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS', 'ASDAS', '', 'DAS', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '32b9eace2e4234dceae6cd33a24e5d3c', 0.00, 0.00, 'Not Started', '2026-08-16 14:59:06', '2026-08-22 15:36:53'),
(28, 15, 54, 85, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS', 'SADAS', '', 'ADSAS', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '63739b355001ec5c020be60c6bbcd6f4', 0.00, 0.00, 'Revision Requested', '2026-08-16 14:59:38', '2026-08-22 15:36:53'),
(29, 15, 54, 86, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS', 'DSASAD', '', 'ASD', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'a48d32b977c127b569d84f21523ae102', 0.00, 0.00, 'Revision Requested', '2026-08-16 14:59:57', '2026-08-22 15:36:53'),
(30, 15, 54, 87, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS', 'DASAS', '', 'DAS', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'a02f2bbc638faa67aa28c606b62e44e4', 0.00, 0.00, 'Revision Requested', '2026-08-16 15:00:05', '2026-08-22 15:36:53'),
(31, 15, 54, 85, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS', 'dasd', '', 'asdas', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '6c5fe00033ada7248aab6f6101bf0641', 100.00, 100.00, 'Approved', '2026-08-16 16:17:10', '2026-08-22 15:36:53'),
(32, 15, 54, 86, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS', 'asdas', '', 'adas', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'e08acff3b6ae10a654f81dd0fa93e899', 100.00, 100.00, 'Approved', '2026-08-16 16:17:23', '2026-08-22 15:36:53'),
(33, 15, 54, 87, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED DOCUMENT ANALYSIS', 'dasdas', '', 'asdas', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'd1f284d854fb7a142d71b9b36e870798', 100.00, 100.00, 'Approved', '2026-08-16 16:17:33', '2026-08-22 15:36:53'),
(34, 17, 56, 97, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED ANALYSIS', 'asddas', '', 'dasd', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '3bea8d288edb2901ef857b304d692a99', 0.00, 0.00, 'Submitted for Review', '2026-08-16 21:08:05', '2026-08-22 15:36:53'),
(35, 17, 56, 97, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED ANALYSIS', 'sdasdas', '', 'dasas', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', 'd988bf51eceecff4419c47bfd331911b', 0.00, 0.00, 'Submitted for Review', '2026-08-16 21:22:52', '2026-08-22 15:36:53'),
(36, 17, 56, 97, 9, 'Student User', 'DEVELOPMENT OF AI ASSISTED ANALYSIS', 'asdas', '', 'das', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '34c3b322bee2db3e1b9af22e531e82f9', 0.00, 0.00, 'Submitted for Review', '2026-08-16 21:23:33', '2026-08-22 15:36:53'),
(37, 18, 57, 143, 9, 'Student User', 'DEVELOPMENT OF AI ANALYSIS', 'asdas', '', 'asd', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '3e873e6b08a90fba3a0a032e2ac8cb6c', 0.00, 0.00, 'Approved', '2026-08-16 21:28:17', '2026-08-22 15:36:53'),
(38, 18, 57, 144, 9, 'Student User', 'DEVELOPMENT OF AI ANALYSIS', 'dasda', '', 'asd', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '4c7b467c19ed707287146deba56b09fd', 0.00, 0.00, 'Approved', '2026-08-16 21:35:03', '2026-08-22 15:36:53'),
(39, 18, 57, 145, 9, 'Student User', 'DEVELOPMENT OF AI ANALYSIS', 'asd', '', 'adasdas', NULL, 'OJT_PRACTICUM_1_NARRATIVE_REPORT (1) (1).docx', '40a2c2330f35d25a0b3d42501189cbdd', 0.00, 0.00, 'Approved', '2026-08-16 21:41:45', '2026-08-22 15:36:53'),
(40, 19, 58, 151, 9, 'Student User', 'CRAD', 'das', 'ddasda', 'sdas', NULL, 'Online-Activity-August-18-2026-.docx 389173289127383871.docx', 'b2378f28befbbaf4548060bda15723fa', 0.00, 0.00, 'Approved', '2026-08-21 15:48:26', '2026-08-22 15:36:53'),
(41, 19, 58, 152, 9, 'Student User', 'CRAD', 'asdasd', 'asdasda', 'sdsadsa', NULL, 'index.docx', '99f7331504bf8d6a43941a8f66bfc385', 0.00, 0.00, 'Approved', '2026-08-21 15:49:12', '2026-08-22 15:36:53'),
(42, 19, 58, 153, 9, 'Student User', 'CRAD', 'sdasdsa', 'sasdasa', 'dsasdsadsa', NULL, 'index.docx', 'bbc3f3b4991bf19d287d4b4eda7da705', 0.00, 0.00, 'Approved', '2026-08-21 15:49:51', '2026-08-22 15:36:53'),
(43, 19, 58, 154, 9, 'Student User', 'CRAD', 'aS', 'asAS', 'asASA', NULL, 'index.docx', '5f7388a8f6f82fcbf8451c9c76f6cb27', 0.00, 91.00, 'Approved', '2026-08-21 16:12:15', '2026-08-22 15:36:53'),
(44, 19, 58, 155, 9, 'Student User', 'CRAD', 'asAS', 'asA', 'Sas', NULL, 'index.docx', 'aa32e0151b25c380c59da2aedf4c83b5', 0.00, 0.00, 'Approved', '2026-08-21 16:12:36', '2026-08-22 15:36:53'),
(45, 19, 58, 156, 9, 'Student User', 'CRAD', 'ss', 'ss', 'ss', NULL, 'index (1).docx', '4423d8e2d69c0e47c4559b674bee1834', 0.00, 0.00, 'Submitted for Review', '2026-08-21 17:00:00', '2026-08-22 15:36:53'),
(46, 19, 58, 157, 9, 'Student User', 'CRAD', 'ss', 'ss', 'ss', NULL, 'index (1).docx', 'c1391e28e0574a804baa505e27460851', 0.00, 100.00, 'Submitted for Review', '2026-08-21 17:00:14', '2026-08-22 15:36:53'),
(47, 19, 58, 158, 9, 'Student User', 'CRAD', 'ss', 'sss', 's', NULL, 'index (1).docx', 'edb8c2c748ccbfabdeec71fdc4a43f6e', 0.00, 100.00, 'Submitted for Review', '2026-08-21 17:00:27', '2026-08-22 15:36:53');

-- --------------------------------------------------------

--
-- Table structure for table `research_proposals`
--

CREATE TABLE `research_proposals` (
  `id` int(10) UNSIGNED NOT NULL,
  `ref_code` varchar(30) NOT NULL COMMENT 'Auto-generated reference e.g. CRD-2026-00001',
  `proposal_number` varchar(30) DEFAULT NULL COMMENT 'Official number generated after approved proposal registration',
  `research_title` varchar(500) NOT NULL,
  `program_course` varchar(200) NOT NULL,
  `year_section` varchar(100) NOT NULL,
  `college_department` varchar(200) NOT NULL,
  `research_adviser` varchar(200) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `rep_name` varchar(200) NOT NULL,
  `rep_id` varchar(50) NOT NULL,
  `rep_email` varchar(200) NOT NULL,
  `rep_contact` varchar(20) NOT NULL,
  `status` enum('Submitted','In Progress','Panel Assigned','Approved','Returned') NOT NULL DEFAULT 'Submitted',
  `progress` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT 'Progress % shown in tracking',
  `date_submitted` date NOT NULL,
  `approved_at` datetime DEFAULT NULL COMMENT 'Date/time when tracking proposal was approved',
  `registered_at` datetime DEFAULT NULL COMMENT 'Date/time when approved proposal received official proposal number',
  `registration_status` enum('Pending','Registered') NOT NULL DEFAULT 'Pending',
  `signature_data` mediumtext DEFAULT NULL COMMENT 'Base64 PNG of representative signature',
  `submitted_by_user` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK to sms2_db users (optional)',
  `notes` text DEFAULT NULL COMMENT 'CRAD officer notes',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `research_revision_cycles`
--

CREATE TABLE `research_revision_cycles` (
  `id` int(10) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `defense_schedule_id` int(10) UNSIGNED NOT NULL,
  `official_result` varchar(60) NOT NULL DEFAULT 'APPROVED WITH REVISION',
  `revision_status` varchar(60) NOT NULL DEFAULT 'Needs Revision',
  `opened_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `defense_attempt_id` int(10) UNSIGNED DEFAULT NULL,
  `revision_submission_id` int(10) UNSIGNED DEFAULT NULL,
  `compliance_verified_by` int(10) UNSIGNED DEFAULT NULL,
  `compliance_verified_at` datetime DEFAULT NULL,
  `compliance_remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `research_venues`
--

CREATE TABLE `research_venues` (
  `id` int(10) UNSIGNED NOT NULL,
  `venue_name` varchar(160) NOT NULL,
  `capacity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `venue_type` varchar(80) NOT NULL DEFAULT '',
  `status` varchar(40) NOT NULL DEFAULT 'Available',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `research_venues`
--

INSERT INTO `research_venues` (`id`, `venue_name`, `capacity`, `venue_type`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'CRAD Conference Room', 30, 'Conference Room', 'Available', NULL, '2026-08-10 13:50:31', '2026-08-10 13:50:31'),
(2, 'Research Room 1', 25, 'Research Room', 'Available', NULL, '2026-08-10 13:50:31', '2026-08-10 13:50:31'),
(3, 'Research Room 2', 25, 'Research Room', 'Available', NULL, '2026-08-10 13:50:31', '2026-08-10 13:50:31'),
(4, 'AVR Room', 100, 'Auditorium', 'Available', NULL, '2026-08-10 13:50:31', '2026-08-10 14:20:53'),
(5, 'Computer Laboratory 1', 40, 'Laboratory', 'Available', NULL, '2026-08-10 13:50:31', '2026-08-10 13:50:31');

-- --------------------------------------------------------

--
-- Table structure for table `research_workflow_events`
--

CREATE TABLE `research_workflow_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `research_group_id` int(10) UNSIGNED NOT NULL,
  `academic_term_id` int(10) UNSIGNED DEFAULT NULL,
  `group_term_id` int(10) UNSIGNED DEFAULT NULL,
  `event_type` varchar(80) NOT NULL,
  `from_phase` varchar(80) DEFAULT NULL,
  `to_phase` varchar(80) DEFAULT NULL,
  `entity_type` varchar(80) DEFAULT NULL,
  `entity_id` int(10) UNSIGNED DEFAULT NULL,
  `actor_user_id` int(10) UNSIGNED DEFAULT NULL,
  `actor_name` varchar(150) NOT NULL DEFAULT '',
  `remarks` text DEFAULT NULL,
  `metadata_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `title_approvals`
--

CREATE TABLE `title_approvals` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` varchar(50) NOT NULL DEFAULT '',
  `student_user_id` int(10) UNSIGNED DEFAULT NULL,
  `student_name` varchar(200) NOT NULL DEFAULT '',
  `submission_date` date NOT NULL,
  `department` varchar(200) NOT NULL DEFAULT '',
  `proposed_title` varchar(500) NOT NULL DEFAULT '',
  `discipline_cluster` varchar(200) NOT NULL DEFAULT '',
  `primary_sdg` varchar(120) NOT NULL DEFAULT '',
  `research_agenda` varchar(300) NOT NULL DEFAULT '',
  `sdg_justification` text NOT NULL,
  `members_json` longtext NOT NULL,
  `adviser_name` varchar(200) NOT NULL DEFAULT '',
  `adviser_email` varchar(200) NOT NULL DEFAULT '',
  `coordinator_name` varchar(200) NOT NULL DEFAULT '',
  `proposal_number` varchar(30) DEFAULT NULL,
  `status` enum('Pending','Reviewed','Approved','Returned') NOT NULL DEFAULT 'Pending',
  `adviser_remarks` text DEFAULT NULL,
  `adviser_signature_data` mediumtext DEFAULT NULL,
  `coordinator_status` varchar(30) NOT NULL DEFAULT 'Not Ready',
  `coordinator_remarks` text DEFAULT NULL,
  `coordinator_screening_json` text DEFAULT NULL,
  `coordinator_signature_data` mediumtext DEFAULT NULL,
  `coordinator_reviewed_at` datetime DEFAULT NULL,
  `crad_status` varchar(30) NOT NULL DEFAULT 'Not Ready',
  `crad_signature_data` mediumtext DEFAULT NULL,
  `crad_reviewed_at` datetime DEFAULT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `title_approvals`
--

INSERT INTO `title_approvals` (`id`, `student_id`, `student_user_id`, `student_name`, `submission_date`, `department`, `proposed_title`, `discipline_cluster`, `primary_sdg`, `research_agenda`, `sdg_justification`, `members_json`, `adviser_name`, `adviser_email`, `coordinator_name`, `proposal_number`, `status`, `adviser_remarks`, `adviser_signature_data`, `coordinator_status`, `coordinator_remarks`, `coordinator_screening_json`, `coordinator_signature_data`, `coordinator_reviewed_at`, `crad_status`, `crad_signature_data`, `crad_reviewed_at`, `sent_at`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(47, 'S230000001', 9, 'Student User', '2026-08-21', 'College of Computer Studies', 'CRAD', 'Engineering, Information Technology, and Computing', 'SDG 9 — Industry, Innovation and Infrastructure', 'Science, Technology, Digital Transformation, and Innovation', 'CRAD', '[[\"User, Student A.\",\"BSIT 4101\",\"OR-2667659\"]]', 'Dr. Roberto M. Santos', 'rsantos@bestlink.edu.ph', 'Mrs. Kris Guevarra', 'TAP-2026-00047', 'Approved', NULL, NULL, 'Approved', NULL, '{\"agenda_alignment\":\"yes\",\"feasible_original\":\"yes\",\"ethical_sdg\":\"yes\"}', NULL, '2026-08-21 15:25:04', 'Approved', NULL, '2026-08-21 15:35:42', '2026-08-21 15:17:11', '2026-08-21 15:22:39', '2026-08-21 15:17:11', '2026-08-22 15:36:53');

--
-- Triggers `title_approvals`
--
DELIMITER $$
CREATE TRIGGER `trg_title_approvals_after_delete` AFTER DELETE ON `title_approvals` FOR EACH ROW BEGIN
            UPDATE research_adviser_assignments a
               SET a.assignment_status = 'Pending'
             WHERE a.assignment_status = 'Assigned'
               AND (
                    (OLD.proposal_number IS NOT NULL
                     AND OLD.proposal_number <> ''
                     AND a.proposal_number = OLD.proposal_number)
                 OR (a.research_group_id IS NOT NULL
                     AND a.research_group_id IN (
                        SELECT g.id
                        FROM research_groups g
                        WHERE g.title_approval_id = OLD.id
                     ))
                 OR (a.group_number IS NOT NULL
                     AND a.group_number <> ''
                     AND a.group_number IN (
                        SELECT g2.group_number
                        FROM research_groups g2
                        WHERE g2.title_approval_id = OLD.id
                     ))
               );
        END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_terms`
--
ALTER TABLE `academic_terms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_academic_term_code` (`term_code`),
  ADD UNIQUE KEY `uniq_academic_year_semester` (`academic_year`,`semester`),
  ADD KEY `idx_academic_term_status` (`status`);

--
-- Indexes for table `chapter_evaluations`
--
ALTER TABLE `chapter_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_chapter_evaluation_submission` (`submission_id`),
  ADD KEY `idx_chapter_eval_evaluator` (`evaluator_user_id`),
  ADD KEY `idx_chapter_eval_group` (`research_group_id`),
  ADD KEY `idx_chapter_eval_created` (`created_at`);

--
-- Indexes for table `chapter_evaluation_notifications`
--
ALTER TABLE `chapter_evaluation_notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_chapter_notification_event` (`event_key`),
  ADD KEY `idx_chapter_notification_recipient` (`recipient_user_id`,`recipient_role`,`recipient_email`),
  ADD KEY `idx_chapter_notification_submission` (`submission_id`),
  ADD KEY `idx_chapter_notification_created` (`created_at`);

--
-- Indexes for table `chapter_submissions`
--
ALTER TABLE `chapter_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_chapter_version` (`research_group_id`,`chapter_number`,`version_number`),
  ADD UNIQUE KEY `uniq_chapter_token` (`submission_token`),
  ADD KEY `idx_chapter_status` (`status`),
  ADD KEY `idx_chapter_group` (`research_group_id`),
  ADD KEY `idx_chapter_student` (`submitted_by_user`),
  ADD KEY `idx_chapter_updated` (`updated_at`);

--
-- Indexes for table `chapter_submission_history`
--
ALTER TABLE `chapter_submission_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chapter_history_submission` (`submission_id`),
  ADD KEY `idx_chapter_history_group` (`research_group_id`),
  ADD KEY `idx_chapter_history_created` (`created_at`);

--
-- Indexes for table `crad_data_integrity_archive`
--
ALTER TABLE `crad_data_integrity_archive`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_integrity_archive_entity` (`entity_type`,`entity_id`,`reason`);

--
-- Indexes for table `crad_schema_migrations`
--
ALTER TABLE `crad_schema_migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_crad_migration_key` (`migration_key`);

--
-- Indexes for table `defense_attempts`
--
ALTER TABLE `defense_attempts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_defense_attempt` (`research_group_id`,`academic_term_id`,`defense_type`,`attempt_number`),
  ADD UNIQUE KEY `uniq_defense_schedule_attempt` (`defense_schedule_id`),
  ADD KEY `idx_defense_attempt_status` (`status`),
  ADD KEY `idx_defense_attempt_term` (`academic_term_id`);

--
-- Indexes for table `defense_official_results`
--
ALTER TABLE `defense_official_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_official_result_attempt` (`defense_attempt_id`),
  ADD KEY `idx_official_result_group` (`research_group_id`),
  ADD KEY `idx_official_result_value` (`official_result`);

--
-- Indexes for table `defense_revision_items`
--
ALTER TABLE `defense_revision_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_revision_item_attempt` (`defense_attempt_id`),
  ADD KEY `idx_revision_item_group_status` (`research_group_id`,`status`),
  ADD KEY `idx_revision_item_evaluation` (`defense_attempt_id`,`source_evaluation_id`);

--
-- Indexes for table `defense_revision_submissions`
--
ALTER TABLE `defense_revision_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_revision_submission_version` (`defense_attempt_id`,`version_number`),
  ADD UNIQUE KEY `uniq_revision_submission_token` (`submission_token`),
  ADD KEY `idx_revision_submission_group` (`research_group_id`),
  ADD KEY `idx_revision_submission_status` (`status`);

--
-- Indexes for table `final_defense_evaluations`
--
ALTER TABLE `final_defense_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_final_panel_submission` (`defense_schedule_id`,`panel_user_id`),
  ADD KEY `idx_final_group` (`research_group_id`),
  ADD KEY `idx_final_panel` (`panel_user_id`),
  ADD KEY `idx_final_status` (`status`),
  ADD KEY `idx_final_attempt` (`defense_attempt_id`);

--
-- Indexes for table `final_defense_recommendations`
--
ALTER TABLE `final_defense_recommendations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_fdr_group` (`research_group_id`),
  ADD KEY `idx_fdr_status` (`status`),
  ADD KEY `idx_fdr_term` (`academic_term_id`),
  ADD KEY `idx_fdr_readiness` (`readiness_check_id`),
  ADD KEY `idx_fdr_final_draft` (`final_draft_submission_id`);

--
-- Indexes for table `final_draft_reviews`
--
ALTER TABLE `final_draft_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_final_draft_review_submission` (`submission_id`,`is_current`),
  ADD KEY `idx_final_draft_review_group` (`research_group_id`),
  ADD KEY `idx_final_draft_review_adviser` (`adviser_user_id`);

--
-- Indexes for table `final_draft_submissions`
--
ALTER TABLE `final_draft_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_final_draft_version` (`research_group_id`,`academic_term_id`,`version_number`),
  ADD UNIQUE KEY `uniq_final_draft_token` (`submission_token`),
  ADD KEY `idx_final_draft_status` (`status`),
  ADD KEY `idx_final_draft_group` (`research_group_id`);

--
-- Indexes for table `final_manuscript_approvals`
--
ALTER TABLE `final_manuscript_approvals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_fma_group` (`research_group_id`),
  ADD KEY `idx_fma_submission` (`manuscript_submission_id`),
  ADD KEY `idx_fma_attempt` (`defense_attempt_id`),
  ADD KEY `idx_fma_revision_submission` (`revision_submission_id`);

--
-- Indexes for table `final_manuscript_approval_history`
--
ALTER TABLE `final_manuscript_approval_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_fmah_group_sequence` (`research_group_id`,`decision_sequence`),
  ADD KEY `idx_fmah_approval` (`approval_id`),
  ADD KEY `idx_fmah_group_status` (`research_group_id`,`decision_status`),
  ADD KEY `idx_fmah_evidence` (`manuscript_submission_id`,`revision_submission_id`);

--
-- Indexes for table `final_readiness_checks`
--
ALTER TABLE `final_readiness_checks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_readiness_group_term` (`research_group_id`,`academic_term_id`),
  ADD KEY `idx_readiness_status` (`overall_status`),
  ADD KEY `idx_readiness_current` (`research_group_id`,`academic_term_id`,`is_current`),
  ADD KEY `idx_readiness_draft` (`final_draft_submission_id`),
  ADD KEY `idx_readiness_review` (`adviser_review_id`);

--
-- Indexes for table `grant_applications`
--
ALTER TABLE `grant_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_ga_reference` (`reference_number`),
  ADD UNIQUE KEY `uniq_ga_token` (`submission_token`),
  ADD KEY `idx_ga_opportunity` (`grant_opportunity_id`),
  ADD KEY `idx_ga_group` (`research_group_id`),
  ADD KEY `idx_ga_status` (`status`),
  ADD KEY `idx_ga_submitted` (`submitted_at`);

--
-- Indexes for table `grant_opportunities`
--
ALTER TABLE `grant_opportunities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_go_status` (`status`),
  ADD KEY `idx_go_deadline` (`application_deadline`),
  ADD KEY `idx_go_created_by` (`created_by_user_id`);

--
-- Indexes for table `grant_proposal_versions`
--
ALTER TABLE `grant_proposal_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_gpv_application_version` (`grant_application_id`,`version_number`),
  ADD KEY `idx_gpv_application` (`grant_application_id`);

--
-- Indexes for table `grant_status_history`
--
ALTER TABLE `grant_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_gsh_application` (`grant_application_id`),
  ADD KEY `idx_gsh_status` (`to_status`),
  ADD KEY `idx_gsh_created` (`created_at`);

--
-- Indexes for table `manuscript_evaluations`
--
ALTER TABLE `manuscript_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_meval_submission` (`submission_id`),
  ADD KEY `idx_meval_group` (`research_group_id`);

--
-- Indexes for table `manuscript_submissions`
--
ALTER TABLE `manuscript_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_manuscript_version` (`research_group_id`,`version_number`),
  ADD UNIQUE KEY `uniq_manuscript_token` (`submission_token`),
  ADD KEY `idx_manuscript_status` (`status`),
  ADD KEY `idx_manuscript_group` (`research_group_id`),
  ADD KEY `idx_manuscript_term_purpose` (`academic_term_id`,`purpose`),
  ADD KEY `idx_manuscript_supersedes` (`supersedes_submission_id`);

--
-- Indexes for table `panel_assignment_notifications`
--
ALTER TABLE `panel_assignment_notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_panel_assignment_notification` (`event_key`),
  ADD KEY `idx_panel_notification_recipient` (`recipient_user_id`,`recipient_role`,`recipient_email`),
  ADD KEY `idx_panel_notification_created` (`created_at`);

--
-- Indexes for table `panel_member_availability`
--
ALTER TABLE `panel_member_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_panel_availability_user` (`panel_user_id`),
  ADD KEY `idx_panel_availability_status` (`availability_status`);

--
-- Indexes for table `preoral_defense_evaluations`
--
ALTER TABLE `preoral_defense_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_preoral_panel_submission` (`defense_schedule_id`,`panel_user_id`),
  ADD KEY `idx_preoral_group` (`research_group_id`),
  ADD KEY `idx_preoral_panel` (`panel_user_id`),
  ADD KEY `idx_preoral_status` (`status`);

--
-- Indexes for table `proposal_documents`
--
ALTER TABLE `proposal_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pd_proposal` (`proposal_id`);

--
-- Indexes for table `proposal_drafts`
--
ALTER TABLE `proposal_drafts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_proposal_draft_student_type` (`student_id`,`form_type`);

--
-- Indexes for table `proposal_members`
--
ALTER TABLE `proposal_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proposal` (`proposal_id`);

--
-- Indexes for table `proposal_status_logs`
--
ALTER TABLE `proposal_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_psl_proposal` (`proposal_id`);

--
-- Indexes for table `publications`
--
ALTER TABLE `publications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pub_group` (`research_group_id`),
  ADD KEY `idx_pub_status` (`status`),
  ADD KEY `idx_pub_approval` (`final_approval_id`),
  ADD KEY `idx_pub_access` (`access_level`),
  ADD KEY `idx_pub_repository_identifier` (`repository_identifier`);

--
-- Indexes for table `research_adviser_assignments`
--
ALTER TABLE `research_adviser_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_raa_adviser_identity` (`adviser_email`,`adviser_name`),
  ADD KEY `idx_raa_group` (`research_group_id`),
  ADD KEY `idx_raa_proposal` (`proposal_id`),
  ADD KEY `idx_raa_group_number` (`group_number`),
  ADD KEY `idx_raa_status` (`assignment_status`),
  ADD KEY `idx_raa_user` (`adviser_user_id`);

--
-- Indexes for table `research_coordinator_assignments`
--
ALTER TABLE `research_coordinator_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_rca_group_number` (`group_number`),
  ADD UNIQUE KEY `uniq_rca_group_coordinator` (`research_group_id`,`coordinator_user_id`),
  ADD KEY `idx_rca_group` (`research_group_id`),
  ADD KEY `idx_rca_title_approval` (`title_approval_id`),
  ADD KEY `idx_rca_status` (`status`);

--
-- Indexes for table `research_defense_schedules`
--
ALTER TABLE `research_defense_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rds_proposal_number` (`proposal_number`),
  ADD KEY `idx_rds_status` (`status`),
  ADD KEY `idx_rds_proposal_id` (`proposal_id`),
  ADD KEY `idx_rds_venue_time` (`venue_id`,`defense_datetime`,`defense_end_datetime`),
  ADD KEY `idx_rds_group_time` (`research_group_id`,`defense_datetime`,`defense_end_datetime`),
  ADD KEY `idx_rds_group_number` (`group_number`),
  ADD KEY `idx_rds_term_type` (`academic_term_id`,`defense_type`),
  ADD KEY `idx_rds_attempt` (`defense_attempt_id`);

--
-- Indexes for table `research_groups`
--
ALTER TABLE `research_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_number` (`group_number`),
  ADD UNIQUE KEY `proposal_id` (`proposal_id`),
  ADD UNIQUE KEY `title_approval_id` (`title_approval_id`),
  ADD KEY `idx_rg_proposal_number` (`proposal_number`),
  ADD KEY `idx_rg_current_phase` (`current_phase`);

--
-- Indexes for table `research_group_terms`
--
ALTER TABLE `research_group_terms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_group_academic_term` (`research_group_id`,`academic_term_id`),
  ADD KEY `idx_group_term_phase` (`current_phase`),
  ADD KEY `idx_group_term_status` (`status`),
  ADD KEY `idx_group_term_term` (`academic_term_id`);

--
-- Indexes for table `research_milestones`
--
ALTER TABLE `research_milestones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_rm_plan_name` (`research_plan_id`,`milestone_name`),
  ADD KEY `idx_rm_plan` (`research_plan_id`),
  ADD KEY `idx_rm_status` (`status`),
  ADD KEY `idx_rm_sequence` (`research_plan_id`,`milestone_order`);

--
-- Indexes for table `research_panel_assignments`
--
ALTER TABLE `research_panel_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_panel_assignment_phase` (`research_group_id`,`panel_user_id`,`defense_phase`),
  ADD KEY `idx_panel_assignment_group` (`research_group_id`),
  ADD KEY `idx_panel_assignment_user` (`panel_user_id`),
  ADD KEY `idx_panel_assignment_status` (`assignment_status`),
  ADD KEY `idx_panel_assignment_schedule` (`defense_schedule_id`);

--
-- Indexes for table `research_plans`
--
ALTER TABLE `research_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_rp_group` (`research_group_id`),
  ADD KEY `idx_rp_group_number` (`group_number`),
  ADD KEY `idx_rp_adviser` (`adviser_id`),
  ADD KEY `idx_rp_status` (`status`);

--
-- Indexes for table `research_progress_activity_logs`
--
ALTER TABLE `research_progress_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rpal_plan` (`research_plan_id`),
  ADD KEY `idx_rpal_user` (`user_id`),
  ADD KEY `idx_rpal_action` (`action`),
  ADD KEY `idx_rpal_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_rpal_created` (`created_at`);

--
-- Indexes for table `research_progress_attachments`
--
ALTER TABLE `research_progress_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rpa_update` (`progress_update_id`),
  ADD KEY `idx_rpa_uploaded` (`uploaded_by`);

--
-- Indexes for table `research_progress_feedback`
--
ALTER TABLE `research_progress_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rpf_update` (`progress_update_id`),
  ADD KEY `idx_rpf_milestone` (`milestone_id`),
  ADD KEY `idx_rpf_plan` (`research_plan_id`),
  ADD KEY `idx_rpf_created` (`created_at`),
  ADD KEY `idx_rpf_adviser` (`adviser_user_id`),
  ADD KEY `idx_rpf_token` (`submission_token`),
  ADD KEY `idx_rpf_update_adviser` (`progress_update_id`,`adviser_user_id`),
  ADD KEY `idx_rpf_plan_type` (`research_plan_id`,`feedback_type`);

--
-- Indexes for table `research_progress_notifications`
--
ALTER TABLE `research_progress_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rpn_recipient_user` (`recipient_user_id`),
  ADD KEY `idx_rpn_recipient_email` (`recipient_email`),
  ADD KEY `idx_rpn_recipient_role` (`recipient_role`),
  ADD KEY `idx_rpn_batch_key` (`batch_key`),
  ADD KEY `idx_rpn_status` (`status`),
  ADD KEY `idx_rpn_created` (`created_at`);

--
-- Indexes for table `research_progress_updates`
--
ALTER TABLE `research_progress_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rpu_plan` (`research_plan_id`),
  ADD KEY `idx_rpu_milestone` (`milestone_id`),
  ADD KEY `idx_rpu_researcher` (`submitted_by_user_id`),
  ADD KEY `idx_rpu_submitted` (`submitted_at`),
  ADD KEY `idx_rpu_group` (`research_group_id`),
  ADD KEY `idx_rpu_token` (`submission_token`),
  ADD KEY `idx_rpu_group_milestone` (`research_group_id`,`milestone_id`),
  ADD KEY `idx_rpu_plan_submitted` (`research_plan_id`,`submitted_at`);

--
-- Indexes for table `research_proposals`
--
ALTER TABLE `research_proposals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ref_code` (`ref_code`),
  ADD UNIQUE KEY `proposal_number` (`proposal_number`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_dept` (`college_department`(50)),
  ADD KEY `idx_submitted` (`date_submitted`);

--
-- Indexes for table `research_revision_cycles`
--
ALTER TABLE `research_revision_cycles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_rrc_schedule` (`defense_schedule_id`),
  ADD KEY `idx_rrc_group` (`research_group_id`),
  ADD KEY `idx_rrc_status` (`revision_status`),
  ADD KEY `idx_rrc_attempt` (`defense_attempt_id`),
  ADD KEY `idx_rrc_submission` (`revision_submission_id`);

--
-- Indexes for table `research_venues`
--
ALTER TABLE `research_venues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_research_venue_name` (`venue_name`),
  ADD KEY `idx_research_venues_status` (`status`);

--
-- Indexes for table `research_workflow_events`
--
ALTER TABLE `research_workflow_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_workflow_group_created` (`research_group_id`,`created_at`),
  ADD KEY `idx_workflow_term` (`academic_term_id`),
  ADD KEY `idx_workflow_event_type` (`event_type`);

--
-- Indexes for table `title_approvals`
--
ALTER TABLE `title_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ta_student_id` (`student_id`),
  ADD KEY `idx_ta_adviser_email` (`adviser_email`(100)),
  ADD KEY `idx_ta_status` (`status`),
  ADD KEY `idx_ta_sent_at` (`sent_at`),
  ADD KEY `idx_ta_proposal_number` (`proposal_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_terms`
--
ALTER TABLE `academic_terms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chapter_evaluations`
--
ALTER TABLE `chapter_evaluations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `chapter_evaluation_notifications`
--
ALTER TABLE `chapter_evaluation_notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `chapter_submissions`
--
ALTER TABLE `chapter_submissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `chapter_submission_history`
--
ALTER TABLE `chapter_submission_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `crad_data_integrity_archive`
--
ALTER TABLE `crad_data_integrity_archive`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `crad_schema_migrations`
--
ALTER TABLE `crad_schema_migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `defense_attempts`
--
ALTER TABLE `defense_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defense_official_results`
--
ALTER TABLE `defense_official_results`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defense_revision_items`
--
ALTER TABLE `defense_revision_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defense_revision_submissions`
--
ALTER TABLE `defense_revision_submissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `final_defense_evaluations`
--
ALTER TABLE `final_defense_evaluations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `final_defense_recommendations`
--
ALTER TABLE `final_defense_recommendations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `final_draft_reviews`
--
ALTER TABLE `final_draft_reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `final_draft_submissions`
--
ALTER TABLE `final_draft_submissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `final_manuscript_approvals`
--
ALTER TABLE `final_manuscript_approvals`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `final_manuscript_approval_history`
--
ALTER TABLE `final_manuscript_approval_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `final_readiness_checks`
--
ALTER TABLE `final_readiness_checks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grant_applications`
--
ALTER TABLE `grant_applications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grant_opportunities`
--
ALTER TABLE `grant_opportunities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grant_proposal_versions`
--
ALTER TABLE `grant_proposal_versions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grant_status_history`
--
ALTER TABLE `grant_status_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `manuscript_evaluations`
--
ALTER TABLE `manuscript_evaluations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `manuscript_submissions`
--
ALTER TABLE `manuscript_submissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `panel_assignment_notifications`
--
ALTER TABLE `panel_assignment_notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `panel_member_availability`
--
ALTER TABLE `panel_member_availability`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `preoral_defense_evaluations`
--
ALTER TABLE `preoral_defense_evaluations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `proposal_documents`
--
ALTER TABLE `proposal_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT for table `proposal_drafts`
--
ALTER TABLE `proposal_drafts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `proposal_members`
--
ALTER TABLE `proposal_members`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `proposal_status_logs`
--
ALTER TABLE `proposal_status_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `publications`
--
ALTER TABLE `publications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `research_adviser_assignments`
--
ALTER TABLE `research_adviser_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `research_coordinator_assignments`
--
ALTER TABLE `research_coordinator_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `research_defense_schedules`
--
ALTER TABLE `research_defense_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `research_groups`
--
ALTER TABLE `research_groups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `research_group_terms`
--
ALTER TABLE `research_group_terms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `research_milestones`
--
ALTER TABLE `research_milestones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT for table `research_panel_assignments`
--
ALTER TABLE `research_panel_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `research_plans`
--
ALTER TABLE `research_plans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `research_progress_activity_logs`
--
ALTER TABLE `research_progress_activity_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `research_progress_attachments`
--
ALTER TABLE `research_progress_attachments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `research_progress_feedback`
--
ALTER TABLE `research_progress_feedback`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `research_progress_notifications`
--
ALTER TABLE `research_progress_notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `research_progress_updates`
--
ALTER TABLE `research_progress_updates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `research_proposals`
--
ALTER TABLE `research_proposals`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `research_revision_cycles`
--
ALTER TABLE `research_revision_cycles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `research_venues`
--
ALTER TABLE `research_venues`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10682;

--
-- AUTO_INCREMENT for table `research_workflow_events`
--
ALTER TABLE `research_workflow_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `title_approvals`
--
ALTER TABLE `title_approvals`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `proposal_documents`
--
ALTER TABLE `proposal_documents`
  ADD CONSTRAINT `fk_pd_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `research_proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `proposal_members`
--
ALTER TABLE `proposal_members`
  ADD CONSTRAINT `fk_pm_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `research_proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `proposal_status_logs`
--
ALTER TABLE `proposal_status_logs`
  ADD CONSTRAINT `fk_psl_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `research_proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `research_adviser_assignments`
--
ALTER TABLE `research_adviser_assignments`
  ADD CONSTRAINT `fk_raa_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `research_proposals` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `research_coordinator_assignments`
--
ALTER TABLE `research_coordinator_assignments`
  ADD CONSTRAINT `fk_rca_title_approval` FOREIGN KEY (`title_approval_id`) REFERENCES `title_approvals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `research_defense_schedules`
--
ALTER TABLE `research_defense_schedules`
  ADD CONSTRAINT `fk_rds_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `research_proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `research_groups`
--
ALTER TABLE `research_groups`
  ADD CONSTRAINT `fk_rg_title_approval` FOREIGN KEY (`title_approval_id`) REFERENCES `title_approvals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `research_milestones`
--
ALTER TABLE `research_milestones`
  ADD CONSTRAINT `fk_rm_research_plan` FOREIGN KEY (`research_plan_id`) REFERENCES `research_plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `research_plans`
--
ALTER TABLE `research_plans`
  ADD CONSTRAINT `fk_rp_research_group` FOREIGN KEY (`research_group_id`) REFERENCES `research_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `research_progress_activity_logs`
--
ALTER TABLE `research_progress_activity_logs`
  ADD CONSTRAINT `fk_rpal_research_plan` FOREIGN KEY (`research_plan_id`) REFERENCES `research_plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `research_progress_attachments`
--
ALTER TABLE `research_progress_attachments`
  ADD CONSTRAINT `fk_rpa_progress_update` FOREIGN KEY (`progress_update_id`) REFERENCES `research_progress_updates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `research_progress_feedback`
--
ALTER TABLE `research_progress_feedback`
  ADD CONSTRAINT `fk_rpf_milestone` FOREIGN KEY (`milestone_id`) REFERENCES `research_milestones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rpf_progress_update` FOREIGN KEY (`progress_update_id`) REFERENCES `research_progress_updates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rpf_research_plan` FOREIGN KEY (`research_plan_id`) REFERENCES `research_plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `research_progress_updates`
--
ALTER TABLE `research_progress_updates`
  ADD CONSTRAINT `fk_rpu_milestone` FOREIGN KEY (`milestone_id`) REFERENCES `research_milestones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rpu_research_plan` FOREIGN KEY (`research_plan_id`) REFERENCES `research_plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
