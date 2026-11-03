<?php
include 'db.php';

if(isset($_POST['register'])){

    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // FIXED ROLE (ONLY admin or tenant)
    $role = 'tenant'; // force tenant only
    mysqli_query($conn, "INSERT INTO users 
    (firstname, middlename, lastname, username, email, password, role) 
    VALUES 
    ('$firstname','$middlename','$lastname','$username','$email','$password','$role')");

    $success = "Registered successfully as $role!";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Stay-Hub Registration</title>
<link rel="icon" href="logo.png" type="image/png">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Segoe UI;
}

body{
    height:100vh;
    background:url('stay-hub-rooms.jpeg') center/cover no-repeat;
    display:flex;
    justify-content:center;
    align-items:center;
    position:relative;
}

/* LEFT TITLE */
.left-title{
    position:absolute;
    top:20px;
    left:630px;
    color:white;
    text-shadow:0 4px 10px rgba(0,0,0,0.7);
}

.left-title .small{ font-size:20px; }
.left-title .big{ font-size:45px; font-weight:bold; }
.left-title .sub{ font-size:19px; }

.card{
    width:420px;
    padding:25px;
    background:white;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.3);
    text-align:center;
}

h2{
    margin-bottom:15px;
}

input, select, button{
    width:100%;
    padding:10px;
    margin:6px 0;
    border-radius:8px;
    border:1px solid #ccc;
    outline:none;
}

.name-group{
    display:flex;
    gap:6px;
}

.name-group input{
    flex:1;
}

button{
    background:#F5A623;
    color:white;
    border:none;
    font-weight:bold;
    cursor:pointer;
}

button:hover{
    background:#D48806;
}

.success{
    background:#d4edda;
    padding:8px;
    border-radius:8px;
    margin-bottom:10px;
    color:#155724;
}

a{
    text-decoration:none;
    font-weight:bold;
}
</style>
</head>

<body>

<div class="left-title">
    <div class="small">Welcome to</div>
    <div class="big">STAY-HUB</div>
    <div class="sub">(Dormitory Management)</div>
</div>

<div class="card">

    <h2>REGISTER</h2>

    <?php if(isset($success)) echo "<p class='success'><i class='fa fa-check-circle'></i> $success</p>"; ?>

    <form method="POST">

        <div class="name-group">
            <input type="text" name="firstname" placeholder="First Name" required>
            <input type="text" name="middlename" placeholder="M.I.">
            <input type="text" name="lastname" placeholder="Last Name" required>
        </div>

        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <!-- ✅ FIXED ROLE LABEL -->
        <select name="role" required>
            <option value="" disabled selected>Role</option>
            <option value="tenant">Tenant</option>
            <option value="admin">Admin</option>
        </select>

        <button name="register"><i class="fa fa-user-plus"></i> Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
</div>

</body>
</html>