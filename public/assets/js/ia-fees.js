/**
 * Hamro ERP — ia-fees.js
 * Fee Setup: List, Add, Edit, Delete fee items
 */

/* ══════════════ HELPER FUNCTIONS ═══════════════════════════════ */
function formatMoney(amount) {
    return parseFloat(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-IN', { year: 'numeric', month: 'short', day: 'numeric' });
}

/* ══════════════ FEE SETUP LIST ═══════════════════════════════ */
window.renderFeeSetup = async function() {
    const mc = document.getElementById('mainContent');
    mc.innerHTML = `<div class="pg fu">
        <div class="bc"><a href="#" onclick="goNav('overview')">Dashboard</a> <span class="bc-sep">&rsaquo;</span> <span class="bc-cur">Fee Items Setup</span></div>
        <div class="pg-head">
            <div class="pg-left"><div class="pg-ico"><i class="fa-solid fa-sliders"></i></div><div><div class="pg-title">Fee Items Setup</div><div class="pg-sub">Configure fee structure for courses</div></div></div>
            <div class="pg-acts"><button class="btn bt" onclick="openAddFeeModal()"><i class="fa-solid fa-plus"></i> Add Fee Item</button></div>
        </div>
        <div class="filter-bar mb">
            <input type="text" id="feeSearchInput" class="form-control" style="max-width:300px" placeholder="Search fee items..." oninput="_filterFeeItems()">
            <select id="feeTypeFilter" class="form-control" style="max-width:180px" onchange="_filterFeeItems()">
                <option value="">All Types</option>
                <option value="admission">Admission</option>
                <option value="monthly">Monthly</option>
                <option value="exam">Exam</option>
                <option value="material">Material</option>
                <option value="fine">Fine</option>
                <option value="other">Other</option>
            </select>
            <select id="feeCourseFilter" class="form-control" style="max-width:200px" onchange="_filterFeeItems()">
                <option value="">All Courses</option>
            </select>
        </div>
        <div class="card" id="feeListContainer"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading fee items...</span></div></div>
    </div>`;
    await _loadFeeItems();
};

let feeItemsData = [];
let coursesData = [];

async function _loadFeeItems() {
    const c = document.getElementById('feeListContainer'); if (!c) return;
    const courseFilter = document.getElementById('feeCourseFilter');
    try {
        const res = await fetch(APP_URL + '/api/admin/fees');
        const result = await res.json(); 
        if (!result.success) throw new Error(result.message);
        
        feeItemsData = result.data || [];
        coursesData = result.courses || [];
        
        // Populate course filter
        if (courseFilter) {
            let options = '<option value="">All Courses</option>';
            coursesData.forEach(crs => {
                options += `<option value="${crs.id}">${crs.name}</option>`;
            });
            courseFilter.innerHTML = options;
        }
        
        _renderFeeItems(feeItemsData);
    } catch(e) { 
        c.innerHTML=`<div style="padding:20px;color:var(--red);text-align:center">${e.message}</div>`; 
    }
}

/* ── QUICK PAYMENT / CONSOLIDATED BILL OVERVIEW ──────────────────── */
window.renderQuickPayment = async (studentId) => {
    const mc = document.getElementById('mainContent');
    if (!mc) return;

    mc.innerHTML = `<div class="pg fu"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Preparing bill overview...</span></div></div>`;

    try {
        const res = await fetch(`${window.APP_URL}/api/admin/fees?action=get_payment_init_data&student_id=${studentId}`);
        const result = await res.json();
        if (!result.success) throw new Error(result.message);

        const { student, institute, summary, records } = result.data;
        const photoSrc = student.photo_url ? (student.photo_url.startsWith('http') ? student.photo_url : window.APP_URL + student.photo_url) : null;
        const initials = student.name ? student.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) : 'ST';

        let recordsHtml = '';
        if (records.length === 0) {
            recordsHtml = `<tr><td colspan="5" style="text-align:center; padding:30px; color:#64748b;">No outstanding fees found.</td></tr>`;
        } else {
            records.forEach(r => {
                recordsHtml += `
                    <tr>
                        <td style="font-weight:600; color:#1e293b;">${r.fee_item_name}</td>
                        <td><span class="badge ${r.fee_type === 'monthly' ? 'bg-info' : 'bg-primary'}">${r.fee_type.replace('_',' ')}</span></td>
                        <td>${new Date(r.due_date).toLocaleDateString()}</td>
                        <td style="text-align:right; font-weight:700;">${getCurrencySymbol()}${parseFloat(r.amount_due).toLocaleString()}</td>
                        <td style="text-align:right; color:#ef4444; font-weight:700;">${getCurrencySymbol()}${parseFloat(r.amount_due - r.amount_paid).toLocaleString()}</td>
                    </tr>
                `;
            });
        }

        const totalDue = records.reduce((sum, r) => sum + (parseFloat(r.amount_due) - parseFloat(r.amount_paid)), 0);

        mc.innerHTML = `
            <div class="pg fu">
                <div class="bc">
                    <a href="#" onclick="goNav('overview')">Dashboard</a> <span class="bc-sep">›</span>
                    <a href="#" onclick="goNav('students')">Students</a> <span class="bc-sep">›</span>
                    <span class="bc-cur">Quick Payment</span>
                </div>

                <div class="card" style="max-width:1000px; margin:20px auto; overflow:hidden; border-radius:16px; box-shadow:0 10px 25px -5px rgba(0,0,0,0.1);">
                    <!-- Institute Header -->
                    <div style="background:linear-gradient(135deg, #009E7E 0%, #007d63 100%); padding:30px; color:white; display:flex; justify-content:space-between; align-items:center;">
                        <div style="display:flex; align-items:center; gap:20px;">
                            ${institute.logo_path ? `<img src="${window.APP_URL}/public/${institute.logo_path}" style="height:60px; filter:brightness(0) invert(1);">` : `<div style="width:60px; height:60px; background:rgba(255,255,255,0.2); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:bold;">${institute.name[0]}</div>`}
                            <div>
                                <h1 style="margin:0; font-size:1.5rem; letter-spacing:-0.5px;">${institute.name}</h1>
                                <p style="margin:5px 0 0; opacity:0.8; font-size:0.9rem;"><i class="fa-solid fa-location-dot"></i> ${institute.address || ''}</p>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:0.8rem; opacity:0.7; text-transform:uppercase; font-weight:700; letter-spacing:1px;">Bill Overview</div>
                            <div style="font-size:1.2rem; font-weight:600;">${new Date().toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</div>
                        </div>
                    </div>

                    <div style="padding:30px; display:grid; grid-template-columns:1fr 1fr; gap:30px; background:#f8fafc;">
                        <!-- Student Section -->
                        <div class="card" style="padding:20px; border:none; box-shadow:0 1px 3px rgba(0,0,0,0.05); background:white;">
                            <h3 style="font-size:0.9rem; color:#64748b; margin-top:0; border-bottom:1px solid #f1f5f9; padding-bottom:10px; margin-bottom:15px; text-transform:uppercase; letter-spacing:0.5px;">Student Information</h3>
                            <div style="display:flex; gap:15px; align-items:center;">
                                <div style="width:70px; height:70px; border-radius:50%; overflow:hidden; background:#f1f5f9; border:3px solid #e2e8f0;">
                                    ${photoSrc ? `<img src="${photoSrc}" style="width:100%; height:100%; object-fit:cover;">` : `<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-weight:bold; color:#64748b; font-size:1.2rem;">${initials}</div>`}
                                </div>
                                <div style="flex:1;">
                                    <div style="font-size:1.1rem; font-weight:700; color:#1e293b;">${student.name}</div>
                                    <div style="font-size:0.85rem; color:#64748b; margin-top:2px;"><i class="fa-solid fa-id-badge"></i> ${student.roll_no || 'No Roll No'}</div>
                                    <div style="font-size:0.85rem; font-weight:600; color:#009E7E; margin-top:4px;">${student.course_name} • ${student.batch_name}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Section -->
                        <div class="card" style="padding:20px; border:none; box-shadow:0 1px 3px rgba(0,0,0,0.05); background:white;">
                            <h3 style="font-size:0.9rem; color:#64748b; margin-top:0; border-bottom:1px solid #f1f5f9; padding-bottom:10px; margin-bottom:15px; text-transform:uppercase; letter-spacing:0.5px;">Financial Summary</h3>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                <div style="padding:10px; background:#f0fdf4; border-radius:10px;">
                                    <div style="font-size:0.75rem; color:#166534; font-weight:600;">Total Paid</div>
                                    <div style="font-size:1.1rem; font-weight:700; color:#166534;">${getCurrencySymbol()}${parseFloat(summary?.total_paid || 0).toLocaleString()}</div>
                                </div>
                                <div style="padding:10px; background:#fef2f2; border-radius:10px;">
                                    <div style="font-size:0.75rem; color:#991b1b; font-weight:600;">Due Amount</div>
                                    <div style="font-size:1.1rem; font-weight:700; color:#991b1b;">${getCurrencySymbol()}${parseFloat(summary?.due_amount || 0).toLocaleString()}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Outstanding Fees Table -->
                    <div style="padding:0 30px 30px;">
                        <h3 style="font-size:1rem; font-weight:700; color:#1e293b; margin-bottom:15px; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-list-check" style="color:#009E7E;"></i> Outstanding Fees
                        </h3>
                        <div class="table-responsive">
                            <table class="table" style="margin:0;">
                                <thead style="background:#f1f5f9;">
                                    <tr>
                                        <th style="padding:12px 15px; font-size:0.85rem; color:#475569;">Fee Item</th>
                                        <th style="padding:12px 15px; font-size:0.85rem; color:#475569;">Type</th>
                                        <th style="padding:12px 15px; font-size:0.85rem; color:#475569;">Due Date</th>
                                        <th style="padding:12px 15px; font-size:0.85rem; color:#475569; text-align:right;">Amount</th>
                                        <th style="padding:12px 15px; font-size:0.85rem; color:#475569; text-align:right;">Net Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${recordsHtml}
                                </tbody>
                                <tfoot style="background:#f8fafc; font-weight:700; border-top:2px solid #e2e8f0;">
                                    <tr>
                                        <td colspan="4" style="text-align:right; padding:15px; font-size:1rem; color:#1e293b;">Total Current Outstanding:</td>
                                        <td style="text-align:right; padding:15px; font-size:1.1rem; color:#ef4444;">${getCurrencySymbol()}${totalDue.toLocaleString()}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Form Section -->
                    <div style="padding:30px; background:#f1f5f9; border-top:1px solid #e2e8f0;">
                        <h3 style="font-size:1rem; font-weight:700; color:#1e293b; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-money-bill-transfer" style="color:#009E7E;"></i> Record New Payment
                        </h3>
                        <form id="quickPaymentForm" class="row">
                            <input type="hidden" name="student_id" value="${studentId}">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Amount to Pay (${getCurrencySymbol()})</label>
                                    <input type="number" name="amount" class="form-control" value="${totalDue}" min="1" max="${totalDue}" required style="font-weight:bold; font-size:1.1rem;">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Payment Mode</label>
                                    <select name="payment_mode" class="form-control" required>
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="esewa">eSewa</option>
                                        <option value="khalti">Khalti</option>
                                        <option value="card">Card</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">Payment Date</label>
                                    <input type="date" name="payment_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Notes / Reference</label>
                                    <input type="text" name="notes" class="form-control" placeholder="e.g., Transaction ID, Cheque No.">
                                </div>
                            </div>
                            
                            <div class="col-12" style="margin-top:20px; display:flex; justify-content:flex-end; gap:12px; border-top:1px solid #cbd5e1; padding-top:25px;">
                                <button type="button" class="btn bs" onclick="goNav('students','profile',{id:${studentId}})" style="padding:10px 25px;">Cancel</button>
                                <button type="submit" class="btn bt" style="padding:10px 35px; background:#009e7e; font-weight:600; border-radius:10px;">
                                    <i class="fa-solid fa-check-circle"></i> Proceed to Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('quickPaymentForm').onsubmit = async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Processing...';

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            data.action = 'record_payment'; // Reuse existing logic if possible, or we might need to adjust it

            try {
                // Since our new UI might pay multiple items, we need a special backend handler or 
                // handle it here by calling record_payment multiple times (not efficient)
                // OR adapt record_payment to handle "auto-distribute"
                
                // For now, let's assume we use the existing record_payment logic 
                // but we need to know WHICH fee record. The diagram shows a "Consolidated" payment.
                // In FinanceService.php, recordPayment handles one record at a time.
                // WE NEED A BULK PAYMENT ACTION IN BACKEND.
                
                // Let's call a new bulk action
                const res = await fetch(`${window.APP_URL}/api/admin/fees`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'record_bulk_payment',
                        student_id: studentId,
                        amount: data.amount,
                        payment_mode: data.payment_mode,
                        payment_date: data.payment_date,
                        notes: data.notes
                    })
                });
                
                const result = await res.json();
                if (result.success) {
                    const d = result.data;
                    window._showEmailSendingScreen(d.receipt_no, d.student_name, d.student_id, d.email_status);
                }
                else {
                    Swal.fire('Error', result.message, 'error');
                    btn.disabled = false; btn.innerHTML = orig;
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Something went wrong', 'error');
                btn.disabled = false; btn.innerHTML = orig;
            }
        };

    } catch (error) {
        console.error(error);
        mc.innerHTML = `<div class="card" style="padding:60px; text-align:center; color:var(--red);">
            <i class="fa-solid fa-circle-exclamation" style="font-size:3rem; margin-bottom:10px;"></i>
            <p>${error.message}</p>
            <button class="btn bt" onclick="goNav('students')">Back to Directory</button>
        </div>`;
    }
}

