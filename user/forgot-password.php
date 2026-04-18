<?php 
include('../config/constants.php'); 


if(isset($_POST['submit'])){
    $email = trim($_POST['email']);
    
    
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $_SESSION['forgot-password'] = "Email không hợp lệ!";
        header('location:'.SITEURL.'user/forgot-password.php');
        exit();
    }
    
    
    $email_domain = substr(strrchr($email, "@"), 1);
    if(strtolower($email_domain) !== 'gmail.com'){
        $_SESSION['forgot-password'] = "Chỉ chấp nhận email Gmail!";
        header('location:'.SITEURL.'user/forgot-password.php');
        exit();
    }
    
   
    $check_sql = "SELECT * FROM tbl_user WHERE email=? AND status='Active'";
    $stmt = mysqli_prepare($conn, $check_sql);
    
    if($stmt){
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $count = mysqli_num_rows($result);
        
        if($count == 1){
            
            require_once(__DIR__ . '/../api/phpmailer-send.php');
            
           
            $reset_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires_at = date('Y-m-d H:i:s', time() + 600); // 10 phút
            
           
            $delete_sql = "DELETE FROM tbl_verification WHERE 
                email = ? AND 
                verification_type = 'email' AND 
                expires_at < UTC_TIMESTAMP()";
            $stmt2 = mysqli_prepare($conn, $delete_sql);
            if ($stmt2) {
                mysqli_stmt_bind_param($stmt2, "s", $email);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
            }
            
         
            $insert_sql = "INSERT INTO tbl_verification SET
                email = ?,
                phone = NULL,
                verification_code = ?,
                verification_type = 'email',
                expires_at = ?,
                is_verified = 0,
                attempts = 0";
            $stmt2 = mysqli_prepare($conn, $insert_sql);
            if ($stmt2) {
                mysqli_stmt_bind_param($stmt2, "sss", $email, $reset_code, $expires_at);
                $result2 = mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
                
                if($result2){
                 
                    $subject = "Mã đặt lại mật khẩu - WowFood";
                    $message = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .code { font-size: 32px; font-weight: bold; color: #ff6b81; text-align: center; padding: 20px; background: #f1f2f6; border-radius: 10px; margin: 20px 0; letter-spacing: 5px; }
                            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em; }
                            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 20px 0; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <h2>Đặt lại mật khẩu WowFood</h2>
                            <p>Xin chào,</p>
                            <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản WowFood. Vui lòng sử dụng mã sau để đặt lại mật khẩu:</p>
                            <div class='code'>{$reset_code}</div>
                            <p><strong>Mã này có hiệu lực trong 10 phút.</strong></p>
                            <div class='warning'>
                                <strong>Lưu ý:</strong> Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này và đảm bảo tài khoản của bạn được bảo mật.
                            </div>
                            <div class='footer'>
                                <p>Trân trọng,<br>Đội ngũ WowFood</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ";
                    
                    $sent = false;
                    if (function_exists('sendEmailWithPHPMailer')) {
                        $sent = sendEmailWithPHPMailer($email, $subject, $message);
                    }
                    
                 
                    $is_localhost = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
                                     strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
                    if ($is_localhost) {
                        $log_file = __DIR__ . '/../logs/verification_codes.log';
                        $log_message = date('Y-m-d H:i:s') . " - Reset Password - Email: {$email}, Code: {$reset_code}\n";
                        file_put_contents($log_file, $log_message, FILE_APPEND);
                    }
                    
                    if($sent){
                     
                        $_SESSION['reset_password_email'] = $email;
                        $_SESSION['forgot-password-success'] = "Mã đặt lại mật khẩu đã được gửi đến email của bạn!";
                        header('location:'.SITEURL.'user/reset-password.php');
                        exit();
                    } else {
                        $_SESSION['forgot-password'] = "Không thể gửi email. Vui lòng thử lại sau.";
                    }
                } else {
                    $_SESSION['forgot-password'] = "Lỗi khi lưu mã reset. Vui lòng thử lại.";
                }
            } else {
                $_SESSION['forgot-password'] = "Lỗi database. Vui lòng thử lại.";
            }
        } else {
            
            $_SESSION['forgot-password-success'] = "Nếu email tồn tại, mã đặt lại mật khẩu đã được gửi đến email của bạn!";
            header('location:'.SITEURL.'user/reset-password.php');
            exit();
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['forgot-password'] = "Lỗi database!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Food Order System</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .login-container h1 {
            text-align: center;
            color: #2f3542;
            margin-bottom: 30px;
        }
        .login-form input[type="email"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .login-form input[type="submit"] {
            width: 100%;
            padding: 12px;
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
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .register-link a {
            color: #ff6b81;
        }
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        .back-link a {
            color: #666;
            text-decoration: none;
        }
        .back-link a:hover {
            color: #ff6b81;
        }
        .info-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <?php include('../partials-front/menu.php'); ?>
    
    <div class="login-container">
        <h1> Quên mật khẩu</h1>
        
        <div class="info-box">
            <strong> Lưu ý:</strong> Mã đặt lại mật khẩu sẽ được gửi đến email Gmail của bạn.
        </div>
        
        <form action="" method="POST" class="login-form">
            <input type="email" name="email" placeholder="Nhập email Gmail của bạn" required pattern="[a-zA-Z0-9._%+-]+@gmail\.com$" title="Chỉ chấp nhận địa chỉ Gmail">
            <input type="submit" name="submit" value="Gửi mã đặt lại" class="btn-primary">
        </form>
        
        <div class="back-link">
            <a href="<?php echo SITEURL; ?>user/login.php"><i class="bi bi-arrow-left"></i> Quay lại đăng nhập</a>
        </div>
        
        <div class="register-link">
            <p>Chưa có tài khoản? <a href="<?php echo SITEURL; ?>user/register.php">Đăng ký tại đây</a></p>
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
        
        if(isset($_SESSION['forgot-password']) && !empty($_SESSION['forgot-password'])) {
            $message = extractMessage($_SESSION['forgot-password']);
            if(!empty($message)) {
                echo "Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: '" . addslashes($message) . "',
                    confirmButtonColor: '#ff6b81'
                });";
                unset($_SESSION['forgot-password']);
            }
        }
        ?>
    </script>
</body>
</html>

