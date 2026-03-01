<?php
include('config/dbcon.php');
include('includes/header.php');
?>

<head>
    <style>
        .dashboard-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 30px 22px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            height: 100%;
        }

        .dashboard-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.1);
        }

        .icon-box {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 30px;
        }

        .bg-primary-soft {
            background: #eaf3ff;
            color: #0d6efd;
        }

        .bg-success-soft {
            background: #eafaf1;
            color: #198754;
        }

        .bg-warning-soft {
            background: #fff4e5;
            color: #ffc107;
        }

        .bg-info-soft {
            background: #e9f6ff;
            color: #0dcaf0;
        }
    </style>
</head>
<h1 class="text-center about-subtitle">Product Management</h1>
<p class="text-muted text-center">Manage your products, issues, and rent requests.</p>
<div class="container-fluid px-4 mt-4">
    <!-- <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-semibold">Product Management</h2>
            <p class="text-muted">Manage your products, issues, and rent requests.</p>
        </div>
    </div> -->

    <div class="row g-4">

        <!-- Add Product -->
        <div class="col-md-3 col-sm-6">
            <div class="dashboard-card text-center">
                <div class="icon-box bg-primary-soft">
                    <i class="bi bi-plus-square"></i>
                </div>
                <h5 class="mt-3">Add Product</h5>
                <p class="text-muted">
                    Add new items to your inventory.
                </p>
                <a href="add_product.php" class="btn btn-primary rounded-pill px-4">
                    Open Module
                </a>
            </div>
        </div>

        <!-- Issue Product -->
        <div class="col-md-3 col-sm-6">
            <div class="dashboard-card text-center">
                <div class="icon-box bg-success-soft">
                    <i class="bi bi-box-arrow-up"></i>
                </div>
                <h5 class="mt-3">Issue Product</h5>
                <p class="text-muted">
                    Issue products to users.
                </p>
                <a href="issue.php" class="btn btn-success rounded-pill px-4">
                    Open Module
                </a>
            </div>
        </div>

        <!-- Rent Requests -->
        <div class="col-md-3 col-sm-6">
            <div class="dashboard-card text-center">
                <div class="icon-box bg-warning-soft">
                    <i class="bi bi-inbox"></i>
                </div>
                <h5 class="mt-3">Rent Requests</h5>
                <p class="text-muted">
                    View and manage rent requests.
                </p>
                <a href="rent_requests.php" class="btn btn-warning rounded-pill px-4 text-white">
                    Open Module
                </a>
            </div>
        </div>

        <!-- View Products -->
        <div class="col-md-3 col-sm-6">
            <div class="dashboard-card text-center">
                <div class="icon-box bg-info-soft">
                    <i class="bi bi-list-ul"></i>
                </div>
                <h5 class="mt-3">View Products</h5>
                <p class="text-muted">
                    See all available products.
                </p>
                <a href="view_all_products.php" class="btn btn-info rounded-pill px-4 text-white">
                    Open Module
                </a>
            </div>
        </div>

    </div>
</div>

<?php
include('Includes/footer.php');
?>