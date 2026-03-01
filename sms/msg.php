<?php
session_start();
$pageTitle = "System Notification";
include('Includes/header.php');
?>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    }

    .msg-wrapper {
        background: var(--bg-secondary);
        min-height: calc(100vh - 80px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .msg-container {
        background: var(--bg-elevated);
        max-width: 550px;
        width: 100%;
        padding: 50px;
        border-radius: 24px;
        box-shadow: 0 20px 50px var(--shadow-color);
        border: 1px solid var(--border-color);
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .msg-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: var(--primary-gradient);
    }

    .msg-icon {
        width: 100px;
        height: 100px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        box-shadow: 0 15px 30px rgba(99, 102, 241, 0.3);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    .msg-icon i {
        font-size: 45px;
        color: white;
    }

    .msg-title {
        font-size: 1.8rem;
        font-weight: 800;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 20px;
    }

    .msg-content {
        background: var(--bg-secondary);
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 30px;
        text-align: left;
        border: 1px solid var(--border-color);
    }

    .msg-content p {
        color: var(--text-primary);
        font-size: 1rem;
        line-height: 1.7;
        margin: 0;
    }

    .msg-content a.btn {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 15px;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(99, 102, 241, 0.3);
    }

    .msg-content a.btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 14px 28px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-primary-action {
        background: var(--primary-gradient);
        color: white;
        border: none;
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
    }

    .btn-primary-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(99, 102, 241, 0.4);
        color: white;
    }

    .btn-secondary-action {
        background: var(--bg-secondary);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
    }

    .btn-secondary-action:hover {
        background: var(--border-color);
        transform: translateY(-1px);
        color: var(--text-primary);
    }

    @media (max-width: 576px) {
        .msg-container {
            padding: 35px 25px;
        }
        
        .msg-icon {
            width: 80px;
            height: 80px;
        }
        
        .msg-icon i {
            font-size: 35px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn-action {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="msg-wrapper">
    <div class="msg-container" id="msgCard">
        <div class="msg-icon">
            <i class="bi bi-bell-fill"></i>
        </div>
        
        <h2 class="msg-title">Important Notice</h2>
        
        <div class="msg-content">
            <?php 
            if (isset($_SESSION['message'])) {
                echo '<p>' . $_SESSION['message'] . '</p>';
                unset($_SESSION['message']);
            } else {
                echo '<p>No new notifications at this time.</p>';
            }
            ?>
        </div>

        <div class="action-buttons">
            <a href="index.php" class="btn-action btn-primary-action">
                <i class="bi bi-house-door-fill"></i>
                Go to Home
            </a>
            <a href="Login.php" class="btn-action btn-secondary-action">
                <i class="bi bi-box-arrow-in-right"></i>
                Back to Login
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof gsap !== 'undefined') {
            gsap.from('#msgCard', { 
                y: 50, 
                opacity: 0, 
                duration: 0.8, 
                ease: "back.out(1.7)" 
            });
            
            gsap.from('.msg-icon', { 
                scale: 0, 
                rotation: -180, 
                duration: 1, 
                ease: "elastic.out(1, 0.3)",
                delay: 0.3
            });
        }
    });
</script>

<?php
include('Includes/bottom.php');
?>