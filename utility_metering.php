<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$message = "";

// CURRENT MONTH/YEAR
$month = date("n");
$year = date("Y");

// HANDLE ADD OR UPDATE UTILITY
if(isset($_POST['save_utility'])){
    $room_id = intval($_POST['room_id']);
    $electricity = number_format(floatval($_POST['electricity']), 2, '.', '');
    $water = number_format(floatval($_POST['water']), 2, '.', '');

    // Fetch all active tenants in the room
    $tenants = mysqli_query($conn, "SELECT id FROM tenants WHERE room_id=$room_id AND status='active'");
    if(mysqli_num_rows($tenants) == 0){
        $message = "No active tenants in this room!";
    } else {
        while($t = mysqli_fetch_assoc($tenants)){
            // Insert or update utility
            $check = mysqli_query($conn, "
                SELECT * FROM utility_metering 
                WHERE tenant_id={$t['id']} AND month=$month AND year=$year
            ");
            if(mysqli_num_rows($check) > 0){
                mysqli_query($conn, "
                    UPDATE utility_metering 
                    SET electricity=$electricity, water=$water
                    WHERE tenant_id={$t['id']} AND month=$month AND year=$year
                ");
            } else {
                mysqli_query($conn, "
                    INSERT INTO utility_metering (tenant_id, electricity, water, month, year)
                    VALUES ({$t['id']}, $electricity, $water, $month, $year)
                ");
            }

            
        }

        $message = "Utilities saved successfully and billing updated!";
    }
}

// FETCH ROOMS AND THEIR UTILITY
$rooms = mysqli_query($conn, "
    SELECT r.id, r.room_number, 
           COALESCE(MAX(u.electricity),0) AS electricity,
           COALESCE(MAX(u.water),0) AS water
    FROM rooms r
    LEFT JOIN tenants t ON t.room_id = r.id AND t.status='active'
    LEFT JOIN utility_metering u 
        ON u.tenant_id = t.id 
        AND u.month=$month 
        AND u.year=$year
    GROUP BY r.id, r.room_number
    ORDER BY r.room_number
");

// FETCH ROOM TO EDIT IF REQUESTED
$edit_room_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$edit_room = null;
if($edit_room_id){
    $res = mysqli_query($conn, "
        SELECT r.id, r.room_number, 
               COALESCE(MAX(u.electricity),0) AS electricity,
               COALESCE(u.water,0) AS water
        FROM rooms r
        LEFT JOIN tenants t ON t.room_id = r.id AND t.status='active'
        LEFT JOIN utility_metering u ON u.tenant_id = t.id AND u.month=$month AND u.year=$year
        WHERE r.id=$edit_room_id
        GROUP BY r.id
    ");
    $edit_room = mysqli_fetch_assoc($res);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Utility Metering</title>
<link rel="icon" href="logo.png">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{display:flex;min-height:100vh;background: url('more-stay-hub-blue-and-white-bg.jpg') center/cover no-repeat;}
.sidebar{width:230px;background:linear-gradient(180deg,#1e3c72,#2a5298);color:white;padding:20px 10px;}
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
.menu li{list-style:none;padding:12px;margin:5px 0;border-radius:8px;cursor:pointer;display:flex;gap:10px;}
.menu li:hover{background:rgba(255,255,255,0.2);}
.active{background:white;color:#2a5298;}
.main{flex:1;display:flex;flex-direction:column;}
.topbar{background:white;padding:15px 25px;display:flex;justify-content:space-between;box-shadow:0 2px 5px rgba(0,0,0,0.1);}
.content{padding:30px;display:flex;gap:20px;flex-wrap:wrap;}

/* Split left and right */
.utility-form{flex:1;min-width:300px;background:white;padding:25px;border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.1);}
.utility-table{flex:2;min-width:400px;background:white;padding:25px;border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.1);overflow-x:auto;}

form{display:flex;flex-direction:column;gap:12px;margin-bottom:20px;}
input,select,button{padding:12px;border-radius:8px;border:none;font-size:16px;}
button{background:#2ecc71;color:white;font-weight:bold;cursor:pointer;transition:0.3s;}
button:hover{background:#27ae60;}
table{width:100%;border-collapse:collapse;margin-top:20px;}
th,td{padding:12px;border-bottom:1px solid rgba(0,0,0,0.1);text-align:center;}
th{background:#2a5298;color:white}
.message{text-align:center;font-weight:bold;margin-bottom:10px;}
a.back-btn{display:inline-block;padding:10px 20px;margin-bottom:20px;border-radius:10px;background:#007bff;color:white;text-decoration:none;font-weight:bold;transition:0.3s;}
a.back-btn:hover{background:#0056b3;}
a.edit-btn{background:#ffa500;color:white;padding:6px 12px;border-radius:8px;text-decoration:none;transition:0.3s;}
a.edit-btn:hover{background:#e69500;transform:scale(1.05);}
</style>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <img src="logo.png">
        <h3>StayHub</h3></div>
    <ul class="menu">
        <li onclick="location.href='dashboard.php'"><i class="fa fa-home"></i> Dashboard</li>
        <li onclick="location.href='user_management.php'"><i class="fa fa-users"></i> User Management</li>
        <li onclick="location.href='add_room.php'"><i class="fa fa-bed"></i> Add Room</li>
        <li onclick="location.href='add_tenant.php'"><i class="fa fa-user-plus"></i> Add Tenant</li>
        <li onclick="location.href='tenant_clearance.php'"><i class="fa fa-file-signature"></i> Tenant Clearance</li>
        <li class="active"><i class="fa fa-bolt"></i> Utility Metering</li>
        <li onclick="location.href='monthly_billing.php'"><i class="fa fa-receipt"></i> Billing</li>
        <li onclick="location.href='report.php'"><i class="fa fa-chart-line"></i> Report</li>
        <li onclick="location.href='logout.php'"><i class="fa fa-sign-out-alt"></i> Logout</li>
    </ul>
</div>

<div class="main">
    <div class="topbar">
        <h3>Utility Metering</h3>
        <div><?= $_SESSION['lastname'] . ', ' . $_SESSION['firstname'] . ' ' . strtoupper($_SESSION['middlename'][0]) . '. ' ?></div>
    </div>

    <div class="content">
        <!-- Left: Utility Form -->
        <div class="utility-form">
            <h2><i class="fa fa-bolt"></i> Utility Metering (<?= date("F Y") ?>)</h2>

            <?php if($message != ""): ?>
                <div class="message"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <?php if($edit_room): ?>
                    <input type="hidden" name="room_id" value="<?= $edit_room['id'] ?>">
                    <input type="number" step="0.01" name="electricity" placeholder="Electricity" value="<?= $edit_room['electricity'] ?>" required>
                    <input type="number" step="0.01" name="water" placeholder="Water" value="<?= $edit_room['water'] ?>" required>
                    <button name="save_utility">Save Utility</button>
                <?php else: ?>
                    <select name="room_id" required>
                        <option value="" disabled selected>Select Room</option>
                        <?php
                        $all_rooms = mysqli_query($conn, "SELECT * FROM rooms ORDER BY room_number");
                        while($r = mysqli_fetch_assoc($all_rooms)){
                            echo "<option value='{$r['id']}'>Room {$r['room_number']}</option>";
                        }
                        ?>
                    </select>
                    <input type="number" step="0.01" name="electricity" placeholder="Electricity" required>
                    <input type="number" step="0.01" name="water" placeholder="Water" required>
                    <button name="save_utility">Add Utility</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Right: Utility Table -->
        <div class="utility-table">
            <table>
                <tr>
                    <th>#</th>
                    <th>Room</th>
                    <th>Electricity</th>
                    <th>Water</th>
                    <th>Total</th>
                </tr>
                <?php $i=1; while($row = mysqli_fetch_assoc($rooms)){ ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= $row['room_number'] ?></td>
                    <td>₱<?= number_format($row['electricity'],2) ?></td>
                    <td>₱<?= number_format($row['water'],2) ?></td>
                    <td>₱<?= number_format($row['electricity'] + $row['water'],2) ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>