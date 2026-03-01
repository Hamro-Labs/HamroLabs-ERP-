<?php
/**
 * Hamro ERP — Plan Assignment Page
 * Refactored to match Super Admin layout and design system.
 */

require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pdo = getDBConnection();

// Fetch summary counts for plans
$planCounts = $pdo->query("SELECT plan, COUNT(*) as count FROM tenants GROUP BY plan")->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch all institutes for the table
$tenants = $pdo->query("SELECT * FROM tenants ORDER BY name ASC")->fetchAll();

$pageTitle = 'Plan Assignment';
$activePage = 'plan-assign.php';
?>

<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<main class="main" id="mainContent">
    <div class="pg fu">
        <!-- Page Header -->
        <div class="pg-head">
            <div class="pg-left">
                <div class="pg-ico ic-t" style="background:var(--soft-purple); color:var(--purple);"><i class="fa-solid fa-layer-group"></i></div>
                <div>
                    <div class="pg-title">Plan Assignment</div>
                    <div class="pg-sub">Assign or change subscription plans for registered institutes.</div>
                </div>
            </div>
            <div class="pg-acts">
                <button class="btn bs" onclick="SuperAdmin.showNotification('Exporting project data...', 'info')"><i class="fa-solid fa-download"></i> Export</button>
                <button class="btn bt" onclick="openBulkModal()"><i class="fa-solid fa-bolt"></i> Bulk Assign</button>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="sg" style="margin-bottom:24px;">
            <div class="sc">
                <div class="sc-top">
                    <div class="sc-ico" style="background:#f0fdf4; color:#16a34a;"><i class="fa-solid fa-seedling"></i></div>
                    <span class="tag bg-t">Starter</span>
                </div>
                <div class="sc-val"><?php echo $planCounts['starter'] ?? 0; ?></div>
                <div class="sc-lbl">Active Institutes</div>
            </div>
            <div class="sc">
                <div class="sc-top">
                    <div class="sc-ico" style="background:#eff6ff; color:#3b82f6;"><i class="fa-solid fa-rocket"></i></div>
                    <span class="tag bg-b">Growth</span>
                </div>
                <div class="sc-val"><?php echo $planCounts['growth'] ?? 0; ?></div>
                <div class="sc-lbl">Active Institutes</div>
            </div>
            <div class="sc">
                <div class="sc-top">
                    <div class="sc-ico" style="background:var(--soft-purple); color:var(--purple);"><i class="fa-solid fa-star"></i></div>
                    <span class="tag bg-p">Pro</span>
                </div>
                <div class="sc-val"><?php echo ($planCounts['professional'] ?? 0) + ($planCounts['pro'] ?? 0); ?></div>
                <div class="sc-lbl">Active Institutes</div>
            </div>
            <div class="sc">
                <div class="sc-top">
                    <div class="sc-ico" style="background:#fef3c7; color:#d97706;"><i class="fa-solid fa-crown"></i></div>
                    <span class="tag bg-y">Enterprise</span>
                </div>
                <div class="sc-val"><?php echo $planCounts['enterprise'] ?? 0; ?></div>
                <div class="sc-lbl">Active Institutes</div>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="tbl-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <div class="ct"><i class="fa-solid fa-building"></i> Plan Assignments</div>
                <div style="display:flex; gap:10px;">
                    <input type="text" class="form-inp" placeholder="Search institute..." style="width:220px;" oninput="filterTable(this.value)">
                    <select class="form-inp" style="width:160px; appearance:auto;" onchange="filterPlan(this.value)">
                        <option value="">All Plans</option>
                        <option value="starter">Starter</option>
                        <option value="growth">Growth</option>
                        <option value="professional">Professional</option>
                        <option value="enterprise">Enterprise</option>
                    </select>
                </div>
            </div>
            
            <div class="tw" style="border:none; border-radius:0;">
                <table id="planTable">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" onchange="selectAll(this)"></th>
                            <th>Institute</th>
                            <th>Current Plan</th>
                            <th>Students</th>
                            <th>Renewal Date</th>
                            <th>Status</th>
                            <th>Quick Assign</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="planTbody">
                        <!-- Rendered by JS -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- Bulk Assign Drawer (Modern Sidebar Style) -->
