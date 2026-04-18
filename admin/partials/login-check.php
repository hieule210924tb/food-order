<?php

    if (!isset($_SESSION['user'])) {
        $_SESSION['no-login-message'] = "<div class='error text-center'>Vui lòng đăng nhập để truy cập Admin Panel.</div>";
        header('location: ' . SITEURL . 'admin/login.php');
        exit();
    }
    

    if (isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
        $_SESSION['access-denied'] = "Bạn không có quyền truy cập trang Admin. Vui lòng đăng nhập bằng tài khoản Admin.";
        header('location: ' . SITEURL . 'index.php');
        exit();
    }
    

    if (!isset($_SESSION['admin_id'])) {
        $_SESSION['no-login-message'] = "<div class='error text-center'>Vui lòng đăng nhập bằng tài khoản Admin để truy cập Admin Panel.</div>";
        header('location: ' . SITEURL . 'admin/login.php');
        exit();
    }
?>