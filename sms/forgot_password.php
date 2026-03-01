<?php
session_start();
// Generate CSRF token if not present
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = "Forgot Password";
include('Includes/header.php');
?>
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        --input-focus-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }

    .forgot-wrapper {
        background: var(--bg-secondary);
        min-height: calc(100vh - 80px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .forgot-container {
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

    .forgot-container::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: var(--primary-gradient);
    }

    .forgot-header {
        text-align: center;
        margin-bottom: 35px;
    }

    .forgot-icon {
        width: 80px;
        height: 80px;
        background: var(--primary-gradient);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        transform: rotate(-10deg);
    }

    .forgot-icon i {
        font-size: 35px;
        color: white;
    }

    .forgot-header h3 {
        font-size: 1.8rem;
        font-weight: 800;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 12px;
    }

    .forgot-header p {
        color: var(--text-muted);
        font-size: 0.95rem;
        line-height: 1.6;
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
        font-size: 1.2rem;
        color: var(--text-muted);
        transition: color 0.3s ease;
    }

    .form-control:focus + .input-icon {
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

    .back-link {
        text-align: center;
        margin-top: 30px;
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
</style>

<div class="forgot-wrapper">
    <div class="forgot-container" id="forgotCard">
        <div class="forgot-header">
            <div class="forgot-icon">
                <i class="bi bi-key-fill"></i>
            </div>
            <h3>Verify Identity</h3>
            <p>Don't worry, it happens to the best of us. Enter your email to receive a secure reset link.</p>
        </div>

        <?php include('message.php'); ?>

        <form action="forgotCode.php" method="POST" id="forgotForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <div class="form-group">
                <label for="email">Institutional Email</label>
                <div class="input-wrapper">
                    <input type="email" name="email" class="form-control" id="email" placeholder="name@bit.edu.np" required>
                    <i class="bi bi-envelope input-icon"></i>
                </div>
            </div>

            <button type="submit" name="forgot_btn" class="btn btn-primary" id="submitBtn">
                <span id="btnText">Request Reset Link</span>
                <i class="bi bi-send-fill" id="btnIcon"></i>
            </button>

            <div class="back-link">
                <a href="Login.php"><i class="bi bi-arrow-left"></i>Back to secure login</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof gsap !== 'undefined') {
            gsap.from('#forgotCard', {
                y: 30,
                opacity: 0,
                duration: 0.8,
                ease: "power3.out"
            });
            gsap.from('.forgot-icon', {
                scale: 0.5,
                rotate: -45,
                opacity: 0,
                duration: 1,
                ease: "back.out(1.7)",
                delay: 0.4
            });
        }

        const form = document.getElementById('forgotForm');
        form.addEventListener('submit', () => {
            const btn = document.getElementById('submitBtn');
            const icon = document.getElementById('btnIcon');
            const text = document.getElementById('btnText');
            
            btn.style.opacity = '0.8';
            btn.style.pointerEvents = 'none';
            text.innerText = 'Transmitting...';
            icon.className = 'spinner-border spinner-border-sm';
        });
    });
</script>


<?php include('Includes/bottom.php'); ?>