<div class="sb-overlay" id="bulkOverlay" onclick="closeBulkModal()"></div>
<div id="bulkDrawer" style="position:fixed; top:0; right:-400px; width:400px; height:100vh; background:#fff; z-index:1100; box-shadow:-10px 0 30px rgba(0,0,0,0.1); transition:0.3s cubic-bezier(0.4, 0, 0.2, 1); display:flex; flex-direction:column;">
    <div style="padding:20px; border-bottom:1px solid var(--cb); display:flex; align-items:center; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:36px; height:36px; background:var(--sa-primary-lt); color:var(--sa-primary); border-radius:10px; display:flex; align-items:center; justify-content:center;"><i class="fa-solid fa-bolt"></i></div>
            <div>
                <div style="font-weight:700; font-size:15px;">Bulk Assignment</div>
                <div style="font-size:11px; color:var(--tl);">Update multiple institutes at once</div>
            </div>
        </div>
        <button class="btn-icon" onclick="closeBulkModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div style="flex:1; overflow-y:auto; padding:20px;">
        <div class="form-row" style="margin-bottom:20px;">
            <label class="form-lbl">Target Group</label>
            <select class="form-inp" style="appearance:auto;">
                <option>All Active Institutes</option>
                <option>Starter Plan Institutes</option>
                <option>Trial Institutes</option>
            </select>
        </div>
        <div class="form-row" style="margin-bottom:20px;">
            <label class="form-lbl">Apply New Plan</label>
            <select class="form-inp" style="appearance:auto;">
                <option value="starter">🌱 Starter</option>
                <option value="growth">🚀 Growth</option>
                <option value="professional">⭐ Professional</option>
                <option value="enterprise">👑 Enterprise</option>
            </select>
        </div>
        <div class="form-row" style="margin-bottom:20px;">
            <label class="form-lbl">Effective Date</label>
            <input type="date" class="form-inp">
        </div>
        <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:12px; padding:15px; margin-top:10px; display:flex; gap:10px;">
            <i class="fa-solid fa-triangle-exclamation" style="color:#d97706; margin-top:2px;"></i>
            <div style="font-size:12px; color:#92400e; line-height:1.4;">
                <strong>Warning:</strong> This will override existing plan assignments. Active billing will be prorated.
            </div>
        </div>
    </div>
    <div style="padding:20px; border-top:1px solid var(--cb); display:flex; gap:12px;">
        <button class="btn bs" style="flex:1;" onclick="closeBulkModal()">Cancel</button>
        <button class="btn bt" style="flex:1;" onclick="applyBulk()">Apply Change</button>
    </div>
</div>

<!-- Assign Modal -->
<div id="assignModal" style="position:fixed; top:50%; left:50%; transform:translate(-50%,-50%) scale(0.9); background:#fff; border-radius:16px; padding:24px; width:90%; max-width:400px; z-index:1200; opacity:0; visibility:hidden; transition:0.2s; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
        <h3 style="font-size:16px; font-weight:800;">Change Plan</h3>
        <button class="btn-icon" onclick="closeAssignModal()"><i class="fa fa-times"></i></button>
    </div>
    <div id="modalInstName" style="font-size:13px; color:var(--text-body); margin-bottom:20px; padding:12px; background:var(--bg); border-radius:10px;"></div>
    <div class="form-row" style="margin-bottom:20px;">
        <label class="form-lbl">Select New Plan</label>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;" id="planCards">
            <div class="plan-card" data-plan="starter" onclick="selectPlan(this)">
                <div style="font-size:18px;">🌱</div>
                <div style="font-weight:700; color:#16a34a;">Starter</div>
            </div>
            <div class="plan-card" data-plan="growth" onclick="selectPlan(this)">
                <div style="font-size:18px;">🚀</div>
                <div style="font-weight:700; color:#3b82f6;">Growth</div>
            </div>
            <div class="plan-card" data-plan="professional" onclick="selectPlan(this)">
                <div style="font-size:18px;">⭐</div>
                <div style="font-weight:700; color:var(--purple);">Professional</div>
            </div>
            <div class="plan-card" data-plan="enterprise" onclick="selectPlan(this)">
                <div style="font-size:18px;">👑</div>
                <div style="font-weight:700; color:#d97706;">Enterprise</div>
            </div>
        </div>
    </div>
    <div style="display:flex; gap:10px;">
        <button class="btn bs" style="flex:1" onclick="closeAssignModal()">Cancel</button>
        <button class="btn bt" style="flex:1" onclick="saveIndividualPlan()">Save Plan</button>
    </div>
</div>

