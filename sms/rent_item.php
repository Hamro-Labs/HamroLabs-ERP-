<?php
include('includes/header.php');
include('admin/config/dbcon.php');

$user_data = null;

if (isset($_POST['fetch_user'])) {
    $user_id = $_POST['user_id'];

    // FIXED: Use mysqli_real_escape_string to prevent SQL injection
    $user_id = mysqli_real_escape_string($conn, $user_id);

    $query = "SELECT name, faculty, year_part FROM user WHERE id = '$user_id' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);
    } else {
        echo "<p class='text-danger text-center fw-bold'>User ID not found!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Issue Slip</title>
    <link rel="stylesheet" href="Offline/CSS/rent.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


</head>

<body>

    <div class="request-container">

        <!-- This is the area that will be printed -->
        <div class="print-area">

            <!-- HEADER -->
            <h4 class="text-center mb-4 fw-bold issue-title">
                BIRGUNJ INSTITUTE OF TECHNOLOGY<br>
                <small class="text-muted">Birgunj, Nepal</small><br>
                <span class="badge bg-dark mt-3">REQUEST SLIP </span>
            </h4>

            <!-- FETCH USER FORM (Will not print) -->
            <?php if (!$user_data): ?>
                <div class="fetch-form-section no-print d-flex justify-content-center">
                    <div class="card shadow-sm border-0 p-4" style="max-width: 420px; width:100%;">
                        <h5 class="mb-3 text-center fw-semibold">Continue with User ID</h5>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">User ID</label>
                                <input type="text" name="user_id" class="form-control form-control-lg"
                                    placeholder="Enter your User ID" required>
                            </div>

                            <p  id="Register">
                                Haven’t registered yet?
                            </p>

                            <div class="row g-2">
                                <div class="col-6">
                                    <button type="submit" name="fetch_user" class="btn btn-warning w-100 fw-semibold">
                                        Proceed
                                    </button>
                                </div>

                                <div class="col-6">
                                    <a href="SignUp.php" class="btn btn-outline-success w-100 fw-semibold">
                                        Register
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            <?php endif; ?>

            <!-- ISSUE FORM (Will print) -->
            <?php if ($user_data): ?>
                <form method="POST" action="request_save.php">

                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="issued_to" class="form-control"
                                value="<?php echo htmlspecialchars($user_data['name']); ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Faculty</label>
                            <input type="text" name="faculty" class="form-control"
                                value="<?php echo htmlspecialchars($user_data['faculty']); ?>" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Year / Part</label>
                            <input type="text" name="year_part" class="form-control"
                                value="<?php echo htmlspecialchars($user_data['year_part']); ?>" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="issue_date" class="form-control" value="<?php echo date('Y-m-d'); ?>"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Purpose of Demand</label>
                            <input type="text" name="purpose" class="form-control" required>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead>
                                <tr>
                                    <th>S.N.</th>
                                    <th>Name of Items</th>
                                    <th>Specification</th>
                                    <th>Quantity</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><input type="text" name="item_name[]" class="form-control"></td>
                                        <td><input type="text" name="specification[]" class="form-control"></td>
                                        <td><input type="number" name="quantity[]" class="form-control" min="1"></td>
                                        <td><input type="text" name="remarks[]" class="form-control"></td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Print Footer (Only shows when printing) -->
                    <div class="print-footer" style="display: none;">
                        <p><strong>Requested By:</strong> ____________________________</p>
                        <p><strong>Signature:</strong> ____________________________</p>
                        <p><strong>Date:</strong> ____________________________</p>
                    </div>

                    <!-- Submit Button (Will not print) -->
                    <div class="text-center mt-4 no-print">
                        <button type="submit" name="submit_request" class="btn btn-primary px-5">
                            Submit Request Slip
                        </button>
                    </div>

                </form>

                <!-- Buttons (Will not print) -->
                <div class="no-print mt-4 d-flex justify-content-end gap-2">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fa-solid fa-print"></i> Print Slip
                    </button>
                </div>

                <!-- Show footer when printing -->
                <style>
                    @media print {
                        .print-footer {
                            display: block !important;
                        }
                    }
                </style>

            <?php endif; ?>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php include('includes/bottom.php'); ?>