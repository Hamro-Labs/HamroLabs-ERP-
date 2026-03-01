<?php
/**
 * Institute Admin Dashboard Stats API
 * Fetches real-time metrics for the institute dashboard
 * 
 * Enhanced with:
 * - Comprehensive KPI metrics
 * - Workflow checklist persistence
 * - Revenue trends with live data
 * - Fee aging reports
 * - Percentage change calculations
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../../config/config.php';
}

header('Content-Type: application/json');

// Ensure user is logged in and is an institute admin
if (!isLoggedIn() || ($_SESSION['userData']['role'] !== 'instituteadmin' && $_SESSION['userData']['role'] !== 'superadmin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$tenantId = $_SESSION['userData']['tenant_id'] ?? null;
$userId = $_SESSION['userData']['id'] ?? null;

// If superadmin is viewing a specific tenant, they might pass tenant_id
if ($_SESSION['userData']['role'] === 'superadmin' && isset($_GET['tenant_id'])) {
    $tenantId = $_GET['tenant_id'];
}

// Handle different actions
$action = $_GET['action'] ?? 'stats';

try {
    $db = getDBConnection();
    
    switch ($action) {
        case 'workflow':
            handleWorkflowChecklist($db, $tenantId, $userId);
            break;
        case 'stats':
        default:
            getDashboardStats($db, $tenantId, $userId);
            break;
    }
} catch (Exception $e) {
    error_log('Dashboard Stats Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while fetching dashboard data']);
}

/**
 * Get comprehensive dashboard statistics
 */