<style>
    .plan-card {
        border: 2px solid var(--cb);
        border-radius: 12px;
        padding: 12px;
        cursor: pointer;
        transition: 0.2s;
        text-align: center;
    }
    .plan-card:hover { border-color: var(--sa-primary-h); background: var(--bg); }
    .plan-card.active { border-color: var(--sa-primary); background: var(--sa-primary-lt); }

    .plan-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        display: inline-block;
    }
    .plan-starter { background: #f0fdf4; color: #16a34a; }
    .plan-growth { background: #eff6ff; color: #3b82f6; }
    .plan-professional { background: var(--soft-purple); color: var(--purple); }
    .plan-enterprise { background: #fffbeb; color: #d97706; }

    .tag-active { background: #dcfce7; color: #16a34a; }
    .tag-trial { background: #eff6ff; color: #3b82f6; }
    .tag-suspended { background: #fee2e2; color: var(--red); }
</style>

<script>
const institutes = <?php echo json_encode($tenants); ?>;

const planMeta = {
    starter: { label: "🌱 Starter", cls: "plan-starter" },
    growth: { label: "🚀 Growth", cls: "plan-growth" },
    professional: { label: "⭐ Professional", cls: "plan-professional" },
    enterprise: { label: "👑 Enterprise", cls: "plan-enterprise" }
};

function renderTable(data) {
    const tbody = document.getElementById('planTbody');
    tbody.innerHTML = data.map(inst => `
        <tr style="border-bottom:1px solid var(--cb);">
            <td style="padding:14px 16px;"><input type="checkbox" class="row-cb" data-id="${inst.id}"></td>
            <td style="padding:14px 16px;">
                <div style="font-weight:700; font-size:13px; color:var(--td);">${inst.name}</div>
                <div style="font-size:11px; color:var(--tl);">${inst.subdomain}.hamrolabs.com.np</div>
            </td>
            <td style="padding:14px 16px;">
                <span class="plan-badge ${planMeta[inst.plan]?.cls || 'plan-starter'}">${planMeta[inst.plan]?.label || inst.plan}</span>
            </td>
            <td style="padding:14px 16px; font-weight:700; font-size:13px; color:var(--td);">${inst.student_limit.toLocaleString()}</td>
            <td style="padding:14px 16px; font-size:13px; color:var(--tb);">${inst.created_at.split(' ')[0]}</td>
            <td style="padding:14px 16px;">
                <span class="tag tag-${inst.status}" style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;">${inst.status.charAt(0).toUpperCase() + inst.status.slice(1)}</span>
            </td>
            <td style="padding:14px 16px;">
                <select class="form-inp" style="font-size:12px; padding:6px 10px; width:auto; appearance:auto;" onchange="quickAssign(${inst.id}, this.value)">
                    <option value="">Quick assign...</option>
                    <option value="starter" ${inst.plan==='starter'?'selected':''}>Starter</option>
                    <option value="growth" ${inst.plan==='growth'?'selected':''}>Growth</option>
                    <option value="professional" ${inst.plan==='professional'?'selected':''}>Professional</option>
                    <option value="enterprise" ${inst.plan==='enterprise'?'selected':''}>Enterprise</option>
                </select>
            </td>
            <td style="padding:14px 16px; text-align:center;">
                <button class="btn-icon" onclick="openAssignModal(${inst.id})"><i class="fa fa-pencil"></i></button>
            </td>
        </tr>
    `).join('');
}

function quickAssign(id, plan) {
    if(!plan) return;
    const formData = new FormData();
    formData.append('id', id);
    formData.append('plan', plan);

    SuperAdmin.showNotification('Updating plan...', 'info');
    fetch('../../api/update_plan.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            SuperAdmin.showNotification('Plan updated successfully!', 'success');
            // Update local state and re-render
            const inst = institutes.find(i => i.id == id);
            if(inst) inst.plan = plan;
            renderTable(institutes);
        } else {
            SuperAdmin.showNotification(data.message, 'error');
        }
    });
}

function filterTable(val) {
    const filtered = institutes.filter(i => i.name.toLowerCase().includes(val.toLowerCase()) || i.sub.toLowerCase().includes(val.toLowerCase()));
    renderTable(filtered);
}

function filterPlan(val) {
    const filtered = val ? institutes.filter(i => i.plan === val) : institutes;
    renderTable(filtered);
}

let currentInstId = null;
function openAssignModal(id) {
    const inst = institutes.find(i => i.id === id);
    currentInstId = id;
    document.getElementById('modalInstName').innerHTML = `Updating plan for <strong>${inst.name}</strong>`;
    
    document.querySelectorAll('.plan-card').forEach(c => {
        c.classList.remove('active');
        if(c.dataset.plan === inst.plan) c.classList.add('active');
    });

    document.getElementById('assignModal').style.visibility = 'visible';
    document.getElementById('assignModal').style.opacity = '1';
    document.getElementById('assignModal').style.transform = 'translate(-50%,-50%) scale(1)';
    document.getElementById('bulkOverlay').classList.add('active'); // Reuse overlay
}

function closeAssignModal() {
    document.getElementById('assignModal').style.visibility = 'hidden';
    document.getElementById('assignModal').style.opacity = '0';
    document.getElementById('assignModal').style.transform = 'translate(-50%,-50%) scale(0.9)';
    document.getElementById('bulkOverlay').classList.remove('active');
}

function selectPlan(el) {
    document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
}

function saveIndividualPlan() {
    const activePlan = document.querySelector('.plan-card.active').dataset.plan;
    quickAssign(currentInstId, activePlan);
    closeAssignModal();
}

function openBulkModal() {
    document.getElementById('bulkOverlay').classList.add('active');
    document.getElementById('bulkDrawer').style.right = '0';
}
function closeBulkModal() {
    document.getElementById('bulkOverlay').classList.remove('active');
    document.getElementById('bulkDrawer').style.right = '-400px';
}
function applyBulk() {
    SuperAdmin.showNotification('Bulk plan assignment applied!', 'success');
    closeBulkModal();
}

function selectAll(cb) {
    document.querySelectorAll('.row-cb').forEach(c => c.checked = cb.checked);
}

document.addEventListener('DOMContentLoaded', () => {
    renderTable(institutes);
});
</script>

<?php include 'footer.php'; ?>
