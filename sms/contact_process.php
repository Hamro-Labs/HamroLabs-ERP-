<?php 

include 'admin/config/dbcon.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $query = "INSERT INTO contact (id, name, email, subject, message) VALUES ('$user_id', '$name', '$email', '$subject', '$message')";
    $query_run = mysqli_query($conn, $query);
    
    if ($query_run) {
        // ===== NOTIFICATION FOR ADMIN =====
        $notif_message = "New Message from " . $name . ": " . $subject;
        $notif_link = "suggestions.php"; // Link to the messages list
        $notif_type = 'suggestion';
        
        $notif_query = "INSERT INTO notifications (user_id, message, type, link) VALUES ('$user_id', '$notif_message', '$notif_type', '$notif_link')";
        mysqli_query($conn, $notif_query);

        echo "Message sent successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

?>