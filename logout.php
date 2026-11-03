<?php
session_start();

// destroy all session data
session_unset();
session_destroy();

// redirect to login page
header("Location: login.php");
exit;
?>

<a href="logout.php" style="
display:inline-block;
padding:10px 15px;
background:#ff4b5c;
color:white;
border-radius:8px;
text-decoration:none;
transition:0.3s;
">Logout</a>