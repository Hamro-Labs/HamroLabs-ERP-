<?php
// Bridge script to run SQL file via PDO
$config = require 'c:/Apache24/htdocs/erp/config/config.php';

try {
    $dsn = "mysql:host=localhost;dbname=hamrolabs_db;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $sql = file_get_contents('c:/Apache24/htdocs/erp/database/fix_missing_tables.sql');
    $pdo->exec($sql);
    echo "SQL executed successfully.\n";
    
    // Verify tables
    $tables = ['refresh_tokens', 'otp_codes'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->fetch()) {
            echo "Table '$table' exists.\n";
        } else {
            echo "Table '$table' MISSING!\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
