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
              
                $_SESSION['login'] = "Email hoặc mật khẩu không đúng!";
                header('location:'.SITEURL.'admin/login.php');
                exit();
            }
        }
        else{
            
            $_SESSION['login'] = "Email hoặc mật khẩu không đúng!";
            header('location:'.SITEURL.'admin/login.php');
            exit();
        }

        
        mysqli_stmt_close($stmt);
    }
    else{
        $_SESSION['login'] = "Lỗi database!";
        header('location:'.SITEURL.'admin/login.php');
        exit();
    }
}

$page_title = 'Đăng nhập Admin - WowFood';
$extra_stylesheets = [
    [
        'href' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css',
        'integrity' => 'sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB',
        'crossorigin' => 'anonymous',
    ],
    '../css/components/user-login.css',
];
include __DIR__ . '/../partials-front/html-head.php';
?>
<body>

    <div class="login-container">
        <div class="login-image">
            <img src="../image/imgLogin.png" alt="WowFood Admin" class="login-bg-image">
            <div class="image-overlay">
                <h2>WowFood Admin</h2>
                <p>Quản lý hệ thống đặt món ăn</p>
            </div>
        </div>
        <div class="login-card">
            <div class="login-header">
                <h2>Đăng nhập Admin</h2>
                <p>Chào mừng trở lại với hệ thống quản lý</p>
            </div>

            <form action="" method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Nhập email của bạn" required>
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
                </div>

                <button type="submit" name="submit" class="btn-login">
                    Đăng nhập
                </button>
            </form>

            <div class="login-footer">
                <p class="back-link">
                    <a href="<?php echo SITEURL; ?>index.php">← Quay lại trang chủ</a>
                </p>
            </div>
        </div>
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
</html>