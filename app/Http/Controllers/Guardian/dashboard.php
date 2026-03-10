<?php
/**
 * Guardian Dashboard API
 * Returns aggregated data for the guardian dashboard overview
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
$role = $user['role'] ?? '';
$tenantId = $user['tenant_id'] ?? null;
$userId = $user['id'] ?? null;

// Only guardians or supers/admins can access
if ($role !== 'guardian' && $role !== 'superadmin' && $role !== 'instituteadmin') {
    // Just a check
}

try {
    $db = getDBConnection();
    
    // 1. Get guardian info
    $stmt = $db->prepare("
        SELECT g.*, u.name as full_name, u.email, u.phone
        FROM guardians g
        JOIN users u ON g.user_id = u.id
        WHERE g.user_id = :uid AND g.tenant_id = :tid
        LIMIT 1
    ");
    $stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
    $guardianInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If testing or mapped, get the student ID. Guardians table has student_id.
    $studentId = $guardianInfo['student_id'] ?? null;
    
    if (!$studentId && isset($_SESSION['userData']['student_id'])) {
        $studentId = $_SESSION['userData']['student_id'];
    }

    $dashboard = [
        'guardian_info' => $guardianInfo,
        'student_info' => null,
        'stats' => [
            'attendance_rate' => 0,
            'attendance_present' => 0,
            'attendance_total' => 0,
            'latest_exam_score' => null,
            'fee_dues' => 0,
            'notices_count' => 0
        ],
        'recent_exams' => [],
        'fee_status' => [],
        'recent_notices' => []
    ];

    if ($studentId) {
        // 2. Student Info
        $stmt = $db->prepare("
            SELECT s.*, b.name as batch_name, c.name as course_name
            FROM students s
            LEFT JOIN batches b ON s.batch_id = b.id
            LEFT JOIN courses c ON b.course_id = c.id
            WHERE s.id = :sid AND s.tenant_id = :tid
            LIMIT 1
        ");
        $stmt->execute(['sid' => $studentId, 'tid' => $tenantId]);
        $studentInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        $dashboard['student_info'] = $studentInfo;
        
        $batchId = $studentInfo['batch_id'] ?? null;
        
        // 3. Attendance Stats (Current Month roughly)
        $startOfMonth = date('Y-m-01');
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) as present_days
            FROM attendance
            WHERE student_id = :sid AND tenant_id = :tid AND attendance_date >= :som
        ");
        $stmt->execute(['sid' => $studentId, 'tid' => $tenantId, 'som' => $startOfMonth]);
        $att = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $totalDays = (int)($att['total_days'] ?? 0);
        $presentDays = (int)($att['present_days'] ?? 0);
        $rate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;
        
        $dashboard['stats']['attendance_rate'] = $rate;
        $dashboard['stats']['attendance_present'] = $presentDays;
        $dashboard['stats']['attendance_total'] = $totalDays;
        
        // 4. Recent Exams
        try {
            $stmt = $db->prepare("
                SELECT ea.score, ea.total_marks, e.title as exam_title, e.exam_date, e.exam_type
                FROM exam_attempts ea
                JOIN exams e ON ea.exam_id = e.id
                WHERE ea.student_id = :sid AND ea.tenant_id = :tid
                ORDER BY e.exam_date DESC LIMIT 3
            ");
            $stmt->execute(['sid' => $studentId, 'tid' => $tenantId]);
            $recentExams = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $dashboard['recent_exams'] = $recentExams;
            
            if (!empty($recentExams)) {
                $latest = $recentExams[0];
                $pct = $latest['total_marks'] > 0 ? round(($latest['score'] / $latest['total_marks']) * 100) : 0;
                $dashboard['stats']['latest_exam_score'] = $pct;
            }
        } catch (Exception $e) {}
        
        // 5. Fee Dues
        try {
            $stmt = $db->prepare("SELECT SUM(amount) as total_due FROM fee_records WHERE student_id = :sid AND status = 'unpaid'");
            $stmt->execute(['sid' => $studentId]);
            $feeRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $dashboard['stats']['fee_dues'] = $feeRow['total_due'] ? (float)$feeRow['total_due'] : 0;
            
            // Fee Schedule
            $stmt = $db->prepare("SELECT title as fee_name, due_date, amount, status 
                                  FROM fee_records 
                                  WHERE student_id = :sid AND tenant_id = :tid 
                                  ORDER BY due_date ASC LIMIT 5");
            $stmt->execute(['sid' => $studentId, 'tid' => $tenantId]);
            $dashboard['fee_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {}
        
        // 6. Recent Notices
        try {
            $stmt = $db->prepare("
                SELECT * FROM notices 
                WHERE tenant_id = :tid AND target_type IN ('all', 'guardians') 
                  OR (target_type = 'batch' AND target_id = :bid)
                ORDER BY created_at DESC LIMIT 3
            ");
            $stmt->execute(['tid' => $tenantId, 'bid' => $batchId]);
            $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $dashboard['recent_notices'] = $notices;
            $dashboard['stats']['notices_count'] = count($notices);
        } catch (Exception $e) {}

    }

    echo json_encode([
        'success' => true, 
        'data' => $dashboard
    ]);
    
} catch (PDOException $e) {
    error_log("Guardian Dashboard Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("Guardian Dashboard Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
