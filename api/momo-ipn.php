<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/constants.php';

// IPN thường không chạy được trên localhost (MoMo gọi từ bên ngoài),
// nhưng vẫn triển khai để khi deploy có thể dùng.

$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payload']);
    exit;
}

$partnerCode = MOMO_PARTNER_CODE;
$accessKey = MOMO_ACCESS_KEY;
$secretKey = MOMO_SECRET_KEY;

// verify signature
$rawHash = "accessKey={$accessKey}"
    . "&amount=" . ($data['amount'] ?? '')
    . "&extraData=" . ($data['extraData'] ?? '')
    . "&message=" . ($data['message'] ?? '')
    . "&orderId=" . ($data['orderId'] ?? '')
    . "&orderInfo=" . ($data['orderInfo'] ?? '')
    . "&orderType=" . ($data['orderType'] ?? '')
    . "&partnerCode=" . ($data['partnerCode'] ?? '')
    . "&payType=" . ($data['payType'] ?? '')
    . "&requestId=" . ($data['requestId'] ?? '')
    . "&responseTime=" . ($data['responseTime'] ?? '')
    . "&resultCode=" . ($data['resultCode'] ?? '')
    . "&transId=" . ($data['transId'] ?? '');

$sig = hash_hmac('sha256', $rawHash, $secretKey);
if (!isset($data['signature']) || $data['signature'] !== $sig) {
    echo json_encode(['success' => false, 'message' => 'Invalid signature']);
    exit;
}

$orderId = (string)($data['orderId'] ?? '');
$order_code = $orderId !== '' ? explode('-', $orderId)[0] : '';
if ($order_code === '') {
    echo json_encode(['success' => false, 'message' => 'Missing order_code']);
    exit;
}

// update payment + order
$conn->query("CREATE TABLE IF NOT EXISTS tbl_payment (
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
    UNIQUE KEY uq_order_code_method (order_code, payment_method)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$raw = json_encode($data, JSON_UNESCAPED_UNICODE);
$resultCode = (int)($data['resultCode'] ?? 1);
$status = ($resultCode === 0) ? 'success' : 'failed';
$requestId = (string)($data['requestId'] ?? '');
$transId = (string)($data['transId'] ?? '');

$stmt = $conn->prepare("UPDATE tbl_payment SET request_id=?, order_id=?, transaction_id=?, payment_status=?, raw_response=? WHERE order_code=? AND payment_method='momo'");
if ($stmt) {
    $stmt->bind_param("ssssss", $requestId, $orderId, $transId, $status, $raw, $order_code);
    $stmt->execute();
    $stmt->close();
}

if ($resultCode === 0) {
    $stmt = $conn->prepare("UPDATE tbl_order SET status='Pending' WHERE order_code=? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $order_code);
        $stmt->execute();
        $stmt->close();
    }
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
    $row = $conn->query("SELECT user_id FROM tbl_order WHERE order_code = '" . $conn->real_escape_string($order_code) . "' LIMIT 1")->fetch_assoc();
    if ($row && !empty($row['user_id'])) {
        $uid = (int) $row['user_id'];
        $notif_msg = "Đơn " . $order_code . " đã thanh toán thành công.";
        $ins = $conn->prepare("INSERT INTO tbl_order_notification (order_code, user_id, message) VALUES (?, ?, ?)");
        if ($ins) {
            $ins->bind_param("sis", $order_code, $uid, $notif_msg);
            $ins->execute();
            $ins->close();
        }
    }
}

echo json_encode(['success' => true]);

