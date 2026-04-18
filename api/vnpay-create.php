<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/constants.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$order_code = isset($_POST['order_code']) ? trim($_POST['order_code']) : '';
if ($order_code === '') {
    echo json_encode(['success' => false, 'message' => 'Thiếu mã đơn hàng']);
    exit;
}

// Đảm bảo bảng tbl_payment có (dùng chung với MoMo)
$conn->query("
    CREATE TABLE IF NOT EXISTS tbl_payment (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        order_code VARCHAR(20) NOT NULL,
        user_id INT UNSIGNED DEFAULT NULL,
        payment_method VARCHAR(20) NOT NULL DEFAULT 'vnpay',
        amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        request_id VARCHAR(64) DEFAULT NULL,
        order_id VARCHAR(64) DEFAULT NULL,
        transaction_id VARCHAR(128) DEFAULT NULL,
        payment_status VARCHAR(30) NOT NULL DEFAULT 'pending',
        raw_response MEDIUMTEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_order_code_method (order_code, payment_method),
        KEY idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$stmt = $conn->prepare("SELECT order_code, user_id, total, status FROM tbl_order WHERE order_code = ? LIMIT 1");
$stmt->bind_param("s", $order_code);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order || (int)$order['user_id'] !== (int)$_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại hoặc không thuộc về bạn']);
    exit;
}

$amount = (int) round((float)$order['total']);
if ($amount < 1000) $amount = 1000;

date_default_timezone_set('Asia/Ho_Chi_Minh');
$vnp_TmnCode = VNPAY_TMN_CODE;
$vnp_HashSecret = VNPAY_HASH_SECRET;
$vnp_Url = VNPAY_URL;
$vnp_ReturnUrl = VNPAY_RETURN_URL;
$vnp_TxnRef = $order_code;
$vnp_Amount = (int)($amount * 100); // VNPay: số tiền x 100 (VD 50.000 VND = 5000000)
$vnp_OrderInfo = 'Thanh toan don hang ' . $order_code; // Tiếng Việt không dấu
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$vnp_CreateDate = date('YmdHis');
$vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes'));

// Chỉ gửi đúng tham số bắt buộc như demo VNPay (không gửi vnp_IpnUrl khi tạo URL - cấu hình IPN trên Merchant Admin)
$inputData = [
    'vnp_Version'   => '2.1.0',
    'vnp_Command'   => 'pay',
    'vnp_TmnCode'   => $vnp_TmnCode,
    'vnp_Amount'    => $vnp_Amount,
    'vnp_CurrCode'  => 'VND',
    'vnp_TxnRef'    => $vnp_TxnRef,
    'vnp_OrderInfo' => $vnp_OrderInfo,
    'vnp_OrderType' => 'other',
    'vnp_Locale'    => 'vn',
    'vnp_ReturnUrl' => $vnp_ReturnUrl,
    'vnp_IpAddr'    => $vnp_IpAddr,
    'vnp_CreateDate'=> $vnp_CreateDate,
    'vnp_ExpireDate'=> $vnp_ExpireDate,
];

ksort($inputData);
$hashdata = '';
$i = 0;
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . '=' . urlencode((string)$value);
    } else {
        $hashdata .= urlencode($key) . '=' . urlencode((string)$value);
        $i = 1;
    }
}
$vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

$query = '';
foreach ($inputData as $key => $value) {
    $query .= urlencode($key) . '=' . urlencode((string)$value) . '&';
}
$payUrl = $vnp_Url . '?' . $query . 'vnp_SecureHash=' . $vnpSecureHash;

// Lưu vào tbl_payment
$stmt = $conn->prepare("SELECT id FROM tbl_payment WHERE order_code = ? AND payment_method = 'vnpay' LIMIT 1");
$stmt->bind_param("s", $order_code);
$stmt->execute();
$payRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($payRow) {
    $stmt = $conn->prepare("UPDATE tbl_payment SET amount = ?, payment_status = 'pending', raw_response = ? WHERE id = ?");
    $raw = json_encode(['payUrl' => $payUrl, 'created' => $vnp_CreateDate]);
    $stmt->bind_param("dsi", $order['total'], $raw, $payRow['id']);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt = $conn->prepare("INSERT INTO tbl_payment (order_code, user_id, payment_method, amount, payment_status, raw_response) VALUES (?, ?, 'vnpay', ?, 'pending', ?)");
    $raw = json_encode(['payUrl' => $payUrl, 'created' => $vnp_CreateDate]);
    $stmt->bind_param("sids", $order_code, $_SESSION['user_id'], $order['total'], $raw);
    $stmt->execute();
    $stmt->close();
}

echo json_encode([
    'success'  => true,
    'order_code' => $order_code,
    'amount'   => $amount,
    'payUrl'   => $payUrl
]);
