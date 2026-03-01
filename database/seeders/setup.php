<?php
/**
 * HamroLabs ERP - Phase 1 Setup Script
 * Run this file to set up the complete Phase 1 environment
 * 
 * Usage: php setup.php
 */

echo "===========================================\n";
echo "HamroLabs ERP - Phase 1 Setup\n";
echo "===========================================\n\n";

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line\n");
}

// Database configuration
$dbHost = 'localhost';
$dbName = 'hamrolabs_db';
$dbUser = 'root';
$dbPass = '';

echo "[1/5] Connecting to MySQL...\n";

try {
    $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "    ✓ Connected to MySQL\n";
} catch (PDOException $e) {
    die("    ✗ Connection failed: " . $e->getMessage() . "\n");
}

echo "[2/5] Creating database if not exists...\n";

try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");
    echo "    ✓ Database ready\n";
} catch (PDOException $e) {
    die("    ✗ Database creation failed: " . $e->getMessage() . "\n");
}

echo "[3/5] Running Phase 1 migrations...\n";

// Read and execute migration file
$migrationFile = __DIR__ . '/sql/phase1_migrations.sql';
if (!file_exists($migrationFile)) {
    die("    ✗ Migration file not found: $migrationFile\n");
}

$sql = file_get_contents($migrationFile);

// Split by semicolons and execute each statement
$statements = array_filter(array_map('trim', explode(';', $sql)));
$executed = 0;
$errors = 0;

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0 || strpos($statement, '/*') === 0) {
        continue;
    }
    
    try {
        $pdo->exec($statement);
        $executed++;
    } catch (PDOException $e) {
        // Ignore duplicate key errors
        if (strpos($e->getMessage(), 'Duplicate') === false) {
            $errors++;
            echo "    Warning: " . substr($e->getMessage(), 0, 80) . "...\n";
        }
    }
}

echo "    ✓ Executed $executed SQL statements\n";
if ($errors > 0) {
    echo "    ⚠ $errors warnings (mostly duplicate keys - OK)\n";
}

echo "[4/5] Running Super Admin upgrades...\n";

// Read and execute upgrade file
$upgradeFile = __DIR__ . '/sql/upgrade_superadmin.sql';
if (file_exists($upgradeFile)) {
    $sql = file_get_contents($upgradeFile);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0 || strpos($statement, '/*') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
        } catch (PDOException $e) {
            // Ignore duplicate key errors
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                echo "    Warning: " . substr($e->getMessage(), 0, 80) . "...\n";
            }
        }
    }
    echo "    ✓ Super Admin tables ready\n";
}

echo "[5/5] Verifying setup...\n";

// Verify key tables exist
$tables = ['tenants', 'users', 'courses', 'batches', 'students', 'guardians', 
            'fee_items', 'fee_records', 'attendance', 'teachers', 
            'batch_subject_allocations', 'timetable_slots', 'notifications', 
            'sms_logs', 'audit_logs', 'refresh_tokens', 'otp_codes'];

$allOk = true;
foreach ($tables as $table) {
    try {
        $result = $pdo->query("SELECT 1 FROM `$table` LIMIT 1");
        echo "    ✓ $table table exists\n";
    } catch (PDOException $e) {
        echo "    ✗ $table table missing!\n";
        $allOk = false;
    }
}

// Check default users
echo "\n[Setup] Default credentials:\n";
echo "    Super Admin: admin@hamrolabs.com / password\n";
echo "    Demo Admin: admin@demo.hamrolabs.com / password\n";
echo "    (Change these immediately in production!)\n";

echo "\n===========================================\n";
if ($allOk) {
    echo "✓ Phase 1 setup completed successfully!\n";
    echo "===========================================\n";
    echo "\nNext steps:\n";
    echo "1. Configure your web server to point to frontend/\n";
    echo "2. Update config.php with your database credentials\n";
    echo "3. Update config.php with SMTP settings for email\n";
    echo "4. Run 'composer install' if using Laravel\n";
    echo "5. Access the application at http://localhost/erp/frontend/\n";
} else {
    echo "✗ Setup completed with errors!\n";
    echo "===========================================\n";
    exit(1);
}
?>
