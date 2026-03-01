<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php
include 'admin/config/dbcon.php';

if (!isset($_SESSION['auth'])) {
    $_SESSION['message'] = "Please login to continue";
    header("Location: login.php");
    exit(0);
}

include "Includes/header.php";

// 1. Fetch User Data (Assuming one unique user ID)
$user_id = mysqli_real_escape_string($conn, $_SESSION['auth_user']['user_id']);
$user_info_fetch = "SELECT * FROM user WHERE id='$user_id' LIMIT 1";
$run_query = mysqli_query($conn, $user_info_fetch);
$user_data = mysqli_fetch_assoc($run_query);

// 2. Fetch Product Data if ID is passed via URL
$product_name = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $product_query = "SELECT product_name FROM products WHERE product_id = $id LIMIT 1";
    $product_result = mysqli_query($conn, $product_query);
    if ($product_row = mysqli_fetch_assoc($product_result)) {
        $product_name = $product_row['product_name'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Slip - BIT</title>
    <style>
        /* Keep your existing CSS here - it looks great */
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #f2f2f2;
        }

        .page {
            max-width: 210mm;
            margin: 15px auto;
            background: #fff;
            padding: 15mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            margin: 0;
            color: #1a1a1a;
        }

        .issue-badge {
            background: #000;
            color: #fff;
            padding: 5px 20px;
            font-weight: bold;
            margin-top: 10px;
            display: inline-block;
        }

        .row {
            margin-top: 15px;
        }

        .row.two {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        label {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: #555;
        }

        input {
            width: 100%;
            border: none;
            border-bottom: 1px solid #ccc;
            padding: 6px 0;
            font-size: 14px;
            outline: none;
        }

        input:focus {
            border-bottom: 1px solid #000;
        }

        input[readonly] {
            color: #666;
        }

        .table-wrap {
            margin-top: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 13px;
        }

        th {
            background: #f9f9f9;
        }

        .actions {
            text-align: center;
            margin-top: 30px;
            gap: 10px;
            display: flex;
            justify-content: center;
        }

        button {
            padding: 10px 25px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            font-weight: bold;
        }

        .btn-print {
            background: #555;
            color: white;
        }

        .btn-save {
            background: #28a745;
            color: white;
        }

        @media print {
            .no-print {
                display: none;
            }

            .page {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>

    <form method="post" action="request_save.php">
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
        <div class="page">
            <div class="header">
                <h1>BIRGUNJ INSTITUTE OF TECHNOLOGY</h1>
                <h3>Birgunj, Nepal</h3>
                <div class="issue-badge">ISSUE SLIP</div>
            </div>

            <div class="row two">
                <div>
                    <label>Purpose of Demand</label>
                    <input type="text" name="purpose" placeholder="e.g. Lab Experiment" required>
                </div>
                <div>
                    <label>Date</label>
                    <input type="date" name="issue_date" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="row two">
                <div>
                    <label>Faculty</label>
                    <input type="text" name="faculty" value="<?= htmlspecialchars($user_data['faculty'] ?? '') ?>"
                        readonly>
                </div>
                <div>
                    <label>Year / Part</label>
                    <input type="text" name="year_part" value="<?= htmlspecialchars($user_data['year_part'] ?? '') ?>"
                        readonly>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th width="50">S.N.</th>
                            <th>Name of Items</th>
                            <th>Specification</th>
                            <th width="80">Qty</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <tr>
                                <td><?= $i ?></td>
                                <td><input type="text" name="item_name[]"
                                        value="<?= ($i == 1) ? htmlspecialchars($product_name) : '' ?>"></td>
                                <td><input type="text" name="specification[]"></td>
                                <td><input type="number" name="quantity[]" min="1"></td>
                                <td><input type="text" name="remarks[]"></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <div class="row two">
                <div>
                    <label>Receiver's Name</label>
                    <input type="text" name="issued_to" value="<?= htmlspecialchars($user_data['name'] ?? '') ?>"
                        readonly>
                </div>
                <div>
                    <label>Sign / Digital Name</label>
                    <input type="text" name="receiver_sign" placeholder="Type name to sign">
                </div>
            </div>

            <div class="row">
                <label>Instructor's Name & Sign (Office Use)</label>
                <input type="text" name="instructor_name" readonly placeholder="To be filled by instructor">
            </div>

            <div class="actions no-print">

                <button type="submit" name="submit_request" class="btn-save">💾 Save Record</button>
            </div>
        </div>
    </form>

</body>

</html>
<?php include "Includes/bottom.php"; ?>