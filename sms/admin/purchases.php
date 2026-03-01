<?php
include "config/dbcon.php";
include "Includes/header.php";

$message = "";
$error = "";

// Get categories and departments
$categories_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$departments_result = mysqli_query($conn, "SELECT * FROM departments ORDER BY name");

// Handle purchase form submission
if (isset($_POST['add_purchase'])) {
    $product_name = htmlspecialchars(trim($_POST['product_name']));
    $category = htmlspecialchars(trim($_POST['category']));
    $department = htmlspecialchars(trim($_POST['department']));
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);
    $supplier = htmlspecialchars(trim($_POST['supplier']));
    $notes = htmlspecialchars(trim($_POST['notes']));

    if (empty($product_name) || $quantity <= 0 || $price < 0) {
        $error = "Please fill in all required fields with valid values.";
    } else {
        $total = $quantity * $price;
        $date = date('Y-m-d');

        $insert_query = "INSERT INTO purchases (product_name, category, department, quantity, price, total, supplier, notes, date, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed')";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "sssidssss", $product_name, $category, $department, $quantity, $price, $total, $supplier, $notes, $date);

        if (mysqli_stmt_execute($stmt)) {
            $purchase_id = mysqli_insert_id($conn);
            $message = "Purchase recorded successfully! Purchase ID: #" . $purchase_id;
            mysqli_stmt_close($stmt);
        } else {
            $error = "Failed to record purchase: " . mysqli_error($conn);
        }
    }
}

// Handle purchase update
if (isset($_POST['update_purchase'])) {
    $purchase_id = intval($_POST['purchase_id']);
    $product_name = htmlspecialchars(trim($_POST['product_name']));
    $category = htmlspecialchars(trim($_POST['category']));
    $department = htmlspecialchars(trim($_POST['department']));
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);
    $supplier = htmlspecialchars(trim($_POST['supplier']));
    $notes = htmlspecialchars(trim($_POST['notes']));
    $date = $_POST['date'];

    $total = $quantity * $price;

    $update_query = "UPDATE purchases SET product_name=?, category=?, department=?, quantity=?, price=?, total=?, supplier=?, notes=?, date=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "sssidssssi", $product_name, $category, $department, $quantity, $price, $total, $supplier, $notes, $date, $purchase_id);

    if (mysqli_stmt_execute($stmt)) {
        $message = "Purchase updated successfully!";
        mysqli_stmt_close($stmt);
        header("Location: purchases.php");
        exit;
    } else {
        $error = "Failed to update purchase: " . mysqli_error($conn);
    }
}

// Handle purchase delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_query = "DELETE FROM purchases WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = "Purchase deleted successfully!";
    } else {
        $error = "Failed to delete purchase.";
    }
    mysqli_stmt_close($stmt);
    header("Location: purchases.php");
    exit;
}

// Handle search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_category = isset($_GET['filter_category']) ? $_GET['filter_category'] : '';
$filter_department = isset($_GET['filter_department']) ? $_GET['filter_department'] : '';
$filter_date_from = isset($_GET['filter_date_from']) ? $_GET['filter_date_from'] : '';
$filter_date_to = isset($_GET['filter_date_to']) ? $_GET['filter_date_to'] : '';

// Build query with filters
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(product_name LIKE ? OR supplier LIKE ? OR notes LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'sss';
}

if (!empty($filter_category)) {
    $where_conditions[] = "category = ?";
    $params[] = $filter_category;
    $types .= 's';
}

if (!empty($filter_department)) {
    $where_conditions[] = "department = ?";
    $params[] = $filter_department;
    $types .= 's';
}

if (!empty($filter_date_from)) {
    $where_conditions[] = "date >= ?";
    $params[] = $filter_date_from;
    $types .= 's';
}

