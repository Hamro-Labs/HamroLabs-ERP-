<?php
require_once 'config/config.php';
require_once 'app/Helpers/StatsHelper.php';

try {
    $stats = \App\Helpers\StatsHelper::getSuperAdminStats();
    if ($stats === null) {
        echo "StatsHelper returned null. Check error logs.\n";
    } else {
        echo "StatsHelper Success!\n";
        print_r($stats);
    }
} catch (Exception $e) {
    echo "Caught exception: " . $e->getMessage() . "\n";
}
