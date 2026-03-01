<?php
include('Includes/header.php');
include('admin/config/dbcon.php');

$issue = null;
$items = [];
$message = "";
$success = false;

/* -------------------------------------------
   AUTO PROCESS RETURN IF ISSUE ID IN URL
-------------------------------------------*/
if (isset($_GET['issue_id'])) {
    $issue_id = intval($_GET['issue_id']);
    
    $issueQuery = mysqli_query($conn, "
        SELECT * FROM issued_items_dets WHERE issue_id='$issue_id'
    ");

    if (mysqli_num_rows($issueQuery) > 0) {
        $issue = mysqli_fetch_assoc($issueQuery);

        $itemsQuery = mysqli_query($conn, "
            SELECT * FROM issued_items_dets WHERE issue_id='$issue_id'
        ");

        while ($row = mysqli_fetch_assoc($itemsQuery)) {
            $items[] = $row;
        }

        // Auto-submit return request
        $user_query = mysqli_query($conn, "SELECT user_id FROM issued_items_dets WHERE issue_id='$issue_id' LIMIT 1");
        $user_data = mysqli_fetch_assoc($user_query);
        $user_id = $user_data ? $user_data['user_id'] : 0;

        $insert_query = mysqli_query($conn, "
            INSERT INTO return_requests (issue_id, user_id, status)
            VALUES ('$issue_id', $user_id, 'pending')
        ");

        if ($insert_query) {
            $message = "Return request submitted successfully. Your Return ID is: " . mysqli_insert_id($conn);
            $success = true;
        } else {
            $message = "Failed to submit return request: " . mysqli_error($conn);
            $success = false;
        }
    } else {
        $message = "Issue ID not found.";
        $success = false;
    }
}

/* -------------------------------------------
   SEARCH ISSUE ID
-------------------------------------------*/
if (isset($_POST['search_issue'])) {
    $issue_id = intval($_POST['issue_id']);

    $issueQuery = mysqli_query($conn, "
        SELECT * FROM issued_items_dets WHERE issue_id='$issue_id'
    ");

    if (mysqli_num_rows($issueQuery) > 0) {
        $issue = mysqli_fetch_assoc($issueQuery);

        $itemsQuery = mysqli_query($conn, "
            SELECT * FROM issued_items_dets WHERE issue_id='$issue_id'
        ");

        while ($row = mysqli_fetch_assoc($itemsQuery)) {
            $items[] = $row;
        }
    } else {
        $message = "Issue ID not found.";
        $success = false;
    }
}

/* -------------------------------------------
   SUBMIT RETURN REQUEST
-------------------------------------------*/
if (isset($_POST['submit_return'])) {
    $issue_id = intval($_POST['issue_id']);

    $user_query = mysqli_query($conn, "SELECT user_id FROM issued_items_dets WHERE issue_id='$issue_id' LIMIT 1");
    $user_data = mysqli_fetch_assoc($user_query);
    $user_id = $user_data ? $user_data['user_id'] : 0;

    $insert_query = mysqli_query($conn, "
        INSERT INTO return_requests (issue_id, user_id, status)
        VALUES ('$issue_id', $user_id, 'pending')
    ");

    if ($insert_query) {
        $message = "Return request submitted successfully. Your Return ID is: " . mysqli_insert_id($conn);
        $success = true;
    } else {
        $message = "Failed to submit return request: " . mysqli_error($conn);
        $success = false;
    }
}
?>

<style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 40px 0;
    }

    .request-container {
        max-width: 1100px;
        margin: 0 auto;
        background: #fff;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .form-label {
        font-size: .85rem;
        font-weight: 600;
        color: #6c757d;
    }

    .form-control {
        padding-left: 42px;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }

    .form-group {
        position: relative;
    }

    .input-icon {
        position: absolute;
        top: 55%;
        left: 14px;
        transform: translateY(-50%);
        color: #adb5bd;
    }

    table input {
        padding-left: 12px !important;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        border: none;
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(13, 110, 253, 0.3);
    }

    .btn-light {
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-light:hover {
        border-color: #0d6efd;
        color: #0d6efd;
    }

    .alert {
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success {
        background: #d1e7dd;
        color: #0f5132;
        border: 2px solid #badbcc;
    }

    .alert-danger {
        background: #f8d7da;
        color: #842029;
        border: 2px solid #f5c2c7;
    }

    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        border: 2px solid #bee5eb;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 30px;
        color: #212529;
    }

    .table {
        margin-top: 20px;
    }

    .table thead {
        background: #f8f9fa;
    }

    .table th {
        font-weight: 600;
        color: #495057;
        border: none;
        padding: 12px;
    }

    .table td {
        vertical-align: middle;
        padding: 12px;
        border-color: #e9ecef;
    }

    .table tbody tr:hover {
        background: #f8f9fa;
    }

    .success-animation {
        animation: slideIn 0.5s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .processing-spinner {
        display: none;
        margin-right: 8px;
    }

    .btn-processing .processing-spinner {
        display: inline-block;
    }

    @media (max-width: 768px) {
        .request-container {
            padding: 20px;
            margin: 20px;
        }

        .form-control {
            padding-left: 36px;
        }
    }
</style>

<div class="request-container">
    <h3 class="text-center mb-4 section-title">
        <i class="bi bi-arrow-return-left me-2"></i> Item Return Process
    </h3>

    <?php if ($message): ?>
        <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?> success-animation">
            <i class="bi <?php echo $success ? 'bi-check-circle' : 'bi-exclamation-circle'; ?> fs-3"></i>
            <div class="flex-grow-1">
                <?php echo $message; ?>
                <?php if ($success): ?>
                    <div class="mt-2">
                        <a href="user_profile.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-arrow-left me-1"></i> Back to Profile
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$issue && !isset($_GET['issue_id'])): ?>
        <!-- SEARCH ISSUE -->
        <form method="POST" class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Enter Issue ID</label>
                <div class="form-group">
                    <i class="bi bi-search input-icon"></i>
                    <input type="number" name="issue_id" class="form-control" required placeholder="Enter your issue ID">
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" name="search_issue" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Search
                </button>
            </div>
        </form>
    <?php endif; ?>

    <?php if ($issue && !$success): ?>
        <form method="POST" id="returnForm">
            <input type="hidden" name="issue_id" value="<?= $issue['issue_id'] ?>">

            <!-- BASIC DETAILS -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 form-group">
                    <label class="form-label">Purpose</label>
                    <i class="bi bi-pencil input-icon"></i>
                    <input type="text" class="form-control" value="<?= $issue['purpose'] ?>" readonly>
                </div>

                <div class="col-md-6 form-group">
                    <label class="form-label">Issue Date</label>
                    <i class="bi bi-calendar input-icon"></i>
                    <input type="text" class="form-control" value="<?= $issue['issue_date'] ?>" readonly>
                </div>

                <div class="col-md-6 form-group">
                    <label class="form-label">Faculty</label>
                    <i class="bi bi-mortarboard input-icon"></i>
                    <input type="text" class="form-control" value="<?= $issue['faculty'] ?>" readonly>
                </div>

                <div class="col-md-6 form-group">
                    <label class="form-label">Year / Part</label>
                    <i class="bi bi-calendar-event input-icon"></i>
                    <input type="text" class="form-control" value="<?= $issue['year_part'] ?>" readonly>
                </div>
            </div>

            <!-- ITEMS TABLE -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>SN</th>
                            <th>Item Name</th>
                            <th>Specification</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sn = 1;
                        foreach ($items as $item): ?>
                            <tr>
                                <td class="text-center"><?= $sn++ ?></td>
                                <td><input class="form-control" value="<?= $item['item_name'] ?>" readonly></td>
                                <td><input class="form-control" value="<?= $item['specification'] ?>" readonly></td>
                                <td><input class="form-control" value="<?= $item['quantity'] ?>" readonly></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- REQUESTER -->
            <div class="row mb-4">
                <div class="col-md-6 form-group">
                    <label class="form-label">Issued To</label>
                    <i class="bi bi-person-fill input-icon"></i>
                    <input type="text" class="form-control" value="<?= $issue['issued_to'] ?>" readonly>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" name="submit_return" class="btn btn-light border px-5" id="submitBtn">
                    <span class="processing-spinner spinner-border spinner-border-sm"></span>
                    <i class="bi bi-check-circle-fill me-1"></i> Submit Return Request
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const returnForm = document.getElementById('returnForm');
        const submitBtn = document.getElementById('submitBtn');

        if (returnForm && submitBtn) {
            returnForm.addEventListener('submit', function(e) {
                // Show processing state
                submitBtn.classList.add('btn-processing');
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <span class="processing-spinner spinner-border spinner-border-sm"></span>
                    Processing Return...
                `;
            });
        }

        // Auto-scroll to alert if there's a message
        if (document.querySelector('.alert')) {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Add animation to alert
        const alertElement = document.querySelector('.alert');
        if (alertElement) {
            alertElement.style.animation = 'slideIn 0.5s ease-out';
        }
    });
</script>
</body>

</html>

<?php include('Includes/bottom.php'); ?>
