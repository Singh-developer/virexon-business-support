-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 15, 2026 at 01:09 PM
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
-- Database: `laravel_agent_business_support`
--

-- --------------------------------------------------------

--
-- Table structure for table `advances`
--

CREATE TABLE `advances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `outstanding_amount` decimal(10,2) NOT NULL,
  `repayment_type` enum('unselected','one_time','emi') NOT NULL DEFAULT 'unselected',
  `emi_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agent_documents`
--

CREATE TABLE `agent_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','re_upload') NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `auditable_type` varchar(255) DEFAULT NULL,
  `auditable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `auditable_type`, `auditable_id`, `ip_address`, `user_agent`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 1, 'assertion_letter.sent', 'App\\Models\\AssertionLetter', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '{\"to\":\"firstform@yopmail.com\"}', '2026-09-10 09:59:27', '2026-09-10 09:59:27'),
(2, 1, 'sanction_letter.created_without_email', 'App\\Models\\SanctionLetter', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '{\"recipient\":\"firstform@yopmail.com\",\"dash_only\":true}', '2026-09-12 11:42:12', '2026-09-12 11:42:12'),
(3, 2, 'sanction_letter.signed_uploaded', 'App\\Models\\SanctionLetter', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '{\"upload_count\":1,\"file\":\"signed-sanction-letters\\/letter-1000-20260914-132148-yonngy.pdf\"}', '2026-09-14 07:51:48', '2026-09-14 07:51:48'),
(4, 1, 'sanction_letter.reupload_required', 'App\\Models\\SanctionLetter', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '{\"reviewed_by\":1,\"comment\":null}', '2026-09-14 07:52:33', '2026-09-14 07:52:33'),
(5, 1, 'sanction_letter.created_without_email', 'App\\Models\\SanctionLetter', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '{\"recipient\":\"firstform@yopmail.com\",\"dash_only\":true}', '2026-09-14 10:03:28', '2026-09-14 10:03:28'),
(13, 1, 'agent.trashed', 'App\\Models\\User', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '{\"agent_id\":4}', '2026-09-15 07:42:34', '2026-09-15 07:42:34'),
(14, 2, 'sanction_letter.process_fee_initiated', 'App\\Models\\SanctionLetter', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '{\"order_id\":\"SLPF-1-YIGVTN\",\"amount\":\"300.00\"}', '2026-09-15 08:19:58', '2026-09-15 08:19:58');

-- --------------------------------------------------------

--
-- Table structure for table `businesses`
--

CREATE TABLE `businesses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_number` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `businesses`
--

INSERT INTO `businesses` (`id`, `name`, `contact_person`, `email`, `phone`, `address`, `tax_number`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Northstar Retail Pvt Ltd', 'Rahul Sharma', 'ops@northstar.demo', '+91 90000 10001', 'New Delhi, India', 'GST29DEMO1234Z1Z1', 'active', NULL, '2026-09-02 09:20:36', '2026-09-02 09:20:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `commissions`
--

CREATE TABLE `commissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `gross_amount` decimal(10,2) NOT NULL,
  `advance_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
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
(1, '2026_09_02_000000_create_users_table', 1),
(2, '2026_09_02_000001_create_roles_and_permissions_tables', 1),
(3, '2026_09_02_000002_create_businesses_table', 1),
(4, '2026_09_02_000003_modify_users_table', 1),
(5, '2026_09_02_000004_create_virtual_cards_table', 1),
(6, '2026_09_02_000005_create_payment_tables', 1),
(7, '2026_09_02_000006_create_auth_support_tables', 1),
(8, '2026_09_02_000007_add_agent_id_to_virtual_cards', 1),
(9, '2026_09_02_000008_add_secure_card_details_to_virtual_cards', 1),
(10, '2026_08_27_100032_create_user_details_table', 2),
(11, '2026_08_31_081605_add_status_to_user_details_table', 2),
(12, '2026_09_02_000009_create_system_settings_table', 2),
(13, '2026_09_03_094720_add_new_fields_to_user_details_table', 3),
(14, '2026_09_03_101510_create_reference_people_table', 4),
(15, '2026_09_03_113536_change_credentials_column_type_in_payment_gateways', 5),
(16, '2026_09_03_121652_add_personal_email_to_user_details', 6),
(17, '2026_09_04_122739_create_notifications_table', 7),
(18, '2026_09_05_110226_create_tickets_table', 8),
(19, '2026_09_05_125244_create_agent_documents_table', 9),
(20, '2026_09_05_125300_add_form_received_to_user_details_status', 10),
(21, '2026_09_05_150543_create_advances_table', 11),
(22, '2026_09_05_150543_create_commissions_table', 11),
(23, '2026_09_07_000000_create_sanction_letters_table', 12),
(24, '2026_09_07_115414_add_signature_image_and_dynamic_fields_to_sanction_letters', 13),
(25, '2026_09_07_120000_add_limit_and_commission_to_user_details', 14),
(26, '2026_09_07_143532_add_notes_to_sanction_letters_table', 15),
(27, '2026_09_09_000001_add_encrypted_cvv_to_virtual_cards', 16),
(28, '2026_09_09_000002_add_payment_type_to_payments', 17),
(29, '2026_09_10_000001_create_wallet_tables', 18),
(30, '2026_09_10_000002_update_payment_gateways_table', 18),
(31, '2026_09_12_000000_rename_assertion_letters_to_sanction_letters', 19),
(32, '2026_09_12_000002_add_sanction_number_to_sanction_letters', 20),
(33, '2026_09_12_000003_add_sanction_workflow_columns_to_sanction_letters', 21),
(34, '2026_09_12_000004_create_sanction_letter_uploads_table', 21),
(35, '2026_09_14_000000_add_upload_deadline_to_sanction_letters', 22),
(36, '2026_09_15_000001_add_soft_deletes_for_trash_feature', 23),
(37, '2026_09_15_000002_add_soft_deletes_to_tickets_table', 24),
(38, '2026_09_15_000003_add_process_fee_to_sanction_letters', 25);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('3a320d7c-c9b1-409b-ac3c-c3489838946b', 'App\\Notifications\\DocumentReviewedNotification', 'App\\Models\\User', 2, '{\"message\":\"Your Fund Application form has been received! Please log in and upload your documents to proceed.\",\"document_id\":null,\"document_type\":\"form\",\"status\":\"form_received\",\"admin_note\":\"Your Fund Application form has been received! Please log in and upload your documents to proceed.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/documents\"}', NULL, '2026-09-05 07:55:42', '2026-09-05 07:55:42'),
('430da26d-6dcf-4ba5-a8f1-67a2e6663f25', 'App\\Notifications\\DocumentReviewedNotification', 'App\\Models\\User', 2, '{\"message\":\"Your Fund Application form has been received! Please log in and upload your documents to proceed.\",\"document_id\":null,\"document_type\":\"form\",\"status\":\"form_received\",\"admin_note\":\"Your Fund Application form has been received! Please log in and upload your documents to proceed.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/documents\"}', NULL, '2026-09-13 12:19:47', '2026-09-13 12:19:47'),
('46d080f2-b3fc-4801-87cb-0052222438ca', 'App\\Notifications\\DocumentReviewedNotification', 'App\\Models\\User', 2, '{\"message\":\"Your Fund Application form has been received! Please log in and upload your documents to proceed.\",\"document_id\":null,\"document_type\":\"form\",\"status\":\"form_received\",\"admin_note\":\"Your Fund Application form has been received! Please log in and upload your documents to proceed.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/documents\"}', NULL, '2026-09-13 07:46:01', '2026-09-13 07:46:01'),
('601602c3-9372-4bfe-97c8-27cfdabcf70a', 'App\\Notifications\\SanctionLetterReviewedNotification', 'App\\Models\\User', 2, '{\"message\":\"Your signed Sanction Letter V-EOM\\/SL\\/AF\\/2026\\/001000 was not accepted. Please upload a new signed PDF.\",\"sanction_id\":2,\"status\":\"reupload_required\",\"needs_action\":true,\"url\":\"http:\\/\\/127.0.0.1:8000\\/agent\\/sanction-letters\\/2\"}', NULL, '2026-09-14 07:52:33', '2026-09-14 07:52:33'),
('b5ff1259-524e-43f8-af18-c2a5a61cfd79', 'App\\Notifications\\SanctionLetterUploadedNotification', 'App\\Models\\User', 1, '{\"message\":\"erewr uploaded a signed Sanction Letter V-EOM\\/SL\\/AF\\/2026\\/001000. Pending your review.\",\"sanction_id\":2,\"status\":\"under_review\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/sanctions\\/2\\/review\"}', NULL, '2026-09-14 07:51:48', '2026-09-14 07:51:48'),
('b90440d5-e54f-460c-82fd-6ecb87c7dc5c', 'App\\Notifications\\TicketStatusUpdatedNotification', 'App\\Models\\User', 2, '{\"ticket_id\":1,\"title\":\"test\",\"message\":\"Your support ticket \\\"test\\\" status was updated to Resolved\"}', '2026-09-05 06:45:34', '2026-09-05 06:28:35', '2026-09-05 06:45:34'),
('d47e03e2-7ea0-4328-adb4-b6e60b7e940e', 'App\\Notifications\\TicketCreatedNotification', 'App\\Models\\User', 1, '{\"ticket_id\":1,\"title\":\"test\",\"message\":\"New support ticket created by erewr\",\"agent_id\":2}', '2026-09-05 06:45:45', '2026-09-05 05:57:13', '2026-09-05 06:45:45'),
('f23b09e5-633f-4cad-9208-1c4623b34b99', 'App\\Notifications\\AgentRegisteredNotification', 'App\\Models\\User', 1, '{\"agent_id\":2,\"name\":\"erewr\",\"message\":\"New agent erewr has completed their registration application.\"}', '2026-09-04 12:31:15', '2026-09-04 07:05:05', '2026-09-04 12:31:15');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `card_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_type` enum('spending','repayment') NOT NULL DEFAULT 'spending',
  `gateway` varchar(255) NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'INR',
  `reference` varchar(255) NOT NULL,
  `gateway_payment_id` varchar(255) DEFAULT NULL,
  `gateway_order_id` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'created',
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_response`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `business_id`, `user_id`, `card_id`, `payment_type`, `gateway`, `amount`, `currency`, `reference`, `gateway_payment_id`, `gateway_order_id`, `status`, `gateway_response`, `metadata`, `completed_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 2, 1, 'spending', 'mock', 12500.00, 'INR', 'PAY-DEMO-0001', 'mock_pay_demo', NULL, 'successful', NULL, NULL, '2026-09-02 07:20:37', '2026-09-02 09:20:37', '2026-09-02 09:20:37', NULL),
(12, 1, 2, 1, 'repayment', 'paytm', 12500.00, 'INR', 'PAY-20260910111308-102CE1', NULL, NULL, 'failed', '{\"error\":\"Paytm credentials are not configured. Please check MID and Merchant Key in Platform Settings.\"}', NULL, NULL, '2026-09-10 05:43:08', '2026-09-10 05:43:08', NULL),
(13, 1, 2, 1, 'repayment', 'paytm', 10.00, 'INR', 'PAY-20260910162451-2HT04O', NULL, NULL, 'failed', '{\"error\":\"Paytm error: System Error [Code: 501]\"}', NULL, NULL, '2026-09-10 10:54:51', '2026-09-10 10:54:54', NULL),
(14, 1, 2, 1, 'repayment', 'paytm', 10.00, 'INR', 'PAY-20260910163259-XMY82L', NULL, NULL, 'failed', '{\"error\":\"Paytm error: System Error [Code: 501]\"}', NULL, NULL, '2026-09-10 11:02:59', '2026-09-10 11:03:00', NULL),
(15, 1, 2, 1, 'repayment', 'paytm', 10.00, 'INR', 'PAY-20260910164216-0AKIYT', NULL, NULL, 'failed', '{\"error\":\"Paytm error: System Error [Code: 501]\"}', NULL, NULL, '2026-09-10 11:12:16', '2026-09-10 11:12:19', NULL),
(16, 1, 2, 1, 'repayment', 'paytm', 10.00, 'INR', 'PAY-20260910165235-YEQXXT', NULL, NULL, 'failed', '{\"error\":\"Paytm error: System Error [Code: 501]\"}', NULL, NULL, '2026-09-10 11:22:35', '2026-09-10 11:22:35', NULL),
(17, 1, 2, 1, 'repayment', 'paytm', 10.00, 'INR', 'PAY-20260910170009-TQVQDQ', NULL, NULL, 'failed', '{\"error\":\"Paytm error: System Error [Code: 501]\"}', NULL, NULL, '2026-09-10 11:30:09', '2026-09-10 11:30:11', NULL),
(18, 1, 2, 1, 'repayment', 'paytm', 10.00, 'INR', 'PAY-20260910171945-4FFTQL', NULL, NULL, 'failed', '{\"error\":\"Paytm error: System Error [Code: 501]\"}', NULL, NULL, '2026-09-10 11:49:45', '2026-09-10 11:49:46', NULL),
(19, 1, 2, 1, 'repayment', 'paytm', 10.00, 'INR', 'PAY-20260910172951-2ZSS3U', NULL, 'PAY-20260910172951-2ZSS3U', 'failed', '{\"head\":{\"responseTimestamp\":\"1789123181562\",\"version\":\"v1\",\"signature\":\"Gz4AOHRgsBK7mKVKBtAgebc2tr7I\\/EkkyEr+Mq9290uuP6qvnQGeZD1fyPlv5Cw8jZFIULqFsRySX1AgfN01G58n3RieC2kFkD8XBu+bVSI=\"},\"body\":{\"resultInfo\":{\"resultStatus\":\"TXN_FAILURE\",\"resultCode\":\"334\",\"resultMsg\":\"Invalid Order Id.\"},\"orderId\":\"PAY-20260910172951-2ZSS3U\",\"mid\":\"xWHqlF99814977452652\"}}', NULL, NULL, '2026-09-10 11:59:51', '2026-09-11 10:39:41', NULL),
(20, 1, 2, 1, 'repayment', 'paytm', 10.00, 'INR', 'PAY-20260910173542-NTD6Z1', NULL, 'PAY-20260910173542-NTD6Z1', 'failed', '{\"head\":{\"responseTimestamp\":\"1789123205754\",\"version\":\"v1\",\"signature\":\"Pqb9ZNF2HaQgU1rc2te853ApHLIN+pegDvwgAbV+fu6PaTSLMMlzFvuZfwH6LDqQZeubiV6TJMILXpF82n9J4wYeyMXGA0LvT5WDFeLUwXg=\"},\"body\":{\"resultInfo\":{\"resultStatus\":\"TXN_FAILURE\",\"resultCode\":\"334\",\"resultMsg\":\"Invalid Order Id.\"},\"orderId\":\"PAY-20260910173542-NTD6Z1\",\"mid\":\"xWHqlF99814977452652\"}}', NULL, NULL, '2026-09-10 12:05:42', '2026-09-11 10:40:05', NULL),
(21, 1, 2, 1, 'repayment', 'paytm', 10.00, 'INR', 'PAY-20260911103957-XKXEHU', NULL, NULL, 'failed', '{\"error\":\"Paytm could not start the transaction: System Error\"}', NULL, NULL, '2026-09-11 05:09:57', '2026-09-11 05:09:58', NULL),
(22, 1, 2, 1, 'repayment', 'paytm', 10.00, 'INR', 'PAY-20260911105507-ROKWIY', NULL, NULL, 'failed', '{\"error\":\"Paytm could not start the transaction: System Error\"}', NULL, NULL, '2026-09-11 05:25:07', '2026-09-11 05:25:08', NULL),
(23, 1, 2, 1, 'repayment', 'paytm', 10.00, 'INR', 'PAY-20260911132123-UXUEJ9', NULL, NULL, 'failed', '{\"error\":\"Paytm could not start the transaction: System Error This usually means the MID\\/Key is not accepted on the PRODUCTION Paytm environment (public sample\\/tutorial MIDs do not work).\"}', NULL, NULL, '2026-09-11 07:51:23', '2026-09-11 07:51:24', NULL),
(24, 1, 2, 1, 'repayment', 'paytm', 10.00, 'INR', 'PAY-20260911144215-NNAGCA', NULL, 'PAY-20260911144215-NNAGCA', 'failed', '{\"head\":{\"responseTimestamp\":\"1789123206367\",\"version\":\"v1\",\"signature\":\"8a2q9nFkMnNjHNmLlo+\\/cUHD2HY3D1GHMu7V+lU2VH5kjuSLHQIsScDhZNvO6ovi6oHuYLvs4YiwUQaP2cSeBHkAyRPkT8oldU5sSx7s5H4=\"},\"body\":{\"resultInfo\":{\"resultStatus\":\"TXN_FAILURE\",\"resultCode\":\"810\",\"resultMsg\":\"ORDER IS CLOSE.\"},\"txnId\":\"20260911210580000305180735103669008\",\"orderId\":\"PAY-20260911144215-NNAGCA\",\"txnAmount\":\"10.00\",\"txnType\":\"SALE\",\"mid\":\"xWHqlF99814977452652\",\"refundAmt\":\"0.0\",\"txnDate\":\"2026-09-11 14:42:16.0\"}}', NULL, NULL, '2026-09-11 09:12:15', '2026-09-11 10:40:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateways`
--

