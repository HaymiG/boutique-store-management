// ===================================
// ADMIN.JS — User Management Logic
// ===================================

document.addEventListener('DOMContentLoaded', () => {
    // Initialize lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // ===================================
    // Delete Confirmation
    // ===================================
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // ===================================
    // Search with debounce
    // ===================================
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                this.closest('form').submit();
            }, 500);
        });
    }

    // ===================================
    // CSRF Token helper for AJAX
    // ===================================
    window.getCsrfToken = function() {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) return csrfMeta.getAttribute('content');
        
        const csrfInput = document.querySelector('input[name="_csrf_token"]');
        if (csrfInput) return csrfInput.value;
        
        return '';
    };

    // ===================================
    // Flash message auto-dismiss
    // ===================================
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            el.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-10px)';
            setTimeout(() => el.remove(), 300);
        });
    }, 5000);

    // ===================================
    // Form validation enhancement
    // ===================================
    const forms = document.querySelectorAll('form[novalidate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                field.classList.remove('is-invalid');
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                // Focus first invalid field
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.focus();
            }
        });
    });

    // ===================================
    // Password match validation
    // ===================================
    const passwordField = document.getElementById('password');
    const confirmField = document.getElementById('passwordConfirmation');
    
    if (passwordField && confirmField) {
        confirmField.addEventListener('input', function() {
            if (this.value && this.value !== passwordField.value) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }

    // ===================================
    // Table row click to edit
    // ===================================
    document.querySelectorAll('table tbody tr[data-href]').forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function(e) {
            if (e.target.closest('button, a, form')) return;
            window.location.href = this.getAttribute('data-href');
        });
    });
});
