<?php
/**
 * Hamro ERP — Revenue Analytics Partial
 */
require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pdo = getDBConnection();

$planPrices = [
    'starter' => 1500,
    'growth' => 3500,
    'professional' => 7500,
    'enterprise' => 15000
];

// Fetch plan distribution and calculate MRR
$stmt = $pdo->query("SELECT plan, COUNT(*) as cnt FROM tenants WHERE status='active' GROUP BY plan");
$planDist = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$totalActive = 0;
$mrr = 0;
$planStats = ['starter' => 0, 'growth' => 0, 'professional' => 0, 'enterprise' => 0];

foreach ($planDist as $p => $c) {
    if (isset($planStats[$p])) {
        $planStats[$p] = (int)$c;
        $mrr += $planStats[$p] * ($planPrices[$p] ?? 0);
        $totalActive += $planStats[$p];
    }
}
$arr = $mrr * 12;

// Recent Signups
$stmt = $pdo->query("SELECT name, plan, province, created_at FROM tenants WHERE status='active' ORDER BY created_at DESC LIMIT 5");
$recentSignups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dummy payments (Platform tenant payments missing in DB schema)
$recentPayments = [
    ['institute' => 'Global Academic Center', 'plan' => 'professional', 'amount' => 7500, 'date' => date('Y-m-d'), 'status' => 'pg'],
    ['institute' => 'Sagarmatha Public School', 'plan' => 'growth', 'amount' => 3500, 'date' => date('Y-m-d', strtotime('-1 day')), 'status' => 'pg'],
    ['institute' => 'Everest Nursing College', 'plan' => 'enterprise', 'amount' => 15000, 'date' => date('Y-m-d', strtotime('-2 days')), 'status' => 'pg'],
];

// Dummy MRR Trend
$baseMRR = max(100000, $mrr * 0.5); // Example base
$mrrTrend = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('M', strtotime("-$i months"));
    $mrrTrend[] = [
        'm' => $month,
        'v' => round(($baseMRR + ($i * rand(-5000, 10000))) / 1000)
    ];
}
$mrrTrend[count($mrrTrend) - 1]['v'] = round($mrr / 1000); // Current month is actual MRR

$pageTitle = 'Revenue Analytics';
$activePage = 'revenue.php';
?>

