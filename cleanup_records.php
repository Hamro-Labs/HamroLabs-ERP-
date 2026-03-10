<?php
/**
 * Script to clean up database records for all users (students, teachers, admins, front desk, guardians)
 * EXCEPT the super admin.
 */

require_once __DIR__ . '/config/config.php';

try {
    $db = getDBConnection();
    
    echo "Starting database cleanup...\n";
    echo "--------------------------\n";
    
    // Disable foreign key checks to allow truncating tables with constraints
    $db->exec('SET FOREIGN_KEY_CHECKS = 0');
    
    // 1. Delete all users EXCEPT 'superadmin'
    // Roles in DB: 'superadmin','instituteadmin','teacher','student','guardian','frontdesk'
    $stmt = $db->query("DELETE FROM users WHERE role != 'superadmin'");
    $usersDeleted = $stmt->rowCount();
    echo "Deleted {$usersDeleted} users (Roles safely removed: student, teacher, instituteadmin, frontdesk, guardian).\n";
    
    // 2. Truncate specific profile tables associated with the deleted users
    $profileTables = [
        'students',
        'teachers',
        'staff',       // Used for admin (instituteadmin) and front desk profiles
        'guardians'
    ];
    
    foreach ($profileTables as $table) {
        $check = $db->query("SHOW TABLES LIKE '$table'");
        if ($check->rowCount() > 0) {
            $db->exec("TRUNCATE TABLE `$table`");
            echo "Truncated profile table: $table\n";
        }
    }
    
    // 3. (Optional) Truncate common transaction tables tied to these users
    // If you also want to remove all attendance, exams, fees, etc., uncomment the array below:
    /*
    $transactionTables = [
        'attendance',
        'staff_attendance',
        'exams',
        'exam_results',
        'assignments',
        'assignment_submissions',
        'student_fee_summary',
        'student_payments',
        'student_invoices',
        'payment_transactions',
        'notices',
        'support_tickets',
        'api_logs',
        'sessions'
    ];
    
    foreach ($transactionTables as $table) {
        $check = $db->query("SHOW TABLES LIKE '$table'");
        if ($check->rowCount() > 0) {
            $db->exec("TRUNCATE TABLE `$table`");
            echo "Truncated transaction table: $table\n";
        }
    }
    */
    
    // Re-enable foreign key checks
    $db->exec('SET FOREIGN_KEY_CHECKS = 1');
    
    echo "--------------------------\n";
    echo "Cleanup completed successfully!\n";
    
} catch (Exception $e) {
    // Attempt to re-enable foreign keys if something goes wrong
    if (isset($db)) {
        try {
            $db->exec('SET FOREIGN_KEY_CHECKS = 1');
        } catch (Exception $innerE) {
            // Ignore
        }
    }
    echo "\nError during cleanup: " . $e->getMessage() . "\n";
}
