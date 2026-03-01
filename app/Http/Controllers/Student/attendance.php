<?php
/**
 * Student Attendance API
 * Handles attendance viewing and leave applications for students
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
$action = $_GET['action'] ?? 'summary';

try {
    $db = getDBConnection();
    
    switch ($action) {
        case 'summary':
            // Get overall attendance summary
            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
                    SUM(CASE WHEN status = 'leave' THEN 1 ELSE 0 END) as leave_days
                FROM attendance 
                WHERE student_id = :sid AND tenant_id = :tid
            ");
            $stmt->execute(['sid' => $studentId, 'tid' => $tenantId]);
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $totalDays = (int)($summary['total_days'] ?? 0);
            $presentDays = (int)($summary['present_days'] ?? 0);
            $lateDays = (int)($summary['late_days'] ?? 0);
            
            // Calculate percentage (late counts as 0.5)
            $percentage = $totalDays > 0 
                ? round((($presentDays + ($lateDays * 0.5)) / $totalDays) * 100, 2) 
                : 0;
            
            // Get monthly breakdown
            $stmt = $db->prepare("
                SELECT 
                    DATE_FORMAT(date, '%Y-%m') as month,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
                FROM attendance 
                WHERE student_id = :sid AND tenant_id = :tid
                GROUP BY DATE_FORMAT(date, '%Y-%m')
                ORDER BY month DESC
                LIMIT 6
            ");
            $stmt->execute(['sid' => $studentId, 'tid' => $tenantId]);
            $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_days' => $totalDays,
                        'present_days' => $presentDays,
                        'absent_days' => (int)($summary['absent_days'] ?? 0),
                        'late_days' => $lateDays,
                        'leave_days' => (int)($summary['leave_days'] ?? 0),
                        'attendance_percentage' => $percentage
                    ],
                    'monthly_breakdown' => $monthlyData
                ]
            ]);
            break;
            
        case 'history':
            // Get detailed attendance history
            $month = $_GET['month'] ?? date('m');
            $year = $_GET['year'] ?? date('Y');
            
            $stmt = $db->prepare("
                SELECT a.*, s.name as subject_name
                FROM attendance a
                LEFT JOIN subjects s ON a.subject_id = s.id
                WHERE a.student_id = :sid 
                  AND a.tenant_id = :tid
                  AND MONTH(a.date) = :month
                  AND YEAR(a.date) = :year
                ORDER BY a.date DESC
            ");
            $stmt->execute([
                'sid' => $studentId, 
                'tid' => $tenantId,
                'month' => $month,
                'year' => $year
            ]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $history,
                'month' => $month,
                'year' => $year
            ]);
            break;
            
        case 'apply_leave':
            // Submit leave application
            if ($method !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) $input = $_POST;
            
            // Validate required fields
            if (empty($input['start_date']) || empty($input['end_date']) || empty($input['reason'])) {
                echo json_encode(['success' => false, 'message' => 'Start date, end date and reason are required']);
                exit;
            }
            
            $stmt = $db->prepare("
                INSERT INTO leave_requests (
                    tenant_id, student_id, user_id, 
                    start_date, end_date, reason, 
                    status, created_at, updated_at
                ) VALUES (
                    :tid, :sid, :uid,
                    :start_date, :end_date, :reason,
                    'pending', NOW(), NOW()
                )
            ");
            
            $stmt->execute([
                'tid' => $tenantId,
                'sid' => $studentId,
                'uid' => $userId,
                'start_date' => $input['start_date'],
                'end_date' => $input['end_date'],
                'reason' => $input['reason']
            ]);
            
            $leaveId = $db->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'Leave application submitted successfully',
                'leave_id' => $leaveId
            ]);
            break;
            
        case 'leave_status':
            // Get leave request status
            $stmt = $db->prepare("
                SELECT lr.*, u.name as reviewed_by_name
                FROM leave_requests lr
                LEFT JOIN users u ON lr.reviewed_by = u.id
                WHERE lr.student_id = :sid AND lr.tenant_id = :tid
                ORDER BY lr.created_at DESC
            ");
            $stmt->execute(['sid' => $studentId, 'tid' => $tenantId]);
            $leaveRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $leaveRequests
            ]);
            break;
            
        case 'cancel_leave':
            // Cancel pending leave request
            if ($method !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $leaveId = $input['leave_id'] ?? null;
            
            if (!$leaveId) {
                echo json_encode(['success' => false, 'message' => 'Leave ID required']);
                exit;
            }
            
            // Only allow canceling pending requests
            $stmt = $db->prepare("
                UPDATE leave_requests 
                SET status = 'cancelled', updated_at = NOW()
                WHERE id = :lid AND student_id = :sid AND status = 'pending'
            ");
            $stmt->execute(['lid' => $leaveId, 'sid' => $studentId]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Leave request cancelled']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Leave request not found or already processed']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    error_log("Student Attendance Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred',
        'code' => 'DB_ERROR'
    ]);
} catch (Exception $e) {
    error_log("Student Attendance Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred',
        'code' => 'GENERAL_ERROR'
    ]);
}