/* ── POST-PAYMENT SUCCESS SCREEN ────────────────────────────────── */
window._showEmailSendingScreen = (receiptNo, studentName, studentId, emailStatus = null) => {
    const mc = document.getElementById('mainContent');
    if (!mc) return;

    let emailStatusHtml = '';
    if (emailStatus !== null) {
        let statusMsg = 'Email status unknown';
        let statusColor = '#475569';
        let bgColor = '#f1f5f9';
        let borderColor = '#e2e8f0';
        let icon = 'fa-circle-info';

        if (emailStatus === 'sent' || emailStatus === 'sent_no_pdf') {
            statusMsg = 'Receipt sent to student email';
            statusColor = '#166534';
            bgColor = '#f0fdf4';
            borderColor = '#bbf7d0';
            icon = 'fa-paper-plane';
        } else if (emailStatus === 'no_email') {
            statusMsg = 'Student has no email address on file';
            statusColor = '#92400e';
            bgColor = '#fffbeb';
            borderColor = '#fde68a';
            icon = 'fa-envelope-circle-check';
        } else if (emailStatus === 'failed') {
            statusMsg = 'Email failed to send (SMTP/Server Error)';
            statusColor = '#991b1b';
            bgColor = '#fef2f2';
            borderColor = '#fecaca';
            icon = 'fa-circle-exclamation';
        }

        emailStatusHtml = `
            <div style="background:${bgColor}; border:1px dashed ${borderColor}; padding:12px; border-radius:12px; margin-bottom:25px; display:inline-block; width:100%;">
                <div style="display:flex; align-items:center; justify-content:center; gap:8px; color:${statusColor}; font-size:0.85rem; font-weight:600;">
                    <i class="fa-solid ${icon}"></i>
                    ${statusMsg}
                </div>
            </div>
        `;
    }

    mc.innerHTML = `
        <div class="pg fu" style="display:flex; align-items:center; justify-content:center; min-height:80vh;">
            <div class="card" style="max-width:500px; width:100%; text-align:center; padding:50px 40px; border-radius:24px; box-shadow:0 20px 50px rgba(0,0,0,0.1);">
                <div style="width:100px; height:100px; background:#f0fdf4; color:#22c55e; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 25px; font-size:3rem; animation: bounceIn 0.8s cubic-bezier(0.68, -0.55, 0.27, 1.55);">
                    <i class="fa-solid fa-check"></i>
                </div>
                
                <h2 style="font-size:1.8rem; font-weight:800; color:#1e293b; margin-bottom:10px;">Payment Successful!</h2>
                <p style="color:#64748b; margin-bottom:30px;">Payment for <strong>${studentName}</strong> has been recorded successfully.</p>
                
                <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:15px; border-radius:12px; margin-bottom:20px; display:inline-block; width:100%;">
                    <div style="font-size:0.75rem; color:#64748b; text-transform:uppercase; font-weight:700; letter-spacing:1px; margin-bottom:5px;">Receipt Number</div>
                    <div style="font-size:1.4rem; font-weight:800; color:#0f172a;">${receiptNo}</div>
                </div>

                ${emailStatusHtml}

                <div style="display:grid; grid-template-columns:1fr; gap:12px;">
                    <button class="btn bt" onclick="window.openReceipt('${receiptNo}')" style="background:#009e7e; padding:12px;">
                        <i class="fa-solid fa-file-pdf"></i> View & Print Receipt
                    </button>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:10px;">
                        <button class="btn bs" onclick="goNav('fee','outstanding')" style="padding:10px;">
                            <i class="fa-solid fa-list"></i> All Dues
                        </button>
                        <button class="btn bs" onclick="goNav('students','profile',{id:${studentId}})" style="padding:10px; border-color:#009e7e; color:#009e7e;">
                            <i class="fa-solid fa-user"></i> Student Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
};

window.openReceipt = (receiptNo, transactionId = null) => {
    let url = `${window.APP_URL}/api/admin/fees?action=generate_receipt_html`;
    if (transactionId) {
        url += `&transaction_id=${transactionId}`;
    } else {
        url += `&receipt_no=${receiptNo}`;
    }
    window.open(url, '_blank');
};

function _renderFeeItems(items) {
    const c = document.getElementById('feeListContainer'); if (!c) return;
    
    if (!items.length) {
        c.innerHTML = `<div style="padding:60px;text-align:center;color:#94a3b8;">
            <i class="fa-solid fa-hand-holding-dollar" style="font-size:3rem;margin-bottom:15px;"></i>
            <p>No fee items configured yet.<br>Click "Add Fee Item" to create your first fee structure.</p>
        </div>`;
        return;
    }
    
    let html = `<div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Fee Name</th>
                    <th>Course</th>
                    <th>Type</th>
                    <th>Amount (NPR)</th>
                    <th>Installments</th>
                    <th>Late Fine/Day</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>`;
    
    items.forEach(fi => {
        const typeColors = {
            'admission': 'bg-g',
            'monthly': 'bg-b',
            'exam': 'bg-y',
            'material': 'bg-p',
            'fine': 'bg-r',
            'other': 'bg-s'
        };
        
        html += `<tr>
            <td><div style="font-weight:600">${fi.name}</div></td>
            <td>${fi.course_name || '-'}</td>
            <td><span class="tag ${typeColors[fi.type] || 'bg-s'}">${fi.type.toUpperCase()}</span></td>
            <td>${parseFloat(fi.amount).toLocaleString()}</td>
            <td>${fi.installments}</td>
            <td>${parseFloat(fi.late_fine_per_day).toLocaleString()}</td>
            <td>${fi.is_active ? '<span class="tag bg-t">Active</span>' : '<span class="tag bg-r">Inactive</span>'}</td>
            <td style="text-align:right;white-space:nowrap">
                <button class="btn-icon" title="Edit" onclick="openEditFeeModal(${fi.id})"><i class="fa-solid fa-pen"></i></button>
                <button class="btn-icon" title="${fi.is_active ? 'Deactivate' : 'Activate'}" onclick="toggleFeeItem(${fi.id})"><i class="fa-solid fa-toggle-${fi.is_active ? 'on' : 'off'}"></i></button>
                <button class="btn-icon text-danger" title="Delete" onclick="deleteFeeItem(${fi.id},'${fi.name.replace(/'/g,"\\'")}')"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>`;
    });
    
    html += `</tbody></table></div>`;
    c.innerHTML = html;
}

