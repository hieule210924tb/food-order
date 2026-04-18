<?php include("partials/menu.php"); ?>
<?php
    $day = "
    SELECT 
        DAYOFWEEK(p.updated_at) AS d, 
        SUM(o.total) AS totals
    FROM tbl_order o
    JOIN tbl_payment p 
        ON o.order_code = p.order_code
    WHERE 
        p.payment_status = 'success'
        AND o.status = 'Ordered'
        AND YEARWEEK(p.updated_at, 1) = YEARWEEK(CURDATE(), 1)
    GROUP BY d
    ";
    $day_res = mysqli_query($conn,$day);
    $days=[0,0,0,0,0,0,0];
    while($day_row=mysqli_fetch_assoc($day_res)){
        if($day_row['d']==2)$days[0]=$day_row['totals'];
        if($day_row['d']==3)$days[1]=$day_row['totals'];
        if($day_row['d']==4)$days[2]=$day_row['totals'];
        if($day_row['d']==5)$days[3]=$day_row['totals'];
        if($day_row['d']==6)$days[4]=$day_row['totals'];
        if($day_row['d']==7)$days[5]=$day_row['totals'];
        if($day_row['d']==1)$days[6]=$day_row['totals'];
    }
    $monday = strtotime("monday this week");
    for($i=0;$i<7;$i++){
        $labels_week[] = date("d/m", strtotime("+$i day", $monday));
    }
    /* DOANH THU THEO THÁNG */
    $month = "
    SELECT 
        MONTH(p.updated_at) AS m,
        SUM(o.total) AS totals
    FROM tbl_order o
    JOIN tbl_payment p 
        ON o.order_code = p.order_code
    WHERE 
        p.payment_status = 'success'
        AND o.status = 'Ordered'
        AND YEAR(p.updated_at) = YEAR(CURDATE())
    GROUP BY m
    ";

    $month_res=mysqli_query($conn,$month);
    $months=array_fill(0,12,0);

    while($month_row=mysqli_fetch_assoc($month_res)){
        $months[$month_row['m']-1]=$month_row['totals'];
    }
    $sql = "
    SELECT o.order_details
    FROM tbl_order o
    JOIN tbl_payment p 
        ON o.order_code = p.order_code
    WHERE p.payment_status = 'success'
    ";

    $res = mysqli_query($conn, $sql);

    $food_count = [];

    while($row = mysqli_fetch_assoc($res)) {

        // chuyển JSON thành mảng
        $details = json_decode($row['order_details'], true);

        if (!$details) continue;

        foreach ($details as $item) {
            $name = trim($item['title']); // tên món
            $qty  = $item['qty'];         // số lượng

            if (!isset($food_count[$name])) {
                $food_count[$name] = 0;
            }

            $food_count[$name] += $qty;
        }
    }

    // sắp xếp giảm dần
    arsort($food_count);

    // lấy top 5
    $top5 = array_slice($food_count, 0, 5, true);

    // tách ra để vẽ chart
    $labels = array_keys($top5);
    $data   = array_values($top5);
?>
<!-- main content section starts-->
<div class="main-content">
    <div class="wrapper">
        <h1>BẢNG ĐIỀU KHIỂN</h1>

        <div class="col-4 text-center">

            <?php

            $sql = "SELECT * FROM tbl_category";

            $res = mysqli_query($conn, $sql);

            $count = mysqli_num_rows($res);
            ?>

            <h1><?php echo $count; ?></h1>
            <br />
            Danh mục
        </div>

        <div class="col-4 text-center">

            <?php
            $sql2 = "SELECT * FROM tbl_food";

            $res2 = mysqli_query($conn, $sql2);

            $count2 = mysqli_num_rows($res2);
            ?>
            <h1><?php echo $count2; ?></h1>
            <br />
            Món ăn
            
        </div>

        <div class="col-4 text-center">
            <?php

            $sql3 = "SELECT * FROM tbl_order";

            $res3 = mysqli_query($conn, $sql3);

            $count3 = mysqli_num_rows($res3);
            ?>
            <h1><?php echo $count3; ?></h1>
            <br />
            Tổng đơn hàng
        </div>

        <div class="col-4 text-center">
            <?php

            $sql4 = "SELECT SUM(total) AS total FROM tbl_order WHERE status='Delivered'";
            $res4 = mysqli_query($conn, $sql4);
            $row4 = mysqli_fetch_assoc($res4);
            $total_revenue = $row4 && $row4['total'] !== null ? (float)$row4['total'] : 0;

            ?>
            <h1><?php echo number_format($total_revenue, 0, ',', '.'); ?> </h1>USD
            <br />
            Doanh thu
        </div>
        <div class="clearfix"></div>    
    </div>
    <div class="grid">
        <div class="card">
            <div class="card-header">
                <div>DOANH THU <?php echo date("Y"); ?></div>
                <form action="export_pdf.php" method="post" >
                        <button type="submit" style="margin :0px 0px 0px 50px"  class="btn-pdf">Xuất PDF</button>
                </form>
                <span>
                    <select id="filter">
                        <option value="week">Tuần này</option>
                        <option value="all">Toàn thời gian</option>
                    </select>
                </span>
            </div>
            <div class="chart-wrap"><canvas id="barChart"></canvas></div>
        </div>
        <div class="card">
            <div class="card-header">
                <div>ĐỒ ĂN BÁN CHẠY</div>
            </div>
            <div class="chart-wrap"><canvas id="pieChart"></canvas></div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = {
        week: <?php echo json_encode($labels_week); ?>,
        all: ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'],
        pie : <?php echo json_encode($labels); ?>
    };
    const values = {
        week: <?php echo json_encode($days); ?>,
        all: <?php echo json_encode($months); ?>,
        pie : <?php echo json_encode($data); ?>
    };
    const barChart = new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: labels.week,
            datasets: [{ data: values.week, backgroundColor: '#36a2eb' }]
        },
        options: { plugins: { legend: { display: false } }, maintainAspectRatio: false }
    });
    document.getElementById('filter').onchange = function() {
        const val = this.value;
        barChart.data.labels = labels[val];
        barChart.data.datasets[0].data = values[val];
        barChart.update();
    };
    new Chart(document.getElementById('pieChart'), {
        type: 'doughnut',
        data: {
            labels: labels.pie,
            datasets: [{
                data: values.pie,
                backgroundColor: ['#ff6384','#36a2eb','#ffce56','#4bc0c0','#9966ff'],
                borderWidth: 2
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>
<?php include("partials/footer.php"); ?> 