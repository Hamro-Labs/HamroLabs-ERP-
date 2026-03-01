<?php
session_start();
include('admin/config/dbcon.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Email configuration - UPDATE THESE WITH YOUR CREDENTIALS
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'your-email@gmail.com');  // Your Gmail address
define('SMTP_PASSWORD', 'your-app-password');      // Your Gmail App Password (not regular password)
define('SMTP_PORT', 465);
define('SMTP_FROM_NAME', 'StoreMart - BIT');
define('SITE_URL', 'http://localhost/sms');

// Set to false for local testing (shows link on page), true to send actual emails
define('SEND_EMAIL', false);

function sendPasswordResetEmail($email, $reset_token, $user_name = 'User')
{
    require_once 'PHPMailer/PHPMailer.php';
    require_once 'PHPMailer/SMTP.php';
    require_once 'PHPMailer/Exception.php';

    $mail = new PHPMailer(true);
    $reset_link = SITE_URL . "/updatePassword.php?email=" . urlencode($email) . "&reset_token=" . $reset_token;

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress($email);
        $mail->addReplyTo(SMTP_USERNAME, SMTP_FROM_NAME);

        // Content
        $mail->isHTML(true);
        $mail->Subject = '🔐 Password Reset Request - StoreMart';

        // Professional HTML Email Template
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen, Ubuntu, sans-serif; background-color: #f3f4f6;">
            <table role="presentation" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td align="center" style="padding: 40px 0;">
                        <table role="presentation" style="width: 600px; max-width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                            
                            <!-- Header with Gradient -->
                            <tr>
                                <td style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); padding: 40px 30px; text-align: center;">
                                    <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;">
                                        🔐 Password Reset
                                    </h1>
                                    <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 16px;">
                                        StoreMart - Birgunj Institute of Technology
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Main Content -->
                            <tr>
                                <td style="padding: 40px 30px;">
                                    <p style="color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                                        Hello <strong>' . htmlspecialchars($user_name) . '</strong>,
                                    </p>
                                    
                                    <p style="color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 25px;">
                                        We received a request to reset your password for your StoreMart account. Click the button below to create a new password:
                                    </p>
                                    
                                    <!-- CTA Button -->
                                    <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td align="center" style="padding: 20px 0;">
                                                <a href="' . $reset_link . '" style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 30px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);">
                                                    Reset My Password
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <!-- Security Notice -->
                                    <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px 20px; border-radius: 8px; margin: 25px 0;">
                                        <p style="color: #92400e; font-size: 14px; margin: 0; line-height: 1.5;">
                                            <strong>⚠️ Security Notice:</strong> This link will expire at the end of today. If you didn\'t request this password reset, please ignore this email or contact support.
                                        </p>
                                    </div>
                                    
                                    <p style="color: #6b7280; font-size: 14px; line-height: 1.6; margin: 20px 0 0;">
                                        If the button doesn\'t work, copy and paste this link into your browser:
                                    </p>
                                    <p style="color: #6366f1; font-size: 12px; word-break: break-all; background: #f3f4f6; padding: 12px; border-radius: 8px; margin: 10px 0;">
                                        ' . $reset_link . '
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="background-color: #f9fafb; padding: 25px 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                                    <p style="color: #6b7280; font-size: 13px; margin: 0 0 10px;">
                                        © ' . date('Y') . ' StoreMart - Birgunj Institute of Technology
                                    </p>
                                    <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                                        This is an automated message. Please do not reply directly to this email.
                                    </p>
                                </td>
                            </tr>
                            
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';

        // Plain text fallback
        $mail->AltBody = "Hello $user_name,\n\nWe received a request to reset your password. Click the link below to reset it:\n\n$reset_link\n\nThis link will expire at the end of today.\n\nIf you didn't request this, please ignore this email.\n\n- StoreMart Team";

        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Mailer Error: ' . $mail->ErrorInfo];
    }
}

if (isset($_POST['forgot_btn'])) {
    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = "Invalid request security token.";
        header("Location: forgot_password.php");
        exit();
    }

    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $forgot_query = "SELECT * FROM user WHERE email=? LIMIT 1";
    $stmt = mysqli_prepare($conn, $forgot_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $forgot_query_run = mysqli_stmt_get_result($stmt);

    if ($forgot_query_run) {
        if (mysqli_num_rows($forgot_query_run) == 1) {
            $user = mysqli_fetch_assoc($forgot_query_run);
            $user_name = $user['name'] ?? 'User';
            
            $reset_token = bin2hex(random_bytes(16));
            date_default_timezone_set('Asia/Kathmandu');
            $expire = date("Y-m-d");

            $query = "UPDATE user SET resettoken=?, resettokenexpire=? WHERE email=?";
            $stmt_update = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt_update, "sss", $reset_token, $expire, $email);

            if (mysqli_stmt_execute($stmt_update)) {
                mysqli_stmt_close($stmt_update);
                
                $reset_link = SITE_URL . "/updatePassword.php?email=" . urlencode($email) . "&reset_token=$reset_token";
                
                if (SEND_EMAIL) {
                    // Production mode - Send actual email
                    $result = sendPasswordResetEmail($email, $reset_token, $user_name);
                    
                    if ($result['success']) {
                        $_SESSION['message'] = "
                            <div style='text-align: center;'>
                                <i class='bi bi-envelope-check' style='font-size: 3rem; color: #10b981;'></i>
                                <h4 style='margin: 15px 0 10px; color: var(--text-primary);'>Email Sent Successfully!</h4>
                                <p>A password reset link has been sent to:<br><strong>" . htmlspecialchars($email) . "</strong></p>
                                <p style='color: var(--text-muted); font-size: 0.9rem;'>Please check your inbox (and spam folder) for the reset link.</p>
                            </div>
                        ";
                    } else {
                        $_SESSION['message'] = "Failed to send email. " . $result['message'];
                        header("Location: forgot_password.php");
                        exit();
                    }
                } else {
                    // Development mode - Show link on page
                    $_SESSION['message'] = "
                        <div style='text-align: center;'>
                            <i class='bi bi-tools' style='font-size: 2.5rem; color: #f59e0b;'></i>
                            <h4 style='margin: 15px 0 10px; color: var(--text-primary);'>Development Mode</h4>
                            <p style='margin-bottom: 15px;'>In production, this link would be sent to your email.</p>
                            <div style='background: var(--bg-secondary); padding: 15px; border-radius: 12px; margin: 15px 0;'>
                                <p style='margin: 0 0 10px; font-weight: 600;'>Reset Token:</p>
                                <code style='background: #1f2937; color: #10b981; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; word-break: break-all;'>$reset_token</code>
                            </div>
                            <a href='$reset_link' style='display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white; padding: 12px 24px; border-radius: 30px; text-decoration: none; font-weight: 600; margin-top: 10px; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);'>
                                <i class='bi bi-key-fill'></i> Reset Password Now
                            </a>
                        </div>
                    ";
                }
                
                header("Location: msg.php");
                exit();
            } else {
                $_SESSION['message'] = "Something went wrong! Please try again later.";
                header("Location: forgot_password.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "No account found with this email address.";
            header("Location: forgot_password.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Database error. Please try again.";
        header("Location: forgot_password.php");
        exit();
    }
} else {
    header("Location: forgot_password.php");
    exit();
}
?>