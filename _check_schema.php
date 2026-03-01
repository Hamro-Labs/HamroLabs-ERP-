<?php
$h = '127.0.0.1';
$d = 'hamrolabs_db';
$u = 'root';
$p = '';
try {
    $pdo = new PDO("mysql:host=$h;dbname=$d;charset=utf8mb4", $u, $p);
    $s = $pdo->query("DESCRIBE inquiries");
    file_put_contents('_schema_results.json', json_encode($s->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT));
} catch (Exception $e) {
    echo $e->getMessage();
}
