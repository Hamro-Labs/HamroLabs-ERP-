<?php
/**
 * Front Desk — User Profile View
 * Allows operators to update their own info (Name, Phone, etc)
 */

if (!isset($_GET['partial'])) {
    renderFrontDeskHeader();
    renderFrontDeskSidebar();
}

$user = $_SESSION['userData'] ?? null;
$db = getDBConnection();
$userData = null;

try {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id AND tenant_id = :tid");
    $stmt->execute(['id' => $user['id'], 'tid' => $user['tenant_id']]);
    $userData = $stmt->fetch();
} catch (Exception $e) {}

$nameParts    = explode(' ', trim($userData['name'] ?? 'Front Desk'));
$userInitials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
?>

<div class="pg-head">
    <div class="pg-title">My Profile</div>
    <div class="pg-sub">Manage your account information and preferences</div>
</div>

<div class="g65">
    <div class="card">
        <div class="ct"><i class="fa-solid fa-user-gear"></i> Personal Information</div>
        <form id="profileForm" onsubmit="handleProfileUpdate(event)">
            <div class="form-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-top:20px;">
                <div class="f-grp">
                    <label class="f-lbl">Full Name</label>
                    <input type="text" name="name" class="f-ctrl" value="<?php echo htmlspecialchars($userData['name'] ?? ''); ?>" required>
                </div>
                <div class="f-grp">
                    <label class="f-lbl">Email Address (Read-only)</label>
                    <input type="email" class="f-ctrl" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" disabled style="background:#f8fafc;">
                </div>
                <div class="f-grp">
                    <label class="f-lbl">Phone Number</label>
                    <input type="text" name="phone" class="f-ctrl" value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>">
                </div>
                <div class="f-grp">
                    <label class="f-lbl">Role</label>
                    <input type="text" class="f-ctrl" value="Front Desk Operator" disabled style="background:#f8fafc;">
                </div>
            </div>
            <div style="margin-top:25px; display:flex; gap:10px;">
                <button type="submit" class="btn pg">Save Changes</button>
                <button type="button" class="btn bs" onclick="goNav('dashboard')">Cancel</button>
            </div>
        </form>
    </div>

    <div class="card" style="text-align:center;">
        <div class="ct"><i class="fa-solid fa-camera"></i> Profile Picture</div>
        <div class="profile-avatar-large" style="width:120px; height:120px; border-radius:50%; background:linear-gradient(135deg, #6C5CE7, #A855F7); color:#fff; display:flex; align-items:center; justify-content:center; font-size:48px; font-weight:800; margin:20px auto;">
            <?php echo $userInitials; ?>
        </div>
        <div style="font-size:13px; color:#64748b; margin-bottom:15px;">Upload a professional picture for your profile.</div>
        <button class="btn bs" style="width:100%; justify-content:center;">
            <i class="fa-solid fa-upload"></i> Change Photo
        </button>
    </div>
</div>

<script>
async function handleProfileUpdate(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const oldTxt = btn.innerText;
    btn.innerText = 'Saving...';
    btn.disabled = true;

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch(`${window.APP_URL}/api/admin/profile`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if(result.success) {
            Swal.fire('Success', 'Profile updated successfully!', 'success');
            // Update local session/ui if needed
        } else {
            Swal.fire('Error', result.message || 'Update failed', 'error');
        }
    } catch(err) {
        Swal.fire('Error', 'An error occurred during update', 'error');
    } finally {
        btn.innerText = oldTxt;
        btn.disabled = false;
    }
}
</script>
