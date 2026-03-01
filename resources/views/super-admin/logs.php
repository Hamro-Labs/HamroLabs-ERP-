<?php
/**
 * Hamro ERP — System Logs Partial
 */
require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pageTitle = 'System Logs';
$activePage = 'logs.php';
?>

<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<main class="main" id="mainContent">
<div class="page fu">
  <div class="pg-hdr">
    <div class="pg-hdr-left">
      <div class="breadcrumb">
        <span class="bc-root" onclick="goNav('overview')">Dashboard</span>
        <span class="bc-sep">›</span>
        <span class="bc-cur">System Logs</span>
      </div>
      <h1 style="display:flex; align-items:center; gap:10px;">
        <i class="fa fa-shield-halved" style="color:var(--green); font-size:1.1rem;"></i>
        Security & System Logs
      </h1>
      <p>Failed logins, audit trails, API requests, and error logs</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <button class="btn bs" onclick="showToast('Exporting logs...', 'info')"><i class="fa fa-download"></i> Export</button>
      <button class="btn btn-red" onclick="showToast('IP blocked!', 'error')"><i class="fa fa-ban"></i> Block IP</button>
    </div>
  </div>

  <div class="stat-grid">
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-red"><i class="fa fa-user-xmark"></i></div><span class="stat-badge bg-r">Last 24h</span></div>
      <div class="stat-val" id="failedLoginVal">...</div>
      <div class="stat-lbl">Failed Login Attempts</div>
      <div class="stat-sub"><i class="fa fa-shield" style="color:#16a34a"></i> Monitoring active</div>
    </div>
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-amber"><i class="fa fa-lock"></i></div><span class="stat-badge bg-y">Auto-blocked</span></div>
      <div class="stat-val">12</div>
      <div class="stat-lbl">IPs Rate-Limited</div>
      <div class="stat-sub"><i class="fa fa-clock" style="color:#d97706"></i> 5 failed attempts threshold</div>
    </div>
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-purple"><i class="fa fa-file-lines"></i></div><span class="stat-badge bg-p">Today</span></div>
      <div class="stat-val" id="auditCountVal">...</div>
      <div class="stat-lbl">Recent Audit Log Entries</div>
      <div class="stat-sub"><i class="fa fa-shield" style="color:var(--purple)"></i> Immutable records</div>
    </div>
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-blue"><i class="fa fa-plug"></i></div><span class="stat-badge bg-b">24h</span></div>
      <div class="stat-val">98,420</div>
      <div class="stat-lbl">API Requests</div>
      <div class="stat-sub"><i class="fa fa-circle-exclamation" style="color:#d97706"></i> 0.02% error rate</div>
    </div>
  </div>

  <div class="tabs" style="display:flex;gap:4px;background:#f1f5f9;border-radius:10px;padding:4px;margin-bottom:16px">
    <button class="tab-btn active" id="tabBtnFailed" onclick="switchLogTab(this,'tab-failed')">Failed Logins</button>
    <button class="tab-btn" id="tabBtnAudit" onclick="switchLogTab(this,'tab-audit')">Audit Log</button>
    <button class="tab-btn" onclick="switchLogTab(this,'tab-api')">API Requests</button>
    <button class="tab-btn" onclick="switchLogTab(this,'tab-errors')">Error Log</button>
  </div>

  <div class="g65">
    <div>
      <div class="toolbar">
        <div class="search-box"><i class="fa fa-search"></i><input class="search-inp" type="text" placeholder="Search by IP, email, tenant..."></div>
        <select class="filter-sel"><option>All Tenants</option><option>Everest Loksewa</option><option>Dharan Civil</option></select>
        <div class="toolbar-right"><button class="btn bs btn-sm" onclick="showToast('Refreshing logs...', 'info')"><i class="fa fa-rotate-right"></i></button></div>
      </div>
      <div id="tab-failed">
        <div class="tbl-wrap" style="margin-bottom:0">
          <div class="tbl-scroll"><table><thead><tr><th>IP Address</th><th>Email Attempted</th><th>Tenant</th><th>Attempts</th><th>Time</th><th>Status</th><th>Action</th></tr></thead><tbody id="failedTbl"></tbody></table></div>
        </div>
      </div>
      <div id="tab-audit" style="display:none">
        <div class="tbl-wrap" style="margin-bottom:0">
          <div class="tbl-scroll"><table><thead><tr><th>Actor</th><th>Action</th><th>Table</th><th>Record</th><th>IP</th><th>Time</th><th>Details</th></tr></thead><tbody id="auditTbl"></tbody></table></div>
        </div>
      </div>
      <div id="tab-api" style="display:none">
        <div class="tbl-wrap" style="margin-bottom:0">
          <div class="tbl-scroll"><table><thead><tr><th>Method</th><th>Endpoint</th><th>Tenant</th><th>Status</th><th>Time</th><th>Duration</th></tr></thead><tbody id="apiTbl"></tbody></table></div>
        </div>
      </div>
      <div id="tab-errors" style="display:none">
        <div class="tbl-wrap" style="margin-bottom:0">
          <div class="tbl-scroll"><table><thead><tr><th>Level</th><th>Message</th><th>File</th><th>Time</th></tr></thead><tbody id="errTbl"></tbody></table></div>
        </div>
      </div>
    </div>
    <div>
      <div class="card" style="margin-bottom:16px">
        <div class="ct"><i class="fa fa-chart-bar"></i> Failed Logins — Last 24h</div>
        <div style="display:flex;align-items:flex-end;gap:3px;height:100px;padding-top:8px" id="failChart"></div>
        <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--text-light);margin-top:4px"><span>00:00</span><span>12:00</span><span>Now</span></div>
      </div>
      <div class="card">
        <div class="ct"><i class="fa fa-globe"></i> Top Offending IPs</div>
        <div id="topIPs"></div>
      </div>
    </div>
  </div>
