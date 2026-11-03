<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$currentMonth = date("m");
$currentYear = date("Y");
$monthDisplay = date("F Y");

$message = "";

/* =========================
   GENERATE BILL
========================= */
if(isset($_POST['generate_bill'])){
    $tenant_id = intval($_POST['tenant_id']);
    $room_id = intval($_POST['room_id']);
    $amount = floatval($_POST['amount']);
    $month = date("Y-m");

    $check = mysqli_query($conn, "SELECT * FROM bills WHERE tenant_id=$tenant_id AND month='$month'");

    if(mysqli_num_rows($check) == 0){
        mysqli_query($conn, "
            INSERT INTO bills (tenant_id, room_id, month, amount, status)
            VALUES ($tenant_id, $room_id, '$month', $amount, 'unpaid')
        ");
        $message = "Bill generated!";
    } else {
        $message = "Already generated!";
    }
}

/* =========================
   MARK AS PAID
========================= */
if(isset($_POST['mark_paid'])){
    $bill_id = intval($_POST['bill_id']);

    mysqli_query($conn, "
        UPDATE bills 
        SET status='paid' 
        WHERE id=$bill_id
    ");

    $message = "Bill marked as PAID!";
}

/* =========================
   GET TENANTS + ROOM DATA
========================= */
$tenants = mysqli_query($conn, "
    SELECT 
        t.id AS tenant_id,
        t.firstname,
        t.lastname,
        t.middlename,
        r.id AS room_id,
        r.room_number,
        r.monthly_rate,
        (SELECT COUNT(*) FROM tenants WHERE room_id=r.id AND status='active') AS tenant_count,
        (SELECT MAX(u.electricity)
         FROM utility_metering u
         JOIN tenants tt ON u.tenant_id=tt.id
         WHERE tt.room_id=r.id AND u.month=$currentMonth AND u.year=$currentYear) AS electricity,
        (SELECT MAX(u.water)
         FROM utility_metering u
         JOIN tenants tt ON u.tenant_id=tt.id
         WHERE tt.room_id=r.id AND u.month=$currentMonth AND u.year=$currentYear) AS water
    FROM tenants t
    JOIN rooms r ON t.room_id = r.id
    WHERE t.status='active'
    ORDER BY r.room_number, t.lastname
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Monthly Billing</title>
<link rel="icon" href="logo.png">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI', sans-serif;}
body{display:flex; min-height:100vh; background: url('more-stay-hub-blue-and-white-bg.jpg') center/cover no-repeat;}

/* SIDEBAR */
.sidebar{
    width:230px;
    background: linear-gradient(180deg, #1e3c72, #2a5298);
    color:white;
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
}
.menu li{list-style:none;padding:12px;margin:5px 0;border-radius:8px;cursor:pointer;display:flex;gap:10px;}
.menu li:hover{background:rgba(255,255,255,0.2);}
.active{background:white;color:#2a5298;}

/* MAIN */
.main{flex:1;display:flex;flex-direction:column;}
.topbar{background:white;padding:15px 25px;display:flex;justify-content:space-between;box-shadow:0 2px 5px rgba(0,0,0,0.1);}
.content{padding:30px;}

/* CARD */
.card{
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.2);
}

/* TABLE */
table{width:100%;border-collapse:collapse;margin-top:20px;}
th, td{padding:12px;border-bottom:1px solid rgba(0,0,0,0.1);text-align:center;}
th{background:#2a5298;color:white;}

button{
    padding:6px 12px;
    border-radius:8px;
    border:none;
    background:#4caf50;
    color:white;
    cursor:pointer;
}
button:hover{background:#27ae60;}

.message{text-align:center;font-weight:bold;margin-bottom:10px;color:#333;}

.paid{color:green;font-weight:bold;}
.unpaid{color:red;font-weight:bold;}
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
        <li onclick="location.href='utility_metering.php'"><i class="fa fa-bolt"></i> Utility Metering</li>
        <li class="active"><i class="fa fa-receipt"></i> Billing</li>
        <?php if($_SESSION['role']=='admin'){ ?>
        <li onclick="location.href='report.php'"><i class="fa fa-chart-line"></i> Report</li>
        <?php } ?>
        <li onclick="location.href='logout.php'"><i class="fa fa-sign-out-alt"></i> Logout</li>
    </ul>
</div>

<!-- MAIN -->
<div class="main">

<div class="topbar">
    <h3>Monthly Billing</h3>
    <div>
        <?= $_SESSION['lastname'] . ', ' . $_SESSION['firstname'] . ' ' . strtoupper($_SESSION['middlename'][0]) . '. ' ?>
    </div>
</div>

<div class="content">

<div class="card">

<h2 style="text-align:center;">
    Monthly Billing Per Tenant - <?= $monthDisplay ?>
</h2>

<?php if($message != ""): ?>
    <div class="message"><?= $message ?></div>
<?php endif; ?>

<table>
<tr>
    <th>#</th>
    <th>Tenant</th>
    <th>Room</th>
    <th>Room Rate</th>
    <th>Electricity</th>
    <th>Water</th>
    <th>Total</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php 
$i=1; 
while($row = mysqli_fetch_assoc($tenants)) {

    $tenant_count = ($row['tenant_count'] > 0) ? $row['tenant_count'] : 1;

    $electricity = $row['electricity'] ?? 0;
    $water = $row['water'] ?? 0;

    $elec_per = $electricity / $tenant_count;
    $water_per = $water / $tenant_count;

    $total = $row['monthly_rate'] + $elec_per + $water_per;

    $fullname = $row['lastname'] . ", " . $row['firstname'] . " " . strtoupper($row['middlename'][0]) . ".";

    $bill = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT * FROM bills 
        WHERE tenant_id={$row['tenant_id']} 
        AND month='".date("Y-m")."'
    "));
?>

<tr>
    <td><?= $i++ ?></td>
    <td><?= $fullname ?></td>
    <td><?= $row['room_number'] ?></td>
    <td>₱<?= number_format($row['monthly_rate'],2) ?></td>
    <td>₱<?= number_format($elec_per,2) ?></td>
    <td>₱<?= number_format($water_per,2) ?></td>
    <td><b>₱<?= number_format($total,2) ?></b></td>

    <!-- STATUS -->
    <td>
        <?php if($bill): ?>
            <?php if($bill['status'] == 'paid'): ?>
                <span class="paid">PAID ✔</span>
            <?php else: ?>
                <span class="unpaid">UNPAID ❌</span>
            <?php endif; ?>
        <?php else: ?>
            NO BILL
        <?php endif; ?>
    </td>

    <!-- ACTION -->
    <td>
        <?php if(!$bill): ?>
            <form method="POST">
                <input type="hidden" name="tenant_id" value="<?= $row['tenant_id'] ?>">
                <input type="hidden" name="room_id" value="<?= $row['room_id'] ?>">
                <input type="hidden" name="amount" value="<?= $total ?>">
                <button type="submit" name="generate_bill">Generate</button>
            </form>
        <?php else: ?>
            <?php if($bill['status'] != 'paid'): ?>
                <form method="POST">
                    <input type="hidden" name="bill_id" value="<?= $bill['id'] ?>">
                    <button type="submit" name="mark_paid" style="background:#2196f3;">
                        Mark as Paid
                    </button>
                </form>
            <?php else: ?>
                ✔ Done
            <?php endif; ?>
        <?php endif; ?>
    </td>

</tr>

<?php } ?>

</table>

</div>
</div>
</div>

</body>
</html>