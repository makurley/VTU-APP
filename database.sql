-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2024 at 08:00 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vtu`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_settings`
--

CREATE TABLE `api_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `value` varchar(255) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `api_settings`
--

INSERT INTO `api_settings` (`id`, `name`, `value`, `created_at`, `updated_at`) VALUES
(1, 'mtn_airtime', 'vtu', '2023-06-28 21:23:36', '2024-04-08 16:02:49'),
(2, 'glo_airtime', 'glad', '2023-06-28 21:23:36', '2023-10-25 05:25:02'),
(3, 'airtel_airtime', 'glad', '2023-06-28 21:23:36', '2023-10-25 05:25:02'),
(4, 'mob_airtime', 'glad', '2023-06-28 21:23:36', '2023-10-25 05:25:02'),
(5, 'cable_api', 'glad', '2023-06-28 23:03:44', '2024-04-08 16:03:03'),
(6, 'power_api', 'glad', '2023-06-28 23:03:44', '2024-04-08 16:03:03'),
(7, 'exam_api', 'glad', '2023-06-28 23:03:44', '2024-04-08 16:03:03'),
(8, 'rechargepin', 'glad', '2023-06-28 23:03:44', '2023-06-28 23:03:44'),
(9, 'bulksms', 'n3tdata', '2023-06-28 23:03:44', '2023-06-28 23:03:44'),
(10, 'mtn_airtimepin', 'glad', '2023-06-29 08:54:55', '2023-10-24 12:43:07'),
(11, 'glo_airtimepin', 'glad', '2023-06-29 08:54:55', '2023-10-24 12:43:07'),
(12, 'airtel_airtimepin', 'glad', '2023-06-29 08:54:55', '2023-10-24 12:43:07'),
(13, 'mob_airtimepin', 'glad', '2023-06-29 08:54:55', '2023-10-24 12:43:07'),
(14, 'mtn_data', 'legit', '2023-06-29 08:55:12', '2024-02-19 15:46:08'),
(15, 'glo_data', 'glad', '2023-06-29 08:55:12', '2024-02-16 07:20:49'),
(16, 'airtel_data', 'legit', '2023-06-29 08:55:12', '2024-02-26 12:41:44'),
(17, 'mob_data', 'glad', '2023-06-29 08:55:12', '2023-11-13 15:02:58'),
(18, 'mtn_datacard', 'glad', '2023-06-29 08:55:29', '2023-07-11 10:09:12'),
(19, 'glo_datacard', 'glad', '2023-06-29 08:55:29', '2023-06-29 08:55:29'),
(20, 'airtel_datacard', 'glad', '2023-06-29 08:55:29', '2023-07-11 10:09:12'),
(21, 'mob_datacard', 'glad', '2023-06-29 08:55:29', '2023-06-29 08:55:29'),
(22, 'mtncg_data', 'glad', '2023-09-18 07:29:53', '2024-02-23 08:13:42');

-- --------------------------------------------------------

--
-- Table structure for table `cable_plans`
--

CREATE TABLE `cable_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `decoder_id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `glad` varchar(50) DEFAULT NULL,
  `vtu` varchar(100) DEFAULT NULL,
  `legit` varchar(100) DEFAULT NULL,
  `n3tdata` varchar(50) DEFAULT NULL,
  `maska` varchar(50) DEFAULT NULL,
  `vtpass` varchar(20) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price` double(20,2) NOT NULL,
  `api` double(20,2) NOT NULL DEFAULT 0.00,
  `reseller` double(20,2) NOT NULL DEFAULT 100.00,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `deleted` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cable_plans`
--

INSERT INTO `cable_plans` (`id`, `decoder_id`, `code`, `glad`, `vtu`, `legit`, `n3tdata`, `maska`, `vtpass`, `name`, `price`, `api`, `reseller`, `status`, `deleted`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '7', '7', NULL, '1', '6', '8', '90', 'DSTV PADI (N2950)', 2950.00, 2950.00, 2950.00, 1, 0, '2022-10-24 17:33:32', '2023-11-18 06:29:56', NULL),
(2, 2, '35', '35', 'gotv-max', '59', '8', '5', NULL, 'GOTV MAX (4860)', 4860.00, 4855.00, 4860.00, 1, 0, '2022-10-25 18:00:56', '2024-04-08 15:50:15', NULL),
(3, 2, '17', '17', 'gotv-jolli', '60', '6', '8', NULL, 'GOTV JOLLI (N3310)', 3310.00, 3305.00, 3310.00, 1, 0, '2022-10-25 18:02:23', '2024-04-08 15:51:34', NULL),
(4, 2, '16', '16', NULL, '61', '5', '5', '0', 'GOTV JINJA (N2260)', 2260.00, 2255.00, 2255.00, 1, 0, '2022-11-01 11:39:10', '2024-01-23 18:28:24', NULL),
(5, 2, '47', '47', NULL, '62', '4', '6', NULL, 'GOTV SMALLIE MONTHLY (N1110)', 1110.00, 1105.00, 1100.00, 1, 0, '2022-11-01 11:40:02', '2023-10-08 10:09:54', NULL),
(6, 2, '2', '2', NULL, '63', '5', '5', NULL, 'GOTV SMALLIE QUARTERLY (N2910)', 2910.00, 2905.00, 2910.00, 1, 0, '2022-11-01 11:41:02', '2023-10-08 10:10:09', NULL),
(7, 1, '23', '23', NULL, '2', '6', '4', 'dstv-yanga', 'DSTV YANGA (N4200)', 4200.00, 4200.00, 4200.00, 1, 0, '2022-11-01 11:48:14', '2023-11-07 10:30:09', NULL),
(8, 1, '33', '33', NULL, '3', '4', '5', 'dstv-confam', 'DSTV CONFAM (N7400)', 7400.00, 7400.00, 7400.00, 1, 0, '2022-11-01 11:49:03', '2023-11-07 10:31:07', NULL),
(9, 1, '9', '9', NULL, '4', '6', '4', 'dstv79', 'DSTV COMPACT (N12500)', 12500.00, 12500.00, 12500.00, 1, 0, '2022-11-01 11:49:49', '2023-11-07 10:32:19', NULL),
(10, 3, '42', '42', NULL, NULL, '4', '4', NULL, 'Nova 1 Day N100', 100.00, 100.00, 100.00, 0, 0, '2022-12-13 20:30:40', '2023-10-08 09:38:08', NULL),
(11, 1, '6', '6', NULL, '5', '2', '7', 'dstv3', 'DSTV PREMIUM (N29500)', 29500.00, 29500.00, 29500.00, 1, 0, '2023-05-04 07:33:02', '2023-11-07 10:41:13', NULL),
(12, 1, '22', '22', NULL, '6', '5', '6', 'dstv6', 'DSTV ASIA (N9900)', 9900.00, 9900.00, 9900.00, 1, 0, '2023-05-04 07:34:20', '2023-11-07 10:42:17', NULL),
(13, 1, '8', '8', NULL, '7', '5', '6', 'dstv7', 'DSTV COMPACT PLUS (N19800)', 19800.00, 19800.00, 19800.00, 1, 0, '2023-05-04 07:35:52', '2023-11-07 10:43:22', NULL),
(14, 1, '34', '34', NULL, '10', '5', '7', 'confam-extra', 'DSTV CONFAM EXTRAVIEW (N11400)', 11400.00, 11400.00, 11400.00, 1, 0, '2023-05-04 07:37:07', '2024-02-29 03:16:18', NULL),
(15, 2, '52', '52', NULL, '64', '6', '4', NULL, 'GOTV SMALLIE YEARLY (N8610)', 8610.00, 8605.00, 8610.00, 1, 0, '2023-05-04 07:48:41', '2023-10-08 10:10:37', NULL),
(16, 2, '36', '36', NULL, '65', '5', '4', NULL, 'GOTV SUPA (N6410)', 6410.00, 6405.00, 6410.00, 1, 0, '2023-05-04 07:49:47', '2023-10-08 10:10:55', NULL),
(17, 3, '43', '43', NULL, NULL, '5', '5', NULL, 'BASIC 1 DAY N200', 200.00, 200.00, 200.00, 0, 0, '2023-05-04 07:56:35', '2023-10-08 09:38:14', NULL),
(18, 3, '44', '44', NULL, NULL, '5', '5', NULL, 'SMART 1DAY N250', 250.00, 250.00, 250.00, 0, 0, '2023-05-04 08:00:39', '2023-10-08 09:38:19', NULL),
(19, 3, '45', '45', NULL, NULL, '5', '5', NULL, 'CLASSIC 1DAY N320', 320.00, 320.00, 320.00, 0, 0, '2023-05-04 08:03:22', '2023-10-08 09:38:26', NULL),
(20, 3, '37', '37', NULL, '71', '4', '5', NULL, 'NOVA 1WEEK N410', 410.00, 405.00, 410.00, 1, 0, '2023-05-04 08:04:27', '2023-10-08 09:32:53', NULL),
(21, 3, '46', '46', NULL, NULL, '7', '6', NULL, 'SUPER 1DAY N500', 500.00, 400.00, 500.00, 0, 0, '2023-05-04 08:06:00', '2023-10-08 09:38:39', NULL),
(22, 3, '38', '38', NULL, '72', '5', '6', NULL, 'BASIC 1WEEK N710', 710.00, 705.00, 710.00, 1, 0, '2023-05-04 08:07:56', '2023-10-08 09:34:30', NULL),
(23, 3, '39', '39', NULL, '73', '6', '5', NULL, 'SMART 1WEEK N910', 910.00, 905.00, 910.00, 1, 0, '2023-05-04 08:09:20', '2023-10-08 09:36:11', NULL),
(24, 3, '54', '54', NULL, '66', '8', '6', NULL, 'NOVA 1MONTH N1210', 1210.00, 1205.00, 1210.00, 1, 0, '2023-05-04 08:13:14', '2023-10-08 09:25:31', NULL),
(25, 3, '40', '40', NULL, '74', '5', '5', NULL, 'CLASSIC 1WEEK N1210', 1210.00, 1205.00, 1210.00, 1, 0, '2023-05-04 08:14:22', '2023-10-08 09:37:08', NULL),
(26, 3, '41', '41', NULL, '75', '4', '5', NULL, 'SUPER 1WEEK N1810', 1810.00, 1805.00, 1810.00, 1, 0, '2023-05-04 08:15:40', '2023-10-08 09:38:00', NULL),
(27, 3, '49', '49', NULL, '67', '6', '7', NULL, 'BASIC 1MONTH N2110', 2110.00, 2105.00, 2105.00, 1, 0, '2023-05-04 08:17:40', '2023-10-08 09:27:32', NULL),
(28, 3, '51', '51', NULL, '68', '5', '4', NULL, 'SMART 1MONTH N2810', 2810.00, 2805.00, 2805.00, 1, 0, '2023-05-04 08:18:47', '2023-10-08 09:29:19', NULL),
(29, 3, '50', '50', NULL, '69', '4', '5', NULL, 'CLASSIC 1MONTH N3110', 3110.00, 3105.00, 3105.00, 1, 0, '2023-05-04 08:20:16', '2023-10-08 09:30:25', NULL),
(30, 3, '48', '48', NULL, '70', '5', '5', NULL, 'SUPER 1MONTH N5310', 5310.00, 5305.00, 5305.00, 1, 0, '2023-05-04 08:21:18', '2023-10-08 09:31:29', NULL),
(31, 2, '55', '55', NULL, '30', '30', '30', NULL, 'GOTV- Supa plus monthly N10500', 10500.00, 10500.00, 10500.00, 0, 0, '2023-08-17 05:34:57', '2023-10-05 06:26:43', NULL),
(32, 1, '30', '30', NULL, '11', '30', '30', 'yanga-extra', 'DSTV YANGA EXTRAVIEW (N8200)', 8200.00, 8200.00, 8200.00, 1, 0, '2023-10-05 06:00:25', '2024-02-29 03:16:31', NULL),
(33, 1, '30', '30', NULL, '12', '30', '30', 'padi-extra', 'DSTV PADI EXTRAVIEW (N6950)', 6950.00, 6950.00, 6950.00, 1, 0, '2023-10-05 06:04:29', '2023-11-07 10:48:47', NULL),
(34, 1, '30', '30', NULL, '14', '30', '30', NULL, 'DSTV COMPACT EXTRAVIEW (N13910)', 13910.00, 13905.00, 13905.00, 0, 0, '2023-10-05 06:06:10', '2023-11-07 10:43:49', NULL),
(35, 1, '30', '30', NULL, '16', '30', '30', NULL, 'DSTV PREMIUM EXTRAVIEW (27910)', 27910.00, 27905.00, 27910.00, 0, 0, '2023-10-05 06:07:46', '2023-11-07 10:43:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `datacard_plans`
--

CREATE TABLE `datacard_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `glad` varchar(50) DEFAULT NULL,
  `vtu` varchar(100) DEFAULT NULL,
  `legit` varchar(100) DEFAULT NULL,
  `maska` varchar(50) DEFAULT NULL,
  `n3tdata` varchar(50) DEFAULT NULL,
  `service` varchar(255) DEFAULT NULL,
  `network_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` double(20,2) NOT NULL,
  `api` double(20,2) NOT NULL DEFAULT 0.00,
  `reseller` double(20,2) NOT NULL DEFAULT 100.00,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `deleted` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `datacard_plans`
