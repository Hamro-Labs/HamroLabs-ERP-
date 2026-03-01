# Attendance Management Module - Implementation Plan

## Hamro Labs ERP (Laravel MVC - Multi-Tenant)

---

## 1. Executive Summary

This document outlines a comprehensive implementation plan for the **Attendance Management Module** in the Hamro Labs ERP system. The module enables:
- Student daily attendance tracking
- Batch & course-wise attendance management
- Automated reporting with export capabilities
- Leave request management
- Lock/unlock attendance system

### Technology Stack (Based on Existing Codebase)
- **Framework**: Custom PHP using Laravel components (not Eloquent ORM)
- **Database**: MySQL with raw PDO queries
- **Authentication**: Session-based with RBAC
- **Multi-tenancy**: Tenant ID filtering via session
- **Views**: PHP-based Blade-like templates

---

## 2. Project Analysis Summary

### 2.1 Existing Architecture Patterns
| Component | Pattern Used |
|-----------|-------------|
| Models | Plain PHP classes with direct PDO queries |
| Controllers | API endpoints returning JSON |
| Authentication | Session-based (`isLoggedIn()`, `getCurrentUser()`) |
| Multi-tenancy | `tenant_id` from `$_SESSION['userData']['tenant_id']` |
| Database | Laravel Migrations + Schema Builder |
| Routes | Laravel Route facade in `routes/web.php` |
| Views | PHP templates in `resources/views/{role}/` |

### 2.2 Key Files Reference
- **Config**: `config/config.php`
- **Database Connection**: `getDBConnection()` function
- **Auth Functions**: Session-based in `config.php`
- **Date Conversion**: `App\Helpers\DateUtils` class

---

## 3. Database Design

### 3.1 Migration Files Required

#### 3.1.1 `YYYY_MM_DD_HHMMSS_create_attendance_tables.php`

```php
// Main Attendance Table
Schema::create('attendance', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('tenant_id');
    $table->unsignedBigInteger('student_id');
    $table->unsignedBigInteger('batch_id');
    $table->unsignedBigInteger('course_id');
    $table->date('attendance_date');
    $table->enum('status', ['present', 'absent', 'late', 'leave'])->default('present');
    $table->unsignedBigInteger('marked_by')->nullable();
    $table->boolean('locked')->default(false);
    $table->timestamps();
    
    // Composite unique constraint - prevents duplicates
    $table->unique(['student_id', 'batch_id', 'attendance_date'], 'unique_student_batch_date');
    
    // Indexes for performance
    $table->index(['tenant_id', 'attendance_date'], 'idx_tenant_date');
    $table->index(['batch_id', 'attendance_date'], 'idx_batch_date');
    $table->index(['student_id', 'attendance_date'], 'idx_student_date');
    $table->index(['tenant_id', 'status'], 'idx_tenant_status');
    
    // Foreign keys
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
    $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
    $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
    $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
    $table->foreign('marked_by')->references('id')->on('users')->onDelete('set null');
});

// Leave Requests Table
Schema::create('leave_requests', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('tenant_id');
    $table->unsignedBigInteger('student_id');
    $table->date('from_date');
    $table->date('to_date');
    $table->text('reason');
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->unsignedBigInteger('approved_by')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->timestamps();
    
    // Indexes
    $table->index(['tenant_id', 'status'], 'idx_tenant_leave_status');
    $table->index(['student_id', 'from_date', 'to_date'], 'idx_student_leave_dates');
    
    // Foreign keys
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
    $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
    $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
});

// Attendance Audit Log Table
Schema::create('attendance_audit_logs', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('tenant_id');
    $table->unsignedBigInteger('attendance_id');
    $table->unsignedBigInteger('user_id');
    $table->string('action'); // created, updated, locked, unlocked
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->timestamp('created_at');
    
    $table->index(['tenant_id', 'created_at'], 'idx_tenant_audit_date');
    $table->index('attendance_id', 'idx_attendance_audit');
});

// Attendance Settings Table (for configurable lock period)
Schema::create('attendance_settings', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('tenant_id');
    $table->integer('lock_period_hours')->default(24);
    $table->boolean('exclude_leave_from_total')->default(true);
    $table->boolean('allow_frontdesk_edit')->default(false);
    $table->timestamps();
    
    $table->unique('tenant_id');
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
});
```

