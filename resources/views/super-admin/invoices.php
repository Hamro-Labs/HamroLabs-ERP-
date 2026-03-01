<?php
require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pageTitle = 'Invoice Generator';
$activePage = 'invoices.php';
?>

<!-- Sidebar -->
<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<!-- Main Content -->
<main class="main">
<div class="page">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
      <div style="font-size:11px;color:var(--text-light);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Revenue Analytics</div>
      <h1 style="font-size:22px;font-weight:800;">Invoice Generator</h1>
      <p style="font-size:13px;color:var(--text-body);margin-top:4px;">Create, preview and send invoices to institutes</p>
    </div>
    <div style="display:flex;gap:10px;">
      <button class="btn bs"><i class="fa fa-list"></i> All Invoices</button>
      <button class="btn bt" onclick="generateInvoice()"><i class="fa fa-file-invoice"></i> Generate Invoice</button>
    </div>
  </div>

  <div class="g65">
    <!-- Form -->
    <div class="card">
      <div style="font-size:15px;font-weight:800;margin-bottom:20px;display:flex;align-items:center;gap:8px;"><i class="fa fa-pen" style="color:var(--green);"></i> Invoice Details</div>
      <div class="form-grp">
        <label class="form-lbl">Institute (Tenant)</label>
        <select class="form-sel" id="instSelect" onchange="updateInvoice()">
          <option value="">— Select Institute —</option>
          <option value="loksewa">Loksewa Pathshala</option>
          <option value="nayab">Nayab Subba Academy</option>
          <option value="kharidar">Kharidar Study Hub</option>
          <option value="psc">PSC Coaching Center</option>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-grp"><label class="form-lbl">Invoice Number</label><input type="text" class="form-inp" id="invNo" value="INV-2025-0001" readonly style="background:#f0fdf4;font-weight:700;color:var(--green);"></div>
        <div class="form-grp"><label class="form-lbl">Invoice Date</label><input type="date" class="form-inp" id="invDate" value="2025-07-10" onchange="updateInvoice()"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-grp"><label class="form-lbl">Billing Period From</label><input type="date" class="form-inp" id="billFrom" value="2025-07-01" onchange="updateInvoice()"></div>
        <div class="form-grp"><label class="form-lbl">Billing Period To</label><input type="date" class="form-inp" id="billTo" value="2025-07-31" onchange="updateInvoice()"></div>
      </div>

      <div class="form-sec-title">Line Items</div>
      <div id="lineItems">
        <div class="line-item" style="display:grid;grid-template-columns:1fr auto auto;gap:10px;margin-bottom:10px;align-items:center;">
          <input type="text" class="form-inp" style="padding:10px 12px;" placeholder="Description" value="Growth Plan Subscription — Jul 2025" oninput="updateInvoice()">
          <input type="number" class="form-inp" style="width:120px;padding:10px 12px;" placeholder="Amount" value="3499" oninput="updateInvoice()">
          <button onclick="removeItem(this)" style="width:36px;height:40px;border:1px solid #fca5a5;background:#fef2f2;border-radius:8px;cursor:pointer;color:var(--red);"><i class="fa fa-trash"></i></button>
        </div>
      </div>
      <button class="btn bs" style="width:100%;" onclick="addItem()"><i class="fa fa-plus"></i> Add Line Item</button>

      <div class="form-sec-title">Additional Settings</div>
      <div class="form-grp"><label class="form-lbl">Discount (%)</label><input type="number" class="form-inp" id="discount" value="0" min="0" max="100" oninput="updateInvoice()"></div>
      <div class="form-grp"><label class="form-lbl">Notes (optional)</label><textarea class="form-inp" rows="3" placeholder="Payment due within 7 days. Bank details: ..."></textarea></div>
      <div style="display:flex;gap:10px;margin-top:8px;">
        <button class="btn bs" style="flex:1;" onclick="previewInvoice()"><i class="fa fa-eye"></i> Preview</button>
        <button class="btn bt" style="flex:1;" onclick="sendInvoice()"><i class="fa fa-paper-plane"></i> Send to Institute</button>
      </div>
    </div>

    <!-- Live Preview -->
    <div>
      <div class="card" style="position:sticky;top:76px;">
        <div style="font-size:13px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;">Live Preview</div>
        <!-- Invoice Preview -->
        <div id="invoicePreview" style="border:1px solid var(--card-border);border-radius:10px;padding:24px;background:#fff;">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;">
            <div>
              <div style="font-size:20px;font-weight:800;color:var(--green);">🎓 Hamro Labs</div>
              <div style="font-size:11px;color:var(--text-light);margin-top:4px;">Kathmandu, Nepal</div>
              <div style="font-size:11px;color:var(--text-light);">support@hamrolabs.com.np</div>
            </div>
            <div style="text-align:right;">
              <div style="font-size:18px;font-weight:800;color:var(--text-dark);">INVOICE</div>
              <div style="font-size:13px;font-weight:700;color:var(--green);" id="previewInvNo">INV-2025-0001</div>
              <div style="font-size:11px;color:var(--text-light);" id="previewDate">Date: 2025-07-10</div>
            </div>
          </div>
          <div style="background:#f8fafc;border-radius:8px;padding:14px;margin-bottom:20px;">
            <div style="font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;margin-bottom:6px;">Bill To</div>
            <div style="font-size:14px;font-weight:800;" id="previewInst">— Select Institute —</div>
            <div style="font-size:12px;color:var(--text-light);" id="previewPeriod">Period: 2025-07-01 to 2025-07-31</div>
          </div>
          <table style="min-width:auto;width:100%;margin-bottom:16px;">
            <thead><tr style="border-bottom:2px solid var(--green);"><th style="padding:8px 0;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;">Description</th><th style="padding:8px 0;text-align:right;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;">Amount</th></tr></thead>
            <tbody id="previewItems"></tbody>
          </table>
          <div style="border-top:1px solid var(--card-border);padding-top:12px;">
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;"><span style="color:var(--text-body);">Subtotal</span><span id="previewSubtotal" style="font-weight:700;">NPR 0</span></div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;" id="discountRow" style="display:none;"><span style="color:var(--text-body);">Discount</span><span id="previewDiscount" style="color:var(--red);font-weight:700;">-NPR 0</span></div>
            <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:800;margin-top:8px;border-top:1px solid var(--card-border);padding-top:8px;"><span>Total</span><span id="previewTotal" style="color:var(--green);">NPR 0</span></div>
          </div>
          <div style="margin-top:20px;padding:12px;background:#f0fdf4;border-radius:8px;font-size:11px;color:#16a34a;font-weight:600;text-align:center;"><i class="fa fa-clock"></i> Payment due within 7 days of invoice date</div>
        </div>
        <div style="display:flex;gap:8px;margin-top:12px;">
          <button class="btn bs btn-sm" style="flex:1;" onclick="downloadPDF()"><i class="fa fa-download"></i> Download PDF</button>
          <button class="btn btn-sm" style="flex:1;background:#eff6ff;color:#3b82f6;" onclick="copyLink()"><i class="fa fa-link"></i> Copy Link</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Invoices -->
  <div class="tbl-wrap" style="margin-top:20px;">
    <div class="tbl-head"><div class="tbl-title"><i class="fa fa-history"></i> Recent Invoices</div></div>
    <table>
      <thead><tr style="background:#f8fafc;"><th style="padding:12px 20px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Invoice No.</th><th style="padding:12px 20px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Institute</th><th style="padding:12px 20px;text-align:right;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Amount</th><th style="padding:12px 20px;text-align:center;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Status</th><th style="padding:12px 20px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Date</th><th style="padding:12px 20px;text-align:center;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;border-bottom:1px solid var(--card-border);">Actions</th></tr></thead>
      <tbody>
        <tr style="border-bottom:1px solid var(--card-border);" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''"><td style="padding:13px 20px;font-family:monospace;font-weight:700;font-size:12px;color:var(--navy);">INV-2025-0009</td><td style="padding:13px 20px;font-size:13px;font-weight:600;">Loksewa Pathshala</td><td style="padding:13px 20px;text-align:right;font-size:13px;font-weight:800;">NPR 3,499</td><td style="padding:13px 20px;text-align:center;"><span style="background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">Paid</span></td><td style="padding:13px 20px;font-size:12px;color:var(--text-body);">2025-07-01</td><td style="padding:13px 20px;text-align:center;"><div style="display:flex;gap:4px;justify-content:center;"><button class="btn btn-sm bs"><i class="fa fa-download"></i></button><button class="btn btn-sm bs"><i class="fa fa-paper-plane"></i></button></div></td></tr>
        <tr style="border-bottom:1px solid var(--card-border);" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''"><td style="padding:13px 20px;font-family:monospace;font-weight:700;font-size:12px;color:var(--navy);">INV-2025-0008</td><td style="padding:13px 20px;font-size:13px;font-weight:600;">Nayab Subba Academy</td><td style="padding:13px 20px;text-align:right;font-size:13px;font-weight:800;">NPR 6,999</td><td style="padding:13px 20px;text-align:center;"><span style="background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">Paid</span></td><td style="padding:13px 20px;font-size:12px;color:var(--text-body);">2025-07-01</td><td style="padding:13px 20px;text-align:center;"><div style="display:flex;gap:4px;justify-content:center;"><button class="btn btn-sm bs"><i class="fa fa-download"></i></button><button class="btn btn-sm bs"><i class="fa fa-paper-plane"></i></button></div></td></tr>
        <tr style="border-bottom:1px solid var(--card-border);" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''"><td style="padding:13px 20px;font-family:monospace;font-weight:700;font-size:12px;color:var(--navy);">INV-2025-0007</td><td style="padding:13px 20px;font-size:13px;font-weight:600;">PSC Coaching Center</td><td style="padding:13px 20px;text-align:right;font-size:13px;font-weight:800;">NPR 1,499</td><td style="padding:13px 20px;text-align:center;"><span style="background:#fef3c7;color:#d97706;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">Pending</span></td><td style="padding:13px 20px;font-size:12px;color:var(--text-body);">2025-07-03</td><td style="padding:13px 20px;text-align:center;"><div style="display:flex;gap:4px;justify-content:center;"><button class="btn btn-sm bs"><i class="fa fa-download"></i></button><button class="btn btn-sm bs"><i class="fa fa-paper-plane"></i></button></div></td></tr>
      </tbody>
    </table>
  </div>
