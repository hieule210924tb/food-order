<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/constants.php';

$sizes = [];
$side_dishes = [];

$res = @$conn->query("SELECT id, name, price_add FROM tbl_size ORDER BY sort_order ASC, id ASC");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $sizes[] = ['id' => (int) $row['id'], 'name' => $row['name'], 'price_add' => (float) $row['price_add']];
    }
}
if (empty($sizes)) {
    $sizes = [['id' => 1, 'name' => 'Nhỏ', 'price_add' => 0], ['id' => 2, 'name' => 'Vừa', 'price_add' => 5], ['id' => 3, 'name' => 'Lớn', 'price_add' => 10]];
}

$res = @$conn->query("SELECT id, name, price, type FROM tbl_side_dish ORDER BY sort_order ASC, id ASC");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $side_dishes[] = ['id' => (int) $row['id'], 'name' => $row['name'], 'price' => (float) $row['price'], 'type' => $row['type']];
    }
}
if (empty($side_dishes)) {
    $side_dishes = [
        ['id' => 1, 'name' => 'Trứng ốp la', 'price' => 8, 'type' => 'food'],
        ['id' => 2, 'name' => 'Nem rán', 'price' => 10, 'type' => 'food'],
        ['id' => 3, 'name' => 'Khoai tây chiên', 'price' => 12, 'type' => 'food'],
        ['id' => 4, 'name' => 'Salad', 'price' => 6, 'type' => 'food'],
        ['id' => 5, 'name' => 'Nước ngọt', 'price' => 5, 'type' => 'drink'],
        ['id' => 6, 'name' => 'Trà đá', 'price' => 3, 'type' => 'drink']
    ];
}

echo json_encode([
    'success' => true,
    'sizes' => $sizes,
    'side_dishes' => $side_dishes
]);
