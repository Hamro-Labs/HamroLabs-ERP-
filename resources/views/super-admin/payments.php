<?php
require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pageTitle = 'Payment History';
$activePage = 'payments.php';
?>

<!-- Sidebar -->
<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<!-- Main Content -->
<main class="main">
<div class="page">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
      <div style="font-size:11px;color:var(--text-light);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Revenue Analytics</div>
      <h1 style="font-size:22px;font-weight:800;">Payment History</h1>
      <p style="font-size:13px;color:var(--text-body);margin-top:4px;">All subscription payments received from institutes</p>
    </div>
    <div style="display:flex;gap:10px;">
      <button class="btn bs" onclick="exportCSV()"><i class="fa fa-download"></i> Export CSV</button>
      <button class="btn bt" onclick="recordPayment()"><i class="fa fa-plus"></i> Record Payment</button>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="stat-grid" style="margin-bottom:24px;">
    <div class="card stat-card">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;"><div style="width:38px;height:38px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#16a34a;font-size:16px;"><i class="fa fa-check-circle"></i></div><span style="font-size:12px;color:var(--text-body);font-weight:600;">Total Received (This Month)</span></div>
      <div class="stat-val" style="color:#16a34a;">NPR 4,82,000</div>
      <div style="font-size:12px;color:var(--text-light);margin-top:6px;">↑ 12% vs last month</div>
    </div>
    <div class="card stat-card">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;"><div style="width:38px;height:38px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#3b82f6;font-size:16px;"><i class="fa fa-clock"></i></div><span style="font-size:12px;color:var(--text-body);font-weight:600;">Pending Payments</span></div>
      <div class="stat-val" style="color:#3b82f6;">NPR 74,500</div>
      <div style="font-size:12px;color:var(--text-light);margin-top:6px;">8 institutes overdue</div>
    </div>
    <div class="card stat-card">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;"><div style="width:38px;height:38px;background:#fef2f2;border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--red);font-size:16px;"><i class="fa fa-rotate-left"></i></div><span style="font-size:12px;color:var(--text-body);font-weight:600;">Refunds / Chargebacks</span></div>
      <div class="stat-val" style="color:var(--red);">NPR 8,999</div>
      <div style="font-size:12px;color:var(--text-light);margin-top:6px;">2 refunds this month</div>
    </div>
    <div class="card stat-card">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;"><div style="width:38px;height:38px;background:#fef3c7;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#d97706;font-size:16px;"><i class="fa fa-wallet"></i></div><span style="font-size:12px;color:var(--text-body);font-weight:600;">Annual Revenue (YTD)</span></div>
      <div class="stat-val" style="color:#d97706;">NPR 38.4L</div>
      <div style="font-size:12px;color:var(--text-light);margin-top:6px;">67% of ARR target</div>
    </div>
  </div>

  <!-- Filters -->
  <div class="tbl-wrap">
    <div class="tbl-head">
      <div class="tbl-title"><i class="fa fa-money-bill-wave"></i> Payment Transactions</div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <div style="position:relative;"><i class="fa fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-light);font-size:12px;"></i><input type="text" class="search-inp" placeholder="Search institute or ref..." style="width:200px;" oninput="filterPayments(this.value)"></div>
        <select class="filter-sel" onchange="filterStatus(this.value)"><option value="">All Status</option><option>Paid</option><option>Pending</option><option>Failed</option><option>Refunded</option></select>
        <select class="filter-sel"><option value="">All Plans</option><option>Starter</option><option>Growth</option><option>Professional</option><option>Enterprise</option></select>
        <input type="month" class="filter-sel" value="2025-07">
      </div>
    </div>
    <table>
      <thead>
        <tr style="background:#f8fafc;">
          <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Ref No.</th>
          <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Institute</th>
          <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Plan</th>
          <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Period</th>
          <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Amount</th>
          <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Method</th>
          <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Status</th>
          <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Date</th>
          <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Actions</th>
        </tr>
      </thead>
      <tbody id="payTbody"></tbody>
    </table>
    <div class="pagination"><span class="pg-info">Showing 1–15 of 142 records</span><div class="pg-btns"><button class="pg-btn"><i class="fa fa-chevron-left"></i></button><button class="pg-btn active">1</button><button class="pg-btn">2</button><button class="pg-btn">3</button><button class="pg-btn"><i class="fa fa-chevron-right"></i></button></div></div>
  </div>
</div>
</main>

<script>
const payments = [
  { ref:"PAY-2025-0142", inst:"Loksewa Pathshala", plan:"growth", period:"Jul 2025", amount:3499, method:"Bank Transfer", status:"paid", date:"2025-07-01" },
  { ref:"PAY-2025-0141", inst:"Nayab Subba Academy", plan:"professional", period:"Jul 2025", amount:6999, method:"eSewa", status:"paid", date:"2025-07-01" },
  { ref:"PAY-2025-0140", inst:"Kharidar Study Hub", plan:"enterprise", period:"Jul 2025", amount:14999, method:"Bank Transfer", status:"paid", date:"2025-07-02" },
  { ref:"PAY-2025-0139", inst:"PSC Coaching Center", plan:"starter", period:"Jul 2025", amount:1499, method:"Cash", status:"pending", date:"2025-07-03" },
  { ref:"PAY-2025-0138", inst:"Bagmati Coaching", plan:"starter", period:"Jul 2025", amount:1499, method:"Bank Transfer", status:"paid", date:"2025-07-04" },
  { ref:"PAY-2025-0137", inst:"Gandaki PSC Institute", plan:"growth", period:"Jul 2025", amount:3499, method:"Khalti", status:"paid", date:"2025-07-05" },
  { ref:"PAY-2025-0136", inst:"Section Officer Prep", plan:"professional", period:"Jul 2025", amount:6999, method:"Bank Transfer", status:"pending", date:"2025-07-06" },
  { ref:"PAY-2025-0135", inst:"Nepal Bank Coaching", plan:"growth", period:"Jun 2025", amount:3499, method:"eSewa", status:"refunded", date:"2025-06-30" },
  { ref:"PAY-2025-0134", inst:"TSC Teachers Hub", plan:"enterprise", period:"Jul 2025", amount:14999, method:"Bank Transfer", status:"paid", date:"2025-07-01" },
  { ref:"PAY-2025-0133", inst:"Staff Nurse Academy", plan:"starter", period:"Jul 2025", amount:1499, method:"Cash", status:"failed", date:"2025-07-07" },
  { ref:"PAY-2025-0132", inst:"Kharidar Study Hub", plan:"enterprise", period:"Jun 2025", amount:14999, method:"Bank Transfer", status:"paid", date:"2025-06-01" },
  { ref:"PAY-2025-0131", inst:"Loksewa Pathshala", plan:"growth", period:"Jun 2025", amount:3499, method:"Bank Transfer", status:"paid", date:"2025-06-01" },
];

