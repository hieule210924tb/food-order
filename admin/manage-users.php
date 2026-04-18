<?php
require_once('../config/constants.php');
require_once('partials/login-check.php');

$keyword = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? 'all';

$conditions = [];
$params = [];
$types = '';

if ($keyword !== '') {
    $conditions[] = '(full_name LIKE ? OR username LIKE ? OR email LIKE ? OR phone LIKE ?)';
    $like = '%' . $keyword . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'ssss';
}

if ($status === 'active') {
    $conditions[] = 'status = ?';
    $params[] = 'Active';
    $types .= 's';
} elseif ($status === 'inactive') {
    $conditions[] = 'status = ?';
    $params[] = 'Inactive';
    $types .= 's';
}

$sql = 'SELECT id, full_name, username, email, phone, address, status, created_at FROM tbl_user';
if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY id DESC';

$users = [];
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($result && ($row = mysqli_fetch_assoc($result))) {
        $users[] = $row;
    }
    mysqli_stmt_close($stmt);
}

include('partials/menu.php');
?>
<div class="main-content">
    <div class="wrapper">
        <div class="user-page">
            <div class="page-header">
                <div>
                    <h1>Quản lý người dùng</h1>
                    <p>Danh sách người dùng đã đăng ký trong hệ thống.</p>
                </div>
            </div>

            <div class="user-panel">
                <div class="panel-head">
                    <h2>Danh sách người dùng</h2>
                </div>

                <form method="get" class="filter-bar">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Tìm theo tên, email, username, số điện thoại">
                    <select name="status">
                        <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Tất cả trạng thái</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                        <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Ngừng hoạt động</option>
                    </select>
                    <button type="submit">Lọc</button>
                </form>

                <div class="table-card">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Người dùng</th>
                                <th>Liên hệ</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Địa chỉ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$users) { ?>
                                <tr>
                                    <td colspan="6" class="empty-row">Chưa có người dùng phù hợp.</td>
                                </tr>
                            <?php } else { ?>
                                <?php $sn = 1; ?>
                                <?php foreach ($users as $user) { ?>
                                    <tr>
                                        <td><?php echo $sn++; ?></td>
                                        <td>
                                            <div class="cell-title">
                                                <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                                <span><?php echo htmlspecialchars($user['username']); ?> · ID: <?php echo (int)$user['id']; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="cell-title">
                                                <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                                                <span><?php echo htmlspecialchars($user['phone'] ?? ''); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status <?php echo ($user['status'] === 'Active') ? 'yes' : 'no-danger'; ?>">
                                                <?php echo htmlspecialchars($user['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                        <td><?php echo htmlspecialchars($user['address'] ?? ''); ?></td>
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
.user-page{display:grid;gap:20px}
.page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
.page-header h1{margin:0 0 10px;font-size:32px;color:#2d3436}
.page-header p{margin:0;color:#747d8c}
.user-panel{background:#fff;border-radius:12px;padding:18px 20px;box-shadow:0 4px 14px rgba(0,0,0,.06);border:1px solid #ecf0f1}
.panel-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:16px}
.panel-head h2{margin:0;font-size:20px;color:#2d3436}
.filter-bar{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px}
.filter-bar input,.filter-bar select{padding:10px 12px;border:1px solid #dfe4ea;border-radius:10px;font-size:14px;background:#fff}
.filter-bar input{min-width:260px;flex:1}
.filter-bar button{padding:10px 16px;border:none;border-radius:10px;background:#2f3542;color:#fff;cursor:pointer}
.table-card{overflow-x:auto}
.user-table{width:100%;border-collapse:separate;border-spacing:0;font-size:14px;min-width:980px}
.user-table thead tr{background:#f8f9fb}
.user-table th{padding:12px 10px;border-bottom:1px solid #e0e6ed;text-align:left;color:#57606f}
.user-table td{padding:10px 8px;border-bottom:1px solid #f0f2f5;vertical-align:middle;color:#2f3542}
.cell-title{display:flex;flex-direction:column;gap:4px}
.cell-title strong{font-size:14px}
.cell-title span{font-size:12px;color:#747d8c}
.status{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600}
.status.yes{background:#edfdf3;color:#1e7e34}
.status.no-danger{background:#fff5f5;color:#c92a2a}
.empty-row{text-align:center;color:#747d8c}
@media (max-width: 768px){
    .page-header{flex-direction:column}
    .filter-bar input{min-width:100%}
}
</style>

<?php include('partials/footer.php'); ?>
