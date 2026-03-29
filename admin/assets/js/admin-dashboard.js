// Admin Dashboard JavaScript
(function() {
    'use strict';
    
    // Mobile menu toggle
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('adminSidebar');
    
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    }
    
    // Nav group toggle
    const navGroups = document.querySelectorAll('.nav-group');
    
    navGroups.forEach(group => {
        const header = group.querySelector('.nav-group-header');
        const storedState = localStorage.getItem(`navGroup_${header.textContent.trim()}`);
        
        if (storedState === 'open') {
            group.classList.add('open');
        }
        
        header.addEventListener('click', function(e) {
            e.stopPropagation();
            group.classList.toggle('open');
            localStorage.setItem(`navGroup_${header.textContent.trim()}`, group.classList.contains('open') ? 'open' : 'closed');
        });
    });
    
    // Global search functionality
    const globalSearch = document.getElementById('globalSearch');
    
    if (globalSearch) {
        let searchTimeout;
        
        globalSearch.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const searchTerm = e.target.value.toLowerCase();
            
            searchTimeout = setTimeout(() => {
                performGlobalSearch(searchTerm);
            }, 300);
        });
    }
    
    function performGlobalSearch(term) {
        if (!term) {
            // Clear highlights
            document.querySelectorAll('.search-highlight').forEach(el => {
                const parent = el.parentNode;
                parent.replaceChild(document.createTextNode(el.textContent), el);
                parent.normalize();
            });
            return;
        }
        
        // Search in current page content
        const contentElements = document.querySelectorAll('.admin-content p, .admin-content h1, .admin-content h2, .admin-content h3, .admin-content .list-item h4, .admin-content .list-item p');
        
        let found = false;
        
        contentElements.forEach(el => {
            const text = el.textContent.toLowerCase();
            if (text.includes(term)) {
                found = true;
                highlightText(el, term);
            }
        });
        
        // If no results found, could show a toast notification
        if (!found && term.length > 2) {
            showToast('No results found for "' + term + '"', 'info');
        }
    }
    
    function highlightText(element, term) {
        const regex = new RegExp(`(${term})`, 'gi');
        element.innerHTML = element.textContent.replace(regex, '<span class="search-highlight">$1</span>');
    }
    
    // Toast notification
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Add toast styles dynamically
    const style = document.createElement('style');
    style.textContent = `
        .search-highlight {
            background: rgba(243, 156, 18, 0.3);
            border-radius: 4px;
            padding: 0 2px;
        }
        
        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transform: translateX(400px);
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        
        .toast-notification.show {
            transform: translateX(0);
        }
        
        .toast-success {
            border-left: 4px solid var(--green);
        }
        
        .toast-error {
            border-left: 4px solid var(--red);
        }
        
        .toast-info {
            border-left: 4px solid var(--blue);
        }
        
        .toast-notification i {
            font-size: 1.2rem;
        }
        
        .toast-success i {
            color: var(--green);
        }
        
        .toast-error i {
            color: var(--red);
        }
        
        .toast-info i {
            color: var(--blue);
        }
    `;
    document.head.appendChild(style);
    
    // Auto-refresh stats (optional)
    let autoRefreshInterval;
    
    function startAutoRefresh() {
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
        
        autoRefreshInterval = setInterval(() => {
            // Only refresh if page is visible
            if (document.visibilityState === 'visible') {
                refreshStats();
            }
        }, 60000); // Every minute
    }
    
    function refreshStats() {
        fetch('ajax/get_stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStats(data.stats);
                }
            })
            .catch(error => console.log('Auto-refresh failed:', error));
    }
    
    function updateStats(stats) {
        // Update stats numbers without full page reload
        const statNumbers = document.querySelectorAll('.stat-info h3');
        if (statNumbers.length >= 4) {
            statNumbers[0].textContent = stats.blogCount;
            statNumbers[1].textContent = stats.testimonialsCount;
            statNumbers[2].textContent = stats.pendingTestimonials;
            statNumbers[3].textContent = stats.usersCount;
        }
    }
    
    // Start auto-refresh if on dashboard
    if (window.location.pathname.includes('index.php')) {
        startAutoRefresh();
    }
    
    // Add loading animation to all buttons
    const buttons = document.querySelectorAll('.action-btn, .login-btn, .submit-btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (this.classList.contains('no-loading')) return;
            
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            this.disabled = true;
            
            // Reset after navigation (if form submit)
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 2000);
        });
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
    
    console.log('Admin dashboard initialized');
})();