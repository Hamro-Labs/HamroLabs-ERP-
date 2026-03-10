<?php
require_once 'config/config.php';
$db = getDBConnection();
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo implode("\n", $tables);
