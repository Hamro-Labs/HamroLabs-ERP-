<?php
/**
 * Hamro ERP — SMS Credits Partial
 */
require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pdo = getDBConnection();

// Overall Stats
$stmt = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM sms_logs WHERE status='delivered') as delivered,
    (SELECT COUNT(*) FROM sms_logs) as total_sent,
    (SELECT COUNT(*) FROM sms_logs WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())) as sent_this_month
");
$overall = $stmt->fetch(PDO::FETCH_ASSOC);
$successRate = $overall['total_sent'] > 0 ? round(($overall['delivered'] / $overall['total_sent']) * 100, 1) : 0;
$sentThisMonth = $overall['sent_this_month'] ?: 0;

// Credit Stats across all tenants
$stmt = $pdo->query("SELECT 
    SUM(500) as total_platform_credits,
    SUM(sms_credits) as total_platform_remaining,
    COUNT(*) as total_tenants,
    SUM(CASE WHEN sms_credits < 100 THEN 1 ELSE 0 END) as low_credit_tenants
    FROM tenants
");
$creditStats = $stmt->fetch(PDO::FETCH_ASSOC);
$usedCredits = max(0, ($creditStats['total_platform_credits'] ?? 0) - ($creditStats['total_platform_remaining'] ?? 0));
$totalCredits = $creditStats['total_platform_credits'] ?: 1;
$lowCreditCount = $creditStats['low_credit_tenants'] ?: 0;

// Top Consumers
$stmt = $pdo->query("SELECT id, name, subdomain, sms_credits, 500 as total_credits, (500 - sms_credits) as used_credits FROM tenants ORDER BY used_credits DESC LIMIT 5");
$topConsumers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Low Credit Alert list
$stmt = $pdo->query("SELECT id, name, subdomain, sms_credits as rem, 500 as total FROM tenants WHERE sms_credits < 100 ORDER BY rem ASC");
$lowCredit = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dummy daily volume for the chart (could be live, but keeping array for chart visual demo)
$smsDaily = [28,31,24,35,42,38,29,33,41,45,38,32,28,36,44,48,42,35,31,38,52,41,39,45,50,43,38,41,47,44];

$pageTitle = 'SMS Credits';
$activePage = 'sms-credits.php';
?>

<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<main class="main" id="mainContent">
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
      <div class="stat-val"><?php echo number_format($sentThisMonth); ?></div>
      <div class="stat-lbl">SMS Sent This Month</div>
      <div class="stat-sub"><i class="fa fa-arrow-trend-up" style="color:#16a34a"></i> Based on logs</div>
    </div>
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-green"><i class="fa fa-circle-check"></i></div><span class="stat-badge bg-g">↑ <?php echo $successRate; ?>%</span></div>
      <div class="stat-val"><?php echo $successRate; ?>%</div>
      <div class="stat-lbl">Delivery Rate</div>
      <div class="stat-sub"><i class="fa fa-circle" style="color:#16a34a;font-size:7px"></i> Platform average</div>
    </div>
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-amber"><i class="fa fa-coins"></i></div><span class="stat-badge bg-y"><?php echo round(($usedCredits / $totalCredits) * 100); ?>% used</span></div>
      <div class="stat-val"><?php echo number_format($usedCredits); ?> / <?php echo number_format($totalCredits); ?></div>
      <div class="stat-lbl">Platform Credits Used/Total</div>
      <div style="margin-top:8px"><div class="prog-t" style="height:8px"><div class="prog-f" style="width:<?php echo round(($usedCredits / $totalCredits) * 100); ?>%;background:#d97706"></div></div></div>
    </div>
    <div class="card">
      <div class="stat-top"><div class="stat-icon-box ic-red"><i class="fa fa-triangle-exclamation"></i></div><span class="stat-badge bg-r">Action</span></div>
      <div class="stat-val"><?php echo number_format($lowCreditCount); ?></div>
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
      <div class="tbl-head"><div class="tbl-title"><i class="fa fa-triangle-exclamation"></i> Low Credit Alert</div><button class="btn btn-amber btn-sm" onclick="showToast('Sending credit alerts to low tenants...', 'info')"><i class="fa fa-bell"></i> Alert All</button></div>
      <div class="tbl-scroll"><table><thead><tr><th>Institute</th><th>Remaining</th><th>% Left</th><th>Action</th></tr></thead><tbody id="lowCreditTbl"></tbody></table></div>
    </div>
  </div>
</div>

<script>
(function() {
    const smsDaily = <?php echo json_encode($smsDaily); ?>;
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

    const topInst = <?php echo json_encode(array_map(function($t) {
        return [
            "name" => $t['name'],
            "code" => strtoupper(substr($t['subdomain'], 0, 3)),
            "used" => (int)$t['used_credits'],
            "total" => (int)$t['total_credits'],
            "col" => "ic-blue"
        ];
    }, $topConsumers)); ?>;

    const topSmsInstitutes = document.getElementById("topSmsInstitutes");
    if (topSmsInstitutes) {
        topSmsInstitutes.innerHTML = topInst.map(t => {
            const pct = Math.round((t.used / t.total) * 100);
            const bc = pct > 90 ? "#e11d48" : pct > 70 ? "#d97706" : "var(--green)";
            const codeLabel = t.code || t.name.substring(0,3).toUpperCase();
            return `<div class="sms-inst-row" style="display:flex;align-items:center;gap:10px;padding:12px;border-radius:10px;border:1px solid var(--card-border);margin-bottom:8px;transition:0.2s;background:#fff">
                <div class="sms-av ${t.col}" style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;flex-shrink:0;">${codeLabel}</div>
                <div style="flex:1">
                    <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:700;margin-bottom:4px"><span>${t.name}</span><span style="color:var(--text-light)">${t.used} / ${t.total}</span></div>
                    <div class="prog-t"><div class="prog-f" style="width:${pct}%;background:${bc}"></div></div>
                </div>
            </div>`;
        }).join("") || `<div style="padding:15px;text-align:center;color:var(--text-light);font-size:13px">No tenant SMS data</div>`;
    }

    const lowCreditTbl = document.getElementById("lowCreditTbl");
    if (lowCreditTbl) {
        const low = <?php echo json_encode(array_map(function($l) {
            return [
                "n" => $l['name'],
                "rem" => (int)$l['rem'],
                "total" => (int)$l['total']
            ];
        }, $lowCredit)); ?>;
        
        lowCreditTbl.innerHTML = low.map(l => {
            const pct = Math.round((l.rem / l.total) * 100);
            const c = pct < 5 ? "pr" : pct < 15 ? "py" : "pb";
            return `<tr><td style="font-weight:600;color:var(--text-dark)">${l.n}</td><td style="font-weight:700">${l.rem.toLocaleString()}</td><td><span class="pill ${c}">${pct}%</span></td><td><button class="btn btn-purple btn-sm" onclick="showToast('Opening top-up for ${l.n.replace(/'/g, "\\'")}...', 'info')"><i class="fa fa-plus"></i> Top Up</button></td></tr>`;
        }).join("") || `<tr><td colspan="4" style="text-align:center;color:var(--text-light);padding:15px;">No tenants with low credit.</td></tr>`;
    }
})();
</script>
</main>
<?php include 'footer.php'; ?>
