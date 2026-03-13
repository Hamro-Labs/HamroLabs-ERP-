# ERP Expansion Plan: Multi-Vertical Architecture & System Upgrade Strategy

**Document Version:** 1.0  
**Prepared For:** Hamro Labs Academic ERP Expansion Strategy  
**Date:** March 2026  
**Classification:** Technical Architecture — Development Blueprint

---

## Executive Summary

This document outlines the complete technical architecture, system design, and implementation strategy for expanding Hamro Labs Academic ERP from a Loksewa-specific platform to a **multi-vertical training institute management system** supporting Computer Training, Bridge Course, Tuition Centers, and CTEVT Skill Training institutes.

**Architectural Approach:** Modular feature-flag system allowing tenant-level customization while maintaining a unified codebase.

**Development Philosophy:** Build shared components first, vertical-specific features second. Avoid forking the codebase into separate products.

**Key Deliverables:**
1. Modular architecture design
2. Feature flag system implementation
3. Database schema evolution strategy
4. New module specifications
5. UI/UX improvements for multi-vertical support
6. Integration architecture (WhatsApp, eSewa/Khalti, CTEVT)

---

## 1. System Architecture Overview

### 1.1 Current Architecture (Loksewa-Focused)

```
┌─────────────────────────────────────────────────────────────┐
│                    HAMRO LABS ERP V3.0                       │
│                   (Current Architecture)                     │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │  Web Portal  │  │  Mobile PWA  │  │ Teacher PWA  │      │
│  │ (Laravel UI) │  │   (Offline)  │  │ (Attendance) │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘      │
│         │                 │                  │               │
│         └─────────────────┼──────────────────┘               │
│                           │                                  │
│              ┌────────────▼──────────────┐                   │
│              │   Laravel 11 Backend      │                   │
│              │   (Multi-Tenant Core)     │                   │
│              └────────────┬──────────────┘                   │
│                           │                                  │
│         ┌─────────────────┼─────────────────┐               │
│         │                 │                 │               │
│    ┌────▼────┐      ┌────▼────┐      ┌────▼────┐           │
│    │ MySQL 8 │      │  Redis  │      │ Python  │           │
│    │ (Data)  │      │(Cache)  │      │ Reports │           │
│    └─────────┘      └─────────┘      └─────────┘           │
│                                                              │
│  Modules: Students, Batches, Attendance, Fees, Exams,       │
│           Study Materials, Notifications                    │
└─────────────────────────────────────────────────────────────┘
```

**Characteristics:**
- ✅ **Multi-Tenant:** Tenant isolation via `tenant_id` column in all tables
- ✅ **Monolithic:** Single codebase, all features enabled for all tenants
- ⚠️ **Loksewa-Optimized:** Exam categories hardcoded, no vertical flexibility
- ⚠️ **Fixed Workflow:** Single student lifecycle (inquiry → admission → batch → exam → completion)

### 1.2 Proposed Multi-Vertical Architecture

```
┌──────────────────────────────────────────────────────────────────────────┐
│               HAMRO LABS MULTI-VERTICAL ERP V4.0                         │
│                  (Proposed Expansion Architecture)                       │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌────────────┐  │
│  │  Web Portal  │  │  Mobile PWA  │  │ Teacher PWA  │  │ Parent App │  │
│  │  (Adaptive)  │  │   (Offline)  │  │ (Multi-role) │  │  (Tuition) │  │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘  └─────┬──────┘  │
│         │                 │                  │                │          │
│         └─────────────────┼──────────────────┴────────────────┘          │
│                           │                                              │
│              ┌────────────▼──────────────────────┐                       │
│              │   Laravel 11 Backend (Modular)    │                       │
│              │   ┌────────────────────────────┐  │                       │
│              │   │  Feature Flag Manager      │  │                       │
│              │   │  (Tenant-Level Control)    │  │                       │
│              │   └────────────┬───────────────┘  │                       │
│              │                │                  │                       │
│              │   ┌────────────▼───────────────┐  │                       │
│              │   │   Module Router            │  │                       │
│              │   │   - Loksewa Module         │  │                       │
│              │   │   - Computer Training Mod  │  │                       │
│              │   │   - Bridge Course Module   │  │                       │
│              │   │   - Tuition Module         │  │                       │
│              │   │   - CTEVT Module           │  │                       │
│              │   └────────────────────────────┘  │                       │
│              └───────────────────────────────────┘                       │
│                           │                                              │
│         ┌─────────────────┼─────────────────┬─────────────────┐         │
│         │                 │                 │                 │         │
│    ┌────▼────┐      ┌────▼────┐      ┌────▼────┐      ┌────▼────┐     │
│    │ MySQL 8 │      │  Redis  │      │ Python  │      │WhatsApp │     │
│    │(Enhanced│      │(Cache+  │      │ Reports │      │   API   │     │
│    │ Schema) │      │ Queue)  │      │ Engine  │      │         │     │
│    └─────────┘      └─────────┘      └─────────┘      └─────────┘     │
│                                                                           │
│  Shared Core: Multi-Tenant, Auth, Attendance, Fees, Notifications       │
│  Vertical Modules: Certificates, Lab Tracking, CTEVT Compliance,        │
│                   Model Questions, Parent Portal                        │
└──────────────────────────────────────────────────────────────────────────┘
```