---

## 4. Model Classes Required

### 4.1 `app/Models/Attendance.php`

```php
<?php
namespace App\Models;

class Attendance {
    protected $table = 'attendance';
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    // Core CRUD operations
    public function create($data);
    public function update($id, $data);
    public function find($id);
    public function delete($id); // Not used per requirements
    
    // Query methods
    public function getByBatch($batchId, $date, $tenantId);
    public function getByStudent($studentId, $dateRange, $tenantId);
    public function getByTenant($tenantId, $filters);
    public function bulkUpsert($records, $tenantId);
    public function markLocked($ids, $locked = true);
    
    // Report methods
    public function getStudentStats($studentId, $batchId, $tenantId);
    public function getBatchStats($batchId, $dateRange, $tenantId);
    public function getTodayStats($tenantId);
}
```

### 4.2 `app/Models/LeaveRequest.php`

```php
<?php
namespace App\Models;

class LeaveRequest {
    protected $table = 'leave_requests';
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    // CRUD operations
    public function create($data);
    public function update($id, $data);
    public function find($id);
    
    // Query methods
    public function getByStudent($studentId, $tenantId);
    public function getPending($tenantId);
    public function getApprovedForDate($date, $tenantId);
    public function approve($id, $approvedBy);
    public function reject($id, $approvedBy);
}
```

### 4.3 `app/Models/AttendanceAuditLog.php`

```php
<?php
namespace App\Models;

class AttendanceAuditLog {
    protected $table = 'attendance_audit_logs';
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    public function log($data);
    public function getByAttendance($attendanceId);
    public function getByTenant($tenantId, $dateRange);
}
```

### 4.4 `app/Models/AttendanceSettings.php`

```php
<?php
namespace App\Models;

class AttendanceSettings {
    protected $table = 'attendance_settings';
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    public function getByTenant($tenantId);
    public function update($tenantId, $data);
    public function createDefaults($tenantId);
}
```

---

## 5. Service Layer Required

### 5.1 `app/Services/AttendanceService.php`

This service will contain all business logic:

```php
<?php
namespace App\Services;

require_once base_path('config/config.php');

class AttendanceService {
    
    private $db;
    private $attendance;
    private $leaveRequest;
    private $auditLog;
    private $settings;
    
    public function __construct() {
        $this->db = getDBConnection();
        $this->attendance = new \App\Models\Attendance();
        $this->leaveRequest = new \App\Models\LeaveRequest();
        $this->auditLog = new \App\Models\AttendanceAuditLog();
        $this->settings = new \App\Models\AttendanceSettings();
    }
    
    /**
     * Take attendance for a batch
     * @param array $data { batch_id, course_id, date, attendance: [{student_id, status}] }
     * @param int $userId
     * @param int $tenantId
     * @return array
     */
    public function takeAttendance($data, $userId, $tenantId);
    
    /**
     * Bulk save attendance with upsert logic
     */
    public function bulkSave($records, $userId, $tenantId);
    
    /**
     * Edit attendance (with lock check)
     */
    public function editAttendance($id, $data, $userId, $tenantId);
    
    /**
     * Lock attendance records
     */
    public function lockAttendance($ids, $tenantId);
    
    /**
     * Unlock attendance records (admin only)
     */
    public function unlockAttendance($ids, $userId, $tenantId);
    
    /**
     * Check if attendance can be edited
     */
    public function canEdit($attendanceId, $tenantId, $isSuperAdmin = false);
    
    /**
     * Process approved leave - mark attendance as leave
     */
    public function processApprovedLeave($leaveId, $tenantId);
    
    /**
     * Calculate attendance percentage
     */
    public function calculatePercentage($studentId, $batchId, $tenantId, $excludeLeave = true);
    
    /**
     * Get attendance report data
     */
    public function getReport($filters, $tenantId);
    
    /**
     * Export attendance data
     */
    public function export($filters, $format, $tenantId);
}
```

