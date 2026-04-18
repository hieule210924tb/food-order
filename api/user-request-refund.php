<?php
ob_start();
require_once __DIR__ . '/../config/constants.php';
ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$order_code = isset($_POST['order_code']) ? trim($_POST['order_code']) : '';
$refund_reason = isset($_POST['refund_reason']) ? trim($_POST['refund_reason']) : '';

if ($order_code === '' || $refund_reason === '') {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã đơn và lý do hoàn tiền']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Đơn phải thuộc user, trạng thái Delivered
$stmt = $conn->prepare("SELECT id, order_code, user_id, total, status FROM tbl_order WHERE order_code = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("si", $order_code, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng hoặc không thuộc quyền của bạn']);
    exit;
}

if ($order['status'] !== 'Delivered') {
    echo json_encode(['success' => false, 'message' => 'Chỉ đơn hàng đã giao mới được yêu cầu hoàn tiền']);
    exit;
}

// Phải thanh toán bằng MoMo hoặc VNPay (đã thanh toán thành công)
$stmt = $conn->prepare("SELECT id, payment_method FROM tbl_payment WHERE order_code = ? AND payment_status IN ('success','paid') AND payment_method IN ('momo','vnpay') LIMIT 1");
$stmt->bind_param("s", $order_code);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$payment) {
    echo json_encode(['success' => false, 'message' => 'Chỉ đơn thanh toán MoMo hoặc VNPay mới được yêu cầu hoàn tiền']);
    exit;
}

// Chưa có yêu cầu hoàn tiền nào cho đơn này
$has_refund = $conn->query("SELECT 1 FROM tbl_refund WHERE order_code = '" . $conn->real_escape_string($order_code) . "' AND refund_status IN ('pending','processing','completed') LIMIT 1");
if ($has_refund && $has_refund->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Đơn này đã có yêu cầu hoàn tiền']);
    exit;
}

$payment_id = (int) $payment['id'];
$refund_amount = (float) $order['total'];
$refund_reason_esc = $conn->real_escape_string($refund_reason);
$order_code_esc = $conn->real_escape_string($order_code);

// INSERT trực tiếp để tránh lỗi bind; processed_by = NULL cho yêu cầu từ khách (nếu cột NOT NULL thì thử 0)
$sql = "INSERT INTO tbl_refund (order_code, payment_id, user_id, refund_amount, refund_reason, refund_status, refund_method, processed_by) VALUES ('$order_code_esc', $payment_id, $user_id, $refund_amount, '$refund_reason_esc', 'pending', 'original', NULL)";
if (!$conn->query($sql)) {
    $err = $conn->error;
    if ($err && (strpos($err, 'processed_by') !== false || strpos($err, 'NULL') !== false)) {
        $sql = "INSERT INTO tbl_refund (order_code, payment_id, user_id, refund_amount, refund_reason, refund_status, refund_method, processed_by) VALUES ('$order_code_esc', $payment_id, $user_id, $refund_amount, '$refund_reason_esc', 'pending', 'original', 0)";
        if (!$conn->query($sql)) {
            echo json_encode(['success' => false, 'message' => 'Không thể tạo yêu cầu. ' . ($conn->error ?: 'Vui lòng thử lại.')]);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể tạo yêu cầu. ' . ($err ?: 'Vui lòng thử lại.')]);
        exit;
    }
}

// Thông báo cho user
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
$notif_msg = "Yêu cầu hoàn tiền đơn " . $order_code . " đã được gửi. Admin sẽ xem xét.";
$notif = $conn->prepare("INSERT INTO tbl_order_notification (order_code, user_id, message) VALUES (?, ?, ?)");
if ($notif) {
    $notif->bind_param("sis", $order_code, $user_id, $notif_msg);
    $notif->execute();
    $notif->close();
}

echo json_encode(['success' => true, 'message' => 'Yêu cầu hoàn tiền đã được gửi. Admin sẽ xem xét.']);