<?php
require_once __DIR__ . '/config/config.php';
$db = getDBConnection();
$stmt = $db->query("SELECT id, roll_no, full_name FROM students ORDER BY id ASC LIMIT 10");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($students);
