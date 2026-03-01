<?php
require 'config/config.php';
$_base = __DIR__ . '/app/Helpers/';
require_once $_base . 'MailHelper.php';

$host = 'localhost';
$db   = 'hamrolabs_db';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass);

echo "--- Database Tables ---\n";
$stmt = $pdo->query('SHOW TABLES');
var_dump($stmt->fetchAll(PDO::FETCH_COLUMN));

echo "\n--- Recent Students without Emails ---\n";
$stmt = $pdo->query('SELECT id, full_name, email, tenant_id FROM students WHERE (email IS NULL OR email = \'\') ORDER BY id DESC LIMIT 5');
var_dump($stmt->fetchAll(PDO::FETCH_ASSOC));
