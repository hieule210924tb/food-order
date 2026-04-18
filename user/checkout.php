<?php
include('../config/constants.php');

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = SITEURL . 'user/checkout.php';
    header('location:' . SITEURL . 'user/login.php');
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

$cart_items = [];
$cart_total = 0;
$selected_cart_ids = [];
if (isset($_GET['selected']) && trim((string)$_GET['selected']) !== '') {
    $selected_cart_ids = array_values(array_filter(array_map('trim', explode(',', (string)$_GET['selected']))));
}
$voucher_prefill = isset($_GET['voucher_code']) ? strtoupper(trim((string)$_GET['voucher_code'])) : '';
if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    foreach ($_SESSION['cart'] as $cart_row) {
        if (empty($cart_row['cart_id'])) continue;
        if (!empty($selected_cart_ids) && !in_array($cart_row['cart_id'], $selected_cart_ids, true)) continue;
        $food_id = (int) ($cart_row['food_id'] ?? 0);
        $qty = max(1, (int) ($cart_row['qty'] ?? 1));
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
            $cart_items[] = [
                'title' => $food['title'],
                'image_name' => $food['image_name'],
                'size_name' => isset($sizes[$size_id]) ? $sizes[$size_id]['name'] : 'Nhỏ',
                'side_names' => array_map(function($id) use ($side_dishes) { return isset($side_dishes[$id]) ? $side_dishes[$id]['name'] : ''; }, (array) $side_dish_ids),
                'unit_price' => $unit_price,
                'qty' => $qty,
                'note' => $note,
                'subtotal' => $subtotal
            ];
        }
    }
}

if (empty($cart_items)) {
    header('location:' . SITEURL . 'user/cart.php');
    exit;
}

