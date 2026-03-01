<?php
require_once __DIR__ . '/config/config.php';
$db = getDBConnection();

// Check schema
$stmt = $db->query("SHOW COLUMNS FROM students");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

$required = ['registration_mode', 'registration_status', 'admission_date', 'dob_ad', 'dob_bs', 'gender'];
$found = [];

foreach ($cols as $col) {
    $name = $col['Field'];
    $null = $col['Null'];
    if (in_array($name, $required)) {
        $status = ($null === 'YES') ? 'NULL=YES ✓' : 'NULL=NO';
        echo "  $name => $status (Type: {$col['Type']})\n";
        $found[] = $name;
    }
}

$missing = array_diff($required, $found);
if ($missing) {
    echo "\nMISSING: " . implode(', ', $missing) . "\n";
} else {
    echo "\nAll required columns found!\n";
}

// Count students
$stmt = $db->prepare("SELECT COUNT(*) FROM students WHERE deleted_at IS NULL");
$stmt->execute();
echo "Total students: " . $stmt->fetchColumn() . "\n";
