<?php
require_once('../config/constants.php');
require_once('partials/login-check.php');

if (!isset($_GET['id'])) {
    $_SESSION['delete_side_dish'] = "<div class='error'>Truy cập trái phép.</div>";
    header('location:' . SITEURL . 'admin/manage-side-dish.php');
    exit();
}

$id  = (int) $_GET['id'];
$sql = "DELETE FROM tbl_side_dish WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($ok) {
    $_SESSION['delete_side_dish'] = "<div class='success'>Xóa món/nước kèm thành công!</div>";
} else {
    $_SESSION['delete_side_dish'] = "<div class='error'>Xóa món/nước kèm thất bại. Vui lòng thử lại!</div>";
}

header('location:' . SITEURL . 'admin/manage-side-dish.php');
exit();

