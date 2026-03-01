<?php
require_once __DIR__ . '/../../../config/config.php';
requirePermission('dashboard.view');

$pageTitle = "Student ID Card - Student";
$themeColor = "#009E7E";
$roleCSS = "student.css";
include VIEWS_PATH . '/layouts/header.php';
?>

    <!-- Sidebar Overlay -->
    <div class="sb-overlay" id="sbOverlay"></div>

    <div class="root">

        <!-- ── HEADER ── -->
        <header class="hdr">
            <div class="hdr-left">
                <button class="sb-toggle" id="sbToggle" title="Toggle Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="hdr-logo-box">
                    <div style="width:28px; height:28px; border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#fff;">
                        <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="Logo" style="width:100%; height:auto;">
                    </div>
                    <span class="logo-txt">Hamro ERP</span>
                </div>
            </div>

            <div class="hdr-right">
                <div class="hbtn nb" title="Notifications">
                    <i class="fa-solid fa-bell"></i>
                    <div class="ndot"></div>
                </div>

                <!-- Student Dropdown -->
                <div style="position:relative;">
                    <div class="u-chip" id="userChip">
                        <div class="u-av">SK</div>
                        <div style="display:flex; flex-direction:column; margin-left:8px; line-height:1.2;">
                            <span style="font-size:12px; font-weight:700;">Suman Karki</span>
                            <span style="font-size:10px; opacity:0.8;">HL-KH-047 (Student)</span>
                        </div>
                        <i class="fa-solid fa-chevron-down" style="font-size:9px; margin-left:6px; opacity:0.7;"></i>
                    </div>
                    
                    <div id="userDropdown" style="position:absolute; top:calc(100% + 10px); right:0; background:#fff; border:1px solid var(--card-border); border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.1); min-width:200px; padding:8px; z-index:1100; visibility:hidden; opacity:0; transition:0.2s;">
                        <a href="student-profile-view.php" style="display:flex; align-items:center; gap:10px; padding:10px; font-size:13px; color:var(--text-dark); text-decoration:none; border-radius:8px;"><i class="fa-regular fa-circle-user" style="color:var(--green)"></i> My Profile</a>
                        <a href="student-change-password.php" style="display:flex; align-items:center; gap:10px; padding:10px; font-size:13px; color:var(--text-dark); text-decoration:none; border-radius:8px;"><i class="fa-solid fa-key" style="color:var(--amber)"></i> Change Password</a>
                        <a href="student-id-card-view.php" style="display:flex; align-items:center; gap:10px; padding:10px; font-size:13px; color:var(--text-dark); text-decoration:none; border-radius:8px;"><i class="fa-solid fa-id-card" style="color:var(--green)"></i> Digital ID Card</a>
                        <div style="height:1px; background:var(--card-border); margin:6px 0;"></div>
                        <a href="../../index.php?logout=1" style="display:flex; align-items:center; gap:10px; padding:10px; font-size:13px; color:var(--red); text-decoration:none; border-radius:8px;"><i class="fa-solid fa-power-off"></i> Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- ── SIDEBAR ── -->
        <nav class="sb" id="sidebar">
            <!-- Sidebar header shown only on mobile -->
            <div class="sb-header">
                <div class="hdr-logo-box">
                    <span class="logo-txt">Student Portal</span>
                </div>
                <button class="sb-close-btn" id="sbClose" title="Close Sidebar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="sb-body" id="sbBody">
                <!-- Rendered via js/student.js -->
            </div>
        </nav>

        <!-- ── MAIN CONTENT ── -->
        <main class="main" id="mainContent">
            <!-- Main Content - ID Card View -->
            <div class="pg">
                <!-- Breadcrumb -->
                <div class="breadcrumb">
                    <a href="student.php">Dashboard</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="student-profile-view.php">My Profile</a>
                    <i class="fas fa-chevron-right"></i>
                    <span class="bc-cur">ID Card</span>
                </div>

                <!-- Page Header -->
                <div class="pg-hdr">
                    <div>
                        <h1>Student ID Card</h1>
                        <p>Your official institute identification card</p>
                    </div>
                    <div class="pg-acts">
                        <button class="btn bs" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button class="btn bt" onclick="alert('Downloading ID Card...')">
                            <i class="fas fa-download"></i> Download PDF
                        </button>
                    </div>
                </div>

                <!-- ID Card Display -->
                <div class="id-card-container">
                    <div class="id-card">
                        <!-- Card Header -->
                        <div class="id-card-header">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 8px;">
                                <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-graduation-cap" style="font-size: 20px;"></i>
                                </div>
                                <h3 style="font-size: 20px; font-weight: 800;">HAMRO INSTITUTE</h3>
                            </div>
                            <p style="font-size: 12px; opacity: 0.9;">Loksewa & Academic Excellence</p>
                        </div>

                        <!-- Card Photo Area -->
                        <div style="display: flex; padding: 24px; gap: 20px; border-bottom: 1px dashed var(--card-border);">
                            <div style="width: 120px; height: 140px; background: linear-gradient(145deg, #f1f5f9, #e2e8f0); border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 2px solid #fff; box-shadow: var(--shadow);">
                                <i class="fas fa-user-graduate" style="font-size: 48px; color: var(--text-light);"></i>
                                <span style="font-size: 10px; color: var(--text-light); margin-top: 8px;">STUDENT PHOTO</span>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div>
                                        <span style="font-size: 10px; color: var(--text-light); text-transform: uppercase;">Student Name</span>
                                        <div style="font-size: 20px; font-weight: 800; color: var(--text-dark);">Rahul Sharma</div>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                        <div>
                                            <span style="font-size: 9px; color: var(--text-light); text-transform: uppercase;">Roll Number</span>
                                            <div style="font-weight: 700;">STU-2025-001</div>
                                        </div>
                                        <div>
                                            <span style="font-size: 9px; color: var(--text-light); text-transform: uppercase;">Batch</span>
                                            <div style="font-weight: 700;">Morning 2025</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Details -->
                        <div style="padding: 20px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px 20px;">
                                <div>
                                    <span style="font-size: 9px; color: var(--text-light); text-transform: uppercase;">Course</span>
                                    <div style="font-weight: 600; font-size: 13px;">Loksewa Nayab Subba</div>
                                </div>
                                <div>
                                    <span style="font-size: 9px; color: var(--text-light); text-transform: uppercase;">Valid Until</span>
                                    <div style="font-weight: 600; font-size: 13px;">Dec 2025</div>
                                </div>
                                <div>
                                    <span style="font-size: 9px; color: var(--text-light); text-transform: uppercase;">Blood Group</span>
                                    <div style="font-weight: 600; font-size: 13px;">B+</div>
                                </div>
                                <div>
                                    <span style="font-size: 9px; color: var(--text-light); text-transform: uppercase;">Date of Birth</span>
                                    <div style="font-weight: 600; font-size: 13px;">15 Jan 2003</div>
                                </div>
                            </div>

                            <!-- Contact & Address -->
                            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--card-border);">
                                <div style="display: flex; gap: 12px; margin-bottom: 8px;">
                                    <i class="fas fa-phone-alt" style="color: var(--green); font-size: 12px;"></i>
                                    <span style="font-size: 12px;">+977 9841234567</span>
                                </div>
                                <div style="display: flex; gap: 12px;">
                                    <i class="fas fa-map-marker-alt" style="color: var(--green); font-size: 12px;"></i>
                                    <span style="font-size: 12px;">Kathmandu, Nepal</span>
                                </div>
                            </div>

                            <!-- QR Code -->
                            <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
                                <div style="background: #f1f5f9; padding: 8px; border-radius: 8px;">
                                    <div style="width: 60px; height: 60px; background: #fff; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-qrcode" style="font-size: 40px; color: var(--text-dark);"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Signature -->
                            <div style="margin-top: 16px; text-align: right; border-top: 1px solid var(--card-border); padding-top: 12px;">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='30' viewBox='0 0 120 30'%3E%3Cpath d='M10,20 Q30,10 50,20 T90,15' stroke='%23475569' fill='none' stroke-width='2'/%3E%3C/svg%3E" style="width: 100px;">
                                <div style="font-size: 11px; color: var(--text-light);">Authorized Signature</div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div style="background: #f8fafc; padding: 12px; text-align: center; border-top: 1px solid var(--card-border);">
                            <span style="font-size: 10px; color: var(--text-light);">This card is property of Hamro Institute. If found, please return to:</span>
                            <div style="font-size: 11px; font-weight: 600;">info@hamroinstitute.edu.np | 01-4412345</div>
                        </div>
                    </div>
                </div>

                <!-- ID Card Info & Actions -->
                <div class="card" style="max-width: 540px; margin: 0 auto;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                        <div class="sc-ico ic-blue"><i class="fas fa-info-circle"></i></div>
                        <div>
                            <h4 style="font-weight: 700; margin-bottom: 4px;">About Your ID Card</h4>
                            <p style="font-size: 12px; color: var(--text-body);">Use this ID card for institute access, library borrowing, and exam hall entry.</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <span class="tag bg-t"><i class="fas fa-check-circle"></i> Verified</span>
                        <span class="tag bg-b"><i class="fas fa-clock"></i> Valid till Dec 2025</span>
                        <span class="tag bg-p"><i class="fas fa-shield-alt"></i> Digital Signature</span>
                    </div>
                </div>
            </div>

            <style>
                .id-card-container {
                    display: flex;
                    justify-content: center;
                    margin-bottom: 24px;
                }
                
                .id-card {
                    width: 100%;
                    max-width: 540px;
                    background: #fff;
                    border-radius: 20px;
                    box-shadow: var(--shadow-md);
                    overflow: hidden;
                    border: 1px solid var(--card-border);
                }
                
                .id-card-header {
                    background: linear-gradient(135deg, var(--green) 0%, var(--green-d) 100%);
                    padding: 24px 20px;
                    color: #fff;
                    text-align: center;
                }
                
                @media print {
                    body * { visibility: hidden; }
                    .id-card, .id-card * { visibility: visible; }
                    .id-card { position: absolute; top: 20px; left: 20px; width: 100%; max-width: 540px; }
                    .pg-acts, .card, .breadcrumb { display: none; }
                }
            </style>
        </main>

    </div>

    <!-- Custom Scripts -->
    <script src="<?php echo APP_URL; ?>/public/assets/js/pwa-handler.js"></script>
    <script src="<?php echo APP_URL; ?>/public/assets/js/student.js"></script>
    <script src="<?php echo APP_URL; ?>/public/assets/js/breadcrumb.js"></script>
    <script>
        // Dropdown Logic
        const chip = document.getElementById('userChip');
        const drop = document.getElementById('userDropdown');
        chip.onclick = (e) => {
            e.stopPropagation();
            const isVisible = drop.style.visibility === 'visible';
            drop.style.visibility = isVisible ? 'hidden' : 'visible';
            drop.style.opacity = isVisible ? '0' : '1';
        };
        document.onclick = () => {
            drop.style.visibility = 'hidden';
            drop.style.opacity = '0';
        };
    </script>

</body>
</html>
