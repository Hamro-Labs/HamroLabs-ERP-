<?php
/**
 * Student Profile API
 * Handles profile viewing and updates for students
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../../../config/config.php';
}

header('Content-Type: application/json');

// Ensure user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
$tenantId = $user['tenant_id'] ?? null;
$userId = $user['id'] ?? null;
$studentId = $_SESSION['userData']['student_id'] ?? null;

if (!$tenantId || !$studentId) {
    echo json_encode(['success' => false, 'message' => 'Student record not found']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'view';

try {
    $db = getDBConnection();
    
    switch ($action) {
        case 'view':
            // Get complete student profile
            $stmt = $db->prepare("
                SELECT s.*, 
                       b.name as batch_name, b.start_date as batch_start,
                       c.name as course_name, c.duration, c.fee as course_fee,
                       t.name as institute_name, t.address as institute_address,
                       t.phone as institute_phone, t.email as institute_email,
                       t.logo_path as institute_logo,
                       u.email as login_email, u.phone as login_phone,
                       u.last_login_at
                FROM students s
                LEFT JOIN batches b ON s.batch_id = b.id
                LEFT JOIN courses c ON b.course_id = c.id
                LEFT JOIN tenants t ON s.tenant_id = t.id
                LEFT JOIN users u ON s.user_id = u.id
                WHERE s.id = :sid AND s.tenant_id = :tid
                LIMIT 1
            ");
            $stmt->execute(['sid' => $studentId, 'tid' => $tenantId]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$profile) {
                echo json_encode(['success' => false, 'message' => 'Profile not found']);
                exit;
            }
            
            // Parse JSON fields
            if ($profile['permanent_address']) {
                $profile['permanent_address'] = json_decode($profile['permanent_address'], true);
            }
            if ($profile['temporary_address']) {
                $profile['temporary_address'] = json_decode($profile['temporary_address'], true);
            }
            if ($profile['academic_qualifications']) {
                $profile['academic_qualifications'] = json_decode($profile['academic_qualifications'], true);
            }
            
            echo json_encode(['success' => true, 'data' => $profile]);
            break;
            
        case 'update':
            if ($method !== 'POST' && $method !== 'PUT') {
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) $input = $_POST;
            
            // Fields that students can update
            $allowedFields = ['phone', 'email', 'temporary_address', 'guardian_name', 'guardian_phone', 'guardian_relation'];
            $updates = [];
            $params = ['sid' => $studentId, 'tid' => $tenantId];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = :$field";
                    $params[$field] = $input[$field];
                }
            }
            
            if (empty($updates)) {
                echo json_encode(['success' => false, 'message' => 'No fields to update']);
                exit;
            }
            
            $sql = "UPDATE students SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = :sid AND tenant_id = :tid";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            // Also update user email/phone if changed
            if (isset($input['email']) || isset($input['phone'])) {
                $userUpdates = [];
                $userParams = ['uid' => $userId];
                
                if (isset($input['email'])) {
                    $userUpdates[] = "email = :email";
                    $userParams['email'] = $input['email'];
                }
                if (isset($input['phone'])) {
                    $userUpdates[] = "phone = :phone";
                    $userParams['phone'] = $input['phone'];
                }
                
                if (!empty($userUpdates)) {
                    $userSql = "UPDATE users SET " . implode(', ', $userUpdates) . ", updated_at = NOW() WHERE id = :uid";
                    $stmt = $db->prepare($userSql);
                    $stmt->execute($userParams);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
            break;
            
        case 'academic_history':
            // Get academic history (enrollments, results, etc.)
            $stmt = $db->prepare("
                SELECT 
                    sbe.*, b.name as batch_name, c.name as course_name,
                    c.duration, c.fee as course_fee
                FROM student_batch_enrollments sbe
                JOIN batches b ON sbe.batch_id = b.id
                JOIN courses c ON b.course_id = c.id
                WHERE sbe.student_id = :sid AND sbe.tenant_id = :tid
                ORDER BY sbe.created_at DESC
            ");
            $stmt->execute(['sid' => $studentId, 'tid' => $tenantId]);
            $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $enrollments
            ]);
            break;
            
        case 'change_password':
            if ($method !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            $currentPassword = $input['current_password'] ?? '';
            $newPassword = $input['new_password'] ?? '';
            $confirmPassword = $input['confirm_password'] ?? '';
            
            // Validation
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                exit;
            }
            
            if ($newPassword !== $confirmPassword) {
                echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
                exit;
            }
            
            if (strlen($newPassword) < 8) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
                exit;
            }
            
            // Verify current password
            $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = :uid");
            $stmt->execute(['uid' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!password_verify($currentPassword, $user['password_hash'])) {
                echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                exit;
            }
            
            // Update password
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $db->prepare("UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :uid");
            $stmt->execute(['hash' => $newHash, 'uid' => $userId]);
            
            echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
            break;
            
        case 'upload_document':
            // Handle document uploads (profile photo, citizenship, etc.)
            if ($method !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            $documentType = $_POST['document_type'] ?? '';
            $allowedTypes = ['photo', 'citizenship', 'transcript', 'certificate'];
            
            if (!in_array($documentType, $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid document type']);
                exit;
            }
            
            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'File upload failed']);
                exit;
            }
            
            $file = $_FILES['document'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($extension, $allowedExtensions)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, PDF']);
                exit;
            }
            
            // Create upload directory
            $uploadDir = __DIR__ . '/../../../../public/uploads/students/documents/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate unique filename
            $filename = $documentType . '_' . $studentId . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Update database based on document type
                $dbField = $documentType === 'photo' ? 'photo_url' : $documentType . '_document_url';
                $relativePath = '/public/uploads/students/documents/' . $filename;
                
                $stmt = $db->prepare("UPDATE students SET $dbField = :path, updated_at = NOW() WHERE id = :sid");
                $stmt->execute(['path' => $relativePath, 'sid' => $studentId]);
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Document uploaded successfully',
                    'path' => $relativePath
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save file']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    error_log("Student Profile Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred',
        'code' => 'DB_ERROR'
    ]);
} catch (Exception $e) {
    error_log("Student Profile Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred',
        'code' => 'GENERAL_ERROR'
    ]);
}