**Key Changes:**
1. **Feature Flag System:** Tenants enable/disable modules based on institute type
2. **Modular Plugins:** Each vertical (Computer Training, CTEVT, etc.) is a Laravel package
3. **Adaptive UI:** Dashboard changes based on enabled modules
4. **Unified Core:** Student, Batch, Fee, Attendance logic shared across all verticals

---

## 2. Modular Architecture Design

### 2.1 Core Modules (Shared Across All Institute Types)

These modules remain **unchanged** and serve all verticals:

| Module | Description | Tables | Reusability |
|--------|-------------|--------|-------------|
| **Multi-Tenancy** | Tenant isolation, subdomain routing, branding | `tenants`, `users`, `subscriptions` | 100% |
| **Authentication** | Login, password reset, OTP, refresh tokens | `users`, `otp_codes`, `refresh_tokens` | 100% |
| **Student Management** | Registration, documents, guardian linking | `students`, `guardians` | 95% (minor enhancements) |
| **Batch Management** | Batch creation, room allocation, capacity | `batches` | 90% (add stream support) |
| **Attendance** | Daily marking, locking, audit logs | `attendance`, `attendance_audit_logs` | 85% (add hour tracking) |
| **Fee Management** | Payment recording, receipts, late fines | `fee_items`, `payments`, `fee_ledger` | 90% (add multi-course) |
| **Communication** | SMS, Email, templates, broadcast | `sms_logs`, `email_logs`, `notifications` | 100% |
| **Reporting** | Excel/PDF reports, analytics | Python report engine | 100% |
| **Study Materials** | Upload, categorize, access control | `study_materials`, `study_material_categories` | 100% |

**Total Reusability:** ~93% of core features work across all verticals without modification.

### 2.2 Vertical-Specific Modules (Institute Type Dependent)

These are **NEW** modules activated via feature flags:

| Module | Loksewa | Computer Training | Bridge Course | Tuition | CTEVT | Development Effort |
|--------|---------|-------------------|---------------|---------|-------|-------------------|
| **Certificate Generation** | ❌ | ✅ | ❌ | ⚠️ Optional | ✅ | 120 hours |
| **Lab Session Management** | ❌ | ✅ | ❌ | ❌ | ✅ | 70 hours |
| **Model Question Bank** | ❌ | ❌ | ✅ | ❌ | ❌ | 60 hours |
| **Stream Management** | ❌ | ❌ | ✅ | ❌ | ❌ | 40 hours |
| **Parent Portal** | ❌ | ❌ | ❌ | ✅ | ❌ | 80 hours |
| **CTEVT Compliance** | ❌ | ❌ | ❌ | ❌ | ✅ | 100 hours |
| **Entrance Result Tracking** | ❌ | ❌ | ✅ | ❌ | ❌ | 30 hours |
| **Project Submission** | ❌ | ✅ | ❌ | ❌ | ⚠️ Optional | 40 hours |
| **Job Placement Tracking** | ❌ | ⚠️ Optional | ❌ | ❌ | ⚠️ Optional | 40 hours |

**Module Ownership:**
- **Computer Training:** Certificates, Lab Management, Project Submission
- **Bridge Course:** Model Questions, Stream Management, Entrance Results
- **Tuition Centers:** Parent Portal
- **CTEVT:** Certificates, Lab Management, CTEVT Compliance

### 2.3 Feature Flag System Implementation

**Database Schema:**

