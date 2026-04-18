<?php
include('../config/constants.php');

// Chỉ cho phép dùng giỏ hàng khi đã đăng nhập
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = SITEURL . 'user/cart.php';
    header('location:' . SITEURL . 'user/login.php');
    exit;
}

include('../partials-front/menu.php');

// Load sizes & side dishes
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

$cart_items = [];
$cart_total = 0;
$cart_count = 0;

// Nếu session giỏ hàng trống (đăng xuất/refresh/quay lại sau thanh toán),
// tự động nạp giỏ đã lưu trong DB theo user để không bị mất dữ liệu.
if ((!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || count($_SESSION['cart']) === 0) && isset($_SESSION['user_id'])) {
    try {
        $uid = (int) $_SESSION['user_id'];
        $cols = [];
        $colRes = @$conn->query("SHOW COLUMNS FROM tbl_cart");
        if ($colRes) {
            while ($cr = $colRes->fetch_assoc()) $cols[] = strtolower((string)($cr['Field'] ?? ''));
        }
        $hasNewSchema = in_array('cart_id', $cols, true) && in_array('qty', $cols, true);

        $restored = [];
        if ($hasNewSchema) {
            $stmtCart = $conn->prepare("SELECT cart_id, food_id, qty, note, size_id, side_dish_ids FROM tbl_cart WHERE user_id = ?");
            if ($stmtCart) {
                $stmtCart->bind_param("i", $uid);
                $stmtCart->execute();
                $res = $stmtCart->get_result();
                while ($c = $res->fetch_assoc()) {
                    $side_ids = [];
                    if (!empty($c['side_dish_ids'])) {
                        $side_ids = array_map('intval', array_filter(explode(',', (string)$c['side_dish_ids'])));
                    }
                    $restored[] = [
                        'cart_id' => (string) $c['cart_id'],
                        'food_id' => (int) $c['food_id'],
                        'qty' => max(1, (int) $c['qty']),
                        'note' => (string) $c['note'],
                        'size_id' => (int) $c['size_id'],
                        'side_dish_ids' => $side_ids
                    ];
                }
                $stmtCart->close();
            }
        } else {
            // Schema cũ: id, user_id, food_id, quantity, note...
            $stmtCart = $conn->prepare("SELECT id, food_id, quantity, note FROM tbl_cart WHERE user_id = ? ORDER BY id ASC");
            if ($stmtCart) {
                $stmtCart->bind_param("i", $uid);
                $stmtCart->execute();
                $res = $stmtCart->get_result();
                while ($c = $res->fetch_assoc()) {
                    $restored[] = [
                        'cart_id' => 'legacy_' . (string)$c['id'],
                        'food_id' => (int) $c['food_id'],
                        'qty' => max(1, (int) $c['quantity']),
                        'note' => (string)($c['note'] ?? ''),
                        'size_id' => 0,
                        'side_dish_ids' => []
                    ];
                }
                $stmtCart->close();
            }
        }

        if (!empty($restored)) {
            $_SESSION['cart'] = $restored;
        }
    } catch (Throwable $e) {
        // Không bắt buộc, nếu lỗi DB thì vẫn render theo session hiện tại
    }
}