---

## 6. Controller Structure

### 6.1 `app/Http/Controllers/Admin/attendance.php`

```php
<?php
/**
 * Attendance API Controller
 * Handles all attendance operations for Institute Admin and Front Desk
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../../config/config.php';
}

header('Content-Type: application/json');

// Auth check
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
$tenantId = $user['tenant_id'] ?? null;
$userId = $user['id'] ?? null;
$role = $user['role'] ?? '';

if (!$tenantId) {
    echo json_encode(['success' => false, 'message' missing']);
    exit => 'Tenant ID;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDBConnection();
    $service = new \App\Services\AttendanceService();
    
    // GET Requests
    if ($method === 'GET') {
        // GET /api/admin/attendance - List attendance with filters
        if (!empty($_GET['action']) && $_GET['action'] === 'report') {
            // Student-wise report
        } elseif (!empty($_GET['action']) && $_GET['action'] === 'export') {
            // Export data
        } elseif (!empty($_GET['action']) && $_GET['action'] === 'stats') {
            // Dashboard stats
        } elseif (!empty($_GET['batch_id']) && !empty($_GET['date'])) {
            // Get attendance for specific batch and date
        } else {
            // List attendance with pagination
        }
    }
    
    // POST - Take attendance
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;
        
        $action = $input['action'] ?? 'take';
        
        switch ($action) {
            case 'take':
                // Save attendance
                break;
            case 'bulk':
                // Bulk save
                break;
            case 'lock':
                // Lock records
                break;
            case 'unlock':
                // Unlock records (admin only)
                break;
        }
    }
    
    // PUT/PATCH - Edit attendance
    elseif ($method === 'PUT' || $method === 'PATCH') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;
        
        // Edit single attendance record
    }
    
    // DELETE - Not used (soft delete not required)
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
```

### 6.2 `app/Http/Controllers/Admin/leave_requests.php`

```php
<?php
/**
 * Leave Requests API Controller
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../../config/config.php';
}

header('Content-Type: application/json');

// Auth check...

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // List leave requests with filters
    }
    elseif ($method === 'POST') {
        // Create leave request (student)
    }
    elseif ($method === 'PUT' || $method === 'PATCH') {
        // Approve/Reject leave (admin)
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
```

---

## 7. Routes Configuration

### 7.1 Add to `routes/web.php`

```php
// Attendance API Routes
Route::any('/api/admin/attendance', function() {
    require_once app_path('Http/Controllers/Admin/attendance.php');
});

Route::any('/api/admin/attendance/{action}', function($action) {
    require_once app_path('Http/Controllers/Admin/attendance.php');
});

// Leave Requests API Routes
Route::any('/api/admin/leave-requests', function() {
    require_once app_path('Http/Controllers/Admin/leave_requests.php');
});

// Front Desk can also access attendance (read-only mostly)
Route::any('/api/frontdesk/attendance', function() {
    require_once app_path('Http/Controllers/Admin/attendance.php');
});
```

---

## 8. UI/UX Implementation

### 8.1 Admin Dashboard Page

Create: `resources/views/admin/attendance.php`

