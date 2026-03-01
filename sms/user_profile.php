<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    header("Location: Login.php");
    exit;
}

include "admin/config/dbcon.php";
include "Includes/header.php";

$user_id = $_SESSION['auth_user']['user_id'];
$username = $_SESSION['auth_user']['user_name'];

// Fetch user details
$user_query = "SELECT * FROM user WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user_details = mysqli_fetch_assoc($user_result);

// Fetch rented items (approved issues)
$rented_query = "SELECT * FROM issued_items_dets WHERE user_id = ? AND issue_status = 'Approved'";
$stmt = mysqli_prepare($conn, $rented_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$rented_result = mysqli_stmt_get_result($stmt);

// Fetch pending returns (approved but not returned)
$pending_returns_query = "SELECT * FROM issued_items_dets WHERE user_id = ? AND issue_status = 'Approved'";
$stmt = mysqli_prepare($conn, $pending_returns_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$pending_returns_result = mysqli_stmt_get_result($stmt);

// Fetch suggestions sent (contact messages)
$suggestions_query = "SELECT * FROM contact WHERE id = ?";
$stmt = mysqli_prepare($conn, $suggestions_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$suggestions_result = mysqli_stmt_get_result($stmt);

// Fetch pending requests (in approval)
$pending_requests_query = "SELECT * FROM issued_items_dets WHERE user_id = ? AND issue_status = 'In_approval'";
$stmt = mysqli_prepare($conn, $pending_requests_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$pending_requests_result = mysqli_stmt_get_result($stmt);

// Calculate statistics
$total_rented = mysqli_num_rows($rented_result);
$total_pending_returns = mysqli_num_rows($pending_returns_result);
$total_suggestions = mysqli_num_rows($suggestions_result);
$total_pending_requests = mysqli_num_rows($pending_requests_result);
?>

<style>
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .profile-header {
        background: var(--bg-elevated);
        border-radius: 16px;
        padding: 40px;
        margin-bottom: 30px;
        box-shadow: var(--shadow-color);
        border: 1px solid var(--border-color);
    }

    .profile-info {
        display: flex;
        align-items: center;
        gap: 30px;
        flex-wrap: wrap;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: white;
        font-weight: bold;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .profile-details h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--text-primary);
    }

    .profile-details p {
        color: var(--text-secondary);
        margin-bottom: 4px;
    }

    .profile-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 30px;
    }

    .stat-card {
        background: var(--bg-secondary);
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        border: 1px solid var(--border-color);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px var(--shadow-color);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-700);
        margin-bottom: 8px;
    }

    .stat-label {
        color: var(--text-secondary);
        font-size: 0.9rem;
        font-weight: 500;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 20px;
        padding-left: 15px;
        border-left: 4px solid var(--primary-700);
    }

    .content-grid {
        display: grid;
        gap: 30px;
    }

    .content-card {
        background: var(--bg-elevated);
        border-radius: 16px;
        padding: 30px;
        box-shadow: var(--shadow-color);
        border: 1px solid var(--border-color);
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead {
        background: var(--bg-secondary);
    }

    .table th,
    .table td {
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }

    .table th {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: var(--bg-secondary);
    }

    .table tbody td {
        color: var(--text-secondary);
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .status-approved {
        background-color: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .status-pending {
        background-color: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .status-in-approval {
        background-color: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 4rem;
        opacity: 0.3;
        margin-bottom: 20px;
    }

    .action-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background-color: var(--primary-700);
        color: white;
    }

    .btn-primary:hover {
        background-color: var(--primary-500);
    }

    .btn-secondary {
        background-color: var(--bg-secondary);
        color: var(--text-primary);
    }

    .btn-secondary:hover {
        background-color: var(--border-color);
    }

    @media (max-width: 768px) {
        .profile-header {
            padding: 30px 20px;
        }

        .profile-info {
            flex-direction: column;
            text-align: center;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            font-size: 40px;
        }

        .profile-details h1 {
            font-size: 1.5rem;
        }

        .profile-stats {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .stat-number {
            font-size: 1.5rem;
        }

        .content-card {
            padding: 20px;
        }

        .table th,
        .table td {
            padding: 10px 8px;
            font-size: 0.85rem;
        }
    }
</style>

<div class="profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-info">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($username, 0, 1)); ?>
            </div>
            <div class="profile-details">
                <h1><?php echo htmlspecialchars($username); ?></h1>
                <p><i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($user_details['email']); ?></p>
                <p><i class="bi bi-building me-2"></i><?php echo htmlspecialchars($user_details['faculty'] ?? 'N/A'); ?> - <?php echo htmlspecialchars($user_details['year_part'] ?? 'N/A'); ?></p>
                <p><i class="bi bi-geo-alt me-2"></i><?php echo htmlspecialchars($user_details['address'] ?? 'N/A'); ?></p>
                <p><i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($user_details['contact'] ?? 'N/A'); ?></p>
            </div>
        </div>

        <!-- Profile Statistics -->
        <div class="profile-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_rented; ?></div>
                <div class="stat-label">Rented Items</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_pending_returns; ?></div>
                <div class="stat-label">Pending Returns</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_suggestions; ?></div>
                <div class="stat-label">Suggestions Sent</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_pending_requests; ?></div>
                <div class="stat-label">Pending Requests</div>
            </div>
        </div>
    </div>

    <!-- Rented Items -->
    <div class="content-card">
        <h3 class="section-title">
            <i class="bi bi-box-seam me-2"></i>Rented Items
        </h3>
        <div class="table-responsive">
            <?php if ($total_rented > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Issue Date</th>
                            <th>Specification</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php mysqli_data_seek($rented_result, 0); // Reset result pointer ?>
                        <?php while ($row = mysqli_fetch_assoc($rented_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><?php echo date('d M Y', strtotime($row['issue_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['specification'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                <td><span class="status-badge status-approved">Approved</span></td>
                                <td>
                                    <a href="return_item.php?issue_id=<?php echo $row['issue_id']; ?>&item_id=<?php echo $row['id']; ?>" class="action-btn btn-primary">
                                        <i class="bi bi-arrow-return-left me-1"></i>Return
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h3>No Items Rented</h3>
                    <p>You haven't rented any items yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pending Returns -->
    <div class="content-card">
        <h3 class="section-title">
            <i class="bi bi-clock me-2"></i>Pending Returns
        </h3>
        <div class="table-responsive">
            <?php if ($total_pending_returns > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Issue Date</th>
                            <th>Days Rented</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php mysqli_data_seek($pending_returns_result, 0); // Reset result pointer ?>
                        <?php while ($row = mysqli_fetch_assoc($pending_returns_result)): 
                            $issue_date = new DateTime($row['issue_date']);
                            $today = new DateTime();
                            $days_rented = $issue_date->diff($today)->days;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><?php echo date('d M Y', strtotime($row['issue_date'])); ?></td>
                                <td><?php echo $days_rented; ?> days</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td>
                                    <a href="return_item.php?issue_id=<?php echo $row['issue_id']; ?>&item_id=<?php echo $row['id']; ?>" class="action-btn btn-primary">
                                        <i class="bi bi-arrow-return-left me-1"></i>Return Now
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-check-circle"></i>
                    <h3>No Pending Returns</h3>
                    <p>All items have been returned.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pending Requests -->
    <div class="content-card">
        <h3 class="section-title">
            <i class="bi bi-hourglass-split me-2"></i>Pending Requests
        </h3>
        <div class="table-responsive">
            <?php if ($total_pending_requests > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Request Date</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($pending_requests_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                <td><span class="status-badge status-in-approval">In Approval</span></td>
                                <td>
                                    <span class="action-btn btn-secondary disabled" style="cursor: not-allowed;">
                                        <i class="bi bi-clock me-1"></i>Waiting
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-check-circle"></i>
                    <h3>No Pending Requests</h3>
                    <p>All your requests have been processed.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Suggestions Sent -->
    <div class="content-card">
        <h3 class="section-title">
            <i class="bi bi-chat-dots me-2"></i>Suggestions Sent
        </h3>
        <div class="table-responsive">
            <?php if ($total_suggestions > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Sent Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($suggestions_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['message'], 0, 100)) . '...'; ?></td>
                                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-chat-dots"></i>
                    <h3>No Suggestions Sent</h3>
                    <p>You haven't sent any suggestions yet.</p>
                    <a href="contact.php" class="action-btn btn-primary mt-3">
                        <i class="bi bi-plus-circle me-1"></i>Send Suggestion
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "Includes/bottom.php"; ?>