CREATE TABLE `payment_gateways` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `mode` enum('sandbox','live') NOT NULL DEFAULT 'sandbox',
  `sandbox_key_id` varchar(255) DEFAULT NULL,
  `sandbox_key_secret` text DEFAULT NULL,
  `live_key_id` varchar(255) DEFAULT NULL,
  `live_key_secret` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `environment` varchar(255) NOT NULL DEFAULT 'test',
  `credentials` text DEFAULT NULL,
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_gateways`
--

INSERT INTO `payment_gateways` (`id`, `name`, `slug`, `is_active`, `mode`, `sandbox_key_id`, `sandbox_key_secret`, `live_key_id`, `live_key_secret`, `status`, `environment`, `credentials`, `configuration`, `created_at`, `updated_at`) VALUES
(1, 'Mock Sandbox', 'mock', 0, 'sandbox', NULL, NULL, NULL, NULL, 0, 'sandbox', 'eyJpdiI6InE2L0YrVmpMYS9Ub0NrWFNXM051dXc9PSIsInZhbHVlIjoieDlxTXA5b0xPclhha2dzdnh5T1hHOXZHdGRBV3VxK1ljR3psOVJxNWxLbEE2WlhQZXZmU2NMMkNYZi8rWkNLQUNjY1hTbm9iR3pyeTZqYjVRYWNwNk84d2dDYUZPQUhEQ3d5NDJQTHVpTnFXeG54Yi9LTWp5Z1BtczFJcEZ2bTdnUWM2d3pVRFdEZDU1UXI5L1dOUS80L1FDY0lMR1k3bEFHSThYZThVRnpFPSIsIm1hYyI6ImYyMTJjZGVlNzg5MDA1MjYzY2RlZjg2NTExZWEyMzc5ZjVjM2FlOGE1MDI0ZGI2NzE3MmY1N2Y1YTg2Y2IzZmEiLCJ0YWciOiIifQ==', NULL, '2026-09-02 09:20:37', '2026-09-09 11:01:26'),
(2, 'Razorpay', 'razorpay', 0, 'sandbox', NULL, NULL, NULL, NULL, 1, 'sandbox', 'eyJpdiI6Ilc0dXlBQTJ6RE5iNEpzNzMxT0RLbFE9PSIsInZhbHVlIjoicVVBRnJMcjcwN2loT2V6dEQ3ZTBsWXFIWXNuOCtCWlFqeEQ4WXdpakt0TUNjcmVyd05nN3hMa3VndWliZlNoL3RkRjZzRFc1TlBGM1hOOCt4eThoNlFkc1BaUzd6VGYza2Rla1IrTUZ0NmxrV0tieGlBWkJhQ1lKSzhZempjU0RyVWwzc2JGcERsSThzZy8rd2NkY0ZGYWRGZUNjdThscEZaVXRxamQ1L2N3PSIsIm1hYyI6IjU0MzcxOWQwNTg5MjYyYzc1ZWFmZTlhMWEyNDFjMTY2Zjk2MGNiOWQyNzY3MWM0OGM4NjU0ZDJjYTA3NTZiNjMiLCJ0YWciOiIifQ==', NULL, '2026-09-02 09:20:37', '2026-09-09 11:01:18'),
(3, 'Paytm', 'paytm', 1, 'live', 'xWHqIF99814977452652', 'dRyWtcr2sKV9%fYD', '', '', 1, 'production', '{\"sandbox_api_key\":\"YOUR_MID\",\"sandbox_api_secret\":\"YOUR_KEY\",\"sandbox_website\":\"WEBSTAGING\",\"production_api_key\":\"xWHqlF99814977452652\",\"production_api_secret\":\"dRyWtcr2sKV9%fYD\",\"production_website\":\"DEFAULT\"}', NULL, '2026-09-02 09:20:37', '2026-09-11 08:17:16');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Dashboard View', 'dashboard.view', '2026-09-02 09:20:36', '2026-09-02 09:20:36'),
(2, 'Businesses Manage', 'businesses.manage', '2026-09-02 09:20:36', '2026-09-02 09:20:36'),
(3, 'Cards View', 'cards.view', '2026-09-02 09:20:36', '2026-09-02 09:20:36'),
(4, 'Cards Manage', 'cards.manage', '2026-09-02 09:20:36', '2026-09-02 09:20:36'),
(5, 'Payments Manage', 'payments.manage', '2026-09-02 09:20:36', '2026-09-02 09:20:36'),
(6, 'Transactions View', 'transactions.view', '2026-09-02 09:20:36', '2026-09-02 09:20:36'),
(7, 'Reports View', 'reports.view', '2026-09-02 09:20:36', '2026-09-02 09:20:36'),
(8, 'Settings Manage', 'settings.manage', '2026-09-02 09:20:36', '2026-09-02 09:20:36'),
(9, 'Audit View', 'audit.view', '2026-09-02 09:20:36', '2026-09-02 09:20:36'),
(10, 'Agents Manage', 'agents.manage', '2026-09-02 09:20:36', '2026-09-02 09:20:36');

-- --------------------------------------------------------

--
-- Table structure for table `permission_role`
--

CREATE TABLE `permission_role` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission_role`
--

