<?php
ob_start();
require_once __DIR__ . '/../config/constants.php';
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

// Chuyển luồng: chat từ admin sẽ hiển thị ở "Thông báo" (tbl_order_notification),
// nên không hiển thị badge số ở mục Chat.
$unread_count = 0;

echo json_encode(['success' => true, 'unread_count' => $unread_count]);
