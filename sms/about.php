<?php
$pageTitle = "About Us";
include "Includes/header.php";
include "admin/config/dbcon.php";
?>

<style>
    /* Production-ready About Page Styles */
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
    .about-hero {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 80px 0;
        position: relative;
        overflow: hidden;
    }

    .about-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.1);
        z-index: 1;
    }

    .about-hero .container {
        position: relative;
        z-index: 2;
    }

    .hero-content {
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
    }

    .hero-content h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 20px;
        animation: fadeInUp 0.8s ease-out;
    }

    .hero-content p {
        font-size: 1.25rem;
        line-height: 1.8;
        margin-bottom: 30px;
        opacity: 0.95;
        animation: fadeInUp 0.8s ease-out 0.2s both;
    }

    /* Stats Section */
    .stats-section {
        padding: 60px 0;
        background: var(--light-color);
    }

    .stat-item {
        text-align: center;
        padding: 30px;
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        transition: var(--transition);
        height: 100%;
    }

    .stat-item:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 10px;
    }

    .stat-label {
        font-size: 1rem;
        color: var(--secondary-color);
        font-weight: 500;
    }

    /* Features Section */
    .features-section {
        padding: 80px 0;
    }

    .section-title {
        text-align: center;
        margin-bottom: 60px;
    }

    .section-title h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 15px;
        position: relative;
        display: inline-block;
    }

    .section-title h2::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: var(--primary-color);
        border-radius: 2px;
    }

    .section-title p {
        font-size: 1.1rem;
        color: var(--secondary-color);
        max-width: 600px;
        margin: 30px auto 0;
    }

    .feature-card {
        padding: 40px 30px;
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border-top: 4px solid var(--primary-color);
        transition: var(--transition);
        height: 100%;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
        border-top-color: var(--info-color);
    }

    .feature-icon {
        font-size: 3rem;
        color: var(--primary-color);
        margin-bottom: 20px;
    }

    .feature-card h3 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 15px;
        color: var(--dark-color);
    }

    .feature-card p {
        color: var(--secondary-color);
        line-height: 1.7;
    }

    /* Technology Section */
    .tech-section {
        padding: 80px 0;
        background: var(--light-color);
    }

    .tech-item {
        text-align: center;
        padding: 20px;
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        transition: var(--transition);
        height: 100%;
    }

    .tech-item:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .tech-icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
    }

    .tech-item h4 {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--dark-color);
    }

    /* Team Section */
    .team-section {
        padding: 80px 0;
    }

    .team-member {
        text-align: center;
        padding: 30px;
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        transition: var(--transition);
        height: 100%;
    }

    .team-member:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .team-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--info-color));
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 2.5rem;
        color: white;
        font-weight: bold;
    }

    .team-member h4 {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 5px;
        color: var(--dark-color);
    }

    .team-member .role {
        color: var(--secondary-color);
        font-size: 0.95rem;
        margin-bottom: 10px;
    }

    .team-member .roll {
        color: var(--primary-color);
        font-size: 0.9rem;
        font-weight: 500;
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
        .hero-content h1 {
            font-size: 2rem;
        }

        .hero-content p {
            font-size: 1rem;
        }

        .section-title h2 {
            font-size: 2rem;
        }

        .feature-card,
        .tech-item,
        .team-member {
            margin-bottom: 20px;
        }
    }

    @media (max-width: 480px) {
        .about-hero {
            padding: 60px 0;
        }

        .hero-content h1 {
            font-size: 1.8rem;
        }

        .stat-number {
            font-size: 2rem;
        }

        .features-section,
        .tech-section,
        .team-section {
            padding: 60px 0;
        }
    }
</style>

