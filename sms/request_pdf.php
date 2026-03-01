<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('admin/config/dbcon.php');

if (!isset($_GET['issue_id'])) {
    header("Location: index.php");
    exit();
}

$issue_id = mysqli_real_escape_string($conn, $_GET['issue_id']);

// Fetch the request data
$query = "SELECT * FROM issued_items_dets WHERE issue_id = '$issue_id' ORDER BY id ASC";
$query_run = mysqli_query($conn, $query);

if ($query_run && mysqli_num_rows($query_run) > 0) {
    $items = [];
    while ($row = mysqli_fetch_assoc($query_run)) {
        $items[] = $row;
    }
    // Header data from the first row
    $header = $items[0];
} else {
    echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h2 style='color:#d9534f;'>Request Not Found</h2>
            <p>Sorry, we could not find any records for Request ID: <strong>#".htmlspecialchars($issue_id)."</strong></p>
            <p>This might happen if the request wasn't saved correctly or was deleted.</p>
            <br>
            <a href='index.php' style='padding:10px 20px; background:#0275d8; color:white; text-decoration:none; border-radius:4px;'>Go Back Home</a>
          </div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Request_Slip_<?= $issue_id ?></title>
    <!-- html2pdf.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }

        .pdf-content {
            width: 210mm;
            min-height: 297mm;
            /* padding: 20mm;
            margin: 10mm auto; */
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #1a1a1a;
        }

        .header h3 {
            margin: 5px 0;
            font-weight: 500;
            font-size: 16px;
            color: #666;
        }

        .badge {
            background: #000;
            color: #fff;
            padding: 8px 15px;
            display: inline-block;
            font-weight: bold;
            margin-top: 10px;
            font-size: 14px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #888;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 15px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px 8px;
            text-align: left;
            font-size: 13px;
        }

        th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
        }

        .signature-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-top: 40px;
        }

        .sig-box {
            border-top: 1px solid #333;
            text-align: center;
            padding-top: 10px;
            font-size: 13px;
            font-weight: bold;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(0, 0, 0, 0.03);
            white-space: nowrap;
            pointer-events: none;
        }

        /* Loader styles */
        #loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>

    <div id="loader">
        <div class="spinner"></div>
        <p style="margin-top: 15px; font-weight: 500;">Generating your PDF document...</p>
    </div>

    <div class="pdf-content" id="printable">
        <div class="watermark">BIT REQUEST SLIP</div>

        <div class="header">
            <h1>BIRGUNJ INSTITUTE OF TECHNOLOGY</h1>
            <h3>Birgunj, Nepal</h3>
            <div class="badge">RENT REQUEST SLIP</div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Receiver Name</span>
                <span class="info-value"><?= htmlspecialchars($header['issued_to']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Issue ID</span>
                <span class="info-value">#<?= htmlspecialchars($issue_id) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Faculty</span>
                <span class="info-value"><?= htmlspecialchars($header['faculty']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Date</span>
                <span class="info-value"><?= date('d-M-Y', strtotime($header['issue_date'])) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Year / Part</span>
                <span class="info-value"><?= htmlspecialchars($header['year_part']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Purpose</span>
                <span class="info-value"><?= htmlspecialchars($header['purpose']) ?></span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="40">SN</th>
                    <th>Name of Item</th>
                    <th>Specification</th>
                    <th width="60">Qty</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= htmlspecialchars($item['specification']) ?></td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td><?= htmlspecialchars($item['remarks']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php
                // Fill remaining rows if items count is low
                $remaining = 10 - count($items);
                for ($i = 0; $i < $remaining; $i++):
                    ?>
                    <tr style="height: 35px;">
                        <td><?= count($items) + $i + 1 ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <!-- <div class="footer">
            <p style="font-size: 11px; color: #777;"><strong>Note:</strong> Only the receiver is responsible for the
                items issued on behalf of their names.</p>

            <div class="signature-row">
                <div class="sig-box">
                    Receiver's Signature
                </div>
                <div class="sig-box">
                    Instructor's Name & Sign
                </div>
            </div>
        </div> -->
    </div>

    <script>
        window.onload = function () {
            // Wait slightly for any fonts/styles to settle
            setTimeout(function() {
                const element = document.getElementById('printable');
                const opt = {
                    margin: 0,
                    filename: 'BIT_Request_Slip_<?= $issue_id ?>.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true, logging: true },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };

                html2pdf().set(opt).from(element).save().then(() => {

                    document.getElementById('loader').innerHTML = `
                    <h4>Download Complete!</h4><p>You can close this tab now.</p><div style="margin-top:20px;"><button onclick="window.location.href='index.php'" style="padding: 10px 20px; cursor: pointer; background:#6c757d; color:white; border:none; border-radius:4px; margin-right:10px;">Close Tab</button><button onclick="location.reload()" style="padding: 10px 20px; cursor: pointer; background:#007bff; color:white; border:none; border-radius:4px;">Download Again</button></div>`;
                }).catch(err => {
                    console.error('PDF Error:', err);
                    document.getElementById('loader').innerHTML = '<h4>PDF Generation Failed</h4><p>There was an error generating your PDF. Please try again.</p><button onclick="location.reload()" style="padding: 8px 20px; cursor: pointer; margin-top:10px;">Retry Download</button>';
                });
            }, 1000); // 1 second delay
        };
    </script>
</body>

</html>