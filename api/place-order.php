<?php
ob_start();
require_once __DIR__ . '/../config/constants.php';
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đặt hàng']);
    exit;
}

$customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
$customer_contact = isset($_POST['customer_contact']) ? trim($_POST['customer_contact']) : '';
$customer_email = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';
$customer_address = isset($_POST['customer_address']) ? trim($_POST['customer_address']) : '';
$order_note = isset($_POST['order_note']) ? trim($_POST['order_note']) : '';
$payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'cash';
$voucher_code = isset($_POST['voucher_code']) ? strtoupper(trim($_POST['voucher_code'])) : '';
$selected_cart_ids_raw = isset($_POST['selected_cart_ids']) ? trim((string)$_POST['selected_cart_ids']) : '';
$selected_cart_ids = $selected_cart_ids_raw !== '' ? array_values(array_filter(array_map('trim', explode(',', $selected_cart_ids_raw)))) : [];
$allowed_payment = ['cash', 'momo', 'vnpay'];
if (!in_array($payment_method, $allowed_payment, true)) {
    echo json_encode(['success' => false, 'message' => 'Phương thức thanh toán không hợp lệ']);
    exit;
}

if (empty($customer_name) || empty($customer_contact) || empty($customer_email) || empty($customer_address)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin giao hàng']);
    exit;
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống. Vui lòng thêm món trước khi đặt hàng.']);
    exit;
}

$sizes = [];
$side_dishes = [];
$res = @$conn->query("SELECT id, name, price_add FROM tbl_size ORDER BY sort_order ASC, id ASC");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) $sizes[$row['id']] = ['name' => $row['name'], 'price_add' => (float) $row['price_add']];
}
if (empty($sizes)) {
    $sizes = [1 => ['name' => 'Nhỏ', 'price_add' => 0], 2 => ['name' => 'Vừa', 'price_add' => 5], 3 => ['name' => 'Lớn', 'price_add' => 10]];
}
$res = @$conn->query("SELECT id, name, price FROM tbl_side_dish ORDER BY sort_order ASC, id ASC");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) $side_dishes[$row['id']] = ['name' => $row['name'], 'price' => (float) $row['price']];
}
if (empty($side_dishes)) {
    $side_dishes = [1 => ['name' => 'Trứng ốp la', 'price' => 8], 2 => ['name' => 'Nem rán', 'price' => 10], 3 => ['name' => 'Khoai tây chiên', 'price' => 12], 4 => ['name' => 'Salad', 'price' => 6], 5 => ['name' => 'Nước ngọt', 'price' => 5], 6 => ['name' => 'Trà đá', 'price' => 3]];
}
function ensureVoucherTable($conn) {
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
}

$cart_total = 0;
$cart_qty_total = 0;
$food_parts = [];
$order_details_items = [];
$first_price = 0;

foreach ($_SESSION['cart'] as $cart_row) {
    if (!empty($selected_cart_ids)) {
        $cid = isset($cart_row['cart_id']) ? (string)$cart_row['cart_id'] : '';
        if ($cid === '' || !in_array($cid, $selected_cart_ids, true)) continue;
    }
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
        $size_name = isset($sizes[$size_id]) ? $sizes[$size_id]['name'] : 'Nhỏ';
        $sides_list = [];
        $side_total = 0;
        foreach ((array) $side_dish_ids as $sid) {
            if (isset($side_dishes[$sid])) {
                $sides_list[] = ['name' => $side_dishes[$sid]['name'], 'price' => (float) $side_dishes[$sid]['price']];
                $side_total += $side_dishes[$sid]['price'];
            }
        }
        $order_details_items[] = [
            'title'    => $food['title'],
            'qty'      => $qty,
            'size_name'=> $size_name,
            'size_add' => (float) $size_add,
            'sides'    => $sides_list,
        ];
        $unit_price = $base_price + $size_add + $side_total;
        $subtotal = $unit_price * $qty;
        $cart_total += $subtotal;
        $cart_qty_total += $qty;
        if ($first_price == 0) $first_price = $unit_price;
        $food_parts[] = $food['title'] . ' x' . $qty;
    }
}
if ($cart_qty_total <= 0) {
    echo json_encode(['success' => false, 'message' => 'Không có món hợp lệ để đặt hàng. Vui lòng chọn lại.']);
    exit;
}