</div>
</main>

<script>
const instNames = { loksewa:'Loksewa Pathshala', nayab:'Nayab Subba Academy', kharidar:'Kharidar Study Hub', psc:'PSC Coaching Center' };

function updateInvoice() {
  const inst = document.getElementById('instSelect').value;
  document.getElementById('previewInst').textContent = inst ? instNames[inst] : '— Select Institute —';
  document.getElementById('previewDate').textContent = 'Date: ' + (document.getElementById('invDate').value || '—');
  document.getElementById('previewPeriod').textContent = 'Period: ' + document.getElementById('billFrom').value + ' to ' + document.getElementById('billTo').value;
  document.getElementById('previewInvNo').textContent = document.getElementById('invNo').value;
  
  const items = document.querySelectorAll('.line-item');
  let subtotal = 0;
  let previewHTML = '';
  items.forEach(item => {
    const desc = item.querySelector('input[type=text]').value;
    const amt = parseFloat(item.querySelector('input[type=number]').value) || 0;
    subtotal += amt;
    previewHTML += `<tr><td style="padding:8px 0;font-size:12px;">${desc}</td><td style="padding:8px 0;text-align:right;font-size:12px;font-weight:700;">NPR ${amt.toLocaleString()}</td></tr>`;
  });
  document.getElementById('previewItems').innerHTML = previewHTML;
  
  const disc = parseFloat(document.getElementById('discount').value) || 0;
  const discAmt = subtotal * disc / 100;
  const total = subtotal - discAmt;
  document.getElementById('previewSubtotal').textContent = `NPR ${subtotal.toLocaleString()}`;
  document.getElementById('previewDiscount').textContent = `-NPR ${discAmt.toLocaleString()}`;
  document.getElementById('previewTotal').textContent = `NPR ${total.toLocaleString()}`;
  if (disc > 0) document.getElementById('discountRow').style.display = 'flex'; else document.getElementById('discountRow').style.display = 'none';
}

