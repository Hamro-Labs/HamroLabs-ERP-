/**
 * Hamro ERP — Front Desk Operator
 * Production Blueprint V3.0 — Implementation (LIGHT THEME)
 */

document.addEventListener('DOMContentLoaded', () => {
    // ── STATE ──
    const urlParams = new URLSearchParams(window.location.search);
    const initialPage = urlParams.get('page');
    let activeNav = initialPage || 'dashboard';
    let expanded = { inquiries: true, admissions: true, fee: true, library: false, notifs: false };

    // ── ELEMENTS ──
    const mainContent = document.getElementById('mainContent');
    const sbBody = document.getElementById('sbBody');
    const sbToggle = document.getElementById('sbToggle');
    const sbClose = document.getElementById('sbClose');
    const sbOverlay = document.getElementById('sbOverlay');

    // ── SIDEBAR TOGGLE ──
    const toggleSidebar = () => document.body.classList.toggle('sb-active');
    const closeSidebar = () => document.body.classList.remove('sb-active');

    if (sbToggle) sbToggle.addEventListener('click', toggleSidebar);
    if (sbClose) sbClose.addEventListener('click', closeSidebar);
    if (sbOverlay) sbOverlay.addEventListener('click', closeSidebar);

    // ── NAVIGATION TREE ──
    const NAV = [
        { id: "dashboard", icon: "fa-house", label: "Dashboard", sub: null, sec: "MAIN" },
        { id: "inquiries", icon: "fa-magnifying-glass", label: "Inquiries", sub: [
            { id: "inq-list", l: "Inquiry List", icon: "fa-list" },
            { id: "inq-add", l: "Add New Inquiry", icon: "fa-plus" },
            { id: "inq-rem", l: "Follow-Up Reminders", icon: "fa-bell" },
            { id: "inq-rep", l: "Conversion Report", icon: "fa-chart-pie" }
        ], sec: "OPERATIONS" },
        { id: "admissions", icon: "fa-user-graduate", label: "Admissions", sub: [
            { id: "adm-form", l: "New Admission Form", icon: "fa-file-signature" },
            { id: "adm-all", l: "All Students", icon: "fa-users" },
            { id: "adm-id", l: "ID Card Generator", icon: "fa-id-card" },
            { id: "adm-doc", l: "Document Verification", icon: "fa-clipboard-check" }
        ], sec: "OPERATIONS" },
        { id: "fee", icon: "fa-money-bill-wave", label: "Fee Collection", sub: [
            { id: "fee-coll", l: "Collect Payment", icon: "fa-hand-holding-dollar" },
            { id: "fee-out", l: "Outstanding Dues", icon: "fa-clock" },
            { id: "fee-rcp", l: "Receipt History", icon: "fa-receipt" },
            { id: "fee-sum", l: "Daily Collection Summary", icon: "fa-table-list" }
        ], sec: "OPERATIONS" },
        { id: "library", icon: "fa-book", label: "Library", sub: [
            { id: "lib-issue", l: "Issue Book", icon: "fa-book-open" },
            { id: "lib-return", l: "Return Book", icon: "fa-arrow-rotate-left" },
            { id: "lib-overdue", l: "Overdue List", icon: "fa-triangle-exclamation" }
        ], sec: "OPERATIONS" },
        { id: "notifs", icon: "fa-paper-plane", label: "Notifications", sub: [
            { id: "sms-send", l: "Send SMS", icon: "fa-message" },
            { id: "email-send", l: "Send Email", icon: "fa-envelope" },
            { id: "notif-hist", l: "Notification History", icon: "fa-clock-rotate-left" }
        ], sec: "COMMUNICATION" },
        { id: "academic", icon: "fa-graduation-cap", label: "Academic", sub: [
            { id: "batches", l: "Batches & Schedule", icon: "fa-users-line" },
            { id: "batch-status", l: "Batch Availability", icon: "fa-chart-pie" },
            { id: "att-mark", l: "Mark Attendance", icon: "fa-clipboard-check" },
            { id: "att-rep", l: "Attendance Report", icon: "fa-chart-line" }
        ], sec: "ACADEMIC" },
        { id: "reports", icon: "fa-chart-column", label: "Reports", sub: [
            { id: "rep-daily", l: "Daily Operations", icon: "fa-calendar-day" },
            { id: "rep-rev", l: "Revenue Analysis", icon: "fa-money-bill-trend-up" },
            { id: "rep-enr", l: "Enrollment Trends", icon: "fa-users-line" },
            { id: "rep-fee", l: "Fee Status", icon: "fa-file-invoice" }
        ], sec: "REPORTS" },
        { id: "settings", icon: "fa-gear", label: "Settings", sub: [
            { id: "profile", l: "My Profile", icon: "fa-user-gear" },
            { id: "password", l: "Security Settings", icon: "fa-key" }
        ], sec: "SETTINGS" }
    ];

    // ── NAVIGATION LOGIC ──
    window.goNav = (id, subActive = null) => {
        activeNav = subActive ? `${id}-${subActive}` : id;

        // Update URL via pushState
        const url = new URL(window.location);
        url.searchParams.set('page', activeNav);
        window.history.pushState({ activeNav }, '', url);

        if (window.innerWidth < 1024) closeSidebar();
        renderSidebar();
        renderPage();
    };

    // Handle Browser Back/Forward
    window.addEventListener('popstate', (e) => {
        if (e.state && e.state.activeNav) {
            activeNav = e.state.activeNav;
        } else {
            const urlParams = new URLSearchParams(window.location.search);
            activeNav = urlParams.get('page') || 'dashboard';
        }
        renderSidebar();
        renderPage();
    });

    window.toggleExp = (id) => {
        expanded[id] = !expanded[id];
        renderSidebar();
    };

    function renderSidebar() {
        const sections = [...new Set(NAV.map(n => n.sec))];
        let html = '';

        sections.forEach(sec => {
            html += `<div class="sb-sec"><div class="sb-sec-lbl">${sec}</div>`;

            NAV.filter(n => n.sec === sec).forEach(nav => {
                const isActive = activeNav === nav.id || activeNav.startsWith(nav.id + '-');
                const isExp = expanded[nav.id];

                html += `<div class="sb-item">
                    <button class="nb-btn ${isActive ? 'active' : ''}" onclick="${nav.sub ? `toggleExp('${nav.id}')` : `goNav('${nav.id}')`}">
                        <i class="fa-solid ${nav.icon}"></i>
                        <span class="nb-lbl">${nav.label}</span>
                        ${nav.sub ? `<i class="fa-solid fa-chevron-right nbc ${isExp ? 'open' : ''}"></i>` : ''}
                    </button>`;

                if (nav.sub && isExp) {
                    html += `<div class="sub-menu">`;
                    nav.sub.forEach(s => {
                        const isSubActive = activeNav === `${nav.id}-${s.id}`;
                        html += `<button class="sub-btn ${isSubActive ? 'active' : ''}" onclick="goNav('${nav.id}', '${s.id}')">
                            <i class="fa-solid ${s.icon}" style="width:14px; text-align:center; font-size:11px; opacity:0.7;"></i>
                            ${s.l}
                        </button>`;
                    });
                    html += `</div>`;
                }
                html += `</div>`;
            });
            html += `</div>`;
        });

        // Append Install App Button
        html += `
            <div class="sb-install-box">
                <button class="install-btn-trigger" onclick="openPwaModal()">
                    <i class="fa-solid fa-bolt"></i>
                    <span>Instant Install</span>
                </button>
            </div>
        `;

        sbBody.innerHTML = html;
        renderBottomNav();
    }

    function renderBottomNav() {
        let bNav = document.getElementById('bottomNav');
        if (!bNav) {
            bNav = document.createElement('nav');
            bNav.id = 'bottomNav';
            bNav.className = 'mobile-bottom-nav';
            document.body.appendChild(bNav);
        }

        const items = [
            { id: 'dashboard', icon: 'fa-house', label: 'Home', action: "goNav('dashboard')" },
            { id: 'inquiries', icon: 'fa-magnifying-glass', label: 'Inquiries', action: "goNav('inquiries', 'inq-list')" },
            { id: 'admissions', icon: 'fa-user-graduate', label: 'Admissions', action: "goNav('admissions', 'adm-all')" },
            { id: 'fee', icon: 'fa-money-bill-wave', label: 'Fee', action: "goNav('fee', 'fee-coll')" },
            { id: 'notifs', icon: 'fa-paper-plane', label: 'Notices', action: "goNav('notifs', 'sms-send')" }
        ];

        let html = '';
        items.forEach(item => {
            const isActive = activeNav === item.id || (typeof activeNav === 'string' && activeNav.startsWith(item.id + '-'));
            html += `<button class="mb-nav-btn ${isActive ? 'active' : ''}" onclick="${item.action}">
                <i class="fa-solid ${item.icon}"></i>
                <span>${item.label}</span>
            </button>`;
        });
        bNav.innerHTML = html;
    }

    // ── PAGE RENDERING ──
    function renderPage() {
        // Reset scroll
        window.scrollTo(0, 0);
        mainContent.innerHTML = '<div class="pg fu">Loading...</div>';

        // High priority exact matches
        if (activeNav === 'dashboard') {
            renderDashboard();
            return;
        }

        // Inquiries Routes
        if (activeNav.startsWith('inquiries')) {
            const parts = activeNav.split('-');
            const sub = parts[1] || 'inq-list'; // Default to list if just 'inquiries'
            
            if (sub === 'inq-list') renderInquiryList();
            else if (sub === 'inq-add') renderInquiryAdd();
            else if (sub === 'inq-rem') renderInquiryFollowups();
            else if (sub === 'inq-rep') renderInquiryReport();
            return;
        }

        // Admissions Routes
        if (activeNav.startsWith('admissions')) {
            const parts = activeNav.split('-');
            const sub = parts[1] || 'adm-all'; // Default to all students
            
            if (sub === 'adm-form') renderAdmissionForm();
            else if (sub === 'adm-all') renderAllStudents();
            else if (sub === 'adm-id') renderIDCardGen();
            else if (sub === 'adm-doc') renderDocVerify();
            return;
        }

        // Fee Routes
        if (activeNav.startsWith('fee')) {
            const parts = activeNav.split('-');
            const sub = parts[1] || 'fee-coll';
            
            if (sub === 'fee-coll') renderFeeRecord();
            else if (sub === 'fee-out') renderFeeOutstanding();
            else if (sub === 'fee-rcp') renderRecentPayments();
            else if (sub === 'fee-sum') renderDailySummary();
            return;
        }

        // Library Routes
        if (activeNav.startsWith('library')) {
            const parts = activeNav.split('-');
            const sub = parts[1] || 'lib-issue';
            
            if (sub === 'lib-issue') renderBookIssue();
            else if (sub === 'lib-return') renderBookReturn();
            else if (sub === 'lib-overdue') renderBookOverdue();
            return;
        }

        // Communication Routes
        if (activeNav.startsWith('notifs')) {
            const parts = activeNav.split('-');
            const sub = parts[1] || 'sms-send';
            
            if (sub === 'sms-send') renderSMSSender();
            else if (sub === 'email-send') renderEmailSender();
            else if (sub === 'notif-hist') renderNotifHistory();
            return;
        }

        // Academic Routes
        if (activeNav.startsWith('academic')) {
            const parts = activeNav.split('-');
            const sub = parts[1] || 'batches';
            
            if (sub === 'batches') renderBatches();
            else if (sub === 'batch-status') renderBatchStatus();
            else if (sub === 'att-mark') renderMarkAttendance();
            else if (sub === 'att-rep') renderAttendanceReport();
            return;
        }

        // Reports Routes
        if (activeNav.startsWith('reports')) {
            const parts = activeNav.split('-');
            const sub = parts[1] || 'rep-daily';
            
            if (sub === 'rep-daily') renderDailyOpsRep();
            else if (sub === 'rep-rev') renderRevenueRep();
            else if (sub === 'rep-enr') renderEnrollmentRep();
            else if (sub === 'rep-fee') renderFeeRep();
            return;
        }

        // Settings Routes
        if (activeNav.startsWith('settings')) {
            const parts = activeNav.split('-');
            const sub = parts[1] || 'profile';
            
            if (sub === 'profile') renderProfile();
            else if (sub === 'password') renderPassword();
            return;
        }

        // Default fallback to dashboard if unknown route
        renderDashboard();
    }

    async function renderDashboard() {
        mainContent.innerHTML = `
            <div class="pg fu">
                <div class="dashboard-hero skeleton" style="height: 180px; margin-bottom: 24px; border: none;"></div>
                
                <div class="sg mb" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
                    <div class="sc skeleton" style="height: 120px; border: none;"></div>
                    <div class="sc skeleton" style="height: 120px; border: none;"></div>
                    <div class="sc skeleton" style="height: 120px; border: none;"></div>
                    <div class="sc skeleton" style="height: 120px; border: none;"></div>
                </div>

                <div class="card skeleton" style="height: 300px; border: none;"></div>
            </div>
        `;

        try {
            const res = await fetch(`${APP_URL}/api/frontdesk/stats`);
            const result = await res.json();

            if (!result.success) throw new Error(result.message);

            const s = result.data;

            mainContent.innerHTML = `
                <div class="pg fu">
                    <div class="dashboard-hero">
                        <div class="hero-content">
                            <h2>Welcome back, ${result.user?.name || 'Operator'}!</h2>
                            <p>Here's what's happening at ${window.APP_CONFIG?.tenantName || result.tenant_name || 'Institute'} today. You have ${s.unassigned_students} students pending batch assignment.</p>
                            <div class="hero-acts">
                                <button class="btn bt" onclick="goNav('admissions', 'adm-form')">
                                    <i class="fa-solid fa-plus"></i> Quick Admission
                                </button>
                                <button class="btn bs" style="background:rgba(255,255,255,0.2); color:#fff; border:none;" onclick="goNav('fee', 'fee-coll')">
                                    <i class="fa-solid fa-receipt"></i> Collect Fee
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pg-head" style="border:none; margin-bottom:10px;">
                        <div class="pg-title" style="font-size:1.2rem;">Operations Overview</div>
                    </div>

                    <!-- QUICK ACTIONS -->
                    <div class="qa-grid">
                        <button class="qa-btn" onclick="goNav('admissions', 'adm-form')">
                            <div class="qa-ico ic-blue"><i class="fa-solid fa-user-plus"></i></div>
                            <div class="qa-lbl">New Admission</div>
                        </button>
                        <button class="qa-btn" onclick="goNav('fee', 'fee-coll')">
                            <div class="qa-ico ic-teal"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                            <div class="qa-lbl">Collect Fee</div>
                        </button>
                        <button class="qa-btn" onclick="goNav('library', 'lib-issue')">
                            <div class="qa-ico ic-purple"><i class="fa-solid fa-book"></i></div>
                            <div class="qa-lbl">Issue Book</div>
                        </button>
                        <button class="qa-btn" onclick="goNav('notifs', 'sms-send')">
                            <div class="qa-ico ic-amber"><i class="fa-solid fa-comment-sms"></i></div>
                            <div class="qa-lbl">Send SMS</div>
                        </button>
                        <button class="qa-btn" onclick="goNav('admissions', 'adm-id')">
                            <div class="qa-ico ic-navy"><i class="fa-solid fa-id-card"></i></div>
                            <div class="qa-lbl">Generate ID Card</div>
                        </button>
                    </div>

                    <!-- STAT GRID -->
                    <div class="sg">
                        <div class="sc">
                            <div class="sc-top"><div class="sc-ico ic-teal"><i class="fa-solid fa-graduation-cap"></i></div><div class="bdg bg-t">+${s.today_checkins} today</div></div>
                            <div class="sc-val">${s.total_students}</div>
                            <div class="sc-lbl">Active Students</div>
                            <div class="sc-delta">${s.unassigned_students} pending batch assignment</div>
                        </div>
                        <div class="sc">
                            <div class="sc-top"><div class="sc-ico ic-blue"><i class="fa-solid fa-money-bill-trend-up"></i></div><div class="bdg bg-t">Live Status</div></div>
                            <div class="sc-val">NPR ${formatMoney(s.today_revenue)}</div>
                            <div class="sc-lbl">Today's Fee Collection</div>
                            <div class="sc-delta">${s.today_transactions.length} transactions today</div>
                        </div>
                        <div class="sc">
                            <div class="sc-top"><div class="sc-ico ic-amber"><i class="fa-solid fa-phone-volume"></i></div><div class="bdg bg-y">Action needed</div></div>
                            <div class="sc-val">${s.weekly_inquiries}</div>
                            <div class="sc-lbl">Weekly New Inquiries</div>
                            <div class="sc-delta">Follow-ups waiting...</div>
                        </div>
                    </div>

                    <div class="sg">
                        <div class="sc">
                            <div class="sc-top"><div class="sc-ico ic-red"><i class="fa-solid fa-circle-exclamation"></i></div><div class="bdg bg-r">Overdue</div></div>
                            <div class="sc-val">NPR ${formatMoney(s.pending_dues)}</div>
                            <div class="sc-lbl">Outstanding Dues</div>
                            <div class="sc-delta">${s.overdue_payments} payments overdue</div>
                        </div>
                        <div class="sc">
                            <div class="sc-top"><div class="sc-ico ic-amber"><i class="fa-solid fa-clipboard-check"></i></div><div class="bdg bg-y">Active</div></div>
                            <div class="sc-val">${s.active_batches}</div>
                            <div class="sc-lbl">Running Batches</div>
                            <div class="sc-delta">${s.total_batches} total defined</div>
                        </div>
                        <div class="sc">
                            <div class="sc-top"><div class="sc-ico ic-purple"><i class="fa-solid fa-clock-rotate-left"></i></div><div class="bdg bg-r">Alert</div></div>
                            <div class="sc-val">${s.attendance_marked > 0 ? 'Marked' : 'Not Marked'}</div>
                            <div class="sc-lbl">Today's Attendance</div>
                            <div class="sc-delta">${s.attendance_marked} records synced</div>
                        </div>
                    </div>

                    <div class="g65">
                        <div class="card">
                            <div class="card-header" style="border:none; padding:0; margin-bottom:20px;">
                                <div class="ct" style="margin:0;"><i class="fa-solid fa-history"></i> Recent Transactions</div>
                                <button class="btn bs btn-sm" onclick="goNav('fee', 'fee-rcp')">View All</button>
                            </div>
                            <div class="tw" style="border:none; box-shadow:none;">
                                ${s.today_transactions.length ? s.today_transactions.map(t => `
                                    <div class="ai">
                                        <div class="ad ic-green">${t.student_name.charAt(0)}</div>
                                        <div class="nm-row">
                                            <div><div class="nm">${t.student_name}</div><div class="sub-txt">${t.receipt_no} · ${t.payment_mode}</div></div>
                                            <span class="pill pg">NPR ${formatMoney(t.amount_paid)}</span>
                                        </div>
                                    </div>
                                `).join('') : '<div style="padding:40px; text-align:center; color:var(--text-light);">No transactions today.</div>'}
                            </div>
                        </div>

                        <div class="card">
                            <div class="ct"><i class="fa-solid fa-bell"></i> System Notifications</div>
                            ${s.recent_notifications.length ? s.recent_notifications.map(n => `
                                <div class="col-item" style="${n.is_read ? 'opacity:0.6' : ''}">
                                    <div class="col-ico">${n.type == 'alert' ? '⚠️' : 'ℹ️'}</div>
                                    <div class="col-lbl">${n.title}</div>
                                    <div class="col-val">${new Date(n.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</div>
                                </div>
                            `).join('') : '<div style="padding:24px; text-align:center; color:#94a3b8;">No new notifications.</div>'}
                            
                            <div style="height:1px; background:var(--card-border); margin:15px 0;"></div>
                            <button class="btn bs" style="width:100%; justify-content:center; margin-top:10px;" onclick="goNav('notifs', 'sms-send')">
                                <i class="fa-solid fa-paper-plane"></i> Quick SMS Broadcast
                            </button>
                        </div>
                    </div>
                </div>
            `;
        } catch (e) {
            mainContent.innerHTML = `<div class="alert alert-danger">Failed to load dashboard: ${e.message}</div>`;
        }
    }


    // ═══════════════════════════════════════════════════════════════
    // FEE OUTSTANDING MODULE - Fetch real data from database
    // ═══════════════════════════════════════════════════════════════
    window.renderFeeOutstanding = async function() {
        mainContent.innerHTML = `
            <div class="pg fu">
                <div class="bc">
                    <a href="javascript:goNav('dashboard')">Dashboard</a>
                    <span class="bc-sep">/</span>
                    <a href="javascript:goNav('fee','fee-coll')">Fee Collection</a>
                    <span class="bc-sep">/</span>
                    <span class="bc-cur">Outstanding Dues</span>
                </div>
                <div class="pg-head" style="display:flex; align-items:center; gap:14px;">
                    <div class="sc-ico ic-red" style="width:44px; height:44px; font-size:20px;">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <div class="pg-title">Outstanding Dues</div>
                        <div class="pg-sub">Students with pending fee payments</div>
                    </div>
                </div>
                
                <!-- Filter Bar -->
                <div class="card mb" style="padding:15px;">
                    <div style="display:flex; gap:15px; flex-wrap:wrap; align-items:center;">
                        <input type="text" id="outstandingSearch" class="form-control" placeholder="Search student name..." 
                               style="max-width:250px;" onkeyup="filterOutstanding()">
                        <select id="outstandingCourseFilter" class="form-control" style="max-width:200px;" onchange="filterOutstanding()">
                            <option value="">All Courses</option>
                        </select>
                        <select id="outstandingStatusFilter" class="form-control" style="max-width:150px;" onchange="filterOutstanding()">
                            <option value="">All Status</option>
                            <option value="overdue">Overdue</option>
                            <option value="pending">Pending</option>
                        </select>
                        <button class="btn bs" onclick="loadOutstandingData()" style="margin-left:auto;">
                            <i class="fa-solid fa-refresh"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- Outstanding Summary Cards -->
                <div class="sg mb" id="outstandingSummary">
                    <div class="sc">
                        <div class="sc-top"><div class="sc-ico ic-red"><i class="fa-solid fa-users"></i></div></div>
                        <div class="sc-val"><div class="skeleton" style="width:40px;height:30px;" id="totalStudentsCount"></div></div>
                        <div class="sc-lbl">Students with Dues</div>
                    </div>
                    <div class="sc">
                        <div class="sc-top"><div class="sc-ico ic-amber"><i class="fa-solid fa-money-bill"></i></div></div>
                        <div class="sc-val"><div class="skeleton" style="width:60px;height:30px;" id="totalOutstandingAmount"></div></div>
                        <div class="sc-lbl">Total Outstanding</div>
                    </div>
                    <div class="sc">
                        <div class="sc-top"><div class="sc-ico ic-red"><i class="fa-solid fa-calendar-xmark"></i></div></div>
                        <div class="sc-val"><div class="skeleton" style="width:40px;height:30px;" id="overdueCount"></div></div>
                        <div class="sc-lbl">Overdue Payments</div>
                    </div>
                </div>

                <!-- Outstanding List -->
                <div class="tw" id="outstandingContainer">
                    <div style="padding:20px;">
                        <div class="skeleton" style="height:40px; width:100%; margin-bottom:12px;"></div>
                        <div class="skeleton" style="height:40px; width:100%; margin-bottom:12px;"></div>
                        <div class="skeleton" style="height:40px; width:100%; margin-bottom:12px;"></div>
                        <div class="skeleton" style="height:40px; width:100%; margin-bottom:12px;"></div>
                        <div class="skeleton" style="height:40px; width:100%; margin-bottom:12px;"></div>
                    </div>
                </div>
            </div>
        `;

        await loadOutstandingData();
    };

    let outstandingDataCache = [];
    let outstandingCoursesCache = [];

    async function loadOutstandingData() {
        const container = document.getElementById('outstandingContainer');
        // Keep skeletons visible during load

        try {
            // Fetch outstanding fees from the API
            const res = await fetch(APP_URL + '/api/frontdesk/fees?action=get_outstanding');
            const result = await res.json();

            if (!result.success) {
                container.innerHTML = `<div class="alert alert-danger">Error: ${result.message || 'Failed to load data'}</div>`;
                return;
            }

            outstandingDataCache = result.data || [];
            
            // Calculate summary statistics
            const totalStudents = outstandingDataCache.length;
            const totalOutstanding = outstandingDataCache.reduce((sum, item) => {
                return sum + (parseFloat(item.total_due || 0) - parseFloat(item.total_paid || 0));
            }, 0);
            
            // Count overdue (due date passed)
            const today = new Date().toISOString().split('T')[0];
            const overdueCount = outstandingDataCache.filter(item => item.due_date && item.due_date < today).length;

            // Update summary cards
            document.getElementById('totalStudentsCount').textContent = totalStudents;
            document.getElementById('totalOutstandingAmount').textContent = 'NPR ' + formatMoney(totalOutstanding);
            document.getElementById('overdueCount').textContent = overdueCount;

            // Populate course filter
            const courseFilter = document.getElementById('outstandingCourseFilter');
            const uniqueCourses = [...new Set(outstandingDataCache.map(d => d.course_id).filter(Boolean))];
            
            // Keep the "All Courses" option and add new ones
            courseFilter.innerHTML = '<option value="">All Courses</option>';
            uniqueCourses.forEach(courseId => {
                const courseData = outstandingDataCache.find(d => d.course_id === courseId);
                if (courseData && courseData.course_name) {
                    courseFilter.innerHTML += `<option value="${courseId}">${courseData.course_name}</option>`;
                    outstandingCoursesCache.push({ id: courseId, name: courseData.course_name });
                }
            });

            renderOutstandingTable(outstandingDataCache);

        } catch (e) {
            console.error('Error loading outstanding data:', e);
            container.innerHTML = `<div class="alert alert-danger">Error loading data: ${e.message}</div>`;
        }
    }

    function renderOutstandingTable(data) {
        const container = document.getElementById('outstandingContainer');

        if (!data || data.length === 0) {
            container.innerHTML = `
                <div style="padding:60px; text-align:center; color:var(--text-light);">
                    <i class="fa-solid fa-check-circle" style="font-size:3rem; margin-bottom:15px; color:var(--green);"></i>
                    <h3>No Outstanding Dues</h3>
                    <p>All students are up to date with their fee payments!</p>
                </div>
            `;
            return;
        }

        const today = new Date().toISOString().split('T')[0];

        let html = `
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th style="text-align:right;">Total Due</th>
                        <th style="text-align:right;">Paid</th>
                        <th style="text-align:right;">Balance</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
        `;

        data.forEach(item => {
            const balance = parseFloat(item.total_due || 0) - parseFloat(item.total_paid || 0);
            const isOverdue = item.due_date && item.due_date < today;
            const statusClass = isOverdue ? 'bg-r' : 'bg-y';
            const statusText = isOverdue ? 'Overdue' : 'Pending';

            html += `
                <tr>
                    <td>
                        <div style="font-weight:600;">${item.student_name || 'N/A'}</div>
                        <div class="sub-txt">ID: ${item.student_id || 'N/A'}</div>
                    </td>
                    <td>${item.course_name || 'N/A'}</td>
                    <td style="text-align:right; font-family:monospace;">NPR ${formatMoney(item.total_due || 0)}</td>
                    <td style="text-align:right; font-family:monospace; color:var(--green);">NPR ${formatMoney(item.total_paid || 0)}</td>
                    <td style="text-align:right; font-family:monospace; font-weight:700; color:var(--red);">NPR ${formatMoney(balance)}</td>
                    <td style="text-align:center;">
                        <span class="tag ${statusClass}">${statusText}</span>
                    </td>
                    <td style="text-align:center;">
                        <button class="btn bt" style="padding:6px 12px; font-size:12px;" onclick="collectPayment(${item.student_id}, '${item.student_name?.replace(/'/g, "\\'")}')"
                            ><i class="fa-solid fa-hand-holding-dollar"></i> Collect
                        </button>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    window.filterOutstanding = function() {
        const searchTerm = document.getElementById('outstandingSearch')?.value?.toLowerCase() || '';
        const courseFilter = document.getElementById('outstandingCourseFilter')?.value || '';
        const statusFilter = document.getElementById('outstandingStatusFilter')?.value || '';
        const today = new Date().toISOString().split('T')[0];

        const filtered = outstandingDataCache.filter(item => {
            const matchSearch = !searchTerm || (item.student_name && item.student_name.toLowerCase().includes(searchTerm));
            const matchCourse = !courseFilter || item.course_id == courseFilter;
            
            let matchStatus = true;
            if (statusFilter === 'overdue') {
                matchStatus = item.due_date && item.due_date < today;
            } else if (statusFilter === 'pending') {
                matchStatus = !item.due_date || item.due_date >= today;
            }

            return matchSearch && matchCourse && matchStatus;
        });

        renderOutstandingTable(filtered);
    };

    window.collectPayment = function(studentId, studentName) {
        // Navigate to fee collection page with student pre-selected
        window.location.href = APP_URL + '/dash/front-desk/fee-collect?student_id=' + studentId;
    };

    function formatMoney(amount) {
        return parseFloat(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // ═══════════════════════════════════════════════════════════════
    // RECEIPT HISTORY MODULE
    // ═══════════════════════════════════════════════════════════════
    async function renderRecentPayments() {
        mainContent.innerHTML = `
            <div class="pg fu">
                <div class="bc">
                    <a href="javascript:goNav('dashboard')">Dashboard</a>
                    <span class="bc-sep">/</span>
                    <a href="javascript:goNav('fee','fee-coll')">Fee Collection</a>
                    <span class="bc-sep">/</span>
                    <span class="bc-cur">Receipt History</span>
                </div>
                <div class="pg-head" style="display:flex; align-items:center; gap:14px;">
                    <div class="sc-ico ic-blue" style="width:44px; height:44px; font-size:20px;">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div style="flex:1;">
                        <div class="pg-title">Receipt History</div>
                        <div class="pg-sub">Recent fee payments and transactions</div>
                    </div>
                    <button class="btn bs" onclick="renderRecentPayments()"><i class="fa-solid fa-refresh"></i> Refresh</button>
                </div>

                <div class="tw" id="historyContainer">
                    <div style="padding:20px;">
                        <div class="skeleton" style="height:40px; width:100%; margin-bottom:12px;"></div>
                        <div class="skeleton" style="height:40px; width:100%; margin-bottom:12px;"></div>
                        <div class="skeleton" style="height:40px; width:100%; margin-bottom:12px;"></div>
                        <div class="skeleton" style="height:40px; width:100%; margin-bottom:12px;"></div>
                        <div class="skeleton" style="height:40px; width:100%; margin-bottom:12px;"></div>
                    </div>
                </div>
            </div>
        `;

        try {
            const res = await fetch(APP_URL + '/api/frontdesk/fees?action=get_recent_payments');
            const result = await res.json();
            const container = document.getElementById('historyContainer');

            if (result.success && result.data) {
                if (result.data.length === 0) {
                    container.innerHTML = '<div style="padding:40px; text-align:center; color:#94a3b8;">No recent payments found.</div>';
                    return;
                }

                container.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Receipt No</th>
                                <th style="text-align:right;">Amount</th>
                                <th style="text-align:center;">Method</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${result.data.map(p => `
                                <tr>
                                    <td>${formatDate(p.payment_date)}</td>
                                    <td>
                                        <div style="font-weight:600;">${p.student_name}</div>
                                        <div class="sub-txt">${p.roll_no}</div>
                                    </td>
                                    <td style="font-family:monospace;">${p.receipt_no}</td>
                                    <td style="text-align:right; font-weight:700; color:#10B981;">Rs. ${formatMoney(p.amount_paid)}</td>
                                    <td style="text-align:center;"><span class="tag bg-t">${p.payment_mode}</span></td>
                                    <td style="text-align:center;">
                                        <button class="btn bt" style="padding:4px 8px;" onclick="window.open('${APP_URL}/api/frontdesk/fees?action=generate_receipt_html&transaction_id=${p.id}', '_blank')">
                                            <i class="fa-solid fa-print"></i>
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }
        } catch (e) {
            document.getElementById('historyContainer').innerHTML = '<div class="alert alert-danger">Failed to load history</div>';
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // STUDENT & INQUIRY RENDERERS
    // ═══════════════════════════════════════════════════════════════
    
    async function renderAllStudents() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Students...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/students?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            // Execute scripts if any
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) {
            mainContent.innerHTML = `<div class="alert alert-danger">Error loading students list</div>`;
        }
    }

    async function renderInquiryList() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Inquiries...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/inquiries?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) {
            mainContent.innerHTML = `<div class="alert alert-danger">Error loading inquiries list</div>`;
        }
    }

    async function renderAdmissionForm() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Form...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/admission-form?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) {
            mainContent.innerHTML = `<div class="alert alert-danger">Error loading admission form</div>`;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // ACADEMIC RENDERERS
    // ═══════════════════════════════════════════════════════════════

    async function renderIDCardGen() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Initialing Generator...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/id-cards?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderDocVerify() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/documents?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    // ═══════════════════════════════════════════════════════════════
    // LIBRARY RENDERERS
    // ═══════════════════════════════════════════════════════════════

    async function renderBookIssue() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Library...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/book-issue?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderBookReturn() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Library...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/book-return?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderBookOverdue() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Scanning...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/book-overdue?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    // ═══════════════════════════════════════════════════════════════
    // COMMUNICATION RENDERERS
    // ═══════════════════════════════════════════════════════════════

    async function renderSMSSender() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Connecting...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/sms-send?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderEmailSender() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Connecting...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/email-send?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    // ═══════════════════════════════════════════════════════════════
    // ACADEMIC RENDERERS
    // ═══════════════════════════════════════════════════════════════

    async function renderMarkAttendance() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/attendance-mark?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderAttendanceReport() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Analyzing...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/attendance-report?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    // ═══════════════════════════════════════════════════════════════
    // REPORT RENDERERS
    // ═══════════════════════════════════════════════════════════════

    async function renderDailyOpsRep() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Aggregating...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/report-daily?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderRevenueRep() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Analytics...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/report-revenue?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderEnrollmentRep() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Trends...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/report-enrollment?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderFeeRep() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Analyzing Dues...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/report-fees?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    // ═══════════════════════════════════════════════════════════════
    // INQUIRY HELPERS
    // ═══════════════════════════════════════════════════════════════

    async function renderInquiryAdd() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/inquiry-add?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderInquiryFollowups() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Reminders...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/inquiry-followup?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderInquiryReport() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Report...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/inquiry-report?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderBatches() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Batches...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/batches?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderCourses() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Courses...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/courses?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderProfile() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Profile...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/profile?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderPassword() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Security...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/password?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderBatchStatus() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Status...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/batch-status?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderNotifHistory() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading History...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/notifications?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    // ═══════════════════════════════════════════════════════════════
    // DAILY SUMMARY MODULE (Native implementation continued)
    // ═══════════════════════════════════════════════════════════════
    async function renderDailySummary() {
        mainContent.innerHTML = `
            <div class="pg fu">
                <div class="pg-head">
                    <div class="pg-left">
                        <div class="pg-ico" style="background:linear-gradient(135deg, #10B981, #059669);"><i class="fa-solid fa-calendar-day"></i></div>
                        <div>
                            <div class="pg-title">Daily Summary</div>
                            <div class="pg-sub">Collection summary for today: ${new Date().toLocaleDateString()}</div>
                        </div>
                    </div>
                </div>

                <div class="sg mb" id="dailySummaryStats">
                    <div class="sc">
                        <div class="sc-top"><div class="sc-ico ic-teal"><i class="fa-solid fa-coins"></i></div></div>
                        <div class="sc-val" id="todayTotalValue">-</div>
                        <div class="sc-lbl">Today's Collection</div>
                    </div>
                    <div class="sc">
                        <div class="sc-top"><div class="sc-ico ic-blue"><i class="fa-solid fa-receipt"></i></div></div>
                        <div class="sc-val" id="todayCountValue">-</div>
                        <div class="sc-lbl">Transactions</div>
                    </div>
                </div>

                <div class="card" id="dailyBreakdownContainer">
                    <div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Generating summary...</span></div>
                </div>
            </div>
        `;

        try {
            const res = await fetch(APP_URL + '/api/frontdesk/fee-reports?action=collection_summary');
            const result = await res.json();
            const container = document.getElementById('dailyBreakdownContainer');

            if (result.success && result.data) {
                const total = result.data.reduce((sum, item) => sum + parseFloat(item.total), 0);
                const count = result.data.reduce((sum, item) => sum + parseInt(item.count), 0);
                
                document.getElementById('todayTotalValue').textContent = 'Rs. ' + formatMoney(total);
                document.getElementById('todayCountValue').textContent = count;

                container.innerHTML = `
                    <div style="padding:20px;">
                        <h4 style="margin-bottom:15px; color:#1a1a2e;">Breakdown by Payment Method</h4>
                        ${result.data.map(item => `
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:15px; background:#f8fafc; border-radius:12px; margin-bottom:10px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:40px; height:40px; border-radius:10px; background:#fff; display:flex; align-items:center; justify-content:center; font-size:18px; border:1px solid #e2e8f0;">
                                        ${item.payment_method === 'cash' ? '💵' : '🏦'}
                                    </div>
                                    <div style="font-weight:700; text-transform:capitalize;">${item.payment_method.replace('_', ' ')}</div>
                                </div>
                                <div style="text-align:right;">
                                    <div style="font-weight:800; color:#10B981; font-size:18px;">Rs. ${formatMoney(item.total)}</div>
                                    <div style="font-size:12px; color:#64748b;">${item.count} transactions</div>
                                </div>
                            </div>
                        `).join('')}
                        
                        <button class="btn" style="width:100%; justify-content:center; margin-top:20px; background:#1a1a2e; color:#fff; padding:15px;" onclick="window.print()">
                            <i class="fa-solid fa-print"></i> Print End of Day Report
                        </button>
                    </div>
                `;
            }
        } catch (e) {
            container.innerHTML = '<div class="alert alert-danger">Failed to load summary</div>';
        }
    }

    // ── UTILS ──
    function formatMoney(n) {
        return parseFloat(n).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // ADMISSION RENDERERS
    // ═══════════════════════════════════════════════════════════════

    async function renderAdmissionForm() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Form...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/admission-form?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderAllStudents() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Students...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/students?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderIDCardGen() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Generator...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/id-cards?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderDocVerify() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Documents...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/documents?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    // ═══════════════════════════════════════════════════════════════
    // FEE RENDERERS
    // ═══════════════════════════════════════════════════════════════

    async function renderFeeRecord() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Connecting...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/fee-collect?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderFeeOutstanding() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Scanning Arrears...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/fee-outstanding?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    async function renderRecentPayments() {
        mainContent.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Fetching History...</span></div></div>`;
        try {
            const res = await fetch(`${APP_URL}/dash/front-desk/fee-receipts?partial=true`);
            const html = await res.text();
            mainContent.innerHTML = `<div class="pg fu">${html}</div>`;
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(s => eval(s.innerHTML));
        } catch (e) { mainContent.innerHTML = `<div class="alert alert-danger">Error</div>`; }
    }

    // ── SEARCH & PROFILE DROPDOWN logic ──
    const userChip = document.getElementById('userChip');
    const userDropdown = document.getElementById('userDropdown');
    const hdrSearch = document.getElementById('hdrSearch');
    const searchResults = document.getElementById('searchResults');

    // Profile Dropdown Toggle
    if (userChip && userDropdown) {
        userChip.addEventListener('click', (e) => {
            e.stopPropagation();
            const isHidden = userDropdown.style.display === 'none';
            userDropdown.style.display = isHidden ? 'block' : 'none';
        });

        // Click outside to close
        document.addEventListener('click', () => {
            userDropdown.style.display = 'none';
        });

        userDropdown.addEventListener('click', (e) => e.stopPropagation());
    }

    // Global Search Logic
    let searchTimeout = null;
    if (hdrSearch && searchResults) {
        hdrSearch.addEventListener('input', (e) => {
            const q = e.target.value.trim();
            clearTimeout(searchTimeout);

            if (q.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`${APP_URL}/api/admin/global-search?q=${encodeURIComponent(q)}`);
                    const data = await res.json();
                    renderSearchResults(data);
                } catch (err) {
                    console.error('Search error:', err);
                }
            }, 300);
        });

        // Hide search results when clicking outside
        document.addEventListener('click', (e) => {
            if (!hdrSearch.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }

    function renderSearchResults(data) {
        if (!data.success || data.total === 0) {
            searchResults.innerHTML = '<div class="search-no-results">No results found</div>';
            searchResults.style.display = 'block';
            return;
        }

        let html = '<div class="search-results-list">';
        
        if (data.students && data.students.length > 0) {
            html += '<div class="search-cat">Students</div>';
            data.students.forEach(s => {
                html += `<div class="search-res-item" onclick="goNav('admissions', 'adm-all'); closeSearch();">
                    <div class="res-main">${s.name}</div>
                    <div class="res-sub">${s.roll_no || ''} • ${s.phone || ''}</div>
                </div>`;
            });
        }

        if (data.batches && data.batches.length > 0) {
            html += '<div class="search-cat">Batches</div>';
            data.batches.forEach(b => {
                html += `<div class="search-res-item" onclick="goNav('academic', 'batches'); closeSearch();">
                    <div class="res-main">${b.name}</div>
                    <div class="res-sub">${b.course_name || ''}</div>
                </div>`;
            });
        }

        html += '</div>';
        searchResults.innerHTML = html;
        searchResults.style.display = 'block';
    }

    window.closeSearch = () => {
        if (hdrSearch) hdrSearch.value = '';
        if (searchResults) searchResults.style.display = 'none';
    };

    // Init
    renderSidebar();
    renderPage();
});
