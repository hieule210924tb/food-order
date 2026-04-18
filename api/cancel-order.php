<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
require_once __DIR__ . '/../config/constants.php';
ob_end_clean();

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

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, order_code, user_id, status FROM tbl_order WHERE order_code = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("si", $order_code, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
    exit;
}

if ($order['status'] !== 'Pending' && $order['status'] !== 'Pending Payment') {
    echo json_encode(['success' => false, 'message' => 'Chỉ có thể hủy đơn khi đang chờ xác nhận. Admin đã xác nhận đơn nên không thể hủy.']);
    exit;
}

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

$stmt = $conn->prepare("UPDATE tbl_order SET status = 'Cancelled' WHERE order_code = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("si", $order_code, $user_id);
$stmt->execute();
$stmt->close();

$msg = "Bạn đã hủy đơn hàng " . $order_code . ".";
$ins = $conn->prepare("INSERT INTO tbl_order_notification (order_code, user_id, message) VALUES (?, ?, ?)");
$ins->bind_param("sis", $order_code, $user_id, $msg);
$ins->execute();
$ins->close();

echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng.']);
