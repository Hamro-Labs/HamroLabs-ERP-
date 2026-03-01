<?php
/**
 * Hamro ERP — Support Tickets Partial
 */
require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pageTitle = 'Support Tickets';
$activePage = 'support.php';
?>

<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<main class="main" id="mainContent">
<div class="page fu">
  <div class="pg-hdr">
    <div class="pg-hdr-left">
      <div class="breadcrumb">
        <span class="bc-root" onclick="goNav('overview')">Dashboard</span>
        <span class="bc-sep">›</span>
        <span class="bc-cur">Support Tickets</span>
      </div>
      <h1 style="display:flex; align-items:center; gap:10px;">
        <i class="fa fa-ticket" style="color:var(--green); font-size:1.1rem;"></i>
        Support Tickets
      </h1>
      <p>Manage institute support requests and impersonation sessions</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <button class="btn bs" onclick="showToast('Exporting tickets...', 'info')"><i class="fa fa-download"></i> Export</button>
      <button class="btn bt" onclick="showToast('Creating new ticket...', 'info')"><i class="fa fa-plus"></i> New Ticket</button>
    </div>
  </div>

  <div class="stat-grid">
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-red"><i class="fa fa-circle-exclamation"></i></div><span class="stat-badge bg-r">Needs attention</span></div>
      <div class="stat-val" id="openTicketsVal">...</div>
      <div class="stat-lbl">Open Tickets</div>
      <div class="stat-sub" id="openTicketsSub"><i class="fa fa-triangle-exclamation" style="color:#e11d48"></i> 2 high priority</div>
    </div>
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-amber"><i class="fa fa-clock"></i></div><span class="stat-badge bg-y">Avg</span></div>
      <div class="stat-val">3.2h</div>
      <div class="stat-lbl">Avg First Response Time</div>
      <div class="stat-sub"><i class="fa fa-arrow-trend-down" style="color:#16a34a"></i> Down from 4.8h</div>
    </div>
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-green"><i class="fa fa-circle-check"></i></div><span class="stat-badge bg-g">This month</span></div>
      <div class="stat-val">0</div>
      <div class="stat-lbl">Tickets Resolved</div>
      <div class="stat-sub"><i class="fa fa-circle" style="color:#16a34a;font-size:7px"></i> 100% satisfaction</div>
    </div>
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-purple"><i class="fa fa-user-secret"></i></div><span class="stat-badge bg-p">Logged</span></div>
      <div class="stat-val">12</div>
      <div class="stat-lbl">Impersonation Sessions</div>
      <div class="stat-sub"><i class="fa fa-shield" style="color:var(--purple)"></i> All audited</div>
    </div>
  </div>

  <div class="tabs" style="display:flex;gap:4px;background:#f1f5f9;border-radius:10px;padding:4px;margin-bottom:16px">
    <button class="tab-btn active" id="tabBtnOpen" onclick="switchTicketTab(this,'tab-open')">Open</button>
    <button class="tab-btn" onclick="switchTicketTab(this,'tab-progress')">In Progress (0)</button>
    <button class="tab-btn" onclick="switchTicketTab(this,'tab-resolved')">Resolved (0)</button>
    <button class="tab-btn" onclick="switchTicketTab(this,'tab-impersonate')">Impersonation Log (4)</button>
  </div>

  <div class="g65">
    <div>
      <div class="toolbar">
        <div class="search-box"><i class="fa fa-search"></i><input class="search-inp" type="text" placeholder="Search tickets..."></div>
        <select class="filter-sel"><option>All Priority</option><option>High</option><option>Medium</option><option>Low</option></select>
      </div>
      <div id="tab-open"><div id="ticketList"></div></div>
      <div id="tab-progress" style="display:none"><div id="progressList"></div></div>
      <div id="tab-resolved" style="display:none"><div id="resolvedList"></div></div>
      <div id="tab-impersonate" style="display:none">
        <div class="tbl-wrap" style="margin-bottom:0">
          <div class="tbl-scroll"><table><thead><tr><th>Super Admin</th><th>Institute</th><th>Duration</th><th>Reason</th><th>Date</th><th>Status</th></tr></thead><tbody id="impersonateTbl"></tbody></table></div>
        </div>
      </div>
    </div>
    <div>
      <div class="card" style="margin-bottom:16px">
        <div class="ct"><i class="fa fa-chart-bar"></i> Tickets by Category</div>
        <div id="categoryBreak"></div>
      </div>
      <div class="card">
        <div class="ct"><i class="fa fa-clock-rotate-left"></i> Recent Activity</div>
        <div id="ticketActivity"></div>
      </div>
    </div>
  </div>