$food_summary = implode(', ', $food_parts);
if (mb_strlen($food_summary) > 150) {
    $food_summary = mb_substr($food_summary, 0, 147) . '...';
}

$order_code = 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -6));

// ----- Bước 4: Phí giao hàng (ưu tiên tính từ GHN theo to_district_id + to_ward_code) -----
$shipping_fee = 0;
$to_district_id = isset($_POST['to_district_id']) ? (int) $_POST['to_district_id'] : 0;
$to_ward_code = isset($_POST['to_ward_code']) ? trim((string) $_POST['to_ward_code']) : '';
$order_weight_gram = isset($_POST['order_weight_gram']) ? max(1, min(30000, (int) $_POST['order_weight_gram'])) : (defined('GHN_DEFAULT_WEIGHT_GRAM') ? GHN_DEFAULT_WEIGHT_GRAM : 500);

if ($to_district_id > 0 && $to_ward_code !== '' && defined('GHN_TOKEN') && GHN_TOKEN !== '' && defined('GHN_SHOP_ID') && GHN_SHOP_ID > 0) {
    $fee_url = (defined('GHN_API_BASE') ? GHN_API_BASE : 'https://dev-online-gateway.ghn.vn/shiip/public-api') . '/v2/shipping-order/fee';
    $fee_body = [
        'from_district_id' => defined('GHN_FROM_DISTRICT_ID') ? (int) GHN_FROM_DISTRICT_ID : 0,
        'from_ward_code'   => defined('GHN_FROM_WARD_CODE') ? (string) GHN_FROM_WARD_CODE : '',
        'to_district_id'   => $to_district_id,
        'to_ward_code'     => $to_ward_code,
        'weight'           => $order_weight_gram,
        'length' => 20, 'width' => 20, 'height' => 10,
    ];
    $ch = curl_init($fee_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Token: ' . GHN_TOKEN, 'ShopId: ' . (int) GHN_SHOP_ID],
        CURLOPT_POSTFIELDS => json_encode($fee_body),
    ]);
    $fee_res = curl_exec($ch);
    curl_close($ch);
    if ($fee_res !== false) {
        $fee_data = json_decode($fee_res, true);
        if (is_array($fee_data) && isset($fee_data['code']) && (int) $fee_data['code'] === 200 && isset($fee_data['data']['total'])) {
            $shipping_fee = (int) $fee_data['data']['total'];
        }
    }
}
if ($shipping_fee <= 0) {
    $shipping_fee = isset($_POST['shipping_fee']) ? max(0, (float) $_POST['shipping_fee']) : 0;
}

