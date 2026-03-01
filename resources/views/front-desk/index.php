<?php
/**
 * Hamro ERP — Front Desk Dashboard
 * Seamless Single Page Application (SPA) entry point
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../../config/config.php';
}

$pageTitle = 'Front Desk Dashboard';
require_once VIEWS_PATH . '/layouts/header_1.php';
require_once __DIR__ . '/sidebar.php';

// Render base layout
renderFrontDeskHeader();
renderFrontDeskSidebar('index');
?>

<!-- ── MAIN CONTENT Shell ── -->
<main class="main" id="mainContent">
    <div class="pg fu">
        <div class="pg-loading">
            <i class="fa-solid fa-circle-notch fa-spin"></i>
            <span>Initializing Operations...</span>
        </div>
    </div>
</main>

<script src="<?php echo APP_URL; ?>/public/assets/js/frontdesk.js"></script>
<style>
/* ── SEARCH OVERLAY STYLES ── */
.search-results-overlay {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    margin-top: 10px;
    max-height: 400px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    border: 1px solid #e2e8f0;
}
.search-results-list { padding: 8px 0; }
.search-cat {
    font-size: 11px;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    padding: 12px 16px 6px;
    letter-spacing: 0.5px;
}
.search-res-item {
    padding: 10px 16px;
    cursor: pointer;
    transition: all 0.2s;
    border-left: 3px solid transparent;
}
.search-res-item:hover {
    background: #f8fafc;
    border-left-color: #6C5CE7;
}
.res-main {
    font-size: 14px;
    font-weight: 600;
    color: #1a1a2e;
}
.res-sub {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}
.search-no-results {
    padding: 24px;
    text-align: center;
    color: #94a3b8;
    font-size: 13px;
}
</style>
<?php
// Include necessary CSS/JS from layout
renderSuperAdminCSS();
?>
</body>
</html>
<?php exit; // End of shell ?>
