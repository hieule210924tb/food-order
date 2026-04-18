<?php
if (!defined('SITEURL')) {
    require_once __DIR__ . '/../../config/constants.php';
}
require_once __DIR__ . '/login-check.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - wowFood</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo SITEURL; ?>css/admin.css">
    <link rel="stylesheet" href="<?php echo SITEURL; ?>css/voucher-admin.css">
</head>

<body class="admin-body">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-brand">wowFood Admin</div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php"><i class="menu-icon bi bi-house-door"></i> Trang chủ</a></li>
                    <li><a href="manage-admin.php"><i class="menu-icon bi bi-people"></i> Quản trị viên</a></li>
                    <li><a href="manage-users.php"><i class="menu-icon bi bi-person"></i> Người dùng</a></li>
                    <li><a href="manage-category.php"><i class="menu-icon bi bi-folder2"></i> Danh mục</a></li>
                    <li><a href="manage-food.php"><i class="menu-icon bi bi-egg-fried"></i> Món ăn</a></li>
                    <li><a href="manage-side-dish.php"><i class="menu-icon bi bi-plus-square-dotted"></i> Món kèm</a></li>
                    <li><a href="manage-order.php"><i class="menu-icon bi bi-box-seam"></i> Đơn hàng</a></li>
                    <li><a href="manage-voucher.php"><i class="menu-icon bi bi-tag"></i> Voucher</a></li>
                    <li><a href="manage-payment.php"><i class="menu-icon bi bi-credit-card"></i> Thanh toán</a></li>
                    <li><a href="refund.php"><i class="menu-icon bi bi-cash-coin"></i> Hoàn tiền</a></li>
                    <li><a href="manage-chat.php" id="chatLinkAdmin" class="sidebar-link-chat"><i
                                class="menu-icon bi bi-chat-dots"></i> Chat <span id="chatBadgeAdmin" class="chat-badge"
                                style="display: none;">0</span></a></li>
                    <li><a href="#" onclick="confirmLogout('logout.php'); return false;"><i
                                class="menu-icon bi bi-box-arrow-right"></i> Đăng xuất</a></li>
                </ul>
            </nav>
        </aside>
        <div class="admin-right">
            <header class="admin-topbar">
                <div class="topbar-left"><span class="topbar-title">Dashboard</span></div>
                <div class="topbar-right"><span class="topbar-user"><i class="topbar-user-icon bi bi-person-circle"></i>
                        Admin</span></div>
            </header>
            <script>
            function confirmLogout(logoutUrl) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Bạn có chắc chắn muốn đăng xuất?',
                        text: 'Bạn sẽ phải đăng nhập lại để tiếp tục sử dụng',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Đăng xuất',
                        cancelButtonText: 'Hủy'
                    }).then(function(r) {
                        if (r.isConfirmed) window.location.href = logoutUrl;
                    });
                } else {
                    window.location.href = logoutUrl;
                }
            }
            </script>
