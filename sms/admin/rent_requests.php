<?php
include('config/authentication.php');

// --- DELETE LOGIC ---
if (isset($_GET['delete_issue'])) {
    $issue_id = mysqli_real_escape_string($conn, $_GET['delete_issue']);
    $sql = "DELETE FROM `issued_items_dets` WHERE `issue_id` = '$issue_id'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        header("Location: rent_requests.php?msg=Entire request slip deleted successfully");
        exit();
    } else {
        echo "Failed: " . mysqli_error($conn);
        exit();
    }
}

// --- APPROVE LOGIC ---
if (isset($_GET['approve_issue'])) {
    $issue_id = mysqli_real_escape_string($conn, $_GET['approve_issue']);
    $sql = "UPDATE `issued_items_dets` SET `issue_status` = 'Approved' WHERE `issue_id` = '$issue_id'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        header("Location: rent_requests.php?msg=Entire request slip approved successfully");
        exit();
    } else {
        echo "Failed: " . mysqli_error($conn);
        exit();
    }
}

include('includes/header.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Requests Management</title>
    <!-- CSS is already in header.php, but adding extra if needed -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <style>
        .action-btns {
            white-space: nowrap;
        }

        .table-container {
            margin-top: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            border-radius: 12px;
            background: white;
        }

        .navbar-custom {
            background: linear-gradient(135deg, #15a5e3 0%, #0d6efd 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="container-fluid px-4">
        
        <div class="navbar-custom">
            <h2 class="mb-0"><i class="fa-solid fa-clipboard-list me-2"></i> Rent Requests Management</h2>
        </div>

        <?php
        if (isset($_GET["msg"])) {
            $msg = $_GET["msg"];
            echo '<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>' . htmlspecialchars($msg) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        }
        ?>

        <div class="table-responsive table-container">
            <table class="table table-hover text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>S.N.</th>
                        <th>User ID</th>
                        <th>Issue ID</th>
                        <th>Date</th>
                        <th>Receiver Name</th>
                        <th>Faculty</th>
                        <th>Purpose</th>
                        <th>Items Count</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Grouped Query to show Slips
                    $sql = "SELECT *, COUNT(id) as item_count FROM `issued_items_dets` GROUP BY issue_id ORDER BY id DESC";
                    $result = mysqli_query($conn, $sql);

                    if (mysqli_num_rows($result) > 0) {
                        $sn = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                            $status = isset($row['issue_status']) ? $row['issue_status'] : 'Pending';
                            $badgeColor = ($status == 'Approved') ? 'bg-success' : 'bg-warning text-dark';
                            
                            // Handle cases where name/faculty might still be missing from DB
                            $disp_name = !empty($row['issued_to']) ? htmlspecialchars($row['issued_to']) : '<span class="text-danger italic">N/A</span>';
                            $disp_faculty = !empty($row['faculty']) ? htmlspecialchars($row['faculty']) : '<span class="text-muted italic">N/A</span>';
                            ?>
                            <tr>
                                <td><?php echo $sn++; ?></td>
                                <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($row['issue_id']); ?></span></td>
                                <td><?php echo date('d-M-Y', strtotime($row['issue_date'])); ?></td>
                                <td class="fw-bold"><?php echo $disp_name; ?></td>
                                <td><?php echo $disp_faculty; ?></td>
                                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                <td><span class="badge bg-info text-white"><?php echo $row['item_count']; ?> Items</span></td>
                                <td><span class="badge <?php echo $badgeColor; ?>"><?php echo $status; ?></span></td>
                                <td class="action-btns">
                                    <a href="view_request.php?id=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-info text-white" title="Detailed Review">
                                        <i class="fa-solid fa-eye"></i> View Slip
                                    </a>

                                    <a href="rent_requests.php?approve_issue=<?php echo $row['issue_id']; ?>"
                                        class="btn btn-sm btn-success <?php echo ($status == 'Approved') ? 'disabled' : ''; ?>"
                                        title="Approve All Items" onclick="return confirm('Approve entire request slip #<?php echo $row['issue_id']; ?>?')">
                                        <i class="fa-solid fa-check"></i>
                                    </a>

                                    <a href="rent_requests.php?delete_issue=<?php echo $row['issue_id']; ?>"
                                        class="btn btn-sm btn-danger" title="Delete Slip"
                                        onclick="return confirm('Delete entire request slip and all its items?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='10' class='text-center py-4'>No request slips found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>

<?php include('includes/footer.php'); ?>