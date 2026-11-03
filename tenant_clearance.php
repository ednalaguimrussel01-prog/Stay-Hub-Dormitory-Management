<?php
session_start();
include 'db.php';

// Only logged in users
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// CLEAR TENANT
if(isset($_GET['clear'])){
    $id = intval($_GET['clear']);
    $tenant = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tenants WHERE id=$id"));

    if($tenant && $tenant['status'] == 'active'){
        mysqli_query($conn, "UPDATE tenants SET status='cleared' WHERE id=$id");
        mysqli_query($conn, "UPDATE rooms SET occupied = occupied - 1 WHERE id={$tenant['room_id']}");
    }

    header("Location: tenant_clearance.php");
    exit;
}

// FETCH TENANTS
$tenants = mysqli_query($conn, "
    SELECT tenants.*, rooms.room_number 
    FROM tenants 
    JOIN rooms ON tenants.room_id = rooms.id
    ORDER BY tenants.status ASC, tenants.lastname ASC, tenants.firstname ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Tenant Clearance</title>
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
    min-height:100vh;
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

/* FORM CARD / TABLE CARD */
.card{
    max-width:900px;
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

/* TABLE */
table {
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th, td {
    padding:12px;
    border-bottom:1px solid rgba(0,0,0,0.1);
    text-align:center;
}

th {
    background:#2a5298;
    color:white
}

/* Buttons */
a.clear-btn {
    padding:6px 12px;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
    color:white;
    background:#2ecc71;
    transition:0.3s;
}

a.clear-btn:hover {
    background:#27ae60;
    transform: scale(1.05);
}

/* Status */
.active { color:#4caf50; font-weight:bold; }
.cleared { color:#ff4b5c; font-weight:bold; }




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
        <li onclick="location.href='add_room.php'"><i class="fa fa-bed"></i> Add Room</li>
        <li onclick="location.href='add_tenant.php'"><i class="fa fa-user-plus"></i> Add Tenant</li>
        <li class="active"><i class="fa fa-file-signature"></i> Tenant Clearance</li>
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
        <h3>Tenant Clearance</h3>
        <div>
            <?= $_SESSION['lastname'] . ', ' . $_SESSION['firstname'] . ' ' . strtoupper($_SESSION['middlename'][0]) . '. ' ?>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <div class="card">
            <h2><i class="fa fa-file-signature"></i> Tenant Clearance</h2>
            
            <table>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Room</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php $i=1; while($row = mysqli_fetch_assoc($tenants)) { ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td>
                        <?= htmlspecialchars($row['lastname'] . ', ' . $row['firstname'] . ' ' . strtoupper(substr($row['middlename'],0,1)) . '.') ?>
                    </td>
                    <td><?= htmlspecialchars($row['room_number']) ?></td>
                    <td class="<?= $row['status'] ?>"><?= $row['status'] ?></td>
                    <td>
                        <?php if($row['status'] == 'active'): ?>
                            <a class="clear-btn" href="?clear=<?= $row['id'] ?>"><i class="fa fa-check"></i> Clear</a>
                        <?php else: ?>
                            ✔ Cleared
                        <?php endif; ?>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</div>

</body>
</html>