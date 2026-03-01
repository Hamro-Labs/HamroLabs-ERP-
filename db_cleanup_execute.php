<?php
/**
 * Database Cleanup Script
 * WARNING: This script performs destructive operations.
 * It deletes students, teachers, and non-admin users along with all related data.
 */

require 'c:/Apache24/htdocs/erp/config/config.php';

try {
    $db = getDBConnection();
    $db->beginTransaction();

    echo "Starting database cleanup...\n";

    // 1. Roles to preserve
    $preserved_roles = ['instituteadmin', 'superadmin'];
    
    // Get user IDs to delete
    $stmt = $db->query("SELECT id FROM users WHERE role NOT IN ('" . implode("','", $preserved_roles) . "')");
    $user_ids_to_delete = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($user_ids_to_delete)) {
        echo "No non-admin users found to delete.\n";
    } else {
        $user_ids_list = implode(',', $user_ids_to_delete);
        echo "Found " . count($user_ids_to_delete) . " users to delete.\n";

        // 2. Clear Child Tables (referenced by student_id or teacher_id or user_id)
        $child_tables = [
            'attendance' => 'student_id',
            'attendance_audit_logs' => null, // No direct student_id in schema research? Truncate if safe or delete by join if exists
            'fee_records' => 'student_id',
            'payment_transactions' => 'student_id',
            'student_fee_summary' => 'student_id',
            'student_invoices' => 'student_id',
            'grades' => 'student_id',
            'leave_requests' => 'student_id',
            'visitors' => 'tenant_id', // Less specific but usually related to non-admins
            'notifications' => 'user_id',
            'messages' => 'sender_id', // Need to handle sender and recipient
            'timetable_slots' => 'teacher_id',
            'study_materials' => 'tenant_id', // Usually related to batches/courses
        ];

        foreach ($child_tables as $table => $column) {
            $check = $db->query("SHOW TABLES LIKE '$table'")->fetch();
            if ($check) {
                if ($column === 'student_id') {
                    $deleted = $db->exec("DELETE FROM `$table` WHERE student_id IS NOT NULL OR student_id IN (SELECT id FROM students)");
                    echo "Table $table: Deleted $deleted records.\n";
                } elseif ($column === 'user_id') {
                    $deleted = $db->exec("DELETE FROM `$table` WHERE user_id IN ($user_ids_list)");
                    echo "Table $table: Deleted $deleted records (by user_id).\n";
                } elseif ($table === 'messages') {
                    $deleted = $db->exec("DELETE FROM `$table` WHERE sender_id IN ($user_ids_list) OR receiver_id IN ($user_ids_list)");
                    echo "Table $table: Deleted $deleted records (by sender/receiver).\n";
                } else {
                    // Truncate non-essential tables for fresh start if they are mainly for students/teachers
                    if (in_array($table, ['attendance', 'attendance_audit_logs', 'fee_records', 'payment_transactions', 'student_fee_summary', 'student_invoices', 'grades', 'leave_requests'])) {
                         $deleted = $db->exec("DELETE FROM `$table` ");
                         echo "Table $table: Cleared all $deleted records.\n";
                    }
                }
            }
        }

        // 3. Delete Main Entities
        $db->exec("DELETE FROM students");
        echo "Cleared students table.\n";
        
        $db->exec("DELETE FROM teachers");
        echo "Cleared teachers table.\n";

        // 4. Delete Users
        $deleted_users = $db->exec("DELETE FROM users WHERE id IN ($user_ids_list)");
        echo "Deleted $deleted_users user accounts.\n";
    }

    $db->commit();
    echo "\nCleanup completed successfully. Only admin accounts remain.\n";

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "ERROR: " . $e->getMessage() . "\n";
}
