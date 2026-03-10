// Teacher Payment/Salary Module (ta-payment.js)
async function renderSalarySlips() {
    mainContent.innerHTML = `
        <div class="pg fu">
            <div class="pg-head" style="margin-bottom:20px;">
                <div class="pg-title">Salary Slips</div>
                <div style="font-size:12px; color:var(--text-body); margin-top:4px;">View and download your monthly salary slips</div>
            </div>
            
            <div id="taSalaryContent" style="margin-top:20px;">
                <div style="text-align:center; padding:40px;">
                    <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
                    <p>Loading Salary Information...</p>
                </div>
            </div>
        </div>
    `;

    try {
        const res = await fetch(`${APP_URL}/api/teacher/payments`);
        const json = await res.json();
        
        if (!json.success) {
            document.getElementById('taSalaryContent').innerHTML = `<div class="alert alert-danger">${json.message}</div>`;
            return;
        }

        const data = json.data || [];
        
        let html = '';
        if (data.length === 0) {
            html = '<div class="alert alert-info">No salary slips available at the moment.</div>';
        } else {
            html += `
                <div class="card p-0">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Month/Year</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.forEach(slip => {
                let statusBadge = slip.status === 'paid' ? `<div class="bdg bg-green">Paid</div>` : `<div class="bdg bg-amber">Pending</div>`;
                const monthName = new Date(slip.year, slip.month - 1).toLocaleString('default', { month: 'long' });
                
                html += `
                    <tr>
                        <td style="font-weight:600;">${monthName} ${slip.year}</td>
                        <td style="font-family:monospace; font-size:14px; color:var(--text-dark);">Rs. ${slip.amount}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-sm" onclick="taDownloadSlip(${slip.id})">
                                <i class="fa-solid fa-download"></i> Download
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
        }

        document.getElementById('taSalaryContent').innerHTML = html;
        
    } catch (error) {
        console.error('Salary Load Error:', error);
        document.getElementById('taSalaryContent').innerHTML = `<div class="alert alert-danger">Failed to load salary slips.</div>`;
    }
}

function taDownloadSlip(id) {
    alert("Downloading slip ID: " + id);
    // In a real app, this would redirect to a PDF generation endpoint
    // window.open(`${APP_URL}/api/teacher/payments/download?id=${id}`, '_blank');
}