$user_row = null;
$user_cols = ['full_name', 'email', 'phone', 'address'];
$stmt_check = $conn->query("SHOW COLUMNS FROM tbl_user LIKE 'ghn_province_id'");
if ($stmt_check && $stmt_check->num_rows > 0) {
    $user_cols[] = 'ghn_province_id';
    $user_cols[] = 'ghn_district_id';
    $user_cols[] = 'ghn_ward_code';
}
$stmt_check && is_object($stmt_check) && $stmt_check->close();
$cols_sql = implode(', ', $user_cols);
$stmt = $conn->prepare("SELECT {$cols_sql} FROM tbl_user WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) $user_row = $result->fetch_assoc();
$stmt->close();

function formatPrice($num) {
    return number_format($num, 0, ',', '.') . ' đ';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - WowFood</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/checkout.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include('../partials-front/menu.php'); ?>

<div class="checkout-page">
    <div class="checkout-breadcrumb">
        <a href="<?php echo SITEURL; ?>">Trang chủ</a>
        <span class="sep">/</span>
        <a href="<?php echo SITEURL; ?>user/cart.php">Giỏ hàng</a>
        <span class="sep">/</span>
        <span class="current">Thanh toán</span>
    </div>

    <h1 class="checkout-title">
        <span class="checkout-title-icon"><i class="bi bi-check-circle"></i></span>
        Thanh toán đơn hàng
    </h1>

    <form id="checkoutForm" class="checkout-layout">
        <input type="hidden" id="voucher_code_input" name="voucher_code" value="">
        <!-- Thanh cố định mobile: nút Đặt hàng luôn hiển thị -->
        <div class="checkout-mobile-sticky" id="checkoutMobileSticky" aria-hidden="true">
            <span class="sticky-total">Tổng: <span id="grandTotalSticky"><?php echo formatPrice($cart_total); ?></span></span>
            <button type="button" class="btn-place-order btn-place-order-sticky" id="btnPlaceOrderSticky" aria-label="Đặt hàng">
                <span class="btn-text">Đặt hàng</span>
                <span class="btn-price"><?php echo formatPrice($cart_total); ?></span>
            </button>
        </div>
        <div class="checkout-main">
            <section class="checkout-card checkout-form-card">
                <h2 class="card-heading">
                    <span class="card-icon"><i class="bi bi-geo-alt"></i></span>
                    Thông tin giao hàng
                </h2>
                <div class="form-grid">
                    <div class="form-group full">
                        <label for="customer_name">Họ và tên <span class="required">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" required
                               value="<?php echo htmlspecialchars($user_row['full_name'] ?? ''); ?>"
                               placeholder="Nguyễn Văn A">
                    </div>
                    <div class="form-group">
                        <label for="customer_contact">Số điện thoại <span class="required">*</span></label>
                        <input type="tel" id="customer_contact" name="customer_contact" required
                               value="<?php echo htmlspecialchars($user_row['phone'] ?? ''); ?>"
                               placeholder="0900 123 456">
                    </div>
                    <div class="form-group">
                        <label for="customer_email">Email <span class="required">*</span></label>
                        <input type="email" id="customer_email" name="customer_email" required
                               value="<?php echo htmlspecialchars($user_row['email'] ?? ''); ?>"
                               placeholder="email@example.com">
                    </div>
                    <div class="form-group full ghn-address-row">
                        <label for="ghn_province_id">Chọn địa chỉ giao hàng (Tỉnh → Quận → Phường/Xã) <span class="required">*</span></label>
                        <div class="ghn-selects">
                            <select id="ghn_province_id" name="ghn_province_id" class="ghn-select" aria-label="Tỉnh/Thành phố">
                                <option value="">-- Chọn Tỉnh/TP --</option>
                            </select>
                            <select id="ghn_district_id" name="ghn_district_id" class="ghn-select" aria-label="Quận/Huyện" disabled>
                                <option value="">-- Chọn Quận/Huyện --</option>
                            </select>
                            <select id="ghn_ward_code" name="ghn_ward_code" class="ghn-select" aria-label="Phường/Xã" disabled>
                                <option value="">-- Chọn Phường/Xã --</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group full">
                        <label for="customer_address">Địa chỉ chi tiết <span class="required">*</span></label>
                        <input type="text" id="customer_address" name="customer_address" required
                               value="<?php echo htmlspecialchars($user_row['address'] ?? ''); ?>"
                               placeholder="Ví dụ: 123 Ngõ Nguyễn Huệ">
                    </div>
                    <div class="form-group full">
                        <label for="order_note">Ghi chú đơn hàng</label>
                        <textarea id="order_note" name="order_note" rows="2"
                                  placeholder="Ghi chú cho cửa hàng (không bắt buộc)"></textarea>
                    </div>
                </div>
            </section>

            <section class="checkout-card payment-card">
                <h2 class="card-heading">
                    <span class="card-icon"><i class="bi bi-credit-card"></i></span>
                    Phương thức thanh toán
                </h2>
                <div class="payment-methods">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cash" checked>
                        <span class="payment-option-box">
                            <span class="payment-icon payment-icon-cash"><i class="bi bi-cash-stack"></i></span>
                            <span class="payment-label">Tiền mặt</span>
                            <span class="payment-desc">Thanh toán khi nhận hàng (COD)</span>
                        </span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="momo">
                        <span class="payment-option-box">
                            <span class="payment-icon payment-icon-momo">
                                <img src="<?php echo SITEURL; ?>image/momo.png" alt="MoMo" class="payment-icon-img" width="26" height="26" style="width:26px;height:26px;max-width:26px;max-height:26px;">
                            </span>
                            <span class="payment-label">Ví MoMo</span>
                            <span class="payment-desc">Thanh toán qua ví điện tử MoMo</span>
                        </span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="vnpay">
                        <span class="payment-option-box">
                            <span class="payment-icon payment-icon-vnpay">
                                <img src="<?php echo SITEURL; ?>image/vnpay.png" alt="VNPAY" class="payment-icon-img" width="26" height="26" style="width:26px;height:26px;max-width:26px;max-height:26px;">
                            </span>
                            <span class="payment-label">VNPAY</span>
                            <span class="payment-desc">Thanh toán thẻ ATM / QR qua cổng VNPAY</span>
                        </span>
                    </label>
                </div>
            </section>
        </div>

        <aside class="checkout-sidebar">
            <div class="checkout-card order-summary-card">
                <h2 class="card-heading">
                    <span class="card-icon"><i class="bi bi-cart3"></i></span>
                    Đơn hàng của bạn
                </h2>
                <div class="order-summary-list">
                    <?php foreach ($cart_items as $item):
                        $extras = array_filter([$item['size_name']]);
                        if (!empty($item['side_names'])) $extras = array_merge($extras, $item['side_names']);
                        $extrasStr = !empty($extras) ? ' (' . implode(', ', $extras) . ')' : '';
                    ?>
                    <div class="order-summary-item">
                        <?php if (!empty($item['image_name'])): ?>
                        <img src="<?php echo SITEURL; ?>image/food/<?php echo htmlspecialchars($item['image_name']); ?>" alt="" class="summary-item-img">
                        <?php else: ?>
                        <div class="summary-item-img summary-item-placeholder"><i class="bi bi-egg-fried"></i></div>
                        <?php endif; ?>
                        <div class="summary-item-info">
                            <span class="summary-item-name"><?php echo htmlspecialchars($item['title']); ?></span>
                            <span class="summary-item-meta">× <?php echo $item['qty']; ?><?php if ($extrasStr): ?> · <?php echo htmlspecialchars($extrasStr); ?><?php endif; ?></span>
                            <span class="summary-item-price"><?php echo formatPrice($item['subtotal']); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="order-summary-footer">
                    <div class="summary-row">
                        <span>Tạm tính</span>
                        <span id="subtotalAmount"><?php echo formatPrice($cart_total); ?></span>
                    </div>
                    <div class="summary-row summary-row-discount" id="voucherRow" style="display:none;">
                        <span>Giảm voucher</span>
                        <span id="voucherAmount">-0 đ</span>
                    </div>
                    <div class="summary-row summary-row-ship" id="shippingFeeRow">
                        <span>Phí ship (GHN)</span>
                        <span id="shippingFeeAmount">0 đ</span>
                    </div>
                    <div class="summary-row" style="display:block;">
                        <label for="voucher_code" style="font-size:13px;color:#6b7280;display:block;margin-bottom:6px;">Voucher</label>
                        <div style="display:flex;gap:8px;">
                            <input type="text" id="voucher_code" placeholder="Nhập mã voucher"
                                   value="<?php echo htmlspecialchars($voucher_prefill); ?>"
                                   style="flex:1;padding:8px 10px;border:1px solid #ddd;border-radius:8px;">
                            <button type="button" id="applyVoucherBtn" style="border:none;background:#22c55e;color:#fff;padding:8px 12px;border-radius:8px;cursor:pointer;">Áp dụng</button>
                        </div>
                        <div id="voucherMsg" style="font-size:12px;margin-top:6px;color:#6b7280;"></div>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Tổng thanh toán</span>
                        <span id="grandTotal"><?php echo formatPrice($cart_total); ?></span>
                    </div>
                </div>
                <button type="submit" class="btn-place-order" id="btnPlaceOrder">
                    <span class="btn-text">Đặt hàng</span>
                    <span class="btn-price"><?php echo formatPrice($cart_total); ?></span>
                </button>
                <a href="<?php echo SITEURL; ?>user/cart.php" class="back-cart"><i class="bi bi-arrow-left"></i> Quay lại giỏ hàng</a>
            </div>
        </aside>
    </form>
</div>

<?php include('../partials-front/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.USER_GHN = <?php
    $ug = ['province_id' => 0, 'district_id' => 0, 'ward_code' => ''];
    if (!empty($user_row['ghn_province_id'])) $ug['province_id'] = (int)$user_row['ghn_province_id'];
    if (!empty($user_row['ghn_district_id'])) $ug['district_id'] = (int)$user_row['ghn_district_id'];
    if (!empty($user_row['ghn_ward_code'])) $ug['ward_code'] = (string)$user_row['ghn_ward_code'];
    echo json_encode($ug);
?>;
</script>
<script>
(function() {
    const SITEURL = '<?php echo SITEURL; ?>';
    const CHECKOUT_DRAFT_KEY = 'wowfood_checkout_draft';
    const form = document.getElementById('checkoutForm');
    const btn = document.getElementById('btnPlaceOrder');
    const btnSticky = document.getElementById('btnPlaceOrderSticky');
    const cartTotal = <?php echo json_encode((float)$cart_total); ?>;
    const USER_GHN = window.USER_GHN || {};
    let voucherDiscount = 0;
    let voucherCode = (document.getElementById('voucher_code')?.value || '').trim();
    let appliedVoucherCode = '';
    const voucherInput = document.getElementById('voucher_code');
    // Khối voucher "Áp dụng" phía trên đã được bỏ (để tránh trùng id),
    // nút apply hiện dùng là `applyVoucherBtn` bên dưới.
    const voucherBtn = document.getElementById('btnApplyVoucher');
    const voucherRow = document.getElementById('voucherRow');
    const voucherAmount = document.getElementById('voucherAmount');
    const voucherMsg = document.getElementById('voucherMsg');
    const voucherHidden = document.getElementById('voucher_code_input');

    function fmt(n) { return Number(n).toLocaleString('vi-VN') + ' đ'; }

    // Bước 3 & 4: Chọn địa chỉ GHN + Tính phí giao hàng
    const selProvince = document.getElementById('ghn_province_id');
    const selDistrict = document.getElementById('ghn_district_id');
    const selWard = document.getElementById('ghn_ward_code');
    let shippingFee = 0;

    function readDraft() {
        try {
            const raw = localStorage.getItem(CHECKOUT_DRAFT_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }
    function saveDraft() {
        try {
            const payment = document.querySelector('input[name="payment_method"]:checked');
            const draft = {
                customer_name: (document.getElementById('customer_name')?.value || '').trim(),
                customer_contact: (document.getElementById('customer_contact')?.value || '').trim(),
                customer_email: (document.getElementById('customer_email')?.value || '').trim(),
                customer_address: (document.getElementById('customer_address')?.value || '').trim(),
                order_note: (document.getElementById('order_note')?.value || '').trim(),
                payment_method: payment ? payment.value : 'cash',
                ghn_province_id: selProvince ? String(selProvince.value || '').trim() : '',
                ghn_district_id: selDistrict ? String(selDistrict.value || '').trim() : '',
                ghn_ward_code: selWard ? String(selWard.value || '').trim() : '',
                voucher_code: (voucherInput?.value || '').trim()
            };
            localStorage.setItem(CHECKOUT_DRAFT_KEY, JSON.stringify(draft));
        } catch (e) {}
    }
    function clearDraft() {
        try { localStorage.removeItem(CHECKOUT_DRAFT_KEY); } catch (e) {}
    }

    // Khôi phục trạng thái checkout khi user quay lại từ cổng thanh toán
    const checkoutDraft = readDraft();
    if (checkoutDraft) {
        const setIf = (id, val) => {
            const el = document.getElementById(id);
            if (el && typeof val === 'string' && val !== '') el.value = val;
        };
        setIf('customer_name', checkoutDraft.customer_name || '');
        setIf('customer_contact', checkoutDraft.customer_contact || '');
        setIf('customer_email', checkoutDraft.customer_email || '');
        setIf('customer_address', checkoutDraft.customer_address || '');
        setIf('order_note', checkoutDraft.order_note || '');
        if (voucherInput && (checkoutDraft.voucher_code || '').trim() !== '') {
            voucherInput.value = checkoutDraft.voucher_code;
            voucherCode = checkoutDraft.voucher_code;
        }
        if (checkoutDraft.payment_method) {
            const radio = document.querySelector('input[name="payment_method"][value="' + checkoutDraft.payment_method + '"]');
            if (radio) radio.checked = true;
        }
        if (checkoutDraft.ghn_province_id) USER_GHN.province_id = parseInt(checkoutDraft.ghn_province_id, 10) || 0;
        if (checkoutDraft.ghn_district_id) USER_GHN.district_id = parseInt(checkoutDraft.ghn_district_id, 10) || 0;
        if (checkoutDraft.ghn_ward_code) USER_GHN.ward_code = checkoutDraft.ghn_ward_code;
    }

    function loadProvinces() {
        return fetch(SITEURL + 'api/ghn-address.php?action=province').then(function(r) { return r.json(); })
            .then(function(res) {
                if (!res.success || !res.data) return;
                var list = Array.isArray(res.data) ? res.data : Object.keys(res.data).map(function(k) { var v = res.data[k]; return typeof v === 'object' ? v : { ProvinceID: k, ProvinceName: v }; });
                selProvince.innerHTML = '<option value="">-- Chọn Tỉnh/TP --</option>';
                list.forEach(function(p) {
                    var id = p.ProvinceID != null ? p.ProvinceID : p.province_id;
                    var name = p.ProvinceName || p.province_name || '';
                    if (id != null && name) selProvince.appendChild(new Option(name, id));
                });
                if (USER_GHN.province_id && selProvince.querySelector('option[value="' + USER_GHN.province_id + '"]')) {
                    selProvince.value = USER_GHN.province_id;
                    return loadDistricts(USER_GHN.province_id);
                }
            }).catch(function() {});
    }
    function loadDistricts(provinceId) {
        selDistrict.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>'; selDistrict.disabled = true;
        selWard.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>'; selWard.disabled = true;
        updateShippingFee(0);
        if (!provinceId) return Promise.resolve();
        return fetch(SITEURL + 'api/ghn-address.php?action=district&province_id=' + encodeURIComponent(provinceId)).then(function(r) { return r.json(); })
            .then(function(res) {
                if (!res.success || !res.data) return;
                var list = Array.isArray(res.data) ? res.data : Object.values(res.data);
                list.forEach(function(d) {
                    var id = d.DistrictID != null ? d.DistrictID : d.district_id;
                    var name = d.DistrictName || d.district_name || '';
                    if (id != null && name) selDistrict.appendChild(new Option(name, id));
                });
                selDistrict.disabled = false;
                if (USER_GHN.district_id && selDistrict.querySelector('option[value="' + USER_GHN.district_id + '"]')) {
                    selDistrict.value = USER_GHN.district_id;
                    return loadWards(USER_GHN.district_id);
                }
            }).catch(function() { selDistrict.disabled = false; });
    }
    function loadWards(districtId) {
        selWard.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>'; selWard.disabled = true;
        updateShippingFee(0);
        if (!districtId) return Promise.resolve();
        return fetch(SITEURL + 'api/ghn-address.php?action=ward&district_id=' + encodeURIComponent(districtId)).then(function(r) { return r.json(); })
            .then(function(res) {
                if (!res.success || !res.data) return;
                var list = Array.isArray(res.data) ? res.data : Object.values(res.data);
                list.forEach(function(w) {
                    var code = (w.WardCode != null ? String(w.WardCode) : (w.ward_code ? String(w.ward_code) : '')).trim();
                    var name = (w.WardName || w.ward_name || '').trim();
                    if (code && name) selWard.appendChild(new Option(name, code));
                });
                selWard.disabled = false;
                if (USER_GHN.ward_code && selWard.querySelector('option[value="' + USER_GHN.ward_code.replace(/"/g, '\\"') + '"]')) {
                    selWard.value = USER_GHN.ward_code;
                    fetchShippingFee();
                }
            }).catch(function() { selWard.disabled = false; });
    }
    function updateTotals() {
        var amountEl = document.getElementById('shippingFeeAmount');
        var grandEl = document.getElementById('grandTotal');
        var grandSticky = document.getElementById('grandTotalSticky');
        var total = cartTotal + shippingFee - voucherDiscount;
        if (total < 0) total = 0;
        if (amountEl) amountEl.textContent = fmt(shippingFee);
        if (grandEl) grandEl.textContent = fmt(total);
        if (grandSticky) grandSticky.textContent = fmt(total);
        var priceSpans = document.querySelectorAll('.btn-place-order .btn-price');
        priceSpans.forEach(function(el) { el.textContent = fmt(total); });
        if (voucherRow && voucherAmount) {
            // voucherDiscount là số tiền giảm thực tế (đã tính theo voucher hợp lệ)
            if (voucherDiscount > 0) {
                voucherRow.style.display = '';
                voucherAmount.textContent = '-' + fmt(voucherDiscount);
            } else {
                voucherRow.style.display = 'none';
                voucherAmount.textContent = '-0 đ';
            }
        }
    }
    function updateShippingFee(fee) {
        shippingFee = fee;
        updateTotals();
    }
    function applyVoucherFromCheckout() {
        const codeInput = document.getElementById('voucher_code');
        const msgEl = document.getElementById('voucherMsg');
        const code = (codeInput ? codeInput.value : '').trim();
        if (!code) {
            voucherDiscount = 0;
            voucherCode = '';
            if (voucherHidden) voucherHidden.value = '';
            if (msgEl) { msgEl.style.color = '#6b7280'; msgEl.textContent = ''; }
            updateShippingFee(shippingFee);
            return;
        }
        const fd = new FormData();
        // api/voucher-apply.php yêu cầu: voucher_code + amount (tổng tiền món, không gồm phí ship)
        fd.append('voucher_code', code);
        fd.append('amount', String(cartTotal));
        fetch(SITEURL + 'api/voucher-apply.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    voucherDiscount = Number(res.discount || 0);
                    voucherCode = res.code || code;
                    if (voucherHidden) voucherHidden.value = voucherCode;
                    if (msgEl) { msgEl.style.color = '#16a34a'; msgEl.textContent = 'Áp dụng thành công: -' + fmt(voucherDiscount); }
                } else {
                    voucherDiscount = 0;
                    voucherCode = '';
                    if (voucherHidden) voucherHidden.value = '';
                    if (msgEl) { msgEl.style.color = '#dc2626'; msgEl.textContent = res.message || 'Voucher không hợp lệ'; }
                }
                updateShippingFee(shippingFee);
            })
            .catch(function() {
                if (msgEl) { msgEl.style.color = '#dc2626'; msgEl.textContent = 'Không thể áp dụng voucher'; }
            });
    }
    function fetchShippingFee() {
        var districtId = (selDistrict && selDistrict.value) ? String(selDistrict.value).trim() : '';
        var wardCode = (selWard && selWard.value) ? String(selWard.value).trim() : '';
        if (!districtId || !wardCode) { updateShippingFee(0); return; }
        var fd = new FormData();
        fd.append('to_district_id', districtId);
        fd.append('to_ward_code', wardCode);
        fetch(SITEURL + 'api/ghn-fee.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                var fee = res.success ? (res.fee || 0) : 0;
                updateShippingFee(fee);
                if (!res.success && res.message) console.warn('Phí ship GHN:', res.message);
            })
            .catch(function(err) { updateShippingFee(0); console.warn('Lỗi gọi phí ship:', err); });
    }
    if (selProvince) selProvince.addEventListener('change', function() { loadDistricts(selProvince.value); });
    if (selDistrict) selDistrict.addEventListener('change', function() { loadWards(selDistrict.value); });
    if (selWard) selWard.addEventListener('change', function() { fetchShippingFee(); });
    // Autosave để quay lại checkout không mất dữ liệu
    ['customer_name','customer_contact','customer_email','customer_address','order_note'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', saveDraft);
    });
    if (selProvince) selProvince.addEventListener('change', saveDraft);
    if (selDistrict) selDistrict.addEventListener('change', saveDraft);
    if (selWard) selWard.addEventListener('change', saveDraft);
    document.querySelectorAll('input[name="payment_method"]').forEach(function(r) {
        r.addEventListener('change', saveDraft);
    });
    loadProvinces();
    const applyVoucherBtn = document.getElementById('applyVoucherBtn');
    if (applyVoucherBtn) applyVoucherBtn.addEventListener('click', applyVoucherFromCheckout);
    if (voucherCode) applyVoucherFromCheckout();

    function setVoucherMessage(msg, ok) {
        if (!voucherMsg) return;
        voucherMsg.textContent = msg || '';
        voucherMsg.style.color = ok ? '#16a34a' : '#ef4444';
    }
    function clearVoucher() {
        voucherDiscount = 0;
        appliedVoucherCode = '';
        if (voucherHidden) voucherHidden.value = '';
        updateTotals();
    }
    function applyVoucher() {
        if (!voucherInput || !voucherBtn) return;
        var code = (voucherInput.value || '').trim();
        if (!code) {
            clearVoucher();
            setVoucherMessage('Vui lòng nhập mã voucher.', false);
            return;
        }
        voucherBtn.disabled = true;
        voucherBtn.dataset.text = voucherBtn.dataset.text || voucherBtn.textContent;
        voucherBtn.textContent = 'Đang kiểm tra...';
        var fdv = new FormData();
        fdv.append('voucher_code', code);
        fetch(SITEURL + 'api/voucher-apply.php', { method: 'POST', body: fdv })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res && res.success) {
                    voucherDiscount = parseFloat(res.discount || 0) || 0;
                    appliedVoucherCode = res.code || code;
                    if (voucherHidden) voucherHidden.value = appliedVoucherCode;
                    setVoucherMessage(res.message || 'Đã áp dụng voucher.', true);
                } else {
                    clearVoucher();
                    setVoucherMessage((res && res.message) ? res.message : 'Voucher không hợp lệ.', false);
                }
                updateTotals();
            })
            .catch(function() {
                clearVoucher();
                setVoucherMessage('Không thể kiểm tra voucher. Vui lòng thử lại.', false);
            })
            .finally(function() {
                voucherBtn.disabled = false;
                voucherBtn.textContent = voucherBtn.dataset.text || 'Áp dụng';
            });
    }

    if (voucherBtn) voucherBtn.addEventListener('click', applyVoucher);
    if (voucherInput) {
        voucherInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); applyVoucher(); }
        });
        voucherInput.addEventListener('input', saveDraft);
    }

    updateTotals();

    if (btnSticky) {
        btnSticky.addEventListener('click', function() {
            if (btn && !btn.disabled) form.requestSubmit();
        });
    }

    function syncStickyButton() {
        if (!btnSticky) return;
        btnSticky.disabled = btn ? btn.disabled : false;
        var textEl = btnSticky.querySelector('.btn-text');
        var priceEl = btnSticky.querySelector('.btn-price');
        if (btn && textEl) textEl.textContent = btn.querySelector('.btn-text').textContent;
        if (btn && priceEl) priceEl.textContent = btn.querySelector('.btn-price').textContent;
    }
    if (btn) {
        var obs = new MutationObserver(syncStickyButton);
        obs.observe(btn, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled'] });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const name = (document.getElementById('customer_name').value || '').trim();
        const contact = (document.getElementById('customer_contact').value || '').trim();
        const email = (document.getElementById('customer_email').value || '').trim();
        const address = (document.getElementById('customer_address').value || '').trim();
        const note = (document.getElementById('order_note').value || '').trim();
        const payment = document.querySelector('input[name="payment_method"]:checked');
        const paymentMethod = payment ? payment.value : 'cash';
        const toDistrictId = selDistrict ? selDistrict.value : '';
        const toWardCode = selWard ? selWard.value : '';
        const toProvinceId = selProvince ? selProvince.value : '';
        var provinceName = (selProvince && selProvince.selectedIndex >= 0) ? (selProvince.options[selProvince.selectedIndex].text || '').trim() : '';
        var districtName = (selDistrict && selDistrict.selectedIndex >= 0) ? (selDistrict.options[selDistrict.selectedIndex].text || '').trim() : '';
        var wardName = (selWard && selWard.selectedIndex >= 0) ? (selWard.options[selWard.selectedIndex].text || '').trim() : '';

        if (!name) { Swal.fire('Lỗi', 'Vui lòng nhập họ tên.', 'error'); return; }
        if (!contact) { Swal.fire('Lỗi', 'Vui lòng nhập số điện thoại.', 'error'); return; }
        if (!email) { Swal.fire('Lỗi', 'Vui lòng nhập email.', 'error'); return; }
        if (!address) { Swal.fire('Lỗi', 'Vui lòng nhập số nhà.', 'error'); return; }

        // Bắt buộc chọn đầy đủ địa chỉ GHN (Tỉnh -> Quận -> Phường/Xã)
        if (!toProvinceId || !toDistrictId || !toWardCode) {
            Swal.fire('Lỗi', 'Vui lòng chọn địa chỉ giao hàng đầy đủ (Tỉnh -> Quận -> Phường/Xã).', 'error');
            return;
        }

        btn.disabled = true;
        btn.querySelector('.btn-text').textContent = 'Đang xử lý...';
        if (btnSticky) { btnSticky.disabled = true; btnSticky.querySelector('.btn-text').textContent = 'Đang xử lý...'; }

        const fd = new FormData();
        fd.append('customer_name', name);
        fd.append('customer_contact', contact);
        fd.append('customer_email', email);
        fd.append('customer_address', address);
        fd.append('order_note', note);
        fd.append('payment_method', paymentMethod);
        fd.append('shipping_fee', shippingFee);
        if (toDistrictId) fd.append('to_district_id', toDistrictId);
        if (toWardCode) fd.append('to_ward_code', toWardCode);
        if (provinceName) fd.append('ghn_province_name', provinceName);
        if (districtName) fd.append('ghn_district_name', districtName);
        if (wardName) fd.append('ghn_ward_name', wardName);
        if (voucherCode) fd.append('voucher_code', voucherCode);
        <?php if (!empty($selected_cart_ids)): ?>
        fd.append('selected_cart_ids', <?php echo json_encode(implode(',', $selected_cart_ids)); ?>);
        <?php endif; ?>

        fetch(SITEURL + 'api/place-order.php', { method: 'POST', body: fd })
            .then(function(r) {
                return r.text().then(function(text) {
                    if (!r.ok) return { _error: true, status: r.status, text: text };
                    try { return JSON.parse(text); } catch (e) { return { _error: true, parseError: true, text: text }; }
                });
            })
            .then(function(data) {
                if (data._error) {
                    btn.disabled = false; if (btnSticky) btnSticky.disabled = false; var t = btn.querySelector('.btn-text'); if (t) t.textContent = 'Đặt hàng'; if (btnSticky) { var ts = btnSticky.querySelector('.btn-text'); if (ts) ts.textContent = 'Đặt hàng'; }
                    var msg = data.status ? ('Máy chủ lỗi (HTTP ' + data.status + ').') : 'Phản hồi không hợp lệ.';
                    if (data.text && data.text.length < 300) msg += ' ' + data.text; else if (data.text) msg += ' (xem Console để biết chi tiết)';
                    Swal.fire('Lỗi', msg, 'error');
                    if (data.text && data.text.length >= 300) console.error('place-order response:', data.text);
                    return;
                }
                if (data.success) {
                    // Thanh toán MoMo: tạo phiên và chuyển sang MoMo
                    if (paymentMethod === 'momo') {
                        const oc = data.order_code || '';
                        if (!oc) {
                            btn.disabled = false; btn.querySelector('.btn-text').textContent = 'Đặt hàng';
                            if (btnSticky) { btnSticky.disabled = false; btnSticky.querySelector('.btn-text').textContent = 'Đặt hàng'; }
                            Swal.fire('Lỗi', 'Thiếu mã đơn hàng để tạo thanh toán MoMo.', 'error');
                            return;
                        }
                        const fd2 = new FormData();
                        fd2.append('order_code', oc);
                        fetch(SITEURL + 'api/momo-create.php', { method: 'POST', body: fd2 })
                            .then(async (r2) => {
                                const text = await r2.text();
                                try { return JSON.parse(text); }
                                catch (e) { return { success:false, message:'API MoMo trả về không phải JSON', raw:text }; }
                            })
                            .then(m => {
                                if (m.success && (m.payUrl || m.deeplink)) {
                                    saveDraft();
                                    window.location.href = m.payUrl || m.deeplink;
                                } else {
                                    btn.disabled = false; btn.querySelector('.btn-text').textContent = 'Đặt hàng';
                                    if (btnSticky) { btnSticky.disabled = false; btnSticky.querySelector('.btn-text').textContent = 'Đặt hàng'; }
                                    Swal.fire('Lỗi', (m.message || 'Không thể tạo thanh toán MoMo.') + (m.raw ? ('\n\n' + String(m.raw).slice(0, 500)) : ''), 'error');
                                }
                            })
                            .catch(() => {
                                btn.disabled = false; btn.querySelector('.btn-text').textContent = 'Đặt hàng';
                                if (btnSticky) { btnSticky.disabled = false; btnSticky.querySelector('.btn-text').textContent = 'Đặt hàng'; }
                                Swal.fire('Lỗi', 'Không thể tạo thanh toán MoMo.', 'error');
                            });
                        return;
                    }
                    // Thanh toán VNPay: tạo URL thanh toán và chuyển sang cổng VNPAY
                    if (paymentMethod === 'vnpay') {
                        const oc = data.order_code || '';
                        if (!oc) {
                            btn.disabled = false; btn.querySelector('.btn-text').textContent = 'Đặt hàng';
                            if (btnSticky) { btnSticky.disabled = false; btnSticky.querySelector('.btn-text').textContent = 'Đặt hàng'; }
                            Swal.fire('Lỗi', 'Thiếu mã đơn hàng để tạo thanh toán VNPay.', 'error');
                            return;
                        }
                        const fd2 = new FormData();
                        fd2.append('order_code', oc);
                        fetch(SITEURL + 'api/vnpay-create.php', { method: 'POST', body: fd2 })
                            .then(r2 => r2.json())
                            .then(v => {
                                if (v.success && v.payUrl) {
                                    saveDraft();
                                    window.location.href = v.payUrl;
                                } else {
                                    btn.disabled = false; btn.querySelector('.btn-text').textContent = 'Đặt hàng';
                                    if (btnSticky) { btnSticky.disabled = false; btnSticky.querySelector('.btn-text').textContent = 'Đặt hàng'; }
                                    Swal.fire('Lỗi', v.message || 'Không thể tạo thanh toán VNPay.', 'error');
                                }
                            })
                            .catch(() => {
                                btn.disabled = false; btn.querySelector('.btn-text').textContent = 'Đặt hàng';
                                if (btnSticky) { btnSticky.disabled = false; btnSticky.querySelector('.btn-text').textContent = 'Đặt hàng'; }
                                Swal.fire('Lỗi', 'Không thể tạo thanh toán VNPay.', 'error');
                            });
                        return;
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Đặt hàng thành công!',
                        html: 'Mã đơn hàng: <strong>' + (data.order_code || '') + '</strong><br>Bạn sẽ nhận thông báo khi đơn có cập nhật.<br>Chat hỗ trợ đơn hàng và yêu cầu hoàn tiền: mục <strong>Chat</strong>.',
                        confirmButtonColor: '#ff6b81'
                    }).then(() => {
                        clearDraft();
                        var url = data.redirect || (SITEURL + 'user/order-history.php');
                        if (data.order_code) url += (url.indexOf('?') >= 0 ? '&' : '?') + 'order_code=' + encodeURIComponent(data.order_code);
                        window.location.href = url;
                    });
                } else {
                    btn.disabled = false; btn.querySelector('.btn-text').textContent = 'Đặt hàng';
                    if (btnSticky) { btnSticky.disabled = false; btnSticky.querySelector('.btn-text').textContent = 'Đặt hàng'; }
                    Swal.fire('Lỗi', data.message || 'Không thể đặt hàng. Vui lòng thử lại.', 'error');
                }
            })
            .catch(function(err) {
                btn.disabled = false; var t = btn.querySelector('.btn-text'); if (t) t.textContent = 'Đặt hàng';
                if (btnSticky) { btnSticky.disabled = false; var ts = btnSticky.querySelector('.btn-text'); if (ts) ts.textContent = 'Đặt hàng'; }
                Swal.fire('Lỗi', 'Kết nối thất bại hoặc có lỗi xảy ra. Vui lòng thử lại.', 'error');
            });
    });
})();
</script>
</body>
</html>
