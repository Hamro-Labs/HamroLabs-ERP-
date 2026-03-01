<?php
/**
 * Front Desk — Header (Refactored to match Institute Admin)
 * White background, centered search, glassmorphism dropdown
 */
$user = getCurrentUser();
$tenantName = $_SESSION['tenant_name'] ?? 'Institute';
$tenantId = $_SESSION['tenant_id'] ?? null;

// User initials
$initials = 'FD';
if ($user && isset($user['name'])) {
    $parts = explode(' ', $user['name']);
    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}

// Institute logo and name - always fetch from tenants table
$logoUrl = null;
$tenantLogo = $_SESSION['institute_logo'] ?? $_SESSION['tenant_logo'] ?? null;

if ($tenantId) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT name, logo_path FROM tenants WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $tenantId]);
        $tenant = $stmt->fetch();
        if ($tenant) {
            // Always use tenant name from database
            if (!empty($tenant['name'])) {
                $tenantName = $tenant['name'];
                $_SESSION['tenant_name'] = $tenantName;
            }
            // Get logo if available
            if (empty($tenantLogo) && !empty($tenant['logo_path'])) {
                $tenantLogo = $tenant['logo_path'];
                $_SESSION['tenant_logo'] = $tenantLogo;
                $_SESSION['institute_logo'] = $tenantLogo;
            }
        }
    } catch (Exception $e) {}
}

if (!empty($tenantLogo)) {
    $logoUrl = (strpos($tenantLogo, 'http') === 0) ? $tenantLogo : APP_URL . $tenantLogo;
}

// Notification count
$notificationCount = 0;
try {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE tenant_id = :tid AND user_id = :uid AND is_read = 0");
    $stmt->execute(['tid' => $_SESSION['userData']['tenant_id'] ?? 0, 'uid' => $_SESSION['userData']['id'] ?? null]);
    $notificationCount = (int) $stmt->fetchColumn();
} catch (Exception $e) {}
?>

<style>
/* ── HEADER (Matching Institute Admin) ── */
:root {
    --header-height: 60px;
    --header-bg: #ffffff;
    --header-border: #e5e7eb;
    --primary: #009E7E;
    --primary-dark: #008F6E;
    --hdr-text-dark: #1f2937;
    --hdr-text-light: #6b7280;
    --hdr-bg-light: #f3f4f6;
    --hdr-shadow: 0 2px 10px rgba(0,0,0,0.1);
    --hdr-shadow-lg: 0 10px 40px rgba(0,0,0,0.15);
}

.header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: var(--header-height);
    background: var(--header-bg);
    border-bottom: 1px solid var(--header-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 16px;
    z-index: 1000;
    box-shadow: var(--hdr-shadow);
}

/* Left Section */
.header-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 0 0 auto;
}

/* Animated Hamburger */
.menu-toggle {
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 5px;
    border-radius: 8px;
    transition: background 0.2s;
}

.menu-toggle:hover {
    background: var(--hdr-bg-light);
}

.menu-toggle span {
    display: block;
    width: 22px;
    height: 2px;
    background: var(--hdr-text-dark);
    transition: all 0.3s;
    border-radius: 2px;
}

.menu-toggle.active span:nth-child(1) {
    transform: rotate(45deg) translate(5px, 5px);
}

.menu-toggle.active span:nth-child(2) {
    opacity: 0;
}

.menu-toggle.active span:nth-child(3) {
    transform: rotate(-45deg) translate(5px, -5px);
}

/* Institute Name Pill */
.institute-name {
    font-size: clamp(12px, 3vw, 15px);
    font-weight: 700;
    color: #ffffff;
    background: var(--primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
    padding: 5px 14px;
    border-radius: 9999px;
}

@media (min-width: 768px) { .institute-name { max-width: 250px; } }
@media (min-width: 1024px) { .institute-name { max-width: 350px; } }

/* Center - Search */
.header-center {
    flex: 1 1 auto;
    display: flex;
    justify-content: center;
    padding: 0 16px;
    max-width: 400px;
}

.search-box {
    position: relative;
    width: 100%;
    max-width: 300px;
}

.search-box input {
    width: 100%;
    height: 38px;
    padding: 0 12px 0 38px;
    border: 1px solid var(--header-border);
    border-radius: 20px;
    font-size: 14px;
    background: var(--hdr-bg-light);
    transition: all 0.2s;
    font-family: var(--font);
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 3px rgba(0, 158, 126, 0.1);
}

.search-box > i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--hdr-text-light);
    font-size: 14px;
}

