<?php
/**
 * Hamro ERP — Save New Tenant API
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../../config/config.php';
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get POST data
    $name = sanitizeInput($_POST['name'] ?? '');
    $nepaliName = sanitizeInput($_POST['nepaliName'] ?? '');
    $subdomain = sanitizeInput($_POST['subdomain'] ?? '');
    $brandColor = sanitizeInput($_POST['brandColor'] ?? '#009E7E');
    $tagline = sanitizeInput($_POST['tagline'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? ''); // Institute Email
    
    $adminName = sanitizeInput($_POST['adminName'] ?? '');
    $adminEmail = sanitizeInput($_POST['adminEmail'] ?? '');
    $adminPhone = sanitizeInput($_POST['adminPhone'] ?? '');
    $adminPass = $_POST['adminPass'] ?? ''; // Don't sanitize password as it might have special chars
    
    $plan = sanitizeInput($_POST['plan'] ?? 'starter');
    $status = sanitizeInput($_POST['status'] ?? 'trial');
    
    // Simple validation
    if (!$name || !$subdomain || !$adminEmail || !$adminPass) {
        throw new Exception("Required fields are missing.");
    }
    
    // Check if subdomain exists
    $stmt = $pdo->prepare("SELECT id FROM tenants WHERE subdomain = ?");
    $stmt->execute([$subdomain]);
    if ($stmt->fetch()) {
        throw new Exception("The subdomain '$subdomain' is already taken.");
    }
    
    // Check if admin email exists globally or within tenant (here globally for admin unique check)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    if ($stmt->fetch()) {
        throw new Exception("The admin email '$adminEmail' is already registered.");
    }

    $pdo->beginTransaction();
    
    // 1. Insert Tenant
    $stmt = $pdo->prepare("INSERT INTO tenants (name, nepali_name, subdomain, brand_color, tagline, address, phone, plan, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$name, $nepaliName, $subdomain, $brandColor, $tagline, $address, $phone, $plan, $status]);
    $tenantId = $pdo->lastInsertId();
    
    // 2. Create Admin User
    $passwordHash = password_hash($adminPass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (tenant_id, role, email, password_hash, phone, name, status, created_at) VALUES (?, 'instituteadmin', ?, ?, ?, ?, 'active', NOW())");
    $stmt->execute([$tenantId, $adminEmail, $passwordHash, $adminPhone, $adminName]);
    $userId = $pdo->lastInsertId();
    
    // 3. Log the action
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, description, created_at) VALUES (?, 'Tenant Created', ?, NOW())");
    $stmt->execute([1, "New tenant '$name' ($subdomain) created with admin '$adminEmail'"]); // Mocking superadmin user_id = 1
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Institute registered successfully!',
        'tenantId' => $tenantId
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
