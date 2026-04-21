-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 21, 2026 lúc 02:56 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `food-oder`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_admin`
--

CREATE TABLE `tbl_admin` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_admin`
--

INSERT INTO `tbl_admin` (`id`, `full_name`, `email`, `username`, `password`, `Phone`) VALUES
(14, 'Administrator', 'admin@wowfood.com', 'admin', 'admin123', NULL),
(15, 'Bùi Đức Duy', 'buiducduy095@gmail.com', 'Đức Duy', '123123123', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_cart`
--

CREATE TABLE `tbl_cart` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `food_id` int(10) UNSIGNED NOT NULL,
  `food_name` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `note` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_cart`
--

INSERT INTO `tbl_cart` (`id`, `user_id`, `food_id`, `food_name`, `price`, `quantity`, `note`, `created_at`) VALUES
(15, 9, 50, 'Pizaa', 22000.00, 1, '', '2026-03-20 18:50:20'),
(20, 11, 52, 'Burger ', 10000.00, 4, '', '2026-04-21 21:50:36');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_category`
--

CREATE TABLE `tbl_category` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `featured` varchar(10) NOT NULL,
  `active` varchar(10) NOT NULL,
  `image_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_category`
--

INSERT INTO `tbl_category` (`id`, `title`, `featured`, `active`, `image_name`) VALUES
(29, 'Pizza', 'Yes', 'Yes', 'Food_Category_357.jpg'),
(30, 'Buger', 'Yes', 'Yes', 'Food_Category_381.jpg'),
(31, 'Momo', 'Yes', 'Yes', 'Food_Category_438.jpg'),
(36, 'Chicken ', 'Yes', 'Yes', 'Food_Category_678.jpg');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_chat`
--

CREATE TABLE `tbl_chat` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `admin_id` int(10) UNSIGNED DEFAULT NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_chat`
--

INSERT INTO `tbl_chat` (`id`, `user_id`, `admin_id`, `sender_type`, `message`, `is_read`, `created_at`) VALUES
(9, 9, NULL, 'user', 'alo', 1, '2026-03-16 14:01:48'),
(10, 9, 14, 'admin', 'gì', 1, '2026-03-16 14:02:15'),
(11, 9, 14, 'admin', 'ơi', 1, '2026-03-16 14:06:12'),
(12, 11, 14, 'user', 'hello', 1, '2026-04-14 15:23:48'),
(13, 11, 14, 'admin', 'hi', 1, '2026-04-14 15:34:23'),
(14, 11, 14, 'user', 'tôi muốn đặt đồ ăn', 1, '2026-04-19 15:49:41'),
(15, 11, 14, 'user', 'hãy gợi ý cho tôi xem có món nào ngon', 1, '2026-04-19 16:04:33'),
(16, 11, 14, 'user', 'có món nào không', 1, '2026-04-19 16:15:15'),
(17, 11, 14, 'user', 'gợi ý món', 0, '2026-04-21 21:00:42'),
(18, 11, 14, 'user', 'xin chào', 0, '2026-04-21 21:00:49'),
(19, 11, 14, 'user', '.', 0, '2026-04-21 21:00:51'),
(20, 11, 14, 'user', 'f', 0, '2026-04-21 21:00:53'),
(21, 11, 14, 'user', 'f', 0, '2026-04-21 21:00:54'),
(22, 11, 14, 'user', 'f', 0, '2026-04-21 21:00:54'),
(23, 11, 14, 'user', 'k', 0, '2026-04-21 21:06:09'),
(24, 11, 14, 'user', 'k', 0, '2026-04-21 21:06:09'),
(25, 11, 14, 'user', 'j', 0, '2026-04-21 21:06:10'),
(26, 11, 14, 'user', 'j', 0, '2026-04-21 21:06:11'),
(27, 11, 14, 'user', 'j', 0, '2026-04-21 21:06:11'),
(28, 11, 14, 'user', 'j', 0, '2026-04-21 21:06:12'),
(29, 11, 14, 'user', 'xin chào', 0, '2026-04-21 21:37:41');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_food`
--

CREATE TABLE `tbl_food` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` varchar(255) NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `image_name` varchar(255) NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `featured` varchar(10) NOT NULL,
  `active` varchar(10) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 999
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_food`
--

INSERT INTO `tbl_food` (`id`, `title`, `description`, `price`, `image_name`, `category_id`, `featured`, `active`, `quantity`) VALUES
(50, 'Pizaa', 'Pizaa phổ biến nhất thế giới', 12000, 'Food-name-1319.jpg', 29, 'Yes', 'Yes', 999),
(52, 'Burger ', 'Bánh kẹp thịt xay', 10000, 'Food-name-4266.jpg', 30, 'Yes', 'Yes', 0),
(54, 'Momo', 'Nguồn gốc từ Nepal', 11000, 'Food-name-6706.jpg', 31, 'Yes', 'Yes', 999),
(60, 'Cánh gà chiên ', '', 5000, 'Food-name-903.cms', 36, 'Yes', 'Yes', 999),
(62, 'Pizaa kiểu ý', 'đây là món ăn mà tôi rất thích', 500000, 'Food-name-316.jpg', 29, 'Yes', 'Yes', 999);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_order`
--

CREATE TABLE `tbl_order` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_code` varchar(20) DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `food` varchar(150) NOT NULL,
  `order_details` text DEFAULT NULL COMMENT 'Chi tiết đơn hàng (món, size, món kèm + giá)',
  `price` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Phí ship GHN',
  `order_date` datetime NOT NULL,
  `status` varchar(50) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_contact` varchar(20) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_address` varchar(255) NOT NULL,
  `to_district_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID quận/huyện GHN',
  `to_ward_code` varchar(20) DEFAULT NULL COMMENT 'Mã phường/xã GHN',
  `order_weight_gram` int(10) UNSIGNED NOT NULL DEFAULT 500 COMMENT 'Khối lượng đơn (gram)',
  `ghn_order_code` varchar(50) DEFAULT NULL COMMENT 'Mã vận đơn GHN',
  `ghn_sort_code` varchar(50) DEFAULT NULL COMMENT 'Mã sắp xếp GHN',
  `ghn_status` varchar(50) DEFAULT NULL COMMENT 'Trạng thái giao hàng GHN',
  `payment_method` varchar(50) DEFAULT 'cash' COMMENT 'cash, online, vnpay, momo, bank',
  `payment_status` varchar(50) DEFAULT 'pending' COMMENT 'pending, paid, failed, refunded',
  `note` text DEFAULT NULL,
  `payment_id` int(10) UNSIGNED DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL COMMENT 'Thời gian hết hạn thanh toán'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_order`
--

INSERT INTO `tbl_order` (`id`, `order_code`, `user_id`, `food`, `order_details`, `price`, `qty`, `total`, `shipping_fee`, `order_date`, `status`, `customer_name`, `customer_contact`, `customer_email`, `customer_address`, `to_district_id`, `to_ward_code`, `order_weight_gram`, `ghn_order_code`, `ghn_sort_code`, `ghn_status`, `payment_method`, `payment_status`, `note`, `payment_id`, `expires_at`) VALUES
(122, 'ORD2026032025A801', 9, 'Cánh gà chiên  x1', '[{\"title\":\"Cánh gà chiên \",\"qty\":1,\"size_name\":\"Lớn\",\"size_add\":10000,\"sides\":[]}]', 15000.00, 1, 75500.00, 60500.00, '2026-03-20 17:49:38', 'Ordered', 'Bùi Đức Duy', '0983224809', 'buiducduy095@gmail.com', 'Số nhà 27A, Xã Tả Phìn, Thị xã Sa Pa, Lào Cai', 2005, '80514', 500, 'LTWHKT', '000-C-00-00', 'created', 'cash', 'pending', NULL, NULL, NULL),
(123, 'ORD2026032036C476', 9, 'Burger  x1', '[{\"title\":\"Burger \",\"qty\":1,\"size_name\":\"Nhỏ\",\"size_add\":0,\"sides\":[]}]', 10000.00, 1, 92500.00, 82500.00, '2026-03-20 17:51:47', 'Pending Payment', 'Bùi Đức Duy', '0983224809', 'buiducduy095@gmail.com', 'Số nhà 27A, Xã Phong Thạnh Tây B, Huyện Phước Long, Bạc Liêu', 1998, '600504', 500, 'LTWHKH', '000-H-00-00', 'created', 'cash', 'pending', NULL, NULL, NULL),
(124, 'ORD202603204AED63', 9, 'Cánh gà chiên  x1', '[{\"title\":\"Cánh gà chiên \",\"qty\":1,\"size_name\":\"Nhỏ\",\"size_add\":0,\"sides\":[]}]', 5000.00, 1, 4500.00, 0.00, '2026-03-20 18:21:40', 'Pending Payment', 'Bùi Đức Duy', '0983224809', 'buiducduy095@gmail.com', 'Số nhà 27A, -- Chọn Phường/Xã --, -- Chọn Quận/Huyện --, -- Chọn Tỉnh/TP --', NULL, NULL, 500, NULL, NULL, NULL, 'cash', 'pending', NULL, NULL, NULL),
(125, 'ORD202603209D2969', 9, 'Cánh gà chiên  x1', '[{\"title\":\"Cánh gà chiên \",\"qty\":1,\"size_name\":\"Nhỏ\",\"size_add\":0,\"sides\":[]}]', 5000.00, 1, 87000.00, 82500.00, '2026-03-20 18:22:18', 'Pending Payment', 'Bùi Đức Duy', '0983224809', 'buiducduy095@gmail.com', 'Số nhà 27A, Thị Trấn Ngã Sáu, Huyện Châu Thành, Hậu Giang', 2096, '64052', 500, 'LTWXFH', 'F-000-H-00-00', 'created', 'cash', 'pending', NULL, NULL, NULL),
(126, 'ORD202603207334DA', 9, 'Cánh gà chiên  x1', '[{\"title\":\"Cánh gà chiên \",\"qty\":1,\"size_name\":\"Nhỏ\",\"size_add\":0,\"sides\":[]}]', 5000.00, 1, 87000.00, 82500.00, '2026-03-20 18:24:39', 'Pending Payment', 'Bùi Đức Duy', '0983224809', 'buiducduy095@gmail.com', 'Số nhà 27A, Xã Khánh Thuận, Huyện U Minh, Cà Mau', 2042, '610306', 500, 'LTWXFX', 'F-000-W-00-00', 'created', 'cash', 'pending', NULL, NULL, NULL),
(127, 'ORD202604146CAA68', 11, 'Pizaa x2', '[{\"title\":\"Pizaa\",\"qty\":2,\"size_name\":\"Vừa\",\"size_add\":5000,\"sides\":[{\"name\":\"Trà đá\",\"price\":3000},{\"name\":\"Cà phê\",\"price\":8000}]}]', 28000.00, 2, 105500.00, 49500.00, '2026-04-14 15:25:43', 'Confirmed', 'Lê Văn Hiếu', '0372953009', 'lehieu210924@gmail.com', 'Tân Minh, Xã Mỹ Lộc, Huyện Thái Thụy, Thái Bình', 1869, '260802', 500, 'LHUY69', '0-000-0-00', 'created', 'cash', 'pending', NULL, NULL, NULL),
(128, 'ORD202604185478DA', 11, 'Pizaa x2, Burger  x3', '[{\"title\":\"Pizaa\",\"qty\":2,\"size_name\":\"Nhỏ\",\"size_add\":0,\"sides\":[]},{\"title\":\"Burger \",\"qty\":3,\"size_name\":\"Nhỏ\",\"size_add\":0,\"sides\":[{\"name\":\"Trứng ốp la\",\"price\":8000},{\"name\":\"Nem rán\",\"price\":10000}]}]', 12000.00, 5, 157500.00, 49500.00, '2026-04-18 17:05:09', 'Delivered', 'Lê Văn Hiếu', '0372953009', 'lehieu210924@gmail.com', 'Tân Minh, Xã Mỹ Lộc, Huyện Thái Thụy, Thái Bình', 1869, '260802', 500, 'LHHH87', '0-000-0-00', 'created', 'cash', 'pending', NULL, NULL, NULL),
(129, 'ORD20260418C79A6B', 11, 'Pizaa kiểu ý x3', '[{\"title\":\"Pizaa kiểu ý\",\"qty\":3,\"size_name\":\"Vừa\",\"size_add\":5000,\"sides\":[]}]', 505000.00, 3, 1564500.00, 49500.00, '2026-04-18 17:07:56', 'Confirmed', 'Lê Văn Hiếu', '0372953009', 'lehieu210924@gmail.com', 'Tân Minh, Xã Mỹ Lộc, Huyện Thái Thụy, Thái Bình', 1869, '260802', 500, 'LHHH83', '0-000-0-00', 'created', 'cash', 'pending', NULL, NULL, NULL),
(130, 'ORD20260418F6A8AB', 11, 'Burger  x2', '[{\"title\":\"Burger \",\"qty\":2,\"size_name\":\"Nhỏ\",\"size_add\":0,\"sides\":[]}]', 10000.00, 2, 54500.00, 49500.00, '2026-04-18 17:09:51', 'Confirmed', 'Lê Văn Hiếu', '0372953009', 'lehieu210924@gmail.com', 'Tân Minh, Xã Mỹ Lộc, Huyện Thái Thụy, Thái Bình', 1869, '260802', 500, 'LHHHRQ', '0-000-0-00', 'created', 'cash', 'pending', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_order_notification`
--

CREATE TABLE `tbl_order_notification` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_code` varchar(20) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_order_notification`
--

INSERT INTO `tbl_order_notification` (`id`, `order_code`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(75, 'ORD20260418F6A8AB', 11, 'Đơn ORD20260418F6A8AB đã được xác nhận.', 0, '2026-04-20 20:55:52'),
(76, 'ORD20260418C79A6B', 11, 'Đơn ORD20260418C79A6B đã được xác nhận.', 0, '2026-04-20 20:55:58'),
(77, 'ORD202604185478DA', 11, 'Đơn ORD202604185478DA đã giao.', 0, '2026-04-20 20:56:05');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_payment`
--

CREATE TABLE `tbl_payment` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_code` varchar(20) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `payment_method` varchar(50) NOT NULL COMMENT 'vnpay, momo, bank, cash',
  `amount` decimal(10,2) NOT NULL,
  `payment_status` varchar(50) NOT NULL DEFAULT 'pending' COMMENT 'pending, success, failed, cancelled, refunded',
  `transaction_id` varchar(255) DEFAULT NULL COMMENT 'Mã giao dịch từ cổng thanh toán',
  `payment_gateway_response` text DEFAULT NULL COMMENT 'Response từ cổng thanh toán',
  `failure_reason` text DEFAULT NULL COMMENT 'Lý do thất bại',
  `paid_at` datetime DEFAULT NULL COMMENT 'Thời gian thanh toán thành công',
  `expires_at` datetime DEFAULT NULL COMMENT 'Thời gian hết hạn thanh toán',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `request_id` varchar(64) DEFAULT NULL,
  `order_id` varchar(64) DEFAULT NULL,
  `raw_response` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_payment`
--

INSERT INTO `tbl_payment` (`id`, `order_code`, `user_id`, `payment_method`, `amount`, `payment_status`, `transaction_id`, `payment_gateway_response`, `failure_reason`, `paid_at`, `expires_at`, `created_at`, `updated_at`, `request_id`, `order_id`, `raw_response`) VALUES
(6, 'ORD2026020439824F', 9, 'momo', 48.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-02-04 14:19:48', '2026-02-04 14:20:17', 'ORD2026020439824F-1770189617-220', 'ORD2026020439824F-1770189617-220', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD2026020439824F-1770189617-220\",\"requestId\":\"ORD2026020439824F-1770189617-220\",\"amount\":48000,\"responseTime\":1770189609653,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNDM5ODI0Ri0xNzcwMTg5NjE3LTIyMA&s=1788f3325ad11ea7d95944ffa8bcfc8ccac892569a99d4612eff62eb1e7ebb90\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNDM5ODI0Ri0xNzcwMTg5NjE3LTIyMA&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNDM5ODI0Ri0xNzcwMTg5NjE3LTIyMA&v=3.0&sr=0&sig=0d79942cbb9d3c1848d5630c53d847123cf50e9f283d13efa141c4e7\"}'),
(7, 'ORD2026020440C0C2', 9, 'momo', 48.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-02-04 14:20:36', '2026-02-04 14:21:05', 'ORD2026020440C0C2-1770189664-734', 'ORD2026020440C0C2-1770189664-734', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD2026020440C0C2-1770189664-734\",\"requestId\":\"ORD2026020440C0C2-1770189664-734\",\"amount\":48000,\"responseTime\":1770189657294,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNDQwQzBDMi0xNzcwMTg5NjY0LTczNA&s=a92e54367a4a9b3a9155c32b6068a15d57ba092b3701664cebda514ed8182f50\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNDQwQzBDMi0xNzcwMTg5NjY0LTczNA&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNDQwQzBDMi0xNzcwMTg5NjY0LTczNA&v=3.0&sr=0&sig=e4a868cc4105032db9f468f9e15227d0fc31fcaa8ceb56ea14423f50\"}'),
(8, 'ORD202602044C05C2', 9, 'momo', 11.00, 'failed', '4664244405', NULL, NULL, NULL, NULL, '2026-02-04 14:23:49', '2026-02-04 14:24:13', 'ORD202602044C05C2-1770189828-364', 'ORD202602044C05C2-1770189828-364', '{\"order_code\":\"ORD202602044C05C2\",\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD202602044C05C2-1770189828-364\",\"requestId\":\"ORD202602044C05C2-1770189828-364\",\"amount\":\"11000\",\"orderInfo\":\"Thanh toan don hang ORD202602044C05C2\",\"orderType\":\"momo_wallet\",\"transId\":\"4664244405\",\"resultCode\":\"99\",\"message\":\"Lỗi không xác định. Vui lòng liên hệ MoMo để biết thêm chi tiết.\",\"payType\":\"qr\",\"responseTime\":\"1770189842523\",\"extraData\":\"\",\"signature\":\"241abaaa6daed34f7ac3e5704da8f9c42d266c07f57d839888cc6759c52e5c24\"}'),
(9, 'ORD2026020431652E', 9, 'momo', 11.00, 'failed', '4664260504', NULL, NULL, NULL, NULL, '2026-02-04 14:29:23', '2026-02-04 14:29:47', 'ORD2026020431652E-1770190163-867', 'ORD2026020431652E-1770190163-867', '{\"order_code\":\"ORD2026020431652E\",\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD2026020431652E-1770190163-867\",\"requestId\":\"ORD2026020431652E-1770190163-867\",\"amount\":\"11000\",\"orderInfo\":\"Thanh toan don hang ORD2026020431652E\",\"orderType\":\"momo_wallet\",\"transId\":\"4664260504\",\"resultCode\":\"99\",\"message\":\"Lỗi không xác định. Vui lòng liên hệ MoMo để biết thêm chi tiết.\",\"payType\":\"qr\",\"responseTime\":\"1770190176683\",\"extraData\":\"\",\"signature\":\"382693e2c19953937a0c0c2106095e97894d6b122b45cfef4fa98a20471950a4\"}'),
(10, 'ORD20260204C04CB4', 9, 'momo', 10.00, 'failed', '4664254038', NULL, NULL, NULL, NULL, '2026-02-04 14:31:56', '2026-02-04 14:32:12', 'ORD20260204C04CB4-1770190316-147', 'ORD20260204C04CB4-1770190316-147', '{\"order_code\":\"ORD20260204C04CB4\",\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD20260204C04CB4-1770190316-147\",\"requestId\":\"ORD20260204C04CB4-1770190316-147\",\"amount\":\"10000\",\"orderInfo\":\"Thanh toan don hang ORD20260204C04CB4\",\"orderType\":\"momo_wallet\",\"transId\":\"4664254038\",\"resultCode\":\"99\",\"message\":\"Lỗi không xác định. Vui lòng liên hệ MoMo để biết thêm chi tiết.\",\"payType\":\"qr\",\"responseTime\":\"1770190321560\",\"extraData\":\"\",\"signature\":\"17d3332a0ca21b3480f78038cda2571b3456d5a7c0986fcdaf77ab986fb27a68\"}'),
(11, 'ORD202602043B3EFD', 9, 'momo', 64000.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-02-04 14:47:48', '2026-02-04 14:47:48', 'ORD202602043B3EFD-1770191267-196', 'ORD202602043B3EFD-1770191267-196', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD202602043B3EFD-1770191267-196\",\"requestId\":\"ORD202602043B3EFD-1770191267-196\",\"amount\":64000,\"responseTime\":1770191260225,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNDNCM0VGRC0xNzcwMTkxMjY3LTE5Ng&s=00c6c0a64bac3f097536d6b6a3894511869b438e49e0a796e567989f63c0e760\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNDNCM0VGRC0xNzcwMTkxMjY3LTE5Ng&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNDNCM0VGRC0xNzcwMTkxMjY3LTE5Ng&v=3.0&sr=0&sig=e32b6566be9b6a7f782b4e42d9bae4a3697552ba6d5ef8f3e2d1d9dd\"}'),
(12, 'ORD20260204604D8C', 9, 'momo', 72000.00, 'failed', '4664422904', NULL, NULL, NULL, NULL, '2026-02-04 14:55:50', '2026-02-04 14:57:01', 'ORD20260204604D8C-1770191750-343', 'ORD20260204604D8C-1770191750-343', '{\"order_code\":\"ORD20260204604D8C\",\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD20260204604D8C-1770191750-343\",\"requestId\":\"ORD20260204604D8C-1770191750-343\",\"amount\":\"72000\",\"orderInfo\":\"Thanh toan don hang ORD20260204604D8C\",\"orderType\":\"momo_wallet\",\"transId\":\"4664422904\",\"resultCode\":\"99\",\"message\":\"Lỗi không xác định. Vui lòng liên hệ MoMo để biết thêm chi tiết.\",\"payType\":\"qr\",\"responseTime\":\"1770191810282\",\"extraData\":\"\",\"signature\":\"39321c08cff7e0739640c9eefeac90fe279baad2600f261a3edf30e007ba54e1\"}'),
(13, 'ORD2026020484B939', 9, 'momo', 51000.00, 'failed', '4664536031', NULL, NULL, NULL, NULL, '2026-02-04 15:28:56', '2026-02-04 15:30:28', 'ORD2026020484B939-1770193736-388', 'ORD2026020484B939-1770193736-388', '{\"order_code\":\"ORD2026020484B939\",\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD2026020484B939-1770193736-388\",\"requestId\":\"ORD2026020484B939-1770193736-388\",\"amount\":\"51000\",\"orderInfo\":\"Thanh toan don hang ORD2026020484B939\",\"orderType\":\"momo_wallet\",\"transId\":\"4664536031\",\"resultCode\":\"99\",\"message\":\"Lỗi không xác định. Vui lòng liên hệ MoMo để biết thêm chi tiết.\",\"payType\":\"qr\",\"responseTime\":\"1770193775476\",\"extraData\":\"\",\"signature\":\"b92885fb9f4b338ba1daf1c99e56431e580f6edbfc0321ccd72ba46548f3833a\"}'),
(14, 'ORD20260204AE0D5E', 9, 'momo', 72000.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-02-04 17:11:23', '2026-02-04 17:11:23', 'ORD20260204AE0D5E-1770199883-490', 'ORD20260204AE0D5E-1770199883-490', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD20260204AE0D5E-1770199883-490\",\"requestId\":\"ORD20260204AE0D5E-1770199883-490\",\"amount\":72000,\"responseTime\":1770199875415,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNEFFMEQ1RS0xNzcwMTk5ODgzLTQ5MA&s=fbc7df7d32b3ebbd417a5c0087d096b5fdc43c471184771ba3e85a6598f7e2f6\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNEFFMEQ1RS0xNzcwMTk5ODgzLTQ5MA&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNEFFMEQ1RS0xNzcwMTk5ODgzLTQ5MA&v=3.0&sr=0&sig=3824cc2d6ad68844b134a3c38b835ac7a944ced6bcd9bdef24737a49\"}'),
(15, 'ORD2026020507CD67', 9, 'momo', 38000.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-02-05 09:41:53', '2026-02-05 09:41:53', 'ORD2026020507CD67-1770259312-328', 'ORD2026020507CD67-1770259312-328', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD2026020507CD67-1770259312-328\",\"requestId\":\"ORD2026020507CD67-1770259312-328\",\"amount\":38000,\"responseTime\":1770259305492,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNTA3Q0Q2Ny0xNzcwMjU5MzEyLTMyOA&s=bd679e8ccfef307086cd498720b4d67c08b8bba5a244363ae4e5681c70c2e8d1\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNTA3Q0Q2Ny0xNzcwMjU5MzEyLTMyOA&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNTA3Q0Q2Ny0xNzcwMjU5MzEyLTMyOA&v=3.0&sr=0&sig=233795e90be23373b42457113325eaeb53bd709eb5c65474d971fdfa\"}'),
(16, 'ORD20260205F0A003', 9, 'momo', 50000.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-02-05 09:54:07', '2026-02-05 09:54:07', 'ORD20260205F0A003-1770260047-932', 'ORD20260205F0A003-1770260047-932', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD20260205F0A003-1770260047-932\",\"requestId\":\"ORD20260205F0A003-1770260047-932\",\"amount\":50000,\"responseTime\":1770260039984,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNUYwQTAwMy0xNzcwMjYwMDQ3LTkzMg&s=05c498887fc43d3ffdda36ec927d3d81eb2716719646254c06da086b4bdc170e\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNUYwQTAwMy0xNzcwMjYwMDQ3LTkzMg&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNUYwQTAwMy0xNzcwMjYwMDQ3LTkzMg&v=3.0&sr=0&sig=4282717e9b922e253063f21ef5f3f011a97d7bdc0620a43070d3cc6e\"}'),
(17, 'ORD20260205CA7F9D', 9, 'momo', 142000.00, 'failed', '4666300804', NULL, NULL, NULL, NULL, '2026-02-05 11:32:29', '2026-02-05 11:32:50', 'ORD20260205CA7F9D-1770265948-653', 'ORD20260205CA7F9D-1770265948-653', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD20260205CA7F9D-1770265948-653\",\"requestId\":\"ORD20260205CA7F9D-1770265948-653\",\"amount\":142000,\"orderInfo\":\"Thanh toan don hang ORD20260205CA7F9D\",\"orderType\":\"momo_wallet\",\"transId\":4666300804,\"resultCode\":99,\"message\":\"Lỗi không xác định. Vui lòng liên hệ MoMo để biết thêm chi tiết.\",\"payType\":\"webApp\",\"responseTime\":1770265962715,\"extraData\":\"\",\"signature\":\"32f8f6a49e0c183956a374d0eb2b63efe9238b3215f4ffca0bab05d7b78b1b9f\"}'),
(18, 'ORD202602052BAD61', 9, 'momo', 222000.00, 'failed', '4666573266', NULL, NULL, NULL, NULL, '2026-02-05 13:57:23', '2026-02-05 13:57:45', 'ORD202602052BAD61-1770274643-829', 'ORD202602052BAD61-1770274643-829', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD202602052BAD61-1770274643-829\",\"requestId\":\"ORD202602052BAD61-1770274643-829\",\"amount\":222000,\"orderInfo\":\"Thanh toan don hang ORD202602052BAD61\",\"orderType\":\"momo_wallet\",\"transId\":4666573266,\"resultCode\":99,\"message\":\"Lỗi không xác định. Vui lòng liên hệ MoMo để biết thêm chi tiết.\",\"payType\":\"webApp\",\"responseTime\":1770274656671,\"extraData\":\"\",\"signature\":\"59a319c125f81e70662016504dd2a019ad48b1b4b7b60bb527335934a0c2dccf\"}'),
(19, 'ORD202602055644FD', 9, 'vnpay', 20000.00, 'pending', NULL, NULL, NULL, NULL, '2026-02-05 08:33:30', '2026-02-05 14:18:30', '2026-02-05 14:18:30', NULL, NULL, NULL),
(20, 'ORD20260205902223', 9, 'vnpay', 45000.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-02-05 15:38:49', '2026-02-05 15:38:49', NULL, NULL, '{\"payUrl\":\"https:\\/\\/sandbox.vnpayment.vn\\/paymentv2\\/vpcpay.html?vnp_Amount=4500000&vnp_Command=pay&vnp_CreateDate=20260205153849&vnp_CurrCode=VND&vnp_ExpireDate=20260205155349&vnp_IpAddr=%3A%3A1&vnp_IpnUrl=http%3A%2F%2Flocalhost%2Fwow-food%2Fapi%2Fvnpay-ipn.php&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+ORD20260205902223&vnp_OrderType=other&vnp_ReturnUrl=http%3A%2F%2Flocalhost%2Fwow-food%2Fuser%2Fvnpay-return.php&vnp_TmnCode=6SWCSTD3&vnp_TxnRef=ORD20260205902223&vnp_Version=2.1.0&vnp_SecureHash=43fb75371699d25492ac11236b4ff1af01af6ba6ac2979e66b5eb16e6eb01fd0e36f5ad2f6c611e1f24467e1f73f1f1b32ce8fe0ca2bb2ca9acf2006761763a9\",\"created\":\"20260205153849\"}'),
(21, 'ORD20260205A1EC2F', 9, 'vnpay', 45000.00, 'success', '15422406', NULL, NULL, NULL, NULL, '2026-02-05 15:42:18', '2026-02-05 15:43:18', NULL, NULL, '{\"vnp_Amount\":\"4500000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15422406\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD20260205A1EC2F\",\"vnp_PayDate\":\"20260205154253\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"6SWCSTD3\",\"vnp_TransactionNo\":\"15422406\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD20260205A1EC2F\"}'),
(22, 'ORD202602056B8749', 9, 'vnpay', 5000.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-02-05 15:44:22', '2026-02-05 15:44:22', NULL, NULL, '{\"payUrl\":\"https:\\/\\/sandbox.vnpayment.vn\\/paymentv2\\/vpcpay.html?vnp_Amount=500000&vnp_Command=pay&vnp_CreateDate=20260205154422&vnp_CurrCode=VND&vnp_ExpireDate=20260205155922&vnp_IpAddr=%3A%3A1&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+ORD202602056B8749&vnp_OrderType=other&vnp_ReturnUrl=http%3A%2F%2Flocalhost%2Fwow-food%2Fuser%2Fvnpay-return.php&vnp_TmnCode=6SWCSTD3&vnp_TxnRef=ORD202602056B8749&vnp_Version=2.1.0&vnp_SecureHash=9d396685ed262344d6e032c95ca9bf5811cb8ea4e676349e171a858ad9bc62ff424f2299e8c2aa33eb787db76db887b2f0c6ba59aa384616d29ff3ae1ac68e96\",\"created\":\"20260205154422\"}'),
(23, 'ORD20260205984EF7', 9, 'vnpay', 50000.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-02-05 15:55:21', '2026-02-05 15:55:21', NULL, NULL, '{\"payUrl\":\"https:\\/\\/sandbox.vnpayment.vn\\/paymentv2\\/vpcpay.html?vnp_Amount=5000000&vnp_Command=pay&vnp_CreateDate=20260205155521&vnp_CurrCode=VND&vnp_ExpireDate=20260205161021&vnp_IpAddr=%3A%3A1&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+ORD20260205984EF7&vnp_OrderType=other&vnp_ReturnUrl=https%3A%2F%2Faliyah-evaporative-interrogatively.ngrok-free.dev%2Fwow-food%2Fuser%2Fvnpay-return.php&vnp_TmnCode=6SWCSTD3&vnp_TxnRef=ORD20260205984EF7&vnp_Version=2.1.0&vnp_SecureHash=38e3460648062752252a4e471641e1ef244a8d39d835d8f819a78cc59ca6165d8fdc3ccb564c5b4100034d415f68f0972fa4c492c460e86901724b10dc071bbb\",\"created\":\"20260205155521\"}'),
(24, 'ORD20260205DB5AB4', 9, 'vnpay', 50000.00, 'success', '15422428', NULL, NULL, NULL, NULL, '2026-02-05 15:55:41', '2026-02-05 15:57:46', NULL, NULL, '{\"vnp_Amount\":\"5000000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15422428\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD20260205DB5AB4\",\"vnp_PayDate\":\"20260205155730\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"6SWCSTD3\",\"vnp_TransactionNo\":\"15422428\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD20260205DB5AB4\"}'),
(25, 'ORD20260205E56430', 9, 'vnpay', 43000.00, 'success', '15422430', NULL, NULL, NULL, NULL, '2026-02-05 15:57:34', '2026-02-05 15:58:31', NULL, NULL, '{\"vnp_Amount\":\"4300000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15422430\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD20260205E56430\",\"vnp_PayDate\":\"20260205155815\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"6SWCSTD3\",\"vnp_TransactionNo\":\"15422430\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD20260205E56430\"}'),
(26, 'ORD20260205081BA0', 9, 'vnpay', 46000.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-02-05 17:32:00', '2026-02-05 17:32:00', NULL, NULL, '{\"payUrl\":\"https:\\/\\/sandbox.vnpayment.vn\\/paymentv2\\/vpcpay.html?vnp_Amount=4600000&vnp_Command=pay&vnp_CreateDate=20260205173200&vnp_CurrCode=VND&vnp_ExpireDate=20260205174700&vnp_IpAddr=%3A%3A1&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+ORD20260205081BA0&vnp_OrderType=other&vnp_ReturnUrl=http%3A%2F%2Flocalhost%2Fwow-food%2Fuser%2Fvnpay-return.php&vnp_TmnCode=6SWCSTD3&vnp_TxnRef=ORD20260205081BA0&vnp_Version=2.1.0&vnp_SecureHash=da0f8e6d536e597dc71c14768b5b69ce20a8b8494b9f4b9bafa089072206a8382782149adbb0b9c885a1f69b5214b8ccf2a61bc83451cae95c2b8a6b9cd90fc0\",\"created\":\"20260205173200\"}'),
(27, 'ORD20260206BB99CF', 9, 'momo', 54000.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-02-06 12:50:52', '2026-02-06 12:50:52', 'ORD20260206BB99CF-1770357052-876', 'ORD20260206BB99CF-1770357052-876', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD20260206BB99CF-1770357052-876\",\"requestId\":\"ORD20260206BB99CF-1770357052-876\",\"amount\":54000,\"responseTime\":1770357044110,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNkJCOTlDRi0xNzcwMzU3MDUyLTg3Ng&s=16e9d592aa911426e1766f6ffc4589183ab4d79dd7b9a3c3a74396b7617eed85\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNkJCOTlDRi0xNzcwMzU3MDUyLTg3Ng&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDIwNkJCOTlDRi0xNzcwMzU3MDUyLTg3Ng&v=3.0&sr=0&sig=3efd49a867076a85fc6c8a6c34671e55a6357fff95e16ecf9e2643ef\"}'),
(28, 'ORD20260206B945C2', 9, 'vnpay', 54000.00, 'success', '15423176', NULL, NULL, NULL, NULL, '2026-02-06 12:51:23', '2026-02-06 12:52:29', NULL, NULL, '{\"vnp_Amount\":\"5400000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15423176\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD20260206B945C2\",\"vnp_PayDate\":\"20260206125211\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"6SWCSTD3\",\"vnp_TransactionNo\":\"15423176\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD20260206B945C2\"}'),
(29, 'ORD20260210AA49AE', 9, 'vnpay', 72000.00, 'success', '15425537', NULL, NULL, NULL, NULL, '2026-02-10 10:40:59', '2026-02-10 10:41:56', NULL, NULL, '{\"vnp_Amount\":\"7200000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15425537\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD20260210AA49AE\",\"vnp_PayDate\":\"20260210104141\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"6SWCSTD3\",\"vnp_TransactionNo\":\"15425537\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD20260210AA49AE\"}'),
(30, 'ORD202602104B0488', 9, 'vnpay', 67000.00, 'success', '15425572', NULL, NULL, NULL, NULL, '2026-02-10 11:04:54', '2026-02-10 11:05:31', NULL, NULL, '{\"vnp_Amount\":\"6700000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15425572\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD202602104B0488\",\"vnp_PayDate\":\"20260210110505\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"6SWCSTD3\",\"vnp_TransactionNo\":\"15425572\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD202602104B0488\"}'),
(31, 'ORD20260210B01583', 9, 'vnpay', 124900.00, 'success', '15425637', NULL, NULL, NULL, NULL, '2026-02-10 12:00:12', '2026-02-10 12:00:59', NULL, NULL, '{\"vnp_Amount\":\"12490000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15425637\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD20260210B01583\",\"vnp_PayDate\":\"20260210120044\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"6SWCSTD3\",\"vnp_TransactionNo\":\"15425637\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD20260210B01583\"}'),
(32, 'ORD2026021090B266', 9, 'vnpay', 97000.00, 'success', '15425819', NULL, NULL, NULL, NULL, '2026-02-10 15:24:42', '2026-02-10 15:26:43', NULL, NULL, '{\"vnp_Amount\":\"9700000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15425819\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD2026021090B266\",\"vnp_PayDate\":\"20260210152527\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"6SWCSTD3\",\"vnp_TransactionNo\":\"15425819\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD2026021090B266\"}'),
(33, 'ORD202603135223C1', 9, 'vnpay', 20000.00, 'failed', NULL, NULL, NULL, NULL, NULL, '2026-03-13 12:57:41', '2026-03-19 15:41:14', NULL, NULL, '{\"payUrl\":\"https:\\/\\/sandbox.vnpayment.vn\\/paymentv2\\/vpcpay.html?vnp_Amount=2000000&vnp_Command=pay&vnp_CreateDate=20260313125741&vnp_CurrCode=VND&vnp_ExpireDate=20260313131241&vnp_IpAddr=%3A%3A1&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+ORD202603135223C1&vnp_OrderType=other&vnp_ReturnUrl=http%3A%2F%2Flocalhost%2Fwow-food%2Fuser%2Fvnpay-return.php&vnp_TmnCode=6SWCSTD3&vnp_TxnRef=ORD202603135223C1&vnp_Version=2.1.0&vnp_SecureHash=928eac1a15987a859a285ec2c499f1a1ac1d51c7b12ff128fa8c7df50e41013ef6acc669fc68baa4e178778e6b0a20ee5c7166028fce5d020807110f8cda502e\",\"created\":\"20260313125741\"}'),
(34, 'ORD2026031370F290', 9, 'momo', 20000.00, 'failed', NULL, NULL, NULL, NULL, NULL, '2026-03-13 12:58:00', '2026-03-19 15:41:14', 'ORD2026031370F290-1773381479-732', 'ORD2026031370F290-1773381479-732', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD2026031370F290-1773381479-732\",\"requestId\":\"ORD2026031370F290-1773381479-732\",\"amount\":20000,\"responseTime\":1773381479929,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMxMzcwRjI5MC0xNzczMzgxNDc5LTczMg&s=600043bb58911b23aa02bc25642595002a03aa677d9873a09762c7781ba40e9f\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMxMzcwRjI5MC0xNzczMzgxNDc5LTczMg&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMxMzcwRjI5MC0xNzczMzgxNDc5LTczMg&v=3.0&sr=0&sig=ti6hsmXPtkn8Qnm\"}'),
(35, 'ORD202603139DACFB', 9, 'vnpay', 97500.00, 'failed', NULL, NULL, NULL, NULL, NULL, '2026-03-13 13:02:04', '2026-03-19 15:41:14', NULL, NULL, '{\"payUrl\":\"https:\\/\\/sandbox.vnpayment.vn\\/paymentv2\\/vpcpay.html?vnp_Amount=9750000&vnp_Command=pay&vnp_CreateDate=20260313130204&vnp_CurrCode=VND&vnp_ExpireDate=20260313131704&vnp_IpAddr=%3A%3A1&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+ORD202603139DACFB&vnp_OrderType=other&vnp_ReturnUrl=https%3A%2F%2Faliyah-evaporative-interrogatively.ngrok-free.dev%2Fwow-food%2Fuser%2Fvnpay-return.php&vnp_TmnCode=6SWCSTD3&vnp_TxnRef=ORD202603139DACFB&vnp_Version=2.1.0&vnp_SecureHash=1f7d4ce702938199b3c8f097c75ce461e8ca639f6dc04a8a30b65abc846af0af3d01eaeff382e61a2101b679b4d66a062fc44058266e85ad01a2a0be6280f4fb\",\"created\":\"20260313130204\"}'),
(36, 'ORD20260313A3FDC9', 9, 'momo', 600500.00, 'failed', NULL, NULL, NULL, NULL, NULL, '2026-03-13 13:20:46', '2026-03-19 15:41:14', 'ORD20260313A3FDC9-1773382845-958', 'ORD20260313A3FDC9-1773382845-958', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD20260313A3FDC9-1773382845-958\",\"requestId\":\"ORD20260313A3FDC9-1773382845-958\",\"amount\":600500,\"responseTime\":1773382845739,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMxM0EzRkRDOS0xNzczMzgyODQ1LTk1OA&s=e7f6c3b8d64aebc9ad6cf69fb57d68224dfe4ee9229bf3e4f25271cc92fb71c7\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMxM0EzRkRDOS0xNzczMzgyODQ1LTk1OA&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMxM0EzRkRDOS0xNzczMzgyODQ1LTk1OA&v=3.0&sr=0&sig=mPCUp5MMAN9lj4W\"}'),
(37, 'ORD202603138AF7B2', 9, 'vnpay', 500000.00, 'failed', NULL, NULL, NULL, NULL, NULL, '2026-03-13 13:39:36', '2026-03-19 15:41:14', NULL, NULL, '{\"payUrl\":\"https:\\/\\/sandbox.vnpayment.vn\\/paymentv2\\/vpcpay.html?vnp_Amount=50000000&vnp_Command=pay&vnp_CreateDate=20260313133936&vnp_CurrCode=VND&vnp_ExpireDate=20260313135436&vnp_IpAddr=%3A%3A1&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+ORD202603138AF7B2&vnp_OrderType=other&vnp_ReturnUrl=http%3A%2F%2Flocalhost%2Fwow-food%2Fuser%2Fvnpay-return.php&vnp_TmnCode=6SWCSTD3&vnp_TxnRef=ORD202603138AF7B2&vnp_Version=2.1.0&vnp_SecureHash=742fb2bad6aaeb74ad491dbfdd8748af6d6ef1082507714b69ec5869b9783ad5851a691ed14715cb99e5e1203568960f3ad4c22dc0185acc244fe60039c819ab\",\"created\":\"20260313133936\"}'),
(38, 'ORD20260313889990', 9, 'vnpay', 560500.00, 'failed', NULL, NULL, NULL, NULL, NULL, '2026-03-13 13:50:02', '2026-03-19 15:41:14', NULL, NULL, '{\"payUrl\":\"https:\\/\\/sandbox.vnpayment.vn\\/paymentv2\\/vpcpay.html?vnp_Amount=56050000&vnp_Command=pay&vnp_CreateDate=20260313135002&vnp_CurrCode=VND&vnp_ExpireDate=20260313140502&vnp_IpAddr=%3A%3A1&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+ORD20260313889990&vnp_OrderType=other&vnp_ReturnUrl=http%3A%2F%2Flocalhost%2Fwow-food%2Fuser%2Fvnpay-return.php&vnp_TmnCode=6SWCSTD3&vnp_TxnRef=ORD20260313889990&vnp_Version=2.1.0&vnp_SecureHash=ba46d4e3656b2b23c566141c1763cee5a0beed866865786c0bbbcce76855ce05ba4a70264d40e2a9a174dc7d54e2f15fc0fa1c4a0a1b6b686dd81fff05fc1b74\",\"created\":\"20260313135002\"}'),
(39, 'ORD20260313636678', 9, 'vnpay', 15000.00, 'failed', NULL, NULL, NULL, NULL, NULL, '2026-03-13 13:54:00', '2026-03-19 15:41:14', NULL, NULL, '{\"payUrl\":\"https:\\/\\/sandbox.vnpayment.vn\\/paymentv2\\/vpcpay.html?vnp_Amount=1500000&vnp_Command=pay&vnp_CreateDate=20260313135400&vnp_CurrCode=VND&vnp_ExpireDate=20260313140900&vnp_IpAddr=%3A%3A1&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+ORD20260313636678&vnp_OrderType=other&vnp_ReturnUrl=+https%3A%2F%2Faliyah-evaporative-interrogatively.ngrok-free.dev%2Fwow-food%2Fuser%2Fvnpay-return.php&vnp_TmnCode=6SWCSTD3&vnp_TxnRef=ORD20260313636678&vnp_Version=2.1.0&vnp_SecureHash=9a572d98b5ed62d24af951c0e63c612e326d8a19b49fe10831e2a9c1eeef902aaf6b60a3bd8febac7fb1e71975fb36b6507a687350e6e9e7b8c7fcd1d6297953\",\"created\":\"20260313135400\"}'),
(40, 'ORD20260313ACDCF4', 9, 'vnpay', 75500.00, 'failed', NULL, NULL, NULL, NULL, NULL, '2026-03-13 13:56:14', '2026-03-19 15:41:14', NULL, NULL, '{\"payUrl\":\"https:\\/\\/sandbox.vnpayment.vn\\/paymentv2\\/vpcpay.html?vnp_Amount=7550000&vnp_Command=pay&vnp_CreateDate=20260313135614&vnp_CurrCode=VND&vnp_ExpireDate=20260313141114&vnp_IpAddr=%3A%3A1&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+ORD20260313ACDCF4&vnp_OrderType=other&vnp_ReturnUrl=+https%3A%2F%2Faliyah-evaporative-interrogatively.ngrok-free.dev%2Fwow-food%2Fuser%2Fvnpay-return.php&vnp_TmnCode=6SWCSTD3&vnp_TxnRef=ORD20260313ACDCF4&vnp_Version=2.1.0&vnp_SecureHash=35c7ede65eb10520e3a772b85edab4c5e87e28907c1fb7cbf7103a215b56286b411119bc3617f8c26ab772b17eb7b66874900e1819957c193c574aceac0c1a86\",\"created\":\"20260313135614\"}'),
(41, 'ORD202603135BC64C', 9, 'vnpay', 510000.00, 'failed', NULL, NULL, NULL, NULL, NULL, '2026-03-13 14:03:04', '2026-03-19 15:41:14', NULL, NULL, '{\"payUrl\":\"https:\\/\\/sandbox.vnpayment.vn\\/paymentv2\\/vpcpay.html?vnp_Amount=51000000&vnp_Command=pay&vnp_CreateDate=20260313140304&vnp_CurrCode=VND&vnp_ExpireDate=20260313141804&vnp_IpAddr=%3A%3A1&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+ORD202603135BC64C&vnp_OrderType=other&vnp_ReturnUrl=https%3A%2F%2Faliyah-evaporative-interrogatively.ngrok-free.dev%2Fwow-food%2Fuser%2Fvnpay-return.php&vnp_TmnCode=6SWCSTD3&vnp_TxnRef=ORD202603135BC64C&vnp_Version=2.1.0&vnp_SecureHash=8d1c007e5329dd65fb89ac53bf83bf3e2254946faf442969e5699e8404ccb518e41c53848f5cb71cecd2ec2ba6cf1bb7c3480240825bfd9a4d46516d016c7eb3\",\"created\":\"20260313140304\"}'),
(42, 'ORD20260315CA5A0E', 9, 'vnpay', 617500.00, 'failed', NULL, NULL, NULL, NULL, NULL, '2026-03-15 17:33:02', '2026-03-19 15:41:14', NULL, NULL, '{\"payUrl\":\"https:\\/\\/sandbox.vnpayment.vn\\/paymentv2\\/vpcpay.html?vnp_Amount=61750000&vnp_Command=pay&vnp_CreateDate=20260315173302&vnp_CurrCode=VND&vnp_ExpireDate=20260315174802&vnp_IpAddr=%3A%3A1&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+ORD20260315CA5A0E&vnp_OrderType=other&vnp_ReturnUrl=http%3A%2F%2Flocalhost%2Fwow-food%2Fuser%2Fvnpay-return.php&vnp_TmnCode=U1Q149D8&vnp_TxnRef=ORD20260315CA5A0E&vnp_Version=2.1.0&vnp_SecureHash=1ed9db428937c9fc550ca43feb823ae4f93cfbbbae066f4828538e0255b8c3863627d52d60adec911bbabd6dd842cde43e995b0cd70d2f61536027f0a587227c\",\"created\":\"20260315173302\"}'),
(43, 'ORD2026031982A9B3', 9, 'vnpay', 608500.00, 'failed', NULL, NULL, NULL, NULL, NULL, '2026-03-19 15:37:45', '2026-03-19 15:49:22', NULL, NULL, '{\"payUrl\":\"https:\\/\\/sandbox.vnpayment.vn\\/paymentv2\\/vpcpay.html?vnp_Amount=60850000&vnp_Command=pay&vnp_CreateDate=20260319153745&vnp_CurrCode=VND&vnp_ExpireDate=20260319155245&vnp_IpAddr=%3A%3A1&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+ORD2026031982A9B3&vnp_OrderType=other&vnp_ReturnUrl=http%3A%2F%2Flocalhost%2Fwow-food%2Fuser%2Fvnpay-return.php&vnp_TmnCode=U1Q149D8&vnp_TxnRef=ORD2026031982A9B3&vnp_Version=2.1.0&vnp_SecureHash=9023cf2ee24f81dc6aecfe4083e13c96b32fcfeee32c74f36adbf9a3616e402ab0f179ca252787a96ef14463397bfe8bfba3f6ff7979dee694491cd41f4707dc\",\"created\":\"20260319153745\"}'),
(44, 'ORD20260319F4E598', 9, 'vnpay', 608500.00, 'failed', NULL, NULL, NULL, NULL, NULL, '2026-03-19 15:41:05', '2026-03-20 17:50:43', NULL, NULL, '{\"payUrl\":\"https:\\/\\/sandbox.vnpayment.vn\\/paymentv2\\/vpcpay.html?vnp_Amount=60850000&vnp_Command=pay&vnp_CreateDate=20260319154613&vnp_CurrCode=VND&vnp_ExpireDate=20260319160113&vnp_IpAddr=%3A%3A1&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+ORD20260319F4E598&vnp_OrderType=other&vnp_ReturnUrl=http%3A%2F%2Flocalhost%2Fwow-food%2Fuser%2Fvnpay-return.php&vnp_TmnCode=U1Q149D8&vnp_TxnRef=ORD20260319F4E598&vnp_Version=2.1.0&vnp_SecureHash=175a814e360d3cc81db019e9c122d6d788aa9e1038d8e791ed33f0a556070a27213f67cd6c79133485bd4984724904bfd981c22617407a27073b528993c8fcc3\",\"created\":\"20260319154613\"}'),
(45, 'ORD202603202D6B83', 9, 'vnpay', 102500.00, 'failed', NULL, NULL, NULL, NULL, NULL, '2026-03-20 17:40:19', '2026-03-20 17:50:43', NULL, NULL, '{\"payUrl\":\"https:\\/\\/sandbox.vnpayment.vn\\/paymentv2\\/vpcpay.html?vnp_Amount=10250000&vnp_Command=pay&vnp_CreateDate=20260320174019&vnp_CurrCode=VND&vnp_ExpireDate=20260320175519&vnp_IpAddr=%3A%3A1&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+ORD202603202D6B83&vnp_OrderType=other&vnp_ReturnUrl=http%3A%2F%2Flocalhost%2Fwow-food%2Fuser%2Fvnpay-return.php&vnp_TmnCode=U1Q149D8&vnp_TxnRef=ORD202603202D6B83&vnp_Version=2.1.0&vnp_SecureHash=884d22947c413d96a3110588d282dff29b2798bf0206b88a050a650c91568f26bb77c0ed93903c0cb6d15b73916dc2108ce8c6dc0e7836637113d7a22dc14267\",\"created\":\"20260320174019\"}'),
(46, 'ORD20260320568677', 9, 'momo', 80500.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-03-20 17:43:03', '2026-03-20 17:43:03', 'ORD20260320568677-1774003382-459', 'ORD20260320568677-1774003382-459', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD20260320568677-1774003382-459\",\"requestId\":\"ORD20260320568677-1774003382-459\",\"amount\":80500,\"responseTime\":1774003380066,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDU2ODY3Ny0xNzc0MDAzMzgyLTQ1OQ&s=fc37db443ebb1f5e7a76492925aa3a2c00a4f3faedd635dc9a5a046e5b8d8e54\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDU2ODY3Ny0xNzc0MDAzMzgyLTQ1OQ&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDU2ODY3Ny0xNzc0MDAzMzgyLTQ1OQ&v=3.0&sr=0&sig=inVZEVP6lDUh8CN\"}'),
(47, 'ORD20260320C7A867', 9, 'momo', 102500.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-03-20 17:46:22', '2026-03-20 17:46:22', 'ORD20260320C7A867-1774003582-380', 'ORD20260320C7A867-1774003582-380', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD20260320C7A867-1774003582-380\",\"requestId\":\"ORD20260320C7A867-1774003582-380\",\"amount\":102500,\"responseTime\":1774003579546,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMEM3QTg2Ny0xNzc0MDAzNTgyLTM4MA&s=54500216c193b73b3749c5b1d99562be1e8b6e2584993912f197250a72cc808b\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMEM3QTg2Ny0xNzc0MDAzNTgyLTM4MA&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMEM3QTg2Ny0xNzc0MDAzNTgyLTM4MA&v=3.0&sr=0&sig=ggw2pzYr7fHJxze\"}'),
(48, 'ORD2026032013EE68', 9, 'momo', 64500.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-03-20 17:49:23', '2026-03-20 17:49:23', 'ORD2026032013EE68-1774003763-714', 'ORD2026032013EE68-1774003763-714', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD2026032013EE68-1774003763-714\",\"requestId\":\"ORD2026032013EE68-1774003763-714\",\"amount\":64500,\"responseTime\":1774003760563,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDEzRUU2OC0xNzc0MDAzNzYzLTcxNA&s=e5afd1b49cb248a5ee87ac76fbc6cfd1a6b0c93efa7bcec9345ee2b615ca77d4\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDEzRUU2OC0xNzc0MDAzNzYzLTcxNA&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDEzRUU2OC0xNzc0MDAzNzYzLTcxNA&v=3.0&sr=0&sig=8uuHd3YvmvNYawV\"}'),
(49, 'ORD2026032025A801', 9, 'vnpay', 75500.00, 'success', '15460255', NULL, NULL, NULL, NULL, '2026-03-20 17:49:39', '2026-03-20 17:50:41', NULL, NULL, '{\"vnp_Amount\":\"7550000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15460255\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD2026032025A801\",\"vnp_PayDate\":\"20260320175027\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"U1Q149D8\",\"vnp_TransactionNo\":\"15460255\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD2026032025A801\"}'),
(50, 'ORD2026032036C476', 9, 'momo', 92500.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-03-20 17:51:49', '2026-03-20 17:51:49', 'ORD2026032036C476-1774003909-118', 'ORD2026032036C476-1774003909-118', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD2026032036C476-1774003909-118\",\"requestId\":\"ORD2026032036C476-1774003909-118\",\"amount\":92500,\"responseTime\":1774003906838,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDM2QzQ3Ni0xNzc0MDAzOTA5LTExOA&s=5db6b1de0e30365976ed754fd657a6b67f867b42977c963764e0dd63b65277f9\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDM2QzQ3Ni0xNzc0MDAzOTA5LTExOA&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDM2QzQ3Ni0xNzc0MDAzOTA5LTExOA&v=3.0&sr=0&sig=CbFs2GMw7MOb4gP\"}'),
(51, 'ORD202603204AED63', 9, 'momo', 4500.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-03-20 18:21:41', '2026-03-20 18:21:41', 'ORD202603204AED63-1774005700-421', 'ORD202603204AED63-1774005700-421', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD202603204AED63-1774005700-421\",\"requestId\":\"ORD202603204AED63-1774005700-421\",\"amount\":4500,\"responseTime\":1774005698614,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDRBRUQ2My0xNzc0MDA1NzAwLTQyMQ&s=99840b78666800046c1e6b9037179cb23b0e235a7a4769521425ca326c0a8bbb\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDRBRUQ2My0xNzc0MDA1NzAwLTQyMQ&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDRBRUQ2My0xNzc0MDA1NzAwLTQyMQ&v=3.0&sr=0&sig=1MKXPd5YFGfCghh\"}'),
(52, 'ORD202603209D2969', 9, 'momo', 87000.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-03-20 18:22:19', '2026-03-20 18:22:19', 'ORD202603209D2969-1774005739-173', 'ORD202603209D2969-1774005739-173', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD202603209D2969-1774005739-173\",\"requestId\":\"ORD202603209D2969-1774005739-173\",\"amount\":87000,\"responseTime\":1774005736754,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDlEMjk2OS0xNzc0MDA1NzM5LTE3Mw&s=e0abf0acaef3cb092d8b03ca498402b8f974d5796845b98ef91f2a8dbafa5b99\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDlEMjk2OS0xNzc0MDA1NzM5LTE3Mw&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDlEMjk2OS0xNzc0MDA1NzM5LTE3Mw&v=3.0&sr=0&sig=YRKRZy5tnjwFS6x\"}'),
(53, 'ORD202603207334DA', 9, 'momo', 87000.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-03-20 18:24:41', '2026-03-20 18:24:41', 'ORD202603207334DA-1774005881-441', 'ORD202603207334DA-1774005881-441', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD202603207334DA-1774005881-441\",\"requestId\":\"ORD202603207334DA-1774005881-441\",\"amount\":87000,\"responseTime\":1774005878536,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDczMzREQS0xNzc0MDA1ODgxLTQ0MQ&s=efd26a02cca728d0f244345eca7976c25945c7da32dba032c166a1660e765746\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDczMzREQS0xNzc0MDA1ODgxLTQ0MQ&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDMyMDczMzREQS0xNzc0MDA1ODgxLTQ0MQ&v=3.0&sr=0&sig=3JZBHfJo7SsPY8c\"}'),
(54, 'ORD202604146CAA68', 11, 'momo', 105500.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-04-14 15:25:44', '2026-04-14 15:25:44', 'ORD202604146CAA68-1776155144-101', 'ORD202604146CAA68-1776155144-101', '{\"partnerCode\":\"MOMONPMB20210629\",\"orderId\":\"ORD202604146CAA68-1776155144-101\",\"requestId\":\"ORD202604146CAA68-1776155144-101\",\"amount\":105500,\"responseTime\":1776155144478,\"message\":\"Thành công.\",\"resultCode\":0,\"payUrl\":\"https:\\/\\/test-payment.momo.vn\\/v2\\/gateway\\/pay?t=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDQxNDZDQUE2OC0xNzc2MTU1MTQ0LTEwMQ&s=3c0fbbdc5b4f846f018ff22283acabed0d31df974f5ecd1bf57f28457bf4ebe6\",\"deeplink\":\"momo:\\/\\/app?action=payWithApp&isScanQR=false&scanQR=false&serviceType=app&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDQxNDZDQUE2OC0xNzc2MTU1MTQ0LTEwMQ&v=3.0\",\"qrCodeUrl\":\"momo:\\/\\/app?action=payWithApp&isScanQR=true&scanQR=true&serviceType=qr&sid=TU9NT05QTUIyMDIxMDYyOXxPUkQyMDI2MDQxNDZDQUE2OC0xNzc2MTU1MTQ0LTEwMQ&v=3.0&sr=0&sig=tb52O0e5DRt9E5q\"}'),
(55, 'ORD202604185478DA', 11, 'vnpay', 157500.00, 'success', '15502507', NULL, NULL, NULL, NULL, '2026-04-18 17:05:11', '2026-04-18 17:07:00', NULL, NULL, '{\"vnp_Amount\":\"15750000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15502507\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD202604185478DA\",\"vnp_PayDate\":\"20260418170653\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"U1Q149D8\",\"vnp_TransactionNo\":\"15502507\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD202604185478DA\"}'),
(56, 'ORD20260418C79A6B', 11, 'vnpay', 1564500.00, 'success', '15502510', NULL, NULL, NULL, NULL, '2026-04-18 17:07:57', '2026-04-18 17:08:46', NULL, NULL, '{\"vnp_Amount\":\"156450000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15502510\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD20260418C79A6B\",\"vnp_PayDate\":\"20260418170841\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"U1Q149D8\",\"vnp_TransactionNo\":\"15502510\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD20260418C79A6B\"}');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_refund`
--

CREATE TABLE `tbl_refund` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_code` varchar(20) NOT NULL,
  `payment_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `refund_reason` text NOT NULL,
  `refund_status` varchar(30) NOT NULL DEFAULT 'pending',
  `refund_method` varchar(30) DEFAULT 'original',
  `refund_transaction_id` varchar(255) DEFAULT NULL,
  `processed_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'Admin ID xử lý; NULL/0 = khách yêu cầu',
  `processed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_refund`
--

INSERT INTO `tbl_refund` (`id`, `order_code`, `payment_id`, `user_id`, `refund_amount`, `refund_reason`, `refund_status`, `refund_method`, `refund_transaction_id`, `processed_by`, `processed_at`, `created_at`, `updated_at`) VALUES
(10, 'ORD20260210AA49AE', 29, 9, 72000.00, '123', 'completed', 'original', '', 14, '2026-02-10 11:09:25', '2026-02-10 11:02:51', '2026-02-10 11:09:25'),
(11, 'ORD202602104B0488', 30, 9, 67000.00, 'sản phẩm không đúng mô tả', 'completed', 'original', '', 14, '2026-02-10 11:07:47', '2026-02-10 11:06:58', '2026-02-10 11:07:47'),
(12, 'ORD20260210B01583', 31, 9, 124900.00, '123', 'completed', 'original', '', 14, '2026-02-10 12:02:11', '2026-02-10 12:01:43', '2026-02-10 12:02:11');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_side_dish`
--

CREATE TABLE `tbl_side_dish` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `type` enum('food','drink') DEFAULT 'food',
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_side_dish`
--

INSERT INTO `tbl_side_dish` (`id`, `name`, `price`, `type`, `sort_order`) VALUES
(1, 'Trứng ốp la', 8000.00, 'food', 1),
(2, 'Nem rán', 10000.00, 'food', 2),
(3, 'Khoai tây chiên', 12000.00, 'food', 3),
(4, 'Salad', 6000.00, 'food', 4),
(5, 'Nước ngọt', 5000.00, 'drink', 5),
(6, 'Trà đá', 3000.00, 'drink', 6),
(7, 'Cà phê', 8000.00, 'drink', 7);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_size`
--

CREATE TABLE `tbl_size` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `price_add` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_size`
--

INSERT INTO `tbl_size` (`id`, `name`, `price_add`, `sort_order`) VALUES
(1, 'Nhỏ', 0.00, 1),
(2, 'Vừa', 5000.00, 2),
(3, 'Lớn', 10000.00, 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `ghn_province_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID Tỉnh/TP GHN',
  `ghn_district_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID Quận/Huyện GHN',
  `ghn_ward_code` varchar(20) DEFAULT NULL COMMENT 'Mã Phường/Xã GHN',
  `status` varchar(10) DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_user`
--

INSERT INTO `tbl_user` (`id`, `full_name`, `username`, `password`, `email`, `phone`, `address`, `ghn_province_id`, `ghn_district_id`, `ghn_ward_code`, `status`, `created_at`) VALUES
(9, 'Bùi Đức Duy', 'buiducduy095', '$2y$10$70mDO8RLJbo3hXKSN1x.lOhF/Rd2qIHl6pdaK231Vqhhm4qrtDiaW', 'buiducduy095@gmail.com', '0983224809', 'Số nhà 27A', NULL, NULL, NULL, 'Active', '2026-02-02 13:16:58'),
(10, 'Nguyễn Thế Phong ', 'nguyenthephong18062004', '$2y$10$8wnh9OuaYu3crW0wYVo12OvKWbZILHS8UqYHcYtVdvYaeKawGu6km', 'nguyenthephong18062004@gmail.com', '0983224809', 'Cây xăng Lân Ngát', 231, 3243, '250903', 'Active', '2026-02-10 15:46:45'),
(11, 'Lê Văn Hiếu', 'lehieu210924', '$2y$10$Tb./cwJ0QSyDn71aDe8dTeJkgPawBJCg.0BhnImGvYqlrKy3mxZsu', 'lehieu210924@gmail.com', '0372953009', '', 226, 1869, '260802', 'Active', '2026-04-14 15:23:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_verification`
--

CREATE TABLE `tbl_verification` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `verification_code` varchar(10) NOT NULL,
  `verification_type` enum('email','phone') NOT NULL DEFAULT 'email',
  `expires_at` datetime NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `attempts` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_verification`
--

INSERT INTO `tbl_verification` (`id`, `email`, `phone`, `verification_code`, `verification_type`, `expires_at`, `is_verified`, `attempts`, `created_at`) VALUES
(1, 'buiducduy095@gmail.com', NULL, '366802', 'email', '2026-01-12 05:07:15', 1, 1, '2026-01-12 10:57:15'),
(2, 'nguyenthephong18062004@gmail.com', NULL, '388784', 'email', '2026-01-14 10:27:08', 1, 1, '2026-01-14 16:17:08'),
(3, 'buiducduy0848@gmail.com', NULL, '732251', 'email', '2026-01-15 09:09:33', 1, 1, '2026-01-15 14:59:33'),
(4, 'buiducduy3005@gmail.com', NULL, '422647', 'email', '2026-01-30 16:50:15', 1, 1, '2026-01-30 22:40:15'),
(5, 'buiducduy095@gmail.com', NULL, '928747', 'email', '2026-02-02 07:26:30', 1, 1, '2026-02-02 13:16:30'),
(6, 'nguyenthephong18062004@gmail.com', NULL, '775495', 'email', '2026-02-10 09:56:13', 0, 0, '2026-02-10 15:46:13'),
(7, 'nguyenthephong18062004@gmail.com', NULL, '044771', 'email', '2026-02-10 09:56:19', 1, 1, '2026-02-10 15:46:19'),
(8, 'lehieu210924@gmail.com', NULL, '087125', 'email', '2026-04-14 10:33:01', 1, 1, '2026-04-14 15:23:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_voucher`
--

CREATE TABLE `tbl_voucher` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_order` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_voucher`
--

INSERT INTO `tbl_voucher` (`id`, `code`, `type`, `value`, `min_order`, `max_discount`, `status`, `valid_from`, `valid_to`, `created_at`) VALUES
(1, 'WOWFOOD10', 'percent', 75.00, 0.00, 0.00, 'active', NULL, NULL, '2026-03-16 13:35:53'),
(2, 'WOWFOOD20K', 'fixed', 20000.00, 100000.00, 0.00, 'active', NULL, NULL, '2026-03-16 13:35:53'),
(3, 'WOWFOOD50', 'percent', 50.00, 300000.00, 100000.00, 'active', '2026-03-16 13:35:53', '2026-04-15 13:35:53', '2026-03-16 13:35:53');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tbl_cart`
--
ALTER TABLE `tbl_cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `food_id` (`food_id`);

--
-- Chỉ mục cho bảng `tbl_category`
--
ALTER TABLE `tbl_category`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tbl_chat`
--
ALTER TABLE `tbl_chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_chat_admin` (`admin_id`),
  ADD KEY `fk_chat_user` (`user_id`);

--
-- Chỉ mục cho bảng `tbl_food`
--
ALTER TABLE `tbl_food`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tbl_order`
--
ALTER TABLE `tbl_order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `fk_order_user` (`user_id`),
  ADD KEY `idx_order_payment_status` (`payment_status`),
  ADD KEY `idx_order_payment_method` (`payment_method`);

--
-- Chỉ mục cho bảng `tbl_order_notification`
--
ALTER TABLE `tbl_order_notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `tbl_payment`
--
ALTER TABLE `tbl_payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_code` (`order_code`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_transaction_id` (`transaction_id`);

--
-- Chỉ mục cho bảng `tbl_refund`
--
ALTER TABLE `tbl_refund`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_code` (`order_code`),
  ADD KEY `idx_payment_id` (`payment_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_refund_status` (`refund_status`),
  ADD KEY `fk_refund_admin` (`processed_by`);

--
-- Chỉ mục cho bảng `tbl_side_dish`
--
ALTER TABLE `tbl_side_dish`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tbl_size`
--
ALTER TABLE `tbl_size`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `tbl_verification`
--
ALTER TABLE `tbl_verification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `verification_code` (`verification_code`);

--
-- Chỉ mục cho bảng `tbl_voucher`
--
ALTER TABLE `tbl_voucher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `code_idx` (`code`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `tbl_cart`
--
ALTER TABLE `tbl_cart`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `tbl_category`
--
ALTER TABLE `tbl_category`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT cho bảng `tbl_chat`
--
ALTER TABLE `tbl_chat`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT cho bảng `tbl_food`
--
ALTER TABLE `tbl_food`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT cho bảng `tbl_order`
--
ALTER TABLE `tbl_order`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT cho bảng `tbl_order_notification`
--
ALTER TABLE `tbl_order_notification`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT cho bảng `tbl_payment`
--
ALTER TABLE `tbl_payment`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT cho bảng `tbl_refund`
--
ALTER TABLE `tbl_refund`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `tbl_side_dish`
--
ALTER TABLE `tbl_side_dish`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `tbl_size`
--
ALTER TABLE `tbl_size`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `tbl_verification`
--
ALTER TABLE `tbl_verification`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `tbl_voucher`
--
ALTER TABLE `tbl_voucher`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `tbl_chat`
--
ALTER TABLE `tbl_chat`
  ADD CONSTRAINT `fk_chat_admin` FOREIGN KEY (`admin_id`) REFERENCES `tbl_admin` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_chat_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_order`
--
ALTER TABLE `tbl_order`
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `tbl_payment`
--
ALTER TABLE `tbl_payment`
  ADD CONSTRAINT `fk_payment_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_refund`
--
ALTER TABLE `tbl_refund`
  ADD CONSTRAINT `fk_refund_admin` FOREIGN KEY (`processed_by`) REFERENCES `tbl_admin` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_refund_payment` FOREIGN KEY (`payment_id`) REFERENCES `tbl_payment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_refund_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
