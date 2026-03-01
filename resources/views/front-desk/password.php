<?php
/**
 * Front Desk — Change Password View
 * Secure interface for operators to update their credentials
 */

if (!isset($_GET['partial'])) {
    renderFrontDeskHeader();
    renderFrontDeskSidebar();
}
?>

<div class="pg-head">
    <div class="pg-title">Security Settings</div>
    <div class="pg-sub">Update your login credentials to keep your account secure</div>
</div>

<div class="card" style="max-width: 500px;">
    <div class="ct"><i class="fa-solid fa-key"></i> Change Password</div>
    <form id="passwordForm" onsubmit="handlePasswordUpdate(event)">
        <div class="f-grp" style="margin-top:20px;">
            <label class="f-lbl">Current Password</label>
            <input type="password" name="current_password" class="f-ctrl" required>
        </div>
        <div class="f-grp">
            <label class="f-lbl">New Password</label>
            <input type="password" name="new_password" class="f-ctrl" required minlength="8">
            <div class="sub-txt" style="margin-top:5px;">Minimum 8 characters</div>
        </div>
        <div class="f-grp">
            <label class="f-lbl">Confirm New Password</label>
            <input type="password" name="confirm_password" class="f-ctrl" required>
        </div>
        
        <div style="margin-top:25px; display:flex; gap:10px;">
            <button type="submit" class="btn pr">Update Password</button>
            <button type="button" class="btn bs" onclick="goNav('dashboard')">Cancel</button>
        </div>
    </form>
</div>

<script>
async function handlePasswordUpdate(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    if (data.new_password !== data.confirm_password) {
        Swal.fire('Error', 'New passwords do not match!', 'error');
        return;
    }

    const btn = e.target.querySelector('button[type="submit"]');
    const oldTxt = btn.innerText;
    btn.innerText = 'Updating...';
    btn.disabled = true;

    try {
        const res = await fetch(`${window.APP_URL}/api/admin/profile?action=change_password`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if(result.success) {
            Swal.fire('Success', 'Password changed successfully! Please login again if required.', 'success');
            e.target.reset();
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
