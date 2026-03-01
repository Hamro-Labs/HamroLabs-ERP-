<?php
/**
 * Hamro ERP — Support Tickets Page
 * Platform Blueprint V3.0
 * 
 * @module SuperAdmin
 */

require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pageTitle = 'Support Tickets';
$activePage = 'support-tickets.php';
?>

<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<main class="main" id="mainContent">
    <div class="page fu">
        <div class="pg-hdr">
            <div class="pg-hdr-left">
                <div class="breadcrumb">
                    <span class="bc-root">Dashboard</span>
                    <span class="bc-sep">›</span>
                    <span class="bc-cur">Support Tickets</span>
                </div>
                <h1 style="display:flex; align-items:center; gap:10px;">
                    <i class="fa fa-ticket" style="color:var(--green); font-size:1.1rem;"></i>
                    Support Tickets
                </h1>
                <p>Manage institute support requests and impersonation sessions</p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button class="btn bs" onclick="SuperAdmin.showNotification('Exporting tickets...', 'info')">
                    <i class="fa fa-download"></i> Export
                </button>
                <button class="btn bt" onclick="SuperAdmin.showNotification('Creating new ticket...', 'info')">
                    <i class="fa fa-plus"></i> New Ticket
                </button>
            </div>
        </div>

        <div class="stat-grid">
            <div class="card">
                <div class="stat-top">
                    <div class="stat-icon-box ic-red"><i class="fa fa-circle-exclamation"></i></div>
                    <span class="stat-badge bg-r">Needs attention</span>
                </div>
                <div class="stat-val" id="openTicketsVal">12</div>
                <div class="stat-lbl">Open Tickets</div>
                <div class="stat-sub">
                    <i class="fa fa-triangle-exclamation" style="color:#e11d48"></i> 2 high priority
                </div>
            </div>
            <div class="card">
                <div class="stat-top">
                    <div class="stat-icon-box ic-amber"><i class="fa fa-clock"></i></div>
                    <span class="stat-badge bg-y">Avg</span>
                </div>
                <div class="stat-val">3.2h</div>
                <div class="stat-lbl">Avg First Response Time</div>
                <div class="stat-sub">
                    <i class="fa fa-arrow-trend-down" style="color:#16a34a"></i> Down from 4.8h
                </div>
            </div>
            <div class="card">
                <div class="stat-top">
                    <div class="stat-icon-box ic-green"><i class="fa fa-circle-check"></i></div>
                    <span class="stat-badge bg-g">This month</span>
                </div>
                <div class="stat-val">156</div>
                <div class="stat-lbl">Resolved Tickets</div>
                <div class="stat-sub">
                    <i class="fa fa-arrow-trend-up" style="color:#16a34a"></i> 23% increase
                </div>
            </div>
        </div>

        <!-- Tickets Table -->
        <div class="card">
            <div class="tbl-head">
                <div class="tbl-title"><i class="fa fa-list"></i> All Tickets</div>
                <select class="filter-sel">
                    <option>All Status</option>
                    <option>Open</option>
                    <option>In Progress</option>
                    <option>Resolved</option>
                </select>
            </div>
            <table>
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;">Ticket ID</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;">Subject</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;">Institute</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;">Priority</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;">Status</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;">Created</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom:1px solid var(--card-border);">
                        <td style="padding:12px 16px;font-size:13px;font-weight:600;">#TKT-001</td>
                        <td style="padding:12px 16px;font-size:13px;">Unable to export student data</td>
                        <td style="padding:12px 16px;font-size:13px;">TSC Teachers Hub</td>
                        <td style="padding:12px 16px;"><span style="background:#fee2e2;color:#dc2626;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:600;">High</span></td>
                        <td style="padding:12px 16px;"><span style="background:#dbeafe;color:#2563eb;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:600;">Open</span></td>
                        <td style="padding:12px 16px;font-size:12px;color:var(--text-light);">2024-01-15</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--card-border);">
                        <td style="padding:12px 16px;font-size:13px;font-weight:600;">#TKT-002</td>
                        <td style="padding:12px 16px;font-size:13px;">Payment gateway integration issue</td>
                        <td style="padding:12px 16px;font-size:13px;">Kharidar Study Hub</td>
                        <td style="padding:12px 16px;"><span style="background:#fef3c7;color:#d97706;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:600;">Medium</span></td>
                        <td style="padding:12px 16px;"><span style="background:#fef3c7;color:#d97706;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:600;">In Progress</span></td>
                        <td style="padding:12px 16px;font-size:12px;color:var(--text-light);">2024-01-14</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--card-border);">
                        <td style="padding:12px 16px;font-size:13px;font-weight:600;">#TKT-003</td>
                        <td style="padding:12px 16px;font-size:13px;">Request for additional storage</td>
                        <td style="padding:12px 16px;font-size:13px;">Nayab Subba Academy</td>
                        <td style="padding:12px 16px;"><span style="background:#dcfce7;color:#16a34a;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:600;">Low</span></td>
                        <td style="padding:12px 16px;"><span style="background:#dcfce7;color:#16a34a;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:600;">Resolved</span></td>
                        <td style="padding:12px 16px;font-size:12px;color:var(--text-light);">2024-01-13</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
