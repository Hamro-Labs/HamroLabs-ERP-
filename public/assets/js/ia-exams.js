/**
 * Hamro ERP — ia-exams.js  (v2 — matches real DB schema)
 * Schema: exams(id, tenant_id, batch_id, course_id, created_by_user_id,
 *   title, duration_minutes, total_marks, negative_mark, question_mode,
 *   start_at, end_at, status[draft|scheduled|active|completed|cancelled])
 */

/* ─────────────────────────────────────────────────────────────────
   HELPERS
───────────────────────────────────────────────────────────────── */
async function _loadExamDropdowns() {
    let batches = [], courses = [];
    try {
        const [br, cr] = await Promise.all([
            fetch(APP_URL + '/api/admin/batches').then(r => r.json()),
            fetch(APP_URL + '/api/admin/courses').then(r => r.json())
        ]);
        batches = br.success ? br.data : [];
        courses = cr.success ? cr.data : [];
    } catch(e) {}
    return { batches, courses };
}
function _batchOpts(batches, sel = '') {
    return `<option value="">— Select Batch * —</option>` +
        batches.map(b => `<option value="${b.id}" ${b.id == sel ? 'selected' : ''}>${b.name}</option>`).join('');
}
function _courseOpts(courses, sel = '') {
    return `<option value="">— Select Course * —</option>` +
        courses.map(c => `<option value="${c.id}" ${c.id == sel ? 'selected' : ''}>${c.name}</option>`).join('');
}

/* ─────────────────────────────────────────────────────────────────
   EXAM LIST
───────────────────────────────────────────────────────────────── */
window.renderExamList = async function() {
    const mc = document.getElementById('mainContent');
    mc.innerHTML = `<div class="pg fu">
        <div class="bc"><a href="#" onclick="goNav('overview')">Dashboard</a> <span class="bc-sep">&rsaquo;</span> <span class="bc-cur">Examinations</span></div>
        <div class="pg-head">
            <div class="pg-left">
                <div class="pg-ico" style="background:linear-gradient(135deg,#8141a5,#a855f7)"><i class="fa-solid fa-file-signature"></i></div>
                <div><div class="pg-title">Examinations</div><div class="pg-sub">Schedule, manage and track exam performance</div></div>
            </div>
            <div class="pg-acts">
                <button class="btn bt" onclick="goNav('exams','create-ex')"><i class="fa-solid fa-plus"></i> Create Exam</button>
            </div>
        </div>

        <!-- Filters -->
        <div class="card" style="padding:12px 16px;margin-bottom:14px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <input type="text" id="examSearch" placeholder="🔍 Search by title…" style="flex:1;min-width:140px;padding:7px 12px;border:1px solid var(--card-border);border-radius:8px;font-family:var(--font);font-size:12.5px;outline:none;" oninput="window._filterExamList()">
            <select id="examStatusFilter" style="padding:7px 12px;border:1px solid var(--card-border);border-radius:8px;font-family:var(--font);font-size:12.5px;outline:none;" onchange="window._filterExamList()">
                <option value="">All Status</option>
                <option value="scheduled">Scheduled</option>
                <option value="active">Active / Ongoing</option>
                <option value="completed">Completed</option>
                <option value="draft">Draft</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="card" id="examListContainer"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading exams…</span></div></div>
    </div>`;

    window._allExams = [];
    await _loadExams();
};

window._filterExamList = function() {
    const search = (document.getElementById('examSearch')?.value || '').toLowerCase();
    const status = document.getElementById('examStatusFilter')?.value || '';
    const filtered = (window._allExams || []).filter(ex =>
        (!search || (ex.title || '').toLowerCase().includes(search) || (ex.batch_name || '').toLowerCase().includes(search)) &&
        (!status || ex.status === status)
    );
    _renderExamTable(filtered);
};

