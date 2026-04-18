<?php 
include('../config/constants.php'); 


if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['redirect']) && $_GET['redirect'] === 'cart') {
    $_SESSION['redirect_after_login'] = SITEURL . 'user/cart.php';
}


if(isset($_POST['submit'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $login_success = false;
    
    
    $user_sql = "SELECT * FROM tbl_user WHERE email=? AND status='Active'";
    $user_stmt = mysqli_prepare($conn, $user_sql);
    
    if($user_stmt){
        mysqli_stmt_bind_param($user_stmt, "s", $email);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        $user_count = mysqli_num_rows($user_result);
        
        if($user_count == 1){
            $user_row = mysqli_fetch_assoc($user_result);
            
            if(password_verify($password, $user_row['password'])){
                $_SESSION['user'] = $user_row['username'];
                $_SESSION['user_id'] = $user_row['id'];
                $_SESSION['user_full_name'] = $user_row['full_name'];
                $_SESSION['login-success'] = "Đăng nhập thành công!";
                $login_success = true;

                // Tự khôi phục giỏ hàng đã lưu theo user (tbl_cart)
                try {
                    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
                        $uid = (int) $user_row['id'];
                        $cols = [];
                        $colRes = @$conn->query("SHOW COLUMNS FROM tbl_cart");
                        if ($colRes) {
                            while ($cr = $colRes->fetch_assoc()) $cols[] = strtolower((string)($cr['Field'] ?? ''));
                        }
                        $hasNewSchema = in_array('cart_id', $cols, true) && in_array('qty', $cols, true);

                        $restored_cart = [];
                        if ($hasNewSchema) {
                            $cart_stmt = $conn->prepare("SELECT cart_id, food_id, qty, note, size_id, side_dish_ids FROM tbl_cart WHERE user_id = ?");
                            if ($cart_stmt) {
                                $cart_stmt->bind_param("i", $uid);
                                $cart_stmt->execute();
                                $cart_res = $cart_stmt->get_result();
                                while ($c = $cart_res->fetch_assoc()) {
                                    $side_ids = [];
                                    if (!empty($c['side_dish_ids'])) {
                                        $side_ids = array_map('intval', array_filter(explode(',', (string)$c['side_dish_ids'])));
                                    }
                                    $restored_cart[] = [
                                        'cart_id' => (string) $c['cart_id'],
                                        'food_id' => (int) $c['food_id'],
                                        'qty' => max(1, (int) $c['qty']),
                                        'note' => (string) $c['note'],
                                        'size_id' => (int) $c['size_id'],
                                        'side_dish_ids' => $side_ids
                                    ];
                                }
                                $cart_stmt->close();
                            }
                        } else {
                            // Schema cũ: id, user_id, food_id, quantity, note...
                            $cart_stmt = $conn->prepare("SELECT id, food_id, quantity, note FROM tbl_cart WHERE user_id = ? ORDER BY id ASC");
                            if ($cart_stmt) {
                                $cart_stmt->bind_param("i", $uid);
                                $cart_stmt->execute();
                                $cart_res = $cart_stmt->get_result();
                                while ($c = $cart_res->fetch_assoc()) {
                                    $restored_cart[] = [
                                        'cart_id' => 'legacy_' . (string)$c['id'],
                                        'food_id' => (int) $c['food_id'],
                                        'qty' => max(1, (int) $c['quantity']),
                                        'note' => (string)($c['note'] ?? ''),
                                        'size_id' => 0,
                                        'side_dish_ids' => []
                                    ];
                                }
                                $cart_stmt->close();
                            }
                        }

                        if (!empty($restored_cart)) {
                            $_SESSION['cart'] = $restored_cart;
                        }
                    }
                } catch (Throwable $e) {
                    // Không chặn luồng đăng nhập nếu lỗi DB
                }
            }
        }
        mysqli_stmt_close($user_stmt);
    }
    
    if($login_success){
        unset($_SESSION['login_email']);
        if(isset($_SESSION['redirect_food_id'])) {
            $food_id = $_SESSION['redirect_food_id'];
            unset($_SESSION['redirect_food_id']);
            header('location:'.SITEURL.'order.php?food_id='.$food_id);
        } elseif(isset($_SESSION['redirect_after_login'])) {
            $url = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            header('location:'.$url);
        } else {
            header('location:'.SITEURL.'index.php');
        }
        exit();
    } else {
        
        $_SESSION['login'] = "Email hoặc mật khẩu không đúng!";
        $_SESSION['login_email'] = htmlspecialchars(trim($email), ENT_QUOTES, 'UTF-8');
        header('location:'.SITEURL.'user/login.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login - Food Order System</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
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
        .login-form input[type="email"],
        .login-form input[type="password"] {
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
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #ffe6e6;
            border-radius: 5px;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #e6ffe6;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include('../partials-front/menu.php'); ?>
    
    <div class="login-container">
        <h1>Đăng nhập</h1>
        
        <form action="" method="POST" class="login-form">
            <input type="email" name="email" placeholder="Email" value="<?php echo isset($_SESSION['login_email']) ? $_SESSION['login_email'] : ''; ?>" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <input type="submit" name="submit" value="Đăng nhập" class="btn-primary">
        </form>
        
        <div class="register-link">
            <p>Chưa có tài khoản? <a href="<?php echo SITEURL; ?>user/register.php">Đăng ký tại đây</a></p>
            <p style="margin-top: 10px;"><a href="<?php echo SITEURL; ?>user/forgot-password.php"> Quên mật khẩu?</a></p>
        </div>
    </div>
    
    <!-- SweetAlert2: hiển thị thông báo đăng nhập/đăng xuất -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php
        function extractMessage($html) {
            $html = strip_tags($html);
            return trim($html);
        }

        // Các key session có thể được set ở login/logout/register/...
        $sessionMessages = ['login-success', 'register-success', 'login', 'no-login-message', 'reset-password-success', 'access-denied'];

        foreach ($sessionMessages as $key) {
            if (isset($_SESSION[$key]) && !empty($_SESSION[$key])) {
                $message = extractMessage($_SESSION[$key]);
                if (!empty($message)) {
                    $icon = 'info';
                    $title = 'Thông báo';

                    if (strpos(strtolower($key), 'success') !== false ||
                        strpos(strtolower($message), 'thành công') !== false ||
                        strpos(strtolower($message), 'successfully') !== false) {
                        $icon = 'success';
                        $title = 'Thành công!';
                    } elseif (strpos(strtolower($message), 'không đúng') !== false ||
                              strpos(strtolower($message), 'lỗi') !== false ||
                              strpos(strtolower($message), 'failed') !== false) {
                        $icon = 'error';
                        $title = 'Lỗi!';
                    } elseif (strpos(strtolower($message), 'đăng nhập') !== false ||
                              strpos(strtolower($message), 'cảnh báo') !== false ||
                              strpos(strtolower($message), 'không có quyền') !== false) {
                        $icon = 'warning';
                        $title = 'Cảnh báo!';
                    }

                    echo "Swal.fire({
                        icon: '" . $icon . "',
                        title: '" . $title . "',
                        text: '" . addslashes($message) . "',
                        showConfirmButton: true,
                        timer: 3000
                    });";
                }
                unset($_SESSION[$key]);
            }
        }
        ?>
    </script>

    <?php include('../partials-front/footer.php'); ?>
    
 
</body>
</html>
