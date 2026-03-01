<?php
/**
 * Hamro ERP — Super Admin Search Page
 * Platform Blueprint V3.0
 * 
 * Global search functionality for super admin with AJAX live search
 * 
 * @module SuperAdmin
 * @version 1.0.0
 */

// Include configuration and modular components
require_once __DIR__ . '/../../../config/config.php';
require_once VIEWS_PATH . '/layouts/header_1.php';

$pageTitle = 'Search';
$activePage = 'search.php';

// Get search query if present
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchType = isset($_GET['type']) ? $_GET['type'] : 'all';
?>

<!-- Sidebar -->
<?php renderSuperAdminHeader(); renderSidebar($activePage); ?>

<!-- Main Content -->
<main class="main" id="mainContent">
    <div class="pg fu">
        
        <!-- Page Header -->
        <div class="pg-head">
            <div class="pg-left">
                <div class="pg-ico ic-t"><i class="fa-solid fa-magnifying-glass"></i></div>
                <div>
                    <div class="pg-title">Search</div>
                    <div class="pg-sub">Search across tenants, users, plans, invoices, and tickets.</div>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card" style="margin-bottom: 24px;">
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 280px;">
                    <div class="search-input-wrapper" style="position: relative;">
                        <i class="fa-solid fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                        <input 
                            type="text" 
                            id="liveSearchInput"
                            value="<?php echo htmlspecialchars($searchQuery); ?>"
                            placeholder="Search tenants, users, plans, invoices..."
                            class="form-input"
                            style="padding-left: 42px; height: 48px; font-size: 15px;"
                        >
                        <div id="searchLoader" class="search-loader" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); display: none;">
                            <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--primary);"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <select id="searchType" class="form-select" style="height: 48px; min-width: 160px;">
                        <option value="all" <?php echo $searchType === 'all' ? 'selected' : ''; ?>>All Categories</option>
                        <option value="tenants" <?php echo $searchType === 'tenants' ? 'selected' : ''; ?>>Tenants</option>
                        <option value="users" <?php echo $searchType === 'users' ? 'selected' : ''; ?>>Users</option>
                        <option value="plans" <?php echo $searchType === 'plans' ? 'selected' : ''; ?>>Plans</option>
                        <option value="invoices" <?php echo $searchType === 'invoices' ? 'selected' : ''; ?>>Invoices</option>
                        <option value="tickets" <?php echo $searchType === 'tickets' ? 'selected' : ''; ?>>Tickets</option>
                    </select>
                </div>
            </div>
            
            <!-- Search Tips -->
            <div style="margin-top: 16px; padding: 12px 16px; background: var(--bg-secondary); border-radius: 8px;">
                <div style="font-size: 13px; color: var(--text-body); font-weight: 600; margin-bottom: 8px;">
                    <i class="fa-solid fa-lightbulb" style="color: var(--warning); margin-right: 6px;"></i>
                    Search Tips
                </div>
                <ul style="font-size: 12px; color: var(--text-light); margin: 0; padding-left: 20px;">
                    <li>Start typing to see live results</li>
                    <li>Search by tenant name, email, or domain</li>
                    <li>Search users by name or email address</li>
                    <li>Filter by category for more specific results</li>
                </ul>
            </div>
        </div>

        <!-- Live Search Results Container -->
        <div id="searchResults">
            <!-- Initial State -->
            <div class="card" style="text-align: center; padding: 48px 24px;">
                <div style="font-size: 48px; color: var(--text-light); margin-bottom: 16px;">
                    <i class="fa-solid fa-magnifying-glass-chart"></i>
                </div>
                <h3 style="font-size: 18px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px;">
                    Start Searching
                </h3>
                <p style="font-size: 14px; color: var(--text-body); margin-bottom: 0;">
                    Start typing to see live results across tenants, users, plans, invoices, and tickets.
                </p>
            </div>
        </div>

    </div>
</main>

<!-- Include Footer -->
<?php require_once 'footer.php'; ?>