async function _loadExams() {
    const c = document.getElementById('examListContainer'); if (!c) return;
    try {
        const res    = await fetch(APP_URL + '/api/admin/exams');
        const result = await res.json();
        if (!result.success) throw new Error(result.message);
        window._allExams = result.data || [];
        _renderExamTable(window._allExams);
    } catch(e) {
        c.innerHTML = `<div style="padding:40px;text-align:center;color:var(--red)"><i class="fa-solid fa-circle-exclamation" style="font-size:2rem;margin-bottom:12px;display:block"></i>${e.message}</div>`;
    }
}

function _renderExamTable(exams) {
    const c = document.getElementById('examListContainer'); if (!c) return;

    if (!exams.length) {
        c.innerHTML = `<div style="padding:80px 40px;text-align:center;color:#94a3b8">
            <i class="fa-solid fa-file-circle-xmark" style="font-size:3.5rem;margin-bottom:16px;opacity:.3;display:block"></i>
            <p style="font-size:14px;font-weight:600;margin-bottom:6px">No exams found</p>
            <p style="font-size:12px;margin-bottom:16px">Schedule your first exam to get started.</p>
            <button class="btn bt btn-sm" onclick="goNav('exams','create-ex')"><i class="fa-solid fa-plus"></i> Create Exam</button>
        </div>`;
        return;
    }

    const statusBadge = {
        scheduled: { bg:'#dbeafe', color:'#1d4ed8', icon:'fa-clock',         label:'Scheduled'  },
        active:    { bg:'#dcfce7', color:'#15803d', icon:'fa-circle-dot',     label:'Active'     },
        completed: { bg:'#f1f5f9', color:'#475569', icon:'fa-circle-check',   label:'Completed'  },
        draft:     { bg:'#fef9c3', color:'#854d0e', icon:'fa-pen-to-square',  label:'Draft'      },
        cancelled: { bg:'#fee2e2', color:'#b91c1c', icon:'fa-ban',            label:'Cancelled'  },
    };

    let html = `<div style="overflow-x:auto"><table class="erp-table" style="width:100%;border-collapse:collapse">
        <thead><tr>
            <th style="text-align:left;padding:10px 14px;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--card-border)">Exam</th>
            <th style="text-align:left;padding:10px 14px;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--card-border)">Batch / Course</th>
            <th style="text-align:left;padding:10px 14px;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--card-border)">Date &amp; Time</th>
            <th style="text-align:center;padding:10px 14px;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--card-border)">Duration</th>
            <th style="text-align:center;padding:10px 14px;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--card-border)">Marks</th>
            <th style="text-align:center;padding:10px 14px;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--card-border)">Status</th>
            <th style="text-align:right;padding:10px 14px;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--card-border)">Actions</th>
        </tr></thead><tbody>`;

    exams.forEach(ex => {
        const sb  = statusBadge[ex.status] || statusBadge.draft;
        const startDt = ex.start_at ? new Date(ex.start_at) : null;
        const endDt   = ex.end_at   ? new Date(ex.end_at)   : null;
        const dateTxt = startDt ? startDt.toLocaleDateString('en-BD', {day:'2-digit', month:'short', year:'numeric'}) : '—';
        const timeTxt = startDt ? startDt.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}) + (endDt ? ' – ' + endDt.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'}) : '') : '';
        html += `<tr style="border-bottom:1px solid var(--card-border);transition:background .12s" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
            <td style="padding:12px 14px">
                <div style="font-weight:700;color:var(--text-dark);font-size:13px">${ex.title}</div>
                <div style="font-size:11px;color:var(--text-light);margin-top:2px">${ex.question_mode === 'auto' ? '🤖 Auto questions' : '✍️ Manual'}</div>
            </td>
            <td style="padding:12px 14px">
                <div style="font-size:12.5px;font-weight:600;color:var(--text-dark)">${ex.batch_name || '—'}</div>
                <div style="font-size:11px;color:var(--text-light)">${ex.course_name || ''}</div>
            </td>
            <td style="padding:12px 14px">
                <div style="font-size:12.5px;font-weight:600">${dateTxt}</div>
                <div style="font-size:11px;color:var(--text-light)">${timeTxt}</div>
            </td>
            <td style="padding:12px 14px;text-align:center;font-size:12.5px;font-weight:600">${ex.duration_minutes} min</td>
            <td style="padding:12px 14px;text-align:center">
                <div style="font-size:13px;font-weight:700">${ex.total_marks}</div>
                ${ex.negative_mark > 0 ? `<div style="font-size:10.5px;color:var(--red)">-${ex.negative_mark}/wrong</div>` : ''}
            </td>
            <td style="padding:12px 14px;text-align:center">
                <span style="font-size:10.5px;font-weight:700;background:${sb.bg};color:${sb.color};padding:4px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:5px">
                    <i class="fa-solid ${sb.icon}" style="font-size:9px"></i> ${sb.label}
                </span>
            </td>
            <td style="padding:12px 14px;text-align:right;white-space:nowrap">
                <button onclick="window.renderEditExamForm(${ex.id})" style="width:30px;height:30px;border:none;background:var(--bg);border-radius:7px;cursor:pointer;color:var(--text-body);display:inline-flex;align-items:center;justify-content:center;margin-left:4px" title="Edit"><i class="fa-solid fa-pen" style="font-size:11px"></i></button>
                <button onclick="window._deleteExam(${ex.id})" style="width:30px;height:30px;border:none;background:#fff0f0;border-radius:7px;cursor:pointer;color:var(--red);display:inline-flex;align-items:center;justify-content:center;margin-left:4px" title="Delete"><i class="fa-solid fa-trash" style="font-size:11px"></i></button>
            </td>
        </tr>`;
    });

    html += `</tbody></table></div>
    <div style="padding:12px 16px;font-size:11.5px;color:var(--text-light);border-top:1px solid var(--card-border)">${exams.length} exam${exams.length !== 1 ? 's' : ''} found</div>`;
    c.innerHTML = html;
}

