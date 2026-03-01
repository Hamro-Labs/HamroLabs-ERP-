<?php
/**
 * Hamro ERP — Update Tenant API
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
    $id = sanitizeInput($_POST['id'] ?? '');
    $name = sanitizeInput($_POST['name'] ?? '');
    $nepaliName = sanitizeInput($_POST['nepaliName'] ?? '');
    $subdomain = sanitizeInput($_POST['subdomain'] ?? '');
    $brandColor = sanitizeInput($_POST['brandColor'] ?? '#009E7E');
    $tagline = sanitizeInput($_POST['tagline'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    
    $plan = sanitizeInput($_POST['plan'] ?? 'starter');
    $status = sanitizeInput($_POST['status'] ?? 'trial');
    
    if (!$id || !$name || !$subdomain) {
        throw new Exception("Required fields are missing.");
    }
    
    // Check if subdomain exists for other tenants
    $stmt = $pdo->prepare("SELECT id FROM tenants WHERE subdomain = ? AND id != ?");
    $stmt->execute([$subdomain, $id]);
    if ($stmt->fetch()) {
        throw new Exception("The subdomain '$subdomain' is already taken.");
    }

    $pdo->beginTransaction();
    
    // Update Tenant
    $stmt = $pdo->prepare("UPDATE tenants SET name = ?, nepali_name = ?, subdomain = ?, brand_color = ?, tagline = ?, address = ?, phone = ?, plan = ?, status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$name, $nepaliName, $subdomain, $brandColor, $tagline, $address, $phone, $plan, $status, $id]);
    
    // Log the action
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, description, created_at) VALUES (?, 'Tenant Updated', ?, NOW())");
    $stmt->execute([1, "Tenant '$name' ($subdomain) updated."]); // Mocking superadmin user_id = 1
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Institute updated successfully!'
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
