<?php
include('../config/constants.php');
require_once('partials/login-check.php');

if(isset($_POST['approve_refund']) && isset($_POST['refund_id'])) {
    $refund_id = intval($_POST['refund_id']);
    if ($refund_id > 0) {
        $refund_row = $conn->query("SELECT order_code, user_id FROM tbl_refund WHERE id = " . $refund_id . " AND refund_status = 'pending' LIMIT 1")->fetch_assoc();
        if ($refund_row) {
            $oc = $refund_row['order_code'];
            $conn->query("UPDATE tbl_order SET status = 'Returning' WHERE order_code = '" . $conn->real_escape_string($oc) . "'");
            $admin_id = $_SESSION['admin_id'] ?? 0;
            $conn->query("UPDATE tbl_refund SET refund_status = 'processing', processed_by = " . (int)$admin_id . " WHERE id = " . $refund_id);
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
            $notif_msg = "Yêu cầu hoàn tiền đơn " . $oc . " đã được phê duyệt. Đơn đang được xử lý hoàn.";
            $uid = (int) $refund_row['user_id'];
            $ins = $conn->prepare("INSERT INTO tbl_order_notification (order_code, user_id, message) VALUES (?, ?, ?)");
            if ($ins) {
                $ins->bind_param("sis", $oc, $uid, $notif_msg);
                $ins->execute();
                $ins->close();
            }
            $_SESSION['refund-success'] = "Đã phê duyệt yêu cầu hoàn tiền đơn " . $oc . ". Trạng thái đơn đã tự cập nhật: Đang hoàn.";
        }
    }
    header('location:' . SITEURL . 'admin/refund.php');
    exit();
}

if(isset($_POST['reject_refund']) && isset($_POST['refund_id'])) {
    $refund_id = intval($_POST['refund_id']);
    if ($refund_id > 0) {
        $refund_row = $conn->query("SELECT order_code, user_id FROM tbl_refund WHERE id = " . $refund_id . " AND refund_status = 'pending' LIMIT 1")->fetch_assoc();
        if ($refund_row) {
            $admin_id = $_SESSION['admin_id'] ?? 0;
            $conn->query("UPDATE tbl_refund SET refund_status = 'rejected', processed_by = " . (int)$admin_id . " WHERE id = " . $refund_id);
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
            $oc = $refund_row['order_code'];
            $notif_msg = "Yêu cầu hoàn tiền đơn " . $oc . " đã bị từ chối.";
            $uid = (int) $refund_row['user_id'];
            $ins = $conn->prepare("INSERT INTO tbl_order_notification (order_code, user_id, message) VALUES (?, ?, ?)");
            if ($ins) {
                $ins->bind_param("sis", $oc, $uid, $notif_msg);
                $ins->execute();
                $ins->close();
            }
            $_SESSION['refund-success'] = "Đã từ chối yêu cầu hoàn tiền đơn " . $oc . ".";
        }
    }
    header('location:' . SITEURL . 'admin/refund.php');
    exit();
}


if(isset($_POST['update_refund_status'])) {
    $refund_id = intval($_POST['refund_id'] ?? 0);
    $refund_status = $_POST['refund_status'] ?? 'pending';
    $refund_transaction_id = mysqli_real_escape_string($conn, $_POST['refund_transaction_id'] ?? '');
    
    $update_sql = "UPDATE tbl_refund SET 
        refund_status = ?,
        refund_transaction_id = ?,
        processed_by = ?,
        processed_at = " . ($refund_status == 'completed' ? "NOW()" : "NULL") . ",
        updated_at = NOW()
        WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $update_sql);
    $admin_id = $_SESSION['admin_id'] ?? null;
    mysqli_stmt_bind_param($stmt, "ssii", $refund_status, $refund_transaction_id, $admin_id, $refund_id);
    
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['refund-success'] = "Cập nhật trạng thái hoàn tiền thành công!";
        // Khi hoàn thành hoàn tiền: hệ thống tự cập nhật đơn → Đã hoàn (Returned) + thông báo user
        if ($refund_status === 'completed' && $refund_id > 0) {
            $refund_row = $conn->query("SELECT order_code, user_id FROM tbl_refund WHERE id = " . (int)$refund_id . " LIMIT 1")->fetch_assoc();
            if ($refund_row) {
                $oc = $refund_row['order_code'];
                $conn->query("UPDATE tbl_order SET status = 'Returned' WHERE order_code = '" . $conn->real_escape_string($oc) . "'");
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
                if (!empty($refund_row['user_id'])) {
                    $uid = (int) $refund_row['user_id'];
                    $notif_msg = "Đơn " . $oc . " đã được hoàn tiền.";
                    $ins = $conn->prepare("INSERT INTO tbl_order_notification (order_code, user_id, message) VALUES (?, ?, ?)");
                    if ($ins) {
                        $ins->bind_param("sis", $oc, $uid, $notif_msg);
                        $ins->execute();
                        $ins->close();
                    }
                }
            }
        }
    } else {
        $_SESSION['refund-error'] = "Có lỗi xảy ra!";
    }
    mysqli_stmt_close($stmt);
    
    header('location:'.SITEURL.'admin/refund.php');
    exit();
}

