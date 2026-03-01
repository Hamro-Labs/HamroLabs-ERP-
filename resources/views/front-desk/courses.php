<?php
/**
 * Front Desk — Courses View
 * Brochure-style view for front desk to explain course offerings
 */

if (!isset($_GET['partial'])) {
    renderFrontDeskHeader();
    renderFrontDeskSidebar();
}

$db = getDBConnection();
$tenantId = $_SESSION['userData']['tenant_id'];

// Get courses
$stmt = $db->prepare("
    SELECT * FROM courses 
    WHERE tenant_id = :tid AND deleted_at IS NULL AND status = 'active'
    ORDER BY name ASC
");
$stmt->execute(['tid' => $tenantId]);
$courses = $stmt->fetchAll();
?>

<!-- Breadcrumbs -->
<div class="bc">
    <a href="javascript:goNav('dashboard')">Dashboard</a>
    <span class="bc-sep">/</span>
    <a href="javascript:goNav('academic','batches')">Academic</a>
    <span class="bc-sep">/</span>
    <span class="bc-cur">Course Brochure</span>
</div>

<div class="pg-head">
    <div class="pg-left">
        <div class="pg-ico" style="background:linear-gradient(135deg, #6C5CE7, #A855F7);">
            <i class="fa-solid fa-book-open"></i>
        </div>
        <div>
            <div class="pg-title">Course Brochure</div>
            <div class="pg-sub">Explore our educational programs and fee structures</div>
        </div>
    </div>
</div>

<div class="courses-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap:20px;">
    <?php foreach ($courses as $c): ?>
    <div class="card course-card" style="padding:0; overflow:hidden;">
        <div class="course-header" style="padding:20px; background:linear-gradient(135deg, #6C5CE7, #A855F7); color:#fff;">
            <div style="font-size:12px; opacity:0.8; text-transform:uppercase; letter-spacing:1px; font-weight:700;"><?php echo htmlspecialchars($c['course_code']); ?></div>
            <div style="font-size:20px; font-weight:800; margin-top:5px;"><?php echo htmlspecialchars($c['name']); ?></div>
        </div>
        <div style="padding:20px;">
            <div style="font-size:14px; color:#64748b; line-height:1.6; margin-bottom:20px;">
                <?php echo nl2br(htmlspecialchars($c['description'] ?? 'No description available for this course. Contact administration for details.')); ?>
            </div>
            
            <div class="course-meta" style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f1f5f9; padding-top:15px;">
                <div>
                    <div style="font-size:11px; color:#94a3b8; text-transform:uppercase;">Duration</div>
                    <div style="font-weight:700; color:#1e293b;"><?php echo htmlspecialchars($c['duration'] ?? 'N/A'); ?></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:11px; color:#94a3b8; text-transform:uppercase;">Fee</div>
                    <div style="font-weight:700; color:#10b981;">NPR <?php echo number_format($c['base_fee'] ?? 0); ?></div>
                </div>
            </div>
            
            <button class="btn" style="width:100%; justify-content:center; margin-top:20px;" onclick="goNav('inquiries', 'inq-add')">
                <i class="fa-solid fa-plus-circle"></i> Create Inquiry
            </button>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php if (empty($courses)): ?>
    <div class="card" style="grid-column: 1 / -1; text-align:center; padding:60px;">
        <i class="fa-solid fa-book-open" style="font-size:48px; color:#cbd5e1; margin-bottom:20px;"></i>
        <div style="font-size:18px; color:#64748b;">No active courses available.</div>
    </div>
    <?php endif; ?>
</div>