function addItem() {
  const div = document.createElement('div');
  div.className = 'line-item';
  div.style.cssText = 'display:grid;grid-template-columns:1fr auto auto;gap:10px;margin-bottom:10px;align-items:center;';
  div.innerHTML = `<input type="text" class="form-inp" style="padding:10px 12px;" placeholder="Description" oninput="updateInvoice()"><input type="number" class="form-inp" style="width:120px;padding:10px 12px;" placeholder="Amount" value="0" oninput="updateInvoice()"><button onclick="removeItem(this)" style="width:36px;height:40px;border:1px solid #fca5a5;background:#fef2f2;border-radius:8px;cursor:pointer;color:var(--red);"><i class="fa fa-trash"></i></button>`;
  document.getElementById('lineItems').appendChild(div);
  updateInvoice();
}
function removeItem(btn) { btn.closest('.line-item').remove(); updateInvoice(); }
function generateInvoice() { showToast('Invoice generated! Preview updated.'); }
function previewInvoice() { showToast('Opening full-screen preview...'); }
function sendInvoice() { showToast('Invoice sent to institute via email!'); }
function downloadPDF() { showToast('Generating PDF invoice...'); }
function copyLink() { showToast('Invoice link copied to clipboard!'); }
function showToast(msg) { const t=document.createElement('div'); t.style.cssText='position:fixed;bottom:24px;right:24px;background:#1E293B;color:#fff;padding:12px 20px;border-radius:10px;font-size:13px;z-index:9999;'; t.innerHTML=`<i class="fa fa-check-circle" style="color:var(--green);margin-right:8px;"></i>${msg}`; document.body.appendChild(t); setTimeout(()=>t.remove(),3000); }
function toggleSidebar() { document.body.classList.toggle('sb-active'); document.body.classList.toggle('sb-collapsed'); }
function toggleMenu(id) { const m=document.getElementById(id); const c=document.getElementById('chev-'+id); const o=m.style.display==='block'; m.style.display=o?'none':'block'; if(c)c.classList.toggle('open',!o); }
function go(url) { window.location.href=url; }
updateInvoice();
</script>
</body>
</html>
