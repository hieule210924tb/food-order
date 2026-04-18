<?php
/**
 * Payment Callback Handler
 * Xử lý callback từ các cổng thanh toán (VNPay, MoMo)
 */

include('../config/constants.php');

// Log callback để debug
function logCallback($data) {
    $log_file = __DIR__ . '/../logs/payment_callback.log';
    $log_entry = date('Y-m-d H:i:s') . " - " . json_encode($data) . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// Lấy dữ liệu từ callback
$callback_data = $_POST ?: $_GET;
logCallback($callback_data);

// Xác thực callback (trong thực tế cần verify signature từ cổng thanh toán)
$order_code = $callback_data['order_code'] ?? $callback_data['vnp_TxnRef'] ?? $callback_data['orderId'] ?? '';
$transaction_id = $callback_data['transaction_id'] ?? $callback_data['vnp_TransactionNo'] ?? $callback_data['transId'] ?? '';
$payment_method = $callback_data['payment_method'] ?? 'vnpay';
$status = $callback_data['status'] ?? $callback_data['vnp_ResponseCode'] ?? $callback_data['resultCode'] ?? '';

// Xác định trạng thái thanh toán
$payment_status = 'failed';
$failure_reason = '';

// VNPay response code: 00 = success
if($payment_method == 'vnpay') {
    if($status == '00' || $status == 'success') {
        $payment_status = 'success';
    } else {
        $payment_status = 'failed';
        $failure_reason = 'Mã lỗi: ' . $status;
    }
}
// MoMo response
elseif($payment_method == 'momo') {
    if($status == '0' || $status == 'success') {
        $payment_status = 'success';
    } else {
        $payment_status = 'failed';
        $failure_reason = 'Mã lỗi: ' . $status;
    }
}

if(empty($order_code)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing order_code']);
    exit();
}

// Tìm payment record
$payment_sql = "SELECT * FROM tbl_payment WHERE order_code = ? AND payment_status = 'pending' ORDER BY id DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $payment_sql);
mysqli_stmt_bind_param($stmt, "s", $order_code);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$payment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if(!$payment) {
    http_response_code(404);
    echo json_encode(['error' => 'Payment not found']);
    exit();
}

// Cập nhật payment
$update_payment_sql = "UPDATE tbl_payment SET 
    payment_status = ?,
    transaction_id = ?,
    payment_gateway_response = ?,
    failure_reason = ?,
    paid_at = " . ($payment_status == 'success' ? "NOW()" : "NULL") . ",
    updated_at = NOW()
    WHERE id = ?";

$stmt = mysqli_prepare($conn, $update_payment_sql);
$response_json = json_encode($callback_data);
mysqli_stmt_bind_param($stmt, "ssssi", 
    $payment_status, 
    $transaction_id, 
    $response_json, 
    $failure_reason, 
    $payment['id']
);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Nếu thanh toán thành công, cập nhật order
if($payment_status == 'success') {
    $update_order_sql = "UPDATE tbl_order SET 
        payment_status = 'paid',
        status = 'Ordered'
        WHERE order_code = ? AND payment_status != 'paid'";
    $stmt = mysqli_prepare($conn, $update_order_sql);
    mysqli_stmt_bind_param($stmt, "s", $order_code);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    // Đồng bộ thông báo đơn hàng: xoá pending và tạo success
    try {
        $conn->query("CREATE TABLE IF NOT EXISTS tbl_order_notification (
            id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_code varchar(20) NOT NULL,
            user_id int(10) UNSIGNED NOT NULL,
            message varchar(255) NOT NULL,
            is_read tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY order_code (order_code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $stmtUser = mysqli_prepare($conn, "SELECT user_id FROM tbl_order WHERE order_code = ? LIMIT 1");
        mysqli_stmt_bind_param($stmtUser, "s", $order_code);
        mysqli_stmt_execute($stmtUser);
        $resUser = mysqli_stmt_get_result($stmtUser);
        $orderUser = mysqli_fetch_assoc($resUser);
        mysqli_stmt_close($stmtUser);

        $uid = isset($orderUser['user_id']) ? (int)$orderUser['user_id'] : 0;
        if ($uid > 0) {
            $pendingLike = '%đang chờ thanh toán%';
            $stmtDelNotif = mysqli_prepare($conn, "DELETE FROM tbl_order_notification WHERE order_code = ? AND user_id = ? AND message LIKE ?");
            mysqli_stmt_bind_param($stmtDelNotif, "sis", $order_code, $uid, $pendingLike);
            mysqli_stmt_execute($stmtDelNotif);
            mysqli_stmt_close($stmtDelNotif);

            $notifSuccess = "Đơn " . $order_code . " đã thanh toán thành công.";
            $stmtInsNotif = mysqli_prepare($conn, "
                INSERT INTO tbl_order_notification (order_code, user_id, message)
                SELECT ?, ?, ?
                WHERE NOT EXISTS (
                    SELECT 1 FROM tbl_order_notification
                    WHERE order_code = ? AND user_id = ? AND message = ?
                )
            ");
            mysqli_stmt_bind_param($stmtInsNotif, "sissis", $order_code, $uid, $notifSuccess, $order_code, $uid, $notifSuccess);
            mysqli_stmt_execute($stmtInsNotif);
            mysqli_stmt_close($stmtInsNotif);
        }
    } catch (Throwable $e) {
        // Không chặn luồng nếu lỗi thông báo
    }
    
    // Gửi email thông báo (nếu cần)
    // sendPaymentConfirmationEmail($order_code);
}

// Trả về response cho cổng thanh toán
if($payment_method == 'vnpay') {
    // VNPay cần response HTML
    if($payment_status == 'success') {
        header('Location: ' . SITEURL . 'user/payment-success.php?order_code=' . $order_code);
    } else {
        header('Location: ' . SITEURL . 'user/payment-failed.php?order_code=' . $order_code . '&reason=' . urlencode($failure_reason));
    }
} else {
    // JSON response cho các cổng khác
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $payment_status,
        'order_code' => $order_code,
        'transaction_id' => $transaction_id,
        'message' => $payment_status == 'success' ? 'Payment successful' : 'Payment failed'
    ]);
}
?>