// Đồng bộ session -> DB để đảm bảo luôn lưu giỏ theo tài khoản
// (trường hợp trước đó session có dữ liệu nhưng chưa kịp ghi vào DB).
if (isset($_SESSION['user_id']) && isset($_SESSION['cart']) && is_array($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    try {
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
            foreach ($_SESSION['cart'] as $idx => $row) {
                if (!is_array($row)) continue;
                $cart_id = isset($row['cart_id']) && $row['cart_id'] !== '' ? (string)$row['cart_id'] : uniqid('c');
                if (empty($row['cart_id'])) {
                    $_SESSION['cart'][$idx]['cart_id'] = $cart_id;
                }

                $food_id = (int) ($row['food_id'] ?? 0);
                $qty = max(1, (int) ($row['qty'] ?? 1));
                $note = isset($row['note']) ? (string)$row['note'] : '';
                $size_id = (int) ($row['size_id'] ?? 0);

                $side_ids = [];
                if (isset($row['side_dish_ids']) && is_array($row['side_dish_ids'])) {
                    $side_ids = array_map('intval', $row['side_dish_ids']);
                }
                $side_dish_ids_str = !empty($side_ids) ? implode(',', $side_ids) : '';

                // user_id(i), cart_id(s), food_id(i), qty(i), note(s), size_id(i), side_dish_ids(s)
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
    } catch (Throwable $e) {
        // Bỏ qua lỗi DB để không chặn trang giỏ
    }
}

if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    foreach ($_SESSION['cart'] as $idx => $cart_row) {
        $food_id = (int) ($cart_row['food_id'] ?? 0);
        if (empty($cart_row['cart_id'])) {
            $_SESSION['cart'][$idx]['cart_id'] = uniqid('c');
        }
        $cart_id = $_SESSION['cart'][$idx]['cart_id'];
        $qty = max(1, (int) (isset($cart_row['qty']) ? $cart_row['qty'] : 1));
        $note = isset($cart_row['note']) ? trim($cart_row['note']) : '';
        $size_id = (int) (isset($cart_row['size_id']) ? $cart_row['size_id'] : 1);
        $side_dish_ids = (isset($cart_row['side_dish_ids']) && is_array($cart_row['side_dish_ids'])) ? $cart_row['side_dish_ids'] : [];

        $stmt = $conn->prepare("SELECT id, title, price, image_name FROM tbl_food WHERE id = ? AND active = 'Yes'");
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
            $cart_count += $qty;
            $size_price_add = isset($sizes[$size_id]) ? $sizes[$size_id]['price_add'] : 0;
            $side_with_prices = [];
            foreach ((array) $side_dish_ids as $sid) {
                if (isset($side_dishes[$sid])) {
                    $side_with_prices[] = ['name' => $side_dishes[$sid]['name'], 'price' => $side_dishes[$sid]['price']];
                }
            }
            $cart_items[] = [
                'cart_id' => $cart_id,
                'food_id' => $food['id'],
                'title' => $food['title'],
                'image_name' => $food['image_name'],
                'base_price' => $base_price,
                'size_id' => $size_id,
                'size_name' => isset($sizes[$size_id]) ? $sizes[$size_id]['name'] : 'Nhỏ',
                'size_price_add' => $size_price_add,
                'side_dish_ids' => $side_dish_ids,
                'side_names' => array_map(function($id) use ($side_dishes) { return isset($side_dishes[$id]) ? $side_dishes[$id]['name'] : ''; }, (array) $side_dish_ids),
                'side_with_prices' => $side_with_prices,
                'unit_price' => $unit_price,
                'qty' => $qty,
                'note' => $note,
                'subtotal' => $subtotal
            ];
        }
    }
}