if (!empty($filter_date_to)) {
    $where_conditions[] = "date <= ?";
    $params[] = $filter_date_to;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get all purchases with filters
if (!empty($params)) {
    $stmt = $conn->prepare("SELECT * FROM purchases $where_clause ORDER BY id DESC");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $purchases_result = $stmt->get_result();
} else {
    $purchases_result = mysqli_query($conn, "SELECT * FROM purchases ORDER BY id DESC");
}

// Get purchase statistics
$total_spent = mysqli_fetch_assoc(@mysqli_query($conn, "SELECT COALESCE(SUM(total), 0) as total FROM purchases"))['total'];
$purchase_count = mysqli_fetch_assoc(@mysqli_query($conn, "SELECT COUNT(*) as count FROM purchases"))['count'];

// Handle print request
$print_purchase = null;
if (isset($_GET['print_id'])) {
    $print_id = intval($_GET['print_id']);
    $print_result = mysqli_query($conn, "SELECT * FROM purchases WHERE id = $print_id");
    $print_purchase = mysqli_fetch_assoc($print_result);
}

// Handle edit request
$edit_purchase = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_result = mysqli_query($conn, "SELECT * FROM purchases WHERE id = $edit_id");
    $edit_purchase = mysqli_fetch_assoc($edit_result);
}

// Reset result pointers for reuse
mysqli_data_seek($categories_result, 0);
mysqli_data_seek($departments_result, 0);
?>

