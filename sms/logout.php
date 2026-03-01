<?php
session_start();

// Unset all session values
unset($_SESSION['auth']);
unset($_SESSION['auth_role']);
unset($_SESSION['auth_user']);
unset($_SESSION['message']);

// Destroy session completely
session_destroy();

// Prevent browser cache

// Redirect to homepage
header("Location: index.php");
exit();
