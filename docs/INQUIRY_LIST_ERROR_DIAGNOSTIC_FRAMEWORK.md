# Diagnostic Framework: "Failed to Load Inquiry List" Error
## Hamro ERP - Front Desk Operations

---

## Executive Summary

This document provides a systematic diagnostic framework for troubleshooting the "Failed to load inquiry list" error encountered by front desk operators. The framework covers six primary failure domains and provides actionable troubleshooting steps with immediate resolution and prevention strategies.

**Error Manifestation:**
```javascript
// From ia-inquiries.js line 45
} catch(e) { 
    c.innerHTML=`<div style="padding:20px;color:var(--red);text-align:center">${e.message}</div>`; 
}
```

---

## 1. Diagnostic Flowchart

```
START: User reports "Failed to load inquiry list"
│
├─► Step 1: Check Browser Console
│   ├─ 404 Error → Routing/Apache Issue (Section 3.1)
│   ├─ 401/403 Error → Authentication Issue (Section 3.2)
│   ├─ 500 Error → Server-side Exception (Section 3.3)
│   ├─ CORS Error → Network/Configuration Issue (Section 3.4)
│   └─ Network Timeout → Latency/Performance Issue (Section 3.5)
│
├─► Step 2: Verify User Session
│   └─ Check $_SESSION['userData'] validity
│
├─► Step 3: Test API Endpoint Directly
│   └─ curl /api/admin/inquiries or /api/frontdesk/inquiries
│
├─► Step 4: Check Database Connectivity
│   └─ Test getDBConnection() and query execution
│
└─► Step 5: Analyze Server Logs
    └─ Apache error.log + Application logs
```

---

## 2. Root Cause Analysis Matrix

| Error Code | Root Cause Category | Frequency | Severity | Detection Method |
|------------|---------------------|-----------|----------|------------------|
| 401 Unauthorized | Session Expiration | High | Medium | Auth middleware logs |
| 403 Forbidden | Permission/Role Mismatch | Medium | Medium | inquiries.php line 30-33 |
| 500 Internal Error | Database/Query Failure | High | Critical | Apache error.log |
| 404 Not Found | Missing API Route | Low | High | Route configuration |
| Timeout | Network/Performance | Medium | Medium | Browser DevTools |
| Empty Response | Data Corruption | Low | High | Database integrity check |

---

## 3. Systematic Troubleshooting Procedures

### 3.1 API Routing & Endpoint Verification

**Affected Files:**
- [`routes/web.php`](routes/web.php:359-361) - Admin inquiry routes
- [`routes/web.php`](routes/web.php:435-437) - Front desk inquiry routes
- [`app/Http/Controllers/Admin/inquiries.php`](app/Http/Controllers/Admin/inquiries.php:1-223)

**Diagnostic Commands:**
```bash
# Verify route accessibility
curl -I http://localhost/erp/api/admin/inquiries
curl -I http://localhost/erp/api/frontdesk/inquiries

# Check Apache mod_rewrite
curl http://localhost/erp/api/admin/inquiries 2>&1 | grep -i "404\|403\|500"
```

**Resolution Steps:**
1. Verify `.htaccess` rewrite rules are active:
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php/$1 [L]
   ```

2. Confirm route registration in [`routes/web.php`](routes/web.php:359):
   ```php
   Route::any('/api/admin/inquiries', function() {
       require_once app_path('Http/Controllers/Admin/inquiries.php');
   });
   ```

3. Check file permissions:
   ```bash
   chmod 644 app/Http/Controllers/Admin/inquiries.php
   chown www-data:www-data app/Http/Controllers/Admin/inquiries.php
   ```

---

### 3.2 Authentication & Session Validation

**Authentication Flow:**
```
ia-inquiries.js (line 23) 
  → fetch(APP_URL + '/api/admin/inquiries')
  → inquiries.php (lines 14-33)
  → isLoggedIn() check
  → Role validation (lines 29-33)
