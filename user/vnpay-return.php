<?php
require_once __DIR__ . '/../config/constants.php';

if (!isset($_GET['vnp_SecureHash']) || !isset($_GET['vnp_TxnRef'])) {
    header('Location: ' . SITEURL . 'user/order-history.php');
    exit;
}

$vnp_SecureHash = $_GET['vnp_SecureHash'];
$inputData = [];
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) === 'vnp_') {
        $inputData[$key] = $value;
    }
}
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

$order_code = $inputData['vnp_TxnRef'] ?? '';
$vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
$vnp_TransactionNo = $inputData['vnp_TransactionNo'] ?? '';
$vnp_Amount = isset($inputData['vnp_Amount']) ? (int)$inputData['vnp_Amount'] / 100 : 0;

$success = false;
$message = 'Chữ ký không hợp lệ.';

            if ($secureHash === $vnp_SecureHash) {
    $stmt = $conn->prepare("SELECT id, order_code, user_id, total, status FROM tbl_order WHERE order_code = ? LIMIT 1");
    $stmt->bind_param("s", $order_code);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($order) {
        $expectedAmount = (int) round((float)$order['total']);
        if ($vnp_Amount >= $expectedAmount - 1) {
            if (in_array($order['status'], ['Pending Payment', 'Pending', 'Unpaid', 'Ordered'], true)) {
                if ($vnp_ResponseCode === '00') {
                    $stmt = $conn->prepare("UPDATE tbl_order SET status = 'Ordered' WHERE id = ?");
                    $stmt->bind_param("i", $order['id']);
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $conn->prepare("UPDATE tbl_payment SET payment_status = 'success', transaction_id = ?, raw_response = ? WHERE order_code = ? AND payment_method = 'vnpay'");
                    $raw = json_encode($inputData);
                    $stmt->bind_param("sss", $vnp_TransactionNo, $raw, $order_code);
                    $stmt->execute();
                    $stmt->close();

                    // Xoá message "đang chờ thanh toán" và thay bằng "đã thanh toán thành công"
                    try {
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

                        $uid = (int)$order['user_id'];
                        $notifSuccess = "Đơn " . $order_code . " đã thanh toán thành công.";

                        // Xoá mọi thông báo pending theo substring (tránh phụ thuộc đúng dấu câu)
                        $pendingLike = '%đang chờ thanh toán%';
                        $stmtDelNotif = $conn->prepare("DELETE FROM tbl_order_notification WHERE order_code = ? AND user_id = ? AND message LIKE ?");
                        if ($stmtDelNotif) {
                            $stmtDelNotif->bind_param("sis", $order_code, $uid, $pendingLike);
                            $stmtDelNotif->execute();
                            $stmtDelNotif->close();
                        }

                        // Chèn success nếu chưa có
                        $stmtIns = $conn->prepare("
                            INSERT INTO tbl_order_notification (order_code, user_id, message)
                            SELECT ?, ?, ?
                            WHERE NOT EXISTS (
                                SELECT 1 FROM tbl_order_notification
                                WHERE order_code = ? AND user_id = ? AND message = ?
                            )
                        ");
                        if ($stmtIns) {
                            $stmtIns->bind_param("sissis", $order_code, $uid, $notifSuccess, $order_code, $uid, $notifSuccess);
                            $stmtIns->execute();
                            $stmtIns->close();
                        }
                    } catch (Throwable $e) {
                        // Không chặn luồng nếu lỗi notify
                    }

                    if (isset($_SESSION['user_id']) && (int)$order['user_id'] === (int)$_SESSION['user_id']) {
                        $_SESSION['cart'] = [];
                        // Xóa luôn giỏ trong DB để sau đăng xuất/đăng nhập không bị hiện lại
                        try {
                            $uid = (int) $order['user_id'];
                            $conn->query("
                                CREATE TABLE IF NOT EXISTS tbl_cart (
                                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                    user_id INT UNSIGNED NOT NULL,
                                    cart_id VARCHAR(50) NOT NULL,
                                    food_id INT UNSIGNED NOT NULL,
                                    qty INT UNSIGNED NOT NULL DEFAULT 1,
                                    note TEXT NULL,
                                    size_id INT UNSIGNED NOT NULL DEFAULT 0,
                                    side_dish_ids TEXT NULL,
                                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                    UNIQUE KEY uniq_user_cart (user_id, cart_id),
                                    KEY idx_user_id (user_id)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                            ");
                            $stmtDel = $conn->prepare("DELETE FROM tbl_cart WHERE user_id = ?");
                            if ($stmtDel) {
                                $stmtDel->bind_param("i", $uid);
                                $stmtDel->execute();
                                $stmtDel->close();
                            }
                        } catch (Throwable $e) {
                            // Bỏ qua lỗi DB để không chặn luồng
                        }
                    }
                    $success = true;
                    $message = 'Thanh toán VNPay thành công.';
                } else {
                    $message = 'Giao dịch chưa thành công. Mã phản hồi: ' . $vnp_ResponseCode;
                }
            } else {
                $success = true;
                $message = 'Đơn hàng đã được xử lý trước đó.';
            }
        } else {
            $message = 'Số tiền không khớp.';
        }
    } else {
        $message = 'Không tìm thấy đơn hàng.';
    }
}

$page_title = $success ? 'Thanh toán thành công' : 'Thanh toán không thành công';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - WowFood</title>
    <link rel="stylesheet" href="<?php echo SITEURL; ?>css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .vnpay-result { max-width: 520px; margin: 60px auto; padding: 32px; text-align: center; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .vnpay-result h1 { font-size: 1.5rem; margin-bottom: 16px; color: #2f3542; }
        .vnpay-result .msg { margin: 16px 0; color: #57606f; }
        .vnpay-result .order-code { font-weight: 700; color: #ff6b81; }
        .vnpay-result .btn { display: inline-block; margin-top: 20px; padding: 12px 24px; background: #ff6b81; color: #fff; text-decoration: none; border-radius: 8px; }
        .vnpay-result .btn:hover { background: #ff4757; color: #fff; }
        .vnpay-result.success h1 { color: #2ed573; }
        .vnpay-result.fail h1 { color: #ff6b81; }
        .vnpay-result h1 .bi { margin-right: 8px; }
    </style>
</head>
<body>
<?php include(__DIR__ . '/../partials-front/menu.php'); ?>

<div class="vnpay-result <?php echo $success ? 'success' : 'fail'; ?>" id="vnpay-result-box">
    <h1><?php echo $success ? '<i class="bi bi-check-circle-fill"></i> Thanh toán thành công' : 'Thanh toán chưa hoàn tất'; ?></h1>
    <p class="msg"><?php echo htmlspecialchars($message); ?></p>
    <?php if ($order_code): ?>
    <p>Mã đơn hàng: <span class="order-code"><?php echo htmlspecialchars($order_code); ?></span></p>
    <?php endif; ?>
    <a href="<?php echo SITEURL; ?>user/order-history.php" class="btn">Xem đơn hàng</a>
    <a href="<?php echo SITEURL; ?>" class="btn" style="background:#95a5a6;margin-left:8px;">Về trang chủ</a>
</div>

<?php include(__DIR__ . '/../partials-front/footer.php'); ?>
<script>
(function() {
    var success = <?php echo $success ? 'true' : 'false'; ?>;
    var message = <?php echo json_encode($message); ?>;
    var orderCode = <?php echo json_encode($order_code ?? ''); ?>;
    var orderUrl = <?php echo json_encode(SITEURL . 'user/order-history.php'); ?>;

    if (success) {
        document.getElementById('vnpay-result-box').style.display = 'none';
        Swal.fire({
            icon: 'success',
            title: 'Thanh toán thành công',
            html: message + (orderCode ? '<br><br><strong>Mã đơn hàng:</strong> ' + orderCode : ''),
            confirmButtonText: 'Xem đơn hàng',
            confirmButtonColor: '#ff6b81'
        }).then(function() {
            window.location.href = orderUrl;
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Thanh toán chưa hoàn tất',
            text: message,
            confirmButtonText: 'Đóng',
            confirmButtonColor: '#ff6b81'
        });
    }
})();
</script>
</body>
</html>
