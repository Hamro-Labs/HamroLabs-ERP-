<?php
/**
 * Super Admin Tenants API
 * Returns JSON data for tenants management
 */

if (!defined('APP_ROOT')) {
    require_once __DIR__ . '/../../../../config/config.php';
}

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Auth check
if (!function_exists('isLoggedIn') || !isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
if (!$user || ($user['role'] ?? '') !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? 'list';

try {
    $db = getDBConnection();
    
    switch ($action) {
        case 'list':
            $status = $_GET['status'] ?? null;
            $search = $_GET['search'] ?? '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = ($page - 1) * $limit;
            
            $where = [];
            $params = [];
            
            if ($status && $status !== 'all') {
                $where[] = 't.status = :status';
                $params['status'] = $status;
            }
            
            if ($search) {
                $where[] = '(t.name LIKE :search OR t.subdomain LIKE :search2 OR t.phone LIKE :search3)';
                $params['search'] = "%$search%";
                $params['search2'] = "%$search%";
                $params['search3'] = "%$search%";
            }
            
            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Get total count
            $countStmt = $db->prepare("SELECT COUNT(*) FROM tenants t $whereClause");
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            
            // Get tenants
            $stmt = $db->prepare("
                SELECT t.*, 
                       s.plan as subscription_plan,
                       s.status as subscription_status,
                       s.end_date as subscription_end,
                       (SELECT COUNT(*) FROM users WHERE tenant_id = t.id) as user_count
                FROM tenants t
                LEFT JOIN subscriptions s ON t.id = s.tenant_id AND s.status = 'active'
                $whereClause
                ORDER BY t.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            $params['limit'] = $limit;
            $params['offset'] = $offset;
            $stmt->execute($params);
            $tenants = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'data' => $tenants,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
            break;
            
        case 'get':
            $id = (int)$_GET['id'];
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Tenant ID required']);
                exit;
            }
            
            $stmt = $db->prepare("
                SELECT t.*, 
                       s.plan as subscription_plan,
                       s.status as subscription_status,
                       s.start_date as subscription_start,
                       s.end_date as subscription_end,
                       s.billing_cycle
                FROM tenants t
                LEFT JOIN subscriptions s ON t.id = s.tenant_id AND s.status = 'active'
                WHERE t.id = :id
            ");
            $stmt->execute(['id' => $id]);
            $tenant = $stmt->fetch();
            
            if (!$tenant) {
                echo json_encode(['success' => false, 'message' => 'Tenant not found']);
                exit;
            }
            
            // Get user count
            $userCount = $db->prepare("SELECT COUNT(*) FROM users WHERE tenant_id = ?");
            $userCount->execute([$id]);
            $tenant['user_count'] = $userCount->fetchColumn();
            
            // Get student count
            $studentCount = $db->prepare("SELECT COUNT(*) FROM students WHERE tenant_id = ?");
            $studentCount->execute([$id]);
            $tenant['student_count'] = $studentCount->fetchColumn();
            
            echo json_encode(['success' => true, 'data' => $tenant]);
            break;
            
        case 'create':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['name']) || empty($input['subdomain'])) {
                echo json_encode(['success' => false, 'message' => 'Name and subdomain are required']);
                exit;
            }
            
            // Check subdomain uniqueness
            $check = $db->prepare("SELECT id FROM tenants WHERE subdomain = ?");
            $check->execute([$input['subdomain']]);
            if ($check->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Subdomain already exists']);
                exit;
            }
            
            $stmt = $db->prepare("
                INSERT INTO tenants (name, subdomain, institute_type, phone, address, province, plan, status, student_limit, sms_credits, trial_ends_at, created_at)
                VALUES (:name, :subdomain, :institute_type, :phone, :address, :province, :plan, :status, :student_limit, :sms_credits, :trial_ends_at, NOW())
            ");
            
            $stmt->execute([
                'name' => $input['name'],
                'subdomain' => $input['subdomain'],
                'institute_type' => $input['institute_type'] ?? null,
                'phone' => $input['phone'] ?? null,
                'address' => $input['address'] ?? null,
                'province' => $input['province'] ?? null,
                'plan' => $input['plan'] ?? 'starter',
                'status' => $input['status'] ?? 'trial',
                'student_limit' => $input['student_limit'] ?? 100,
                'sms_credits' => $input['sms_credits'] ?? 500,
                'trial_ends_at' => date('Y-m-d H:i:s', strtotime('+60 days'))
            ]);
            
            $tenantId = $db->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'Tenant created successfully',
                'data' => ['id' => $tenantId]
            ]);
            break;
            
        case 'update':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)$input['id'];
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Tenant ID required']);
                exit;
            }
            
            $fields = [];
            $params = ['id' => $id];
            
            $allowedFields = ['name', 'phone', 'address', 'province', 'plan', 'status', 'student_limit', 'sms_credits', 'institute_type'];
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $fields[] = "$field = :$field";
                    $params[$field] = $input[$field];
                }
            }
            
            if (empty($fields)) {
                echo json_encode(['success' => false, 'message' => 'No fields to update']);
                exit;
            }
            
            $stmt = $db->prepare("UPDATE tenants SET " . implode(', ', $fields) . " WHERE id = :id");
            $stmt->execute($params);
            
            echo json_encode(['success' => true, 'message' => 'Tenant updated successfully']);
            break;
            
        case 'delete':
            $id = (int)$_GET['id'];
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Tenant ID required']);
                exit;
            }
            
            $stmt = $db->prepare("DELETE FROM tenants WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Tenant deleted successfully']);
            break;
            
        case 'stats':
            // Quick stats for tenant management
            $stats = [
                'total' => 0,
                'active' => 0,
                'trial' => 0,
                'suspended' => 0
            ];
            
            $stmt = $db->query("
                SELECT status, COUNT(*) as count 
                FROM tenants 
                GROUP BY status
            ");
            while ($row = $stmt->fetch()) {
                $stats[$row['status']] = (int)$row['count'];
                $stats['total'] += (int)$row['count'];
            }
            
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
