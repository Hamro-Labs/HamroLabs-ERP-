<?php
require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pageTitle = 'Feature Usage Heatmap';
$activePage = 'heatmap.php';
?>

<!-- Sidebar -->
<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<!-- Main Content -->
<main class="main">
<div class="page">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
      <div style="font-size:11px;color:var(--text-light);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Platform Analytics</div>
      <h1 style="font-size:22px;font-weight:800;">Feature Usage Heatmap</h1>
      <p style="font-size:13px;color:var(--text-body);margin-top:4px;">Which features and modules are most actively used across institutes</p>
    </div>
    <div style="display:flex;gap:10px;">
      <select class="filter-sel"><option>Last 30 Days</option><option>Last 7 Days</option><option>Last 90 Days</option></select>
      <button class="btn bs"><i class="fa fa-download"></i> Export</button>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="stat-grid" style="margin-bottom:24px;">
    <div class="card stat-card"><div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;"><div style="width:38px;height:38px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--green);font-size:16px;"><i class="fa fa-trophy"></i></div><span style="font-size:12px;color:var(--text-body);font-weight:600;">Most Used Feature</span></div><div style="font-size:16px;font-weight:800;color:var(--text-dark);">Attendance</div><div style="font-size:12px;color:var(--text-light);margin-top:4px;">82,400 events / 30 days</div></div>
    <div class="card stat-card"><div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;"><div style="width:38px;height:38px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#3b82f6;font-size:16px;"><i class="fa fa-arrow-trend-up"></i></div><span style="font-size:12px;color:var(--text-body);font-weight:600;">Fastest Growing</span></div><div style="font-size:16px;font-weight:800;color:#3b82f6;">Mock Exams</div><div style="font-size:12px;color:var(--text-light);margin-top:4px;">↑ 34% usage this month</div></div>
    <div class="card stat-card"><div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;"><div style="width:38px;height:38px;background:#fef2f2;border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--red);font-size:16px;"><i class="fa fa-arrow-trend-down"></i></div><span style="font-size:12px;color:var(--text-body);font-weight:600;">Least Used</span></div><div style="font-size:16px;font-weight:800;color:var(--red);">AI Features</div><div style="font-size:12px;color:var(--text-light);margin-top:4px;">Enterprise only — 7 tenants</div></div>
    <div class="card stat-card"><div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;"><div style="width:38px;height:38px;background:#fef3c7;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#d97706;font-size:16px;"><i class="fa fa-fire"></i></div><span style="font-size:12px;color:var(--text-body);font-weight:600;">Peak Usage Hour</span></div><div style="font-size:16px;font-weight:800;color:#d97706;">10:00 – 11:00 AM</div><div style="font-size:12px;color:var(--text-light);margin-top:4px;">Consistent Mon–Fri</div></div>
  </div>

  <!-- Feature vs Day Heatmap -->
  <div class="card" style="margin-bottom:20px;overflow-x:auto;">
    <div style="font-size:14px;font-weight:800;margin-bottom:20px;display:flex;align-items:center;gap:8px;"><i class="fa fa-table-cells" style="color:var(--green);"></i> Feature × Day Heatmap <span style="font-size:11px;font-weight:500;color:var(--text-light);margin-left:8px;">Click a cell for details</span></div>
    <div id="heatmapGrid" style="overflow-x:auto;"></div>
    <!-- Legend -->
    <div style="display:flex;align-items:center;gap:8px;margin-top:16px;flex-wrap:wrap;">
      <span style="font-size:12px;color:var(--text-light);">Less</span>
      <div class="heat-cell heat-0" style="width:20px;height:20px;border-radius:4px;"></div>
      <div class="heat-cell heat-1" style="width:20px;height:20px;border-radius:4px;"></div>
      <div class="heat-cell heat-2" style="width:20px;height:20px;border-radius:4px;"></div>
      <div class="heat-cell heat-3" style="width:20px;height:20px;border-radius:4px;"></div>
      <div class="heat-cell heat-4" style="width:20px;height:20px;border-radius:4px;"></div>
      <div class="heat-cell heat-5" style="width:20px;height:20px;border-radius:4px;"></div>
      <div class="heat-cell heat-6" style="width:20px;height:20px;border-radius:4px;"></div>
      <span style="font-size:12px;color:var(--text-light);">More</span>
    </div>
  </div>

  <!-- Feature Rankings -->
  <div class="g2">
    <div class="tbl-wrap">
      <div class="tbl-head"><div class="tbl-title"><i class="fa fa-ranking-star"></i> Feature Usage Rankings</div></div>
      <table>
        <thead><tr style="background:#f8fafc;"><th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">#</th><th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Feature</th><th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Events</th><th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Usage</th></tr></thead>
        <tbody id="rankTbody"></tbody>
      </table>
    </div>
    <div class="card">
      <div style="font-size:14px;font-weight:800;margin-bottom:16px;display:flex;align-items:center;gap:8px;"><i class="fa fa-clock" style="color:var(--green);"></i> Hourly Usage Pattern</div>
      <div id="hourGrid" style="display:flex;flex-direction:column;gap:8px;"></div>
    </div>
  </div>
