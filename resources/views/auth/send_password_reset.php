<?php
require_once __DIR__ . '/../../../config/config.php';

header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$userEmail = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
if (empty($userEmail)) {
    echo json_encode(['success' => false, 'message' => 'Email address is required.']);
    exit;
}

try {
    $db = getDBConnection();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id, tenant_id, role, name FROM users WHERE email = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$userEmail]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // For security, don't reveal if email exists, but here we provide feedback as requested
        echo json_encode(['success' => false, 'message' => 'This email address is not registered in our system.']);
        exit;
    }

    $userId = $user['id'];
    $tenantId = $user['tenant_id'];
    $role = $user['role'];
    $userName = $user['name'] ?? 'User';

    // Generate a secure 6-digit OTP
    $resetToken = sprintf("%06d", random_int(100000, 999999));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    // Insert into password_resets table
    $insertStmt = $db->prepare("INSERT INTO password_resets (tenant_id, user_id, role, email, token, expires_at) VALUES (?, ?, ?, ?, ?, ?)");
    $insertStmt->execute([$tenantId, $userId, $role, $userEmail, $resetToken, $expiresAt]);

    $mail = new PHPMailer(true);

    // Setup SMTP from config if available, else use defaults
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'pdewbrath@gmail.com'; 
    $mail->Password   = 'tuma ezat qmap vyxk'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('infohamrolabs@gmail.com', APP_NAME . ' Security');
    $mail->addAddress($userEmail, $userName);     

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset OTP - Hamro Academic ERP';
    
    $resetLink = APP_URL . "/auth/reset-password?email=" . urlencode($userEmail) . "&token=" . $resetToken;
    
    $emailBody = "
    <div style='font-family: \"Poppins\", Arial, sans-serif; padding: 40px; max-width:600px; margin:0 auto; background-color:#ffffff; border-radius:12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); color:#334155; border: 1px solid #e2e8f0;'>
        <div style='text-align:center; margin-bottom:30px;'>
            <h2 style='color:#006D44; margin:0; font-size:24px;'>Password Reset Request</h2>
        </div>
        <p>Hello <strong>{$userName}</strong>,</p>
        <p>We received a request to reset your password. Use the verification code below to proceed with the reset process. This code is valid for <strong>30 minutes</strong>.</p>
        
        <div style='text-align:center; margin: 40px 0;'>
            <div style='display:inline-block; padding:20px 40px; background-color:#f0fdf4; color:#006D44; border:2px dashed #006D44; font-size:36px; letter-spacing:10px; font-weight:800; border-radius:12px;'>{$resetToken}</div>
            <p style='margin-top:20px; font-size:13px; color:#64748b;'>Alternatively, you can click the button below:</p>
            <a href='{$resetLink}' style='display:inline-block; margin-top:10px; padding:12px 30px; background-color:#006D44; color:#ffffff; text-decoration:none; border-radius:8px; font-weight:600;'>Reset Password Now</a>
        </div>
        
        <p style='font-size:14px; line-height:1.6;'>If you didn't request a password reset, please ignore this email or contact support if you have concerns about your account security.</p>
        
        <hr style='border:0; border-top:1px solid #e2e8f0; margin:30px 0;'>
        <p style='font-size:12px; color:#94a3b8; text-align:center;'>
            &copy; " . date('Y') . " Hamro Labs. All rights reserved.<br>
            Secure Academic Management Platform
        </p>
    </div>
    ";
    
    $mail->Body    = $emailBody;
    $mail->AltBody = "Your Password Reset OTP is: {$resetToken}. Alternatively, copy and paste this link: {$resetLink}";

    $mail->send();

    echo json_encode(['success' => true, 'message' => 'A 6-digit verification code has been sent to your email!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "We couldn't send the reset email. Please try again later."]);
}
?>
