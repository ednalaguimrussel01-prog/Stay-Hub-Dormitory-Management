<?php
session_start();
include 'db.php';

// Only allow admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: dashboard.php");
    exit;
}

// Reset login attempts
if(isset($_GET['reset'])){
    $id = intval($_GET['reset']);
    mysqli_query($conn, "UPDATE users SET attempts=0 WHERE id=$id");
    header("Location: user_management.php");
    exit;
}

// Activate/Deactivate account
if(isset($_GET['toggle'])){
    $id = intval($_GET['toggle']);
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM users WHERE id=$id"));

    if($user){
        $new = ($user['status']=='active') ? 'inactive' : 'active';
        mysqli_query($conn, "UPDATE users SET status='$new' WHERE id=$id");
    }

    header("Location: user_management.php");
    exit;
}

// Fetch users
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY role DESC, firstname ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>User Management</title>
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
    transition:0.3s;
    display:flex;
    align-items:center;
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
    padding:20px;
}

/* TABLE */
.table-container{
    background:white;
    border-radius:10px;
    padding:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

h2{
    margin-bottom:15px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th, td{
    padding:12px;
    text-align:center;
}

th{
    background:#2a5298;
    color:white;
}

tr:nth-child(even){
    background:#f2f2f2;
}

/* STATUS */
.status-active{ color:#2ecc71; font-weight:bold; }
.status-inactive{ color:#e74c3c; font-weight:bold; }

/* BUTTONS */
.btn{
    padding:6px 10px;
    border:none;
    border-radius:6px;
    color:white;
    text-decoration:none;
    font-size:13px;
}

.reset{ background:#27ae60; }
.toggle{ background:#e74c3c; }
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
        <li class="active"><i class="fa fa-users"></i> User Management</li>
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

    <div class="topbar">
        <h3>User Management</h3>
        <div>
            <?= $_SESSION['lastname'] . ', ' . $_SESSION['firstname'] . ' ' . strtoupper($_SESSION['middlename'][0]) . '. ' ?>
        </div>
    </div>

    <div class="content">
        <div class="table-container">

            <h2><i class="fa fa-users"></i> User List</h2>

            <table>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Attempts</th>
                    <th>Actions</th>
                </tr>

                <?php $i=1; while($row = mysqli_fetch_assoc($users)) { ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= $row['lastname'].', '.$row['firstname'].' '.strtoupper($row['middlename'][0]).'.' ?></td>
                    <td><?= $row['username'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['role'] ?></td>

                    <td>
                        <span class="<?= $row['status']=='active'?'status-active':'status-inactive' ?>">
                            <?= $row['status'] ?>
                        </span>
                    </td>

                    <td><?= $row['attempts'] ?></td>

                    <td>
                        <a class="btn reset" href="?reset=<?= $row['id'] ?>">Reset</a>
                        <a class="btn toggle" href="?toggle=<?= $row['id'] ?>">
                            <?= $row['status']=='active'?'Deactivate':'Activate' ?>
                        </a>
                    </td>
                </tr>
                <?php } ?>

            </table>

        </div>
    </div>

</div>

</body>
</html>