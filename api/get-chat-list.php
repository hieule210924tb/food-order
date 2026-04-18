<?php
ob_start();
require_once __DIR__ . '/../config/constants.php';
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập Admin']);
    exit;
}

$adminId = (int) $_SESSION['admin_id'];

// Lấy danh sách user đang có chat + số tin nhắn người dùng chưa được admin đọc
$sql = "
SELECT
    c.user_id,
    u.full_name AS user_name,
    SUM(CASE
            WHEN c.sender_type = 'user' AND (c.is_read = 0 OR c.is_read IS NULL)
            THEN 1
            ELSE 0
        END) AS unread_count,
    MAX(c.id) AS last_message_id
FROM tbl_chat c
LEFT JOIN tbl_user u ON u.id = c.user_id
WHERE c.user_id IS NOT NULL
  AND (c.admin_id = ? OR c.admin_id IS NULL)
GROUP BY c.user_id
ORDER BY last_message_id DESC
LIMIT 50
";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn']);
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $adminId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$chatList = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $chatList[] = [
            'user_id' => (int) $row['user_id'],
            'user_name' => (string) ($row['user_name'] ?? ''),
            'unread_count' => (int) ($row['unread_count'] ?? 0),
        ];
    }
}

mysqli_stmt_close($stmt);

echo json_encode(['success' => true, 'chat_list' => $chatList]);

