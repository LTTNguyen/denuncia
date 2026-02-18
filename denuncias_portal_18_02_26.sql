-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Feb 18, 2026 at 10:56 AM
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
-- Table structure for table `portal_admin_login_attempt`
--

CREATE TABLE `portal_admin_login_attempt` (
  `id` bigint(20) NOT NULL,
  `email` varchar(190) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_admin_login_attempt`
--

INSERT INTO `portal_admin_login_attempt` (`id`, `email`, `success`, `ip`, `user_agent`, `created_at`) VALUES
(1, 'thuy.nguyen@tymelectricos.cl', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 15:34:44'),
(2, 'yourstrongpasswordhere', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 15:42:46'),
(3, 'thuy.nguyen@tymelectricos.cl', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 15:43:15'),
(4, '', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 15:43:16'),
(5, '', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 15:43:17'),
(6, '', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 15:43:17'),
(7, '', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 15:43:17'),
(8, '', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 15:43:19'),
(9, 'thuy.nguyen@tymelectricos.cl', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 15:48:32'),
(10, 'thuy.nguyen@tymelectricos.cl', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 15:49:11'),
(11, 'thuy.nguyen@tymelectricos.cl', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 16:00:51'),
(12, 'thuy.nguyen@tymelectricos.cl', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 17:55:38'),
(13, 'thuy.nguyen@tymelectricos.cl', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 12:50:43'),
(14, 'thuy.nguyen@tymelectricos.cl', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 12:50:50'),
(15, 'thuy.nguyen@tymelectricos.cl', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 13:01:44'),
(16, 'thuy.nguyen@tymelectricos.cl', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 13:21:48'),
(17, 'thuy.nguyen@tymelectricos.cl', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 13:22:06'),
(18, 'thuynguyen@tymelectricos.cl', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 16:32:09'),
(19, 'thuy.nguyen@tymelectricos.cl', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 16:32:25');

-- --------------------------------------------------------

--
-- Table structure for table `portal_admin_user`
--

CREATE TABLE `portal_admin_user` (
  `id` int(11) NOT NULL,
  `email` varchar(190) NOT NULL,
  `full_name` varchar(160) DEFAULT NULL,
  `role` enum('ADMIN','INVESTIGATOR','READONLY') NOT NULL DEFAULT 'INVESTIGATOR',
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_admin_user`
--

INSERT INTO `portal_admin_user` (`id`, `email`, `full_name`, `role`, `password_hash`, `is_active`, `last_login_at`, `created_at`) VALUES
(1, 'thuy.nguyen@tymelectricos.cl', 'Admin', 'ADMIN', '$2y$10$ozqiTf8v71ZUiorhGGx5iu2OB5uNJxx8wmcHYXO0HT.jY4OfufuG2', 1, '2026-02-16 13:32:25', '2026-02-13 15:27:09');

-- --------------------------------------------------------

--
-- Table structure for table `portal_audit_log`
--

CREATE TABLE `portal_audit_log` (
  `id` bigint(20) NOT NULL,
  `report_id` bigint(20) DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `actor_type` enum('REPORTER','INVESTIGATOR','SYSTEM') NOT NULL DEFAULT 'SYSTEM',
  `actor_label` varchar(160) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `meta_json` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_audit_log`
--

INSERT INTO `portal_audit_log` (`id`, `report_id`, `action`, `actor_type`, `actor_label`, `ip`, `user_agent`, `meta_json`, `created_at`) VALUES
(1, 46, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"AYDXCH3UNC\"}', '2026-02-11 20:01:36'),
(2, 46, 'CASE_VIEW', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"channel\":\"CMP\"}', '2026-02-11 20:01:36'),
(3, 47, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"HQYQE2T8QV\"}', '2026-02-11 20:04:54'),
(4, 47, 'CASE_VIEW', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"channel\":\"LK\"}', '2026-02-11 20:04:54'),
(5, 48, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"V542ZZTAJY\"}', '2026-02-11 20:09:03'),
(6, 48, 'CASE_VIEW', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"channel\":\"CMP\"}', '2026-02-11 20:09:03'),
(7, 48, 'REPORTER_MESSAGE', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"len\":5}', '2026-02-11 20:09:24'),
(8, 48, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"V542ZZTAJY\"}', '2026-02-11 20:25:35'),
(9, 51, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"TM2Z49ANA2\"}', '2026-02-12 16:39:32'),
(10, 51, 'CASE_VIEW', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"channel\":\"CMP\"}', '2026-02-12 16:39:32'),
(11, 51, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"TM2Z49ANA2\"}', '2026-02-12 18:11:53'),
(12, 51, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"TM2Z49ANA2\"}', '2026-02-12 18:30:47'),
(13, 51, 'CASE_VIEW', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"channel\":\"CMP\"}', '2026-02-12 18:30:48'),
(14, 52, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"WF2RS2V9NT\"}', '2026-02-12 19:11:44'),
(15, 52, 'CASE_VIEW', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"channel\":\"CMP\"}', '2026-02-12 19:11:44'),
(16, 53, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"JACUALJZPT\"}', '2026-02-12 19:23:31'),
(17, 53, 'CASE_VIEW', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"channel\":\"CMP\"}', '2026-02-12 19:23:32'),
(18, 53, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"JACUALJZPT\"}', '2026-02-12 19:27:15'),
(19, 53, 'CASE_VIEW', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"channel\":\"CMP\"}', '2026-02-12 19:27:15'),
(20, 53, 'REPORTER_MESSAGE', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"len\":5}', '2026-02-12 19:27:22'),
(21, 53, 'CASE_SESSION_EXPIRED', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-12 20:03:16'),
(22, 53, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"JACUALJZPT\"}', '2026-02-12 20:21:54'),
(34, NULL, 'ADMIN_LOGIN_OK', 'INVESTIGATOR', 'thuy.nguyen@tymelectricos.cl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"role\":\"ADMIN\"}', '2026-02-13 17:55:38'),
(35, NULL, 'ADMIN_LOGIN_OK', 'INVESTIGATOR', 'thuy.nguyen@tymelectricos.cl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"role\":\"ADMIN\"}', '2026-02-16 12:50:43'),
(36, NULL, 'ADMIN_LOGIN_OK', 'INVESTIGATOR', 'thuy.nguyen@tymelectricos.cl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"role\":\"ADMIN\"}', '2026-02-16 12:50:50'),
(37, NULL, 'ADMIN_LOGIN_OK', 'INVESTIGATOR', 'thuy.nguyen@tymelectricos.cl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"role\":\"ADMIN\"}', '2026-02-16 13:01:44'),
(38, NULL, 'ADMIN_LOGIN_FAIL', 'INVESTIGATOR', 'thuy.nguyen@tymelectricos.cl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-16 13:21:48'),
(39, NULL, 'ADMIN_LOGIN_OK', 'INVESTIGATOR', 'thuy.nguyen@tymelectricos.cl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"role\":\"ADMIN\"}', '2026-02-16 13:22:06'),
(40, 53, 'ADMIN_STATUS_CHANGE', 'INVESTIGATOR', 'thuy.nguyen@tymelectricos.cl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"old\":\"NEW\",\"new\":\"PENDING\",\"note\":\"\"}', '2026-02-16 13:22:17'),
(41, 53, 'INVESTIGATOR_MESSAGE', 'INVESTIGATOR', 'thuy.nguyen@tymelectricos.cl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"len\":7}', '2026-02-16 13:22:29'),
(42, 53, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"JACUALJZPT\"}', '2026-02-16 13:22:48'),
(43, 53, 'CASE_VIEW', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"channel\":\"CMP\"}', '2026-02-16 13:22:49'),
(44, NULL, 'ADMIN_LOGIN_FAIL', 'INVESTIGATOR', 'thuynguyen@tymelectricos.cl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-16 16:32:09'),
(45, NULL, 'ADMIN_LOGIN_OK', 'INVESTIGATOR', 'thuy.nguyen@tymelectricos.cl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"role\":\"ADMIN\"}', '2026-02-16 16:32:25'),
(46, 53, 'ADMIN_STATUS_CHANGE', 'INVESTIGATOR', 'thuy.nguyen@tymelectricos.cl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"old\":\"PENDING\",\"new\":\"IN_REVIEW\",\"note\":\"In review now\"}', '2026-02-16 16:32:59'),
(47, 53, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"JACUALJZPT\"}', '2026-02-16 16:33:42'),
(48, 53, 'CASE_VIEW', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"channel\":\"CMP\"}', '2026-02-16 16:33:42'),
(49, 53, 'ADMIN_STATUS_CHANGE', 'INVESTIGATOR', 'thuy.nguyen@tymelectricos.cl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"old\":\"IN_REVIEW\",\"new\":\"WAITING_REPORTER\",\"note\":\"\"}', '2026-02-16 18:43:28'),
(50, 53, 'CASE_SESSION_EXPIRED', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-16 18:43:37'),
(51, 53, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"JACUALJZPT\"}', '2026-02-16 18:43:45'),
(52, 53, 'CASE_SESSION_EXPIRED', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-16 19:37:47'),
(53, 53, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"JACUALJZPT\"}', '2026-02-16 19:38:04'),
(54, 53, 'REPORTER_MESSAGE', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"len\":11}', '2026-02-16 19:38:33'),
(55, 53, 'REPORTER_LOGIN_OK', 'REPORTER', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"key\":\"JACUALJZPT\"}', '2026-02-16 19:49:09');

-- --------------------------------------------------------

--
-- Table structure for table `portal_category`
--

CREATE TABLE `portal_category` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `code` varchar(40) DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_category`
--

INSERT INTO `portal_category` (`id`, `company_id`, `group_id`, `code`, `name`, `is_active`, `sort_order`, `created_at`) VALUES
(9, 1, NULL, NULL, 'Ética / Conducta', 0, 1, '2026-02-04 15:30:15'),
(10, 1, NULL, NULL, 'Fraude', 0, 2, '2026-02-04 15:30:15'),
(11, 1, NULL, NULL, 'Seguridad y Salud', 0, 3, '2026-02-04 15:30:15'),
(12, 1, NULL, NULL, 'Acoso / Discriminación', 0, 4, '2026-02-04 15:30:15'),
(13, 1, NULL, NULL, 'Cumplimiento / Legal', 0, 5, '2026-02-04 15:30:15'),
(14, 2, NULL, NULL, 'Ética / Conducta', 1, 1, '2026-02-04 15:48:11'),
(15, 2, NULL, NULL, 'Fraude', 1, 2, '2026-02-04 15:48:11'),
(16, 2, NULL, NULL, 'Seguridad y Salud', 1, 3, '2026-02-04 15:48:11'),
(17, 2, NULL, NULL, 'Acoso / Discriminación', 1, 4, '2026-02-04 15:48:11'),
(18, 2, NULL, NULL, 'Cumplimiento / Legal', 1, 5, '2026-02-04 15:48:11'),
(21, 3, NULL, NULL, 'Ética / Conducta', 1, 1, '2026-02-04 15:48:23'),
(22, 3, NULL, NULL, 'Fraude', 1, 2, '2026-02-04 15:48:23'),
(23, 3, NULL, NULL, 'Seguridad y Salud', 1, 3, '2026-02-04 15:48:23'),
(24, 3, NULL, NULL, 'Acoso / Discriminación', 1, 4, '2026-02-04 15:48:23'),
(25, 3, NULL, NULL, 'Cumplimiento / Legal', 1, 5, '2026-02-04 15:48:23'),
(26, 1, 2, 'LK_ACOSO_LABORAL', 'Acoso laboral (Ley Karin)', 1, 1, '2026-02-11 15:36:28'),
(27, 1, 2, 'LK_ACOSO_SEXUAL', 'Acoso sexual', 1, 2, '2026-02-11 15:36:28'),
(28, 1, 2, 'LK_VIOLENCIA_TRABAJO', 'Violencia en el trabajo', 1, 3, '2026-02-11 15:36:28'),
(29, 1, 1, 'CMP_COHECHO', 'Cohecho o corrupción', 1, 10, '2026-02-11 15:36:28'),
(30, 1, 1, 'CMP_ADMIN_DESLEAL', 'Administración desleal', 1, 11, '2026-02-11 15:36:28'),
(31, 1, 1, 'CMP_LAVADO_ACTIVOS', 'Lavado de activos', 1, 12, '2026-02-11 15:36:28'),
(32, 1, 1, 'CMP_RECEPTACION', 'Receptación', 1, 13, '2026-02-11 15:36:28'),
(33, 1, 1, 'CMP_CORRUP_PRIVADOS', 'Corrupción entre privados', 1, 14, '2026-02-11 15:36:28'),
(34, 1, 1, 'CMP_FRAUDE_CONTABLE', 'Fraude contable', 1, 15, '2026-02-11 15:36:28'),
(35, 1, 1, 'CMP_DELITOS_TRIBUTARIOS', 'Delitos tributarios', 1, 16, '2026-02-11 15:36:28'),
(36, 1, 1, 'CMP_INFRACC_AMBIENTAL', 'Infracciones ambientales', 1, 17, '2026-02-11 15:36:28'),
(37, 1, 1, 'CMP_CONFLICTO_INTERES', 'Conflicto de interés', 1, 18, '2026-02-11 15:36:28'),
(38, 1, 1, 'CMP_CODIGO_ETICA', 'Incumplimiento Código de Ética', 1, 19, '2026-02-11 15:36:28'),
(39, 1, 1, 'CMP_OTRO', 'Otro (especificar)', 1, 99, '2026-02-11 15:36:28'),
(40, 1, 2, 'LK_OTRO', 'Otro (especificar)', 1, 99, '2026-02-11 18:53:02');

-- --------------------------------------------------------

--
-- Table structure for table `portal_category_group`
--

CREATE TABLE `portal_category_group` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `code` varchar(40) NOT NULL,
  `name` varchar(160) NOT NULL,
  `law_ref` varchar(80) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_category_group`
--

INSERT INTO `portal_category_group` (`id`, `company_id`, `code`, `name`, `law_ref`, `description`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 1, 'COMPLIANCE', 'Comportamiento Correcto', 'Ley 20.393 / 21.595', NULL, 1, 1, '2026-02-11 18:53:02'),
(2, 1, 'LEY_21643', 'Canal Ley Karin (Acoso y Violencia)', 'Ley 21.643', NULL, 2, 1, '2026-02-11 18:53:02');

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
(1, 'T&M', 'tym', 'images/tym_logo.png', 1, '2026-02-04 14:06:28'),
(2, 'RK Maestranza', 'rk', 'images/logo_rk.png', 0, '2026-02-04 14:06:28'),
(3, 'Andes Suministros', 'andes', 'images/logo_andes_pic.png', 0, '2026-02-04 14:06:28');

-- --------------------------------------------------------

--
-- Table structure for table `portal_evidence_type`
--

CREATE TABLE `portal_evidence_type` (
  `id` int(11) NOT NULL,
  `code` varchar(40) NOT NULL,
  `name` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_evidence_type`
--

INSERT INTO `portal_evidence_type` (`id`, `code`, `name`) VALUES
(1, 'EMAILS', 'Correos electrónicos'),
(2, 'MESSAGES', 'Mensajes'),
(3, 'PHOTOS', 'Fotografías'),
(4, 'RECORDINGS', 'Grabaciones'),
(5, 'ACCOUNTING', 'Documentos contables'),
(6, 'OTHER', 'Otro');

-- --------------------------------------------------------

--
-- Table structure for table `portal_notify_recipient`
--

CREATE TABLE `portal_notify_recipient` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `email` varchar(160) NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_notify_recipient`
--

INSERT INTO `portal_notify_recipient` (`id`, `company_id`, `category_id`, `email`, `name`, `is_active`, `created_at`) VALUES
(1, 1, NULL, 'thuy.nguyen@tymelectricos.cl', 'Thuy Nguyen - Innovation', 1, '2026-02-09 20:20:31');

-- --------------------------------------------------------

--
-- Table structure for table `portal_report`
--

CREATE TABLE `portal_report` (
  `id` bigint(20) NOT NULL,
  `company_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `secondary_classification` varchar(255) DEFAULT NULL,
  `report_key` varchar(16) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 1,
  `reporter_name` varchar(120) DEFAULT NULL,
  `reporter_rut` varchar(20) DEFAULT NULL,
  `reporter_email` varchar(160) DEFAULT NULL,
  `reporter_cargo` varchar(120) DEFAULT NULL,
  `reporter_phone` varchar(60) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `description` mediumtext NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `location_type` enum('COMPANY','PROJECT','REMOTE','OTHER') DEFAULT NULL,
  `area_unit` varchar(160) DEFAULT NULL,
  `occurred_at` datetime DEFAULT NULL,
  `event_kind` enum('SINGLE','REITERATED') NOT NULL DEFAULT 'SINGLE',
  `event_period` varchar(160) DEFAULT NULL,
  `reported_to_superior` enum('YES','NO','NA') NOT NULL DEFAULT 'NA',
  `protection_requested` tinyint(1) NOT NULL DEFAULT 0,
  `protection_detail` varchar(500) DEFAULT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'NEW',
  `terms_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `terms_accepted_at` datetime DEFAULT NULL,
  `terms_accepted_ip` varchar(45) DEFAULT NULL,
  `terms_accepted_ua` varchar(255) DEFAULT NULL,
  `evidence_other_detail` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_report`
--

INSERT INTO `portal_report` (`id`, `company_id`, `category_id`, `secondary_classification`, `report_key`, `password_hash`, `is_anonymous`, `reporter_name`, `reporter_rut`, `reporter_email`, `reporter_cargo`, `reporter_phone`, `subject`, `description`, `location`, `location_type`, `area_unit`, `occurred_at`, `event_kind`, `event_period`, `reported_to_superior`, `protection_requested`, `protection_detail`, `status`, `terms_accepted`, `terms_accepted_at`, `terms_accepted_ip`, `terms_accepted_ua`, `evidence_other_detail`, `created_at`, `updated_at`) VALUES
(1, 3, 21, NULL, 'CHFLBCC3VZ', '$2y$10$SwcVXTlayRJ/On1/OZMDnOG7mOWafbLhPtVBKSS/AJYSqHQcfjH2C', 1, '', NULL, '', NULL, NULL, 'ssdsd', 'sdsdsdsddsdsdsdsdsdsd', 'sdsds', NULL, NULL, '2026-12-16 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-04 17:33:29', '2026-02-04 17:33:29'),
(2, 2, 16, NULL, 'MMYHR47D4B', '$2y$10$fh2hNZPwVLURVVXizItTWuhGadGz0UKvukYUeHqXQ6k8leaELJChu', 1, '', NULL, '', NULL, NULL, 'aaaaa', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'aaaaaa', NULL, NULL, NULL, 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-04 17:54:22', '2026-02-04 17:55:16'),
(3, 3, 23, NULL, 'PZLBRCFHJM', '$2y$10$oVeEVoC/82a11NGCMC0zIOMnuQ9tYwRo4eyW1G9E3keQaseC0pAgu', 0, 'Thuy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'sdsdsdsdsdsd', 'sdsdsdsdsdssdssdsds', 'sdsdsdsdsdsd', NULL, NULL, NULL, 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-04 18:15:15', NULL),
(4, 3, 23, NULL, 'RU6R3QE73V', '$2y$10$PLw5CmVMoJ0Bc7ashO/8vOVGISfgnOYKTHgLRQiWwe2nB4TBO3l9.', 0, 'Thuy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'sdsdsdsswewewewewe', 'eeeeeeeeeeeeeeeeeeeeeeeeeeeee', 'dzxvxsfdasds', NULL, NULL, '2026-12-16 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-04 18:15:44', NULL),
(5, 3, 24, NULL, 'CQKXE7TD3S', '$2y$10$OlbWtK0UOrCdPlxLw3zzHuFzVsjfKEQU9IetBwhSg6OFPKANFj5Ae', 1, '', NULL, '', NULL, NULL, 'ttttttttttttttttttttttttttttt', 'wwwwwwwwwwwwwwwwwwwwwwwwwwwww', 'eeeeeeeeeeeeeeeeeeeeeeeeeeee', NULL, NULL, '2026-12-16 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-04 18:16:14', NULL),
(6, 2, 14, NULL, 'H3ZW25WNVM', '$2y$10$8k1e6eAw1G/ReGaBbKmsEu4Gyz9K1rN0I5W08L.KV/R.raYsR7iya', 1, '', NULL, '', NULL, NULL, 'aaaaa', 'wwwwwwwwwwwwwwwwwwwwwwwwwwwww', 'văn phòng tym', NULL, NULL, '2026-12-16 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-04 18:25:08', NULL),
(7, 3, 21, NULL, 'FE3274Z3XQ', '$2y$10$Zmu0GLcuN0ZMXESns8Ouo.PQF8f9Q//taNlFQP7FdAUwuJn4MWrdS', 1, '', NULL, '', NULL, NULL, 'aaaaa', 'djfjdfsddfsdfrs', 'văn phòng tym', NULL, NULL, '2026-12-16 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-04 18:38:03', '2026-02-04 18:38:24'),
(8, 3, 22, NULL, 'DMQHKMB5PJ', '$2y$10$iDnOUTJ/cINrckSSE6h9r.hTiC2CceKSQiAzNxFGkfff9iyaDQg9G', 0, 'Thuy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'sdsdsdsswewewewewe', 'sddsdsdsdsdsdsdsdsdsd', 'aaaaaa', NULL, NULL, NULL, 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-04 18:39:46', NULL),
(9, 1, 11, NULL, 'XPP4RCYK7P', '$2y$10$iHmPPQMxAR1F.b3JCdRWvevfG/sQRtzpfNNSkMj4QrHAPt5nOqtnW', 1, '', NULL, '', NULL, NULL, 'aaaaa', 'ururrurusjshshsheheheheey', 'aaaaaa', NULL, NULL, '2026-02-04 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-04 19:05:42', NULL),
(10, 1, 10, NULL, '7VW249SX9F', '$2y$10$0MkqMSk06i5qP/91ixwqd.1PPO8WNXFSx8WvQxKBQ883c/RUkwvGa', 1, '', NULL, '', NULL, NULL, 'sdsdsdsdsdsd', 'dsfdsdfsdfsfdsfsdfsdfsdfsdf', 'aaaaaa', NULL, NULL, '2026-02-27 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-04 19:46:19', NULL),
(11, 2, 15, NULL, '8LTMPEX89Q', '$2y$10$DX6jrM1YNUjOXXDSYndcz.C3ztyUHH19xF5h3ISQFA1UGB9zSn0mu', 1, '', NULL, '', NULL, NULL, 'sdsdsdsswewewewewe', 'dffdddfdfdfdfdfdfdfdfdfd', 'aaaaaa', NULL, NULL, '2025-11-05 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-05 11:54:33', '2026-02-05 11:54:48'),
(12, 1, 9, NULL, 'PP9X2YZ7MG', '$2y$10$zLcaO.oLnAMbgIWI/4gQeuFXZia15WI3apmNJU50vRE1FRzXOJ9bK', 1, '', NULL, '', NULL, NULL, 'sdsdsdsswewewewewe', 'dfdfdfdfddfdfdfdfdfd', 'adadadadasdasd', NULL, NULL, '2026-02-26 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-09 16:34:41', NULL),
(13, 2, 15, NULL, 'E3TQ5S4RMX', '$2y$10$A/BeldYBLkh1nxCg1Gg87udzFBODsBO6lff3ZbkkaGuZaZ6T3fuka', 0, 'Thuy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'sdsdsdsswewewewewe', 'fffffffffffffffffffffffffffff', 'adadadadasdasd', NULL, NULL, '2026-02-10 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-09 19:51:20', NULL),
(14, 1, 10, NULL, '9TPXDQ7XU4', '$2y$10$3R3n/f3wF3UrRMPoIKiu8e/1mkgIHYdsq.O.WtI439XQxORL1f32y', 1, '', NULL, '', NULL, NULL, 'sdsdsdsswewewewewe', 'sssssssssssssssssssssssssssssssssssssss', 'aaaaaa', NULL, NULL, '2026-02-25 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-09 20:02:48', NULL),
(15, 1, 10, NULL, 'ZE526MQFME', '$2y$10$KEpcXx42z2hYmuHDTkgyL.QHzVSSBeYRzIfYiHPg1pHvpFi6VfaOS', 1, '', NULL, '', NULL, NULL, 'aaaaa', 'rtrtrtrtrtrtrtrtrt', 'aaaaaa', NULL, NULL, '2026-02-04 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-09 20:21:09', NULL),
(16, 1, 11, NULL, 'K6WPD58WA5', '$2y$10$XN/Mp2yX0TTKKZ4G7woYaeevwbBU9xyiV6WPdk8pnTpERnUBVycBK', 1, '', NULL, '', NULL, NULL, 'sdsdsdsswewewewewe', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'adadadadasdasd', NULL, NULL, '2026-02-18 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-09 20:24:03', NULL),
(17, 1, 11, NULL, '2VRELD5MWW', '$2y$10$8itIUTiBVisYgnMiR9mkV.RTIdki43FkpwaaYwxiCP9PfM39Blf9q', 1, '', NULL, '', NULL, NULL, 'sdsdsdsswewewewewe', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'adadadadasdasd', NULL, NULL, '2026-02-25 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-09 20:28:26', NULL),
(18, 1, 11, NULL, 'YNF6VLLJFM', '$2y$10$VIHcZRP08kfOjH9/zPhNiOht8c1hPBXHhzuXVn14twe7EzTzq9.rm', 1, '', NULL, '', NULL, NULL, 'sdsdsdsswewewewewe', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'adadadadasdasd', NULL, NULL, '2026-02-25 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-09 20:29:23', NULL),
(19, 1, 11, NULL, 'AQSJRZQSZY', '$2y$10$VunxzRt9dNUqdOty.shqouXnxol3BmGAEMPVQowcJlooSY3SFisu6', 1, '', NULL, '', NULL, NULL, 'sdsdsdsswewewewewe', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'adadadadasdasd', NULL, NULL, '2026-02-25 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-09 20:29:27', NULL),
(20, 3, 23, NULL, 'LQ9K5RBA94', '$2y$10$HfUzhIWS4p6DKmWJWQwKqO1bDv.uQfPuuHqWM3nAHmqj1qND7zefW', 1, '', NULL, '', NULL, NULL, 'sdsdsdsswewewewewe', 'aaaaaaaaaaaaaaaaaaaaaaaaaa', 'adadadadasdasd', NULL, NULL, '2026-02-24 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-09 20:35:03', NULL),
(21, 1, 10, NULL, 'LNQZWY8ARW', '$2y$10$f/g.z9AWPibKJxrRTHKiP..7NHMHMa5sv3SlcCnTSbYnyYhMiiThu', 1, '', NULL, '', NULL, NULL, 'Test', 'Hello hello hello hello hello', 'Oficina', NULL, NULL, '2026-02-11 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-10 11:47:37', '2026-02-10 11:47:53'),
(22, 1, 9, NULL, 'EWS5757T8S', '$2y$10$czjlJp2jVNH0W/I4Rp.t9OAVZfpUVmEwnsYdDrh8DfQ6CSyCNqfQG', 0, 'Thuyy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'Test 2', 'nothingggggggggggggggggggggg', 'Mina', NULL, NULL, '2026-01-15 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-10 13:04:45', NULL),
(23, 1, 11, NULL, 'B3DUB2Y7VZ', '$2y$10$evinS281OdsY2FkPMMgIk.8BcdEWsa0hJWZgObO0fY1M./KxEBw3C', 1, '', NULL, '', NULL, NULL, 'Test 3', 'Nothinggggggggggggggggggggggggggggg', 'Outdoor', NULL, NULL, '2025-12-11 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-10 13:06:20', NULL),
(24, 1, 9, NULL, 'NK6HQ7LKJ7', '$2y$10$GGtaGfW0FDHVXmM4zC0KeOMakqzoYOYbGay854zfW5S6lI76K/cJm', 1, '', NULL, '', NULL, NULL, 'aaaaa', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'adadadadasdasd', NULL, NULL, '2026-02-24 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-10 19:33:53', NULL),
(25, 1, 9, NULL, 'PQ63XQSXR2', '$2y$10$skkN5IgGPrn9RH6dorrBw.SVPMTJ1YR2U6PD3SgbyIdJVbJFxWD36', 1, '', NULL, '', NULL, NULL, 'aaaaa', 'vvvvvvvvvvvvvvvvvvvvvvvvvvvvvfdfsdffffffffffffff dddddddddddddddddddddddddddddddddddddddddddddddddddddddd', 'Oficina tecnica', NULL, NULL, '2026-02-25 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-10 19:38:05', NULL),
(26, 1, 9, NULL, 'VDQ5WJAF8L', '$2y$10$a6NepS3KQiGFN7dykQCTyeGgffpc7O50Ozht5n7pBeyEqXgbF1jey', 1, '', NULL, '', NULL, NULL, 'sdsdsdsswewewewewe', 'tttttttttttttttttttttttttttttttttttttttttttttttttt', 'aaaaaa', NULL, NULL, '2026-02-24 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-10 19:45:01', NULL),
(27, 1, 9, NULL, '488YUZQJMD', '$2y$10$IqVap3AA1lf946AJe6nQ5.u/6poVuC0P4A1pLgBi0R92lA3f8rUCq', 1, '', NULL, '', NULL, NULL, 'Test mail', 'mail mail mail mail mail', 'mail', NULL, NULL, '2026-02-10 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-10 20:14:56', NULL),
(28, 1, 9, NULL, 'TAU93PJ7S3', '$2y$10$/WU6xntDEFlyGJcSPzwmb.B8HHZmps/iXnK3RHrGy6uDsyof/ucYK', 1, '', NULL, '', NULL, NULL, 'aaaaa', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'aaaaaa', NULL, NULL, '2026-01-06 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 12:02:59', NULL),
(29, 1, 10, NULL, '8D5QMCNSXE', '$2y$10$PuVIF9N7pFZfakjPViiXMeXagk2qamzjQx3hVYnd4VS9H1BUwV0OS', 0, 'Thuy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'sdsdsdsswewewewewe', 'fdffdfdfdfdfdfdfdfd', 'aaaaaa', NULL, NULL, '2026-02-10 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 13:22:48', NULL),
(30, 1, 10, NULL, 'B2TLE8AT8B', '$2y$10$fveSStSxfWSi5EUmgGsVT.B9Xnq759C5Ysqi0elj7DRfm/uQFCH2u', 1, '', NULL, '', NULL, NULL, 'aaaaa', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'adadadadasdasd', NULL, NULL, '2026-02-11 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 13:24:12', NULL),
(31, 1, 9, NULL, 'CZ9Q8VLZZB', '$2y$10$dloJLaqMWKXOlU8L1qBmQ.LA4SlIxg/shteQXwq8PQTpjr5oWy7Q6', 1, '', NULL, '', NULL, NULL, 'aaaaa', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'adadadadasdasd', NULL, NULL, '2026-02-11 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 14:00:56', NULL),
(32, 1, 11, NULL, 'ETUW9QY9AJ', '$2y$10$DejPmcH.i0YAB3Uv4hXvP.P7VxHE0Zb9DTTFWvZhZnzIMRp.SWABS', 0, 'Thuy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'aaaaa', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'adadadadasdasd', NULL, NULL, '2026-02-11 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 14:01:24', NULL),
(33, 1, 10, NULL, '5ZVFYT5YVE', '$2y$10$nzHf543UBihdm5Cf87sWHeizJ4M57d3wQcpXj0Jf.zq1u.0KuLtza', 0, 'Thuy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'ttttttttttttttttttttttttttttt', 'aaaaaaaaaaaaaaaaaaaaaaaaa', 'Oficina tecnica', NULL, NULL, '2026-02-04 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 14:03:12', NULL),
(34, 1, 10, NULL, 'Q26XJ4CC62', '$2y$10$75FqEy5YPJZmr4d5fiV9ne.DN3emmt3AnoapFHzzJnsS1ue/PKZVK', 0, 'Thuy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'ttttttttttttttttttttttttttttt', 'aaaaaaaaaaaaaaaaaaaaaaaaa', 'Oficina tecnica', NULL, NULL, '2026-02-04 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 14:03:18', NULL),
(35, 1, 11, NULL, 'TEWKAQWTRM', '$2y$10$sxuZfZ30PZGXI0HYpIvXquV8k2Hvl.0YA9uFNHJXdGAH6c61E1NKW', 0, 'Thuyy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'ttttttttttttttttttttttttttttt', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'aaaaaa', NULL, NULL, '2026-02-03 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 14:03:53', NULL),
(36, 1, 10, NULL, 'K4TQU9BSAM', '$2y$10$a9ZAw3AQMZQGDLiMpuwoWeKcXae5MG1By9L2f7Bweb1OHaIGxN7Pu', 0, 'Thuy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'sdsdsdsswewewewewe', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'aaaaaa', NULL, NULL, '2026-02-03 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 14:06:41', NULL),
(37, 1, 9, NULL, 'A2NC7P4NC3', '$2y$10$bsebWjQd2jIUk6F70GVQy.XVWThSnsH6gBziFRhui.NyssmrxbBtm', 0, 'Thuyy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'sdsdsdsswewewewewe', 'AAAAAAAAAAAAAAAAAAAAAAAAA', 'aaaaaa', NULL, NULL, '2026-02-03 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 14:08:24', NULL),
(38, 1, 9, NULL, 'PJ5PN54V6Y', '$2y$10$/xwjtU5w8zS8K.QDXalus.gyTm5BVSR0bVQYEOnl8fn2cKQ85b07m', 0, 'Thuyy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'sdsdsdsswewewewewe', 'AAAAAAAAAAAAAAAAAAAAAAAAA', 'aaaaaa', NULL, NULL, '2026-02-03 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 14:14:12', NULL),
(39, 1, 9, NULL, 'H4QBUZSS7K', '$2y$10$2OoSkKMT4J6/TAxU9B5ZBOYknGC70cTlH2RBeEddIGsP9cD5.F7hm', 0, 'Thuy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'ttttttttttttttttttttttttttttt', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'aaaaaa', NULL, NULL, '2026-02-02 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 15:19:35', NULL),
(40, 1, 10, NULL, 'DJZH3EW3FC', '$2y$10$3dP0YPf56Z7EOTqA/QMT3.D5nuMNo8qT56eHsC1uTSOGtR1Wnhdjq', 1, '', NULL, '', NULL, NULL, 'sdsdsdsdsdsd', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'văn phòng tym', NULL, NULL, '2026-02-05 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 15:20:02', NULL),
(41, 1, 27, NULL, 'ZEHNM6DF9E', '$2y$10$L2rhZNYADk3yIvTATo7KC.XBMG3Cyfr68C.c4GEfMtuL4vPzyvPSC', 0, 'Thuyy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'ttttttttttttttttttttttttttttt', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'adadadadasdasd', NULL, NULL, '2026-02-04 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 15:48:11', NULL),
(42, 1, 30, NULL, 'KEH8NX578T', '$2y$10$oJhTqQCbjZLZv.TFwokRGOdm4SSD85L5lvkoWENEIHTljH71Y7Yyu', 0, 'Thuy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'sdsdsdsswewewewewe', 'aaaaaaaaaaaaaaaaaaaaaaaaaa', 'aaaaaa', NULL, NULL, '2026-02-03 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 15:56:52', NULL),
(43, 1, 30, NULL, '7UVB3DVWQ5', '$2y$10$rOgYyOCrga3h7ExpwhAY.e4/49CD3V3Rih57pJW/wDFGxJ0yG.oAK', 0, 'Thuy', NULL, 'thuynguyen200k@gmail.com', NULL, NULL, 'sdsdsdsswewewewewe', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'adadadadasdasd', NULL, NULL, '2026-02-03 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 15:59:14', NULL),
(44, 1, 28, NULL, 'VNVK88TY66', '$2y$10$nkDAATbTL3pUmeUEY7Jk6uGK6UWh/NI5Ic9cpsJFhauMKnvDo4MSO', 1, '', NULL, '', NULL, NULL, 'Test new view', 'sasassssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss', 'văn phòng tym', NULL, NULL, NULL, 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 0, NULL, NULL, NULL, NULL, '2026-02-11 18:04:36', NULL),
(45, 1, 30, NULL, 'MG5B78FRFU', '$2y$10$rSdmVGHj3.4Yie0Nh/apZ.XXV9wZx3USKZvaHsSx.gSFfDUhNWKJ2', 1, '', '', '', '', '', 'asasasasaasasasasasasas', 'asaasasasasasasasa', NULL, 'PROJECT', 'RRHHRRHHRRHHRRHH Test', '2026-02-06 00:00:00', 'SINGLE', NULL, 'YES', 0, NULL, 'NEW', 1, '2026-02-11 16:24:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-11 19:24:55', NULL),
(46, 1, 37, NULL, 'AYDXCH3UNC', '$2y$10$pCCoTJj5bvzdkg9jbgsk8OQtkUIH8zMWNUwZvys6Ds4GfNFtKYMs2', 0, 'Thuy Nguyen', '27279961k', 'thuynguyen200k@gmail.com', 'IT', '920952116', 'aaaaa', 'aaaaaaaaaaaaaaaaaaaaaaaaa', NULL, 'COMPANY', 'Finanzas', '2024-12-15 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 1, '2026-02-11 17:01:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-11 20:01:26', NULL),
(47, 1, 27, NULL, 'HQYQE2T8QV', '$2y$10$71u7ZcPpytU1.RSgFYvtoeUlzGofV7/fbl6XKVlA7kUIbt6hYhbbW', 0, 'Thuy', '27279961k', 'thuynguyen200k@gmail.com', 'IT', '920952116', 'ttttttttttttttttttttttttttttt', 'nothing happens', NULL, 'COMPANY', 'Finanzas', '1998-04-16 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 1, '2026-02-11 17:04:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-11 20:04:48', NULL),
(48, 1, 38, 'No', 'V542ZZTAJY', '$2y$10$8idczMoD1hCj/7mHzWn.TeRudAGv4H8bjJiM/d9xjT7t322K2nkGm', 0, 'Thuy', '27279961k', 'thuynguyen200k@gmail.com', 'IT', '920952116', 'sdsdsdsswewewewewe', 'aaaaaaaaaaaaaaa', NULL, 'COMPANY', 'Finanzas', '2021-12-18 00:00:00', 'SINGLE', NULL, 'YES', 0, NULL, 'NEW', 1, '2026-02-11 17:08:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-11 20:08:45', '2026-02-11 20:09:24'),
(49, 1, 38, NULL, 'XLZWH6R9HL', '$2y$10$atWsmqbx/sxIEhqNtabSp.UOHVCHNKjOy36DANYguN46ogoFqKTgy', 1, '', '', '', '', '', 'No', 'âsasasasasasasa', NULL, 'PROJECT', 'Finanzas', '2022-08-19 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 1, '2026-02-11 17:11:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-11 20:11:27', NULL),
(50, 1, 38, NULL, 'HK9PAB9WC8', '$2y$10$Uk4xkvsanW5wq83XmsP1BuLcjWbbIyupVgxtokycMADLKrymmavyK', 1, '', '', '', '', '', 'No', 'âsasasasasasasa', NULL, 'PROJECT', 'Finanzas', '2022-08-19 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'NEW', 1, '2026-02-11 17:11:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-11 20:11:49', NULL),
(51, 1, 31, 'No', 'TM2Z49ANA2', '$2y$10$XuZy0gF5N5ZItJE8Y7ZjouzI8tDdPE/7w8DhwxoYxPEaqscVamB8O', 0, 'Thuy', '27279961k', 'thuynguyen200k@gmail.com', 'IT', '920952116', 'Only for Test', 'Only for testing', 'Oficina tecnica', 'COMPANY', 'Finanzas', '1996-04-16 00:00:00', 'SINGLE', NULL, 'YES', 1, NULL, 'NEW', 1, '2026-02-12 12:23:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-12 15:23:15', NULL),
(52, 1, 34, 'No', 'WF2RS2V9NT', '$2y$10$7Y6xhCEby10Myp2TBUeCw.bZ1SosZ7hW3molEoeQMrF9tNDw3s7pi', 0, 'Thuy Nguyen', '27279961k', 'thuynguyen200k@gmail.com', 'IT', '920952116', 'Test for multiple documents', 'jejejejejejejeje......', 'Oficina tecnica', 'COMPANY', 'Finanzas', '2021-09-08 00:00:00', 'SINGLE', NULL, 'NO', 0, NULL, 'NEW', 1, '2026-02-12 16:11:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-12 19:11:39', NULL),
(53, 1, 32, NULL, 'JACUALJZPT', '$2y$10$nIoOmsRUYIPFCQV...JalOjbaadL5lZYPd/sSmSd.envILQ6vjXDS', 0, 'Thuy', '27279961k', 'thuynguyen200k@gmail.com', 'IT', '920952116', 'Test for multiple documents', 'aaaaaaaaaaaaaaaaaaa', 'văn phòng tym', 'PROJECT', 'Finanzas', '1996-11-18 00:00:00', 'SINGLE', NULL, 'NA', 0, NULL, 'WAITING_REPORTER', 1, '2026-02-12 16:23:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-12 19:23:26', '2026-02-16 19:38:33');

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
  `sha256` char(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_report_attachment`
--

INSERT INTO `portal_report_attachment` (`id`, `report_id`, `stored_path`, `original_name`, `mime_type`, `size_bytes`, `sha256`, `created_at`) VALUES
(1, 47, 'uploads/HQYQE2T8QV/62c721305777f07bf01b.pdf', 'third_eyes_requirement_tym.pdf', 'application/pdf', 709546, '97275dd847b73c8b1dab87b9bca4c26278344af3caf2008e937cf83939803019', '2026-02-11 20:04:48'),
(2, 49, 'uploads/XLZWH6R9HL/aab52460d50f255ddd2f.pdf', 'third_eyes_requirement_tym.pdf', 'application/pdf', 709546, '97275dd847b73c8b1dab87b9bca4c26278344af3caf2008e937cf83939803019', '2026-02-11 20:11:27'),
(3, 50, 'uploads/HK9PAB9WC8/07c0a8ac679df7628c69.pdf', 'third_eyes_requirement_tym.pdf', 'application/pdf', 709546, '97275dd847b73c8b1dab87b9bca4c26278344af3caf2008e937cf83939803019', '2026-02-11 20:11:49'),
(4, 51, 'uploads/TM2Z49ANA2/dcbc65a3bf9f3560f279.pdf', 'third_eyes_requirement_tym.pdf', 'application/pdf', 709546, '97275dd847b73c8b1dab87b9bca4c26278344af3caf2008e937cf83939803019', '2026-02-12 15:23:15'),
(5, 53, 'uploads/JACUALJZPT/1e7557e77fde912bc638.png', 'logo-foot.png', 'image/png', 24618, 'e070cfe0c8cb80291a7e787809881136e4f628b1beb46ee680763f1e2e31617a', '2026-02-12 19:23:26'),
(6, 53, 'uploads/JACUALJZPT/ae7e684650b31d8b8d1e.jpg', 'servicios_elctricos_tym_ltda_cover.jpg', 'image/jpeg', 58719, 'aa0073bec67c4a11e5c4031390e31e5e05a5626f41c2bee969620d919fdaa7b8', '2026-02-12 19:23:26'),
(7, 53, 'uploads/JACUALJZPT/3a57dd9acca8a499bb4e.pdf', 'T&M-SGI-PRO-01 Procedimiento Control de Información Documentada V2.pdf', 'application/pdf', 434781, '789a0fb5b674b035a7e3925c7215f26f941bcc17ab49bea8d34e717ede5d0103', '2026-02-12 19:23:26'),
(8, 53, 'uploads/JACUALJZPT/cb2ed31788b8756a05d6.pdf', 'third_eyes_requirement_tym.pdf', 'application/pdf', 709546, '97275dd847b73c8b1dab87b9bca4c26278344af3caf2008e937cf83939803019', '2026-02-12 19:23:26');

-- --------------------------------------------------------

--
-- Table structure for table `portal_report_evidence`
--

CREATE TABLE `portal_report_evidence` (
  `report_id` bigint(20) NOT NULL,
  `evidence_type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_report_evidence`
--

INSERT INTO `portal_report_evidence` (`report_id`, `evidence_type_id`) VALUES
(45, 6),
(46, 6),
(47, 1),
(48, 1),
(48, 2),
(49, 5),
(50, 5),
(51, 1),
(52, 2),
(52, 3),
(52, 4),
(53, 1);

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
(13, 9, 'REPORTER', 'ururrurusjshshsheheheheey', '2026-02-04 19:05:42'),
(14, 10, 'REPORTER', 'dsfdsdfsdfsfdsfsdfsdfsdfsdf', '2026-02-04 19:46:19'),
(15, 11, 'REPORTER', 'dffdddfdfdfdfdfdfdfdfdfd', '2026-02-05 11:54:33'),
(16, 11, 'REPORTER', 'ứdsdsdsdsd', '2026-02-05 11:54:48'),
(17, 12, 'REPORTER', 'dfdfdfdfddfdfdfdfdfd', '2026-02-09 16:34:41'),
(18, 13, 'REPORTER', 'fffffffffffffffffffffffffffff', '2026-02-09 19:51:20'),
(19, 14, 'REPORTER', 'sssssssssssssssssssssssssssssssssssssss', '2026-02-09 20:02:48'),
(20, 15, 'REPORTER', 'rtrtrtrtrtrtrtrtrt', '2026-02-09 20:21:09'),
(21, 16, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-09 20:24:03'),
(22, 17, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-09 20:28:26'),
(23, 18, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-09 20:29:23'),
(24, 19, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-09 20:29:27'),
(25, 20, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-09 20:35:03'),
(26, 21, 'REPORTER', 'Hello hello hello hello hello', '2026-02-10 11:47:37'),
(27, 21, 'REPORTER', 'Hi Hi Hi Hi', '2026-02-10 11:47:53'),
(28, 22, 'REPORTER', 'nothingggggggggggggggggggggg', '2026-02-10 13:04:45'),
(29, 23, 'REPORTER', 'Nothinggggggggggggggggggggggggggggg', '2026-02-10 13:06:20'),
(30, 24, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-10 19:33:53'),
(31, 25, 'REPORTER', 'vvvvvvvvvvvvvvvvvvvvvvvvvvvvvfdfsdffffffffffffff dddddddddddddddddddddddddddddddddddddddddddddddddddddddd', '2026-02-10 19:38:05'),
(32, 26, 'REPORTER', 'tttttttttttttttttttttttttttttttttttttttttttttttttt', '2026-02-10 19:45:01'),
(33, 27, 'REPORTER', 'mail mail mail mail mail', '2026-02-10 20:14:56'),
(34, 28, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-11 12:02:59'),
(35, 29, 'REPORTER', 'fdffdfdfdfdfdfdfdfd', '2026-02-11 13:22:48'),
(36, 30, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-11 13:24:12'),
(37, 31, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-11 14:00:56'),
(38, 32, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-11 14:01:24'),
(39, 33, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-11 14:03:12'),
(40, 34, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-11 14:03:18'),
(41, 35, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-11 14:03:53'),
(42, 36, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-11 14:06:41'),
(43, 37, 'REPORTER', 'AAAAAAAAAAAAAAAAAAAAAAAAA', '2026-02-11 14:08:24'),
(44, 38, 'REPORTER', 'AAAAAAAAAAAAAAAAAAAAAAAAA', '2026-02-11 14:14:12'),
(45, 39, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-11 15:19:35'),
(46, 40, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-11 15:20:02'),
(47, 41, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-11 15:48:11'),
(48, 42, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-11 15:56:52'),
(49, 43, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-11 15:59:14'),
(50, 44, 'REPORTER', 'sasassssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss', '2026-02-11 18:04:36'),
(51, 45, 'REPORTER', 'asaasasasasasasasa', '2026-02-11 19:24:55'),
(52, 46, 'REPORTER', 'aaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-11 20:01:26'),
(53, 47, 'REPORTER', 'nothing happens', '2026-02-11 20:04:48'),
(54, 48, 'REPORTER', 'aaaaaaaaaaaaaaa', '2026-02-11 20:08:45'),
(55, 48, 'REPORTER', 'hello', '2026-02-11 20:09:24'),
(56, 49, 'REPORTER', 'âsasasasasasasa', '2026-02-11 20:11:27'),
(57, 50, 'REPORTER', 'âsasasasasasasa', '2026-02-11 20:11:49'),
(58, 51, 'REPORTER', 'Only for testing', '2026-02-12 15:23:15'),
(59, 52, 'REPORTER', 'jejejejejejejeje......', '2026-02-12 19:11:39'),
(60, 53, 'REPORTER', 'aaaaaaaaaaaaaaaaaaa', '2026-02-12 19:23:26'),
(61, 53, 'REPORTER', 'Hello', '2026-02-12 19:27:22'),
(62, 53, 'INVESTIGATOR', 'pending', '2026-02-16 13:22:29'),
(63, 53, 'REPORTER', 'Ok Ok Ok Ok', '2026-02-16 19:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `portal_report_person`
--

CREATE TABLE `portal_report_person` (
  `id` bigint(20) NOT NULL,
  `report_id` bigint(20) NOT NULL,
  `role` enum('ACCUSED','WITNESS') NOT NULL,
  `full_name` varchar(160) DEFAULT NULL,
  `position` varchar(160) DEFAULT NULL,
  `company` varchar(160) DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_report_person`
--

INSERT INTO `portal_report_person` (`id`, `report_id`, `role`, `full_name`, `position`, `company`, `notes`, `created_at`) VALUES
(1, 46, 'ACCUSED', 'Thuy', 'IT', 'TyM', '', '2026-02-11 20:01:26'),
(2, 46, 'WITNESS', 'Thuyy', 'Innovación', '', '', '2026-02-11 20:01:26'),
(3, 51, 'ACCUSED', 'Alex', 'Finanzas A', 'TyM', '', '2026-02-12 15:23:15'),
(4, 51, 'WITNESS', 'Leo', 'Finanzas B', 'TyM', '', '2026-02-12 15:23:15'),
(5, 52, 'ACCUSED', 'Thuy', 'IT', 'TyM', '', '2026-02-12 19:11:39'),
(6, 52, 'WITNESS', 'Thuyy', 'Innovación', 'TyM', '', '2026-02-12 19:11:39');

-- --------------------------------------------------------

--
-- Table structure for table `portal_report_status_history`
--

CREATE TABLE `portal_report_status_history` (
  `id` bigint(20) NOT NULL,
  `report_id` bigint(20) NOT NULL,
  `old_status` varchar(32) NOT NULL,
  `new_status` varchar(32) NOT NULL,
  `note` varchar(500) DEFAULT NULL,
  `changed_by_admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_report_status_history`
--

INSERT INTO `portal_report_status_history` (`id`, `report_id`, `old_status`, `new_status`, `note`, `changed_by_admin_id`, `created_at`) VALUES
(1, 1, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-04 17:33:29'),
(2, 2, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-04 17:54:22'),
(3, 3, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-04 18:15:15'),
(4, 4, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-04 18:15:44'),
(5, 5, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-04 18:16:14'),
(6, 6, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-04 18:25:08'),
(7, 7, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-04 18:38:03'),
(8, 8, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-04 18:39:46'),
(9, 9, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-04 19:05:42'),
(10, 10, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-04 19:46:19'),
(11, 11, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-05 11:54:33'),
(12, 12, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-09 16:34:41'),
(13, 13, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-09 19:51:20'),
(14, 14, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-09 20:02:48'),
(15, 15, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-09 20:21:09'),
(16, 16, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-09 20:24:03'),
(17, 17, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-09 20:28:26'),
(18, 18, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-09 20:29:23'),
(19, 19, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-09 20:29:27'),
(20, 20, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-09 20:35:03'),
(21, 21, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-10 11:47:37'),
(22, 22, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-10 13:04:45'),
(23, 23, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-10 13:06:20'),
(24, 24, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-10 19:33:53'),
(25, 25, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-10 19:38:05'),
(26, 26, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-10 19:45:01'),
(27, 27, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-10 20:14:56'),
(28, 28, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 12:02:59'),
(29, 29, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 13:22:48'),
(30, 30, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 13:24:12'),
(31, 31, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 14:00:56'),
(32, 32, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 14:01:24'),
(33, 33, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 14:03:12'),
(34, 34, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 14:03:18'),
(35, 35, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 14:03:53'),
(36, 36, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 14:06:41'),
(37, 37, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 14:08:24'),
(38, 38, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 14:14:12'),
(39, 39, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 15:19:35'),
(40, 40, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 15:20:02'),
(41, 41, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 15:48:11'),
(42, 42, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 15:56:52'),
(43, 43, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 15:59:14'),
(44, 44, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 18:04:36'),
(45, 45, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 19:24:55'),
(46, 46, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 20:01:26'),
(47, 47, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 20:04:48'),
(48, 48, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 20:08:45'),
(49, 49, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 20:11:27'),
(50, 50, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-11 20:11:49'),
(51, 51, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-12 15:23:15'),
(52, 52, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-12 19:11:39'),
(53, 53, 'NEW', 'NEW', 'Creación de caso', NULL, '2026-02-12 19:23:26'),
(64, 53, 'NEW', 'PENDING', '', 1, '2026-02-16 13:22:17'),
(65, 53, 'PENDING', 'IN_REVIEW', 'In review now', 1, '2026-02-16 16:32:59'),
(66, 53, 'IN_REVIEW', 'WAITING_REPORTER', '', 1, '2026-02-16 18:43:28');

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
-- Indexes for table `portal_admin_login_attempt`
--
ALTER TABLE `portal_admin_login_attempt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_time` (`email`,`created_at`);

--
-- Indexes for table `portal_admin_user`
--
ALTER TABLE `portal_admin_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admin_email` (`email`);

--
-- Indexes for table `portal_audit_log`
--
ALTER TABLE `portal_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_al_report` (`report_id`);

--
-- Indexes for table `portal_category`
--
ALTER TABLE `portal_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_company_code` (`company_id`,`code`),
  ADD KEY `idx_group_id` (`group_id`);

--
-- Indexes for table `portal_category_group`
--
ALTER TABLE `portal_category_group`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_company_group_code` (`company_id`,`code`);

--
-- Indexes for table `portal_company`
--
ALTER TABLE `portal_company`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `portal_evidence_type`
--
ALTER TABLE `portal_evidence_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ev_code` (`code`);

--
-- Indexes for table `portal_notify_recipient`
--
ALTER TABLE `portal_notify_recipient`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_company_cat_email` (`company_id`,`category_id`,`email`),
  ADD KEY `idx_company` (`company_id`),
  ADD KEY `idx_category` (`category_id`);

--
-- Indexes for table `portal_report`
--
ALTER TABLE `portal_report`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_key` (`report_key`),
  ADD KEY `fk_r_category` (`category_id`),
  ADD KEY `idx_status_created` (`status`,`created_at`),
  ADD KEY `idx_company_status_created` (`company_id`,`status`,`created_at`);

--
-- Indexes for table `portal_report_attachment`
--
ALTER TABLE `portal_report_attachment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ra_report` (`report_id`);

--
-- Indexes for table `portal_report_evidence`
--
ALTER TABLE `portal_report_evidence`
  ADD PRIMARY KEY (`report_id`,`evidence_type_id`),
  ADD KEY `idx_evi_type` (`evidence_type_id`);

--
-- Indexes for table `portal_report_message`
--
ALTER TABLE `portal_report_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rm_report` (`report_id`);

--
-- Indexes for table `portal_report_person`
--
ALTER TABLE `portal_report_person`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rp_report` (`report_id`);

--
-- Indexes for table `portal_report_status_history`
--
ALTER TABLE `portal_report_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_report_time` (`report_id`,`created_at`),
  ADD KEY `fk_prsh_admin` (`changed_by_admin_id`);

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
-- AUTO_INCREMENT for table `portal_admin_login_attempt`
--
ALTER TABLE `portal_admin_login_attempt`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `portal_admin_user`
--
ALTER TABLE `portal_admin_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `portal_audit_log`
--
ALTER TABLE `portal_audit_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `portal_category`
--
ALTER TABLE `portal_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `portal_category_group`
--
ALTER TABLE `portal_category_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `portal_company`
--
ALTER TABLE `portal_company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `portal_evidence_type`
--
ALTER TABLE `portal_evidence_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `portal_notify_recipient`
--
ALTER TABLE `portal_notify_recipient`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `portal_report`
--
ALTER TABLE `portal_report`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `portal_report_attachment`
--
ALTER TABLE `portal_report_attachment`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `portal_report_message`
--
ALTER TABLE `portal_report_message`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `portal_report_person`
--
ALTER TABLE `portal_report_person`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `portal_report_status_history`
--
ALTER TABLE `portal_report_status_history`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `portal_resource`
--
ALTER TABLE `portal_resource`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `portal_audit_log`
--
ALTER TABLE `portal_audit_log`
  ADD CONSTRAINT `fk_al_report` FOREIGN KEY (`report_id`) REFERENCES `portal_report` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `portal_category`
--
ALTER TABLE `portal_category`
  ADD CONSTRAINT `fk_pc_company` FOREIGN KEY (`company_id`) REFERENCES `portal_company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pc_group` FOREIGN KEY (`group_id`) REFERENCES `portal_category_group` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `portal_category_group`
--
ALTER TABLE `portal_category_group`
  ADD CONSTRAINT `fk_pcg_company` FOREIGN KEY (`company_id`) REFERENCES `portal_company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `portal_report_evidence`
--
ALTER TABLE `portal_report_evidence`
  ADD CONSTRAINT `fk_pre_report` FOREIGN KEY (`report_id`) REFERENCES `portal_report` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pre_type` FOREIGN KEY (`evidence_type_id`) REFERENCES `portal_evidence_type` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `portal_report_message`
--
ALTER TABLE `portal_report_message`
  ADD CONSTRAINT `fk_rm_report` FOREIGN KEY (`report_id`) REFERENCES `portal_report` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `portal_report_person`
--
ALTER TABLE `portal_report_person`
  ADD CONSTRAINT `fk_rp_report` FOREIGN KEY (`report_id`) REFERENCES `portal_report` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `portal_report_status_history`
--
ALTER TABLE `portal_report_status_history`
  ADD CONSTRAINT `fk_prsh_admin` FOREIGN KEY (`changed_by_admin_id`) REFERENCES `portal_admin_user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prsh_report` FOREIGN KEY (`report_id`) REFERENCES `portal_report` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `portal_resource`
--
ALTER TABLE `portal_resource`
  ADD CONSTRAINT `fk_pr_company` FOREIGN KEY (`company_id`) REFERENCES `portal_company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
