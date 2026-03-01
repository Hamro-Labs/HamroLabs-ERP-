# Student Portal Implementation Plan

## Executive Summary

This plan outlines the complete implementation strategy for the Student Portal in Hamro ERP. The Student Portal provides students with self-service access to their academic information, class schedules, attendance records, fee details, study materials, exams, and personal profile management.

## Current State Analysis

### Existing Student Implementation

**Controllers:**
- [`app/Http/Controllers/Student/fees.php`](app/Http/Controllers/Student/fees.php:1) - Fee ledger and payment history (2288 chars)
- [`app/Http/Controllers/Student/study_materials.php`](app/Http/Controllers/Student/study_materials.php:1) - Study materials access (24031 chars)

**Views:**
- [`resources/views/student/index.php`](resources/views/student/index.php:1) - Main dashboard shell (5633 chars)
- [`resources/views/student/fees.php`](resources/views/student/fees.php:1) - Fee view page (1222 chars)
- [`resources/views/student/student-profile-view.php`](resources/views/student/student-profile-view.php:1) - Profile view (21868 chars)
- [`resources/views/student/student-security-settings.php`](resources/views/student/student-security-settings.php:1) - Security settings (21867 chars)
- [`resources/views/student/student-id-card-view.php`](resources/views/student/student-id-card-view.php:1) - Digital ID card (16294 chars)

**JavaScript:**
- [`public/assets/js/student.js`](public/assets/js/student.js:1) - Main student JavaScript with navigation and page renderers

**CSS:**
- [`public/assets/css/student.css`](public/assets/css/student.css:1) - Student portal styles (31802 chars)

**Models Available for Student Portal:**
- [`app/Models/Student.php`](app/Models/Student.php:1) - Student data
- [`app/Models/Attendance.php`](app/Models/Attendance.php:1) - Attendance records
- [`app/Models/FeeRecord.php`](app/Models/FeeRecord.php:1) - Fee records
- [`app/Models/PaymentTransaction.php`](app/Models/PaymentTransaction.php:1) - Payment history
- [`app/Models/StudyMaterial.php`](app/Models/StudyMaterial.php:1) - Study materials
- [`app/Models/LeaveRequest.php`](app/Models/LeaveRequest.php:1) - Leave requests
- [`app/Models/StudentInvoice.php`](app/Models/StudentInvoice.php:1) - Invoices

### Existing Routes

```php
Route::any('/api/student/fees', function() {
    require_once app_path('Http/Controllers/Student/fees.php');
});
```

## Student Portal Feature Requirements

Based on the navigation structure in [`student.js`](public/assets/js/student.js:37), the Student Portal requires:

### 1. Dashboard (`dashboard`)
- Overview of today's classes
- Pending assignments count
- Fee status summary
- Recent notices
- Attendance percentage
- Upcoming exams

### 2. My Classes (`classes`)
- Today's timetable
- Weekly schedule view
- Academic calendar integration
- Class join links (for online classes)

### 3. Attendance (`att`)
- Attendance summary/percentage
- Monthly attendance history
- Leave application form
- Leave request status tracking

### 4. Assignments (`assignments`)
- Pending assignments list
- Assignment submission form
- Graded assignments with feedback
- Submission history

### 5. Exams & Mock Tests (`exams`)
- Available exams list
- Exam results with detailed analytics
- Performance charts/graphs
- Batch leaderboard
- Mock test practice mode

### 6. Fee Management (`fee`)
- Fee status overview
- Payment history
- Downloadable receipts
- Outstanding dues display

### 7. Study Materials (`study`)
- Notes and resources (already implemented)
- Previous year papers
- Bookmarked materials
- Download history

### 8. Library (`library`)
- Currently borrowed books
- Book search/catalog
- Due date reminders
- Fine calculation

### 9. Notices (`notices`)
- Institute announcements
- Batch-specific notices
- Fee reminders
- Mark as read functionality

### 10. My Profile (`profile`)
- Personal details view/edit
- Academic history
- Document uploads (citizenship, photos)
- Password change
- Digital ID card

## Implementation Phases

### Phase 1: Core Infrastructure & Routes (4 hours)

