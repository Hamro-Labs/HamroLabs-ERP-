<?php
require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pageTitle = 'Feature Flags';
$activePage = 'flags.php';
?>

<!-- Sidebar -->
<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<!-- Main Content -->
<main class="main">
<div class="page">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
      <div style="font-size:11px;color:var(--text-light);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Plan Management</div>
      <h1 style="font-size:22px;font-weight:800;">Feature Flags</h1>
      <p style="font-size:13px;color:var(--text-body);margin-top:4px;">Control feature availability per plan and per tenant</p>
    </div>
    <div style="display:flex;gap:10px;">
      <button class="btn bs"><i class="fa fa-history"></i> Change Log</button>
      <button class="btn bt" onclick="openNewFlag()"><i class="fa fa-plus"></i> New Flag</button>
    </div>
  </div>

  <!-- Tabs -->
  <div style="display:flex;gap:4px;margin-bottom:20px;background:#f1f5f9;border-radius:12px;padding:4px;width:fit-content;">
    <button class="btn" id="tab-global" onclick="switchTab('global')" style="background:var(--green);color:#fff;border-radius:9px;padding:8px 18px;font-size:13px;">Global Flags</button>
    <button class="btn btn-ghost" id="tab-tenant" onclick="switchTab('tenant')" style="padding:8px 18px;font-size:13px;">Per-Tenant Override</button>
  </div>

  <!-- Global Flags -->
  <div id="section-global">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;margin-bottom:24px;" id="categoryCards"></div>

    <div class="tbl-wrap">
      <div class="tbl-head">
        <div class="tbl-title"><i class="fa fa-flag"></i> All Feature Flags</div>
        <div style="display:flex;gap:8px;">
          <div style="position:relative;"><i class="fa fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-light);font-size:12px;"></i><input type="text" class="search-inp" placeholder="Search flags..." style="width:200px;" oninput="filterFlags(this.value)"></div>
          <select class="filter-sel" onchange="filterCategory(this.value)">
            <option value="">All Categories</option>
            <option>Academic</option><option>Financial</option><option>Communication</option><option>AI</option><option>Security</option>
          </select>
        </div>
      </div>
      <table>
        <thead>
          <tr style="background:#f8fafc;">
            <th style="padding:12px 20px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Flag Key</th>
            <th style="padding:12px 20px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Description</th>
            <th style="padding:12px 20px;text-align:center;font-size:11px;font-weight:700;color:#16a34a;text-transform:uppercase;border-bottom:1px solid var(--card-border);">Starter</th>
            <th style="padding:12px 20px;text-align:center;font-size:11px;font-weight:700;color:#3b82f6;text-transform:uppercase;border-bottom:1px solid var(--card-border);">Growth</th>
            <th style="padding:12px 20px;text-align:center;font-size:11px;font-weight:700;color:var(--purple);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Pro</th>
            <th style="padding:12px 20px;text-align:center;font-size:11px;font-weight:700;color:#d97706;text-transform:uppercase;border-bottom:1px solid var(--card-border);">Enterprise</th>
            <th style="padding:12px 20px;text-align:center;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Actions</th>
          </tr>
        </thead>
        <tbody id="flagTbody"></tbody>
      </table>
    </div>
  </div>

  <!-- Per-Tenant Override -->
  <div id="section-tenant" style="display:none;">
    <div class="card" style="margin-bottom:16px;">
      <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <div style="flex:1;min-width:200px;">
          <label class="form-lbl">Select Institute</label>
          <select class="form-sel" onchange="loadTenantFlags(this.value)">
            <option value="">— Choose an institute —</option>
            <option>Loksewa Pathshala (Growth)</option>
            <option>PSC Coaching Center (Starter)</option>
            <option>Nayab Subba Academy (Professional)</option>
            <option>Kharidar Study Hub (Enterprise)</option>
          </select>
        </div>
        <div style="padding-top:20px;"><button class="btn bt"><i class="fa fa-refresh"></i> Reset to Plan Defaults</button></div>
      </div>
    </div>
    <div id="tenantFlagSection" style="display:none;">
      <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px;margin-bottom:16px;font-size:13px;color:#92400e;"><i class="fa fa-triangle-exclamation"></i> Per-tenant overrides take precedence over plan defaults. Changes apply immediately.</div>
      <div class="tbl-wrap">
        <div class="tbl-head"><div class="tbl-title"><i class="fa fa-building"></i> Loksewa Pathshala — Feature Override</div></div>
        <table><thead><tr style="background:#f8fafc;"><th style="padding:12px 20px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Feature</th><th style="padding:12px 20px;text-align:center;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Plan Default</th><th style="padding:12px 20px;text-align:center;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Override</th><th style="padding:12px 20px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Reason / Note</th></tr></thead>
        <tbody id="tenantFlagTbody"></tbody></table>
      </div>
    </div>
  </div>
