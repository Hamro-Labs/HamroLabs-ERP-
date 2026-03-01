/**
 * Hamro ERP — Institute Admin · ia-attendance.js
 * Handles Attendance module logic and rendering
 */

window.renderAttendanceTake = async function() {
    const mc = document.getElementById('mainContent');
    if (!mc) return;

    _iaRenderBreadcrumb([
        { label: 'Attendance', link: "javascript:goNav('attendance','take')" },
        { label: 'Mark Attendance' }
    ]);

    mc.innerHTML = `
        <div class="pg fu">
            <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom: 24px;">
                <div>
                    <h2 style="font-size:1.5rem; color:var(--text-dark); margin:0;">Mark Attendance</h2>
                    <p style="color:var(--text-light); margin:5px 0 0 0; font-size:13px;">Manage daily attendance for courses and batches.</p>
                </div>
            </div>

            <div class="card" style="margin-bottom: 24px;">
                <div class="card-body" style="display:flex; gap:16px; flex-wrap:wrap; align-items:flex-end;">
                    <div style="flex:1; min-width:180px;">
                        <label class="form-label" style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--text-dark);">Batch</label>
                        <select id="attBatchSel" class="form-input" style="width:100%; padding:10px; border:1px solid var(--card-border); border-radius:8px;">
                            <option value="">Select Batch...</option>
                        </select>
                    </div>
                    <div style="flex:1; min-width:180px;">
                        <label class="form-label" style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--text-dark);">Date</label>
                        <input type="date" id="attDateSel" class="form-input" style="width:100%; padding:10px; border:1px solid var(--card-border); border-radius:8px;" value="${new Date().toISOString().split('T')[0]}">
                    </div>
                    <div>
                        <button class="qa-btn green" onclick="loadAttendanceRecords()" style="white-space:nowrap; padding:10px 20px;">
                            <i class="fa-solid fa-search"></i> Load Data
                        </button>
                    </div>
                </div>
            </div>

            <div id="attendanceRecordsArea">
                <div style="text-align:center; padding: 40px; color:var(--text-light);">
                    <i class="fa-solid fa-list-check" style="font-size:3rem; margin-bottom:16px; opacity:0.5;"></i>
                    <p>Select a batch to load attendance records.</p>
                </div>
            </div>
        </div>
    `;

    // Load batches dropdown
    try {
        const res = await fetch((window.APP_URL || '') + '/api/admin/batches');
        const data = await res.json();
        if (data.success && data.data) {
            const sel = document.getElementById('attBatchSel');
            data.data.forEach(b => {
                sel.innerHTML += `<option value="${b.id}">${b.name}</option>`;
            });
        }
    } catch(err) {
        console.error('Error loading batches', err);
    }
};

