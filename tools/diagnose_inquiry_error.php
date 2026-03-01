<?php
/**
 * Inquiry List Error Diagnostic Tool
 * Hamro ERP - Quick troubleshooting script for front desk operators
 * 
 * Usage: Navigate to http://your-server/erp/tools/diagnose_inquiry_error.php
 * Or run via CLI: php diagnose_inquiry_error.php
 */

// Prevent production exposure
$isCli = php_sapi_name() === 'cli';
if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Inquiry List Diagnostics</title>';
    echo '<style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 900px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #00B894; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; font-size: 1.2em; }
        .status { padding: 10px 15px; border-radius: 4px; margin: 10px 0; }
        .status.ok { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status.warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .status.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .status.info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 13px; }
        .code { font-family: "Consolas", "Monaco", monospace; background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; }
        .action { background: #00B894; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .action:hover { background: #00a383; }
        .section { margin: 30px 0; }
    </style></head><body><div class="container">';
    echo '<h1>🔧 Inquiry List Error Diagnostic Tool</h1>';
    echo '<p>This tool checks all components related to the inquiry list loading functionality.</p>';
}

$results = [];
$errors = [];
$warnings = [];

function output($message, $type = 'info') {
    global $isCli;
    if ($isCli) {
        $icons = ['ok' => '✓', 'error' => '✗', 'warning' => '⚠', 'info' => 'ℹ'];
        echo "{$icons[$type]} {$message}\n";
    } else {
        echo "<div class=\"status {$type}\">{$message}</div>";
    }
}

function section($title) {
    global $isCli;
    if ($isCli) {
        echo "\n=== {$title} ===\n";
    } else {
        echo "<div class=\"section\"><h2>{$title}</h2>";
    }
}

function endSection() {
    global $isCli;
    if (!$isCli) {
        echo "</div>";
    }
}

// ============================================
// TEST 1: Configuration File Check
// ============================================
section('1. Configuration File Check');

$configPaths = [
    __DIR__ . '/../config/config.php',
    __DIR__ . '/../config/database.php',
    __DIR__ . '/../app/Http/Controllers/Admin/inquiries.php'
];

foreach ($configPaths as $path) {
    if (file_exists($path)) {
        output("Found: " . basename($path), 'ok');
    } else {
        output("Missing: {$path}", 'error');
        $errors[] = "Missing file: {$path}";
    }
}

// Try to load config
$configFile = __DIR__ . '/../config/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
    output("Configuration loaded successfully", 'ok');
    
    if (defined('APP_ENV')) {
        output("APP_ENV: " . APP_ENV, 'info');
    }
    if (defined('DB_HOST')) {
        output("Database Host: " . DB_HOST, 'info');
    }
} else {
    output("Cannot load configuration file", 'error');
    $errors[] = "Configuration file not found";
}
endSection();

// ============================================
// TEST 2: Database Connection
// ============================================
section('2. Database Connectivity');

if (function_exists('getDBConnection')) {
    try {
        $db = getDBConnection();
        output("Database connection established", 'ok');
        
        // Test basic query
        $stmt = $db->query("SELECT 1 as test");
        $result = $stmt->fetch();
        if ($result && $result['test'] == 1) {
            output("Basic query executed successfully", 'ok');
        }
        
        // Check inquiries table
        $stmt = $db->query("SHOW TABLES LIKE 'inquiries'");
        if ($stmt->rowCount() > 0) {
            output("'inquiries' table exists", 'ok');
            
            // Check record count
            $stmt = $db->query("SELECT COUNT(*) as cnt FROM inquiries");
            $count = $stmt->fetch()['cnt'];
            output("Total inquiries in database: {$count}", 'info');
            
            // Check for corrupted records
            $stmt = $db->query("SELECT COUNT(*) as cnt FROM inquiries WHERE tenant_id IS NULL");
            $orphaned = $stmt->fetch()['cnt'];
            if ($orphaned > 0) {
                output("Warning: {$orphaned} inquiries missing tenant_id", 'warning');
                $warnings[] = "{$orphaned} orphaned inquiry records";
            } else {
                output("No orphaned inquiry records found", 'ok');
            }
            
            // Check table structure
            $stmt = $db->query("DESCRIBE inquiries");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $requiredColumns = ['id', 'tenant_id', 'full_name', 'phone', 'status', 'created_at'];
            $missingColumns = array_diff($requiredColumns, $columns);
            if (empty($missingColumns)) {
                output("All required columns present", 'ok');
            } else {
                output("Missing columns: " . implode(', ', $missingColumns), 'error');
                $errors[] = "Missing table columns: " . implode(', ', $missingColumns);
            }
            
        } else {
            output("'inquiries' table does not exist!", 'error');
            $errors[] = "Inquiries table missing - run migrations";
        }
        
        // Check courses table (for JOIN)
        $stmt = $db->query("SHOW TABLES LIKE 'courses'");
        if ($stmt->rowCount() > 0) {
            output("'courses' table exists (for JOIN operations)", 'ok');
        } else {
            output("Warning: 'courses' table not found (JOIN may fail)", 'warning');
            $warnings[] = "Courses table missing";
        }
        
    } catch (PDOException $e) {
        output("Database error: " . $e->getMessage(), 'error');
        $errors[] = "Database connection failed: " . $e->getMessage();
    } catch (Exception $e) {
        output("Error: " . $e->getMessage(), 'error');
        $errors[] = $e->getMessage();
    }
} else {
    output("getDBConnection() function not available", 'error');
    $errors[] = "Database helper function missing";
}
endSection();

