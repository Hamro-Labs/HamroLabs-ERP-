const fs = require('fs');

const htmlContent = fs.readFileSync('c:/Apache24/htdocs/erp/academic-calendar-hamropatro.html', 'utf8');

const styleMatch = htmlContent.match(/<style>([\s\S]*?)<\/style>/);
let css = styleMatch ? styleMatch[1] : '';

// Remove base HTML resets that might break the ERP layout
css = css.replace(/body\s*(?:[^{]*)\{[^}]+\}/g, '');
css = css.replace(/html\s*(?:[^{]*)\{[^}]+\}/g, '');
css = css.replace(/\*[\s\S]*?\}/g, '');
css = css.replace(/:root\s*\{[^}]+\}/g, '');
css = css.replace(/\.hdr\b[\s\S]*?\}/g, '');
css = css.replace(/\.sb\b[\s\S]*?\}/g, '');
css = css.replace(/\.main\b[\s\S]*?\}/g, '');

const mainMatch = htmlContent.match(/<main class="main">([\s\S]*?)<\/main>/);
let mainHtml = mainMatch ? mainMatch[1] : '';
const modalsMatch = htmlContent.match(/(<!-- ADD EVENT MODAL -->[\s\S]*?)<script>/);
let modalsHtml = modalsMatch ? modalsMatch[1] : '';
const scriptMatch = htmlContent.match(/<script>([\s\S]*?)<\/script>/);
let jsContent = scriptMatch ? scriptMatch[1] : '';

jsContent = jsContent.replace('renderTodayBanner();', '');
jsContent = jsContent.replace('renderCal();', '');
// Remove window.onload or similar if any
jsContent = jsContent.replace(/window\.onload\s*=\s*(?:function\s*\(\)|=>)\s*\{[^}]*\}/, '');



let finalJs = `/**
 * Hamro ERP — Institute Admin · Academic Calendar
 */

window.renderAcademicCalendar = function() {
    const mc = document.getElementById('mainContent');
    if (!mc) return;

    mc.innerHTML = \`
<style>
/* Scoped Calendar Variables */
.academic-calendar-wrap {
  --sa-primary:#009E7E;--sa-primary-d:#007a62;--sa-primary-h:#00b894;--sa-primary-lt:#e0f5f0;
  --navy:#0F172A;--red:#E11D48;--purple:#8141A5;--soft-purple:#F3E8FF;--amber:#d97706;
  --blue:#3b82f6;--success:#00B894;--warning:#FDCB6E;--danger:#E17055;
  --bg:#F8FAFC;--cb:#E2E8F0;--td:#1E293B;--tb:#475569;--tl:#94A3B8;--white:#fff;
  --sh:0 1px 3px rgba(0,0,0,.07);--shm:0 4px 20px rgba(0,0,0,.10);
  --font:'Plus Jakarta Sans',sans-serif;
}
${css}
</style>
<div class="academic-calendar-wrap">
${mainHtml}
${modalsHtml}
</div>
\`;

    setTimeout(() => {
        window.initAcademicCalendar();
    }, 100);
};

// --- DOM LOGIC ---
${jsContent}

window.initAcademicCalendar = function() {
    // Re-bind modal clicks or events if necessary
    // E.g. attach events here if needed, or just let inline onclick handlers run (which they do since they are global)
    if (typeof renderTodayBanner === 'function') renderTodayBanner();
    if (typeof renderCal === 'function') renderCal();
    _iaFetchEvents(); // Hook to fetch actual calendar events
};

// Overwrite saveEvent, deleteEvent, and add fetching logic
window._iaFetchEvents = async function() {
    try {
        const res = await fetch('/api/admin/academic-calendar');
        const data = await res.json();
        if (data.success && data.data) {
            window.EVENTS = data.data; // Replace the mocked EVENTS
            if (typeof renderCal === 'function') renderCal();
        }
    } catch(err) {
        console.error('Failed to load events:', err);
    }
};

window.saveEvent = async function() {
    const title = document.getElementById('evTitle').value.trim();
    const start = document.getElementById('evStart').value;
    const end = document.getElementById('evEnd').value || start;
    if(!title) { showToast('Please enter event title','warn'); return; }
    if(!start) { showToast('Please select start date','warn'); return; }

    const payload = {
        title,
        type: document.getElementById('evType').value,
        start,
        end,
        batch: document.getElementById('evBatch').value,
        description: document.getElementById('evDesc').value.trim()
    };

    try {
        const res = await fetch('/api/admin/academic-calendar/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            closeModal('addModal');
            _iaFetchEvents();
            showToast(\`"\${title}" added to calendar\`, 'success');
        } else {
            showToast(data.message || 'Error saving event', 'error');
        }
    } catch (err) {
        showToast('Network error', 'error');
    }
}

window.deleteEvent = async function(id) {
    if(!confirm('Are you sure you want to delete this event?')) return;
    try {
        const res = await fetch('/api/admin/academic-calendar/delete?id='+id, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            closeModal('detailModal');
            _iaFetchEvents();
            showToast('Event deleted', 'warn');
        } else {
            showToast(data.message || 'Error deleting', 'error');
        }
    } catch (err) {
        showToast('Network error', 'error');
    }
}
`;

fs.writeFileSync('c:/Apache24/htdocs/erp/public/assets/js/ia-academic-calendar.js', finalJs);
console.log('ia-academic-calendar.js generated.');