```sql
CREATE TABLE tenant_features (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  feature_key VARCHAR(100) NOT NULL,
  is_enabled TINYINT(1) DEFAULT 1,
  config JSON DEFAULT NULL, -- Feature-specific settings
  enabled_at TIMESTAMP NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (tenant_id, feature_key)
);

-- Example data
INSERT INTO tenant_features VALUES
(1, 101, 'certificates', 1, '{"auto_number": true, "qr_code": true}', NOW()),
(2, 101, 'lab_management', 1, '{"workstations": 30}', NOW()),
(3, 102, 'model_questions', 1, NULL, NOW()),
(4, 102, 'stream_management', 1, '{"streams": ["Science", "Management"]}', NOW());
```

**Usage in Code (Laravel):**

```php
// Check if feature is enabled for current tenant
if (auth()->user()->tenant->hasFeature('certificates')) {
    // Show certificate menu
}

// Get feature config
$labConfig = auth()->user()->tenant->getFeatureConfig('lab_management');
$maxWorkstations = $labConfig['workstations'] ?? 20;

// Feature flag middleware
Route::middleware(['tenant.feature:certificates'])->group(function () {
    Route::get('/certificates', [CertificateController::class, 'index']);
    Route::post('/certificates/generate', [CertificateController::class, 'generate']);
});
```

**Feature Categories:**

```php
// config/features.php
return [
    'core' => [
        'students' => ['name' => 'Student Management', 'always_enabled' => true],
        'attendance' => ['name' => 'Attendance System', 'always_enabled' => true],
        'fees' => ['name' => 'Fee Management', 'always_enabled' => true],
    ],
    'computer_training' => [
        'certificates' => ['name' => 'Certificate Generation', 'plan_min' => 'starter'],
        'lab_management' => ['name' => 'Lab & Workstation Tracking', 'plan_min' => 'growth'],
        'project_submission' => ['name' => 'Project Submission', 'plan_min' => 'growth'],
    ],
    'bridge_course' => [
        'model_questions' => ['name' => 'Model Question Bank', 'plan_min' => 'starter'],
        'stream_management' => ['name' => 'Stream Management', 'plan_min' => 'starter'],
        'entrance_results' => ['name' => 'Entrance Result Tracking', 'plan_min' => 'growth'],
    ],
    'ctevt' => [
        'ctevt_compliance' => ['name' => 'CTEVT Compliance Module', 'plan_min' => 'professional'],
        'hour_tracking' => ['name' => 'Hour-Based Tracking', 'plan_min' => 'starter'],
    ],
    'tuition' => [
        'parent_portal' => ['name' => 'Parent Portal', 'plan_min' => 'growth'],
    ],
];
```

---

## 3. New Module Specifications

### 3.1 Certificate Generation Module

**Feature Name:** `certificates`  
**Applicable To:** Computer Training, CTEVT  
**Priority:** 🔴 CRITICAL

**Functional Requirements:**

1. **Template Management**
   - Admin creates certificate templates (HTML/CSS)
   - Variables: `{{student_name}}`, `{{course_name}}`, `{{grade}}`, `{{issue_date}}`, `{{certificate_number}}`
   - Orientation: Portrait / Landscape
   - Logo, signature, border customization
   - Preview mode before finalizing

2. **Certificate Generation**
   - Single certificate generation (per student)
   - Batch generation (select 50 students → generate 50 PDFs)
   - QR code auto-generated (links to `/verify/{certificate_id}`)
   - Auto-increment certificate numbering (`HLC/2026/001`, `HLC/2026/002`...)
   - PDF generation via Laravel Snappy (wkhtmltopdf)

3. **Verification System**
   - Public verification page: `/verify/{certificate_number}`
   - Shows: Student name, course, grade, issue date, institute name
   - No login required (public trust mechanism)

4. **Certificate Management**
   - List all issued certificates
   - Filter by course, batch, date range
   - Reissue certificate (if lost)
   - Track collection status (collected / pending)

**Technical Architecture:**

