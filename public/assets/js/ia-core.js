/**
 * Hamro ERP — Institute Admin · ia-core.js
 * Core: shared state, sidebar, page routing
 * Load this file LAST, after all ia-*.js domain modules.
 *
 * Sidebar config is injected by PHP as window._IA_NAV_CONFIG
 * Badge counters injected as window._IA_BADGES
 */

/* ── STATE ──────────────────────────────────────────────────────── */
window._IA = {
    activeNav: 'overview',
    activeSub: null,
    expanded: JSON.parse(localStorage.getItem('_ia_expanded') || '{}'),
};

/**
 * Global helper to get the currency symbol. 
 * Prioritizes window._INSTITUTE_CONFIG, then window.INSTITUTE_CONFIG, fallback to ₹.
 */
window.getCurrencySymbol = function() {
    return window._INSTITUTE_CONFIG?.currency_symbol || window.INSTITUTE_CONFIG?.currency_symbol || '₹';
};

/* Build flat nav from PHP-injected config (for routing compatibility) */
function _iaBuildFlatNav() {
    const cfg = window._IA_NAV_CONFIG || [];
    const flat = [];
    cfg.forEach(section => {
        (section.items || []).forEach(item => {
            flat.push({
                id: item.id,
                icon: item.icon,
                label: item.label,
                sub: item.sub || null,
                sec: section.section,
                badge_key: item.badge_key || null,
            });
        });
    });
    return flat;
}

const _IA_NAV = _iaBuildFlatNav();

/* ── Save expanded state to localStorage ── */
function _iaSaveExpanded() {
    localStorage.setItem('_ia_expanded', JSON.stringify(_IA.expanded));
}

/* ── NAVIGATION ─────────────────────────────────────────────────── */
window.goNav = function(id, subId = null, params = null) {
    _IA.activeNav = id; _IA.activeSub = subId;
    const url = new URL(window.location);
    const pageVal = subId ? `${id}-${subId}` : id;
    url.search = ''; url.searchParams.set('page', pageVal);
    if (params) Object.keys(params).forEach(k => url.searchParams.set(k, params[k]));
    window.history.pushState({ pageVal }, '', url);
    if (window.innerWidth < 1024) document.body.classList.remove('sb-active');
    _iaRenderSidebar(); _iaRenderPage();
};

window.toggleExp = function(id) {
    _IA.expanded[id] = !_IA.expanded[id];
    _iaSaveExpanded();
    
    // Smooth toggle if element exists
    const el = document.getElementById(`sub-${id}`);
    const chev = document.querySelector(`[onclick="toggleExp('${id}')"] .nbc`);
    if (el) {
        if (_IA.expanded[id]) {
            el.style.display = 'block';
            setTimeout(() => el.classList.add('open'), 10);
            if (chev) chev.classList.add('open');
        } else {
            el.classList.remove('open');
            setTimeout(() => el.style.display = 'none', 200);
            if (chev) chev.classList.remove('open');
        }
    } else {
        _iaRenderSidebar();
    }
};

