<?php
require __DIR__ . '/config/config.php';
$db = getDBConnection();
$stmt = $db->query("SHOW COLUMNS FROM fee_records LIKE 'status'");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
