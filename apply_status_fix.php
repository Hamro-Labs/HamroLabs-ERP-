<?php
define('LARAVEL_START', microtime(true));
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement("ALTER TABLE fee_records MODIFY COLUMN status ENUM('pending', 'paid', 'partial', 'overdue', 'cancelled') DEFAULT 'pending'");
    echo "Successfully updated fee_records status enum.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