function formatPrice($num) {
    return number_format($num, 0, ',', '.') . ' đ';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - WowFood</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/cart.css">
</head>
<body>
    <div class="cart-container">
        <div class="cart-header">
            <h1>Giỏ hàng</h1>
            <span class="cart-badge" id="cartCount"><?php echo $cart_count; ?> món</span>
        </div>
        <div id="cartItems">
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon"><i class="bi bi-cart-x"></i></div>
                    <h2>Giỏ hàng trống</h2>
                    <p>Hãy thêm món ăn từ thực đơn nhé!</p>
                    <a href="<?php echo SITEURL; ?>food.php">Xem thực đơn</a>
                </div>
            <?php else: ?>
                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item" data-cart-id="<?php echo htmlspecialchars($item['cart_id']); ?>" data-base-price="<?php echo $item['base_price']; ?>" data-item-name="<?php echo htmlspecialchars($item['title']); ?>">
                    <div style="display:flex;align-items:flex-start;justify-content:center;padding-top:4px;">
                        <input type="checkbox" class="cart-select-item" data-cart-id="<?php echo htmlspecialchars($item['cart_id']); ?>" checked>
                    </div>
                    <?php if (!empty($item['image_name'])): ?>
                        <img src="<?php echo SITEURL; ?>image/food/<?php echo htmlspecialchars($item['image_name']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="cart-item-image">
                    <?php else: ?>
                        <div class="cart-item-image-placeholder">Chưa có ảnh</div>
                    <?php endif; ?>
                    <div class="cart-item-info">
                        <div class="cart-item-name"><?php echo htmlspecialchars($item['title']); ?></div>
                        <div class="cart-item-price item-price" data-cart-id="<?php echo htmlspecialchars($item['cart_id']); ?>">
                            <?php echo formatPrice($item['unit_price']); ?> × <?php echo $item['qty']; ?> = <strong><?php echo formatPrice($item['subtotal']); ?></strong>
                        </div>
                        <div class="cart-collapse open">
                            <div class="cart-collapse-head" onclick="this.parentElement.classList.toggle('open')">
                                <span class="cart-collapse-icon"><i class="bi bi-chevron-down"></i></span>
                                <label>Kích thước</label>
                            </div>
                            <div class="cart-collapse-body"><div class="cart-size-select">
                            <?php foreach ($sizes as $sid => $s): ?>
                                <span class="cart-size-opt <?php echo $sid == $item['size_id'] ? 'selected' : ''; ?>" 
                                      data-size-id="<?php echo $sid; ?>" data-size-name="<?php echo htmlspecialchars($s['name']); ?>" data-price-add="<?php echo $s['price_add']; ?>"
                                      data-cart-id="<?php echo htmlspecialchars($item['cart_id']); ?>"><?php echo htmlspecialchars($s['name']); ?> (+<?php echo formatPrice($s['price_add']); ?>)</span>
                            <?php endforeach; ?>
                            </div></div>
                        </div>
                        <div class="cart-collapse">
                            <div class="cart-collapse-head" onclick="this.parentElement.classList.toggle('open')">
                                <span class="cart-collapse-icon"><i class="bi bi-chevron-down"></i></span>
                                <label>Món/nước kèm</label>
                            </div>
                            <div class="cart-collapse-body"><div class="cart-sides-select">
                                <?php foreach ($side_dishes as $sid => $sd): ?>
                                <div class="cart-side-item">
                                    <input type="checkbox" class="cart-side-cb" data-side-id="<?php echo $sid; ?>" data-side-name="<?php echo htmlspecialchars($sd['name']); ?>" data-price="<?php echo $sd['price']; ?>"
                                           data-cart-id="<?php echo htmlspecialchars($item['cart_id']); ?>"
                                           <?php echo in_array($sid, $item['side_dish_ids']) ? 'checked' : ''; ?>>
                                    <label><?php echo htmlspecialchars($sd['name']); ?> (+<?php echo formatPrice($sd['price']); ?>)</label>
                                </div>
                                <?php endforeach; ?>
                            </div></div>
                        </div>
                        <div class="cart-item-controls">
                            <button class="quantity-btn" onclick="changeQty('<?php echo $item['cart_id']; ?>', -1)">-</button>
                            <input type="number" class="quantity-input item-qty" data-cart-id="<?php echo htmlspecialchars($item['cart_id']); ?>" value="<?php echo $item['qty']; ?>" min="1" onchange="setQty('<?php echo $item['cart_id']; ?>', this.value)">
                            <button class="quantity-btn" onclick="changeQty('<?php echo $item['cart_id']; ?>', 1)">+</button>
                            <button class="remove-btn" onclick="removeItem('<?php echo $item['cart_id']; ?>')">Xóa</button>
                        </div>
                        <input type="text" class="note-input" placeholder="Ghi chú..." value="<?php echo htmlspecialchars($item['note']); ?>"
                               data-cart-id="<?php echo htmlspecialchars($item['cart_id']); ?>" onblur="updateNote('<?php echo $item['cart_id']; ?>', this.value)">
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php if (!empty($cart_items)): ?>
        <div class="cart-summary" id="cartSummary">
            <div class="summary-title">Chi tiết đơn hàng</div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <input type="checkbox" id="selectAllCartItems" checked>
                <label for="selectAllCartItems" style="font-size:0.9em;color:#374151;cursor:pointer;">Chọn tất cả món để thanh toán</label>
            </div>
            <div class="summary-details" id="summaryDetails">
                <?php foreach ($cart_items as $item): 
                    $extrasParts = [];
                    $extrasParts[] = $item['size_name'] . ' (+' . formatPrice($item['size_price_add']) . ')';
                    foreach ($item['side_with_prices'] as $swp) {
                        $extrasParts[] = $swp['name'] . ' (+' . formatPrice($swp['price']) . ')';
                    }
                    $extrasHtml = '';
                    if (!empty($extrasParts)) {
                        $extrasHtml = '<span class="summary-detail-extras">';
                        foreach ($extrasParts as $p) {
                            $extrasHtml .= '<span class="summary-extra-line">' . htmlspecialchars($p) . '</span>';
                        }
                        $extrasHtml .= '</span>';
                    }
                ?>
                <div class="summary-detail-item">
                    <span class="summary-detail-name"><?php echo htmlspecialchars($item['title']); ?> <span class="summary-detail-qty">× <?php echo $item['qty']; ?></span><?php echo $extrasHtml; ?></span>
                    <span class="summary-detail-price"><?php echo formatPrice($item['subtotal']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="summary-row summary-total">
                <span>Tổng cộng:</span>
                <span id="cartTotal"><?php echo formatPrice($cart_total); ?></span>
            </div>
            <button type="button" class="checkout-btn" id="checkoutSelectedBtn">Thanh toán món đã chọn</button>
        </div>
        <?php endif; ?>
    </div>
    <?php include('../partials-front/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const SITEURL = '<?php echo SITEURL; ?>';

        function getItemData(cartId) {
            const item = document.querySelector('.cart-item[data-cart-id="' + cartId + '"]');
            if (!item) return null;
            const basePrice = parseFloat(item.dataset.basePrice) || 0;
            let sizeAdd = 0;
            const selSize = item.querySelector('.cart-size-opt.selected');
            if (selSize) sizeAdd = parseFloat(selSize.dataset.priceAdd) || 0;
            let sideTotal = 0;
            item.querySelectorAll('.cart-side-cb:checked').forEach(cb => {
                if (cb.dataset.cartId === cartId) sideTotal += parseFloat(cb.dataset.price) || 0;
            });
            const qty = parseInt(item.querySelector('.item-qty[data-cart-id="' + cartId + '"]')?.value) || 1;
            const unitPrice = basePrice + sizeAdd + sideTotal;
            const name = item.dataset.itemName || item.querySelector('.cart-item-name')?.textContent || 'Món';
            return { name, unitPrice, qty, subtotal: unitPrice * qty };
        }

        function updateItemDisplay(cartId) {
            const d = getItemData(cartId);
            if (!d) return;
            const priceEl = document.querySelector('.item-price[data-cart-id="' + cartId + '"]');
            if (priceEl) priceEl.innerHTML = formatPrice(d.unitPrice) + ' × ' + d.qty + ' = <strong>' + formatPrice(d.subtotal) + '</strong>';
            updateGrandTotal();
        }

        function getItemExtrasWithPrices(cartId) {
            const item = document.querySelector('.cart-item[data-cart-id="' + cartId + '"]');
            if (!item) return [];
            const parts = [];
            const selSize = item.querySelector('.cart-size-opt.selected');
            if (selSize && selSize.dataset.cartId === cartId) {
                const name = (selSize.dataset.sizeName || '').trim();
                const price = parseFloat(selSize.dataset.priceAdd) || 0;
                if (name) parts.push(name + ' (+' + formatPrice(price) + ')');
            }
            item.querySelectorAll('.cart-side-cb:checked').forEach(cb => {
                if (cb.dataset.cartId === cartId && cb.dataset.sideName) {
                    const price = parseFloat(cb.dataset.price) || 0;
                    parts.push(cb.dataset.sideName + ' (+' + formatPrice(price) + ')');
                }
            });
            return parts;
        }

        function getSelectedCartIds() {
            return Array.from(document.querySelectorAll('.cart-select-item:checked')).map(function(cb) { return cb.dataset.cartId; });
        }
        function updateGrandTotal() {
            let total = 0;
            const details = [];
            const selected = getSelectedCartIds();
            document.querySelectorAll('.cart-item').forEach(item => {
                const cartId = item.dataset.cartId;
                if (selected.indexOf(cartId) === -1) return;
                const d = getItemData(cartId);
                if (d) {
                    total += d.subtotal;
                    const extras = getItemExtrasWithPrices(cartId);
                    details.push({ name: d.name, qty: d.qty, subtotal: d.subtotal, extras });
                }
            });
            const totalEl = document.getElementById('cartTotal');
            if (totalEl) totalEl.textContent = formatPrice(Math.max(0, total));
            const detailsEl = document.getElementById('summaryDetails');
            if (detailsEl) {
                detailsEl.innerHTML = details.map(d => {
                    const name = String(d.name).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
                    const extrasHtml = (d.extras || []).length
                        ? ' <span class="summary-detail-extras">' +
                            (d.extras || []).map(e =>
                                '<span class="summary-extra-line">' +
                                String(e)
                                    .replace(/&/g,'&amp;')
                                    .replace(/</g,'&lt;')
                                    .replace(/>/g,'&gt;')
                                    .replace(/"/g,'&quot;') +
                                '</span>'
                            ).join('') +
                          '</span>'
                        : '';
                    return '<div class="summary-detail-item"><span class="summary-detail-name">' + name + ' <span class="summary-detail-qty">× ' + d.qty + '</span>' + extrasHtml + '</span><span class="summary-detail-price">' + formatPrice(d.subtotal) + '</span></div>';
                }).join('');
            }
        }

        function formatPrice(n) { return new Intl.NumberFormat('vi-VN').format(Math.round(n)) + ' đ'; }

        function changeQty(cartId, delta) {
            const input = document.querySelector('.item-qty[data-cart-id="' + cartId + '"]');
            if (!input) return;
            let val = Math.max(1, (parseInt(input.value) || 1) + delta);
            input.value = val;
            updateItemDisplay(cartId);
            saveCartItem(cartId);
        }

        function setQty(cartId, value) {
            const val = Math.max(1, parseInt(value) || 1);
            const input = document.querySelector('.item-qty[data-cart-id="' + cartId + '"]');
            if (input) input.value = val;
            updateItemDisplay(cartId);
            saveCartItem(cartId);
        }

        function saveCartItem(cartId) {
            const item = document.querySelector('.cart-item[data-cart-id="' + cartId + '"]');
            if (!item) return;
            const qty = parseInt(item.querySelector('.item-qty[data-cart-id="' + cartId + '"]')?.value) || 1;
            const note = item.querySelector('.note-input[data-cart-id="' + cartId + '"]')?.value || '';
            let sizeId = 1;
            item.querySelectorAll('.cart-size-opt.selected').forEach(o => { if (o.dataset.cartId === cartId) sizeId = o.dataset.sizeId; });
            const sideIds = Array.from(item.querySelectorAll('.cart-side-cb:checked')).filter(cb => cb.dataset.cartId === cartId).map(cb => cb.dataset.sideId);
            const fd = new FormData();
            fd.append('cart_id', cartId);
            fd.append('quantity', qty);
            fd.append('note', note);
            fd.append('size_id', sizeId);
            fd.append('side_dish_ids', sideIds.join(','));
            fetch(SITEURL + 'api/update-cart.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => { if (data.success) updateItemDisplay(cartId); });
        }

        function updateNote(cartId, note) { saveCartItem(cartId); }

        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAllCartItems');
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    document.querySelectorAll('.cart-select-item').forEach(function(cb) { cb.checked = selectAll.checked; });
                    updateGrandTotal();
                });
            }
            document.querySelectorAll('.cart-select-item').forEach(function(cb) {
                cb.addEventListener('change', updateGrandTotal);
            });
            document.querySelectorAll('.cart-size-opt').forEach(el => {
                el.addEventListener('click', function() {
                    const cartId = this.dataset.cartId;
                    this.closest('.cart-item').querySelectorAll('.cart-size-opt').forEach(o => { o.classList.remove('selected'); });
                    this.classList.add('selected');
                    saveCartItem(cartId);
                    updateItemDisplay(cartId);
                });
            });
            document.querySelectorAll('.cart-side-cb').forEach(el => {
                el.addEventListener('change', function() {
                    const cartId = this.dataset.cartId;
                    saveCartItem(cartId);
                    updateItemDisplay(cartId);
                });
            });
            const checkoutBtn = document.getElementById('checkoutSelectedBtn');
            if (checkoutBtn) {
                checkoutBtn.addEventListener('click', function() {
                    const selected = getSelectedCartIds();
                    if (!selected.length) {
                        Swal.fire('Thông báo', 'Bạn chưa chọn món nào để thanh toán.', 'warning');
                        return;
                    }
                    let url = SITEURL + 'user/checkout.php?selected=' + encodeURIComponent(selected.join(','));
                    window.location.href = url;
                });
            }
        });

        function removeItem(cartId) {
            Swal.fire({ title: 'Xác nhận xóa', text: 'Bạn có chắc muốn xóa món này?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ff4757', cancelButtonColor: '#6c757d', confirmButtonText: 'Xóa' })
                .then((r) => {
                    if (r.isConfirmed) {
                        const fd = new FormData();
                        fd.append('cart_id', cartId);
                        fetch(SITEURL + 'api/remove-from-cart.php', { method: 'POST', body: fd })
                            .then(res => res.json())
                            .then(data => { if (data.success) location.reload(); });
                    }
                });
        }
    </script>
</body>
</html>
