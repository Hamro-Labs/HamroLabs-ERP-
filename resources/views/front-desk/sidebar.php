<?php
/**
 * Hamro ERP — Front Desk Sidebar Component
 * Modern, elegant navigation for front desk operators
 * Follows super admin styling pattern
 */

/**
 * Render the top header bar for Front Desk module
 */
function renderFrontDeskHeader() {
    if (isset($_GET['partial']) && $_GET['partial'] == 'true') return;
    require __DIR__ . '/header.php';
}

function getFrontDeskMenu() {
    return [
        'overview' => [
            'title' => 'Dashboard',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'icon'  => 'fa-house',
                    'href'  => APP_URL . '/dash/front-desk/index',
                ]
            ]
        ],
        'operations' => [
            'title' => 'Operations',
            'items' => [
                [
                    'label'       => 'Inquiries',
                    'icon'        => 'fa-magnifying-glass',
                    'href'        => '#',
                    'has_submenu' => true,
                    'submenu_id'  => 'inquiries',
                    'submenu'     => [
                        ['label' => 'Inquiry List',        'href' => APP_URL . '/dash/front-desk/inquiries'],
                        ['label' => 'Add New Inquiry',      'href' => APP_URL . '/dash/front-desk/inquiry-add'],
                        ['label' => 'Follow-Up Reminders',  'href' => APP_URL . '/dash/front-desk/inquiry-followup'],
                        ['label' => 'Conversion Report',    'href' => APP_URL . '/dash/front-desk/inquiry-report'],
                    ]
                ],
                [
                    'label'       => 'Admissions',
                    'icon'        => 'fa-user-graduate',
                    'href'        => '#',
                    'has_submenu' => true,
                    'submenu_id'  => 'admissions',
                    'submenu'     => [
                        ['label' => 'New Admission',           'href' => APP_URL . '/dash/front-desk/admission-form'],
                        ['label' => 'All Students',            'href' => APP_URL . '/dash/front-desk/students'],
                        ['label' => 'ID Card Generator',       'href' => APP_URL . '/dash/front-desk/id-cards'],
                        ['label' => 'Document Verification',  'href' => APP_URL . '/dash/front-desk/documents'],
                    ]
                ]
            ]
        ],
        'billing' => [
            'title' => 'Billing',
            'items' => [
                [
                    'label'       => 'Fee Collection',
                    'icon'        => 'fa-money-bill-wave',
                    'href'        => '#',
                    'has_submenu' => true,
                    'submenu_id'  => 'fees',
                    'submenu'     => [
                        ['label' => 'Collect Payment',        'href' => APP_URL . '/dash/front-desk/fee-collect'],
                        ['label' => 'Outstanding Dues',       'href' => APP_URL . '/dash/front-desk/fee-outstanding'],
                        ['label' => 'Receipt History',       'href' => APP_URL . '/dash/front-desk/fee-receipts'],
                        ['label' => 'Daily Summary',          'href' => APP_URL . '/dash/front-desk/fee-daily'],
                    ]
                ]
            ]
        ],
        'academic' => [
            'title' => 'Academic',
            'items' => [
                [
                    'label'       => 'Batches',
                    'icon'        => 'fa-users-rectangle',
                    'href'        => '#',
                    'has_submenu' => true,
                    'submenu_id'  => 'batches',
                    'submenu'     => [
                        ['label' => 'All Batches',         'href' => APP_URL . '/dash/front-desk/batches'],
                        ['label' => 'Batch Availability',  'href' => APP_URL . '/dash/front-desk/batch-status'],
                        ['label' => 'Seat Allocation',      'href' => APP_URL . '/dash/front-desk/seat-allocation'],
                    ]
                ],
                [
                    'label'       => 'Attendance',
                    'icon'        => 'fa-clipboard-check',
                    'href'        => '#',
                    'has_submenu' => true,
                    'submenu_id'  => 'attendance',
                    'submenu'     => [
                        ['label' => 'Mark Attendance',    'href' => APP_URL . '/dash/front-desk/attendance-mark'],
                        ['label' => 'Attendance Report', 'href' => APP_URL . '/dash/front-desk/attendance-report'],
                    ]
                ]
            ]
        ],
        'library' => [
            'title' => 'Library',
            'items' => [
                [
                    'label'       => 'Library',
                    'icon'        => 'fa-book',
                    'href'        => '#',
                    'has_submenu' => true,
                    'submenu_id'  => 'library',
                    'submenu'     => [
                        ['label' => 'Issue Book',     'href' => APP_URL . '/dash/front-desk/book-issue'],
                        ['label' => 'Return Book',   'href' => APP_URL . '/dash/front-desk/book-return'],
                        ['label' => 'Overdue List',  'href' => APP_URL . '/dash/front-desk/book-overdue'],
                    ]
                ]
            ]
        ],
        'communication' => [
            'title' => 'Communication',
            'items' => [
                [
                    'label'       => 'Notifications',
                    'icon'        => 'fa-paper-plane',
                    'href'        => '#',
                    'has_submenu' => true,
                    'submenu_id'  => 'notifications',
                    'submenu'     => [
                        ['label' => 'Send SMS',             'href' => APP_URL . '/dash/front-desk/sms-send'],
                        ['label' => 'Send Email',           'href' => APP_URL . '/dash/front-desk/email-send'],
                        ['label' => 'Notification History', 'href' => APP_URL . '/dash/front-desk/notifications'],
                    ]
                ]
            ]
        ],
        'reports' => [
            'title' => 'Reports',
            'items' => [
                [
                    'label'       => 'Reports',
                    'icon'        => 'fa-chart-bar',
                    'href'        => '#',
                    'has_submenu' => true,
                    'submenu_id'  => 'reports',
                    'submenu'     => [
                        ['label' => 'Daily Report',        'href' => APP_URL . '/dash/front-desk/report-daily'],
                        ['label' => 'Revenue Report',       'href' => APP_URL . '/dash/front-desk/report-revenue'],
                        ['label' => 'Enrollment Report',    'href' => APP_URL . '/dash/front-desk/report-enrollment'],
                        ['label' => 'Fee Collection Report', 'href' => APP_URL . '/dash/front-desk/report-fees'],
                    ]
                ]
            ]
        ],
        'settings' => [
            'title' => 'Settings',
            'items' => [
                [
                    'label'       => 'Settings',
                    'icon'        => 'fa-gear',
                    'href'        => '#',
                    'has_submenu' => true,
                    'submenu_id'  => 'settings',
                    'submenu'     => [
                        ['label' => 'My Profile',      'href' => APP_URL . '/dash/front-desk/profile'],
                        ['label' => 'Change Password', 'href' => APP_URL . '/dash/front-desk/password'],
                    ]
                ]
            ]
        ]
    ];
}