/* ── SIDEBAR (Mirrors Super Admin structure) ────────────────── */
function _iaRenderSidebar(filter = '') {
    const sbBody = document.getElementById('sbBody'); if (!sbBody) return;
    const badges = window._IA_BADGES || {};
    const sections = [...new Set(_IA_NAV.map(n => n.sec))];
    let html = '';

    sections.forEach(sec => {
        const items = _IA_NAV.filter(n => {
            if (n.sec !== sec) return false; if (!filter) return true;
            return n.label.toLowerCase().includes(filter) || (n.sub && n.sub.some(s => s.l.toLowerCase().includes(filter)));
        });
        if (!items.length) return;

        // Section Label
        html += `<div class="sb-lbl">${sec}</div>`;

        items.forEach(nav => {
            const hasSub = !!(nav.sub && nav.sub.length);
            const isActive = _IA.activeNav === nav.id;
            const isExp = filter ? true : _IA.expanded[nav.id];
            
            // Badge logic
            const badgeVal = nav.badge_key && badges[nav.badge_key] ? badges[nav.badge_key] : null;
            const badgeHtml = badgeVal ? `<span class="sb-badge" style="margin-left:auto; background:var(--red); color:#fff; font-size:10px; font-weight:800; padding:2px 6px; border-radius:10px;">${badgeVal}</span>` : '';

            if (hasSub) {
                // Parent Button with Submenu
                html += `
                    <button class="nb-btn ${isActive ? 'active' : ''}" onclick="toggleExp('${nav.id}')">
                        <i class="fa-solid ${nav.icon} nbi"></i>
                        <span class="nbl">${nav.label}</span>
                        ${badgeHtml}
                        <i class="fa fa-chevron-right nbc ${isExp ? 'open' : ''}" style="font-size:10px; margin-left:8px;"></i>
                    </button>
                    <div class="sub-menu ${isExp ? 'open' : ''}" id="sub-${nav.id}" style="${isExp ? '' : 'display:none;'}">
                `;

                nav.sub.forEach(s => {
                    if (filter && !s.l.toLowerCase().includes(filter) && !nav.label.toLowerCase().includes(filter)) return;
                    
                    const isSubActive = _IA.activeNav === nav.id && _IA.activeSub === s.id;
                    const subBadge = s.badge_key && badges[s.badge_key] ? `<span class="sb-badge sm" style="margin-left:auto; opacity:0.7;">${badges[s.badge_key]}</span>` : '';
                    
                    // Note: Super Admin uses <a> for sub-menu, but Institute Admin core uses <button> for JS routing.
                    // We'll stick to <button> for technical consistency but apply .sub-btn class.
                    const action = s.onclick ? s.onclick : `goNav('${nav.id}', '${s.id}')`;
                    html += `
                        <button class="sub-btn ${isSubActive ? 'active' : ''}" onclick="${action}">
                            <i class="fa-solid ${s.icon} smi" style="font-size:11px; margin-right:8px; opacity:0.6;"></i>
                            ${s.l}
                            ${subBadge}
                        </button>
                    `;
                    
                    // Special case for child nested items
                    if (s.child && isExp) {
                        s.child.forEach(c => {
                            const isChildActive = _IA.activeNav === nav.id && _IA.activeSub === c.id;
                            const childAction = c.onclick ? c.onclick : `goNav('${nav.id}', '${c.id}')`;
                            html += `
                                <button class="sub-btn child ${isChildActive ? 'active' : ''}" onclick="${childAction}" style="padding-left:60px; font-size:12px; opacity:0.8;">
                                    <i class="fa-solid ${c.icon} smi" style="font-size:10px; margin-right:6px; opacity:0.5;"></i>
                                    ${c.l}
                                </button>
                            `;
                        });
                    }
                });

                html += `</div>`;
            } else {
                // Direct Link Button
                html += `
                    <button class="nb-btn ${isActive ? 'active' : ''}" onclick="goNav('${nav.id}')">
                        <i class="fa-solid ${nav.icon} nbi"></i>
                        <span class="nbl">${nav.label}</span>
                        ${badgeHtml}
                    </button>
                `;
            }
        });
    });

    // PWA Install Box (Match Super Admin footer style if needed, or keep at bottom)
    html += `<div style="padding:15px 18px;"><button class="install-btn-trigger" onclick="openPwaModal()" style="width:100%; padding:10px; background:var(--teal-lt); color:var(--teal); border:none; border-radius:10px; font-weight:700; font-size:13px; cursor:pointer;"><i class="fa-solid fa-bolt"></i> App Install</button></div>`;
    
    sbBody.innerHTML = html;
    _iaRenderBottomNav();
}

function _iaRenderBottomNav() {
    let bNav = document.getElementById('bottomNav');
    if (!bNav) { bNav = document.createElement('nav'); bNav.id='bottomNav'; bNav.className='mobile-bottom-nav'; document.body.appendChild(bNav); }
    const items = [
        {id:'overview',icon:'fa-house',label:'Home',action:"goNav('overview')"},
        {id:'students',icon:'fa-user-graduate',label:'Students',action:"goNav('students','all')"},
        {id:'fee',icon:'fa-hand-holding-dollar',label:'Fee',action:"goNav('fee','record')"},
        {id:'exams',icon:'fa-file-signature',label:'Exams',action:"goNav('exams','schedule')"},
        {id:'comms',icon:'fa-paper-plane',label:'Comms',action:"goNav('comms','sms')"}
    ];
    bNav.innerHTML = items.map(i => `<button class="mb-nav-btn ${_IA.activeNav===i.id?'active':''}" onclick="${i.action}"><i class="fa-solid ${i.icon}"></i><span>${i.label}</span></button>`).join('');
}

