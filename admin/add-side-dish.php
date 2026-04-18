<?php
require_once('../config/constants.php');
require_once('partials/login-check.php');

if (isset($_POST['submit'])) {
    $errors = [];

    $name_raw       = trim($_POST['name'] ?? '');
    $price_raw      = $_POST['price'] ?? '';
    $type_raw       = $_POST['type'] ?? 'food';
    $sort_order_raw = $_POST['sort_order'] ?? '0';

    if (mb_strlen($name_raw) < 2) {
        $errors[] = "Tên món/nước kèm phải có ít nhất 2 ký tự.";
    }
    if ($price_raw === '' || !is_numeric($price_raw) || floatval($price_raw) < 0) {
        $errors[] = "Vui lòng nhập giá hợp lệ (>= 0).";
    }

    $allowed_types = ['food', 'drink'];
    if (!in_array($type_raw, $allowed_types, true)) {
        $errors[] = "Loại món kèm không hợp lệ.";
    }

    $price      = floatval($price_raw);
    $sort_order = (int) $sort_order_raw;

    if (!empty($errors)) {
        $_SESSION['side_dish_form_errors'] = $errors;
        $_SESSION['side_dish_form_old']    = $_POST;
        header('location:add-side-dish.php');
        exit();
    }

    $sql = "INSERT INTO tbl_side_dish (`name`, `price`, `type`, `sort_order`) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sdsi", $name_raw, $price, $type_raw, $sort_order);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['add_side_dish'] = "<div class='success'>Thêm món/nước kèm thành công!</div>";
        mysqli_stmt_close($stmt);
        header('location:manage-side-dish.php');
        exit();
    }

    mysqli_stmt_close($stmt);
    $_SESSION['add_side_dish'] = "<div class='error'>Lỗi hệ thống, vui lòng thử lại sau.</div>";
    header('location:add-side-dish.php');
    exit();
}

include('partials/menu.php');
?>

<div class="main-content">
    <div class="wrapper">
        <h1 class="heading">Thêm món/nước kèm</h1>
        <p class="sub-heading">Thêm các lựa chọn đi kèm cho món chính (trứng ốp la, nước ngọt, ...).</p>

        <div class="form-container">
            <form action="" method="post">
                <table class="tbl-form">
                    <tr>
                        <td class="lbl">Tên món/nước kèm</td>
                        <td>
                            <input type="text" name="name" class="input-ctrl"
                                   value="<?php echo htmlspecialchars($_SESSION['side_dish_form_old']['name'] ?? ''); ?>">
                        </td>
                    </tr>

                    <tr>
                        <td class="lbl">Giá thêm</td>
                        <td>
                            <input type="number" name="price" step="0.01" class="input-ctrl"
                                   value="<?php echo htmlspecialchars($_SESSION['side_dish_form_old']['price'] ?? ''); ?>">
                        </td>
                    </tr>

                    <tr>
                        <td class="lbl">Loại</td>
                        <td>
                            <?php $old_type = $_SESSION['side_dish_form_old']['type'] ?? 'food'; ?>
                            <label><input type="radio" name="type" value="food" <?php if ($old_type === 'food') echo 'checked'; ?>> Món ăn</label>
                            <label style="margin-left:15px;"><input type="radio" name="type" value="drink" <?php if ($old_type === 'drink') echo 'checked'; ?>> Nước uống</label>
                        </td>
                    </tr>

                    <tr>
                        <td class="lbl">Thứ tự hiển thị</td>
                        <td>
                            <input type="number" name="sort_order" class="input-ctrl"
                                   value="<?php echo htmlspecialchars($_SESSION['side_dish_form_old']['sort_order'] ?? '0'); ?>">
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <button type="submit" name="submit" class="btn-submit">Thêm món/nước kèm</button>
                            <a href="manage-side-dish.php" class="btn-back">Quay lại</a>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>

<style>
    .heading { margin-bottom: 10px; }
    .sub-heading { color: #747d8c; margin-bottom: 25px; }
    .form-container {
        background: #fff; border-radius: 12px; padding: 25px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06); border: 1px solid #ecf0f1;
        max-width: 750px;
    }
    .tbl-form { width: 100%; border-collapse: separate; border-spacing: 0 14px; }
    .lbl { width: 180px; font-weight: 600; font-size: 14px; }
    .input-ctrl { width: 100%; padding: 10px; border: 1px solid #dfe4ea; border-radius: 6px; }
    .btn-submit {
        padding: 10px 20px; border-radius: 999px; background: #1e90ff;
        color: white; font-weight: 500; border: none; cursor: pointer;
    }
    .btn-back {
        margin-left: 10px; padding: 10px 20px; border-radius: 999px;
        background: #ecf0f1; color: #2c3e50; text-decoration: none; font-size: 13px;
    }
</style>

<?php
if (isset($_SESSION['side_dish_form_errors'])) {
    $msg = implode("\\n", $_SESSION['side_dish_form_errors']);
    unset($_SESSION['side_dish_form_errors'], $_SESSION['side_dish_form_old']);
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>Swal.fire({icon:'error', title:'Lỗi nhập liệu', text:'".addslashes($msg)."'});</script>";
}
include('partials/footer.php');
?>

