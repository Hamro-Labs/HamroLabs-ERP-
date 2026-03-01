<?php
/**
 * Fee Setup API Controller
 * Handles fee items setup for the current tenant
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../../config/config.php';
}

require_once base_path('vendor/autoload.php');

use Dompdf\Dompdf;
use Dompdf\Options;

header('Content-Type: application/json');

// Ensure user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$tenantId = $_SESSION['userData']['tenant_id'] ?? null;
if (!$tenantId) {
    echo json_encode(['success' => false, 'message' => 'Tenant ID missing']);
    exit;
}

$role = $_SESSION['userData']['role'] ?? '';
// RBAC check
if (!in_array($role, ['instituteadmin', 'frontdesk', 'superadmin'])) {
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if (!function_exists('getReceiptHtmlString')) {
    function getReceiptHtmlString($db, $tenantId, $transactionId, $receiptNo = null) {
        if (!$transactionId && !$receiptNo) return "";

        $query = "
            SELECT pt.*, fr.fee_item_id, fi.name as fee_item_name, fi.amount as fee_item_amount,
                   s.full_name as student_name, COALESCE(NULLIF(s.email, ''), u.email) as student_email, s.phone,
                   COALESCE(JSON_UNQUOTE(JSON_EXTRACT(s.permanent_address, '$.district')), '') as student_address,
                   s.roll_no, c.name as course_name, b.name as batch_name,
                   fr.amount_due, fr.amount_paid as record_paid, fr.fine_applied,
                   t.name as institute_name, t.address as institute_address,
                   t.phone as institute_contact, t.email as institute_email,
                   t.logo_path as institute_logo
            FROM payment_transactions pt
            JOIN fee_records fr ON pt.fee_record_id = fr.id
            JOIN fee_items fi ON fr.fee_item_id = fi.id
            JOIN students s ON pt.student_id = s.id
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN batches b ON s.batch_id = b.id
            LEFT JOIN courses c ON b.course_id = c.id
            LEFT JOIN tenants t ON pt.tenant_id = t.id
            WHERE pt.tenant_id = :tenant
        ";

        $params = ['tenant' => $tenantId];
        if ($transactionId) {
            $query .= " AND pt.id = :tid";
            $params['tid'] = $transactionId;
        } else {
            $query .= " AND pt.receipt_number = :rno";
            $params['rno'] = $receiptNo;
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!$transactions) return "";
        
        $txn = $transactions[0];
        $logoPath = $txn['institute_logo'] ?? '';
        $logoUrl = '';
        if ($logoPath) {
            if (strpos($logoPath, '/uploads/') === 0 && strpos($logoPath, '/public/') !== 0) {
                $logoPath = '/public' . $logoPath;
            }
            $logoUrl = (defined('APP_URL') ? APP_URL : '') . $logoPath;
        }

        $totalPaid = 0;
        $items = [];
        foreach ($transactions as $t) {
            $totalPaid += floatval($t['amount']);
            $items[] = [
                'name' => $t['fee_item_name'],
                'amount' => $t['amount']
            ];
        }

        $receiptData = [
            'institute_name'    => $txn['institute_name'] ?? 'Institute',
            'institute_address' => $txn['institute_address'] ?? '',
            'institute_contact' => $txn['institute_contact'] ?? '',
            'institute_email'   => $txn['institute_email'] ?? '',
            'institute_logo_url'=> $logoUrl,
            'receipt_no'        => $txn['receipt_number'],
            'date_ad'           => $txn['payment_date'],
            'date_bs'           => '', 
            'student_name'      => $txn['student_name'],
            'student_email'     => $txn['student_email'] ?? '',
            'course_name'       => $txn['course_name'] ?? '',
            'batch_name'        => $txn['batch_name'] ?? '',
            'course_fee'        => floatval($txn['amount_due']),
            'paid_amount'       => $totalPaid,
            'remaining'         => max(0, floatval($txn['amount_due']) - floatval($txn['record_paid'])),
            'fine_amount'       => $txn['fine_applied'] ?? 0,
            'address'           => $txn['student_address'] ?? '',
            'contact_number'    => $txn['phone'] ?? '',
            'payment_mode'      => $txn['payment_method'],
            'transaction_id'    => $txn['id'],
            'remarks'           => $txn['notes'] ?? '',
            'items'             => $items,
            'is_email'          => true,
            'auto_download'     => empty($_GET['is_email'])
        ];

        ob_start();
        $isDownload = empty($_GET['is_email']);
        require base_path('scripts/receipt_template.php');
        return ob_get_clean();
    }
}

if (!function_exists('getReceiptPdfPath')) {
    function getReceiptPdfPath($db, $tenantId, $transactionId, $receiptNo = null) {
        $html = getReceiptHtmlString($db, $tenantId, $transactionId, $receiptNo);
        if (!$html) return null;

        $pdfDir = __DIR__ . '/../../../../public/uploads/receipts/';
        if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);
        
        $filename = 'receipt_' . ($receiptNo ?: $transactionId) . '.pdf';
        $pdfPath = $pdfDir . $filename;

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Arial');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            file_put_contents($pdfPath, $dompdf->output());
            return $pdfPath;
        } catch (\Exception $e) {
            error_log("PDF Generation Error: " . $e->getMessage());
            return null;
        }
    }
}

try {
    $db = getDBConnection();
    
    // Initialize models and services
    $feeItemModel = new \App\Models\FeeItem();
    $feeRecordModel = new \App\Models\FeeRecord();
    $settingsModel = new \App\Models\FeeSettings();
    $invoiceModel = new \App\Models\StudentInvoice();
    $transactionModel = new \App\Models\PaymentTransaction();
    $calculationService = new \App\Services\FeeCalculationService();
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'list';

        if ($action === 'list') {
            // List fee items
            $query = "SELECT fi.*, 
                      c.name as course_name,
                      (SELECT COUNT(*) FROM fee_records fr WHERE fr.fee_item_id = fi.id) as total_records
                      FROM fee_items fi
                      LEFT JOIN courses c ON fi.course_id = c.id
                      WHERE fi.tenant_id = :tid AND fi.deleted_at IS NULL";
            
            $params = ['tid' => $tenantId];

            if (!empty($_GET['id'])) {
                $query .= " AND fi.id = :id";
                $params['id'] = $_GET['id'];
            }

            if (!empty($_GET['course_id'])) {
                $query .= " AND fi.course_id = :course_id";
                $params['course_id'] = $_GET['course_id'];
            }

            if (!empty($_GET['type'])) {
                $query .= " AND fi.type = :type";
                $params['type'] = $_GET['type'];
            }

            if (!empty($_GET['search'])) {
                $search = '%' . $_GET['search'] . '%';
                $query .= " AND (fi.name LIKE :search)";
                $params['search'] = $search;
            }

            $query .= " ORDER BY fi.type ASC, fi.name ASC";

            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $feeItems = $stmt->fetchAll();

            // Get courses for dropdown
            $stmt = $db->prepare("SELECT id, name FROM courses WHERE tenant_id = :tid AND deleted_at IS NULL ORDER BY name ASC");
            $stmt->execute(['tid' => $tenantId]);
            $courses = $stmt->fetchAll();

            echo json_encode([
                'success' => true, 
                'data' => $feeItems,
                'courses' => $courses
            ]);
        }
        else if ($action === 'get_outstanding') {
            // Get outstanding fees for a student or all students
            $studentId = $_GET['student_id'] ?? null;
            
            if ($studentId) {
                // Get outstanding for specific student + total summary
                $query = "SELECT fr.*, fi.name as fee_item_name, fi.type as fee_type, 
                          s.full_name as student_name, s.roll_no as student_code
                          FROM fee_records fr
                          JOIN fee_items fi ON fr.fee_item_id = fi.id
                          JOIN students s ON fr.student_id = s.id
                          WHERE fr.tenant_id = :tid AND fr.student_id = :sid 
                          AND fr.amount_due > fr.amount_paid
                          ORDER BY fr.due_date ASC";
                $stmt = $db->prepare($query);
                $stmt->execute(['tid' => $tenantId, 'sid' => $studentId]);
                $records = $stmt->fetchAll();

                // Get summary from student_fee_summary
                $stmt = $db->prepare("SELECT * FROM student_fee_summary WHERE student_id = :sid");
                $stmt->execute(['sid' => $studentId]);
                $summary = $stmt->fetch();
                
                if ($summary && $summary['due_amount'] > 0 && count($records) === 0) {
                    try {
                        $db->beginTransaction();
                        $stmtB = $db->prepare("SELECT batch_id FROM students WHERE id = :sid");
                        $stmtB->execute(['sid' => $studentId]);
                        $batchInfo = $stmtB->fetch();
                        $batchId = $batchInfo ? $batchInfo['batch_id'] : null;

                        $stmtF = $db->prepare("SELECT id FROM fee_items WHERE tenant_id = :tid LIMIT 1");
                        $stmtF->execute(['tid' => $tenantId]);
                        $feeItem = $stmtF->fetch();
                        if (!$feeItem) {
                             $db->prepare("INSERT INTO fee_items (tenant_id, name, type, amount, installments, is_active) VALUES (?, 'Base Course Fee', 'one_time', 0, 1, 1)")->execute([$tenantId]);
                             $feeItemId = $db->lastInsertId();
                        } else {
                             $feeItemId = $feeItem['id'];
                        }

                        $recordAmt = (float)$summary['total_fee'];
                        $paid = $recordAmt - (float)$summary['due_amount'];
                        $status = ($paid >= $recordAmt) ? 'paid' : 'pending';

                        $stmtR = $db->prepare("INSERT INTO fee_records (tenant_id, student_id, batch_id, fee_item_id, installment_no, amount_due, amount_paid, due_date, status, academic_year) VALUES (?, ?, ?, ?, 1, ?, ?, CURDATE(), ?, ?)");
                        $stmtR->execute([
                            $tenantId, 
                            $studentId, 
                            $batchId, 
                            $feeItemId, 
                            $recordAmt,
                            $paid,
                            $status,
                            date('Y') . '-' . (date('Y') + 1)
                        ]);
                        $db->commit();
                        
                        // Refetch records
                        $stmtFetch = $db->prepare($query);
                        $stmtFetch->execute(['tid' => $tenantId, 'sid' => $studentId]);
                        $records = $stmtFetch->fetchAll();
                        
                    } catch (Exception $e) {
                         if ($db->inTransaction()) $db->rollBack();
                    }
                }

                $response = [
                    'success' => true, 
                    'data' => $records,
                    'summary' => $summary
                ];

                echo json_encode($response);
            } else {
                // Get summary for all students with outstanding
                // Optimized: Join with pre-calculated counts from fee_records to avoid correlated subqueries
                $query = "SELECT s.id as student_id, s.full_name as student_name, c.id as course_id, c.name as course_name,
                                 sfs.total_fee as total_due, sfs.paid_amount as total_paid, sfs.due_amount as current_balance,
                                 fr_stats.next_due_date, fr_stats.outstanding_count
                          FROM student_fee_summary sfs
                          JOIN students s ON sfs.student_id = s.id
                          LEFT JOIN batches b ON s.batch_id = b.id
                          LEFT JOIN courses c ON b.course_id = c.id
                          LEFT JOIN (
                              SELECT student_id, MIN(due_date) as next_due_date, COUNT(*) as outstanding_count
                              FROM fee_records
                              WHERE amount_due > amount_paid AND tenant_id = :tid2
                              GROUP BY student_id
                          ) fr_stats ON s.id = fr_stats.student_id
                          WHERE sfs.due_amount > 0 AND sfs.tenant_id = :tid
                          ORDER BY sfs.due_amount DESC 
                          LIMIT 1000";
                $stmt = $db->prepare($query);
                $stmt->execute(['tid' => $tenantId, 'tid2' => $tenantId]);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $data]);
            }
            return;
        }
        else if ($action === 'get_recent_payments') {
            // Get recent payments from transactions table
            $query = "SELECT pt.*, fi.name as fee_item_name, s.full_name as student_name, pt.receipt_number as receipt_no, pt.amount as amount_paid
                      FROM payment_transactions pt
                      JOIN fee_records fr ON pt.fee_record_id = fr.id
                      JOIN fee_items fi ON fr.fee_item_id = fi.id
                      JOIN students s ON pt.student_id = s.id
                      WHERE pt.tenant_id = :tid
                      ORDER BY pt.payment_date DESC, pt.id DESC
                      LIMIT 50";
            $stmt = $db->prepare($query);
            $stmt->execute(['tid' => $tenantId]);
            $data = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $data]);
        }
        else if ($action === 'get_calculated_fine') {
            $feeRecordId = $_GET['fee_record_id'] ?? null;
            if (!$feeRecordId) throw new Exception("Fee record ID required");

            $fine = $calculationService->calculateLateFine($feeRecordId);

            echo json_encode([
                'success' => true,
                'data' => ['fine' => $fine]
            ]);
        }
        else if ($action === 'get_student_ledger') {
            $studentId = $_GET['student_id'] ?? null;
            if (!$studentId) throw new Exception("Student ID required");

            $ledger = $feeRecordModel->getByStudent($studentId, $tenantId);
            $transactions = $transactionModel->getByStudent($studentId, $tenantId);
            $balance = $feeRecordModel->getStudentBalance($studentId, $tenantId);

            echo json_encode([
                'success' => true,
                'data' => [
                    'ledger' => $ledger,
                    'transactions' => $transactions,
                    'balance' => $balance
                ]
            ]);
        }
        

        else if ($action === 'get_payment_details') {
            $transactionId = $_GET['transaction_id'] ?? null;
            if (!$transactionId) throw new Exception("Transaction ID required");
            
            $stmt = $db->prepare("
                SELECT pt.*, fr.fee_item_id, fi.name as fee_item_name, s.full_name as student_name, s.email,
                       c.name as course_name, b.name as batch_name, fr.amount_due, fr.fine_applied
                FROM payment_transactions pt
                JOIN fee_records fr ON pt.fee_record_id = fr.id
                JOIN fee_items fi ON fr.fee_item_id = fi.id
                JOIN students s ON pt.student_id = s.id
                LEFT JOIN batches b ON s.batch_id = b.id
                LEFT JOIN courses c ON b.course_id = c.id
                WHERE pt.id = :tid AND pt.tenant_id = :tenant
            ");
            $stmt->execute(['tid' => $transactionId, 'tenant' => $tenantId]);
            $txn = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$txn) throw new Exception("Transaction not found");
            
            // Receipt URL now points to the HTML receipt page
            $receiptUrl = APP_URL . '/api/admin/fees?action=generate_receipt_html&transaction_id=' . $transactionId;
            $imageUrl = $txn['receipt_path'] ? APP_URL . '/public/' . $txn['receipt_path'] : null;

            echo json_encode([
                'success' => true,
                'data' => [
                    'transaction' => $txn,
                    'receipt_url' => $receiptUrl,
                    'pdf_url' => $receiptUrl,
                    'image_url' => $imageUrl
                ]
            ]);
        }
        else if ($action === 'get_payment_init_data') {
            $studentId = $_GET['student_id'] ?? null;
            if (!$studentId) throw new Exception("Student ID required");

            // 1. Fetch Student Details
            $stmtS = $db->prepare("
                SELECT s.id, s.full_name as name, s.roll_no, s.photo_url,
                       c.name as course_name, b.name as batch_name
                FROM students s
                LEFT JOIN batches b ON s.batch_id = b.id
                LEFT JOIN courses c ON b.course_id = c.id
                WHERE s.id = :sid AND s.tenant_id = :tid
            ");
            $stmtS->execute(['sid' => $studentId, 'tid' => $tenantId]);
            $student = $stmtS->fetch(\PDO::FETCH_ASSOC);
            if (!$student) throw new Exception("Student not found");

            // 2. Fetch Institute (Tenant) Details
            $stmtI = $db->prepare("SELECT name, logo_path, address, phone, email FROM tenants WHERE id = :tid");
            $stmtI->execute(['tid' => $tenantId]);
            $institute = $stmtI->fetch(\PDO::FETCH_ASSOC);

            // 3. Fetch Fee Summary
            $stmtSum = $db->prepare("SELECT * FROM student_fee_summary WHERE student_id = :sid");
            $stmtSum->execute(['sid' => $studentId]);
            $summary = $stmtSum->fetch(\PDO::FETCH_ASSOC);

            // 4. Fetch Outstanding Records
            $stmtRecs = $db->prepare("
                SELECT fr.*, fi.name as fee_item_name, fi.type as fee_type
                FROM fee_records fr
                JOIN fee_items fi ON fr.fee_item_id = fi.id
                WHERE fr.student_id = :sid AND fr.tenant_id = :tid 
                AND fr.amount_due > fr.amount_paid
                ORDER BY fr.due_date ASC
            ");
            $stmtRecs->execute(['sid' => $studentId, 'tid' => $tenantId]);
            $records = $stmtRecs->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => [
                    'student' => $student,
                    'institute' => $institute,
                    'summary' => $summary,
                    'records' => $records
                ]
            ]);
        }
        else if ($action === 'generate_receipt_html') {
            $transactionId = $_GET['transaction_id'] ?? null;
            $receiptNo = $_GET['receipt_no'] ?? null;
            
            if (!$transactionId && !$receiptNo) throw new Exception("Transaction ID or Receipt Number required");        
            $html = getReceiptHtmlString($db, $tenantId, $transactionId, $receiptNo);
            if (!$html) throw new Exception("Payment not found");
            
            if (!empty($_GET['is_pdf'])) {
                $options = new Options();
                $options->set('isHtml5ParserEnabled', true);
                $options->set('isRemoteEnabled', true);
                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $dompdf->stream("Receipt_" . ($receiptNo ?: $transactionId) . ".pdf", ["Attachment" => true]);
                exit;
            }

            header('Content-Type: text/html; charset=UTF-8');
            echo $html;
            exit;
        }
    }

    else if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $action = $input['action'] ?? 'create';

        if ($action === 'create' || $action === 'update') {
            $name = $input['name'] ?? '';
            $courseId = $input['course_id'] ?? null;
            $type = $input['type'] ?? 'monthly';
            $amount = floatval($input['amount'] ?? 0);
            $installments = intval($input['installments'] ?? 1);
            $lateFinePerDay = floatval($input['late_fine_per_day'] ?? 0);
            $isActive = isset($input['is_active']) ? ($input['is_active'] ? 1 : 0) : 1;

            if (empty($name)) {
                throw new Exception("Fee item name is required");
            }
            if (empty($courseId)) {
                throw new Exception("Please select a course");
            }
            if ($amount <= 0) {
                throw new Exception("Amount must be greater than 0");
            }

            if ($action === 'create') {
                $stmt = $db->prepare("
                    INSERT INTO fee_items (tenant_id, course_id, name, type, amount, installments, late_fine_per_day, is_active) 
                    VALUES (:tid, :course_id, :name, :type, :amount, :installments, :late_fine, :is_active)
                ");

                $stmt->execute([
                    'tid' => $tenantId,
                    'course_id' => $courseId,
                    'name' => $name,
                    'type' => $type,
                    'amount' => $amount,
                    'installments' => $installments,
                    'late_fine' => $lateFinePerDay,
                    'is_active' => $isActive
                ]);

                $feeItemId = $db->lastInsertId();

                echo json_encode([
                    'success' => true, 
                    'message' => 'Fee item created successfully',
                    'data' => ['id' => $feeItemId]
                ]);
            } else {
                $id = $input['id'] ?? null;
                if (!$id) {
                    throw new Exception("Fee item ID is required for update");
                }

                $stmt = $db->prepare("
                    UPDATE fee_items 
                    SET name = :name, course_id = :course_id, type = :type, 
                        amount = :amount, installments = :installments, 
                        late_fine_per_day = :late_fine, is_active = :is_active
                    WHERE id = :id AND tenant_id = :tid
                ");

                $stmt->execute([
                    'id' => $id,
                    'tid' => $tenantId,
                    'course_id' => $courseId,
                    'name' => $name,
                    'type' => $type,
                    'amount' => $amount,
                    'installments' => $installments,
                    'late_fine' => $lateFinePerDay,
                    'is_active' => $isActive
                ]);

                echo json_encode([
                    'success' => true, 
                    'message' => 'Fee item updated successfully'
                ]);
            }
        } 
        else if ($action === 'delete') {
            $id = $input['id'] ?? null;
            if (!$id) {
                throw new Exception("Fee item ID is required");
            }

            // Check if there are fee records
            $stmt = $db->prepare("SELECT COUNT(*) FROM fee_records WHERE fee_item_id = :id");
            $stmt->execute(['id' => $id]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                // Soft delete - just mark as inactive
                $stmt = $db->prepare("UPDATE fee_items SET is_active = 0 WHERE id = :id AND tenant_id = :tid");
                $stmt->execute(['id' => $id, 'tid' => $tenantId]);
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Fee item deactivated (has existing fee records)'
                ]);
            } else {
                // Hard delete
                $stmt = $db->prepare("DELETE FROM fee_items WHERE id = :id AND tenant_id = :tid");
                $stmt->execute(['id' => $id, 'tid' => $tenantId]);
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Fee item deleted successfully'
                ]);
            }
        }
        else if ($action === 'toggle') {
            $id = $input['id'] ?? null;
            if (!$id) {
                throw new Exception("Fee item ID is required");
            }

            $stmt = $db->prepare("UPDATE fee_items SET is_active = NOT is_active WHERE id = :id AND tenant_id = :tid");
            $stmt->execute(['id' => $id, 'tid' => $tenantId]);

            echo json_encode([
                'success' => true, 
                'message' => 'Fee item status updated'
            ]);
        }
        else if ($action === 'record_payment') {
            // 1. Record via Service
            $financeService = new \App\Services\FinanceService();
            $result = $financeService->recordPayment($input, $tenantId);

            // 2. Get the transaction ID for receipt link
            $transactionId = null;
            try {
                $stmt = $db->prepare("SELECT id FROM payment_transactions WHERE receipt_number = :rno AND tenant_id = :tid ORDER BY id DESC LIMIT 1");
                $stmt->execute(['rno' => $result['receipt_no'], 'tid' => $tenantId]);
                $txnRow = $stmt->fetch(\PDO::FETCH_ASSOC);
                $transactionId = $txnRow ? $txnRow['id'] : null;
            } catch (\Exception $e) {
                error_log("Could not fetch transaction ID: " . $e->getMessage());
            }

            // 3. Try to send email with receipt
            $emailStatus = 'failed';
            $amount = floatval($input['amount_paid'] ?? 0);
            try {
                $stmt = $db->prepare("
                    SELECT s.full_name as student_name, COALESCE(NULLIF(s.email, ''), u.email) as email 
                    FROM students s 
                    LEFT JOIN users u ON s.user_id = u.id 
                    WHERE s.id = :sid AND s.tenant_id = :tid
                ");
                $stmt->execute(['sid' => $result['fee_record']['student_id'], 'tid' => $tenantId]);
                $student = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if (empty($student['email'])) {
                    $emailStatus = 'no_email';
                } else if ($transactionId) {
                    $pdfPath = getReceiptPdfPath($db, $tenantId, $transactionId);
                    
                    if ($pdfPath && file_exists($pdfPath) && method_exists('\App\Helpers\MailHelper', 'sendPaymentReceiptEmail')) {
                        $success = \App\Helpers\MailHelper::sendPaymentReceiptEmail(
                            $db, $tenantId, $student['email'], $student['student_name'],
                            $result['receipt_no'], $pdfPath, null, $amount
                        );
                        $emailStatus = $success ? 'sent' : 'failed';
                    } else {
                        // Fallback: Send email without PDF attachment if generation failed
                        $emailUrl = APP_URL . '/api/admin/fees?action=generate_receipt_html&is_email=1&';
                        $emailUrl .= $transactionId ? 'transaction_id=' . $transactionId : 'receipt_no=' . $result['receipt_no'];
                        
                        $ch = curl_init($emailUrl);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                        $message = curl_exec($ch);
                        curl_close($ch);

                        if (empty($message) || strpos($message, 'Error') !== false) {
                            $message = "<div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                                <h2>Hello " . htmlspecialchars($student['student_name']) . ",</h2>
                                <p>Thank you for your payment. Your transaction has been recorded successfully.</p>
                                <ul>
                                    <li><strong>Receipt No:</strong> " . $result['receipt_no'] . "</li>
                                    <li><strong>Amount Paid:</strong> " . number_format($result['amount_paid'], 2) . "</li>
                                </ul>
                                <p>You can view and download your full receipt from your dashboard.</p>
                            </div>";
                        }

                        $subject = "Payment Receipt Confirmation - " . $result['receipt_no'];
                        $success = \App\Helpers\MailHelper::send($db, $tenantId, $student['email'], $student['student_name'], $subject, $message);
                        $emailStatus = $success ? 'sent_no_pdf' : 'failed';
                    }
                }
            } catch (\Exception $e) {
                error_log("Receipt email failed: " . $e->getMessage());
                $emailStatus = 'failed';
            }

            echo json_encode([
                'success' => true, 
                'message' => 'Payment recorded successfully!',
                'data' => [
                    'receipt_no' => $result['receipt_no'],
                    'transaction_id' => $transactionId,
                    'amount_paid' => $result['amount_paid'],
                    'email_status' => $emailStatus,
                    'student_id' => $result['fee_record']['student_id'],
                    'student_name' => $student['student_name'] ?? 'Student'
                ]
            ]);
            exit;
        }
        else if ($action === 'record_bulk_payment') {
            $data = $input; // Already decoded or from POST
            $financeService = new \App\Services\FinanceService();
            $result = $financeService->recordBulkPayment($data, $tenantId);
            
            // Try to send email
            $emailStatus = 'failed';
            try {
                $stmt = $db->prepare("
                    SELECT s.full_name as student_name, COALESCE(NULLIF(s.email, ''), u.email) as email 
                    FROM students s 
                    LEFT JOIN users u ON s.user_id = u.id 
                    WHERE s.id = :sid AND s.tenant_id = :tid
                ");
                $stmt->execute(['sid' => $data['student_id'], 'tid' => $tenantId]);
                $student = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if (empty($student['email'])) {
                    $emailStatus = 'no_email';
                } else {
                    $pdfPath = getReceiptPdfPath($db, $tenantId, null, $result['receipt_no']);
                    
                    if ($pdfPath && file_exists($pdfPath) && method_exists('\App\Helpers\MailHelper', 'sendPaymentReceiptEmail')) {
                        $success = \App\Helpers\MailHelper::sendPaymentReceiptEmail(
                            $db, $tenantId, $student['email'], $student['student_name'],
                            $result['receipt_no'], $pdfPath, null, $result['amount_paid']
                        );
                        $emailStatus = $success ? 'sent' : 'failed';
                    } else {
                        // Fallback: Send email without PDF attachment if generation failed
                        $emailUrl = APP_URL . '/api/admin/fees?action=generate_receipt_html&is_email=1&receipt_no=' . $result['receipt_no'];
                        
                        $ch = curl_init($emailUrl);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                        $message = curl_exec($ch);
                        curl_close($ch);

                        if (empty($message) || strpos($message, 'Error') !== false) {
                            $message = "<div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                                <h2>Hello " . htmlspecialchars($student['student_name']) . ",</h2>
                                <p>Thank you for your payment. Your transaction has been recorded successfully.</p>
                                <ul>
                                    <li><strong>Receipt No:</strong> " . $result['receipt_no'] . "</li>
                                    <li><strong>Amount Paid:</strong> " . number_format($result['amount_paid'], 2) . "</li>
                                </ul>
                                <p>You can view and download your full receipt from your dashboard.</p>
                            </div>";
                        }

                        $subject = "Payment Receipt Confirmation - " . $result['receipt_no'];
                        $success = \App\Helpers\MailHelper::send($db, $tenantId, $student['email'], $student['student_name'], $subject, $message);
                        $emailStatus = $success ? 'sent_no_pdf' : 'failed';
                    }
                }
            } catch (\Exception $e) {
                error_log("Bulk receipt email failed: " . $e->getMessage());
                $emailStatus = 'failed';
            }

            echo json_encode([
                'success' => true,
                'message' => 'Bulk payment recorded successfully!',
                'data' => [
                    'receipt_no' => $result['receipt_no'],
                    'amount_paid' => $result['amount_paid'],
                    'email_status' => $emailStatus,
                    'student_id' => $data['student_id'],
                    'student_name' => $student['student_name'] ?? 'Student'
                ]
            ]);
            exit;
        }
        else if ($action === 'send_payment_email') {
            $transactionId = $input['transaction_id'] ?? null;
            if (!$transactionId) {
                throw new Exception("Transaction ID is required");
            }
            
            $txn = $transactionModel->find($transactionId);
            if (!$txn || $txn['tenant_id'] != $tenantId) {
                throw new Exception("Transaction not found");
            }
            
            $studentId = $txn['student_id'];
            $receiptNo = $txn['receipt_number'];
            
            // Fetch Student Details
            $stmt = $db->prepare("
                SELECT s.full_name as student_name, COALESCE(NULLIF(s.email, ''), u.email) as email
                FROM students s 
                LEFT JOIN users u ON s.user_id = u.id
                WHERE s.id = :sid AND s.tenant_id = :tid
            ");
            $stmt->execute(['sid' => $studentId, 'tid' => $tenantId]);
            $student = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (empty($student['email'])) {
                throw new Exception("Student does not have an email address on file.");
            }
            
            $pdfPath = getReceiptPdfPath($db, $tenantId, $transactionId);
            
            $emailSent = false;
            if ($pdfPath && file_exists($pdfPath) && method_exists('\App\Helpers\MailHelper', 'sendPaymentReceiptEmail')) {
                $emailSent = \App\Helpers\MailHelper::sendPaymentReceiptEmail(
                    $db,
                    $tenantId,
                    $student['email'],
                    $student['student_name'],
                    $receiptNo,
                    $pdfPath,
                    null,
                    $txn['amount']
                );
            }
            
            if ($emailSent) {
                echo json_encode(['success' => true, 'message' => 'Email sent successfully to ' . $student['email']]);
            } else {
                // Even if PDF failed, try sending email without attachment using the HTML receipt
                try {
                    $emailUrl = APP_URL . '/api/admin/fees?action=generate_receipt_html&is_email=1&transaction_id=' . $transactionId;
                    
                    $ch = curl_init($emailUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    $message = curl_exec($ch);
                    curl_close($ch);

                    if (empty($message) || strpos($message, 'Error') !== false) {
                        $message = "<div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                            <h2>Hello " . htmlspecialchars($student['student_name']) . ",</h2>
                            <p>Thank you for your payment. Your transaction has been recorded successfully.</p>
                            <ul>
                                <li><strong>Receipt No:</strong> " . $receiptNo . "</li>
                            </ul>
                            <p>You can view and download your full receipt from your dashboard.</p>
                        </div>";
                    }

                    $subject = "Payment Receipt Confirmation - " . $receiptNo;
                    $success = \App\Helpers\MailHelper::send($db, $tenantId, $student['email'], $student['student_name'], $subject, $message);
                    
                    if ($success) {
                        echo json_encode(['success' => true, 'message' => 'HTML Receipt sent successfully to ' . $student['email']]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to send email. Check MailHelper configuration or student email.']);
                    }
                } catch (\Exception $e) {
                    error_log("Receipt email fallback failed: " . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Failed to send email. Check MailHelper configuration or student email.']);
                }
            }
            exit;
        }
        else if ($action === 'generate_fees_on_enroll') {
            $studentId = $input['student_id'] ?? null;
            $batchId = $input['batch_id'] ?? null;
            $courseId = $input['course_id'] ?? null;

            if (!$studentId || !$batchId || !$courseId) {
                throw new Exception("Student, Batch and Course IDs are required");
            }

            $calculationService->generateFeesForEnrollment($studentId, $batchId, $courseId, $tenantId);

            echo json_encode([
                'success' => true,
                'message' => 'Fee records generated successfully'
            ]);
        }
        // Moved GET endpoints to the $method === 'GET' block above
        else if ($action === 'edit_payment') {
            $transactionId = $input['transaction_id'] ?? null;
            $amountPaid = floatval($input['amount_paid'] ?? 0);
            $paidDate = $input['paid_date'] ?? date('Y-m-d');
            $paymentMode = $input['payment_mode'] ?? 'cash';
            $notes = $input['notes'] ?? null;
            $resendEmail = !empty($input['resend_email']);

            if (!$transactionId) throw new Exception("Transaction ID required");
            if ($amountPaid <= 0) throw new Exception("Amount must be greater than 0");

            $txn = $transactionModel->find($transactionId);
            if (!$txn || $txn['tenant_id'] != $tenantId) throw new Exception("Transaction not found");

            $feeRecordId = $txn['fee_record_id'];
            $feeRecord = $feeRecordModel->find($feeRecordId);

            $receiptPath = $txn['receipt_path'];
            // Removing image upload from edit_payment per user request

            $amountDiff = $amountPaid - floatval($txn['amount']);
            $newAmountPaidTotal = floatval($feeRecord['amount_paid']) + $amountDiff;
            $totalAmountDue = floatval($feeRecord['amount_due']) + floatval($feeRecord['fine_applied']);
            
            $isOverdue = (strtotime($feeRecord['due_date']) < time());
            $status = ($newAmountPaidTotal >= $totalAmountDue) ? 'paid' : ($newAmountPaidTotal > 0 ? 'partial' : ($isOverdue ? 'overdue' : 'pending'));

            $stmt = $db->prepare("UPDATE fee_records SET amount_paid = amount_paid + :diff, status = :status WHERE id = :fid");
            $stmt->execute(['diff' => $amountDiff, 'status' => $status, 'fid' => $feeRecordId]);

            $stmt = $db->prepare("UPDATE payment_transactions SET amount = :amt, payment_date = :pdate, payment_method = :pmode, receipt_path = :rpath, notes = :notes WHERE id = :tid");
            $stmt->execute(['amt' => $amountPaid, 'pdate' => $paidDate, 'pmode' => $paymentMode, 'rpath' => $receiptPath, 'notes' => $notes, 'tid' => $transactionId]);

            $stmt = $db->prepare("SELECT s.full_name as student_name, s.email, c.name as course_name, b.name as batch_name FROM students s LEFT JOIN batches b ON s.batch_id = b.id LEFT JOIN courses c ON b.course_id = c.id WHERE s.id = :sid AND s.tenant_id = :tid");
            $stmt->execute(['sid' => $txn['student_id'], 'tid' => $tenantId]);
            $student = $stmt->fetch(\PDO::FETCH_ASSOC);

            $stmt = $db->prepare("SELECT name FROM tenants WHERE id = :tid");
            $stmt->execute(['tid' => $tenantId]);
            $institute = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Regenerate PDF from HTML template
            $pdfDir = __DIR__ . '/../../../../public/uploads/receipts/';
            if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);
            $pdfPath = $pdfDir . 'pdf_' . $txn['receipt_number'] . '.pdf';
            
            $receiptUrl = APP_URL . '/api/admin/fees?action=generate_receipt_html&transaction_id=' . $transactionId;
            $scriptPath = base_path('scripts/generate_receipt.py');
            if (file_exists($scriptPath)) {
                $cmd = "python " . escapeshellarg($scriptPath) . " " . escapeshellarg($receiptUrl) . " " . escapeshellarg($pdfPath) . " 2>&1";
                shell_exec($cmd);
            }

            $emailSent = false;
            if ($resendEmail && !empty($student['email']) && file_exists($pdfPath)) {
                $imageAttachment = $receiptPath ? realpath(__DIR__ . '/../../../../public/' . $receiptPath) : null;
                if (method_exists('\App\Helpers\MailHelper', 'sendPaymentReceiptEmail')) {
                    $emailSent = \App\Helpers\MailHelper::sendPaymentReceiptEmail(
                        $db, $tenantId, $student['email'], $student['student_name'], 
                        $txn['receipt_number'], $pdfPath, $imageAttachment, $amountPaid
                    );
                }
            }

            echo json_encode(['success' => true, 'message' => 'Payment updated successfully' . ($emailSent ? ' and Email Resent' : '')]);
        }
        else if ($action === 'delete_payment') {
            $transactionId = $input['transaction_id'] ?? null;
            if (!$transactionId) throw new Exception("Transaction ID required");

            $txn = $transactionModel->find($transactionId);
            if (!$txn || $txn['tenant_id'] != $tenantId) throw new Exception("Transaction not found");

            $feeRecordId = $txn['fee_record_id'];
            $feeRecord = $feeRecordModel->find($feeRecordId);
            
            $amountDiff = -floatval($txn['amount']);
            $newAmountPaidTotal = floatval($feeRecord['amount_paid']) + $amountDiff;
            $totalAmountDue = floatval($feeRecord['amount_due']) + floatval($feeRecord['fine_applied']);
            $isOverdue = (strtotime($feeRecord['due_date']) < time());
            $status = ($newAmountPaidTotal >= $totalAmountDue) ? 'paid' : ($newAmountPaidTotal > 0 ? 'partial' : ($isOverdue ? 'overdue' : 'pending'));
            
            $stmt = $db->prepare("UPDATE fee_records SET amount_paid = amount_paid + :diff, status = :status WHERE id = :fid");
            $stmt->execute(['diff' => $amountDiff, 'status' => $status, 'fid' => $feeRecordId]);

            $stmt = $db->prepare("DELETE FROM payment_transactions WHERE id = :tid");
            $stmt->execute(['tid' => $transactionId]);

            echo json_encode(['success' => true, 'message' => 'Payment deleted and ledger reverted']);
        }
        else {
            throw new Exception("Invalid action: " . $action);
        }
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
