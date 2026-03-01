<?php
/**
 * Hamro ERP — Update Tenant Plan API
 */

require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $id = sanitizeInput($_POST['id'] ?? '');
    $plan = sanitizeInput($_POST['plan'] ?? '');
    
    if (!$id || !$plan) {
        throw new Exception("Missing parameters.");
    }

    // Update plan and also student_limit based on plan
    $limits = [
        'starter' => 150,
        'growth' => 500,
        'professional' => 1500,
        'enterprise' => 10000 
    ];
    $limit = $limits[$plan] ?? 100;

    $stmt = $pdo->prepare("UPDATE tenants SET plan = ?, student_limit = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$plan, $limit, $id]);
    
    // Log the action
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, description, created_at) VALUES (?, 'Plan Updated', ?, NOW())");
    $stmt->execute([1, "Plan for tenant ID $id updated to $plan ($limit students)"]);

    echo json_encode(['success' => true, 'message' => 'Plan updated successfully.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