/* ── PAGE ROUTER ────────────────────────────────────────────────── */
function _iaRenderPage() {
    const mc = document.getElementById('mainContent'); if (!mc) return;
    mc.innerHTML = '<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading...</span></div></div>';
    const urlParams = new URLSearchParams(window.location.search);
    const nav = _IA.activeNav, sub = _IA.activeSub;
    if (nav==='overview') { _iaRenderDashboard(); return; }
    if (nav==='students') { if(sub==='add') window.renderAddStudentForm?.(); else if(sub==='edit' || sub==='complete') window.renderEditStudentForm?.(urlParams.get('id')); else if(sub==='view') window.renderStudentProfile?.(urlParams.get('id')); else if(sub==='vault') window.renderDocumentVault?.(); else if(sub==='alumni') window.renderAlumniList?.(); else window.renderStudentList?.(); return; }
    if (nav==='academic') {
        if (sub==='courses') { if(urlParams.get('id')) window.renderEditCourseForm?.(urlParams.get('id')); else if(urlParams.get('action')==='add') window.renderAddCourseForm?.(); else window.renderCourseList?.(); return; }
        if (sub==='batches') { if(urlParams.get('id')) window.renderEditBatchForm?.(urlParams.get('id')); else if(urlParams.get('action')==='add') window.renderAddBatchForm?.(); else window.renderBatchList?.(); return; }
        if (sub==='subjects') { if(urlParams.get('id')) window.renderEditSubjectForm?.(urlParams.get('id')); else if(urlParams.get('action')==='add') window.renderAddSubjectForm?.(); else window.renderSubjectList?.(); return; }
        if (sub==='allocation') { window.renderSubjectAllocation?.(); return; }
        if (sub==='timetable') { window.renderTimetablePage?.(); return; }
        if (sub==='calendar') { window.renderAcademicCalendar?.(); return; }
    }
    if (nav==='inq') {
        if(sub==='list') window.renderInquiryList?.(); else if(sub==='add-inq') window.renderAddInquiryForm?.();
        else if(sub==='inq-analytics') window.renderInquiryAnalytics?.(); else if(sub==='adm-form') window.renderAdmissionForm?.();
        return;
    }
    if (nav==='exams') {
        if (sub==='create-ex')    { window.renderCreateExamForm?.(); return; }
        if (sub==='schedule' || sub==='results' || !sub) { window.renderExamList?.(); return; }
        if (sub==='qbank')        { mc.innerHTML=`<div class="pg fu"><div class="card" style="text-align:center;padding:100px 40px;"><i class="fa-solid fa-database" style="font-size:3rem;color:var(--purple);margin-bottom:20px;opacity:.5"></i><h2>Question Bank</h2><p style="color:var(--text-body);margin-top:10px">Coming in V3.1</p></div></div>`; return; }
        window.renderExamList?.(); return;
    }
    if (nav==='fee' && sub==='setup') { window.renderFeeSetup?.(); return; }
    if (nav==='fee' && sub==='record') { window.renderFeeRecord?.(); return; }
    if (nav==='fee' && sub==='outstanding') { window.renderFeeOutstanding?.(); return; }
    if (nav==='fee' && sub==='fin-reports') { window.renderFeeReports?.(); return; }
    if (nav==='fee' && sub==='ledger') { window.renderStudentLedger?.(urlParams.get('id')); return; }
    if (nav==='attendance') {
        if (sub==='take') { window.renderAttendanceTake?.(); return; }
        if (sub==='leave') { window.renderLeaveRequests?.(); return; }
        if (sub==='report') { window.renderAttendanceReport?.(); return; }
        window.renderAttendanceTake?.(); return;
    }
    if (nav==='teachers')  { 
        if(sub==='add') window.renderAddStaffForm?.('teacher'); 
        else if(sub==='allocation') window.renderSubjectAllocation?.();
        else window.renderStaffList?.('teacher'); 
        return; 
    }
    if (nav==='frontdesk') { sub==='add'?window.renderAddStaffForm?.('frontdesk'):window.renderStaffList?.('frontdesk'); return; }
    if (nav==='settings') {
        if (sub==='prof') { window.renderInstituteProfile?.(); return; }
        if (sub==='user-prof') { window.renderUserProfile?.(); return; }
        if (sub==='billing') { window.renderBillingSettings?.(); return; }
        if (sub==='em-tpls') { window.renderEmailTemplates?.(); return; }
        if (sub==='email') { window.renderEmailSettings?.(); return; }
        if (sub==='brand') { window.renderBrandingSettings?.(); return; }
        if (sub==='rbac') { window.renderRBACSettings?.(); return; }
        if (sub==='notif') { window.renderNotificationSettings?.(); return; }
        if (sub==='year') { window.renderAcademicYearSettings?.(); return; }
    }
    if (nav==='lms') {
        if (sub==='overview' || !sub) { window.renderLMSDashboard?.(); return; }
        if (sub==='materials' || sub==='videos' || sub==='assignments') { window.renderStudyMaterials?.(sub); return; }
        if (sub==='categories') { window.renderLMSCategories?.(); return; }
    }
    mc.innerHTML = `<div class="pg fu"><div class="card" style="text-align:center;padding:100px 40px;"><i class="fa-solid fa-cubes-stacked" style="font-size:3rem;color:var(--tl);margin-bottom:20px;"></i><h2>${(sub||nav).toUpperCase()} Module</h2><p style="color:var(--tb);margin-top:10px;">Coming soon in V3.1.</p></div></div>`;
}

/* ── DASHBOARD (FINALIZED UI) ────────────────────────────────────── */
function _iaRenderDashboardSkeleton() {
    const mc = document.getElementById('mainContent');
    if (!mc) return;

    mc.innerHTML = `
        <div class="welcome-banner skeleton" style="height:150px; background:var(--bg); overflow:hidden; position:relative;">
            <div class="skeleton-pulse" style="position:absolute; inset:0; background:linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); animation: skeleton-wave 1.5s infinite;"></div>
        </div>
        <div class="kpi-grid" style="grid-template-columns:repeat(3,1fr); gap:20px; margin-top:20px;">
            ${Array(6).fill(0).map(() => `<div class="card" style="height:120px; background:var(--bg); position:relative; overflow:hidden;"><div class="skeleton-pulse" style="position:absolute; inset:0; background:linear-gradient(90deg, transparent, rgba(0,0,0,0.05), transparent); animation: skeleton-wave 1.5s infinite;"></div></div>`).join('')}
        </div>
        <div class="main-grid" style="margin-top:20px;">
            <div class="card" style="height:400px; background:var(--bg); position:relative; overflow:hidden;"><div class="skeleton-pulse" style="position:absolute; inset:0; background:linear-gradient(90deg, transparent, rgba(0,0,0,0.05), transparent); animation: skeleton-wave 1.5s infinite;"></div></div>
            <div class="card" style="height:400px; background:var(--bg); position:relative; overflow:hidden;"><div class="skeleton-pulse" style="position:absolute; inset:0; background:linear-gradient(90deg, transparent, rgba(0,0,0,0.05), transparent); animation: skeleton-wave 1.5s infinite;"></div></div>
        </div>
        <style>
            @keyframes skeleton-wave { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
        </style>
    `;
}

