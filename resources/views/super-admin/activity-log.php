<?php
/**
 * Hamro ERP — Activity Log Page
 * Platform Blueprint V3.0
 * 
 * @module SuperAdmin
 */

require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pageTitle = 'Activity Log';
$activePage = 'activity-log.php';
?>

<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<main class="main" id="mainContent">
    <div class="page fu">
        <div class="page-head">
            <div class="page-title-row">
                <div class="page-icon" style="background:rgba(52,152,219,0.1); color:#3498db;">
                    <i class="fa-solid fa-list-ul"></i>
                </div>
                <div>
                    <div class="page-title">My Activity Log</div>
                    <div class="page-sub">Track your recent actions on the platform.</div>
                </div>
            </div>
        </div>

        <!-- FILTER OPTIONS -->
        <div class="card mb" style="padding:15px;">
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <div style="flex:1; min-width:250px; position:relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-light); font-size:12px;"></i>
                    <input type="text" placeholder="Search activities..." style="width:100%; border:1px solid var(--card-border); border-radius:8px; padding:10px 10px 10px 35px; font-size:13px; font-weight:500;">
                </div>
                <select style="border:1px solid var(--card-border); border-radius:8px; padding:0 12px; font-size:13px; font-weight:500; min-width:150px;">
                    <option>All Time</option>
                    <option>Last 7 Days</option>
                    <option>Last 30 Days</option>
                </select>
                <button class="btn bt" style="font-size:13px;" onclick="SuperAdmin.showNotification('Filters applied!', 'info')">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
            </div>
        </div>

        <!-- DATA TABLE -->
        <div class="card">
            <div class="tbl-wrap">
                <table style="width:100%;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="padding:14px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;">Action</th>
                            <th style="padding:14px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;">User</th>
                            <th style="padding:14px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;">Institute</th>
                            <th style="padding:14px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;">IP Address</th>
                            <th style="padding:14px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid var(--card-border);">
                            <td style="padding:14px 16px;font-size:13px;"><span style="background:#dbeafe;color:#3b82f6;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;">LOGIN</span></td>
                            <td style="padding:14px 16px;font-size:13px;font-weight:600;">admin@hamrolabs.edu.np</td>
                            <td style="padding:14px 16px;font-size:13px;color:var(--text-body);">Platform Admin</td>
                            <td style="padding:14px 16px;font-size:13px;color:var(--text-light);">192.168.1.100</td>
                            <td style="padding:14px 16px;font-size:13px;color:var(--text-light);">2024-01-15 09:23:45</td>
                        </tr>
                        <tr style="border-bottom:1px solid var(--card-border);">
                            <td style="padding:14px 16px;font-size:13px;"><span style="background:#dcfce7;color:#16a34a;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;">CREATE</span></td>
                            <td style="padding:14px 16px;font-size:13px;font-weight:600;">superadmin@hamrolabs.edu.np</td>
                            <td style="padding:14px 16px;font-size:13px;color:var(--text-body);">Global</td>
                            <td style="padding:14px 16px;font-size:13px;color:var(--text-light);">192.168.1.105</td>
                            <td style="padding:14px 16px;font-size:13px;color:var(--text-light);">2024-01-15 08:45:12</td>
                        </tr>
                        <tr style="border-bottom:1px solid var(--card-border);">
                            <td style="padding:14px 16px;font-size:13px;"><span style="background:#fef3c7;color:#d97706;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;">UPDATE</span></td>
                            <td style="padding:14px 16px;font-size:13px;font-weight:600;">admin@tsc.edu.np</td>
                            <td style="padding:14px 16px;font-size:13px;color:var(--text-body);">TSC Teachers Hub</td>
                            <td style="padding:14px 16px;font-size:13px;color:var(--text-light);">10.0.0.45</td>
                            <td style="padding:14px 16px;font-size:13px;color:var(--text-light);">2024-01-14 16:30:22</td>
                        </tr>
                        <tr style="border-bottom:1px solid var(--card-border);">
                            <td style="padding:14px 16px;font-size:13px;"><span style="background:#fee2e2;color:#dc2626;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;">DELETE</span></td>
                            <td style="padding:14px 16px;font-size:13px;font-weight:600;">superadmin@hamrolabs.edu.np</td>
                            <td style="padding:14px 16px;font-size:13px;color:var(--text-body);">Global</td>
                            <td style="padding:14px 16px;font-size:13px;color:var(--text-light);">192.168.1.105</td>
                            <td style="padding:14px 16px;font-size:13px;color:var(--text-light);">2024-01-14 14:15:08</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
