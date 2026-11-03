<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// Current month/year
$month = date("m");
$year = date("Y");
$monthDisplay = date("F Y");

// FETCH ROOMS
$rooms = mysqli_query($conn, "
    SELECT r.id, r.room_number, r.monthly_rate,
           COUNT(t.id) AS tenant_count
    FROM rooms r
    LEFT JOIN tenants t 
        ON t.room_id = r.id AND t.status='active'
    GROUP BY r.id
    ORDER BY r.room_number
");

// TOTALS
$grand_total = 0;
$total_tenants = 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Monthly Report</title>
<link rel="icon" href="logo.png">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{display:flex;min-height:100vh;background: url('more-stay-hub-blue-and-white-bg.jpg') center/cover no-repeat;}

/* SIDEBAR */
.sidebar{
    width:230px;
    background:linear-gradient(180deg,#1e3c72,#2a5298);
    color:white;
    display:flex;
    flex-direction:column;
    padding:20px 10px;
}
.logo{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:30px;
    padding-left:10px;
}

.logo img{
    width:35px;
    height:35px;
    border-radius:50%;
    background: url('account-logo-as-staff.jpeg') center/cover no-repeat;
    <?php if($_SESSION['role']=='admin'){ ?> 
    background: url('account-logo-as-admin.jpeg') center/cover no-repeat;
    <?php } ?>
}
.menu li{
    list-style:none;
    padding:12px;
    margin:5px 0;
    border-radius:8px;
    cursor:pointer;
    display:flex;
    gap:10px;
}
.menu li:hover{background:rgba(255,255,255,0.2);}
.active{background:white;color:#2a5298;}

/* MAIN */
.main{flex:1;display:flex;flex-direction:column;}
.topbar{
    background:white;
    padding:15px 25px;
    display:flex;
    justify-content:space-between;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}

/* CONTENT */
.content{padding:30px;}

/* CARD */
.card{
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}
th, td{
    padding:12px;
    border-bottom:1px solid rgba(0,0,0,0.1);
    text-align:center;
}
th{background:#2a5298;color:white;}

/* SUMMARY */
.summary{
    margin-top:20px;
    text-align:center;
    font-size:18px;
    font-weight:bold;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">
        <img src="logo.png">
        <h3>StayHub</h3>
    </div>

    <ul class="menu">
        <li onclick="location.href='dashboard.php'"><i class="fa fa-home"></i> Dashboard</li>

        <?php if($_SESSION['role']=='admin'){ ?>
        <li onclick="location.href='user_management.php'"><i class="fa fa-users"></i> User Management</li>
        <li onclick="location.href='add_room.php'"><i class="fa fa-bed"></i> Add Room</li>
        <?php } ?>

        <li onclick="location.href='add_tenant.php'"><i class="fa fa-user-plus"></i> Add Tenant</li>
        <li onclick="location.href='tenant_clearance.php'"><i class="fa fa-file-signature"></i> Tenant Clearance</li>

        <?php if($_SESSION['role']=='admin'){ ?>
        <li onclick="location.href='utility_metering.php'"><i class="fa fa-bolt"></i> Utility Metering</li>
        <?php } ?>

        <li onclick="location.href='monthly_billing.php'"><i class="fa fa-receipt"></i> Billing</li>

        <?php if($_SESSION['role']=='admin'){ ?>
        <li class="active"><i class="fa fa-chart-line"></i> Report</li>
        <?php } ?>

        <li onclick="location.href='logout.php'"><i class="fa fa-sign-out-alt"></i> Logout</li>
    </ul>
</div>

<!-- MAIN -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <h3>Monthly Report</h3>
        <div>
            <?= $_SESSION['lastname'] . ', ' . $_SESSION['firstname'] . ' ' . strtoupper($_SESSION['middlename'][0]) . '. ' ?>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <div class="card">

            <h2 style="text-align:center;">Monthly Report - <?= $monthDisplay ?></h2>

            <table>
                <tr>
                    <th>#</th>
                    <th>Room</th>
                    <th>Tenants</th>
                    <th>Room Rate</th>
                    <th>Room Income</th>
                    <th>Electricity</th>
                    <th>Water</th>
                    <th>Total</th>
                </tr>

                <?php 
                $i = 1;
                while($row = mysqli_fetch_assoc($rooms)){

                    $room_id = $row['id'];
                    $tenant_count = $row['tenant_count'];
                    $room_rate = $row['monthly_rate'];

                    $room_income = $room_rate * $tenant_count;

                    // ✅ FIXED UTILITY (NO DUPLICATION)
                    $utility = mysqli_fetch_assoc(mysqli_query($conn, "
                        SELECT 
                            COALESCE(MAX(u.electricity),0) AS electricity,
                            COALESCE(MAX(u.water),0) AS water
                        FROM utility_metering u
                        JOIN tenants t ON u.tenant_id = t.id
                        WHERE t.room_id = $room_id 
                        AND u.month = $month 
                        AND u.year = $year
                    "));

                    $electricity = $utility['electricity'];
                    $water = $utility['water'];

                    $total = $room_income + $electricity + $water;

                    $grand_total += $total;
                    $total_tenants += $tenant_count;
                ?>

                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= $row['room_number'] ?></td>
                    <td><?= $tenant_count ?></td>
                    <td>₱<?= number_format($room_rate,2) ?></td>
                    <td>₱<?= number_format($room_income,2) ?></td>
                    <td>₱<?= number_format($electricity,2) ?></td>
                    <td>₱<?= number_format($water,2) ?></td>
                    <td><b>₱<?= number_format($total,2) ?></b></td>
                </tr>

                <?php } ?>
            </table>

            <div class="summary">
                Total Tenants: <?= $total_tenants ?> <br><br>
                Grand Total Income: ₱<?= number_format($grand_total,2) ?>
            </div>

        </div>
    </div>

</div>

</body>
</html>