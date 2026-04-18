<?php
require_once __DIR__ . '/../config/constants.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Trước khi xóa session: đồng bộ giỏ hàng từ session -> DB
// để đăng xuất/đăng nhập lại không bị mất giỏ.
try {
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && isset($_SESSION['cart']) && is_array($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
        $user_id = (int) $_SESSION['user_id'];
        $conn->query("
            CREATE TABLE IF NOT EXISTS tbl_cart (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                cart_id VARCHAR(50) NOT NULL,
                food_id INT UNSIGNED NOT NULL,
                qty INT UNSIGNED NOT NULL DEFAULT 1,
                note TEXT NULL,
                size_id INT UNSIGNED NOT NULL DEFAULT 0,
                side_dish_ids TEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_user_cart (user_id, cart_id),
                KEY idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $stmt_cart = $conn->prepare("
            INSERT INTO tbl_cart (user_id, cart_id, food_id, qty, note, size_id, side_dish_ids)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                qty = VALUES(qty),
                note = VALUES(note),
                size_id = VALUES(size_id),
                side_dish_ids = VALUES(side_dish_ids)
        ");
        if ($stmt_cart) {
            foreach ($_SESSION['cart'] as $row) {
                if (!is_array($row)) continue;
                $cart_id = isset($row['cart_id']) && $row['cart_id'] !== '' ? (string)$row['cart_id'] : uniqid('c');
                if (empty($row['cart_id'])) $row['cart_id'] = $cart_id;

                $food_id = (int) ($row['food_id'] ?? 0);
                $qty = max(1, (int) ($row['qty'] ?? 1));
                $note = isset($row['note']) ? (string)$row['note'] : '';
                $size_id = (int) ($row['size_id'] ?? 0);

                $side_ids = [];
                if (isset($row['side_dish_ids']) && is_array($row['side_dish_ids'])) {
                    $side_ids = array_map('intval', $row['side_dish_ids']);
                }
                $side_dish_ids_str = !empty($side_ids) ? implode(',', $side_ids) : '';

                $stmt_cart->bind_param(
                    "isiisis",
                    $user_id,
                    $cart_id,
                    $food_id,
                    $qty,
                    $note,
                    $size_id,
                    $side_dish_ids_str
                );
                $stmt_cart->execute();
            }
            $stmt_cart->close();
        }
    }
} catch (Throwable $e) {
    // Bỏ qua lỗi DB, tránh chặn luồng đăng xuất
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'] ?? '/',
        $params['domain'] ?? '',
        (bool) ($params['secure'] ?? false),
        (bool) ($params['httponly'] ?? true)
    );
}


session_destroy();


session_start();
session_regenerate_id(true);
$_SESSION['login-success'] = 'Đăng xuất thành công!';


$redirectUrl = SITEURL . 'user/login.php';

if (!headers_sent()) {
    header('Location: ' . $redirectUrl);
    exit;
}


