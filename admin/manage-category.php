<?php
require_once('../config/constants.php');
require_once('partials/login-check.php');

function chuyenTrangQuanLyDanhMuc($query = '')
{
    $url = SITEURL . 'admin/manage-category.php';
    if ($query !== '') {
        $url .= '?' . ltrim($query, '?');
    }

    header('location:' . $url);
    exit;
}

function datThongBaoTam($type, $message)
{
    $_SESSION['manage_category_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function layThongBaoTam()
{
    if (!isset($_SESSION['manage_category_flash'])) {
        return null;
    }

    $flash = $_SESSION['manage_category_flash'];
    unset($_SESSION['manage_category_flash']);

    return $flash;
}

function chuanHoaYesNo($value, $default = 'No')
{
    if ($value === 'Yes' || $value === 'No') {
        return $value;
    }

    return $default;
}

function layThuMucAnhDanhMuc()
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR . 'category';
}

function xoaTepAnhDanhMuc($tenAnh)
{
    if ($tenAnh === '') {
        return;
    }

    $duongDan = layThuMucAnhDanhMuc() . DIRECTORY_SEPARATOR . $tenAnh;
    if (is_file($duongDan)) {
        @unlink($duongDan);
    }
}

function taiLenAnhDanhMuc($tepTin)
{
    if (!isset($tepTin) || ($tepTin['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'image_name' => ''];
    }

    if (($tepTin['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Tải ảnh lên thất bại.'];
    }

    $extension = strtolower(pathinfo($tepTin['name'], PATHINFO_EXTENSION));
    $dinhDangChoPhep = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($extension, $dinhDangChoPhep, true)) {
        return ['success' => false, 'message' => 'Chỉ chấp nhận ảnh JPG, JPEG, PNG, GIF hoặc WEBP.'];
    }

    $tenAnh = 'Food_Category_' . mt_rand(100, 999) . '.' . $extension;
    $duongDan = layThuMucAnhDanhMuc() . DIRECTORY_SEPARATOR . $tenAnh;

    if (!move_uploaded_file($tepTin['tmp_name'], $duongDan)) {
        return ['success' => false, 'message' => 'Không thể lưu ảnh vào thư mục danh mục.'];
    }

    return ['success' => true, 'image_name' => $tenAnh];
}

function layDanhMucTheoId($conn, $id)
{
    $stmt = mysqli_prepare($conn, 'SELECT id, title, featured, active, image_name FROM tbl_category WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $category = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    return $category ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $featured = chuanHoaYesNo($_POST['featured'] ?? 'No');
    $active = chuanHoaYesNo($_POST['active'] ?? 'Yes', 'Yes');
    $xoaAnhHienTai = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';

    if ($title === '') {
        datThongBaoTam('error', 'Vui lòng nhập tên danh mục.');
        chuyenTrangQuanLyDanhMuc();
    }

    if ($action === 'add') {
        $upload = taiLenAnhDanhMuc($_FILES['image'] ?? null);
        if (!$upload['success']) {
            datThongBaoTam('error', $upload['message']);
            chuyenTrangQuanLyDanhMuc();
        }

        $stmt = mysqli_prepare($conn, 'INSERT INTO tbl_category (title, featured, active, image_name) VALUES (?, ?, ?, ?)');
        if (!$stmt) {
            datThongBaoTam('error', 'Không thể thêm danh mục vào cơ sở dữ liệu.');
            chuyenTrangQuanLyDanhMuc();
        }

        mysqli_stmt_bind_param($stmt, 'ssss', $title, $featured, $active, $upload['image_name']);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$success) {
            xoaTepAnhDanhMuc($upload['image_name']);
            datThongBaoTam('error', 'Thêm danh mục thất bại.');
            chuyenTrangQuanLyDanhMuc();
        }

        datThongBaoTam('success', 'Thêm danh mục thành công.');
        chuyenTrangQuanLyDanhMuc();
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $currentCategory = layDanhMucTheoId($conn, $id);

        if (!$currentCategory) {
            datThongBaoTam('error', 'Không tìm thấy danh mục cần cập nhật.');
            chuyenTrangQuanLyDanhMuc();
        }

        $tenAnhMoi = $currentCategory['image_name'];
        $tenAnhMoiVuaTai = '';

        if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $upload = taiLenAnhDanhMuc($_FILES['image']);
            if (!$upload['success']) {
                datThongBaoTam('error', $upload['message']);
                chuyenTrangQuanLyDanhMuc('edit_id=' . $id);
            }

            $tenAnhMoiVuaTai = $upload['image_name'];
            $tenAnhMoi = $tenAnhMoiVuaTai;
        } elseif ($xoaAnhHienTai) {
            $tenAnhMoi = '';
        }

        $stmt = mysqli_prepare($conn, 'UPDATE tbl_category SET title = ?, featured = ?, active = ?, image_name = ? WHERE id = ?');
        if (!$stmt) {
            xoaTepAnhDanhMuc($tenAnhMoiVuaTai);
            datThongBaoTam('error', 'Không thể cập nhật danh mục.');
            chuyenTrangQuanLyDanhMuc('edit_id=' . $id);
        }

        mysqli_stmt_bind_param($stmt, 'ssssi', $title, $featured, $active, $tenAnhMoi, $id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$success) {
            xoaTepAnhDanhMuc($tenAnhMoiVuaTai);
            datThongBaoTam('error', 'Cập nhật danh mục thất bại.');
            chuyenTrangQuanLyDanhMuc('edit_id=' . $id);
        }

        if ($tenAnhMoiVuaTai !== '' && $currentCategory['image_name'] !== '') {
            xoaTepAnhDanhMuc($currentCategory['image_name']);
        }

        if ($tenAnhMoiVuaTai === '' && $xoaAnhHienTai && $currentCategory['image_name'] !== '') {
            xoaTepAnhDanhMuc($currentCategory['image_name']);
        }

        datThongBaoTam('success', 'Cập nhật danh mục thành công.');
        chuyenTrangQuanLyDanhMuc();
    }
}

if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    $category = layDanhMucTheoId($conn, $deleteId);

    if (!$category) {
        datThongBaoTam('error', 'Không tìm thấy danh mục cần xóa.');
        chuyenTrangQuanLyDanhMuc();
    }

    $stmt = mysqli_prepare($conn, 'DELETE FROM tbl_category WHERE id = ?');
    if (!$stmt) {
        datThongBaoTam('error', 'Không thể xóa danh mục.');
        chuyenTrangQuanLyDanhMuc();
    }

    mysqli_stmt_bind_param($stmt, 'i', $deleteId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$success) {
        datThongBaoTam('error', 'Xóa danh mục thất bại.');
        chuyenTrangQuanLyDanhMuc();
    }

    xoaTepAnhDanhMuc($category['image_name']);
    datThongBaoTam('success', 'Xóa danh mục thành công.');
    chuyenTrangQuanLyDanhMuc();
}

$keyword = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? 'all';
$editId = (int)($_GET['edit_id'] ?? 0);

$conditions = [];
$params = [];
$types = '';

if ($keyword !== '') {
    $conditions[] = 'title LIKE ?';
    $params[] = '%' . $keyword . '%';
    $types .= 's';
}

if ($status === 'active') {
    $conditions[] = 'active = ?';
    $params[] = 'Yes';
    $types .= 's';
} elseif ($status === 'inactive') {
    $conditions[] = 'active = ?';
    $params[] = 'No';
    $types .= 's';
}

$sql = 'SELECT id, title, featured, active, image_name FROM tbl_category';
if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY id DESC';

$categories = [];
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($result && ($row = mysqli_fetch_assoc($result))) {
        $categories[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$editCategory = null;
if ($editId > 0) {
    $editCategory = layDanhMucTheoId($conn, $editId);
}

$flash = layThongBaoTam();

include('partials/menu.php');
?>
<div class="main-content">
    <div class="wrapper">
        <div class="category-page">
            <div class="page-header">
                <div>
                    <h1>Quản lý danh mục</h1>
                    <p>Danh sách danh mục món ăn đang có trong hệ thống.</p>
                </div>
            </div>

            <?php if ($flash) { ?>
                <div class="notice notice-<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php } ?>

            <div class="category-panel">
                <div class="panel-head">
                    <h2><?php echo $editCategory ? 'Cập nhật danh mục' : 'Thêm danh mục mới'; ?></h2>
                    <?php if ($editCategory) { ?>
                        <a href="manage-category.php" class="text-link">Hủy chỉnh sửa</a>
                    <?php } ?>
                </div>

                <form method="post" enctype="multipart/form-data" class="category-form">
                    <input type="hidden" name="action" value="<?php echo $editCategory ? 'update' : 'add'; ?>">
                    <?php if ($editCategory) { ?>
                        <input type="hidden" name="id" value="<?php echo (int)$editCategory['id']; ?>">
                    <?php } ?>

                    <div class="form-grid">
                        <div class="field">
                            <label for="title">Tên danh mục</label>
                            <input id="title" type="text" name="title" required value="<?php echo htmlspecialchars($editCategory['title'] ?? ''); ?>">
                        </div>

                        <div class="field">
                            <label for="image">Hình ảnh</label>
                            <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp">
                        </div>

                        <div class="field">
                            <label for="featured">Nổi bật</label>
                            <select id="featured" name="featured">
                                <option value="Yes" <?php echo (($editCategory['featured'] ?? 'No') === 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                <option value="No" <?php echo (($editCategory['featured'] ?? 'No') === 'No') ? 'selected' : ''; ?>>No</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="active">Hoạt động</label>
                            <select id="active" name="active">
                                <option value="Yes" <?php echo (($editCategory['active'] ?? 'Yes') === 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                <option value="No" <?php echo (($editCategory['active'] ?? 'Yes') === 'No') ? 'selected' : ''; ?>>No</option>
                            </select>
                        </div>
                    </div>

                    <?php if ($editCategory && $editCategory['image_name'] !== '') { ?>
                        <div class="image-preview-row">
                            <div>
                                <span class="preview-label">Ảnh hiện tại</span>
                                <img src="<?php echo SITEURL; ?>image/category/<?php echo htmlspecialchars($editCategory['image_name']); ?>" alt="<?php echo htmlspecialchars($editCategory['title']); ?>" class="preview-image">
                            </div>
                            <label class="checkbox">
                                <input type="checkbox" name="remove_image" value="1">
                                Xóa ảnh hiện tại nếu không tải ảnh mới
                            </label>
                        </div>
                    <?php } ?>

                    <div class="form-actions">
                        <button type="submit" class="submit-btn"><?php echo $editCategory ? 'Lưu thay đổi' : 'Thêm danh mục'; ?></button>
                    </div>
                </form>
            </div>

            <div class="category-panel">
                <div class="panel-head">
                    <h2>Danh sách danh mục</h2>
                </div>

                <form method="get" class="filter-bar">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Tìm theo tên danh mục">
                    <select name="status">
                        <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Tất cả trạng thái</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                        <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Ngừng hoạt động</option>
                    </select>
                    <button type="submit">Lọc</button>
                </form>

                <div class="table-card">
                    <table class="category-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên danh mục</th>
                                <th>Hình ảnh</th>
                                <th>Nổi bật</th>
                                <th>Hoạt động</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$categories) { ?>
                                <tr>
                                    <td colspan="6" class="empty-row">Chưa có danh mục phù hợp.</td>
                                </tr>
                            <?php } else { ?>
                                <?php $sn = 1; ?>
                                <?php foreach ($categories as $category) { ?>
                                    <tr>
                                        <td><?php echo $sn++; ?></td>
                                        <td>
                                            <div class="cell-title">
                                                <strong><?php echo htmlspecialchars($category['title']); ?></strong>
                                                <span>ID: <?php echo (int)$category['id']; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($category['image_name'] !== '') { ?>
                                                <img src="<?php echo SITEURL; ?>image/category/<?php echo htmlspecialchars($category['image_name']); ?>" alt="<?php echo htmlspecialchars($category['title']); ?>" class="table-image">
                                            <?php } else { ?>
                                                <span class="missing-image">Chưa có hình ảnh</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <span class="status <?php echo $category['featured'] === 'Yes' ? 'yes' : 'no'; ?>">
                                                <?php echo $category['featured']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status <?php echo $category['active'] === 'Yes' ? 'yes' : 'no-danger'; ?>">
                                                <?php echo $category['active']; ?>
                                            </span>
                                        </td>
                                        <td class="actions">
                                            <a href="manage-category.php?edit_id=<?php echo (int)$category['id']; ?>" class="action-btn action-edit">Cập nhật</a>
                                            <a href="manage-category.php?delete_id=<?php echo (int)$category['id']; ?>" class="action-btn action-delete" onclick="return confirm('Bạn có chắc muốn xóa danh mục này không?')">Xóa</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.category-page{display:grid;gap:20px}
.page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
.page-header h1{margin:0 0 10px;font-size:32px;color:#2d3436}
.page-header p{margin:0;color:#747d8c}
.category-panel{background:#fff;border-radius:12px;padding:18px 20px;box-shadow:0 4px 14px rgba(0,0,0,.06);border:1px solid #ecf0f1}
.panel-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:16px}
.panel-head h2{margin:0;font-size:20px;color:#2d3436}
.text-link{color:#1e90ff;text-decoration:none;font-size:14px}
.notice{padding:12px 14px;border-radius:10px;font-size:14px}
.notice-success{background:#edfdf3;border:1px solid #b7ebc6;color:#1e7e34}
.notice-error{background:#fff5f5;border:1px solid #ffc9c9;color:#c92a2a}
.category-form{display:grid;gap:16px}
.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.field{display:flex;flex-direction:column;gap:8px}
.field label,.preview-label{font-weight:600;color:#57606f;font-size:14px}
.field input[type="text"],.field input[type="file"],.field select{width:100%;padding:11px 12px;border:1px solid #dfe4ea;border-radius:10px;font-size:14px;background:#fff;box-sizing:border-box}
.image-preview-row{display:flex;justify-content:space-between;gap:16px;align-items:flex-end;flex-wrap:wrap}
.preview-image{display:block;width:110px;height:90px;object-fit:cover;border-radius:10px;margin-top:8px;border:1px solid #e0e6ed}
.checkbox{display:flex;align-items:center;gap:8px;color:#57606f;font-size:14px}
.form-actions{display:flex}
.submit-btn{display:inline-block;padding:10px 18px;border:none;border-radius:999px;background:#1e90ff;color:#fff;font-size:13px;font-weight:500;cursor:pointer}
.filter-bar{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px}
.filter-bar input,.filter-bar select{padding:10px 12px;border:1px solid #dfe4ea;border-radius:10px;font-size:14px;background:#fff}
.filter-bar input{min-width:260px;flex:1}
.filter-bar button{padding:10px 16px;border:none;border-radius:10px;background:#2f3542;color:#fff;cursor:pointer}
.table-card{overflow-x:auto}
.category-table{width:100%;border-collapse:separate;border-spacing:0;font-size:14px;min-width:860px}
.category-table thead tr{background:#f8f9fb}
.category-table th{padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;color:#57606f}
.category-table td{padding:10px 8px;border-bottom:1px solid #f0f2f5;vertical-align:middle;color:#2f3542}
.cell-title{display:flex;flex-direction:column;gap:4px}
.cell-title strong{font-size:14px}
.cell-title span{font-size:12px;color:#747d8c}
.table-image{width:90px;height:68px;object-fit:cover;border-radius:8px}
.missing-image{color:#ff6b81;font-size:12px}
.status{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600}
.status.yes{background:#edfdf3;color:#1e7e34}
.status.no{background:#f1f2f6;color:#57606f}
.status.no-danger{background:#fff5f5;color:#c92a2a}
.actions{white-space:nowrap}
.action-btn{display:inline-block;padding:6px 12px;border-radius:999px;font-size:12px;text-decoration:none}
.action-edit{background:#ecf0f1;color:#2c3e50;margin-right:4px}
.action-delete{background:#ff6b81;color:#fff}
.empty-row{text-align:center;color:#747d8c}
@media (max-width: 768px){
    .page-header{flex-direction:column}
    .form-grid{grid-template-columns:1fr}
    .filter-bar input{min-width:100%}
}
</style>

<?php include('partials/footer.php'); ?>