function getCurrentPage() {
    return basename($_SERVER['PHP_SELF']);
}

function renderFrontDeskSidebar($activePage = null) {
    if (isset($_GET['partial']) && $_GET['partial'] == 'true') return;
    $menu        = getFrontDeskMenu();
    $currentFile = $activePage ?? getCurrentPage();
    
    // Support both ?page= (JavaScript) and ?nav=/ ?sub= (legacy) parameters
    $pageParam = $_GET['page'] ?? '';
    if (!empty($pageParam)) {
        $parts = explode('-', $pageParam, 2);
        $currentNav = $parts[0] ?? '';
        $currentSub = $parts[1] ?? '';
    } else {
        $currentNav  = $_GET['nav'] ?? '';
        $currentSub  = $_GET['sub'] ?? '';
    }

    // Resolve active states
    foreach ($menu as &$section) {
        foreach ($section['items'] as &$item) {
            $itemHref = basename(strtok($item['href'], '?'));
            if ($itemHref === $currentFile) {
                $item['active'] = true;
            }
            if (isset($item['submenu'])) {
                foreach ($item['submenu'] as $sub) {
                    if (basename(strtok($sub['href'] ?? '', '?')) === $currentFile) {
                        $item['active']       = true;
                        $item['submenu_open'] = true;
                    }
                }
            }
        }
    }
    unset($section, $item);
    ?>
    <!-- ── FRONT DESK SIDEBAR ── -->
    <nav class="sb" id="sidebar">
        <!-- Mobile-only header inside sidebar -->
        <div class="sb-header" style="padding: 16px 20px; display: flex; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <img src="<?php echo APP_URL; ?>/public/assets/images/logo.png" alt="Logo" style="height:28px; width:auto; margin-right:10px; filter: brightness(0) invert(1);">
            <div class="logo-txt" style="font-size:14px; font-weight:800; color:#fff; letter-spacing:0.5px;">FRONT DESK</div>
            <button class="sb-toggle" style="margin-left:auto; background:none; border:none; color:#fff;" id="sbClose">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Sidebar Search -->
        <div style="padding: 15px 16px; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <div style="position:relative;">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,0.5); font-size:12px;"></i>
                <input type="text" id="sbSearch" placeholder="Quick find..." 
                    style="width:100%; padding:10px 10px 10px 35px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.05); color:#fff; font-size:13px; outline:none; transition:all 0.2s;">
            </div>
        </div>

        <div class="sb-body" id="sbBody">
            <?php foreach ($menu as $section): ?>
                <div class="sb-lbl"><?php echo $section['title']; ?></div>

                <?php foreach ($section['items'] as $item):
                    $isActive = !empty($item['active']);
                    $hasSubmenu = !empty($item['has_submenu']);
                ?>
                    <?php if ($hasSubmenu): ?>
                        <button class="nb-btn <?php echo $isActive ? 'active' : ''; ?>"
                                onclick="toggleSubmenu('<?php echo $item['submenu_id']; ?>')">
                            <i class="fa <?php echo $item['icon']; ?> nbi"></i>
                            <span class="nbl"><?php echo $item['label']; ?></span>
                            <i class="fa fa-chevron-right nbc <?php echo !empty($item['submenu_open']) ? 'open' : ''; ?>"
                               id="chev-<?php echo $item['submenu_id']; ?>"></i>
                        </button>
                        <div class="sub-menu <?php echo !empty($item['submenu_open']) ? 'open' : ''; ?>"
                             id="<?php echo $item['submenu_id']; ?>"
                             style="<?php echo empty($item['submenu_open']) ? 'display:none;' : ''; ?>">
                            <?php foreach ($item['submenu'] as $sub):
                                $subActive = false;
                                $href = $sub['href'] ?? '#';
                                $onClick = " onclick=\"window.location.href='{$href}'\"";
                            ?>
                                <a href="<?php echo $href; ?>" <?php echo $onClick; ?>
                                   class="sub-btn <?php echo $subActive ? 'active' : ''; ?>">
                                    <?php echo $sub['label']; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo $item['href']; ?>"
                           class="nb-btn <?php echo $isActive ? 'active' : ''; ?>">
                            <i class="fa <?php echo $item['icon']; ?> nbi"></i>
                            <span class="nbl"><?php echo $item['label']; ?></span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </nav>
    <div id="sbOverlay" class="sb-overlay"></div>
    
    <script>
    function toggleSubmenu(id) {
        const submenu = document.getElementById(id);
        const chevron = document.getElementById('chev-' + id);
        
        if (submenu.style.display === 'none' || !submenu.classList.contains('open')) {
            submenu.style.display = 'block';
            submenu.classList.add('open');
            chevron.classList.add('open');
        } else {
            submenu.style.display = 'none';
            submenu.classList.remove('open');
            chevron.classList.remove('open');
        }
    }
    </script>
    <?php
}
?>
