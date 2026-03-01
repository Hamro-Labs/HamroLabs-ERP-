<?php
include('config/authentication.php');
include('includes/header.php');
date_default_timezone_set('Asia/Kathmandu');
?>

<div class="container-fluid px-4">
    <style>
        /* ================= BASE ================= */
        .issue-container {
            max-width: 900px;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        .header-section {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .issue-badge {
            background: #212529;
            color: #fff;
            padding: 8px 25px;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
            border-radius: 5px;
        }

        label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #444;
            margin-bottom: 5px;
        }

        .form-control-custom {
            border: none;
            border-bottom: 2px solid #dee2e6;
            border-radius: 0;
            padding: 8px 0;
            transition: all 0.3s;
        }

        .form-control-custom:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }

        .item-table th {
            background: #f8f9fa;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        @media print {
            .no-print { display: none !important; }
            .issue-container { box-shadow: none; margin: 0; padding: 0; max-width: 100%; }
        }
    </style>

    <div class="issue-container">
        <form method="post" action="issue_save.php">
            <div class="header-section">
                <h1 class="display-6 fw-bold">BIRGUNJ INSTITUTE OF TECHNOLOGY</h1>
                <p class="text-muted mb-1">Birgunj, Nepal</p>
                <div class="issue-badge">ISSUE SLIP</div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label>User ID / Admin ID</label>
                    <input type="text" name="user_id" class="form-control form-control-custom" value="Admin" required>
                </div>
                <div class="col-md-4">
                    <label>Purpose of Demand</label>
                    <input type="text" name="purpose" class="form-control form-control-custom" placeholder="e.g. Lab Session" required>
                </div>
                <div class="col-md-4">
                    <label>Date</label>
                    <input type="date" name="issue_date" class="form-control form-control-custom" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label>Faculty</label>
                    <input type="text" name="faculty" class="form-control form-control-custom" placeholder="e.g. DCOM" required>
                </div>
                <div class="col-md-6">
                    <label>Year / Part</label>
                    <input type="text" name="year_part" class="form-control form-control-custom" placeholder="e.g. III/I" required>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered item-table">
                    <thead>
                        <tr class="text-center">
                            <th width="50">S.N.</th>
                            <th>Name of Items</th>
                            <th>Specification</th>
                            <th width="100">Quantity</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <tr>
                                <td class="text-center align-middle"><?= $i ?></td>
                                <td><input type="text" name="item_name[]" class="form-control border-0"></td>
                                <td><input type="text" name="specification[]" class="form-control border-0"></td>
                                <td><input type="number" name="quantity[]" class="form-control border-0 text-center" min="1"></td>
                                <td><input type="text" name="remarks[]" class="form-control border-0"></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <label>Receiver's Name</label>
                    <input type="text" name="issued_to" class="form-control form-control-custom" placeholder="Enter Full Name" required>
                </div>
                <div class="col-md-6">
                    <label>Sign (Digital Print)</label>
                    <input type="text" name="receiver_sign" class="form-control form-control-custom" placeholder="Type name to sign">
                </div>
            </div>

            <div class="text-muted small mb-4">
                <p><strong>Note:</strong> The receiver is solely responsible for all items issued under their name.</p>
            </div>

            <div class="d-flex justify-content-center gap-3 no-print mt-5">
                <a href="rent_requests.php" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="button" onclick="window.print()" class="btn btn-info text-white px-4">
                    <i class="fa fa-print me-2"></i> Print Slip
                </button>
                <button type="submit" name="submit_issue" class="btn btn-primary px-5">
                    <i class="fa fa-save me-2"></i> Save & Approve Slip
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'Includes/footer.php'; ?>