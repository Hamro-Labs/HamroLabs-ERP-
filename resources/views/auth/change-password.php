<?php
/**
 * Hamro ERP — Change Password Page
 * Platform Blueprint V3.0
 * 
 * @module SuperAdmin
 */

require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pageTitle = 'Change Password';
$activePage = 'change-password.php';
?>

<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<main class="main" id="mainContent">
    <div class="page fu">
        <div class="page-head">
            <div class="page-title-row">
                <div class="page-icon" style="background:rgba(231,76,60,0.1); color:var(--red);">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div>
                    <div class="page-title">Change Password</div>
                    <div class="page-sub">Update your account security credentials.</div>
                </div>
            </div>
        </div>

        <div class="card" style="max-width: 600px; margin: 0 auto; text-align: center; padding: 40px 20px;">
            <div style="font-size: 40px; color: var(--green); margin-bottom: 20px;">
                <i class="fa-solid fa-envelope-circle-check"></i>
            </div>
            <h3 style="margin-bottom: 10px; color: var(--text-dark);">Password Reset</h3>
            <p style="color: var(--text-body); font-size: 13px; margin-bottom: 20px;">
                For security reasons, changing your password is done via a secure reset link sent to your registered email address. Ensure your SMTP settings are configured properly.
            </p>
            
            <div style="text-align:left; margin-bottom:20px;">
                <label style="display:block; font-size:12px; font-weight:600; color:var(--text-dark); margin-bottom:5px;">Registered Email Address</label>
                <input type="email" id="resetEmail" class="form-control" placeholder="Enter your email to receive OTP" style="width:100%; padding:10px; border:1px solid var(--card-border); border-radius:8px;">
            </div>

            <button class="btn bt" style="width:100%;" id="btnSndPwd" onclick="sendPasswordResetEmail()">
                <i class="fa-solid fa-paper-plane"></i> Send Reset OTP
            </button>
            
            <div style="margin-top:20px; font-size:12px;">
                <a href="javascript:void(0)" onclick="goNav('settings', 'email-cfg')" style="color:var(--text-light); text-decoration:underline;">
                    <i class="fa-solid fa-gear"></i> Configure SMTP Settings
                </a>
            </div>
        </div>
    </div>
</main>

<script>
function sendPasswordResetEmail() {
    const email = document.getElementById('resetEmail').value;
    if (!email) {
        SuperAdmin.showNotification('Please enter your email address', 'warning');
        return;
    }
    // Simulate API call
    SuperAdmin.showNotification('Password reset OTP sent to ' + email, 'success');
}
</script>

<?php include 'footer.php'; ?>