<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<main class="main" id="mainContent">
<div class="page fu">
  <div class="pg-hdr">
    <div class="pg-hdr-left">
      <div class="breadcrumb">
        <span class="bc-root" onclick="goNav('overview')">Dashboard</span>
        <span class="bc-sep">›</span>
        <span class="bc-cur">Revenue Analytics</span>
      </div>
      <h1 style="display:flex; align-items:center; gap:10px;">
        <i class="fa fa-chart-bar" style="color:var(--green); font-size:1.1rem;"></i>
        Revenue Analytics
      </h1>
      <p>MRR/ARR trends, subscription breakdown, and payment health</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <select class="btn bs" style="padding:8px 12px;font-size:13px" id="periodSel" onchange="updateCharts()">
        <option value="12">Last 12 Months</option>
        <option value="6">Last 6 Months</option>
        <option value="3">Last 3 Months</option>
      </select>
      <button class="btn bs" onclick="showToast('Exporting revenue report...', 'info')"><i class="fa fa-download"></i> Export</button>
      <button class="btn bt" onclick="showToast('Opening invoice generator...', 'info')"><i class="fa fa-file-invoice"></i> Generate Invoice</button>
    </div>
  </div>

  <div class="stat-grid">
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-green"><i class="fa fa-money-bill-trend-up"></i></div><span class="stat-badge bg-g">↑ Realtime</span></div>
      <div class="stat-val">NPR <?php echo number_format($mrr); ?></div>
      <div class="stat-lbl">Monthly Recurring Revenue (MRR)</div>
      <div class="stat-sub"><i class="fa fa-arrow-trend-up" style="color:#16a34a"></i> Live platform data</div>
    </div>
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-blue"><i class="fa fa-calendar-check"></i></div><span class="stat-badge bg-b">Annual</span></div>
      <div class="stat-val">NPR <?php echo number_format($arr); ?></div>
      <div class="stat-lbl">Annual Recurring Revenue (ARR)</div>
      <div class="stat-sub"><i class="fa fa-circle" style="color:#3b82f6;font-size:7px"></i> <?php echo $totalActive; ?> active subscriptions</div>
    </div>
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-red"><i class="fa fa-person-running"></i></div><span class="stat-badge bg-r">Watch</span></div>
      <div class="stat-val">3.2%</div>
      <div class="stat-lbl">Monthly Churn Rate</div>
      <div class="stat-sub"><i class="fa fa-minus" style="color:var(--text-light)"></i> 2 tenants at risk</div>
    </div>
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-amber"><i class="fa fa-triangle-exclamation"></i></div><span class="stat-badge bg-y">Action</span></div>
      <div class="stat-val">NPR 18,500</div>
      <div class="stat-lbl">Overdue Payments</div>
      <div class="stat-sub"><i class="fa fa-clock" style="color:#d97706"></i> 9 invoices overdue</div>
    </div>
  </div>

  <div class="g65">
    <div class="card">
      <div class="ct"><i class="fa fa-chart-bar"></i> MRR Trend — Last 12 Months <span style="margin-left:auto;font-size:11px;color:var(--text-light);font-weight:500">NPR (×1000)</span></div>
      <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--text-light);margin-bottom:4px"><span>0</span><span>100K</span><span>200K</span><span>300K</span></div>
      <div class="bar-chart-wrap" id="mrrChart" style="display:flex;align-items:flex-end;gap:5px;height:160px;padding-top:10px"></div>
      <div style="display:flex;gap:14px;flex-wrap:wrap;margin-top:12px">
        <span style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--text-body)"><span style="width:10px;height:10px;border-radius:3px;background:var(--green);display:inline-block"></span> MRR</span>
        <span style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--text-body)"><span style="width:10px;height:10px;border-radius:3px;background:#3b82f6;display:inline-block"></span> New Revenue</span>
      </div>
    </div>
    <div class="card">
      <div class="ct"><i class="fa fa-chart-pie"></i> Revenue by Plan</div>
      <div class="donut-wrap" style="display:flex;align-items:center;justify-content:center;gap:30px;flex-wrap:wrap;padding:10px 0;">
        <canvas id="donutChart" width="130" height="130"></canvas>
        <div class="donut-labels" style="display:flex;flex-direction:column;gap:12px;min-width:140px">
          <div class="donut-lbl-row" style="display:flex;align-items:center;gap:10px;font-size:12.5px"><div class="donut-dot" style="width:10px;height:10px;border-radius:50%;background:#d97706"></div><span style="flex:1">Enterprise</span><strong>41%</strong></div>
          <div class="donut-lbl-row" style="display:flex;align-items:center;gap:10px;font-size:12.5px"><div class="donut-dot" style="width:10px;height:10px;border-radius:50%;background:var(--purple)"></div><span style="flex:1">Professional</span><strong>33%</strong></div>
          <div class="donut-lbl-row" style="display:flex;align-items:center;gap:10px;font-size:12.5px"><div class="donut-dot" style="width:10px;height:10px;border-radius:50%;background:#3b82f6"></div><span style="flex:1">Growth</span><strong>19%</strong></div>
          <div class="donut-lbl-row" style="display:flex;align-items:center;gap:10px;font-size:12.5px"><div class="donut-dot" style="width:10px;height:10px;border-radius:50%;background:#16a34a"></div><span style="flex:1">Starter</span><strong>7%</strong></div>
        </div>
      </div>
    </div>
  </div>

  <div class="g2">
    <div class="tbl-wrap">
      <div class="tbl-head"><div class="tbl-title"><i class="fa fa-receipt"></i> Recent Payments</div><button class="btn bs btn-sm" onclick="showToast('Loading all payments...', 'info')">View All</button></div>
      <div class="tbl-scroll"><table><thead><tr><th>Institute</th><th>Plan</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead><tbody id="payTbl"></tbody></table></div>
    </div>
    <div class="card">
      <div class="ct"><i class="fa fa-layer-group"></i> Subscription Breakdown</div>
      <div id="planBreak"></div>
      <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--card-border)">
        <div class="ct" style="font-size:13px"><i class="fa fa-arrow-trend-up"></i> This Month Movement</div>
        <div style="display:flex;flex-direction:column;gap:8px" id="movRows"></div>
      </div>
    </div>
  </div>

  <div class="g2">
    <div class="card"><div class="ct"><i class="fa fa-user-plus"></i> Recent Signups</div><div id="signupList"></div></div>
    <div class="card"><div class="ct"><i class="fa fa-person-running"></i> Churn Risk Tenants</div><div id="churnList"></div></div>
  </div>
