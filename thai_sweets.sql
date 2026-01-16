-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 16, 2026 at 05:58 AM
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
-- Database: `thai_sweets`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `receiver_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `order_code` varchar(20) DEFAULT NULL,
  `total_price` int(11) DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `slip_image` varchar(255) DEFAULT NULL,
  `transfer_time` datetime DEFAULT NULL,
  `reject_note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `receiver_name`, `phone`, `address`, `order_code`, `total_price`, `payment_method`, `created_at`, `status`, `slip_image`, `transfer_time`, `reject_note`) VALUES
(5, NULL, NULL, NULL, NULL, 'OD20251223033443', 50, 'bank', '2025-12-23 09:34:43', 'pending', NULL, NULL, NULL),
(6, NULL, NULL, NULL, NULL, 'OD20251223033450', 50, 'bank', '2025-12-23 09:34:50', 'pending', NULL, NULL, NULL),
(7, NULL, NULL, NULL, NULL, 'OD20251223033559', 35, 'bank', '2025-12-23 09:35:59', 'pending', NULL, NULL, NULL),
(8, 1, NULL, NULL, NULL, 'OD20251223034214', 50, 'bank', '2025-12-23 09:42:14', 'pending', NULL, NULL, NULL),
(9, 1, NULL, NULL, NULL, 'OD20251223035226', 50, 'bank', '2025-12-23 09:52:26', 'pending', NULL, NULL, NULL),
(10, 1, NULL, NULL, NULL, 'OD20251223060945', 150, 'bank', '2025-12-23 12:09:45', 'pending', NULL, NULL, NULL),
(11, 1, NULL, NULL, NULL, 'OD20251223061732', 215, 'bank', '2025-12-23 12:17:32', 'pending', NULL, NULL, NULL),
(12, 1, NULL, NULL, NULL, 'OD20251223062209', 30, 'bank', '2025-12-23 12:22:09', 'pending', NULL, NULL, NULL),
(13, 1, NULL, NULL, NULL, 'OD20251223072351', 35, 'bank', '2025-12-23 13:23:51', 'pending', NULL, NULL, NULL),
(14, 1, NULL, NULL, NULL, 'OD20251223072507', 65, 'bank', '2025-12-23 13:25:07', 'pending', NULL, NULL, NULL),
(15, 1, NULL, NULL, NULL, 'OD20251223072906', 85, 'bank', '2025-12-23 13:29:06', 'pending', NULL, NULL, NULL),
(16, 1, NULL, NULL, NULL, 'OD20251223073029', 65, 'bank', '2025-12-23 13:30:29', 'pending', NULL, NULL, NULL),
(17, 1, NULL, NULL, NULL, 'OD20251223073630', 45, 'bank', '2025-12-23 13:36:30', 'pending', NULL, NULL, NULL),
(18, 1, NULL, NULL, NULL, 'OD20251223074424', 65, 'bank', '2025-12-23 13:44:24', 'pending', NULL, NULL, NULL),
(19, 1, NULL, NULL, NULL, 'OD20251225071033', 278, 'bank', '2025-12-25 13:10:33', 'pending', NULL, NULL, NULL),
(20, 1, NULL, NULL, NULL, 'OD20251225072025', 110, 'bank', '2025-12-25 13:20:25', 'pending', NULL, NULL, NULL),
(21, 1, NULL, NULL, NULL, 'OD20251225073042', 279, 'bank', '2025-12-25 13:30:42', 'pending', NULL, NULL, NULL),
(23, 1, NULL, NULL, NULL, NULL, 105, 'promptpay', '2026-01-07 10:55:25', 'pending', NULL, NULL, NULL),
(24, 1, NULL, NULL, NULL, NULL, 50, 'promptpay', '2026-01-07 10:55:36', 'pending', NULL, NULL, NULL),
(25, 1, NULL, NULL, NULL, NULL, 50, 'promptpay', '2026-01-07 10:58:28', 'pending', NULL, NULL, NULL),
(27, 1, NULL, NULL, NULL, NULL, 114, 'promptpay', '2026-01-07 11:12:46', 'pending', NULL, NULL, NULL),
(28, 1, NULL, NULL, NULL, NULL, 55, 'promptpay', '2026-01-07 11:12:57', 'pending', NULL, NULL, NULL),
(29, 1, NULL, NULL, NULL, NULL, 124, 'bank', '2026-01-07 11:13:22', 'pending', NULL, NULL, NULL),
(30, 1, NULL, NULL, NULL, NULL, 59, 'bank', '2026-01-07 11:16:47', 'pending', NULL, NULL, NULL),
(31, 1, NULL, NULL, NULL, 'ORD1768277590', 100, 'promptpay', '2026-01-13 11:13:10', 'pending', NULL, NULL, NULL),
(32, 1, NULL, NULL, NULL, 'ORD1768277616', 109, 'promptpay', '2026-01-13 11:13:36', 'pending', NULL, NULL, NULL),
(33, 1, NULL, NULL, NULL, 'ORD1768277972', 55, 'promptpay', '2026-01-13 11:19:32', 'pending', NULL, NULL, NULL),
(34, 1, NULL, NULL, NULL, 'ORD1768278252', 59, 'promptpay', '2026-01-13 11:24:12', 'pending', NULL, NULL, NULL),
(35, 1, NULL, NULL, NULL, 'ORD1768283235', 59, 'bank', '2026-01-13 12:47:15', 'pending', NULL, NULL, NULL),
(36, 1, NULL, NULL, NULL, 'ORD1768283415', 69, 'bank', '2026-01-13 12:50:15', 'shipped', 'slip_1768283473.jpg', NULL, NULL),
(37, 1, NULL, NULL, NULL, 'ORD1768283686', 178, 'promptpay', '2026-01-13 12:54:46', 'shipped', 'slip_1768283850.jpg', NULL, NULL),
(38, 1, NULL, NULL, NULL, 'ORD1768285123', 119, 'promptpay', '2026-01-13 13:18:43', 'shipped', 'slip_1768285197.png', NULL, NULL),
(39, 1, NULL, NULL, NULL, 'ORD1768458673', 65, 'bank', '2026-01-15 13:31:13', 'pending', NULL, NULL, NULL),
(40, 10, NULL, NULL, NULL, 'ORD6968B58161495', 109, '', '2026-01-15 16:38:09', 'paid', 'slip_40_1768476271.webp', '2026-01-15 18:21:00', NULL),
(41, 10, NULL, NULL, NULL, 'ORD6968CFF279927', 59, '', '2026-01-15 18:30:58', 'paid', 'slip_41_1768476863.webp', '2026-01-15 18:34:00', NULL),
(42, 10, NULL, NULL, NULL, 'ORD6968D1C594578', 59, '', '2026-01-15 18:38:45', 'paid', 'slip_42_1768477141.png', '2026-01-15 20:38:00', NULL),
(43, 10, NULL, NULL, NULL, 'ORD6968D2DDB4C44', 50, '', '2026-01-15 18:43:25', 'paid', 'slip_43_1768477418.webp', '2026-01-11 18:43:00', NULL),
(44, 10, NULL, NULL, NULL, 'ORD6968E3A94F589', 342, '', '2026-01-15 19:55:05', 'paid', 'slip_44_1768481721.webp', '2026-01-15 19:55:00', NULL),
(45, 10, NULL, NULL, NULL, 'ORD6968E45A35801', 638, '', '2026-01-15 19:58:02', 'paid', 'slip_45_1768481898.webp', '2026-01-15 19:58:00', NULL),
(46, 10, NULL, NULL, NULL, 'ORD6968E4D159BE0', 536, '', '2026-01-15 20:00:01', 'paid', 'slip_46_1768524543.webp', '2026-01-22 07:27:00', NULL),
(47, 10, NULL, NULL, NULL, 'ORD69698600DD947', 613, '', '2026-01-16 07:27:44', 'pending', NULL, '2026-01-16 09:55:00', 'ไม่ผ่าน'),
(48, 10, NULL, NULL, NULL, 'ORD69699326D89E5', 397, '', '2026-01-16 08:23:50', 'pending', NULL, '2026-01-16 09:58:00', NULL),
(49, 10, NULL, NULL, NULL, 'ORD6969939F3EBD6', 110, '', '2026-01-16 08:25:51', 'pending', NULL, '2026-01-16 10:00:00', NULL),
(50, 9, NULL, NULL, NULL, 'ORD6969A4AFA7254', 275, '', '2026-01-16 09:38:39', 'pending', NULL, '2026-01-16 09:54:00', NULL),
(51, 9, NULL, NULL, NULL, 'ORD6969A59153FFA', 150, '', '2026-01-16 09:42:25', 'pending', NULL, '2026-01-16 11:21:00', 'asdasdas'),
(52, 9, NULL, NULL, NULL, 'ORD6969A7FF19EF0', 224, '', '2026-01-16 09:52:47', 'pending', NULL, NULL, NULL),
(53, 10, NULL, NULL, NULL, 'ORD6969A8AAD3248', 165, '', '2026-01-16 09:55:38', 'pending', NULL, NULL, NULL),
(57, 10, 'admin7221', '0644892028', 'aesfasfsa', 'TH1768532853', 150, 'bank', '2026-01-16 10:07:33', 'pending', NULL, NULL, NULL),
(58, 10, 'admin7221', '0644892028', 'ฟหกฟก', 'TH1768532872', 150, 'bank', '2026-01-16 10:07:52', 'pending', NULL, NULL, NULL),
(59, 9, 'THEERATEP REAKYAM', '0644892028', '165\r\n-', 'OD20260116042231', 165, '', '2026-01-16 10:22:31', 'pending', NULL, NULL, NULL),
(60, 9, 'THEERATEP REAKYAM', '0644892028', '165\r\n-', 'OD20260116042312', 300, '', '2026-01-16 10:23:12', 'pending', NULL, NULL, NULL),
(61, 10, 'admin7221', '0644892028', 'ฟหกฟก', 'TH1768536106', 150, 'bank', '2026-01-16 11:01:46', 'waiting_verification', 'slip_1768536106_6969b82a6df62.jpg', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `total` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `qty`, `total`) VALUES
(1, 1, 0, 'ทองหยิบ', 50, 1, 50),
(2, 2, 0, 'ทองหยิบ', 35, 1, 35),
(3, 2, 0, 'ทองหยิบ', 50, 1, 50),
(4, 3, 0, 'ทองหยิบ', 35, 1, 35),
(5, 4, 0, 'ทองหยิบ', 50, 1, 50),
(6, 4, 0, 'ทองหยิบ', 50, 1, 50),
(7, 5, 0, 'ทองหยิบ', 50, 1, 50),
(8, 6, 0, 'ทองหยิบ', 50, 1, 50),
(9, 7, 0, 'ทองหยิบ', 35, 1, 35),
(10, 8, 0, 'ทองหยิบ', 50, 1, 50),
(11, 9, 0, 'ทองหยิบ', 50, 1, 50),
(12, 10, 0, 'ขนมชั้น', 25, 2, 50),
(13, 10, 0, 'ทองหยิบ', 50, 2, 100),
(14, 11, 0, 'ทองหยิบ', 50, 2, 100),
(15, 11, 0, 'ทองหยอด', 30, 1, 30),
(16, 11, 0, 'ฝอยทอง', 40, 1, 40),
(17, 11, 0, 'ลูกชุบ', 45, 1, 45),
(18, 12, 0, 'ทองหยอด', 30, 1, 30),
(19, 13, 0, 'ทองหยิบ', 35, 1, 35),
(20, 14, 0, 'ทองหยอด', 30, 1, 30),
(21, 14, 0, 'ทองหยิบ', 35, 1, 35),
(22, 15, 0, 'ทองหยิบ', 35, 1, 35),
(23, 15, 0, 'ทองหยิบ', 50, 1, 50),
(24, 16, 0, 'ทองหยิบ', 35, 1, 35),
(25, 16, 0, 'ทองหยอด', 30, 1, 30),
(26, 17, 0, 'ลูกชุบ', 45, 1, 45),
(27, 18, 0, 'ทองหยิบ', 35, 1, 35),
(28, 18, 0, 'ทองหยอด', 30, 1, 30),
(29, 19, 0, 'บัวลอย 3 สี', 59, 1, 59),
(30, 19, 0, 'ขนมถ้วยใบเตย', 50, 1, 50),
(31, 19, 0, 'วุ้นเป็ดมะพร้าวอ่อน', 59, 1, 59),
(32, 19, 0, 'ข้าวเหนียวมะม่วง', 55, 2, 110),
(33, 20, 0, 'ข้าวเหนียวมะม่วง', 55, 2, 110),
(34, 21, 0, 'เม็ดขนุนชาววัง (เผือก)', 69, 1, 69),
(35, 21, 0, 'ขนมถ้วยใบเตย', 50, 2, 100),
(36, 21, 0, 'ข้าวเหนียวมะม่วง', 55, 2, 110),
(37, 23, 0, 'ขนมถ้วยใบเตย', 50, 1, 50),
(38, 23, 0, 'ข้าวเหนียวมะม่วง', 55, 1, 55),
(39, 24, 0, 'ขนมถ้วยใบเตย', 50, 1, 50),
(40, 25, 0, 'ขนมถ้วยใบเตย', 50, 1, 50),
(41, 27, 0, 'ข้าวเหนียวมะม่วง', 55, 1, 55),
(42, 27, 0, 'บัวลอย 3 สี', 59, 1, 59),
(43, 28, 0, 'ข้าวเหนียวมะม่วง', 55, 1, 55),
(44, 29, 0, 'ข้าวเหนียวมะม่วง', 55, 1, 55),
(45, 29, 0, 'เม็ดขนุนชาววัง (เผือก)', 69, 1, 69),
(46, 30, 0, 'บัวลอย 3 สี', 59, 1, 59),
(47, 31, 0, 'ขนมถ้วยใบเตย', 50, 2, 100),
(48, 32, 0, 'บัวลอย 3 สี', 59, 1, 59),
(49, 32, 0, 'ขนมถ้วยใบเตย', 50, 1, 50),
(50, 33, 0, 'ข้าวเหนียวมะม่วง', 55, 1, 55),
(51, 34, 0, 'บัวลอย 3 สี', 59, 1, 59),
(52, 35, 0, 'บัวลอย 3 สี', 59, 1, 59),
(53, 36, 0, 'เม็ดขนุนชาววัง (เผือก)', 69, 1, 69),
(54, 37, 0, 'ขนมถ้วยใบเตย', 50, 1, 50),
(55, 37, 0, 'เม็ดขนุนชาววัง (ถั่ว)', 69, 1, 69),
(56, 37, 0, 'บัวลอย 3 สี', 59, 1, 59),
(57, 38, 0, 'ขนมถ้วยใบเตย', 50, 1, 50),
(58, 38, 0, 'เม็ดขนุนชาววัง (ถั่ว)', 69, 1, 69),
(59, 39, 0, 'ทองหยิบ', 65, 1, 65),
(60, 57, 31, 'ขนมถ้วยใบเตย', 50, 3, NULL),
(61, 58, 31, 'ขนมถ้วยใบเตย', 50, 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `detail` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `detail`, `image`, `created_at`, `description`) VALUES
(2, 'ทองหยอด', 65.00, NULL, '1766638572_Thong Yod.jpg', '2025-12-17 07:06:11', NULL),
(3, 'ฝอยทอง', 70.00, NULL, '1766640279_foithong.jpg', '2025-12-17 07:06:11', NULL),
(4, 'ขนมชั้นใบเตย', 60.00, NULL, '1766638586_Pandan layer cake.jpg', '2025-12-17 07:06:11', NULL),
(9, 'หม้อแกงฟักทอง', 60.00, NULL, '1766638562_Pumpkin curry.jpg', '2025-12-25 04:46:03', NULL),
(10, 'ขนมชั้นอัญชันงาดำ', 59.00, NULL, '1766638550_Butterfly pea and black sesame layered dessert.jpg', '2025-12-25 04:49:54', NULL),
(12, 'ขนมชั้นน้ำตาลสด', 55.00, NULL, '1766638508_694cc3ac0517b.jpg', '2025-12-25 04:55:08', NULL),
(13, 'หม้อแกงเผือก', 60.00, NULL, '1766638691_694cc463290be.jpg', '2025-12-25 04:58:11', NULL),
(15, 'หม้อแกงถั่วทองน้ำตาลโตนด', 65.00, NULL, '1766638872_694cc51866d8a.jpg', '2025-12-25 05:01:12', NULL),
(17, 'หม้อแกงถั่ว', 60.00, NULL, '1766639085_Bean curry casserole.jpg', '2025-12-25 05:04:26', NULL),
(18, 'บะบิ่นมะพร้าวอ่อน', 59.00, NULL, '1766639206_694cc666e912b.jpg', '2025-12-25 05:06:46', NULL),
(19, 'บ้าบิ่นใบเตย', 59.00, NULL, '1766639267_694cc6a34cb03.jpg', '2025-12-25 05:07:47', NULL),
(20, 'บ้าบิ่นมะพร้าวอ่อน', 59.00, NULL, '1766639306_694cc6ca832f4.jpg', '2025-12-25 05:08:26', NULL),
(21, 'วุ้นพุดดิ้งหม้อแกงเผือก', 75.00, NULL, '1766639435_694cc74b64b6a.jpg', '2025-12-25 05:10:35', NULL),
(22, 'วุ้นกะทิน้ำตาลสด', 55.00, NULL, '1766639546_694cc7baeabb0.jpg', '2025-12-25 05:12:26', NULL),
(23, 'วุ้นเป็ดมะพร้าวอ่อน', 59.00, NULL, '1766639656_Young coconut jelly in duck shape.jpg', '2025-12-25 05:13:35', NULL),
(26, 'ทองหยิบ', 65.00, NULL, '1766639893_694cc9158dade.jpg', '2025-12-25 05:18:13', NULL),
(27, 'ข้าวเหนียวตัดอัญชันหน้าทองหยอด', 59.00, NULL, '1766640187_694cca3b5c442.jpg', '2025-12-25 05:23:07', NULL),
(28, 'เม็ดขนุนชาววัง (ถั่ว)', 69.00, NULL, '1766640523_694ccb8be57fa.jpg', '2025-12-25 05:28:43', NULL),
(29, 'เม็ดขนุนชาววัง (เผือก)', 69.00, NULL, '1766640603_694ccbdb61120.jpg', '2025-12-25 05:30:03', NULL),
(30, 'ข้าวเหนียวมะม่วง', 55.00, NULL, '1766641159_694cce074cbbf.jpg', '2025-12-25 05:39:19', NULL),
(31, 'ขนมถ้วยใบเตย', 50.00, NULL, '1766641310_694cce9e299aa.jpg', '2025-12-25 05:41:50', NULL),
(32, 'บัวลอย 3 สี', 59.00, NULL, '1766641443_694ccf2369049.jpg', '2025-12-25 05:44:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('bank_account_name', ''),
('bank_account_number', ''),
('bank_name', 'กสิกรไทย'),
('site_address', ''),
('site_email', ''),
('site_name', 'ร้านขนมไทย'),
('site_phone', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_img` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `last_active` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `address`, `profile_img`, `password`, `created_at`, `role`, `last_active`) VALUES
(1, 'กิ่งดาว', NULL, NULL, NULL, NULL, '$2y$10$xJUQSOahOpLbHaH191.SGOjZjFnzV58YcABZAeWzBb6/0PcPESPUu', '2025-12-17 07:00:55', 'user', '2026-01-16 11:46:42'),
(5, 'สุจิรา', NULL, NULL, NULL, NULL, '$2y$10$AKbXyn2rJ891i7MassJR9Og48ZnIdQ.Dp4sLVrOglaWNyz6CYeRBG', '2025-12-23 05:54:52', 'user', '2026-01-16 11:46:42'),
(6, 'admin', NULL, NULL, NULL, NULL, '$2y$10$8.uX8E.zSIn7q5S0M/Y7reT7Y0/8UvLhKz6R.u7K8K8K8K8K8K8K', '2025-12-25 04:14:55', 'admin', '2026-01-16 11:46:42'),
(9, 'admin2', NULL, NULL, NULL, NULL, '123456', '2026-01-15 06:54:46', 'admin', '2026-01-16 11:56:08'),
(10, 'Tzllx', 'dxkpor@gmail.com', '0644892028', '165', 'profile_10_1768525821.jpg', 'dx1234', '2026-01-15 07:43:37', 'user', '2026-01-16 11:56:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
