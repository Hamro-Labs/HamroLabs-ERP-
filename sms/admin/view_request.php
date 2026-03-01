<?php
include('config/dbcon.php');
include('includes/header.php');

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // 1. Fetch the reference item to get the common issue_id
    $ref_query = "SELECT * FROM issued_items_dets WHERE id = '$id' LIMIT 1";
    $ref_run = mysqli_query($conn, $ref_query);

    if (mysqli_num_rows($ref_run) > 0) {
        $ref_row = mysqli_fetch_assoc($ref_run);
        $issue_id = $ref_row['issue_id'];

        // 2. Fetch ALL items for this specific slip (share the same issue_id)
        $query = "SELECT * FROM issued_items_dets WHERE issue_id = '$issue_id' ORDER BY id ASC";
        $query_run = mysqli_query($conn, $query);

        // Use the first row for header data
        $row = $ref_row;
    } else {
        echo "<h4>No Record Found</h4>";
        exit();
    }
} else {
    echo "<h4>ID Missing from URL</h4>";
    exit();
}
?>

<style>
    body {
        background: #f4f6f9;
    }

    .view-container {
        max-width: 900px;
        margin: 30px auto;
        background: #fff;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .1);
        position: relative;
    }

    @media print {
        @page {
            size: A4;
            margin: 15mm;
        }

        body * {
            visibility: hidden;
        }

        .print-only,
        .print-only * {
            visibility: visible;
        }

        .print-only {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        body {
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .no-print {
            display: none !important;
        }

        .view-container {
            box-shadow: none;
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: 100%;
            border-radius: 0;
        }

        /* Print-specific styles matching the image */
        .print-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .print-header #print-name {
            text-transform: uppercase;
            font-weight: bold;
        }

        .print-header h2 {
            font-family: 'Times New Roman', Times, serif;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 0;
            padding: 0;
        }

        .print-header .location {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            margin: 2px 0;
        }

        .print-header .slip-title {
            background: #000;
            color: #fff;
            display: inline-block;
            padding: 4px 20px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 8px;
        }

        .print-info-section {
            margin: 15px 0;
            font-family: 'Times New Roman', Times, serif;
        }

        .print-info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .print-info-row .label {
            font-weight: bold;
        }

        .print-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
        }

        .print-table th,
        .print-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
        }

        .print-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .print-table td {
            min-height: 30px;
        }

        .print-table .sn-col {
            width: 8%;
            text-align: center;
        }

        .print-table .name-col {
            width: 35%;
        }

        .print-table .spec-col {
            width: 25%;
        }

        .print-table .qty-col {
            width: 12%;
            text-align: center;
        }

        .print-table .remarks-col {
            width: 20%;
        }

        .print-footer {
            margin-top: 20px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
        }

        .print-footer .receiver-section {
            margin-bottom: 15px;
        }

        .print-footer .receiver-section .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }

        .print-footer .sign-label {
            display: inline-block;
            margin-left: 200px;
            font-weight: bold;
        }

        .print-footer .note {
            margin-top: 15px;
            font-style: italic;
            font-size: 11px;
        }

        .print-footer .note-label {
            font-weight: bold;
        }

    }

    /* Screen view styles */
    .info-label {
        font-weight: bold;
        color: #555;
        text-transform: uppercase;
        font-size: 0.85rem;
    }

    .info-value {
        border-bottom: 1px dashed #ccc;
        padding: 5px 0;
        margin-bottom: 15px;
        min-height: 32px;
        color: #333;
    }
</style>

