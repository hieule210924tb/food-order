<?php

include('../config/constants.php');
require_once('partials/login-check.php');
require_once('partials/menu.php');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$has_order_details_col = false;
$r = @$conn->query("SHOW COLUMNS FROM tbl_order LIKE 'order_details'");
if ($r && $r->num_rows > 0) $has_order_details_col = true;

$sql = "SELECT id, order_code, user_id, food, price, qty, total, order_date, status, customer_name, customer_contact, customer_email, customer_address";
if ($has_order_details_col) $sql .= ", order_details";
$sql .= " FROM tbl_order ORDER BY order_date DESC";
$params = [];
$types = '';
if ($search !== '') {
    $sql = "SELECT id, order_code, user_id, food, price, qty, total, order_date, status, customer_name, customer_contact, customer_email, customer_address";
    if ($has_order_details_col) $sql .= ", order_details";
    $sql .= " FROM tbl_order WHERE order_code LIKE ? OR customer_name LIKE ? OR customer_contact LIKE ? ORDER BY order_date DESC";
    $like = '%' . $search . '%';
    $params = [$like, $like, $like];
    $types = 'sss';
}

function formatOrderDetailPrice($n) {
    return number_format((float)$n, 0, ',', '.') . ' ₫';
}
function renderOrderDetailsHtml($order_details_json, $food_fallback) {
    if (empty($order_details_json)) return $food_fallback;
    $items = @json_decode($order_details_json, true);
    if (!is_array($items)) return $food_fallback;
    $lines = [];
    foreach ($items as $item) {
        $title = isset($item['title']) ? $item['title'] : '';
        $qty = isset($item['qty']) ? (int)$item['qty'] : 1;
        $lines[] = $title . ' x ' . $qty;
        if (!empty($item['size_name']) && isset($item['size_add'])) {
            $add = (float)$item['size_add'];
            $lines[] = $item['size_name'] . ' (+' . formatOrderDetailPrice($add) . ')';
        }
        if (!empty($item['sides']) && is_array($item['sides'])) {
            foreach ($item['sides'] as $s) {
                $name = isset($s['name']) ? $s['name'] : '';
                $price = isset($s['price']) ? (float)$s['price'] : 0;
                if ($name !== '') $lines[] = $name . ' (+' . formatOrderDetailPrice($price) . ')';
            }
        }
    }
    return implode("\n", $lines);
}

if (count($params) > 0) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}


