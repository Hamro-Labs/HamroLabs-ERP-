<?php
/**
 * Hamro ERP — SMS Credits Partial
 */
?>
<div class="page fu">
  <div class="pg-hdr">
    <div class="pg-hdr-left">
      <div class="breadcrumb">
        <span class="bc-root" onclick="goNav('overview')">Dashboard</span>
        <span class="bc-sep">›</span>
        <span class="bc-cur">SMS Credits</span>
      </div>
      <h1 style="display:flex; align-items:center; gap:10px;">
        <i class="fa fa-comment-sms" style="color:var(--green); font-size:1.1rem;"></i>
        SMS Credits
      </h1>
      <p>Platform-wide SMS usage, credit allocation, and gateway health</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <button class="btn bs" onclick="showToast('Exporting SMS report...', 'info')"><i class="fa fa-download"></i> Export</button>
      <button class="btn bt" onclick="showToast('Opening bulk credit top-up...', 'info')"><i class="fa fa-plus"></i> Top Up Credits</button>
    </div>
  </div>

  <div class="stat-grid">
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-purple"><i class="fa fa-paper-plane"></i></div><span class="stat-badge bg-p">This month</span></div>
      <div class="stat-val" id="smsSentMonth">...</div>
      <div class="stat-lbl">SMS Sent This Month</div>
      <div class="stat-sub" id="smsSentSub"><i class="fa fa-arrow-trend-up" style="color:#16a34a"></i> Based on logs</div>
    </div>
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-green"><i class="fa fa-circle-check"></i></div><span class="stat-badge bg-g" id="smsRateBadge">↑ ...%</span></div>
      <div class="stat-val" id="smsRateVal">...%</div>
      <div class="stat-lbl">Delivery Rate</div>
      <div class="stat-sub"><i class="fa fa-circle" style="color:#16a34a;font-size:7px"></i> Platform average</div>
    </div>
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-amber"><i class="fa fa-coins"></i></div><span class="stat-badge bg-y" id="smsCreditBadge">...% used</span></div>
      <div class="stat-val" id="smsCreditVal">... / ...</div>
      <div class="stat-lbl">Platform Credits Used/Total</div>
      <div style="margin-top:8px"><div class="prog-t" style="height:8px"><div class="prog-f" id="smsCreditProg" style="width:0%;background:#d97706"></div></div></div>
    </div>
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-red"><i class="fa fa-triangle-exclamation"></i></div><span class="stat-badge bg-r">Action</span></div>
      <div class="stat-val">9</div>
      <div class="stat-lbl">Tenants Near Credit Limit</div>
      <div class="stat-sub"><i class="fa fa-clock" style="color:#d97706"></i> &lt;20% remaining</div>
    </div>
  </div>

  <div class="g65">
    <div class="card">
      <div class="ct"><i class="fa fa-chart-bar"></i> Daily SMS Volume — Last 30 Days</div>
      <div style="display:flex;align-items:flex-end;gap:3px;height:130px;padding-top:8px" id="smsChart"></div>
      <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--text-light);margin-top:6px"><span>1 Feb</span><span>8 Feb</span><span>15 Feb</span><span>21 Feb</span></div>
    </div>
    <div class="card">
      <div class="ct"><i class="fa fa-tower-broadcast"></i> Gateway Status</div>
      <div class="gateway-card" style="margin-bottom:12px; background:#f8fafc; border-radius:10px; padding:16px; border:1px solid var(--card-border);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <div style="font-size:13.5px;font-weight:800">Sparrow SMS <span class="tag bg-g" style="margin-left:4px">Primary</span></div>
          <span class="pill pg"><i class="fa fa-circle" style="font-size:7px"></i> Online</span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px">
          <div style="color:var(--text-body)">Latency: <strong>95ms</strong></div>
          <div style="color:var(--text-body)">Today sent: <strong>41,200</strong></div>
          <div style="color:var(--text-body)">Delivery: <strong>97.4%</strong></div>
          <div style="color:var(--text-body)">Nepali Unicode: <strong style="color:#16a34a">✓ Yes</strong></div>
        </div>
      </div>
      <div class="gateway-card" style="background:#f8fafc; border-radius:10px; padding:16px; border:1px solid var(--card-border);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <div style="font-size:13.5px;font-weight:800">Aakash SMS <span class="tag bg-b" style="margin-left:4px">Failover</span></div>
          <span class="pill pb"><i class="fa fa-pause-circle" style="font-size:7px"></i> Standby</span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px">
          <div style="color:var(--text-body)">Last failover: <strong>Never</strong></div>
          <div style="color:var(--text-body)">Status: <strong style="color:#16a34a">Ready</strong></div>
          <div style="color:var(--text-body)">Auto-switch: <strong>Enabled</strong></div>
          <div style="color:var(--text-body)">Nepali Unicode: <strong style="color:#16a34a">✓ Yes</strong></div>
        </div>
      </div>
    </div>
  </div>

  <div class="g2">
    <div class="card">
      <div class="ct"><i class="fa fa-arrow-down-wide-short"></i> Top SMS Consuming Tenants</div>
      <div id="topSmsInstitutes"></div>
    </div>
    <div class="tbl-wrap">
      <div class="tbl-head"><div class="tbl-title"><i class="fa fa-triangle-exclamation"></i> Low Credit Alert</div><button class="btn btn-amber btn-sm" onclick="showToast('Sending credit alerts to 9 tenants...', 'info')"><i class="fa fa-bell"></i> Alert All</button></div>
      <div class="tbl-scroll"><table><thead><tr><th>Institute</th><th>Remaining</th><th>% Left</th><th>Action</th></tr></thead><tbody id="lowCreditTbl"></tbody></table></div>
    </div>
  </div>