// ============================================
// TEST 3: Session & Authentication
// ============================================
section('3. Session & Authentication');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

output("Session ID: " . session_id(), 'info');
output("Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive'), 
    session_status() === PHP_SESSION_ACTIVE ? 'ok' : 'warning');

if (isset($_SESSION['userData'])) {
    output("User session data exists", 'ok');
    $userData = $_SESSION['userData'];
    
    if (isset($userData['id'])) {
        output("User ID: {$userData['id']}", 'info');
    }
    if (isset($userData['role'])) {
        output("User Role: {$userData['role']}", 'info');
        $allowedRoles = ['instituteadmin', 'frontdesk', 'superadmin'];
        if (in_array($userData['role'], $allowedRoles)) {
            output("Role has inquiry access permission", 'ok');
        } else {
            output("Role '{$userData['role']}' may not have inquiry access", 'warning');
            $warnings[] = "User role may not have permission";
        }
    }
    if (isset($userData['tenant_id'])) {
        output("Tenant ID: {$userData['tenant_id']}", 'info');
    } else {
        output("Warning: Tenant ID missing from session", 'warning');
        $warnings[] = "Missing tenant_id in session";
    }
} else {
    output("No user session data found (user not logged in)", 'warning');
    $warnings[] = "User not authenticated";
}

if (function_exists('isLoggedIn')) {
    $loggedIn = isLoggedIn();
    output("isLoggedIn(): " . ($loggedIn ? 'true' : 'false'), 
        $loggedIn ? 'ok' : 'warning');
} else {
    output("isLoggedIn() function not available", 'error');
}
endSection();

// ============================================
// TEST 4: API Endpoint Accessibility
// ============================================
section('4. API Endpoint Check');

if (isset($_SERVER['HTTP_HOST'])) {
    $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
    $baseUrl = "{$protocol}://{$_SERVER['HTTP_HOST']}";
    $baseUrl .= dirname($_SERVER['REQUEST_URI'], 2); // Go up 2 levels from /tools/
    
    output("Base URL detected: {$baseUrl}", 'info');
    
    // Check if we can make HTTP requests
    if (function_exists('curl_init')) {
        output("cURL extension available", 'ok');
        
        // Test inquiries endpoint (may fail without session, but checks routing)
        $ch = curl_init("{$baseUrl}/api/admin/inquiries");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        // Add current session cookie if available
        if (isset($_COOKIE[session_name()])) {
            curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . $_COOKIE[session_name()]);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        output("API HTTP Response Code: {$httpCode}", 
            $httpCode == 200 ? 'ok' : ($httpCode == 401 ? 'warning' : 'error'));
        
        if ($httpCode == 0) {
            output("Cannot connect to API endpoint", 'error');
            $errors[] = "API endpoint unreachable";
        } elseif ($httpCode == 401) {
            output("API requires authentication (expected if not logged in)", 'warning');
        } elseif ($httpCode == 404) {
            output("API endpoint not found - check routing", 'error');
            $errors[] = "API route not configured";
        } elseif ($httpCode == 500) {
            output("Server error - check application logs", 'error');
            $errors[] = "Server-side error in API";
        }
        
        // Try to parse JSON response
        if ($response) {
            $json = json_decode($response, true);
            if ($json !== null) {
                output("API returns valid JSON", 'ok');
                if (isset($json['success'])) {
                    output("API response structure valid", 'ok');
                }
            } else {
                output("API does not return valid JSON", 'error');
                $errors[] = "Invalid JSON response from API";
            }
        }
        
    } else {
        output("cURL extension not available", 'warning');
        $warnings[] = "cURL not installed - cannot test HTTP endpoints";
    }
} else {
    output("Running in CLI mode - skipping HTTP endpoint tests", 'info');
}
endSection();

// ============================================
// TEST 5: File Permissions
// ============================================
section('5. File System Permissions');

$cacheDir = sys_get_temp_dir();
output("System temp directory: {$cacheDir}", 'info');

if (is_writable($cacheDir)) {
    output("Cache directory is writable", 'ok');
} else {
    output("Cache directory not writable", 'error');
    $errors[] = "Cannot write to cache directory";
}

$uploadsDir = __DIR__ . '/../public/uploads';
if (is_dir($uploadsDir)) {
    if (is_writable($uploadsDir)) {
        output("Uploads directory is writable", 'ok');
    } else {
        output("Uploads directory not writable", 'warning');
        $warnings[] = "Uploads directory not writable";
    }
} else {
    output("Uploads directory not found", 'warning');
}