</div>
</main>

<!-- New Flag Drawer -->
<div class="overlay" id="flagOverlay" onclick="closeNewFlag()"></div>
<div class="drawer" id="flagDrawer">
  <div class="drawer-hdr">
    <div class="drawer-hdr-icon" style="background:#eff6ff;color:#3b82f6;"><i class="fa fa-flag"></i></div>
    <div class="drawer-hdr-text"><h3>New Feature Flag</h3><p>Add a new platform feature flag</p></div>
    <button class="drawer-close" onclick="closeNewFlag()"><i class="fa fa-times"></i></button>
  </div>
  <div class="drawer-body">
    <div class="form-grp"><label class="form-lbl">Flag Key</label><input type="text" class="form-inp" placeholder="module.feature_name"><div class="form-hint">Use snake_case. Example: exam.ai_proctoring</div></div>
    <div class="form-grp"><label class="form-lbl">Display Name</label><input type="text" class="form-inp" placeholder="AI Proctoring for Exams"></div>
    <div class="form-grp"><label class="form-lbl">Category</label><select class="form-sel"><option>Academic</option><option>Financial</option><option>Communication</option><option>AI</option><option>Security</option><option>UI</option></select></div>
    <div class="form-grp"><label class="form-lbl">Description</label><textarea class="form-inp" rows="3" placeholder="What does this flag control?"></textarea></div>
    <div class="form-sec-title">Plan Defaults</div>
    <div style="display:flex;flex-direction:column;gap:14px;">
      <div class="toggle-wrap"><label class="toggle"><input type="checkbox"><span class="toggle-slider"></span></label><span style="font-size:13px;">🌱 Starter</span></div>
      <div class="toggle-wrap"><label class="toggle"><input type="checkbox" checked><span class="toggle-slider"></span></label><span style="font-size:13px;">🚀 Growth</span></div>
      <div class="toggle-wrap"><label class="toggle"><input type="checkbox" checked><span class="toggle-slider"></span></label><span style="font-size:13px;">⭐ Professional</span></div>
      <div class="toggle-wrap"><label class="toggle"><input type="checkbox" checked><span class="toggle-slider"></span></label><span style="font-size:13px;">👑 Enterprise</span></div>
    </div>
  </div>
  <div class="drawer-footer">
    <button class="btn bs" onclick="closeNewFlag()">Cancel</button>
    <button class="btn bt"><i class="fa fa-check"></i> Create Flag</button>
  </div>
</div>

