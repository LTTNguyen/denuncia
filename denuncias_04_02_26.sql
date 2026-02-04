-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Feb 04, 2026 at 08:22 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `denuncias_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `portal_category`
--

CREATE TABLE `portal_category` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_category`
--

INSERT INTO `portal_category` (`id`, `company_id`, `name`, `is_active`, `sort_order`, `created_at`) VALUES
(9, 1, 'Ética / Conducta', 1, 1, '2026-02-04 15:30:15'),
(10, 1, 'Fraude', 1, 2, '2026-02-04 15:30:15'),
(11, 1, 'Seguridad y Salud', 1, 3, '2026-02-04 15:30:15'),
(12, 1, 'Acoso / Discriminación', 1, 4, '2026-02-04 15:30:15'),
(13, 1, 'Cumplimiento / Legal', 1, 5, '2026-02-04 15:30:15'),
(14, 2, 'Ética / Conducta', 1, 1, '2026-02-04 15:48:11'),
(15, 2, 'Fraude', 1, 2, '2026-02-04 15:48:11'),
(16, 2, 'Seguridad y Salud', 1, 3, '2026-02-04 15:48:11'),
(17, 2, 'Acoso / Discriminación', 1, 4, '2026-02-04 15:48:11'),
(18, 2, 'Cumplimiento / Legal', 1, 5, '2026-02-04 15:48:11'),
(21, 3, 'Ética / Conducta', 1, 1, '2026-02-04 15:48:23'),
(22, 3, 'Fraude', 1, 2, '2026-02-04 15:48:23'),
(23, 3, 'Seguridad y Salud', 1, 3, '2026-02-04 15:48:23'),
(24, 3, 'Acoso / Discriminación', 1, 4, '2026-02-04 15:48:23'),
(25, 3, 'Cumplimiento / Legal', 1, 5, '2026-02-04 15:48:23');

-- --------------------------------------------------------

--
-- Table structure for table `portal_company`
--