```php
// Model: Certificate.php
class Certificate extends Model {
    protected $fillable = [
        'tenant_id', 'student_id', 'enrollment_id', 'template_id',
        'certificate_number', 'grade', 'issue_date', 'qr_code_url',
        'pdf_url', 'issued_by', 'collected_at'
    ];
    
    public function student() {
        return $this->belongsTo(Student::class);
    }
    
    public function template() {
        return $this->belongsTo(CertificateTemplate::class);
    }
    
    public static function generateNumber($tenantId) {
        $year = date('Y');
        $count = self::where('tenant_id', $tenantId)
                     ->whereYear('created_at', $year)
                     ->count() + 1;
        $prefix = auth()->user()->tenant->certificate_prefix ?? 'CERT';
        return "{$prefix}/{$year}/" . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}

// Controller: CertificateController.php
public function generateBatch(Request $request) {
    $enrollmentIds = $request->enrollment_ids; // Array of IDs
    $templateId = $request->template_id;
    
    foreach ($enrollmentIds as $enrollmentId) {
        GenerateCertificateJob::dispatch($enrollmentId, $templateId);
    }
    
    return response()->json(['message' => 'Certificates queued for generation']);
}

// Job: GenerateCertificateJob.php
public function handle() {
    $enrollment = Enrollment::find($this->enrollmentId);
    $template = CertificateTemplate::find($this->templateId);
    
    $certNumber = Certificate::generateNumber($enrollment->tenant_id);
    $qrUrl = route('certificate.verify', $certNumber);
    
    // Render HTML with variables replaced
    $html = str_replace([
        '{{student_name}}', '{{course_name}}', '{{grade}}', '{{issue_date}}'
    ], [
        $enrollment->student->full_name,
        $enrollment->batch->course->name,
        $enrollment->final_grade ?? 'A',
        now()->format('Y-m-d')
    ], $template->html_template);
    
    // Generate QR code
    $qrCode = QrCode::size(100)->generate($qrUrl);
    $html = str_replace('{{qr_code}}', $qrCode, $html);
    
    // Generate PDF
    $pdf = PDF::loadHTML($html);
    $filename = "certificates/{$certNumber}.pdf";
    Storage::put($filename, $pdf->output());
    
    // Save certificate record
    Certificate::create([
        'tenant_id' => $enrollment->tenant_id,
        'student_id' => $enrollment->student_id,
        'enrollment_id' => $enrollment->id,
        'template_id' => $this->templateId,
        'certificate_number' => $certNumber,
        'grade' => $enrollment->final_grade,
        'issue_date' => now(),
        'qr_code_url' => $qrUrl,
        'pdf_url' => Storage::url($filename),
        'issued_by' => auth()->id(),
    ]);
}
```

**UI Design:**

```
┌────────────────────────────────────────────────────────┐
│  Certificate Management                                │
├────────────────────────────────────────────────────────┤
│  [+ New Template]  [Generate Certificates]             │
├────────────────────────────────────────────────────────┤
│                                                         │
│  Filters: [Course ▼] [Batch ▼] [Date Range]  [Search] │
│                                                         │
│  ┌──────────────────────────────────────────────────┐  │
│  │ ☑ CS101 - Basic Computer - Batch A - 50 students│  │
│  │ ☐ CS102 - Tally Prime - Batch B - 30 students   │  │
│  │ ☐ CS103 - Photoshop - Batch A - 25 students     │  │
│  └──────────────────────────────────────────────────┘  │
│                                                         │
│  Selected: 50 students                                 │
│  Template: [Professional Design ▼]                     │
│                                                         │
│  [Preview Sample] [Generate Batch (50 PDFs)]          │
│                                                         │
└────────────────────────────────────────────────────────┘
```

---

### 3.2 Lab & Practical Session Management Module

**Feature Name:** `lab_management`  
**Applicable To:** Computer Training, CTEVT  
**Priority:** 🟡 MEDIUM

**Functional Requirements:**

1. **Lab Room Setup**
   - Define lab rooms (Lab A, Lab B, Workshop)
   - Specify workstation count per lab
   - Assign lab to specific courses

2. **Workstation Assignment**
   - Assign student to specific PC/workstation for course duration
   - Track workstation utilization (which PCs are in use)
   - Reassignment if student transfers or PC breaks

3. **Session Type Tracking**
   - Mark attendance as "Theory" or "Practical"
   - Record hours per session (e.g., 1.5 hours practical)
   - Auto-calculate total theory vs practical hours

4. **Hour-Based Reporting**
   - Student dashboard: "320/390 hours completed (82%)"
   - Admin report: List students below 80% threshold
   - CTEVT export: Hours summary in government format

**Database Schema:**

```sql
CREATE TABLE lab_rooms (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  name VARCHAR(100) NOT NULL,
  workstation_count INT DEFAULT 0,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE workstation_assignments (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  student_id BIGINT NOT NULL,
  enrollment_id BIGINT NOT NULL,
  lab_room_id BIGINT NOT NULL,
  workstation_number INT NOT NULL,
  assigned_at DATE NOT NULL,
  released_at DATE DEFAULT NULL,
  UNIQUE KEY unique_workstation_per_enrollment (enrollment_id, lab_room_id, workstation_number)
);

-- Modify existing attendance table
ALTER TABLE attendance 
ADD COLUMN session_type ENUM('theory', 'practical') DEFAULT 'theory' AFTER status,
ADD COLUMN hours DECIMAL(4,2) DEFAULT 1.00 AFTER session_type;

-- Modify courses table to store hour requirements
ALTER TABLE courses
ADD COLUMN required_theory_hours INT DEFAULT 0 AFTER duration_months,
ADD COLUMN required_practical_hours INT DEFAULT 0 AFTER required_theory_hours;
```

