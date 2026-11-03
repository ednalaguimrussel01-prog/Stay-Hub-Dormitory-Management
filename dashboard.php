<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// Total Rooms
$totalRooms = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM rooms"))['total'];

// Occupied Rooms
$occupiedRooms = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS occupied FROM rooms WHERE occupied > 0"))['occupied'];

// Total Active Tenants
$totalTenants = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS tenants FROM tenants WHERE status='active'"))['tenants'];

// Pending Tenants (for example: inactive or clearance pending)
$pendingTenants = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS pending FROM tenants WHERE status='inactive'"))['pending'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Stay-Hub Dashboard</title>
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

/* --- SIDEBAR --- */
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

.menu{
    list-style:none;
}

.menu li{
    padding:12px;
    margin:5px 0;
    border-radius:8px;
    cursor:pointer;
    transition:0.3s;
    display:flex;
    align-items:center;
    gap:10px;
}

.menu li:hover{
    background: rgba(255,255,255,0.2);
}

.menu li.active{
    background:white;
    color:#2a5298;
}

/* --- MAIN --- */
.main{
    flex:1;
    display:flex;
    flex-direction:column;
}

/* topbar */
.topbar{
    background:white;
    padding:15px 25px;
    display:flex;
    justify-content:space-between;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}

/* content */
.content{
    padding:25px;
}

/* dashboard cards */
.cards{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(200px,1fr));
    gap:20px;
}

.card{
    padding:20px;
    border-radius:10px;
    color:white;
    font-weight:bold;
    text-align:center;
}

.card h2{
    font-size:2em;
    margin-top:10px;
}

.blue{ background:#4A90E2; }
.green{ background:#27ae60; }
.orange{ background:#f39c12; }
.red{ background:#e74c3c; }
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
        <li class="active" onclick="showPage('Dashboard')"><i class="fa fa-home"></i> Dashboard</li>
        <li onclick="location.href='user_management.php'"><i class="fa fa-users"></i> User Management</li>
        <li onclick="location.href='add_room.php'"><i class="fa fa-bed"></i> Add Room</li>       
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
        <h3 id="title">Dashboard</h3>
        <div>
            <?= $_SESSION['lastname'] . ', ' . $_SESSION['firstname'] . ' ' . strtoupper($_SESSION['middlename'][0]) . '. ' ?>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <div id="dashboardPage">
            <div class="cards">
                <div class="card blue">Total Rooms<br><h2><?= $totalRooms ?></h2></div>
                <div class="card green">Occupied<br><h2><?= $occupiedRooms ?></h2></div>
                <div class="card orange">Tenants<br><h2><?= $totalTenants ?></h2></div>
                <div class="card red">Pending<br><h2><?= $pendingTenants ?></h2></div>
            </div>
        </div>
    </div>

</div>

<script>
function showPage(page){
    document.getElementById("title").innerText = page;

    // highlight active
    document.querySelectorAll(".menu li").forEach(li => li.classList.remove("active"));
    event.currentTarget.classList.add("active");

    // only dashboard content for now
    document.getElementById("dashboardPage").style.display = "block";
}
</script>

</body>
</html>