</div>
</main>

<script>
const features = ['Attendance','Fee Collection','Admissions','Mock Exams','Study Materials','Assignments','Library','SMS Alerts','Guardian Portal','Reports'];
const days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
const heatData = [
  [6,5,6,6,5,3,2],[5,4,5,5,5,2,1],[4,4,4,5,4,2,1],[4,5,5,6,4,2,1],
  [3,3,4,4,3,2,1],[2,3,3,3,3,1,0],[2,2,2,2,2,1,0],[4,4,4,4,3,1,0],
  [2,3,3,3,2,1,0],[3,3,4,4,3,1,0]
];
const featureEvents = [82400,68200,54100,47800,38200,29400,18700,67800,22100,31400];

const grid = document.getElementById('heatmapGrid');
let html = `<div style="display:flex;gap:8px;"><div style="width:130px;"></div>${days.map(d=>`<div style="width:48px;text-align:center;font-size:11px;font-weight:700;color:var(--text-light);">${d}</div>`).join('')}</div>`;
heatData.forEach((row, fi) => {
  html += `<div style="display:flex;gap:8px;align-items:center;margin-top:8px;"><div style="width:130px;font-size:12px;font-weight:600;color:var(--text-dark);text-align:right;padding-right:10px;">${features[fi]}</div>`;
  row.forEach((val, di) => {
    html += `<div class="heat-cell heat-${val}" onclick="showCellDetail('${features[fi]}','${days[di]}',${featureEvents[fi]})" title="${features[fi]} — ${days[di]}: ${val*100+Math.random()*100|0} events">${val*100+Math.floor(Math.random()*100)}</div>`;
  });
  html += '</div>';
});
grid.innerHTML = html;

document.getElementById('rankTbody').innerHTML = features.map((f,i) => `
  <tr style="border-bottom:1px solid var(--card-border);" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
    <td style="padding:12px 16px;font-size:13px;font-weight:800;color:${i<3?'#d97706':'var(--text-light)'};">${i+1}</td>
    <td style="padding:12px 16px;font-size:13px;font-weight:700;">${f}</td>
    <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:var(--green);">${featureEvents[i].toLocaleString()}</td>
    <td style="padding:12px 16px;min-width:100px;"><div style="height:6px;background:#f1f5f9;border-radius:3px;"><div style="width:${Math.round(featureEvents[i]/82400*100)}%;height:6px;background:var(--green);border-radius:3px;"></div></div></td>
  </tr>`).join('');

const hours = [0,2,0,0,1,4,7,9,8,9,6,5,6,4,3,2,3,2,2,1,1,0,0,0];
document.getElementById('hourGrid').innerHTML = hours.map((v,i) => `
  <div style="display:flex;align-items:center;gap:8px;">
    <span style="font-size:11px;color:var(--text-light);width:40px;">${String(i).padStart(2,'0')}:00</span>
    <div style="flex:1;height:8px;background:#f1f5f9;border-radius:4px;"><div style="width:${v*10}%;height:8px;background:var(--green);border-radius:4px;"></div></div>
    <span style="font-size:11px;font-weight:700;color:var(--green);width:30px;">${v*10}%</span>
  </div>`).join('');

function showCellDetail(feat, day, events) { showToast(`${feat} on ${day}: ~${Math.floor(events/7).toLocaleString()} events`); }
function showToast(msg) { const t=document.createElement('div'); t.style.cssText='position:fixed;bottom:24px;right:24px;background:#1E293B;color:#fff;padding:12px 20px;border-radius:10px;font-size:13px;z-index:9999;'; t.innerHTML=`<i class="fa fa-info-circle" style="color:var(--green);margin-right:8px;"></i>${msg}`; document.body.appendChild(t); setTimeout(()=>t.remove(),3000); }
function toggleSidebar() { document.body.classList.toggle('sb-active'); document.body.classList.toggle('sb-collapsed'); }
function toggleMenu(id) { const m=document.getElementById(id); const c=document.getElementById('chev-'+id); const o=m.style.display==='block'; m.style.display=o?'none':'block'; if(c)c.classList.toggle('open',!o); }
function go(url) { window.location.href=url; }
</script>
</body>
</html>
