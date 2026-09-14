'use strict';

// Auto-dismiss flash messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.flash-messages .alert').forEach(function(alert) {
        setTimeout(function() {
            alert.classList.remove('show');
            setTimeout(function() { alert.remove(); }, 300);
        }, 5000);
    });

    // Confirm dialogs for destructive actions
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) e.preventDefault();
        });
    });

    // Form submit loading state
    document.querySelectorAll('form').forEach(function(form) {
        if (form.dataset.noLoading) return;
        form.addEventListener('submit', function() {
            var btn = this.querySelector('button[type="submit"]');
            if (!btn) return;
            var orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Please wait…';
            setTimeout(function() { btn.disabled = false; btn.innerHTML = orig; }, 10000);
        });
    });

    // Bootstrap tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
        new bootstrap.Tooltip(el);
    });
});

/* ── Page loading bar ──────────────────────────────────────────────────── */
function initPageLoader() {
    const loader = document.createElement('div');
    loader.id = 'pageLoader';
    document.body.prepend(loader);

    document.querySelectorAll('a[href]').forEach(a => {
        const href = a.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript')
            || href.startsWith('mailto') || a.target === '_blank') return;
        a.addEventListener('click', () => { loader.style.display = 'block'; });
    });

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => { loader.style.display = 'block'; });
    });

    window.addEventListener('pageshow', () => { loader.style.display = 'none'; });
}

/* ── Mobile sidebar toggle ─────────────────────────────────────────────── */
function initMobileSidebar() {
    const toggle  = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (!toggle || !sidebar) return;

    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);

    toggle.addEventListener('click', () => {
        document.body.classList.toggle('mobile-menu-open');
    });
    overlay.addEventListener('click', () => {
        document.body.classList.remove('mobile-menu-open');
    });

    // Close on nav link click (mobile)
    sidebar.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', () => {
            document.body.classList.remove('mobile-menu-open');
        });
    });
}

/* ── Bottom navigation active state ────────────────────────────────────── */
function initBottomNav() {
    const nav = document.querySelector('.bottom-nav');
    if (!nav) return;
    const currentPath = window.location.pathname;
    nav.querySelectorAll('a').forEach(a => {
        const href = a.getAttribute('href');
        if (href && currentPath.startsWith(href.replace(/^.*\/job-portal\/public/, ''))) {
            a.classList.add('active');
        }
    });
}

/* ── Notification count via AJAX ───────────────────────────────────────── */
function initNotifCount() {
    if (!window.__loggedIn) return;
    const badges = document.querySelectorAll('.notif-count-badge');
    if (!badges.length) return;

    const base = document.querySelector('meta[name="base-url"]')?.content || '';

    fetch(base + '/api/notifications/count', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.ok ? r.json() : null)
    .then(data => {
        if (!data) return;
        badges.forEach(b => {
            if (data.count > 0) {
                b.textContent = data.count > 99 ? '99+' : data.count;
                b.style.display = 'inline-flex';
            } else {
                b.style.display = 'none';
            }
        });
    })
    .catch(() => {});
}

/* ── Re-run on DOMContentLoaded ────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    initPageLoader();
    initMobileSidebar();
    initBottomNav();
    initNotifCount();
});

/* ── Unread message count badge ────────────────────────────────────────── */
function initMsgCount() {
    if (!window.__loggedIn) return;
    const badges = document.querySelectorAll('.msg-unread-badge');
    if (!badges.length) return;

    const base = document.querySelector('meta[name="base-url"]')?.content || '';

    function fetchCount() {
        fetch(base + '/api/messages/unread', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!data) return;
            badges.forEach(b => {
                if (data.count > 0) {
                    b.textContent = data.count > 99 ? '99+' : data.count;
                    b.style.display = 'inline-flex';
                } else {
                    b.style.display = 'none';
                }
            });
        })
        .catch(() => {});
    }

    fetchCount();
    setInterval(fetchCount, 15000); // refresh every 15 seconds
}

document.addEventListener('DOMContentLoaded', () => {
    initMsgCount();
});