/* ─────────────────────────────────────────────────────────────────
   CREATE EXAM FORM
───────────────────────────────────────────────────────────────── */
window.renderCreateExamForm = async function() {
    const mc = document.getElementById('mainContent');
    mc.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Preparing form…</span></div></div>`;

    const { batches, courses } = await _loadExamDropdowns();
    const today = new Date().toISOString().slice(0, 10);

    mc.innerHTML = `<div class="pg fu">
        <div class="bc"><a href="#" onclick="goNav('overview')">Dashboard</a> <span class="bc-sep">&rsaquo;</span> <a href="#" onclick="goNav('exams','schedule')">Examinations</a> <span class="bc-sep">&rsaquo;</span> <span class="bc-cur">${window._examEditId ? 'Edit Exam' : 'Create Exam'}</span></div>

        <div class="pg-head">
            <div class="pg-left">
                <div class="pg-ico" style="background:linear-gradient(135deg,#8141a5,#a855f7)"><i class="fa-solid fa-circle-plus"></i></div>
                <div>
                    <div class="pg-title" id="examFormTitle">${window._examEditId ? 'Edit Exam' : 'Create New Exam'}</div>
                    <div class="pg-sub">${window._examEditId ? 'Update exam details and reschedule' : 'Schedule an exam or mock test for your students'}</div>
                </div>
            </div>
            <div class="pg-acts">
                <button class="btn" style="background:var(--bg);color:var(--text-body);border:1px solid var(--card-border)" onclick="goNav('exams','schedule')"><i class="fa-solid fa-arrow-left"></i> Back</button>
            </div>
        </div>

        <form id="createExamForm" onsubmit="window._submitExamForm(event)" novalidate>
            <div id="examFormErrors" style="display:none;background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#b91c1c;font-size:12.5px;font-weight:600"></div>

            <div style="display:grid;grid-template-columns:1fr 320px;gap:18px;align-items:start">

                <!-- ── LEFT COL ── -->
                <div style="display:flex;flex-direction:column;gap:16px">

                    <!-- Basic Info -->
                    <div class="card">
                        <div class="card-header"><h4><i class="fa-solid fa-info-circle" style="color:#8141a5;margin-right:6px"></i>Exam Information</h4></div>
                        <div class="card-body" style="display:flex;flex-direction:column;gap:14px;padding-top:14px">

                            <div>
                                <label style="font-size:11.5px;font-weight:700;color:var(--text-dark);display:block;margin-bottom:5px">Exam Title <span style="color:var(--red)">*</span></label>
                                <input type="text" id="exTitle" placeholder="e.g. First Terminal Examination 2081" maxlength="255" required
                                    style="width:100%;padding:9px 12px;border:1.5px solid var(--card-border);border-radius:8px;font-family:var(--font);font-size:13px;outline:none;box-sizing:border-box"
                                    oninput="window._updateExamPreview()" onfocus="this.style.borderColor='#8141a5'" onblur="this.style.borderColor='var(--card-border)'">
                            </div>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                                <div>
                                    <label style="font-size:11.5px;font-weight:700;color:var(--text-dark);display:block;margin-bottom:5px">Batch <span style="color:var(--red)">*</span></label>
                                    <select id="exBatch" required style="width:100%;padding:9px 12px;border:1.5px solid var(--card-border);border-radius:8px;font-family:var(--font);font-size:12.5px;outline:none;box-sizing:border-box" onchange="window._updateExamPreview()" onfocus="this.style.borderColor='#8141a5'" onblur="this.style.borderColor='var(--card-border)'">
                                        ${_batchOpts(batches)}
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size:11.5px;font-weight:700;color:var(--text-dark);display:block;margin-bottom:5px">Course <span style="color:var(--red)">*</span></label>
                                    <select id="exCourse" required style="width:100%;padding:9px 12px;border:1.5px solid var(--card-border);border-radius:8px;font-family:var(--font);font-size:12.5px;outline:none;box-sizing:border-box" onfocus="this.style.borderColor='#8141a5'" onblur="this.style.borderColor='var(--card-border)'">
                                        ${_courseOpts(courses)}
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label style="font-size:11.5px;font-weight:700;color:var(--text-dark);display:block;margin-bottom:5px">Question Mode</label>
                                <div style="display:flex;gap:10px">
                                    <label style="flex:1;border:1.5px solid var(--card-border);border-radius:8px;padding:10px 14px;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:12.5px;transition:all .15s" id="qmManualLbl">
                                        <input type="radio" name="exQMode" value="manual" checked onchange="window._toggleQMode('manual')"> ✍️ <strong>Manual</strong><span style="color:var(--text-light);font-size:11px;margin-left:4px">— enter questions yourself</span>
                                    </label>
                                    <label style="flex:1;border:1.5px solid var(--card-border);border-radius:8px;padding:10px 14px;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:12.5px;transition:all .15s" id="qmAutoLbl">
                                        <input type="radio" name="exQMode" value="auto" onchange="window._toggleQMode('auto')"> 🤖 <strong>Auto</strong><span style="color:var(--text-light);font-size:11px;margin-left:4px">— from question bank</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule -->
                    <div class="card">
                        <div class="card-header"><h4><i class="fa-solid fa-calendar-days" style="color:var(--green);margin-right:6px"></i>Date &amp; Time <span style="font-size:11px;font-weight:400;color:var(--text-light)">(all fields required)</span></h4></div>
                        <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;padding-top:14px">
                            <div>
                                <label style="font-size:11.5px;font-weight:700;color:var(--text-dark);display:block;margin-bottom:5px">Exam Date <span style="color:var(--red)">*</span></label>
                                <input type="date" id="exDate" required min="${today}"
                                    style="width:100%;padding:9px 12px;border:1.5px solid var(--card-border);border-radius:8px;font-family:var(--font);font-size:12.5px;outline:none;box-sizing:border-box"
                                    oninput="window._updateExamPreview()" onfocus="this.style.borderColor='#8141a5'" onblur="this.style.borderColor='var(--card-border)'">
                            </div>
                            <div>
                                <label style="font-size:11.5px;font-weight:700;color:var(--text-dark);display:block;margin-bottom:5px">Start Time <span style="color:var(--red)">*</span></label>
                                <input type="time" id="exStartTime" required
                                    style="width:100%;padding:9px 12px;border:1.5px solid var(--card-border);border-radius:8px;font-family:var(--font);font-size:12.5px;outline:none;box-sizing:border-box"
                                    onfocus="this.style.borderColor='#8141a5'" onblur="this.style.borderColor='var(--card-border)'">
                            </div>
                            <div>
                                <label style="font-size:11.5px;font-weight:700;color:var(--text-dark);display:block;margin-bottom:5px">End Time <span style="color:var(--red)">*</span></label>
                                <input type="time" id="exEndTime" required
                                    style="width:100%;padding:9px 12px;border:1.5px solid var(--card-border);border-radius:8px;font-family:var(--font);font-size:12.5px;outline:none;box-sizing:border-box"
                                    onfocus="this.style.borderColor='#8141a5'" onblur="this.style.borderColor='var(--card-border)'">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── RIGHT COL ── -->
                <div style="display:flex;flex-direction:column;gap:16px">

                    <!-- Marks -->
                    <div class="card">
                        <div class="card-header"><h4><i class="fa-solid fa-star" style="color:#f59e0b;margin-right:6px"></i>Marks &amp; Duration</h4></div>
                        <div class="card-body" style="display:flex;flex-direction:column;gap:12px;padding-top:14px">
                            <div>
                                <label style="font-size:11.5px;font-weight:700;color:var(--text-dark);display:block;margin-bottom:5px">Total Marks <span style="color:var(--red)">*</span></label>
                                <input type="number" id="exTotalMarks" placeholder="100" min="1" max="9999" required
                                    style="width:100%;padding:9px 12px;border:1.5px solid var(--card-border);border-radius:8px;font-family:var(--font);font-size:13px;font-weight:700;outline:none;box-sizing:border-box"
                                    oninput="window._updateExamPreview()" onfocus="this.style.borderColor='#8141a5'" onblur="this.style.borderColor='var(--card-border)'">
                            </div>
                            <div>
                                <label style="font-size:11.5px;font-weight:700;color:var(--text-dark);display:block;margin-bottom:5px">Duration <span style="color:var(--red)">*</span> <span style="font-weight:400;color:var(--text-light)">(minutes)</span></label>
                                <input type="number" id="exDuration" placeholder="180" min="1" max="600" required
                                    style="width:100%;padding:9px 12px;border:1.5px solid var(--card-border);border-radius:8px;font-family:var(--font);font-size:13px;font-weight:700;outline:none;box-sizing:border-box"
                                    onfocus="this.style.borderColor='#8141a5'" onblur="this.style.borderColor='var(--card-border)'">
                            </div>
                            <div style="padding:10px 12px;background:var(--bg);border-radius:8px;border:1px solid var(--card-border)">
                                <div style="font-size:10.5px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px">Negative Marking</div>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <input type="number" id="exNegMark" placeholder="0.00" min="0" max="10" step="0.25"
                                        style="flex:1;padding:7px 10px;border:1.5px solid var(--card-border);border-radius:7px;font-family:var(--font);font-size:12.5px;outline:none"
                                        onfocus="this.style.borderColor='#8141a5'" onblur="this.style.borderColor='var(--card-border)'">
                                    <span style="font-size:11.5px;color:var(--text-light)">marks per wrong ans</span>
                                </div>
                                <div style="font-size:10.5px;color:var(--text-light);margin-top:4px">Set 0 for no negative marking</div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Preview -->
                    <div class="card" style="background:linear-gradient(135deg,#0f172a,#1e1b4b);border:none;overflow:hidden;position:relative">
                        <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(129,65,165,.15);border-radius:50%"></div>
                        <div class="card-body" style="position:relative;z-index:1">
                            <div style="font-size:9.5px;font-weight:800;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:10px">Live Preview</div>
                            <div id="prevTitle" style="font-size:14px;font-weight:800;color:#fff;margin-bottom:10px;min-height:20px">Exam Title</div>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px">
                                <span id="prevDate" style="font-size:10.5px;font-weight:600;background:rgba(0,184,148,.15);color:#6ee7b7;padding:3px 9px;border-radius:20px">Date</span>
                                <span id="prevMarks" style="font-size:10.5px;font-weight:600;background:rgba(245,158,11,.15);color:#fcd34d;padding:3px 9px;border-radius:20px">Marks</span>
                            </div>
                            <div id="prevBatch" style="font-size:11px;color:rgba(255,255,255,.35)"></div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" id="examSubmitBtn" class="btn bt" style="width:100%;padding:12px;font-size:13px;justify-content:center">
                        <i class="fa-solid fa-calendar-plus"></i> ${window._examEditId ? 'Update Exam' : 'Schedule Exam'}
                    </button>
                    <button type="button" onclick="goNav('exams','schedule')" style="width:100%;padding:9px;background:none;border:1px solid var(--card-border);border-radius:8px;cursor:pointer;font-family:var(--font);font-size:12.5px;color:var(--text-body);transition:all .12s" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='none'">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>`;

    window._updateExamPreview();
};

