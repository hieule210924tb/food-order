<?php 
include('../config/constants.php');
// Xử lý đăng ký trước khi output HTML
if(isset($_POST['submit'])){
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, $_POST['phone']) : '';
    $address = isset($_POST['address']) ? mysqli_real_escape_string($conn, trim($_POST['address'])) : '';
    $ghn_province_id = isset($_POST['ghn_province_id']) ? (int) $_POST['ghn_province_id'] : 0;
    $ghn_district_id = isset($_POST['ghn_district_id']) ? (int) $_POST['ghn_district_id'] : 0;
    $ghn_ward_code = isset($_POST['ghn_ward_code']) ? mysqli_real_escape_string($conn, trim($_POST['ghn_ward_code'])) : '';
    $verification_type = isset($_POST['verification_type']) ? $_POST['verification_type'] : 'email';
    // Validate password match
    if($password !== $confirm_password){
        $_SESSION['register'] = "Passwords do not match!";
        header('location:'.SITEURL.'user/register.php');
        exit();
    }

    // Bắt buộc chọn đủ địa chỉ GHN
    if ($ghn_province_id <= 0 || $ghn_district_id <= 0 || empty($ghn_ward_code)) {
        $_SESSION['register'] = "Vui lòng chọn địa chỉ đầy đủ (Tỉnh → Quận → Phường/Xã).";
        header('location:'.SITEURL.'user/register.php');
        exit();
    }
    
    // Validate password length
    if(strlen($password) < 6){
        $_SESSION['register'] = "Password must be at least 6 characters!";
        header('location:'.SITEURL.'user/register.php');
        exit();
    }
    // Validate email format
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $_SESSION['register'] = "Email không hợp lệ!";
        header('location:'.SITEURL.'user/register.php');
        exit();
    }
    $email_domain = substr(strrchr($email, "@"), 1);
    if(strtolower($email_domain) !== 'gmail.com'){
        $_SESSION['register'] = "Chỉ chấp nhận đăng ký bằng Gmail!";
        header('location:'.SITEURL.'user/register.php');
        exit();
    }
    // Check if email already exists
    $check_sql = "SELECT * FROM tbl_user WHERE email=?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) > 0){
        mysqli_stmt_close($stmt);
        $_SESSION['register'] = "Email already exists!";
        header('location:'.SITEURL.'user/register.php');
        exit();
    }
    mysqli_stmt_close($stmt);
    
    $_SESSION['pending_registration'] = [
        'full_name' => $full_name,
        'email' => $email,
        'password' => $password, // Lưu password gốc để hash sau khi xác minh
        'phone' => $phone,
        'address' => $address,
        'ghn_province_id' => $ghn_province_id,
        'ghn_district_id' => $ghn_district_id,
        'ghn_ward_code' => $ghn_ward_code,
        'verification_type' => 'email' // Chỉ dùng email
    ];
    require_once(__DIR__ . '/../api/phpmailer-send.php');
    
    // Tạo mã xác minh và gửi email trực tiếp
    $verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires_at = date('Y-m-d H:i:s', time() + 600);
    
    // Xóa mã cũ
    $delete_sql = "DELETE FROM tbl_verification WHERE 
        email = ? AND 
        is_verified = 0 AND 
        expires_at < UTC_TIMESTAMP()";
    $stmt = mysqli_prepare($conn, $delete_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    // Lưu mã mới
    $insert_sql = "INSERT INTO tbl_verification SET
        email = ?,
        phone = NULL,
        verification_code = ?,
        verification_type = 'email',
        expires_at = ?,
        is_verified = 0,
        attempts = 0";
    $stmt = mysqli_prepare($conn, $insert_sql);
    if (!$stmt) {
        $_SESSION['register'] = "Lỗi khi chuẩn bị lưu mã xác minh: " . mysqli_error($conn);
        unset($_SESSION['pending_registration']);
        header('location:'.SITEURL.'user/register.php');
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "sss", $email, $verification_code, $expires_at);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    if (!$result) {
        $_SESSION['register'] = "Lỗi khi lưu mã xác minh: " . mysqli_error($conn);
        unset($_SESSION['pending_registration']);
        header('location:'.SITEURL.'user/register.php');
        exit();
    }
    
    // Gửi email sử dụng function từ send-verification.php
    $subject = "Mã xác minh đăng ký - WowFood";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .code { font-size: 32px; font-weight: bold; color: #ff6b81; text-align: center; padding: 20px; background: #f1f2f6; border-radius: 10px; margin: 20px 0; letter-spacing: 5px; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Xác minh đăng ký tài khoản WowFood</h2>
            <p>Xin chào,</p>
            <p>Cảm ơn bạn đã đăng ký tài khoản tại WowFood. Vui lòng sử dụng mã xác minh sau để hoàn tất đăng ký:</p>
            <div class='code'>{$verification_code}</div>
            <p><strong>Mã này có hiệu lực trong 10 phút.</strong></p>
            <p>Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email này.</p>
            <div class='footer'>
                <p>Trân trọng,<br>Đội ngũ WowFood</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Gửi email bằng PHPMailer
    $sent = false;
    if (function_exists('sendEmailWithPHPMailer')) {
        $sent = sendEmailWithPHPMailer($email, $subject, $message);
    }
    
    if ($sent) {
        // Chuyển đến trang xác minh mã
        header('location:'.SITEURL.'user/verify-code.php');
        exit();
    } else {
        $_SESSION['register'] = "Không thể gửi mã xác minh. Vui lòng kiểm tra cấu hình email hoặc thử lại sau.";
        unset($_SESSION['pending_registration']);
        header('location:'.SITEURL.'user/register.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Register - Food Order System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/checkout.css">
    <style>
        .register-container {
            max-width: 560px;
            margin: 0 auto;
            margin-top: 100px;
            margin-bottom: 80px;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .register-form .form-group { margin-bottom: 18px; }
        .register-form .form-group label { display: block; font-size: 0.9rem; font-weight: 600; color: #2f3542; margin-bottom: 8px; }
        .register-form .form-group label .required { color: #ff6b81; }
        .register-form .ghn-address-row .ghn-selects { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
        @media (max-width: 600px) { .register-form .ghn-address-row .ghn-selects { grid-template-columns: 1fr; } }
        .register-container h1 {
            text-align: center;
            color: #2f3542;
            margin-bottom: 30px;
        }
        .register-form input[type="text"],
        .register-form input[type="password"],
        .register-form input[type="email"],
        .register-form input[type="tel"],
        .register-form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }
        .register-form textarea {
            resize: vertical;
            min-height: 80px;
        }
        .register-form input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #ff6b81;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }
        .register-form input[type="submit"]:hover {
            background-color: #ff4757;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
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
    
    <div class="register-container">
        <h1>Đăng ký</h1>
        
        <form action="" method="POST" class="register-form" id="registerForm">
            <div class="form-group">
                <label for="full_name">Họ và tên <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" placeholder="Nguyễn Văn A" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" placeholder="email@gmail.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu <span class="required">*</span></label>
                <input type="password" id="password" name="password" placeholder="Ít nhất 6 ký tự" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu <span class="required">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
            </div>
            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="tel" id="phone" name="phone" placeholder="0900 123 456" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
            </div>
            <div class="form-group full ghn-address-row">
                <label for="ghn_province_id">Chọn địa chỉ (Tỉnh → Quận → Phường/Xã) <span class="required">*</span></label>
                <div class="ghn-selects">
                    <select id="ghn_province_id" name="ghn_province_id" class="ghn-select" aria-label="Tỉnh/Thành phố">
                        <option value="">-- Chọn Tỉnh/TP --</option>
                    </select>
                    <select id="ghn_district_id" name="ghn_district_id" class="ghn-select" aria-label="Quận/Huyện" disabled>
                        <option value="">-- Chọn Quận/Huyện --</option>
                    </select>
                    <select id="ghn_ward_code" name="ghn_ward_code" class="ghn-select" aria-label="Phường/Xã" disabled>
                        <option value="">-- Chọn Phường/Xã --</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="address">Địa chỉ chi tiết (số nhà, đường) <span class="required">*</span></label>
                <input type="text" id="address" name="address" placeholder="Ví dụ: 123 Ngõ Nguyễn Huệ" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
            </div>
            <div style="margin-bottom: 15px; padding: 10px; background-color: #e3f2fd; border-radius: 5px; font-size: 0.9em; color: #1976d2;">
                <strong>📧 Lưu ý:</strong> Mã xác minh sẽ được gửi đến email Gmail của bạn.
            </div>
            <input type="submit" name="submit" value="Đăng ký" class="btn-primary">
        </form>
        
        <div class="login-link">
            <p>Đã có tài khoản? <a href="<?php echo SITEURL; ?>user/login.php">Đăng nhập tại đây</a></p>
        </div>
    </div>
    
    <?php include('../partials-front/footer.php'); ?>
    <script>
    var SITEURL = <?php echo json_encode(defined('SITEURL') ? SITEURL : ''); ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    (function() {
        var selProvince = document.getElementById('ghn_province_id');
        var selDistrict = document.getElementById('ghn_district_id');
        var selWard = document.getElementById('ghn_ward_code');
        if (!selProvince) return;
        function loadProvinces() {
            fetch(SITEURL + 'api/ghn-address.php?action=province').then(function(r) { return r.json(); })
                .then(function(res) {
                    if (!res.success || !res.data) return;
                    var list = Array.isArray(res.data) ? res.data : Object.keys(res.data).map(function(k) { var v = res.data[k]; return typeof v === 'object' ? v : { ProvinceID: k, ProvinceName: v }; });
                    selProvince.innerHTML = '<option value="">-- Chọn Tỉnh/TP --</option>';
                    list.forEach(function(p) {
                        var id = p.ProvinceID != null ? p.ProvinceID : p.province_id;
                        var name = p.ProvinceName || p.province_name || '';
                        if (id != null && name) selProvince.appendChild(new Option(name, id));
                    });
                }).catch(function() {});
        }
        function loadDistricts(provinceId) {
            selDistrict.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>'; selDistrict.disabled = true;
            selWard.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>'; selWard.disabled = true;
            if (!provinceId) return;
            fetch(SITEURL + 'api/ghn-address.php?action=district&province_id=' + encodeURIComponent(provinceId)).then(function(r) { return r.json(); })
                .then(function(res) {
                    if (!res.success || !res.data) return;
                    var list = Array.isArray(res.data) ? res.data : Object.values(res.data);
                    list.forEach(function(d) {
                        var id = d.DistrictID != null ? d.DistrictID : d.district_id;
                        var name = d.DistrictName || d.district_name || '';
                        if (id != null && name) selDistrict.appendChild(new Option(name, id));
                    });
                    selDistrict.disabled = false;
                }).catch(function() { selDistrict.disabled = false; });
        }
        function loadWards(districtId) {
            selWard.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>'; selWard.disabled = true;
            if (!districtId) return;
            fetch(SITEURL + 'api/ghn-address.php?action=ward&district_id=' + encodeURIComponent(districtId)).then(function(r) { return r.json(); })
                .then(function(res) {
                    if (!res.success || !res.data) return;
                    var list = Array.isArray(res.data) ? res.data : Object.values(res.data);
                    list.forEach(function(w) {
                        var code = (w.WardCode != null ? String(w.WardCode) : (w.ward_code ? String(w.ward_code) : '')).trim();
                        var name = (w.WardName || w.ward_name || '').trim();
                        if (code && name) selWard.appendChild(new Option(name, code));
                    });
                    selWard.disabled = false;
                }).catch(function() { selWard.disabled = false; });
        }
        selProvince.addEventListener('change', function() { loadDistricts(selProvince.value); });
        selDistrict.addEventListener('change', function() { loadWards(selDistrict.value); });
        loadProvinces();

       
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                const prov = String(selProvince?.value || '').trim();
                const dist = String(selDistrict?.value || '').trim();
                const ward = String(selWard?.value || '').trim();
                if (!prov || !dist || !ward) {
                    e.preventDefault();
                    Swal.fire('Lỗi', 'Vui lòng chọn địa chỉ đầy đủ (Tỉnh → Quận → Phường/Xã).', 'error');
                    return false;
                }
            });
        }
    })();
    </script>
    <script>
        <?php
        function extractMessage($html) {
            $html = strip_tags($html);
            return trim($html);
        }
        
        $sessionMessages = ['register-success', 'register'];
        
        foreach($sessionMessages as $key) {
            if(isset($_SESSION[$key]) && !empty($_SESSION[$key])) {
                $message = extractMessage($_SESSION[$key]);
                if(!empty($message)) {
                    $icon = 'info';
                    $title = 'Thông báo';
                    
                    if(strpos(strtolower($_SESSION[$key]), 'success') !== false || 
                       strpos(strtolower($message), 'thành công') !== false ||
                       strpos(strtolower($message), 'successfully') !== false) {
                        $icon = 'success';
                        $title = 'Thành công!';
                    } elseif(strpos(strtolower($_SESSION[$key]), 'error') !== false || 
                             strpos(strtolower($message), 'lỗi') !== false ||
                             strpos(strtolower($message), 'failed') !== false ||
                             strpos(strtolower($message), 'không khớp') !== false ||
                             strpos(strtolower($message), 'đã tồn tại') !== false ||
                             strpos(strtolower($message), 'không hợp lệ') !== false ||
                             strpos(strtolower($message), 'bắt buộc') !== false ||
                             strpos(strtolower($message), 'already exists') !== false ||
                             strpos(strtolower($message), 'do not match') !== false) {
                        $icon = 'error';
                        $title = 'Lỗi!';
                    } elseif(strpos(strtolower($message), 'warning') !== false) {
                        $icon = 'warning';
                        $title = 'Cảnh báo!';
                    } elseif($key === 'register-info') {
                        $icon = 'info';
                        $title = 'Thông tin test';
                    }
                    
                    // Xử lý HTML trong message (cho register-info)
                    $htmlContent = '';
                    if($key === 'register-info' && strpos($_SESSION[$key], '<') !== false) {
                        $htmlContent = ', html: `' . $_SESSION[$key] . '`';
                    }
                    
                    echo "Swal.fire({
                        icon: '" . $icon . "',
                        title: '" . $title . "',
                        " . ($htmlContent ? $htmlContent : "text: '" . addslashes($message) . "'") . ",
                        showConfirmButton: true,
                        timer: " . ($key === 'register-info' ? '5000' : '3000') . "
                    });";
                }
                unset($_SESSION[$key]);
            }
        }
        ?>
    </script>
</body>
</html>
