-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2026 at 10:09 AM
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
-- Database: `skillbridge_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `action_details` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `admin_id`, `action_type`, `action_details`, `ip_address`, `created_at`) VALUES
(1, 2, 'UPLOAD_RESOURCE', 'Uploaded resource: Module 1 to course: IT22', NULL, '2026-05-12 06:10:54'),
(2, 2, 'UPLOAD_RESOURCE', 'Uploaded resource: Module 1 to course: IT22', NULL, '2026-05-12 06:10:57'),
(3, 2, 'UPLOAD_RESOURCE', 'Uploaded resource: Module 1 to course: IT22', NULL, '2026-05-12 06:12:10'),
(4, 2, 'UPLOAD_RESOURCE', 'Uploaded resource: module 3 to course: Advance Database', NULL, '2026-05-12 06:22:02'),
(5, 2, 'AWARD_CERT', 'Manually awarded certificate SB-2026-F4401BA5 to User ID: 3', NULL, '2026-05-13 07:01:55'),
(6, 2, 'AWARD_CERT', 'Manually awarded certificate SB-2026-075C7A17 to User ID: 5', NULL, '2026-05-13 07:02:19'),
(7, 2, 'AWARD_CERT', 'Manually awarded certificate SB-2026-5517D7DD to User ID: 7', NULL, '2026-05-13 07:34:41'),
(8, 2, 'AWARD_CERT', 'Manually awarded certificate SB-2026-C4C21C19 to User ID: 7', NULL, '2026-05-13 08:02:56');

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `total_questions` int(11) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`id`, `course_id`, `title`, `total_questions`, `created_at`) VALUES
(8, 3, 'activity', 10, '2026-04-30 05:02:27'),
(9, 1, 'Quiz 4', 1, '2026-05-13 07:15:43');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_questions`
--

CREATE TABLE `assessment_questions` (
  `id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_option` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_questions`
--

INSERT INTO `assessment_questions` (`id`, `assessment_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`) VALUES
(1, 8, '1. Which Python library is primarily used for high-performance numerical calculations and multidimensional array operations?', 'Pandas', 'Matplotlib', 'NumPy', 'Scikit-learn', 'C'),
(3, 9, 'fewferhyhyrth', 'hr5hrh', 'hrthrh', 'rhrhrh', 'rhrhrh', 'B');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_scores`
--

CREATE TABLE `assessment_scores` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `taken_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_scores`
--

INSERT INTO `assessment_scores` (`id`, `user_id`, `assessment_id`, `score`, `taken_at`) VALUES
(5, 3, 8, 1, '2026-04-30 05:03:59'),
(6, 4, 8, 1, '2026-04-30 06:23:54'),
(7, 5, 8, 1, '2026-04-30 15:10:11'),
(8, 7, 8, 1, '2026-05-13 07:40:10');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `certificate_code` varchar(50) NOT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `user_id`, `course_id`, `certificate_code`, `issued_at`) VALUES
(1, 5, 3, 'SB-2026-9CB2F758', '2026-05-12 05:18:50'),
(2, 3, 4, 'SB-2026-F4401BA5', '2026-05-13 07:01:55'),
(3, 5, 1, 'SB-2026-075C7A17', '2026-05-13 07:02:19'),
(4, 7, 4, 'SB-2026-5517D7DD', '2026-05-13 07:34:41'),
(5, 7, 3, 'SB-2026-70893D24', '2026-05-13 07:40:27'),
(6, 7, 2, 'SB-2026-C4C21C19', '2026-05-13 08:02:56');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `lessons` int(11) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `banner_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `lessons`, `category`, `created_at`, `banner_image`) VALUES
(1, 'Web Development Masterclass & Certifications', 8, 'Development', '2026-04-29 09:28:10', NULL),
(2, 'Advance Typography and UI/UX Design', 6, 'Design', '2026-04-29 09:28:10', NULL),
(3, 'Data Science & Machine Learning with Python', 12, 'Development', '2026-04-29 09:28:10', NULL),
(4, 'Advance Database', 6, 'databases', '2026-04-30 17:07:15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_reviews`
--

CREATE TABLE `course_reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_reviews`
--