async function _iaRenderDashboard() {
    const mc = document.getElementById('mainContent');
    if (!mc) return;

    _iaRenderDashboardSkeleton();

    // Use absolute URL defined globally in APP_URL
    const endpoint = window.APP_URL ? window.APP_URL + '/api/admin/stats' : '/api/admin/stats';

    try {
        const res = await fetch(endpoint);
        const result = await res.json(); 
        if (!result.success) throw new Error(result.message);
        const s = result.data;
        
        // Formatting helpers
        const formatMoney = num => new Intl.NumberFormat('en-IN').format(num || 0);

        // Calculate dynamic values for UI
        const todayFee = s.today_fee || 0;
        const outstanding = s.outstanding_dues || 0;
        const ttlStudents = s.total_students || 0;
        const attRate = s.attendance_rate || 0;

        // Fee aging with real data (Combine 61-90 and 90+ into 60+)
        const ag = s.fee_aging || {};
        const ag0  = ag['0_30']   || {amount:0, count:0};
        const ag31 = ag['31_60']  || {amount:0, count:0};
        const ag61 = ag['61_90']  || {amount:0, count:0};
        const ag90plus = ag['90plus'] || {amount:0, count:0};
        
        const ag60 = {
            amount: ag61.amount + ag90plus.amount,
            count: ag61.count + ag90plus.count
        };

        const totalAging = (ag0.amount + ag31.amount + ag60.amount) || 1;
        const ag0pct  = Math.max(5, Math.round((ag0.amount  / totalAging) * 100));
        const ag31pct = Math.max(5, Math.round((ag31.amount / totalAging) * 100));
        const ag60pct = Math.max(5, Math.round((ag60.amount / totalAging) * 100));

        // Next exam label
        const nextExamLabel = s.next_exam ? `Next: ${s.next_exam.title}` : 'None scheduled';

        mc.innerHTML = `
        <!-- WELCOME BANNER -->
        <div class="welcome-banner">
            <div class="wb-left">
                <div class="wb-greeting">${new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</div>
                <div class="wb-title">🌅 Good Morning, Admin!</div>
                <div class="wb-quote">"Education is not the filling of a pail, but the lighting of a fire." — W.B. Yeats</div>
            </div>
            <div class="wb-stats" style="position:relative;z-index:2;">
                <div class="wb-stat">
                    <div class="wb-stat-val">${ttlStudents}</div>
                    <div class="wb-stat-lbl">Total Students</div>
                </div>
                <div class="wb-stat">
                    <div class="wb-stat-val">${s.active_batches ?? '—'}</div>
                    <div class="wb-stat-lbl">Active Batches</div>
                </div>
                <div class="wb-stat">
                    <div class="wb-stat-val">${s.total_teachers ?? '—'}</div>
                    <div class="wb-stat-lbl">Teachers</div>
                </div>
            </div>
        </div>

        <!-- KPI CARDS (6 cards matching HTML reference) -->
        <div class="kpi-grid" style="grid-template-columns:repeat(3,1fr);">
            <div class="kpi-card green">
                <div class="kpi-top">
                    <div class="kpi-label">Active Students</div>
                    <div class="kpi-icon green"><i class="fa-solid fa-users"></i></div>
                </div>
                <div class="kpi-val">${ttlStudents}</div>
                <div class="kpi-sub"><i class="fa-solid fa-arrow-trend-up up"></i><span class="up">+${s.student_growth_percent||0}%</span> this month</div>
                <div class="progress-bar-wrap" style="margin-top:10px">
                    <div class="progress-bar"><div class="progress-bar-fill green" style="width:100%"></div></div>
                </div>
            </div>

            <div class="kpi-card red">
                <div class="kpi-top">
                    <div class="kpi-label">Today's Attendance</div>
                    <div class="kpi-icon red"><i class="fa-solid fa-calendar-check"></i></div>
                </div>
                <div class="kpi-val">${attRate}<small>%</small></div>
                <div class="kpi-sub"><i class="fa-solid fa-circle-exclamation down"></i>&nbsp;${s.attendance?.present || 0}/${s.attendance?.total || 0} students present</div>
                <div class="progress-bar-wrap" style="margin-top:10px">
                    <div class="progress-bar"><div class="progress-bar-fill red" style="width:${attRate}%"></div></div>
                </div>
            </div>

            <div class="kpi-card blue">
                <div class="kpi-top">
                    <div class="kpi-label">Today's Collection</div>
                    <div class="kpi-icon blue"><i class="fa-solid fa-money-bill-wave"></i></div>
                </div>
                <div class="kpi-val">₹<small> ${formatMoney(todayFee)}</small></div>
                <div class="kpi-sub"><i class="fa-solid fa-arrow-trend-up up"></i><span class="up">${s.today_fee_change_percent >= 0 ? '+' : ''}${s.today_fee_change_percent}%</span> since yesterday</div>
                <div class="mini-chart" style="margin-top:8px">
                    ${(s.revenue_trend || []).map(t => `<div class="mini-bar" style="height:${Math.max(20, Math.min(100, (t.amount / (Math.max(...s.revenue_trend.map(rt=>rt.amount))||1)) * 100))}%;background:#3b82f6;opacity:0.6"></div>`).join('')}
                </div>
            </div>

            <div class="kpi-card orange">
                <div class="kpi-top">
                    <div class="kpi-label">Outstanding Dues</div>
                    <div class="kpi-icon orange"><i class="fa-solid fa-triangle-exclamation"></i></div>
                </div>
                <div class="kpi-val">₹<small> ${formatMoney(outstanding)}</small></div>
                <div class="kpi-sub"><i class="fa-solid fa-users" style="color:var(--text-light)"></i>&nbsp;Pending receivables</div>
                <div class="progress-bar-wrap" style="margin-top:10px">
                    <div class="progress-bar"><div class="progress-bar-fill orange" style="width:35%"></div></div>
                </div>
            </div>

            <div class="kpi-card purple">
                <div class="kpi-top">
                    <div class="kpi-label">New Inquiries</div>
                    <div class="kpi-icon purple"><i class="fa-solid fa-magnifying-glass"></i></div>
                </div>
                <div class="kpi-val">${s.new_inquiries ?? 0}</div>
                <div class="kpi-sub"><i class="fa-solid fa-clock" style="color:var(--text-light)"></i>&nbsp;${s.followups_today ?? 0} follow-ups due today</div>
            </div>

            <div class="kpi-card teal">
                <div class="kpi-top">
                    <div class="kpi-label">Upcoming Exams</div>
                    <div class="kpi-icon teal"><i class="fa-solid fa-file-pen"></i></div>
                </div>
                <div class="kpi-val">${s.upcoming_exams ?? 0}</div>
                <div class="kpi-sub"><i class="fa-solid fa-calendar" style="color:var(--text-light)"></i>&nbsp;${nextExamLabel}</div>
            </div>
        </div>

        <!-- MAIN GRID -->
        <div class="main-grid">
            <!-- LEFT: REVENUE TREND & DAILY WORKFLOW -->
            <div>
                <!-- Revenue Charts -->
                <div class="section-hdr">
                    <h3><i class="fa-solid fa-chart-line" style="color:var(--green);margin-right:6px"></i>Revenue Trends</h3>
                    <a href="#" onclick="goNav('reports','fee-rep')">Full Analytics →</a>
                </div>
                <div class="card" style="margin-bottom:20px;">
                    <div class="card-body" style="height:250px;position:relative;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <!-- Daily Workflow Checklist -->
                <div class="section-hdr">
                    <h3><i class="fa-solid fa-list-check" style="color:var(--green);margin-right:6px"></i>Daily Workflow</h3>
                </div>
                <div class="card" style="margin-bottom:20px;">
                    <div class="card-body">
                        <div style="display:flex;flex-direction:column;gap:12px;">
                            ${(s.workflow || []).map((w, i) => {
                                const wdone = w.done ? 'checked' : '';
                                const clr  = w.done ? 'var(--text-light)' : 'var(--text-dark)';
                                const tdec = w.done ? 'line-through' : 'none';
                                return `<div style="display:flex;align-items:center;gap:10px;cursor:pointer;" onclick="_iaToggleWorkflow('${w.key}', this)">
                                    <input type="checkbox" class="wf-check" ${wdone} style="cursor:pointer;width:16px;height:16px;accent-color:var(--green)" disabled>
                                    <div style="flex:1">
                                        <div class="wf-title" style="font-size:13px;font-weight:600;color:${clr};text-decoration:${tdec}">${w.task}</div>
                                        <div style="font-size:11px;color:var(--text-body);">${w.desc}</div>
                                    </div>
                                    <div class="wf-spinner" style="display:none;font-size:12px;color:var(--green)"><i class="fa-solid fa-circle-notch fa-spin"></i></div>
                                </div>`;
                            }).join('')}
                        </div>
                    </div>
                </div>

                <!-- FEE OVERVIEW -->
                <div class="section-hdr" style="margin-top:18px">
                    <h3><i class="fa-solid fa-money-bill-wave" style="color:var(--green);margin-right:6px"></i>Fee Overview — This Month</h3>
                    <a href="#" onclick="goNav('fee','fin-reports')">Full Report →</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="fee-summary">
                            <div class="fee-sum-item collected">
                                <div class="fs-val">₹ ${formatMoney(s.monthly_collected || 0)}</div>
                                <div class="fs-lbl">Collected</div>
                            </div>
                            <div class="fee-sum-item outstanding">
                                <div class="fs-val">₹ ${formatMoney(outstanding)}</div>
                                <div class="fs-lbl">Outstanding</div>
                            </div>
                            <div class="fee-sum-item discount">
                                <div class="fs-val">₹ ${formatMoney(s.monthly_discount || 0)}</div>
                                <div class="fs-lbl">Discounts Given</div>
                            </div>
                        </div>

                        <div style="font-size:12px;font-weight:600;color:var(--text-body);margin-bottom:8px">Due Aging Report</div>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-label"><span>0–30 days (${ag0.count} students)</span><span style="font-weight:700;color:#22c55e">₹ ${formatMoney(ag0.amount)}</span></div>
                            <div class="progress-bar"><div class="progress-bar-fill green" style="width:${ag0pct}%"></div></div>
                        </div>
                        <div class="progress-bar-wrap" style="margin-top:8px">
                            <div class="progress-bar-label"><span>31–60 days (${ag31.count} students)</span><span style="font-weight:700;color:#f59e0b">₹ ${formatMoney(ag31.amount)}</span></div>
                            <div class="progress-bar"><div class="progress-bar-fill orange" style="width:${ag31pct}%"></div></div>
                        </div>
                        <div class="progress-bar-wrap" style="margin-top:8px">
                            <div class="progress-bar-label"><span>60+ days (${ag60.count} students)</span><span style="font-weight:700;color:var(--red)">₹ ${formatMoney(ag60.amount)}</span></div>
                            <div class="progress-bar"><div class="progress-bar-fill red" style="width:${ag60pct}%"></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL -->
            <div class="right-panel">
                <!-- QUICK ACTIONS -->
                <div class="card">
                    <div class="card-header">
                        <h4>⚡ Quick Actions</h4>
                    </div>
                    <div class="quick-actions">
                        <div class="qa-grid">
                            <div class="qa-btn green"  onclick="goNav('students','add')">  <i class="fa-solid fa-user-plus"></i>        <span>New Admission</span></div>
                            <div class="qa-btn blue"   onclick="goNav('fee','record')">     <i class="fa-solid fa-money-bill-wave"></i> <span>Collect Fee</span></div>
                            <div class="qa-btn purple" onclick="goNav('exams','create-ex')"><i class="fa-solid fa-file-pen"></i>         <span>Create Exam</span></div>
                            <div class="qa-btn orange" onclick="goNav('comms','sms')">     <i class="fa-solid fa-bell"></i>            <span>Send SMS</span></div>
                            <div class="qa-btn teal"   onclick="goNav('lms','materials')"> <i class="fa-solid fa-upload"></i>           <span>Upload Material</span></div>
                            <div class="qa-btn red"    onclick="goNav('reports','fee-rep')"><i class="fa-solid fa-chart-bar"></i>       <span>Download Report</span></div>
                        </div>
                    </div>
                </div>

                <!-- ACCOUNT ACTIVITY -->
                <div class="card">
                    <div class="card-header">
                        <h4>🔐 Account Activity</h4>
                        <span class="card-badge green">Secure</span>
                    </div>
                    <div class="card-body" style="padding:12px 16px">
                        <div style="display:flex;flex-direction:column;gap:8px">
                            <div style="background:var(--bg);border-radius:8px;padding:10px 12px;border:1px solid var(--card-border)">
                                <div style="font-size:10px;color:var(--text-light);font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Last Login</div>
                                <div style="font-size:12px;font-weight:600;color:var(--text-dark)">Session info from server</div>
                                <div style="font-size:10.5px;color:var(--text-light);margin-top:2px">Secured · HTTPS</div>
                            </div>
                            <div style="background:rgba(0,184,148,.06);border-radius:8px;padding:10px 12px;border:1px solid rgba(0,184,148,.2)">
                                <div style="font-size:10px;color:var(--green);font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Current Session</div>
                                <div style="font-size:12px;font-weight:600;color:var(--text-dark)">${new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</div>
                                <div style="font-size:10.5px;color:var(--text-light);margin-top:2px">Active · Browser</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SYSTEM ACTIVITY -->
                <div class="card">
                    <div class="card-header">
                        <h4>📌 System Activity</h4>
                        <span class="card-badge orange">Recent</span>
                    </div>
                    <div class="activity-list">
                        ${(s.recent_activity || []).slice(0, 10).map(a => {
                            const timeStr = new Date(a.time).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
                            const icon = a.type === 'payment' ? 'fa-money-bill-wave' : (a.type === 'inquiry' ? 'fa-magnifying-glass' : 'fa-bolt');
                            const clr = a.type === 'payment' ? 'var(--blue)' : (a.type === 'inquiry' ? 'var(--purple)' : 'var(--green)');
                            
                            return `<div class="act-item">
                                <div class="act-dot" style="background:var(--bg);color:${clr}"><i class="fa-solid ${icon}" style="font-size:11px"></i></div>
                                <div class="act-content">
                                    <div class="act-text"><strong>${a.title}</strong></div>
                                    <div class="act-desc" style="font-size:11px;color:var(--text-body)">${a.desc}</div>
                                    <div class="act-time" style="font-size:10px;color:var(--text-light);margin-top:2px">${a.user} · ${timeStr}</div>
                                </div>
                            </div>`;
                        }).join('')}
                    </div>
                </div>
            </div>
        </div>
        `;

        // Initialize Charts
        setTimeout(() => _iaInitRevenueChart(s.revenue_trend), 150);

        // Bind interactions
        _iaBindDashboardInteractions();

    } catch(err) {
        mc.innerHTML = `<div class="card" style="padding:40px;text-align:center;color:var(--red);"><i class="fa-solid fa-circle-exclamation" style="font-size:3rem;margin-bottom:16px;"></i><h3>Failed to load dashboard</h3><p>${err.message}</p><button class="qa-btn" style="margin:20px auto 0;" onclick="_iaRenderDashboard()">Retry</button></div>`;
    }
}

