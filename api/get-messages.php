<?php
ob_start();
require_once __DIR__ . '/../config/constants.php';
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập Admin']);
    exit;
}

if (!isset($_GET['user_id']) || !isset($_GET['last_id'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu tham số']);
    exit;
}

$adminId = (int) $_SESSION['admin_id'];
$userId = (int) $_GET['user_id'];
$lastId = (int) $_GET['last_id'];

$sql = "
SELECT
    c.id,
    c.sender_type,
    c.message,
    c.created_at,
    c.is_read,
    u.full_name AS user_name,
    a.full_name AS admin_name
FROM tbl_chat c
LEFT JOIN tbl_user u ON u.id = c.user_id
LEFT JOIN tbl_admin a ON a.id = c.admin_id
WHERE c.user_id = ?
  AND c.id > ?
  AND (c.admin_id = ? OR c.admin_id IS NULL)
ORDER BY c.id ASC
";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn']);
    exit;
}

mysqli_stmt_bind_param($stmt, 'iii', $userId, $lastId, $adminId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

// Đánh dấu tin nhắn người dùng đã được Admin đọc
$upd = "
UPDATE tbl_chat
SET is_read = 1
WHERE user_id = ?
  AND sender_type = 'user'
  AND (admin_id = ? OR admin_id IS NULL)
  AND (is_read = 0 OR is_read IS NULL)
";
$stmtUpd = mysqli_prepare($conn, $upd);
if ($stmtUpd) {
    mysqli_stmt_bind_param($stmtUpd, 'ii', $userId, $adminId);
    mysqli_stmt_execute($stmtUpd);
    mysqli_stmt_close($stmtUpd);
}

$messages = [];
while ($row = mysqli_fetch_assoc($res)) {
    $messages[] = [
        'id' => (int) $row['id'],
        'sender_type' => $row['sender_type'] ?? '',
        'message' => $row['message'] ?? '',
        'created_at' => $row['created_at'] ?? '',
        'is_read' => (int) ($row['is_read'] ?? 0),
        'user_name' => $row['user_name'] ?? '',
        'admin_name' => $row['admin_name'] ?? '',
    ];
}

mysqli_stmt_close($stmt);

echo json_encode(['success' => true, 'messages' => $messages]);