function _filterFeeItems() {
    const search = document.getElementById('feeSearchInput')?.value?.toLowerCase() || '';
    const typeFilter = document.getElementById('feeTypeFilter')?.value || '';
    const courseFilter = document.getElementById('feeCourseFilter')?.value || '';
    
    const filtered = feeItemsData.filter(fi => {
        const matchSearch = !search || fi.name.toLowerCase().includes(search);
        const matchType = !typeFilter || fi.type === typeFilter;
        const matchCourse = !courseFilter || String(fi.course_id) === courseFilter;
        return matchSearch && matchType && matchCourse;
    });
    
    _renderFeeItems(filtered);
}

/* ══════════════ ADD/EDIT MODAL ═══════════════════════════════ */
function openAddFeeModal() {
    const coursesOptions = coursesData.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    
    const modalHtml = `
    <div class="modal-overlay" onclick="if(event.target===this)closeModal('feeModal')">
        <div class="modal" style="max-width:550px">
            <div class="modal-header">
                <h3><i class="fa-solid fa-plus"></i> Add Fee Item</h3>
                <button class="modal-close" onclick="closeModal('feeModal')">&times;</button>
            </div>
            <form id="feeItemForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Fee Item Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Monthly Tuition Fee">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Course *</label>
                        <select name="course_id" class="form-control" required>
                            <option value="">Select Course</option>
                            ${coursesOptions}
                        </select>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div class="form-group">
                            <label class="form-label">Fee Type *</label>
                            <select name="type" class="form-control" required>
                                <option value="monthly">Monthly</option>
                                <option value="admission">Admission</option>
                                <option value="exam">Exam</option>
                                <option value="material">Material</option>
                                <option value="fine">Fine</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Amount (NPR) *</label>
                            <input type="number" name="amount" class="form-control" required min="1" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div class="form-group">
                            <label class="form-label">Installments</label>
                            <input type="number" name="installments" class="form-control" value="1" min="1" max="12">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Late Fine per Day (NPR)</label>
                            <input type="number" name="late_fine_per_day" class="form-control" value="0" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="is_active" checked>
                            <span class="form-check-label">Active</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bs" onclick="closeModal('feeModal')">Cancel</button>
                    <button type="submit" class="btn bt">Save Fee Item</button>
                </div>
            </form>
        </div>
    </div>`;
    
    _showModal('feeModal', modalHtml);
    document.getElementById('feeItemForm').onsubmit = e => _submitFeeForm(e, 'create');
}

function openEditFeeModal(id) {
    const feeItem = feeItemsData.find(fi => fi.id === id);
    if (!feeItem) return;
    
    const coursesOptions = coursesData.map(c => 
        `<option value="${c.id}" ${c.id === feeItem.course_id ? 'selected' : ''}>${c.name}</option>`
    ).join('');
    
    const modalHtml = `
    <div class="modal-overlay" onclick="if(event.target===this)closeModal('feeModal')">
        <div class="modal" style="max-width:550px">
            <div class="modal-header">
                <h3><i class="fa-solid fa-pen"></i> Edit Fee Item</h3>
                <button class="modal-close" onclick="closeModal('feeModal')">&times;</button>
            </div>
            <form id="feeItemForm">
                <input type="hidden" name="id" value="${id}">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Fee Item Name *</label>
                        <input type="text" name="name" class="form-control" required value="${feeItem.name}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Course *</label>
                        <select name="course_id" class="form-control" required>
                            <option value="">Select Course</option>
                            ${coursesOptions}
                        </select>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div class="form-group">
                            <label class="form-label">Fee Type *</label>
                            <select name="type" class="form-control" required>
                                <option value="monthly" ${feeItem.type === 'monthly' ? 'selected' : ''}>Monthly</option>
                                <option value="admission" ${feeItem.type === 'admission' ? 'selected' : ''}>Admission</option>
                                <option value="exam" ${feeItem.type === 'exam' ? 'selected' : ''}>Exam</option>
                                <option value="material" ${feeItem.type === 'material' ? 'selected' : ''}>Material</option>
                                <option value="fine" ${feeItem.type === 'fine' ? 'selected' : ''}>Fine</option>
                                <option value="other" ${feeItem.type === 'other' ? 'selected' : ''}>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Amount (NPR) *</label>
                            <input type="number" name="amount" class="form-control" required min="1" step="0.01" value="${feeItem.amount}">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div class="form-group">
                            <label class="form-label">Installments</label>
                            <input type="number" name="installments" class="form-control" value="${feeItem.installments}" min="1" max="12">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Late Fine per Day (NPR)</label>
                            <input type="number" name="late_fine_per_day" class="form-control" value="${feeItem.late_fine_per_day}" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="is_active" ${feeItem.is_active ? 'checked' : ''}>
                            <span class="form-check-label">Active</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bs" onclick="closeModal('feeModal')">Cancel</button>
                    <button type="submit" class="btn bt">Update Fee Item</button>
                </div>
            </form>
        </div>
    </div>`;
    
    _showModal('feeModal', modalHtml);
    document.getElementById('feeItemForm').onsubmit = e => _submitFeeForm(e, 'update');
}

function _showModal(id, html) {
    // Remove existing modal if any
    const existing = document.getElementById(id);
    if (existing) existing.remove();
    
    const div = document.createElement('div');
    div.id = id;
    div.innerHTML = html;
    document.body.appendChild(div);
    
    // Add modal styles if not exists
    if (!document.getElementById('modal-styles')) {
        const style = document.createElement('style');
        style.id = 'modal-styles';
        style.textContent = `
            .modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999; }
            .modal { background:#fff;border-radius:12px;width:90%;max-width:500px;box-shadow:0 20px 60px rgba(0,0,0,0.3); }
            .modal-header { padding:20px 25px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center; }
            .modal-header h3 { margin:0;font-size:1.2rem;display:flex;align-items:center;gap:10px; }
            .modal-close { background:none;border:none;font-size:1.5rem;cursor:pointer;color:#64748b; }
            .modal-body { padding:25px; }
            .modal-footer { padding:15px 25px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px; }
        `;
        document.head.appendChild(style);
    }
}

window.closeModal = function(id) {
    const modal = document.getElementById(id);
    if (modal) modal.remove();
};

async function _submitFeeForm(e, action) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const data = {
        action: action,
        name: formData.get('name'),
        course_id: formData.get('course_id'),
        type: formData.get('type'),
        amount: formData.get('amount'),
        installments: formData.get('installments'),
        late_fine_per_day: formData.get('late_fine_per_day'),
        is_active: form.querySelector('input[name="is_active"]')?.checked || false
    };
    
    if (action === 'update') {
        data.id = formData.get('id');
    }
    
    try {
        const res = await fetch(APP_URL + '/api/admin/fees', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await res.json();
        if (!result.success) throw new Error(result.message);
        
        closeModal('feeModal');
        await _loadFeeItems();
        
        // Show success toast
        _showToast(result.message || 'Fee item saved successfully');
    } catch(err) {
        alert(err.message);
    }
}

async function toggleFeeItem(id) {
    if (!confirm('Are you sure you want to toggle this fee item status?')) return;
    
    try {
        const res = await fetch(APP_URL + '/api/admin/fees', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'toggle', id: id })
        });
        
        const result = await res.json();
        if (!result.success) throw new Error(result.message);
        
        await _loadFeeItems();
        _showToast(result.message || 'Status updated');
    } catch(err) {
        alert(err.message);
    }
}

async function deleteFeeItem(id, name) {
    if (!confirm(`Are you sure you want to delete "${name}"? This action may not be reversible if there are existing fee records.`)) return;
    
    try {
        const res = await fetch(APP_URL + '/api/admin/fees', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', id: id })
        });
        
        const result = await res.json();
        if (!result.success) throw new Error(result.message);
        
        await _loadFeeItems();
        _showToast(result.message || 'Fee item deleted');
    } catch(err) {
        alert(err.message);
    }
}