async function _iaToggleWorkflow(taskKey, el) {
    const check = el.querySelector('.wf-check');
    const title = el.querySelector('.wf-title');
    const spinner = el.querySelector('.wf-spinner');
    
    const isCompleted = !check.checked;
    
    // Show spinner
    if (spinner) spinner.style.display = 'block';
    
    try {
        const endpoint = (window.APP_URL || '') + '/api/admin/dashboard_stats.php?action=workflow';
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                task_key: taskKey,
                is_completed: isCompleted,
                task_name: title.innerText
            })
        });
        
        const result = await res.json();
        if (result.success) {
            check.checked = isCompleted;
            if (isCompleted) {
                title.style.color = 'var(--text-light)';
                title.style.textDecoration = 'line-through';
            } else {
                 title.style.color = 'var(--text-dark)';
                 title.style.textDecoration = 'none';
            }
        } else {
            alert('Failed to update workflow: ' + result.message);
        }
    } catch (err) {
        console.error('Workflow update error:', err);
    } finally {
        if (spinner) spinner.style.display = 'none';
    }
}

function _iaInitRevenueChart(trendData) {
    if(!trendData || !trendData.length) return;
    const canvas = document.getElementById('revenueChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    
    const labels = trendData.map(d => d.month);
    const data   = trendData.map(d => d.amount);

    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(0, 184, 148, 0.4)');
    gradient.addColorStop(1, 'rgba(0, 184, 148, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue (NPR)',
                data: data,
                borderColor: '#00B894',
                borderWidth: 3,
                fill: true,
                backgroundColor: gradient,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#00B894',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#f1f5f9' },
                    ticks: { callback: v => getCurrencySymbol() + (v/1000) + 'K' }
                },
                x: { grid: { display: false } }
            }
        }
    });
}

