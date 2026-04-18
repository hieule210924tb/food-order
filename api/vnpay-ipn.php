<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/constants.php';

$inputData = [];
$method = $_SERVER['REQUEST_METHOD'];
$params = ($method === 'GET') ? $_GET : $_POST;

foreach ($params as $key => $value) {
    if (substr($key, 0, 4) === 'vnp_') {
        $inputData[$key] = $value;
    }
}

if (!isset($inputData['vnp_SecureHash']) || !isset($inputData['vnp_TxnRef'])) {
    echo json_encode(['RspCode' => '99', 'Message' => 'Invalid request']);
    exit;
}

$vnp_SecureHash = $inputData['vnp_SecureHash'];
unset($inputData['vnp_SecureHash']);
unset($inputData['vnp_SecureHashType']);
ksort($inputData);

$hashData = '';
$i = 0;
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
    } else {
        $hashData .= urlencode($key) . '=' . urlencode($value);
        $i = 1;
    }
}
$secureHash = hash_hmac('sha512', $hashData, VNPAY_HASH_SECRET);

$returnData = ['RspCode' => '97', 'Message' => 'Invalid signature'];

if ($secureHash !== $vnp_SecureHash) {
    echo json_encode($returnData);
    exit;
}

$order_code = $inputData['vnp_TxnRef'];
$vnp_Amount = isset($inputData['vnp_Amount']) ? (int)$inputData['vnp_Amount'] / 100 : 0;
$vnp_TransactionNo = $inputData['vnp_TransactionNo'] ?? '';
$vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
$vnp_TransactionStatus = $inputData['vnp_TransactionStatus'] ?? '';

$stmt = $conn->prepare("SELECT id, order_code, user_id, total, status FROM tbl_order WHERE order_code = ? LIMIT 1");
$stmt->bind_param("s", $order_code);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode(['RspCode' => '01', 'Message' => 'Order not found']);
    exit;
}

$expectedAmount = (int) round((float)$order['total']);
if ($vnp_Amount < $expectedAmount - 1) {
    echo json_encode(['RspCode' => '04', 'Message' => 'invalid amount']);
    exit;
}

if (!in_array($order['status'], ['Pending Payment', 'Pending', 'Unpaid', 'Ordered'], true)) {
    echo json_encode(['RspCode' => '02', 'Message' => 'Order already confirmed']);
    exit;
}

if ($vnp_ResponseCode === '00' && $vnp_TransactionStatus === '00') {
    $stmt = $conn->prepare("UPDATE tbl_order SET status = 'Pending' WHERE id = ?");
    $stmt->bind_param("i", $order['id']);
    $stmt->execute();
    $stmt->close();

    $raw = json_encode($inputData);
    $stmt = $conn->prepare("UPDATE tbl_payment SET payment_status = 'success', transaction_id = ?, raw_response = ?, updated_at = NOW() WHERE order_code = ? AND payment_method = 'vnpay'");
    $stmt->bind_param("sss", $vnp_TransactionNo, $raw, $order_code);
    $stmt->execute();
    if ($stmt->affected_rows === 0) {
        $conn->query("INSERT INTO tbl_payment (order_code, user_id, payment_method, amount, payment_status, transaction_id, raw_response) VALUES ('" . $conn->real_escape_string($order_code) . "', " . (int)$order['user_id'] . ", 'vnpay', " . (float)$order['total'] . ", 'success', '" . $conn->real_escape_string($vnp_TransactionNo) . "', '" . $conn->real_escape_string($raw) . "')");
    }
    $stmt->close();

    // Thông báo cho user: đơn đã thanh toán thành công
    $conn->query("CREATE TABLE IF NOT EXISTS tbl_order_notification (
      id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      order_code varchar(20) NOT NULL,
      user_id int(10) UNSIGNED NOT NULL,
      message varchar(255) NOT NULL,
      is_read tinyint(1) DEFAULT 0,
      created_at datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      KEY user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $uid = (int) $order['user_id'];
    $notif_msg = "Đơn " . $order_code . " đã thanh toán thành công.";
    $ins = $conn->prepare("INSERT INTO tbl_order_notification (order_code, user_id, message) VALUES (?, ?, ?)");
    if ($ins) {
        $ins->bind_param("sis", $order_code, $uid, $notif_msg);
        $ins->execute();
        $ins->close();
    }
}

$returnData = ['RspCode' => '00', 'Message' => 'Confirm Success'];
echo json_encode($returnData);