<!-- Hero Section -->
<section class="about-hero">
    <div class="container">
        <div class="hero-content">
            <h1>About Our Project</h1>
            <p>College Store Management System - A modern web-based solution designed to streamline inventory management for educational institutions, replacing traditional manual processes with efficient digital workflows.</p>
            <div class="d-flex justify-content-center gap-3">
                <span class="badge bg-light text-dark">Web Development</span>
                <span class="badge bg-light text-dark">Database Management</span>
                <span class="badge bg-light text-dark">College Project 2023-24</span>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6 col-lg-3 fade-in">
                <div class="stat-item">
                    <div class="stat-number">4+</div>
                    <div class="stat-label">Months Development</div>
                    <p class="small text-muted">Project Duration</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 fade-in">
                <div class="stat-item">
                    <div class="stat-number">5</div>
                    <div class="stat-label">Core Modules</div>
                    <p class="small text-muted">Inventory, Users, Reports</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 fade-in">
                <div class="stat-item">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Responsive</div>
                    <p class="small text-muted">Works on All Devices</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 fade-in">
                <div class="stat-item">
                    <div class="stat-number">Secure</div>
                    <div class="stat-label">Data Protection</div>
                    <p class="small text-muted">User Authentication</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="section-title">
            <h2>Key Features</h2>
            <p>Our system provides comprehensive solutions for modern inventory management in educational institutions</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h3>User Authentication</h3>
                    <p>Secure login with role-based access control for administrators, staff, and managers.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h3>Inventory Management</h3>
                    <p>Add, update, delete, and track inventory items with categories and detailed information.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <h3>Issue & Return System</h3>
                    <p>Track items issued to departments or students with return deadline management.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-bar-chart-line"></i>
                    </div>
                    <h3>Report Generation</h3>
                    <p>Generate detailed inventory reports, transaction history, and low stock alerts.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <h3>Search & Filter</h3>
                    <p>Quickly find items by name, category, or department with advanced filtering options.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h3>Dashboard Analytics</h3>
                    <p>Visual charts showing inventory status, popular items, and transaction trends.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Technology Section -->
<section class="tech-section">
    <div class="container">
        <div class="section-title">
            <h2>Technology Stack</h2>
            <p>Built with modern web technologies for reliability and performance</p>
        </div>
        <div class="row g-4">
            <div class="col-md-2 col-4 fade-in">
                <div class="tech-item">
                    <div class="tech-icon text-danger">
                        <i class="fab fa-html5"></i>
                    </div>
                    <h4>HTML5</h4>
                </div>
            </div>
            <div class="col-md-2 col-4 fade-in">
                <div class="tech-item">
                    <div class="tech-icon text-primary">
                        <i class="fab fa-css3-alt"></i>
                    </div>
                    <h4>CSS3</h4>
                </div>
            </div>
            <div class="col-md-2 col-4 fade-in">
                <div class="tech-item">
                    <div class="tech-icon text-warning">
                        <i class="fab fa-js-square"></i>
                    </div>
                    <h4>JavaScript</h4>
                </div>
            </div>
            <div class="col-md-2 col-4 fade-in">
                <div class="tech-item">
                    <div class="tech-icon text-purple">
                        <i class="fab fa-bootstrap"></i>
                    </div>
                    <h4>Bootstrap 5</h4>
                </div>
            </div>
            <div class="col-md-2 col-4 fade-in">
                <div class="tech-item">
                    <div class="tech-icon text-success">
                        <i class="fas fa-database"></i>
                    </div>
                    <h4>MySQL</h4>
                </div>
            </div>
            <div class="col-md-2 col-4 fade-in">
                <div class="tech-item">
                    <div class="tech-icon text-info">
                        <i class="fab fa-php"></i>
                    </div>
                    <h4>PHP</h4>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team-section">
    <div class="container">
        <div class="section-title">
            <h2>Project Team</h2>
            <p>Meet the talented individuals behind this project</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="team-member">
                    <div class="team-avatar">
                        <i class="bi bi-person"></i>
                    </div>
                    <h4>Devbarat Prasad Patel</h4>
                    <div class="role">Project Lead & Developer</div>
                    <div class="roll">Roll No: 7</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="team-member">
                    <div class="team-avatar">
                        <i class="bi bi-person"></i>
                    </div>
                    <h4>Manu Patel Kurmi</h4>
                    <div class="role">UI/UX Designer</div>
                    <div class="roll">Roll No: 13</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="team-member">
                    <div class="team-avatar">
                        <i class="bi bi-person"></i>
                    </div>
                    <h4>Akriti Maharazan</h4>
                    <div class="role">Quality Assurance</div>
                    <div class="roll">Roll No: 1</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="team-member">
                    <div class="team-avatar">
                        <i class="bi bi-person"></i>
                    </div>
                    <h4>Dilasha Gurung</h4>
                    <div class="role">Documentation</div>
                    <div class="roll">Roll No: 10</div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Smooth scroll animations
    document.addEventListener('DOMContentLoaded', function() {
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
        document.querySelectorAll('.stat-item, .feature-card, .tech-item, .team-member').forEach(el => {
            observer.observe(el);
        });

        // Add hover effect to cards
        const cards = document.querySelectorAll('.stat-item, .feature-card, .tech-item, .team-member');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>

<?php include('Includes/bottom.php'); ?>
