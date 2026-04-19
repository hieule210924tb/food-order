<?php 
include('../config/constants.php'); 


if(!isset($_SESSION['reset_password_email'])){
    $_SESSION['forgot-password'] = "Vui lòng yêu cầu đặt lại mật khẩu trước!";
    header('location:'.SITEURL.'user/forgot-password.php');
    exit();
}

$email = $_SESSION['reset_password_email'];


if(isset($_POST['submit'])){
    $code = trim($_POST['code']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    

    if($new_password !== $confirm_password){
        $_SESSION['reset-password'] = "Mật khẩu không khớp!";
        header('location:'.SITEURL.'user/reset-password.php');
        exit();
    }
    
 
    if(strlen($new_password) < 6){
        $_SESSION['reset-password'] = "Mật khẩu phải có ít nhất 6 ký tự!";
        header('location:'.SITEURL.'user/reset-password.php');
        exit();
    }
    
    
    $check_sql = "SELECT * FROM tbl_verification WHERE 
        email = ? AND 
        verification_code = ? AND 
        verification_type = 'email' AND 
        is_verified = 0 AND 
        expires_at > UTC_TIMESTAMP() AND
        attempts < 5
        ORDER BY created_at DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $check_sql);
    
    if($stmt){
        mysqli_stmt_bind_param($stmt, "ss", $email, $code);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $count = mysqli_num_rows($result);
        
        if($count == 1){
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_sql = "UPDATE tbl_user SET password = ? WHERE email = ? AND status = 'Active'";
            $stmt2 = mysqli_prepare($conn, $update_sql);
            
            if($stmt2){
                mysqli_stmt_bind_param($stmt2, "ss", $hashed_password, $email);
                $result2 = mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
                
                if($result2){
                    
                    $mark_sql = "UPDATE tbl_verification SET is_verified = 1 WHERE email = ? AND verification_code = ?";
                    $stmt3 = mysqli_prepare($conn, $mark_sql);
                    if($stmt3){
                        mysqli_stmt_bind_param($stmt3, "ss", $email, $code);
                        mysqli_stmt_execute($stmt3);
                        mysqli_stmt_close($stmt3);
                    }
                    
                   
                    unset($_SESSION['reset_password_email']);
                    
                    $_SESSION['reset-password-success'] = "Đặt lại mật khẩu thành công! Vui lòng đăng nhập với mật khẩu mới.";
                    header('location:'.SITEURL.'user/login.php');
                    exit();
                } else {
                    $_SESSION['reset-password'] = "Lỗi khi cập nhật mật khẩu. Vui lòng thử lại.";
                }
            } else {
                $_SESSION['reset-password'] = "Lỗi database!";
            }
        } else {
            
            $update_attempts_sql = "UPDATE tbl_verification SET attempts = attempts + 1 WHERE email = ? AND verification_code = ?";
            $stmt4 = mysqli_prepare($conn, $update_attempts_sql);
            if($stmt4){
                mysqli_stmt_bind_param($stmt4, "ss", $email, $code);
                mysqli_stmt_execute($stmt4);
                mysqli_stmt_close($stmt4);
            }
            
            $_SESSION['reset-password'] = "Mã xác minh không đúng hoặc đã hết hạn!";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['reset-password'] = "Lỗi database!";
    }
}

$page_title = 'Đặt lại mật khẩu - Food Order System';
$extra_stylesheets = ['../css/user-reset-password.css'];
include __DIR__ . '/../partials-front/html-head.php';
?>

<body>
    <?php include('../partials-front/menu.php'); ?>
    
    <div class="login-container">
        <h1>  Đặt lại mật khẩu</h1>
        
        <div class="info-box">
            <strong><i class="bi bi-check-circle-fill"></i></strong> Mã đặt lại mật khẩu đã được gửi đến email của bạn.
        </div>
        
        <div class="email-display">
             <?php echo htmlspecialchars($email); ?>
        </div>
        
        <form action="" method="POST" class="login-form">
            <input type="text" name="code" placeholder="Nhập mã xác minh (6 số)" required maxlength="6" pattern="[0-9]{6}" title="Mã xác minh gồm 6 chữ số">
            <input type="password" name="new_password" placeholder="Mật khẩu mới" required minlength="6">
            <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu mới" required minlength="6">
            <input type="submit" name="submit" value="Đặt lại mật khẩu" class="btn-primary">
        </form>
        
        <div class="back-link">
            <a href="<?php echo SITEURL; ?>user/forgot-password.php"><i class="bi bi-arrow-repeat"></i> Gửi lại mã</a> | 
            <a href="<?php echo SITEURL; ?>user/login.php"><i class="bi bi-arrow-left"></i> Quay lại đăng nhập</a>
        </div>
    </div>
    
    <?php include('../partials-front/footer.php'); ?>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php
        function extractMessage($html) {
            $html = strip_tags($html);
            return trim($html);
        }
        
        if(isset($_SESSION['reset-password']) && !empty($_SESSION['reset-password'])) {
            $message = extractMessage($_SESSION['reset-password']);
            if(!empty($message)) {
                echo "Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: '" . addslashes($message) . "',
                    confirmButtonColor: '#ff6b81'
                });";
                unset($_SESSION['reset-password']);
            }
        }
        
        if(isset($_SESSION['reset-password-success']) && !empty($_SESSION['reset-password-success'])) {
            $message = extractMessage($_SESSION['reset-password-success']);
            if(!empty($message)) {
                echo "Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: '" . addslashes($message) . "',
                    confirmButtonColor: '#ff6b81',
                    showConfirmButton: true,
                    timer: 5000
                });";
                unset($_SESSION['reset-password-success']);
            }
        }
        ?>
    </script>
</body>
</html>

