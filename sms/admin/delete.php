<?php
include "config/dbcon.php";
$id = $_GET["id"];
$sql = "DELETE FROM `user` WHERE id = $id";
$result = mysqli_query($conn, $sql);
if ($result) {
    header("Location: user_management.php");
} else {
    echo "Failed: " . mysqli_error($conn);
}
?>