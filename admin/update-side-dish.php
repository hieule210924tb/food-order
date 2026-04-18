<?php
require_once('../config/constants.php');
require_once('partials/login-check.php');

if (!isset($_GET['id'])) {
    header('location:' . SITEURL . 'admin/manage-side-dish.php');
    exit();
}

$id  = (int) $_GET['id'];
$sql = "SELECT * FROM tbl_side_dish WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row    = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    header('location:' . SITEURL . 'admin/manage-side-dish.php');
    exit();
}

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
        header('location:update-side-dish.php?id=' . $id);
        exit();
    }

    $sql_u = "UPDATE tbl_side_dish SET `name` = ?, `price` = ?, `type` = ?, `sort_order` = ? WHERE id = ?";
    $stmt_u = mysqli_prepare($conn, $sql_u);
    mysqli_stmt_bind_param($stmt_u, "sdsii", $name_raw, $price, $type_raw, $sort_order, $id);

    if (mysqli_stmt_execute($stmt_u)) {
        $_SESSION['update_side_dish'] = "<div class='success'>Cập nhật món/nước kèm thành công!</div>";
        mysqli_stmt_close($stmt_u);
        header('location:manage-side-dish.php');
        exit();
    }

    mysqli_stmt_close($stmt_u);
    $_SESSION['update_side_dish'] = "<div class='error'>Lỗi hệ thống, vui lòng thử lại sau.</div>";
    header('location:update-side-dish.php?id=' . $id);
    exit();
}

include('partials/menu.php');
?>

<div class="main-content">
    <div class="wrapper">
        <h1 style="margin-bottom:10px;">Cập nhật món/nước kèm</h1>
        <p style="color:#747d8c; margin-bottom:25px;">
            Chỉnh sửa thông tin món/nước đi kèm trong hệ thống.
        </p>

        <div style="background:#ffffff; border-radius:12px; padding:25px;
                    box-shadow:0 4px 14px rgba(0,0,0,0.06);
                    border:1px solid #ecf0f1; max-width:750px;">

            <form action="" method="post">
                <table style="width:100%; border-collapse:separate; border-spacing:0 14px; font-size:14px;">
                    <tr>
                        <td style="width:180px; font-weight:600;">Tên món/nước kèm</td>
                        <td>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>"
                                   style="width:100%; padding:8px; border:1px solid #dfe4ea; border-radius:6px;">
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Giá thêm</td>
                        <td>
                            <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($row['price']); ?>"
                                   style="width:100%; padding:8px; border:1px solid #dfe4ea; border-radius:6px;">
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Loại</td>
                        <td>
                            <label style="margin-right:15px; cursor:pointer;">
                                <input type="radio" name="type" value="food" <?php echo ($row['type'] === 'food') ? 'checked' : ''; ?>> Món ăn
                            </label>
                            <label style="cursor:pointer;">
                                <input type="radio" name="type" value="drink" <?php echo ($row['type'] === 'drink') ? 'checked' : ''; ?>> Nước uống
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Thứ tự hiển thị</td>
                        <td>
                            <input type="number" name="sort_order" value="<?php echo (int) $row['sort_order']; ?>"
                                   style="width:100%; padding:8px; border:1px solid #dfe4ea; border-radius:6px;">
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" style="padding-top:15px;">
                            <button type="submit" name="submit"
                                    style="padding:10px 25px; border-radius:999px; background:#1e90ff;
                                           color:white; font-size:13px; font-weight:600; border:none; cursor:pointer;">
                                Cập nhật món/nước kèm
                            </button>

                            <a href="manage-side-dish.php"
                               style="margin-left:10px; padding:10px 20px; border-radius:999px;
                                      background:#ecf0f1; color:#2c3e50; font-size:13px;
                                      text-decoration:none; font-weight:500;">
                                Quay lại
                            </a>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>

<?php
if (isset($_SESSION['side_dish_form_errors'])) {
    $msg = implode("\\n", $_SESSION['side_dish_form_errors']);
    unset($_SESSION['side_dish_form_errors']);
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>Swal.fire({icon:'error', title:'Lỗi nhập liệu', text:'".addslashes($msg)."'});</script>";
}
include('partials/footer.php');
?>