function getDashboardStats($db, $tenantId, $userId) {
    $stats = [];
    $today = date('Y-m-d');
    $currentMonth = date('Y-m');
    
    // 0. Institute Profile Info
    $stmt = $db->prepare("SELECT name, brand_color, logo_path FROM tenants WHERE id = :tid");
    $stmt->execute(['tid' => $tenantId]);
    $tenant = $stmt->fetch();
    $stats['institute_name'] = $tenant['name'] ?? 'Dashboard';
    $stats['brand_color'] = $tenant['brand_color'] ?? '#006D44';
    $stats['logo_url'] = !empty($tenant['logo_path']) ? APP_URL . $tenant['logo_path'] : null;
    
    // 1. Total Active Students
    $stmt = $db->prepare("SELECT COUNT(*) FROM students WHERE tenant_id = :tid AND status = 'active' AND deleted_at IS NULL");
    $stmt->execute(['tid' => $tenantId]);
    $stats['total_students'] = (int)$stmt->fetchColumn();
    
    // 2. New Students This Month (Growth)
    $stmt = $db->prepare("SELECT COUNT(*) FROM students WHERE tenant_id = :tid AND created_at >= DATE_FORMAT(NOW() ,'%Y-%m-01') AND deleted_at IS NULL");
    $stmt->execute(['tid' => $tenantId]);
    $stats['new_students_month'] = (int)$stmt->fetchColumn();
    
    // 3. Student Growth % (compare with previous month)
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM students 
        WHERE tenant_id = :tid 
        AND created_at >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01')
        AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01')
        AND deleted_at IS NULL
    ");
    $stmt->execute(['tid' => $tenantId]);
    $lastMonthStudents = (int)$stmt->fetchColumn();
    $stats['student_growth_percent'] = $lastMonthStudents > 0 
        ? round((($stats['new_students_month'] - $lastMonthStudents) / $lastMonthStudents) * 100, 1)
        : ($stats['new_students_month'] > 0 ? 100 : 0);
    
    // 4. Active Batches count
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM batches WHERE tenant_id = :tid AND status = 'active'");
        $stmt->execute(['tid' => $tenantId]);
        $stats['active_batches'] = (int)$stmt->fetchColumn();
    } catch (Exception $e) { 
        $stats['active_batches'] = 0; 
    }
    
    // 5. Total Teachers count
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE tenant_id = :tid AND role = 'teacher' AND status = 'active'");
        $stmt->execute(['tid' => $tenantId]);
        $stats['total_teachers'] = (int)$stmt->fetchColumn();
    } catch (Exception $e) { 
        $stats['total_teachers'] = 0; 
    }
    
    // 6. Today's Attendance Rate with detailed breakdown
    try {
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status='present' THEN 1 END) as present,
                COUNT(CASE WHEN status='absent' THEN 1 END) as absent,
                COUNT(CASE WHEN status='late' THEN 1 END) as late,
                COUNT(CASE WHEN status='excused' THEN 1 END) as excused
            FROM attendance 
            WHERE tenant_id = :tid AND attendance_date = CURDATE()
        ");
        $stmt->execute(['tid' => $tenantId]);
        $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $totalAttendance = (int)$attendance['total'];
        $stats['attendance'] = [
            'total' => $totalAttendance,
            'present' => (int)$attendance['present'],
            'absent' => (int)$attendance['absent'],
            'late' => (int)$attendance['late'],
            'excused' => (int)$attendance['excused'],
            'rate' => $totalAttendance > 0 ? round(((int)$attendance['present'] / $totalAttendance) * 100, 1) : 0
        ];
        $stats['attendance_rate'] = $stats['attendance']['rate'];
        
        // Batch-wise Attendance Breakdown
        $stmtBatch = $db->prepare("
            SELECT b.name as batch_name,
                   COUNT(*) as total,
                   COUNT(CASE WHEN a.status='present' THEN 1 END) as present
            FROM attendance a
            JOIN batches b ON a.batch_id = b.id
            WHERE a.tenant_id = :tid AND a.attendance_date = CURDATE()
            GROUP BY a.batch_id, b.name
        ");
        $stmtBatch->execute(['tid' => $tenantId]);
        $stats['batch_attendance'] = [];
        foreach ($stmtBatch->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $totalBatch = (int)$row['total'];
            $stats['batch_attendance'][] = [
                'batch_name' => $row['batch_name'],
                'rate' => $totalBatch > 0 ? round(((int)$row['present'] / $totalBatch) * 100, 1) : 0
            ];
        }
    } catch (Exception $e) {
        $stats['attendance'] = ['total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'rate' => 0];
        $stats['attendance_rate'] = 0;
        $stats['batch_attendance'] = [];
    }
    
    // 7. Today's Fee Collection (Fix: Use payment_transactions for real-time transactions)
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(amount), 0) as total,
               COUNT(*) as transactions,
               COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN amount ELSE 0 END), 0) as cash_total,
               COALESCE(SUM(CASE WHEN payment_method != 'cash' THEN amount ELSE 0 END), 0) as bank_total
        FROM payment_transactions 
        WHERE tenant_id = :tid 
        AND payment_date = CURDATE()
    ");
    $stmt->execute(['tid' => $tenantId]);
    $todayFee = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['today_fee'] = (float)$todayFee['total'];
    $stats['today_fee_cash'] = (float)$todayFee['cash_total'];
    $stats['today_fee_bank'] = (float)$todayFee['bank_total'];
    $stats['today_fee_transactions'] = (int)$todayFee['transactions'];
    
    // Compare with yesterday's collection for percentage change
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(amount), 0) as total
        FROM payment_transactions 
        WHERE tenant_id = :tid 
        AND payment_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    ");
    $stmt->execute(['tid' => $tenantId]);
    $yesterdayFee = (float)$stmt->fetchColumn();
    $stats['today_fee_change_percent'] = $yesterdayFee > 0 
        ? round((($stats['today_fee'] - $yesterdayFee) / $yesterdayFee) * 100, 1)
        : ($stats['today_fee'] > 0 ? 100 : 0);
    
    // 8. Outstanding Dues (Fix: Use student_fee_summary for accurate receivables)
    $stmt = $db->prepare("
        SELECT 
            COALESCE(SUM(due_amount), 0) as total,
            COUNT(*) as students_count
        FROM student_fee_summary 
        WHERE tenant_id = :tid AND due_amount > 0
    ");
    $stmt->execute(['tid' => $tenantId]);
    $outstanding = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['outstanding_dues'] = (float)$outstanding['total'];
    $stats['outstanding_students'] = (int)$outstanding['students_count'];
    
    // Fee Aging Buckets (real data)
    try {
        $stmt = $db->prepare("
            SELECT
                SUM(CASE WHEN DATEDIFF(NOW(), due_date) BETWEEN 0 AND 30 THEN (amount_due - amount_paid) ELSE 0 END) as aged_0_30,
                COUNT(DISTINCT CASE WHEN DATEDIFF(NOW(), due_date) BETWEEN 0 AND 30 THEN student_id END) as aged_0_30_count,
                SUM(CASE WHEN DATEDIFF(NOW(), due_date) BETWEEN 31 AND 60 THEN (amount_due - amount_paid) ELSE 0 END) as aged_31_60,
                COUNT(DISTINCT CASE WHEN DATEDIFF(NOW(), due_date) BETWEEN 31 AND 60 THEN student_id END) as aged_31_60_count,
                SUM(CASE WHEN DATEDIFF(NOW(), due_date) BETWEEN 61 AND 90 THEN (amount_due - amount_paid) ELSE 0 END) as aged_61_90,
                COUNT(DISTINCT CASE WHEN DATEDIFF(NOW(), due_date) BETWEEN 61 AND 90 THEN student_id END) as aged_61_90_count,
                SUM(CASE WHEN DATEDIFF(NOW(), due_date) > 90 THEN (amount_due - amount_paid) ELSE 0 END) as aged_90plus,
                COUNT(DISTINCT CASE WHEN DATEDIFF(NOW(), due_date) > 90 THEN student_id END) as aged_90plus_count
            FROM fee_records
            WHERE tenant_id = :tid AND amount_due > amount_paid AND due_date IS NOT NULL
        ");
        $stmt->execute(['tid' => $tenantId]);
        $aging = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['fee_aging'] = [
            '0_30'   => ['amount' => (float)($aging['aged_0_30']   ?? 0), 'count' => (int)($aging['aged_0_30_count']   ?? 0)],
            '31_60'  => ['amount' => (float)($aging['aged_31_60']  ?? 0), 'count' => (int)($aging['aged_31_60_count']  ?? 0)],
            '61_90'  => ['amount' => (float)($aging['aged_61_90']  ?? 0), 'count' => (int)($aging['aged_61_90_count']  ?? 0)],
            '90plus' => ['amount' => (float)($aging['aged_90plus'] ?? 0), 'count' => (int)($aging['aged_90plus_count'] ?? 0)],
        ];
    } catch (Exception $e) {
        $stats['fee_aging'] = [
            '0_30'   => ['amount' => 0, 'count' => 0],
            '31_60'  => ['amount' => 0, 'count' => 0],
            '61_90'  => ['amount' => 0, 'count' => 0],
            '90plus' => ['amount' => 0, 'count' => 0],
        ];
    }
    
    // 9. Monthly Fee Summary (Fix: Use payment_transactions for monthly figures)
    $stmt = $db->prepare("
        SELECT 
            COALESCE(SUM(amount), 0) as collected
        FROM payment_transactions 
        WHERE tenant_id = :tid AND DATE_FORMAT(payment_date,'%Y-%m') = :month
    ");
    $stmt->execute(['tid' => $tenantId, 'month' => $currentMonth]);
    $stats['monthly_collected'] = (float)$stmt->fetchColumn();
    $stats['monthly_discount'] = 0; // Discount calculation would require fee_records lookup
    
    $stmt = $db->prepare("SELECT COALESCE(SUM(due_amount), 0) FROM student_fee_summary WHERE tenant_id = :tid");
    $stmt->execute(['tid' => $tenantId]);
    $stats['monthly_outstanding'] = (float)$stmt->fetchColumn();
    
    // Compare with last month for collection change (Fix: Use payment_transactions)
    $lastMonth = date('Y-m', strtotime('-1 month'));
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(amount), 0) as collected
        FROM payment_transactions 
        WHERE tenant_id = :tid AND DATE_FORMAT(payment_date,'%Y-%m') = :month
    ");
    $stmt->execute(['tid' => $tenantId, 'month' => $lastMonth]);
    $lastMonthCollection = (float)$stmt->fetchColumn();
    $stats['monthly_collection_change_percent'] = $lastMonthCollection > 0
        ? round((($stats['monthly_collected'] - $lastMonthCollection) / $lastMonthCollection) * 100, 1)
        : ($stats['monthly_collected'] > 0 ? 100 : 0);
    
    // 10. Inquiries (Fix: Remove non-existent follow_up_date and show all inquiries)
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM inquiries WHERE tenant_id = :tid AND deleted_at IS NULL");
        $stmt->execute(['tid' => $tenantId]);
        $stats['new_inquiries'] = (int)$stmt->fetchColumn();
        
        $stats['followups_today'] = 0; // Follow-ups require a different table/logic
        
        // Pending inquiries count
        $stmt = $db->prepare("SELECT COUNT(*) FROM inquiries WHERE tenant_id = :tid AND status = 'pending' AND deleted_at IS NULL");
        $stmt->execute(['tid' => $tenantId]);
        $stats['pending_inquiries'] = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        $stats['new_inquiries'] = 0;
        $stats['followups_today'] = 0;
        $stats['pending_inquiries'] = 0;
    }
    
    // 11. Upcoming Exams (next 7 days)
    try {
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM exams 
            WHERE tenant_id = :tid AND exam_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ");
        $stmt->execute(['tid' => $tenantId]);
        $stats['upcoming_exams'] = (int)$stmt->fetchColumn();
        
        $stmt = $db->prepare("
            SELECT e.title, e.exam_date, b.name as batch_name, 
                   (SELECT COUNT(*) FROM students WHERE batch_id = e.batch_id AND deleted_at IS NULL AND status = 'active') as enrolled_count
            FROM exams e
            LEFT JOIN batches b ON e.batch_id = b.id
            WHERE e.tenant_id = :tid AND e.exam_date >= CURDATE() 
            ORDER BY e.exam_date ASC LIMIT 3
        ");
        $stmt->execute(['tid' => $tenantId]);
        $stats['upcoming_exams_list'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        $stats['upcoming_exams'] = 0;
        $stats['upcoming_exams_list'] = [];
    }
    
    // 12. Enrollment Trend (Last 6 Months)
    $stmt = $db->prepare("
        SELECT DATE_FORMAT(created_at, '%b') as month, COUNT(*) as count 
        FROM students 
        WHERE tenant_id = :tid AND created_at > DATE_SUB(NOW(), INTERVAL 6 MONTH) AND deleted_at IS NULL
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY created_at ASC
    ");
    $stmt->execute(['tid' => $tenantId]);
    $stats['enrollment_trend'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    // 13. Revenue Trend (Last 6 Months) (Fix: Use payment_transactions)
    $revenueTrend = [];
    for ($i = 5; $i >= 0; $i--) {
        $m = date('M', strtotime("-$i months"));
        $monthKey = date('Y-m', strtotime("-$i months"));
        
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0) FROM payment_transactions 
            WHERE tenant_id = :tid AND payment_date LIKE :month
        ");
        $stmt->execute(['tid' => $tenantId, 'month' => "$monthKey%"]);
        $amt = (float)$stmt->fetchColumn();
        
        $revenueTrend[] = [
            'month' => $m,
            'amount' => $amt,
            'discounts' => 0,
            'formatted' => 'Rs. ' . ($amt >= 1000 ? number_format($amt / 1000, 1) . 'K' : number_format($amt))
        ];
    }
    $stats['revenue_trend'] = $revenueTrend;
    
    // Calculate revenue change from last month
    $currentMonthRevenue = $revenueTrend[5]['amount'] ?? 0;
    $lastMonthRevenue = $revenueTrend[4]['amount'] ?? 0;
    $stats['revenue_change_percent'] = $lastMonthRevenue > 0
        ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
        : ($currentMonthRevenue > 0 ? 100 : 0);
    
    // 14. Recent Activity (Combined feed from various tables)
    try {
        $activities = [];
        
        // 1. Audit Logs (Primary)
        $stmt = $db->prepare("
            SELECT action, l.created_at, name as user_name, description 
            FROM audit_logs l 
            LEFT JOIN users u ON l.user_id = u.id 
            WHERE l.tenant_id = :tid 
            ORDER BY l.created_at DESC LIMIT 5
        ");
        $stmt->execute(['tid' => $tenantId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $activities[] = [
                'type' => 'log',
                'title' => $row['action'],
                'desc' => $row['description'],
                'user' => $row['user_name'],
                'time' => $row['created_at']
            ];
        }

        // 2. Recent Payments
        $stmt = $db->prepare("
            SELECT p.amount, p.payment_date, s.full_name, p.created_at
            FROM payment_transactions p
            JOIN students s ON p.student_id = s.id
            WHERE p.tenant_id = :tid
            ORDER BY p.created_at DESC LIMIT 5
        ");
        $stmt->execute(['tid' => $tenantId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $activities[] = [
                'type' => 'payment',
                'title' => 'Fee Payment',
                'desc' => 'Rs. ' . number_format($row['amount']) . ' from ' . $row['full_name'],
                'user' => 'System',
                'time' => $row['created_at']
            ];
        }

        // 3. New Inquiries
        $stmt = $db->prepare("
            SELECT full_name as name, created_at
            FROM inquiries
            WHERE tenant_id = :tid AND deleted_at IS NULL
            ORDER BY created_at DESC LIMIT 5
        ");
        $stmt->execute(['tid' => $tenantId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $activities[] = [
                'type' => 'inquiry',
                'title' => 'New Inquiry',
                'desc' => 'Inquiry received from ' . $row['name'],
                'user' => 'Visitor',
                'time' => $row['created_at']
            ];
        }

        // Sort by time
        usort($activities, function($a, $b) {
            return strcmp($b['time'], $a['time']);
        });

        $stats['recent_activity'] = array_slice($activities, 0, 10);
    } catch (Exception $e) {
        $stats['recent_activity'] = [];
    }
    
    // 15. Daily Workflow Checklist Items (from database)
    $stats['workflow'] = getWorkflowChecklist($db, $tenantId, $userId, $today);
    
    // 16. Fee Collection Target vs Actual (if monthly_targets table exists)
    try {
        $stmt = $db->prepare("
            SELECT fee_collection_target as target
            FROM monthly_targets 
            WHERE tenant_id = :tid AND year = YEAR(NOW()) AND month = MONTH(NOW())
        ");
        $stmt->execute(['tid' => $tenantId]);
        $target = $stmt->fetchColumn();
        $stats['monthly_target'] = $target ? (float)$target : 0;
        $stats['target_achievement_percent'] = $stats['monthly_target'] > 0
            ? round(($stats['monthly_collected'] / $stats['monthly_target']) * 100, 1)
            : 0;
    } catch (Exception $e) {
        $stats['monthly_target'] = 0;
        $stats['target_achievement_percent'] = 0;
    }
    
    // 17. Critical Alerts Check
    try {
        $alerts = [];
        
        // Low Attendance (< 60% this month)
        $stmtAlert = $db->prepare("
            SELECT s.full_name, s.roll_no,
                   COUNT(CASE WHEN a.status='present' THEN 1 END) as present_days,
                   COUNT(*) as total_days
            FROM students s
            JOIN attendance a ON s.id = a.student_id
            WHERE s.tenant_id = :tid AND s.deleted_at IS NULL AND s.status = 'active'
            AND a.attendance_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
            GROUP BY s.id, s.full_name, s.roll_no
            HAVING (COUNT(CASE WHEN a.status='present' THEN 1 END) / COUNT(*)) < 0.60 AND COUNT(*) > 5
            LIMIT 3
        ");
        $stmtAlert->execute(['tid' => $tenantId]);
        foreach ($stmtAlert->fetchAll(PDO::FETCH_ASSOC) as $row) {
             $rate = round(((int)$row['present_days'] / (int)$row['total_days']) * 100);
             $alerts[] = [
                 'type' => 'attendance',
                 'icon' => 'fa-user-slash',
                 'color' => 'orange',
                 'title' => 'Low Attendance Warning',
                 'message' => $row['full_name'] . ' (' . $row['roll_no'] . ') is at ' . $rate . '% attendance this month.'
             ];
        }
        
        // Critical High Dues (> 5000)
        $stmtDues = $db->prepare("
            SELECT full_name, roll_no, due_amount
            FROM student_fee_summary
            WHERE tenant_id = :tid AND due_amount > 5000
            ORDER BY due_amount DESC LIMIT 3
        ");
        $stmtDues->execute(['tid' => $tenantId]);
        foreach ($stmtDues->fetchAll(PDO::FETCH_ASSOC) as $row) {
             $alerts[] = [
                 'type' => 'fee',
                 'icon' => 'fa-triangle-exclamation',
                 'color' => 'red',
                 'title' => 'Critical Overdue Amount',
                 'message' => $row['full_name'] . ' (' . $row['roll_no'] . ') has dues of ₹' . number_format($row['due_amount']) . '.'
             ];
        }
        
        $stats['critical_alerts'] = $alerts;
    } catch (Exception $e) {
        $stats['critical_alerts'] = [];
    }
    
    echo json_encode(['success' => true, 'data' => $stats]);
}

/**
 * Handle workflow checklist operations (GET/POST)
 */
function handleWorkflowChecklist($db, $tenantId, $userId) {
    $method = $_SERVER['REQUEST_METHOD'];
    $today = date('Y-m-d');
    
    if ($method === 'POST') {
        // Update checklist item
        $input = json_decode(file_get_contents('php://input'), true);
        $taskKey = $input['task_key'] ?? null;
        $isCompleted = $input['is_completed'] ?? false;
        $taskName = $input['task_name'] ?? '';
        $taskDescription = $input['task_description'] ?? '';
        
        if (!$taskKey) {
            echo json_encode(['success' => false, 'message' => 'Task key is required']);
            return;
        }
        
        try {
            // Check if record exists
            $stmt = $db->prepare("
                SELECT id FROM workflow_checklists 
                WHERE tenant_id = :tid AND user_id = :uid AND task_key = :key AND checklist_date = :date
            ");
            $stmt->execute([
                'tid' => $tenantId,
                'uid' => $userId,
                'key' => $taskKey,
                'date' => $today
            ]);
            $existing = $stmt->fetchColumn();
            
            if ($existing) {
                // Update existing
                $stmt = $db->prepare("
                    UPDATE workflow_checklists 
                    SET is_completed = :completed,
                        completed_at = :completed_at,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([
                    'completed' => $isCompleted ? 1 : 0,
                    'completed_at' => $isCompleted ? date('Y-m-d H:i:s') : null,
                    'id' => $existing
                ]);
            } else {
                // Insert new
                $stmt = $db->prepare("
                    INSERT INTO workflow_checklists 
                    (tenant_id, user_id, task_key, task_name, task_description, is_completed, checklist_date, completed_at, created_at, updated_at)
                    VALUES (:tid, :uid, :key, :name, :desc, :completed, :date, :completed_at, NOW(), NOW())
                ");
                $stmt->execute([
                    'tid' => $tenantId,
                    'uid' => $userId,
                    'key' => $taskKey,
                    'name' => $taskName,
                    'desc' => $taskDescription,
                    'completed' => $isCompleted ? 1 : 0,
                    'date' => $today,
                    'completed_at' => $isCompleted ? date('Y-m-d H:i:s') : null
                ]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Workflow updated successfully']);
        } catch (Exception $e) {
            error_log('Workflow update error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to update workflow']);
        }
    } else {
        // GET - Return checklist for today
        $checklist = getWorkflowChecklist($db, $tenantId, $userId, $today);
        echo json_encode(['success' => true, 'data' => $checklist]);
    }
}

/**
 * Get workflow checklist for a specific date
 */
function getWorkflowChecklist($db, $tenantId, $userId, $date) {
    // Default tasks
    $defaultTasks = [
        [
            'key' => 'verify_attendance',
            'task' => 'Verify Today\'s Attendance',
            'desc' => 'Check if all teachers have marked attendance for their first periods.',
            'icon' => 'fa-clipboard-user',
            'color' => 'blue'
        ],
        [
            'key' => 'process_admissions',
            'task' => 'Process Pending Admissions',
            'desc' => 'Review and approve pending student applications.',
            'icon' => 'fa-user-plus',
            'color' => 'green'
        ],
        [
            'key' => 'revenue_reconciliation',
            'task' => 'Daily Revenue Reconciliation',
            'desc' => 'Cross-check physical receipts with digital collection records.',
            'icon' => 'fa-money-bill-wave',
            'color' => 'purple'
        ],
        [
            'key' => 'followup_calls',
            'task' => 'Follow-up Calls',
            'desc' => 'Contact pending inquiries and overdue fee students.',
            'icon' => 'fa-phone',
            'color' => 'orange'
        ],
        [
            'key' => 'exam_preparation',
            'task' => 'Exam Preparation Check',
            'desc' => 'Verify upcoming exam schedules and required materials.',
            'icon' => 'fa-file-signature',
            'color' => 'red'
        ]
    ];
    
    try {
        // Get saved checklist from database
        $stmt = $db->prepare("
            SELECT task_key, task_name, task_description, is_completed, completed_at
            FROM workflow_checklists 
            WHERE tenant_id = :tid AND user_id = :uid AND checklist_date = :date
        ");
        $stmt->execute(['tid' => $tenantId, 'uid' => $userId, 'date' => $date]);
        $savedTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Create lookup array
        $savedLookup = [];
        foreach ($savedTasks as $task) {
            $savedLookup[$task['task_key']] = $task;
        }
        
        // Merge with defaults
        $result = [];
        foreach ($defaultTasks as $default) {
            $key = $default['key'];
            $saved = $savedLookup[$key] ?? null;
            
            $result[] = [
                'key' => $key,
                'task' => $saved['task_name'] ?? $default['task'],
                'desc' => $saved['task_description'] ?? $default['desc'],
                'icon' => $default['icon'],
                'color' => $default['color'],
                'done' => $saved ? (bool)$saved['is_completed'] : false,
                'completed_at' => $saved['completed_at'] ?? null
            ];
        }
        
        return $result;
    } catch (Exception $e) {
        // Return defaults if table doesn't exist
        $result = [];
        foreach ($defaultTasks as $task) {
            $result[] = [
                'key' => $task['key'],
                'task' => $task['task'],
                'desc' => $task['desc'],
                'icon' => $task['icon'],
                'color' => $task['color'],
                'done' => false,
                'completed_at' => null
            ];
        }
        return $result;
    }
}
