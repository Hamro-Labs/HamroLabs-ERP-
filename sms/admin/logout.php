<?php 
include 'config/dbcon.php';
session_start();
session_destroy();
header("Location: index.php");
exit();
?>