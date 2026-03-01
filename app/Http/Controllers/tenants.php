<?php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../../config/config.php';
}

header('Content-Type: application/json');

try {
    $db = getDBConnection();
    
    $status = sanitizeInput($_GET['status'] ?? '');
    
    $query = "SELECT * FROM tenants WHERE deleted_at IS NULL";
    if ($status === 'suspended') {
        $query .= " AND status = 'suspended'";
    }
    $query .= " ORDER BY created_at DESC";
    
    $tenants = $db->query($query)->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $tenants]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
