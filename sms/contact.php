<?php
$pageTitle = "Contact Us";
include "Includes/header.php";
include "admin/config/dbcon.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['auth'])) {
    $user_id = $_SESSION['auth_user']['user_id'];
} else {
    $user_id = 0;
}
?>

<style>
    /* Production-ready Contact Page Styles */
    :root {
        --primary-color: #0d6efd;
        --primary-dark: #0b5ed7;
        --secondary-color: #6c757d;
        --success-color: #198754;
        --info-color: #0dcaf0;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --light-color: #f8f9fa;
        --dark-color: #212529;
        --border-color: #dee2e6;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.15);
        --radius: 8px;
        --transition: all 0.3s ease;
    }

    /* Hero Section */
    .contact-hero {
        background: linear-gradient(135deg, #0f172a, #1e40af);
        color: white;
        padding: 80px 0;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .contact-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.1);
        z-index: 1;
    }

    .contact-hero .container {
        position: relative;
        z-index: 2;
    }

    .contact-hero h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 20px;
        animation: fadeInUp 0.8s ease-out;
    }

    .contact-hero p {
        font-size: 1.25rem;
        line-height: 1.8;
        max-width: 600px;
        margin: 0 auto;
        opacity: 0.95;
        animation: fadeInUp 0.8s ease-out 0.2s both;
    }

    /* Contact Section */
    .contact-section {
        padding: 80px 0;
        background: var(--light-color);
    }

    .contact-card {
        background: white;
        border-radius: var(--radius);
        padding: 30px;
        box-shadow: var(--shadow);
        height: 100%;
        transition: var(--transition);
    }

    .contact-card:hover {
        box-shadow: var(--shadow-lg);
    }

    .contact-card h2 {
        font-size: 1.75rem;
        font-weight: 600;
        margin-bottom: 20px;
        color: var(--dark-color);
    }

    .contact-card p {
        color: var(--secondary-color);
        line-height: 1.7;
        margin-bottom: 25px;
    }

    /* Contact Info */
    .contact-info-item {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding: 15px;
        background: var(--light-color);
        border-radius: var(--radius);
        transition: var(--transition);
    }

    .contact-info-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .info-icon {
        font-size: 1.5rem;
        color: var(--primary-color);
        min-width: 30px;
        text-align: center;
    }

    .contact-info-item span {
        font-weight: 600;
        color: var(--dark-color);
    }

    .contact-info-item a {
        color: var(--primary-color);
        text-decoration: none;
        transition: var(--transition);
    }

    .contact-info-item a:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    /* Form Styles */
    .form-label {
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 8px;
        color: var(--dark-color);
    }

    .form-control {
        border-radius: var(--radius);
        padding: 12px 16px;
        border: 1px solid var(--border-color);
        font-size: 1rem;
        transition: var(--transition);
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 120px;
    }

    .btn-send {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        border: none;
        border-radius: var(--radius);
        padding: 12px 30px;
        font-weight: 600;
        color: white;
        font-size: 1rem;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-width: 150px;
        justify-content: center;
    }

    .btn-send:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(13, 110, 253, 0.3);
        color: white;
    }

    .btn-send:disabled {
        opacity: 0.6;
        transform: none;
        cursor: not-allowed;
    }

    /* Success/Error Messages */
    .alert {
        border-radius: var(--radius);
        padding: 15px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background: rgba(25, 135, 84, 0.1);
        border: 1px solid rgba(25, 135, 84, 0.2);
        color: var(--success-color);
    }

    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        border: 1px solid rgba(220, 53, 69, 0.2);
        color: var(--danger-color);
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeInUp 0.8s ease-out;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .contact-hero h1 {
            font-size: 2rem;
        }

        .contact-hero p {
            font-size: 1rem;
        }

        .contact-card {
            margin-bottom: 20px;
        }

        .contact-info-item {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }

        .btn-send {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .contact-hero {
            padding: 60px 0;
        }

        .contact-section {
            padding: 60px 0;
        }

        .contact-card {
            padding: 20px;
        }
    }
</style>

<!-- Hero Section -->
<section class="contact-hero">
    <div class="container">
        <h1 class="fade-in">Contact Us</h1>
        <p class="fade-in">
            Have questions, feedback, or need support? We're here to help you with the College Store Management System.
        </p>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        <div class="row g-4">
            <!-- Contact Information -->
            <div class="col-lg-5 fade-in">
                <div class="contact-card">
                    <h2><i class="bi bi-geo-alt-fill"></i> Contact Information</h2>
                    <p>
                        Reach out to us for system support, suggestions, or administrative inquiries. Our team is here to help!
                    </p>

                    <div class="contact-info-item">
                        <i class="bi bi-envelope-at info-icon"></i>
                        <div>
                            <span class="d-block fw-bold text-secondary" style="font-size: 0.85rem; text-transform: uppercase;">Email Us</span>
                            <a href="mailto:support@collegestore.edu" class="text-decoration-none text-dark fw-semibold">
                                support@collegestore.edu
                            </a>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <i class="bi bi-telephone-fill info-icon"></i>
                        <div>
                            <span>Phone:</span>
                            <div>+977 9811144402</div>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <i class="bi bi-geo-fill info-icon"></i>
                        <div>
                            <span>Location:</span>
                            <div>College Campus, Nepal</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-7 fade-in">
                <div class="contact-card">
                    <h2><i class="bi bi-pencil-square"></i> Send a Message</h2>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            <div>
                                <strong>Success!</strong> Your message has been sent successfully. We'll get back to you soon.
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle"></i>
                            <div>
                                <strong>Error!</strong> There was a problem sending your message. Please try again.
                            </div>
                        </div>
                    <?php endif; ?>

                    <form action="contact_process.php" method="POST" id="contactForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-person-fill"></i> Full Name
                                </label>
                                <input type="text" name="name" class="form-control" required placeholder="Enter your full name">
                            </div>

                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-envelope-fill"></i> Email Address
                                </label>
                                <input type="email" name="email" class="form-control" required placeholder="name@example.com">
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    <i class="bi bi-chat-left-text-fill"></i> Subject
                                </label>
                                <input type="text" name="subject" class="form-control" required placeholder="How can we help you?">
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    <i class="bi bi-chat-dots-fill"></i> Message
                                </label>
                                <textarea name="message" rows="5" class="form-control" required placeholder="Your message..."></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn-send" name="suggestions_btn" id="submitBtn">
                                    <i class="bi bi-send-fill"></i> Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Form submission handling
        const contactForm = document.getElementById('contactForm');
        const submitBtn = document.getElementById('submitBtn');

        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
            });
        }

        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        // Observe all fade-in elements
        document.querySelectorAll('.contact-card').forEach(el => {
            observer.observe(el);
        });

        // Add hover effect to info items
        const infoItems = document.querySelectorAll('.contact-info-item');
        infoItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(5px)';
            });
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });
    });
</script>

<?php include "Includes/bottom.php"; ?>