CREATE TABLE `portal_company` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_company`
--

INSERT INTO `portal_company` (`id`, `name`, `slug`, `logo_path`, `is_active`, `created_at`) VALUES
(1, 'T&M', 'tym', 'images/LOGO_TYM.png', 1, '2026-02-04 14:06:28'),
(2, 'RK Maestranza', 'rk', 'images/logo_rk.png', 1, '2026-02-04 14:06:28'),
(3, 'Andes Suministros', 'andes', 'images/logo_andes_pic.png', 1, '2026-02-04 14:06:28');

-- --------------------------------------------------------

--
-- Table structure for table `portal_report`
--

CREATE TABLE `portal_report` (
  `id` bigint(20) NOT NULL,
  `company_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `report_key` varchar(16) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 1,
  `reporter_name` varchar(120) DEFAULT NULL,
  `reporter_email` varchar(160) DEFAULT NULL,
  `reporter_phone` varchar(60) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `description` mediumtext NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `occurred_at` datetime DEFAULT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'NEW',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_report`
--

INSERT INTO `portal_report` (`id`, `company_id`, `category_id`, `report_key`, `password_hash`, `is_anonymous`, `reporter_name`, `reporter_email`, `reporter_phone`, `subject`, `description`, `location`, `occurred_at`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 21, 'CHFLBCC3VZ', '$2y$10$SwcVXTlayRJ/On1/OZMDnOG7mOWafbLhPtVBKSS/AJYSqHQcfjH2C', 1, '', '', NULL, 'ssdsd', 'sdsdsdsddsdsdsdsdsdsd', 'sdsds', '2026-12-16 00:00:00', 'NEW', '2026-02-04 17:33:29', '2026-02-04 17:33:29'),
(2, 2, 16, 'MMYHR47D4B', '$2y$10$fh2hNZPwVLURVVXizItTWuhGadGz0UKvukYUeHqXQ6k8leaELJChu', 1, '', '', NULL, 'aaaaa', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'aaaaaa', NULL, 'NEW', '2026-02-04 17:54:22', '2026-02-04 17:55:16'),
(3, 3, 23, 'PZLBRCFHJM', '$2y$10$oVeEVoC/82a11NGCMC0zIOMnuQ9tYwRo4eyW1G9E3keQaseC0pAgu', 0, 'Thuy', 'thuynguyen200k@gmail.com', NULL, 'sdsdsdsdsdsd', 'sdsdsdsdsdssdssdsds', 'sdsdsdsdsdsd', NULL, 'NEW', '2026-02-04 18:15:15', NULL),
(4, 3, 23, 'RU6R3QE73V', '$2y$10$PLw5CmVMoJ0Bc7ashO/8vOVGISfgnOYKTHgLRQiWwe2nB4TBO3l9.', 0, 'Thuy', 'thuynguyen200k@gmail.com', NULL, 'sdsdsdsswewewewewe', 'eeeeeeeeeeeeeeeeeeeeeeeeeeeee', 'dzxvxsfdasds', '2026-12-16 00:00:00', 'NEW', '2026-02-04 18:15:44', NULL),
(5, 3, 24, 'CQKXE7TD3S', '$2y$10$OlbWtK0UOrCdPlxLw3zzHuFzVsjfKEQU9IetBwhSg6OFPKANFj5Ae', 1, '', '', NULL, 'ttttttttttttttttttttttttttttt', 'wwwwwwwwwwwwwwwwwwwwwwwwwwwww', 'eeeeeeeeeeeeeeeeeeeeeeeeeeee', '2026-12-16 00:00:00', 'NEW', '2026-02-04 18:16:14', NULL),
(6, 2, 14, 'H3ZW25WNVM', '$2y$10$8k1e6eAw1G/ReGaBbKmsEu4Gyz9K1rN0I5W08L.KV/R.raYsR7iya', 1, '', '', NULL, 'aaaaa', 'wwwwwwwwwwwwwwwwwwwwwwwwwwwww', 'văn phòng tym', '2026-12-16 00:00:00', 'NEW', '2026-02-04 18:25:08', NULL),
(7, 3, 21, 'FE3274Z3XQ', '$2y$10$Zmu0GLcuN0ZMXESns8Ouo.PQF8f9Q//taNlFQP7FdAUwuJn4MWrdS', 1, '', '', NULL, 'aaaaa', 'djfjdfsddfsdfrs', 'văn phòng tym', '2026-12-16 00:00:00', 'NEW', '2026-02-04 18:38:03', '2026-02-04 18:38:24'),
(8, 3, 22, 'DMQHKMB5PJ', '$2y$10$iDnOUTJ/cINrckSSE6h9r.hTiC2CceKSQiAzNxFGkfff9iyaDQg9G', 0, 'Thuy', 'thuynguyen200k@gmail.com', NULL, 'sdsdsdsswewewewewe', 'sddsdsdsdsdsdsdsdsdsd', 'aaaaaa', NULL, 'NEW', '2026-02-04 18:39:46', NULL),
(9, 1, 11, 'XPP4RCYK7P', '$2y$10$iHmPPQMxAR1F.b3JCdRWvevfG/sQRtzpfNNSkMj4QrHAPt5nOqtnW', 1, '', '', NULL, 'aaaaa', 'ururrurusjshshsheheheheey', 'aaaaaa', '2026-02-04 00:00:00', 'NEW', '2026-02-04 19:05:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `portal_report_attachment`
--

CREATE TABLE `portal_report_attachment` (
  `id` bigint(20) NOT NULL,
  `report_id` bigint(20) NOT NULL,
  `stored_path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(120) NOT NULL,
  `size_bytes` bigint(20) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `portal_report_message`
--

