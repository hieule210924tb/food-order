-- Bước 7: Lưu mã đơn nội bộ + mã vận đơn GHN. Thêm cột cho đơn hàng & giao hàng GHN.
-- Chạy file này một lần để cập nhật bảng tbl_order. (Bỏ qua từng dòng nếu cột đã tồn tại.)

ALTER TABLE `tbl_order` ADD COLUMN `shipping_fee` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Phí ship GHN' AFTER `total`;
ALTER TABLE `tbl_order` ADD COLUMN `to_district_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'ID quận/huyện GHN' AFTER `customer_address`;
ALTER TABLE `tbl_order` ADD COLUMN `to_ward_code` VARCHAR(20) NULL DEFAULT NULL COMMENT 'Mã phường/xã GHN' AFTER `to_district_id`;
ALTER TABLE `tbl_order` ADD COLUMN `order_weight_gram` INT UNSIGNED NOT NULL DEFAULT 500 COMMENT 'Khối lượng đơn (gram)' AFTER `to_ward_code`;
ALTER TABLE `tbl_order` ADD COLUMN `ghn_order_code` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Mã vận đơn GHN' AFTER `order_weight_gram`;
ALTER TABLE `tbl_order` ADD COLUMN `ghn_sort_code` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Mã sắp xếp GHN' AFTER `ghn_order_code`;
ALTER TABLE `tbl_order` ADD COLUMN `ghn_status` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Trạng thái giao hàng GHN' AFTER `ghn_sort_code`;
ALTER TABLE `tbl_order` ADD COLUMN `order_details` TEXT NULL DEFAULT NULL COMMENT 'Chi tiết đơn hàng (món, size, món kèm + giá)' AFTER `food`;
