<?php
/**
 * Hamro ERP — Profile Page
 * Platform Blueprint V3.0
 * 
 * @module SuperAdmin
 */

require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pageTitle = 'My Profile';
$activePage = 'profile.php';
?>

<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<main class="main" id="mainContent">
    <div class="page fu">
        <div class="page-head">
            <div class="page-title-row">
                <div class="page-icon" style="background:rgba(0,184,148,0.1); color:var(--green);">
                    <i class="fa-regular fa-circle-user"></i>
                </div>
                <div>
                    <div class="page-title">My Profile</div>
                    <div class="page-sub">Manage your personal information and preferences.</div>
                </div>
            </div>
        </div>

        <div class="g2 mb">
            <div class="card">
                <div class="ct"><i class="fa-solid fa-user-pen"></i> Personal Information</div>
                <form style="display:flex; flex-direction:column; gap:15px; margin-top:15px;" onsubmit="event.preventDefault(); SuperAdmin.showNotification('Profile updated successfully!', 'success');">
                    <div style="display:flex; gap:15px; flex-wrap: wrap;">
                        <div style="flex:1; min-width: 200px;">
                            <label style="display:block; font-size:12px; font-weight:600; color:var(--text-dark); margin-bottom:5px;">First Name</label>
                            <input type="text" class="form-control" placeholder="Anil" value="Anil" style="width:100%; padding:10px; border:1px solid var(--card-border); border-radius:8px;">
                        </div>
                        <div style="flex:1; min-width: 200px;">
                            <label style="display:block; font-size:12px; font-weight:600; color:var(--text-dark); margin-bottom:5px;">Last Name</label>
                            <input type="text" class="form-control" placeholder="Shrestha" value="Shrestha" style="width:100%; padding:10px; border:1px solid var(--card-border); border-radius:8px;">
                        </div>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; color:var(--text-dark); margin-bottom:5px;">Email Address</label>
                        <input type="email" class="form-control" placeholder="admin@hamrolabs.com" value="admin@hamrolabs.com" style="width:100%; padding:10px; border:1px solid var(--card-border); border-radius:8px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; color:var(--text-dark); margin-bottom:5px;">Phone Number</label>
                        <input type="text" class="form-control" placeholder="+977-9800000000" value="+977-9800000000" style="width:100%; padding:10px; border:1px solid var(--card-border); border-radius:8px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; color:var(--text-dark); margin-bottom:5px;">Role</label>
                        <input type="text" class="form-control" value="Super Admin" disabled style="width:100%; padding:10px; border:1px solid var(--card-border); border-radius:8px; background:#f8fafc;">
                    </div>
                    <div style="margin-top:10px;">
                        <button type="submit" class="btn bt">Save Changes</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="ct"><i class="fa-solid fa-gear"></i> Preferences</div>
                <div style="display:flex; flex-direction:column; gap:15px; margin-top:15px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px; background:#f8fafc; border-radius:8px;">
                        <div>
                            <div style="font-weight:600; font-size:13px;">Email Notifications</div>
                            <div style="font-size:12px; color:var(--text-light);">Receive email notifications for important updates</div>
                        </div>
                        <label style="position:relative; display:inline-block; width:44px; height:24px;">
                            <input type="checkbox" checked style="opacity:0; width:0; height:0;">
                            <span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:#22c55e; transition:.4s; border-radius:24px;"></span>
                        </label>
                    </div>
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px; background:#f8fafc; border-radius:8px;">
                        <div>
                            <div style="font-weight:600; font-size:13px;">Two-Factor Authentication</div>
                            <div style="font-size:12px; color:var(--text-light);">Add an extra layer of security to your account</div>
                        </div>
                        <label style="position:relative; display:inline-block; width:44px; height:24px;">
                            <input type="checkbox" style="opacity:0; width:0; height:0;">
                            <span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:#ccc; transition:.4s; border-radius:24px;"></span>
                        </label>
                    </div>
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px; background:#f8fafc; border-radius:8px;">
                        <div>
                            <div style="font-weight:600; font-size:13px;">Activity Logs</div>
                            <div style="font-size:12px; color:var(--text-light);">Keep a record of your account activity</div>
                        </div>
                        <label style="position:relative; display:inline-block; width:44px; height:24px;">
                            <input type="checkbox" checked style="opacity:0; width:0; height:0;">
                            <span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:#22c55e; transition:.4s; border-radius:24px;"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
