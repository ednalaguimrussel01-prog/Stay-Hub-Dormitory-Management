<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'tenant'){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 🔥 GET TENANT INFO USING user_id
$tenant = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT t.*, r.room_number 
    FROM tenants t
    LEFT JOIN rooms r ON t.room_id = r.id
    WHERE t.user_id = $user_id
"));

if(!$tenant){
    echo "You are not yet assigned to a room. Contact admin.";
    exit;
}

$tenant_id = $tenant['id'];

// 🔥 GET BILLS
$bills = mysqli_query($conn, "
    SELECT * FROM bills 
    WHERE tenant_id = $tenant_id
    ORDER BY month DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Tenant Dashboard</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
body{font-family:Segoe UI;background:#f5f7fa;margin:0;padding:20px;}
.card{background:white;padding:20px;border-radius:10px;margin-bottom:20px;box-shadow:0 5px 10px rgba(0,0,0,0.1);}
table{width:100%;border-collapse:collapse;}
th,td{padding:10px;border-bottom:1px solid #ddd;text-align:center;}
th{background:#2a5298;color:white;}
</style>
</head>

<body>

<h2>Welcome, <?= $_SESSION['lastname'] . ', ' . $_SESSION['firstname'] . ' ' . strtoupper($_SESSION['middlename'][0]) . '. ' ?>!</h2>

<div class="card">
    <h3><i class="fa fa-bed"></i> Room Info</h3>
    <p><b>Room:</b> <?= $tenant['room_number'] ?></p>
    <p><b>Status:</b> <?= $tenant['status'] ?></p>
</div>

<div class="card">
    <h3><i class="fa fa-receipt"></i> Your Bills</h3>

    <table>
        <tr>
            <th>Month</th>
            <th>Amount</th>
            <th>Status</th>
        </tr>

        <?php while($b = mysqli_fetch_assoc($bills)){ ?>
        <tr>
            <td><?= $b['month'] ?></td>
            <td>₱<?= number_format($b['amount'],2) ?></td>
            <td><?= $b['status'] ?></td>
        </tr>
        <?php } ?>

    </table>
</div>

<a href="logout.php">Logout</a>

</body>
</html>