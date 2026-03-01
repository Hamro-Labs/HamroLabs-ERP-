<?php
session_start();
include('dbcon.php');

// Not logged in
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    $_SESSION['message'] = "Please login first";
    header("Location: ../Login.php");
    exit();
}

// Logged in but role = User
if ($_SESSION['auth_user']['role'] === 'user') {
    $_SESSION['message'] = "Access denied. Admins only.";
    header("Location: ../index.php"); // user homepage
    exit();
}

// Logged in and role = Admin → allow


$_SESSION['message'] = "Welcome to the admin dashboard!";


?>