#### Page Structure:
```
┌─────────────────────────────────────────────────────────────┐
│ Page Header: Attendance Management                    [?] │
├─────────────────────────────────────────────────────────────┤
│ Filters Bar                                                │
│ ┌──────────┐ ┌──────────┐ ┌────────────┐ ┌────────────┐     │
│ │ Course ▼ │ │ Batch ▼  │ │ Date Picker│ │ Search    │     │
│ └──────────┘ └──────────┘ └────────────┘ └────────────┘     │
├─────────────────────────────────────────────────────────────┤
│ Quick Stats                                                 │
│ ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐               │
│ │Today % │ │Absent  │ │ Late   │ │ On Leave│               │
│ └────────┘ └────────┘ └────────┘ └────────┘               │
├─────────────────────────────────────────────────────────────┤
│ Attendance Table                                     [Export]│
│ ┌────┬──────────┬────────┬───────┬────────┬────────┐       │
│ │ ☐  │ Roll No  │ Name   │ Status│ Lock   │ Actions│       │
│ ├────┼──────────┼────────┼───────┼────────┼────────┤       │
│ │ ☑  │ 000001   │ Ram    │ [P]   │ 🔒     │ ✏️     │       │
│ │ ☑  │ 000002   │ Shyam  │ [A]   │ 🔓     │ ✏️     │       │
│ └────┴──────────┴────────┴───────┴────────┴────────┘       │
│ [Mark All Present] [Mark All Absent]         [Save Button]  │
├─────────────────────────────────────────────────────────────┤
│ Pagination: [< Prev] 1 2 3 ... [Next >]                     │
└─────────────────────────────────────────────────────────────┘
```

#### Status Toggle Options:
- Present (green)
- Absent (red)  
- Late (yellow)
- Leave (blue)

#### Status Toggle UI:
```html
<div class="status-toggle">
    <button class="status-btn present active" data-status="present">P</button>
    <button class="status-btn absent" data-status="absent">A</button>
    <button class="status-btn late" data-status="late">L</button>
    <button class="status-btn leave" data-status="leave">LV</button>
</div>
```

### 8.2 Leave Requests Page

Create: `resources/views/admin/leave-requests.php`

### 8.3 Reports Page

Create: `resources/views/admin/attendance-reports.php`

---

## 9. JavaScript Implementation

### 9.1 Add to `public/assets/js/ia-attendance.js`

```javascript
// Attendance Management JavaScript

const AttendanceModule = {
    // Configuration
    config: {
        apiBase: '/api/admin/attendance',
        leaveApiBase: '/api/admin/leave-requests'
    },
    
    // State
    state: {
        currentBatch: null,
        currentCourse: null,
        currentDate: null,
        students: [],
        isLocked: false
    },
    
    // Initialize
    init() {
        this.bindEvents();
        this.loadInitialData();
    },
    
    // Event Bindings
    bindEvents() {
        // Course selection
        document.getElementById('course-select')?.addEventListener('change', (e) => {
            this.loadBatches(e.target.value);
        });
        
        // Batch selection
        document.getElementById('batch-select')?.addEventListener('change', (e) => {
            this.loadStudents(e.target.value);
        });
        
        // Date picker
        document.getElementById('attendance-date')?.addEventListener('change', (e) => {
            this.loadAttendance();
        });
        
        // Save button
        document.getElementById('save-attendance')?.addEventListener('click', () => {
            this.saveAttendance();
        });
        
        // Mark all present
        document.getElementById('mark-all-present')?.addEventListener('click', () => {
            this.markAllPresent();
        });
    },
    
    // Load courses
    async loadCourses() {
        // Fetch courses from API
    },
    
    // Load batches by course
    async loadBatches(courseId) {
        // Fetch batches from API
    },
    
    // Load students for attendance
    async loadStudents(batchId, date) {
        // Fetch enrolled students
    },
    
    // Load existing attendance
    async loadAttendance() {
        // Fetch existing attendance records
    },
    
    // Save attendance
    async saveAttendance() {
        // Collect attendance data and save
    },
    
    // Toggle student status
    toggleStatus(studentId, status) {
        // Update student status in state
    },
    
    // Export functions
    exportToExcel() {
        // Export to Excel
    },
    
    exportToPDF() {
        // Export to PDF
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('attendance-page')) {
        AttendanceModule.init();
    }
});
```

