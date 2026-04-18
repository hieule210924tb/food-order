<?php
ob_start();
require_once __DIR__ . '/../config/constants.php';
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
    exit;
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để sử dụng voucher.']);
    exit;
}

$voucher_code = isset($_POST['voucher_code']) ? trim((string) $_POST['voucher_code']) : '';
if ($voucher_code === '') {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã voucher.']);
    exit;
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống.']);
    exit;
}

// Ensure voucher table exists.
$createSql = "CREATE TABLE IF NOT EXISTS tbl_voucher (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    value DECIMAL(10,2) NOT NULL DEFAULT 0,
    min_order DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_discount DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    valid_from DATETIME NULL,
    valid_to DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY code_idx (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($createSql);

// Load sizes/sides for price calc.
$sizes = [];
$side_dishes = [];
$res = @$conn->query("SELECT id, name, price_add FROM tbl_size ORDER BY sort_order ASC, id ASC");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) $sizes[$row['id']] = ['name' => $row['name'], 'price_add' => (float) $row['price_add']];
}
if (empty($sizes)) {
    $sizes = [1 => ['name' => 'Nho', 'price_add' => 0], 2 => ['name' => 'Vua', 'price_add' => 5], 3 => ['name' => 'Lon', 'price_add' => 10]];
}
$res = @$conn->query("SELECT id, name, price FROM tbl_side_dish ORDER BY sort_order ASC, id ASC");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) $side_dishes[$row['id']] = ['name' => $row['name'], 'price' => (float) $row['price']];
}
if (empty($side_dishes)) {
    $side_dishes = [1 => ['name' => 'Trung op la', 'price' => 8], 2 => ['name' => 'Nem ran', 'price' => 10], 3 => ['name' => 'Khoai tay chien', 'price' => 12], 4 => ['name' => 'Salad', 'price' => 6], 5 => ['name' => 'Nuoc ngot', 'price' => 5], 6 => ['name' => 'Tra da', 'price' => 3]];
}

$cart_total = 0;
foreach ($_SESSION['cart'] as $cart_row) {
    $food_id = (int) ($cart_row['food_id'] ?? 0);
    $qty = max(1, (int) ($cart_row['qty'] ?? 1));
    $size_id = (int) (isset($cart_row['size_id']) ? $cart_row['size_id'] : 1);
    $side_dish_ids = (isset($cart_row['side_dish_ids']) && is_array($cart_row['side_dish_ids'])) ? $cart_row['side_dish_ids'] : [];
    $stmt = $conn->prepare("SELECT id, title, price FROM tbl_food WHERE id = ? AND active = 'Yes'");
    $stmt->bind_param("i", $food_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $food = $result->fetch_assoc();
    $stmt->close();
    if ($food) {
        $base_price = (float) $food['price'];
        $size_add = isset($sizes[$size_id]) ? $sizes[$size_id]['price_add'] : 0;
        $side_total = 0;
        foreach ((array) $side_dish_ids as $sid) {
            $side_total += isset($side_dishes[$sid]) ? $side_dishes[$sid]['price'] : 0;
        }
        $unit_price = $base_price + $size_add + $side_total;
        $subtotal = $unit_price * $qty;
        $cart_total += $subtotal;
    }
}

$now = date('Y-m-d H:i:s');
$stmt = $conn->prepare("SELECT code, type, value, min_order, max_discount, valid_from, valid_to FROM tbl_voucher WHERE status = 'active' AND UPPER(code) = UPPER(?) AND (valid_from IS NULL OR valid_from <= ?) AND (valid_to IS NULL OR valid_to >= ?) LIMIT 1");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Lỗi xử lý voucher.']);
    exit;
}
$stmt->bind_param('sss', $voucher_code, $now, $now);
$stmt->execute();
$voucher = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$voucher) {
    echo json_encode(['success' => false, 'message' => 'Voucher không hợp lệ hoặc đã hết hạn.']);
    exit;
}

$min_order = (float) $voucher['min_order'];
if ($cart_total < $min_order) {
    echo json_encode(['success' => false, 'message' => 'Đơn hàng chưa đạt tối thiểu để dùng voucher.']);
    exit;
}

$discount = 0;
if ($voucher['type'] === 'percent') {
    $discount = $cart_total * ((float) $voucher['value'] / 100);
    if ((float) $voucher['max_discount'] > 0) {
        $discount = min($discount, (float) $voucher['max_discount']);
    }
} else {
    $discount = (float) $voucher['value'];
}
if ($discount < 0) $discount = 0;
if ($discount > $cart_total) $discount = $cart_total;

echo json_encode([
    'success' => true,
    'message' => 'Đã áp dụng voucher.',
    'code' => (string) $voucher['code'],
    'discount' => (float) $discount,
    'total_before' => (float) $cart_total,
    'total_after' => (float) max(0, $cart_total - $discount)
]);