**1.1 Create Student API Routes**

Update [`routes/web.php`](routes/web.php:1):

```php
// Student Portal API Routes
Route::any('/api/student/dashboard', function() {
    require_once app_path('Http/Controllers/Student/dashboard.php');
});

Route::any('/api/student/classes', function() {
    require_once app_path('Http/Controllers/Student/classes.php');
});

Route::any('/api/student/attendance', function() {
    require_once app_path('Http/Controllers/Student/attendance.php');
});

Route::any('/api/student/assignments', function() {
    require_once app_path('Http/Controllers/Student/assignments.php');
});

Route::any('/api/student/exams', function() {
    require_once app_path('Http/Controllers/Student/exams.php');
});

Route::any('/api/student/notices', function() {
    require_once app_path('Http/Controllers/Student/notices.php');
});

Route::any('/api/student/library', function() {
    require_once app_path('Http/Controllers/Student/library.php');
});

Route::any('/api/student/profile', function() {
    require_once app_path('Http/Controllers/Student/profile.php');
});

// Study materials already exists
Route::any('/api/student/study-materials', function() {
    require_once app_path('Http/Controllers/Student/study_materials.php');
});

// Fees already exists
Route::any('/api/student/fees', function() {
    require_once app_path('Http/Controllers/Student/fees.php');
});
```

**1.2 Create Base Student Controller**

Create `app/Http/Controllers/Student/BaseController.php`:
- Authentication check helper
- Student data retrieval helper
- Tenant validation
- JSON response standardization

### Phase 2: Dashboard & Core Views (6 hours)

**2.1 Student Dashboard Controller**

Create `app/Http/Controllers/Student/dashboard.php`:
```php
<?php
/**
 * Student Dashboard API
 * Returns aggregated data for student dashboard
 */

header('Content-Type: application/json');

// Auth check
$studentId = $_SESSION['userData']['student_id'] ?? null;
$tenantId = $_SESSION['userData']['tenant_id'] ?? null;

if (!$studentId || !$tenantId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $db = getDBConnection();
    
    $dashboard = [
        'today_classes' => [], // From timetable
        'attendance_summary' => [], // Calculate percentage
        'pending_assignments' => 0,
        'fee_summary' => [],
        'recent_notices' => [],
        'upcoming_exams' => []
    ];
    
    // Fetch today's classes
    $stmt = $db->prepare("
        SELECT t.*, s.name as subject_name, st.name as teacher_name
        FROM timetables t
        JOIN subjects s ON t.subject_id = s.id
        JOIN staff st ON t.teacher_id = st.id
        WHERE t.batch_id = (SELECT batch_id FROM students WHERE id = :sid)
        AND t.day_of_week = DAYNAME(CURDATE())
        AND t.tenant_id = :tid
    ");
    $stmt->execute(['sid' => $studentId, 'tid' => $tenantId]);
    $dashboard['today_classes'] = $stmt->fetchAll();
    
    // Fetch attendance summary
    $attendanceModel = new \App\Models\Attendance();
    $dashboard['attendance_summary'] = $attendanceModel->getStudentSummary($studentId, $tenantId);
    
    // Fetch fee summary
    $feeRecordModel = new \App\Models\FeeRecord();
    $dashboard['fee_summary'] = $feeRecordModel->getStudentSummary($studentId, $tenantId);
    
    echo json_encode(['success' => true, 'data' => $dashboard]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
```

**2.2 Update student.js Dashboard Renderer**

The [`renderDashboard()`](public/assets/js/student.js:1) function exists with mock data. Update it to:
1. Fetch real data from `/api/student/dashboard`
2. Display loading states
3. Handle errors gracefully
4. Refresh data periodically

### Phase 3: Classes & Timetable Module (4 hours)

**3.1 Classes Controller**

Create `app/Http/Controllers/Student/classes.php`:
- Today's timetable endpoint
- Weekly schedule endpoint  
- Academic calendar events endpoint
- Online class join link retrieval

**3.2 View Renderers in student.js**