<!-- Additional CSS for Purchases Management -->
<style>
    /* 60-30-10 Color Rule - Admin Purchases */
    :root {
        --bg-60-white: #ffffff;
        --bg-60-light: #f8f9fa;
        
        /* 30% - Blue-Gray Tones */
        --bg-30-panel: #e9ecef;
        --bg-30-card: #dee2e6;
        --bg-30-slate: #6c757d;
        
        /* 10% - Deep Accent */
        --accent-10-primary: #0d6efd;
        --accent-10-success: #198754;
        --accent-10-warning: #ffc107;
        --accent-10-danger: #dc3545;
        --accent-10-info: #0dcaf0;
        
        /* Text Colors */
        --text-primary: #212529;
        --text-secondary: #495057;
        --text-muted: #6c757d;
    }

    .stat-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.1);
        transition: transform 0.3s ease;
        background: var(--bg-60-white);
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .bg-primary-custom {
        background: linear-gradient(135deg, var(--accent-10-primary), #0a58ca);
        color: white;
    }

    .bg-success-custom {
        background: linear-gradient(135deg, var(--accent-10-success), #146c43);
        color: white;
    }

    .bg-info-custom {
        background: linear-gradient(135deg, var(--accent-10-info), #0aa2c0);
        color: white;
    }

    .bg-warning-custom {
        background: linear-gradient(135deg, var(--accent-10-warning), #cc9a00);
        color: #000;
    }

    .print-only {
        display: none;
    }

    .form-label {
        font-weight: 500;
        color: var(--text-secondary);
        margin-bottom: 6px;
    }

    .form-select, .form-control {
        border-radius: 10px;
        padding: 12px 15px;
        border: 1px solid var(--bg-30-card);
        transition: all 0.3s ease;
        background: var(--bg-60-white);
    }

    .form-select:focus, .form-control:focus {
        border-color: var(--accent-10-primary);
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
    }

    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.08);
        background: var(--bg-60-white);
    }

    .card-header {
        background: transparent;
        border-bottom: 1px solid var(--bg-30-panel);
        padding: 20px;
        font-weight: 600;
        color: var(--text-primary);
    }

    /* Table Styles */
    .table-container {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .table {
        margin-bottom: 0;
    }

    .table thead {
        background: linear-gradient(135deg, var(--accent-10-primary), #0a58ca);
        color: white;
    }

    .table th {
        font-weight: 600;
        border: none;
        padding: 15px 12px;
        white-space: nowrap;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .table th:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .table th .sort-icon {
        margin-left: 5px;
        opacity: 0.7;
    }

    .table th.sorted .sort-icon {
        opacity: 1;
    }

    .table td {
        padding: 12px;
        vertical-align: middle;
        border-color: #f0f0f0;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background-color: var(--bg-60-light);
    }

    /* Badge Styles */
    .badge-category {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-department {
        background: linear-gradient(135deg, #11998e, #38ef7d);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-status.completed {
        background: linear-gradient(135deg, var(--accent-10-success), #146c43);
        color: white;
    }

    .badge-status.pending {
        background: linear-gradient(135deg, var(--accent-10-warning), #cc9a00);
        color: #000;
    }

    /* Action Button Styles */
    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-action:hover {
        transform: scale(1.1);
    }

    .btn-print {
        background: linear-gradient(135deg, var(--accent-10-info), #0aa2c0);
        color: white;
        border: none;
    }

    .btn-edit {
        background: linear-gradient(135deg, var(--accent-10-warning), #cc9a00);
        color: #000;
        border: none;
    }

    .btn-delete {
        background: linear-gradient(135deg, var(--accent-10-danger), #b02a37);
        color: white;
        border: none;
    }

    /* Search and Filter Styles */
    .search-box {
        position: relative;
    }

    .search-box .form-control {
        padding-left: 45px;
    }

    .search-box .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }

    .filter-section {
        background: var(--bg-60-light);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .filter-section .form-select,
    .filter-section .form-control {
        background: white;
    }

    /* Print Styles */
    @media print {
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            background: white !important;
        }

        .no-print, .sb-navbar, #layoutSidenav_nav, .sb-sidenav, footer {
            display: none !important;
        }

        #layoutSidenav_content {
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
        }

        .print-only {
            display: block !important;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            background: white;
            z-index: 9999;
        }

        .purchase-bill {
            border: 2px solid #000;
            padding: 15mm;
            margin: 0;
        }

        .bill-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .bill-header h1 {
            font-size: 28px;
            margin: 0;
            font-weight: bold;
            color: #000;
        }

        .bill-header h3 {
            font-size: 16px;
            margin: 8px 0;
            color: #000;
        }

        .bill-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }

        .bill-details div {
            font-size: 14px;
            color: #000;
        }

        .bill-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .bill-table th,
        .bill-table td {
            border: 1px solid #000;
            padding: 12px;
            font-size: 14px;
            text-align: center;
            color: #000;
        }

        .bill-table th {
            background: #f0f0f0;
            font-weight: bold;
        }

        .bill-total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            padding: 15px 0;
            border-top: 2px solid #000;
            color: #000;
        }

        .bill-footer {
            margin-top: 25px;
            font-size: 12px;
            color: #000;
        }

        .bill-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }

        .bill-signatures div {
            text: center;
            width: 150px;
        }

        .bill-signatures .line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 8px;
            color: #000;
        }
    }
</style>

<div class="container-fluid px-4 mt-4">
    <?php if ($print_purchase): ?>
    <!-- Print View -->
    <div class="print-only purchase-bill">
        <div class="bill-header">
            <h1>BIRGUNJ INSTITUTE OF TECHNOLOGY</h1>
            <h3>Birgunj, Nepal</h3>
            <h3 style="font-weight: bold; margin-top: 15px;">PURCHASE BILL</h3>
        </div>

        <div class="bill-details">
            <div>
                <strong>Bill No:</strong> <?php echo $print_purchase['id']; ?><br>
                <strong>Date:</strong> <?php echo date('d/m/Y', strtotime($print_purchase['date'])); ?><br>
                <strong>Time:</strong> <?php echo date('h:i A'); ?>
            </div>
            <div style="text-align: right;">
                <strong>Category:</strong> <?php echo htmlspecialchars($print_purchase['category'] ?? 'N/A'); ?><br>
                <strong>Department:</strong> <?php echo htmlspecialchars($print_purchase['department'] ?? 'N/A'); ?>
            </div>
        </div>

        <table class="bill-table">
            <thead>
                <tr>
                    <th style="width: 10%;">S.N.</th>
                    <th style="width: 35%;">Item Name</th>
                    <th style="width: 15%;">Category</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 15%;">Rate (Rs.)</th>
                    <th style="width: 15%;">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td><?php echo htmlspecialchars($print_purchase['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($print_purchase['category'] ?? '-'); ?></td>
                    <td><?php echo $print_purchase['quantity']; ?></td>
                    <td><?php echo number_format($print_purchase['price'], 2); ?></td>
                    <td><?php echo number_format($print_purchase['total'], 2); ?></td>
                </tr>
                <?php for ($i = 2; $i <= 5; $i++): ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <div style="margin-bottom: 15px;">
            <strong>Supplier:</strong> <?php echo htmlspecialchars($print_purchase['supplier'] ?? 'N/A'); ?>
        </div>

        <div class="bill-total">
            Total Amount: Rs. <?php echo number_format($print_purchase['total'], 2); ?>
        </div>

        <div class="bill-footer">
            <p><strong>Notes:</strong> <?php echo htmlspecialchars($print_purchase['notes'] ?? 'No notes'); ?></p>
        </div>

        <div class="bill-signatures">
            <div>
                <div class="line">Prepared By</div>
            </div>
            <div>
                <div class="line">Checked By</div>
            </div>
            <div>
                <div class="line">Approved By</div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 25px; font-size: 11px; color: #000;">
            <p>This is a computer-generated document. No signature required.</p>
        </div>
    </div>

    <div class="no-print text-center mt-4">
        <button onclick="window.print()" class="btn btn-primary-custom btn-lg">
            <i class="bi bi-printer me-2"></i>Print Bill
        </button>
        <a href="purchases.php" class="btn btn-secondary btn-lg ms-2">
            <i class="bi bi-arrow-left me-2"></i>Back to Purchases
        </a>
    </div>

    <?php elseif ($edit_purchase): ?>
    <!-- Edit Purchase Form -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-pencil-square me-2"></i>Edit Purchase #<?php echo $edit_purchase['id']; ?></span>
                    <a href="purchases.php" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>Cancel
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="purchase_id" value="<?php echo $edit_purchase['id']; ?>">

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" name="product_name" class="form-control" 
                                        value="<?php echo htmlspecialchars($edit_purchase['product_name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" 
                                        value="<?php echo $edit_purchase['date']; ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="category" class="form-select">
                                        <option value="">Select Category</option>
                                        <?php 
                                        mysqli_data_seek($categories_result, 0);
                                        while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                                            <option value="<?php echo $cat['name']; ?>" 
                                                <?php echo ($edit_purchase['category'] == $cat['name']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Department</label>
                                    <select name="department" class="form-select">
                                        <option value="">Select Department</option>
                                        <?php 
                                        mysqli_data_seek($departments_result, 0);
                                        while ($dept = mysqli_fetch_assoc($departments_result)): ?>
                                            <option value="<?php echo $dept['name']; ?>"
                                                <?php echo ($edit_purchase['department'] == $dept['name']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($dept['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" class="form-control" min="1" 
                                        value="<?php echo $edit_purchase['quantity']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Unit Price (Rs.) <span class="text-danger">*</span></label>
                                    <input type="number" name="price" class="form-control" min="0" step="0.01" 
                                        value="<?php echo $edit_purchase['price']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Total (Rs.)</label>
                                    <input type="text" class="form-control" 
                                        value="<?php echo number_format($edit_purchase['total'], 2); ?>" readonly
                                        style="background: var(--bg-60-light);">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Supplier</label>
                            <input type="text" name="supplier" class="form-control" 
                                value="<?php echo htmlspecialchars($edit_purchase['supplier']); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"><?php echo htmlspecialchars($edit_purchase['notes'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="update_purchase" class="btn btn-primary-custom flex-grow-1">
                                <i class="bi bi-check-circle me-1"></i> Update Purchase
                            </button>
                            <a href="purchases.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- Normal View -->
    <h1 class="mb-4"><i class="bi bi-cart-plus me-2"></i>Purchases Management</h1>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-primary-custom">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 1.8rem; font-weight: bold;"><?php echo $purchase_count; ?></div>
                            <div>Total Purchases</div>
                        </div>
                        <div class="stat-icon bg-white bg-opacity-25">
                            <i class="bi bi-cart-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-success-custom">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 1.8rem; font-weight: bold;">Rs. <?php echo number_format($total_spent, 0); ?></div>
                            <div>Total Spent</div>
                        </div>
                        <div class="stat-icon bg-white bg-opacity-25">
                            <i class="bi bi-currency-dollar fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-info-custom">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 1.8rem; font-weight: bold;"><?php echo mysqli_num_rows($categories_result); ?></div>
                            <div>Categories</div>
                        </div>
                        <div class="stat-icon bg-white bg-opacity-25">
                            <i class="bi bi-folder fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-warning-custom">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 1.8rem; font-weight: bold;"><?php echo mysqli_num_rows($departments_result); ?></div>
                            <div>Departments</div>
                        </div>
                        <div class="stat-icon bg-white bg-opacity-50">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Add Purchase Form -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-primary-custom">
                    <i class="bi bi-plus-circle me-2"></i>Add New Purchase
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="product_name" class="form-control" placeholder="Enter product name" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="category" class="form-select">
                                        <option value="">Select Category</option>
                                        <?php 
                                        mysqli_data_seek($categories_result, 0);
                                        while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                                            <option value="<?php echo $cat['name']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Department</label>
                                    <select name="department" class="form-select">
                                        <option value="">Select Department</option>
                                        <?php 
                                        mysqli_data_seek($departments_result, 0);
                                        while ($dept = mysqli_fetch_assoc($departments_result)): ?>
                                            <option value="<?php echo $dept['name']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Price (Rs.) <span class="text-danger">*</span></label>
                                    <input type="number" name="price" class="form-control" min="0" step="0.01" value="0.00" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Supplier</label>
                            <input type="text" name="supplier" class="form-control" placeholder="Supplier Name">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="add_purchase" class="btn btn-primary-custom flex-grow-1">
                                <i class="bi bi-check-circle me-1"></i> Record Purchase
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- All Purchases Table -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span><i class="bi bi-table me-2"></i>All Purchases</span>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge bg-secondary"><?php echo mysqli_num_rows($purchases_result); ?> Records</span>
                        <a href="purchases.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </div>
                
                <!-- Search and Filter Section -->
                <div class="filter-section mx-3 mt-3">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <div class="search-box">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" name="search" class="form-control" 
                                    placeholder="Product, supplier, notes..." 
                                    value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="filter_category" class="form-select">
                                <option value="">All Categories</option>
                                <?php 
                                mysqli_data_seek($categories_result, 0);
                                while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                                    <option value="<?php echo $cat['name']; ?>" 
                                        <?php echo ($filter_category == $cat['name']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Department</label>
                            <select name="filter_department" class="form-select">
                                <option value="">All Departments</option>
                                <?php 
                                mysqli_data_seek($departments_result, 0);
                                while ($dept = mysqli_fetch_assoc($departments_result)): ?>
                                    <option value="<?php echo $dept['name']; ?>"
                                        <?php echo ($filter_department == $dept['name']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary-custom w-100">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover" id="purchasesTable">
                            <thead>
                                <tr>
                                    <th onclick="sortTable(0)">ID <i class="bi bi-chevron-expand sort-icon"></i></th>
                                    <th onclick="sortTable(1)">Product <i class="bi bi-chevron-expand sort-icon"></i></th>
                                    <th>Category</th>
                                    <th>Department</th>
                                    <th onclick="sortTable(4)">Qty <i class="bi bi-chevron-expand sort-icon"></i></th>
                                    <th onclick="sortTable(5)">Total (Rs.) <i class="bi bi-chevron-expand sort-icon"></i></th>
                                    <th onclick="sortTable(6)">Date <i class="bi bi-chevron-expand sort-icon"></i></th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $row_num = 1;
                                mysqli_data_seek($purchases_result, 0);
                                while ($purchase = mysqli_fetch_assoc($purchases_result)): ?>
                                    <tr>
                                        <td><strong>#<?php echo $purchase['id']; ?></strong></td>
                                        <td>
                                            <div class="fw-medium"><?php echo htmlspecialchars($purchase['product_name']); ?></div>
                                            <?php if (!empty($purchase['supplier'])): ?>
                                                <small class="text-muted"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($purchase['supplier']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($purchase['category'])): ?>
                                                <span class="badge-category"><?php echo htmlspecialchars($purchase['category']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($purchase['department'])): ?>
                                                <span class="badge-department"><?php echo htmlspecialchars($purchase['department']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $purchase['quantity']; ?></td>
                                        <td><strong class="text-success"><?php echo number_format($purchase['total'], 2); ?></strong></td>
                                        <td><?php echo date('d M Y', strtotime($purchase['date'])); ?></td>
                                        <td>
                                            <span class="badge-status <?php echo $purchase['status']; ?>">
                                                <?php echo ucfirst($purchase['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="purchases.php?print_id=<?php echo $purchase['id']; ?>" 
                                                   class="btn btn-action btn-print" title="Print">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                                <a href="purchases.php?edit_id=<?php echo $purchase['id']; ?>" 
                                                   class="btn btn-action btn-edit" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="purchases.php?delete_id=<?php echo $purchase['id']; ?>" 
                                                   class="btn btn-action btn-delete" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this purchase?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php $row_num++; endwhile; ?>
                                <?php if (mysqli_num_rows($purchases_result) == 0): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-3 mb-0">No purchases found.</p>
                                            <p class="text-muted small">Try adjusting your search or filter criteria.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Table sorting functionality
    let sortDirection = true;
    
    function sortTable(columnIndex) {
        const table = document.getElementById('purchasesTable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Get the sort icon element
        const th = table.querySelectorAll('th')[columnIndex];
        const icon = th.querySelector('.sort-icon');
        
        // Reset all icons
        table.querySelectorAll('.sort-icon').forEach(i => {
            i.className = 'bi bi-chevron-expand sort-icon';
        });
        
        // Toggle sort direction
        sortDirection = !sortDirection;
        icon.className = sortDirection ? 'bi bi-chevron-down sort-icon' : 'bi bi-chevron-up sort-icon';
        
        // Sort rows
        rows.sort((a, b) => {
            let aVal, bVal;
            
            switch(columnIndex) {
                case 0: // ID
                    aVal = parseInt(a.cells[0].textContent.replace('#', ''));
                    bVal = parseInt(b.cells[0].textContent.replace('#', ''));
                    break;
                case 1: // Product Name
                    aVal = a.cells[1].textContent.toLowerCase().trim();
                    bVal = b.cells[1].textContent.toLowerCase().trim();
                    break;
                case 4: // Quantity
                    aVal = parseInt(a.cells[4].textContent);
                    bVal = parseInt(b.cells[4].textContent);
                    break;
                case 5: // Total
                    aVal = parseFloat(a.cells[5].textContent.replace(/[^0-9.-]+/g, ''));
                    bVal = parseFloat(b.cells[5].textContent.replace(/[^0-9.-]+/g, ''));
                    break;
                case 6: // Date
                    aVal = new Date(a.cells[6].textContent);
                    bVal = new Date(b.cells[6].textContent);
                    break;
                default:
                    return 0;
            }
            
            if (aVal < bVal) return sortDirection ? -1 : 1;
            if (aVal > bVal) return sortDirection ? 1 : -1;
            return 0;
        });
        
        // Reorder rows
        rows.forEach(row => tbody.appendChild(row));
    }
    
    // Auto-submit filter form on select change
    document.querySelectorAll('.filter-section select').forEach(select => {
        select.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
    
    // Clear filters button
    function clearFilters() {
        window.location.href = 'purchases.php';
    }
</script>

<?php include "Includes/footer.php"; ?>
