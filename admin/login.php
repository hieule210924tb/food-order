<?php 
require '../config/constants.php';


if(isset($_POST['submit'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    
    $sql = "SELECT * FROM tbl_admin WHERE email=?";
    $stmt = mysqli_prepare($conn, $sql);

    if($stmt){
        
        mysqli_stmt_bind_param($stmt, "s", $email);

        
        mysqli_stmt_execute($stmt);

       
        $result = mysqli_stmt_get_result($stmt);
        $count = mysqli_num_rows($result);

        if($count == 1){
            $admin = mysqli_fetch_assoc($result);
            
           
            if(password_verify($password, $admin['password']) || $admin['password'] === $password){
                
                $_SESSION['login'] = '';
                $_SESSION['user'] = $admin['username'];
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['login-success'] = "Đăng nhập thành công!";
                header('location:'.SITEURL.'admin/index.php');
                exit();
            }
            else{
              
                $_SESSION['login'] = "<div class='error'>Email hoặc mật khẩu không đúng!</div>";
                header('location:'.SITEURL.'admin/login.php');
                exit();
            }
        }
        else{
            
            $_SESSION['login'] = "<div class='error'>Email hoặc mật khẩu không đúng!</div>";
            header('location:'.SITEURL.'admin/login.php');
            exit();
        }

        
        mysqli_stmt_close($stmt);
    }
    else{
        $_SESSION['login'] = "<div class='error'>Lỗi database!</div>";
        header('location:'.SITEURL.'admin/login.php');
        exit();
    }
}
?>
<html>

<head>
    <title>Admin Login - Food Order System</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
    body.login-page {
        background: url("../image/bg.jpg") center center no-repeat;
        background-size: cover;
        background-attachment: fixed;
        min-height: 100vh;
        margin: 0;
    }
    .login-form input[type="email"],
    .login-form input[type="password"] {
        width: 80%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
        box-sizing: border-box;
    }

    .login-form input[type="submit"] {
        width: 80%;
        padding: 10px;
        background-color: #ff6b81;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 1rem;
        cursor: pointer;
    }

    .login-form input[type="submit"]:hover {
        background-color: #ff4757;
    }
    </style>
</head>

<body class="login-page">
    <?php include('../partials-front/menu.php'); ?>

    <div class="login" style="margin-top: 150px;">
        <h1 class="text-center">Đăng nhập Admin</h1>
        <br><br>
        <br><br>

        <form action="" method="POST" class="login-form text-center">
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Mật khẩu" required><br><br>
            <input type="submit" name="submit" value="Đăng nhập" class="btn-primary">
        </form>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    <?php
            if(isset($_SESSION['login-success'])){
                echo "Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: '" . addslashes($_SESSION['login-success']) . "',
                    showConfirmButton: true,
                    timer: 3000
                });";
                unset($_SESSION['login-success']);
            }
            if(isset($_SESSION['register-success'])){
                echo "Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: '" . addslashes($_SESSION['register-success']) . "',
                    showConfirmButton: true,
                    timer: 3000
                });";
                unset($_SESSION['register-success']);
            }
            if(isset($_SESSION['login']) && !empty($_SESSION['login'])){
                $loginMsg = strip_tags($_SESSION['login']);
                if(!empty($loginMsg)){
                    echo "Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: '" . addslashes($loginMsg) . "',
                        showConfirmButton: true
                    });";
                }
                unset($_SESSION['login']);
            }
            if(isset($_SESSION['no-login-message']) && !empty($_SESSION['no-login-message'])){
                $noLoginMsg = strip_tags($_SESSION['no-login-message']);
                if(!empty($noLoginMsg)){
                    echo "Swal.fire({
                        icon: 'warning',
                        title: 'Cảnh báo!',
                        text: '" . addslashes($noLoginMsg) . "',
                        showConfirmButton: true
                    });";
                }
                unset($_SESSION['no-login-message']);
            }
            ?>
    </script>
</body>
<?php include("partials/footer.php"); ?>

</html>