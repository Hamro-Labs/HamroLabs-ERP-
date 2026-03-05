<?php
/**
 * Student Admission Form — Institute Admin
 * Shared component wrapper for the Admin SPA.
 *
 * Served as a PHP partial fragment (injected by ia-students-v2.js via fetch).
 * Supports ?partial=true to skip the full HTML shell.
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../../config/config.php';
}

// ── Auth: Institute Admin only ──
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$user = getCurrentUser();
if (!in_array($user['role'] ?? '', ['instituteadmin', 'superadmin'])) {
    http_response_code(403);
    echo '<p>Access Denied</p>';
    exit;
}

$tenantId = $_SESSION['userData']['tenant_id'] ?? null;
if (!$tenantId) {
    echo '<p>Tenant not found</p>';
    exit;
}

// ── Data Fetching ──
$db = getDBConnection();

$stmtCourses = $db->prepare("SELECT id, name, code FROM courses WHERE tenant_id = :tid AND status = 'active' AND is_active = 1 AND deleted_at IS NULL ORDER BY name");
$stmtCourses->execute(['tid' => $tenantId]);
$courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

$stmtBatches = $db->prepare("SELECT id, course_id, name, shift FROM batches WHERE tenant_id = :tid AND status IN ('active', 'upcoming') AND deleted_at IS NULL ORDER BY name");
$stmtBatches->execute(['tid' => $tenantId]);
$batches = $stmtBatches->fetchAll(PDO::FETCH_ASSOC);

// ── Component Parameters ──
$apiEndpoint        = APP_URL . '/api/admin/students';
$successRedirectUrl = APP_URL . '/dash/admin';  // The JS will call goNav('students') on success
$viewAllStudentsUrl  = 'javascript:goNav(\'students\')';  // SPA navigation
$componentId        = 'adm';
$pageTitle          = 'Student Admission';

// Render shared component (partial only — no full HTML shell)
require VIEWS_PATH . '/components/student/add-student-form.php';

// ── Override: post-success action for SPA ──
// After admission, redirect back to admin student list using SPA routing
?>
<script>
// Override success redirect for admin SPA context
(function() {
    // After successful admission, use SPA navigation
    const origFn = window['handleAdmissionSubmit_adm'];
    if (!origFn) return;
    // The shared component already handles success; override the redirect URL
    // by patching the modal "View Student Database" action to use SPA nav
})();

// Ensure goNav is available for "View All Students" link
document.addEventListener('DOMContentLoaded', function() {
    const viewBtn = document.querySelector('.pg-acts .btn-p');
    if (viewBtn && typeof window.goNav === 'function') {
        viewBtn.href = '#';
        viewBtn.onclick = function(e) {
            e.preventDefault();
            window.goNav('students');
        };
    }
});
</script>