---

## 10. Business Logic Rules Implementation

### 10.1 Validation Rules

| Rule | Implementation |
|------|----------------|
| One attendance per student per batch per day | DB unique constraint + application check |
| Cannot mark future date | Date validation in controller |
| Cannot duplicate attendance | Upsert logic with conflict resolution |
| Locked records cannot be edited | Check `locked` flag before update |
| Must validate student-batch relation | Validate before insert |
| Must enforce tenant_id filter | Always filter by tenant_id |

### 10.2 Lock System Logic

```php
public function canEdit($attendanceId, $tenantId, $isSuperAdmin = false) {
    // Get settings
    $settings = $this->settings->getByTenant($tenantId);
    $lockPeriodHours = $settings['lock_period_hours'] ?? 24;
    
    // Get attendance record
    $attendance = $this->attendance->find($attendanceId);
    
    // Super admin can always edit
    if ($isSuperAdmin) {
        return true;
    }
    
    // Check if locked explicitly
    if ($attendance['locked']) {
        return false;
    }
    
    // Check time limit
    $createdAt = strtotime($attendance['created_at']);
    $now = time();
    $hoursDiff = ($now - $createdAt) / 3600;
    
    if ($hoursDiff > $lockPeriodHours) {
        return false;
    }
    
    return true;
}
```

### 10.3 Attendance Percentage Calculation

```php
public function calculatePercentage($studentId, $batchId, $tenantId, $excludeLeave = true) {
    $db = getDBConnection();
    
    $whereClause = "student_id = :sid AND batch_id = :bid";
    $params = ['sid' => $studentId, 'bid' => $batchId];
    
    // Get total classes
    $totalQuery = "SELECT COUNT(*) as total FROM attendance 
                   WHERE $whereClause AND tenant_id = :tid";
    $params['tid'] = $tenantId;
    
    // Get present count
    $presentQuery = "SELECT COUNT(*) as present FROM attendance 
                     WHERE $whereClause AND status = 'present' AND tenant_id = :tid";
    
    // If exclude leave from total
    if ($excludeLeave) {
        $totalQuery .= " AND status != 'leave'";
    }
    
    // Calculate percentage
    $percentage = ($presentCount / $totalCount) * 100;
    
    return round($percentage, 2);
}
```

---

## 11. Security Implementation

### 11.1 Tenant Isolation

```php
// Always include tenant_id in queries
$stmt = $db->prepare("
    SELECT * FROM attendance 
    WHERE tenant_id = :tid 
    AND batch_id = :batch_id 
    AND attendance_date = :date
");
```

### 11.2 Authorization

```php
// Check role permissions
$canEdit = in_array($role, ['instituteadmin', 'superadmin']);
$canUnlock = $role === 'instituteadmin' || $role === 'superadmin';
$frontDeskReadOnly = $role === 'frontdesk';
```

### 11.3 Audit Logging

```php
public function logAudit($attendanceId, $userId, $tenantId, $action, $oldValues, $newValues) {
    $stmt = $this->db->prepare("
        INSERT INTO attendance_audit_logs 
        (tenant_id, attendance_id, user_id, action, old_values, new_values, created_at)
        VALUES (:tid, :aid, :uid, :action, :old, :new, NOW())
    ");
    
    $stmt->execute([
        'tid' => $tenantId,
        'aid' => $attendanceId,
        'uid' => $userId,
        'action' => $action,
        'old' => json_encode($oldValues),
        'new' => json_encode($newValues)
    ]);
}
```

---

## 12. API Endpoints Summary

