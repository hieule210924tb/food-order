<?php
require_once __DIR__ . '/../config/constants.php';

$order_code = isset($_GET['order_code']) ? trim((string)$_GET['order_code']) : '';
$resultCode = isset($_GET['resultCode']) ? (int)$_GET['resultCode'] : 1;
$transId = isset($_GET['transId']) ? trim((string)$_GET['transId']) : '';
$message = isset($_GET['message']) ? trim((string)$_GET['message']) : '';

$success = false;
$uiMessage = 'Thanh toán MoMo chưa hoàn tất.';

if ($order_code !== '') {
    $stmt = $conn->prepare("SELECT id, user_id, status FROM tbl_order WHERE order_code = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $order_code);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } else {
        $order = null;
    }

    if ($order) {
        if ($resultCode === 0) {
            // Trên localhost có thể không nhận được IPN, nên xử lý luôn ở return URL
            if (in_array($order['status'], ['Pending Payment', 'Pending', 'Unpaid', 'Ordered'], true)) {
                $stmt = $conn->prepare("UPDATE tbl_order SET status = 'Ordered' WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $order['id']);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            $raw = json_encode($_GET, JSON_UNESCAPED_UNICODE);
            $stmt = $conn->prepare("UPDATE tbl_payment 
                SET payment_status = 'success', transaction_id = ?, raw_response = ?, updated_at = NOW() 
                WHERE order_code = ? AND payment_method = 'momo'");
            if ($stmt) {
                $stmt->bind_param("sss", $transId, $raw, $order_code);
                $stmt->execute();
                if ($stmt->affected_rows === 0) {
                    $uid = (int)$order['user_id'];
                    $escOrder = $conn->real_escape_string($order_code);
                    $escTrans = $conn->real_escape_string($transId);
                    $escRaw = $conn->real_escape_string($raw);
                    $conn->query("INSERT INTO tbl_payment (order_code, user_id, payment_method, amount, payment_status, transaction_id, raw_response)
                                  VALUES ('{$escOrder}', {$uid}, 'momo', 0, 'success', '{$escTrans}', '{$escRaw}')");
                }
                $stmt->close();
            }

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
                // Bỏ qua lỗi thông báo để không chặn luồng thanh toán
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
            $uiMessage = 'Thanh toán MoMo thành công.';
        } else {
            $uiMessage = $message !== '' ? $message : 'Giao dịch chưa thành công.';
        }
    } else {
        $uiMessage = 'Không tìm thấy đơn hàng.';
    }
} else {
    $uiMessage = 'Thiếu mã đơn hàng khi quay lại từ MoMo.';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Thanh toán thành công' : 'Thanh toán chưa hoàn tất'; ?> - WowFood</title>
    <link rel="stylesheet" href="<?php echo SITEURL; ?>css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include(__DIR__ . '/../partials-front/menu.php'); ?>
<script>
(function() {
    var success = <?php echo $success ? 'true' : 'false'; ?>;
    var msg = <?php echo json_encode($uiMessage); ?>;
    var orderCode = <?php echo json_encode($order_code); ?>;
    var orderUrl = <?php echo json_encode(SITEURL . 'user/order-history.php'); ?>;
    if (success) {
        Swal.fire({
            icon: 'success',
            title: 'Thanh toán thành công',
            html: msg + (orderCode ? '<br><br><strong>Mã đơn hàng:</strong> ' + orderCode : ''),
            confirmButtonColor: '#ff6b81',
            confirmButtonText: 'Xem đơn hàng'
        }).then(function() {
            window.location.href = orderUrl;
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Thanh toán chưa hoàn tất',
            text: msg,
            confirmButtonColor: '#ff6b81',
            confirmButtonText: 'Về lịch sử đơn'
        }).then(function() {
            window.location.href = orderUrl;
        });
    }
})();
</script>
</body>
</html>