const statusBadge = {
  paid: '<span style="background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;"><i class="fa fa-check"></i> Paid</span>',
  pending: '<span style="background:#fef3c7;color:#d97706;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;"><i class="fa fa-clock"></i> Pending</span>',
  failed: '<span style="background:#fee2e2;color:var(--red);padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;"><i class="fa fa-xmark"></i> Failed</span>',
  refunded: '<span style="background:#f3f4f6;color:#6b7280;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;"><i class="fa fa-rotate-left"></i> Refunded</span>',
};
const planBadge = { starter:'plan-starter', growth:'plan-growth', professional:'plan-professional', enterprise:'plan-enterprise' };
const planEmoji = { starter:'🌱', growth:'🚀', professional:'⭐', enterprise:'👑' };
const methodIcon = { 'Bank Transfer':'fa-building-columns', 'eSewa':'fa-mobile-screen', 'Khalti':'fa-mobile-screen', 'Cash':'fa-money-bill' };

function renderPayments(data) {
  document.getElementById('payTbody').innerHTML = data.map(p => `
    <tr style="border-bottom:1px solid var(--card-border);" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
      <td style="padding:13px 16px;font-size:12px;font-family:monospace;color:var(--navy);font-weight:700;">${p.ref}</td>
      <td style="padding:13px 16px;">
        <div style="font-size:13px;font-weight:700;color:var(--text-dark);">${p.inst}</div>
      </td>
      <td style="padding:13px 16px;"><span class="plan-badge ${planBadge[p.plan]}">${planEmoji[p.plan]} ${p.plan.charAt(0).toUpperCase()+p.plan.slice(1)}</span></td>
      <td style="padding:13px 16px;font-size:13px;color:var(--text-body);">${p.period}</td>
      <td style="padding:13px 16px;text-align:right;font-size:13px;font-weight:800;color:var(--text-dark);">NPR ${p.amount.toLocaleString()}</td>
      <td style="padding:13px 16px;font-size:12px;color:var(--text-body);"><i class="fa ${methodIcon[p.method]||'fa-credit-card'}" style="margin-right:5px;"></i>${p.method}</td>
      <td style="padding:13px 16px;text-align:center;">${statusBadge[p.status]}</td>
      <td style="padding:13px 16px;font-size:12px;color:var(--text-body);">${p.date}</td>
      <td style="padding:13px 16px;text-align:center;">
        <div style="display:flex;gap:4px;justify-content:center;">
          <button class="btn btn-sm bs" title="View Invoice"><i class="fa fa-file-invoice"></i></button>
          ${p.status==='pending' ? '<button class="btn btn-sm btn-green" title="Mark Paid" onclick="markPaid(this)"><i class="fa fa-check"></i></button>' : ''}
          ${p.status==='paid' ? '<button class="btn btn-sm btn-blue" title="Send Receipt"><i class="fa fa-envelope"></i></button>' : ''}
        </div>
      </td>
    </tr>`).join('');
}

function filterPayments(v) { renderPayments(payments.filter(p => p.inst.toLowerCase().includes(v.toLowerCase()) || p.ref.toLowerCase().includes(v.toLowerCase()))); }
function filterStatus(v) { renderPayments(v ? payments.filter(p=>p.status===v.toLowerCase()) : payments); }
function markPaid(btn) { btn.closest('tr').querySelector('td:nth-child(7)').innerHTML = '<span style="background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;"><i class="fa fa-check"></i> Paid</span>'; btn.remove(); showToast('Payment marked as received'); }
function recordPayment() { showToast('Recording new payment...'); }
function exportCSV() { showToast('Exporting payment records as CSV...'); }
function showToast(msg) { const t=document.createElement('div'); t.style.cssText='position:fixed;bottom:24px;right:24px;background:#1E293B;color:#fff;padding:12px 20px;border-radius:10px;font-size:13px;z-index:9999;'; t.innerHTML=`<i class="fa fa-check-circle" style="color:var(--green);margin-right:8px;"></i>${msg}`; document.body.appendChild(t); setTimeout(()=>t.remove(),3000); }
function toggleSidebar() { document.body.classList.toggle('sb-active'); document.body.classList.toggle('sb-collapsed'); }
function toggleMenu(id) { const m=document.getElementById(id); const c=document.getElementById('chev-'+id); const o=m.style.display==='block'; m.style.display=o?'none':'block'; if(c)c.classList.toggle('open',!o); }
function go(url) { window.location.href=url; }
renderPayments(payments);
</script>
</body>
</html>
