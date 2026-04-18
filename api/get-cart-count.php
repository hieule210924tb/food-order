<?php
ob_start();
require_once __DIR__ . '/../config/constants.php';
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

$count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $count += isset($item['qty']) ? (int) $item['qty'] : 0;
    }
}

echo json_encode(['count' => (int) $count]);
