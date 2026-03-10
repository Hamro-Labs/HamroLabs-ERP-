// Teacher Classes Module (ta-classes.js)
async function renderMyClasses() {
    mainContent.innerHTML = `
        <div class="pg fu">
            <div class="pg-head" style="margin-bottom:20px;">
                <div class="pg-title">My Classes</div>
                <div style="font-size:12px; color:var(--text-body); margin-top:4px;">Manage your daily schedule and timetable</div>
            </div>
            
            <div class="tabs">
                <button class="tab-btn active" onclick="taSwitchClassTab('today')">Today's Schedule</button>
                <button class="tab-btn" onclick="taSwitchClassTab('weekly')">Weekly Timetable</button>
            </div>
            
            <div id="taClassContent" style="margin-top:20px;">
                <div style="text-align:center; padding:40px;">
                    <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
                    <p>Loading Schedule...</p>
                </div>
            </div>
        </div>
    `;

    try {
        const res = await fetch(`${APP_URL}/api/teacher/classes?action=today`);
        const json = await res.json();
        
        if (!json.success) {
            document.getElementById('taClassContent').innerHTML = `<div class="alert alert-danger">${json.message}</div>`;
            return;
        }

        const data = json.data || [];
        
        let html = '<div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">';
        if (data.length === 0) {
            html = '<div class="alert alert-info">No classes scheduled for today.</div>';
        } else {
            data.forEach(cls => {
                let statusBadge = `<div class="bdg bg-t">Upcoming</div>`;
                const now = new Date().toLocaleTimeString('en-US', {hour12:false});
                // Simple string compare
                if(now > cls.start_time && now < cls.end_time) statusBadge = `<div class="bdg bg-green">Ongoing</div>`;
                else if(now > cls.end_time) statusBadge = `<div class="bdg bg-gray">Completed</div>`;

                html += `
                    <div class="card p-20" style="position:relative;">
                        <div style="position:absolute; top:20px; right:20px;">
                            ${statusBadge}
                        </div>
                        <h4 style="margin:0 0 10px 0; color:var(--text-dark);">${cls.subject_name || 'Subject'}</h4>
                        <div style="color:var(--text-light); font-size:13px; margin-bottom:5px;">
                            <i class="fa-solid fa-users" style="width:16px;"></i> ${cls.batch_name || 'Batch'}
                        </div>
                        <div style="color:var(--text-light); font-size:13px; margin-bottom:5px;">
                            <i class="fa-solid fa-clock" style="width:16px;"></i> ${cls.start_time} - ${cls.end_time}
                        </div>
                        <div style="color:var(--text-light); font-size:13px; margin-bottom:15px;">
                            <i class="fa-solid fa-door-open" style="width:16px;"></i> Room: ${cls.room || 'TBA'}
                        </div>
                        <div style="border-top:1px solid var(--card-border); padding-top:15px; text-align:right;">
                            <button class="btn btn-sm" onclick="taMarkClassAttendance(${cls.id})"><i class="fa-solid fa-user-check"></i> Mark Attendance</button>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
        }

        document.getElementById('taClassContent').innerHTML = html;
        window.taCurrentClassData = data;
        
    } catch (error) {
        console.error('Classes Load Error:', error);
        document.getElementById('taClassContent').innerHTML = `<div class="alert alert-danger">Failed to load classes.</div>`;
    }
}

function taSwitchClassTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    
    if(tab === 'today') {
        renderMyClasses();
    } else {
        taRenderWeeklyTimetable();
    }
}

async function taRenderWeeklyTimetable() {
    const container = document.getElementById('taClassContent');
    container.innerHTML = `<div style="text-align:center; padding:40px;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p>Loading Timetable...</p></div>`;
    
    try {
        const res = await fetch(`${APP_URL}/api/teacher/classes?action=weekly`);
        const json = await res.json();
        
        if (!json.success) {
            container.innerHTML = `<div class="alert alert-danger">${json.message}</div>`;
            return;
        }

        container.innerHTML = `<div class="card">Weekly timetable rendering logic goes here (table layout).</div>`;
        
    } catch (error) {
        console.error('Weekly Timetable Load Error:', error);
        container.innerHTML = `<div class="alert alert-danger">Failed to load weekly timetable.</div>`;
    }
}

function taMarkClassAttendance(classId) {
    alert("Functionality to mark attendance for class ID " + classId + " will open an attendance modal.");
}
