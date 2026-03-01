<?php
include 'config/authentication.php';
include 'Includes/header.php';

// Get real statistics from database (suppress errors if tables don't exist)
$total_users = @mysqli_fetch_assoc(@mysqli_query($conn, "SELECT COUNT(*) as count FROM user"))['count'] ?: 0;
$total_products = @mysqli_fetch_assoc(@mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'] ?: 0;
$total_issues = @mysqli_fetch_assoc(@mysqli_query($conn, "SELECT COUNT(*) as count FROM issued_items_dets"))['count'] ?: 0;
$pending_requests = @mysqli_fetch_assoc(@mysqli_query($conn, "SELECT COUNT(*) as count FROM issued_items_dets WHERE issue_status = 'In_approval' OR issue_status IS NULL OR issue_status = ''"))['count'] ?: 0;

// Get recent activities (suppress errors if tables don't exist)
$recent_users = @mysqli_query($conn, "SELECT * FROM user ORDER BY id DESC LIMIT 5") ?: [];
$recent_issues = @mysqli_query($conn, "SELECT * FROM issued_items_dets ORDER BY id DESC LIMIT 5") ?: [];

// Get low stock products (quantity < 10)
$low_stock = @mysqli_query($conn, "SELECT * FROM products WHERE quantity < 10 ORDER BY quantity ASC LIMIT 5") ?: [];

// Get data for charts - Birgunj Institute of Technology Departments
$department_data = [0, 0, 0, 0, 0];
$departments = ['Computer Engineering', 'Civil Engineering', 'Electrical and Electronics Engineering', 'Electrical Engineering', 'Mechanical Engineering'];

foreach ($departments as $i => $department) {
    $result = @mysqli_fetch_assoc(@mysqli_query($conn, "SELECT COUNT(*) as count FROM issued_items_dets WHERE faculty LIKE '%$department%'"));
    $department_data[$i] = $result['count'] ?: 0;
}

// Get products by department
$products_by_dept = [0, 0, 0, 0, 0];
foreach ($departments as $i => $department) {
    $result = @mysqli_fetch_assoc(@mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE category LIKE '%$department%'"));
    $products_by_dept[$i] = $result['count'] ?: 0;
}
?>

    <link rel="stylesheet" href="../Offline/CSS/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid px-4">
    <h1 class="mt-4">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard Overview</li>
    </ol>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 2rem; font-weight: bold;"><?php echo $total_users; ?></div>
                            <div>Total Users</div>
                        </div>
                        <i class="bi bi-people-fill" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="user_management.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 2rem; font-weight: bold;"><?php echo $total_products; ?></div>
                            <div>Total Products</div>
                        </div>
                        <i class="bi bi-box-seam" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="view_all_products.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 2rem; font-weight: bold;"><?php echo $total_issues; ?></div>
                            <div>Total Issues</div>
                        </div>
                        <i class="bi bi-clipboard-check" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="rent_requests.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 2rem; font-weight: bold;"><?php echo $pending_requests; ?></div>
                            <div>Pending Requests</div>
                        </div>
                        <i class="bi bi-hourglass-split" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="rent_requests.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Issues by Department (BIT)
                </div>
                <div class="card-body">
                    <canvas id="departmentChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Products by Department (BIT)
                </div>
                <div class="card-body">
                    <canvas id="productsDeptChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities and Low Stock -->
    <div class="row">
        <!-- Recent Issues -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-clock me-1"></i>
                    Recent Issues
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Issued To</th>
                                    <th>Item</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($issue = mysqli_fetch_assoc($recent_issues)): ?>
                                    <tr>
                                        <td><?php echo $issue['id']; ?></td>
                                        <td><?php echo htmlspecialchars($issue['issued_to']); ?></td>
                                        <td><?php echo htmlspecialchars($issue['item_name']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($issue['issue_date'])); ?></td>
                                        <td>
                                            <?php
                                            $status = isset($issue['issue_status']) ? $issue['issue_status'] : 'Pending';
                                            $badgeClass = ($status == 'Approved') ? 'bg-success' : 'bg-warning text-dark';
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $status; ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if (mysqli_num_rows($recent_issues) == 0): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No recent issues</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Low Stock Alerts
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($product = mysqli_fetch_assoc($low_stock)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                                        <td><span class="badge bg-danger"><?php echo $product['quantity']; ?></span></td>
                                        <td>
                                            <a href="product_edit.php?id=<?php echo $product['product_id']; ?>"
                                                class="btn btn-sm btn-primary">
                                                <i class="bi bi-plus-circle"></i> Add Stock
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if (mysqli_num_rows($low_stock) == 0): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No low stock items</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-lightning me-1"></i>
                    Quick Actions
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="add_product.php" class="btn btn-primary w-100 py-3">
                                <i class="bi bi-plus-circle d-block mb-2" style="font-size: 1.5rem;"></i>
                                Add Product
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="add-new.php" class="btn btn-success w-100 py-3">
                                <i class="bi bi-person-plus d-block mb-2" style="font-size: 1.5rem;"></i>
                                Add User
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="rent_requests.php" class="btn btn-warning w-100 py-3">
                                <i class="bi bi-clipboard-check d-block mb-2" style="font-size: 1.5rem;"></i>
                                Review Requests
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="suggestions.php" class="btn btn-info w-100 py-3">
                                <i class="bi bi-chat-dots d-block mb-2" style="font-size: 1.5rem;"></i>
                                View Messages
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Department Chart - Issues by BIT Department
    const departmentCtx = document.getElementById('departmentChart').getContext('2d');
    new Chart(departmentCtx, {
        type: 'bar',
        data: {
            labels: ['Computer Eng.', 'Civil Eng.', 'Electrical & Electronics', 'Electrical Eng.', 'Mechanical Eng.'],
            datasets: [{
                label: 'Issues by Department',
                data: [<?php echo implode(', ', $department_data); ?>],
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Birgunj Institute of Technology - Issues by Department'
                }
            }
        }
    });

    // Products by Department Chart
    const productsDeptCtx = document.getElementById('productsDeptChart').getContext('2d');
    new Chart(productsDeptCtx, {
        type: 'doughnut',
        data: {
            labels: ['Computer Eng.', 'Civil Eng.', 'Electrical & Electronics', 'Electrical Eng.', 'Mechanical Eng.'],
            datasets: [{
                data: [<?php echo implode(', ', $products_by_dept); ?>],
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Birgunj Institute of Technology - Products by Department'
                }
            }
        }
    });
</script>

<?php include 'Includes/footer.php'; ?>