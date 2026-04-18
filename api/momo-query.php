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

$stmt = $conn->prepare("SELECT order_code, user_id, total, status FROM tbl_order WHERE order_code = ? LIMIT 1");
$stmt->bind_param("s", $order_code);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order || (int)$order['user_id'] !== (int)$_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại hoặc không thuộc về bạn']);
    exit;
}

$stmt = $conn->prepare("SELECT id, request_id, order_id, payment_status FROM tbl_payment WHERE order_code = ? AND payment_method = 'momo' LIMIT 1");
$stmt->bind_param("s", $order_code);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$payment || empty($payment['request_id']) || empty($payment['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa có phiên thanh toán MoMo cho đơn này']);
    exit;
}

$partnerCode = MOMO_PARTNER_CODE;
$accessKey = MOMO_ACCESS_KEY;
$secretKey = MOMO_SECRET_KEY;

$requestId = $payment['request_id'];
$orderId = $payment['order_id'];
$rawHash = "accessKey={$accessKey}&orderId={$orderId}&partnerCode={$partnerCode}&requestId={$requestId}";
$signature = hash_hmac('sha256', $rawHash, $secretKey);

$payload = [
    'partnerCode' => $partnerCode,
    'requestId' => $requestId,
    'orderId' => $orderId,
    'lang' => 'vi',
    'signature' => $signature
];

$ch = curl_init(MOMO_ENDPOINT . '/v2/gateway/api/query');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=UTF-8']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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

$resultCode = (int)($data['resultCode'] ?? 1);
$transId = (string)($data['transId'] ?? '');

// Update payment record
$raw = json_encode($data, JSON_UNESCAPED_UNICODE);
if ($resultCode === 0) {
    $stmt = $conn->prepare("UPDATE tbl_payment SET payment_status = 'success', transaction_id = ?, raw_response = ? WHERE id = ?");
    $stmt->bind_param("ssi", $transId, $raw, $payment['id']);
    $stmt->execute();
    $stmt->close();

    // Update order status
    $stmt = $conn->prepare("UPDATE tbl_order SET status = 'Pending' WHERE order_code = ? LIMIT 1");
    $stmt->bind_param("s", $order_code);
    $stmt->execute();
    $stmt->close();

    // Clear cart after successful online payment
    $_SESSION['cart'] = [];

    echo json_encode(['success' => true, 'paid' => true, 'message' => 'Thanh toán thành công', 'momo' => $data]);
    exit;
}

// pending or failed
$newStatus = ($resultCode === 1000 || $resultCode === 7000) ? 'pending' : 'failed';
$stmt = $conn->prepare("UPDATE tbl_payment SET payment_status = ?, raw_response = ? WHERE id = ?");
$stmt->bind_param("ssi", $newStatus, $raw, $payment['id']);
$stmt->execute();
$stmt->close();

echo json_encode([
    'success' => true,
    'paid' => false,
    'message' => $data['message'] ?? 'Chưa thanh toán',
    'resultCode' => $resultCode,
    'momo' => $data
]);

