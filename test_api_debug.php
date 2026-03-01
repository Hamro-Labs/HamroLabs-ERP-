<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['action'] = 'get_student_ledger';
$_GET['student_id'] = 1;

// Mock session and auth
session_start();
$_SESSION['userData'] = [
    'id' => 1,
    'tenant_id' => 5,
    'role' => 'instituteadmin'
];

require __DIR__.'/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

ob_start();
try {
    require_once __DIR__ . '/app/Http/Controllers/Admin/fees.php';
    echo "\nJSON ERROR: " . json_last_error_msg() . "\n";
} catch (\Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
$output = ob_get_clean();

echo "--- RAW OUTPUT ---\n";
echo $output;
echo "\n--- RAW OUTPUT END ---\n";
