<?php
/**
 * Teacher Dashboard API
 * Returns aggregated data for teacher dashboard overview
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

// Get teacher_id from session or user data
$teacherId = $_SESSION['userData']['teacher_id'] ?? null;

// If role is teacher but no teacher_id, try to fetch it
if ($role === 'teacher' && !$teacherId && $userId) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id FROM teachers WHERE user_id = :uid AND tenant_id = :tid LIMIT 1");
        $stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $teacherId = $result['id'];
            $_SESSION['userData']['teacher_id'] = $teacherId;
        }
    } catch (Exception $e) {
        error_log("Failed to fetch teacher_id: " . $e->getMessage());
    }
}

if (!$tenantId || !$teacherId) {
    echo json_encode(['success' => false, 'message' => 'Teacher record not found']);
    exit;
}

try {
    $db = getDBConnection();
    $dashboard = [
        'teacher_info' => null,
        'today_classes' => [],
        'stats' => [
            'today_class_count' => 0,
            'attendance_rate' => 0,
            'pending_assignments' => 0,
            'submitted_questions' => 0
        ],
        'announcements' => [],
        'syllabus_coverage' => [],
        'leave_balance' => []
    ];
    
    // 1. Get teacher basic info
    $stmt = $db->prepare("
        SELECT t.*,
               tenant.name as institute_name, tenant.logo_path as institute_logo
        FROM teachers t
        LEFT JOIN tenants tenant ON t.tenant_id = tenant.id
        WHERE t.id = :tid AND t.tenant_id = :tenant_id
        LIMIT 1
    ");
    $stmt->execute(['tid' => $teacherId, 'tenant_id' => $tenantId]);
    $dashboard['teacher_info'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 2. Fetch today's classes from timetable
    $dayOfWeek = date('w') + 1; // 1=Sunday, 2=Monday, ..., 7=Saturday
    
    $stmt = $db->prepare("
        SELECT t.*, s.name as subject_name, s.code as subject_code,
               b.name as batch_name, c.name as course_name
        FROM timetable_slots t
        LEFT JOIN subjects s ON t.subject_id = s.id
        LEFT JOIN batches b ON t.batch_id = b.id
        LEFT JOIN courses c ON b.course_id = c.id
        WHERE t.teacher_id = :tid 
          AND t.day_of_week = :day
          AND t.tenant_id = :tenant_id
        ORDER BY t.start_time ASC
    ");
    $stmt->execute(['tid' => $teacherId, 'day' => $dayOfWeek, 'tenant_id' => $tenantId]);
    $today_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format classes for frontend
    $formatted_classes = [];
    $now = date('H:i:s');
    foreach ($today_classes as $cls) {
        $status = 'LATER';
        if ($now >= $cls['start_time'] && $now <= $cls['end_time']) {
            $status = 'ONGOING';
        } else if ($now > $cls['end_time']) {
            $status = 'COMPLETED';
        } else {
            $status = 'UPCOMING';
        }
        
        $cls['status'] = $status;
        $formatted_classes[] = $cls;
    }
    $dashboard['today_classes'] = $formatted_classes;
    $dashboard['stats']['today_class_count'] = count($formatted_classes);
    
    // 3. Stats (Mocked or simple calculated for missing advanced tables)
    // Pending assignments
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM homework WHERE created_by = :uid AND status = 'published'");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $dashboard['stats']['pending_assignments'] = $row['cnt'] ?? 0;
    } catch (Exception $e) {}
    
    // Submitted questions
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM questions WHERE created_by_user_id = :uid");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $dashboard['stats']['submitted_questions'] = $row['cnt'] ?? 0;
    } catch (Exception $e) {
        $dashboard['stats']['submitted_questions'] = 0; // fallback if table doesn't exist
    }

    // Attendance rate
    // E.g., student attendance for this teacher's classes today? 
    // Just putting a static logic or simple count if table exists
    $dashboard['stats']['attendance_rate'] = 82; // Static for demo as requested by UI blueprint
    
    // 4. Announcements
    try {
        $stmt = $db->prepare("
            SELECT * FROM notices 
            WHERE tenant_id = :tid AND target_type IN ('all', 'staff') AND status = 'active'
            ORDER BY created_at DESC LIMIT 3
        ");
        $stmt->execute(['tid' => $tenantId]);
        $dashboard['announcements'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // 5. Syllabus Coverage mock data
    $dashboard['syllabus_coverage'] = [
        ['subject' => 'Maths', 'percentage' => 85, 'color' => 'var(--green)'],
        ['subject' => 'Science', 'percentage' => 60, 'color' => 'var(--amber)'],
        ['subject' => 'English', 'percentage' => 45, 'color' => 'var(--blue)']
    ];

    // 6. Leave Balance mock data or from DB
    $dashboard['leave_balance'] = [
        ['type' => 'Casual Leaves', 'used' => 4, 'total' => 12, 'percentage' => 33, 'color' => 'var(--blue)'],
        ['type' => 'Sick Leaves', 'used' => 1, 'total' => 8, 'percentage' => 12, 'color' => 'var(--red)']
    ];

    echo json_encode([
        'success' => true, 
        'data' => $dashboard,
        'timestamp' => date('c')
    ]);
    
} catch (PDOException $e) {
    error_log("Teacher Dashboard Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred', 'code' => 'DB_ERROR']);
} catch (Exception $e) {
    error_log("Teacher Dashboard Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred', 'code' => 'GENERAL_ERROR']);
}
