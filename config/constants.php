<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('SITEURL')) define('SITEURL', 'http://localhost/php/Do_An_Cnpm/');

// if (!defined('SITEURL')) define('SITEURL', ' https://aliyah-evaporative-interrogatively.ngrok-free.dev/wow-food/'); // link deploy sử dụng tạm thời không xóa 


$host = "localhost";
$username = "root";
$port = 3306;
$password = "";
$dbname = "food-oder"; // Tên DB phải trùng với sql/food-oder.sql (chú ý: "oder" không phải "order")

if (!isset($conn) || !($conn instanceof mysqli)) {
    $conn = @new mysqli($host, $username, $password, $dbname, $port);
    if ($conn->connect_error) {
        $err = $conn->connect_error;
        $msg = "Không kết nối được cơ sở dữ liệu. ";
        if (strpos($err, 'refused') !== false || strpos($err, '2002') !== false || strpos($err, 'Connection refused') !== false) {
            $msg .= "Vui lòng bật MySQL trong XAMPP (Start MySQL).";
        } elseif (strpos($err, 'Unknown database') !== false) {
            $msg .= "Database \"{$dbname}\" chưa tồn tại. Tạo database và import file sql/food-oder.sql trong phpMyAdmin.";
        } else {
            $msg .= "Kiểm tra host, dbname, password trong config/constants.php. Lỗi: " . $err;
        }
        die("<html><head><meta charset=\"utf-8\"><title>Lỗi kết nối</title></head><body style=\"font-family:sans-serif;padding:2rem;max-width:600px;margin:0 auto;\"><h2>Lỗi kết nối cơ sở dữ liệu</h2><p>" . htmlspecialchars($msg) . "</p></body></html>");
    }
    $conn->set_charset("utf8mb4");
}

if (!defined('GHN_API_BASE')) define('GHN_API_BASE', 'https://dev-online-gateway.ghn.vn/shiip/public-api');
if (!defined('GHN_MASTER_DATA_URL')) define('GHN_MASTER_DATA_URL', GHN_API_BASE . '/master-data');
if (!defined('GHN_TOKEN')) define('GHN_TOKEN', '915d83da-c9d0-11f0-b989-ea7e29c7fb39');
if (!defined('GHN_SHOP_ID')) define('GHN_SHOP_ID', 198208);
if (!defined('GHN_FROM_DISTRICT_ID')) define('GHN_FROM_DISTRICT_ID', 3440);
if (!defined('GHN_FROM_WARD_CODE')) define('GHN_FROM_WARD_CODE', '1A0607');
if (!defined('GHN_DEFAULT_WEIGHT_GRAM')) define('GHN_DEFAULT_WEIGHT_GRAM', 500);

// MoMo
if (!defined('MOMO_ENDPOINT')) define('MOMO_ENDPOINT', 'https://test-payment.momo.vn');
if (!defined('MOMO_PARTNER_CODE')) define('MOMO_PARTNER_CODE', 'MOMONPMB20210629');
if (!defined('MOMO_ACCESS_KEY')) define('MOMO_ACCESS_KEY', 'Q2XhhSdgpKUlQ4Ky');
if (!defined('MOMO_SECRET_KEY')) define('MOMO_SECRET_KEY', 'k6B53GQKSjktZGJBK2MyrDa7w9S6RyCf');
if (!defined('MOMO_REDIRECT_URL')) define('MOMO_REDIRECT_URL', SITEURL . 'user/momo-return.php');
if (!defined('MOMO_IPN_URL')) define('MOMO_IPN_URL', SITEURL . 'api/momo-ipn.php');

// VNPay (Sandbox – môi trường TEST)
if (!defined('VNPAY_TMN_CODE')) define('VNPAY_TMN_CODE', 'U1Q149D8');
if (!defined('VNPAY_HASH_SECRET')) define('VNPAY_HASH_SECRET', '5GM0WI0CINW73KI4EGBME7ITBHB2HZMH');
if (!defined('VNPAY_URL')) define('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
if (!defined('VNPAY_RETURN_URL')) define('VNPAY_RETURN_URL', SITEURL . 'user/vnpay-return.php');
if (!defined('VNPAY_IPN_URL')) define('VNPAY_IPN_URL', SITEURL . 'api/vnpay-ipn.php');
