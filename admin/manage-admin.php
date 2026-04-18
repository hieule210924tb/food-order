<?php include('partials/menu.php'); ?>

<div class="main-content">
    <div class="wrapper">
        <h1 style="margin-bottom:10px;">Quản lý quản trị viên</h1>
        <p style="color:#747d8c; margin-bottom:25px;">
            Danh sách các tài khoản quản trị hệ thống.
        </p>

        <a href="add-admin.php" class="btn-primary-custom">
            + Thêm quản trị viên
        </a>

        <div class="table-container">
            <table class="tbl-full-custom">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th style="text-align:left;">Họ tên</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th style="text-align:center;">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $hasPhone = false;
                    $colRes = mysqli_query($conn, "SHOW COLUMNS FROM tbl_admin LIKE 'phone'");
                    if ($colRes && mysqli_num_rows($colRes) > 0) $hasPhone = true;

                    $sql = $hasPhone
                        ? "SELECT id, full_name, email, phone FROM tbl_admin ORDER BY id ASC"
                        : "SELECT id, full_name, email, username FROM tbl_admin ORDER BY id ASC";
                    $res = mysqli_query($conn, $sql);

                    if ($res instanceof mysqli_result) {
                        $sn = 1;
                        if (mysqli_num_rows($res) > 0) {
                            while ($rows = mysqli_fetch_assoc($res)) {
                                // Trích xuất dữ liệu
                                $id = $rows['id'];
                                $full_name = htmlspecialchars($rows['full_name']);
                                $email = htmlspecialchars($rows['email']);
                                $phone = $hasPhone
                                    ? htmlspecialchars($rows['phone'])
                                    : htmlspecialchars($rows['username']);
                                ?>

                                <tr>
                                    <td><?php echo $sn++; ?></td>
                                    <td style="font-weight:600;"><?php echo $full_name; ?></td>
                                    <td><?php echo $email; ?></td>
                                    <td><?php echo $phone; ?></td>
                                    <td style="text-align:center; white-space:nowrap;">
                                        <a href="<?php echo SITEURL; ?>admin/update-admin.php?id=<?php echo $id; ?>" class="btn-secondary-custom">
                                            Cập nhật
                                        </a>
                                        <a href="<?php echo SITEURL; ?>admin/delete-admin.php?id=<?php echo $id; ?>" class="btn-danger-custom">
                                            Xóa
                                        </a>
                                    </td>
                                </tr>

                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="5" class="error-msg">Chưa có quản trị viên nào</td></tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5" class="error-msg">Lỗi khi tải danh sách quản trị viên</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .btn-primary-custom {
        display: inline-block;
        margin-bottom: 20px;
        padding: 8px 16px;
        border-radius: 999px;
        background: #1e90ff;
        color: white;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
    }
    .table-container {
        background: #ffffff;
        border-radius: 12px;
        padding: 18px 20px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        border: 1px solid #ecf0f1;
        overflow-x: auto;
    }
    .tbl-full-custom {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 14px;
    }
    .tbl-full-custom thead tr { background: #f8f9fb; }
    .tbl-full-custom th { padding: 12px 10px; border-bottom: 1px solid #e0e6ed; }
    .tbl-full-custom td { padding: 10px 8px; border-bottom: 1px solid #f0f2f5; }
    .btn-secondary-custom {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 999px;
        background: #ecf0f1;
        color: #2c3e50;
        font-size: 12px;
        text-decoration: none;
        margin-right: 4px;
    }
    .btn-danger-custom {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 999px;
        background: #ff6b81;
        color: white;
        font-size: 12px;
        text-decoration: none;
    }
    .error-msg { color: red; text-align: center; padding: 15px; }
</style>

<?php include('partials/footer.php'); ?>