if (isset($_POST['update_status']) && isset($_POST['order_code']) && isset($_POST['status'])) {
    $oc = trim($_POST['order_code']);
    $st = trim($_POST['status']);
    $allow = ['Pending', 'Pending Payment', 'Confirmed', 'On Delivery', 'Delivered', 'Cancelled', 'Returning', 'Returned'];
    if ($oc !== '' && in_array($st, $allow, true)) {
        $conn->query("UPDATE tbl_order SET status = '" . $conn->real_escape_string($st) . "' WHERE order_code = '" . $conn->real_escape_string($oc) . "'");

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
        $msg_map = [
            'Pending' => 'Đơn ' . $oc . ' đã được xác nhận.',
            'Confirmed' => 'Đơn ' . $oc . ' đã được xác nhận.',
            'On Delivery' => 'Đơn ' . $oc . ' đang giao.',
            'Delivered' => 'Đơn ' . $oc . ' đã giao.',
            'Cancelled' => 'Đơn ' . $oc . ' đã hủy.',
            'Returning' => 'Đơn ' . $oc . ' đang hoàn.',
            'Returned' => 'Đơn ' . $oc . ' đã hoàn.',
        ];
        $notif_msg = isset($msg_map[$st]) ? $msg_map[$st] : 'Đơn ' . $oc . ' đã cập nhật trạng thái: ' . $st . '.';
        $row = $conn->query("SELECT user_id FROM tbl_order WHERE order_code = '" . $conn->real_escape_string($oc) . "' LIMIT 1")->fetch_assoc();
        if ($row && !empty($row['user_id'])) {
            $uid = (int) $row['user_id'];
            $ins = $conn->prepare("INSERT INTO tbl_order_notification (order_code, user_id, message) VALUES (?, ?, ?)");
            if ($ins) {
                $ins->bind_param("sis", $oc, $uid, $notif_msg);
                $ins->execute();
                $ins->close();
            }
        }
    }
    header('Location: manage-order.php' . ($search !== '' ? '?search=' . urlencode($search) : ''));
    exit;
}
?>
<div class="main-content">
    <div class="wrapper">
        <h1 style="margin-bottom: 10px;">Quản lý đơn hàng</h1>
        <p style="color:#747d8c;margin-bottom:25px;"></p>

        <form method="get" style="margin-bottom: 20px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Mã đơn / tên / SĐT"
                style="padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px; width: 280px;">
            <button type="submit"
                style="padding: 10px 18px; background: #ff6b81; color: #fff; border: none; border-radius: 8px; cursor: pointer;">Tìm</button>
        </form>

        <div
            style="background:#fff;border-radius:12px;padding:18px 20px;box-shadow:0 4px 14px rgba(0,0,0,0.06);border:1px solid #ecf0f1;overflow-x:auto;">
            <table class="tbl-full" style="width:100%;border-collapse:separate;border-spacing:0;font-size:14px;">
                <thead>
                    <tr style="background:#f8f9fb;">
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">Mã đơn</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">Khách hàng</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">Chi tiết đơn hàng
                        </th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:right;">Tổng tiền</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">Trạng thái</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">Ngày đặt</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:center;">Cập nhật trạng
                            thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $status = $row['status'] ?? 'Pending';
                            $status_labels = ['Pending' => 'Chờ xử lý','Confirmed' => 'Đã xác nhận', 'On Delivery' => 'Đang giao', 'Delivered' => 'Đã giao', 'Cancelled' => 'Đã hủy', 'Returning' => 'Đang hoàn', 'Returned' => 'Đã hoàn'];
                            $status_text = isset($status_labels[$status]) ? $status_labels[$status] : $status;
                            $status_style = 'color:#57606f;';
                            if ($status === 'Delivered') $status_style = 'color:green;';
                            elseif ($status === 'Cancelled') $status_style = 'color:red;';
                            elseif ($status === 'On Delivery') $status_style = 'color:#0984e3;';
                            elseif ($status === 'Confirmed') $status_style = 'color:#27ae60;';
                            $food_fallback = isset($row['food']) ? $row['food'] : '';
                            $order_details_json = ($has_order_details_col && isset($row['order_details'])) ? $row['order_details'] : '';
                            $detail_html = renderOrderDetailsHtml($order_details_json, $food_fallback);
                    ?>
                    <tr class="order-main-row" style="border-bottom:1px solid #f0f2f5;">
                        <td style="padding:10px 8px;font-weight:600;">
                            <?php echo htmlspecialchars($row['order_code']); ?></td>
                        <td style="padding:10px 8px;font-size:13px;">
                            <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($row['customer_contact']); ?></small><br>
                            <small style="color:#57606f;">Địa chỉ:
                                <?php echo nl2br(htmlspecialchars($row['customer_address'] ?? '')); ?></small>
                        </td>
                        <td style="padding:10px 8px;font-size:13px;max-width:280px;">
                            <button type="button" class="admin-order-detail-toggle" aria-expanded="false"
                                style="background:#f1f3f5;border:1px solid #dee2e6;border-radius:6px;padding:4px 10px;font-size:12px;cursor:pointer;margin-bottom:6px;">▶
                                Chi tiết đơn hàng</button>
                            <div class="admin-order-detail-content"
                                style="line-height:1.6;white-space:pre-line;display:none;">
                                <?php echo nl2br(htmlspecialchars($detail_html)); ?></div>
                        </td>
                        <td style="padding:10px 8px;text-align:right;font-weight:600;color:#ff6b81;">
                            <?php echo number_format((float)$row['total'], 0, ',', '.'); ?> đ</td>
                        <td style="padding:10px 8px;"><span
                                style="<?php echo $status_style; ?>"><?php echo htmlspecialchars($status_text); ?></span>
                        </td>
                        <td style="padding:10px 8px;"><?php echo date('d/m/Y H:i', strtotime($row['order_date'])); ?>
                        </td>
                        <td style="padding:10px 8px;text-align:center;">
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="order_code"
                                    value="<?php echo htmlspecialchars($row['order_code']); ?>">
                                <select name="status" onchange="this.form.submit()"
                                    style="padding:6px 10px;border-radius:6px;font-size:12px;">
                                    <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Chờ
                                        xử lý</option>
                                    <option value="Confirmed" <?php echo $status === 'Confirmed' ? 'selected' : ''; ?>>
                                        Đã xác nhận</option>
                                    <option value="On Delivery"
                                        <?php echo $status === 'On Delivery' ? 'selected' : ''; ?>>Đang giao</option>
                                    <option value="Delivered" <?php echo $status === 'Delivered' ? 'selected' : ''; ?>>
                                        Đã giao</option>
                                    <option value="Cancelled" <?php echo $status === 'Cancelled' ? 'selected' : ''; ?>>
                                        Đã hủy</option>
                                    <option value="Returning" <?php echo $status === 'Returning' ? 'selected' : ''; ?>>
                                        Đang hoàn</option>
                                    <option value="Returned" <?php echo $status === 'Returned' ? 'selected' : ''; ?>>Đã
                                        hoàn</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="7" style="padding:24px;text-align:center;color:#747d8c;">Chưa có đơn hàng.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('.admin-order-detail-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var cell = this.closest('td');
        var content = cell ? cell.querySelector('.admin-order-detail-content') : null;
        if (!content) return;
        var open = content.style.display !== 'none';
        content.style.display = open ? 'none' : 'block';
        this.setAttribute('aria-expanded', open ? 'false' : 'true');
        this.textContent = open ? '▶ Chi tiết đơn hàng' : '▼ Thu gọn';
    });
});
</script>
<?php include('partials/footer.php'); ?>