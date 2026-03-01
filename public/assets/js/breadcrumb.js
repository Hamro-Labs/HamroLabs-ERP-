// Breadcrumb Navigation Logic
// This module handles breadcrumb navigation across all pages

class Breadcrumb {
    constructor() {
        this.breadcrumbElement = null;
        this.currentPath = [];
        this.init();
    }

    init() {
        // Initialize breadcrumb functionality
        this.setupEventListeners();
        this.updateBreadcrumb();
    }

    setupEventListeners() {
        // Add click event listeners to breadcrumb links
        document.addEventListener('click', (e) => {
            if (e.target.closest('.breadcrumb a')) {
                e.preventDefault();
                const link = e.target.closest('.breadcrumb a');
                const page = link.getAttribute('data-page');
                this.navigateToPage(page);
            }
        });
    }

    updateBreadcrumb(path = null) {
        // Update breadcrumb based on current page or provided path
        if (!path) {
            path = this.getCurrentPagePath();
        }
        
        this.currentPath = path;
        this.renderBreadcrumb();
    }

    getCurrentPagePath() {
        // Determine current page path based on URL or page context
        const currentPage = this.getCurrentPage();
        
        switch(currentPage) {
            case 'dashboard':
                return ['Dashboard'];
            case 'tenant-management':
                return ['Dashboard', 'Tenant Management'];
            case 'plan-management':
                return ['Dashboard', 'Plan Management'];
            case 'analytics':
                return ['Dashboard', 'Analytics'];
            case 'support':
                return ['Dashboard', 'Support'];
            case 'frontdesk':
                return ['Dashboard', 'Front Desk'];
            case 'student':
                return ['Dashboard', 'Student Portal'];
            case 'teacher':
                return ['Dashboard', 'Teacher Portal'];
            case 'guardian':
                return ['Dashboard', 'Guardian Portal'];
            case 'institute-admin':
                return ['Dashboard', 'Institute Admin'];
            default:
                return ['Dashboard'];
        }
    }

    getCurrentPage() {
        // Get current page identifier from URL or context
        const url = window.location.pathname;
        const page = url.split('/').pop().split('.')[0];
        
        // Handle different page naming conventions
        if (page.includes('tenant')) return 'tenant-management';
        if (page.includes('plan')) return 'plan-management';
        if (page.includes('frontdesk')) return 'frontdesk';
        if (page.includes('student')) return 'student';
        if (page.includes('teacher')) return 'teacher';
        if (page.includes('guardian')) return 'guardian';
        if (page.includes('institute')) return 'institute-admin';
        if (page.includes('superadmin')) return 'dashboard';
        
        return page || 'dashboard';
    }

    renderBreadcrumb() {
        // Render the breadcrumb HTML
        const breadcrumbHTML = this.generateBreadcrumbHTML();
        
        // Find or create breadcrumb container
        let breadcrumbContainer = document.querySelector('.breadcrumb');
        
        if (!breadcrumbContainer) {
            // Create breadcrumb container if it doesn't exist
            breadcrumbContainer = document.createElement('div');
            breadcrumbContainer.className = 'breadcrumb';
            
            // Insert breadcrumb at the beginning of the page header
            const pageHeader = document.querySelector('.pg-hdr-left') || 
                              document.querySelector('.page-header') ||
                              document.querySelector('.header');
            
            if (pageHeader) {
                pageHeader.insertBefore(breadcrumbContainer, pageHeader.firstChild);
            }
        }
        
        breadcrumbContainer.innerHTML = breadcrumbHTML;
    }

    generateBreadcrumbHTML() {
        // Generate HTML for the breadcrumb
        return this.currentPath.map((item, index) => {
            if (index === this.currentPath.length - 1) {
                // Last item (current page) - not clickable
                return `<span>${item}</span>`;
            } else {
                // Clickable breadcrumb item
                const pageId = this.getPageId(item);
                return `<a href="#" data-page="${pageId}">${item}</a>`;
            }
        }).join('');
    }

    getPageId(pageName) {
        // Map page names to page identifiers
        const pageMap = {
            'Dashboard': 'dashboard',
            'Tenant Management': 'tenant-management',
            'Plan Management': 'plan-management',
            'Analytics': 'analytics',
            'Support': 'support',
            'Front Desk': 'frontdesk',
            'Student Portal': 'student',
            'Teacher Portal': 'teacher',
            'Guardian Portal': 'guardian',
            'Institute Admin': 'institute-admin'
        };
        
        return pageMap[pageName] || 'dashboard';
    }

    navigateToPage(pageId) {
        // Handle navigation when breadcrumb link is clicked
        let targetUrl = '';
        
        switch(pageId) {
            case 'dashboard':
                targetUrl = 'super_admin.php';
                break;
            case 'tenant-management':
                targetUrl = 'tenant-management.html';
                break;
            case 'plan-management':
                targetUrl = 'plan-management.html'; // Create this page
                break;
            case 'analytics':
                targetUrl = 'analytics.html'; // Create this page
                break;
            case 'support':
                targetUrl = 'support.html'; // Create this page
                break;
            case 'frontdesk':
                targetUrl = 'frontdesk.php';
                break;
            case 'student':
                targetUrl = 'student.php';
                break;
            case 'teacher':
                targetUrl = 'teacher.php';
                break;
            case 'guardian':
                targetUrl = 'guardian.php';
                break;
            case 'institute-admin':
                targetUrl = 'instituteadmin.php';
                break;
            default:
                targetUrl = 'super_admin.php';
        }
        
        // Navigate to the target page
        window.location.href = targetUrl;
    }
}

// Initialize breadcrumb when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.breadcrumb = new Breadcrumb();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Breadcrumb;
}