window.loadAttendanceRecords = async function() {
    const batchId = document.getElementById('attBatchSel').value;
    const date = document.getElementById('attDateSel').value;
    const area = document.getElementById('attendanceRecordsArea');

    if (!batchId || !date) {
        alert("Please select both a batch and a date.");
        return;
    }

    area.innerHTML = `
        <div style="text-align:center; padding:40px;">
            <i class="fa-solid fa-circle-notch fa-spin"></i> Loading...
        </div>
    `;

    try {
        const res = await fetch((window.APP_URL || '') + `/api/admin/attendance?batch_id=${batchId}&date=${date}`);
        const data = await res.json();
        
        if (!data.success) throw new Error(data.message);

        const records = data.data || [];
        
        if (records.length === 0) {
             area.innerHTML = `
                <div class="card" style="text-align:center; padding: 40px; color:var(--text-light);">
                    <p>No students found in this batch.</p>
                </div>
             `;
             return;
        }

        // Summary counters
        let pres = 0, abs = 0, late = 0, leave = 0;
        records.forEach(r => {
            const s = r.attendance?.status;
            if (s === 'present') pres++;
            if (s === 'absent') abs++;
            if (s === 'late') late++;
            if (s === 'leave') leave++;
        });

        // Determine global lock state
        const anyLocked = records.some(r => r.attendance?.locked);
        
        let html = `
            <div style="display:flex; justify-content:space-between; margin-bottom:20px; align-items:center;">
                <div style="display:flex; gap:16px;">
                    <div style="background:var(--bg); padding:10px 16px; border-radius:8px; border:1px solid var(--card-border);">
                        <span style="font-size:12px; color:var(--text-light); display:block;">Present</span>
                        <span style="font-size:16px; font-weight:700; color:var(--green);"><span id="count_present">${pres}</span></span>
                    </div>
                    <div style="background:var(--bg); padding:10px 16px; border-radius:8px; border:1px solid var(--card-border);">
                        <span style="font-size:12px; color:var(--text-light); display:block;">Absent</span>
                        <span style="font-size:16px; font-weight:700; color:var(--red);"><span id="count_absent">${abs}</span></span>
                    </div>
                    <div style="background:var(--bg); padding:10px 16px; border-radius:8px; border:1px solid var(--card-border);">
                        <span style="font-size:12px; color:var(--text-light); display:block;">Late</span>
                        <span style="font-size:16px; font-weight:700; color:var(--orange);"><span id="count_late">${late}</span></span>
                    </div>
                    <div style="background:var(--bg); padding:10px 16px; border-radius:8px; border:1px solid var(--card-border);">
                        <span style="font-size:12px; color:var(--text-light); display:block;">Leave</span>
                        <span style="font-size:16px; font-weight:700; color:var(--blue);"><span id="count_leave">${leave}</span></span>
                    </div>
                </div>
                <div>
                    <button class="qa-btn" onclick="bulkMarkAll('present')" style="background:var(--green); color:#fff; border:none; padding:8px 16px; margin-right:8px; border-radius:6px; cursor:pointer;" ${anyLocked ? 'disabled' : ''}>Mark All P</button>
                    <button class="qa-btn" onclick="bulkMarkAll('absent')" style="background:var(--red); color:#fff; border:none; padding:8px 16px; border-radius:6px; cursor:pointer;" ${anyLocked ? 'disabled' : ''}>Mark All A</button>
                </div>
            </div>

            <div class="card">
                <table class="data-table" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--card-border); color:var(--text-light);">
                            <th style="padding:12px 16px; text-align:left; font-weight:600; font-size:13px;"># Roll</th>
                            <th style="padding:12px 16px; text-align:left; font-weight:600; font-size:13px;">Student</th>
                            <th style="padding:12px 16px; text-align:center; font-weight:600; font-size:13px;">Status</th>
                            <th style="padding:12px 16px; text-align:center; font-weight:600; font-size:13px;">Locked</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        // Render rows
        records.forEach(r => {
            const hasAtt = !!r.attendance;
            const status = r.attendance?.status || 'present'; // Default present if unmarked
            const locked = r.attendance?.locked || 0;
            const aid = r.attendance?.id || '';

            const pClass = status === 'present' ? 'active green' : '';
            const aClass = status === 'absent' ? 'active red' : '';
            const lClass = status === 'late' ? 'active orange' : '';
            const lvClass = status === 'leave' ? 'active blue' : '';

            const lockIcon = locked ? '<i class="fa-solid fa-lock" style="color:var(--text-light)"></i>' : '<i class="fa-solid fa-lock-open" style="color:var(--green)"></i>';

            html += `
                <tr style="border-bottom: 1px solid var(--card-border); transition: background 0.2s; cursor:pointer;" class="att-row" data-sid="${r.student_id}" data-id="${aid}">
                    <td style="padding:12px 16px; font-size:14px; font-weight:600; color:var(--text-dark);">${r.roll_no}</td>
                    <td style="padding:12px 16px;">
                        <div style="display:flex; align-items:center;">
                            <img src="${r.photo_url || (window.APP_URL || '') + '/public/assets/images/default-avatar.png'}" style="width:32px; height:32px; border-radius:50%; margin-right:12px; object-fit:cover;">
                            <span style="font-size:14px; font-weight:600; color:var(--text-dark);">${r.full_name}</span>
                        </div>
                    </td>
                    <td style="padding:12px 16px; text-align:center;">
                        <div class="status-toggle" style="display:inline-flex; border-radius:6px; overflow:hidden; border:1px solid var(--card-border);">
                            <button class="status-btn pres ${pClass}" onclick="updateAttStatus(this, 'present')" ${locked ? 'disabled' : ''} style="padding:6px 12px; background:${pClass ? 'var(--green)' : '#fff'}; color:${pClass ? '#fff' : 'var(--text-dark)'}; border:none; cursor:pointer; font-size:13px; font-weight:600;">P</button>
                            <button class="status-btn abs ${aClass}" onclick="updateAttStatus(this, 'absent')" ${locked ? 'disabled' : ''} style="padding:6px 12px; background:${aClass ? 'var(--red)' : '#fff'}; color:${aClass ? '#fff' : 'var(--text-dark)'}; border:none; border-left:1px solid var(--card-border); cursor:pointer; font-size:13px; font-weight:600;">A</button>
                            <button class="status-btn late ${lClass}" onclick="updateAttStatus(this, 'late')" ${locked ? 'disabled' : ''} style="padding:6px 12px; background:${lClass ? 'var(--orange)' : '#fff'}; color:${lClass ? '#fff' : 'var(--text-dark)'}; border:none; border-left:1px solid var(--card-border); cursor:pointer; font-size:13px; font-weight:600;">L</button>
                            <button class="status-btn leav ${lvClass}" onclick="updateAttStatus(this, 'leave')" ${locked ? 'disabled' : ''} style="padding:6px 12px; background:${lvClass ? 'var(--blue)' : '#fff'}; color:${lvClass ? '#fff' : 'var(--text-dark)'}; border:none; border-left:1px solid var(--card-border); cursor:pointer; font-size:13px; font-weight:600;">LV</button>
                        </div>
                        <input type="hidden" class="att-status-val" value="${status}">
                    </td>
                    <td style="padding:12px 16px; text-align:center;">
                        ${lockIcon}
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>

            <div style="margin-top:24px; text-align:right;">
                <button class="qa-btn" onclick="saveAttendance()" style="background:var(--brand); color:#fff; border:none; padding:12px 24px; border-radius:8px; font-weight:600; font-size:14px; cursor:pointer;" ${anyLocked ? 'disabled' : ''}>
                    <i class="fa-solid fa-save" style="margin-right:8px;"></i> Save Attendance
                </button>
            </div>
        `;

        area.innerHTML = html;

    } catch(err) {
        area.innerHTML = `<div style="color:red; padding:20px;">Error: ${err.message}</div>`;
    }
};

window.updateAttStatus = function(btn, status) {
    if (btn.disabled) return;
    const group = btn.parentElement;
    
    // Reset all buttons in group
    const btns = group.querySelectorAll('.status-btn');
    btns.forEach(b => {
        b.className = b.className.replace(/active (green|red|orange|blue)/g, '').trim();
        b.style.background = '#fff';
        b.style.color = 'var(--text-dark)';
    });

    // Set active class
    let colorVar = '';
    let clz = '';
    if (status === 'present') { colorVar = 'var(--green)'; clz = 'active green'; }
    if (status === 'absent') { colorVar = 'var(--red)'; clz = 'active red'; }
    if (status === 'late') { colorVar = 'var(--orange)'; clz = 'active orange'; }
    if (status === 'leave') { colorVar = 'var(--blue)'; clz = 'active blue'; }

    btn.className += ' ' + clz;
    btn.style.background = colorVar;
    btn.style.color = '#fff';

    // Update hidden input
    const row = group.closest('tr');
    row.querySelector('.att-status-val').value = status;

    // Recalculate totals
    recalcAttTotals();
};

window.bulkMarkAll = function(status) {
    const rows = document.querySelectorAll('.att-row');
    rows.forEach(r => {
        const btnClass = status === 'present' ? '.pres' : '.abs';
        const btn = r.querySelector(btnClass);
        if (btn && !btn.disabled) {
            updateAttStatus(btn, status);
        }
    });
};

window.recalcAttTotals = function() {
    let p=0, a=0, l=0, v=0;
    document.querySelectorAll('.att-status-val').forEach(inp => {
        if(inp.value === 'present') p++;
        if(inp.value === 'absent') a++;
        if(inp.value === 'late') l++;
        if(inp.value === 'leave') v++;
    });
    const ep = document.getElementById('count_present'); if(ep) ep.innerText = p;
    const ea = document.getElementById('count_absent'); if(ea) ea.innerText = a;
    const el = document.getElementById('count_late'); if(el) el.innerText = l;
    const elv = document.getElementById('count_leave'); if(elv) elv.innerText = v;
};

window.saveAttendance = async function() {
    const batchId = document.getElementById('attBatchSel').value;
    const date = document.getElementById('attDateSel').value;
    
    const attendance = [];
    document.querySelectorAll('.att-row').forEach(row => {
        const sid = row.getAttribute('data-sid');
        const status = row.querySelector('.att-status-val').value;
        if (sid && status) {
            attendance.push({ student_id: sid, status: status });
        }
    });

    if (attendance.length === 0) return;

    try {
        const payload = {
            action: 'take',
            batch_id: batchId,
            attendance_date: date,
            attendance: attendance
        };

        const res = await fetch((window.APP_URL || '') + '/api/admin/attendance', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (data.success) {
            alert('Attendance saved successfully!');
            loadAttendanceRecords(); // reload
        } else {
            alert('Error: ' + data.message);
        }
    } catch(err) {
        alert('Server error: ' + err.message);
    }
};

window.renderAttendanceReport = function() {
    const mc = document.getElementById('mainContent');
    if (!mc) return;

    _iaRenderBreadcrumb([
        { label: 'Attendance', link: "javascript:goNav('attendance','report')" },
        { label: 'Reports' }
    ]);

    mc.innerHTML = `
        <div class="pg fu">
            <div class="card" style="text-align:center;padding:100px 40px;">
                <i class="fa-solid fa-chart-pie" style="font-size:3rem;color:var(--brand);margin-bottom:20px;opacity:.5"></i>
                <h2>Attendance Analytics</h2>
                <p style="color:var(--text-body);margin-top:10px">Advanced attendance reporting coming soon in next module iteration.</p>
            </div>
        </div>
    `;
};

window.renderLeaveRequests = async function() {
    const mc = document.getElementById('mainContent');
    if (!mc) return;

    _iaRenderBreadcrumb([
        { label: 'Attendance', link: "javascript:goNav('attendance','leave')" },
        { label: 'Leave Requests' }
    ]);

    mc.innerHTML = `
        <div class="pg fu">
             <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom: 24px;">
                <div>
                    <h2 style="font-size:1.5rem; color:var(--text-dark); margin:0;">Leave Requests</h2>
                    <p style="color:var(--text-light); margin:5px 0 0 0; font-size:13px;">Manage student leave applications.</p>
                </div>
            </div>
            <div class="card">
                <div id="leaveReqList" style="padding:20px;text-align:center;">
                    <i class="fa-solid fa-circle-notch fa-spin"></i> Loading...
                </div>
            </div>
        </div>
    `;

    try {
        const res = await fetch((window.APP_URL || '') + '/api/admin/leave-requests?status=pending');
        const data = await res.json();
        
        let html = '';
        if (data.success && data.data.length > 0) {
            html += `<table class="data-table" style="width:100%; border-collapse:collapse;">
                    <thead><tr style="border-bottom: 1px solid var(--card-border); color:var(--text-light);"><th style="padding:12px;text-align:left;">Student ID</th><th style="padding:12px;text-align:left;">From</th><th style="padding:12px;text-align:left;">To</th><th style="padding:12px;text-align:left;">Reason</th><th style="padding:12px;text-align:center;">Actions</th></tr></thead><tbody>`;
            data.data.forEach(r => {
                html += `<tr style="border-bottom:1px solid var(--card-border);">
                    <td style="padding:12px; font-weight:600;">${r.student_id}</td>
                    <td style="padding:12px;">${r.from_date}</td>
                    <td style="padding:12px;">${r.to_date}</td>
                    <td style="padding:12px;color:var(--text-light);">${r.reason}</td>
                    <td style="padding:12px;text-align:center;">
                        <button class="qa-btn" style="background:var(--green); color:#fff; padding:6px 10px; font-size:12px; border:none; border-radius:4px; cursor:pointer;" onclick="actionLeave(${r.id}, 'approve')">Approve</button>
                        <button class="qa-btn" style="background:var(--red); color:#fff; padding:6px 10px; font-size:12px; border:none; border-radius:4px; cursor:pointer; margin-left:4px;" onclick="actionLeave(${r.id}, 'reject')">Reject</button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
        } else {
             html = `<div style="text-align:center; padding: 40px; color:var(--text-light);">
                <p>No pending leave requests.</p>
            </div>`;
        }
        document.getElementById('leaveReqList').innerHTML = html;
    } catch(err) {
        document.getElementById('leaveReqList').innerHTML = 'Error loading requests.';
    }
};

window.actionLeave = async function(id, action) {
    if (!confirm(`Are you sure you want to ${action} this request?`)) return;
    try {
        const res = await fetch((window.APP_URL || '') + '/api/admin/leave-requests', {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id, action: action})
        });
        const data = await res.json();
        if (data.success) {
            alert('Leave request updated successfully.');
            renderLeaveRequests();
        } else {
            alert(data.message);
        }
    } catch(err) {
        alert('Server Error');
    }
};

// Auto-register to nav clicks if breadcrumb script is loaded
if (typeof _iaRenderBreadcrumb === 'undefined') {
    window._iaRenderBreadcrumb = function() {};
}
