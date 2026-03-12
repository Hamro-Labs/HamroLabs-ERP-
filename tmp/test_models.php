<?php
// Verification script for FeeSettings/AttendanceSettings models
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
$app->make(Kernel::class)->bootstrap();

use App\Models\FeeSettings;
use App\Models\AttendanceSettings;

try {
    $tenantId = 1; // Assuming tenant 1 exists
    
    echo "Testing FeeSettings::getByTenant($tenantId)...\n";
    $fee = FeeSettings::getByTenant($tenantId);
    if ($fee) {
        echo "SUCCESS: FeeSettings found for tenant $tenantId.\n";
    } else {
        echo "NOTE: No FeeSettings record found for tenant $tenantId (this is fine if DB is empty).\n";
    }
    
    echo "Testing AttendanceSettings::getByTenant($tenantId)...\n";
    $attendance = AttendanceSettings::getByTenant($tenantId);
    if ($attendance) {
        echo "SUCCESS: AttendanceSettings found for tenant $tenantId.\n";
    } else {
        echo "NOTE: No AttendanceSettings record found for tenant $tenantId.\n";
    }

    // Double check if method exists via reflection to be 100% sure
    $refFee = new ReflectionMethod(FeeSettings::class, 'getByTenant');
    echo "Method FeeSettings::getByTenant exists: " . ($refFee ? 'Yes' : 'No') . "\n";
    
    $refAtt = new ReflectionMethod(AttendanceSettings::class, 'getByTenant');
    echo "Method AttendanceSettings::getByTenant exists: " . ($refAtt ? 'Yes' : 'No') . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