### 12.1 Attendance Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin/attendance` | List attendance with filters |
| GET | `/api/admin/attendance?batch_id=X&date=Y` | Get attendance for batch/date |
| GET | `/api/admin/attendance?action=report` | Student-wise report |
| GET | `/api/admin/attendance?action=stats` | Dashboard stats |
| GET | `/api/admin/attendance?action=export` | Export data |
| POST | `/api/admin/attendance` | Take attendance |
| POST | `/api/admin/attendance` (action=bulk) | Bulk save |
| POST | `/api/admin/attendance` (action=lock) | Lock records |
| POST | `/api/admin/attendance` (action=unlock) | Unlock records |
| PUT | `/api/admin/attendance` | Edit attendance |

### 12.2 Leave Request Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin/leave-requests` | List leave requests |
| POST | `/api/admin/leave-requests` | Submit leave request |
| PUT | `/api/admin/leave-requests` | Approve/Reject leave |

---

## 13. Implementation Phases

### Phase 1: Core Infrastructure (Priority: High)
- [ ] Create database migrations
- [ ] Create model classes
- [ ] Create service layer
- [ ] Create controller
- [ ] Add routes

### Phase 2: Frontend UI (Priority: High)
- [ ] Create attendance page view
- [ ] Add JavaScript module
- [ ] Integrate with existing CSS
- [ ] Add status toggle components

### Phase 3: Features (Priority: Medium)
- [ ] Leave request system
- [ ] Lock/unlock functionality
- [ ] Report generation

### Phase 4: Export & Analytics (Priority: Medium)
- [ ] Excel export
- [ ] PDF export
- [ ] Dashboard widgets

### Phase 5: Testing & Polish (Priority: Low)
- [ ] Unit testing
- [ ] Integration testing
- [ ] Bug fixes
- [ ] Performance optimization

---

## 14. File Creation Checklist

### New Files to Create:

#### Database
- [ ] `database/migrations/YYYY_MM_DD_HHMMSS_create_attendance_tables.php`

#### Models
- [ ] `app/Models/Attendance.php`
- [ ] `app/Models/LeaveRequest.php`
- [ ] `app/Models/AttendanceAuditLog.php`
- [ ] `app/Models/AttendanceSettings.php`

#### Services
- [ ] `app/Services/AttendanceService.php`

#### Controllers
- [ ] `app/Http/Controllers/Admin/attendance.php`
- [ ] `app/Http/Controllers/Admin/leave_requests.php`

#### Routes
- [ ] Update `routes/web.php` with attendance routes

#### Views
- [ ] `resources/views/admin/attendance.php`
- [ ] `resources/views/admin/leave-requests.php`
- [ ] `resources/views/admin/attendance-reports.php`

#### JavaScript
- [ ] `public/assets/js/ia-attendance.js`

---

## 15. Dependencies & Considerations

### 15.1 Existing Dependencies
- `getDBConnection()` - Database connection
- `isLoggedIn()` / `getCurrentUser()` - Authentication
- Date conversion utilities in `DateUtils`
- Existing CSS framework in `public/assets/css/`

### 15.2 Future Extensibility
- QR Code scanning integration point
- Biometric device sync point
- SMS notification hook
- Email notification hook

### 15.3 Performance Considerations
- Database indexes on frequently queried columns
- Eager loading for batch operations
- Pagination for large datasets
- Caching for dashboard stats

---

## 16. Risk Mitigation

| Risk | Mitigation |
|------|------------|
| Duplicate records | DB unique constraint + application validation |
| Cross-tenant data leak | Always filter by tenant_id |
| Slow reports | Proper indexing + query optimization |
| Data tampering | Audit logging on all changes |
| Lock system bypass | Server-side validation + role check |

---

## 17. Success Criteria

- [ ] Attendance marking completes in < 2 minutes per batch
- [ ] Zero duplicate records
- [ ] 100% tenant isolation verified
- [ ] Accurate percentage calculations
- [ ] Lock system prevents editing after lock period
- [ ] Export generates valid Excel/PDF files
- [ ] All user roles have appropriate permissions

---

*Plan Version: 1.0*
*Created: Based on PRD Specification*
*Architecture: Custom PHP with Laravel Components*