function _iaBindDashboardInteractions() {
     // Animate progress bars on load
    document.querySelectorAll('.progress-bar-fill').forEach(bar => {
        const target = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => { bar.style.width = target; }, 200);
    });
}

/* ── DOM READY ──────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    const pv = (new URLSearchParams(window.location.search).get('page')) || 'overview';
    _IA.activeNav = pv.includes('-') ? pv.split('-')[0] : pv;
    _IA.activeSub = pv.includes('-') ? pv.split('-')[1] : null;

    // ── Sidebar toggle (desktop collapse + mobile drawer) ──
    const sbToggle = document.getElementById('sbToggle');
    const sbClose = document.getElementById('sbClose');
    const sbOverlay = document.getElementById('sbOverlay');
    const sbSearch = document.getElementById('sbSearch');

    const toggleSB = () => {
        if (window.innerWidth >= 1024) {
            document.body.classList.toggle('sb-collapsed');
            localStorage.setItem('_ia_sb_collapsed', document.body.classList.contains('sb-collapsed') ? '1' : '0');
        } else {
            document.body.classList.toggle('sb-active');
        }
    };

    // Restore collapsed state on desktop
    if (window.innerWidth >= 1024 && localStorage.getItem('_ia_sb_collapsed') === '1') {
        document.body.classList.add('sb-collapsed');
    }

    if (sbToggle)  sbToggle.addEventListener('click', toggleSB);
    if (sbClose)   sbClose.addEventListener('click', () => document.body.classList.remove('sb-active'));
    if (sbOverlay) sbOverlay.addEventListener('click', () => document.body.classList.remove('sb-active'));
    
    // Global Search Functionality
    if (sbSearch) {
        let searchTimeout = null;
        let searchResultsDropdown = null;
        
        // Create search results dropdown
        const createSearchDropdown = () => {
            if (searchResultsDropdown) return searchResultsDropdown;
            searchResultsDropdown = document.createElement('div');
            searchResultsDropdown.id = 'global-search-results';
            searchResultsDropdown.style.cssText = `
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                max-height: 400px;
                overflow-y: auto;
                z-index: 9999;
                display: none;
                margin-top: 8px;
            `;
            const searchContainer = sbSearch.parentElement;
            searchContainer.style.position = 'relative';
            searchContainer.appendChild(searchResultsDropdown);
            return searchResultsDropdown;
        };
        
        // Perform global search
        const performSearch = async (query) => {
            if (query.length < 2) {
                const dropdown = createSearchDropdown();
                dropdown.style.display = 'none';
                return;
            }
            
            try {
                const baseUrl = typeof APP_URL !== 'undefined' ? APP_URL : (typeof window.APP_URL !== 'undefined' ? window.APP_URL : '');
                const url = `${baseUrl}/api/admin/global-search?q=${encodeURIComponent(query)}`;
                const res = await fetch(url);
                const data = await res.json();
                
                if (data.success) {
                    displaySearchResults(data);
                }
            } catch (err) {
                console.error('Search error:', err);
            }
        };
        
        // Display search results
        const displaySearchResults = (data) => {
            const dropdown = createSearchDropdown();
            let html = '';
            
            // Students section
            if (data.students && data.students.length > 0) {
                html += `<div class="gs-section"><div class="gs-section-title">Students</div>`;
                data.students.forEach(s => {
                    const meta = s.roll_no ? `Roll: ${s.roll_no}` : (s.email || '');
                    html += `<a href="#" class="gs-item" data-type="student" data-id="${s.id}">
                        <span class="gs-icon">🎓</span>
                        <span class="gs-name">${s.name}</span>
                        <span class="gs-meta">${meta}</span>
                    </a>`;
                });
                html += `</div>`;
            }
            
            // Teachers/Staff section
            if (data.teachers && data.teachers.length > 0) {
                html += `<div class="gs-section"><div class="gs-section-title">Teachers/Staff</div>`;
                data.teachers.forEach(t => {
                    html += `<a href="#" class="gs-item" data-type="teacher" data-id="${t.id}">
                        <span class="gs-icon">👨‍🏫</span>
                        <span class="gs-name">${t.name}</span>
                        <span class="gs-meta">${t.role || ''}</span>
                    </a>`;
                });
                html += `</div>`;
            }
            
            // Batches section
            if (data.batches && data.batches.length > 0) {
                html += `<div class="gs-section"><div class="gs-section-title">Batches</div>`;
                data.batches.forEach(b => {
                    html += `<a href="#" class="gs-item" data-type="batch" data-id="${b.id}">
                        <span class="gs-icon">📚</span>
                        <span class="gs-name">${b.name}</span>
                        <span class="gs-meta">${b.course_name || ''}</span>
                    </a>`;
                });
                html += `</div>`;
            }
            
            // Courses section
            if (data.courses && data.courses.length > 0) {
                html += `<div class="gs-section"><div class="gs-section-title">Courses</div>`;
                data.courses.forEach(c => {
                    html += `<a href="#" class="gs-item" data-type="course" data-id="${c.id}">
                        <span class="gs-icon">📖</span>
                        <span class="gs-name">${c.name}</span>
                    </a>`;
                });
                html += `</div>`;
            }
            
            if (!html) {
                html = '<div class="gs-empty">No results found</div>';
            }
            
            dropdown.innerHTML = html;
            dropdown.style.display = 'block';
            
            // Add click handlers for results
            dropdown.querySelectorAll('.gs-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    const type = item.dataset.type;
                    const id = item.dataset.id;
                    handleSearchResultClick(type, id);
                });
            });
        };
        
        // Handle search result click - navigate to relevant page
        const handleSearchResultClick = (type, id) => {
            const dropdown = createSearchDropdown();
            dropdown.style.display = 'none';
            sbSearch.value = '';
            
            switch(type) {
                case 'student':
                    goNav('students', null, { id: id, action: 'view' });
                    break;
                case 'teacher':
                    goNav('staff', null, { id: id, action: 'view' });
                    break;
                case 'batch':
                    goNav('batches', null, { id: id, action: 'view' });
                    break;
                case 'course':
                    goNav('courses', null, { id: id, action: 'view' });
                    break;
            }
        };
        
        // Debounced search input handler
        sbSearch.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            
            // Clear previous timeout
            if (searchTimeout) clearTimeout(searchTimeout);
            
            if (query.length === 0) {
                const dropdown = createSearchDropdown();
                dropdown.style.display = 'none';
                return;
            }
            
            // Debounce search by 300ms
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (searchResultsDropdown && !sbSearch.contains(e.target) && !searchResultsDropdown.contains(e.target)) {
                searchResultsDropdown.style.display = 'none';
            }
        });
        
        // Keep dropdown open when clicking inside it
        if (searchResultsDropdown) {
            searchResultsDropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }
    }

    window.addEventListener('popstate', e => {
        const p = (e.state && e.state.pageVal) ? e.state.pageVal : (new URLSearchParams(window.location.search).get('page') || 'overview');
        _IA.activeNav = p.includes('-') ? p.split('-')[0] : p;
        _IA.activeSub = p.includes('-') ? p.split('-')[1] : null;
        _iaRenderSidebar(); _iaRenderPage();
    });

    const uc = document.getElementById('userChip'), ud = document.getElementById('userDropdown');
    if (uc && ud) {
        uc.addEventListener('click', e => { e.stopPropagation(); ud.classList.toggle('active'); });
        document.addEventListener('click', () => ud.classList.remove('active'));
    }

    _iaRenderSidebar(); _iaRenderPage();
});
