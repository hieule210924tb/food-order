<?php
include('../config/constants.php');

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = SITEURL . 'user/request-refund.php' . (isset($_GET['order_code']) ? '?order_code=' . urlencode($_GET['order_code']) : '');
    header('location:' . SITEURL . 'user/login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$order_code = isset($_GET['order_code']) ? trim($_GET['order_code']) : '';

if ($order_code === '' || !preg_match('/^ORD[\w]+$/', $order_code)) {
    header('location:' . SITEURL . 'user/order-history.php');
    exit;
}

$stmt = $conn->prepare("SELECT id, order_code, user_id, food, total, order_date, status, customer_name FROM tbl_order WHERE order_code = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("si", $order_code, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    $_SESSION['refund_error'] = 'Không tìm thấy đơn hàng hoặc không thuộc quyền của bạn.';
    header('location:' . SITEURL . 'user/order-history.php');
    exit;
}

if ($order['status'] !== 'Delivered') {
    $_SESSION['refund_error'] = 'Chỉ đơn hàng đã giao mới được yêu cầu hoàn tiền.';
    header('location:' . SITEURL . 'user/order-history.php');
    exit;
}

$stmt = $conn->prepare("SELECT id, payment_method FROM tbl_payment WHERE order_code = ? AND payment_status IN ('success','paid') AND payment_method IN ('momo','vnpay') LIMIT 1");
$stmt->bind_param("s", $order_code);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$payment) {
    $_SESSION['refund_error'] = 'Chỉ đơn thanh toán MoMo hoặc VNPay mới được yêu cầu hoàn tiền.';
    header('location:' . SITEURL . 'user/order-history.php');
    exit;
}

$has_refund = @$conn->query("SELECT 1 FROM tbl_refund WHERE order_code = '" . $conn->real_escape_string($order_code) . "' AND refund_status IN ('pending','processing','completed') LIMIT 1");
if ($has_refund && $has_refund->num_rows > 0) {
    $_SESSION['refund_error'] = 'Đơn này đã có yêu cầu hoàn tiền.';
    header('location:' . SITEURL . 'user/order-history.php');
    exit;
}

function formatPrice($num) {
    return number_format($num, 0, ',', '.') . ' đ';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu hoàn tiền - WowFood</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/order-history.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    .refund-page { max-width: 560px; margin: 0 auto; padding: 100px 24px 60px; }
    .refund-box { background: #fff; border-radius: 14px; padding: 24px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border: 1px solid #ecf0f1; }
    .refund-box h2 { margin: 0 0 8px 0; font-size: 1.25rem; color: #2d3436; }
    .refund-box .sub { color: #636e72; font-size: 13px; margin-bottom: 20px; }
    .refund-order-info { background: #f8f9fa; border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; font-size: 14px; }
    .refund-order-info p { margin: 0 0 6px 0; }
    .refund-form label { display: block; font-weight: 600; color: #2d3436; margin-bottom: 6px; font-size: 14px; }
    .refund-form textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; resize: vertical; min-height: 100px; box-sizing: border-box; }
    .refund-form .btn-submit { margin-top: 16px; padding: 12px 24px; border-radius: 8px; border: none; background: #27ae60; color: #fff; font-weight: 600; font-size: 14px; cursor: pointer; }
    .refund-form .btn-submit:hover { background: #219a52; }
    .refund-form .btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }
    .back-link { display: inline-block; margin-top: 16px; color: #ff6b81; font-weight: 600; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
<?php include('../partials-front/menu.php'); ?>

<div class="order-history-page refund-page">
    <div class="order-history-breadcrumb">
        <a href="<?php echo SITEURL; ?>">Trang chủ</a>
        <span class="sep">/</span>
        <a href="<?php echo SITEURL; ?>user/order-history.php">Lịch sử đơn hàng</a>
        <span class="sep">/</span>
        <span class="current">Yêu cầu hoàn tiền</span>
    </div>

    <div class="refund-box">
        <h2>🔄Tạo yêu cầu hoàn tiền</h2>
        <p class="sub">Điền lý do hoàn tiền. Admin sẽ xem xét và cập nhật trạng thái đơn (đang hoàn / đã hoàn).</p>

        <div class="refund-order-info">
            <p><strong>Mã đơn:</strong> <?php echo htmlspecialchars($order['order_code']); ?></p>
            <p><strong>Tổng tiền:</strong> <?php echo formatPrice($order['total']); ?></p>
            <p><strong>Thanh toán:</strong> <?php echo strtoupper($payment['payment_method']); ?></p>
        </div>

        <form id="refundForm" class="refund-form" method="post" action="">
            <input type="hidden" name="order_code" value="<?php echo htmlspecialchars($order_code); ?>">
            <label for="refund_reason">Lý do hoàn tiền *</label>
            <textarea id="refund_reason" name="refund_reason" required placeholder="Ví dụ: Sản phẩm không đúng mô tả, giao thiếu món, cần hủy sau khi nhận hàng..."></textarea>
            <button type="submit" class="btn-submit" id="btnSubmit">Gửi yêu cầu hoàn tiền</button>
        </form>

        <a href="<?php echo SITEURL; ?>user/order-history.php" class="back-link"><i class="bi bi-arrow-left"></i> Quay lại lịch sử đơn hàng</a>
    </div>
</div>

<?php include('../partials-front/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function() {
    var form = document.getElementById('refundForm');
    var btn = document.getElementById('btnSubmit');
    var SITEURL = '<?php echo SITEURL; ?>';
    if (!form || !btn) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        btn.disabled = true;
        btn.textContent = 'Đang gửi...';
        var fd = new FormData(form);
        fetch(SITEURL + 'api/user-request-refund.php', { method: 'POST', body: fd })
            .then(function(r) {
                return r.text().then(function(t) {
                    try {
                        if (r.ok || r.headers.get('Content-Type') && r.headers.get('Content-Type').indexOf('json') !== -1)
                            return JSON.parse(t);
                        return { success: false, message: t && t.length < 200 ? t : 'Phản hồi không hợp lệ.' };
                    } catch (e) {
                        return { success: false, message: t && t.length < 200 ? t : 'Phản hồi không hợp lệ.' };
                    }
                });
            })
            .then(function(data) {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công',
                        text: data.message || 'Yêu cầu hoàn tiền đã được gửi. Admin sẽ xem xét.',
                        confirmButtonColor: '#27ae60'
                    }).then(function() {
                        window.location.href = SITEURL + 'user/order-history.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Không thể tạo yêu cầu',
                        text: data.message || 'Có lỗi xảy ra. Vui lòng thử lại.',
                        confirmButtonColor: '#ff6b81'
                    });
                    btn.disabled = false;
                    btn.textContent = 'Gửi yêu cầu hoàn tiền';
                }
            })
            .catch(function(err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Kết nối lỗi hoặc máy chủ không phản hồi. Vui lòng thử lại.',
                    confirmButtonColor: '#ff6b81'
                });
                btn.disabled = false;
                btn.textContent = 'Gửi yêu cầu hoàn tiền';
            });
    });
})();
</script>
</body>
</html>
