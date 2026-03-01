<?php
/**
 * Front Desk Dashboard Stats API
 * Fetches real-time metrics for the front desk dashboard
 * Uses parameterized queries for security and proper caching
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../../config/config.php';
}

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Set JSON response type
http_response_code(200);

// Ensure user is logged in and is a front desk operator
if (!isLoggedIn() || $_SESSION['userData']['role'] !== 'frontdesk') {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized access. Please login.',
        'code' => 'UNAUTHORIZED'
    ]);
    exit;
}

$tenantId = $_SESSION['userData']['tenant_id'] ?? null;
$userId = $_SESSION['userData']['id'] ?? null;

if (!$tenantId) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Tenant ID missing',
        'code' => 'MISSING_TENANT'
    ]);
    exit;
}

// Simple cache implementation (5-minute cache)
$cacheKey = "frontdesk_stats_{$tenantId}";
$cacheFile = sys_get_temp_dir() . '/fd_cache_' . md5($cacheKey) . '.json';
$cacheExpiry = 300; // 5 minutes

function getCachedData($cacheFile, $cacheExpiry) {
    if (file_exists($cacheFile) && is_readable($cacheFile)) {
        $content = file_get_contents($cacheFile);
        $data = json_decode($content, true);
        if ($data && isset($data['cached_at']) && (time() - $data['cached_at'] < $cacheExpiry)) {
            return $data['stats'];
        }
    }
    return null;
}

function setCachedData($cacheFile, $stats) {
    $data = [
        'cached_at' => time(),
        'stats' => $stats
    ];
    @file_put_contents($cacheFile, json_encode($data));
}

// Check for cache (skip cache for real-time data requests)
$useCache = !isset($_GET['refresh']);
if ($useCache) {
    $cachedStats = getCachedData($cacheFile, $cacheExpiry);
    if ($cachedStats !== null) {
        echo json_encode([
            'success' => true, 
            'data' => $cachedStats,
            'cached' => true,
            'timestamp' => date('c')
        ]);
        exit;
    }
}

try {
    $db = getDBConnection();
    $stats = [];
    $today = date('Y-m-d');
    
    // 1-4, 10, 12. Combined Student, Batch, and Course Counts
    $stmt = $db->prepare("
        SELECT 
            (SELECT COUNT(*) FROM students WHERE tenant_id = :tid1 AND status = 'active' AND deleted_at IS NULL) as total_students,
            (SELECT COUNT(*) FROM students WHERE tenant_id = :tid2 AND created_at >= :today1 AND deleted_at IS NULL) as today_checkins,
            (SELECT COUNT(*) FROM batches WHERE tenant_id = :tid3 AND status = 'active' AND deleted_at IS NULL) as active_batches,
            (SELECT COUNT(*) FROM batches WHERE tenant_id = :tid4 AND deleted_at IS NULL) as total_batches,
            (SELECT COUNT(*) FROM courses WHERE tenant_id = :tid5 AND status = 'active' AND deleted_at IS NULL) as active_courses,
            (SELECT COUNT(*) FROM students WHERE tenant_id = :tid6 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND deleted_at IS NULL) as weekly_inquiries
    ");
    $stmt->execute([
        'tid1' => $tenantId, 'tid2' => $tenantId, 'tid3' => $tenantId, 
        'tid4' => $tenantId, 'tid5' => $tenantId, 'tid6' => $tenantId,
        'today1' => $today
    ]);
    $counts = $stmt->fetch();
    $stats = array_merge($stats, $counts);

    // 5. Today's Fee Collection (Revenue) - Optimized with index-friendly date filter
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount_paid), 0) FROM fee_records WHERE tenant_id = :tid AND (paid_date = :today OR (paid_date IS NULL AND created_at >= :today_ts AND amount_paid > 0))");
    $stmt->execute(['tid' => $tenantId, 'today' => $today, 'today_ts' => $today]);
    $stats['today_revenue'] = (float) $stmt->fetchColumn();
    
    // 6, 8. Outstanding Dues & Overdue Payments
    $stmt = $db->prepare("
        SELECT 
            COALESCE(SUM(amount_due - amount_paid), 0) as pending_dues,
            COUNT(CASE WHEN due_date < :today THEN 1 END) as overdue_payments
        FROM fee_records 
        WHERE tenant_id = :tid AND amount_due > amount_paid
    ");
    $stmt->execute(['tid' => $tenantId, 'today' => $today]);
    $dues = $stmt->fetch();
    $stats['pending_dues'] = (float) $dues['pending_dues'];
    $stats['overdue_payments'] = (int) $dues['overdue_payments'];
    
    // 7. Pending Tasks - Students without enrollment
    $stmt = $db->prepare("SELECT COUNT(*) FROM students s WHERE s.tenant_id = :tid AND s.status = 'active' AND s.deleted_at IS NULL AND NOT EXISTS (SELECT 1 FROM enrollments e WHERE e.student_id = s.id)");
    $stmt->execute(['tid' => $tenantId]);
    $stats['unassigned_students'] = (int) $stmt->fetchColumn();
    
    // 9. Today's Attendance
    $stmt = $db->prepare("SELECT COUNT(*) FROM attendance WHERE tenant_id = :tid AND attendance_date = :today");
    $stmt->execute(['tid' => $tenantId, 'today' => $today]);
    $stats['attendance_marked'] = (int) $stmt->fetchColumn();
    
    // 11. Unread Notifications for current user
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE tenant_id = :tid AND user_id = :uid AND is_read = 0");
    $stmt->execute(['tid' => $tenantId, 'uid' => $userId]);
    $stats['unread_notifications'] = (int) $stmt->fetchColumn();
    
    // Get recent notifications for display
    $stmt = $db->prepare("SELECT id, title, body, type, is_read, created_at FROM notifications WHERE tenant_id = :tid AND user_id = :uid ORDER BY created_at DESC LIMIT 5");
    $stmt->execute(['tid' => $tenantId, 'uid' => $userId]);
    $stats['recent_notifications'] = $stmt->fetchAll();
    
    // Get today's fee transactions - Optimized with index-friendly date filter
    $stmt = $db->prepare("SELECT fr.id, fr.receipt_no, s.full_name as student_name, fr.amount_paid, fr.paid_date, fr.payment_mode 
        FROM fee_records fr 
        JOIN students s ON fr.student_id = s.id 
        WHERE fr.tenant_id = :tid AND (fr.paid_date = :today OR (fr.paid_date IS NULL AND fr.created_at >= :today_ts)) 
        ORDER BY fr.created_at DESC 
        LIMIT 10");
    $stmt->execute(['tid' => $tenantId, 'today' => $today, 'today_ts' => $today]);
    $stats['today_transactions'] = $stmt->fetchAll();
    
    // Get upcoming batch starts
    $stmt = $db->prepare("SELECT id, name, start_date, max_strength FROM batches WHERE tenant_id = :tid AND start_date >= :today AND status = 'upcoming' ORDER BY start_date ASC LIMIT 5");
    $stmt->execute(['tid' => $tenantId, 'today' => $today]);
    $stats['upcoming_batches'] = $stmt->fetchAll();


    
    // Store in cache
    setCachedData($cacheFile, $stats);
    
    echo json_encode([
        'success' => true, 
        'data' => $stats,
        'user' => [
            'name' => $_SESSION['userData']['name'] ?? 'Operator',
            'email' => $_SESSION['userData']['email'] ?? ''
        ],
        'tenant_name' => $_SESSION['tenant_name'] ?? 'Institute',
        'cached' => false,
        'timestamp' => date('c')
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("FrontDesk Stats Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred',
        'code' => 'DB_ERROR'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    error_log("FrontDesk Stats Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred',
        'code' => 'GENERAL_ERROR'
    ]);
}
