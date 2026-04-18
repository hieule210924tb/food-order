<?php
include('../config/constants.php');
require_once('partials/login-check.php');
require_once('partials/menu.php');
?>

<?php
$search_payment = isset($_GET['search']) ? trim($_GET['search']) : '';
?>
<div class="main-content">
    <div class="wrapper">
        <h1 style="margin-bottom: 10px;">Quản lý thanh toán</h1>
        <p style="color:#747d8c;margin-bottom:25px;">Theo dõi chi tiết các giao dịch thanh toán của khách hàng theo thời
            gian thực.</p>

        <form method="get" style="margin-bottom:20px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search_payment); ?>"
                placeholder="Mã đơn hàng"
                style="padding:10px 14px;border:1px solid #ddd;border-radius:8px;width:260px;">
            <button type="submit"
                style="padding:10px 18px;background:#ff6b81;color:#fff;border:none;border-radius:8px;cursor:pointer;">Tìm</button>
        </form>

        <div
            style="background:#ffffff;border-radius:12px;padding:18px 20px;box-shadow:0 4px 14px rgba(0,0,0,0.06);border:1px solid #ecf0f1;overflow-x:auto;">
            <table class="tbl-full" style="width:100%;border-collapse:separate;border-spacing:0;font-size:14px;">
                <thead>
                    <tr style="background:#f8f9fb;">
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">ID</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">Mã đơn hàng</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">Phương thức</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:right;">Số tiền</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">Trạng thái</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">Mã giao dịch</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">Ngày tạo</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">Ngày thanh toán
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
            function paymentMethodLabel($method) {
                $map = ['cash' => 'Tiền mặt', 'momo' => 'MoMo', 'vnpay' => 'VNPay'];
                return $map[strtolower($method)] ?? strtoupper($method);
            }
            $all_rows = [];
            $payment_sql = "SELECT * FROM tbl_payment";
            if ($search_payment !== '') {
                $payment_sql .= " WHERE order_code LIKE '%" . mysqli_real_escape_string($conn, $search_payment) . "%'";
            }
            $payment_sql .= " ORDER BY id DESC";
            $payment_res = mysqli_query($conn, $payment_sql);
            if ($payment_res && mysqli_num_rows($payment_res) > 0) {
                while ($payment = mysqli_fetch_assoc($payment_res)) {
                    $payment['_source'] = 'payment';
                    $all_rows[] = $payment;
                }
            }
            $cash_sql = "SELECT o.id, o.order_code, o.user_id, o.total AS amount, o.order_date AS created_at FROM tbl_order o LEFT JOIN tbl_payment p ON p.order_code = o.order_code WHERE p.order_code IS NULL";
            if ($search_payment !== '') {
                $cash_sql .= " AND o.order_code LIKE '%" . mysqli_real_escape_string($conn, $search_payment) . "%'";
            }
            $cash_sql .= " ORDER BY o.order_date DESC";
            $cash_res = @mysqli_query($conn, $cash_sql);
            if ($cash_res && mysqli_num_rows($cash_res) > 0) {
                while ($order = mysqli_fetch_assoc($cash_res)) {
                    $order['payment_method'] = 'cash';
                    $order['payment_status'] = 'success';
                    $order['transaction_id'] = null;
                    $order['paid_at'] = $order['created_at'];
                    $order['_source'] = 'order';
                    $all_rows[] = $order;
                }
            }
            usort($all_rows, function($a, $b) {
                $t1 = strtotime($a['created_at'] ?? 0);
                $t2 = strtotime($b['created_at'] ?? 0);
                return $t2 - $t1;
            });
            if (!empty($all_rows)) {
                foreach ($all_rows as $payment) {
                    $status_class = '';
                    $status_text = '';
                    $st = $payment['payment_status'] ?? '';
                    if ($st === 'success' || $st === 'paid') {
                        $status_class = 'style="color: green;"';
                        $status_text = 'Thành công';
                    } else {
                        $status_class = 'style="color: red;"';
                        $status_text = 'Thất bại';
                    }
                    $paid_at = $payment['paid_at'] ?? $payment['updated_at'] ?? ($payment['payment_status'] === 'success' || ($payment['payment_status'] ?? '') === 'paid' ? ($payment['created_at'] ?? null) : null);
                    ?>
                    <tr style="border-bottom:1px solid #f0f2f5;">
                        <td style="padding:10px 8px;"><?php echo (int)($payment['id'] ?? 0); ?></td>
                        <td style="padding:10px 8px;font-weight:600;color:#2c3e50;">
                            <?php echo htmlspecialchars($payment['order_code']); ?>
                        </td>
                        <td style="padding:10px 8px;">
                            <span
                                style="display:inline-block;padding:4px 10px;border-radius:999px;background:#f1f2f6;font-size:12px;color:#57606f;">
                                <?php echo htmlspecialchars(paymentMethodLabel($payment['payment_method'])); ?>
                            </span>
                        </td>
                        <td style="padding:10px 8px;text-align:right;font-weight:600;color:#ff6b81;">
                            <?php echo number_format((float)($payment['amount'] ?? 0), 0, ',', '.'); ?> đ
                        </td>
                        <td style="padding:10px 8px;">
                            <span <?php echo $status_class; ?>
                                style="display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600;">
                                <?php echo $status_text; ?>
                            </span>
                        </td>
                        <td style="padding:10px 8px;">
                            <?php echo htmlspecialchars($payment['transaction_id'] ?? '—'); ?>
                        </td>
                        <td style="padding:10px 8px;">
                            <?php echo !empty($payment['created_at']) ? date('d/m/Y H:i', strtotime($payment['created_at'])) : '—'; ?>
                        </td>
                        <td style="padding:10px 8px;">
                            <?php echo !empty($paid_at) ? date('d/m/Y H:i', strtotime($paid_at)) : '—'; ?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                echo '<tr><td colspan="8" class="error" style="padding:14px 8px;text-align:center;">Chưa có giao dịch thanh toán nào</td></tr>';
            }
            ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include('partials/footer.php'); ?>