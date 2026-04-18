<?php
require_once('../config/constants.php');
require_once('partials/login-check.php');

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

<?php include('partials/footer.php'); ?>