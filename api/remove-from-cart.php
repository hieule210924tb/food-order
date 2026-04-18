<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/constants.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để sử dụng giỏ hàng']);
    exit;
}

$cart_id = isset($_POST['cart_id']) ? trim($_POST['cart_id']) : '';

$targetItem = null;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cid = isset($item['cart_id']) ? (string)$item['cart_id'] : '';
        if ($cid !== '' && $cid === $cart_id) {
            $targetItem = is_array($item) ? $item : null;
            break;
        }
    }
}

$targetFoodId = $targetItem && isset($targetItem['food_id']) ? (int)$targetItem['food_id'] : 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function($item) use ($cart_id) {
        return (isset($item['cart_id']) ? $item['cart_id'] : '') !== $cart_id;
    }));
}

// Đồng bộ xóa khỏi DB (cả schema mới và schema cũ)
try {
    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($uid > 0 && $cart_id !== '') {
        $cols = [];
        $colRes = @$conn->query("SHOW COLUMNS FROM tbl_cart");
        if ($colRes) {
            while ($c = $colRes->fetch_assoc()) {
                $cols[] = strtolower((string)($c['Field'] ?? ''));
            }
        }

        $hasCartIdCol = in_array('cart_id', $cols, true);

        if ($hasCartIdCol) {
            $stmtDel = $conn->prepare("DELETE FROM tbl_cart WHERE user_id = ? AND cart_id = ?");
            if ($stmtDel) {
                $stmtDel->bind_param("is", $uid, $cart_id);
                $stmtDel->execute();
                $stmtDel->close();
            }
        } else {
            // Schema cũ: không có cart_id/qty, có id + food_id + quantity...
            // Nếu cart_id có dạng legacy_{id} thì xóa theo id.
            if (strpos($cart_id, 'legacy_') === 0) {
                $legacyIdStr = substr($cart_id, strlen('legacy_'));
                $legacyId = (int)$legacyIdStr;
                if ($legacyId > 0) {
                    $stmtDel = $conn->prepare("DELETE FROM tbl_cart WHERE user_id = ? AND id = ? LIMIT 1");
                    if ($stmtDel) {
                        $stmtDel->bind_param("ii", $uid, $legacyId);
                        $stmtDel->execute();
                        $stmtDel->close();
                    }
                }
            } else {
                // Fallback: xóa dòng mới nhất theo user_id + food_id
                if ($targetFoodId > 0) {
                    $stmtFind = $conn->prepare("SELECT id FROM tbl_cart WHERE user_id = ? AND food_id = ? ORDER BY id DESC LIMIT 1");
                    if ($stmtFind) {
                        $stmtFind->bind_param("ii", $uid, $targetFoodId);
                        $stmtFind->execute();
                        $res = $stmtFind->get_result();
                        $row = $res ? $res->fetch_assoc() : null;
                        $stmtFind->close();
                        if ($row && isset($row['id'])) {
                            $legacyId = (int)$row['id'];
                            $stmtDel = $conn->prepare("DELETE FROM tbl_cart WHERE user_id = ? AND id = ? LIMIT 1");
                            if ($stmtDel) {
                                $stmtDel->bind_param("ii", $uid, $legacyId);
                                $stmtDel->execute();
                                $stmtDel->close();
                            }
                        }
                    }
                }
            }
        }
    }
} catch (Throwable $e) {
    // Không chặn user khi DB lỗi
}

echo json_encode(['success' => true, 'message' => 'Đã xóa món khỏi giỏ hàng']);
