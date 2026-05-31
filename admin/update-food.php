<?php
require_once('../config/constants.php');
require_once('partials/login-check.php');

if (isset($_POST['submit'])) {
    $errors = [];

    $id           = intval($_POST['id'] ?? 0);
    $title        = trim($_POST['title'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $price_raw    = $_POST['price'] ?? '';
    $category     = intval($_POST['category'] ?? 0);
    $featured_raw = $_POST['featured'] ?? '';
    $active_raw   = $_POST['active'] ?? '';
    $current_image = trim($_POST['current_image'] ?? '');

    $featured = ($featured_raw === 'Yes') ? 'Yes' : (($featured_raw === 'No') ? 'No' : '');
    $active   = ($active_raw === 'Yes') ? 'Yes' : (($active_raw === 'No') ? 'No' : '');

    if ($id <= 0) {
        $errors[] = 'Món ăn không hợp lệ.';
    }
    if (mb_strlen($title) < 3) {
        $errors[] = 'Tên món phải có ít nhất 3 ký tự.';
    }
    if (mb_strlen($description) < 10) {
        $errors[] = 'Mô tả phải có ít nhất 10 ký tự.';
    }
    if ($price_raw === '' || !is_numeric($price_raw) || floatval($price_raw) < 0) {
        $errors[] = 'Vui lòng nhập giá hợp lệ (>= 0).';
    }
    if ($category <= 0) {
        $errors[] = 'Vui lòng chọn danh mục.';
    }
    if ($featured === '') {
        $errors[] = "Vui lòng chọn trạng thái 'Nổi bật'.";
    }
    if ($active === '') {
        $errors[] = "Vui lòng chọn trạng thái 'Hoạt động'.";
    }

    $price       = floatval($price_raw);
    $image_name  = $current_image;

    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] !== '') {
        $original_name = $_FILES['image']['name'];
        $ext           = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed       = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];

        if (!in_array($ext, $allowed, true)) {
            $errors[] = 'Định dạng ảnh không hợp lệ.';
        } else {
            $image_name      = 'Food-name-' . rand(0, 9999) . '.' . $ext;
            $source_path     = $_FILES['image']['tmp_name'];
            $destination_path = '../image/food/' . $image_name;

            if (!move_uploaded_file($source_path, $destination_path)) {
                $errors[] = 'Tải hình ảnh lên máy chủ thất bại.';
            } elseif ($current_image !== '') {
                $old_path = '../image/food/' . $current_image;
                if (is_file($old_path)) {
                    unlink($old_path);
                }
            }
        }
    }

    if (!empty($errors)) {
        $_SESSION['food_form_errors'] = $errors;
        header('location:' . SITEURL . 'admin/update-food.php?id=' . $id);
        exit();
    }

    $sql_update = 'UPDATE tbl_food SET title = ?, `description` = ?, price = ?, image_name = ?, category_id = ?, featured = ?, active = ? WHERE id = ?';
    $stmt_update = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, 'ssdssssi', $title, $description, $price, $image_name, $category, $featured, $active, $id);

    if (mysqli_stmt_execute($stmt_update)) {
        $_SESSION['update'] = "<div class='success'>Cập nhật món ăn thành công!</div>";
        mysqli_stmt_close($stmt_update);
        header('location:' . SITEURL . 'admin/manage-food.php');
        exit();
    }

    mysqli_stmt_close($stmt_update);
    $_SESSION['update'] = "<div class='error'>Lỗi hệ thống, vui lòng thử lại sau.</div>";
    header('location:' . SITEURL . 'admin/update-food.php?id=' . $id);
    exit();
}

if (!isset($_GET['id'])) {
    header('location:' . SITEURL . 'admin/manage-food.php');
    exit();
}

$id = intval($_GET['id']);

$sql2 = "SELECT * FROM tbl_food WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql2);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$row2 = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row2) {
    header('location:' . SITEURL . 'admin/manage-food.php');
    exit();
}

$title            = $row2['title'];
$description      = $row2['description'];
$price            = $row2['price'];
$current_image    = $row2['image_name'];
$current_category = $row2['category_id'];
$featured         = $row2['featured'];
$active           = $row2['active'];

