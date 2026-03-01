<?php
session_start();
include('admin/config/dbcon.php');

if (isset($_GET['email']) && isset($_GET['reset_token'])) {
    date_default_timezone_set('Asia/Kathmandu');
    $date = date('Y-m-d');
    $email = $_GET['email'];
    $reset_token = $_GET['reset_token'];

    // Handle form submission before any output
    if (isset($_POST['updatePass_btn'])) {
        $password = $_POST['password'];
        $cnfrmpassword = $_POST['cnfrmpassword'];

        if ($password == $cnfrmpassword) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $update_query = "UPDATE user SET password=?, resettoken=NULL, resettokenexpire=NULL WHERE email=?";
            $stmt_update = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt_update, "ss", $hashed_password, $email);

            if (mysqli_stmt_execute($stmt_update)) {
                mysqli_stmt_close($stmt_update);
                $_SESSION['message'] = "Password Updated Successfully!";
                header("Location: Login.php");
                exit(0);
            } else {
                $_SESSION['message'] = "Server Down! Try again later.";
                header("Location: updatePassword.php?email=$email&reset_token=$reset_token");
                exit(0);
            }
        } else {
            $_SESSION['message'] = "Password and Confirm Password do not match.";
            header("Location: updatePassword.php?email=$email&reset_token=$reset_token");
            exit(0);
        }
    }

    $pageTitle = "Update Password";
    include('Includes/header.php');

    $query = "SELECT * FROM user WHERE email=? AND resettoken=? AND resettokenexpire=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $email, $reset_token, $date);
    mysqli_stmt_execute($stmt);
    $query_run = mysqli_stmt_get_result($stmt);

    if ($query_run && mysqli_num_rows($query_run) == 1) {
?>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        --input-focus-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        --error-color: #ef4444;
        --success-color: #10b981;
    }

    .update-wrapper {
        background: var(--bg-secondary);
        min-height: calc(100vh - 80px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .update-container {
        background: var(--bg-elevated);
        max-width: 480px;
        width: 100%;
        padding: 45px;
        border-radius: 24px;
        box-shadow: 0 20px 50px var(--shadow-color);
        border: 1px solid var(--border-color);
        position: relative;
        overflow: hidden;
    }

    .update-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: var(--primary-gradient);
    }

    .update-header {
        text-align: center;
        margin-bottom: 35px;
    }

    .update-icon {
        width: 80px;
        height: 80px;
        background: var(--primary-gradient);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
    }

    .update-icon i {
        font-size: 35px;
        color: white;
    }

    .update-header h3 {
        font-size: 1.8rem;
        font-weight: 800;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 10px;
    }

    .update-header p {
        color: var(--text-muted);
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .form-group {
        margin-bottom: 22px;
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .input-wrapper {
        position: relative;
    }

    .form-control {
        width: 100%;
        padding: 14px 18px 14px 50px;
        border-radius: 12px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-primary);
        color: var(--text-primary);
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: var(--input-focus-shadow);
    }

    .input-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.25rem;
        color: var(--text-muted);
        transition: color 0.3s ease;
    }

    .input-wrapper:focus-within .input-icon {
        color: #6366f1;
    }

    .toggle-password {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: var(--text-muted);
        font-size: 1.1rem;
        transition: color 0.3s ease;
    }

    .toggle-password:hover {
        color: #6366f1;
    }

    .btn-primary {
        width: 100%;
        padding: 16px;
        font-size: 1.1rem;
        font-weight: 700;
        border-radius: 12px;
        background: var(--primary-gradient);
        border: none;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px -5px rgba(99, 102, 241, 0.5);
    }

    .back-link {
        text-align: center;
        margin-top: 25px;
    }

    .back-link a {
        color: var(--text-secondary);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: color 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .back-link a:hover {
        color: #6366f1;
    }

    .password-match-msg {
        font-size: 0.75rem;
        margin-top: 6px;
        display: none;
        font-weight: 500;
    }

    .password-match-msg.error {
        color: var(--error-color);
        display: block;
    }

    .password-match-msg.success {
        color: var(--success-color);
        display: block;
    }
</style>

<div class="update-wrapper">
    <div class="update-container" id="updateCard">
        <div class="update-header">
            <div class="update-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h3>Create New Password</h3>
            <p>Your new password must be different from previously used passwords and at least 8 characters long.</p>
        </div>

        <?php include('message.php'); ?>

        <form method="POST" id="updateForm">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            
            <div class="form-group">
                <label for="password">New Password</label>
                <div class="input-wrapper">
                    <input type="password" required placeholder="At least 8 characters" name="password" id="password" class="form-control" minlength="8">
                    <i class="bi bi-lock input-icon"></i>
                    <i class="bi bi-eye toggle-password" id="togglePass1"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="cnfrmpassword">Confirm New Password</label>
                <div class="input-wrapper">
                    <input type="password" required placeholder="Repeat your password" name="cnfrmpassword" id="cnfrmpassword" class="form-control">
                    <i class="bi bi-shield-check input-icon"></i>
                    <i class="bi bi-eye toggle-password" id="togglePass2"></i>
                </div>
                <div class="password-match-msg" id="matchMsg">Passwords do not match</div>
            </div>
            
            <button type="submit" name="updatePass_btn" class="btn-primary" id="submitBtn">
                <span>Reset Password</span>
                <i class="bi bi-arrow-right-circle"></i>
            </button>

            <div class="back-link">
                <a href="Login.php"><i class="bi bi-arrow-left"></i>Back to Login</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // GSAP Animation
        if (typeof gsap !== 'undefined') {
            gsap.from('#updateCard', { y: 40, opacity: 0, duration: 0.8, ease: "power3.out" });
        }

        // Toggle password visibility
        document.getElementById('togglePass1').addEventListener('click', function() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        document.getElementById('togglePass2').addEventListener('click', function() {
            const input = document.getElementById('cnfrmpassword');
            input.type = input.type === 'password' ? 'text' : 'password';
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        // Password match validation
        const password = document.getElementById('password');
        const confirm = document.getElementById('cnfrmpassword');
        const matchMsg = document.getElementById('matchMsg');

        const checkMatch = () => {
            if (confirm.value && password.value !== confirm.value) {
                matchMsg.classList.add('error');
                matchMsg.classList.remove('success');
                matchMsg.innerText = 'Passwords do not match';
                confirm.style.borderColor = '#ef4444';
            } else if (confirm.value && password.value === confirm.value) {
                matchMsg.classList.add('success');
                matchMsg.classList.remove('error');
                matchMsg.innerText = 'Passwords match!';
                confirm.style.borderColor = '#10b981';
            } else {
                matchMsg.classList.remove('error', 'success');
                confirm.style.borderColor = '';
            }
        };

        password.addEventListener('input', checkMatch);
        confirm.addEventListener('input', checkMatch);

        // Form submit loading state
        document.getElementById('updateForm').addEventListener('submit', function(e) {
            if (password.value !== confirm.value) {
                e.preventDefault();
                matchMsg.classList.add('error');
                if (typeof gsap !== 'undefined') {
                    gsap.to('#updateCard', { x: 10, duration: 0.1, repeat: 5, yoyo: true });
                }
            } else {
                const btn = document.getElementById('submitBtn');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
            }
        });
    });
</script>

<?php
    } else {
        $_SESSION['message'] = "Invalid or Expired Link.";
        header("Location: Login.php");
        exit(0);
    }
} else {
    header("Location: Login.php");
    exit(0);
}

include('Includes/bottom.php');
?>