<!-- AJAX Live Search Script -->
<script>
(function() {
    const searchInput = document.getElementById('liveSearchInput');
    const searchType = document.getElementById('searchType');
    const searchResults = document.getElementById('searchResults');
    const searchLoader = document.getElementById('searchLoader');
    
    let debounceTimer = null;
    const DEBOUNCE_DELAY = 300;

    // Perform search
    function performSearch() {
        const query = searchInput.value.trim();
        const type = searchType.value;
        
        if (query.length < 2) {
            searchResults.innerHTML = `
                <div class="card" style="text-align: center; padding: 48px 24px;">
                    <div style="font-size: 48px; color: var(--text-light); margin-bottom: 16px;">
                        <i class="fa-solid fa-magnifying-glass-chart"></i>
                    </div>
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px;">
                        Start Searching
                    </h3>
                    <p style="font-size: 14px; color: var(--text-body); margin-bottom: 0;">
                        Start typing to see live results across tenants, users, plans, invoices, and tickets.
                    </p>
                </div>
            `;
            return;
        }

        // Show loader
        searchLoader.style.display = 'block';
        
        // Make AJAX request
        fetch(`../../api/search.php?q=${encodeURIComponent(query)}&type=${encodeURIComponent(type)}`)
            .then(response => response.json())
            .then(data => {
                searchLoader.style.display = 'none';
                renderResults(data);
            })
            .catch(error => {
                searchLoader.style.display = 'none';
                console.error('Search error:', error);
                searchResults.innerHTML = `
                    <div class="card" style="text-align: center; padding: 48px 24px;">
                        <div style="font-size: 48px; color: #dc2626; margin-bottom: 16px;">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <h3 style="font-size: 18px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px;">
                            Search Error
                        </h3>
                        <p style="font-size: 14px; color: var(--text-body); margin-bottom: 0;">
                            An error occurred while searching. Please try again.
                        </p>
                    </div>
                `;
            });
    }

    // Render search results
    function renderResults(data) {
        if (!data.success) {
            searchResults.innerHTML = `
                <div class="card" style="text-align: center; padding: 48px 24px;">
                    <div style="font-size: 48px; color: #dc2626; margin-bottom: 16px;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px;">
                        Error
                    </h3>
                    <p style="font-size: 14px; color: var(--text-body); margin-bottom: 0;">
                        ${data.error || 'An error occurred'}
                    </p>
                </div>
            `;
            return;
        }

        const counts = data.counts;
        const results = data.results;
        
        if (counts.total === 0) {
            searchResults.innerHTML = `
                <div class="card" style="text-align: center; padding: 48px 24px;">
                    <div style="font-size: 48px; color: var(--text-light); margin-bottom: 16px;">
                        <i class="fa-solid fa-search"></i>
                    </div>
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px;">
                        No results found
                    </h3>
                    <p style="font-size: 14px; color: var(--text-body); margin-bottom: 0;">
                        We couldn't find any matches for "<strong>${data.query}</strong>".
                    </p>
                </div>
            `;
            return;
        }

        let html = `
            <div class="results-header" style="margin-bottom: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <span style="font-size: 14px; color: var(--text-body);">
                            Found <strong style="color: var(--primary);">${counts.total}</strong> results for 
                            "<strong>${data.query}</strong>"
                        </span>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        `;

        // Summary badges
        if (counts.tenants > 0) {
            html += `<span class="badge" style="background: #e0f2fe; color: #0284c7;"><i class="fa-solid fa-building"></i> ${counts.tenants} Tenants</span>`;
        }
        if (counts.users > 0) {
            html += `<span class="badge" style="background: #f0fdf4; color: #16a34a;"><i class="fa-solid fa-users"></i> ${counts.users} Users</span>`;
        }
        if (counts.plans > 0) {
            html += `<span class="badge" style="background: #fef3c7; color: #d97706;"><i class="fa-solid fa-layer-group"></i> ${counts.plans} Plans</span>`;
        }
        if (counts.invoices > 0) {
            html += `<span class="badge" style="background: #f3e8ff; color: #9333ea;"><i class="fa-solid fa-file-invoice"></i> ${counts.invoices} Invoices</span>`;
        }
        if (counts.tickets > 0) {
            html += `<span class="badge" style="background: #ffedd5; color: #ea580c;"><i class="fa-solid fa-ticket"></i> ${counts.tickets} Tickets</span>`;
        }

        html += `</div></div></div>`;

        // Tenants Results
        if (counts.tenants > 0) {
            html += `
                <div class="result-section" style="margin-bottom: 24px;">
                    <div class="section-header" style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        <i class="fa-solid fa-building" style="color: #0284c7;"></i>
                        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Tenants</h3>
                        <span class="badge" style="background: #e0ffe0; color: #16a34a; font-size: 11px;">${counts.tenants}</span>
                    </div>
                    <div class="card" style="overflow: hidden;">
                        <table class="data-table">
                            <thead><tr><th>Name</th><th>Email</th><th>Domain</th><th>Plan</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
            `;
            results.tenants.forEach(tenant => {
                const statusColor = tenant.status === 'active' ? '#16a34a' : (tenant.status === 'suspended' ? '#dc2626' : '#d97706');
                html += `
                    <tr>
                        <td><a href="tenant-management.php?view=${tenant.id}" style="color: var(--primary); font-weight: 600;">${tenant.name}</a></td>
                        <td>${tenant.email}</td>
                        <td>${tenant.domain}</td>
                        <td><span class="badge" style="background: #fef3c7; color: #d97706;">${tenant.plan}</span></td>
                        <td><span class="badge" style="background: ${statusColor}20; color: ${statusColor};">${tenant.status}</span></td>
                        <td>
                            <a href="tenant-management.php?view=${tenant.id}" class="btn-icon" title="View"><i class="fa-solid fa-eye"></i></a>
                        </td>
                    </tr>
                `;
            });
            html += `</tbody></table></div></div>`;
        }

        // Users Results
        if (counts.users > 0) {
            html += `
                <div class="result-section" style="margin-bottom: 24px;">
                    <div class="section-header" style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        <i class="fa-solid fa-users" style="color: #16a34a;"></i>
                        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Users</h3>
                        <span class="badge" style="background: #f0fdf4; color: #16a34a; font-size: 11px;">${counts.users}</span>
                    </div>
                    <div class="card" style="overflow: hidden;">
                        <table class="data-table">
                            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
            `;
            results.users.forEach(user => {
                const statusColor = user.status === 'active' ? '#16a34a' : '#dc2626';
                const initial = user.name ? user.name.charAt(0).toUpperCase() : 'U';
                html += `
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600;">${initial}</div>
                                ${user.name}
                            </div>
                        </td>
                        <td>${user.email}</td>
                        <td><span class="badge" style="background: #f3e8ff; color: #9333ea;">${user.role}</span></td>
                        <td><span class="badge" style="background: ${statusColor}20; color: ${statusColor};">${user.status}</span></td>
                        <td><a href="users.php?view=${user.id}" class="btn-icon" title="View"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>
                `;
            });
            html += `</tbody></table></div></div>`;
        }

        // Plans Results
        if (counts.plans > 0) {
            html += `
                <div class="result-section" style="margin-bottom: 24px;">
                    <div class="section-header" style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        <i class="fa-solid fa-layer-group" style="color: #d97706;"></i>
                        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Plans</h3>
                        <span class="badge" style="background: #fef3c7; color: #d97706; font-size: 11px;">${counts.plans}</span>
                    </div>
                    <div class="card" style="overflow: hidden;">
                        <table class="data-table">
                            <thead><tr><th>Plan Name</th><th>Price</th><th>Billing</th><th>Actions</th></tr></thead>
                            <tbody>
            `;
            results.plans.forEach(plan => {
                html += `
                    <tr>
                        <td><a href="plans.php?edit=${plan.id}" style="color: var(--primary); font-weight: 600;">${plan.name}</a></td>
                        <td><span style="font-weight: 700;">$${parseFloat(plan.price).toFixed(2)}</span></td>
                        <td>${plan.billing_cycle}</td>
                        <td><a href="plans.php?edit=${plan.id}" class="btn-icon" title="Edit"><i class="fa-solid fa-pen"></i></a></td>
                    </tr>
                `;
            });
            html += `</tbody></table></div></div>`;
        }

        // Invoices Results
        if (counts.invoices > 0) {
            html += `
                <div class="result-section" style="margin-bottom: 24px;">
                    <div class="section-header" style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        <i class="fa-solid fa-file-invoice" style="color: #9333ea;"></i>
                        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Invoices</h3>
                        <span class="badge" style="background: #f3e8ff; color: #9333ea; font-size: 11px;">${counts.invoices}</span>
                    </div>
                    <div class="card" style="overflow: hidden;">
                        <table class="data-table">
                            <thead><tr><th>Invoice #</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
            `;
            results.invoices.forEach(invoice => {
                const statusColor = invoice.status === 'paid' ? '#16a34a' : '#d97706';
                html += `
                    <tr>
                        <td><a href="invoices.php?view=${invoice.id}" style="color: var(--primary); font-weight: 600;">${invoice.invoice_number}</a></td>
                        <td><span style="font-weight: 700;">$${parseFloat(invoice.amount).toFixed(2)}</span></td>
                        <td><span class="badge" style="background: ${statusColor}20; color: ${statusColor};">${invoice.status}</span></td>
                        <td><a href="invoices.php?view=${invoice.id}" class="btn-icon" title="View"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>
                `;
            });
            html += `</tbody></table></div></div>`;
        }

        // Tickets Results
        if (counts.tickets > 0) {
            html += `
                <div class="result-section" style="margin-bottom: 24px;">
                    <div class="section-header" style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        <i class="fa-solid fa-ticket" style="color: #ea580c;"></i>
                        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Support Tickets</h3>
                        <span class="badge" style="background: #ffedd5; color: #ea580c; font-size: 11px;">${counts.tickets}</span>
                    </div>
                    <div class="card" style="overflow: hidden;">
                        <table class="data-table">
                            <thead><tr><th>Subject</th><th>Priority</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
            `;
            results.tickets.forEach(ticket => {
                const priorityColor = ticket.priority === 'high' ? '#dc2626' : (ticket.priority === 'medium' ? '#d97706' : '#16a34a');
                const statusColor = ticket.status === 'open' ? '#16a34a' : '#6b7280';
                html += `
                    <tr>
                        <td><a href="support-tickets.php?view=${ticket.id}" style="color: var(--primary); font-weight: 600;">${ticket.subject}</a></td>
                        <td><span class="badge" style="background: ${priorityColor}20; color: ${priorityColor};">${ticket.priority}</span></td>
                        <td><span class="badge" style="background: ${statusColor}20; color: ${statusColor};">${ticket.status}</span></td>
                        <td><a href="support-tickets.php?view=${ticket.id}" class="btn-icon" title="View"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>
                `;
            });
            html += `</tbody></table></div></div>`;
        }

        searchResults.innerHTML = html;
    }

    // Debounce function
    function debounce(func, delay) {
        return function(...args) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => func.apply(this, args), delay);
        };
    }

    // Event listeners
    searchInput.addEventListener('input', debounce(performSearch, DEBOUNCE_DELAY));
    searchType.addEventListener('change', debounce(performSearch, DEBOUNCE_DELAY));
    
    // Initial search if query exists
    if (searchInput.value.length >= 2) {
        performSearch();
    }
})();
</script>

<!-- Additional Styles for Search -->
<style>
.search-loader {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: translateY(-50%) rotate(0deg); }
    to { transform: translateY(-50%) rotate(360deg); }
}
.data-table {
    width: 100%;
    border-collapse: collapse;
}
.data-table th,
.data-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
}
.data-table th {
    background: var(--bg-secondary);
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    color: var(--text-light);
}
.data-table tbody tr:hover {
    background: var(--bg-secondary);
}
.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    color: var(--text-light);
    text-decoration: none;
    transition: all 0.2s;
}
.btn-icon:hover {
    background: var(--primary);
    color: white;
}
</style>