```javascript
window.renderClassesToday = function() { /* ... */ }
window.renderClassesWeekly = function() { /* ... */ }
window.renderClassesCalendar = function() { /* ... */ }
```

### Phase 4: Attendance Module (4 hours)

**4.1 Attendance Controller**

Create `app/Http/Controllers/Student/attendance.php`:
```php
<?php
/**
 * Student Attendance API
 */

$action = $_GET['action'] ?? 'summary';

switch ($action) {
    case 'summary':
        // Overall attendance percentage
        break;
        
    case 'history':
        // Monthly detailed history
        break;
        
    case 'apply_leave':
        // Submit leave request
        break;
        
    case 'leave_status':
        // Check leave request status
        break;
}
```

**4.2 View Renderers**

```javascript
window.renderAttendanceSummary = function() { /* ... */ }
window.renderAttendanceHistory = function() { /* ... */ }
window.renderAttendanceLeave = function() { /* ... */ }
```

### Phase 5: Assignments Module (5 hours)

**5.1 Assignments Controller**

Create `app/Http/Controllers/Student/assignments.php`:
- List pending assignments
- Submit assignment (file upload)
- View graded assignments with teacher feedback
- Submission history

**5.2 Database Schema (if needed)**

```sql
CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    batch_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    title VARCHAR(255),
    description TEXT,
    due_date DATE,
    max_marks INT,
    attachment_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE assignment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_text TEXT,
    attachment_url VARCHAR(500),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    marks_obtained INT,
    feedback TEXT,
    graded_by INT,
    graded_at TIMESTAMP
);
```

### Phase 6: Exams & Results Module (6 hours)

**6.1 Exams Controller**

Create `app/Http/Controllers/Student/exams.php`:
- List available exams
- Get exam results with detailed breakdown
- Performance analytics/charts data
- Batch leaderboard
- Mock test questions

**6.2 View Renderers**

```javascript
window.renderExamsAvailable = function() { /* ... */ }
window.renderExamsResults = function() { /* ... */ }
window.renderExamsAnalytics = function() { /* ... */ }
window.renderExamsLeaderboard = function() { /* ... */ }
window.renderExamsMockTest = function() { /* ... */ }
```

### Phase 7: Fee Module Enhancement (3 hours)

**7.1 Update Existing Fees Controller**

Enhance [`app/Http/Controllers/Student/fees.php`](app/Http/Controllers/Student/fees.php:1):
- Add receipt download endpoint
- Add payment gateway integration (eSewa, Khalti)
- Add invoice generation

**7.2 View Renderers**

```javascript
window.renderFeeStatus = function() { /* ... */ }
window.renderFeePayments = function() { /* ... */ }
window.renderFeeReceipts = function() { /* ... */ }
```

### Phase 8: Study Materials Module (already implemented)

The study materials controller is already implemented at [`app/Http/Controllers/Student/study_materials.php`](app/Http/Controllers/Student/study_materials.php:1).

**Required: Add View Renderers**

```javascript
window.renderStudyNotes = function() { /* ... */ }
window.renderStudyPapers = function() { /* ... */ }
window.renderStudyBookmarks = function() { /* ... */ }
window.renderStudyDownloads = function() { /* ... */ }
```

### Phase 9: Library Module (3 hours)

**9.1 Library Controller**

Create `app/Http/Controllers/Student/library.php`:
- Currently borrowed books
- Book search
- Due date reminders
- Fine calculation

### Phase 10: Notices Module (3 hours)

**10.1 Notices Controller**

Create `app/Http/Controllers/Student/notices.php`:
- List institute announcements
- List batch-specific notices
- Mark notices as read
- Notification preferences

### Phase 11: Profile Module (4 hours)

**11.1 Profile Controller**

Create `app/Http/Controllers/Student/profile.php`:
- Get personal details
- Update personal details
- Academic history
- Document uploads
- Password change

**11.2 View Files**

Existing files:
- [`student-profile-view.php`](resources/views/student/student-profile-view.php:1)
- [`student-security-settings.php`](resources/views/student/student-security-settings.php:1)
- [`student-id-card-view.php`](resources/views/student/student-id-card-view.php:1)

### Phase 12: Integration & Testing (6 hours)

