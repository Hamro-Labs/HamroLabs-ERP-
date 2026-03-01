<?php
include 'config/authentication.php';
include 'Includes/header.php';

$message = "";
$error = "";

// Handle form submissions
if (isset($_POST['save_general'])) {
    $institute_name = htmlspecialchars($_POST['institute_name']);
    $store_name = htmlspecialchars($_POST['store_name']);
    $address = htmlspecialchars($_POST['address']);
    $phone = htmlspecialchars($_POST['phone']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Use INSERT ... ON DUPLICATE KEY UPDATE for upsert
    $query = "INSERT INTO system_settings (id, institute_name, store_name, address, phone, email) 
              VALUES (1, ?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE 
              institute_name = VALUES(institute_name),
              store_name = VALUES(store_name),
              address = VALUES(address),
              phone = VALUES(phone),
              email = VALUES(email)";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssss", $institute_name, $store_name, $address, $phone, $email);

    if (mysqli_stmt_execute($stmt)) {
        $message = "General settings saved successfully!";
    } else {
        $error = "Failed to save settings: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// Fetch current settings
$settings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM system_settings WHERE id = 1"));
if (!$settings) {
    $settings = [
        'institute_name' => 'Birgunj Institute of Technology',
        'store_name' => 'StoreMart',
        'address' => 'Birgunj, Parsa, Nepal',
        'phone' => '+977 9811144402',
        'email' => 'store@bit.edu.np'
    ];
}
?>

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .settings-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .nav-pills .nav-link.active {
            background-color: #0d6efd;
        }
    </style>
</head>

<div class="container-fluid px-4 mt-4">
    <h1 class="mb-4"><i class="bi bi-gear me-2"></i>System Settings</h1>

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

    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-list me-2"></i>Settings Menu
                </div>
                <div class="card-body p-0">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#general">
                            <i class="bi bi-building me-2"></i>General
                        </button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#security">
                            <i class="bi bi-shield-lock me-2"></i>Security
                        </button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#about">
                            <i class="bi bi-info-circle me-2"></i>About
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="tab-content">
                <!-- General Settings -->
                <div class="tab-pane fade show active" id="general">
                    <div class="settings-section">
                        <h4><i class="bi bi-building me-2"></i>General Settings</h4>
                        <hr>
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Institute Name</label>
                                    <input type="text" name="institute_name" class="form-control"
                                        value="<?php echo htmlspecialchars($settings['institute_name'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Store Name</label>
                                    <input type="text" name="store_name" class="form-control"
                                        value="<?php echo htmlspecialchars($settings['store_name'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-control"
                                        value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control"
                                        value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control"
                                        rows="2"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <button type="submit" name="save_general" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Save Settings
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="tab-pane fade" id="security">
                    <div class="settings-section">
                        <h4><i class="bi bi-shield-lock me-2"></i>Security Recommendations</h4>
                        <hr>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Password hashing is enabled (BCRYPT)
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Session management is active
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Prepared statements are used for database queries
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                Consider enabling HTTPS for production
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                Change database password regularly
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- About -->
                <div class="tab-pane fade" id="about">
                    <div class="settings-section">
                        <h4><i class="bi bi-info-circle me-2"></i>About This System</h4>
                        <hr>
                        <h5>Store Management System</h5>
                        <p class="text-muted">Version 1.0.0</p>
                        <p>A comprehensive web-based solution for managing institutional resources including:</p>
                        <ul>
                            <li>Product/Inventory Management</li>
                            <li>Rental Issue Tracking</li>
                            <li>User Management</li>
                            <li>Purchase Recording</li>
                            <li>Communication System</li>
                        </ul>
                        <p class="text-muted mt-4">
                            <strong>Developed for:</strong> Birgunj Institute of Technology<br>
                            <strong>Location:</strong> Birgunj, Parsa, Nepal
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'Includes/footer.php'; ?>