CREATE TABLE `portal_report_message` (
  `id` bigint(20) NOT NULL,
  `report_id` bigint(20) NOT NULL,
  `sender_type` enum('REPORTER','INVESTIGATOR') NOT NULL DEFAULT 'REPORTER',
  `message` mediumtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_report_message`
--

INSERT INTO `portal_report_message` (`id`, `report_id`, `sender_type`, `message`, `created_at`) VALUES
(1, 1, 'REPORTER', 'sdsdsdsddsdsdsdsdsdsd', '2026-02-04 17:33:29'),
(2, 2, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-04 17:54:22'),
(3, 2, 'REPORTER', 'dwewewewewewewewewe', '2026-02-04 17:54:42'),
(4, 2, 'REPORTER', 'jsjsussjsusjsdusjsjsj', '2026-02-04 17:55:00'),
(5, 2, 'REPORTER', 'jsjsussjsusjsdusjsjsj', '2026-02-04 17:55:16'),
(6, 3, 'REPORTER', 'sdsdsdsdsdssdssdsds', '2026-02-04 18:15:15'),
(7, 4, 'REPORTER', 'eeeeeeeeeeeeeeeeeeeeeeeeeeeee', '2026-02-04 18:15:44'),
(8, 5, 'REPORTER', 'wwwwwwwwwwwwwwwwwwwwwwwwwwwww', '2026-02-04 18:16:14'),
(9, 6, 'REPORTER', 'wwwwwwwwwwwwwwwwwwwwwwwwwwwww', '2026-02-04 18:25:08'),
(10, 7, 'REPORTER', 'djfjdfsddfsdfrs', '2026-02-04 18:38:03'),
(11, 7, 'REPORTER', 'fnjjdjdjdjdjdjdjdjdjdjdjd', '2026-02-04 18:38:24'),
(12, 8, 'REPORTER', 'sddsdsdsdsdsdsdsdsdsd', '2026-02-04 18:39:46'),
(13, 9, 'REPORTER', 'ururrurusjshshsheheheheey', '2026-02-04 19:05:42');

-- --------------------------------------------------------

--
-- Table structure for table `portal_resource`
--

CREATE TABLE `portal_resource` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `title` varchar(160) NOT NULL,
  `url` varchar(500) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `portal_category`
--
ALTER TABLE `portal_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pc_company` (`company_id`);

--
-- Indexes for table `portal_company`
--
ALTER TABLE `portal_company`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `portal_report`
--
ALTER TABLE `portal_report`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_key` (`report_key`),
  ADD KEY `fk_r_company` (`company_id`),
  ADD KEY `fk_r_category` (`category_id`);

--
-- Indexes for table `portal_report_attachment`
--
ALTER TABLE `portal_report_attachment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ra_report` (`report_id`);

--
-- Indexes for table `portal_report_message`
--
ALTER TABLE `portal_report_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rm_report` (`report_id`);

--
-- Indexes for table `portal_resource`
--
ALTER TABLE `portal_resource`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pr_company` (`company_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `portal_category`
--
ALTER TABLE `portal_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `portal_company`
--
ALTER TABLE `portal_company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `portal_report`
--
ALTER TABLE `portal_report`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `portal_report_attachment`
--
ALTER TABLE `portal_report_attachment`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `portal_report_message`
--
ALTER TABLE `portal_report_message`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `portal_resource`
--
ALTER TABLE `portal_resource`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `portal_category`
--
ALTER TABLE `portal_category`
  ADD CONSTRAINT `fk_pc_company` FOREIGN KEY (`company_id`) REFERENCES `portal_company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `portal_report`
--
ALTER TABLE `portal_report`
  ADD CONSTRAINT `fk_r_category` FOREIGN KEY (`category_id`) REFERENCES `portal_category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_r_company` FOREIGN KEY (`company_id`) REFERENCES `portal_company` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `portal_report_attachment`
--
ALTER TABLE `portal_report_attachment`
  ADD CONSTRAINT `fk_ra_report` FOREIGN KEY (`report_id`) REFERENCES `portal_report` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `portal_report_message`
--
ALTER TABLE `portal_report_message`
  ADD CONSTRAINT `fk_rm_report` FOREIGN KEY (`report_id`) REFERENCES `portal_report` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `portal_resource`
--
ALTER TABLE `portal_resource`
  ADD CONSTRAINT `fk_pr_company` FOREIGN KEY (`company_id`) REFERENCES `portal_company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