$logDir = __DIR__ . '/../storage/logs';
if (is_dir($logDir)) {
    if (is_writable($logDir)) {
        output("Log directory is writable", 'ok');
    } else {
        output("Log directory not writable", 'warning');
    }
}
endSection();

// ============================================
// TEST 6: PHP Configuration
// ============================================
section('6. PHP Configuration');

output("PHP Version: " . phpversion(), version_compare(phpversion(), '7.4.0', '>=') ? 'ok' : 'warning');

$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'session'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        output("Extension '{$ext}' loaded", 'ok');
    } else {
        output("Extension '{$ext}' NOT loaded", 'error');
        $errors[] = "Missing PHP extension: {$ext}";
    }
}

output("Memory Limit: " . ini_get('memory_limit'), 'info');
output("Max Execution Time: " . ini_get('max_execution_time') . "s", 'info');
output("Post Max Size: " . ini_get('post_max_size'), 'info');

if ((int)ini_get('max_execution_time') < 30) {
    output("Warning: max_execution_time may be too low for long queries", 'warning');
    $warnings[] = "Low execution time limit";
}
endSection();

// ============================================
// TEST 7: Performance Check
// ============================================
section('7. Performance Test');

if (function_exists('getDBConnection')) {
    try {
        $db = getDBConnection();
        $startTime = microtime(true);
        
        // Run the actual inquiry query
        $stmt = $db->prepare("
            SELECT i.id, i.full_name, i.phone, i.email, i.source, i.status, 
                   i.created_at, i.updated_at, c.name as course_name 
            FROM inquiries i 
            LEFT JOIN courses c ON i.course_id = c.id
            WHERE i.tenant_id = :tid AND i.deleted_at IS NULL
            ORDER BY i.created_at DESC LIMIT 500
        ");
        
        // Use tenant_id 1 for testing, or skip if not available
        $testTenantId = isset($_SESSION['userData']['tenant_id']) ? $_SESSION['userData']['tenant_id'] : 1;
        $stmt->execute(['tid' => $testTenantId]);
        $results = $stmt->fetchAll();
        
        $duration = (microtime(true) - $startTime) * 1000;
        
        if ($duration < 1000) {
            output("Query executed in " . round($duration, 2) . "ms", 'ok');
        } elseif ($duration < 3000) {
            output("Query executed in " . round($duration, 2) . "ms (acceptable)", 'warning');
        } else {
            output("Query slow: " . round($duration, 2) . "ms (consider optimization)", 'error');
            $warnings[] = "Slow query performance";
        }
        
        output("Records returned: " . count($results), 'info');
        
    } catch (Exception $e) {
        output("Performance test error: " . $e->getMessage(), 'error');
    }
}
endSection();

// ============================================
// SUMMARY
// ============================================
section('Summary');

if (empty($errors) && empty($warnings)) {
    output("✅ All checks passed! The inquiry list should be working correctly.", 'ok');
} elseif (empty($errors)) {
    output("✅ No critical errors found, but there are warnings to address.", 'warning');
    foreach ($warnings as $warning) {
        output("⚠ {$warning}", 'warning');
    }
} else {
    output("❌ Critical errors found that need immediate attention:", 'error');
    foreach ($errors as $error) {
        output("✗ {$error}", 'error');
    }
    if (!empty($warnings)) {
        output("Additional warnings:", 'warning');
        foreach ($warnings as $warning) {
            output("⚠ {$warning}", 'warning');
        }
    }
}

// Quick fixes section
if (!$isCli) {
    echo '<h2>Quick Actions</h2>';
    echo '<button class="action" onclick="location.reload()">🔄 Run Diagnostics Again</button>';
    echo '<button class="action" onclick="window.open(\'../debug_vars.php\', \'_blank\')">📊 View Debug Info</button>';
    
    if (!empty($errors)) {
        echo '<h2>Recommended Fixes</h2><ul>';
        if (in_array("Missing file: " . __DIR__ . "/../config/config.php", $errors)) {
            echo '<li>Ensure configuration files exist in config/ directory</li>';
        }
        if (in_array("Inquiries table missing - run migrations", $errors)) {
            echo '<li>Run database migrations from database/migrations/ directory</li>';
        }
        if (in_array("Database connection failed", $errors)) {
            echo '<li>Check MySQL service is running and credentials in config/config.php</li>';
        }
        echo '</ul>';
    }
}

endSection();

if (!$isCli) {
    echo '</div></body></html>';
}

// CLI summary
if ($isCli) {
    echo "\n================================\n";
    echo "Errors: " . count($errors) . " | Warnings: " . count($warnings) . "\n";
    if (count($errors) > 0) {
        echo "Status: FAILED\n";
        exit(1);
    } elseif (count($warnings) > 0) {
        echo "Status: WARNING\n";
        exit(0);
    } else {
        echo "Status: OK\n";
        exit(0);
    }
}
