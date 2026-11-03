<?php
session_start();
include 'db.php';

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    $user = mysqli_fetch_assoc($query);

    if($user){

        // ✅ Check role match
        if($user['role'] != $role){
            $error = "Invalid role selected!";
        }

        elseif($user['status'] == 'inactive'){
            $error = "Account deactivated!";
        }

        elseif(password_verify($password, $user['password'])){
            mysqli_query($conn, "UPDATE users SET attempts=0 WHERE id='{$user['id']}'");

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['middlename'] = $user['middlename'];
            $_SESSION['lastname'] = $user['lastname'];
            $_SESSION['role'] = $user['role'];

            if($user['role'] == 'admin'){
                header("Location: dashboard.php");
            } else {
                header("Location: tenant_dashboard.php");
            }
            exit;
        }
        else{
            $attempts = $user['attempts'] + 1;

            if($attempts >= 3){
                mysqli_query($conn, "UPDATE users SET attempts=3, status='inactive' WHERE id='{$user['id']}'");
                $error = "Account deactivated!";
            } else {
                mysqli_query($conn, "UPDATE users SET attempts='$attempts' WHERE id='{$user['id']}'");
                $error = "Invalid password ($attempts/3)";
            }
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Stay-Hub Login</title>
<link rel="icon" href="logo.png" type="image/png">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }

body{
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    background: url('stay-hub-room.jpeg') center/cover no-repeat;
}

/* 🔥 LEFT TITLE */
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

.container{
    backdrop-filter: blur(15px);
    padding:40px;
    border-radius:15px;
    width:340px;
    color:white;
    text-align:center;
}

/* INPUT GROUP */
.form-group{
    position:relative;
    margin-bottom:15px;
}

.form-group i{
    position:absolute;
    left:12px;
    top:50%;
    transform:translateY(-50%);
    color:#555;
}

input, select{
    width:100%;
    padding:12px 12px 12px 35px;
    border:none;
    border-radius:8px;
    background:rgba(255,255,255,0.9);
}

/* BUTTON */
button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    background:#00c6ff;
    color:white;
    font-weight:bold;
    cursor:pointer;
}

button:hover{
    background:#0072ff;
}

.error{
    color:#ff4d4d;
    margin-bottom:10px;
}
</style>
</head>

<body>
<div class="left-title">
    <div class="small">Welcome to</div>
    <div class="big">STAY-HUB</div>
    <div class="sub">(Dormitory Management)</div>
</div>

<div class="container">

    <h2>Login</h2>

    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">

        <div class="form-group">
            <i class="fa fa-user-shield"></i>
            <select name="role" required>
                <option value="" disabled selected>Select Role</option>
                <option value="admin">Admin</option>
                <option value="tenant">Tenant</option>
            </select>
        </div>

        <div class="form-group">
            <i class="fa fa-user"></i>
            <input type="text" name="username" placeholder="Username" required>
        </div>

        <div class="form-group">
            <i class="fa fa-lock"></i>
            <input type="password" name="password" placeholder="Password" required>
        </div>

        

        <button name="login">
            <i class="fa fa-sign-in-alt"></i> Login
        </button>

    </form>

    <p>Don't have an account? <a href="register.php">Register</a></p>

</div>

</body>
</html>