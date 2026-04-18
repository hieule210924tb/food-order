-- Bảng thông báo đơn hàng cho user
CREATE TABLE IF NOT EXISTS `tbl_order_notification` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_code` varchar(20) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_code` (`order_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
