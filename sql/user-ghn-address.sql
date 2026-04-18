-- Thêm cột địa chỉ GHN vào tbl_user để lưu địa chỉ đăng ký và pre-fill ở checkout
ALTER TABLE `tbl_user`
  ADD COLUMN `ghn_province_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'ID Tỉnh/TP GHN' AFTER `address`,
  ADD COLUMN `ghn_district_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'ID Quận/Huyện GHN' AFTER `ghn_province_id`,
  ADD COLUMN `ghn_ward_code` VARCHAR(20) NULL DEFAULT NULL COMMENT 'Mã Phường/Xã GHN' AFTER `ghn_district_id`;
