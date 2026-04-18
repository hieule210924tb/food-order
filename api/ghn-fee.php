<?php
/**
 * Bước 4: Tính phí giao hàng – Hệ thống gửi thông tin, GHN trả về phí, hiển thị cho khách.
 * POST: to_district_id (int), to_ward_code (string), weight (int, gram, optional)
 */
ob_start();
require_once __DIR__ . '/../config/constants.php';
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'fee' => 0, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

$to_district_id = isset($_REQUEST['to_district_id']) ? (int) $_REQUEST['to_district_id'] : 0;
$to_ward_code = isset($_REQUEST['to_ward_code']) ? trim((string) $_REQUEST['to_ward_code']) : '';
$to_ward_code = $to_ward_code === '' ? '' : (string) $to_ward_code; // GHN cần chuỗi (vd: "1A0607" hoặc "510101")
$weight = isset($_REQUEST['weight']) ? max(1, (int) $_REQUEST['weight']) : (defined('GHN_DEFAULT_WEIGHT_GRAM') ? GHN_DEFAULT_WEIGHT_GRAM : 500);

if (!$to_district_id || $to_ward_code === '') {
    echo json_encode(['success' => false, 'fee' => 0, 'message' => 'Thiếu quận/huyện hoặc phường/xã giao hàng.']);
    exit;
}

if (!defined('GHN_TOKEN') || GHN_TOKEN === '' || !defined('GHN_SHOP_ID') || GHN_SHOP_ID <= 0) {
    echo json_encode(['success' => false, 'fee' => 0, 'message' => 'Chưa cấu hình GHN.']);
    exit;
}

$url = (defined('GHN_API_BASE') ? GHN_API_BASE : 'https://dev-online-gateway.ghn.vn/shiip/public-api') . '/v2/shipping-order/fee';

$body = [
    'from_district_id'  => defined('GHN_FROM_DISTRICT_ID') ? (int) GHN_FROM_DISTRICT_ID : 0,
    'from_ward_code'    => defined('GHN_FROM_WARD_CODE') ? (string) GHN_FROM_WARD_CODE : '',
    'to_district_id'   => $to_district_id,
    'to_ward_code'     => $to_ward_code,
    'weight'           => $weight,
    'length'           => 20,
    'width'            => 20,
    'height'           => 10,
    'service_type_id'  => 2, // 2 = Standard (E-Commerce)
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST          => true,
    CURLOPT_HTTPHEADER    => [
        'Content-Type: application/json',
        'Token: ' . GHN_TOKEN,
        'ShopId: ' . (int) GHN_SHOP_ID,
    ],
    CURLOPT_POSTFIELDS => json_encode($body),
]);

$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode(['success' => false, 'fee' => 0, 'message' => 'Không kết nối được GHN.']);
    exit;
}

$data = json_decode($response, true);
if (is_array($data) && isset($data['code']) && (int) $data['code'] === 200 && isset($data['data']['total'])) {
    $fee = (int) $data['data']['total'];
    echo json_encode(['success' => true, 'fee' => $fee, 'total' => $fee, 'message' => $data['message'] ?? 'Success']);
    exit;
}

$msg = isset($data['message']) ? $data['message'] : 'Không lấy được phí ship.';
echo json_encode(['success' => false, 'fee' => 0, 'message' => $msg]);