include('partials/menu.php');
?>

<div class="main-content">
    <div class="wrapper">

        <h1 style="margin-bottom:10px;">Cập nhật món ăn</h1>
        <p style="color:#747d8c; margin-bottom:25px;">
            Chỉnh sửa thông tin món ăn trong hệ thống.
        </p>

        <?php
        if (isset($_SESSION['update'])) {
            echo $_SESSION['update'];
            unset($_SESSION['update']);
        }
        ?>

        <div style="background:#ffffff; border-radius:12px; padding:25px; 
                    box-shadow:0 4px 14px rgba(0,0,0,0.06); 
                    border:1px solid #ecf0f1; max-width:750px;">

            <form action="" method="post" enctype="multipart/form-data">
                <table style="width:100%; border-collapse:separate; border-spacing:0 14px; font-size:14px;">

                    <tr>
                        <td style="width:180px; font-weight:600;">Tên món</td>
                        <td>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>"
                                   style="width:100%; padding:8px; border:1px solid #dfe4ea; border-radius:6px;">
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Mô tả</td>
                        <td>
                            <textarea name="description" rows="5"
                                      style="width:100%; padding:8px; border:1px solid #dfe4ea; border-radius:6px;"><?php echo htmlspecialchars($description); ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Giá</td>
                        <td>
                            <input type="number" name="price" value="<?php echo $price; ?>"
                                   style="width:100%; padding:8px; border:1px solid #dfe4ea; border-radius:6px;">
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Hình ảnh hiện tại</td>
                        <td>
                            <?php if ($current_image != ""): ?>
                                <img src="<?php echo SITEURL; ?>image/food/<?php echo $current_image; ?>" 
                                     width="90" style="border-radius:8px;">
                            <?php else: ?>
                                <span style="color:red; font-size:13px;">Chưa có hình ảnh</span>
                            
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Ảnh mới</td>
                        <td>
                            <input type="file" name="image">
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Danh mục</td>
                        <td>
                            <select name="category" style="width:100%; padding:8px; border:1px solid #dfe4ea; border-radius:6px;">
                                <?php
                                $sql = "SELECT * FROM tbl_category WHERE active='Yes'";
                                $res = mysqli_query($conn, $sql);

                                while ($row = mysqli_fetch_assoc($res)) {
                                    $cid = $row['id'];
                                    $ctitle = $row['title'];
                                    $selected = ($cid == $current_category) ? "selected" : "";
                                    ?>
                                    <option value="<?php echo $cid; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($ctitle); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Nổi bật</td>
                        <td>
                            <label style="margin-right:15px; cursor:pointer;">
                                <input type="radio" name="featured" value="Yes" <?php echo ($featured == "Yes") ? "checked" : ""; ?>> Yes
                            </label>
                            <label style="cursor:pointer;">
                                <input type="radio" name="featured" value="No" <?php echo ($featured == "No") ? "checked" : ""; ?>> No
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight:600;">Hoạt động</td>
                        <td>
                            <label style="margin-right:15px; cursor:pointer;">
                                <input type="radio" name="active" value="Yes" <?php echo ($active == "Yes") ? "checked" : ""; ?>> Yes
                            </label>
                            <label style="cursor:pointer;">
                                <input type="radio" name="active" value="No" <?php echo ($active == "No") ? "checked" : ""; ?>> No
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" style="padding-top:15px;">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="current_image" value="<?php echo $current_image; ?>">

                            <button type="submit" name="submit" 
                                    style="padding:10px 25px; border-radius:999px; background:#1e90ff; 
                                           color:white; font-size:13px; font-weight:600; border:none; cursor:pointer;">
                                Cập nhật món ăn
                            </button>

                            <a href="manage-food.php" 
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
if (isset($_SESSION['food_form_errors'])) {
    $msg = implode("\\n", $_SESSION['food_form_errors']);
    unset($_SESSION['food_form_errors']);
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>Swal.fire({icon:'error', title:'Lỗi nhập liệu', text:'" . addslashes($msg) . "'});</script>";
}
include('partials/footer.php');
?>