window._toggleQMode = function(mode) {
    const ml = document.getElementById('qmManualLbl');
    const al = document.getElementById('qmAutoLbl');
    if (ml) ml.style.borderColor = mode === 'manual' ? '#8141a5' : 'var(--card-border)';
    if (al) al.style.borderColor = mode === 'auto'   ? '#8141a5' : 'var(--card-border)';
};

window._updateExamPreview = function() {
    const title  = document.getElementById('exTitle')?.value   || 'Exam Title';
    const date   = document.getElementById('exDate')?.value    || 'Date';
    const marks  = document.getElementById('exTotalMarks')?.value || '—';
    const bEl    = document.getElementById('exBatch');
    const batch  = bEl?.options[bEl.selectedIndex]?.text || '';

    const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    set('prevTitle', title);
    set('prevDate',  date);
    set('prevMarks', marks !== '—' ? marks + ' Marks' : '—');
    set('prevBatch', batch && !batch.includes('Select') ? '📚 ' + batch : '');
};

/* ─────────────────────────────────────────────────────────────────
   SUBMIT
───────────────────────────────────────────────────────────────── */
window._submitExamForm = async function(e) {
    e.preventDefault();
    const errBox = document.getElementById('examFormErrors');
    const btn    = document.getElementById('examSubmitBtn');
    const isEdit = !!window._examEditId;

    const val = id => document.getElementById(id)?.value?.trim() || '';
    const errors = [];

    if (!val('exTitle'))      errors.push('Exam title is required.');
    if (!val('exBatch'))      errors.push('Please select a batch.');
    if (!val('exCourse'))     errors.push('Please select a course.');
    if (!val('exDate'))       errors.push('Exam date is required.');
    if (!val('exStartTime'))  errors.push('Start time is required.');
    if (!val('exEndTime'))    errors.push('End time is required.');
    if (!val('exTotalMarks')) errors.push('Total marks is required.');
    if (!val('exDuration'))   errors.push('Duration is required.');

    if (errors.length) {
        errBox.style.display = 'block';
        errBox.innerHTML     = errors.map(e => `• ${e}`).join('<br>');
        errBox.scrollIntoView({ behavior:'smooth', block:'nearest' });
        return;
    }

    errBox.style.display = 'none';
    btn.disabled         = true;
    btn.innerHTML        = '<i class="fa-solid fa-circle-notch fa-spin"></i> Saving…';

    const qMode = document.querySelector('input[name="exQMode"]:checked')?.value || 'manual';

    const payload = {
        action:           isEdit ? 'update' : 'create',
        id:               window._examEditId || undefined,
        title:            val('exTitle'),
        batch_id:         val('exBatch'),
        course_id:        val('exCourse'),
        exam_date:        val('exDate'),
        start_time:       val('exStartTime'),
        end_time:         val('exEndTime'),
        duration_minutes: val('exDuration'),
        total_marks:      val('exTotalMarks'),
        negative_mark:    document.getElementById('exNegMark')?.value || '0',
        question_mode:    qMode,
    };

    try {
        const res    = await fetch(APP_URL + '/api/admin/exams', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) });
        const result = await res.json();
        if (!result.success) throw new Error(result.message);
        _examToast(result.message || (isEdit ? 'Exam updated!' : 'Exam scheduled!'), 'success');
        window._examEditId = null;
        setTimeout(() => goNav('exams', 'schedule'), 1200);
    } catch(err) {
        _examToast(err.message, 'error');
        btn.disabled = false;
        btn.innerHTML = `<i class="fa-solid fa-calendar-plus"></i> ${isEdit ? 'Update Exam' : 'Schedule Exam'}`;
    }
};

