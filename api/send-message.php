<?php
ob_start();
require_once __DIR__ . '/../config/constants.php';
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập Admin']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

$adminId = (int) $_SESSION['admin_id'];
$message = isset($_POST['message']) ? trim((string) $_POST['message']) : '';
$userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Thiếu user_id']);
    exit;
}

if ($message === '') {
    echo json_encode(['success' => false, 'message' => 'Tin nhắn không được để trống']);
    exit;
}

// (Optional) giới hạn độ dài để tránh spam cực đoan
if (mb_strlen($message) > 2000) {
    echo json_encode(['success' => false, 'message' => 'Tin nhắn quá dài']);
    exit;
}

$sql = "INSERT INTO tbl_chat (user_id, admin_id, sender_type, message, is_read) VALUES (?, ?, 'admin', ?, 0)";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn']);
    exit;
}

mysqli_stmt_bind_param($stmt, 'iis', $userId, $adminId, $message);
if (mysqli_stmt_execute($stmt)) {
    $newId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // Đẩy tin nhắn chat sang phần "Thông báo" của user
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

    $notif_order_code = "CHAT";
    $notif_message = "Tin nhắn mới từ Admin: " . $message;
    if (mb_strlen($notif_message) > 240) {
        $notif_message = mb_substr($notif_message, 0, 240) . "...";
    }

    $insNotif = $conn->prepare("INSERT INTO tbl_order_notification (order_code, user_id, message) VALUES (?, ?, ?)");
    if ($insNotif) {
        $insNotif->bind_param("sis", $notif_order_code, $userId, $notif_message);
        $insNotif->execute();
        $insNotif->close();
    }

    echo json_encode(['success' => true, 'id' => (int) $newId]);
    exit;
}

$err = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);
echo json_encode(['success' => false, 'message' => 'Lỗi lưu tin nhắn: ' . $err]);

