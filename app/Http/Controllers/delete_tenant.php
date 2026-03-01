<?php
/**
 * Hamro ERP — Delete Tenant API
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
    
    // Get tenant ID
    $id = sanitizeInput($_POST['id'] ?? '');
    
    if (!$id) {
        throw new Exception("Tenant ID is required.");
    }
    
    $pdo->beginTransaction();
    
    // Check if tenant exists
    $stmt = $pdo->prepare("SELECT name FROM tenants WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    $tenant = $stmt->fetch();
    
    if (!$tenant) {
        throw new Exception("Tenant not found.");
    }
    
    // Soft delete tenant
    $stmt = $pdo->prepare("UPDATE tenants SET deleted_at = NOW(), status = 'suspended' WHERE id = ?");
    $stmt->execute([$id]);
    
    // Suspend all users in this tenant
    $stmt = $pdo->prepare("UPDATE users SET status = 'suspended' WHERE tenant_id = ?");
    $stmt->execute([$id]);
    
    // Log the action
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, description, created_at) VALUES (?, 'Tenant Deleted', ?, NOW())");
    $stmt->execute([1, "Tenant '{$tenant['name']}' (ID: $id) was soft-deleted and users suspended."]); // Mocking superadmin user_id = 1
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Institute deleted successfully!'
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