$voucher_code = isset($_POST['voucher_code']) ? trim((string) $_POST['voucher_code']) : '';
$voucher_discount = 0;
if ($voucher_code !== '') {
    ensureVoucherTable($conn);
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
        echo json_encode(['success' => false, 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu để sử dụng voucher.']);
        exit;
    }
    if ($voucher['type'] === 'percent') {
        $voucher_discount = $cart_total * ((float) $voucher['value'] / 100);
        if ((float) $voucher['max_discount'] > 0) {
            $voucher_discount = min($voucher_discount, (float) $voucher['max_discount']);
        }
    } else {
        $voucher_discount = (float) $voucher['value'];
    }
    if ($voucher_discount < 0) $voucher_discount = 0;
    if ($voucher_discount > $cart_total) $voucher_discount = $cart_total;
}

$order_total = max(0, $cart_total - $voucher_discount) + $shipping_fee;

$status = in_array($payment_method, ['momo', 'vnpay'], true) ? 'Pending Payment' : 'Pending';

$customer_contact_safe = mb_substr($customer_contact, 0, 20);
$customer_email_safe = mb_substr($customer_email, 0, 150);
// Địa chỉ đầy đủ: số nhà + phường/xã + quận/huyện + tỉnh/thành phố
$ghn_ward_name = isset($_POST['ghn_ward_name']) ? trim((string) $_POST['ghn_ward_name']) : '';
$ghn_district_name = isset($_POST['ghn_district_name']) ? trim((string) $_POST['ghn_district_name']) : '';
$ghn_province_name = isset($_POST['ghn_province_name']) ? trim((string) $_POST['ghn_province_name']) : '';
$address_parts = array_filter([trim($customer_address), $ghn_ward_name, $ghn_district_name, $ghn_province_name]);
$full_address = implode(', ', $address_parts);
$customer_address_safe = mb_substr($full_address !== '' ? $full_address : $customer_address, 0, 255);

// Lưu đơn nội bộ (total = tạm tính + phí ship) -----
$stmt = $conn->prepare("INSERT INTO tbl_order (order_code, user_id, food, price, qty, total, order_date, status, customer_name, customer_contact, customer_email, customer_address) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị dữ liệu. Vui lòng thử lại.']);
    exit;
}
$stmt->bind_param("sisdidsssss", $order_code, $_SESSION['user_id'], $food_summary, $first_price, $cart_qty_total, $order_total, $status, $customer_name, $customer_contact_safe, $customer_email_safe, $customer_address_safe);

if (!$stmt->execute()) {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Không thể tạo đơn hàng. Vui lòng thử lại.']);
    exit;
}
$stmt->close();

// Cập nhật cột GHN và order_details nếu bảng đã chạy migration (sql/order-ghn-fields.sql)
$has_ghn_cols = false;
$has_order_details = false;
$r = @$conn->query("SHOW COLUMNS FROM tbl_order LIKE 'shipping_fee'");
if ($r && $r->num_rows > 0) $has_ghn_cols = true;
$r2 = @$conn->query("SHOW COLUMNS FROM tbl_order LIKE 'order_details'");
if ($r2 && $r2->num_rows > 0) $has_order_details = true;
if ($has_ghn_cols) {
    $conn->query("UPDATE tbl_order SET shipping_fee = " . (float) $shipping_fee . ", to_district_id = " . ($to_district_id ? (int) $to_district_id : "NULL") . ", to_ward_code = " . ($to_ward_code !== '' ? "'" . $conn->real_escape_string($to_ward_code) . "'" : "NULL") . ", order_weight_gram = " . (int) $order_weight_gram . " WHERE order_code = '" . $conn->real_escape_string($order_code) . "'");
}
if ($has_order_details && !empty($order_details_items)) {
    $order_details_json = $conn->real_escape_string(json_encode($order_details_items, JSON_UNESCAPED_UNICODE));
    $conn->query("UPDATE tbl_order SET order_details = '" . $order_details_json . "' WHERE order_code = '" . $conn->real_escape_string($order_code) . "'");
}

// ----- Bước 6: Tạo đơn giao hàng trên GHN, nhận mã vận đơn -----
$ghn_order_code = null;
$ghn_sort_code = null;
$ghn_status = null;
if ($to_district_id > 0 && $to_ward_code !== '' && defined('GHN_TOKEN') && GHN_TOKEN !== '' && defined('GHN_SHOP_ID') && GHN_SHOP_ID > 0) {
    $to_address_full = $customer_address_safe;
    $create_url = (defined('GHN_API_BASE') ? GHN_API_BASE : 'https://dev-online-gateway.ghn.vn/shiip/public-api') . '/v2/shipping-order/create';
    $create_body = [
        'payment_type_id' => 2,
        'required_note'   => 'CHOTHUHANG',
        'to_name'          => $customer_name,
        'to_phone'         => $customer_contact,
        'to_address'       => $to_address_full,
        'to_ward_code'     => $to_ward_code,
        'to_district_id'   => $to_district_id,
        'weight'           => $order_weight_gram,
        'length'           => 20,
        'width'            => 20,
        'height'           => 10,
        'service_type_id'  => 2,
        'content'          => mb_substr($food_summary, 0, 200),
        'client_order_code'=> $order_code,
        'cod_amount'       => ($payment_method === 'cash') ? (int) round($order_total) : 0,
        'items'            => [['name' => 'Đồ ăn', 'quantity' => 1, 'weight' => $order_weight_gram]],
    ];
    $ch = curl_init($create_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Token: ' . GHN_TOKEN, 'ShopId: ' . (int) GHN_SHOP_ID],
        CURLOPT_POSTFIELDS => json_encode($create_body),
    ]);
    $create_res = curl_exec($ch);
    curl_close($ch);
    if ($create_res !== false) {
        $create_data = json_decode($create_res, true);
        if (is_array($create_data) && isset($create_data['code']) && (int) $create_data['code'] === 200 && isset($create_data['data'])) {
            $d = $create_data['data'];
            $ghn_order_code = isset($d['order_code']) ? (string) $d['order_code'] : null;
            $ghn_sort_code = isset($d['sort_code']) ? (string) $d['sort_code'] : null;
            $ghn_status = 'created';
            if ($has_ghn_cols && $ghn_order_code !== null) {
                $conn->query("UPDATE tbl_order SET ghn_order_code = '" . $conn->real_escape_string($ghn_order_code) . "', ghn_sort_code = " . ($ghn_sort_code !== null ? "'" . $conn->real_escape_string($ghn_sort_code) . "'" : "NULL") . ", ghn_status = '" . $conn->real_escape_string($ghn_status) . "' WHERE order_code = '" . $conn->real_escape_string($order_code) . "'");
            }
        }
    }
}

