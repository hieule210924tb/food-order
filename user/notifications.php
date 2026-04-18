<?php
include('../config/constants.php');

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = SITEURL . 'user/notifications.php';
    header('location:' . SITEURL . 'user/login.php');
    exit;
}

$conn->query("CREATE TABLE IF NOT EXISTS tbl_order_notification (
  id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  order_code varchar(20) NOT NULL,
  user_id int(10) UNSIGNED NOT NULL,
  message varchar(255) NOT NULL,
  is_read tinyint(1) DEFAULT 0,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY order_code (order_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$user_id = (int) $_SESSION['user_id'];

if (isset($_GET['mark_read']) && $_GET['mark_read'] === '1') {
    $conn->query("UPDATE tbl_order_notification SET is_read = 1 WHERE user_id = " . $user_id);
    header('Location: ' . SITEURL . 'user/notifications.php');
    exit;
}

if (isset($_GET['delete_all']) && $_GET['delete_all'] === '1') {
    $conn->query("DELETE FROM tbl_order_notification WHERE user_id = " . $user_id);
    header('Location: ' . SITEURL . 'user/notifications.php');
    exit;
}

$res = $conn->query("SELECT id, order_code, message, is_read, created_at FROM tbl_order_notification WHERE user_id = " . $user_id . " AND message NOT LIKE '%đang chờ thanh toán%' ORDER BY created_at DESC LIMIT 100");
$notifications = [];
if ($res) {
    while ($row = $res->fetch_assoc()) $notifications[] = $row;
    $res->free();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo đơn hàng - WowFood</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/order-history.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    .notif-page { max-width: 640px; margin: 0 auto; padding: 100px 24px 60px; }
    .notif-item { background: #fff; border-radius: 12px; padding: 14px 18px; margin-bottom: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: 1px solid #e8ecf4; }
    .notif-item.unread { border-left: 4px solid #ff6b81; }
    .notif-msg { font-size: 0.95rem; color: #2f3542; margin-bottom: 4px; }
    .notif-meta { font-size: 0.8rem; color: #64748b; }
    .notif-empty { text-align: center; padding: 48px 24px; color: #64748b; }
    .notif-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; align-items: center; }
    .notif-actions a, .notif-actions .notif-btn { display: inline-block; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; }
    .notif-btn-mark { background: #27ae60; color: #fff; }
    .notif-btn-mark:hover { background: #219a52; color: #fff; }
    .notif-btn-delete { background: #e74c3c; color: #fff; }
    .notif-btn-delete:hover { background: #c0392b; color: #fff; }
    </style>
</head>
<body>
<?php include('../partials-front/menu.php'); ?>

<div class="order-history-page notif-page">
    <div class="order-history-breadcrumb">
        <a href="<?php echo SITEURL; ?>">Trang chủ</a>
        <span class="sep">/</span>
        <span class="current">Thông báo đơn hàng</span>
    </div>
    <h1 class="order-history-title">
        <span class="order-history-title-icon"><i class="bi bi-bell"></i></span>
        Thông báo đơn hàng
    </h1>

    <?php if (!empty($notifications)): ?>
    <div class="notif-actions">
        <a href="<?php echo SITEURL; ?>user/notifications.php?mark_read=1" class="notif-btn notif-btn-mark"><i class="bi bi-check-all"></i> Đánh dấu đã đọc</a>
        <a href="<?php echo SITEURL; ?>user/notifications.php?delete_all=1" class="notif-btn notif-btn-delete" id="notifDeleteAll">🗑 Xóa tất cả thông báo</a>
    </div>
    <?php endif; ?>

    <?php if (empty($notifications)): ?>
        <div class="notif-empty">Chưa có thông báo nào.</div>
    <?php else: ?>
        <div class="order-history-list">
            <?php foreach ($notifications as $n): ?>
            <div class="notif-item <?php echo ($n['is_read'] ? '' : 'unread'); ?>">
                <div class="notif-msg"><?php echo htmlspecialchars($n['message']); ?></div>
                <div class="notif-meta"><?php echo date('d/m/Y H:i', strtotime($n['created_at'])); ?> · Mã đơn: <?php echo htmlspecialchars($n['order_code']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include('../partials-front/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function() {
    var btn = document.getElementById('notifDeleteAll');
    if (btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var url = this.getAttribute('href');
            Swal.fire({
                title: 'Xóa tất cả thông báo?',
                text: 'Bạn có chắc muốn xóa tất cả thông báo? Không thể hoàn tác.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Xóa tất cả',
                cancelButtonText: 'Hủy'
            }).then(function(result) {
                if (result.isConfirmed && url) {
                    window.location.href = url;
                }
            });
        });
    }
})();
</script>
</body>
</html>
