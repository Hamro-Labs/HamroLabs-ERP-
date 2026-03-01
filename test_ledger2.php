<?php
require 'config/config.php';
session_start();
$_SESSION['userData'] = ['id' => 1, 'tenant_id' => 5];

require 'vendor/autoload.php'; // If composer is there
require 'app/Models/FeeRecord.php';

try {
    $m = new \App\Models\FeeRecord();
    print_r($m->getByStudent(33, 5));
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