</div>

<script>
    const s = window.platformStatsData || {tickets: {critical:0, high:0, normal:0}};
    const tickets = [
        {id:"TK-1042",inst:"Everest Loksewa Classes",issue:"SMS not delivered to students after batch update",pri:"high",status:"open",created:"Feb 21, 10:30",category:"SMS / Notifications"},
        {id:"TK-1041",inst:"Dharan Civil Service Hub",issue:"PDF receipt not generating — Python worker timeout",pri:"high",status:"open",created:"Feb 21, 09:15",category:"Report Engine"},
        {id:"TK-1040",inst:"Kathmandu PSC Center",issue:"Exam module showing wrong rank for tie-scores",pri:"med",status:"open",created:"Feb 20, 16:45",category:"Exam Engine"},
        {id:"TK-1039",inst:"Birgunj Loksewa Academy",issue:"Student unable to login after password reset",pri:"med",status:"open",created:"Feb 20, 14:20",category:"Authentication"},
        {id:"TK-1038",inst:"Chitwan Loksewa Kendra",issue:"Attendance report export shows blank columns",pri:"low",status:"open",created:"Feb 19, 11:00",category:"Reports"},
        {id:"TK-1037",inst:"Nepalgunj Study Circle",issue:"Guardian portal not receiving SMS alerts",pri:"med",status:"open",created:"Feb 19, 08:30",category:"SMS / Notifications"},
        {id:"TK-1036",inst:"Biratnagar Nursing Prep",issue:"Fee receipt PDF missing institute logo",pri:"low",status:"open",created:"Feb 18, 15:00",category:"Report Engine"},
    ];

    const priColors = {"high": "pri-high", "med": "pri-med", "low": "pri-low"};
    const priPill = {"high": "pr", "med": "py", "low": "pb"};
    const priLabel = {"high": "High", "med": "Medium", "low": "Low"};

    function renderTickets(data, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = data.map(t => `
            <div class="ticket-card ${priColors[t.pri]}" style="background:#fff; border:1px solid var(--card-border); border-radius:10px; padding:16px; margin-bottom:10px; cursor:pointer; transition:0.2s; border-left:4px solid transparent;" onclick="showToast('Opening ticket ${t.id}...', 'info')">
                <style>
                    .ticket-card:hover { border-color: var(--green); box-shadow: var(--shadow-md); transform: translateX(2px); }
                    .ticket-card.pri-high { border-left-color: #e11d48; }
                    .ticket-card.pri-med { border-left-color: #d97706; }
                    .ticket-card.pri-low { border-left-color: #3b82f6; }
                </style>
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px">
                    <div style="font-size:13.5px;font-weight:700;color:var(--text-dark);flex:1">${t.issue}</div>
                    <span class="pill ${priPill[t.pri]}" style="font-size:9px;white-space:nowrap">${priLabel[t.pri]}</span>
                </div>
                <div class="ticket-meta" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:6px">
                    <span class="tag bg-b">${t.id}</span>
                    <span style="font-size:12px;color:var(--text-body);font-weight:600">${t.inst}</span>
                    <span class="tag bg-p">${t.category}</span>
                    <span style="font-size:11px;color:var(--text-light);margin-left:auto">${t.created}</span>
                </div>
                <div style="display:flex;gap:8px;margin-top:10px">
                    <button class="btn btn-blue btn-sm" onclick="event.stopPropagation();showToast('Viewing ticket...', 'info')"><i class="fa fa-eye"></i> View</button>
                    <button class="btn btn-purple btn-sm" onclick="event.stopPropagation();showToast('Impersonating admin for ${t.inst}...', 'info')"><i class="fa fa-user-secret"></i> Impersonate</button>
                    <button class="btn btn-green btn-sm" onclick="event.stopPropagation();showToast('Ticket resolved!', 'success')"><i class="fa fa-check"></i> Resolve</button>
                </div>
            </div>`).join("");
    }

    renderTickets(tickets, "ticketList");
    renderTickets([
        {id:"TK-1035",inst:"Pokhara Banking Classes",issue:"Bulk SMS import failing for Unicode characters",pri:"med",created:"Feb 18, 09:00",category:"SMS / Notifications"},
        {id:"TK-1034",inst:"Hetauda TSC Coaching",issue:"Student dashboard not loading on low-end Android",pri:"med",created:"Feb 17, 14:30",category:"PWA / Frontend"},
        {id:"TK-1033",inst:"Butwal Classes",issue:"Fee overdue alert not triggered at 7-day mark",pri:"low",created:"Feb 16, 10:00",category:"Fee Management"},
    ], "progressList");
    renderTickets([
        {id:"TK-1032",inst:"Dhankuta PSC Center",issue:"Cannot upload PDF study materials > 10MB",pri:"med",created:"Feb 16, 09:00",category:"File Storage"},
        {id:"TK-1031",inst:"Janakpur Civil Prep",issue:"Timetable builder conflict detection not working",pri:"low",created:"Feb 15, 11:30",category:"Academic"},
    ], "resolvedList");

    const impersonateTbl = document.getElementById("impersonateTbl");
    if (impersonateTbl) {
        impersonateTbl.innerHTML = [
            {a:"Super Admin",i:"Dharan Civil Service",d:"14 mins",r:"Report engine debug",dt:"Feb 21, 10:00",s:"pg"},
            {a:"Super Admin",i:"Everest Loksewa",d:"22 mins",r:"SMS gateway config fix",dt:"Feb 20, 15:30",s:"pg"},
            {a:"Super Admin",i:"Biratnagar Nursing",d:"8 mins",r:"Receipt template fix",dt:"Feb 19, 11:00",s:"pg"},
            {a:"Super Admin",i:"Kathmandu PSC",d:"31 mins",r:"Exam rank bug investigation",dt:"Feb 18, 14:00",s:"pg"},
        ].map(r => `<tr><td style="font-weight:600">${r.a}</td><td>${r.i}</td><td>${r.d}</td><td style="font-size:12px;color:var(--text-body)">${r.r}</td><td style="font-size:12px;color:var(--text-light)">${r.dt}</td><td><span class="pill ${r.s}">Ended</span></td></tr>`).join("");
    }

    const categoryBreak = document.getElementById("categoryBreak");
    if (categoryBreak) {
        categoryBreak.innerHTML = [
            {name:"SMS / Notifications",cnt:18,c:"var(--purple)"},
            {name:"Report Engine",cnt:14,c:"#3b82f6"},
            {name:"Exam Engine",cnt:9,c:"#d97706"},
            {name:"Authentication",cnt:7,c:"#e11d48"},
            {name:"Fee Management",cnt:6,c:"var(--green)"},
            {name:"PWA / Frontend",cnt:4,c:"#0d9488"},
        ].map(c => `<div style="margin-bottom:12px"><div style="display:flex;justify-content:space-between;font-size:12px;font-weight:600;margin-bottom:4px"><span>${c.name}</span><span style="color:var(--text-light)">${c.cnt}</span></div><div class="prog-t"><div class="prog-f" style="width:${Math.round((c.cnt / 18) * 100)}%;background:${c.c}"></div></div></div>`).join("");
    }

    const ticketActivity = document.getElementById("ticketActivity");
    if (ticketActivity) {
        ticketActivity.innerHTML = [
            {ico:"fa-ticket",col:"ic-red",txt:"New HIGH priority ticket: TK-1042 from Everest Loksewa",tm:"30 min ago"},
            {ico:"fa-user-secret",col:"ic-purple",txt:"Impersonation session started for Dharan Civil Service",tm:"1h ago"},
            {ico:"fa-circle-check",col:"ic-green",txt:"Ticket TK-1033 resolved by Super Admin",tm:"2h ago"},
            {ico:"fa-comment",col:"ic-blue",txt:"Reply sent to Birgunj Academy on TK-1039",tm:"3h ago"},
        ].map(a => `<div class="ai"><div class="ai-dot ${a.col}" style="font-size:13px"><i class="fa ${a.ico}"></i></div><div><div class="ai-txt">${a.txt}</div><div class="ai-tm">${a.tm}</div></div></div>`).join("");
    }

    window.switchTicketTab = function(btn, tabId) {
        btn.parentElement.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        ["tab-open", "tab-progress", "tab-resolved", "tab-impersonate"].forEach(t => {
            const el = document.getElementById(t); if (el) el.style.display = t === tabId ? "block" : "none";
        });
    };

    // Populate Dynamic Stats
    (function() {
        const openCount = (s.tickets && s.tickets.high + s.tickets.normal) || 0;
        if (document.getElementById('openTicketsVal')) document.getElementById('openTicketsVal').textContent = openCount;
        if (document.getElementById('tabBtnOpen')) document.getElementById('tabBtnOpen').textContent = `Open (${openCount})`;
    })();
</script>
</main>
<?php include 'footer.php'; ?>
