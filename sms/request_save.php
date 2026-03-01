<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('admin/config/dbcon.php');

if (isset($_POST['submit_request'])) {
    
    // ===== USER ID (Prioritize POST for rent_item.php, then SESSION for rent_request.php) =====
    $user_id = 0;
    if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    } elseif (isset($_SESSION['auth_user']['user_id'])) {
        $user_id = $_SESSION['auth_user']['user_id'];
    }

    if ($user_id == 0) {
        $_SESSION['message'] = "User Identification Error";
        header("Location: index.php");
        exit();
    }

    // ===== COMMON DATA =====
    $issue_date = mysqli_real_escape_string($conn, $_POST['issue_date']);
    $issued_to  = mysqli_real_escape_string($conn, $_POST['issued_to']);
    $faculty    = mysqli_real_escape_string($conn, $_POST['faculty']);
    $year_part  = mysqli_real_escape_string($conn, $_POST['year_part']);
    $purpose    = mysqli_real_escape_string($conn, $_POST['purpose']);

    // Same issue_id for all items (single issue slip)
    $issue_id = time(); // Using timestamp as issue ID for now

    // Prepare statement
    $issue_status = 'In_approval';
    
    // ===== ITEM LOOP =====
    $first_item_id = 0;
    if (isset($_POST['item_name']) && is_array($_POST['item_name'])) {
        foreach ($_POST['item_name'] as $i => $item_name) {

            if (!empty($item_name) && !empty($_POST['quantity'][$i])) {
                
                // Escape data
                $item_name = mysqli_real_escape_string($conn, $item_name);
                $specification = mysqli_real_escape_string($conn, $_POST['specification'][$i]);
                $quantity = mysqli_real_escape_string($conn, $_POST['quantity'][$i]);
                $remarks = mysqli_real_escape_string($conn, $_POST['remarks'][$i]);

                // Direct Query
                $query = "INSERT INTO issued_items_dets 
                          (user_id, issue_id, issue_date, issued_to, faculty, year_part, purpose, item_name, quantity, specification, remarks, issue_status) 
                          VALUES 
                          ('$user_id', '$issue_id', '$issue_date', '$issued_to', '$faculty', '$year_part', '$purpose', '$item_name', '$quantity', '$specification', '$remarks', '$issue_status')";
                
                if (mysqli_query($conn, $query)) {
                    if ($first_item_id == 0) {
                        $first_item_id = mysqli_insert_id($conn);
                    }
                } else {
                    // Log error or set message
                    $_SESSION['message'] = "Database Error: " . mysqli_error($conn);
                    header("Location: rent_request.php");
                    exit();
                }
            }
        }
    }

    // ===== NOTIFICATION FOR ADMIN =====
    $notif_message = "New Rent Request from " . $issued_to . " (Issue #" . $issue_id . ")";
    $notif_link = "view_request.php?id=" . $first_item_id;
    $notif_type = 'rent_request';
    
    if ($first_item_id > 0) {
        $notif_query = "INSERT INTO notifications (user_id, message, type, link) VALUES ('$user_id', '$notif_message', '$notif_type', '$notif_link')";
        mysqli_query($conn, $notif_query);
    }

    if ($first_item_id > 0) {
        // ===== SUCCESS =====
        header("Location: request_success.php?issue_id=" . $issue_id);
        exit();
    } else {
        $_SESSION['message'] = "Please fill at least one item and its quantity.";
        header("Location: rent_request.php");
        exit();
    }
} else {
    // If accessed directly or not logged in
    $_SESSION['message'] = "Invalid access request";
    header("Location: index.php");
    exit();
}
?>