<div class="container">
    <div class="no-print mt-3">
        <a href="rent_requests.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
        <button onclick="window.print()" class="btn btn-primary shadow"><i class="fa-solid fa-print"></i> Print
            Slip</button>
    </div>

    <div class="view-container">
        <!-- Screen View -->
        <div class="screen-only">
            <div class="text-center mb-5">
                <h3 class="fw-bold mb-0">BIRGUNJ INSTITUTE OF TECHNOLOGY</h3>
                <p class="text-muted">Birgunj, Nepal</p>
                <div class="d-inline-block px-4 py-1 bg-dark text-white rounded-pill fw-bold">ISSUE SLIP (OFFICIAL
                    COPY)</div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <p class="info-label">User ID / Registration No.</p>
                    <div class="info-value"><?php echo $row['user_id']; ?></div>
                </div>
                <div class="col-md-6">
                    <p class="info-label">Date of Issue</p>
                    <div class="info-value"><?php echo date('F d, Y', strtotime($row['issue_date'])); ?></div>
                </div>
                <div class="col-md-6" id="print-name">
                    <p class="info-label">Full Name</p>
                    <div class="info-value"><?php echo $row['issued_to']; ?></div>
                </div>
                <div class="col-md-6">
                    <p class="info-label">Faculty / Department</p>
                    <div class="info-value"><?php echo $row['faculty']; ?></div>
                </div>
                <div class="col-md-6">
                    <p class="info-label">Year / Part</p>
                    <div class="info-value"><?php echo $row['year_part']; ?></div>
                </div>
                <div class="col-md-6">
                    <p class="info-label">Status</p>
                    <div class="info-value">
                        <span
                            class="badge <?php echo ($row['issue_status'] == 'Approved') ? 'bg-success' : 'bg-warning text-dark'; ?>">
                            <?php echo $row['issue_status'] ?? 'Pending'; ?>
                        </span>
                    </div>
                </div>
                <div class="col-12 mt-2">
                    <p class="info-label">Purpose of Demand</p>
                    <div class="info-value"><?php echo $row['purpose']; ?></div>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-bordered border-dark">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 40%;">Name of Items</th>
                            <th>Specification</th>
                            <th style="width: 10%;">Qty</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        mysqli_data_seek($query_run, 0); // Reset result pointer
                        while ($item = mysqli_fetch_assoc($query_run)): ?>
                            <tr>
                                <td class="text-center"><?php echo $item['item_name']; ?></td>
                                <td><?php echo $item['specification']; ?></td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td><?php echo $item['remarks']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="row mt-5">
                <!-- <div class="col-4 text-center mt-5">
                        <hr class="mx-auto" style="width: 80%; border-top: 1px solid black;">
                        <p class="fw-bold">Applicant's Signature</p>
                    </div>
                    <div class="col-4 text-center mt-5">
                    </div>
                    <div class="col-4 text-center mt-5">
                        <hr class="mx-auto" style="width: 80%; border-top: 1px solid black;">
                        <p class="fw-bold">Authorized Approval</p>
                    </div> -->

                <div class="d-flex align-items-center justify-content-center gap-3 mt-4">

                    <div class="approve">
                        <a href="rent_requests.php?approve_issue=<?php echo $row['issue_id']; ?>"
                            class="btn btn-sm btn-success <?php echo ($status == 'Approved') ? 'disabled' : ''; ?>"
                            title="Approve All Items"
                            onclick="return confirm('Approve entire request slip #<?php echo $row['issue_id']; ?>?')">
                            <i class="fa-solid fa-check"></i>Approve
                        </a>
                    </div>
                        <div class="delete"></div>
                    <a href="rent_requests.php?delete_issue=<?php echo $row['issue_id']; ?>"
                        class="btn btn-sm btn-danger" title="Delete Slip"
                        onclick="return confirm('Delete entire request slip and all its items?')">
                        <i class="fa-solid fa-trash"></i> Delete
                    </a>
                         </div>
            </div>


        </div>
    </div>

    <!-- Print View (matches the image format) -->
    <div class="print-only" style="display: none;">
        <div class="print-header">
            <h2>BIRGUNJ INSTITUTE OF TECHNOLOGY</h2>
            <p class="location">Birgunj, Nepal</p>
            <div class="slip-title">ISSUE SLIP</div>
        </div>

        <div class="print-info-section">
            <div class="print-info-row">
                <div><span class="label">Purpose of Demand:</span> <?php echo $row['purpose']; ?></div>
                <div><span class="label">Date :-</span>
                    <?php echo date('Y-m-d', strtotime($row['issue_date'])); ?></div>
            </div>
            <div class="print-info-row">
                <div><span class="label">Faculty :</span> <?php echo $row['faculty']; ?></div>
                <div><span class="label">Year / Part :-</span> <?php echo $row['year_part']; ?></div>
            </div>
        </div>

        <table class="print-table">
            <thead>
                <tr>
                    <th class="sn-col">S.N.</th>
                    <th class="name-col">Name of Items</th>
                    <th class="spec-col">Specification</th>
                    <th class="qty-col">Quantity</th>
                    <th class="remarks-col">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sn = 1;
                mysqli_data_seek($query_run, 0);
                while ($item = mysqli_fetch_assoc($query_run)): ?>
                    <tr>
                        <td class="sn-col"><?php echo $sn++; ?>.</td>
                        <td class="name-col"><?php echo $item['item_name']; ?></td>
                        <td class="spec-col"><?php echo $item['specification']; ?></td>
                        <td class="qty-col"><?php echo $item['quantity']; ?></td>
                        <td class="remarks-col"><?php echo $item['remarks']; ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php for ($i = $sn; $i <= 15; $i++): ?>
                    <tr>
                        <td class="sn-col"><?php echo $i; ?>.</td>
                        <td class="name-col">&nbsp;</td>
                        <td class="spec-col">&nbsp;</td>
                        <td class="qty-col">&nbsp;</td>
                        <td class="remarks-col">&nbsp;</td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <div class="print-footer">
            <div class="receiver-section">
                <span class="label">Receiver's Name :-<?php echo $row['issued_to']; ?></span>
                <span class="sign-label">Sign :-</span>
            </div>
            <div>
                <div>1.</div>
                <div>2.</div>
                <div>3.</div>
                <div>4.</div>
            </div>
            <div class="note">
                <span class="note-label">Note :-</span> Only Receiver's are responsible for the items issued on
                behalf of their names.<br>
                <span style="margin-left: 45px;">Instructro's name & sign :-</span>
            </div>
        </div>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Show print-only content only when printing
    window.addEventListener('beforeprint', function () {
        document.querySelector('.screen-only').style.display = 'none';
        document.querySelector('.print-only').style.display = 'block';
    });

    window.addEventListener('afterprint', function () {
        document.querySelector('.screen-only').style.display = 'block';
        document.querySelector('.print-only').style.display = 'none';
    });
</script>