--

INSERT INTO `datacard_plans` (`id`, `code`, `glad`, `vtu`, `legit`, `maska`, `n3tdata`, `service`, `network_id`, `name`, `size`, `image`, `price`, `api`, `reseller`, `status`, `deleted`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '1', NULL, NULL, NULL, NULL, '1', NULL, 1, 'MTN CG 1.5GB (N295)', '1.5GB', NULL, 295.00, 2950.00, 295.00, 1, 0, '2023-04-20 16:03:28', '2023-06-28 11:22:38', NULL),
(2, '2', NULL, NULL, NULL, NULL, '2', NULL, 1, 'MTN SME 500MB (N120)', '500MB', NULL, 120.00, 120.00, 120.00, 0, 0, '2023-04-20 16:25:02', '2023-07-11 10:10:50', NULL),
(3, '3', NULL, NULL, NULL, NULL, '3', NULL, 1, 'MTN SME 1GB (N230)', '1GB', NULL, 230.00, 230.00, 230.00, 0, 0, '2023-04-21 01:50:00', '2023-07-11 10:11:13', NULL),
(4, '13', NULL, NULL, NULL, NULL, '13', NULL, 2, 'GLO CG 200MB (N80)', '200MB', NULL, 80.00, 90.00, 79.00, 1, 0, '2023-05-06 00:32:36', '2023-05-07 07:13:08', NULL),
(5, '4', NULL, NULL, NULL, NULL, '4', NULL, 1, 'MTN SME 2GB (N460)', '2GB', NULL, 460.00, 460.00, 460.00, 0, 0, '2023-05-06 12:18:11', '2023-07-11 10:11:21', NULL),
(6, '5', NULL, NULL, NULL, NULL, '5', NULL, 1, 'MTN SME 3GB (N690)', '3GB', NULL, 690.00, 690.00, 690.00, 0, 0, '2023-05-06 12:18:59', '2023-07-11 10:11:31', NULL),
(7, '6', NULL, NULL, NULL, NULL, '6', NULL, 1, 'MTN SME 5GB (N1150)', '5GB', NULL, 1150.00, 1150.00, 1150.00, 0, 0, '2023-05-06 12:19:55', '2023-07-11 10:11:37', NULL),
(8, '7', NULL, NULL, NULL, NULL, '7', NULL, 1, 'MTN SME 10GB (N2300)', '10GB', NULL, 2300.00, 2300.00, 2300.00, 0, 0, '2023-05-06 12:20:46', '2023-07-11 10:10:44', NULL),
(9, '20', NULL, NULL, NULL, NULL, '20', NULL, 1, 'MTN CG 750MB (N140)', '750MB', NULL, 140.00, 140.00, 140.00, 1, 0, '2023-05-06 12:21:43', '2023-05-25 08:27:23', NULL),
(10, '14', NULL, NULL, NULL, NULL, '14', NULL, 2, 'GLO CG 500MB (N130)', '500MB', NULL, 130.00, 130.00, 130.00, 1, 0, '2023-05-07 07:17:21', '2023-05-07 07:17:21', NULL),
(11, '15', NULL, NULL, NULL, NULL, '15', NULL, 2, 'GLO CG 1GB (N230)', '1GB', NULL, 230.00, 230.00, 230.00, 1, 0, '2023-05-07 07:18:31', '2023-05-07 07:18:31', NULL),
(12, '16', NULL, NULL, NULL, NULL, '16', NULL, 2, 'GLO CG 2GB (N460)', '2GB', NULL, 460.00, 460.00, 460.00, 1, 0, '2023-05-07 07:19:30', '2023-05-07 07:19:30', NULL),
(13, '17', NULL, NULL, NULL, NULL, '17', NULL, 2, 'GLO CG 3GB (N690)', '3GB', NULL, 690.00, 690.00, 490.00, 1, 0, '2023-05-07 07:21:42', '2023-05-07 07:21:42', NULL),
(14, '18', NULL, NULL, NULL, NULL, '18', NULL, 2, 'GLO CG 5GB (N1150)', '5GB', NULL, 1150.00, 1150.00, 1150.00, 1, 0, '2023-05-07 07:23:03', '2023-05-07 07:23:03', NULL),
(15, '19', NULL, NULL, NULL, NULL, '19', NULL, 2, 'GLO CG 10GB (N2300)', '10GB', NULL, 2300.00, 2300.00, 2300.00, 1, 0, '2023-05-07 07:24:28', '2023-05-07 07:24:28', NULL),
(16, '8', NULL, NULL, NULL, NULL, '8', NULL, 3, 'AIRTEL CG 500MB (N125)', '500MB', NULL, 125.00, 125.00, 125.00, 1, 0, '2023-05-07 07:26:46', '2023-05-07 07:27:59', NULL),
(17, '9', NULL, NULL, NULL, NULL, '9', NULL, 3, 'AIRTEL CG 1GB (N230)', '1GB', NULL, 230.00, 230.00, 230.00, 1, 0, '2023-05-07 07:27:42', '2023-05-07 07:27:42', NULL),
(18, '10', NULL, NULL, NULL, NULL, '10', NULL, 3, 'AIRTEL CG 2GB (N460)', '2GB', NULL, 460.00, 460.00, 460.00, 1, 0, '2023-05-07 07:28:56', '2023-05-07 07:28:56', NULL),
(19, '11', NULL, NULL, NULL, NULL, '11', NULL, 3, 'AIRTEL CG 5GB (N1150)', '5GB', NULL, 1150.00, 0.00, 100.00, 1, 0, '2023-05-07 07:29:59', '2023-05-07 07:29:59', NULL),
(20, '12', NULL, NULL, NULL, NULL, '12', NULL, 3, 'AIRTEL CG 10GB (N2300)', '10GB', NULL, 2300.00, 2300.00, 2300.00, 1, 0, '2023-05-07 07:30:52', '2023-05-07 07:30:52', NULL),
(21, '22', NULL, NULL, NULL, NULL, '22', NULL, 1, 'MTN CG 1GB (N205)', '1GB', NULL, 205.00, 204.00, 204.00, 1, 0, '2023-05-25 06:02:14', '2023-06-06 18:23:03', NULL),
(22, '21', NULL, NULL, NULL, NULL, '21', NULL, 1, 'MTN CG 2GB (N410)', '2GB', NULL, 410.00, 410.00, 410.00, 1, 0, '2023-05-25 06:03:26', '2023-07-01 16:51:39', NULL),
(23, '4', '4', NULL, NULL, NULL, '4', NULL, 1, 'MTN CG 3GB (585)', '3GB', NULL, 585.00, 585.00, 585.00, 0, 0, '2023-05-25 06:05:11', '2023-06-06 17:42:25', NULL),
(24, '5', '5', NULL, NULL, NULL, '5', NULL, 1, 'MTN CG 5GB (995)', '5GB', NULL, 995.00, 995.00, 995.00, 0, 0, '2023-05-25 06:06:36', '2023-06-06 17:42:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `data_bundles`
--

CREATE TABLE `data_bundles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `glad` varchar(50) DEFAULT NULL,
  `vtu` varchar(100) DEFAULT NULL,
  `legit` varchar(100) DEFAULT NULL,
  `n3tdata` varchar(50) DEFAULT NULL,
  `maska` varchar(50) DEFAULT NULL,
  `service` varchar(255) DEFAULT NULL,
  `network_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` double(20,2) NOT NULL,
  `api` double(20,2) NOT NULL DEFAULT 0.00,
  `reseller` double(20,2) NOT NULL DEFAULT 100.00,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `deleted` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `data_bundles`
--

INSERT INTO `data_bundles` (`id`, `code`, `glad`, `vtu`, `legit`, `n3tdata`, `maska`, `service`, `network_id`, `name`, `image`, `price`, `api`, `reseller`, `status`, `deleted`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, '266', '266', NULL, '51', '46', '246', 'CG', 3, 'AIRTEL CG 500MB (N110)', NULL, 110.00, 108.00, 108.00, 1, 0, '2022-10-24 16:24:43', '2024-02-09 14:34:05', NULL),
(19, '333', '333', NULL, '70', '56', '312', 'CG', 2, 'GLO CG 200MB (N65)', NULL, 65.00, 60.00, 60.00, 1, 0, '2022-11-13 17:09:26', '2023-11-28 06:37:03', NULL),
(20, '331', '331', NULL, '71', '45', '311', 'CG', 2, 'GLO CG 500MB (N130)', NULL, 130.00, 125.00, 125.00, 1, 0, '2022-11-13 17:09:26', '2023-12-28 07:24:29', NULL),
(21, '334', '334', NULL, '72', '3', '306', 'CG', 2, 'GLO CG 1GB (N235)', NULL, 235.00, 233.00, 233.00, 1, 0, '2022-11-13 17:09:26', '2024-02-06 09:01:08', NULL),
(22, '332', '332', NULL, '73', '56', '307', 'CG', 2, 'GLO CG 2GB (N470)', NULL, 470.00, 466.00, 466.00, 1, 0, '2022-11-13 17:09:26', '2024-02-06 09:02:13', NULL),
(23, '336', '336', NULL, '74', '45', '308', 'CG', 2, 'GLO CG 3GB (N705)', NULL, 705.00, 700.00, 700.00, 1, 0, '2022-11-13 17:09:26', '2024-02-06 09:02:43', NULL),
(24, '329', '329', NULL, '75', '56', '309', 'CG', 2, 'GLO CG 5GB (N1175)', NULL, 1175.00, 1165.00, 1165.00, 1, 0, '2022-11-13 17:09:26', '2024-02-06 09:03:11', NULL),
(25, '335', '335', NULL, '76', '45', '310', 'CG', 2, 'GLO CG 10GB (N2350)', NULL, 2350.00, 2330.00, 2330.00, 1, 0, '2022-11-13 17:09:26', '2024-02-06 09:03:27', NULL),
(30, '267', '267', NULL, '52', '47', '213', 'CG', 3, 'AIRTEL CG 1GB (N208)', NULL, 208.00, 207.00, 207.00, 1, 0, '2022-11-13 17:21:40', '2024-02-09 14:34:31', NULL),
(32, '268', '268', NULL, '53', '48', '214', 'CG', 3, 'AIRTEL CG 2GB (N416)', NULL, 416.00, 414.00, 414.00, 1, 0, '2022-11-13 17:21:40', '2024-02-09 14:35:41', NULL),
(33, '269', '269', NULL, '54', '49', '215', 'CG', 3, 'AIRTEL CG 5GB (N1040)', NULL, 1040.00, 1035.00, 1035.00, 1, 0, '2022-11-13 17:21:40', '2024-02-09 14:38:15', NULL),
(34, '267', NULL, NULL, NULL, NULL, NULL, 'airtel_cg', 3, '1GB - 30days(Corporate Gifting) ', NULL, 270.00, 270.00, 269.00, 1, 1, '2022-11-13 17:21:40', '2022-11-13 17:21:40', NULL),
(35, '273', '273', NULL, '55', '56', '216', 'CG', 3, 'AIRTEL CG 10GB (N2080)', NULL, 2080.00, 2070.00, 2070.00, 1, 0, '2022-11-13 17:21:40', '2024-02-09 14:39:03', NULL),
(39, '384', '384', NULL, '80', '45', '331', 'GIFTING', 1, 'MTN GIFTING 1.5GB (N350)', NULL, 350.00, 340.00, 340.00, 0, 0, '2023-05-01 04:20:33', '2023-12-02 08:03:35', NULL),
(40, '387', '387', NULL, '81', '30', '30', 'GIFTING', 1, 'MTN GIFTING 2GB (N500)', NULL, 500.00, 495.00, 495.00, 0, 0, '2023-05-01 04:22:07', '2023-12-15 07:42:46', NULL),
(41, '390', '390', NULL, '82', '45', '318', 'GIFTING', 1, 'MTN GIFTING 3GB (N750)', NULL, 750.00, 745.00, 745.00, 0, 0, '2023-05-01 04:23:39', '2023-12-28 18:30:02', NULL),
(42, '179', '179', '500', '36', '1', '212', 'SME', 1, 'MTN SME 500MB (N140)', NULL, 140.00, 138.00, 138.00, 1, 0, '2023-05-01 04:24:46', '2024-04-08 15:35:45', NULL),
(43, '166', '166', NULL, '37', '2', '207', 'SME', 1, 'MTN SME 1GB (N265)', NULL, 265.00, 264.00, 264.00, 1, 0, '2023-05-01 04:26:10', '2024-02-09 17:05:39', NULL),
(44, '167', '167', NULL, '38', '3', '208', 'SME', 1, 'MTN SME 2GB (N530)', NULL, 530.00, 528.00, 528.00, 1, 0, '2023-05-01 04:28:17', '2024-02-09 17:06:02', NULL),
(45, '168', '168', NULL, '39', '4', '209', 'SME', 1, 'MTN SME 3GB (N795)', NULL, 795.00, 792.00, 792.00, 1, 0, '2023-05-01 04:29:57', '2024-02-09 17:06:19', NULL),
(46, '169', '169', NULL, '40', '5', '210', 'SME', 1, 'MTN SME 5GB (N1325)', NULL, 1325.00, 1320.00, 1320.00, 1, 0, '2023-05-01 04:31:11', '2024-02-09 17:06:41', NULL),
(47, '182', '182', NULL, '85', '45', '342', 'CG', 4, '9MOBILE CG 500MB (N80)', NULL, 80.00, 77.00, 77.00, 1, 0, '2023-05-01 07:44:12', '2023-11-14 10:56:25', NULL),
(48, '298', '298', NULL, '86', '56', '335', 'CG', 4, '9MOBILE CG 1GB (N140)', NULL, 140.00, 137.00, 137.00, 1, 0, '2023-05-01 07:45:59', '2023-11-15 11:44:45', NULL),
(49, '299', '299', NULL, '87', '45', '336', 'CG', 4, '9MOBILE CG 2GB (N280)', NULL, 280.00, 274.00, 274.00, 1, 0, '2023-05-01 07:47:03', '2023-11-15 11:45:46', NULL),
(50, '303', '303', NULL, '88', '56', '337', 'CG', 4, '9MOBILE CG 3GB (N420)', NULL, 420.00, 411.00, 411.00, 1, 0, '2023-05-01 07:48:27', '2023-11-15 11:47:23', NULL),
(51, '304', '304', NULL, '90', '56', '338', 'CG', 4, '9MOBILE CG 5GB (N700)', NULL, 700.00, 685.00, 685.00, 1, 0, '2023-05-01 07:49:38', '2023-11-15 11:49:01', NULL),
(52, '305', '305', NULL, '91', '45', '339', 'CG', 4, '9MOBILE CG 10GB (N1400)', NULL, 1400.00, 1370.00, 1370.00, 1, 0, '2023-05-01 07:50:28', '2023-11-15 11:50:33', NULL),
(53, '260', '260', NULL, '41', '6', '247', 'SME', 1, 'MTN SME 10GB (N2650)', NULL, 2650.00, 2640.00, 2640.00, 1, 0, '2023-05-09 09:12:13', '2024-02-09 17:06:59', NULL),
(54, '225', '225', NULL, '42', '50', '264', 'MTN CG', 5, 'MTN CG 500MB (N140)', NULL, 140.00, 138.00, 138.00, 1, 0, '2023-09-18 07:59:46', '2024-02-12 12:24:56', NULL),
(55, '213', '213', NULL, '46', '51', '248', 'MTN CG', 5, 'MTN CG 1GB (N272)', NULL, 272.00, 270.00, 270.00, 1, 0, '2023-09-18 08:06:06', '2024-02-15 09:01:47', NULL),
(56, '215', '215', NULL, '47', '52', '250', 'MTN CG', 5, 'MTN CG 2GB (N544)', NULL, 544.00, 540.00, 540.00, 1, 0, '2023-09-18 08:11:02', '2024-02-15 09:02:27', NULL),
(57, '216', '216', NULL, '48', '53', '266', 'MTN CG', 5, 'MTN CG 3GB (N816)', NULL, 816.00, 810.00, 810.00, 1, 0, '2023-09-18 08:12:28', '2024-02-15 09:03:58', NULL),
(58, '217', '217', NULL, '49', '54', '265', 'MTN CG', 5, 'MTN CG 5GB (N1360)', NULL, 1360.00, 1350.00, 1350.00, 1, 0, '2023-09-18 08:15:30', '2024-02-15 09:04:30', NULL),
(59, '257', '257', NULL, '50', '55', '211', 'MTN CG', 5, 'MTN CG 10GB (N2720)', NULL, 2720.00, 2700.00, 2700.00, 1, 0, '2023-09-18 08:17:57', '2024-02-15 09:04:55', NULL),
(60, '258', '258', NULL, '43', '30', '30', 'MTN CG', 5, 'MTN CG 20GB (N5300)', NULL, 5300.00, 5250.00, 5250.00, 0, 0, '2023-09-20 06:24:23', '2024-02-09 14:24:52', NULL),
(61, '271', '271', NULL, '44', '30', '30', 'MTN CG', 5, 'MTN CG 150MB (N65)', NULL, 65.00, 60.00, 60.00, 1, 0, '2023-09-20 06:29:40', '2024-02-23 08:16:47', NULL),
(62, '272', '272', NULL, '45', '30', '30', 'MTN CG', 5, 'MTN CG 250MB (N95)', NULL, 95.00, 90.00, 90.00, 1, 0, '2023-09-20 06:30:53', '2024-02-23 08:15:05', NULL),
(63, '270', '270', NULL, '30', '30', '30', 'MTN CG', 5, 'MTN CG 50MB (N30)', NULL, 30.00, 25.00, 25.00, 1, 0, '2023-09-20 06:35:30', '2024-02-23 08:17:45', NULL),
(64, '30', '30', NULL, '92', '30', '30', 'MTN CG', 5, 'MTN CG 4GB (N900)', NULL, 900.00, 899.00, 890.00, 0, 0, '2023-09-20 06:36:58', '2023-11-06 13:36:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `data_pins`
--

CREATE TABLE `data_pins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `network_id` int(11) NOT NULL,
  `plan_id` bigint(20) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `quantity` varchar(255) DEFAULT NULL,
  `pins` text DEFAULT NULL,
  `amount` double(20,2) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `cost` double(20,2) DEFAULT NULL,
  `charge` double(20,2) NOT NULL DEFAULT 0.00,
  `load_code` varchar(200) DEFAULT NULL,
  `check_bal` varchar(100) DEFAULT NULL,
  `serial` text DEFAULT NULL,
  `response` text DEFAULT NULL,
  `old_balance` double(20,2) DEFAULT NULL,
  `new_balance` double(20,2) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `api_name` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `decoders`
--

CREATE TABLE `decoders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `glad` varchar(50) DEFAULT NULL,
  `vtu` varchar(100) DEFAULT NULL,
  `legit` varchar(100) DEFAULT NULL,
  `n3tdata` varchar(50) DEFAULT NULL,
  `maska` varchar(50) DEFAULT NULL,
  `vtpass` varchar(20) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `discount` int(11) DEFAULT 10,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `decoders`
--

INSERT INTO `decoders` (`id`, `code`, `glad`, `vtu`, `legit`, `n3tdata`, `maska`, `vtpass`, `name`, `image`, `discount`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '1', NULL, 'dstv', NULL, NULL, NULL, '01', 'DSTV', 'decoder/dstv.png', 10, 1, '2022-10-24 17:59:04', '2022-10-24 17:59:04', NULL),
(2, '2', NULL, 'gotv', NULL, NULL, NULL, '02', 'GOTV', 'decoder/gotv.jpg', 10, 1, '2022-10-24 17:59:58', '2022-10-25 17:59:58', NULL),
(3, '3', NULL, 'startimes', NULL, NULL, NULL, '03', 'STARTIME', 'decoder/startimes.jpg', 10, 1, '2022-10-24 17:59:58', '2023-10-08 09:39:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `decoder_trxes`
--

CREATE TABLE `decoder_trxes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `decoder_id` int(11) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `customer_name` varchar(200) DEFAULT 'Default Customer',
  `number` varchar(255) DEFAULT NULL,
  `amount` double(20,2) DEFAULT NULL,
  `charge` double(20,2) NOT NULL DEFAULT 0.00,
  `message` varchar(500) DEFAULT NULL,
  `response` text DEFAULT NULL,
  `old_balance` double(20,2) DEFAULT NULL,
  `new_balance` double(20,2) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `api_name` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deposits`
--

CREATE TABLE `deposits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `trx` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `amount` double(20,2) NOT NULL,
  `gateway` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `response` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `education`
--

CREATE TABLE `education` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `glad` varchar(50) DEFAULT NULL,
  `vtu` varchar(100) DEFAULT NULL,
  `legit` varchar(100) DEFAULT NULL,
  `n3tdata` varchar(50) DEFAULT NULL,
  `maska` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price` double(20,2) NOT NULL,
  `reseller` double(20,2) NOT NULL DEFAULT 100.00,
  `api` double(20,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `deleted` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `education`
--

INSERT INTO `education` (`id`, `code`, `glad`, `vtu`, `legit`, `n3tdata`, `maska`, `name`, `price`, `reseller`, `api`, `status`, `deleted`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'WAEC', 'WAEC', NULL, '1', '1', '1', 'WAEC Result Pin', 3600.00, 3570.00, 3570.00, 1, 0, '2022-10-25 14:30:02', '2024-01-04 05:28:58', NULL),
(2, 'NECO', 'NECO', NULL, '2', '2', '2', 'NECO Result Checker', 1300.00, 1270.00, 1270.00, 1, 0, '2022-10-25 14:30:02', '2024-01-04 05:30:16', NULL),
(3, 'NABTEB', 'NABTEB', NULL, '3', '3', '3', 'Nabteb Exam', 850.00, 100.00, 0.00, 0, 0, '2023-04-11 23:41:59', '2023-09-04 12:28:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `edu_trxes`
--

CREATE TABLE `edu_trxes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `education_id` int(11) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `quantity` int(9) DEFAULT NULL,
  `amount` double(20,2) DEFAULT NULL,
  `pins` text DEFAULT NULL,
  `serial` text DEFAULT NULL,
  `response` text DEFAULT NULL,
  `charge` double(20,2) NOT NULL DEFAULT 0.00,
  `old_balance` double(20,2) DEFAULT NULL,
  `new_balance` double(20,2) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `api_name` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `electricities`
--

CREATE TABLE `electricities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `glad` varchar(50) DEFAULT NULL,
  `vtu` varchar(100) DEFAULT NULL,
  `legit` varchar(100) DEFAULT NULL,
  `n3tdata` varchar(50) DEFAULT NULL,
  `maska` varchar(50) DEFAULT NULL,
  `vtpass` varchar(20) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `minimum` int(11) NOT NULL DEFAULT 500,
  `fee` double(20,2) NOT NULL DEFAULT 100.00,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `deleted` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `electricities`
--

INSERT INTO `electricities` (`id`, `code`, `glad`, `vtu`, `legit`, `n3tdata`, `maska`, `vtpass`, `name`, `image`, `minimum`, `fee`, `status`, `deleted`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '19', '19', 'ibadan-electric', '30', '6', '21', 'ibadan-electric', 'Ibadan Electricity IBDEC', NULL, 100, 10.00, 1, 0, '2022-10-24 18:43:14', '2024-04-08 15:43:02', NULL),
(2, '20', '20', NULL, '30', '2', '4', 'eko-electric', 'EKO ELECTRIC', 'power/edbec.png', 500, 10.00, 1, 0, '2022-10-24 19:45:42', '2023-11-07 21:09:50', NULL),
(3, '24', '24', NULL, '30', '5', '6', NULL, 'JOS Electricity Company', 'power/jed.png', 500, 10.00, 1, 0, '2022-10-24 19:46:49', '2023-10-08 08:14:22', NULL),
(4, '22', '22', NULL, '30', '7', '5', NULL, 'Kaduna Electricity', NULL, 500, 10.00, 1, 0, '2022-11-01 12:19:27', '2023-10-08 08:14:32', NULL),
(5, '18', '18', NULL, '30', '1', '5', 'ikeja-electric', 'Ikeja Electric', NULL, 500, 10.00, 1, 0, '2023-04-01 18:39:39', '2023-11-07 21:08:54', NULL),
(6, '25', '25', NULL, '30', '8', '6', 'abuja-electric', 'Abuja Electric', NULL, 100, 10.00, 1, 0, '2023-04-11 23:48:06', '2023-11-07 21:11:49', NULL),
(7, '29', '29', NULL, '30', '9', '121', NULL, 'Benin Electricity', NULL, 500, 10.00, 1, 0, '2023-04-27 06:20:18', '2023-10-08 08:15:17', NULL),
(8, '21', '21', NULL, '30', '4', '4', NULL, 'PORT HARCOURT ELECTRIC', NULL, 500, 10.00, 1, 0, '2023-05-04 11:22:28', '2023-10-08 08:15:29', NULL),
(9, '23', '23', NULL, '30', '3', '4', NULL, 'KANO ELECTRIC', NULL, 500, 10.00, 1, 0, '2023-05-04 11:24:15', '2023-10-08 08:15:37', NULL),
(10, '26', '26', NULL, '30', '10', '4', NULL, 'ENUGU ELECTRIC', NULL, 500, 10.00, 1, 0, '2023-05-04 11:27:20', '2023-10-08 08:15:53', NULL),
(11, '28', '28', NULL, NULL, '8', '4', NULL, 'YOLA ELECTRIC', NULL, 500, 10.00, 1, 0, '2023-05-04 11:28:05', '2023-10-08 08:14:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `shortcodes` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `type`, `subject`, `content`, `shortcodes`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME_EMAIL', 'Welcome to KingsData Connect', '<p>Welcome to KingsData. your Visa to cheap data. Thanks for choosing us.</p>', '{\"site_name\":\"Site Name\", \"username\":\"username\"}', '2022-10-20 23:03:46', '2024-02-29 01:49:02'),
(2, 'DEPOSIT_EMAIL', 'You Receive {{amount}} to your Account', '<p>Hello {{username}}</p>\r\n<p>You receive {{amount}} via {{method}} to your account.</p>\r\n<p>We are glad to have you.</p>', ' {\"date\":\"Transaction Date \", \"method\":\"Payment Gateway\", \"username\":\"Username of User\",\"amount\":\"Deposit amount\"}', '2022-10-20 23:05:00', '2022-10-20 23:05:00'),
(3, 'REFERRAL_EMAIL', 'Congratuations You have a new referral', '<p>Hi, {{username}}</p>\r\n<p>You have a new referral {{refer_name}}</p>\r\n<p>Thank you</p>', '{\"site_name\":\"Site Name\", \"username\":\"User Name\", \"refer_name\":\"Referred username\", \"date\":\"Date\"}', '2022-10-20 23:03:46', '2024-02-29 01:50:41'),
(4, 'TRX_EMAIL', 'Transaction Alert - {{trx_type}} ({{amount}})', '<p>Hello {{username}},</p>\r\n<p>There is a {{trx_type}} Transaction on your account at {{date}}.</p>\r\n<p>Amount: {{amount}}.<br />Reference: {{code}}</p>\r\n<p>{{trx_details}}</p>\r\n<p>We are glad to have you.</p>', '{\"trx_type\":\"Credit or Debit\",\"code\":\"Trx Code\", \"amount\":\"Transaction Amounnt\", \"username\":\"User Name\",\"date\":\"Transaction date\",\"trx_details\":\"Transaction Details\"}', '2022-10-20 23:03:46', '2024-02-29 01:50:10');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mdeposits`
--

CREATE TABLE `mdeposits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `amount` double(20,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_100000_create_password_resets_table', 1),
(2, '2019_08_19_000000_create_failed_jobs_table', 1),
(3, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(4, '2023_04_26_211948_create_updates_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `networks`
--

CREATE TABLE `networks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(11) NOT NULL,
  `glad` varchar(50) DEFAULT NULL,
  `vtu` varchar(100) DEFAULT NULL,
  `legit` varchar(100) DEFAULT NULL,
  `n3tdata` varchar(50) DEFAULT NULL,
  `maska` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `swap` tinyint(4) NOT NULL DEFAULT 0,
  `data` tinyint(4) NOT NULL DEFAULT 0,
  `datacard` tinyint(2) NOT NULL DEFAULT 0,
  `airtime` tinyint(4) NOT NULL DEFAULT 0,
  `cardpin` tinyint(4) NOT NULL DEFAULT 0,
  `number` varchar(255) DEFAULT NULL,
  `minimum` int(11) NOT NULL DEFAULT 100,
  `discount` double(11,2) NOT NULL DEFAULT 99.00,
  `pin_discount` double(5,2) NOT NULL DEFAULT 99.00,
  `reseller` double(5,2) DEFAULT NULL,
  `reseller_pin` double(5,2) DEFAULT NULL,
  `api_discount` varchar(5) NOT NULL DEFAULT '100',
  `api_pin_discount` varchar(5) NOT NULL DEFAULT '100',
  `rate` int(11) DEFAULT 10,
  `p_code` varchar(20) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `networks`
--

INSERT INTO `networks` (`id`, `code`, `glad`, `vtu`, `legit`, `n3tdata`, `maska`, `name`, `image`, `swap`, `data`, `datacard`, `airtime`, `cardpin`, `number`, `minimum`, `discount`, `pin_discount`, `reseller`, `reseller_pin`, `api_discount`, `api_pin_discount`, `rate`, `p_code`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '1', '1', 'mtn', '1', '1', '1', 'MTN', 'networks/mtn.png', 1, 1, 1, 1, 1, '081', 100, 99.00, 99.00, 99.50, 99.80, '99.5', '99.8', 25, '1', 1, '2022-10-23 16:20:27', '2024-04-08 15:32:09', NULL),
(2, '2', '2', 'glo', '3', '3', '2', 'GLO', 'networks/glo.png', 0, 1, 0, 1, 1, '0701234', 100, 99.00, 99.00, 97.50, 98.80, '97.5', '98.8', 20, '3', 1, '2022-10-23 16:20:27', '2024-04-08 15:31:29', NULL),
(3, '3', '3', 'airtel', '2', '2', '4', 'AIRTEL', 'networks/airtel.png', 0, 1, 0, 1, 1, '0901', 100, 99.00, 99.00, 97.50, 98.80, '97.5', '98.8', 30, '2', 1, '2022-10-23 16:20:27', '2024-04-08 15:31:37', NULL),
(4, '6', '6', 'etisalat', '4', '4', '3', '9MOBILE', 'networks/9mob.png', 0, 1, 0, 1, 1, NULL, 100, 98.00, 99.00, 97.50, 98.80, '97.5', '98.8', 10, NULL, 1, '2022-10-23 16:20:27', '2024-04-08 15:31:46', NULL),
(5, '1', '1', NULL, '1', '1', '1', 'MTN CG', 'networks/mtn.png', 0, 1, 0, 0, 0, NULL, 100, 99.00, 99.00, 99.00, 99.00, '100', '100', 10, NULL, 1, '2022-10-23 16:20:27', '2024-02-15 09:18:44', '2024-04-08 15:30:11');

-- --------------------------------------------------------

--
-- Table structure for table `network_trxes`
--

CREATE TABLE `network_trxes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `network_id` int(11) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL,
  `amount` double(20,2) DEFAULT NULL,
  `charge` double(20,2) NOT NULL DEFAULT 0.00,
  `old_balance` double(20,2) DEFAULT NULL,
  `new_balance` double(20,2) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `system` varchar(10) NOT NULL DEFAULT 'WEB',
  `api_name` varchar(50) DEFAULT NULL,
  `response` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `type`, `title`, `slug`, `content`, `created_at`, `updated_at`) VALUES
(3, 'terms', 'Terms', 'terms', '<p><strong>TErms and Conditions</strong></p>\r\n<p>We are lovable</p>', '2022-10-22 17:51:27', '2022-10-22 17:51:27'),
(4, 'policy', 'Policy', '', '<p><strong>Privacy Policy</strong></p>\r\n<p>We are</p>', '2022-11-02 17:02:25', '2024-02-29 01:51:44');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `power_trxes`
--

CREATE TABLE `power_trxes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `electricity_id` int(11) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `token` varchar(200) DEFAULT NULL,
  `amount` double(20,2) DEFAULT NULL,
  `charge` double(20,2) NOT NULL DEFAULT 0.00,
  `old_balance` double(20,2) DEFAULT NULL,
  `new_balance` double(20,2) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `api_name` varchar(50) DEFAULT NULL,
  `response` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recharge_pins`
--

CREATE TABLE `recharge_pins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `network_id` int(11) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `quantity` varchar(255) DEFAULT NULL,
  `pins` text DEFAULT NULL,
  `amount` double(20,2) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `cost` double(20,2) DEFAULT NULL,
  `charge` double(20,2) NOT NULL DEFAULT 0.00,
  `load_code` varchar(200) DEFAULT NULL,
  `serial` text DEFAULT NULL,
  `response` text DEFAULT NULL,
  `old_balance` double(20,2) DEFAULT NULL,
  `new_balance` double(20,2) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `api_name` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `permissions` mediumtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `touch_icon` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `telegram` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(255) DEFAULT NULL,
  `currency` varchar(20) DEFAULT NULL,
  `currency_code` varchar(5) DEFAULT NULL,
  `primary_color` varchar(255) DEFAULT NULL,
  `custom_css` text DEFAULT NULL,
  `custom_js` text DEFAULT NULL,
  `is_announcement` tinyint(4) NOT NULL DEFAULT 1,
  `announcement` varchar(255) DEFAULT NULL,
  `is_adsense` tinyint(4) DEFAULT 1,
  `google_adsense` varchar(255) DEFAULT NULL,
  `is_analytics` tinyint(4) DEFAULT 1,
  `google_analytics_id` varchar(255) DEFAULT NULL,
  `is_youtube_link` tinyint(2) DEFAULT NULL,
  `youtube_link` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `title`, `description`, `phone`, `address`, `email`, `touch_icon`, `favicon`, `logo`, `facebook`, `twitter`, `instagram`, `telegram`, `whatsapp`, `currency`, `currency_code`, `primary_color`, `custom_css`, `custom_js`, `is_announcement`, `announcement`, `is_adsense`, `google_adsense`, `is_analytics`, `google_analytics_id`, `is_youtube_link`, `youtube_link`, `created_at`, `updated_at`) VALUES
