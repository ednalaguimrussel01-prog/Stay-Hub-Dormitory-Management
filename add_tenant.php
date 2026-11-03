<?php
session_start();
include 'db.php';

// Only allow admin
if(!isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit;
}

$message = "";

// Handle Add Tenant
if(isset($_POST['add_tenant'])){
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $room_id = intval($_POST['room_id']);

    // Check room capacity
    $room = mysqli_fetch_assoc(mysqli_query($conn, "SELECT capacity, occupied FROM rooms WHERE id=$room_id"));
    if(!$room){
        $message = "Room not found!";
    } elseif($room['occupied'] >= $room['capacity']){
        $message = "Cannot add tenant. Room is full!";
    } else {
        
        /// 🔥 GET USER ACCOUNT FIRST (must be registered)
$user = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT id FROM users 
    WHERE firstname='$firstname'
    AND middlename='$middlename' 
    AND lastname='$lastname' 
    AND role='tenant'
"));

if(!$user){
    $message = "Tenant must register first!";
} else {

    $user_id = $user['id'];

    // Check if already added
    $checkTenant = mysqli_query($conn, "SELECT * FROM tenants WHERE user_id=$user_id");
    if(mysqli_num_rows($checkTenant) > 0){
        $message = "Tenant already exists!";
    } else {

        mysqli_query($conn, "INSERT INTO tenants 
        (user_id, firstname, middlename, lastname, room_id) 
        VALUES 
        ($user_id, '$firstname','$middlename','$lastname',$room_id)");

        mysqli_query($conn, "UPDATE rooms SET occupied = occupied + 1 WHERE id=$room_id");

        $message = "Tenant added successfully!";
    }
}
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Tenant</title>
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
input, select, button{
    width:100%;
    padding:12px;
    margin:10px 0;
    border-radius:8px;
    border:none;
}

input, select{
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
        <?php if($_SESSION['role']=='admin'){ ?>
        <li onclick="location.href='user_management.php'"><i class="fa fa-users"></i> User Management</li>
        <li onclick="location.href='add_room.php'"><i class="fa fa-bed"></i> Add Room</li>
        <?php } ?>
        <li class="active"><i class="fa fa-user-plus"></i> Add Tenant</li>
        <li onclick="location.href='tenant_clearance.php'"><i class="fa fa-file-signature"></i> Tenant Clearance</li>
        
        <li onclick="location.href='utility_metering.php'"><i class="fa fa-bolt"></i> Utility Metering</li>
        
        <li onclick="location.href='monthly_billing.php'"><i class="fa fa-receipt"></i> Billing</li>
        <?php if($_SESSION['role']=='admin'){ ?>
        <li onclick="location.href='report.php'"><i class="fa fa-chart-line"></i> Report</li>
        <?php } ?>
        <li onclick="location.href='logout.php'"><i class="fa fa-sign-out-alt"></i> Logout</li>
    </ul>
</div>

<!-- MAIN -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <h3>Add Tenant</h3>
        <div>
            <?= $_SESSION['lastname'] . ', ' . $_SESSION['firstname'] . ' ' . strtoupper($_SESSION['middlename'][0]) . '. ' ?>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <div class="form-container">
            <h2><i class="fa fa-user-plus"></i> Add New Tenant</h2>

            <?php if($message != ""): ?>
                <div class="message"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="firstname" placeholder="First Name" required>
                <input type="text" name="middlename" placeholder="Middle Name">
                <input type="text" name="lastname" placeholder="Last Name" required>

                <select name="room_id" required>
                    <option value="" disabled selected>Select Room</option>
                    <?php
                    $rooms = mysqli_query($conn, "SELECT * FROM rooms");
                    while($r = mysqli_fetch_assoc($rooms)){
                        echo "<option value='{$r['id']}'>Room {$r['room_number']} ({$r['occupied']}/{$r['capacity']})</option>";
                    }
                    ?>
                </select>

                <button type="submit" name="add_tenant">
                    <i class="fa fa-plus"></i> Add Tenant
                </button>
            </form>
        </div>
    </div>

</div>

</body>
</html>