<?php
include('../config/constants.php');
require('../src/dompdf/vendor/autoload.php');
use Dompdf\Dompdf;
$dompdf = new Dompdf();
$html = '
<meta charset="UTF-8">
<style>
    body{font-family: DejaVu Sans;}
    table{border-collapse:collapse;}
    tr th {text-align: center;}
    tr td {text-align: center;}
</style>

<h2 style="text-align: center;">Đơn hàng đã bán</h2>

<table border="1" width="100%" cellpadding="5">
<tr>
    <th>ID</th>
    <th>Món ăn</th>
    <th>Tổng tiền</th>
</tr>
';

$counter = 1;
$tong_doanhthu = 0;

$sql = "
SELECT o.order_details, p.amount
FROM tbl_order o
JOIN tbl_payment p 
    ON o.order_code = p.order_code
WHERE 
    p.payment_status = 'success'
    AND o.status = 'Ordered'
";

$res = mysqli_query($conn,$sql);

while($row = mysqli_fetch_assoc($res)) {

    $details = json_decode($row['order_details'], true);

    if (!$details) continue;

    // gom tên món trong 1 đơn
    $tenmon = [];
    foreach ($details as $item) {
        $tenmon[] = trim($item['title']);
    }

    $tenmon_str = implode(", ", $tenmon);

    $amount = $row['amount'];
    $tong_doanhthu += $amount;

    $html .= "<tr>
        <td>".$counter."</td>
        <td>".$tenmon_str."</td>
        <td>".number_format($amount,0,',','.')."</td>
    </tr>";

    $counter++;
}

$html .= "
<tr>
    <td colspan='2'><b>Tổng doanh thu</b></td>
    <td><b>".number_format($tong_doanhthu,0,',','.')."</b></td>
</tr>
</table>
";
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$dompdf->stream("donhang.pdf",["Attachment"=>0]);
exit();
?>