/* ─────────────────────────────────────────────────────────────────
   EDIT
───────────────────────────────────────────────────────────────── */
window.renderEditExamForm = async function(examId) {
    window._examEditId = examId;
    await window.renderCreateExamForm();

    try {
        const res    = await fetch(APP_URL + '/api/admin/exams');
        const result = await res.json();
        if (!result.success) return;
        const ex = (result.data || []).find(e => e.id == examId);
        if (!ex) return;

        const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
        set('exTitle',      ex.title);
        set('exBatch',      ex.batch_id);
        set('exCourse',     ex.course_id);
        set('exDate',       ex.exam_date);
        set('exStartTime',  ex.start_time);
        set('exEndTime',    ex.end_time);
        set('exDuration',   ex.duration_minutes);
        set('exTotalMarks', ex.total_marks);
        set('exNegMark',    ex.negative_mark);

        // Set question mode radio
        const qmRadio = document.querySelector(`input[name="exQMode"][value="${ex.question_mode || 'manual'}"]`);
        if (qmRadio) { qmRadio.checked = true; window._toggleQMode(ex.question_mode || 'manual'); }

        window._updateExamPreview();
    } catch(e) {}
};

/* ─────────────────────────────────────────────────────────────────
   DELETE
───────────────────────────────────────────────────────────────── */
window._deleteExam = async function(examId) {
    if (!confirm('Delete this exam? This cannot be undone.')) return;
    try {
        const res    = await fetch(APP_URL + '/api/admin/exams', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'delete', id:examId}) });
        const result = await res.json();
        if (!result.success) throw new Error(result.message);
        _examToast('Exam deleted.', 'success');
        await _loadExams();
    } catch(e) { _examToast(e.message, 'error'); }
};

/* ─────────────────────────────────────────────────────────────────
   TOAST
───────────────────────────────────────────────────────────────── */
function _examToast(msg, type = 'success') {
    let t = document.getElementById('examToast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'examToast';
        Object.assign(t.style, {
            position:'fixed', bottom:'24px', right:'24px', zIndex:'9999',
            padding:'12px 20px', borderRadius:'10px', fontFamily:'var(--font)',
            fontSize:'13px', fontWeight:'600', display:'flex', alignItems:'center', gap:'8px',
            boxShadow:'0 8px 30px rgba(0,0,0,.15)', transition:'all .3s',
            transform:'translateY(20px)', opacity:'0'
        });
        document.body.appendChild(t);
    }
    t.style.background = type === 'error' ? 'var(--red)' : 'var(--green)';
    t.style.color      = '#fff';
    t.innerHTML        = `<i class="fa-solid ${type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'}"></i> ${msg}`;
    requestAnimationFrame(() => { t.style.opacity='1'; t.style.transform='translateY(0)'; });
    setTimeout(() => { t.style.opacity='0'; t.style.transform='translateY(20px)'; }, 3000);
}
