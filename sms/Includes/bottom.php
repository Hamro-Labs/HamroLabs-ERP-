<style>
    /* 60-30-10 Color Rule - Footer with Light/Dark Theme */
    :root {
        /* 60% - White/Light Neutral - Light Theme */
        --bg-60-white: #ffffff;
        --bg-60-light: #f8f9fa;

        /* 30% - Blue-Gray Tones - Light Theme */
        --bg-30-panel: #212529;
        --bg-30-slate: #343a40;
        --bg-30-cool: #495057;

        /* 10% - Deep Accent */
        --accent-10-primary: #0d6efd;
        --accent-10-hover: #0a58ca;

        /* Text Colors - Light Theme */
        --text-light: #ffffff;
        --text-muted: #adb5bd;
    }

    [data-theme="dark"] {
        /* 60% - Dark Neutral - Dark Theme (#0f1720) */
        --bg-60-white: #0f1720;
        --bg-60-light: #1b2a36;

        /* 30% - Dark Blue-Gray Tones - Dark Theme (#1b2a36) */
        --bg-30-panel: #1b2a36;
        --bg-30-slate: #243447;
        --bg-30-cool: #2d3e50;

        /* 10% - Bright Friendly Blue - Accent (#3b82f6) */
        --accent-10-primary: #3b82f6;
        --accent-10-hover: #60a5fa;
    }

    footer {
        background: var(--bg-30-panel);
        color: var(--text-light);
        padding: 60px 0 30px;
        margin-top: 60px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .footer-container {
        width: 90%;
        max-width: 1400px;
        margin: 0 auto;
    }

    .footer-row {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 50px;
        margin-bottom: 40px;
    }

    .footer-column h5 {
        color: var(--text-light);
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .footer-column h6 {
        color: var(--text-light);
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 18px;
    }

    .footer-column p {
        color: var(--text-muted);
        font-size: 0.9rem;
        line-height: 1.7;
        transition: color 0.3s ease;
    }

    .footer-description {
        margin-top: 15px;
    }

    .footer-links ul {
        list-style: none;
        padding: 0;
    }

    .footer-links li {
        margin-bottom: 12px;
    }

    .footer-links a {
        color: var(--text-muted);
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .footer-links a:hover {
        color: var(--accent-10-primary);
        transform: translateX(5px);
    }

    .footer-column a {
        color: var(--text-muted);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .footer-column a:hover {
        color: var(--accent-10-primary);
    }

    .footer-container hr {
        border: none;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin: 30px 0;
        transition: border-color 0.3s ease;
    }

    .footer-bottom {
        text-align: center;
        color: var(--text-muted);
        font-size: 0.85rem;
        transition: color 0.3s ease;
    }

    .social-links {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }

    .social-links a {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-light);
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .social-links a:hover {
        background: var(--accent-10-primary);
        transform: translateY(-3px);
    }

    @media (max-width: 992px) {
        .footer-row {
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .footer-column:first-child {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 576px) {
        footer {
            padding: 40px 0 25px;
        }

        .footer-row {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .footer-column h5 {
            font-size: 1.1rem;
        }
    }
</style>

<footer>
    <div class="footer-container">

        <div class="footer-row">

            <!-- Branding -->
            <div class="footer-column">
                <h5>Birgunj Institute of Technology</h5>
                <p style="color: var(--text-muted);">Store Management System</p>
                <p class="footer-description">
                    Manage products, stock, sales, and reports efficiently for Birgunj Institute of Technology.
                    Streamline your institutional operations with our comprehensive solution.
                </p>
                <div class="social-links">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-twitter"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-column footer-links">
                <h6>Quick Links</h6>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="rent_item.php">Rent an Item</a></li>
                    <li><a href="return_item.php">Return an Item</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="footer-column">
                <h6>Contact Us</h6>
                <p>
                    <i class="bi bi-geo-alt-fill me-2"></i>
                    Birgunj, Parsa, Nepal
                </p>
                <p style="margin-top: 10px;">
                    <i class="bi bi-envelope-fill me-2"></i>
                    <a href="mailto:store@bit.edu.np">store@bit.edu.np</a>
                </p>
                <p style="margin-top: 10px;">
                    <i class="bi bi-telephone-fill me-2"></i>
                    <a href="tel:+9779811144402">+977 9811144402</a>
                </p>
            </div>

        </div>

        <hr>

        <!-- Bottom -->
        <div class="footer-bottom">
            <p>© 2025 Store Management System — Birgunj Institute of Technology. All Rights Reserved.</p>
        </div>

    </div>
</footer>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">