**Code Example:**

```php
// Calculate student's total hours
public function getTotalHoursAttribute() {
    return $this->attendance()
                ->where('status', 'present')
                ->sum('hours');
}

public function getTheoryHoursAttribute() {
    return $this->attendance()
                ->where('status', 'present')
                ->where('session_type', 'theory')
                ->sum('hours');
}

public function getPracticalHoursAttribute() {
    return $this->attendance()
                ->where('status', 'present')
                ->where('session_type', 'practical')
                ->sum('hours');
}

// Check if student meets minimum hour requirement (CTEVT)
public function meetsHourRequirement() {
    $course = $this->enrollment->batch->course;
    $totalRequired = $course->required_theory_hours + $course->required_practical_hours;
    return $this->total_hours >= ($totalRequired * 0.80); // 80% minimum
}
```

---

### 3.3 CTEVT Compliance Module

**Feature Name:** `ctevt_compliance`  
**Applicable To:** CTEVT Skill Training Institutes  
**Priority:** 🔴 CRITICAL (for CTEVT segment)

**Functional Requirements:**

1. **CTEVT Course Setup**
   - Link course to CTEVT code (e.g., "Caregiver - 390 hours - CTEVT Code: CG-390")
   - Define competency units (e.g., "Patient Lifting", "Medication Administration")
   - Set theory vs practical hour breakdown

2. **Competency Grading**
   - Teacher marks student as "Competent" / "Not Yet Competent" per unit
   - Overall competency percentage calculation
   - Block certificate generation until 100% competent

3. **Government Reporting**
   - Monthly attendance report (CTEVT format Excel)
   - Student registration form (auto-fill from student data)
   - Annual renewal documentation checklist

4. **Compliance Alerts**
   - Alert 30 days before CTEVT affiliation expires
   - Flag students below 80% attendance (ineligible for certificate)
   - Instructor qualification expiry reminders

**Database Schema:**

```sql
CREATE TABLE ctevt_courses (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  course_id BIGINT NOT NULL,
  ctevt_code VARCHAR(50) UNIQUE,
  total_hours INT NOT NULL,
  theory_hours INT DEFAULT 0,
  practical_hours INT DEFAULT 0,
  level ENUM('basic', 'intermediate', 'diploma') DEFAULT 'basic',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE competency_units (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  ctevt_course_id BIGINT NOT NULL,
  unit_name VARCHAR(255) NOT NULL,
  unit_code VARCHAR(50),
  hours INT NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE student_competencies (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  student_id BIGINT NOT NULL,
  enrollment_id BIGINT NOT NULL,
  competency_unit_id BIGINT NOT NULL,
  status ENUM('competent', 'not_yet_competent', 'pending') DEFAULT 'pending',
  assessed_by BIGINT, -- teacher_id
  assessed_at DATE,
  notes TEXT,
  UNIQUE KEY unique_student_competency (student_id, competency_unit_id)
);

CREATE TABLE ctevt_reports (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  report_type ENUM('monthly_attendance', 'annual_renewal', 'student_registration') NOT NULL,
  month INT,
  year INT,
  file_path VARCHAR(500),
  generated_by BIGINT,
  generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 4. Database Schema Upgrade Strategy

**Approach:** Incremental migrations, no breaking changes

### 4.1 Phase 1 Schema Changes (Computer Training Focus)

```sql
-- Migration 001: Add institute_type to tenants
ALTER TABLE tenants
ADD COLUMN institute_type ENUM('loksewa', 'computer_training', 'bridge_course', 'tuition', 'ctevt', 'hybrid') DEFAULT 'loksewa' AFTER plan;

-- Migration 002: Expand course categories
ALTER TABLE courses
MODIFY COLUMN category ENUM('loksewa','health','banking','tsc','general','engineering','computer_training','bridge_course','ctevt_skill') NOT NULL DEFAULT 'general';

-- Migration 003: Add hour tracking to courses
ALTER TABLE courses
ADD COLUMN required_theory_hours INT DEFAULT 0 AFTER duration_months,
ADD COLUMN required_practical_hours INT DEFAULT 0 AFTER required_theory_hours;

