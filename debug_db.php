<?php
require_once 'c:/Apache24/htdocs/erp/config/config.php';
$db = getDBConnection();

$tables = ['users', 'students', 'staff'];

foreach ($tables as $table) {
    echo "--- Table: $table ---\n";
    try {
        $stmt = $db->query("DESCRIBE $table");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Field']} ({$row['Type']})\n";
        }
        
        // Also show sample data to understand roles
        echo "\nSample data (first 3 rows):\n";
        $data = $db->query("SELECT * FROM $table LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
        print_r($data);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
