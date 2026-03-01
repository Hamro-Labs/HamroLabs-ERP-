<?php
include('config/authentication.php');

if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Mark as read
    $update_query = "UPDATE notifications SET is_read = 1 WHERE id = '$id'";
    mysqli_query($conn, $update_query);
    
    // Get link to redirect
    $link_query = "SELECT link FROM notifications WHERE id = '$id' LIMIT 1";
    $link_run = mysqli_query($conn, $link_query);
    
    if(mysqli_num_rows($link_run) > 0) {
        $row = mysqli_fetch_assoc($link_run);
        $redirect_link = $row['link'] ? $row['link'] : 'index.php';
        header("Location: " . $redirect_link);
        exit(0);
    } else {
        header("Location: index.php");
        exit(0);
    }
} else {
    header("Location: index.php");
    exit(0);
}
?>
