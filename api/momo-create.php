<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/constants.php';

if (!function_exists('curl_init')) {
    echo json_encode(['success' => false, 'message' => 'PHP chưa bật extension cURL (curl_init không tồn tại). Hãy bật php_curl trong php.ini và restart Apache.']);
    exit;
}

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

// đảm bảo có bảng payment tối thiểu để lưu requestId/orderId
$ok = $conn->query("
    CREATE TABLE IF NOT EXISTS tbl_payment (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        order_code VARCHAR(20) NOT NULL,
        user_id INT UNSIGNED DEFAULT NULL,
        payment_method VARCHAR(20) NOT NULL DEFAULT 'momo',
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
if ($ok === false) {
    echo json_encode(['success' => false, 'message' => 'Không tạo/không truy cập được bảng tbl_payment: ' . $conn->error]);
    exit;
}

// Nếu bảng đã tồn tại từ trước (schema cũ), đảm bảo các cột cần thiết có tồn tại
function momoEnsureColumn(mysqli $conn, string $table, string $col, string $ddl) {
    $dbRes = $conn->query("SELECT DATABASE() AS db");
    $dbRow = $dbRes ? $dbRes->fetch_assoc() : null;
    $db = $dbRow['db'] ?? '';
    if ($db === '') return;
    $stmt = $conn->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1");
    if (!$stmt) return;
    $stmt->bind_param("sss", $db, $table, $col);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    if (!$exists) {
        $conn->query("ALTER TABLE {$table} ADD COLUMN {$ddl}");
    }
}

momoEnsureColumn($conn, 'tbl_payment', 'request_id', "request_id VARCHAR(64) DEFAULT NULL");
momoEnsureColumn($conn, 'tbl_payment', 'order_id', "order_id VARCHAR(64) DEFAULT NULL");
momoEnsureColumn($conn, 'tbl_payment', 'transaction_id', "transaction_id VARCHAR(128) DEFAULT NULL");
momoEnsureColumn($conn, 'tbl_payment', 'payment_status', "payment_status VARCHAR(30) NOT NULL DEFAULT 'pending'");
momoEnsureColumn($conn, 'tbl_payment', 'raw_response', "raw_response MEDIUMTEXT DEFAULT NULL");
momoEnsureColumn($conn, 'tbl_payment', 'created_at', "created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
momoEnsureColumn($conn, 'tbl_payment', 'updated_at', "updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

// Load order & verify ownership
$stmt = $conn->prepare("SELECT order_code, user_id, total, status FROM tbl_order WHERE order_code = ? LIMIT 1");
$stmt->bind_param("s", $order_code);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order || (int)$order['user_id'] !== (int)$_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại hoặc không thuộc về bạn']);
    exit;
}

if (!in_array($order['status'], ['Pending Payment', 'Pending', 'Unpaid', 'Ordered'], true)) {
    // vẫn cho tạo QR nếu đang Pending Payment; chặn các trạng thái hoàn tất
}

// Hệ thống đã lưu total theo VND (vd 12000.00 = 12.000đ)
// MoMo yêu cầu amount = số tiền VND, tối thiểu 1.000
$amount = (int) round((float)$order['total']);
if ($amount < 1000) $amount = 1000;

// Reuse existing payment row if any
$stmt = $conn->prepare("SELECT * FROM tbl_payment WHERE order_code = ? AND payment_method = 'momo' LIMIT 1");
$stmt->bind_param("s", $order_code);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

// MoMo yêu cầu orderId/requestId duy nhất, mỗi lần tạo thanh toán phải tạo mới để tránh "trùng orderId"
$uniq = time() . '-' . random_int(100, 999);
$requestId = $order_code . '-' . $uniq;
$orderId = $order_code . '-' . $uniq;

$partnerCode = MOMO_PARTNER_CODE;
$accessKey = MOMO_ACCESS_KEY;
$secretKey = MOMO_SECRET_KEY;

$redirectUrl = MOMO_REDIRECT_URL . '?order_code=' . urlencode($order_code);
$ipnUrl = MOMO_IPN_URL;
$orderInfo = 'Thanh toan don hang ' . $order_code;
$orderType = 'momo_wallet';
$requestType = 'captureWallet';
$extraData = '';

$rawHash = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&ipnUrl={$ipnUrl}&orderId={$orderId}&orderInfo={$orderInfo}&partnerCode={$partnerCode}&redirectUrl={$redirectUrl}&requestId={$requestId}&requestType={$requestType}";
$signature = hash_hmac('sha256', $rawHash, $secretKey);

$payload = [
    'partnerCode' => $partnerCode,
    'partnerName' => 'WowFood',
    'storeName' => 'WowFood',
    'storeId' => 'WowFood',
    'requestId' => $requestId,
    'amount' => (string)$amount,
    'orderId' => $orderId,
    'orderInfo' => $orderInfo,
    'orderType' => $orderType,
    'redirectUrl' => $redirectUrl,
    'ipnUrl' => $ipnUrl,
    'lang' => 'vi',
    'extraData' => $extraData,
    'requestType' => $requestType,
    'autoCapture' => true,
    'signature' => $signature
];

$ch = curl_init(MOMO_ENDPOINT . '/v2/gateway/api/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=UTF-8']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
// local dev thường hay lỗi CA/SSL, nới lỏng để test (UAT)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$resp = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($resp === false) {
    echo json_encode(['success' => false, 'message' => 'Không gọi được MoMo: ' . $err]);
    exit;
}

$data = json_decode($resp, true);
if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Phản hồi MoMo không hợp lệ', 'raw' => $resp]);
    exit;
}

if (($data['resultCode'] ?? 1) != 0) {
    $msg = $data['message'] ?? 'MoMo tạo thanh toán thất bại';
    $code = $data['resultCode'] ?? '';
    echo json_encode(['success' => false, 'message' => ($code !== '' ? ('['.$code.'] '.$msg) : $msg), 'momo' => $data]);
    exit;
}

// Upsert payment record
if ($payment) {
    $stmt = $conn->prepare("UPDATE tbl_payment SET amount = ?, request_id = ?, order_id = ?, payment_status = 'pending', raw_response = ? WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'SQL prepare failed (UPDATE tbl_payment): ' . $conn->error]);
        exit;
    }
    $raw = json_encode($data, JSON_UNESCAPED_UNICODE);
    $stmt->bind_param("dsssi", $order['total'], $requestId, $orderId, $raw, $payment['id']);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt = $conn->prepare("INSERT INTO tbl_payment (order_code, user_id, payment_method, amount, request_id, order_id, payment_status, raw_response) VALUES (?, ?, 'momo', ?, ?, ?, 'pending', ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'SQL prepare failed (INSERT tbl_payment): ' . $conn->error]);
        exit;
    }
    $raw = json_encode($data, JSON_UNESCAPED_UNICODE);
    $stmt->bind_param("sidsss", $order_code, $_SESSION['user_id'], $order['total'], $requestId, $orderId, $raw);
    $stmt->execute();
    $stmt->close();
}

echo json_encode([
    'success' => true,
    'order_code' => $order_code,
    'amount' => $amount,
    'payUrl' => $data['payUrl'] ?? '',
    'deeplink' => $data['deeplink'] ?? '',
    'qrCodeUrl' => $data['qrCodeUrl'] ?? '',
    'deeplinkMiniApp' => $data['deeplinkMiniApp'] ?? ''
]);

