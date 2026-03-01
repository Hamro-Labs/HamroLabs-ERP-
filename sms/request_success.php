<?php include('includes/header.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Submitted</title>

    <link rel="stylesheet" href="admin/CSS/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body class="text-center">

    <div class="container mt-5" style="max-width:600px; margin:auto; padding:28px; background:#1e1e2f; color:#fff; border-radius:12px; box-shadow:0 5px 14px rgba(0,0,0,0.25);">

        <div style="font-size:62px; margin-bottom:14px; color:#0d6efd;">
            <i class="bi bi-send-check-fill"></i>
        </div>

        <h4 style="font-weight:600; margin-bottom:10px;">
            <i class="bi bi-check-circle-fill" style="color:#4CAF50;"></i> Your request has been submitted successfully
        </h4>

        <p style="font-size:16px; opacity:0.88; margin-bottom:22px;">
            <i class="bi bi-shield-lock-fill"></i> Please wait for admin approval.
        </p>

        <div style="margin:24px 0;">
            <h5 style="font-weight:500; margin-bottom:12px;">
                <i class="bi bi-file-earmark-arrow-down-fill"></i> Download Your Request Form
            </h5>
            <a href="request_pdf.php?issue_id=<?= isset($_GET['issue_id']) ? $_GET['issue_id'] : '' ?>" style="display:inline-block; padding:10px 20px; background:#0d6efd; color:#fff; text-decoration:none; border-radius:6px; font-size:15px;">
                <i class="bi bi-download"></i> Download PDF
            </a>
        </div>

        <div style="margin-top:20px;">
            <a href="index.php" style="display:inline-block; padding:9px 18px; background:#fff; color:#1e1e2f; text-decoration:none; border-radius:6px; font-size:15px; font-weight:500;">
                <i class="bi bi-house-door-fill"></i> Go to Home
            </a>
        </div>

    </div>

<?php include('includes/bottom.php'); ?>
