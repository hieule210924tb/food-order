<?php
require_once('../config/constants.php');
require_once('partials/login-check.php');

if (isset($_POST['submit'])) {
    $errors = [];

   
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price_raw = $_POST['price'] ?? '';
    $category = intval($_POST['category'] ?? 0);
    $featured_raw = $_POST['featured'] ?? '';
    $active_raw = $_POST['active'] ?? '';

    $featured = ($featured_raw === 'Yes') ? 'Yes' : (($featured_raw === 'No') ? 'No' : '');
    $active = ($active_raw === 'Yes') ? 'Yes' : (($active_raw === 'No') ? 'No' : '');

    
    if (mb_strlen($title) < 3) $errors[] = "Tên món phải có ít nhất 3 ký tự.";
    if (mb_strlen($description) < 10) $errors[] = "Mô tả phải có ít nhất 10 ký tự.";
    if ($price_raw === '' || !is_numeric($price_raw) || floatval($price_raw) < 0) {
        $errors[] = "Vui lòng nhập giá hợp lệ (>= 0).";
    }
    if ($category <= 0) $errors[] = "Vui lòng chọn danh mục.";
    if ($featured === '') $errors[] = "Vui lòng chọn trạng thái 'Nổi bật'.";
    if ($active === '') $errors[] = "Vui lòng chọn trạng thái 'Hoạt động'.";

    $price = floatval($price_raw);
    $image_name = "";

    
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] !== '') {
        $original_name = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];

        if (!in_array($ext, $allowed, true)) {
            $errors[] = "Định dạng ảnh không hợp lệ.";
        } else {
            $image_name = "Food-name-" . rand(0, 9999) . '.' . $ext;
            $source_path = $_FILES['image']['tmp_name'];
            $destination_path = "../image/food/" . $image_name;

            if (!move_uploaded_file($source_path, $destination_path)) {
                $errors[] = "Tải hình ảnh lên máy chủ thất bại.";
            }
        }
    }

    
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_old'] = $_POST;
        header('location:add-food.php');
        exit();
    }

 
    $sql2 = "INSERT INTO tbl_food (title, `description`, price, image_name, category_id, featured, active) 
             VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql2);
    mysqli_stmt_bind_param($stmt, "ssdssss", $title, $description, $price, $image_name, $category, $featured, $active);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['add'] = "<div class='success'>Thêm món ăn thành công!</div>";
        header('location:manage-food.php');
    } else {
        $_SESSION['add'] = "<div class='error'>Lỗi hệ thống, vui lòng thử lại sau.</div>";
        header('location:add-food.php');
    }
    mysqli_stmt_close($stmt);
    exit();
}

include('partials/menu.php');
?>

<div class="main-content">
    <div class="wrapper">
        <h1 class="heading">Thêm món ăn</h1>
        <p class="sub-heading">Thêm món ăn mới vào hệ thống quản lý.</p>

        <div class="form-container">
            <form action="" method="post" enctype="multipart/form-data">
                <table class="tbl-form">
                    <tr>
                        <td class="lbl">Tên món</td>
                        <td>
                            <input type="text" name="title" class="input-ctrl" 
                                   value="<?php echo htmlspecialchars($_SESSION['form_old']['title'] ?? ''); ?>">
                        </td>
                    </tr>

                    <tr>
                        <td class="lbl">Mô tả</td>
                        <td>
                            <textarea name="description" rows="5" class="input-ctrl"><?php echo htmlspecialchars($_SESSION['form_old']['description'] ?? ''); ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <td class="lbl">Giá</td>
                        <td>
                            <input type="number" name="price" step="0.01" class="input-ctrl" 
                                   value="<?php echo htmlspecialchars($_SESSION['form_old']['price'] ?? ''); ?>">
                        </td>
                    </tr>

                    <tr>
                        <td class="lbl">Hình ảnh</td>
                        <td><input type="file" name="image"></td>
                    </tr>

                    <tr>
                        <td class="lbl">Danh mục</td>
                        <td>
                            <select name="category" class="input-ctrl">
                                <option value="0">-- Chọn danh mục --</option>
                                <?php
                                $sql = "SELECT id, title FROM tbl_category WHERE active='Yes'";
                                $res = mysqli_query($conn, $sql);
                                $old_cat = intval($_SESSION['form_old']['category'] ?? 0);

                                while ($row = mysqli_fetch_assoc($res)) {
                                    $selected = ($row['id'] == $old_cat) ? "selected" : "";
                                    echo "<option value='{$row['id']}' $selected>".htmlspecialchars($row['title'])."</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td class="lbl">Nổi bật</td>
                        <td>
                            <?php $old_f = $_SESSION['form_old']['featured'] ?? 'No'; ?>
                            <label><input type="radio" name="featured" value="Yes" <?php if($old_f=="Yes") echo "checked"; ?>> Có</label>
                            <label style="margin-left: 15px;"><input type="radio" name="featured" value="No" <?php if($old_f=="No") echo "checked"; ?>> Không</label>
                        </td>
                    </tr>

                    <tr>
                        <td class="lbl">Hoạt động</td>
                        <td>
                            <?php $old_a = $_SESSION['form_old']['active'] ?? 'Yes'; ?>
                            <label><input type="radio" name="active" value="Yes" <?php if($old_a=="Yes") echo "checked"; ?>> Có</label>
                            <label style="margin-left: 15px;"><input type="radio" name="active" value="No" <?php if($old_a=="No") echo "checked"; ?>> Không</label>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <button type="submit" name="submit" class="btn-submit">Thêm món ăn</button>
                            <a href="manage-food.php" class="btn-back">Quay lại</a>
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
    .lbl { width: 150px; font-weight: 600; font-size: 14px; }
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

if (isset($_SESSION['form_errors'])) {
    $msg = implode("\\n", $_SESSION['form_errors']);
    unset($_SESSION['form_errors']);
    unset($_SESSION['form_old']);
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>Swal.fire({icon:'error', title:'Lỗi nhập liệu', text:'".addslashes($msg)."'});</script>";
}
include('partials/footer.php');
?>