INSERT INTO `permission_role` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 1),
(2, 2),
(3, 1),
(3, 2),
(3, 3),
(4, 1),
(4, 2),
(5, 1),
(5, 2),
(5, 3),
(6, 1),
(6, 2),
(6, 3),
(7, 1),
(7, 2),
(8, 1),
(9, 1),
(10, 1),
(10, 2);

-- --------------------------------------------------------

--
-- Table structure for table `reference_people`
--

CREATE TABLE `reference_people` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `person_name` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `company_agent_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reference_people`
--

INSERT INTO `reference_people` (`id`, `user_id`, `person_name`, `mobile`, `company_agent_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'persoan 1', '8526548526', 'gdfgdfg', '2026-09-03 05:04:28', '2026-09-03 05:04:28', NULL),
(2, 5, 'hgj', '5555665565', 'hjghj', '2026-09-03 05:11:23', '2026-09-03 05:11:23', NULL),
(3, 5, '67rh', '7733883388', 'ghrtggui5t', '2026-09-03 05:11:23', '2026-09-03 05:11:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'super-admin', '2026-09-02 09:20:36', '2026-09-02 09:20:36'),
(2, 'Admin', 'admin', '2026-09-02 09:20:36', '2026-09-02 09:20:36'),
(3, 'Agent', 'agent', '2026-09-02 09:20:36', '2026-09-02 09:20:36'),
(4, 'tst', 'test', '2026-09-07 05:30:12', '2026-09-07 05:30:12');

-- --------------------------------------------------------

--
-- Table structure for table `sanction_letters`
--

CREATE TABLE `sanction_letters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sanction_number` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `business_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `greeting` text NOT NULL,
  `body` text NOT NULL,
  `dynamic_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dynamic_fields`)),
  `closing` text NOT NULL,
  `notes` text DEFAULT NULL,
  `signature_name` varchar(255) DEFAULT NULL,
  `signature_designation` varchar(255) DEFAULT NULL,
  `signature_company` varchar(255) DEFAULT NULL,
  `signature_image` varchar(255) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `process_fee` decimal(18,2) NOT NULL DEFAULT 0.00,
  `process_fee_status` varchar(255) NOT NULL DEFAULT 'unpaid',
  `process_fee_reference` varchar(255) DEFAULT NULL,
  `process_fee_payment_id` varchar(255) DEFAULT NULL,
  `process_fee_paid_at` timestamp NULL DEFAULT NULL,
  `process_fee_gateway` varchar(255) DEFAULT NULL,
  `process_fee_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`process_fee_response`)),
  `sent_at` timestamp NULL DEFAULT NULL,
  `upload_deadline_at` timestamp NULL DEFAULT NULL,
  `downloaded_at` timestamp NULL DEFAULT NULL,
  `signed_pdf_path` varchar(255) DEFAULT NULL,
  `signed_pdf_uploaded_at` timestamp NULL DEFAULT NULL,
  `signed_pdf_upload_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `review_status` varchar(30) NOT NULL DEFAULT 'pending',
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `review_comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sanction_letters`
--

