<?php
ob_start();
require_once __DIR__ . '/../config/constants.php';
ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

$count = 0;
if (!empty($_SESSION['user_id']) && isset($conn)) {
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
    $user_id = (int) $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM tbl_order_notification WHERE user_id = ? AND (is_read = 0 OR is_read IS NULL)");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) $count = (int) $row['c'];
        $stmt->close();
    }
}
echo json_encode(['success' => true, 'unread_count' => $count]);
