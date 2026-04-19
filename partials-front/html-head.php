<?php
/**
 * Khối <head> dùng chung cho các trang trong thư mục user/.
 * Trước khi include, đặt:
 *   $page_title — tiêu đề tab (bắt buộc khuyến nghị)
 *   $extra_stylesheets — mảng tùy chọn: chuỗi href hoặc mảng ['href'=>..., 'integrity'=>..., 'crossorigin'=>...]
 */
if (!isset($page_title)) {
    $page_title = 'Food Order System';
}
if (!isset($extra_stylesheets) || !is_array($extra_stylesheets)) {
    $extra_stylesheets = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="../css/style.css">
<?php foreach ($extra_stylesheets as $sheet) : ?>
<?php if (is_string($sheet)) : ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($sheet, ENT_QUOTES, 'UTF-8'); ?>">
<?php else : ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($sheet['href'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
<?php if (!empty($sheet['integrity'])) : ?> integrity="<?php echo htmlspecialchars($sheet['integrity'], ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>
<?php if (!empty($sheet['crossorigin'])) : ?> crossorigin="<?php echo htmlspecialchars($sheet['crossorigin'], ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>
<?php endif; ?>
<?php endforeach; ?>
</head>