function _showToast(message) {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position:fixed;bottom:20px;right:20px;background:#10b981;color:#fff;padding:12px 20px;
        border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:10000;
        animation:slideIn 0.3s ease;
    `;
    toast.innerHTML = `<i class="fa-solid fa-check-circle"></i> ${message}`;
    document.body.appendChild(toast);
    
    // Add animation keyframes if not exists
    if (!document.getElementById('toast-anim')) {
        const style = document.createElement('style');
        style.id = 'toast-anim';
        style.textContent = `
            @keyframes slideIn { from { transform:translateX(100%);opacity:0; } to { transform:translateX(0);opacity:1; } }
        `;
        document.head.appendChild(style);
    }
    
    setTimeout(() => toast.remove(), 3000);
}
// Combined with window._showEmailSendingScreen above

/* ══════════════ FEE RECORD / PAYMENT COLLECTION ═══════════════════════════════ */
window.renderFeeRecord = async function() {
    const mc = document.getElementById('mainContent');
    mc.innerHTML = `<div class="pg fu">
        <div class="bc"><a href="#" onclick="goNav('overview')">Dashboard</a> <span class="bc-sep">&rsaquo;</span> <span class="bc-cur">Record Payment</span></div>
        <div class="pg-head">
            <div class="pg-left"><div class="pg-ico"><i class="fa-solid fa-money-bill-wave"></i></div><div><div class="pg-title">Record Fee Payment</div><div class="pg-sub">Record student fee payments</div></div></div>
            <div class="pg-acts"><button class="btn bs" onclick="renderFeeOutstanding()"><i class="fa-solid fa-list"></i> View All Dues</button></div>
        </div>
        
        <!-- Inject Premium Styles -->
        <link rel="stylesheet" href="${APP_URL}/public/assets/css/ia-payment-premium.css">
        
        <!-- Student Search Section -->
        <div class="card mb" style="padding:25px;">
            <h4 style="margin:0 0 15px 0;"><i class="fa-solid fa-magnifying-glass"></i> Find Student</h4>
            <div style="display:flex;gap:15px;flex-wrap:wrap;">
                <input type="text" id="studentSearchInput" class="form-control" style="flex:1;min-width:250px" placeholder="Search by name, student ID, or phone..." onkeyup="_searchStudents(this.value)">
                <select id="studentCourseFilter" class="form-control" style="width:200px" onchange="_filterStudentsByCourse()">
                    <option value="">All Courses</option>
                </select>
            </div>
            <div id="studentSearchResults" style="margin-top:15px;max-height:300px;overflow-y:auto;"></div>
        </div>
        
        <!-- Selected Student & Outstanding Fees -->
        <div id="selectedStudentSection">            <!-- Selected Student Header -->
            <div class="card mb" id="selectedStudentSectionCard" style="display:none; padding:25px; border-left: 5px solid var(--green);">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
                    <div>
                        <h3 id="selectedStudentName" style="margin:0; font-size:1.5rem; color:var(--text-dark);"></h3>
                        <p id="selectedStudentInfo" style="margin:5px 0 0 0; color:var(--text-light); font-size:0.95rem;"></p>
                    </div>
                    <button class="btn bs" onclick="_clearSelectedStudent()"><i class="fa-solid fa-xmark"></i> Clear Selection</button>
                </div>
                
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:20px;" id="studentFinancialSummary">
                    <div class="card glass-card" style="padding:20px; display:flex; align-items:center; gap:15px;">
                        <div class="progress-circle" id="circlePaid" style="--p-color:#10b981; --p-val:0deg;"><span>0%</span></div>
                        <div>
                            <span style="font-size:0.75rem; color:#64748b; text-transform:uppercase; font-weight:700;">Yearly Paid</span>
                            <div style="font-size:1.2rem; font-weight:800; color:#10b981;" id="sumTotalPaid">NPR 0</div>
                        </div>
                    </div>
                    <div class="card glass-card" style="padding:20px;">
                        <span style="font-size:0.75rem; color:#ef4444; text-transform:uppercase; font-weight:700;">Total Outstanding</span>
                        <div style="font-size:1.2rem; font-weight:800; color:#ef4444;" id="sumTotalBalance">NPR 0</div>
                        <div style="font-size:0.75rem; color:#94a3b8; margin-top:4px;" id="sumTotalAssigned">Assigned: NPR 0</div>
                    </div>
                    <div class="card glass-card" style="padding:20px;">
                        <span style="font-size:0.75rem; color:#f59e0b; text-transform:uppercase; font-weight:700;">Next Due</span>
                        <div style="font-size:1.2rem; font-weight:800; color:#f59e0b;" id="nextDueAmount">NPR 0</div>
                        <div style="font-size:0.75rem; color:#94a3b8; margin-top:4px;" id="nextDueDate">Date: -</div>
                    </div>
                </div>
            </div>
            
            <!-- Outstanding Fees Table -->
            <div class="card mb" style="padding:25px;">
                <h4 style="margin:0 0 15px 0;"><i class="fa-solid fa-clock"></i> Outstanding Fees for this Student</h4>
                <div id="outstandingFeesList"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading...</span></div></div>
            </div>
            
            <!-- Payment Form -->
            <div class="card mb" style="padding:25px;" id="paymentFormSection">
                <h4 style="margin:0 0 15px 0;"><i class="fa-solid fa-credit-card"></i> Record Payment</h4>
                <form id="feePaymentForm">
                    <input type="hidden" id="paymentStudentId" name="student_id">
                    <input type="hidden" id="paymentFeeRecordId" name="fee_record_id">
                    
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;">
                        <div class="form-group">
                            <label class="form-label">Fee Type *</label>
                            <select name="fee_item_id" id="paymentFeeItem" class="form-control" required>
                                <option value="">Select Fee</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Installment *</label>
                            <select name="installment_no" id="paymentInstallment" class="form-control" required>
                                <option value="">Select Installment</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;">
                        <div class="form-group">
                            <label class="form-label">Amount Due (NPR)</label>
                            <input type="text" id="amountDue" class="form-control" readonly style="background:#f1f5f9;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Amount Paying *</label>
                            <input type="number" name="amount_paid" id="amountPaid" class="form-control" required min="0" step="0.01" placeholder="0.00">
                            <div id="liveFineDisplay"></div>
                        </div>
                    </div>
                    
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;">
                        <div class="form-group">
                            <label class="form-label">Payment Date *</label>
                            <input type="date" name="paid_date" id="paidDate" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payment Mode *</label>
                            <select name="payment_mode" class="form-control" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="khalti">FonePay / QR</option>
                                <option value="esewa">eSewa</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;">
                        <div class="form-group">
                            <label class="form-label">Receipt No. (Optional)</label>
                            <input type="text" name="receipt_no" class="form-control" placeholder="Auto-generated if left blank">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Upload Bill/Receipt Image (Optional)</label>
                            <input type="file" name="receipt_image" accept="image/*,.pdf" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any additional notes..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn bt"><i class="fa-solid fa-check"></i> Record Payment</button>
                </form>
            </div>
        </div>
        
        <!-- Recent Payments -->
        <div class="card" style="padding:25px;">
            <h4 style="margin:0 0 15px 0;"><i class="fa-solid fa-history"></i> Recent Payments</h4>
            <div id="recentPaymentsList"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading...</span></div></div>
        </div>
    </div>`;
    
    // Set default payment date to today
    document.getElementById('paidDate').value = new Date().toISOString().split('T')[0];

    // Handle Fee Item Change -> Populate Installments
    document.getElementById('paymentFeeItem').addEventListener('change', function() {
        const feeId = this.value;
        const instSelect = document.getElementById('paymentInstallment');
        instSelect.innerHTML = '<option value="">Select Installment</option>';
        
        if (!feeId) return;

        const filtered = outstandingFeesData.filter(of => of.fee_item_id == feeId);
        filtered.forEach(of => {
            const isOverdue = new Date(of.due_date) < new Date();
            const label = `Inst. ${of.installment_no} (Due: ${of.due_date})${isOverdue ? ' - OVERDUE' : ''}`;
            instSelect.innerHTML += `<option value="${of.installment_no}">${label}</option>`;
        });
    });

    // Handle Installment Change -> Set Amount and Record ID
    document.getElementById('paymentInstallment').addEventListener('change', function() {
        const instNo = this.value;
        const feeId = document.getElementById('paymentFeeItem').value;
        
        if (!instNo || !feeId) return;

        const fr = outstandingFeesData.find(of => of.fee_item_id == feeId && of.installment_no == instNo);
        if (fr) {
            const balance = parseFloat(fr.amount_due) - parseFloat(fr.amount_paid);
            document.getElementById('amountDue').value = balance;
            document.getElementById('amountPaid').value = balance;
            document.getElementById('paymentFeeRecordId').value = fr.id;
            
            // Trigger Live Fine Update
            _updateLiveFineFeedback(fr.id);
        }
    });

    // Live Fine Support on Date Change
    document.getElementById('paidDate').addEventListener('change', () => {
        const rid = document.getElementById('paymentFeeRecordId').value;
        if (rid) _updateLiveFineFeedback(rid);
    });
    
    // Bind feePaymentForm submit
    document.getElementById('feePaymentForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const origText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Processing...';
        btn.disabled = true;

        const formData = new FormData(this);
        formData.append('action', 'record_payment');

        try {
            // Send to fees.php
            const res = await fetch(APP_URL + '/api/admin/fees', {
                method: 'POST',
                body: formData
            });

            const result = await res.json();
            if (result.success) {
                this.reset();
                document.getElementById('paidDate').value = new Date().toISOString().split('T')[0];
                
                // Show Email Sending Screen
                const d = result.data;
                window._showEmailSendingScreen(d.receipt_no, d.student_name, d.student_id, d.email_status);
                
                // Refresh data in background
                const studentId = document.getElementById('paymentStudentId').value;
                if (studentId) {
                    await _loadOutstandingFees(studentId);
                    await renderStudentLedger(studentId);
                    await _loadRecentPayments();
                }
            } else {
                alert(result.message || 'Payment failed');
            }
        } catch(err) {
            console.error(err);
            alert('An error occurred while recording payment.');
        } finally {
            btn.innerHTML = origText;
            btn.disabled = false;
        }
    });

    // Load courses for filter
    await _loadCoursesForFilter();
    
    // Load recent payments
    await _loadRecentPayments();

    // CHECK FOR STUDENT_ID IN URL (Auto-select student)
    const urlParams = new URLSearchParams(window.location.search);
    const sid = urlParams.get('student_id');
    if (sid) {
        _autoSelectStudent(sid);
    }
};

async function _autoSelectStudent(id) {
    try {
        // Fetch student details from API
        const res = await fetch(APP_URL + '/api/admin/students?id=' + id);
        const result = await res.json();
        if (result.success && result.data) {
            // Some APIs return an array even for single ID, or a single object
            const s = Array.isArray(result.data) ? result.data[0] : result.data;
            if (s) {
                // Mapping field names if they differ
                const name = s.full_name || s.name;
                const course = s.course_name || '';
                const batch = s.batch_name || '';
                _selectStudent(s.id, name, course, batch);
            }
        }
    } catch(e) { console.error('Auto-select error:', e); }
}

let studentsData = [];
let selectedStudent = null;
let outstandingFeesData = [];

async function _loadCoursesForFilter() {
    try {
        const res = await fetch(APP_URL + '/api/admin/fees');
        const result = await res.json();
        if (result.success && result.courses) {
            const select = document.getElementById('studentCourseFilter');
            result.courses.forEach(c => {
                select.innerHTML += `<option value="${c.id}">${c.name}</option>`;
            });
        }
    } catch(e) { console.error(e); }
}

async function _searchStudents(query) {
    const container = document.getElementById('studentSearchResults');
    if (!query || query.length < 2) {
        container.innerHTML = '';
        return;
    }
    
    try {
        const res = await fetch(APP_URL + '/api/admin/students?search=' + encodeURIComponent(query));
        const result = await res.json();
        
        if (!result.success || !result.data || result.data.length === 0) {
            container.innerHTML = '<div style="padding:20px;text-align:center;color:#94a3b8;">No students found</div>';
            return;
        }
        
        studentsData = result.data;
        let html = '<table class="table" style="margin:0;"><thead><tr><th>Name</th><th>Course</th><th>Batch</th><th>Action</th></tr></thead><tbody>';
        
        result.data.forEach(s => {
            html += `<tr>
                <td><strong>${s.name}</strong><br><small>${s.student_id || 'N/A'}</small></td>
                <td>${s.course_name || '-'}</td>
                <td>${s.batch_name || '-'}</td>
                <td><button class="btn bt" style="padding:6px 12px;font-size:12px;" onclick="_selectStudent(${s.id}, '${s.name.replace(/'/g,"\\'")}', '${(s.course_name || '').replace(/'/g,"\\'")}', '${(s.batch_name || '').replace(/'/g,"\\'")}')"><i class="fa-solid fa-check"></i> Select</button></td>
            </tr>`;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
    } catch(e) {
        container.innerHTML = '<div style="padding:20px;text-align:center;color:#ef4444;">Error loading students</div>';
    }
}

function _filterStudentsByCourse() {
    const courseFilter = document.getElementById('studentCourseFilter')?.value;
    const searchInput = document.getElementById('studentSearchInput');
    if (courseFilter && studentsData.length > 0) {
        const filtered = studentsData.filter(s => String(s.course_id) === courseFilter);
        // Re-render results
        if (filtered.length > 0) {
            let html = '<table class="table" style="margin:0;"><thead><tr><th>Name</th><th>Course</th><th>Batch</th><th>Action</th></tr></thead><tbody>';
            filtered.forEach(s => {
                html += `<tr>
                    <td><strong>${s.name}</strong><br><small>${s.student_id || 'N/A'}</small></td>
                    <td>${s.course_name || '-'}</td>
                    <td>${s.batch_name || '-'}</td>
                    <td><button class="btn bt" style="padding:6px 12px;font-size:12px;" onclick="_selectStudent(${s.id}, '${s.name.replace(/'/g,"\\'")}', '${(s.course_name || '').replace(/'/g,"\\'")}', '${(s.batch_name || '').replace(/'/g,"\\'")}')"><i class="fa-solid fa-check"></i> Select</button></td>
                </tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('studentSearchResults').innerHTML = html;
        } else {
            document.getElementById('studentSearchResults').innerHTML = '<div style="padding:20px;text-align:center;color:#94a3b8;">No students found in selected course</div>';
        }
    } else if (searchInput.value) {
        _searchStudents(searchInput.value);
    }
}