-- Migration 004: Add session type and hours to attendance
ALTER TABLE attendance
ADD COLUMN session_type ENUM('theory', 'practical') DEFAULT 'theory' AFTER status,
ADD COLUMN hours DECIMAL(4,2) DEFAULT 1.00 AFTER session_type;

-- Migration 005: Add final grade to enrollments
ALTER TABLE enrollments
ADD COLUMN final_grade VARCHAR(10) DEFAULT NULL AFTER status,
ADD COLUMN completion_certificate_issued TINYINT(1) DEFAULT 0 AFTER final_grade;

-- Migration 006: Create certificates table
CREATE TABLE certificates (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  student_id BIGINT NOT NULL,
  enrollment_id BIGINT NOT NULL,
  certificate_number VARCHAR(50) UNIQUE NOT NULL,
  template_id BIGINT NOT NULL,
  grade VARCHAR(10) DEFAULT NULL,
  issue_date DATE NOT NULL,
  qr_code_url VARCHAR(500),
  pdf_url VARCHAR(500),
  issued_by BIGINT,
  collected_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (enrollment_id) REFERENCES enrollments(id)
);

-- Migration 007: Create certificate templates
CREATE TABLE certificate_templates (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  name VARCHAR(100) NOT NULL,
  orientation ENUM('portrait', 'landscape') DEFAULT 'landscape',
  html_template TEXT NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);

-- Migration 008: Create tenant_features table
CREATE TABLE tenant_features (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  feature_key VARCHAR(100) NOT NULL,
  is_enabled TINYINT(1) DEFAULT 1,
  config JSON DEFAULT NULL,
  enabled_at TIMESTAMP NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_tenant_feature (tenant_id, feature_key),
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);

-- Migration 009: Lab rooms and workstations
CREATE TABLE lab_rooms (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  name VARCHAR(100) NOT NULL,
  workstation_count INT DEFAULT 0,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);

CREATE TABLE workstation_assignments (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  student_id BIGINT NOT NULL,
  enrollment_id BIGINT NOT NULL,
  lab_room_id BIGINT NOT NULL,
  workstation_number INT NOT NULL,
  assigned_at DATE NOT NULL,
  released_at DATE DEFAULT NULL,
  UNIQUE KEY unique_workstation (enrollment_id, lab_room_id, workstation_number),
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (lab_room_id) REFERENCES lab_rooms(id)
);

-- Migration 010: Multi-enrollment support
-- Remove unique constraint on student_id + batch_id
ALTER TABLE enrollments
DROP INDEX idx_unique_enrollment;

-- Add course_id to enrollments for direct reference
ALTER TABLE enrollments
ADD COLUMN course_id BIGINT AFTER batch_id,
ADD FOREIGN KEY (course_id) REFERENCES courses(id);

-- Create new composite index
CREATE INDEX idx_student_enrollments ON enrollments(student_id, status);
```

### 4.2 Phase 2 Schema Changes (Bridge Course & CTEVT)

```sql
-- Migration 011: Stream management for bridge courses
CREATE TABLE streams (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  name VARCHAR(100) NOT NULL, -- e.g., "Science", "Management"
  code VARCHAR(20),
  description TEXT,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);

ALTER TABLE batches
ADD COLUMN stream_id BIGINT DEFAULT NULL AFTER course_id,
ADD FOREIGN KEY (stream_id) REFERENCES streams(id);

-- Migration 012: Model question bank
CREATE TABLE model_questions (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  college_name VARCHAR(100),
  exam_year INT,
  subject VARCHAR(100),
  question_text TEXT NOT NULL,
  answer_text TEXT,
  difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);

CREATE TABLE student_question_attempts (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  student_id BIGINT NOT NULL,
  model_question_id BIGINT NOT NULL,
  status ENUM('correct', 'incorrect', 'skipped') DEFAULT 'skipped',
  attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (model_question_id) REFERENCES model_questions(id)
);

-- Migration 013: Entrance exam results
CREATE TABLE entrance_results (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  student_id BIGINT NOT NULL,
  enrollment_id BIGINT NOT NULL,
  college_name VARCHAR(200) NOT NULL,
  program VARCHAR(100),
  entrance_score DECIMAL(6,2),
  entrance_rank INT,
  scholarship_percentage INT DEFAULT 0,
  admission_status ENUM('admitted', 'waitlisted', 'rejected', 'pending') DEFAULT 'pending',
  result_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (student_id) REFERENCES students(id)
);

