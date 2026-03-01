<?php
include 'config/authentication.php';
include 'Includes/header.php';
?>

    <link rel="stylesheet" href="../Offline/CSS/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .admin-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .admin-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .module-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            height: 100%;
            border: none;
        }

        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .module-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 28px;
        }

        .module-card h4 {
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .module-card p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .module-btn {
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .module-btn:hover {
            transform: scale(1.05);
        }

        .icon-blue {
            background: #e3f2fd;
            color: #1976d2;
        }

        .icon-green {
            background: #e8f5e9;
            color: #388e3c;
        }

        .icon-yellow {
            background: #fff8e1;
            color: #ffa000;
        }

        .icon-red {
            background: #ffebee;
            color: #d32f2f;
        }

        .icon-purple {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .icon-teal {
            background: #e0f2f1;
            color: #00796b;
        }

        .icon-orange {
            background: #fff3e0;
            color: #ef6c00;
        }

        .icon-gray {
            background: #eceff1;
            color: #546e7a;
        }

        .row-gaps {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-left: 15px;
            border-left: 4px solid #0d6efd;
        }
    </style>

    <div class="admin-header">
        <h1><i class="bi bi-speedometer2 me-3"></i>Admin Panel Dashboard</h1>
        <p>Manage your Store Management System</p>
    </div>

    <div class="container-fluid px-4">

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="module-card">
                    <div class="module-icon icon-blue">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h4>Users</h4>
                    <p>Manage system users</p>
                    <a href="user_management.php" class="btn btn-primary module-btn">
                        <i class="bi bi-arrow-right-circle me-1"></i> Manage
                    </a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="module-card">
                    <div class="module-icon icon-green">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h4>Products</h4>
                    <p>Inventory management</p>
                    <a href="Product_manage.php" class="btn btn-success module-btn">
                        <i class="bi bi-arrow-right-circle me-1"></i> Manage
                    </a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="module-card">
                    <div class="module-icon icon-yellow">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <h4>Issues</h4>
                    <p>Rental requests</p>
                    <a href="rent_requests.php" class="btn btn-warning module-btn text-dark">
                        <i class="bi bi-arrow-right-circle me-1"></i> View
                    </a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="module-card">
                    <div class="module-icon icon-teal">
                        <i class="bi bi-cart-plus"></i>
                    </div>
                    <h4>Purchases</h4>
                    <p>Purchase bills</p>
                    <a href="purchases.php" class="btn btn-info module-btn">
                        <i class="bi bi-arrow-right-circle me-1"></i> Manage
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Modules Section -->
        <h3 class="section-title">Core Modules</h3>
        <div class="row row-gaps">
            <div class="col-md-4">
                <div class="module-card">
                    <div class="module-icon icon-blue">
                        <i class="bi bi-plus-square-fill"></i>
                    </div>
                    <h4>Product Management</h4>
                    <p>Add, edit, and track products with stock levels</p>
                    <a href="Product_manage.php" class="btn btn-primary module-btn">
                        <i class="bi bi-gear me-1"></i> Open Module
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="module-card">
                    <div class="module-icon icon-yellow">
                        <i class="bi bi-bar-chart-line-fill"></i>
                    </div>
                    <h4>Reports & Analytics</h4>
                    <p>View statistics, charts, and summaries</p>
                    <a href="dashboard.php" class="btn btn-warning module-btn text-dark">
                        <i class="bi bi-graph-up me-1"></i> View Reports
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="module-card">
                    <div class="module-icon icon-gray">
                        <i class="bi bi-gear-fill"></i>
                    </div>
                    <h4>Settings</h4>
                    <p>Configure system preferences and database</p>
                    <a href="settings.php" class="btn btn-secondary module-btn">
                        <i class="bi bi-sliders me-1"></i> Settings
                    </a>
                </div>
            </div>
        </div>

        <!-- Additional Modules Section -->
        <h3 class="section-title">Additional Features</h3>
        <div class="row row-gaps">
            <div class="col-md-3">
                <div class="module-card">
                    <div class="module-icon icon-teal">
                        <i class="bi bi-cart-plus"></i>
                    </div>
                    <h4>Purchases</h4>
                    <p>Record purchase invoices and update stock</p>
                    <a href="purchases.php" class="btn btn-info module-btn">
                        <i class="bi bi-plus-circle me-1"></i> Open
                    </a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="module-card">
                    <div class="module-icon icon-orange">
                        <i class="bi bi-chat-left-text-fill"></i>
                    </div>
                    <h4>Suggestions</h4>
                    <p>View and respond to user messages</p>
                    <a href="suggestions.php" class="btn btn-warning module-btn text-dark">
                        <i class="bi bi-envelope-open me-1"></i> View
                    </a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="module-card">
                    <div class="module-icon icon-purple">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h4>User Management</h4>
                    <p>Manage users, roles, and permissions</p>
                    <a href="user_management.php" class="btn btn-primary module-btn">
                        <i class="bi bi-person-gear me-1"></i> Manage
                    </a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="module-card">
                    <div class="module-icon icon-green">
                        <i class="bi bi-house-fill"></i>
                    </div>
                    <h4>User Home</h4>
                    <p>Return to user-facing homepage</p>
                    <a href="../index.php" class="btn btn-success module-btn">
                        <i class="bi bi-arrow-left me-1"></i> Go Home
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="module-card"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2" style="color: white;"><i class="bi bi-lightning me-2"></i>Quick Actions
                            </h4>
                            <p class="mb-0" style="color: rgba(255,255,255,0.9);">Access frequently used functions</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="add_product.php" class="btn btn-light me-2">
                                <i class="bi bi-plus-lg me-1"></i> Add Product
                            </a>
                            <a href="add-new.php" class="btn btn-light">
                                <i class="bi bi-person-plus me-1"></i> Add User
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="Offline/JS/script.js"></script>

    <?php include 'Includes/footer.php'; ?>