function _selectStudent(id, name, course, batch) {
    selectedStudent = { id, name, course, batch };
    
    document.getElementById('selectedStudentSection').style.display = 'block';
    document.getElementById('selectedStudentName').textContent = name;
    document.getElementById('selectedStudentInfo').textContent = `${course} ${batch ? ' • ' + batch : ''}`;
    
    // Reset summary
    document.getElementById('sumTotalAssigned').textContent = 'Loading...';
    document.getElementById('sumTotalPaid').textContent = 'Loading...';
    document.getElementById('sumTotalBalance').textContent = 'Loading...';
    document.getElementById('paymentStudentId').value = id;
    document.getElementById('studentSearchResults').innerHTML = '';
    document.getElementById('studentSearchInput').value = '';
    
    // Load outstanding fees for this student
    _loadOutstandingFees(id);
}

function _clearSelectedStudent() {
    selectedStudent = null;
    outstandingFeesData = [];
    document.getElementById('selectedStudentSection').style.display = 'none';
    document.getElementById('paymentStudentId').value = '';
}

async function _loadOutstandingFees(studentId) {
    const container = document.getElementById('outstandingFeesList');
    container.innerHTML = '<div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading...</span></div>';
    
    try {
        const res = await fetch(APP_URL + '/api/admin/fees?action=get_outstanding&student_id=' + studentId);
        const result = await res.json();
        
        if (!result.success) {
            container.innerHTML = '<div style="padding:20px;text-align:center;color:#ef4444;">' + result.message + '</div>';
            return;
        }
        
        outstandingFeesData = result.data || [];
        const accountSummary = result.summary || {};
        
        if (outstandingFeesData.length === 0) {
            container.innerHTML = `<div style="padding:60px; text-align:center; color:#64748b;">
                <i class="fa-solid fa-circle-check" style="font-size:3rem; color:#10b981; margin-bottom:15px; diplay:block;"></i>
                <h3 style="margin:0; color:#1e293b;">Clear Account</h3>
                <p style="margin-top:5px;">This student has no outstanding fee records.</p>
            </div>`;
            return;
        }
        
        // Populate fee item dropdown
        const feeSelect = document.getElementById('paymentFeeItem');
        feeSelect.innerHTML = '<option value="">Select Fee</option>';
        
        const uniqueFees = {};
        outstandingFeesData.forEach(of => {
            if (!uniqueFees[of.fee_item_id]) {
                uniqueFees[of.fee_item_id] = of;
                feeSelect.innerHTML += `<option value="${of.fee_item_id}">${of.fee_item_name} (${of.fee_type})</option>`;
            }
        });
        
        // Render outstanding fees table
        let html = '<table class="table"><thead><tr><th>Fee Type</th><th>Inst.</th><th>Due Date</th><th>Fee Amt</th><th>Paid</th><th>Fine</th><th>Balance</th><th>Action</th></tr></thead><tbody>';
        
        outstandingFeesData.forEach(of => {
            const assigned = parseFloat(of.amount_due);
            const paid = parseFloat(of.amount_paid);
            const balance = assigned - paid;
            
            const isPaid = balance <= 0;
            const isOverdue = !isPaid && new Date(of.due_date) < new Date();
            
            html += `<tr>
                <td>${of.fee_item_name}</td>
                <td>${of.installment_no}</td>
                <td><span class="${isOverdue ? 'text-danger fw-bold' : ''}">${of.due_date}</span></td>
                <td>${assigned.toLocaleString()}</td>
                <td style="color:#10b981;">${paid.toLocaleString()}</td>
                <td id="fine_cell_${of.id}">-</td>
                <td><strong style="color:${balance > 0 ? '#ef4444' : 'inherit'}">${balance.toLocaleString()}</strong></td>
                <td><button class="btn bt" onclick="_quickSelectFee(${of.id})">Pay Now</button></td>
            </tr>`;
            
            if (isOverdue) _updateCalculatedFine(of.id);
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;

        // Update Summary with ACCURATE data from backend summary table
        const totalAssigned = parseFloat(accountSummary.total_fee || 0);
        const totalPaid = parseFloat(accountSummary.paid_amount || 0);
        const totalBalance = parseFloat(accountSummary.due_amount || 0);

        const paidPercent = totalAssigned > 0 ? Math.round((totalPaid / totalAssigned) * 100) : 0;
        const circle = document.getElementById('circlePaid');
        if (circle) {
            circle.style.setProperty('--p-val', (paidPercent * 3.6) + 'deg');
            circle.querySelector('span').textContent = paidPercent + '%';
        }

        document.getElementById('sumTotalAssigned').textContent = 'Assigned: NPR ' + totalAssigned.toLocaleString();
        document.getElementById('sumTotalPaid').textContent = 'NPR ' + totalPaid.toLocaleString();
        document.getElementById('sumTotalBalance').textContent = 'NPR ' + totalBalance.toLocaleString();
        
        // Next Due Logic
        const nextDue = outstandingFeesData[0]; // Already sorted by date in backend
        if (nextDue) {
            document.getElementById('nextDueAmount').textContent = 'NPR ' + (parseFloat(nextDue.amount_due) - parseFloat(nextDue.amount_paid)).toLocaleString();
            document.getElementById('nextDueDate').textContent = 'Date: ' + nextDue.due_date;
            
            // SMART PAY: Auto-select oldest
            _quickSelectFee(nextDue.id);
        }

        
    } catch(e) {
        container.innerHTML = '<div style="padding:20px;text-align:center;color:#ef4444;">Error loading fees</div>';
    }
}

async function _updateLiveFineFeedback(recordId) {
    const display = document.getElementById('liveFineDisplay');
    const inputDate = document.getElementById('paidDate').value;
    
    try {
        // We might need to pass the date to the API if it's not today
        const res = await fetch(`${APP_URL}/api/admin/fees?action=get_calculated_fine&fee_record_id=${recordId}&payment_date=${inputDate}`);
        const result = await res.json();
        if (result.success && result.data.fine > 0) {
            display.innerHTML = `<div class="live-fine-badge"><i class="fa-solid fa-triangle-exclamation"></i> Late Fine: NPR ${result.data.fine.toLocaleString()}</div>`;
        } else {
            display.innerHTML = '';
        }
    } catch(e) { display.innerHTML = ''; }
}

async function _updateCalculatedFine(recordId) {
    try {
        const res = await fetch(APP_URL + '/api/admin/fees?action=get_calculated_fine&fee_record_id=' + recordId);
        const result = await res.json();
        if (result.success && result.data.fine > 0) {
            const cell = document.getElementById('fine_cell_' + recordId);
            if (cell) cell.innerHTML = `<span style="color:red;font-weight:600">${result.data.fine.toLocaleString()}</span>`;
        }
    } catch(e) { console.error(e); }
}

function _quickSelectFee(recordId) {
    const fr = outstandingFeesData.find(of => of.id === recordId);
    if (!fr) return;
    
    const feeSelect = document.getElementById('paymentFeeItem');
    const instSelect = document.getElementById('paymentInstallment');
    
    feeSelect.value = fr.fee_item_id;
    // Trigger change manually
    feeSelect.dispatchEvent(new Event('change'));
    
    // Wait for installment select to populate
    setTimeout(() => {
        instSelect.value = fr.installment_no;
        instSelect.dispatchEvent(new Event('change'));
        // Scroll to form
        document.getElementById('paymentFormSection').scrollIntoView({ behavior: 'smooth' });
    }, 100);
}

async function _loadRecentPayments() {
    const container = document.getElementById('recentPaymentsList');
    if (!container) return; // Optional element in dom

    try {
        const res = await fetch(APP_URL + '/api/admin/fees?action=get_recent_payments');
        const result = await res.json();

        if (!result.success || !result.data || result.data.length === 0) {
            container.innerHTML = '<div style="padding:20px;text-align:center;color:#94a3b8;">No recent payments</div>';
            return;
        }

        let html = '<table class="table"><thead><tr><th>Date</th><th>Student</th><th>Receipt</th><th>Amount</th><th>Method</th><th>Actions</th></tr></thead><tbody>';
        
        result.data.forEach(t => {
            html += `<tr>
                <td>${t.paid_date}</td>
                <td>${t.student_name}</td>
                <td><strong>${t.receipt_no}</strong></td>
                <td><strong>${parseFloat(t.amount_paid).toLocaleString()}</strong></td>
                <td><span class="tag bg-s">${t.payment_mode.toUpperCase()}</span></td>
                <td>
                    <button class="btn bs" style="padding:4px 8px;font-size:12px;" onclick="window.open('/payment_flow/generate_pdf.php?receipt_no=${t.receipt_no}', '_blank')" title="Download PDF"><i class="fa-solid fa-file-pdf"></i></button>
                    <button class="btn bs" style="padding:4px 8px;font-size:12px;" onclick="viewPayment(${t.id})" title="View"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn bs" style="padding:4px 8px;font-size:12px;" onclick="editPayment(${t.id})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn bs" style="padding:4px 8px;font-size:12px;color:var(--red);" onclick="deletePayment(${t.id})" title="Delete"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>`;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
        
    } catch(e) {
        container.innerHTML = '<div style="padding:20px;text-align:center;color:#ef4444;">Error loading recent payments</div>';
    }
}


// Student Ledger View
window.renderStudentLedger = async function(studentId) {
    const mc = document.getElementById('mainContent');
    mc.innerHTML = `<div class="pg fu">
        <div class="bc"><a href="#" onclick="goNav('overview')">Dashboard</a> <span class="bc-sep">&rsaquo;</span> <span class="bc-cur">Student Ledger</span></div>
        <div class="pg-head">
            <div class="pg-left"><div class="pg-ico"><i class="fa-solid fa-book"></i></div><div><div class="pg-title" id="ledgerTitle">Student Ledger</div><div class="pg-sub">Fee history and payment transactions</div></div></div>
            <div class="pg-acts">
                <div class="btn-group" style="margin-right:15px; background:#f1f5f9; padding:4px; border-radius:8px;">
                    <button class="btn btn-sm" id="btnLedgerTable" onclick="_switchLedgerView('table')" style="background:var(--blue); color:#fff; border-radius:6px;"><i class="fa-solid fa-table-list"></i> Table</button>
                    <button class="btn btn-sm" id="btnLedgerTimeline" onclick="_switchLedgerView('timeline')" style="background:transparent; color:#64748b; border-radius:6px;"><i class="fa-solid fa-timeline"></i> Timeline</button>
                </div>
                <button class="btn bs" onclick="_printLedger()"><i class="fa-solid fa-print"></i> Print</button>
            </div>
        </div>
        <div id="ledgerContent"><div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading ledger...</span></div></div>
    </div>`;
    
    try {
        const res = await fetch(`${APP_URL}/api/admin/fees?action=get_student_ledger&student_id=${studentId}`);
        const result = await res.json();
        if (!result.success) throw new Error(result.message);
        
        _renderLedgerUI(result.data);
    } catch(e) {
        document.getElementById('ledgerContent').innerHTML = `<div style="padding:20px;color:red">${e.message}</div>`;
    }
};

function _renderLedgerUI(data) {
    const c = document.getElementById('ledgerContent');
    const { ledger, transactions, balance } = data;
    
    let summaryHtml = `
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:25px;">
        <div class="card glass-card" style="padding:20px;border-left:4px solid var(--blue);">
            <div style="color:#64748b;font-size:0.9rem;font-weight:600;">Total Due</div>
            <div style="font-size:1.5rem;font-weight:800;">NPR ${parseFloat(balance.total_due).toLocaleString()}</div>
        </div>
        <div class="card glass-card" style="padding:20px;border-left:4px solid var(--green);">
            <div style="color:#64748b;font-size:0.9rem;font-weight:600;">Total Paid</div>
            <div style="font-size:1.5rem;font-weight:800;color:var(--green);">NPR ${parseFloat(balance.total_paid).toLocaleString()}</div>
        </div>
        <div class="card glass-card" style="padding:20px;border-left:4px solid var(--red);">
            <div style="color:#64748b;font-size:0.9rem;font-weight:600;">Balance Payable</div>
            <div style="font-size:1.5rem;font-weight:800;color:var(--red);">NPR ${parseFloat(balance.balance).toLocaleString()}</div>
        </div>
    </div>`;

    c.innerHTML = summaryHtml + `
        <div id="ledgerTableView">
            <div class="card mb" style="padding:25px;">
                <h4 class="mb"><i class="fa-solid fa-list"></i> Fee Records</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr><th>Date</th><th>Fee Item</th><th>Inst.</th><th>Due</th><th>Paid</th><th>Fine</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            ${ledger.map(l => `
                                <tr>
                                    <td>${l.due_date}</td>
                                    <td>${l.fee_item_name}</td>
                                    <td>${l.installment_no}</td>
                                    <td>${parseFloat(l.amount_due).toLocaleString()}</td>
                                    <td>${parseFloat(l.amount_paid).toLocaleString()}</td>
                                    <td style="color:red">${l.fine_applied > 0 ? l.fine_applied : '-'}</td>
                                    <td><span class="tag bg-${l.status === 'paid' ? 't' : (l.status === 'overdue' ? 'r' : 'y')}">${l.status.toUpperCase()}</span></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card" style="padding:25px;">
                <h4 class="mb"><i class="fa-solid fa-receipt"></i> Payment History</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr><th>Date</th><th>Receipt No.</th><th>Method</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            ${transactions.map(t => `
                                <tr>
                                    <td>${t.payment_date}</td>
                                    <td><strong>${t.receipt_number}</strong></td>
                                    <td><span class="tag bg-s">${t.payment_method.toUpperCase()}</span></td>
                                    <td><strong>${parseFloat(t.amount).toLocaleString()}</strong></td>
                                    <td><span class="tag bg-t">COMPLETED</span></td>
                                    <td>
                                        <button class="btn bs" style="padding:4px 8px;font-size:12px;" onclick="window.open(APP_URL + '/api/admin/fees?action=get_payment_details&transaction_id=${t.id}', '_blank')" title="Download PDF"><i class="fa-solid fa-file-pdf"></i></button>
                                        <button class="btn bs" style="padding:4px 8px;font-size:12px;" onclick="viewPayment(${t.id})" title="View"><i class="fa-solid fa-eye"></i></button>
                                        <button class="btn bs" style="padding:4px 8px;font-size:12px;" onclick="editPayment(${t.id})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                        <button class="btn bs" style="padding:4px 8px;font-size:12px;color:var(--red);" onclick="deletePayment(${t.id})" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="ledgerTimelineView" style="display:none">
            <div class="payment-timeline">
                ${ledger.concat(transactions.map(t => ({...t, is_payment: true}))).sort((a,b) => new Date(b.payment_date || b.due_date) - new Date(a.payment_date || a.due_date)).map(item => {
                    if (item.is_payment) {
                        return `
                            <div class="timeline-item paid">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-date">${item.payment_date}</div>
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                        <div>
                                            <h4 style="margin:0; color:#16a34a;">Payment Received</h4>
                                            <p style="margin:4px 0 0 0; font-size:0.9rem; color:#64748b;">Receipt: ${item.receipt_number} • Via ${item.payment_method.toUpperCase()}</p>
                                        </div>
                                        <div style="text-align:right">
                                            <div style="font-size:1.1rem; font-weight:800; color:#16a34a;">+ NPR ${parseFloat(item.amount).toLocaleString()}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        return `
                            <div class="timeline-item ${item.status}">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-date">${item.due_date}</div>
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                        <div>
                                            <h4 style="margin:0; color:var(--text-dark);">${item.fee_item_name}</h4>
                                            <p style="margin:4px 0 0 0; font-size:0.9rem; color:#64748b;">Installment ${item.installment_no}</p>
                                        </div>
                                        <div style="text-align:right">
                                            <div style="font-size:1.1rem; font-weight:800; color:var(--text-dark);">NPR ${parseFloat(item.amount_due).toLocaleString()}</div>
                                            <span class="tag bg-${item.status === 'paid' ? 't' : (item.status === 'overdue' ? 'r' : 'y')}" style="margin-top:5px; display:inline-block;">${item.status.toUpperCase()}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                }).join('')}
            </div>
        </div>
    `;
}

window._switchLedgerView = function(view) {
    const table = document.getElementById('ledgerTableView');
    const timeline = document.getElementById('ledgerTimelineView');
    const btnTable = document.getElementById('btnLedgerTable');
    const btnTimeline = document.getElementById('btnLedgerTimeline');

    if (view === 'timeline') {
        table.style.display = 'none';
        timeline.style.display = 'block';
        btnTimeline.style.background = 'var(--blue)';
        btnTimeline.style.color = '#fff';
        btnTable.style.background = 'transparent';
        btnTable.style.color = '#64748b';
    } else {
        table.style.display = 'block';
        timeline.style.display = 'none';
        btnTable.style.background = 'var(--blue)';
        btnTable.style.color = '#fff';
        btnTimeline.style.background = 'transparent';
        btnTimeline.style.color = '#64748b';
    }
};



/* ══════════════ VIEW / EDIT / DELETE PAYMENTS ═══════════════════════════════ */
window.viewPayment = async function(transactionId) {
    try {
        const res = await fetch(APP_URL + '/api/admin/fees?action=get_payment_details&transaction_id=' + transactionId);
        const result = await res.json();
        
        if (!result.success) throw new Error(result.message);
        
        const txn = result.data.transaction;
        
        const modalHtml = `
        <div class="modal-overlay" onclick="if(event.target===this)closeModal('viewPaymentModal')">
            <div class="modal" style="max-width:650px">
                <div class="modal-header">
                    <h3><i class="fa-solid fa-eye"></i> View Payment Details</h3>
                    <button class="modal-close" onclick="closeModal('viewPaymentModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                        <div>
                            <p style="margin:0 0 5px 0;color:#64748b;">Receipt Number</p>
                            <h4 style="margin:0;">${txn.receipt_number}</h4>
                        </div>
                        <div>
                            <p style="margin:0 0 5px 0;color:#64748b;">Payment Date</p>
                            <h4 style="margin:0;">${txn.payment_date}</h4>
                        </div>
                    </div>
                    
                    <table class="table" style="margin-bottom:20px;">
                        <tbody>
                            <tr>
                                <td><strong>Student Name</strong></td>
                                <td>${txn.student_name}</td>
                            </tr>
                            <tr>
                                <td><strong>Class / Batch</strong></td>
                                <td>${txn.course_name} / ${txn.batch_name}</td>
                            </tr>
                            <tr>
                                <td><strong>Fee Type</strong></td>
                                <td>${txn.fee_item_name}</td>
                            </tr>
                            <tr>
                                <td><strong>Amount Paid</strong></td>
                                <td><strong>NPR ${parseFloat(txn.amount).toLocaleString()}</strong></td>
                            </tr>
                            <tr>
                                <td><strong>Payment Method</strong></td>
                                <td><span class="tag bg-s">${txn.payment_method.toUpperCase()}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Notes</strong></td>
                                <td>${txn.notes || 'N/A'}</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div style="display:flex;gap:15px;justify-content:center;margin-top:20px;">
                        <a href="${APP_URL}/api/admin/fees?action=generate_receipt_html&transaction_id=${transactionId}" target="_blank" class="btn bt"><i class="fa-solid fa-file-pdf"></i> View / Download Receipt</a>
                        ${result.data.image_url ? `<a href="${result.data.image_url}" target="_blank" class="btn bs"><i class="fa-solid fa-image"></i> View Uploaded Bill</a>` : ''}
                    </div>
                </div>
            </div>
        </div>`;
        
        _showModal('viewPaymentModal', modalHtml);
        
    } catch(err) {
        alert(err.message);
    }
};

window.editPayment = async function(transactionId) {
    try {
        const res = await fetch(APP_URL + '/api/admin/fees?action=get_payment_details&transaction_id=' + transactionId);
        const result = await res.json();
        
        if (!result.success) throw new Error(result.message);
        const txn = result.data.transaction;
        
        const modalHtml = `
        <div class="modal-overlay" onclick="if(event.target===this)closeModal('editPaymentModal')">
            <div class="modal" style="max-width:550px">
                <div class="modal-header">
                    <h3><i class="fa-solid fa-pen"></i> Edit Payment Record</h3>
                    <button class="modal-close" onclick="closeModal('editPaymentModal')">&times;</button>
                </div>
                <form id="editPaymentForm">
                    <input type="hidden" name="transaction_id" value="${txn.id}">
                    <div class="modal-body">
                        <div class="mb" style="background:#f8fafc;padding:15px;border-radius:8px;">
                            <p style="margin:0 0 5px 0;"><strong>Student:</strong> ${txn.student_name} (${txn.course_name})</p>
                            <p style="margin:0;"><strong>Fee Area:</strong> ${txn.fee_item_name}</p>
                            <p style="margin:4px 0 0 0;font-size:0.85rem;color:#b91c1c;"><i class="fa-solid fa-circle-info"></i> Modifying amounts will automatically recalculate student ledger balances and rewrite the PDF receipt.</p>
                        </div>
                    
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;">
                            <div class="form-group">
                                <label class="form-label">Payment Date</label>
                                <input type="date" name="paid_date" class="form-control" value="${txn.payment_date}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Amount Paid</label>
                                <input type="number" name="amount_paid" class="form-control" value="${txn.amount}" required min="1" step="0.01">
                            </div>
                        </div>

                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;">
                            <div class="form-group">
                                <label class="form-label">Payment Mode</label>
                                <select name="payment_mode" class="form-control" required>
                                    <option value="cash" ${txn.payment_method==='cash'?'selected':''}>Cash</option>
                                    <option value="bank_transfer" ${txn.payment_method==='bank_transfer'?'selected':''}>Bank Transfer</option>
                                    <option value="cheque" ${txn.payment_method==='cheque'?'selected':''}>Cheque</option>
                                    <option value="esewa" ${txn.payment_method==='esewa'?'selected':''}>eSewa</option>
                                    <option value="khalti" ${txn.payment_method==='khalti'?'selected':''}>Khalti</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Replace Bill/Receipt (Optional)</label>
                                <input type="file" name="receipt_image" accept="image/*,.pdf" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">${txn.notes || ''}</textarea>
                        </div>

                        <div class="form-group" style="padding:10px;border:1px solid #e2e8f0;border-radius:6px;background:#f8fafc;">
                            <label class="form-check" style="margin:0;">
                                <input type="checkbox" name="resend_email" value="1">
                                <span class="form-check-label">Resend Updated Email Receipt to Student</span>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bs" onclick="closeModal('editPaymentModal')">Cancel</button>
                        <button type="submit" class="btn bt">Save Updates</button>
                    </div>
                </form>
            </div>
        </div>`;
        
        _showModal('editPaymentModal', modalHtml);
        
        document.getElementById('editPaymentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;

            const formData = new FormData(this);
            formData.append('action', 'edit_payment');

            try {
                const saveRes = await fetch(APP_URL + '/api/admin/fees', { method: 'POST', body: formData });
                const saveResult = await saveRes.json();
                
                if (saveResult.success) {
                    _showToast(saveResult.message);
                    closeModal('editPaymentModal');
                    if(document.getElementById('paymentStudentId') && document.getElementById('paymentStudentId').value) {
                        await renderStudentLedger(document.getElementById('paymentStudentId').value);
                    }
                    if(document.getElementById('recentPaymentsList')) {
                        await _loadRecentPayments();
                    }
                } else {
                    alert(saveResult.message || 'Update failed');
                }
            } catch(err) {
                alert('Update Error');
            } finally {
                btn.innerHTML = 'Save Updates';
                btn.disabled = false;
            }
        });

    } catch(err) {
        alert(err.message);
    }
};

window.deletePayment = async function(transactionId) {
    if(!confirm("Are you sure you want to completely delete this payment? The ledger balances will actively deduct this amount.")) return;
    try {
        const formData = new FormData();
        formData.append('action', 'delete_payment');
        formData.append('transaction_id', transactionId);

        const res = await fetch(APP_URL + '/api/admin/fees', {
            method: 'POST',
            body: formData
        });
        
        const result = await res.json();
        if(result.success) {
            _showToast(result.message);
            if(document.getElementById('paymentStudentId') && document.getElementById('paymentStudentId').value) {
                await renderStudentLedger(document.getElementById('paymentStudentId').value);
            }
            if(document.getElementById('recentPaymentsList')) {
                await _loadRecentPayments();
            }
        } else {
            alert(result.message);
        }
    } catch(err) {
        alert("Failed to delete payment.");
    }
};

/* ══════════════ FEE OUTSTANDING ═══════════════════════════════ */
window.renderFeeOutstanding = async function() {
    const mc = document.getElementById('mainContent');
    
    // Inject Premium CSS
    if (!document.getElementById('ia-fees-premium-css')) {
        const link = document.createElement('link');
        link.id = 'ia-fees-premium-css';
        link.rel = 'stylesheet';
        link.href = window.APP_URL + '/public/assets/css/ia-fees-premium.css';
        document.head.appendChild(link);
    }

    mc.innerHTML = `<div class="pg fu">
        <div class="bc">
            <a href="#" onclick="goNav('overview')"><i class="fa-solid fa-house"></i></a> 
            <span class="bc-sep">/</span> 
            <a href="#" onclick="goNav('fee')">Fee Management</a> 
            <span class="bc-sep">/</span> 
            <span class="bc-cur">Outstanding Dues</span>
        </div>
        
        <div class="pg-head">
            <div class="pg-left">
                <div class="pg-ico"><i class="fa-solid fa-clock"></i></div>
                <div>
                    <div class="pg-title">Outstanding Dues</div>
                    <div class="pg-sub">Real-time tracking of pending student fees</div>
                </div>
            </div>
            <div class="pg-acts">
                <button class="btn bs" onclick="_loadOutstandingData()"><i class="fa-solid fa-arrows-rotate"></i> Sync Data</button>
            </div>
        </div>
        
        <!-- Premium Glassmorphism Stats -->
        <div class="due-stats-grid" id="outstandingSummaryCards">
            <div class="due-stat-card blue">
                <div class="due-stat-header">
                    <div class="due-stat-label">Students with Dues</div>
                    <div class="due-stat-icon blue"><i class="fa-solid fa-users"></i></div>
                </div>
                <div class="due-stat-value" id="iaTotalStudents">0</div>
            </div>
            <div class="due-stat-card orange">
                <div class="due-stat-header">
                    <div class="due-stat-label">Total Outstanding</div>
                    <div class="due-stat-icon orange"><i class="fa-solid fa-money-bill-wave"></i></div>
                </div>
                <div class="due-stat-value" id="iaTotalOutstanding">NPR 0</div>
            </div>
            <div class="due-stat-card red">
                <div class="due-stat-header">
                    <div class="due-stat-label">Pending Records</div>
                    <div class="due-stat-icon red"><i class="fa-solid fa-list-check"></i></div>
                </div>
                <div class="due-stat-value" id="iaPendingItems">0</div>
            </div>
            <div class="due-stat-card teal">
                <div class="due-stat-header">
                    <div class="due-stat-label">Collection Rate</div>
                    <div class="due-stat-icon teal"><i class="fa-solid fa-chart-pie"></i></div>
                </div>
                <div class="due-stat-value" id="iaCollectionRate">0%</div>
            </div>
        </div>
        
        <div class="premium-filter-bar" style="margin-bottom:20px;">
            <div class="premium-search-input">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="outstandingSearchInput" placeholder="Search by student name or record..." oninput="_filterOutstanding()">
            </div>
            <div class="filter-group" style="display:flex; gap:10px;">
                <select id="outstandingCourseFilter" class="form-control" style="width:180px; border-radius:12px;" onchange="_filterOutstanding()">
                    <option value="">All Courses</option>
                </select>
                <select id="outstandingStatusFilter" class="form-control" style="width:150px; border-radius:12px;" onchange="_filterOutstanding()">
                    <option value="">All Status</option>
                    <option value="overdue">Overdue</option>
                    <option value="pending">Upcoming</option>
                </select>
            </div>
        </div>

        <div class="premium-due-table-container" id="outstandingListContainer">
            <div class="pg-loading" style="padding:60px; text-align:center;">
                <i class="fa-solid fa-circle-notch fa-spin"></i><span> Loading outstanding data...</span>
            </div>
        </div>
    </div>`;
    
    await _loadOutstandingData();
};

let outstandingAllData = [];

async function _loadOutstandingData() {
    const c = document.getElementById('outstandingListContainer');
    const courseFilter = document.getElementById('outstandingCourseFilter');
    
    try {
        const res = await fetch(APP_URL + '/api/admin/fees?action=get_outstanding');
        const result = await res.json();
        
        if (!result.success) {
            c.innerHTML = '<div style="padding:40px;text-align:center;color:#ef4444;">' + result.message + '</div>';
            return;
        }
        
        outstandingAllData = result.data || [];
        
        // Accurate Summary Statistics
        const totalStudents = outstandingAllData.length;
        const totalDue = outstandingAllData.reduce((sum, d) => sum + parseFloat(d.total_due || 0), 0);
        const totalPaid = outstandingAllData.reduce((sum, d) => sum + parseFloat(d.total_paid || 0), 0);
        const totalOutstanding = totalDue - totalPaid;
        const totalPendingItems = outstandingAllData.reduce((sum, d) => sum + parseInt(d.outstanding_count || 0), 0);
        const collectionRate = totalDue > 0 ? Math.round((totalPaid / totalDue) * 100) : 0;
        
        document.getElementById('iaTotalStudents').textContent = totalStudents;
        document.getElementById('iaTotalOutstanding').textContent = 'NPR ' + totalOutstanding.toLocaleString();
        document.getElementById('iaPendingItems').textContent = totalPendingItems;
        document.getElementById('iaCollectionRate').textContent = collectionRate + '%';
        
        // Populate course filter
        const currentCourseVal = courseFilter.value;
        courseFilter.innerHTML = '<option value="">All Courses</option>';
        const courses = [...new Set(outstandingAllData.map(d => d.course_id).filter(Boolean))];
        courses.forEach(cid => {
            const cname = outstandingAllData.find(d => d.course_id === cid)?.course_name;
            if (cname) courseFilter.innerHTML += `<option value="${cid}">${cname}</option>`;
        });
        courseFilter.value = currentCourseVal;
        
        _renderOutstanding(outstandingAllData);
    } catch(e) {
        console.error('Error loading outstanding data:', e);
        c.innerHTML = '<div style="padding:40px;text-align:center;color:#ef4444;"><i class="fa-solid fa-triangle-exclamation"></i> Error loading data</div>';
    }
}

function _filterOutstanding() {
    const search = document.getElementById('outstandingSearchInput')?.value?.toLowerCase() || '';
    const courseFilter = document.getElementById('outstandingCourseFilter')?.value || '';
    const statusFilter = document.getElementById('outstandingStatusFilter')?.value || '';
    const today = new Date().toISOString().split('T')[0];
    
    const filtered = outstandingAllData.filter(d => {
        const matchSearch = !search || (d.student_name && d.student_name.toLowerCase().includes(search));
        const matchCourse = !courseFilter || String(d.course_id) === courseFilter;
        
        let matchStatus = true;
        if (statusFilter === 'overdue') {
            matchStatus = d.next_due_date && d.next_due_date < today;
        } else if (statusFilter === 'pending') {
            matchStatus = !d.next_due_date || d.next_due_date >= today;
        }
        
        return matchSearch && matchCourse && matchStatus;
    });
    
    _renderOutstanding(filtered);
}

function _renderOutstanding(data) {
    const c = document.getElementById('outstandingListContainer');
    const today = new Date().toISOString().split('T')[0];
    
    if (!data.length) {
        c.innerHTML = `<div style="padding:80px; text-align:center; color:#64748b;">
            <i class="fa-solid fa-magnifying-glass" style="font-size:3rem; margin-bottom:15px; opacity:0.3;"></i>
            <p>No students match your outstanding dues filter.</p>
        </div>`;
        return;
    }
    
    const getInitials = (n) => (n || 'S').split(' ').slice(0,2).map(w=>w[0]).join('').toUpperCase();
    const getAvatarColor = (id) => ['av-teal','av-blue','av-purple','av-amber','av-red'][(id || 0) % 5];

    let html = `<table class="premium-due-table" style="width:100%; border-collapse:collapse;">
        <thead>
            <tr>
                <th>STUDENT</th>
                <th>PENDING ITEMS</th>
                <th>FINANCIAL PROGRESS</th>
                <th>BALANCE</th>
                <th>NEXT DUEDATE</th>
                <th style="text-align:right;">ACTION</th>
            </tr>
        </thead>
        <tbody>`;
    
    data.forEach(d => {
        const balance = parseFloat(d.total_due || 0) - parseFloat(d.total_paid || 0);
        const progress = d.total_due > 0 ? Math.round((parseFloat(d.total_paid) / parseFloat(d.total_due)) * 100) : 0;
        const isOverdue = d.next_due_date && d.next_due_date < today;

        html += `<tr>
            <td>
                <div class="due-s-card">
                    <div class="due-s-av ${getAvatarColor(d.student_id)}">${getInitials(d.student_name)}</div>
                    <div>
                        <div class="due-s-name">${d.student_name}</div>
                        <div class="due-s-course">${d.course_name || 'N/A'}</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="due-count-badge">${d.outstanding_count} Items</span>
            </td>
            <td style="width:200px;">
                <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-weight:700; color:#64748b; margin-bottom:4px;">
                    <span>Paid: ${progress}%</span>
                    <span>NPR ${parseFloat(d.total_paid).toLocaleString()}</span>
                </div>
                <div class="due-fin-progress">
                    <div class="due-fin-bar" style="width:${progress}%"></div>
                </div>
            </td>
            <td>
                <strong style="color:#ef4444; font-size:1rem;">NPR ${balance.toLocaleString()}</strong>
            </td>
            <td>
                <div class="due-date-badge ${isOverdue ? 'overdue' : ''}">
                    <i class="fa-solid ${isOverdue ? 'fa-triangle-exclamation' : 'fa-calendar-day'}"></i>
                    ${d.next_due_date || 'N/A'}
                </div>
            </td>
            <td style="text-align:right;">
                <button class="btn bt" style="border-radius:10px; padding:10px 18px;" onclick="goNav('fee','record',{student_id:${d.student_id}})">
                    <i class="fa-solid fa-hand-holding-dollar"></i> Collect
                </button>
            </td>
        </tr>`;
    });
    
    html += '</tbody></table>';
    c.innerHTML = html;
}

async function _selectStudentForPayment(studentId) {
    if (!studentId) return;
    // The renderFeeRecord handles sid from URL, but for internal nav we can call this
    await _autoSelectStudent(studentId);
}

window.renderFeeReports = async function() {
    const mc = document.getElementById('mainContent');
    mc.innerHTML = '<div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading Reports Dashboard...</span></div>';
    
    try {
        const res = await fetch(APP_URL + '/dash/admin/report-fees?spa=true');
        if (!res.ok) throw new Error('Failed to load dashboard');
        const html = await res.text();
        mc.innerHTML = html;
        
        // Browsers do not execute script tags injected via innerHTML. 
        // We must extract them and append them to the document.
        const scripts = mc.querySelectorAll('script');
        for (let i = 0; i < scripts.length; i++) {
            const oldScript = scripts[i];
            const newScript = document.createElement('script');
            // copy all attributes
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            // copy the script text
            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            // replace original with new (forces execution)
            oldScript.parentNode.replaceChild(newScript, oldScript);
        }
        
        // Setup initial scripts and data bindings expected by the report dashboard
        if (typeof filterBatches === 'function') filterBatches();
        if (typeof toggleReportFilters === 'function') toggleReportFilters();
        if (typeof loadFeeReportData === 'function') loadFeeReportData();
        
    } catch(err) {
        mc.innerHTML = '<div style="padding:40px;text-align:center;color:#ef4444;"><i class="fa-solid fa-triangle-exclamation"></i> Error loading Fee Reports module.</div>';
    }
};
