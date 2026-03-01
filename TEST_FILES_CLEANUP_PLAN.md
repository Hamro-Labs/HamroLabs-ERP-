# Test Files Cleanup Plan - ERP Project

## Executive Summary

This document provides actionable steps for cleaning up test-related, diagnostic, and temporary files from the ERP project codebase. **DO NOT PROCEED WITHOUT CREATING A BACKUP FIRST.**

---

## Phase 1: Pre-Cleanup Safety Checklist

### 1.1 Create Full Backup
```bash
# Create a zip backup of the entire project
cd c:\Apache24\htdocs
zip -r erp_backup_$(date +%Y%m%d_%H%M%S).zip erp/

# OR use git to tag current state
cd c:\Apache24\htdocs\erp
git add -A
git commit -m "Pre-cleanup backup: $(date)"
git tag backup-before-cleanup-$(date +%Y%m%d)
```

### 1.2 Verify Active Usage
Before deleting any file, verify it's not actively used:

```bash
# Search for file references in the codebase
grep -r "check_email_table" --include="*.php" .
grep -r "tmp_get_otp" --include="*.php" .
grep -r "test_pages.php" --include="*.php" .
```

---

## Phase 2: Diagnostic Scripts Removal (SAFE)

### 2.1 Files to Delete (7 files)
These are standalone diagnostic scripts with no production dependencies:

| # | File Path | Verification Command | Delete Command |
|---|-----------|---------------------|----------------|
| 1 | `check_email_table.php` | `grep -r "check_email_table" --include="*.php" .` | `del check_email_table.php` |
| 2 | `check_enums.php` | `grep -r "check_enums" --include="*.php" .` | `del check_enums.php` |
| 3 | `check_fees.php` | `grep -r "check_fees" --include="*.php" .` | `del check_fees.php` |
| 4 | `check_photo.php` | `grep -r "check_photo" --include="*.php" .` | `del check_photo.php` |
| 5 | `check_schema.php` | `grep -r "check_schema" --include="*.php" .` | `del check_schema.php` |
| 6 | `check_table.php` | `grep -r "check_table" --include="*.php" .` | `del check_table.php` |
| 7 | `check_tables.php` | `grep -r "check_tables" --include="*.php" .` | `del check_tables.php` |

### 2.2 Quick Removal Script (Windows PowerShell)
```powershell
# Navigate to project root
cd C:\Apache24\htdocs\erp

# Remove diagnostic scripts
$files = @(
    "check_email_table.php",
    "check_enums.php",
    "check_fees.php",
    "check_photo.php",
    "check_schema.php",
    "check_table.php",
    "check_tables.php"
)

foreach ($file in $files) {
    if (Test-Path $file) {
        Remove-Item $file -Force
        Write-Host "Deleted: $file" -ForegroundColor Green
    } else {
        Write-Host "Not found: $file" -ForegroundColor Yellow
    }
}
```

---

## Phase 3: Temporary/Debug Files Removal (SAFE)

### 3.1 Files to Delete (5 files)
These are temporary debug files prefixed with `tmp_` and `scratch`:

| # | File Path | Verification Command | Delete Command |
|---|-----------|---------------------|----------------|
| 1 | `tmp_get_otp.php` | `grep -r "tmp_get_otp" --include="*.php" .` | `del tmp_get_otp.php` |
| 2 | `tmp_users_schema.php` | `grep -r "tmp_users_schema" --include="*.php" .` | `del tmp_users_schema.php` |
| 3 | `tmp_debug_500.php` | `grep -r "tmp_debug_500" --include="*.php" .` | `del tmp_debug_500.php` |
| 4 | `tmp_check_schema.php` | `grep -r "tmp_check_schema" --include="*.php" .` | `del tmp_check_schema.php` |
| 5 | `scratch.php` | `grep -r "scratch.php" --include="*.php" .` | `del scratch.php` |

### 3.2 Quick Removal Script (Windows PowerShell)
```powershell
cd C:\Apache24\htdocs\erp

$tempFiles = @(
    "tmp_get_otp.php",
    "tmp_users_schema.php",
    "tmp_debug_500.php",
    "tmp_check_schema.php",
    "scratch.php"
)

foreach ($file in $tempFiles) {
    if (Test-Path $file) {
        Remove-Item $file -Force
        Write-Host "Deleted: $file" -ForegroundColor Green
    } else {
        Write-Host "Not found: $file" -ForegroundColor Yellow
    }
}
```

---

