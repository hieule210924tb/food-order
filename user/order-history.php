<?php
include('../config/constants.php');

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = SITEURL . 'user/order-history.php';
    header('location:' . SITEURL . 'user/login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$conn->query("UPDATE tbl_order o
    LEFT JOIN tbl_payment p
      ON p.order_code = o.order_code
     AND p.payment_method IN ('momo','vnpay')
     AND p.payment_status IN ('success','paid')
    SET o.status = 'Payment Failed'
    WHERE o.user_id = " . $user_id . "
      AND o.status = 'Pending Payment'
      AND o.order_date <= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
      AND p.id IS NULL");

$conn->query("UPDATE tbl_payment p
    JOIN tbl_order o ON o.order_code = p.order_code
    SET p.payment_status = 'failed', p.updated_at = NOW()
    WHERE o.user_id = " . $user_id . "
      AND o.status = 'Payment Failed'
      AND p.payment_method IN ('momo','vnpay')
      AND p.payment_status = 'pending'");

$has_order_details = false;
$r = @$conn->query("SHOW COLUMNS FROM tbl_order LIKE 'order_details'");
if ($r && $r->num_rows > 0) $has_order_details = true;
$sql = "SELECT id, order_code, user_id, food, price, qty, total, order_date, status, customer_name, customer_contact, customer_email, customer_address";
if ($has_order_details) $sql .= ", order_details";
$sql .= " FROM tbl_order WHERE user_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

// Chỉ cho hoàn tiền khi thanh toán MoMo/VNPay và đơn đã giao: lấy payment_method + đã có yêu cầu hoàn tiền chưa
$payment_method_by_order = [];
$payment_method_pending_by_order = [];
$order_codes_with_refund = [];
if (!empty($orders)) {
    $order_codes = array_column($orders, 'order_code');
    $placeholders = implode(',', array_fill(0, count($order_codes), '?'));
    $stmtPm = $conn->prepare("SELECT order_code, payment_method, payment_status, created_at
        FROM tbl_payment
        WHERE order_code IN ($placeholders) AND payment_method IN ('momo','vnpay')
        ORDER BY created_at DESC, id DESC");
    if ($stmtPm) {
        $stmtPm->bind_param(str_repeat('s', count($order_codes)), ...$order_codes);
        $stmtPm->execute();
        $resPm = $stmtPm->get_result();
        while ($row = $resPm->fetch_assoc()) {
            if (!isset($payment_method_pending_by_order[$row['order_code']]) && $row['payment_status'] === 'pending') {
                $payment_method_pending_by_order[$row['order_code']] = $row['payment_method'];
            }
            if (!isset($payment_method_by_order[$row['order_code']]) && in_array($row['payment_status'], ['success', 'paid'], true)) {
                $payment_method_by_order[$row['order_code']] = $row['payment_method'];
            }
        }
        $stmtPm->close();
    }
    $has_refund_table = @$conn->query("SHOW TABLES LIKE 'tbl_refund'")->num_rows > 0;
    if ($has_refund_table) {
        $stmt2 = $conn->prepare("SELECT order_code FROM tbl_refund WHERE user_id = ? AND order_code IN ($placeholders) AND refund_status IN ('pending','processing','completed')");
        if ($stmt2) {
            $stmt2->bind_param("i" . str_repeat('s', count($order_codes)), $user_id, ...$order_codes);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            while ($row = $res2->fetch_assoc()) {
                $order_codes_with_refund[] = $row['order_code'];
            }
            $stmt2->close();
        }
    }
}

function formatDetailPrice($n) {
    return number_format((float)$n, 0, ',', '.') . ' ₫';
}
function renderOrderDetailText($order_details_json, $food_fallback) {
    if (empty($order_details_json)) return $food_fallback;
    $items = @json_decode($order_details_json, true);
    if (!is_array($items)) return $food_fallback;
    $lines = [];
    foreach ($items as $item) {
        $title = isset($item['title']) ? $item['title'] : '';
        $qty = isset($item['qty']) ? (int)$item['qty'] : 1;
        $lines[] = $title . ' x ' . $qty;
        if (!empty($item['size_name']) && isset($item['size_add'])) {
            $lines[] = $item['size_name'] . ' (+' . formatDetailPrice($item['size_add']) . ')';
        }
        if (!empty($item['sides']) && is_array($item['sides'])) {
            foreach ($item['sides'] as $s) {
                $name = isset($s['name']) ? $s['name'] : '';
                $price = isset($s['price']) ? (float)$s['price'] : 0;
                if ($name !== '') $lines[] = $name . ' (+' . formatDetailPrice($price) . ')';
            }
        }
    }
    return implode("\n", $lines);
}

function formatPrice($num) {
    return number_format($num, 0, ',', '.') . ' đ';
}

function statusLabel($status) {
    $map = [
        'Pending' => 'Đã đặt - Chờ xác nhận',
        'Pending Payment' => 'Chờ thanh toán',
        'Payment Failed' => 'Thanh toán thất bại',
        'Confirmed' => 'Đã xác nhận',
        'Ordered' => 'Đã đặt',
        'On Delivery' => 'Đang giao',
        'Delivered' => 'Đã giao',
        'Cancelled' => 'Đã hủy',
        'Returning' => 'Đang hoàn',
        'Returned' => 'Đã hoàn',
    ];
    return $map[$status] ?? $status;
}

function statusClass($status) {
    $map = [
        'Pending' => 'status-pending',
        'Pending Payment' => 'status-pending',
        'Payment Failed' => 'status-cancelled',
        'Confirmed' => 'status-ordered',
        'Ordered' => 'status-ordered',
        'On Delivery' => 'status-delivery',
        'Delivered' => 'status-delivered',
        'Cancelled' => 'status-cancelled',
        'Returning' => 'status-delivery',
        'Returned' => 'status-delivered',
    ];
    return $map[$status] ?? 'status-default';
}

function canUserCancel($status) {
    return in_array($status, ['Pending', 'Pending Payment'], true);
}
function canUserPayAgain($status, $order_date_str) {
    if ($status !== 'Pending Payment') return false;
    $order_time = strtotime($order_date_str);
    if (!$order_time) return false;
    return (time() - $order_time) < 600; // 10 phút
}

function canUserRequestRefund($status, $order_code, $payment_method_by_order, $order_codes_with_refund) {
    return $status === 'Delivered'
        && isset($payment_method_by_order[$order_code])
        && !in_array($order_code, $order_codes_with_refund, true);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đơn hàng - WowFood</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/order-history.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include('../partials-front/menu.php'); ?>

<div class="order-history-page">
    <div class="order-history-breadcrumb">
        <a href="<?php echo SITEURL; ?>">Trang chủ</a>
        <span class="sep">/</span>
        <span class="current">Lịch sử đơn hàng</span>
    </div>

    <h1 class="order-history-title">
        <span class="order-history-title-icon"><i class="bi bi-list-ul"></i></span>
        Lịch sử đơn hàng
    </h1>

    <?php if (!empty($_GET['order_code']) && preg_match('/^ORD[\w]+$/', $_GET['order_code'])): ?>
    <div class="order-history-success-msg" role="alert">
        <i class="bi bi-check-circle-fill"></i> Đơn hàng <strong><?php echo htmlspecialchars($_GET['order_code']); ?></strong> đã được tạo. Chúng tôi sẽ liên hệ bạn sớm.
    </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['refund_error'])): ?>
    <div class="order-history-success-msg" style="background:#fff3cd;border-color:#ffc107;color:#856404;" role="alert">
        <?php echo htmlspecialchars($_SESSION['refund_error']); unset($_SESSION['refund_error']); ?>
    </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="order-history-empty">
            <div class="empty-icon"><i class="bi bi-box-seam"></i></div>
            <h2>Chưa có đơn hàng nào</h2>
            <p>Đơn hàng của bạn sẽ hiển thị tại đây sau khi đặt hàng.</p>
            <a href="<?php echo SITEURL; ?>food.php" class="btn-browse">Xem thực đơn</a>
        </div>
    <?php else: ?>
        <div class="order-history-list">
            <?php foreach ($orders as $order):
                $order_date = strtotime($order['order_date']);
                $date_str = date('d/m/Y', $order_date);
                $time_str = date('H:i', $order_date);
                $status = $order['status'] ?? 'Pending';
                $order_details_json = ($has_order_details && isset($order['order_details'])) ? $order['order_details'] : '';
                $detail_text = renderOrderDetailText($order_details_json, $order['food'] ?? '');
            ?>
            <div class="order-card" data-order-code="<?php echo htmlspecialchars($order['order_code']); ?>">
                <div class="order-card-header">
                    <div class="order-code-wrap">
                        <span class="order-code"><?php echo htmlspecialchars($order['order_code']); ?></span>
                        <span class="order-date"><?php echo $date_str; ?> lúc <?php echo $time_str; ?></span>
                    </div>
                    <span class="order-status <?php echo statusClass($status); ?>"><?php echo htmlspecialchars(statusLabel($status)); ?></span>
                </div>
                <div class="order-card-body">
                    <div class="order-detail-block">
                        <button type="button" class="order-detail-toggle" aria-expanded="false" style="background:none;border:none;padding:0;font-weight:600;font-size:14px;color:#ff6b81;cursor:pointer;margin-bottom:6px;display:flex;align-items:center;gap:6px;"><span class="toggle-icon"><i class="bi bi-chevron-right"></i></span> Chi tiết đơn hàng</button>
                        <div class="order-detail-content" style="font-size:14px;line-height:1.7;color:#2d3436;white-space:pre-line;margin-bottom:12px;padding:12px;background:#f8f9fa;border-radius:8px;display:none;"><?php echo nl2br(htmlspecialchars($detail_text)); ?></div>
                    </div>
                    <div class="order-address-detail" style="margin-bottom:12px;padding:10px 12px;background:#fff3f4;border-radius:8px;font-size:14px;">
                        <p style="margin:0 0 4px 0;"><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                        <p style="margin:0 0 4px 0;"><strong>SĐT:</strong> <?php echo htmlspecialchars($order['customer_contact']); ?></p>
                        <p style="margin:0;"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
                    </div>
                    <div class="order-meta">
                        <span class="order-total"><?php echo formatPrice($order['total']); ?></span>
                    </div>
                    <?php if (canUserCancel($status)): ?>
                    <div class="order-cancel-wrap">
                        <button type="button" class="btn-cancel-order" data-order-code="<?php echo htmlspecialchars($order['order_code']); ?>">Hủy đơn</button>
                    </div>
                    <?php endif; ?>
                    <?php if (canUserPayAgain($status, $order['order_date']) && isset($payment_method_pending_by_order[$order['order_code']])): ?>
                    <div class="order-cancel-wrap" style="margin-top:8px;">
                        <button type="button"
                                class="btn-pay-order"
                                data-order-code="<?php echo htmlspecialchars($order['order_code']); ?>"
                                data-payment-method="<?php echo htmlspecialchars($payment_method_pending_by_order[$order['order_code']]); ?>"
                                style="background:#2d9cdb;color:#fff;border:none;border-radius:8px;padding:8px 12px;cursor:pointer;font-weight:600;">
                            Thanh toán lại
                        </button>
                    </div>
                    <?php endif; ?>
                    <div class="order-support-wrap" style="margin-top:12px;padding-top:12px;border-top:1px solid #eee;display:flex;flex-wrap:wrap;align-items:center;gap:10px;">
                        <a href="<?php echo SITEURL; ?>user/chat.php?order_code=<?php echo urlencode($order['order_code']); ?>" class="order-chat-link" style="color:#ff6b81;font-weight:600;text-decoration:none;">💬 Chat hỗ trợ đơn hàng</a>
                        <?php if (canUserRequestRefund($status, $order['order_code'], $payment_method_by_order, $order_codes_with_refund)): ?>
                        <a href="<?php echo SITEURL; ?>user/request-refund.php?order_code=<?php echo urlencode($order['order_code']); ?>" class="btn-request-refund" style="display:inline-block;padding:6px 14px;border-radius:8px;background:#27ae60;color:#fff;font-weight:600;font-size:13px;text-decoration:none;">🔄 Tạo yêu cầu hoàn tiền</a>
                        <?php elseif (in_array($order['order_code'], $order_codes_with_refund, true)): ?>
                        <span style="color:#27ae60;font-size:13px;"><i class="bi bi-check-circle-fill"></i> Đã gửi yêu cầu hoàn tiền</span>
                        <?php else: ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include('../partials-front/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function() {
    var SITEURL = '<?php echo SITEURL; ?>';

    document.querySelectorAll('.order-detail-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var block = this.closest('.order-detail-block');
            var content = block ? block.querySelector('.order-detail-content') : null;
            if (!content) return;
            var open = content.style.display !== 'none';
            content.style.display = open ? 'none' : 'block';
            this.setAttribute('aria-expanded', open ? 'false' : 'true');
            var icon = this.querySelector('.toggle-icon');
            if (icon) icon.innerHTML = open ? '<i class="bi bi-chevron-right"></i>' : '<i class="bi bi-chevron-down"></i>';
            this.innerHTML = (open ? '<span class="toggle-icon"><i class="bi bi-chevron-right"></i></span> Chi tiết đơn hàng' : '<span class="toggle-icon"><i class="bi bi-chevron-down"></i></span> Thu gọn');
        });
    });

    document.querySelectorAll('.btn-cancel-order').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var code = this.getAttribute('data-order-code');
            if (!code) return;
            Swal.fire({
                title: 'Xác nhận hủy đơn',
                text: 'Bạn có chắc muốn hủy đơn hàng ' + code + '? Chỉ có thể hủy khi đơn đang chờ xác nhận.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4757',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Hủy đơn',
                cancelButtonText: 'Không'
            }).then(function(r) {
                if (!r.isConfirmed) return;
                var fd = new FormData();
                fd.append('order_code', code);
                fetch(SITEURL + 'api/cancel-order.php', { method: 'POST', body: fd })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data.success) {
                            Swal.fire('Thành công', data.message || 'Đã hủy đơn hàng.', 'success').then(function() { location.reload(); });
                        } else {
                            Swal.fire('Lỗi', data.message || 'Không thể hủy đơn.', 'error');
                        }
                    })
                    .catch(function() { Swal.fire('Lỗi', 'Có lỗi xảy ra. Vui lòng thử lại.', 'error'); });
            });
        });
    });

    document.querySelectorAll('.btn-pay-order').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var code = this.getAttribute('data-order-code');
            var method = this.getAttribute('data-payment-method');
            if (!code || !method) return;
            var fd = new FormData();
            fd.append('order_code', code);

            if (method === 'momo') {
                fetch(SITEURL + 'api/momo-create.php', { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(m) {
                        if (m.success && (m.payUrl || m.deeplink)) {
                            window.location.href = m.payUrl || m.deeplink;
                        } else {
                            Swal.fire('Lỗi', m.message || 'Không thể tạo thanh toán MoMo.', 'error');
                        }
                    })
                    .catch(function() { Swal.fire('Lỗi', 'Không thể tạo thanh toán MoMo.', 'error'); });
                return;
            }

            if (method === 'vnpay') {
                fetch(SITEURL + 'api/vnpay-create.php', { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(v) {
                        if (v.success && v.payUrl) {
                            window.location.href = v.payUrl;
                        } else {
                            Swal.fire('Lỗi', v.message || 'Không thể tạo thanh toán VNPay.', 'error');
                        }
                    })
                    .catch(function() { Swal.fire('Lỗi', 'Không thể tạo thanh toán VNPay.', 'error'); });
            }
        });
    });
})();
</script>
</body>
</html>
