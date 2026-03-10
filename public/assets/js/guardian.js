/**
 * Hamro ERP — Guardian Dashboard
 * Production Blueprint V3.0 — Implementation (LIGHT THEME)
 */

document.addEventListener('DOMContentLoaded', () => {
    // ── STATE ──
    const urlParams = new URLSearchParams(window.location.search);
    const initialPage = urlParams.get('page');
    let activeNav = initialPage || 'dashboard';
    let expanded = { attendance: true, exams: false, fee: false, notices: false, messages: false };

    // ── ELEMENTS ──
    const mainContent = document.getElementById('mainContent');
    const sbBody = document.getElementById('sbBody');
    const sbToggle = document.getElementById('sbToggle');
    const sbOverlay = document.getElementById('sbOverlay');

    // ── SIDEBAR TOGGLE (Matches Super Admin pattern) ──
    const toggleSidebar = () => {
        if (window.innerWidth >= 1024) {
            document.body.classList.toggle('sb-collapsed');
        } else {
            document.body.classList.toggle('sb-active');
        }
    };
    const closeSidebar = () => document.body.classList.remove('sb-active');

    if (sbToggle) sbToggle.addEventListener('click', toggleSidebar);
    if (sbOverlay) sbOverlay.addEventListener('click', closeSidebar);

    // ── NAVIGATION TREE — PRD v3.0 Section 4.6 ──
    const NAV = [
        { id: "dashboard", icon: "fa-columns", label: "Dashboard", sub: null, sec: "MAIN" },
        
        { id: "attendance", icon: "fa-calendar-check", label: "Attendance", sub: [
            { id: "sum",   l: "Attendance Summary",   nav: "attendance", sub: "sum"  },
            { id: "hist",  l: "Attendance History",   nav: "attendance", sub: "hist" },
            { id: "leave", l: "Leave Applications",    nav: "attendance", sub: "leave"}
        ], sec: "MONITORING" },

        { id: "exams", icon: "fa-trophy", label: "Exam Results", sub: [
            { id: "hist",     l: "Result History",    nav: "exams", sub: "hist"     },
            { id: "trend",    l: "Performance Trend", nav: "exams", sub: "trend"    },
            { id: "analysis", l: "Subject Analysis",  nav: "exams", sub: "analysis" }
        ], sec: "MONITORING" },

        { id: "fee", icon: "fa-wallet", label: "Fee", sub: [
            { id: "dues",     l: "Outstanding Dues", nav: "fee", sub: "dues"    },
            { id: "pay",      l: "Payment History",  nav: "fee", sub: "pay"     },
            { id: "receipts", l: "Download Receipts",nav: "fee", sub: "receipts"}
        ], sec: "MONITORING" },

        { id: "notices", icon: "fa-bullhorn", label: "Notices", sub: [
            { id: "inst",  l: "Institute Announcements", nav: "notices", sub: "inst"  },
            { id: "batch", l: "My Child's Notices",      nav: "notices", sub: "batch" }
        ], sec: "MAIN" },

        { id: "messages", icon: "fa-envelope", label: "Messages", sub: [
            { id: "inbox",   l: "Inbox",          nav: "messages", sub: "inbox"   },
            { id: "contact", l: "Contact Admin",  nav: "messages", sub: "contact" }
        ], sec: "MAIN" },
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
                        <span style="flex:1; text-align:left;">${nav.label}</span>
                        ${nav.sub ? `<i class="fa-solid fa-chevron-right" style="font-size:10px; transition:0.2s; ${isExp ? 'transform:rotate(90deg)' : ''}"></i>` : ''}
                    </button>`;

                if (nav.sub && isExp) {
                    html += `<div class="sub-menu">`;
                    nav.sub.forEach(s => {
                        const isSubActive = activeNav === `${nav.id}-${s.id}`;
                        html += `<button class="sub-btn ${isSubActive ? 'active' : ''}" onclick="goNav('${nav.id}', '${s.id}')">
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
            { id: 'dashboard', icon: 'fa-columns', label: 'Home', action: "goNav('dashboard')" },
            { id: 'attendance', icon: 'fa-calendar-check', label: 'Attendance', action: "goNav('attendance', 'sum')" },
            { id: 'exams', icon: 'fa-trophy', label: 'Results', action: "goNav('exams', 'hist')" },
            { id: 'fee', icon: 'fa-wallet', label: 'Fee', action: "goNav('fee', 'dues')" },
            { id: 'messages', icon: 'fa-envelope', label: 'Messages', action: "goNav('messages', 'inbox')" }
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
        mainContent.innerHTML = '<div class="pg fu">Loading...</div>';

        if (activeNav === 'dashboard') {
            renderDashboard();
        } else {
            renderGenericPage();
        }
    }

    async function renderDashboard() {
        mainContent.innerHTML = '<div class="pg fu" style="display:flex;align-items:center;justify-content:center;height:50vh;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i></div>';

        try {
            const res = await fetch(`${APP_URL}/api/guardian/dashboard`);
            const json = await res.json();
            
            if (!json.success) {
                mainContent.innerHTML = `<div class="pg fu"><div class="alert alert-danger">${json.message}</div></div>`;
                return;
            }

            const data = json.data;
            const gInfo = data.guardian_info || {};
            const sInfo = data.student_info || {};
            const stats = data.stats || {};
            
            const gName = gInfo.full_name || 'Guardian';
            const sName = sInfo.full_name || 'Student';
            const sBatch = sInfo.batch_name || 'Batch';
            const sRoll = sInfo.roll_no || 'Roll TBA';

            let examsHtml = '';
            if (data.recent_exams && data.recent_exams.length > 0) {
                data.recent_exams.forEach(ex => {
                    const score = ex.score || 0;
                    const total = ex.total_marks || 100;
                    examsHtml += `
                        <div class="ex-row">
                            <div class="ex-ico" style="background:#EEF2FF; color:#6366F1;"><i class="fa-solid fa-file-pen"></i></div>
                            <div class="ex-info">
                                <div class="ex-subj">${ex.exam_title || 'Exam'}</div>
                                <div class="ex-meta">${ex.exam_type || 'Test'} · ${ex.exam_date || ''}</div>
                            </div>
                            <div class="ex-score">
                                <div class="ex-val">${score}/${total}</div>
                            </div>
                        </div>
                    `;
                });
            } else {
                examsHtml = '<div style="padding:15px; color:var(--text-light); text-align:center;">No recent exams.</div>';
            }

            // Update sidebar info
            const sbChildInfo = document.getElementById('sbChildInfo');
            if (sbChildInfo) {
                const init = sName.substring(0, 2).toUpperCase();
                sbChildInfo.innerHTML = `
                    <div class="child-av">${init}</div>
                    <div class="child-meta">
                        <span class="child-name">${sName}</span>
                        <span class="child-roll">Roll: ${sRoll}</span>
                    </div>
                `;
            }

            let feeHtml = '';
            if (data.fee_status && data.fee_status.length > 0) {
                data.fee_status.forEach(fee => {
                    const isPaid = fee.status === 'paid';
                    feeHtml += `
                        <div class="fee-item">
                            <div class="fee-info">
                                <div class="fee-name">${fee.fee_name || 'Fee'}</div>
                                <div class="fee-due">${isPaid ? 'Paid on' : 'Due'}: ${fee.due_date || 'N/A'}</div>
                            </div>
                            <div class="fee-amt ${isPaid ? 'pg' : 'pr'}">
                                ${isPaid ? 'PAID' : 'Rs. ' + (fee.amount || 0)}
                            </div>
                        </div>
                    `;
                });
            } else {
                feeHtml = '<div style="padding:15px; color:var(--text-light); text-align:center;">No fee records found.</div>';
            }

            let noticeHtml = '';
            if (data.recent_notices && data.recent_notices.length > 0) {
                data.recent_notices.forEach(not => {
                    noticeHtml += `
                        <div class="ex-row" style="margin-bottom:10px;">
                            <div class="ex-info">
                                <div class="ex-subj" style="font-size:13px; font-weight:600;">${not.title || 'Notice'}</div>
                                <div class="ex-meta" style="font-size:11px; margin-top:2px;">${not.created_at || 'Recently'}</div>
                            </div>
                        </div>
                    `;
                });
            } else {
                noticeHtml = '<div style="padding:15px; color:var(--text-light); text-align:center;">No new notices.</div>';
            }

            mainContent.innerHTML = `
                <div class="pg fu">
                    <div class="pg-header">
                        <div class="pg-title">Namaste, ${gName} 👋</div>
                        <div class="pg-sub">Monitoring ${sName} · ${sBatch} · Roll: ${sRoll}</div>
                    </div>

                    <!-- QUICK ACTIONS -->
                    <div class="qa-grid">
                        <button class="qa-btn" onclick="goNav('attendance', 'sum')">
                            <i class="fa-solid fa-chart-bar" style="color:var(--green)"></i> View Attendance Report
                        </button>
                        <button class="qa-btn" onclick="goNav('fee', 'receipts')">
                            <i class="fa-solid fa-file-invoice-dollar" style="color:var(--amber)"></i> Download Fee Receipt
                        </button>
                        <button class="qa-btn primary" onclick="goNav('messages', 'contact')">
                            <i class="fa-solid fa-envelope"></i> Message Institute Admin
                        </button>
                    </div>

                    <!-- STAT GRID -->
                    <div class="sg">
                        <div class="sc green">
                            <div class="sc-ico green"><i class="fa-solid fa-calendar-check"></i></div>
                            <div class="sc-lbl">Child's Attendance</div>
                            <div class="att-ring">
                                <div class="att-pct">${stats.attendance_rate || 0}%</div>
                                <div style="font-size:11px; color:var(--text-body); line-height:1.2;">Month's Presence: ${stats.attendance_present} / ${stats.attendance_total} days</div>
                            </div>
                        </div>
                        <div class="sc blue">
                            <div class="sc-ico blue"><i class="fa-solid fa-trophy"></i></div>
                            <div class="sc-lbl">Latest Exam Score</div>
                            <div class="sc-val">${stats.latest_exam_score !== null ? stats.latest_exam_score + '%' : 'N/A'}</div>
                            <div class="sc-sub">Recent Test Performance</div>
                        </div>
                        <div class="sc amber">
                            <div class="sc-ico amber"><i class="fa-solid fa-wallet"></i></div>
                            <div class="sc-lbl">Fee Dues</div>
                            <div class="sc-val">Rs. ${stats.fee_dues.toLocaleString()}</div>
                            <div class="sc-sub">Outstanding Amount</div>
                        </div>
                        <div class="sc purple">
                            <div class="sc-ico purple"><i class="fa-solid fa-bullhorn"></i></div>
                            <div class="sc-lbl">Recent Notices</div>
                            <div class="sc-val">${stats.notices_count || 0} New</div>
                            <div class="sc-sub">Relevant to Child's Batch</div>
                        </div>
                    </div>

                    <div class="g64">
                        <div>
                            <!-- EXAM RESULTS -->
                            <div class="card">
                                <div class="card-h">
                                    <div class="card-t"><i class="fa-solid fa-square-poll-vertical" style="color:var(--green)"></i> Recent Exam Results</div>
                                    <a href="javascript:void(0)" onclick="goNav('exams','hist')" class="btn-l" style="font-size:11px; color:var(--green); font-weight:700; text-decoration:none;">View All</a>
                                </div>
                                <div class="card-b">
                                    ${examsHtml}
                                </div>
                            </div>

                            <!-- RECENT MESSAGES -->
                            <div class="contact-zone" style="margin-top:20px;">
                                <div class="contact-t"><i class="fa-solid fa-headset"></i> Contact Institute Admin</div>
                                <div class="contact-d">Direct line to the academic counselor. Type your message below to start a conversation.</div>
                                <textarea class="contact-box" style="width:100%; border:1px solid #ddd; padding:10px; border-radius:8px;" rows="3" placeholder="Type your inquiry here regarding ${sName}'s progress..."></textarea>
                                <button class="btn btn-primary mt-10" onclick="alert('Message sent to admin!')">Send Message</button>
                                <div style="clear:both;"></div>
                            </div>
                        </div>

                        <div>
                            <!-- FEE STATUS -->
                            <div class="card">
                                <div class="card-h">
                                    <div class="card-t"><i class="fa-solid fa-credit-card" style="color:var(--amber)"></i> Fee Status</div>
                                </div>
                                <div class="card-b">
                                    ${feeHtml}
                                    <button class="btn bs" style="width:100%; margin-top:16px; font-size:12px; background:var(--green); color:#fff; border:none; padding:10px; border-radius:6px; font-weight:800; cursor:pointer;" onclick="goNav('fee','dues')">Pay Outstanding Online</button>
                                </div>
                            </div>

                            <!-- NOTICE BOARD -->
                            <div class="card" style="margin-top:20px;">
                                <div class="card-h">
                                    <div class="card-t"><i class="fa-solid fa-bullhorn" style="color:var(--purple)"></i> Recent Notices</div>
                                </div>
                                <div class="card-b" style="padding:0 18px 18px;">
                                    ${noticeHtml}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

        } catch (error) {
            console.error('Render Dashboard Error:', error);
            mainContent.innerHTML = `<div class="pg fu"><div class="alert alert-danger">Failed to load dashboard data.</div></div>`;
        }
    }

    function renderGenericPage() {
        const title = activeNav.split('-').map(s=>s.charAt(0).toUpperCase()+s.slice(1)).join(' ');
        mainContent.innerHTML = `
            <div class="pg fu">
                <div class="card" style="text-align:center; padding:80px 40px;">
                    <i class="fa-solid fa-shield-halved" style="font-size:3rem; color:var(--text-light); margin-bottom:20px;"></i>
                    <h2>${title} Module</h2>
                    <p style="color:var(--text-body); margin-top:10px;">Guardian monitoring tools are being synced with the V3.0 production server.</p>
                </div>
            </div>
        `;
    }

    // Init
    renderSidebar();
    renderPage();
});
