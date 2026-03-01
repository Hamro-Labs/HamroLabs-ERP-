<?php

require __DIR__ . '/config/database.php';
require __DIR__ . '/app/Helpers/MailHelper.php';

$host = '127.0.0.1';
$db   = 'hamrolabs_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);

$res = \App\Helpers\MailHelper::sendStudentCredentials($pdo, 5, [
    'full_name' => 'John Doe',
    'email' => 'test@example.com',
    'plain_password' => 'secret123',
    'course_name' => 'Testing JS',
    'batch_name' => 'Batch 1',
    'roll_no' => 'RL101',
    'admission_date' => '2023-10-10'
]);

var_dump($res);