-- Migration 014: CTEVT compliance tables
CREATE TABLE ctevt_courses (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  course_id BIGINT NOT NULL,
  ctevt_code VARCHAR(50) UNIQUE,
  total_hours INT NOT NULL,
  theory_hours INT DEFAULT 0,
  practical_hours INT DEFAULT 0,
  level ENUM('basic', 'intermediate', 'diploma') DEFAULT 'basic',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE competency_units (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  ctevt_course_id BIGINT NOT NULL,
  unit_name VARCHAR(255) NOT NULL,
  unit_code VARCHAR(50),
  hours INT NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ctevt_course_id) REFERENCES ctevt_courses(id)
);

CREATE TABLE student_competencies (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  student_id BIGINT NOT NULL,
  enrollment_id BIGINT NOT NULL,
  competency_unit_id BIGINT NOT NULL,
  status ENUM('competent', 'not_yet_competent', 'pending') DEFAULT 'pending',
  assessed_by BIGINT,
  assessed_at DATE,
  notes TEXT,
  UNIQUE KEY unique_student_competency (student_id, competency_unit_id),
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (competency_unit_id) REFERENCES competency_units(id)
);
```

---

## 5. UI/UX Multi-Vertical Adaptations

### 5.1 Adaptive Dashboard Design

**Principle:** Dashboard modules appear/disappear based on enabled features.

**Example — Computer Training Institute:**

```
┌──────────────────────────────────────────────────────────────┐
│  Dashboard - Skill Training Nepal                            │
├──────────────────────────────────────────────────────────────┤
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐    │
│  │ Students │  │  Active  │  │  Pending │  │   Labs   │    │
│  │   245    │  │  Batches │  │   Fees   │  │ Occupied │    │
│  │          │  │    18    │  │ NPR 85K  │  │   28/30  │    │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘    │
│                                                               │
│  Recent Activities                                           │
│  • Certificate generated for "Basic Computer Batch A" (50)  │
│  • New enrollment: Ramesh KC → Tally Prime                  │
│  • Lab Room A: Workstation 15 marked faulty                 │
│                                                               │
│  Certificates Pending Issuance: 12 students                 │
│  [View List] [Generate Batch]                               │
└──────────────────────────────────────────────────────────────┘
```

**Example — Bridge Course Institute:**

```
┌──────────────────────────────────────────────────────────────┐
│  Dashboard - Excellence Academy                              │
├──────────────────────────────────────────────────────────────┤
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐    │
│  │ Students │  │ Science  │  │Management│  │ Entrance │    │
│  │   580    │  │ Stream:  │  │ Stream:  │  │ Mock Test│    │
│  │          │  │   320    │  │   260    │  │ Scheduled│    │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘    │
│                                                               │
│  Entrance Results Summary                                    │
│  • St. Xavier's College: 45 students admitted (85%)         │
│  • DAV College: 28 students admitted (78%)                  │
│  • Full Scholarships: 12 students                           │
│                                                               │
│  Model Question Practice: 2,340 questions attempted         │
│  [Add New Model Questions] [View Analytics]                 │
└──────────────────────────────────────────────────────────────┘
```

### 5.2 Menu Structure (Feature-Dependent)

**Base Menu (All Institutes):**
- Dashboard
- Students
- Batches
- Attendance
- Fees
- Study Materials
- Reports

**Computer Training Additions:**
- 🆕 Certificates
- 🆕 Lab Management
- 🆕 Projects

**Bridge Course Additions:**
- 🆕 Streams
- 🆕 Model Questions
- 🆕 Entrance Results

**CTEVT Additions:**
- 🆕 CTEVT Compliance
- 🆕 Competency Tracking
- 🆕 Hour Reports

---

## 6. Integration Architecture

### 6.1 WhatsApp Business API Integration

**Purpose:** Nepal's primary messaging platform (SMS declining)

**Architecture:**

```
Hamro Labs ERP
      ↓
   WhatsApp Business API (360dialog / Twilio)
      ↓
   WhatsApp Servers
      ↓
   Student/Parent/Teacher
```

**Implementation:**

```php
// Service: WhatsAppService.php
class WhatsAppService {
    public function sendMessage($to, $message) {
        $url = config('whatsapp.api_url') . '/messages';
        $apiKey = config('whatsapp.api_key');
        
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
        ])->post($url, [
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message],
        ]);
        
        return $response->successful();
    }
    
    public function sendDocument($to, $documentUrl, $caption = null) {
        // Send study material PDFs
    }
}