.fd-search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 4px;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: var(--hdr-shadow-lg);
    border: 1px solid #e5e7eb;
    max-height: 380px;
    overflow-y: auto;
    z-index: 1100;
    font-size: 13px;
    display: none;
}

.fd-search-section { padding: 6px 10px; font-weight: 600; font-size: 11px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #f3f4f6; background: #f9fafb; }
.fd-search-item { padding: 8px 10px; cursor: pointer; display: flex; flex-direction: column; gap: 2px; }
.fd-search-item:hover { background: #f3f4f6; }
.fd-search-main { display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #111827; }
.fd-search-meta { font-size: 11px; color: #6b7280; }

/* Right Section */
.header-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 0 0 auto;
}

/* Quick Action Buttons */
.fd-quick-actions {
    display: none;
    align-items: center;
    gap: 4px;
}

@media (min-width: 1024px) {
    .fd-quick-actions { display: flex; }
}

.btn-icon {
    width: 36px;
    height: 36px;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--hdr-text-light);
    font-size: 16px;
    position: relative;
    transition: all 0.2s;
}

.btn-icon:hover {
    background: var(--hdr-bg-light);
    color: var(--primary);
}

.btn-icon .badge {
    position: absolute;
    top: 2px;
    right: 2px;
    background: #ef4444;
    color: white;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 5px;
    border-radius: 10px;
    min-width: 16px;
    text-align: center;
}

/* Profile Button */
.profile-section { position: relative; }

.profile-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    border-radius: 50%;
    cursor: pointer;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: box-shadow 0.2s;
}

.profile-btn:hover { box-shadow: 0 0 0 3px rgba(0, 158, 126, 0.2); }

.profile-btn img { width: 100%; height: 100%; object-fit: cover; }

.profile-initials {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

/* Glassmorphism Dropdown */
.fd-dropdown {
    position: absolute;
    top: calc(100% + 12px);
    right: 0;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-radius: 18px;
    box-shadow: 0 15px 50px rgba(0,0,0,0.12);
    min-width: 260px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-15px) scale(0.95);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    z-index: 1001;
    border: 1px solid rgba(255, 255, 255, 0.4);
    overflow: hidden;
}

.fd-dropdown.active {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

.fd-dd-header {
    padding: 20px;
    background: linear-gradient(135deg, rgba(0, 158, 126, 0.08) 0%, rgba(255, 255, 255, 0) 100%);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    gap: 12px;
}

.fd-dd-header .u-av-lg {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 158, 126, 0.2);
}

.fd-dd-header .user-meta { flex: 1; overflow: hidden; }
.fd-dd-header .name { font-weight: 700; color: var(--hdr-text-dark); font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.fd-dd-header .role { font-size: 11px; color: var(--hdr-text-light); margin-top: 2px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }

.fd-dd-menu { list-style: none; padding: 10px; margin: 0; }

.fd-dd-menu li a {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 14px;
    color: var(--hdr-text-dark);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    border-radius: 10px;
}

.fd-dd-menu li a:hover {
    background: rgba(0, 158, 126, 0.08);
    color: var(--primary);
    transform: translateX(4px);
}

.fd-dd-menu li a i {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    border-radius: 8px;
    color: var(--hdr-text-light);
    font-size: 12px;
    transition: all 0.2s;
}

.fd-dd-menu li a:hover i {
    background: var(--primary);
    color: white;
    transform: scale(1.1);
}

.fd-dd-divider { height: 1px; background: rgba(0, 0, 0, 0.05); margin: 8px 10px; }

.fd-dd-menu li a.logout { color: #ef4444; }
.fd-dd-menu li a.logout i { background: #fef2f2; color: #ef4444; }
.fd-dd-menu li a.logout:hover { background: #fef2f2; color: #dc2626; }
.fd-dd-menu li a.logout:hover i { background: #dc2626; color: white; }

/* Tooltip */
[data-tooltip] { position: relative; }
[data-tooltip]::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: calc(100% + 8px);
    left: 50%;
    transform: translateX(-50%) scale(0.8);
    background: var(--hdr-text-dark);
    color: white;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 12px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s;
    pointer-events: none;
}
[data-tooltip]:hover::after { opacity: 1; visibility: visible; transform: translateX(-50%) scale(1); }

/* Notifications Panel (Slide-in) */
.fd-notif-panel {
    position: fixed;
    top: var(--header-height);
    right: -100%;
    width: 100%;
    max-width: 380px;
    height: calc(100vh - var(--header-height));
    background: white;
    box-shadow: var(--hdr-shadow-lg);
    transition: right 0.3s;
    z-index: 1001;
    display: flex;
    flex-direction: column;
}

.fd-notif-panel.active { right: 0; }

.fd-panel-header {
    padding: 16px;
    border-bottom: 1px solid var(--header-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.fd-panel-header h3 { font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px; }

.fd-panel-body { flex: 1; overflow-y: auto; padding: 16px; }

.fd-notif-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 8px;
    transition: background 0.2s;
    cursor: pointer;
}

.fd-notif-item:hover { background: #f9fafb; }
.fd-notif-item.unread { background: #f0fdf4; }

.fd-notif-ico {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    flex-shrink: 0;
}

.fd-notif-content { flex: 1; }
.fd-notif-title { font-size: 13px; font-weight: 700; color: var(--hdr-text-dark); }
.fd-notif-text { font-size: 12px; color: var(--hdr-text-light); margin-top: 2px; }
.fd-notif-time { font-size: 11px; color: #94a3b8; margin-top: 4px; }

/* Mobile */
.fd-mobile-actions { display: flex; gap: 4px; }
@media (min-width: 768px) { .fd-mobile-actions { display: none; } }
@media (max-width: 767px) { .header-center { display: none; } }

/* Mobile Search Overlay */
.fd-mobile-search {
    position: fixed;
    top: var(--header-height);
    left: 0;
    right: 0;
    background: white;
    padding: 16px;
    border-bottom: 1px solid var(--header-border);
    transform: translateY(-100%);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
    z-index: 998;
}

.fd-mobile-search.active { transform: translateY(0); opacity: 1; visibility: visible; }

.fd-mobile-search input {
    width: 100%;
    height: 44px;
    padding: 0 16px;
    border: 2px solid var(--primary);
    border-radius: 8px;
    font-size: 16px;
    font-family: var(--font);
}
</style>

<header class="header">
    <!-- Left Section -->
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="institute-name" title="<?= htmlspecialchars($tenantName) ?>">
            <?= htmlspecialchars($tenantName) ?>
        </div>
    </div>

    <!-- Center - Search -->
    <div class="header-center">
        <div class="search-box">
            <i class="fa-solid fa-search"></i>
            <input type="text" id="fdGlobalSearch" placeholder="Search students..." autocomplete="off">
            <div id="fdSearchResults" class="fd-search-results"></div>
        </div>
    </div>

    <!-- Right Section -->
    <div class="header-right">
        <!-- Mobile Search Toggle -->
        <button class="btn-icon fd-mobile-actions" id="fdMobileSearchToggle" data-tooltip="Search">
            <i class="fa-solid fa-search"></i>
        </button>

        <!-- Quick Actions (Desktop) -->
        <div class="fd-quick-actions">
            <button class="btn-icon" onclick="goNav('admissions', 'adm-form')" data-tooltip="Quick Admission">
                <i class="fa-solid fa-user-plus"></i>
            </button>
            <button class="btn-icon" onclick="goNav('fee', 'fee-coll')" data-tooltip="Collect Fee">
                <i class="fa-solid fa-money-bill"></i>
            </button>
            <button class="btn-icon" onclick="window.location.reload()" data-tooltip="Refresh">
                <i class="fa-solid fa-rotate-right"></i>
            </button>
            <button class="btn-icon" id="fdNotifToggle" data-tooltip="Notifications">
                <i class="fa-solid fa-bell"></i>
                <?php if ($notificationCount > 0): ?>
                    <span class="badge" id="fdNotifBadge"><?= $notificationCount ?></span>
                <?php endif; ?>
            </button>
        </div>

        <!-- Profile Button -->
        <div class="profile-section">
            <button class="profile-btn" id="fdProfileToggle" aria-label="Profile Menu" title="<?= htmlspecialchars($tenantName) ?>">
                <?php if (!empty($logoUrl)): ?>
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($tenantName) ?>"
                         onerror="this.style.display='none'; this.parentElement.querySelector('.profile-initials').style.display='flex';">
                    <div class="profile-initials" style="display:none;"><?= strtoupper(substr($tenantName, 0, 1)) ?></div>
                <?php else: ?>
                    <div class="profile-initials"><?= $initials ?></div>
                <?php endif; ?>
            </button>

            <!-- Profile Dropdown (Glassmorphism) -->
            <div class="fd-dropdown" id="fdProfileDropdown">
                <div class="fd-dd-header">
                    <div class="u-av-lg"><?= $initials ?></div>
                    <div class="user-meta">
                        <div class="name"><?= htmlspecialchars($user['name'] ?? 'Front Desk') ?></div>
                        <div class="role">Front Desk Operator</div>
                    </div>
                </div>
                <ul class="fd-dd-menu">
                    <li>
                        <a href="#" onclick="goNav('settings', 'profile'); return false;">
                            <i class="fa-regular fa-circle-user"></i>
                            My Profile
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="goNav('settings', 'password'); return false;">
                            <i class="fa-solid fa-shield-halved"></i>
                            Account Security
                        </a>
                    </li>
                    <li class="fd-dd-divider"></li>
                    <li>
                        <a href="<?= APP_URL ?>/logout" class="logout">
                            <i class="fa-solid fa-power-off"></i>
                            Sign Out
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Search Overlay -->
<div class="fd-mobile-search" id="fdMobileSearch">
    <input type="text" id="fdMobileSearchInput" placeholder="Search students..." autocomplete="off">
</div>

<!-- Notifications Panel (Slide-in) -->
<div class="fd-notif-panel" id="fdNotifPanel">
    <div class="fd-panel-header">
        <h3><i class="fa-solid fa-bell"></i> Notifications</h3>
        <button class="btn-icon" id="fdCloseNotifPanel">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <div class="fd-panel-body" id="fdNotifBody">
        <div style="padding: 20px; text-align: center; color: var(--hdr-text-light);">
            <i class="fa-solid fa-bell" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
            <p>No new notifications</p>
        </div>
    </div>
</div>

<!-- Spacer for fixed header -->
<div style="height: var(--header-height);"></div>

<script>
// Global app configuration
window.APP_CONFIG = window.APP_CONFIG || {};
window.APP_CONFIG.tenantName = <?= json_encode($tenantName) ?>;

(function() {
    const menuToggle = document.getElementById('menuToggle');
    const profileToggle = document.getElementById('fdProfileToggle');
    const profileDropdown = document.getElementById('fdProfileDropdown');
    const mobileSearchToggle = document.getElementById('fdMobileSearchToggle');
    const mobileSearch = document.getElementById('fdMobileSearch');
    const notifToggle = document.getElementById('fdNotifToggle');
    const notifPanel = document.getElementById('fdNotifPanel');
    const closeNotifPanel = document.getElementById('fdCloseNotifPanel');

    // Toggle Sidebar
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            document.body.classList.toggle('sb-active');
            const sbOverlay = document.getElementById('sbOverlay');
            if (sbOverlay) sbOverlay.classList.toggle('active');
        });
    }

    // Profile Dropdown
    if (profileToggle) {
        profileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
        });
        document.addEventListener('click', function(e) {
            if (!profileDropdown.contains(e.target) && !profileToggle.contains(e.target)) {
                profileDropdown.classList.remove('active');
            }
        });
    }

    // Mobile Search
    if (mobileSearchToggle && mobileSearch) {
        mobileSearchToggle.addEventListener('click', function() {
            mobileSearch.classList.toggle('active');
            if (mobileSearch.classList.contains('active')) {
                document.getElementById('fdMobileSearchInput').focus();
            }
        });
    }

    // Notifications Panel
    if (notifToggle && notifPanel) {
        notifToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            notifPanel.classList.toggle('active');
            loadNotifications();
        });
    }
    if (closeNotifPanel) {
        closeNotifPanel.addEventListener('click', function() {
            notifPanel.classList.remove('active');
        });
    }

    // Live Search
    const searchInput = document.getElementById('fdGlobalSearch');
    const searchResults = document.getElementById('fdSearchResults');
    let searchTimeout = null;

    function hideSearch() {
        if (searchResults) { searchResults.style.display = 'none'; searchResults.innerHTML = ''; }
    }

    function doSearch(query) {
        query = (query || '').trim();
        if (query.length < 2) { hideSearch(); return; }

        fetch('<?= APP_URL ?>/api/frontdesk/students?q=' + encodeURIComponent(query))
            .then(r => r.json())
            .then(result => {
                if (!result.success || !result.data || result.data.length === 0) {
                    searchResults.innerHTML = '<div class="fd-search-item"><div class="fd-search-main">No matches for "<strong>' + query + '</strong>"</div></div>';
                    searchResults.style.display = 'block';
                    return;
                }
                searchResults.innerHTML = '<div class="fd-search-section">Students</div>' +
                    result.data.map(s => `
                        <div class="fd-search-item" onclick="goNav('admissions','adm-all')">
                            <div class="fd-search-main"><span>${s.full_name || s.name}</span></div>
                            <div class="fd-search-meta">${s.roll_no ? 'Roll: ' + s.roll_no + ' • ' : ''}${s.phone || s.email || ''}</div>
                        </div>
                    `).join('');
                searchResults.style.display = 'block';
            })
            .catch(() => hideSearch());
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            if (searchTimeout) clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => doSearch(this.value), 250);
        });
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') hideSearch();
        });
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) hideSearch();
        });
    }

    // Load notifications
    function loadNotifications() {
        const body = document.getElementById('fdNotifBody');
        body.innerHTML = '<div style="padding:40px; text-align:center; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin" style="font-size:24px;"></i><p style="margin-top:10px;">Loading...</p></div>';

        fetch('<?= APP_URL ?>/api/frontdesk/notifications')
            .then(r => r.json())
            .then(result => {
                if (!result.success || !result.data || result.data.length === 0) {
                    body.innerHTML = '<div style="padding:40px; text-align:center; color:#94a3b8;"><i class="fa-solid fa-bell" style="font-size:48px; margin-bottom:16px; opacity:0.3;"></i><p>No new notifications</p></div>';
                    return;
                }
                body.innerHTML = result.data.map(n => `
                    <div class="fd-notif-item ${n.is_read ? '' : 'unread'}">
                        <div class="fd-notif-ico" style="background:${n.color || '#10B981'};"><i class="fa-solid ${n.icon || 'fa-bell'}"></i></div>
                        <div class="fd-notif-content">
                            <div class="fd-notif-title">${n.title}</div>
                            <div class="fd-notif-text">${n.message}</div>
                            <div class="fd-notif-time">${n.time_ago || ''}</div>
                        </div>
                    </div>
                `).join('');
            })
            .catch(() => {
                body.innerHTML = '<div style="padding:40px; text-align:center; color:#94a3b8;"><i class="fa-solid fa-bell" style="font-size:48px; margin-bottom:16px; opacity:0.3;"></i><p>No new notifications</p></div>';
            });
    }
})();
</script>
