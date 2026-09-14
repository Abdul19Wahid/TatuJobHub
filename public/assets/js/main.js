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
