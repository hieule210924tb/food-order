<?php
require_once('../config/constants.php');
require_once('partials/login-check.php');

if (isset($_POST['submit'])) {

    $fullname = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, $_POST['phone']) : '';
    $address = isset($_POST['address']) ? mysqli_real_escape_string($conn, $_POST['address']) : '';

    if ($password !== $confirm_password) {

        $_SESSION['add'] = "<div class='error'>Mật khẩu không khớp!</div>";

        header('location:' . SITEURL . 'admin/add-admin.php');
        exit();

    }

    if (strlen($password) < 6) {

        $_SESSION['add'] = "<div class='error'>Mật khẩu phải có ít nhất 6 ký tự!</div>";

        header('location:' . SITEURL . 'admin/add-admin.php');
        exit();

    }

    $check_sql = "SELECT * FROM tbl_admin WHERE email=? OR phone=? OR username=?";

    $stmt = mysqli_prepare($conn, $check_sql);

    mysqli_stmt_bind_param($stmt, "sss", $email, $phone, $username);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {

        mysqli_stmt_close($stmt);

        $_SESSION['add'] = "<div class='error'>Email, số điện thoại hoặc tên đăng nhập đã tồn tại!</div>";

        header('location:' . SITEURL . 'admin/add-admin.php');
        exit();

    }

    mysqli_stmt_close($stmt);

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO tbl_admin (full_name,email,username,password,phone)
VALUES (?,?,?,?,?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "sssss", $fullname, $email, $username, $hashed_password, $phone);

    $res = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    if ($res) {

        $_SESSION['add'] = "<div class='success'>Thêm quản trị viên thành công!</div>";

        header('location:' . SITEURL . 'admin/manage-admin.php');
        exit();

    }

    $_SESSION['add'] = "<div class='error'>Thêm quản trị viên thất bại!</div>";

    header('location:' . SITEURL . 'admin/add-admin.php');
    exit();

}

include('partials/menu.php');
?>


<div class="main-content">
    <div class="wrapper">

        <h1 style="margin-bottom:10px;">Thêm quản trị viên</h1>

        <p style="color:#747d8c;margin-bottom:25px;">
            Tạo tài khoản quản trị mới cho hệ thống.
        </p>


        <div style="
background:#ffffff;
border-radius:12px;
padding:25px;
box-shadow:0 4px 14px rgba(0,0,0,0.06);
border:1px solid #ecf0f1;
max-width:650px;
">

            <form action="" method="POST">

                <table style="width:100%;border-collapse:separate;border-spacing:0 14px;font-size:14px;">

                    <tr>
                        <td style="width:180px;font-weight:600;">Họ tên</td>
                        <td>
                            <input type="text" name="full_name" placeholder="Nhập họ tên" required
                                style="width:100%;padding:8px;border:1px solid #dfe4ea;border-radius:6px;">
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Email</td>
                        <td>
                            <input type="email" name="email" placeholder="Nhập email" required
                                style="width:100%;padding:8px;border:1px solid #dfe4ea;border-radius:6px;">
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Tên đăng nhập</td>
                        <td>
                            <input type="text" name="username" placeholder="Tên đăng nhập" required
                                style="width:100%;padding:8px;border:1px solid #dfe4ea;border-radius:6px;">
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Mật khẩu</td>
                        <td>
                            <input type="password" name="password" placeholder="Mật khẩu" required minlength="6"
                                style="width:100%;padding:8px;border:1px solid #dfe4ea;border-radius:6px;">
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Xác nhận mật khẩu</td>
                        <td>
                            <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required
                                style="width:100%;padding:8px;border:1px solid #dfe4ea;border-radius:6px;">
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Số điện thoại</td>
                        <td>
                            <input type="tel" name="phone" placeholder="Số điện thoại"
                                style="width:100%;padding:8px;border:1px solid #dfe4ea;border-radius:6px;">
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Địa chỉ</td>
                        <td>
                            <textarea name="address" rows="3" placeholder="Địa chỉ"
                                style="width:100%;padding:8px;border:1px solid #dfe4ea;border-radius:6px;"></textarea>
                        </td>
                    </tr>

                    <tr>

                        <td colspan="2" style="padding-top:10px;">

                            <button type="submit" name="submit" style="
padding:8px 18px;
border-radius:999px;
background:#1e90ff;
color:white;
font-size:13px;
font-weight:500;
border:none;
cursor:pointer;
">

                                Thêm quản trị viên

                            </button>


                            <a href="<?php echo SITEURL; ?>admin/manage-admin.php" style="
margin-left:8px;
padding:8px 16px;
border-radius:999px;
background:#ecf0f1;
color:#2c3e50;
font-size:13px;
text-decoration:none;
">

                                Quay lại

                            </a>

                        </td>

                    </tr>

                </table>

            </form>

        </div>

    </div>
</div>


<?php include('partials/footer.php'); ?>