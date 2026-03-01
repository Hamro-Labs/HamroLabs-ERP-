<?php
/**
 * Inquiries API Controller
 * Handles fetching and managing inquiries for the current tenant
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../../config/config.php';
}

header('Content-Type: application/json');

// Ensure user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
$tenantId = $user['tenant_id'] ?? null;
$userRole = $user['role'] ?? '';

if (!$tenantId) {
    echo json_encode(['success' => false, 'message' => 'Tenant ID missing']);
    exit;
}

// Allow frontdesk and instituteadmin roles
$allowedRoles = ['instituteadmin', 'frontdesk', 'superadmin'];
if (!in_array($userRole, $allowedRoles)) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Insufficient permissions.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDBConnection();

    if ($method === 'GET') {
        $id = !empty($_GET['id']) ? (int)$_GET['id'] : null;

        if ($id) {
            // Full details for single inquiry
            $query = "SELECT i.*, c.name as course_name 
                      FROM inquiries i 
                      LEFT JOIN courses c ON i.course_id = c.id
                      WHERE i.id = :id AND i.tenant_id = :tid";
            $params = ['id' => $id, 'tid' => $tenantId];
        } else {
            // Optimized column list for the main table/list
            $query = "SELECT i.id, i.full_name, i.phone, i.email, i.source, i.status, i.created_at, i.updated_at,
                             c.name as course_name 
                      FROM inquiries i 
                      LEFT JOIN courses c ON i.course_id = c.id
                      WHERE i.tenant_id = :tid 
                        AND i.deleted_at IS NULL";
            
            $params = ['tid' => $tenantId];

            if (!empty($_GET['search'])) {
                $search = '%' . $_GET['search'] . '%';
                $query .= " AND (i.full_name LIKE :search OR i.phone LIKE :search OR i.email LIKE :search)";
                $params['search'] = $search;
            }

            if (!empty($_GET['status'])) {
                $query .= " AND i.status = :status";
                $params['status'] = $_GET['status'];
            }

            $query .= " ORDER BY i.created_at DESC LIMIT 500";
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($id && empty($inquiries)) {
            echo json_encode(['success' => false, 'message' => 'Inquiry not found']);
        } else {
            echo json_encode(['success' => true, 'data' => $id ? $inquiries[0] : $inquiries]);
        }
    }
    
    if ($method === 'POST' || $method === 'PUT') {
        // Create or update inquiry
        $input = $_POST;
        
        // Handle JSON input as well
        if (empty($input)) {
            $jsonInput = file_get_contents('php://input');
            $input = json_decode($jsonInput, true) ?? [];
        }
        
        // Validate required fields
        if (empty($input['full_name'])) {
            echo json_encode(['success' => false, 'message' => 'Full name is required']);
            exit;
        }
        
        if (empty($input['phone'])) {
            echo json_encode(['success' => false, 'message' => 'Phone number is required']);
            exit;
        }
        
        if (empty($input['course_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please select a course']);
            exit;
        }
        
        if (empty($input['source'])) {
            echo json_encode(['success' => false, 'message' => 'Please select a source']);
            exit;
        }
        
        // Check if updating or creating
        $inquiryId = $input['id'] ?? null;
        
        // Get database columns to check if optional fields exist
        $stmt = $db->query("SHOW COLUMNS FROM inquiries LIKE 'alt_phone'");
        $hasAltPhone = $stmt->fetch() !== false;
        
        $stmt = $db->query("SHOW COLUMNS FROM inquiries LIKE 'address'");
        $hasAddress = $stmt->fetch() !== false;
        
        if ($inquiryId) {
            // Update existing inquiry
            $query = "UPDATE inquiries SET 
                full_name = :name,
                phone = :phone,
                email = :email,
                course_id = :course_id,
                source = :source,
                status = :status,
                notes = :notes,
                updated_at = NOW()";
            
            $params = [
                'id' => $inquiryId,
                'name' => $input['full_name'],
                'phone' => $input['phone'],
                'email' => $input['email'] ?? null,
                'course_id' => $input['course_id'],
                'source' => $input['source'],
                'status' => $input['status'] ?? 'pending',
                'notes' => $input['notes'] ?? null,
                'tid' => $tenantId
            ];
            
            // Add optional fields if they exist in the table
            if ($hasAltPhone) {
                $query .= ", alt_phone = :alt_phone";
                $params['alt_phone'] = $input['alt_phone'] ?? null;
            }
            if ($hasAddress) {
                $query .= ", address = :address";
                $params['address'] = $input['address'] ?? null;
            }
            
            $query .= " WHERE id = :id AND tenant_id = :tid";
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            
            echo json_encode(['success' => true, 'message' => 'Inquiry updated successfully', 'id' => $inquiryId]);
        } else {
            // Build dynamic query based on available columns
            $query = "INSERT INTO inquiries (
                tenant_id, full_name, phone, email, course_id, source, status, notes, created_at, updated_at
            ) VALUES (
                :tid, :name, :phone, :email, :course_id, :source, :status, :notes, NOW(), NOW()
            )";
            
            $params = [
                'tid' => $tenantId,
                'name' => $input['full_name'],
                'phone' => $input['phone'],
                'email' => $input['email'] ?? null,
                'course_id' => $input['course_id'],
                'source' => $input['source'],
                'status' => $input['status'] ?? 'pending',
                'notes' => $input['notes'] ?? null
            ];
            
            // Add optional fields if they exist in the table
            if ($hasAltPhone) {
                $query = str_replace('course_id, source', 'course_id, alt_phone, source', $query);
                $query = str_replace(':course_id, :source', ':course_id, :alt_phone, :source', $query);
                $params['alt_phone'] = $input['alt_phone'] ?? null;
            }
            if ($hasAddress) {
                $query = str_replace('status, notes', 'status, address, notes', $query);
                $query = str_replace(':status, :notes', ':status, :address, :notes', $query);
                $params['address'] = $input['address'] ?? null;
            }
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            
            $newId = $db->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Inquiry created successfully', 'id' => $newId]);
        }
    }
    
    if ($method === 'DELETE') {
        // Delete inquiry
        $input = json_decode(file_get_contents('php://input'), true);
        $inquiryId = $input['id'] ?? null;
        
        if (!$inquiryId) {
            echo json_encode(['success' => false, 'message' => 'Inquiry ID is required']);
            exit;
        }
        
        $stmt = $db->prepare("DELETE FROM inquiries WHERE id = :id AND tenant_id = :tid");
        $stmt->execute(['id' => $inquiryId, 'tid' => $tenantId]);
        
        echo json_encode(['success' => true, 'message' => 'Inquiry deleted successfully']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
