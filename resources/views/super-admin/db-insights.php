<?php
/**
 * Hamro ERP — Database Insights
 */
require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pageTitle = 'Database Insights';
$activePage = 'db-insights.php';
?>

<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<main class="main" id="mainContent">
  <div class="page fu">
    <div class="pg-hdr">
      <div class="pg-hdr-left">
        <div class="breadcrumb">
          <span class="bc-root">Dashboard</span>
          <span class="bc-sep">›</span>
          <span class="bc-cur">Database Insights</span>
        </div>
        <h1 style="display:flex; align-items:center; gap:10px;">
          <i class="fa fa-database" style="color:var(--green); font-size:1.1rem;"></i>
          Database Insights
        </h1>
        <p>Monitor database health, performance metrics, and active queries.</p>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <button class="btn bs" onclick="showToast('Exporting metrics...', 'info')"><i class="fa fa-download"></i> Export Report</button>
        <button class="btn bt" onclick="showToast('Running diagnostic...', 'info')"><i class="fa fa-play"></i> Run Diagnostic</button>
      </div>
    </div>

    <div class="stat-grid">
      <div class="card">
        <div class="stat-top"><div class="stat-icon-box ic-green"><i class="fa fa-server"></i></div><span class="stat-badge bg-g">Healthy</span></div>
        <div class="stat-val">99.9%</div>
        <div class="stat-lbl">Uptime</div>
        <div class="stat-sub"><i class="fa fa-check" style="color:#16a34a"></i> Normal operation</div>
      </div>
      <div class="card">
        <div class="stat-top"><div class="stat-icon-box ic-blue"><i class="fa fa-bolt"></i></div><span class="stat-badge bg-b">Avg</span></div>
        <div class="stat-val">12ms</div>
        <div class="stat-lbl">Query Latency</div>
        <div class="stat-sub"><i class="fa fa-arrow-trend-down" style="color:#16a34a"></i> Down 2ms today</div>
      </div>
      <div class="card">
        <div class="stat-top"><div class="stat-icon-box ic-purple"><i class="fa fa-hard-drive"></i></div><span class="stat-badge bg-p">Total</span></div>
        <div class="stat-val">42.5 GB</div>
        <div class="stat-lbl">Storage Used</div>
        <div class="stat-sub"><i class="fa fa-chart-pie" style="color:var(--purple)"></i> 34% of capacity</div>
      </div>
      <div class="card">
        <div class="stat-top"><div class="stat-icon-box ic-amber"><i class="fa fa-triangle-exclamation"></i></div><span class="stat-badge bg-y">Active</span></div>
        <div class="stat-val">0</div>
        <div class="stat-lbl">Slow Queries</div>
        <div class="stat-sub"><i class="fa fa-circle" style="color:#d97706;font-size:7px"></i> Last 24 hours</div>
      </div>
    </div>

    <!-- Active Connections & Queries -->
    <div class="g65" style="margin-top: 20px;">
      <div>
        <div class="card">
          <div class="ct"><i class="fa fa-bolt"></i> Active Connections</div>
          <div class="tbl-wrap" style="margin-bottom:0; box-shadow:none;">
            <table>
              <thead>
                <tr>
                  <th>PID</th>
                  <th>User</th>
                  <th>Host</th>
                  <th>DB</th>
                  <th>Command</th>
                  <th>Time</th>
                  <th>State</th>
                  <th>Info</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1423</td>
                  <td>db_admin</td>
                  <td>localhost</td>
                  <td>hamrolabs_db</td>
                  <td>Query</td>
                  <td>2</td>
                  <td>executing</td>
                  <td>SELECT * FROM tenants WHERE...</td>
                </tr>
                <tr>
                  <td>1425</td>
                  <td>db_admin</td>
                  <td>localhost</td>
                  <td>hamrolabs_db</td>
                  <td>Sleep</td>
                  <td>120</td>
                  <td>-</td>
                  <td>-</td>
                </tr>
                <tr>
                  <td>1428</td>
                  <td>db_admin</td>
                  <td>localhost</td>
                  <td>hamrolabs_db</td>
                  <td>Sleep</td>
                  <td>45</td>
                  <td>-</td>
                  <td>-</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div>
        <div class="card">
          <div class="ct"><i class="fa fa-chart-pie"></i> Storage Breakdown</div>
          <div style="padding: 10px 0;">
            <div style="margin-bottom: 12px">
              <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:600; margin-bottom:4px;">
                <span>User Data</span><span style="color:var(--text-light)">20 GB</span>
              </div>
              <div class="prog-t"><div class="prog-f" style="width:50%; background:var(--green)"></div></div>
            </div>
            <div style="margin-bottom: 12px">
              <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:600; margin-bottom:4px;">
                <span>Logs & Analytics</span><span style="color:var(--text-light)">15 GB</span>
              </div>
              <div class="prog-t"><div class="prog-f" style="width:35%; background:var(--blue)"></div></div>
            </div>
            <div style="margin-bottom: 12px">
              <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:600; margin-bottom:4px;">
                <span>Indexes</span><span style="color:var(--text-light)">7.5 GB</span>
              </div>
              <div class="prog-t"><div class="prog-f" style="width:15%; background:var(--purple)"></div></div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <script>
    function showToast(msg, type) { 
        const cols = {info: 'var(--blue)', success: 'var(--green)', error: 'var(--red)'};
        const col = cols[type] || 'var(--green)';
        const t=document.createElement('div'); 
        t.style.cssText=`position:fixed;bottom:24px;right:24px;background:#1E293B;color:#fff;padding:12px 20px;border-radius:10px;font-size:13px;z-index:9999;box-shadow:0 10px 30px rgba(0,0,0,0.2);`; 
        t.innerHTML=`<i class="fa fa-info-circle" style="color:${col};margin-right:8px;"></i>${msg}`; 
        document.body.appendChild(t); 
        setTimeout(()=>t.remove(),3000); 
    }
  </script>
</main>
<?php include 'footer.php'; ?>
