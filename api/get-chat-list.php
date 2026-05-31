<?php
ob_start();
require_once __DIR__ . '/../config/constants.php';
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập Admin']);
    exit;
}

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
GROUP BY c.user_id, u.full_name
ORDER BY last_message_id DESC
LIMIT 50
";

$res = mysqli_query($conn, $sql);
if (!$res) {
    echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn']);
    exit;
}

$chatList = [];
while ($row = mysqli_fetch_assoc($res)) {
    $chatList[] = [
        'user_id' => (int) $row['user_id'],
        'user_name' => (string) ($row['user_name'] ?? ''),
        'unread_count' => (int) ($row['unread_count'] ?? 0),
    ];
}

echo json_encode(['success' => true, 'chat_list' => $chatList]);