<script>
const flags = [
  { key:"admission.form_filling", desc:"Student admission form & document upload", cat:"Academic", starter:true, growth:true, pro:true, ent:true },
  { key:"fee.management", desc:"Fee collection, receipts & installment plans", cat:"Financial", starter:true, growth:true, pro:true, ent:true },
  { key:"attendance.tracking", desc:"Teacher attendance marking via PWA", cat:"Academic", starter:true, growth:true, pro:true, ent:true },
  { key:"exam.mock_test_engine", desc:"MCQ-based online exam & auto-evaluation", cat:"Academic", starter:false, growth:true, pro:true, ent:true },
  { key:"lms.study_materials", desc:"Upload & manage study materials library", cat:"Academic", starter:false, growth:true, pro:true, ent:true },
  { key:"lms.video_streaming", desc:"HLS video streaming for lectures", cat:"Academic", starter:false, growth:false, pro:false, ent:true },
  { key:"library.module", desc:"Book catalog, issue/return & fines", cat:"Academic", starter:false, growth:true, pro:true, ent:true },
  { key:"report.python_excel", desc:"Advanced Excel reports via Python engine", cat:"Academic", starter:false, growth:false, pro:true, ent:true },
  { key:"guardian.portal", desc:"Parent read-only monitoring portal", cat:"Academic", starter:false, growth:true, pro:true, ent:true },
  { key:"sms.automation", desc:"Automated SMS reminders & alerts", cat:"Communication", starter:true, growth:true, pro:true, ent:true },
  { key:"api.access", desc:"Public REST API for integrations", cat:"Academic", starter:false, growth:false, pro:true, ent:true },
  { key:"fee.esewa_khalti", desc:"eSewa/Khalti online payment gateway", cat:"Financial", starter:false, growth:false, pro:false, ent:true },
  { key:"ai.weak_subject", desc:"AI weak subject detection & recommendations", cat:"AI", starter:false, growth:false, pro:false, ent:true },
  { key:"ai.performance_predict", desc:"Student pass/fail probability prediction", cat:"AI", starter:false, growth:false, pro:false, ent:true },
  { key:"security.2fa", desc:"Two-factor authentication for admins", cat:"Security", starter:true, growth:true, pro:true, ent:true },
  { key:"ui.white_label", desc:"Remove Hamro Labs branding from UI", cat:"UI", starter:false, growth:false, pro:false, ent:true },
];

const check = (v) => v ? '<i class="fa fa-toggle-on" style="color:var(--green);font-size:18px;cursor:pointer;"></i>' : '<i class="fa fa-toggle-off" style="color:#cbd5e1;font-size:18px;cursor:pointer;"></i>';

function renderFlags(data) {
  document.getElementById('flagTbody').innerHTML = data.map((f,i) => `
    <tr class="ff-row" style="border-bottom:1px solid var(--card-border);transition:.15s;">
      <td style="padding:13px 20px;">
        <div style="font-size:12px;font-weight:700;font-family:monospace;color:var(--navy);background:#f1f5f9;padding:3px 8px;border-radius:6px;display:inline-block;">${f.key}</div>
        <div style="font-size:11px;color:var(--text-light);margin-top:4px;">${f.cat}</div>
      </td>
      <td style="padding:13px 20px;font-size:13px;color:var(--text-body);">${f.desc}</td>
      <td style="padding:13px 20px;text-align:center;" onclick="toggleFlag(${i},'starter')">${check(f.starter)}</td>
      <td style="padding:13px 20px;text-align:center;" onclick="toggleFlag(${i},'growth')">${check(f.growth)}</td>
      <td style="padding:13px 20px;text-align:center;" onclick="toggleFlag(${i},'pro')">${check(f.pro)}</td>
      <td style="padding:13px 20px;text-align:center;" onclick="toggleFlag(${i},'ent')">${check(f.ent)}</td>
      <td style="padding:13px 20px;text-align:center;">
        <div style="display:flex;justify-content:center;gap:6px;">
          <button class="btn btn-sm bs"><i class="fa fa-pen"></i></button>
          <button class="btn btn-sm" style="background:#fef2f2;color:var(--red);"><i class="fa fa-trash"></i></button>
        </div>
      </td>
    </tr>`).join('');
}

function toggleFlag(idx, plan) {
  flags[idx][plan] = !flags[idx][plan];
  renderFlags(flags);
  showToast(`Flag ${flags[idx].key} updated`);
}