INSERT INTO `course_reviews` (`id`, `user_id`, `course_id`, `rating`, `review_text`, `created_at`) VALUES
(1, 5, 3, 2, '', '2026-05-12 05:57:40');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `progress` int(11) DEFAULT 0,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `user_id`, `course_id`, `progress`, `enrolled_at`) VALUES
(1, 3, 1, 0, '2026-04-29 10:12:20'),
(2, 3, 2, 0, '2026-04-30 04:07:23'),
(3, 3, 3, 0, '2026-04-30 04:12:28'),
(4, 4, 1, 0, '2026-04-30 06:22:52'),
(5, 4, 2, 0, '2026-04-30 06:23:14'),
(6, 4, 3, 0, '2026-04-30 06:23:32'),
(7, 5, 3, 0, '2026-04-30 15:09:53'),
(12, 4, 4, 0, '2026-05-13 03:59:15'),
(13, 5, 4, 100, '2026-05-13 06:23:16'),
(14, 5, 1, 100, '2026-05-13 06:42:05'),
(15, 5, 2, 0, '2026-05-13 07:09:50'),
(16, 7, 4, 100, '2026-05-13 07:24:37'),
(17, 7, 1, 0, '2026-05-13 07:24:49'),
(18, 7, 2, 100, '2026-05-13 07:31:57'),
(19, 7, 3, 0, '2026-05-13 07:32:00');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 5, 'New Material Available', 'New learning material \'Module 1\' has been added to IT22.', 0, '2026-05-12 06:12:10');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `course_id`, `file_name`, `file_path`, `file_type`, `uploaded_at`) VALUES
(1, 3, 'Sti_Trace.log', '69f2d6918d46b_Sti_Trace.log', 'log', '2026-04-30 04:12:01'),
(2, 3, 'Group-4-Evacation-plan.docx', '69f2e3c49c846_Group-4-Evacation-plan.docx', 'docx', '2026-04-30 05:08:20'),
(3, 3, 'index.html', '69f2e3e2b0fd5_index.html', 'html', '2026-04-30 05:08:50'),
(4, 2, 'Module 1', '69f2e5b6af825_SkillBridge.docx', 'docx', '2026-04-30 05:16:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `referral` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` int(11) DEFAULT 0,
  `age` int(11) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `course_year` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `referral`, `created_at`, `is_admin`, `age`, `phone_number`, `course_year`, `profile_picture`) VALUES
(2, 'Admin', 'admin@gmail.com', '$2y$10$hxnwURgiw/oQDcWBa5Km/OE1agH9Gtc2n2KO37shZSHVtX1Z96ex.', 'other', '2026-04-29 09:42:44', 1, NULL, NULL, NULL, NULL),
(3, 'Shane Jaybee Cojetia', 'cojetiashanejaybee@gmail.com', '$2y$10$R7mwJeBMR9oaNFPkXZuh.O7k879zXxyPjPveG6Lgw.fOrYYrmuQVe', 'social', '2026-04-29 09:44:48', 0, NULL, '', '', '1777561262_5R-2.JPG'),
(4, 'soffie', 'sofie@gnail.com', '$2y$10$DrprTGiHXdxF4mgAja6d6e/D4b2peJ7OpUVJwXcfVD6B3wYHJDxpK', 'friend', '2026-04-30 06:22:20', 0, NULL, NULL, NULL, NULL),
(5, 'Red Sunwin Tepace', 'red@gmail.com', '$2y$10$nZWEgZbdWhjsSOkUtXFSq.Q3xS3gXOIFIxqvrBrj3fFoFvzP6hjXi', 'school_announcement', '2026-04-30 14:32:38', 0, 21, '09109948116', 'BSIT 2a', '1777571352_168887865_807473006820140_6137518652221481920_n.jpg'),
(7, 'Faj Catuyo', 'faj@gmail.com', '$2y$10$UD5jiSVcbcLaIOfAQPPbre62YMLzITFI7ijXcTX5vKf7FvaNb9eM6', 'social', '2026-05-13 07:24:04', 0, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `assessment_questions`
--
ALTER TABLE `assessment_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `assessment_scores`
--
ALTER TABLE `assessment_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_code` (`certificate_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_reviews`
--
ALTER TABLE `course_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `assessment_questions`
--
ALTER TABLE `assessment_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `assessment_scores`
--
ALTER TABLE `assessment_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=994;

--
-- AUTO_INCREMENT for table `course_reviews`
--
ALTER TABLE `course_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assessments`
--
ALTER TABLE `assessments`
  ADD CONSTRAINT `assessments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assessment_questions`
--
ALTER TABLE `assessment_questions`
  ADD CONSTRAINT `assessment_questions_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assessment_scores`
--
ALTER TABLE `assessment_scores`
  ADD CONSTRAINT `assessment_scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessment_scores_ibfk_2` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
