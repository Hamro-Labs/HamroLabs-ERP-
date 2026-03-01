<?php
/**
 * Hamro ERP — Subscription Plans Page
 * Refactored to match Super Admin layout and design system.
 */

require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pageTitle = 'Subscription Plans';
$activePage = 'plans.php';
?>

<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<main class="main" id="mainContent">
    <div class="pg fu">

        <!-- Page Header -->
        <div class="pg-head">
            <div class="pg-left">
                <div class="pg-ico ic-p"><i class="fa-solid fa-layer-group"></i></div>
                <div>
                    <div class="pg-title">Subscription Plans</div>
                    <div class="pg-sub">Manage pricing tiers and feature access for all platform plans.</div>
                </div>
            </div>
            <div class="pg-acts">
                <button class="btn bs"><i class="fa-solid fa-download"></i> Export</button>
                <button class="btn bt" onclick="SuperAdmin.showNotification('Adding new plan...', 'info')"><i class="fa-solid fa-plus"></i> New Plan</button>
            </div>
        </div>

        <!-- Plan Cards Grid -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:24px; margin-bottom:32px;" id="planGrid">
            <!-- Rendered by JS -->
        </div>

        <!-- Feature Matrix Table -->
        <div class="card">
            <div class="tbl-head">
                <div class="ct"><i class="fa-solid fa-table-list"></i> Feature Comparison Matrix</div>
            </div>
            <div class="tw" style="border:none; border-radius:0;">
                <table>
                    <thead>
                        <tr>
                            <th style="min-width:240px;">Platform Feature</th>
                            <th style="text-align:center;">Starter</th>
                            <th style="text-align:center;">Growth</th>
                            <th style="text-align:center;">Professional</th>
                            <th style="text-align:center;">Enterprise</th>
                        </tr>
                    </thead>
                    <tbody id="featureMatrix">
                        <!-- Rendered by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
const plans = [
    { name:'Starter', price:0, color:'#16a34a', bg:'#f0fdf4', features:['3 Teachers','150 Students','5 GB Storage','Standard SMS','Email Support'], btn:'Current Default' },
    { name:'Growth', price:49, color:'#3b82f6', bg:'#eff6ff', features:['10 Teachers','500 Students','50 GB Storage','Bulk SMS','Priority Support','Analytics'], btn:'Edit Plan', popular:true },
    { name:'Professional', price:99, color:'#8b5cf6', bg:'#f5f3ff', features:['50 Teachers','1500 Students','200 GB Storage','Custom Domain','API Access','24/7 Support'], btn:'Edit Plan' },
    { name:'Enterprise', price:199, color:'#d97706', bg:'#fffbeb', features:['Unlimited Teachers','Unlimited Students','Unlimited Storage','Full White-label','Dedicated Manager','Custom Integration'], btn:'Edit Plan' }
];

const features = [
    { name:'Max Teacher Accounts', starter:'3', growth:'10', pro:'50', ent:'Unlimited' },
    { name:'Max Student Records', starter:'150', growth:'500', pro:'1,500', ent:'Unlimited' },
    { name:'Cloud Storage Capacity', starter:'5 GB', growth:'50 GB', pro:'200 GB', ent:'Unlimited' },
    { name:'SMS Gateway Access', starter:true, growth:true, pro:true, ent:true },
    { name:'Bulk Communication Tools', starter:false, growth:true, pro:true, ent:true },
    { name:'Custom Subdomain', starter:true, growth:true, pro:true, ent:true },
    { name:'White-label / Custom Domain', starter:false, growth:false, pro:true, ent:true },
    { name:'Advanced Analytics Dashboard', starter:false, growth:true, pro:true, ent:true },
    { name:'API & Third-party Integrations', starter:false, growth:false, pro:true, ent:true },
    { name:'Priority Support Response', starter:false, growth:true, pro:true, ent:true },
    { name:'Dedicated Account Manager', starter:false, growth:false, pro:false, ent:true },
];

function renderPlans() {
    document.getElementById('planGrid').innerHTML = plans.map(p => `
        <div class="card" style="border:1px solid var(--cb); border-top: 4px solid ${p.color}; position:relative; display:flex; flex-direction:column;">
            ${p.popular ? `<span style="position:absolute; top:-12px; right:20px; background:${p.color}; color:#fff; font-size:10px; font-weight:800; padding:4px 12px; border-radius:20px; text-transform:uppercase; letter-spacing:1px;">Most Popular</span>` : ''}
            <div style="margin-bottom:20px;">
                <div style="font-size:12px; font-weight:700; color:var(--tl); text-transform:uppercase; margin-bottom:4px; letter-spacing:1px;">Tier</div>
                <div style="font-size:20px; font-weight:800; color:${p.color};">${p.name}</div>
            </div>
            <div style="margin-bottom:24px;">
                <span style="font-size:40px; font-weight:800; color:var(--td);">$${p.price}</span>
                <span style="color:var(--tl); font-weight:600;">/month</span>
            </div>
            <ul style="list-style:none; padding:0; margin:0 0 30px; flex:1;">
                ${p.features.map(f => `
                    <li style="padding:8px 0; font-size:13px; color:var(--tb); display:flex; align-items:center; gap:10px;">
                        <i class="fa-solid fa-circle-check" style="color:${p.color}; font-size:14px;"></i>
                        ${f}
                    </li>
                `).join('')}
            </ul>
            <button class="btn" style="width:100%; height:44px; border:1px solid ${p.color}; color:${p.color}; font-weight:700; background:transparent; transition:0.2s;" onmouseover="this.style.background='${p.color}'; this.style.color='#fff';" onmouseout="this.style.background='transparent'; this.style.color='${p.color}';">
                ${p.btn}
            </button>
        </div>
    `).join('');
}

function renderMatrix() {
    document.getElementById('featureMatrix').innerHTML = features.map(f => `
        <tr style="border-bottom:1px solid var(--cb);">
            <td style="padding:14px 20px; font-size:13px; font-weight:700; color:var(--td);">${f.name}</td>
            <td style="padding:14px 20px; text-align:center; font-size:13px;">${renderVal(f.starter)}</td>
            <td style="padding:14px 20px; text-align:center; font-size:13px;">${renderVal(f.growth)}</td>
            <td style="padding:14px 20px; text-align:center; font-size:13px;">${renderVal(f.pro)}</td>
            <td style="padding:14px 20px; text-align:center; font-size:13px;">${renderVal(f.ent)}</td>
        </tr>
    `).join('');
}

function renderVal(v) {
    if(typeof v === 'boolean') {
        return v ? '<i class="fa-solid fa-check" style="color:#16a34a; font-size:16px;"></i>' : '<i class="fa-solid fa-minus" style="color:var(--tl); font-size:14px;"></i>';
    }
    return `<strong>${v}</strong>`;
}

document.addEventListener('DOMContentLoaded', () => {
    renderPlans();
    renderMatrix();
});
</script>

<?php include 'footer.php'; ?>
