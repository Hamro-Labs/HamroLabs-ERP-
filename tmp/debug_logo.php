<?php
require 'c:/Apache24/htdocs/erp/config/config.php';
$db = getDBConnection();
$stmt = $db->query('SELECT id, name, logo_path FROM tenants');
$tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "--- TENANTS ---\n";
foreach ($tenants as $t) {
    echo "ID: {$t['id']}, Name: {$t['name']}, Logo Path: '{$t['logo_path']}'\n";
}

echo "\n--- TABLES ---\n";
$stmt = $db->query('SHOW TABLES');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    echo "$table\n";
}

// Simulate ReceiptHelper logic for the first tenant
if (!empty($tenants)) {
    $logoPath = $tenants[0]['logo_path'];
    $originalPath = $logoPath;
    if ($logoPath) {
        if (strpos($logoPath, '/uploads/') === 0 && strpos($logoPath, '/public/') !== 0) {
            $logoPath = '/public' . $logoPath;
        }
        $logoUrl = (defined('APP_URL') ? APP_URL : '') . $logoPath;
        echo "\n--- LOGO URL SIMULATION ---\n";
        echo "Original: $originalPath\n";
        echo "After logic: $logoPath\n";
        echo "Final URL: $logoUrl\n";
    }
}
