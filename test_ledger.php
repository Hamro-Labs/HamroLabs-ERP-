<?php
require 'config/config.php';
try {
    $tenantId = 5;
    $studentId = 33;
    $feeRecordModel = new \App\Models\FeeRecord();
    $transactionModel = new \App\Models\PaymentTransaction();

    $ledger = $feeRecordModel->getByStudent($studentId, $tenantId);
    $transactions = $transactionModel->getByStudent($studentId, $tenantId);
    $balance = $feeRecordModel->getStudentBalance($studentId, $tenantId);
    
    echo "SUCCESS\n";
    print_r(['ledger' => count($ledger), 'transactions' => count($transactions), 'balance' => $balance]);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