</div>

<!-- Audit Diff Modal -->
<div class="modal" id="auditModal">
  <div class="modal-content" style="max-width:800px;">
    <div class="modal-hdr">
      <div>
        <h3 style="font-size:1.1rem;margin:0">Audit Record Details</h3>
        <p style="font-size:12px;color:var(--text-light);margin:4px 0 0" id="auditModalSub">View exact data changes</p>
      </div>
      <button class="btn bs btn-sm" onclick="closeModal('auditModal')"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body" style="padding:16px;">
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
          <h4 style="font-size:12px;color:var(--text-light);margin-bottom:8px;text-transform:uppercase;">Old Values (JSON)</h4>
          <pre id="auditOldValues" style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:12px; border-radius:6px; font-family:'Courier New', monospace; font-size:12px; overflow-x:auto; white-space:pre-wrap; min-height:100px;"></pre>
        </div>
        <div>
          <h4 style="font-size:12px;color:var(--text-light);margin-bottom:8px;text-transform:uppercase;">New Values (JSON)</h4>
          <pre id="auditNewValues" style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:12px; border-radius:6px; font-family:'Courier New', monospace; font-size:12px; overflow-x:auto; white-space:pre-wrap; min-height:100px;"></pre>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
    const s = window.platformStatsData || {};
    const auditLogs = s.auditLogs?.length ? s.auditLogs : [
        { id: 101, user_id: "SA-001", level: "WARNING", message: "UPDATED tenants", ip_address: "103.42.88.201", time: new Date(Date.now() - 3600000).toISOString(), table: "tenants", record_id: "Everest Loksewa", old_values: { "plan": "starter", "status": "trial", "student_limit": 100 }, new_values: { "plan": "professional", "status": "active", "student_limit": 1500 } },
        { id: 102, user_id: "ADM-992", level: "INFO", message: "CREATED fee_items", ip_address: "27.34.101.222", time: new Date(Date.now() - 7200000).toISOString(), table: "fee_items", record_id: "Admission Fee", old_values: null, new_values: { "amount": 5000, "is_refundable": false } },
        { id: 103, user_id: "SA-001", level: "ERROR", message: "DELETED users", ip_address: "103.42.88.201", time: new Date(Date.now() - 86400000).toISOString(), table: "users", record_id: "USR-088", old_values: { "role": "teacher", "status": "active" }, new_values: null }
    ];

    window.auditDataMap = {};
    auditLogs.forEach(log => {
        if(log.id) window.auditDataMap[log.id] = log;
    });

    window.viewAuditDetails = function(id) {
        const log = window.auditDataMap[id];
        if(!log) return;
        document.getElementById('auditModalSub').textContent = `Actor: ${log.user_id || 'System'} | Action: ${log.message.split(' ')[0].toUpperCase()} | Table: ${log.table} (ID: ${log.record_id})`;
        document.getElementById('auditOldValues').textContent = log.old_values ? JSON.stringify(log.old_values, null, 2) : 'null (No previous data)';
        document.getElementById('auditNewValues').textContent = log.new_values ? JSON.stringify(log.new_values, null, 2) : 'null (Record deleted/No new data)';
        document.getElementById('auditModal').classList.add('active');
    };

    if(typeof window.closeModal !== 'function') {
        window.closeModal = function(id) {
            document.getElementById(id).classList.remove('active');
        };
    }
    const failedCount = s.failedLogins || 0;

    const failedLogins = [
        {ip:"103.42.88.201",email:"admin@everest.com",tenant:"Everest Loksewa",attempts:8,time:"10:42 AM",s:"pr"},
        {ip:"182.68.54.109",email:"test@test.com",tenant:"Unknown",attempts:12,time:"10:38 AM",s:"pr"},
        {ip:"27.34.101.222",email:"admin@kpcenter.com",tenant:"Kathmandu PSC",attempts:5,time:"10:21 AM",s:"py"},
    ];

    const failedTbl = document.getElementById("failedTbl");
    if (failedTbl) {
        failedTbl.innerHTML = failedLogins.map(r => `
          <tr>
            <td><span class="ip-cell" style="font-family:'Courier New',monospace;font-size:12px;color:var(--text-body);background:#f1f5f9;padding:2px 6px;border-radius:4px;">${r.ip}</span></td>
            <td style="font-size:12px;color:var(--text-body)">${r.email}</td>
            <td style="font-weight:600;color:var(--text-dark)">${r.tenant}</td>
            <td><span class="pill ${r.attempts>=10?"pr":r.attempts>=5?"py":"pb"}" style="font-size:10px">${r.attempts}x</span></td>
            <td style="font-size:11px;color:var(--text-light)">${r.time}</td>
            <td><span class="pill ${r.s}" style="font-size:9px">${r.s==="pr"?"Blocked":r.s==="py"?"CAPTCHA":"Allowed"}</span></td>
            <td><button class="btn btn-red btn-sm" onclick="showToast('IP ${r.ip} blocked!', 'error')"><i class="fa fa-ban"></i></button></td>
          </tr>`).join("");
    }

    const auditTbl = document.getElementById("auditTbl");
    if (auditTbl) {
        auditTbl.innerHTML = auditLogs.map(r => {
            const ac={"ERROR":"pr","WARNING":"py","INFO":"pb","SUCCESS":"pg"};
            const action = r.message.split(' ')[0].toUpperCase();
            return `<tr>
                <td style="font-weight:600">${r.user_id || 'System'}</td>
                <td><span class="pill ${ac[r.level]||"pb"}" style="font-size:9px">${action}</span></td>
                <td style="font-family:monospace;font-size:12px">${r.table || r.level}</td>
                <td style="font-size:12px;color:var(--text-dark);max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${r.record_id || r.message}">${r.record_id || r.message}</td>
                <td><span class="ip-cell">${r.ip_address || '-'}</span></td>
                <td style="font-size:11px;color:var(--text-light)">${new Date(r.time).toLocaleTimeString()}</td>
                <td><button class="btn bs btn-sm" onclick="viewAuditDetails(${r.id})">Details</button></td>
            </tr>`;
        }).join("");
    }

    const apiTbl = document.getElementById("apiTbl");
    if (apiTbl) {
        apiTbl.innerHTML = [
            {m:"POST",ep:"/api/v1/students",t:"Everest Loksewa",s:201,d:"42ms",sc:"pg"},
            {m:"GET",ep:"/api/v1/fee/report",t:"Dharan Civil",s:200,d:"284ms",sc:"pg"},
            {m:"POST",ep:"/api/v1/exams/attempt",t:"Kathmandu PSC",s:422,d:"18ms",sc:"pr"},
            {m:"GET",ep:"/api/v1/attendance",t:"Biratnagar",s:200,d:"31ms",sc:"pg"},
            {m:"POST",ep:"/api/v1/sms/send",t:"Chitwan LK",s:500,d:"5001ms",sc:"pr"},
        ].map(r => `<tr><td><span class="pill ${r.m==="GET"?"pb":"pp"}" style="font-size:9px">${r.m}</span></td><td style="font-family:monospace;font-size:12px">${r.ep}</td><td style="font-size:12px">${r.t}</td><td><span class="pill ${r.sc}" style="font-size:9px">${r.s}</span></td><td style="font-size:11px;color:var(--text-light)">${r.d}</td><td style="font-size:12px;font-weight:600;color:${parseInt(r.d)>1000?"#e11d48":"var(--text-dark)"}">${r.d}</td></tr>`).join("");
    }

    const errTbl = document.getElementById("errTbl");
    if (errTbl) {
        errTbl.innerHTML = [
            {lvl:"ERROR",msg:"Python subprocess timeout: report generation exceeded 30s",f:"ReportController.php:84",tm:"10:42 AM",c:"pr"},
            {lvl:"WARNING",msg:"Redis memory usage at 72% — approaching soft limit",f:"QueueWorker.php:201",tm:"09:30 AM",c:"py"},
            {lvl:"ERROR",msg:"ClamAV scan failed for upload: process not responding",f:"FileService.php:112",tm:"08:14 AM",c:"pr"},
            {lvl:"INFO",msg:"Scheduled backup completed: 18.4GB in 142 seconds",f:"BackupCommand.php:55",tm:"06:00 AM",c:"pb"},
        ].map(r => `<tr><td><span class="pill ${r.c}" style="font-size:9px">${r.lvl}</span></td><td style="font-size:12px;max-width:300px">${r.msg}</td><td style="font-family:monospace;font-size:11px;color:var(--text-light)">${r.f}</td><td style="font-size:11px;color:var(--text-light)">${r.tm}</td></tr>`).join("");
    }

    const flData = [4,2,6,3,1,8,5,12,9,14,18,22,16,20,25,18,14,10,8,12,16,14,10,8];
    const failChart = document.getElementById("failChart");
    if (failChart) {
        failChart.innerHTML = flData.map((v, i) => {
            const h = Math.round((v / 30) * 90);
            const c = v >= 15 ? "#e11d48" : v >= 10 ? "#d97706" : "#94A3B8";
            return `<div style="flex:1;height:${h}px;background:${c};border-radius:2px 2px 0 0;cursor:pointer;transition:0.2s" title="${v} failed logins at hour ${i}"></div>`;
        }).join("");
    }

    const topIPs = document.getElementById("topIPs");
    if (topIPs) {
        topIPs.innerHTML = [
            {ip:"103.42.88.201",cnt:23,loc:"Kathmandu, NP",s:"pr"},
            {ip:"182.68.54.109",cnt:17,loc:"Mumbai, IN",s:"pr"},
            {ip:"27.34.101.222",cnt:9,loc:"Bangalore, IN",s:"py"},
            {ip:"49.248.73.90",cnt:6,loc:"Dharan, NP",s:"pb"},
        ].map(r => `
            <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f1f5f9">
                <span class="ip-cell" style="font-family:'Courier New',monospace;font-size:12px;color:var(--text-body);background:#f1f5f9;padding:2px 6px;border-radius:4px;">${r.ip}</span>
                <div style="flex:1"><div style="font-size:11px;color:var(--text-light)">${r.loc}</div></div>
                <span class="tag ${r.s==="pr"?"bg-r":r.s==="py"?"bg-y":"bg-b"}" style="font-size:10px">${r.cnt} attempts</span>
                <button class="btn btn-red btn-sm" onclick="showToast('IP ${r.ip} blocked!', 'error')"><i class="fa fa-ban"></i></button>
            </div>`).join("");
    }

    window.switchLogTab = function(btn, tabId) {
        btn.parentElement.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        ["tab-failed", "tab-audit", "tab-api", "tab-errors"].forEach(t => {
            const el = document.getElementById(t); if (el) el.style.display = t === tabId ? "block" : "none";
        });
    };

    // Auto-switch tab based on activeSub
    if (window.activeSub) {
        const tabMap = { 'audit': 'tab-audit', 'errors': 'tab-errors', 'api': 'tab-api', 'failed': 'tab-failed' };
        const tid = tabMap[window.activeSub];
        if (tid) {
            const btn = document.querySelector(`.tab-btn[onclick*="${tid}"]`);
            if (btn) btn.click();
        }
    }

    // Populate Dynamic Stats
    if (document.getElementById('failedLoginVal')) document.getElementById('failedLoginVal').textContent = failedCount;
    if (document.getElementById('auditCountVal')) document.getElementById('auditCountVal').textContent = auditLogs.length + '+';
    if (document.getElementById('tabBtnFailed')) document.getElementById('tabBtnFailed').textContent = `Failed Logins (${failedCount})`;
    if (document.getElementById('tabBtnAudit')) document.getElementById('tabBtnAudit').textContent = `Audit Log (${auditLogs.length}+)`;
})();
</script>
</main>
<?php include 'footer.php'; ?>