## Phase 4: SMS Module Test Pages (REVIEW REQUIRED)

### 4.1 Files Under Review (4 files)
These files are part of the SMS module and may be used for testing:

| # | File Path | Purpose | Recommendation |
|---|-----------|---------|----------------|
| 1 | `sms/test_pages.php` | Central test hub | Verify with SMS module owner |
| 2 | `sms/test_profile.php` | Profile testing | Verify with SMS module owner |
| 3 | `sms/quick_login.php` | Quick login for testing | Verify with SMS module owner |
| 4 | `sms/project_verification.md` | Test documentation | Move to `docs/` folder |

### 4.2 Verification Steps
```bash
# Check if these files are linked anywhere
grep -r "test_pages.php" --include="*.php" .
grep -r "quick_login.php" --include="*.php" .

# Check recent access logs (if available)
find sms/test_pages.php -mtime -30  # Accessed in last 30 days
```

### 4.3 Conditional Action
- **IF** files are not referenced AND not accessed in 30+ days → **DELETE**
- **IF** files are actively used → **MOVE** to `sms/tests/` subdirectory
- **IF** unsure → **KEEP** and flag for future review

---

## Phase 5: Test Infrastructure Files (KEEP)

### 5.1 Core Test Files - DO NOT DELETE
These are legitimate test files required for QA:

```
tests/
├── GlobalSearchFrontendTest.js    # Frontend search tests
├── GlobalSearchTest.php           # API search tests
├── LoginApiTest.php               # Login API tests
├── LoginSystemTest.php            # Login system tests
└── test_fee_generation.php        # Fee generation tests
```

### 5.2 Required Bootstrap Files - DO NOT DELETE
```
bootstrap/
├── app.php                        # Application bootstrap
├── legacy.php                     # Legacy support
└── cache/                         # Cache directory

config/
├── config.php                     # Configuration
├── app.php                        # App config
├── database.php                   # Database config
└── mail.php                       # Mail config
```

---

## Phase 6: Cleanup Verification

### 6.1 Post-Cleanup Checks
```bash
# Verify no broken references remain
grep -r "check_" --include="*.php" . | grep -v "check_"  # Should return nothing

# Ensure application still loads
curl -I http://localhost/erp/

# Run existing tests to ensure nothing broke
cd C:\Apache24\htdocs\erp
php tests/LoginSystemTest.php
```

### 6.2 Application Health Check
```php
<?php
// Create a quick health check script
require_once 'config/config.php';

$checks = [
    'Database Connection' => false,
    'Config Loaded' => false,
    'Bootstrap Works' => false,
];

try {
    $checks['Config Loaded'] = defined('APP_URL');
    $checks['Bootstrap Works'] = class_exists('App\Models\User');
    // Add DB check if applicable
    
    echo "Health Check Results:\n";
    foreach ($checks as $name => $status) {
        echo "$name: " . ($status ? "✅ PASS" : "❌ FAIL") . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
```

---

## Phase 7: Git Cleanup (Optional)

### 7.1 Add Cleanup to .gitignore
```bash
# Ignore future temporary files
echo "tmp_*.php" >> .gitignore
echo "scratch.php" >> .gitignore
echo "check_*.php" >> .gitignore
```

### 7.2 Commit Cleanup
```bash
git add -A
git commit -m "chore: cleanup diagnostic and temporary files

- Removed 7 diagnostic check scripts
- Removed 5 temporary debug files
- Verified application integrity
- Updated .gitignore to prevent future temp files"
```

---

## Summary: Deletion Count

| Category | Files | Status |
|----------|-------|--------|
| Diagnostic Scripts | 7 | ✅ Ready to delete |
| Temporary Files | 5 | ✅ Ready to delete |
| SMS Test Pages | 4 | ⚠️ Review required |
| Core Test Files | 5 | 🔴 Keep |
| Bootstrap/Config | 6+ | 🔴 Keep |
| **TOTAL TO REVIEW** | **27** | **12 deletable** |

---

## Emergency Rollback

If issues occur after deletion:

```bash
# Restore from git
git checkout backup-before-cleanup-$(date +%Y%m%d)

# OR restore from zip backup
unzip erp_backup_YYYYMMDD_HHMMSS.zip -d C:\Apache24\htdocs\
```

---

## Approval Sign-off

| Role | Name | Signature | Date |
|------|------|-----------|------|
| Tech Lead | | | |
| QA Lead | | | |
| Project Manager | | | |

---

*Generated: 2026-03-01*
*Next Review: Post-cleanup verification*