```

**Diagnostic Script:**
```php
<?php
// test_session.php - Place in document root
require 'config/config.php';

header('Content-Type: application/json');

echo json_encode([
    'session_active' => isset($_SESSION['userData']),
    'user_data' => $_SESSION['userData'] ?? null,
    'is_logged_in' => isLoggedIn(),
    'session_id' => session_id(),
    'session_status' => session_status() === PHP_SESSION_ACTIVE ? 'active' : 'inactive',
    'cookie_params' => session_get_cookie_params()
]);
```

**Common Issues & Fixes:**

| Issue | Symptom | Solution |
|-------|---------|----------|
| Session Expired | 401 Unauthorized | Check SESSION_LIFETIME in [`config/config.php`](config/config.php:26) |
| Missing tenant_id | "Tenant ID missing" error | Verify user profile in database |
| Role Mismatch | "Access denied" error | Check user role is in ['instituteadmin', 'frontdesk', 'superadmin'] |
| Session Corruption | Intermittent failures | Clear PHP session files: `rm -rf /var/lib/php/sessions/*` |

**Role Validation Check:**
```php
// From inquiries.php lines 28-33
$allowedRoles = ['instituteadmin', 'frontdesk', 'superadmin'];
if (!in_array($userRole, $allowedRoles)) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Insufficient permissions.']);
    exit;
}
```

---

### 3.3 Database Connection & Query Performance

**Connection Configuration:**
- Primary: [`config/config.php`](config/config.php:8-11)
- Laravel Config: [`config/database.php`](config/database.php:10-21)

**Diagnostic Queries:**
```sql
-- 1. Test database connectivity
SELECT 1;

-- 2. Check inquiries table existence
SHOW TABLES LIKE 'inquiries';

-- 3. Verify table structure
DESCRIBE inquiries;

-- 4. Check for corrupted records
SELECT COUNT(*) FROM inquiries WHERE tenant_id IS NULL;

-- 5. Analyze query performance
EXPLAIN SELECT i.id, i.full_name, i.phone, i.email, i.source, i.status, 
          i.created_at, i.updated_at, c.name as course_name 
          FROM inquiries i 
          LEFT JOIN courses c ON i.course_id = c.id
          WHERE i.tenant_id = 1 AND i.deleted_at IS NULL
          ORDER BY i.created_at DESC LIMIT 500;
```

**Performance Bottlenecks:**

| Bottleneck | Detection | Solution |
|------------|-----------|----------|
| Missing Index | Slow query (>2s) | `CREATE INDEX idx_inquiries_tenant ON inquiries(tenant_id, deleted_at, created_at);` |
| Large Dataset | Memory exhaustion | Implement pagination (see Optimization section) |
| Table Lock | Query timeout | Check for long-running transactions |
| Connection Pool | Too many connections | Tune MySQL max_connections |

**Query Optimization:**
```sql
-- Recommended indexes for inquiries table
CREATE INDEX idx_inquiries_tenant_status ON inquiries(tenant_id, status, deleted_at);
CREATE INDEX idx_inquiries_created ON inquiries(created_at);
CREATE INDEX idx_inquiries_search ON inquiries(full_name, phone, email);
```

---

### 3.4 Server-Side Exception Analysis

**Log Locations:**
- Apache Error Log: `/var/log/apache2/error.log` or `C:\Apache24\logs\error.log`
- PHP Error Log: `/var/log/php_errors.log`
- Application Log: Check `error_log()` calls in [`app/Http/Controllers/Admin/inquiries.php`](app/Http/Controllers/Admin/inquiries.php:221-223)

**Exception Handling:**
```php
// From inquiries.php lines 221-223
try {
    // ... database operations
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
```

**Common Stack Traces:**

```
# PDOException: SQLSTATE[42S02]: Base table or view not found
→ Run migrations: php migrate_email_tables.php

# PDOException: SQLSTATE[HY000] [2002] Connection refused
→ Check MySQL service status
→ Verify DB_HOST in config.php

# Fatal error: Allowed memory size exhausted
→ Increase memory_limit in php.ini
→ Implement query pagination

# Undefined function isLoggedIn()
→ Ensure config.php is loaded: require_once __DIR__ . '/../../../config/config.php';
```

**Debug Mode Activation:**
```php
// Add to inquiries.php for detailed debugging
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
}
```

---

### 3.5 Network Connectivity & Latency

**Network Diagnostic Commands:**
```bash
# Test server connectivity
ping localhost
telnet localhost 80

# Check response time
curl -w "@curl-format.txt" -o /dev/null -s http://localhost/erp/api/admin/inquiries

# curl-format.txt content:
# time_namelookup: %{time_namelookup}
# time_connect: %{time_connect}
# time_total: %{time_total}
```

**Network Issues Matrix:**

| Symptom | Cause | Solution |
|---------|-------|----------|
| Connection Refused | Apache not running | `sudo systemctl start apache2` |
| Timeout (>30s) | Query too slow | Add query timeout, optimize indexes |
| DNS Resolution Failed | Hosts file issue | Check /etc/hosts or Windows hosts |
| SSL Handshake Error | Certificate issue | Update APP_URL to http in development |

**Timeout Configuration:**
```php
// Database timeout
$db->setAttribute(PDO::ATTR_TIMEOUT, 30);

// PHP execution time
set_time_limit(60);

// Apache Timeout directive
Timeout 60
```

---

### 3.6 Cache Layer Integrity

**Cache Implementation:**
From [`app/Http/Controllers/FrontDesk/frontdesk_stats.php`](app/Http/Controllers/FrontDesk/frontdesk_stats.php:44-65)
```php
$cacheKey = "frontdesk_stats_{$tenantId}";
$cacheFile = sys_get_temp_dir() . '/fd_cache_' . md5($cacheKey) . '.json';
$cacheExpiry = 300; // 5 minutes
```

**Cache Diagnostic Commands:**
```bash
# List cache files
ls -la /tmp/fd_cache_*

# Clear cache
rm -f /tmp/fd_cache_*

# Check disk space
df -h /tmp
```

**Cache Corruption Symptoms:**
- Stale data displayed
- Empty response after cache hit
- JSON parse errors in frontend

**Resolution:**
```bash
# Automated cache clearing script
echo "Clearing inquiry cache..."
rm -f /tmp/fd_cache_*inquiry*
rm -f /tmp/fd_cache_*tenant_${TENANT_ID}*
echo "Cache cleared successfully"
```

---

## 4. Frontend-Backend Data Synchronization

### 4.1 Request Flow Analysis

```javascript
// ia-inquiries.js - Line 20-46
async function _loadInquiries() {
    const c = document.getElementById('inquiryListContainer'); 
    if (!c) return;
    try {
        const res = await fetch(APP_URL + '/api/admin/inquiries');  // Line 23
        const result = await res.json();                           // Line 24
        if (!result.success) throw new Error(result.message);
        // ... render logic
    } catch(e) { 
        c.innerHTML=`<div style="padding:20px;color:var(--red);text-align:center">${e.message}</div>`; 
    }
}
```

### 4.2 Data Validation Checklist

| Checkpoint | Expected Value | Validation Method |
|------------|----------------|-------------------|
| APP_URL | http://localhost/erp | Browser console: `console.log(APP_URL)` |
| Response Format | `{success: true, data: [...]}` | Network tab in DevTools |
| Content-Type | application/json | Response headers |
| HTTP Status | 200 OK | Status column in Network tab |

### 4.3 Synchronization Fixes

**Fix 1: Add Loading State Timeout**
```javascript
async function _loadInquiries() {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 10000); // 10s timeout
    
    try {
        const res = await fetch(APP_URL + '/api/admin/inquiries', {
            signal: controller.signal
        });
        clearTimeout(timeoutId);
        // ... rest of logic
    } catch(e) {
        if (e.name === 'AbortError') {
            c.innerHTML = '<div class="error">Request timed out. Please try again.</div>';
        }
    }
}
```

**Fix 2: Add Retry Logic**
```javascript
async function fetchWithRetry(url, options = {}, maxRetries = 3) {
    for (let i = 0; i < maxRetries; i++) {
        try {
            const response = await fetch(url, options);
            if (response.ok) return response;
            throw new Error(`HTTP ${response.status}`);
        } catch (e) {
            if (i === maxRetries - 1) throw e;
            await new Promise(r => setTimeout(r, 1000 * (i + 1)));
        }
    }
}
```

---

## 5. User Profile & Data Corruption

### 5.1 Corrupted User Profile Detection

**Diagnostic Query:**
```sql
-- Check user session data integrity
SELECT u.id, u.email, u.role, u.tenant_id, u.status, t.name as tenant_name
FROM users u
LEFT JOIN tenants t ON u.tenant_id = t.id
WHERE u.id = {USER_ID};

-- Verify tenant exists
SELECT * FROM tenants WHERE id = {TENANT_ID};

-- Check for orphaned inquiries
SELECT COUNT(*) FROM inquiries 
WHERE tenant_id NOT IN (SELECT id FROM tenants);
```

### 5.2 Profile Repair Procedures

**Scenario: Missing tenant_id in user record**
```sql
-- Fix orphaned user
UPDATE users SET tenant_id = {VALID_TENANT_ID} 
WHERE id = {USER_ID} AND (tenant_id IS NULL OR tenant_id = 0);
```

**Scenario: Corrupted inquiries data**
```sql
-- Soft delete corrupted records (don't hard delete)
UPDATE inquiries 
SET deleted_at = NOW(), notes = CONCAT(notes, ' | Marked corrupted on ', NOW())
WHERE tenant_id IS NULL OR full_name IS NULL;
```

---

## 6. Third-Party Integration Failures

### 6.1 Integration Points

| Integration | Usage | Failure Impact |
|-------------|-------|----------------|
| PHPMailer | Email notifications | Non-critical for inquiry list |
| Payment Gateway | Fee collection | Non-critical for inquiry list |
| SMS Provider | Follow-up notifications | Non-critical for inquiry list |
| External Auth | SSO login | Critical - blocks access |

### 6.2 Isolation Testing

```php
// Test mode for inquiries API
if (isset($_GET['test_mode']) && APP_ENV === 'development') {
    // Skip external service calls
    $skipExternalServices = true;
}
```

---

## 7. Software Update Impact Assessment

### 7.1 Recent Changes to Monitor

Track modifications in:
- [`app/Http/Controllers/Admin/inquiries.php`](app/Http/Controllers/Admin/inquiries.php)
- [`config/config.php`](config/config.php)
- [`app/Http/Middleware/auth.php`](app/Http/Middleware/auth.php)

### 7.2 Rollback Procedures

```bash
# Git rollback (if version controlled)
git log --oneline -10
git checkout {COMMIT_HASH} -- app/Http/Controllers/Admin/inquiries.php

# Database migration rollback
php artisan migrate:rollback --step=1
```

---

## 8. Immediate Resolution Playbook

### 8.1 Emergency Response (Production Down)

```bash
#!/bin/bash
# emergency_fix.sh

echo "=== Emergency Inquiry List Fix ==="

# 1. Clear all caches
echo "Clearing caches..."
rm -f /tmp/fd_cache_*

# 2. Restart Apache
echo "Restarting Apache..."
sudo systemctl restart apache2

# 3. Verify database connection
echo "Testing database..."
mysql -u root -e "SELECT COUNT(*) FROM hamrolabs_db.inquiries;"

# 4. Check disk space
echo "Disk space check..."
df -h

echo "=== Emergency fixes applied ==="
```

### 8.2 Quick Fixes by Error Message

| Error Message | Quick Fix |
|---------------|-----------|
| "Unauthorized" | Clear browser cookies, re-login |
| "Tenant ID missing" | Check user profile in database |
| "Access denied" | Verify user role assignment |
| "Table not found" | Run migration scripts |
| Connection timeout | Restart MySQL service |
| Memory exhausted | Increase PHP memory_limit |

---

## 9. Prevention Strategies

### 9.1 Monitoring Implementation

```php
// Add to inquiries.php for proactive monitoring
$startTime = microtime(true);
// ... query execution
$duration = (microtime(true) - $startTime) * 1000;

if ($duration > 2000) {
    error_log("[PERFORMANCE] Inquiry list query took {$duration}ms for tenant {$tenantId}");
}
```

### 9.2 Health Check Endpoint

```php
// health_check.php
header('Content-Type: application/json');

$checks = [
    'database' => false,
    'session' => false,
    'cache_writable' => false
];

try {
    $db = getDBConnection();
    $checks['database'] = true;
    
    // Test inquiry query
    $stmt = $db->query("SELECT 1 FROM inquiries LIMIT 1");
    $checks['inquiries_table'] = true;
} catch (Exception $e) {
    $checks['database_error'] = $e->getMessage();
}

$checks['session'] = isLoggedIn();
$checks['cache_writable'] = is_writable(sys_get_temp_dir());

echo json_encode(['healthy' => !in_array(false, $checks), 'checks' => $checks]);
```

### 9.3 Recommended Configuration Hardening

```php
// config/config.php additions
if (!defined('API_TIMEOUT')) define('API_TIMEOUT', 30);
if (!defined('QUERY_LIMIT')) define('QUERY_LIMIT', 500);
if (!defined('ENABLE_QUERY_CACHE')) define('ENABLE_QUERY_CACHE', true);
```

---

## 10. Testing & Validation

### 10.1 Automated Test Script

```bash
#!/bin/bash
# test_inquiry_api.sh

BASE_URL="http://localhost/erp"

echo "Testing Inquiry API Endpoints..."

# Test 1: Health check
echo "1. Health Check..."
curl -s "${BASE_URL}/health_check.php" | jq .

# Test 2: Without authentication
echo "2. Unauthenticated access (should fail)..."
curl -s "${BASE_URL}/api/admin/inquiries" | jq .

# Test 3: With authentication (requires valid session cookie)
echo "3. Authenticated access..."
curl -s -b "PHPSESSID=VALID_SESSION_ID" "${BASE_URL}/api/admin/inquiries" | jq .

echo "Tests completed."
```

### 10.2 Load Testing

```bash
# Apache Bench test
ab -n 100 -c 10 -C "PHPSESSID=VALID_SESSION" \
   http://localhost/erp/api/admin/inquiries
```

---

## 11. Escalation Matrix

| Issue Tier | Response Time | Escalate To |
|------------|---------------|-------------|
| Single user affected | 15 minutes | Front Desk Supervisor |
| Multiple users affected | 5 minutes | IT Administrator |
| Complete system down | Immediate | System Administrator |
| Data corruption suspected | Immediate | Database Administrator |

---

## Appendix A: Error Code Reference

| Code | Meaning | Action |
|------|---------|--------|
| UNAUTHORIZED | Session expired | Re-login required |
| MISSING_TENANT | User profile incomplete | Update user record |
| ACCESS_DENIED | Role not authorized | Check role permissions |
| TABLE_NOT_FOUND | Migration not run | Execute migrations |
| QUERY_TIMEOUT | Performance issue | Optimize query/add indexes |

---

## Appendix B: Contact Information

| Role | Responsibility | Contact |
|------|----------------|---------|
| Front Desk Operator | First line reporting | Internal extension |
| IT Support | Initial troubleshooting | it-support@company.com |
| Database Admin | Data integrity issues | dba@company.com |
| System Admin | Infrastructure issues | sysadmin@company.com |

---

*Document Version: 1.0*  
*Last Updated: 2026-03-01*  
*System: Hamro ERP v3.0*  
*Applicable Environments: Production, Staging, Development*
