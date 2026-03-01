<?php
include("config/dbcon.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['auth']) || !in_array($_SESSION['auth_user']['role'], ['Admin', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

include("Includes/header.php");

$message = "";
$error = "";

// Handle reply submission
if (isset($_POST['send_reply'])) {
    $id = intval($_POST['id']);
    $recipient_email = filter_var($_POST['recipient_email'], FILTER_SANITIZE_EMAIL);
    $recipient_name = htmlspecialchars($_POST['recipient_name']);
    $original_subject = htmlspecialchars($_POST['original_subject']);
    $reply_message = htmlspecialchars($_POST['reply_message']);

    // Validate inputs
    if (empty($reply_message)) {
        $error = "Reply message cannot be empty.";
    } elseif (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        // Create reply subject
        $reply_subject = "Re: " . $original_subject;

        // Create email body
        $email_body = "Dear $recipient_name,\n\n";
        $email_body .= "Thank you for contacting Birgunj Institute of Technology Store Management System.\n\n";
        $email_body .= "Your message: " . htmlspecialchars($_POST['original_message']) . "\n\n";
        $email_body .= "Our Response:\n" . $reply_message . "\n\n";
        $email_body .= "Best regards,\nBIT Store Management Team\n\n";
        $email_body .= "--- This is an automated response ---";

        // Since we can't send actual emails without mail server configuration,
        // we'll save the reply to the database and show a success message

        // Update the contact record with reply
        $update_query = "UPDATE contact SET reply_message = ?, replied_at = NOW(), replied_by = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        $admin_name = $_SESSION['auth_user']['user_name'] ?? 'Admin';
        mysqli_stmt_bind_param($stmt, "ssi", $reply_message, $admin_name, $id);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Reply saved successfully! (Email would be sent to: $recipient_email)";

            // Log the action
            $log_query = "INSERT INTO activity_log (user_id, action, details) VALUES (?, 'Reply to Message', ?)";
            $stmt_log = mysqli_prepare($conn, $log_query);
            $user_id = $_SESSION['auth_user']['user_id'] ?? 0;
            $details = "Replied to message from $recipient_email";
            mysqli_stmt_bind_param($stmt_log, "is", $user_id, $details);
            mysqli_stmt_execute($stmt_log);
        } else {
            $error = "Failed to save reply: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch message details
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM contact WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    } else {
        echo "<div class='container mt-4'><div class='alert alert-danger'>No record found with ID: $id</div></div>";
        include("Includes/footer.php");
        exit();
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<div class='container mt-4'><div class='alert alert-danger'>ID parameter is missing</div></div>";
    include("Includes/footer.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Message</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .reply-container {
            max-width: 800px;
            margin: 40px auto;
        }

        .original-message {
            background-color: #e9ecef;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin-bottom: 20px;
        }

        .reply-form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <div class="container reply-container">
        <a href="suggestions.php" class="btn btn-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Back to Messages
        </a>

        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Original Message -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-envelope-open me-2"></i>Original Message
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong><i class="bi bi-person me-1"></i>From:</strong>
                        <?php echo htmlspecialchars($row['name']); ?>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="bi bi-envelope me-1"></i>Email:</strong>
                        <a
                            href="mailto:<?php echo htmlspecialchars($row['email']); ?>"><?php echo htmlspecialchars($row['email']); ?></a>
                    </div>
                </div>
                <div class="mb-3">
                    <strong><i class="bi bi-chat-left-text me-1"></i>Subject:</strong>
                    <?php echo htmlspecialchars($row['subject']); ?>
                </div>
                <div class="mb-3">
                    <strong><i class="bi bi-calendar me-1"></i>Received:</strong>
                    <?php echo isset($row['created_at']) ? date('d M Y, h:i A', strtotime($row['created_at'])) : 'N/A'; ?>
                </div>
                <hr>
                <div class="original-message">
                    <strong>Message:</strong><br>
                    <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                </div>

                <?php if (!empty($row['reply_message'])): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-reply me-1"></i>
                        <strong>Already Replied:</strong>
                        <?php echo nl2br(htmlspecialchars($row['reply_message'])); ?>
                        <br><small class="text-muted">
                            Replied on:
                            <?php echo isset($row['replied_at']) ? date('d M Y, h:i A', strtotime($row['replied_at'])) : 'N/A'; ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reply Form -->
        <div class="reply-form">
            <h4 class="mb-4"><i class="bi bi-reply-all me-2"></i>Send Reply</h4>

            <form method="post">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <input type="hidden" name="recipient_email" value="<?php echo htmlspecialchars($row['email']); ?>">
                <input type="hidden" name="recipient_name" value="<?php echo htmlspecialchars($row['name']); ?>">
                <input type="hidden" name="original_subject" value="<?php echo htmlspecialchars($row['subject']); ?>">
                <input type="hidden" name="original_message" value="<?php echo htmlspecialchars($row['message']); ?>">

                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-person me-1"></i>To:</label>
                    <input type="text" class="form-control"
                        value="<?php echo htmlspecialchars($row['name']); ?> (<?php echo htmlspecialchars($row['email']); ?>)"
                        readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-envelope me-1"></i>Subject:</label>
                    <input type="text" class="form-control" value="Re: <?php echo htmlspecialchars($row['subject']); ?>"
                        readonly>
                </div>

                <div class="mb-3">
                    <label for="reply_message" class="form-label"><i class="bi bi-chat-text me-1"></i>Your Reply <span
                            class="text-danger">*</span></label>
                    <textarea class="form-control" id="reply_message" name="reply_message" rows="6"
                        placeholder="Type your response here..."
                        required><?php echo isset($_POST['reply_message']) ? htmlspecialchars($_POST['reply_message']) : ''; ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="send_reply" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Send Reply
                    </button>
                    <button type="button" onclick="window.location.href='suggestions.php';" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php include("Includes/footer.php"); ?>