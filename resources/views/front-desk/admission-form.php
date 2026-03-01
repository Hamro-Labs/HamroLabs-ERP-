<?php
/**
 * Student Admission Form — Premium Edition
 * Mobile-First, 101% Responsive, Glassmorphic Design
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../../config/config.php';
}

$pageTitle = 'Student Admission';
require_once VIEWS_PATH . '/layouts/header_1.php';
require_once __DIR__ . '/sidebar.php';

// Data Fetching Logic
$db = getDBConnection();
$tenantId = $_SESSION['userData']['tenant_id'];

$stmtCourses = $db->prepare("SELECT id, name, code FROM courses WHERE tenant_id = :tid AND status = 'active' AND deleted_at IS NULL ORDER BY name");
$stmtCourses->execute(['tid' => $tenantId]);
$courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

$stmtBatches = $db->prepare("SELECT id, course_id, name, shift FROM batches WHERE tenant_id = :tid AND deleted_at IS NULL ORDER BY name");
$stmtBatches->execute(['tid' => $tenantId]);
$batches = $stmtBatches->fetchAll(PDO::FETCH_ASSOC);
?>

<?php renderFrontDeskHeader(); ?>
<?php renderFrontDeskSidebar('admission-form'); ?>

<style>
/* ── DESIGN SYSTEM & TOKENS ─────────────────────────── */
:root {
    --primary: #00b894;
    --primary-dark: #009e7e;
    --primary-light: rgba(0, 184, 148, 0.1);
    --secondary: #6c5ce7;
    --accent: #ff7675;
    --bg-main: #f8fafc;
    --card-bg: rgba(255, 255, 255, 0.9);
    --text-main: #1e293b;
    --text-muted: #64748b;
    --border: #e2e8f0;
    --radius-lg: 24px;
    --radius-md: 16px;
    --radius-sm: 12px;
    --shadow-premium: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --glass-blur: blur(12px);
}

/* ── LAYOUT & BASE ────────────────────────────────── */
.pg-container { padding: 1rem; max-width: 1100px; margin: 0 auto; animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.pg-head { margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.pg-title-group { display: flex; align-items: center; gap: 1.25rem; }
.pg-icon-box { width: 54px; height: 54px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); box-shadow: 0 8px 16px rgba(0, 184, 148, 0.2); }
.pg-title { font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0; letter-spacing: -0.02em; }
.pg-subtitle { font-size: 0.875rem; color: var(--text-muted); margin: 2px 0 0; }

/* ── PREMIUM CARD ────────────────────────────────── */
.adm-card {
    background: var(--card-bg);
    backdrop-filter: var(--glass-blur);
    border: 1px solid rgba(255, 255, 255, 0.6);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-premium);
    overflow: hidden;
}

.adm-banner {
    background: linear-gradient(135deg, var(--primary-dark), var(--primary));
    padding: 3rem 1.5rem;
    text-align: center;
    color: #fff;
    position: relative;
}

.adm-banner::after {
    content: ''; position: absolute; top: -10%; right: -5%; width: 200px; height: 200px;
    background: rgba(255, 255, 255, 0.1); border-radius: 50%; pointer-events: none;
}

.adm-banner-icon { width: 60px; height: 60px; background: rgba(255, 255, 255, 0.2); border-radius: 18px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 26px; }

/* ── FORM ELEMENTS ───────────────────────────────── */
.adm-body { padding: 1.5rem; }
@media (min-width: 768px) { .adm-body { padding: 3rem; } }

