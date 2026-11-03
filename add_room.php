<?php
session_start();
include 'db.php';

// Only allow admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: dashboard.php");
    exit;
}

$message = "";

// Handle Add Room
if(isset($_POST['add_room'])){
    $room_number = mysqli_real_escape_string($conn, $_POST['room_number']);
    $capacity = intval($_POST['capacity']);

    $check = mysqli_query($conn, "SELECT * FROM rooms WHERE room_number='$room_number'");
    if(mysqli_num_rows($check) > 0){
        $message = "Room already exists!";
    } elseif($capacity < 1){
        $message = "Capacity must be at least 1!";
    } else {
        mysqli_query($conn, "INSERT INTO rooms (room_number, capacity, occupied) VALUES ('$room_number', $capacity, 0)");
        $message = "Room added successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Room</title>
<link rel="icon" href="logo.png">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', sans-serif;
}

body{
    display:flex;
    height:100vh;
    background: url('more-stay-hub-blue-and-white-bg.jpg') center/cover no-repeat;
}

/* SIDEBAR */
.sidebar{
    width:230px;
    background: linear-gradient(180deg, #1e3c72, #2a5298);
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

.menu li:hover{
    background:rgba(255,255,255,0.2);
}

.active{
    background:white;
    color:#2a5298;
}

/* MAIN */
.main{
    flex:1;
    display:flex;
    flex-direction:column;
}

.topbar{
    background:white;
    padding:15px 25px;
    display:flex;
    justify-content:space-between;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}

/* CONTENT */
.content{
    padding:30px;
}

/* FORM CARD */
.form-container{
    max-width:600px;
    background:white;
    padding:60px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

h2{
    margin-bottom:15px;
}

/* INPUTS */
input, button{
    width:100%;
    padding:12px;
    margin:10px 0;
    border-radius:8px;
    border:none;
}

input{
    background:#f2f2f2;
}

button{
    background:#2ecc71;
    color:white;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    background:#27ae60;
}

/* MESSAGE */
.message{
    text-align:center;
    font-weight:bold;
    margin-bottom:10px;
    color:#333;
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
        <li onclick="location.href='user_management.php'"><i class="fa fa-users"></i> User Management</li>
        <li class="active"><i class="fa fa-bed"></i> Add Room</li>
        <li onclick="location.href='add_tenant.php'"><i class="fa fa-user-plus"></i> Add Tenant</li>
        <li onclick="location.href='tenant_clearance.php'"><i class="fa fa-file-signature"></i> Tenant Clearance</li>
        <li onclick="location.href='utility_metering.php'"><i class="fa fa-bolt"></i> Utility Metering</li>
        <li onclick="location.href='monthly_billing.php'"><i class="fa fa-receipt"></i> Billing</li>
        <li onclick="location.href='report.php'"><i class="fa fa-chart-line"></i> Report</li>
        <li onclick="location.href='logout.php'"><i class="fa fa-sign-out-alt"></i> Logout</li>
    </ul>
</div>

<!-- MAIN -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <h3>Add Room</h3>
        <div>
            <?= $_SESSION['lastname'] . ', ' . $_SESSION['firstname'] . ' ' . strtoupper($_SESSION['middlename'][0]) . '. ' ?>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <div class="form-container">
            <h2><i class="fa fa-bed"></i> Add New Room</h2>

            <?php if($message != ""): ?>
                <div class="message"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="room_number" placeholder="Room Name" required>
                <input type="number" name="capacity" placeholder="Capacity" required min="1">
                <button type="submit" name="add_room">
                    <i class="fa fa-plus"></i> Add Room
                </button>
            </form>
        </div>
    </div>

</div>

</body>
</html>