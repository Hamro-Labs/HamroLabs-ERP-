<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FeeItem;
use App\Models\FeeRecord;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentService;
use Exception;
use PDO;

class TestFeeGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:fee-generation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tests the fee generation logic in StudentService';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting Test for Fee Generation Logic...");

        // Change directory to project root so relative paths like require_once('config.php') work
        chdir(dirname(dirname(dirname(__DIR__))));

        $db = \DB::connection()->getPdo();

        // Ensure a test tenant exists
        $stmt = $db->query("SELECT id FROM tenants LIMIT 1");
        $tenant = $stmt->fetch();
        if (!$tenant) {
            $this->error("No tenants found to test with.");
            return;
        }
        $tenantId = $tenant['id'];

        // We will manually clean up instead of using a transaction to avoid PDO nesting errors
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
            $this->info("\n[Test Case 1] Enrollment WITHOUT Fee Items");
            $studentService = new StudentService();
            
            $studentData1 = [
                'full_name' => 'Test Student 1',
                'email' => 'test1@example.com',
                'contact_number' => '9800000001',
                'batch_id' => $batchId,
                'registration_mode' => 'full'
            ];
            
            $result1 = $studentService->registerStudent($studentData1, $tenantId);
            $studentId1 = $result1['student']['id'];
            
            // Assert Fee Summary
            $stmtSum1 = $db->prepare("SELECT total_fee, due_amount FROM student_fee_summary WHERE student_id = :sid");
            $stmtSum1->execute(['sid' => $studentId1]);
            $sum1 = $stmtSum1->fetch();
            $this->line("Fee Summary Total: {$sum1['total_fee']}, Due: {$sum1['due_amount']}");
            if ($sum1['total_fee'] == 10000) $this->info("PASS: Summary Total Fee matches course fee."); else $this->error("FAIL: Summary Total Fee mismatch.");
            
            // Assert Fee Records (Should have fallback dummy item)
            $stmtRec1 = $db->prepare("SELECT COUNT(*) as count, SUM(amount_due) as total FROM fee_records WHERE student_id = :sid");
            $stmtRec1->execute(['sid' => $studentId1]);
            $rec1 = $stmtRec1->fetch();
            $this->line("Fee Records Count: {$rec1['count']}, Total Due: {$rec1['total']}");
            if ($rec1['count'] == 1 && $rec1['total'] == 10000) $this->info("PASS: Fallback fee record generated successfully."); else $this->error("FAIL: Fallback record generation failed.");

            // Test Case 2: Enrollment WITH Fee Items
            $this->info("\n[Test Case 2] Enrollment WITH Fee Items");
            
            // Add Fee Items to the course
            $db->prepare("INSERT INTO fee_items (tenant_id, course_id, name, type, amount, installments, is_active) VALUES (?, ?, 'Admission Fee', 'other', 2000, 1, 1)")->execute([$tenantId, $courseId]);
            $db->prepare("INSERT INTO fee_items (tenant_id, course_id, name, type, amount, installments, is_active) VALUES (?, ?, 'Monthly Tuition', 'monthly', 8000, 4, 1)")->execute([$tenantId, $courseId]);

            $studentData2 = [
                'full_name' => 'Test Student 2',
                'email' => 'test2@example.com',
                // Skipping email and pass to avoid MailHelper config issues
                'contact_number' => '9800000002',
                'batch_id' => $batchId,
                'registration_mode' => 'full'
            ];
            
            $result2 = $studentService->registerStudent($studentData2, $tenantId);
            $studentId2 = $result2['student']['id'];
            
            // Assert Fee Summary
            $stmtSum2 = $db->prepare("SELECT total_fee, due_amount FROM student_fee_summary WHERE student_id = :sid");
            $stmtSum2->execute(['sid' => $studentId2]);
            $sum2 = $stmtSum2->fetch();
            $this->line("Fee Summary Total: {$sum2['total_fee']}, Due: {$sum2['due_amount']}");
            if ($sum2['total_fee'] == 10000) $this->info("PASS: Summary Total Fee matches course fee."); else $this->error("FAIL: Summary Total Fee mismatch.");
            
            // Assert Fee Records (Should have 1 + 4 = 5 items)
            $stmtRec2 = $db->prepare("SELECT COUNT(*) as count, SUM(amount_due) as total FROM fee_records WHERE student_id = :sid");
            $stmtRec2->execute(['sid' => $studentId2]);
            $rec2 = $stmtRec2->fetch();
            $this->line("Fee Records Count: {$rec2['count']}, Total Due: {$rec2['total']}");
            if ($rec2['count'] == 5 && $rec2['total'] == 10000) $this->info("PASS: Multiple fee records generated successfully."); else $this->error("FAIL: Multiple fee records failed.");

            // Clean up test data manually
            $db->prepare("DELETE FROM batches WHERE id = ?")->execute([$batchId]);
            $db->prepare("DELETE FROM courses WHERE id = ?")->execute([$courseId]);
            $db->prepare("DELETE FROM students WHERE id IN (?, ?)")->execute([$studentId1, $studentId2]);
            $this->info("\nTest data cleaned up successfully.");

        } catch (Exception $e) {
            $this->error("Test failed with exception: " . $e->getMessage());
        }
    }
}
