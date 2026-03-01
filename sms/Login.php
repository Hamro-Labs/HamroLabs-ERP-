<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('admin/config/dbcon.php');

$error = '';

// If already logged in
if (isset($_SESSION['auth']) && $_SESSION['auth'] === true) {
    header("Location: index.php");
    exit;
}

// Handle form submission
if (isset($_POST['login_btn'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } else {
        $sql = "SELECT * FROM user WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $query = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($query) == 1) {
            $user_info = mysqli_fetch_assoc($query);

            // Check password (supports both hash and legacy plain text for migration)
            $auth_success = false;
            if (password_verify($password, $user_info['password'])) {
                $auth_success = true;
            } elseif ($user_info['password'] === $password) {
                $auth_success = true;
                // Optionally hash the password on successful login
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $update_sql = "UPDATE user SET password=? WHERE id=?";
                $update_stmt = mysqli_prepare($conn, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "si", $hashed, $user_info['id']);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
            }

            if ($auth_success) {
                $_SESSION['auth'] = true;
                $_SESSION['auth_user'] = [
                    'user_id' => $user_info['id'],
                    'user_name' => $user_info['name'],
                    'user_email' => $user_info['email'],
                    'role' => $user_info['role']
                ];
               

                $_SESSION['message'] = "Welcome back, " . $user_info['name'] . "!";
                if ($user_info['role'] == 'Admin' || $user_info['role'] == 'admin') {
                    header("Location: admin/index.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $error = "Invalid password. Please try again.";
            }

        } else {
            $error = "No account found with this email address.";
        }
    }
}

$pageTitle = "Login";
include('Includes/header.php');

// Get current theme
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
?>

<!-- Login Content -->
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        --input-focus-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        --error-color: #ef4444;
    }

    .login-wrapper {
        background: var(--bg-secondary);
        min-height: calc(100vh - 80px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .login-container {
        background: var(--bg-elevated);
        max-width: 450px;
        width: 100%;
        padding: 45px;
        border-radius: 24px;
        box-shadow: 0 20px 50px var(--shadow-color);
        border: 1px solid var(--border-color);
        position: relative;
        overflow: hidden;
    }

    .login-container::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: var(--primary-gradient);
    }

    .login-header {
        text-align: center;
        margin-bottom: 35px;
    }

    .login-icon-box {
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

    .login-icon-box i {
        font-size: 35px;
        color: white;
    }

    .login-header h3 {
        font-size: 2rem;
        font-weight: 800;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 10px;
    }

    .login-header p {
        color: var(--text-muted);
        font-size: 1rem;
        font-weight: 500;
    }

    .form-group {
        margin-bottom: 24px;
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
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.25rem;
        color: var(--text-muted);
        transition: color 0.3s ease;
    }

    .form-control:focus + .input-icon {
        color: #6366f1;
    }

    .toggle-password {
        position: absolute;
        right: 18px;
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
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.4);
    }

    .forgot-password {
        text-align: right;
        margin-top: 10px;
        margin-bottom: 25px;
    }

    .forgot-password a {
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .forgot-password a:hover {
        color: #6366f1;
    }

    .divider {
        display: flex;
        align-items: center;
        margin: 30px 0;
    }

    .divider::before, .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-color);
    }

    .divider span {
        padding: 0 15px;
        color: var(--text-muted);
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .signup-footer {
        text-align: center;
    }

    .signup-footer p {
        color: var(--text-muted);
        margin-bottom: 15px;
        font-size: 0.95rem;
    }

    .signup-btn-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 30px;
        border-radius: 30px;
        background: var(--bg-secondary);
        color: var(--text-primary);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .signup-btn-link:hover {
        background: var(--primary-gradient);
        color: white;
        border-color: transparent;
        transform: translateY(-1px);
    }
</style>

<div class="login-wrapper">
    <div class="login-container" id="loginCard">
        <div class="login-header">
            <div class="login-icon-box">
                <i class="bi bi-person-lock"></i>
            </div>
            <h3>Welcome Back</h3>
            <p>Access your StoreMart secure panel</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="border-radius: 12px; padding: 15px; margin-bottom: 25px; background: rgba(239, 68, 68, 0.1); color: var(--error-color); border: 1px solid rgba(239, 68, 68, 0.2); font-weight: 500;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php include "message.php"; ?>

        <form action="" method="POST" id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <i class="bi bi-envelope-at input-icon"></i>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 10px;">
                <label for="password">Security Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" class="form-control" id="password" placeholder="••••••••" required>
                    <i class="bi bi-shield-lock input-icon"></i>
                    <i class="bi bi-eye toggle-password" id="togglePass"></i>
                </div>
            </div>

            <div class="forgot-password">
                <a href="forgot_password.php">Recover credentials?</a>
            </div>

            <button type="submit" name="login_btn" class="btn-primary" id="loginBtn">
                <span id="btnText">Sign In Securely</span>
                <i class="bi bi-arrow-right-short" style="font-size: 1.4rem;"></i>
            </button>

            <div class="divider">
                <span>Or</span>
            </div>

            <div class="signup-footer">
                <p>New to Birgunj Institute of Technology?</p>
                <a href="SignUp.php" class="signup-btn-link">
                    <i class="bi bi-person-plus"></i>
                    Enroll Now
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof gsap !== 'undefined') {
            gsap.from('#loginCard', {
                y: 40,
                opacity: 0,
                duration: 1,
                ease: "expo.out"
            });
        }

        const togglePass = document.getElementById('togglePass');
        const passInput = document.getElementById('password');
        
        togglePass.addEventListener('click', () => {
            const type = passInput.type === 'password' ? 'text' : 'password';
            passInput.type = type;
            togglePass.classList.toggle('bi-eye');
            togglePass.classList.toggle('bi-eye-slash');
        });

        const form = document.getElementById('loginForm');
        form.addEventListener('submit', () => {
            const btn = document.getElementById('loginBtn');
            const text = document.getElementById('btnText');
            btn.style.opacity = '0.8';
            btn.style.pointerEvents = 'none';
            text.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Authenticating...';
        });
    });
</script>


<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<?php include "Includes/bottom.php"; ?>