<?php
require_once 'c:/Apache24/htdocs/erp/config/config.php';
$db = getDBConnection();
$stmt = $db->query('SHOW TABLES');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo json_encode($tables, JSON_PRETTY_PRINT);
