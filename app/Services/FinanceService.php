<?php
/**
 * FinanceService
 * Handles core financial logic (Payments, Refunds, Summaries)
 */

namespace App\Services;

use App\Models\FeeRecord;
use App\Models\PaymentTransaction;
use App\Models\FeeSettings;
use App\Services\FeeCalculationService;
use Exception;

class FinanceService {
    private $db;
    private $feeRecordModel;
    private $transactionModel;
    private $settingsModel;
    private $calculationService;

    public function __construct() {
        if (class_exists('\Illuminate\Support\Facades\DB') && \Illuminate\Support\Facades\DB::getFacadeRoot()) {
            $this->db = \Illuminate\Support\Facades\DB::connection()->getPdo();
        } elseif (function_exists('getDBConnection')) {
            $this->db = getDBConnection();
        }
        $this->feeRecordModel = new FeeRecord();
        $this->transactionModel = new PaymentTransaction();
        $this->settingsModel = new FeeSettings();
        $this->calculationService = new FeeCalculationService();
    }

    /**
     * Record a student payment with transaction safety
     */
    public function recordPayment($input, $tenantId) {
        $this->db->beginTransaction();

        try {
            $feeRecordId = $input['fee_record_id'];
            $amountPaid = floatval($input['amount_paid']);
            $paidDate = $input['paid_date'] ?? date('Y-m-d');
            $paymentMode = strtolower(str_replace(' ', '_', $input['payment_mode'] ?? 'cash'));
            $receiptNo = $input['receipt_no'] ?? null;
            $fineAmount = floatval($input['fine_amount'] ?? 0);
            $notes = $input['notes'] ?? null;

            // 1. Get current fee record
            $feeRecord = $this->feeRecordModel->find($feeRecordId);
            if (!$feeRecord || $feeRecord['tenant_id'] != $tenantId) {
                throw new Exception("Fee record not found");
            }

            // 2. Generate receipt number if not provided
            if (!$receiptNo) {
                // generateDocNumber is in FeeCalculationService
                $receiptNo = $this->calculationService->generateDocNumber($tenantId, 'receipt');
                $this->settingsModel->incrementNumber($tenantId, 'receipt');
            }

            // 3. Calculate status
            $totalAmountToPay = floatval($feeRecord['amount_due']) + $fineAmount;
            $newPaidTotal = floatval($feeRecord['amount_paid']) + $amountPaid;
            $status = ($newPaidTotal >= $totalAmountToPay) ? 'paid' : 'partial';

            // 4. Record payment in fee_records (Audit logged inside Model)
            $this->feeRecordModel->recordPayment($feeRecordId, [
                'amount_paid' => $amountPaid,
                'paid_date' => $paidDate,
                'receipt_no' => $receiptNo,
                'receipt_path' => null,
                'payment_mode' => $paymentMode,
                'cashier_user_id' => $_SESSION['userData']['id'] ?? null,
                'fine_applied' => $fineAmount,
                'status' => $status
            ]);

            // 5. Sync with student_fee_summary
            $query = "UPDATE student_fee_summary SET 
                      paid_amount = paid_amount + ?,
                      due_amount = due_amount - ?,
                      fee_status = CASE 
                          WHEN (due_amount - ?) <= 0 THEN 'paid'
                          WHEN (paid_amount + ?) > 0 THEN 'partial'
                          ELSE 'unpaid'
                      END
                      WHERE student_id = ? AND tenant_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $amountPaid, $amountPaid, 
                $amountPaid, $amountPaid, 
                $feeRecord['student_id'], $tenantId
            ]);

            // 6. Log Transaction (Audit logged inside Model)
            $this->transactionModel->create([
                'tenant_id' => $tenantId,
                'student_id' => $feeRecord['student_id'],
                'fee_record_id' => $feeRecordId,
                'amount' => $amountPaid,
                'payment_method' => $paymentMode,
                'receipt_number' => $receiptNo,
                'receipt_path' => null,
                'payment_date' => $paidDate,
                'recorded_by' => $_SESSION['userData']['id'] ?? null,
                'notes' => $notes,
                'status' => 'completed'
            ]);

