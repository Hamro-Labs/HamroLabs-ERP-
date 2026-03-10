// Teacher Profile Module
async function renderTeacherProfile() {
    mainContent.innerHTML = `
        <div class="pg fu">
            <div class="pg-head" style="margin-bottom:20px;">
                <div class="pg-title">My Profile</div>
                <div style="font-size:12px; color:var(--text-body); margin-top:4px;">Manage your personal details and settings</div>
            </div>
            
            <div id="taProfileContainer">
                <div style="text-align:center; padding:40px;">
                    <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
                    <p>Loading Profile...</p>
                </div>
            </div>
        </div>
    `;

    try {
        const res = await fetch(`${APP_URL}/api/teacher/profile`);
        const json = await res.json();
        
        if (!json.success) {
            document.getElementById('taProfileContainer').innerHTML = `<div class="alert alert-danger">${json.message}</div>`;
            return;
        }

        const data = json.data;
        
        let avatarHtml = '';
        if (data.avatar) {
            avatarHtml = `<img src="${data.avatar}" alt="Avatar" style="width:100px; height:100px; border-radius:50%; object-fit:cover;">`;
        } else {
            const initials = data.full_name ? data.full_name.substring(0, 2).toUpperCase() : 'T';
            avatarHtml = `<div style="width:100px; height:100px; border-radius:50%; background:var(--blue); color:white; display:flex; align-items:center; justify-content:center; font-size:36px; font-weight:bold;">${initials}</div>`;
        }

        document.getElementById('taProfileContainer').innerHTML = `
            <div class="grid" style="grid-template-columns: 1fr 2fr; gap: 20px;">
                <div class="card" style="text-align:center;">
                    <div style="margin-bottom:20px; display:flex; justify-content:center;">
                        ${avatarHtml}
                    </div>
                    <h3 style="margin:0 0 5px 0;">${data.full_name || 'N/A'}</h3>
                    <div style="color:var(--text-light); font-size:14px; margin-bottom:15px;">${data.employee_id || 'ID Pending'}</div>
                    <div class="bdg bg-green" style="margin-bottom:20px;">${data.status || 'Active'}</div>
                    
                    <div style="border-top:1px solid var(--card-border); padding-top:15px; text-align:left;">
                        <div style="margin-bottom:10px;"><i class="fa-solid fa-envelope" style="width:20px; color:var(--text-light);"></i> ${data.email || 'N/A'}</div>
                        <div style="margin-bottom:10px;"><i class="fa-solid fa-phone" style="width:20px; color:var(--text-light);"></i> ${data.phone || 'N/A'}</div>
                        <div style="margin-bottom:10px;"><i class="fa-solid fa-calendar-alt" style="width:20px; color:var(--text-light);"></i> Joined: ${data.joined_date || 'N/A'}</div>
                    </div>
                </div>
                
                <div class="card">
                    <h3 style="margin-bottom:15px; border-bottom:1px solid var(--card-border); padding-bottom:10px;">Professional Details</h3>
                    
                    <div style="margin-bottom:20px;">
                        <label style="font-size:12px; font-weight:bold; color:var(--text-light);">Qualification</label>
                        <div style="font-size:15px;">${data.qualification || 'Not Specified'}</div>
                    </div>
                    
                    <div style="margin-bottom:20px;">
                        <label style="font-size:12px; font-weight:bold; color:var(--text-light);">Specialization</label>
                        <div style="font-size:15px;">${data.specialization || 'Not Specified'}</div>
                    </div>

                    <h3 style="margin-top:30px; margin-bottom:15px; border-bottom:1px solid var(--card-border); padding-bottom:10px;">Security Settings</h3>
                    
                    <form id="taPasswordForm" onsubmit="taChangePassword(event)">
                        <div class="form-group mb-15">
                            <label>Current Password</label>
                            <input type="password" name="current_password" class="input" required>
                        </div>
                        <div class="form-group mb-15">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="input" required minlength="6">
                        </div>
                        <div class="form-group mb-20">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" class="input" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-primary" id="taBtnPass"><i class="fa-solid fa-save"></i> Update Password</button>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Profile Load Error:', error);
        document.getElementById('taProfileContainer').innerHTML = `<div class="alert alert-danger">Failed to load profile.</div>`;
    }
}

async function taChangePassword(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('taBtnPass');
    
    if(form.new_password.value !== form.confirm_password.value) {
        alert("New passwords do not match!");
        return;
    }

    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';
    btn.disabled = true;

    try {
        const formData = new FormData();
        formData.append('action', 'change_password');
        formData.append('current_password', form.current_password.value);
        formData.append('new_password', form.new_password.value);
        
        // Add CSRF
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) formData.append('csrf_token', csrfMeta.getAttribute('content'));

        const res = await fetch(`${APP_URL}/api/teacher/profile`, {
            method: 'POST',
            body: formData
        });
        
        const json = await res.json();
        
        if (json.success) {
            alert("Password updated successfully.");
            form.reset();
        } else {
            alert(json.message || "Failed to update password.");
        }
    } catch (error) {
        console.error("Password update error:", error);
        alert("System error. Please try again.");
    } finally {
        btn.innerHTML = '<i class="fa-solid fa-save"></i> Update Password';
        btn.disabled = false;
    }
}
