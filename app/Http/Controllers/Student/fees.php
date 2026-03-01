<?php
/**
 * Student Portal — Fee Controller
 * Handles student-specific fee queries and payment history
 */

use App\Models\FeeRecord;
use App\Models\StudentInvoice;
use App\Models\PaymentTransaction;
use App\Models\Student;

// Set JSON header
header('Content-Type: application/json');

// Ensure student is logged in and session is valid
$studentId = $_SESSION['student_id'] ?? null;
$tenantId = $_SESSION['tenant_id'] ?? null;

if (!$studentId || !$tenantId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$action = $_GET['action'] ?? 'get_ledger';

// Initialize models
$feeRecordModel = new FeeRecord();
$transactionModel = new PaymentTransaction();

try {
    switch ($action) {
        case 'get_ledger':
            $ledger = $feeRecordModel->getByStudent($studentId, $tenantId);
            $transactions = $transactionModel->getByStudent($studentId, $tenantId);
            
            // Calculate summary
            $totalDue = 0;
            $totalPaid = 0;
            foreach ($ledger as $l) {
                $totalDue += $l['amount_due'];
                $totalPaid += $l['amount_paid'];
            }
            
            echo json_encode([
                'success' => true, 
                'data' => [
                    'ledger' => $ledger,
                    'transactions' => $transactions,
                    'summary' => [
                        'total_due' => $totalDue,
                        'total_paid' => $totalPaid,
                        'balance' => $totalDue - $totalPaid
                    ]
                ]
            ]);
            break;

        case 'get_outstanding':
            $ledger = $feeRecordModel->getByStudent($studentId, $tenantId);
            $outstanding = array_filter($ledger, function($l) {
                return ($l['amount_due'] - $l['amount_paid']) > 0;
            });
            echo json_encode(['success' => true, 'data' => array_values($outstanding)]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
