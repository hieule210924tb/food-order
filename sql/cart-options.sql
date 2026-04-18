-- Bảng kích thước (Size)
CREATE TABLE IF NOT EXISTS `tbl_size` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `price_add` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tbl_size` (`id`, `name`, `price_add`, `sort_order`) VALUES
(1, 'Nhỏ', 0.00, 1),
(2, 'Vừa', 5.00, 2),
(3, 'Lớn', 10.00, 3);

-- Bảng món ăn/nước kèm theo
CREATE TABLE IF NOT EXISTS `tbl_side_dish` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `type` enum('food','drink') DEFAULT 'food',
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tbl_side_dish` (`id`, `name`, `price`, `type`, `sort_order`) VALUES
(1, 'Trứng ốp la', 8.00, 'food', 1),
(2, 'Nem rán', 10.00, 'food', 2),
(3, 'Khoai tây chiên', 12.00, 'food', 3),
(4, 'Salad', 6.00, 'food', 4),
(5, 'Nước ngọt', 5.00, 'drink', 5),
(6, 'Trà đá', 3.00, 'drink', 6),
(7, 'Cà phê', 8.00, 'drink', 7);