**12.1 Update student.js renderPage() Function**

Ensure all navigation routes are properly handled:

```javascript
function renderPage() {
    mainContent.innerHTML = '<div class="pg fu">Loading...</div>';
    
    // Dashboard
    if (activeNav === 'dashboard') {
        renderDashboard();
        return;
    }
    
    // Classes
    if (activeNav === 'classes-today') { renderClassesToday(); return; }
    if (activeNav === 'classes-weekly') { renderClassesWeekly(); return; }
    if (activeNav === 'classes-cal') { renderClassesCalendar(); return; }
    
    // Attendance
    if (activeNav === 'att-sum') { renderAttendanceSummary(); return; }
    if (activeNav === 'att-hist') { renderAttendanceHistory(); return; }
    if (activeNav === 'att-leave') { renderAttendanceLeave(); return; }
    
    // ... all other routes
    
    renderGenericPage();
}
```

**12.2 Testing Checklist**

- [ ] Student login works correctly
- [ ] Dashboard loads with real data
- [ ] All navigation items work
- [ ] Mobile responsive layout
- [ ] Dark/light theme support
- [ ] Offline capability for key features
- [ ] Push notifications for notices

## API Endpoints Summary

| Endpoint | Method | Description | Status |
|----------|--------|-------------|--------|
| `/api/student/dashboard` | GET | Dashboard data | 🔄 New |
| `/api/student/classes` | GET | Timetable data | 🔄 New |
| `/api/student/attendance` | GET/POST | Attendance & leave | 🔄 New |
| `/api/student/assignments` | GET/POST | Assignments | 🔄 New |
| `/api/student/exams` | GET | Exams & results | 🔄 New |
| `/api/student/fees` | GET | Fee details | ✅ Exists |
| `/api/student/study-materials` | GET | Study materials | ✅ Exists |
| `/api/student/library` | GET | Library info | 🔄 New |
| `/api/student/notices` | GET | Notices | 🔄 New |
| `/api/student/profile` | GET/POST | Profile data | 🔄 New |

## Timeline Estimate

| Phase | Tasks | Hours |
|-------|-------|-------|
| Phase 1 | Routes & Base Infrastructure | 4 |
| Phase 2 | Dashboard Module | 6 |
| Phase 3 | Classes & Timetable | 4 |
| Phase 4 | Attendance Module | 4 |
| Phase 5 | Assignments Module | 5 |
| Phase 6 | Exams & Results | 6 |
| Phase 7 | Fee Module Enhancement | 3 |
| Phase 8 | Study Materials Integration | 2 |
| Phase 9 | Library Module | 3 |
| Phase 10 | Notices Module | 3 |
| Phase 11 | Profile Module | 4 |
| Phase 12 | Integration & Testing | 6 |
| **Total** | | **50 hours** |

## Priority Matrix

### High Priority (Core Academic)
1. Dashboard - Student landing page
2. My Classes - Timetable viewing
3. Attendance - Self-monitoring
4. Fee Status - Financial transparency
5. Study Materials - Access to resources

### Medium Priority (Engagement)
6. Assignments - Submission system
7. Exams & Results - Performance tracking
8. Notices - Communication

### Lower Priority (Nice to Have)
9. Library - Book management
10. Mock Tests - Practice system
11. Leaderboard - Gamification

## Security Considerations

1. **Data Access Control**: Students can only access their own data
2. **Tenant Isolation**: Ensure tenant_id filtering on all queries
3. **File Upload Security**: Validate file types and sizes for assignments
4. **Payment Security**: Use secure payment gateway integrations
5. **Session Management**: Proper timeout and renewal

## Mobile-First Design

The Student Portal should be optimized for mobile usage:
- Responsive CSS already in place
- Touch-friendly navigation
- Offline access to study materials
- Push notifications for important updates
- Quick action buttons for common tasks

## Future Enhancements

1. **Mobile App**: PWA or native app wrapper
2. **Offline Mode**: Download materials for offline access
3. **AI Chatbot**: Academic assistance bot
4. **Parent Portal**: Guardian access to student progress
5. **Video Lectures**: Integrated learning management
