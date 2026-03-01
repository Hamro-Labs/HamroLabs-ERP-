<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('config/dbcon.php');

if (isset($_POST['submit_issue']) || isset($_POST['submit'])) {

    // ===== USER ID =====
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id'] ?? 'Admin');

    // ===== COMMON DATA =====
    $issue_date = $_POST['issue_date'];
    $issued_to  = $_POST['receiver_name'] ?? $_POST['issued_to'] ?? '';
    $faculty    = $_POST['faculty'];
    $year_part  = $_POST['year_part'];
    $purpose    = $_POST['purpose'];

    // Same issue_id for all items (single issue slip)
    $issue_id = time();

    $first_item_id = 0;
    // ===== ITEM LOOP =====
    if (isset($_POST['item_name']) && is_array($_POST['item_name'])) {
        foreach ($_POST['item_name'] as $i => $item_name) {

            if (!empty($item_name) && isset($_POST['quantity'][$i]) && !empty($_POST['quantity'][$i])) {
                
                $item_name = mysqli_real_escape_string($conn, $item_name);
                $specification = mysqli_real_escape_string($conn, $_POST['specification'][$i]);
                $quantity = mysqli_real_escape_string($conn, $_POST['quantity'][$i]);
                $remarks = mysqli_real_escape_string($conn, $_POST['remarks'][$i]);

                $query = "INSERT INTO issued_items_dets 
                          (user_id, issue_id, issue_date, issued_to, faculty, year_part, purpose, item_name, quantity, specification, remarks, issue_status) 
                          VALUES 
                          ('$user_id', '$issue_id', '$issue_date', '$issued_to', '$faculty', '$year_part', '$purpose', '$item_name', '$quantity', '$specification', '$remarks', 'Approved')";
                
                if (mysqli_query($conn, $query)) {
                    if ($first_item_id == 0) $first_item_id = mysqli_insert_id($conn);
                }
            }
        }
    }

    // ===== NOTIFICATION (Self Notification for record) =====
    if ($first_item_id > 0) {
        $notif_message = "New Issue Slip Created for " . $issued_to;
        $notif_link = "view_request.php?id=" . $first_item_id;
        $notif_query = "INSERT INTO notifications (user_id, message, type, link) VALUES ('$user_id', '$notif_message', 'issue', '$notif_link')";
        mysqli_query($conn, $notif_query);
    }

    // ===== SUCCESS =====
    header("Location: rent_requests.php?msg=Issue slip saved successfully");
    exit();
} else {
    header("Location: dashboard.php");
    exit();
}
?>