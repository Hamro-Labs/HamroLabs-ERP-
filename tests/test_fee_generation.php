<?php
/**
 * Test Fee Generation Logic
 */

require __DIR__ . '/../config/config.php';
require __DIR__ . '/../vendor/autoload.php';

// Stub Laravel's \DB for non-Laravel test environment
class DB {
    public static function connection() {
        return getDBConnection();
    }
}

if (!function_exists('base_path')) {
    function base_path($path = '') {
        return __DIR__ . '/../' . $path;
    }
}

$db = getDBConnection();

echo "Starting Test for Fee Generation Logic...\n";

// Ensure a test tenant exists
$stmt = $db->query("SELECT id FROM tenants LIMIT 1");
$tenant = $stmt->fetch();
if (!$tenant) {
    die("No tenants found to test with.\n");
}
$tenantId = $tenant['id'];

// Create temporary objects
$db->beginTransaction();
try {
    // 1. Create a dummy course with a fee
    $stmtC = $db->prepare("INSERT INTO courses (tenant_id, name, code, duration_months, fee) VALUES (:tid, 'Test Course', 'TEST1', 12, 10000)");
    $stmtC->execute(['tid' => $tenantId]);
    $courseId = $db->lastInsertId();

    // 2. Create a dummy batch
    $stmtB = $db->prepare("INSERT INTO batches (tenant_id, course_id, name, start_date) VALUES (:tid, :cid, 'Test Batch', CURDATE())");
    $stmtB->execute(['tid' => $tenantId, 'cid' => $courseId]);
    $batchId = $db->lastInsertId();

    // Test Case 1: Enrollment WITHOUT Fee Items
    echo "\n[Test Case 1] Enrollment WITHOUT Fee Items\n";
    $studentService = new \App\Services\StudentService();
    
    $studentData1 = [
        'full_name' => 'Test Student 1',
        'email' => 'test1@example.com',
        'contact_number' => '9800000001',
        'password' => 'pass123',
        'batch_id' => $batchId,
        'registration_mode' => 'full'
    ];
    
    $result1 = $studentService->registerStudent($studentData1, $tenantId);
    $studentId1 = $result1['student']['id'];
    
    // Assert Fee Summary
    $stmtSum1 = $db->prepare("SELECT total_fee, due_amount FROM student_fee_summary WHERE student_id = :sid");
    $stmtSum1->execute(['sid' => $studentId1]);
    $sum1 = $stmtSum1->fetch();
    echo "Fee Summary Total: {$sum1['total_fee']}, Due: {$sum1['due_amount']}\n";
    if ($sum1['total_fee'] == 10000) echo "PASS: Summary Total Fee matches course fee.\n"; else echo "FAIL: Summary Total Fee mismatch.\n";
    
    // Assert Fee Records (Should have fallback dummy item)
    $stmtRec1 = $db->prepare("SELECT COUNT(*) as count, SUM(amount_due) as total FROM fee_records WHERE student_id = :sid");
    $stmtRec1->execute(['sid' => $studentId1]);
    $rec1 = $stmtRec1->fetch();
    echo "Fee Records Count: {$rec1['count']}, Total Due: {$rec1['total']}\n";
    if ($rec1['count'] == 1 && $rec1['total'] == 10000) echo "PASS: Fallback fee record generated successfully.\n"; else echo "FAIL: Fallback record generation failed.\n";


    // Test Case 2: Enrollment WITH Fee Items
    echo "\n[Test Case 2] Enrollment WITH Fee Items\n";
    
    // Add Fee Items to the course
    $db->prepare("INSERT INTO fee_items (tenant_id, course_id, name, type, amount, installments, is_active) VALUES (?, ?, 'Admission Fee', 'one_time', 2000, 1, 1)")->execute([$tenantId, $courseId]);
    $db->prepare("INSERT INTO fee_items (tenant_id, course_id, name, type, amount, installments, is_active) VALUES (?, ?, 'Monthly Tuition', 'monthly', 8000, 4, 1)")->execute([$tenantId, $courseId]);

    $studentData2 = [
        'full_name' => 'Test Student 2',
        'email' => 'test2@example.com',
        'contact_number' => '9800000002',
        'password' => 'pass123',
        'batch_id' => $batchId,
        'registration_mode' => 'full'
    ];
    
    $result2 = $studentService->registerStudent($studentData2, $tenantId);
    $studentId2 = $result2['student']['id'];
    
    // Assert Fee Summary
    $stmtSum2 = $db->prepare("SELECT total_fee, due_amount FROM student_fee_summary WHERE student_id = :sid");
    $stmtSum2->execute(['sid' => $studentId2]);
    $sum2 = $stmtSum2->fetch();
    echo "Fee Summary Total: {$sum2['total_fee']}, Due: {$sum2['due_amount']}\n";
    if ($sum2['total_fee'] == 10000) echo "PASS: Summary Total Fee matches course fee.\n"; else echo "FAIL: Summary Total Fee mismatch.\n";
    
    // Assert Fee Records (Should have 1 + 4 = 5 items)
    $stmtRec2 = $db->prepare("SELECT COUNT(*) as count, SUM(amount_due) as total FROM fee_records WHERE student_id = :sid");
    $stmtRec2->execute(['sid' => $studentId2]);
    $rec2 = $stmtRec2->fetch();
    echo "Fee Records Count: {$rec2['count']}, Total Due: {$rec2['total']}\n";
    if ($rec2['count'] == 5 && $rec2['total'] == 10000) echo "PASS: Multiple fee records generated successfully.\n"; else echo "FAIL: Multiple fee records failed.\n";

    // Clean up test data
    $db->rollBack();
    echo "\nTest data rolled back successfully.\n";

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Test failed with exception: " . $e->getMessage() . "\n";
}
