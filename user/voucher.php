<?php
include('../config/constants.php');

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = SITEURL . 'user/voucher.php';
    header('location:' . SITEURL . 'user/login.php');
    exit;
}

// Đảm bảo bảng voucher tồn tại (giống API voucher-apply)
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

$now = date('Y-m-d H:i:s');

$stmt = $conn->prepare("SELECT code, type, value, min_order, max_discount, valid_from, valid_to 
                        FROM tbl_voucher 
                        WHERE status = 'active'
                          AND (valid_from IS NULL OR valid_from <= ?)
                          AND (valid_to IS NULL OR valid_to >= ?)
                        ORDER BY created_at DESC");
$stmt->bind_param('ss', $now, $now);
$stmt->execute();
$result = $stmt->get_result();
$vouchers = [];
while ($row = $result->fetch_assoc()) {
    $vouchers[] = $row;
}
$stmt->close();

function formatMoney($n) {
    return number_format((float)$n, 0, ',', '.') . ' đ';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher của bạn - WowFood</title>
    <link rel="stylesheet" href="<?php echo SITEURL; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo SITEURL; ?>css/voucher.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<?php include('../partials-front/menu.php'); ?>

<section class="voucher-page">
    <div class="voucher-container">
        <div class="voucher-header">
            <div class="voucher-title">
                <span class="voucher-title-icon"><i class="bi bi-ticket-perforated"></i></span>
                <span>Voucher của bạn</span>
            </div>
        </div>
        <p class="voucher-desc">
            Thu thập mã giảm giá, nhấn <strong>Sao chép</strong> và dán vào ô <strong>Mã voucher</strong> ở bước thanh toán để áp dụng.
        </p>

        <?php if (empty($vouchers)): ?>
            <div class="voucher-empty">
                <div class="voucher-empty-icon"><i class="bi bi-ticket-perforated"></i></div>
                <h2>Chưa có voucher nào</h2>
<p>Hiện tại bạn chưa có mã giảm giá. Hãy theo dõi các chương trình khuyến mãi trên WowFood.</p>
                <a href="<?php echo SITEURL; ?>food.php"><i class="bi bi-egg-fried"></i> Xem thực đơn</a>
            </div>
        <?php else: ?>
            <div class="voucher-list">
                <?php foreach ($vouchers as $v):
                    $isPercent = $v['type'] === 'percent';
                    $valText = $isPercent ? (rtrim(rtrim((float)$v['value'], '0'), '.') . '%') : formatMoney($v['value']);
                    $minText = ((float)$v['min_order'] > 0) ? 'Đơn tối thiểu ' . formatMoney($v['min_order']) : 'Không yêu cầu đơn tối thiểu';
                    $maxText = ((float)$v['max_discount'] > 0) ? 'Giảm tối đa ' . formatMoney($v['max_discount']) : 'Không giới hạn giảm';
                    $timeText = '';
                    if (!empty($v['valid_from']) || !empty($v['valid_to'])) {
                        $from = $v['valid_from'] ? date('d/m/Y', strtotime($v['valid_from'])) : '';
                        $to = $v['valid_to'] ? date('d/m/Y', strtotime($v['valid_to'])) : '';
                        if ($from && $to) $timeText = 'Hiệu lực: ' . $from . ' - ' . $to;
                        elseif ($to) $timeText = 'Hạn dùng đến: ' . $to;
                    }
                ?>
                <div class="voucher-card">
                    <div class="voucher-badge">Đang áp dụng</div>
                    <div class="voucher-code-row">
                        <span class="voucher-code"><?php echo htmlspecialchars($v['code']); ?></span>
                        <button type="button" class="voucher-copy-btn" data-code="<?php echo htmlspecialchars($v['code']); ?>">
                            <i class="bi bi-clipboard"></i> Sao chép
                        </button>
                    </div>
                    <div class="voucher-main">
                        <strong><?php echo $valText; ?></strong>
                        <span style="font-size:0.85rem;color:#64748b;">giảm trên tổng tiền món ăn (không gồm phí ship)</span>
                    </div>
                    <div class="voucher-note">
                        <?php echo htmlspecialchars($minText . ' · ' . $maxText); ?>
                    </div>
                    <div class="voucher-meta">
                        <?php if ($timeText): ?>
                            <span><i class="bi bi-clock"></i> <?php echo htmlspecialchars($timeText); ?></span>
                        <?php else: ?>
                            <span><i class="bi bi-clock"></i> Không giới hạn thời gian</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include('../partials-front/footer.php'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var buttons = document.querySelectorAll('.voucher-copy-btn');
    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var code = this.getAttribute('data-code') || '';
            if (!code) return;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(function () {
                    alert('Đã sao chép mã: ' + code + '\nHãy dán vào ô Mã voucher ở bước thanh toán.');
                }).catch(function () {
                    alert('Không thể sao chép tự động. Bạn hãy ghi nhớ mã: ' + code);
                });
            } else {
                alert('Mã voucher: ' + code + '\nHãy sao chép thủ công và dán vào ô Mã voucher ở bước thanh toán.');
            }
        });
    });
});
</script>
</body>
</html>