INSERT INTO `sanction_letters` (`id`, `sanction_number`, `user_id`, `business_id`, `title`, `subject`, `greeting`, `body`, `dynamic_fields`, `closing`, `notes`, `signature_name`, `signature_designation`, `signature_company`, `signature_image`, `pdf_path`, `status`, `process_fee`, `process_fee_status`, `process_fee_reference`, `process_fee_payment_id`, `process_fee_paid_at`, `process_fee_gateway`, `process_fee_response`, `sent_at`, `upload_deadline_at`, `downloaded_at`, `signed_pdf_path`, `signed_pdf_uploaded_at`, `signed_pdf_upload_count`, `review_status`, `reviewed_at`, `reviewed_by`, `review_comment`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1001, 2, 1, 'Sanction Letter A/F', 'Your Advance Fund Sanction Letter from Virexon', 'Dear Sir/Madam,', 'With reference to your application/request for financial support and subject to the terms and conditions of the applicable [[bold:Agent Agreement / Advance Fund Agreement]], we are pleased to inform you that the Company has approved the following Advance Fund in your favour:', '{\"agent_name\":\"erewr\",\"agent_email\":\"firstform@yopmail.com\",\"agent_id\":\"4324\",\"agent_address\":\"dwqd, qdw, qdwqd - wdqd\",\"letter_address\":\"dwqd, qdw, qdwqd - wdqd\",\"business_name\":\"Northstar Retail Pvt Ltd\",\"letter_no\":\"V-EOM\\/SL\\/AF\\/2026\\/001001\",\"reference_no\":\"RFE\\/AF\\/2026\\/001001\",\"sanction_number\":\"1001\",\"document_title\":\"Sanction Letter A\\/F\",\"email_subject\":\"Your Advance Fund Sanction Letter from Virexon\",\"approved_amount\":45,\"disbursement_mode\":\"Bank Account Transfer\",\"process_fee\":300,\"tenure\":\"12 Months\",\"monthly_principal_settlement\":15000,\"monthly_portal_service_charges\":1200,\"total_monthly_settlement\":16200,\"sanction_date\":\"14\\/09\\/2026\",\"signature_name\":\"\",\"signature_designation\":\"\",\"signature_company\":\"\",\"guardian_relation\":\"S\\/o\"}', 'Yours sincerely,', NULL, NULL, NULL, NULL, NULL, 'sanction-letters/sanction-letter-1001-20260914-153327.pdf', 'sent', 0.00, 'unpaid', NULL, NULL, NULL, 'paytm', '{\"attempts\":[{\"order_id\":\"SLPF-1-YIGVTN\",\"state\":\"failed\",\"at\":\"2026-09-15 16:12:17\"}],\"status_api\":{\"head\":{\"responseTimestamp\":\"1789468937372\",\"version\":\"v1\",\"signature\":\"QG97Be+2WPR2c63CucAOuvSxWO8QhdDm4BaMxvGMoFzp2vJuuPtkHe6IHjQHni9mdAjVbWim\\/uZX245QcihT4qlk8ozQ07GE6tSD9BAdr6M=\"},\"body\":{\"resultInfo\":{\"resultStatus\":\"TXN_FAILURE\",\"resultCode\":\"810\",\"resultMsg\":\"ORDER IS CLOSE.\"},\"txnId\":\"20260915011000000306617126106913113\",\"orderId\":\"SLPF-1-YIGVTN\",\"txnAmount\":\"300.00\",\"txnType\":\"SALE\",\"mid\":\"xWHqlF99814977452652\",\"refundAmt\":\"0.0\",\"txnDate\":\"2026-09-15 13:49:58.0\"}}}', '2026-09-14 10:03:28', '2026-09-16 10:03:28', NULL, NULL, NULL, 0, 'pending', NULL, NULL, NULL, '2026-09-14 10:03:28', '2026-09-15 10:42:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sanction_letter_uploads`
--

CREATE TABLE `sanction_letter_uploads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sanction_letter_id` bigint(20) UNSIGNED NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'payment_mode', 'live', '2026-09-03 05:58:27', '2026-09-11 04:36:37'),
(2, 'sanction_letter_sequence', '1002', '2026-09-12 11:42:10', '2026-09-14 10:03:27');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `user_id`, `title`, `message`, `attachment_path`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'test', 'fsdfasfd support ticket testing', 'tickets/CoTaUu1tJAgtQ9iwNTeuYYYsmTJThu95TArSOIu0.jpg', 'resolved', '2026-09-05 05:57:12', '2026-09-05 06:28:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `card_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `gateway` varchar(255) DEFAULT NULL,
  `gateway_transaction_id` varchar(255) DEFAULT NULL,
  `transaction_type` varchar(255) NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `fee` decimal(18,2) NOT NULL DEFAULT 0.00,
  `net_amount` decimal(18,2) NOT NULL,
  `status` varchar(255) NOT NULL,
  `reference` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `business_id`, `user_id`, `card_id`, `payment_id`, `gateway`, `gateway_transaction_id`, `transaction_type`, `amount`, `fee`, `net_amount`, `status`, `reference`, `description`, `metadata`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 2, 1, 1, 'mock', 'mock_pay_demo', 'payment', 12500.00, 0.00, 12500.00, 'successful', 'PAY-DEMO-0001', 'Demo card payment', '{\"seeded\":true}', '2026-09-02 09:20:37', '2026-09-02 09:20:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `business_id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `status`, `deleted_at`) VALUES