// Thông báo cho user theo đúng luồng thanh toán
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
$notif_msg = "Đơn " . $order_code . " đã đặt thành công. Chat hỗ trợ / hoàn tiền: mục Chat.";

// Với COD: thông báo "đã đặt thành công" ngay khi tạo đơn.
// Với MoMo/VNPay: KHÔNG chèn thông báo "đang chờ thanh toán" lúc tạo đơn nữa,
// chỉ chèn khi return/callback xác nhận thanh toán thành công.
if ($payment_method === 'cash') {
    $ins = $conn->prepare("INSERT INTO tbl_order_notification (order_code, user_id, message) VALUES (?, ?, ?)");
    if ($ins) {
        $uid = (int) $_SESSION['user_id'];
        $ins->bind_param("sis", $order_code, $uid, $notif_msg);
        $ins->execute();
        $ins->close();
    }
}

// Chỉ xóa giỏ ngay lập tức với COD.
// Với momo/vnpay: giữ giỏ để khi user quay lại chỉnh sửa/trả lại luồng trước khi thanh toán thành công.
if ($payment_method === 'cash') {
    // Xóa đúng các món đã thanh toán khỏi giỏ; nếu không truyền chọn thì xóa toàn bộ
    if (!empty($selected_cart_ids) && is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function($row) use ($selected_cart_ids) {
            $cid = isset($row['cart_id']) ? (string)$row['cart_id'] : '';
            return $cid === '' || !in_array($cid, $selected_cart_ids, true);
        }));
    } else {
        $_SESSION['cart'] = [];
}
}
if (isset($_SESSION['redirect_after_login'])) unset($_SESSION['redirect_after_login']);

$redirect = SITEURL . 'index.php';
if (file_exists(__DIR__ . '/../user/order-history.php')) {
    $redirect = SITEURL . 'user/order-history.php';
}

echo json_encode([
    'success' => true,
    'message' => 'Đặt hàng thành công!',
    'order_code' => $order_code,
    'redirect' => $redirect
]);



