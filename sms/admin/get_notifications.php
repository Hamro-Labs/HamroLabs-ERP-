<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('config/dbcon.php');

header('Content-Type: application/json');

if (!isset($_SESSION['auth']) || strtolower($_SESSION['auth_user']['role']) !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

// Fetch unread notifications newer than last_id
$query = "SELECT * FROM notifications WHERE is_read = 0 AND id > $last_id ORDER BY id ASC";
$query_run = mysqli_query($conn, $query);

$notifications = [];
if ($query_run) {
    while ($row = mysqli_fetch_assoc($query_run)) {
        $notifications[] = [
            'id' => $row['id'],
            'message' => $row['message'],
            'link' => $row['link'],
            'created_at' => $row['created_at'],
            'type' => $row['type']
        ];
    }
}

echo json_encode(['status' => 'success', 'notifications' => $notifications]);
?>