(1, 1, NULL, 'Super Admin', 'admin@agent-support.local', '+91 90000 00001', NULL, '$2y$12$jYt0vnT5Na2Tau/OK.NTOOsnulaSfwECScH66NjXbLao9dFinM7KG', NULL, '2026-09-02 09:20:36', '2026-09-02 09:20:36', 'active', NULL),
(2, 3, 1, 'erewr', 'firstform@yopmail.com', '+91 90000 00002', NULL, '$2y$12$siAzHPMO98jkvRAsGVafhOs9Spgu2HE3rBrd2MQ5RHZJ8zoNUnBDu', NULL, '2026-09-02 09:20:37', '2026-09-03 05:04:28', 'active', NULL),
(4, 3, NULL, 'fewef', 'gfgf@yopmail.com', NULL, NULL, '$2y$12$zKraq/DZzI8MfSwKoM15o.CArAn.ylTcYKhDpeXqUbRTHzcPz9D.m', NULL, '2026-09-02 14:48:01', '2026-09-15 07:42:34', 'active', '2026-09-15 07:42:34'),
(5, 3, NULL, 'tttt', 'ttttttt@yopmail.com', NULL, NULL, '$2y$12$fJqjtGuegxTsghnXNcOGredfm4KTWs3/5zu/.l1j8iOaDjxO1e0Om', NULL, '2026-09-03 05:11:23', '2026-09-03 05:11:23', 'active', NULL),
(6, 3, NULL, 'Test Agent', 'testagent@example.com', NULL, NULL, '$2y$12$cZghAEfoDddv5gOOA0GKcuFH/MrKGSSp9dTBnEnJ8tC1vDtj4rleW', NULL, '2026-09-03 10:42:34', '2026-09-03 10:42:34', 'active', NULL),
(7, 3, 1, 'Rahul Sharma', 'agent@agent-support.local', '+91 90000 00002', NULL, '$2y$12$siAzHPMO98jkvRAsGVafhOs9Spgu2HE3rBrd2MQ5RHZJ8zoNUnBDu', NULL, '2026-09-03 10:48:11', '2026-09-03 10:48:11', 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_details`
--

CREATE TABLE `user_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `application_status` enum('pending','form_received','approved','rejected') NOT NULL DEFAULT 'pending',
  `agent_id_number` varchar(255) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `personal_email` varchar(255) DEFAULT NULL,
  `whatsapp_number` varchar(255) DEFAULT NULL,
  `is_married` tinyint(1) NOT NULL DEFAULT 0,
  `spouse_name` varchar(255) DEFAULT NULL,
  `spouse_mobile` varchar(255) DEFAULT NULL,
  `current_address` text DEFAULT NULL,
  `current_city` varchar(255) DEFAULT NULL,
  `current_state` varchar(255) DEFAULT NULL,
  `current_pincode` varchar(255) DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `permanent_city` varchar(255) DEFAULT NULL,
  `permanent_state` varchar(255) DEFAULT NULL,
  `permanent_pincode` varchar(255) DEFAULT NULL,
  `loan_amount` decimal(10,2) DEFAULT NULL,
  `loan_tenure` int(11) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `routing_number` varchar(255) DEFAULT NULL,
  `pan_number` varchar(255) DEFAULT NULL,
  `aadhar_number` varchar(255) DEFAULT NULL,
  `pan_file_path` varchar(255) DEFAULT NULL,
  `aadhar_file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `guardian_name` varchar(255) DEFAULT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `account_type` varchar(255) DEFAULT NULL,
  `branch_name` varchar(255) DEFAULT NULL,
  `purpose_of_advance` text DEFAULT NULL,
  `max_limit` decimal(18,2) NOT NULL DEFAULT 500000.00,
  `commission_rate` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `commission_fixed` decimal(18,2) NOT NULL DEFAULT 0.00,
  `commission_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_details`
--

INSERT INTO `user_details` (`id`, `user_id`, `application_status`, `agent_id_number`, `father_name`, `mother_name`, `date_of_birth`, `gender`, `mobile`, `personal_email`, `whatsapp_number`, `is_married`, `spouse_name`, `spouse_mobile`, `current_address`, `current_city`, `current_state`, `current_pincode`, `permanent_address`, `permanent_city`, `permanent_state`, `permanent_pincode`, `loan_amount`, `loan_tenure`, `account_name`, `bank_name`, `account_number`, `routing_number`, `pan_number`, `aadhar_number`, `pan_file_path`, `aadhar_file_path`, `created_at`, `updated_at`, `guardian_name`, `address_line_2`, `account_type`, `branch_name`, `purpose_of_advance`, `max_limit`, `commission_rate`, `commission_fixed`, `commission_type`, `deleted_at`) VALUES
(1, 4, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50000.00, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-02 14:48:01', '2026-09-15 07:42:33', NULL, NULL, NULL, NULL, NULL, 500000.00, 0.0000, 0.00, 'percentage', '2026-09-15 07:42:33'),
(4, 2, 'pending', '4324', 'rewrr', NULL, '2026-09-08', 'male', '435345345', NULL, NULL, 0, NULL, NULL, 'dwqd', 'qdw', 'qdwqd', 'wdqd', NULL, NULL, NULL, NULL, 45.00, 60, 'dwqd', 'wqdwq', 'dwqd', 'wqdq', '3423423423', NULL, NULL, NULL, '2026-09-03 05:04:28', '2026-09-14 06:04:09', 'rewrr', 'wdd', 'Savings', 'fergerg', 'gdfgbdfhdfh', 20000.00, 5.0000, 200.00, 'fixed', NULL),
(5, 5, 'pending', 'ttttt', 'tttt', NULL, '2026-09-30', 'male', '53425234324', NULL, NULL, 0, NULL, NULL, 'trtet', 'gjhj', 'hgjhg', 'jhgj', NULL, NULL, NULL, NULL, 55.00, 60, 'hjhgj', 'hgj', 'hgjj', 'jhgj', '34543534', NULL, NULL, NULL, '2026-09-03 05:11:23', '2026-09-03 05:11:23', 'tttt', 'hgfhjh', 'Savings', 'hgj', 'jhgjghj', 500000.00, 0.0000, 0.00, 'percentage', NULL),
(6, 7, 'pending', 'AGT12345', NULL, NULL, '1990-05-15', 'male', '9876543210', 'rahul.personal@gmail.com', NULL, 1, NULL, NULL, '123 Support St', 'Mumbai', 'Maharashtra', '400001', NULL, NULL, NULL, NULL, NULL, NULL, 'Rahul Sharma', 'HDFC Bank', '50100200300400', 'HDFC0001234', 'ABCDE1234F', NULL, NULL, NULL, '2026-09-04 08:20:00', '2026-09-04 08:20:00', 'Rajesh Sharma', NULL, 'Savings', 'Andheri', NULL, 500000.00, 0.0000, 0.00, 'percentage', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `virtual_cards`
--

CREATE TABLE `virtual_cards` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `agent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference` varchar(255) NOT NULL,
  `provider_card_id` varchar(255) DEFAULT NULL,
  `encrypted_pan` text DEFAULT NULL,
  `encrypted_cvv` text DEFAULT NULL,
  `last4` varchar(4) DEFAULT NULL,
  `cardholder_name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `card_limit` decimal(18,2) NOT NULL,
  `daily_limit` decimal(18,2) NOT NULL,
  `monthly_limit` decimal(18,2) NOT NULL,
  `per_transaction_limit` decimal(18,2) NOT NULL,
  `current_usage` decimal(18,2) NOT NULL DEFAULT 0.00,
  `expiry_date` date NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `last_transaction_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `virtual_cards`
--

INSERT INTO `virtual_cards` (`id`, `business_id`, `agent_id`, `reference`, `provider_card_id`, `encrypted_pan`, `encrypted_cvv`, `last4`, `cardholder_name`, `status`, `card_limit`, `daily_limit`, `monthly_limit`, `per_transaction_limit`, `current_usage`, `expiry_date`, `created_by`, `last_transaction_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 2, 'VC-DEMO-0001', 'sandbox_card_001', 'eyJpdiI6IkFBZnI2YXg3L09WMXN0Z2JNdEVCL1E9PSIsInZhbHVlIjoianhLV0Zaai9ZRXk1OVNndGwvM1dPNWdyRVJHNFBmbEduVS9qMEV1a0t5OD0iLCJtYWMiOiI2MjliM2ZjZjI5MGY5YWU1YzIxMTBiZmEzMjBhMzFjNjE4ZGQxYzM2YmIyYzY1OGU0OGM3ZmZjNzA0ZDdkMjJmIiwidGFnIjoiIn0=', 'eyJpdiI6InVvNDcxUkVka0Z0WUFOSFpuTEt1Q1E9PSIsInZhbHVlIjoidTRYN1hpb3pXL0VzSG5vU0RvZis0UT09IiwibWFjIjoiZDYwZTU5NzdiMjg3N2NhNWJmMGYwNTMyMmE4ODE0MDI1MDBmMzAxYTNmYTlhZjI0Y2IyYTAyYjI0MGMzNWFhYyIsInRhZyI6IiJ9', '1111', 'Rahul Sharma', 'active', 20000.00, 20000.00, 200000.00, 20000.00, 12500.00, '2027-09-02', 1, '2026-09-09 12:07:14', '2026-09-02 09:20:37', '2026-09-13 07:32:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `type` enum('credit','debit') NOT NULL DEFAULT 'credit',
  `status` enum('pending','success','failed') NOT NULL DEFAULT 'pending',
  `gateway` varchar(255) NOT NULL DEFAULT 'paytm',
  `txn_token` text DEFAULT NULL,
  `txn_id` varchar(255) DEFAULT NULL,
  `bank_txn_id` varchar(255) DEFAULT NULL,
  `payment_mode` varchar(255) DEFAULT NULL,
  `gateway_name` varchar(255) DEFAULT NULL,
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_response`)),
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `webhook_events`
--

CREATE TABLE `webhook_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `gateway` varchar(255) NOT NULL,
  `event_id` varchar(255) DEFAULT NULL,
  `event_type` varchar(255) DEFAULT NULL,
  `payload_hash` varchar(255) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `advances`
--
ALTER TABLE `advances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `advances_user_id_foreign` (`user_id`);

--
-- Indexes for table `agent_documents`
--
ALTER TABLE `agent_documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `agent_documents_user_id_document_type_unique` (`user_id`,`document_type`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_id_foreign` (`user_id`),
  ADD KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`);

--
-- Indexes for table `businesses`
--
ALTER TABLE `businesses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `businesses_created_by_foreign` (`created_by`),
  ADD KEY `businesses_status_index` (`status`);

--
-- Indexes for table `commissions`
--
ALTER TABLE `commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commissions_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_reference_unique` (`reference`),
  ADD KEY `payments_user_id_foreign` (`user_id`),
  ADD KEY `payments_card_id_foreign` (`card_id`),
  ADD KEY `payments_business_id_status_index` (`business_id`,`status`),
  ADD KEY `payments_gateway_index` (`gateway`),
  ADD KEY `payments_gateway_payment_id_index` (`gateway_payment_id`),
  ADD KEY `payments_gateway_order_id_index` (`gateway_order_id`),
  ADD KEY `payments_status_index` (`status`);

--
-- Indexes for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_gateways_slug_unique` (`slug`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`);

--
-- Indexes for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `permission_role_role_id_foreign` (`role_id`);

--
-- Indexes for table `reference_people`
--
ALTER TABLE `reference_people`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_people_company_agent_id_unique` (`company_agent_id`),
  ADD KEY `reference_people_user_id_foreign` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`);

--
-- Indexes for table `sanction_letters`
--
ALTER TABLE `sanction_letters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sanction_letters_sanction_number_unique` (`sanction_number`),
  ADD UNIQUE KEY `sanction_letters_process_fee_reference_unique` (`process_fee_reference`),
  ADD KEY `assertion_letters_user_id_foreign` (`user_id`),
  ADD KEY `assertion_letters_business_id_foreign` (`business_id`),
  ADD KEY `sanction_letters_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `sanction_letters_review_status_index` (`review_status`);

--
-- Indexes for table `sanction_letter_uploads`
--
ALTER TABLE `sanction_letter_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sanction_letter_uploads_sanction_letter_id_foreign` (`sanction_letter_id`),
  ADD KEY `sanction_letter_uploads_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `system_settings_key_unique` (`key`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tickets_user_id_foreign` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transactions_business_id_foreign` (`business_id`),
  ADD KEY `transactions_user_id_foreign` (`user_id`),
  ADD KEY `transactions_payment_id_foreign` (`payment_id`),
  ADD KEY `transactions_card_id_transaction_type_status_index` (`card_id`,`transaction_type`,`status`),
  ADD KEY `transactions_gateway_transaction_id_index` (`gateway_transaction_id`),
  ADD KEY `transactions_transaction_type_index` (`transaction_type`),
  ADD KEY `transactions_status_index` (`status`),
  ADD KEY `transactions_reference_index` (`reference`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_id_foreign` (`role_id`),
  ADD KEY `users_business_id_foreign` (`business_id`),
  ADD KEY `users_status_index` (`status`);

--
-- Indexes for table `user_details`
--
ALTER TABLE `user_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_details_agent_id_number_unique` (`agent_id_number`),
  ADD UNIQUE KEY `user_details_pan_number_unique` (`pan_number`),
  ADD UNIQUE KEY `user_details_aadhar_number_unique` (`aadhar_number`),
  ADD KEY `user_details_user_id_foreign` (`user_id`);

--
-- Indexes for table `virtual_cards`
--
ALTER TABLE `virtual_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `virtual_cards_reference_unique` (`reference`),
  ADD UNIQUE KEY `virtual_cards_agent_id_unique` (`agent_id`),
  ADD KEY `virtual_cards_created_by_foreign` (`created_by`),
  ADD KEY `virtual_cards_business_id_status_index` (`business_id`,`status`),
  ADD KEY `virtual_cards_status_index` (`status`),
  ADD KEY `virtual_cards_agent_id_status_index` (`agent_id`,`status`),
  ADD KEY `virtual_cards_provider_card_id_index` (`provider_card_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallets_user_id_index` (`user_id`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wallet_transactions_order_id_unique` (`order_id`);

--
-- Indexes for table `webhook_events`
--
ALTER TABLE `webhook_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `webhook_events_gateway_payload_hash_unique` (`gateway`,`payload_hash`),
  ADD KEY `webhook_events_gateway_index` (`gateway`),
  ADD KEY `webhook_events_payload_hash_index` (`payload_hash`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `advances`
--
ALTER TABLE `advances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `agent_documents`
--
ALTER TABLE `agent_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `businesses`
--
ALTER TABLE `businesses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `commissions`
--
ALTER TABLE `commissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reference_people`
--
ALTER TABLE `reference_people`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sanction_letters`
--
ALTER TABLE `sanction_letters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sanction_letter_uploads`
--
ALTER TABLE `sanction_letter_uploads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_details`
--
ALTER TABLE `user_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `virtual_cards`
--
ALTER TABLE `virtual_cards`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `webhook_events`
--
ALTER TABLE `webhook_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `advances`
--
ALTER TABLE `advances`
  ADD CONSTRAINT `advances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `agent_documents`
--
ALTER TABLE `agent_documents`
  ADD CONSTRAINT `agent_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `businesses`
--
ALTER TABLE `businesses`
  ADD CONSTRAINT `businesses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `commissions`
--
ALTER TABLE `commissions`
  ADD CONSTRAINT `commissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_card_id_foreign` FOREIGN KEY (`card_id`) REFERENCES `virtual_cards` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reference_people`
--
ALTER TABLE `reference_people`
  ADD CONSTRAINT `reference_people_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sanction_letters`
--
ALTER TABLE `sanction_letters`
  ADD CONSTRAINT `assertion_letters_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assertion_letters_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sanction_letters_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sanction_letter_uploads`
--
ALTER TABLE `sanction_letter_uploads`
  ADD CONSTRAINT `sanction_letter_uploads_sanction_letter_id_foreign` FOREIGN KEY (`sanction_letter_id`) REFERENCES `sanction_letters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sanction_letter_uploads_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_card_id_foreign` FOREIGN KEY (`card_id`) REFERENCES `virtual_cards` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_details`
--
ALTER TABLE `user_details`
  ADD CONSTRAINT `user_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `virtual_cards`
--
ALTER TABLE `virtual_cards`
  ADD CONSTRAINT `virtual_cards_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `virtual_cards_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `virtual_cards_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