require_once('partials/menu.php');
?>

<div class="main-content">
    <div class="wrapper">
        <h1 style="margin-bottom: 8px;">Quản lý hoàn tiền</h1>
        <p style="color:#747d8c;margin-bottom:25px;max-width:600px;">
            Theo dõi và xử lý các yêu cầu hoàn tiền từ khách hàng một cách nhanh chóng, rõ ràng và minh bạch.
        </p>

        <?php
        if(isset($_SESSION['refund-success'])) {
            echo '<div class="success">' . $_SESSION['refund-success'] . '</div>';
            unset($_SESSION['refund-success']);
        }
        if(isset($_SESSION['refund-error'])) {
            echo '<div class="error">' . $_SESSION['refund-error'] . '</div>';
            unset($_SESSION['refund-error']);
        }
        ?>
        <form method="get" style="margin-bottom:16px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                placeholder="Mã đơn / tên / email khách"
                style="padding:9px 12px;border:1px solid #dde1e7;border-radius:8px;width:260px;">
            <button type="submit"
                style="padding:9px 16px;background:#ff6b81;color:#fff;border:none;border-radius:8px;cursor:pointer;">Tìm</button>
        </form>
        <div
            style="background:#ffffff;border-radius:12px;padding:18px 20px;box-shadow:0 4px 14px rgba(0,0,0,0.06);border:1px solid #ecf0f1;overflow-x:auto;">
            <table class="tbl-full" style="width:100%;border-collapse:separate;border-spacing:0;font-size:13px;">
                <thead>
                    <tr style="background:#f8f9fb;">
                        <th style="padding:10px;border-bottom:1px solid #e0e6ed;text-align:left;">ID</th>
                        <th style="padding:10px;border-bottom:1px solid #e0e6ed;text-align:left;">Mã đơn hàng</th>
                        <th style="padding:10px;border-bottom:1px solid #e0e6ed;text-align:left;">Khách hàng</th>
                        <th style="padding:10px;border-bottom:1px solid #e0e6ed;text-align:right;">Số tiền</th>
                        <th style="padding:10px;border-bottom:1px solid #e0e6ed;text-align:left;">Lý do</th>
                        <th style="padding:10px;border-bottom:1px solid #e0e6ed;text-align:left;">Trạng thái</th>
                        <th style="padding:10px;border-bottom:1px solid #e0e6ed;text-align:left;">Phương thức</th>
                        <th style="padding:10px;border-bottom:1px solid #e0e6ed;text-align:left;">Ngày tạo</th>
                        <th style="padding:10px;border-bottom:1px solid #e0e6ed;text-align:left;">Người xử lý</th>
                        <th style="padding:10px;border-bottom:1px solid #e0e6ed;text-align:center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
            $search_refund = isset($_GET['search']) ? trim($_GET['search']) : '';
            $refund_sql = "SELECT r.*, a.full_name as admin_name, u.full_name as user_name, u.email as user_email 
                          FROM tbl_refund r 
                          LEFT JOIN tbl_admin a ON r.processed_by = a.id 
                          LEFT JOIN tbl_user u ON r.user_id = u.id";
            if ($search_refund !== '') {
                $like = '%' . mysqli_real_escape_string($conn, $search_refund) . '%';
                $refund_sql .= " WHERE r.order_code LIKE '" . $like . "' OR u.full_name LIKE '" . $like . "' OR u.email LIKE '" . $like . "'";
            }
            $refund_sql .= " ORDER BY r.id DESC";
            $refund_res = mysqli_query($conn, $refund_sql);
            
            if(mysqli_num_rows($refund_res) > 0) {
                while($refund = mysqli_fetch_assoc($refund_res)) {
                    $status_class = '';
                    $status_text = '';
                    switch($refund['refund_status']) {
                        case 'pending':
                            $status_class = 'style="color: orange;"';
                            $status_text = 'Chờ xử lý';
                            break;
                        case 'processing':
                            $status_class = 'style="color: blue;"';
                            $status_text = 'Đang xử lý';
                            break;
                        case 'completed':
                            $status_class = 'style="color: green;"';
                            $status_text = 'Hoàn thành';
                            break;
                        case 'failed':
                            $status_class = 'style="color: red;"';
                            $status_text = 'Thất bại';
                            break;
                        case 'rejected':
                            $status_class = 'style="color: #95a5a6;"';
                            $status_text = 'Đã từ chối';
                            break;
                    }
                    ?>
                    <tr>
                        <td style="padding:9px 8px;"><?php echo $refund['id']; ?></td>
                        <td style="padding:9px 8px;font-weight:600;color:#2c3e50;">
                            <?php echo htmlspecialchars($refund['order_code']); ?>
                        </td>
                        <td style="padding:9px 8px;">
                            <?php if($refund['user_name']): ?>
                            <strong><?php echo htmlspecialchars($refund['user_name']); ?></strong><br>
                            <small
                                style="color:#666;"><?php echo htmlspecialchars($refund['user_email'] ?? ''); ?></small>
                            <?php else: ?>
                            <span style="color:#999;">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:9px 8px;text-align:right;font-weight:600;color:#ff6b81;">
                            <?php echo number_format($refund['refund_amount'], 0, ',', '.'); ?> đ
                        </td>
                        <td style="padding:9px 8px;max-width:240px;word-wrap:break-word;color:#34495e;">
                            <?php echo htmlspecialchars($refund['refund_reason']); ?>
                        </td>
                        <td <?php echo $status_class; ?> style="padding:9px 8px;">
                            <span
                                style="display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;background:#f1f2f6;">
                                <?php echo $status_text; ?>
                            </span>
                        </td>
                        <td style="padding:9px 8px;"><?php echo htmlspecialchars($refund['refund_method'] ?? 'N/A'); ?>
                        </td>
                        <td style="padding:9px 8px;"><?php echo date('d/m/Y H:i', strtotime($refund['created_at'])); ?>
                        </td>
                        <td style="padding:9px 8px;">
                            <?php if (empty($refund['processed_by']) || $refund['processed_by'] == 0): ?>
                            <span style="color:#e67e22;font-weight:600;">Khách yêu cầu</span>
                            <?php else: ?>
                            <?php echo htmlspecialchars($refund['admin_name'] ?? 'Đang xử lý'); ?>
                            <?php endif; ?>
                        </td>
                        <td style="padding:9px 8px;text-align:center;white-space:nowrap;">
                            <?php if($refund['refund_status'] == 'pending'): ?>
                            <form method="post" style="display:inline-block;margin-right:6px;">
                                <input type="hidden" name="approve_refund" value="1">
                                <input type="hidden" name="refund_id" value="<?php echo (int)$refund['id']; ?>">
                                <button type="submit" class="btn-secondary"
                                    style="padding:6px 12px;font-size:12px;border-radius:999px;border:1px solid #27ae60;background:#2ecc71;color:#fff;font-weight:500;cursor:pointer;">Phê
                                    duyệt</button>
                            </form>
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="reject_refund" value="1">
                                <input type="hidden" name="refund_id" value="<?php echo (int)$refund['id']; ?>">
                                <button type="submit" class="btn-secondary"
                                    style="padding:6px 12px;font-size:12px;border-radius:999px;border:1px solid #e74c3c;background:#e74c3c;color:#fff;font-weight:500;cursor:pointer;">Từ
                                    chối</button>
                            </form>
                            <?php elseif($refund['refund_status'] == 'processing'): ?>
                            <button
                                onclick="openRefundModal(<?php echo $refund['id']; ?>, '<?php echo htmlspecialchars($refund['order_code'], ENT_QUOTES); ?>', '<?php echo $refund['refund_status']; ?>')"
                                class="btn-secondary"
                                style="padding:6px 12px;font-size:12px;border-radius:999px;border:1px solid #3498db;background:#3498ff;color:#fff;font-weight:500;cursor:pointer;">
                                Đánh dấu đã hoàn
                            </button>
                            <?php elseif($refund['refund_status'] == 'completed'): ?>
                            <span style="color:#27ae60;font-weight:600;">Đã xử lý</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                echo '<tr><td colspan="10" class="error" style="padding:14px 8px;text-align:center;">Chưa có yêu cầu hoàn tiền nào</td></tr>';
            }
            ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div id="refundModal"
    style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1000;">
    <div
        style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#ffffff;padding:24px 26px;border-radius:14px;max-width:520px;width:92%;box-shadow:0 10px 30px rgba(0,0,0,0.25);border:1px solid #ecf0f1;">
        <h2 style="margin-bottom:14px;">Cập nhật trạng thái hoàn tiền</h2>
        <p style="color:#95a5a6;font-size:13px;margin-bottom:16px;">Chọn trạng thái mới và (nếu có) nhập mã giao dịch
            hoàn tiền từ cổng thanh toán.</p>
        <form method="POST" action="">
            <input type="hidden" name="refund_id" id="modal_refund_id">
            <table class="tbn-30" style="width:100%;border-collapse:separate;border-spacing:0 10px;font-size:13px;">
                <tr>
                    <td style="width:140px;color:#2f3542;font-weight:600;">Mã đơn hàng</td>
                    <td><strong id="modal_order_code"></strong></td>
                </tr>
                <tr>
                    <td style="color:#2f3542;font-weight:600;">Trạng thái *</td>
                    <td>
                        <select name="refund_status" id="modal_refund_status" required
                            style="width:100%;padding:9px 11px;border:1px solid #dde1e7;border-radius:8px;box-sizing:border-box;font-size:13px;background:#fff;">
                            <option value="pending">Chờ xử lý</option>
                            <option value="processing">Đang xử lý</option>
                            <option value="completed">Hoàn thành</option>
                            <option value="rejected">Đã từ chối</option>
                            <option value="failed">Thất bại</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td style="color:#2f3542;font-weight:600;">Mã giao dịch hoàn tiền</td>
                    <td>
                        <input type="text" name="refund_transaction_id" placeholder="Nếu có"
                            style="width:100%;padding:9px 11px;border:1px solid #dde1e7;border-radius:8px;box-sizing:border-box;font-size:13px;">
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top:6px;">
                        <input type="submit" name="update_refund_status" value="Cập nhật" class="btn-secondary"
                            style="padding:9px 20px;border-radius:999px;border:1px solid #3498db;background:#3498ff;color:#fff;font-weight:600;font-size:13px;cursor:pointer;box-shadow:0 6px 14px rgba(52,152,219,0.35);transition:all 0.15s;">
                        <button type="button" onclick="closeRefundModal()" class="btn-secondary"
                            style="margin-left:10px;padding:9px 20px;border-radius:999px;border:1px solid #dfe4ea;background:#ffffff;color:#2c3e50;font-size:13px;cursor:pointer;">
                            Hủy
                        </button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<script>
function openRefundModal(refundId, orderCode, currentStatus) {
    document.getElementById('modal_refund_id').value = refundId;
    document.getElementById('modal_order_code').textContent = orderCode;
    document.getElementById('modal_refund_status').value = currentStatus;
    document.getElementById('refundModal').style.display = 'block';
}

function closeRefundModal() {
    document.getElementById('refundModal').style.display = 'none';
}


document.getElementById('refundModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRefundModal();
    }
});
</script>

<?php include('partials/footer.php'); ?>