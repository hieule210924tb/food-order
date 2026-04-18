    <?php
include('../config/constants.php');
require_once('partials/login-check.php');
require_once('partials/menu.php');
?>
    <div class="main-content">
        <div class="wrapper">
            <h1 style="margin-bottom: 10px;">Quản lý món ăn</h1>
            <p style="color:#747d8c;margin-bottom:25px;">Danh sách các món ăn đang có trong hệ thống.</p>

            <a href="add-food.php" style="display:inline-block;margin-bottom:20px;padding:8px 16px;border-radius:999px;
                  background:#1e90ff;color:white;font-size:13px;font-weight:500;text-decoration:none;">
                + Thêm món ăn
            </a>

            <div style="background:#ffffff;border-radius:12px;padding:18px 20px;
                    box-shadow:0 4px 14px rgba(0,0,0,0.06);
                    border:1px solid #ecf0f1;overflow-x:auto;">

                <table style="width:100%;border-collapse:separate;border-spacing:0;font-size:14px;">
                    <thead>
                        <tr style="background:#f8f9fb;">
                            <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;">STT</th>
                            <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">Tên món</th>

                            <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;">Hình ảnh</th>
                            <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">Giá</th>
                            <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;">Nổi bật</th>
                            <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;">Hoạt động</th>
                            <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:center;">Thao tác
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                    $sql="SELECT * FROM tbl_food";
                    $res = mysqli_query($conn, $sql);
                    $count = mysqli_num_rows($res);
                    $sn = 1;

                    if($count > 0){
                        while($row=mysqli_fetch_assoc($res)){
                            $id = $row['id'];
                            $title = $row['title'];
                            $price = $row['price'];
                            $image_name = $row['image_name'];
                            $featured = $row['featured'];
                            $active = $row['active'];
                ?>
                        <tr style="border-bottom:1px solid #f0f2f5;">
                            <td style="padding:10px 8px;"><?php echo $sn++; ?></td>
                            <td style="padding:10px 8px;font-weight:600;"><?php echo htmlspecialchars($title); ?></td>

                            <td style="padding:10px 8px;">
                                <?php if($image_name!=""){ ?>
                                <img src="<?php echo SITEURL; ?>image/food/<?php echo $image_name; ?>" width="90"
                                    style="border-radius:8px;">
                                <?php } else { ?>
                                <span style="color:red;font-size:12px;">Chưa có hình ảnh</span>
                                <?php } ?>
                            </td>
                            <td style="padding:10px 8px;text-align:left;font-weight:600;color:#ff6b81;">
                                <?php echo number_format($price,0,',','.'); ?> đ
                            </td>
                            <td style="padding:10px 8px;">
                                <?php if($featured=="Yes"){ ?>
                                <span style="color:green;font-weight:600;">Yes</span>
                                <?php } else { ?>
                                <span style="color:#57606f;">No</span>
                                <?php } ?>
                            </td>
                            <td style="padding:10px 8px;">
                                <?php if($active=="Yes"){ ?>
                                <span style="color:green;font-weight:600;">Yes</span>
                                <?php } else { ?>
                                <span style="color:red;font-weight:600;">No</span>
                                <?php } ?>
                            </td>
                            <td style="padding:10px 8px;text-align:center;white-space:nowrap;">
                                <a href="<?php echo SITEURL; ?>admin/update-food.php?id=<?php echo $id ?>" style="display:inline-block;padding:6px 12px;border-radius:999px;
                                      background:#ecf0f1;color:#2c3e50;
                                      font-size:12px;text-decoration:none;margin-right:4px;">
                                    Cập nhật
                                </a>
                                <a href="<?php echo SITEURL; ?>admin/delete-food.php?id=<?php echo $id ?>&image_name=<?php echo $image_name; ?>"
                                    style="display:inline-block;padding:6px 12px;border-radius:999px;
                                      background:#ff6b81;color:white;
                                      font-size:12px;text-decoration:none;">
                                    Xóa
                                </a>
                            </td>
                        </tr>
                        <?php
                        }
                    } else {
                        echo "<tr><td colspan='7' style='color:red;text-align:center;'>Chưa có món ăn nào</td></tr>";
                    }
                ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include('partials/footer.php'); ?>