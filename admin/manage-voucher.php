<?php

if (isset($_GET['deactivate']) && ctype_digit($_GET['deactivate'])) {
    require_once __DIR__ . '/../config/constants.php';

    $vid = (int) $_GET['deactivate'];

    $stmt = $conn->prepare("UPDATE tbl_voucher SET status = 'inactive' WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $vid);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: manage-voucher.php?msg=deactivated');
    exit;
}

require_once __DIR__ . '/partials/menu.php';

$createSql = "CREATE TABLE IF NOT EXISTS tbl_voucher (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    value DECIMAL(10,2) NOT NULL DEFAULT 0,
    min_order DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_discount DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    valid_from DATETIME NULL,
    valid_to DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY code_idx (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($createSql);

$errors = [];
$success = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'deactivated') {
    $success = 'Đã ngừng áp dụng voucher.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $code = isset($_POST['code']) ? strtoupper(trim($_POST['code'])) : '';
    $type = isset($_POST['type']) && $_POST['type'] === 'fixed' ? 'fixed' : 'percent';
    $value = isset($_POST['value']) ? (float) $_POST['value'] : 0;
    $min_order = isset($_POST['min_order']) ? (float) $_POST['min_order'] : 0;
    $max_discount = isset($_POST['max_discount']) ? (float) $_POST['max_discount'] : 0;
    $status = (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'inactive' : 'active';
    $valid_from = isset($_POST['valid_from']) && $_POST['valid_from'] !== '' ? $_POST['valid_from'] : null;
    $valid_to = isset($_POST['valid_to']) && $_POST['valid_to'] !== '' ? $_POST['valid_to'] : null;

    if ($code === '') {
        $errors[] = 'Mã voucher không được để trống.';
    }
    if ($value <= 0) {
        $errors[] = 'Giá trị giảm phải lớn hơn 0.';
    }
    if ($type === 'percent' && $value > 100) {
        $errors[] = 'Phần trăm giảm tối đa 100%.';
    }

    if (empty($errors)) {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE tbl_voucher 
                SET code = ?, type = ?, value = ?, min_order = ?, max_discount = ?, status = ?, valid_from = ?, valid_to = ?
                WHERE id = ?");
            $stmt->bind_param(
                "ssddssssi",
                $code,
                $type,
                $value,
                $min_order,
                $max_discount,
                $status,
                $valid_from,
                $valid_to,
                $id
            );
        } else {
            $stmt = $conn->prepare("INSERT INTO tbl_voucher (code, type, value, min_order, max_discount, status, valid_from, valid_to)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "ssddssss",
                $code,
                $type,
                $value,
                $min_order,
                $max_discount,
                $status,
                $valid_from,
                $valid_to
            );
        }

        if ($stmt && $stmt->execute()) {
            $success = $id > 0 ? 'Cập nhật voucher thành công.' : 'Thêm voucher mới thành công.';
        } else {
            $errors[] = 'Không thể lưu voucher. Vui lòng thử lại.';
        }
        if ($stmt) $stmt->close();
    }
}

$listSql = "SELECT id, code, type, value, min_order, max_discount, status, valid_from, valid_to, created_at
            FROM tbl_voucher
            ORDER BY created_at DESC";
$res = $conn->query($listSql);
$rows = [];
if ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) $rows[] = $r;
}

$editVoucher = null;
if (isset($_GET['edit']) && ctype_digit($_GET['edit'])) {
    $eid = (int) $_GET['edit'];
    foreach ($rows as $r) {
        if ((int)$r['id'] === $eid) {
            $editVoucher = $r;
            break;
        }
    }
}
?>

