<?php
require_once('../config/constants.php');
require_once('partials/login-check.php');

if (isset($_POST['submit'])) {
    $id        = (int) $_POST['id'];
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');

    $errors = [];

    $hasPhone = false;
    $colRes = mysqli_query($conn, "SHOW COLUMNS FROM tbl_admin LIKE 'phone'");
    if ($colRes && mysqli_num_rows($colRes) > 0) $hasPhone = true;

    if ($full_name === '') {
        $errors[] = 'Họ tên không được để trống.';
    }

    if ($hasPhone) {
        if ($phone === '') {
            $errors[] = 'Số điện thoại không được để trống.';
        }
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ.';
    }

    if ($hasPhone) {
        $check_sql = "SELECT id FROM tbl_admin WHERE (email = ? OR phone = ?) AND id <> ?";
        $stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt, "ssi", $email, $phone, $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $check_sql = "SELECT id FROM tbl_admin WHERE email = ? AND id <> ?";
        $stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt, "si", $email, $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }

    if (mysqli_num_rows($result) > 0) {
        $errors[] = $hasPhone ? 'Email hoặc số điện thoại đã được sử dụng.' : 'Email đã được sử dụng.';
    }
    mysqli_stmt_close($stmt);


    if (!empty($errors)) {
        $_SESSION['update'] = "<div class='error'>" . implode('<br>', array_map('htmlspecialchars', $errors)) . "</div>";
        header('location: ' . SITEURL . 'admin/update-admin.php?id=' . $id);
        exit();
    }

    
    if ($hasPhone) {
        $sql = "UPDATE tbl_admin SET full_name=?, email=?, phone=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $full_name, $email, $phone, $id);
        $res = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $sql = "UPDATE tbl_admin SET full_name=?, email=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $full_name, $email, $id);
        $res = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    if ($res) {
        $_SESSION['update'] = "<div class='success'>Cập nhật quản trị viên thành công!</div>";
        header('location: ' . SITEURL . 'admin/manage-admin.php');
    } else {
        $_SESSION['update'] = "<div class='error'>Cập nhật quản trị viên thất bại!</div>";
        header('location: ' . SITEURL . 'admin/update-admin.php?id=' . $id);
    }
    exit();
}


$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('location: ' . SITEURL . 'admin/manage-admin.php');
    exit();
}

$hasPhone = false;
$colRes = mysqli_query($conn, "SHOW COLUMNS FROM tbl_admin LIKE 'phone'");
if ($colRes && mysqli_num_rows($colRes) > 0) $hasPhone = true;

if ($hasPhone) {
    $sql = "SELECT id, full_name, email, phone FROM tbl_admin WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    if ($res && mysqli_num_rows($res) === 1) {
        $row = mysqli_fetch_assoc($res);
        $fullname = $row['full_name'];
        $email    = $row['email'];
        $phone    = $row['phone'];
    } else {
        header('location: ' . SITEURL . 'admin/manage-admin.php');
        exit();
    }
} else {
    $sql = "SELECT id, full_name, email, username FROM tbl_admin WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    if ($res && mysqli_num_rows($res) === 1) {
        $row = mysqli_fetch_assoc($res);
        $fullname = $row['full_name'];
        $email    = $row['email'];
        $phone    = '';
    } else {
        header('location: ' . SITEURL . 'admin/manage-admin.php');
        exit();
    }
}

require 'partials/menu.php';
?>

<div class="main-content">
    <div class="wrapper">
        <h1 style="margin-bottom:10px;">Cập nhật quản trị viên</h1>
        <p style="color:#747d8c; margin-bottom:25px;">
            Chỉnh sửa thông tin tài khoản quản trị hệ thống.
        </p>

        <?php
        if (isset($_SESSION['update'])) {
            echo $_SESSION['update'];
            unset($_SESSION['update']);
        }
        ?>

        <div style="background:#ffffff; border-radius:12px; padding:25px; box-shadow:0 4px 14px rgba(0,0,0,0.06); border:1px solid #ecf0f1; max-width:750px;">
            <form action="" method="post">
                <table style="width:100%; border-collapse:separate; border-spacing:0 14px; font-size:14px;">
                    
                    <tr>
                        <td style="width:180px; font-weight:600;">Họ tên</td>
                        <td>
                            <input type="text" name="full_name" required
                                value="<?php echo htmlspecialchars($fullname); ?>"
                                style="width:100%; padding:8px; border:1px solid #dfe4ea; border-radius:6px; box-sizing:border-box;">
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Email</td>
                        <td>
                            <input type="email" name="email" required
                                value="<?php echo htmlspecialchars($email); ?>"
                                style="width:100%; padding:8px; border:1px solid #dfe4ea; border-radius:6px; box-sizing:border-box;">
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Số điện thoại</td>
                        <td>
                            <input type="tel" name="phone" <?php echo $hasPhone ? 'required' : ''; ?>
                                value="<?php echo htmlspecialchars($phone); ?>"
                                style="width:100%; padding:8px; border:1px solid #dfe4ea; border-radius:6px; box-sizing:border-box;">
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" style="padding-top:15px;">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            
                            <button type="submit" name="submit"
                                style="padding:10px 25px; border-radius:999px; background:#1e90ff; color:white; font-size:13px; font-weight:600; border:none; cursor:pointer;">
                                Cập nhật
                            </button>

                            <a href="manage-admin.php"
                                style="margin-left:10px; padding:10px 20px; border-radius:999px; background:#ecf0f1; color:#2c3e50; font-size:13px; text-decoration:none; font-weight:500;">
                                Quay lại
                            </a>
                        </td>
                    </tr>

                </table>
            </form>
        </div>
    </div>
</div>

<?php require 'partials/footer.php'; ?>