(1, 'Rickypay Data', 'Rickypayis your visa to cheap data plans, data card pins, airtime with discount, cable subscription with discount, airtime to cash, exam pins and more.', '08035852702', NULL, 'rickypay@gmail.com', 'touch.png', 'favicon.png', 'logo.png', 'https://www.facebook.com/profile.php', 'https://twitter.com/', NULL, 'https://t.me/', 'https://wa.me/', '₦', 'NGN', NULL, '<style>\r\n\r\n</style>', '<script>\r\n\r\n</script>', 0, NULL, 1, NULL, 1, NULL, 1, 'https://www.youtube.com/embed/FVcW9Aj5R2g', NULL, '2024-04-08 14:59:52');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(10) UNSIGNED NOT NULL,
  `ticket` varchar(191) NOT NULL,
  `subject` varchar(191) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `value` varchar(255) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `name`, `value`, `created_at`, `updated_at`) VALUES
(1, 'verify_email', '1', '2022-10-22 09:32:48', '2024-01-31 13:12:11'),
(2, 'is_electricity', '1', '2022-10-22 09:41:08', '2023-11-06 05:16:26'),
(3, 'airtime_cash', '1', '2022-10-22 09:41:11', '2022-10-22 09:41:11'),
(4, 'is_education', '1', '2022-10-22 09:41:14', '2023-09-04 12:29:58'),
(5, 'is_data', '1', '2022-10-22 09:41:14', '2023-08-21 19:11:58'),
(6, 'is_airtime', '1', '2022-10-22 09:41:18', '2023-10-24 12:52:31'),
(7, 'is_cable', '1', '2022-10-22 09:41:18', '2023-11-06 12:30:17'),
(8, 'is_bulksms', '0', '2022-10-22 09:41:21', '2024-01-11 08:34:03'),
(9, 'airtime_pin', '1', '2022-10-22 09:41:23', '2023-11-23 05:07:44'),
(10, 'is_https', '0', '2022-10-22 09:41:28', '2024-02-29 01:48:26'),
(11, 'welcome_email', '1', '2022-10-22 09:41:31', '2022-10-22 09:41:31'),
(12, 'monnify_payment', '1', '2022-10-22 11:33:20', '2024-02-29 01:25:25'),
(13, 'flutter_payment', '1', '2022-10-22 11:33:24', '2024-02-29 01:25:22'),
(14, 'bank_name', 'Kuda', '2022-10-26 08:54:42', '2024-02-29 01:25:51'),
(15, 'account_name', 'Joshua Adeyemi', '2022-10-26 08:54:42', '2024-02-29 01:25:51'),
(16, 'account_number', '2004880341', '2022-10-26 08:54:43', '2024-02-29 01:25:51'),
(17, 'bank_transfer', '1', '2022-10-26 08:55:50', '2022-10-26 08:55:50'),
(18, 'auto_bank', '1', '2022-10-26 09:07:09', '2024-02-29 01:28:57'),
(19, 'paystack_payment', '1', '2022-10-26 09:16:30', '2024-02-29 01:25:21'),
(20, 'bank_fee', '0', '2022-10-26 09:53:06', '2023-05-01 12:54:04'),
(21, 'auto_fee', '1.5', '2022-10-26 09:53:06', '2023-10-08 11:15:59'),
(22, 'card_fee', '1.5', '2022-10-26 09:53:06', '2023-05-23 16:02:41'),
(23, 'monnify_demo', '0', '2022-10-26 15:35:32', '2023-04-01 15:18:05'),
(24, 'referral_commission', '0.1', '2022-11-05 12:34:27', '2023-10-11 11:05:38'),
(25, 'is_affiliate', '1', '2022-11-07 18:12:33', '2022-11-07 18:12:33'),
(26, 'trx_email', '1', '2022-11-09 21:51:57', '2022-11-09 21:51:57'),
(27, 'referral_email', '1', '2022-11-09 21:52:01', '2022-11-09 21:52:01'),
(28, 'is_maintenance', '0', '2022-11-09 23:47:21', '2022-11-10 00:07:25'),
(29, 'data_api', 'glad', '2023-04-27 07:47:00', '2023-07-07 09:13:39'),
(30, 'airtime_api', 'maska', '2023-04-28 06:51:57', '2023-07-04 07:46:12'),
(31, 'cable_api', 'glad', '2023-04-28 06:51:57', '2023-07-04 17:03:36'),
(32, 'power_api', 'glad', '2023-04-28 06:51:57', '2023-04-28 06:51:57'),
(33, 'exam_api', 'glad', '2023-04-28 06:51:57', '2023-04-28 06:51:57'),
(34, 'is_datacard', '0', '2023-05-06 10:32:28', '2023-07-08 11:50:31'),
(35, 'cable_discount', '100', '2023-05-06 10:43:53', '2023-11-11 11:12:39'),
(36, 'datacard_api', 'n3tdata', '2023-05-12 15:11:12', '2023-05-25 08:27:58'),
(37, 'auto_cap', '500', '2023-06-02 14:31:23', '2023-06-13 14:17:28'),
(38, 'payvessel_accounts', '0', '2023-10-07 10:04:32', '2024-02-29 01:44:11'),
(39, 'auto_fee2', '35', '2023-10-07 15:17:57', '2023-10-07 15:18:52'),
(40, 'developer_upgrade', '0', '2023-10-11 09:19:33', '2023-10-11 11:04:48'),
(41, 'reseller_upgrade', '500', '2023-11-09 02:39:49', '2023-11-09 05:34:19'),
(42, 'bulksms_price', '5', '2024-02-29 02:01:04', '2024-02-29 02:01:04');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_comments`
--

CREATE TABLE `ticket_comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `ticket_id` int(10) UNSIGNED NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0: user, 1: admin',
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `service` varchar(20) DEFAULT NULL,
  `amount` double(20,2) DEFAULT NULL,
  `charge` double(20,2) NOT NULL DEFAULT 0.00,
  `response` text DEFAULT NULL,
  `old_balance` double(20,2) DEFAULT NULL,
  `new_balance` double(20,2) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `system` varchar(10) NOT NULL DEFAULT 'WEB',
  `message` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `ref_id` mediumint(9) DEFAULT NULL,
  `user_role` varchar(255) NOT NULL DEFAULT 'user',
  `type` varchar(20) NOT NULL DEFAULT 'user',
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(210) DEFAULT NULL,
  `balance` double(20,3) NOT NULL DEFAULT 0.000,
  `bonus` double(20,3) NOT NULL DEFAULT 0.000,
  `bvn` varchar(20) DEFAULT NULL,
  `kyc_verify` tinyint(1) NOT NULL DEFAULT 0,
  `phone_verify` tinyint(4) NOT NULL DEFAULT 0,
  `email_notify` tinyint(2) NOT NULL DEFAULT 1,
  `blocked` tinyint(4) NOT NULL DEFAULT 0,
  `suspend` tinyint(1) NOT NULL DEFAULT 0,
  `trxpin` mediumint(9) DEFAULT NULL,
  `virtual_ref` varchar(50) DEFAULT NULL,
  `virtual_banks` text DEFAULT NULL,
  `payvessel_ref` varchar(100) DEFAULT NULL,
  `payvessel_banks` text DEFAULT NULL,
  `developer` tinyint(2) NOT NULL DEFAULT 0,
  `api_key` varchar(250) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_settings`
--
ALTER TABLE `api_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cable_plans`
--
ALTER TABLE `cable_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `datacard_plans`
--
ALTER TABLE `datacard_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data_bundles`
--
ALTER TABLE `data_bundles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data_pins`
--
ALTER TABLE `data_pins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `decoders`
--
ALTER TABLE `decoders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `decoder_trxes`
--
ALTER TABLE `decoder_trxes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `decoder_trxes_user_id_index` (`user_id`),
  ADD KEY `decoder_trxes_decoder_id_index` (`decoder_id`);

--
-- Indexes for table `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deposits_user_id_index` (`user_id`);

--
-- Indexes for table `education`
--
ALTER TABLE `education`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `edu_trxes`
--
ALTER TABLE `edu_trxes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `edu_trxes_user_id_index` (`user_id`),
  ADD KEY `edu_trxes_education_id_index` (`education_id`);