.section-title { font-size: 1.1rem; font-weight: 800; color: var(--primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; }
.section-title i { opacity: 0.8; }

.form-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
@media (min-width: 640px) { .form-grid.cols-2 { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 992px) { .form-grid.cols-3 { grid-template-columns: repeat(3, 1fr); } }

.f-group { margin-bottom: 0.5rem; }
.f-label { display: block; font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 10px; margin-left: 2px; }
.f-label.required::after { content: '*'; color: var(--accent); margin-left: 4px; }

.input-box { position: relative; }
.input-box i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px; pointer-events: none; transition: var(--transition); }

.fi { 
    width: 100%; padding: 13px 16px 13px 46px; border: 1.5px solid var(--border); border-radius: 14px; 
    font-size: 14px; font-weight: 500; outline: none; transition: var(--transition); 
    background: #fff; color: var(--text-main); box-sizing: border-box; font-family: inherit;
}
.fi:focus { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-light); background: #fff; }
.fi:focus + i { color: var(--primary); }

.fi-select { padding-left: 46px; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 16px center; background-size: 16px; }

textarea.fi { padding-left: 16px; resize: vertical; min-height: 80px; }

/* ── PRIORITY SECTION ─────────────────────────────── */
.priority-box { background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 2rem; position: relative; }
.priority-box::before { content: 'PRIORITY'; position: absolute; top: -10px; left: 20px; background: var(--primary); color: #fff; font-size: 10px; font-weight: 900; padding: 2px 10px; border-radius: 20px; letter-spacing: 0.05em; }

/* ── SECURITY SECTION ─────────────────────────────── */
.security-box { background: #fff1f2; border: 1.5px solid #fecdd3; border-radius: var(--radius-md); padding: 1.5rem; margin-top: 2rem; }

/* ── BUTTONS ───────────────────────────────────────── */
.btn-submit { width: 100%; padding: 16px; background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; border: none; border-radius: 16px; font-size: 16px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 12px; transition: var(--transition); box-shadow: 0 10px 20px -5px rgba(0, 184, 148, 0.4); }
.btn-submit:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 15px 30px -5px rgba(0, 184, 148, 0.5); }
.btn-submit:active { transform: translateY(0); }
.btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }

.btn-secondary { padding: 12px 20px; background: #fff; border: 1.5px solid var(--border); border-radius: 12px; color: var(--text-main); font-weight: 700; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: var(--transition); }
.btn-secondary:hover { border-color: var(--text-muted); background: #f8fafc; }

/* ── MODAL PREMIUM ─────────────────────────────────── */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(8px); z-index: 10000; align-items: center; justify-content: center; padding: 1rem; animation: modalFade 0.3s ease-out; }
@keyframes modalFade { from { opacity: 0; } to { opacity: 1; } }

.modal-content { background: #fff; border-radius: 24px; padding: 2.5rem; max-width: 440px; width: 100%; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); transform: scale(0.9); animation: modalPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
@keyframes modalPop { to { transform: scale(1); } }

.modal-icon { width: 72px; height: 72px; border-radius: 50%; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center; font-size: 30px; color: #fff; box-shadow: 0 8px 20px rgba(0, 184, 148, 0.3); }
</style>

<main class="main" id="mainContent">
<div class="pg-container">

    <!-- Header Section -->
    <header class="pg-head">
        <div class="pg-title-group">
            <div class="pg-icon-box">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
                <h1 class="pg-title">Student Admission</h1>
                <p class="pg-subtitle">New enrollment gateway</p>
            </div>
        </div>
        <div>
            <a href="<?= APP_URL ?>/dash/front-desk/students" class="btn-secondary">
                <i class="fa-solid fa-list-ul"></i> All Students
            </a>
        </div>
    </header>

    <!-- Unified Form Card -->
    <div class="adm-card">
        <div class="adm-banner">
            <div class="adm-banner-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h2 style="font-size: 1.75rem; font-weight: 900; margin: 0; letter-spacing: -0.02em;">Admission Form</h2>
            <p style="opacity: 0.85; font-size: 14px; margin: 8px 0 0;">Fill carefully to complete the registration process</p>
        </div>

        <div class="adm-body">
            <form id="formAdmission" onsubmit="handleAdmissionSubmit(event)">
                
                <!-- Priority Section: Course & Batch -->
                <div class="priority-box">
                    <h3 class="section-title" style="margin-bottom: 1.25rem;">
                        <i class="fas fa-book-open"></i> Academic Placement
                    </h3>
                    <div class="form-grid cols-2">
                        <div class="f-group">
                            <label class="f-label required">Target Course</label>
                            <div class="input-box">
                                <i class="fas fa-layer-group"></i>
                                <select name="course_id" id="selCourse" class="fi fi-select" onchange="syncBatches(this.value)" required>
                                    <option value="">Select Course</option>
                                    <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="f-group">
                            <label class="f-label required">Target Batch</label>
                            <div class="input-box">
                                <i class="fas fa-users-viewfinder"></i>
                                <select name="batch_id" id="selBatch" class="fi fi-select" required>
                                    <option value="">Choose Course First</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <h3 class="section-title"><i class="fas fa-id-card-clip"></i> Primary Information</h3>
                <div class="form-grid cols-2">
                    <div class="f-group">
                        <label class="f-label required">Full Name</label>
                        <div class="input-box">
                            <i class="fas fa-user-tag"></i>
                            <input type="text" name="full_name" class="fi" placeholder="e.g. Roshan Sharma" required>
                        </div>
                    </div>
                    <div class="f-group">
                        <label class="f-label required">Contact Number</label>
                        <div class="input-box">
                            <i class="fas fa-phone-volume"></i>
                            <input type="tel" name="contact_number" class="fi" placeholder="98XXXXXXXX" pattern="[0-9]{10}" required>
                        </div>
                    </div>
                    <div class="f-group">
                        <label class="f-label required">Email Address</label>
                        <div class="input-box">
                            <i class="fas fa-envelope-circle-check"></i>
                            <input type="email" name="email" class="fi" placeholder="email@domain.com" required>
                        </div>
                    </div>
                    <div class="f-group">
                        <label class="f-label required">Gender</label>
                        <div class="input-box">
                            <i class="fas fa-venus-mars"></i>
                            <select name="gender" class="fi fi-select" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Secondary Information -->
                <h3 class="section-title" style="margin-top: 2rem;"><i class="fas fa-folder-plus"></i> Additional Details</h3>
                <div class="form-grid cols-3">
                    <div class="f-group">
                        <label class="f-label required">Date of Birth (AD)</label>
                        <input type="date" name="dob_ad" class="fi" onchange="handleDobSync(this.value)" style="padding-left: 20px;" required>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Date of Birth (BS)</label>
                        <input type="text" name="dob_bs" id="inpDobBs" class="fi" placeholder="YYYY-MM-DD" style="padding-left: 20px;">
                    </div>
                    <div class="f-group">
                        <label class="f-label">Blood Group</label>
                        <select name="blood_group" class="fi" style="padding-left: 20px;">
                            <option value="">Unknown</option>
                            <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                            <option value="<?= $bg ?>"><?= $bg ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-grid cols-2" style="margin-top: 1.25rem;">
                    <div class="f-group">
                        <label class="f-label required">Guardian Name</label>
                        <div class="input-box">
                            <i class="fas fa-user-shield"></i>
                            <input type="text" name="father_name" class="fi" placeholder="Guardian's Name" required>
                        </div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Citizenship / ID No.</label>
                        <div class="input-box">
                            <i class="fas fa-fingerprint"></i>
                            <input type="text" name="citizenship_no" class="fi" placeholder="ID Number (Optional)">
                        </div>
                    </div>
                </div>

                <!-- Address & Qualification -->
                <div class="form-grid cols-2" style="margin-top: 1.25rem;">
                    <div class="f-group">
                        <label class="f-label required">Permanent Address</label>
                        <textarea name="permanent_address" class="fi" placeholder="State, District, Municipality..." required></textarea>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Temporary Address</label>
                        <textarea name="temporary_address" class="fi" placeholder="Current stay (if different)"></textarea>
                    </div>
                </div>

                <div class="f-group" style="margin-top: 1.25rem;">
                    <label class="f-label">Academic Background</label>
                    <textarea name="academic_qualification" class="fi" placeholder="e.g. SEE (2080), +2 Science (2082)..."></textarea>
                </div>

                <!-- Account Security -->
                <div class="security-box">
                    <div style="display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 1.5rem;">
                        <div style="width: 44px; height: 44px; background: #e11d48; color: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 10px rgba(225, 29, 72, 0.2);">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; font-weight: 800; color: #1e293b; font-size: 15px;">Login Credentials</h4>
                            <p style="margin: 4px 0 0; font-size: 12px; color: #be123c;">Required for student portal access</p>
                        </div>
                    </div>
                    <div class="form-grid cols-2">
                        <div class="f-group">
                            <div class="input-box">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" id="inpPass" class="fi" placeholder="Portals Password" minlength="8" required>
                                <span onclick="togglePassView('inpPass')" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer; padding: 5px;">
                                    <i class="fas fa-eye" id="inpPassEye"></i>
                                </span>
                            </div>
                        </div>
                        <div class="f-group">
                            <div class="input-box">
                                <i class="fas fa-lock-open"></i>
                                <input type="password" name="confirm_password" id="inpConfPass" class="fi" placeholder="Confirm Password" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submission UI -->
                <div style="margin-top: 3rem;">
                    <button type="submit" id="btnSubmitAdm" class="btn-submit">
                        <i class="fas fa-circle-check"></i> Register & Finalize Admission
                    </button>
                    <p style="text-align: center; font-size: 11px; color: var(--text-muted); margin-top: 1.5rem;">
                        By submitting, you agree to the institution's terms & conditions.
                    </p>
                </div>
            </form>
        </div>

        <footer style="padding: 1.5rem; background: #f8fafc; border-top: 1px solid var(--border); text-align: center; color: var(--text-muted); font-size: 11px; font-weight: 600; letter-spacing: 0.05em;">
            POWERED BY <span style="color: var(--primary);">HAMRO LABS ACADEMIC ERP</span> • V2.5
        </footer>
    </div>
</div>
</main>

<!-- SUCCESS DIALOG -->
<div class="modal-overlay" id="successDialog">
    <div class="modal-content">
        <div class="modal-icon" id="diagIcon"></div>
        <h3 id="diagTitle" style="font-size: 22px; font-weight: 900; color: #0f172a; margin: 0 0 10px;"></h3>
        <div id="diagBody" style="font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 2rem;"></div>
        <div id="diagActions" style="display: flex; flex-direction: column; gap: 10px;"></div>
    </div>
</div>

<script>
/* ── JAVASCRIPT LOGIC (ROBUST & CLEAN) ──────────────── */
<?php
$user = $_SESSION['userData'] ?? [];
echo "window.currentUser = " . json_encode($user) . ";\n";
echo "window.APP_URL = '" . APP_URL . "';\n";
?>

const BATCH_DATA = <?= json_encode($batches) ?>;

// Dynamic Batch Sync
function syncBatches(courseId) {
    const sel = document.getElementById('selBatch');
    if (!sel) return;
    sel.innerHTML = '<option value="">— Select Batch —</option>';
    if (!courseId) return;
    
    const matches = BATCH_DATA.filter(b => b.course_id == courseId);
    if (!matches.length) {
        sel.innerHTML = '<option value="">No active batches</option>';
        return;
    }
    matches.forEach(b => {
        sel.innerHTML += `<option value="${b.id}">${b.name} (${b.shift})</option>`;
    });
}

// Date Conversion
async function handleDobSync(val) {
    if (!val) return;
    try {
        const res = await fetch(`${APP_URL}/api/admin/date-convert?ad=${encodeURIComponent(val)}`);
        const data = await res.json();
        if (data.success && data.bs) {
            document.getElementById('inpDobBs').value = data.bs;
        }
    } catch(err) { console.error("DOB Sync Error:", err); }
}

// Password Visibility
function togglePassView(id) {
    const inp = document.getElementById(id);
    const ico = document.getElementById(id + 'Eye');
    if (!inp || !ico) return;
    const isPass = inp.type === 'password';
    inp.type = isPass ? 'text' : 'password';
    ico.className = isPass ? 'fas fa-eye-slash' : 'fas fa-eye';
}

// ── FORM SUBMISSION ───────────────────────────────────
async function handleAdmissionSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitAdm');
    if (btn.disabled) return;
    
    const form = e.target;
    
    // Validation
    if (form.password.value !== form.confirm_password.value) {
        Swal.fire({ title: 'Security Issue', text: 'Passwords mismatch. Please correct.', icon: 'warning', confirmButtonColor: '#00b894' });
        return;
    }

    btn.disabled = true;
    const oldText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Enrollment...';

    const payload = {
        full_name:              form.full_name.value.trim(),
        contact_number:         form.contact_number.value.trim(),
        email:                  form.email.value.trim(),
        password:               form.password.value,
        batch_id:               form.batch_id.value,
        dob_ad:                 form.dob_ad.value,
        dob_bs:                 form.dob_bs.value,
        gender:                 form.gender.value,
        blood_group:            form.blood_group.value,
        father_name:            form.father_name.value.trim(),
        citizenship_no:         form.citizenship_no.value.trim(),
        permanent_address:      JSON.stringify({ address: form.permanent_address.value.trim() }),
        temporary_address:      form.temporary_address.value.trim() ? JSON.stringify({ address: form.temporary_address.value.trim() }) : null,
        academic_qualification: form.academic_qualification.value.trim(),
        registration_status:    'fully_registered'
    };

    try {
        const res = await fetch(`${APP_URL}/api/frontdesk/students`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const result = await res.json();

        if (result.success) {
            showDiag('success', 'Registration Done!', 
                `<p style="margin-bottom:1rem;">Student <strong>${payload.full_name}</strong> is now enrolled.</p>
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:14px; padding:1.25rem; text-align:left;">
                    <div style="margin-bottom:6px;"><span style="opacity:0.6; width:70px; display:inline-block;">Portal:</span> <strong>${payload.email}</strong></div>
                    <div><span style="opacity:0.6; width:70px; display:inline-block;">Pass:</span> <code style="background:#fff; padding:2px 6px; border-radius:6px; border:1px solid #dcfce7;">${payload.password}</code></div>
                </div>`,
                [
                    { label: 'View Student Database', click: `window.location.href = '${APP_URL}/dash/front-desk/students'`, style: 'background:linear-gradient(135deg,#00b894,#009e7e);color:#fff;' },
                    { label: 'Enroll Another Student', click: `closeDiag(); document.getElementById('formAdmission').reset();`, style: 'background:#f1f5f9;color:#1e293b;' }
                ]
            );
            form.reset();
        } else {
            Swal.fire({ title: 'Admission Failed', text: result.message || 'Verification error.', icon: 'error', confirmButtonColor: '#00b894' });
        }
    } catch (err) {
        Swal.fire('Connection Error', 'Failed to reach server.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = oldText;
    }
}

// ── CUSTOM DIALOG ─────────────────────────────────────
function showDiag(type, title, html, actions) {
    const overlay = document.getElementById('successDialog');
    const icon    = document.getElementById('diagIcon');
    if (!overlay || !icon) return;

    icon.style.background = type === 'success' ? 'linear-gradient(135deg,#00b894,#009e7e)' : 'linear-gradient(135deg,#ff7675,#e11d48)';
    icon.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'times'}"></i>`;
    document.getElementById('diagTitle').textContent = title;
    document.getElementById('diagBody').innerHTML = html;
    
    document.getElementById('diagActions').innerHTML = actions.map(a =>
        `<button onclick="${a.click}" class="btn-submit" style="height:48px; font-size:14px; box-shadow:none; ${a.style}">${a.label}</button>`
    ).join('');

    overlay.style.display = 'flex';
}

function closeDiag() { document.getElementById('successDialog').style.display = 'none'; }
document.getElementById('successDialog')?.addEventListener('click', (e) => { if (e.target === e.currentTarget) closeDiag(); });

// Initialization
window.addEventListener('DOMContentLoaded', () => {
    console.log("Refactored Premium Admission UI Initialized.");
});
</script>

<?php
renderSuperAdminCSS();
echo '<script src="' . APP_URL . '/public/assets/js/frontdesk.js"></script>';
?>
</body>
</html>