</div>

<script>
(function() {
    const s = {
        mrrTrend: <?php echo json_encode($mrrTrend); ?>,
        planStats: <?php echo json_encode($planStats); ?>,
        recentPayments: <?php echo json_encode($recentPayments); ?>,
        recentSignups: <?php echo json_encode($recentSignups); ?>
    };
    const mrrData = s.mrrTrend || [];

    window.updateCharts = function() {
        const val = parseInt(document.getElementById("periodSel").value);
        renderMRR(val);
    };

    function renderMRR(n) {
        const data = mrrData.slice(-n);
        const mx = Math.max(...data.map(d => d.v));
        const container = document.getElementById("mrrChart");
        if (!container) return;
        container.innerHTML = data.map(d => {
            const h = Math.round((d.v / mx) * 150);
            return `<div class="bar-col" style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px">
                <div class="bar-val" style="font-size:8px;font-weight:700;text-align:center;color:var(--text-dark)">${d.v}K</div>
                <div class="bar-fill" style="width:100%;border-radius:5px 5px 0 0;cursor:pointer;transition:opacity .2s;height:${h}px;background:linear-gradient(180deg,var(--green),var(--green-d))" title="${d.m}: NPR ${d.v}K"></div>
                <div class="bar-lbl" style="font-size:9px;color:var(--text-light);text-align:center">${d.m}</div>
            </div>`;
        }).join("");
    }

    function renderDonut() {
        const c = document.getElementById("donutChart");
        if (!c) return;
        const ctx = c.getContext("2d");
        const cx = 65, cy = 65, r = 52, ir = 32;
        const slices = [
            {v: s.planStats['enterprise'] || 0, c: "#d97706", l: 'Enterprise'}, 
            {v: s.planStats['professional'] || 0, c: "#8141A5", l: 'Professional'}, 
            {v: s.planStats['growth'] || 0, c: "#3b82f6", l: 'Growth'}, 
            {v: s.planStats['starter'] || 0, c: "#16a34a", l: 'Starter'}
        ];
        const total = slices.reduce((acc, curr) => acc + curr.v, 0);

        let a = -Math.PI / 2;
        ctx.clearRect(0, 0, 130, 130);
        slices.forEach(sl => {
            if (total === 0) return;
            const sw = (sl.v / total) * 2 * Math.PI;
            ctx.beginPath();
            ctx.moveTo(cx, cy);
            ctx.arc(cx, cy, r, a, a + sw);
            ctx.closePath();
            ctx.fillStyle = sl.c;
            ctx.fill();
            a += sw;
        });
        ctx.beginPath();
        ctx.arc(cx, cy, ir, 0, 2 * Math.PI);
        ctx.fillStyle = "#fff";
        ctx.fill();
        ctx.fillStyle = "#1E293B";
        ctx.font = "bold 13px Plus Jakarta Sans";
        ctx.textAlign = "center";
        ctx.fillText(total, cx, cy + 4);
        ctx.font = "9px Plus Jakarta Sans";
        ctx.fillStyle = "#94A3B8";
        ctx.fillText("Active", cx, cy + 16);
    }

    const planTotal = Object.values(s.planStats || {}).reduce((a, b) => a + b, 0);
    const planData = [
        {name: "Enterprise 🏆", cnt: s.planStats['enterprise'] || 0, c: "#d97706"},
        {name: "Professional ⭐", cnt: s.planStats['professional'] || 0, c: "#8141A5"},
        {name: "Growth 📈", cnt: s.planStats['growth'] || 0, c: "#3b82f6"},
        {name: "Starter 🌱", cnt: s.planStats['starter'] || 0, c: "#16a34a"},
    ].map(p => ({...p, pct: planTotal > 0 ? (p.cnt / planTotal) * 100 : 0}));

    const planBreak = document.getElementById("planBreak");
    if (planBreak) {
        planBreak.innerHTML = planData.map(p => `
        <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;margin-bottom:5px"><span>${p.name}</span><span style="color:var(--text-light)">${p.cnt} tenants</span></div>
            <div class="prog-t" style="height:8px"><div class="prog-f" style="width:${p.pct}%;background:${p.c}"></div></div>
        </div>`).join("");
    }

    const movRows = document.getElementById("movRows");
    if (movRows) {
        movRows.innerHTML = [
            {l: "New signups", v: "+2", c: "#16a34a"}, {l: "Upgrades", v: "+0", c: "#3b82f6"},
            {l: "Downgrades", v: "-0", c: "#d97706"}, {l: "Churned", v: "-0", c: "#e11d48"},
        ].map(r => `<div style="display:flex;justify-content:space-between;padding:8px 10px;background:#f8fafc;border-radius:8px;border:1px solid var(--card-border)"><span style="font-size:13px">${r.l}</span><span style="font-weight:800;color:${r.c}">${r.v}</span></div>`).join("");
    }

    const planBadge = {enterprise: "plan-enterprise", professional: "plan-professional", growth: "plan-growth", starter: "plan-starter"};
    const planIcon = {enterprise: "🏆", professional: "⭐", growth: "📈", starter: "🌱"};
    const pays = (s.recentPayments || []).map(p => ({
        i: p.institute,
        p: p.plan,
        a: "NPR " + parseInt(p.amount).toLocaleString(),
        d: new Date(p.date).toLocaleDateString(),
        s: "pg"
    }));

    const payTbl = document.getElementById("payTbl");
    if (payTbl) {
        payTbl.innerHTML = pays.map(p => `<tr><td style="font-weight:600;color:var(--text-dark)">${p.i}</td><td><span class="plan-badge ${planBadge[p.p]}">${planIcon[p.p]} ${p.p}</span></td><td style="font-weight:700">${p.a}</td><td style="font-size:12px;color:var(--text-light)">${p.d}</td><td><span class="pill ${p.s}">${{pg: "Paid", py: "Pending", pr: "Overdue"}[p.s]}</span></td></tr>`).join("");
    }

    const signups = (s.recentSignups || []).map(sn => ({
        n: sn.name,
        p: sn.plan,
        d: new Date(sn.created_at).toLocaleDateString(),
        prov: sn.province || 'Nepal'
    }));

    const signupList = document.getElementById("signupList");
    if (signupList) {
        signupList.innerHTML = signups.map((sn, idx) => `<div class="tl-item" style="display:flex;align-items:center;gap:12px;padding:12px 0;${idx === signups.length - 1 ? '' : 'border-bottom:1px solid #f1f5f9;'}"><div class="tl-dot ic-green" style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px"><i class="fa fa-user-plus" style="font-size:13px"></i></div><div style="flex:1"><div style="font-size:13.5px;font-weight:700">${sn.n}</div><div style="font-size:11px;color:var(--text-light)">${sn.prov} · ${sn.d}</div></div><span class="plan-badge ${planBadge[sn.p]}" style="font-size:10px">${planIcon[sn.p]} ${sn.p}</span></div>`).join("");
    }

    const churns = [
        {n: "Butwal Classes", p: "starter", r: "No login 30d", lvl: "pr"},
        {n: "Hetauda TSC", p: "starter", r: "Payment overdue", lvl: "pr"},
        {n: "Pokhara Banking", p: "starter", r: "Low usage", lvl: "py"},
        {n: "Palpa Coaching", p: "growth", r: "Downgraded", lvl: "py"},
    ];

    const churnList = document.getElementById("churnList");
    if (churnList) {
        churnList.innerHTML = churns.map((c, idx) => `<div class="tl-item" style="display:flex;align-items:center;gap:12px;padding:12px 0;${idx === churns.length - 1 ? '' : 'border-bottom:1px solid #f1f5f9;'}"><div class="tl-dot ic-red" style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px"><i class="fa fa-triangle-exclamation" style="font-size:13px"></i></div><div style="flex:1"><div style="font-size:13.5px;font-weight:700">${c.n}</div><div style="font-size:11px;color:var(--text-light)"><span class="plan-badge ${planBadge[c.p]}" style="padding:1px 6px;font-size:10px">${c.p}</span> · ${c.r}</div></div><span class="pill ${c.lvl}" style="font-size:9px">${c.lvl === "pr" ? "High" : "Medium"}</span></div>`).join("");
    }

    renderMRR(12);
    setTimeout(renderDonut, 100);
})();
</script>

<style>
.bar-fill:hover { opacity: 0.8; }
.plan-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.plan-starter { background: #f0fdf4; color: #16a34a; }
.plan-growth { background: #eff6ff; color: #3b82f6; }
.plan-professional { background: var(--soft-purple); color: var(--purple); }
.plan-enterprise { background: #fef3c7; color: #d97706; }
</style>
</main>
<?php include 'footer.php'; ?>
