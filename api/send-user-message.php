<?php
ob_start();
require_once __DIR__ . '/../config/constants.php';
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để chat']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$message = isset($_POST['message']) ? trim((string) $_POST['message']) : '';
$orderCode = isset($_POST['order_code']) ? trim((string) $_POST['order_code']) : '';

if ($message === '') {
    echo json_encode(['success' => false, 'message' => 'Tin nhắn không được để trống']);
    exit;
}

if (mb_strlen($message) > 2000) {
    echo json_encode(['success' => false, 'message' => 'Tin nhắn quá dài']);
    exit;
}

if ($orderCode !== '') {
    $message = "Mã đơn: {$orderCode} - {$message}";
}

// Tin từ user: admin_id = NULL (hội thoại chung theo user_id, admin trả lời sẽ gắn admin_id thật)
$sql = "INSERT INTO tbl_chat (user_id, admin_id, sender_type, message, is_read) VALUES (?, NULL, 'user', ?, 0)";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn']);
    exit;
}

mysqli_stmt_bind_param($stmt, 'is', $userId, $message);
if (mysqli_stmt_execute($stmt)) {
    $newId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true, 'id' => (int) $newId]);
    exit;
}

$err = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);
echo json_encode(['success' => false, 'message' => 'Lỗi lưu tin nhắn: ' . $err]);