--
-- Indexes for table `electricities`
--
ALTER TABLE `electricities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `mdeposits`
--
ALTER TABLE `mdeposits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mdeposits_user_id_index` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `networks`
--
ALTER TABLE `networks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `network_trxes`
--
ALTER TABLE `network_trxes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `network_trxes_user_id_index` (`user_id`),
  ADD KEY `network_trxes_network_id_index` (`network_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pages_slug_unique` (`slug`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `power_trxes`
--
ALTER TABLE `power_trxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recharge_pins`
--
ALTER TABLE `recharge_pins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ticket_comments`
--
ALTER TABLE `ticket_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `api_settings`
--
ALTER TABLE `api_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `cable_plans`
--
ALTER TABLE `cable_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `datacard_plans`
--
ALTER TABLE `datacard_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `data_bundles`
--
ALTER TABLE `data_bundles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `data_pins`
--
ALTER TABLE `data_pins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `decoder_trxes`
--
ALTER TABLE `decoder_trxes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `education`
--
ALTER TABLE `education`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `edu_trxes`
--
ALTER TABLE `edu_trxes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `electricities`
--
ALTER TABLE `electricities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `mdeposits`
--
ALTER TABLE `mdeposits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `networks`
--
ALTER TABLE `networks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `network_trxes`
--
ALTER TABLE `network_trxes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `power_trxes`
--
ALTER TABLE `power_trxes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recharge_pins`
--
ALTER TABLE `recharge_pins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `ticket_comments`
--
ALTER TABLE `ticket_comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