// Usage
$whatsapp = new WhatsAppService();
$whatsapp->sendMessage('9779841234567', 'Your fee is due on 5th March. Pay via eSewa: esewa.com.np/pay/12345');
```

### 6.2 eSewa / Khalti Payment Gateway Integration

**Purpose:** Digital fee collection, reduce cash handling

**Architecture:**

```
Student Portal
      ↓
   [Pay via eSewa] button
      ↓
   eSewa Payment Page
      ↓
   Payment Success → Webhook → Hamro Labs ERP → Auto-update fee record
```

**Implementation (eSewa):**

```php
// Controller: PaymentController.php
public function initiateEsewa(Request $request) {
    $feeRecord = FeeRecord::find($request->fee_record_id);
    
    $esewaConfig = [
        'merchant_code' => config('esewa.merchant_code'),
        'amount' => $feeRecord->amount,
        'transaction_uuid' => Str::uuid(),
        'product_code' => "FEE-{$feeRecord->id}",
        'success_url' => route('payment.esewa.success'),
        'failure_url' => route('payment.esewa.failure'),
    ];
    
    return view('payments.esewa-form', compact('esewaConfig'));
}

public function esewaSuccess(Request $request) {
    $transactionCode = $request->refId;
    
    // Verify with eSewa API
    $verification = Http::post('https://esewa.com.np/epay/transrec', [
        'amt' => $request->amt,
        'rid' => $transactionCode,
        'pid' => $request->pid,
        'scd' => config('esewa.merchant_code'),
    ]);
    
    if ($verification->body() === 'Success') {
        // Update payment record
        $feeRecord = FeeRecord::where('id', str_replace('FEE-', '', $request->pid))->first();
        
        Payment::create([
            'tenant_id' => $feeRecord->tenant_id,
            'student_id' => $feeRecord->student_id,
            'amount' => $request->amt,
            'payment_method' => 'esewa',
            'transaction_id' => $transactionCode,
            'status' => 'completed',
        ]);
        
        $feeRecord->update(['status' => 'paid']);
        
        return redirect()->route('student.dashboard')->with('success', 'Payment successful!');
    }
    
    return redirect()->route('student.dashboard')->with('error', 'Payment verification failed');
}
```

---

## 7. Implementation Checklist

### Phase 1 (Computer Training - 14 weeks)

**Week 1-2: Foundation**
- [ ] Create feature flag system (`tenant_features` table)
- [ ] Implement feature middleware
- [ ] Add `institute_type` to tenants table
- [ ] Expand `course.category` ENUM

**Week 3-5: Certificate Module (CRITICAL)**
- [ ] Design certificate templates table
- [ ] Build template designer UI (HTML/CSS editor)
- [ ] Implement QR code generation
- [ ] Build PDF generation job (Laravel queue)
- [ ] Create batch certificate generator
- [ ] Public verification page

**Week 6-7: Multi-Enrollment**
- [ ] Remove unique constraint on enrollments
- [ ] Add `course_id` to enrollments
- [ ] Update enrollment UI (multi-select courses)
- [ ] Modify fee calculation logic
- [ ] Update student profile (show all enrollments)

**Week 8-9: Lab Management**
- [ ] Create lab_rooms table
- [ ] Build lab room setup UI
- [ ] Implement workstation assignment
- [ ] Add session_type to attendance
- [ ] Build hour tracking logic

**Week 10-11: Payment Integration**
- [ ] eSewa API integration
- [ ] Khalti API integration
- [ ] Payment webhook handlers
- [ ] Auto-receipt generation

**Week 12-13: WhatsApp Integration**
- [ ] 360dialog / Twilio setup
- [ ] WhatsApp message service
- [ ] Template approval from WhatsApp
- [ ] Notification routing (SMS vs WhatsApp)

**Week 14: Testing & Beta Launch**
- [ ] QA testing (10 beta institutes)
- [ ] Bug fixes
- [ ] User training videos
- [ ] Public launch

---

## 8. Success Metrics

**Technical Metrics:**
- Certificate generation: <5 seconds per certificate
- Batch generation (100 certs): <60 seconds
- System uptime: 99.5%+
- Page load time: <2 seconds

**Business Metrics:**
- Phase 1: 50 computer training institutes by end of Q2 2026
- Certificate module adoption: 90%+ of computer training customers
- Digital payment adoption: 60%+ of customers
- Customer churn: <10% annual

---

**Document End**

This expansion plan provides the complete technical blueprint for transforming Hamro Labs into a multi-vertical platform while maintaining code quality, scalability, and Nepal-market focus.
