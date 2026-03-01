<?php
include 'config/dbcon.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

isset($_GET['id']) ? $id = $_GET['id'] : $id = "";
if ($id != "") {
    $query = "DELETE FROM products WHERE product_id='$id' ";
    $query_run = mysqli_query($conn, $query);

    if ($query_run) {
        $_SESSION['message'] = "Product Deleted Successfully";
        header("Location: product_view.php");
        exit(0);
    } else {
        $_SESSION['message'] = "Product Not Deleted";
        header("Location: product_view.php");
        exit(0);
    }
    
    $_SESSION['message'] = "Product Deleted Successfully";
    header("Location: view_all_products.php");
} else {
    $_SESSION['message'] = "Invalid Product ID";
    header("Location: view_all_.php");
    exit(0);
}
?>