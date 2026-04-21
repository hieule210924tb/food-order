-- =========================================================
-- WowFood optimized schema (fresh import)
-- File: sql/food-oder-optimized.sql
-- Use case: import quickly on a new database
-- Target: MariaDB 10.4+ / MySQL 8+
-- =========================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS `food-oder`;
CREATE DATABASE `food-oder` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `food-oder`;

-- ---------------------------------------------------------
-- Core tables
-- ---------------------------------------------------------
CREATE TABLE `tbl_admin` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_admin_username` (`username`),
  UNIQUE KEY `uniq_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_user` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `ghn_province_id` int(10) UNSIGNED DEFAULT NULL,
  `ghn_district_id` int(10) UNSIGNED DEFAULT NULL,
  `ghn_ward_code` varchar(20) DEFAULT NULL,
  `status` varchar(10) DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_username` (`username`),
  UNIQUE KEY `uniq_user_email` (`email`),
  KEY `idx_user_phone` (`phone`),
  KEY `idx_user_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_category` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `featured` varchar(10) NOT NULL DEFAULT 'Yes',
  `active` varchar(10) NOT NULL DEFAULT 'Yes',
  `image_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_food` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `description` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_name` varchar(255) NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `featured` varchar(10) NOT NULL DEFAULT 'Yes',
  `active` varchar(10) NOT NULL DEFAULT 'Yes',
  `quantity` int(11) NOT NULL DEFAULT 999,
  PRIMARY KEY (`id`),
  KEY `idx_food_category` (`category_id`),
  CONSTRAINT `fk_food_category` FOREIGN KEY (`category_id`) REFERENCES `tbl_category` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_food_price_non_negative` CHECK (`price` >= 0),
  CONSTRAINT `chk_food_quantity_non_negative` CHECK (`quantity` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_size` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `price_add` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_side_dish` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `type` enum('food','drink') DEFAULT 'food',
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_cart` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `food_id` int(10) UNSIGNED NOT NULL,
  `food_name` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `note` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cart_user` (`user_id`),
  KEY `idx_cart_food` (`food_id`),
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cart_food` FOREIGN KEY (`food_id`) REFERENCES `tbl_food` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_cart_quantity_positive` CHECK (`quantity` > 0),
  CONSTRAINT `chk_cart_price_non_negative` CHECK (`price` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_order` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_code` varchar(20) DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `food` varchar(150) NOT NULL,
  `order_details` text DEFAULT NULL COMMENT 'JSON snapshot',
  `price` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `order_date` datetime NOT NULL,
  `status` varchar(50) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_contact` varchar(20) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_address` varchar(255) NOT NULL,
  `to_district_id` int(10) UNSIGNED DEFAULT NULL,
  `to_ward_code` varchar(20) DEFAULT NULL,
  `order_weight_gram` int(10) UNSIGNED NOT NULL DEFAULT 500,
  `ghn_order_code` varchar(50) DEFAULT NULL,
  `ghn_sort_code` varchar(50) DEFAULT NULL,
  `ghn_status` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'cash',
  `payment_status` varchar(50) DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `payment_id` int(10) UNSIGNED DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_order_code` (`order_code`),
  KEY `idx_order_user` (`user_id`),
  KEY `idx_order_payment_status` (`payment_status`),
  KEY `idx_order_payment_method` (`payment_method`),
  KEY `idx_order_status_date` (`status`, `order_date`),
  KEY `idx_order_user_date` (`user_id`, `order_date`),
  KEY `idx_order_expires_at` (`expires_at`),
  CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_order_qty_positive` CHECK (`qty` > 0),
  CONSTRAINT `chk_order_total_non_negative` CHECK (`total` >= 0),
  CONSTRAINT `chk_order_shipping_non_negative` CHECK (`shipping_fee` >= 0),
  CONSTRAINT `chk_order_weight_positive` CHECK (`order_weight_gram` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_payment` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_code` varchar(20) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` varchar(50) NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_gateway_response` text DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `request_id` varchar(64) DEFAULT NULL,
  `order_id` varchar(64) DEFAULT NULL,
  `raw_response` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_payment_order_code` (`order_code`),
  KEY `idx_payment_user_id` (`user_id`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_payment_transaction_id` (`transaction_id`),
  KEY `idx_payment_order_status` (`order_code`, `payment_status`),
  KEY `idx_payment_expires_at` (`expires_at`),
  UNIQUE KEY `uniq_payment_request_id` (`request_id`),
  UNIQUE KEY `uniq_payment_gateway_order_id` (`order_id`),
  CONSTRAINT `fk_payment_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_order_code` FOREIGN KEY (`order_code`) REFERENCES `tbl_order` (`order_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_payment_amount_non_negative` CHECK (`amount` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tbl_order`
  ADD CONSTRAINT `fk_order_payment` FOREIGN KEY (`payment_id`) REFERENCES `tbl_payment` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE `tbl_order_notification` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_code` varchar(20) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order_notif_user` (`user_id`),
  KEY `idx_order_notif_user_read_created` (`user_id`, `is_read`, `created_at`),
  CONSTRAINT `fk_order_notif_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_order_notif_order` FOREIGN KEY (`order_code`) REFERENCES `tbl_order` (`order_code`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_refund` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_code` varchar(20) NOT NULL,
  `payment_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `refund_reason` text NOT NULL,
  `refund_status` varchar(30) NOT NULL DEFAULT 'pending',
  `refund_method` varchar(30) DEFAULT 'original',
  `refund_transaction_id` varchar(255) DEFAULT NULL,
  `processed_by` int(10) UNSIGNED DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_refund_order_code` (`order_code`),
  KEY `idx_refund_payment_id` (`payment_id`),
  KEY `idx_refund_user_id` (`user_id`),
  KEY `idx_refund_status` (`refund_status`),
  KEY `idx_refund_user_created` (`user_id`, `created_at`),
  KEY `idx_refund_status_created` (`refund_status`, `created_at`),
  KEY `idx_refund_admin` (`processed_by`),
  CONSTRAINT `fk_refund_payment` FOREIGN KEY (`payment_id`) REFERENCES `tbl_payment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_refund_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_refund_admin` FOREIGN KEY (`processed_by`) REFERENCES `tbl_admin` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_refund_amount_non_negative` CHECK (`refund_amount` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_chat` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `admin_id` int(10) UNSIGNED DEFAULT NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chat_admin` (`admin_id`),
  KEY `idx_chat_user` (`user_id`),
  KEY `idx_chat_user_created` (`user_id`, `created_at`),
  KEY `idx_chat_user_read_created` (`user_id`, `is_read`, `created_at`),
  CONSTRAINT `fk_chat_admin` FOREIGN KEY (`admin_id`) REFERENCES `tbl_admin` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_chat_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_verification` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `verification_code` varchar(10) NOT NULL,
  `verification_type` enum('email','phone') NOT NULL DEFAULT 'email',
  `expires_at` datetime NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `attempts` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_verification_email` (`email`),
  KEY `idx_verification_code` (`verification_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_voucher` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_order` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_voucher_code` (`code`),
  CONSTRAINT `chk_voucher_value_non_negative` CHECK (`value` >= 0),
  CONSTRAINT `chk_voucher_min_order_non_negative` CHECK (`min_order` >= 0),
  CONSTRAINT `chk_voucher_max_discount_non_negative` CHECK (`max_discount` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Seed data (copied/adapted from food-oder.sql)
-- ---------------------------------------------------------
INSERT INTO `tbl_admin` (`id`, `full_name`, `email`, `username`, `password`, `phone`, `created_at`) VALUES
(14, 'Administrator', 'admin@wowfood.com', 'admin', 'admin123', NULL, CURRENT_TIMESTAMP),
(15, 'Bùi Đức Duy', 'buiducduy095@gmail.com', 'ducduy', '123123123', NULL, CURRENT_TIMESTAMP);

INSERT INTO `tbl_user` (`id`, `full_name`, `username`, `password`, `email`, `phone`, `address`, `ghn_province_id`, `ghn_district_id`, `ghn_ward_code`, `status`, `created_at`) VALUES
(9, 'Bùi Đức Duy', 'buiducduy095', '$2y$10$70mDO8RLJbo3hXKSN1x.lOhF/Rd2qIHl6pdaK231Vqhhm4qrtDiaW', 'buiducduy095@gmail.com', '0983224809', 'Số nhà 27A', NULL, NULL, NULL, 'Active', '2026-02-02 13:16:58'),
(10, 'Nguyễn Thế Phong ', 'nguyenthephong18062004', '$2y$10$8wnh9OuaYu3crW0wYVo12OvKWbZILHS8UqYHcYtVdvYaeKawGu6km', 'nguyenthephong18062004@gmail.com', '0983224809', 'Cây xăng Lân Ngát', 231, 3243, '250903', 'Active', '2026-02-10 15:46:45'),
(11, 'Lê Văn Hiếu', 'lehieu210924', '$2y$10$Tb./cwJ0QSyDn71aDe8dTeJkgPawBJCg.0BhnImGvYqlrKy3mxZsu', 'lehieu210924@gmail.com', '0372953009', '', 226, 1869, '260802', 'Active', '2026-04-14 15:23:23');

INSERT INTO `tbl_category` (`id`, `title`, `featured`, `active`, `image_name`) VALUES
(29, 'Pizza', 'Yes', 'Yes', 'Food_Category_357.jpg'),
(30, 'Buger', 'Yes', 'Yes', 'Food_Category_381.jpg'),
(31, 'Momo', 'Yes', 'Yes', 'Food_Category_438.jpg'),
(36, 'Chicken ', 'Yes', 'Yes', 'Food_Category_678.jpg');

INSERT INTO `tbl_food` (`id`, `title`, `description`, `price`, `image_name`, `category_id`, `featured`, `active`, `quantity`) VALUES
(50, 'Pizaa', 'Pizaa phổ biến nhất thế giới', 12000.00, 'Food-name-1319.jpg', 29, 'Yes', 'Yes', 999),
(52, 'Burger ', 'Bánh kẹp thịt xay', 10000.00, 'Food-name-4266.jpg', 30, 'Yes', 'Yes', 0),
(54, 'Momo', 'Nguồn gốc từ Nepal', 11000.00, 'Food-name-6706.jpg', 31, 'Yes', 'Yes', 999),
(60, 'Cánh gà chiên ', '', 5000.00, 'Food-name-903.cms', 36, 'Yes', 'Yes', 999),
(62, 'Pizaa kiểu ý', 'đây là món ăn mà tôi rất thích', 500000.00, 'Food-name-316.jpg', 29, 'Yes', 'Yes', 999);

INSERT INTO `tbl_size` (`id`, `name`, `price_add`, `sort_order`) VALUES
(1, 'Nhỏ', 0.00, 1),
(2, 'Vừa', 5000.00, 2),
(3, 'Lớn', 10000.00, 3);

INSERT INTO `tbl_side_dish` (`id`, `name`, `price`, `type`, `sort_order`) VALUES
(1, 'Trứng ốp la', 8000.00, 'food', 1),
(2, 'Nem rán', 10000.00, 'food', 2),
(3, 'Khoai tây chiên', 12000.00, 'food', 3),
(4, 'Salad', 6000.00, 'food', 4),
(5, 'Nước ngọt', 5000.00, 'drink', 5),
(6, 'Trà đá', 3000.00, 'drink', 6),
(7, 'Cà phê', 8000.00, 'drink', 7);

INSERT INTO `tbl_cart` (`id`, `user_id`, `food_id`, `food_name`, `price`, `quantity`, `note`, `created_at`) VALUES
(15, 9, 50, 'Pizaa', 22000.00, 1, '', '2026-03-20 18:50:20'),
(20, 11, 52, 'Burger ', 10000.00, 4, '', '2026-04-21 21:50:36');

INSERT INTO `tbl_order` (`id`, `order_code`, `user_id`, `food`, `order_details`, `price`, `qty`, `total`, `shipping_fee`, `order_date`, `status`, `customer_name`, `customer_contact`, `customer_email`, `customer_address`, `to_district_id`, `to_ward_code`, `order_weight_gram`, `ghn_order_code`, `ghn_sort_code`, `ghn_status`, `payment_method`, `payment_status`, `note`, `payment_id`, `expires_at`) VALUES
(128, 'ORD202604185478DA', 11, 'Pizaa x2, Burger  x3', '[{\"title\":\"Pizaa\",\"qty\":2,\"size_name\":\"Nhỏ\",\"size_add\":0,\"sides\":[]},{\"title\":\"Burger \",\"qty\":3,\"size_name\":\"Nhỏ\",\"size_add\":0,\"sides\":[{\"name\":\"Trứng ốp la\",\"price\":8000},{\"name\":\"Nem rán\",\"price\":10000}]}]', 12000.00, 5, 157500.00, 49500.00, '2026-04-18 17:05:09', 'Delivered', 'Lê Văn Hiếu', '0372953009', 'lehieu210924@gmail.com', 'Tân Minh, Xã Mỹ Lộc, Huyện Thái Thụy, Thái Bình', 1869, '260802', 500, 'LHHH87', '0-000-0-00', 'created', 'cash', 'pending', NULL, NULL, NULL),
(129, 'ORD20260418C79A6B', 11, 'Pizaa kiểu ý x3', '[{\"title\":\"Pizaa kiểu ý\",\"qty\":3,\"size_name\":\"Vừa\",\"size_add\":5000,\"sides\":[]}]', 505000.00, 3, 1564500.00, 49500.00, '2026-04-18 17:07:56', 'Confirmed', 'Lê Văn Hiếu', '0372953009', 'lehieu210924@gmail.com', 'Tân Minh, Xã Mỹ Lộc, Huyện Thái Thụy, Thái Bình', 1869, '260802', 500, 'LHHH83', '0-000-0-00', 'created', 'cash', 'pending', NULL, NULL, NULL);

INSERT INTO `tbl_payment` (`id`, `order_code`, `user_id`, `payment_method`, `amount`, `payment_status`, `transaction_id`, `payment_gateway_response`, `failure_reason`, `paid_at`, `expires_at`, `created_at`, `updated_at`, `request_id`, `order_id`, `raw_response`) VALUES
(55, 'ORD202604185478DA', 11, 'vnpay', 157500.00, 'success', '15502507', NULL, NULL, NULL, NULL, '2026-04-18 17:05:11', '2026-04-18 17:07:00', NULL, NULL, '{\"vnp_Amount\":\"15750000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15502507\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD202604185478DA\",\"vnp_PayDate\":\"20260418170653\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"U1Q149D8\",\"vnp_TransactionNo\":\"15502507\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD202604185478DA\"}'),
(56, 'ORD20260418C79A6B', 11, 'vnpay', 1564500.00, 'success', '15502510', NULL, NULL, NULL, NULL, '2026-04-18 17:07:57', '2026-04-18 17:08:46', NULL, NULL, '{\"vnp_Amount\":\"156450000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15502510\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD20260418C79A6B\",\"vnp_PayDate\":\"20260418170841\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"U1Q149D8\",\"vnp_TransactionNo\":\"15502510\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD20260418C79A6B\"}');

INSERT INTO `tbl_order_notification` (`id`, `order_code`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(75, 'ORD20260418C79A6B', 11, 'Đơn ORD20260418C79A6B đã được xác nhận.', 0, '2026-04-20 20:55:52'),
(76, 'ORD20260418C79A6B', 11, 'Đơn ORD20260418C79A6B đã được xác nhận.', 0, '2026-04-20 20:55:58'),
(77, 'ORD202604185478DA', 11, 'Đơn ORD202604185478DA đã giao.', 0, '2026-04-20 20:56:05');

INSERT INTO `tbl_chat` (`id`, `user_id`, `admin_id`, `sender_type`, `message`, `is_read`, `created_at`) VALUES
(12, 11, 14, 'user', 'hello', 1, '2026-04-14 15:23:48'),
(13, 11, 14, 'admin', 'hi', 1, '2026-04-14 15:34:23'),
(14, 11, 14, 'user', 'tôi muốn đặt đồ ăn', 1, '2026-04-19 15:49:41'),
(15, 11, 14, 'user', 'hãy gợi ý cho tôi xem có món nào ngon', 1, '2026-04-19 16:04:33');

INSERT INTO `tbl_verification` (`id`, `email`, `phone`, `verification_code`, `verification_type`, `expires_at`, `is_verified`, `attempts`, `created_at`) VALUES
(1, 'buiducduy095@gmail.com', NULL, '366802', 'email', '2026-01-12 05:07:15', 1, 1, '2026-01-12 10:57:15'),
(8, 'lehieu210924@gmail.com', NULL, '087125', 'email', '2026-04-14 10:33:01', 1, 1, '2026-04-14 15:23:01');

INSERT INTO `tbl_voucher` (`id`, `code`, `type`, `value`, `min_order`, `max_discount`, `status`, `valid_from`, `valid_to`, `created_at`) VALUES
(1, 'WOWFOOD10', 'percent', 75.00, 0.00, 0.00, 'active', NULL, NULL, '2026-03-16 13:35:53'),
(2, 'WOWFOOD20K', 'fixed', 20000.00, 100000.00, 0.00, 'active', NULL, NULL, '2026-03-16 13:35:53'),
(3, 'WOWFOOD50', 'percent', 50.00, 300000.00, 100000.00, 'active', '2026-03-16 13:35:53', '2026-04-15 13:35:53', '2026-03-16 13:35:53');

INSERT INTO `tbl_refund` (`id`, `order_code`, `payment_id`, `user_id`, `refund_amount`, `refund_reason`, `refund_status`, `refund_method`, `refund_transaction_id`, `processed_by`, `processed_at`, `created_at`, `updated_at`) VALUES
(10, 'ORD202604185478DA', 55, 11, 157500.00, 'Hoàn tiền test', 'completed', 'original', '', 14, '2026-04-20 21:09:25', '2026-04-20 21:02:51', '2026-04-20 21:09:25');

SET FOREIGN_KEY_CHECKS = 1;