function filterFlags(v) { renderFlags(flags.filter(f => f.key.includes(v.toLowerCase()) || f.desc.toLowerCase().includes(v.toLowerCase()))); }
function filterCategory(v) { renderFlags(v ? flags.filter(f=>f.cat===v) : flags); }
function switchTab(t) {
  document.getElementById('section-global').style.display = t==='global'?'block':'none';
  document.getElementById('section-tenant').style.display = t==='tenant'?'block':'none';
  document.getElementById('tab-global').style.cssText = t==='global'?'background:var(--green);color:#fff;border-radius:9px;padding:8px 18px;font-size:13px;':'padding:8px 18px;font-size:13px;';
  document.getElementById('tab-tenant').style.cssText = t==='tenant'?'background:var(--green);color:#fff;border-radius:9px;padding:8px 18px;font-size:13px;':'padding:8px 18px;font-size:13px;';
}
function loadTenantFlags(v) {
  if (!v) return;
  document.getElementById('tenantFlagSection').style.display = 'block';
  document.getElementById('tenantFlagTbody').innerHTML = flags.slice(0,8).map(f => `
    <tr style="border-bottom:1px solid var(--card-border);">
      <td style="padding:13px 20px;font-size:13px;font-weight:600;">${f.desc}</td>
      <td style="padding:13px 20px;text-align:center;">${check(f.growth)}</td>
      <td style="padding:13px 20px;text-align:center;cursor:pointer;">${check(f.growth)}</td>
      <td style="padding:13px 20px;"><input class="form-inp" style="padding:6px 10px;font-size:12px;" placeholder="Add note..."></td>
    </tr>`).join('');
}
function renderCategories() {
  const cats = { Academic:{count:0,color:'var(--green)',icon:'fa-graduation-cap'}, Financial:{count:0,color:'#3b82f6',icon:'fa-dollar-sign'}, Communication:{count:0,color:'#d97706',icon:'fa-message'}, AI:{count:0,color:'var(--purple)',icon:'fa-robot'}, Security:{count:0,color:'var(--red)',icon:'fa-shield'}, UI:{count:0,color:'#0d9488',icon:'fa-palette'} };
  flags.forEach(f => { if(cats[f.cat]) cats[f.cat].count++; });
  document.getElementById('categoryCards').innerHTML = Object.entries(cats).map(([k,v]) => `
    <div class="card" style="cursor:pointer;" onclick="filterCategory('${k}')">
      <div style="display:flex;align-items:center;gap:12px;">
        <div style="width:42px;height:42px;border-radius:10px;background:${v.color}1a;color:${v.color};display:flex;align-items:center;justify-content:center;font-size:18px;"><i class="fa ${v.icon}"></i></div>
        <div><div style="font-size:15px;font-weight:800;color:var(--text-dark);">${v.count}</div><div style="font-size:12px;color:var(--text-light);">${k}</div></div>
      </div>
    </div>`).join('');
}
function openNewFlag() { document.getElementById('flagOverlay').classList.add('active'); document.getElementById('flagDrawer').classList.add('active'); }
function closeNewFlag() { document.getElementById('flagOverlay').classList.remove('active'); document.getElementById('flagDrawer').classList.remove('active'); }
function showToast(msg) { const t=document.createElement('div'); t.style.cssText='position:fixed;bottom:24px;right:24px;background:#1E293B;color:#fff;padding:12px 20px;border-radius:10px;font-size:13px;z-index:9999;'; t.innerHTML=`<i class="fa fa-check-circle" style="color:var(--green);margin-right:8px;"></i>${msg}`; document.body.appendChild(t); setTimeout(()=>t.remove(),3000); }
function toggleSidebar() { document.body.classList.toggle('sb-active'); document.body.classList.toggle('sb-collapsed'); }
function toggleMenu(id) { const m=document.getElementById(id); const c=document.getElementById('chev-'+id); const o=m.style.display==='block'; m.style.display=o?'none':'block'; if(c)c.classList.toggle('open',!o); }
function go(url) { window.location.href=url; }
renderFlags(flags); renderCategories();
</script>
</body>
</html>