<main class="admin-main">
    <div class="admin-container">
        <div class="admin-page-header">
            <h1 class="admin-page-title"><i class="bi bi-ticket-perforated"></i> Quản lý voucher</h1>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="admin-alert admin-alert-danger">
                <?php foreach ($errors as $e): ?>
                    <div><?php echo htmlspecialchars($e); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="admin-alert admin-alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <h2 class="admin-card-title"><?php echo $editVoucher ? 'Sửa voucher' : 'Thêm voucher mới'; ?></h2>
            <form method="post" class="admin-form-grid">
                <input type="hidden" name="id" value="<?php echo $editVoucher ? (int)$editVoucher['id'] : 0; ?>">
                <div class="admin-form-group">
                    <label for="code">Mã voucher</label>
                    <input type="text" id="code" name="code" required
                           value="<?php echo htmlspecialchars($editVoucher['code'] ?? ''); ?>">
                </div>
                <div class="admin-form-group">
                    <label for="type">Loại giảm</label>
                    <select id="type" name="type">
                        <option value="percent" <?php echo (!isset($editVoucher['type']) || $editVoucher['type'] === 'percent') ? 'selected' : ''; ?>>Phần trăm (%)</option>
                        <option value="fixed" <?php echo (isset($editVoucher['type']) && $editVoucher['type'] === 'fixed') ? 'selected' : ''; ?>>Số tiền (đ)</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label for="value">Giá trị giảm</label>
                    <input type="number" step="0.01" min="0" id="value" name="value" required
                           value="<?php echo isset($editVoucher['value']) ? (float)$editVoucher['value'] : ''; ?>">
                </div>
                <div class="admin-form-group">
                    <label for="min_order">Đơn tối thiểu (đ)</label>
                    <input type="number" step="0.01" min="0" id="min_order" name="min_order"
                           value="<?php echo isset($editVoucher['min_order']) ? (float)$editVoucher['min_order'] : '0'; ?>">
                </div>
                <div class="admin-form-group">
                    <label for="max_discount">Giảm tối đa (đ)</label>
                    <input type="number" step="0.01" min="0" id="max_discount" name="max_discount"
                           value="<?php echo isset($editVoucher['max_discount']) ? (float)$editVoucher['max_discount'] : '0'; ?>">
                </div>
                <div class="admin-form-group">
                    <label for="status">Trạng thái</label>
                    <select id="status" name="status">
                        <option value="active" <?php echo (!isset($editVoucher['status']) || $editVoucher['status'] === 'active') ? 'selected' : ''; ?>>Đang hoạt động</option>
                        <option value="inactive" <?php echo (isset($editVoucher['status']) && $editVoucher['status'] === 'inactive') ? 'selected' : ''; ?>>Ngừng áp dụng</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label for="valid_from">Hiệu lực từ</label>
                    <input type="datetime-local" id="valid_from" name="valid_from"
                           value="<?php echo isset($editVoucher['valid_from']) && $editVoucher['valid_from'] ? date('Y-m-d\TH:i', strtotime($editVoucher['valid_from'])) : ''; ?>">
                </div>
                <div class="admin-form-group">
                    <label for="valid_to">Hiệu lực đến</label>
                    <input type="datetime-local" id="valid_to" name="valid_to"
                           value="<?php echo isset($editVoucher['valid_to']) && $editVoucher['valid_to'] ? date('Y-m-d\TH:i', strtotime($editVoucher['valid_to'])) : ''; ?>">
                </div>
                <div class="admin-form-actions">
                    <button type="submit" class="btn-primary"><?php echo $editVoucher ? 'Cập nhật' : 'Thêm mới'; ?></button>
                    <?php if ($editVoucher): ?>
                        <a href="manage-voucher.php" class="btn-secondary">Hủy sửa</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="admin-card" style="margin-top:24px;">
            <h2 class="admin-card-title">Danh sách voucher</h2>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mã</th>
                            <th>Loại</th>
                            <th>Giá trị</th>
                            <th>Đơn tối thiểu</th>
                            <th>Giảm tối đa</th>
                            <th>Hiệu lực</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="9" style="text-align:center;">Chưa có voucher nào.</td></tr>
                        <?php else: ?>
                            <?php foreach ($rows as $r):
                                $isPercent = $r['type'] === 'percent';
                                $valText = $isPercent ? (rtrim(rtrim((float)$r['value'], '0'), '.') . '%') : number_format((float)$r['value'], 0, ',', '.') . ' đ';
                                $minText = (float)$r['min_order'] > 0 ? number_format((float)$r['min_order'], 0, ',', '.') . ' đ' : '-';
                                $maxText = (float)$r['max_discount'] > 0 ? number_format((float)$r['max_discount'], 0, ',', '.') . ' đ' : '-';
                                $timeText = (!empty($r['valid_from']) || !empty($r['valid_to']))
                                    ? (($r['valid_from'] ? date('d/m/Y H:i', strtotime($r['valid_from'])) : '') . ($r['valid_to'] ? ' - ' . date('d/m/Y H:i', strtotime($r['valid_to'])) : ''))
                                    : 'Không giới hạn';
                            ?>
                            <tr>
                                <td><?php echo (int)$r['id']; ?></td>
                                <td><?php echo htmlspecialchars($r['code']); ?></td>
                                <td><?php echo $isPercent ? 'Phần trăm' : 'Số tiền'; ?></td>
                                <td><?php echo $valText; ?></td>
                                <td><?php echo $minText; ?></td>
                                <td><?php echo $maxText; ?></td>
                                <td><?php echo htmlspecialchars($timeText); ?></td>
                                <td>
                                    <span class="badge <?php echo $r['status'] === 'active' ? 'badge-success' : 'badge-secondary'; ?>">
                                        <?php echo $r['status'] === 'active' ? 'Đang hoạt động' : 'Ngừng áp dụng'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="manage-voucher.php?edit=<?php echo (int)$r['id']; ?>" class="btn-small">Sửa</a>
                                    <?php if ($r['status'] === 'active'): ?>
                                        <a href="manage-voucher.php?deactivate=<?php echo (int)$r['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Ngừng áp dụng voucher này?');">Ngừng</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<?php include('partials/footer.php'); ?>