            $this->db->commit();
            return [
                'receipt_no' => $receiptNo,
                'fee_record' => $feeRecord,
                'amount_paid' => $amountPaid,
                'payment_mode' => $paymentMode,
                'paid_date' => $paidDate
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Record a bulk payment by distributing amount across outstanding records
     */
    public function recordBulkPayment($input, $tenantId) {
        $studentId = $input['student_id'];
        $totalAmountPaid = floatval($input['amount']);
        $paidDate = $input['payment_date'] ?? date('Y-m-d');
        $paymentMode = strtolower(str_replace(' ', '_', $input['payment_mode'] ?? 'cash'));
        $notes = $input['notes'] ?? null;

        $this->db->beginTransaction();

        try {
            // 1. Generate a single receipt number for the entire bulk transaction
            $receiptNo = $this->calculationService->generateDocNumber($tenantId, 'receipt');
            $this->settingsModel->incrementNumber($tenantId, 'receipt');

            // 2. Fetch outstanding records for this student, oldest first
            $stmt = $this->db->prepare("
                SELECT id, amount_due, amount_paid 
                FROM fee_records 
                WHERE student_id = :sid AND tenant_id = :tid 
                AND amount_due > amount_paid
                ORDER BY due_date ASC
            ");
            $stmt->execute(['sid' => $studentId, 'tid' => $tenantId]);
            $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $remainingAmount = $totalAmountPaid;
            $processedRecords = [];

            foreach ($records as $record) {
                if ($remainingAmount <= 0) break;

                $netDue = floatval($record['amount_due']) - floatval($record['amount_paid']);
                $paymentForThisRecord = min($remainingAmount, $netDue);
                
                $newPaidTotal = floatval($record['amount_paid']) + $paymentForThisRecord;
                $status = ($newPaidTotal >= floatval($record['amount_due'])) ? 'paid' : 'partial';

                // Update Fee Record
                $this->feeRecordModel->recordPayment($record['id'], [
                    'amount_paid' => $paymentForThisRecord,
                    'paid_date' => $paidDate,
                    'receipt_no' => $receiptNo,
                    'payment_mode' => $paymentMode,
                    'cashier_user_id' => $_SESSION['userData']['id'] ?? null,
                    'status' => $status
                ]);

                // Log Individual Transaction Component (linked to same receipt)
                $this->transactionModel->create([
                    'tenant_id' => $tenantId,
                    'student_id' => $studentId,
                    'fee_record_id' => $record['id'],
                    'amount' => $paymentForThisRecord,
                    'payment_method' => $paymentMode,
                    'receipt_number' => $receiptNo,
                    'payment_date' => $paidDate,
                    'recorded_by' => $_SESSION['userData']['id'] ?? null,
                    'notes' => $notes . " (Bulk Payment Part)",
                    'status' => 'completed'
                ]);

                $remainingAmount -= $paymentForThisRecord;
                $processedRecords[] = $record['id'];
            }

            // 3. Update Student Summary once for the total amount
            $query = "UPDATE student_fee_summary SET 
                      paid_amount = paid_amount + ?,
                      due_amount = due_amount - ?,
                      fee_status = CASE 
                          WHEN (due_amount - ?) <= 0 THEN 'paid'
                          WHEN (paid_amount + ?) > 0 THEN 'partial'
                          ELSE 'unpaid'
                      END
                      WHERE student_id = ? AND tenant_id = ?";
            $stmtSum = $this->db->prepare($query);
            $stmtSum->execute([
                $totalAmountPaid, $totalAmountPaid, 
                $totalAmountPaid, $totalAmountPaid, 
                $studentId, $tenantId
            ]);

            $this->db->commit();
            return [
                'success' => true,
                'receipt_no' => $receiptNo,
                'amount_paid' => $totalAmountPaid,
                'records_affected' => count($processedRecords)
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
