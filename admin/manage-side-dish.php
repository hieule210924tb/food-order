<?php
require_once('../config/constants.php');
require_once('partials/login-check.php');
require_once('partials/menu.php');
?>

<div class="main-content">
    <div class="wrapper">
        <h1 style="margin-bottom: 10px;">Quản lý món/nước kèm</h1>
        <p style="color:#747d8c;margin-bottom:25px;">Danh sách các món/nước đi kèm mà khách có thể chọn thêm.</p>

        <a href="add-side-dish.php" style="display:inline-block;margin-bottom:20px;padding:8px 16px;border-radius:999px;
                  background:#1e90ff;color:white;font-size:13px;font-weight:500;text-decoration:none;">
            + Thêm món/nước kèm
        </a>

        <div style="background:#ffffff;border-radius:12px;padding:18px 20px;
                    box-shadow:0 4px 14px rgba(0,0,0,0.06);
                    border:1px solid #ecf0f1;overflow-x:auto;">

            <table style="width:100%;border-collapse:separate;border-spacing:0;font-size:14px;">
                <thead>
                    <tr style="background:#f8f9fb;">
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;">STT</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">Tên món/nước</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;">Loại</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;">Giá thêm</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;">Thứ tự</th>
                        <th style="padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM tbl_side_dish ORDER BY sort_order ASC, id ASC";
                    $res = mysqli_query($conn, $sql);
                    $sn  = 1;

                    if ($res && mysqli_num_rows($res) > 0) {
                        while ($row = mysqli_fetch_assoc($res)) {
                            $id         = (int) $row['id'];
                            $name       = $row['name'];
                            $price      = (float) $row['price'];
                            $type       = $row['type'];
                            $sort_order = (int) $row['sort_order'];
                            ?>
                            <tr style="border-bottom:1px solid #f0f2f5;">
                                <td style="padding:10px 8px;"><?php echo $sn++; ?></td>
                                <td style="padding:10px 8px;font-weight:600;"><?php echo htmlspecialchars($name); ?></td>
                                <td style="padding:10px 8px;">
                                    <?php if ($type === 'drink'): ?>
                                        <span style="padding:3px 10px;border-radius:999px;background:#e3f2fd;color:#1565c0;font-size:12px;">Nước uống</span>
                                    <?php else: ?>
                                        <span style="padding:3px 10px;border-radius:999px;background:#e8f5e9;color:#2e7d32;font-size:12px;">Món ăn</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:10px 8px;text-align:left;font-weight:600;color:#ff6b81;">
                                    <?php echo number_format($price, 0, ',', '.'); ?> đ
                                </td>
                                <td style="padding:10px 8px;text-align:center;"><?php echo $sort_order; ?></td>
                                <td style="padding:10px 8px;text-align:center;white-space:nowrap;">
                                    <a href="update-side-dish.php?id=<?php echo $id; ?>" style="display:inline-block;padding:6px 12px;border-radius:999px;
                                      background:#ecf0f1;color:#2c3e50;
                                      font-size:12px;text-decoration:none;margin-right:4px;">
                                        Cập nhật
                                    </a>
                                    <a href="delete-side-dish.php?id=<?php echo $id; ?>"
                                       style="display:inline-block;padding:6px 12px;border-radius:999px;
                                      background:#ff6b81;color:white;
                                      font-size:12px;text-decoration:none;"
                                      onclick="return confirm('Bạn có chắc muốn xóa món/nước kèm này?');">
                                        Xóa
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' style='color:red;text-align:center;padding:14px 8px;'>Chưa có món/nước kèm nào</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include('partials/footer.php'); ?>