</div>

<script>
(function() {
    const smsDaily = [28,31,24,35,42,38,29,33,41,45,38,32,28,36,44,48,42,35,31,38,52,41,39,45,50,43,38,41,47,44];
    const smsChart = document.getElementById("smsChart");
    if (smsChart) {
        smsChart.innerHTML = smsDaily.map((v, i) => {
            const h = Math.round((v / 60) * 120);
            const c = i === smsDaily.length - 1 ? "var(--green)" : "#a5d6ca";
            return `<div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:2px">
                <div style="font-size:7px;font-weight:700;color:var(--text-dark)">${i === smsDaily.length - 1 ? v + "K" : ""}</div>
                <div style="height:${h}px;width:100%;background:${c};border-radius:3px 3px 0 0;cursor:pointer;transition:0.2s" title="Day ${i + 1}: ${v}K SMS"></div>
            </div>`;
        }).join("");
    }

    const s = window.platformStatsData || {};
    const sms = s.sms || {};

    const topInst = (sms.topUsers || []).map(t => ({
        name: t.name,
        code: t.subdomain.substring(0, 3).toUpperCase(),
        used: parseInt(t.used),
        total: parseInt(t.total) || 1000,
        col: "ic-blue"
    }));
    const topSmsInstitutes = document.getElementById("topSmsInstitutes");
    if (topSmsInstitutes) {
        topSmsInstitutes.innerHTML = topInst.map(t => {
            const pct = Math.round((t.used / t.total) * 100);
            const bc = pct > 90 ? "#e11d48" : pct > 70 ? "#d97706" : "var(--green)";
            return `<div class="sms-inst-row" style="display:flex;align-items:center;gap:10px;padding:12px;border-radius:10px;border:1px solid var(--card-border);margin-bottom:8px;transition:0.2s;background:#fff">
                <div class="sms-av ${t.col}" style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;flex-shrink:0;">${t.code}</div>
                <div style="flex:1">
                    <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:700;margin-bottom:4px"><span>${t.name}</span><span style="color:var(--text-light)">${(t.used/1000).toFixed(0)}K / ${(t.total/1000).toFixed(0)}K</span></div>
                    <div class="prog-t"><div class="prog-f" style="width:${pct}%;background:${bc}"></div></div>
                </div>
            </div>`;
        }).join("");
    }

    const lowCreditTbl = document.getElementById("lowCreditTbl");
    if (lowCreditTbl) {
        const low = [
            {n:"Everest Loksewa",rem:1200,total:5000},{n:"Hetauda TSC",rem:20,total:500},
            {n:"Pokhara Banking",rem:80,total:500},{n:"Birgunj Academy",rem:350,total:2000},
            {n:"Palpa Coaching",rem:400,total:2000},{n:"Ilam Loksewa",rem:90,total:500},
        ];
        lowCreditTbl.innerHTML = low.map(l => {
            const pct = Math.round((l.rem / l.total) * 100);
            const c = pct < 5 ? "pr" : pct < 15 ? "py" : "pb";
            return `<tr><td style="font-weight:600;color:var(--text-dark)">${l.n}</td><td style="font-weight:700">${l.rem.toLocaleString()}</td><td><span class="pill ${c}">${pct}%</span></td><td><button class="btn btn-purple btn-sm" onclick="showToast('Opening top-up for ${l.n}...', 'info')"><i class="fa fa-plus"></i> Top Up</button></td></tr>`;
        }).join("");
    }

    // Populate Dynamic Stats
    if (sms) {
        if (document.getElementById('smsSentMonth')) document.getElementById('smsSentMonth').textContent = (sms.sentThisMonth / 1000).toFixed(1) + 'K';
        if (document.getElementById('smsRateBadge')) document.getElementById('smsRateBadge').textContent = '↑ ' + sms.successRate + '%';
        if (document.getElementById('smsRateVal')) document.getElementById('smsRateVal').textContent = sms.successRate + '%';
        
        const creditPct = Math.round((sms.usedCredits / (sms.totalCredits || 1)) * 100);
        if (document.getElementById('smsCreditBadge')) document.getElementById('smsCreditBadge').textContent = creditPct + '% used';
        if (document.getElementById('smsCreditVal')) document.getElementById('smsCreditVal').textContent = (sms.usedCredits / 1000).toFixed(1) + 'K / ' + (sms.totalCredits / 1000).toFixed(1) + 'K';
        if (document.getElementById('smsCreditProg')) document.getElementById('smsCreditProg').style.width = creditPct + '%';
    }
})();
</script>
