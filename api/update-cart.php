<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/constants.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để sử dụng giỏ hàng']);
    exit;
}

$cart_id = isset($_POST['cart_id']) ? trim($_POST['cart_id']) : '';
$quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;
$size_id = isset($_POST['size_id']) ? (int) $_POST['size_id'] : 0;
$side_dish_ids = [];
if (isset($_POST['side_dish_ids']) && is_string($_POST['side_dish_ids'])) {
    $side_dish_ids = array_map('intval', array_filter(explode(',', $_POST['side_dish_ids'])));
}
$note = isset($_POST['note']) ? trim($_POST['note']) : '';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

foreach ($_SESSION['cart'] as $index => $item) {
    if (isset($item['cart_id']) && $item['cart_id'] === $cart_id) {
        if ($quantity <= 0) {
            array_splice($_SESSION['cart'], $index, 1);
        } else {
            $_SESSION['cart'][$index]['qty'] = max(1, $quantity);
            $_SESSION['cart'][$index]['note'] = $note;
            $_SESSION['cart'][$index]['size_id'] = $size_id;
            $_SESSION['cart'][$index]['side_dish_ids'] = $side_dish_ids;
        }
        echo json_encode(['success' => true, 'message' => 'Đã cập nhật']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Không tìm thấy món trong giỏ']);
