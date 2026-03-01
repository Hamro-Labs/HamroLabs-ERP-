<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Login status
$isLoggedIn = isset($_SESSION['auth']) && $_SESSION['auth'] === true;

// Default values
$username = '';
$role = '';

if ($isLoggedIn && isset($_SESSION['auth_user'])) {
    $username = $_SESSION['auth_user']['user_name'];
    $role = $_SESSION['auth_user']['role'];
    $user_id = $_SESSION['auth_user']['user_id'];
}

// Check for theme preference
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
if (isset($_GET['theme'])) {
    $theme = $_GET['theme'] === 'dark' ? 'dark' : 'light';
    setcookie('theme', $theme, time() + (365 * 24 * 60 * 60), '/');
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <title><?php echo isset($pageTitle) ? $pageTitle : "Default Site Title"; ?></title>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <!-- GSAP Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js" defer></script>

    <style>
        /* ===== CSS CUSTOM PROPERTIES ===== */
        :root {
            /* Primary Colors - Light Theme */
            --primary-900: #0a2472;
            --primary-700: #0d6efd;
            --primary-500: #3b82f6;
            --primary-300: #93c5fd;
            --primary-100: #dbeafe;
            
            /* Neutral Colors */
            --neutral-900: #111827;
            --neutral-800: #1f2937;
            --neutral-700: #374151;
            --neutral-600: #4b5563;
            --neutral-500: #6b7280;
            --neutral-400: #9ca3af;
            --neutral-300: #d1d5db;
            --neutral-200: #e5e7eb;
            --neutral-100: #f3f4f6;
            --neutral-50: #f9fafb;
            --white: #ffffff;
            
            /* Semantic Colors - Light Theme */
            --bg-primary: var(--white);
            --bg-secondary: var(--neutral-50);
            --bg-elevated: var(--white);
            --text-primary: var(--neutral-900);
            --text-secondary: var(--neutral-700);
            --text-muted: var(--neutral-500);
            --border-color: var(--neutral-200);
            --shadow-color: rgba(0, 0, 0, 0.1);
            --overlay-color: rgba(0, 0, 0, 0.5);
            
            /* Navbar Specific */
            --navbar-bg: var(--primary-700);
            --navbar-text: var(--white);
            --navbar-hover: var(--primary-300);
            --navbar-shadow: 0 2px 8px rgba(13, 110, 253, 0.2);
            
            /* Typography */
            --font-base: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Inter', system-ui, sans-serif;
            --font-size-xs: 0.75rem;
            --font-size-sm: 0.875rem;
            --font-size-base: 1rem;
            --font-size-lg: 1.125rem;
            --font-size-xl: 1.25rem;
            --font-size-2xl: 1.5rem;
            --font-size-3xl: 1.875rem;
            
            /* Spacing */
            --space-1: 0.25rem;
            --space-2: 0.5rem;
            --space-3: 0.75rem;
            --space-4: 1rem;
            --space-5: 1.25rem;
            --space-6: 1.5rem;
            --space-8: 2rem;
            --space-10: 2.5rem;
            --space-12: 3rem;
            
            /* Container */
            --container-padding: 1rem;
            --container-max-width: 1400px;
        }
        
        /* ===== DARK THEME OVERRIDES ===== */
        [data-theme="dark"] {
            --bg-primary: var(--neutral-900);
            --bg-secondary: var(--neutral-800);
            --bg-elevated: var(--neutral-800);
            --text-primary: var(--neutral-50);
            --text-secondary: var(--neutral-200);
            --text-muted: var(--neutral-400);
            --border-color: var(--neutral-700);
            --shadow-color: rgba(0, 0, 0, 0.3);
            --overlay-color: rgba(0, 0, 0, 0.7);
            --navbar-bg: var(--primary-900);
            --navbar-text: var(--neutral-100);
            --navbar-hover: var(--primary-300);
            --navbar-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }
        
        /* ===== RESET & BASE STYLES ===== */
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            scroll-behavior: smooth;
            -webkit-text-size-adjust: 100%;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        body {
            font-family: var(--font-base);
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.5;
            overflow-x: hidden;
            transition: background-color 0.3s, color 0.3s;
        }
        
        /* ===== NAVBAR STYLES - MOBILE FIRST ===== */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: var(--navbar-bg);
            box-shadow: var(--navbar-shadow);
            z-index: 1000;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            height: 60px;
        }
        
        .nav-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 var(--container-padding);
            height: 100%;
            max-width: var(--container-max-width);
            margin: 0 auto;
        }
        
        /* Brand Logo */
        .brand {
            display: flex;
            align-items: center;
            color: var(--navbar-text);
            font-size: var(--font-size-lg);
            font-weight: 700;
            text-decoration: none;
            z-index: 1002;
            margin-right: 6.5vw;
        }

        .brand span {
            color: #ff6b6b;
        }
        
        /* Mobile Menu Toggle */
        .menu-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            background: none;
            border: none;
            color: var(--navbar-text);
            font-size: var(--font-size-xl);
            cursor: pointer;
            z-index: 1002;
            border-radius: 4px;
        }
        
        .menu-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .menu-toggle .bi-x {
            display: none;
        }
        
        .menu-toggle[aria-expanded="true"] .bi-list {
            display: none;
        }
        
        .menu-toggle[aria-expanded="true"] .bi-x {
            display: block;
        }
        
        /* Mobile Navigation Menu */
        .nav-content {
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--navbar-bg);
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            overflow-y: auto;
            z-index: 1001;
            padding: var(--space-4);
            display: flex;
            flex-direction: column;
            gap: var(--space-6);
        }
        
        .nav-content.active {
            transform: translateX(0);
            height: fit-content;
        }
        
        /* Navigation Links */
        .nav-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: var(--space-2);
        }
        
        .nav-links a {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            color: var(--navbar-text);
            text-decoration: none;
            padding: var(--space-3) var(--space-4);
            border-radius: 8px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .nav-links a:hover,
        .nav-links a.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* Right Side Actions */
        .nav-actions {
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
        }
        
        .action-group {
            display: flex;
            flex-direction: column;
            gap: var(--space-3);
        }
        
        /* Theme Toggle */
        .theme-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            background-color: rgba(255, 255, 255, 0.15);
            color: var(--navbar-text);
            padding: var(--space-2) var(--space-4);
            border-radius: 20px;
            text-decoration: none;
            font-size: var(--font-size-sm);
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .theme-toggle:hover {
            background-color: rgba(255, 255, 255, 0.25);
        }
        
        /* User Info */
        .user-info {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            color: var(--navbar-text);
            padding: var(--space-2) var(--space-3);
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 6px;
            font-size: var(--font-size-sm);
        }
        
        /* Auth Buttons */
        .auth-buttons {
            display: flex;
            flex-direction: column;
            gap: var(--space-3);
        }
        
        .auth-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            background-color: rgba(255, 255, 255, 0.2);
            color: var(--navbar-text);
            padding: var(--space-3);
            border-radius: 20px;
            text-decoration: none;
            font-size: var(--font-size-sm);
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .logout-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            background-color: rgba(220, 53, 69, 0.2);
            color: var(--navbar-text);
            padding: var(--space-3);
            border-radius: 20px;
            text-decoration: none;
            font-size: var(--font-size-sm);
            font-weight: 500;
            border: 1px solid rgba(220, 53, 69, 0.3);
            cursor: pointer;
            font-family: inherit;
            width: 100%;
        }
        
        /* ===== TABLET STYLES (768px and up) ===== */
        @media (min-width: 768px) {
            body {
                padding-top: 70px;
            }
            
            .navbar {
                height: 70px;
            }
            
            .brand {
                font-size: var(--font-size-xl);
            }
            
            .nav-content {
                top: 70px;
            }
            
            .auth-buttons {
                flex-direction: row;
            }
            
            .action-group {
                flex-direction: row;
                align-items: center;
            }
        }
        
        /* ===== DESKTOP STYLES (1024px and up) ===== */
        @media (min-width: 1024px) {
            body {
                padding-top: 80px;
            }
            
            .navbar {
                height: 80px;
            }
            
            .nav-container {
                padding: 0 2rem;
            }
            
            .brand {
                font-size: var(--font-size-2xl);
            }
            
            .menu-toggle {
                display: none;
            }
            
            .nav-content {
                position: static;
                transform: none;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                flex: 1;
                padding: 0;
                background: none;
                overflow: visible;
                top: auto;
                bottom: auto;
                left: auto;
                right: auto;
                gap: var(--space-8);
            }
            
            .nav-links {
                flex-direction: row;
                gap: var(--space-2);
            }
            
            .nav-links a {
                padding: var(--space-2) var(--space-4);
            }
            
            .nav-actions {
                flex-direction: row;
                align-items: center;
                gap: var(--space-4);
            }
            
            .user-info {
                padding: var(--space-2) var(--space-4);
            }
        }
        
        /* ===== LARGE DESKTOP STYLES (1400px and up) ===== */
        @media (min-width: 1400px) {
            .nav-container {
                padding: 0 calc(var(--container-padding) * 2);
            }
            
            .nav-links {
                gap: var(--space-4);
            }
        }
        
        /* ===== UTILITY CLASSES ===== */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        
        .skip-to-content {
            position: absolute;
            top: -40px;
            left: 0;
            background: var(--primary-700);
            color: white;
            padding: 8px;
            z-index: 1003;
            text-decoration: none;
            border-radius: 4px;
        }
        
        .skip-to-content:focus {
            top: 10px;
        }
        
        /* Prevent body scroll when menu is open */
        body.menu-open {
            overflow: hidden;
        }
        
        /* Smooth fade-in animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</head>

<body>
   
    <!-- Navbar Component -->
    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <div class="nav-container">
            <!-- Brand Logo -->
            <a href="index.php" class="brand">
                Store<span>Mart</span>
            </a>
            
            <!-- Mobile Menu Toggle -->
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="navContent">
                <i class="bi bi-list"></i>
                <i class="bi bi-x"></i>
            </button>
            
            <!-- Navigation Content -->
            <div class="nav-content" id="navContent">
                <!-- Navigation Links -->
                <ul class="nav-links" role="menubar">
                    <li role="none">
                        <a href="index.php" role="menuitem">
                            <i class="bi bi-house"></i>
                            <span>Home</span>
                        </a>
                    </li>
                    <li role="none">
                        <a href="about.php" role="menuitem">
                            <i class="bi bi-info-circle"></i>
                            <span>About</span>
                        </a>
                    </li>
                    <li role="none">
                        <a href="rent_item.php" role="menuitem">
                            <i class="bi bi-bag-plus"></i>
                            <span>Rent</span>
                        </a>
                    </li>
                    <li role="none">
                        <a href="return_item.php" role="menuitem">
                            <i class="bi bi-arrow-return-left"></i>
                            <span>Return</span>
                        </a>
                    </li>
                    <li role="none">
                        <a href="contact.php" role="menuitem">
                            <i class="bi bi-envelope"></i>
                            <span>Contact</span>
                        </a>
                    </li>
                    
                    <?php if ($isLoggedIn): ?>
                    <li role="none">
                        <a href="user_profile.php" role="menuitem">
                            <i class="bi bi-person-circle"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($isLoggedIn && $role === 'Admin'): ?>
                    <li role="none">
                        <a href="admin/index.php" role="menuitem" class="admin-link">
                            <i class="bi bi-shield-check"></i>
                            <span>Admin</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Right Side Actions -->
                <div class="nav-actions">
                    <!-- Theme Toggle -->
                    <div class="action-group">
                        <a href="?theme=<?php echo $theme === 'dark' ? 'light' : 'dark'; ?>" 
                           class="theme-toggle"
                           aria-label="Switch to <?php echo $theme === 'dark' ? 'light' : 'dark'; ?> mode">
                            <i class="bi bi-<?php echo $theme === 'dark' ? 'sun' : 'moon'; ?>-fill"></i>
                            <span><?php echo $theme === 'dark' ? 'Light' : 'Dark'; ?></span>
                        </a>
                    </div>
                    
                    <!-- User/Auth Section -->
                    <div class="action-group">
                        <?php if ($isLoggedIn): ?>
                            <div class="user-info">
                                <i class="bi bi-person-circle"></i>
                                <span class="username"><?php echo htmlspecialchars($username); ?></span>
                                <?php if (isset($user_id)): ?>
                                <span class="user-id">(ID: <?php echo htmlspecialchars($user_id); ?>)</span>
                                <?php endif; ?>
                            </div>
                            
                            <form action="logout.php" method="POST" class="logout-form">
                                <button type="submit" class="logout-btn">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="auth-buttons">
                                <a href="Login.php" class="auth-btn">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                    <span>Login</span>
                                </a>
                                <a href="SignUp.php" class="auth-btn">
                                    <i class="bi bi-person-plus"></i>
                                    <span>Sign Up</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Wrapper -->
    <main id="main-content" tabindex="-1">

    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="container mt-3">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Notice!</strong> ' . $_SESSION['message'] . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>';
        unset($_SESSION['message']);
    }
    ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile menu functionality
        const menuToggle = document.getElementById('menuToggle');
        const navContent = document.getElementById('navContent');
        const body = document.body;
        
        if (menuToggle && navContent) {
            menuToggle.addEventListener('click', function() {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
                navContent.classList.toggle('active');
                body.classList.toggle('menu-open');
            });
            
            // Close menu when clicking on links
            navContent.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    menuToggle.setAttribute('aria-expanded', 'false');
                    navContent.classList.remove('active');
                    body.classList.remove('menu-open');
                });
            });
            
            // Close menu with Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && navContent.classList.contains('active')) {
                    menuToggle.setAttribute('aria-expanded', 'false');
                    navContent.classList.remove('active');
                    body.classList.remove('menu-open');
                }
            });
            
            // Close menu when clicking outside on mobile
            if (window.innerWidth < 1024) {
                document.addEventListener('click', (e) => {
                    if (!navContent.contains(e.target) && 
                        !menuToggle.contains(e.target) && 
                        navContent.classList.contains('active')) {
                        menuToggle.setAttribute('aria-expanded', 'false');
                        navContent.classList.remove('active');
                        body.classList.remove('menu-open');
                    }
                });
            }
        }
        
        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Close mobile menu when resizing to desktop
                if (window.innerWidth >= 1024) {
                    if (navContent) {
                        navContent.classList.remove('active');
                        body.classList.remove('menu-open');
                    }
                    if (menuToggle) {
                        menuToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            }, 250);
        });
        
        // GSAP animations if available
        if (typeof gsap !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);
            
            // Animate navbar on load
            gsap.from('.navbar', {
                y: -100,
                duration: 0.5,
                ease: "power2.out"
            });
        }
    });
    
    // Touch device detection
    (function() {
        const isTouchDevice = 'ontouchstart' in window || 
                             navigator.maxTouchPoints > 0 || 
                             navigator.msMaxTouchPoints > 0;
        
        if (isTouchDevice) {
            document.body.classList.add('touch-device');
        }
    })();
</script>
</body>
</html>