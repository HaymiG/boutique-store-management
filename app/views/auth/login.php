<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sign in to Boutique Store Management System">
    <title>Login — Boutique Store</title>
    <link rel="stylesheet" href="/frontend/css/base.css">
    <link rel="stylesheet" href="/frontend/css/auth.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<div class="auth-container">
    <div class="auth-form">
        <!-- Logo -->
        <div style="text-align:center;margin-bottom:2.5rem">
            <div class="logo" style="margin-bottom:0.5rem;font-size:1.5rem">
                Boutique<span style="font-weight:300;margin-left:4px">Store</span>
            </div>
            <p style="color:var(--text-secondary);font-size:0.875rem">Sign in to your account</p>
        </div>

        <!-- Flash Messages -->
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success" id="flashSuccess">
                <i data-lucide="check-circle" style="width:16px;height:16px"></i>
                <span><?= htmlspecialchars($flashSuccess) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-error" id="flashError">
                <i data-lucide="alert-circle" style="width:16px;height:16px"></i>
                <span><?= htmlspecialchars($flashError) ?></span>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form id="loginForm" method="POST" action="/login" novalidate>
            <?= $csrfField ?>

            <div class="form-group">
                <label class="form-label" for="loginEmail">Email</label>
                <div class="input-icon-wrapper">
                    <i data-lucide="mail" class="input-icon"></i>
                    <input 
                        type="email" 
                        id="loginEmail" 
                        name="email" 
                        class="form-control form-control-icon" 
                        placeholder="you@example.com"
                        value="<?= htmlspecialchars($session->getFlash('old_email', '')) ?>"
                        autocomplete="email"
                        required
                    >
                </div>
                <span class="field-error" id="emailError"></span>
            </div>

            <div class="form-group">
                <label class="form-label" for="loginPassword">Password</label>
                <div class="input-icon-wrapper">
                    <i data-lucide="lock" class="input-icon"></i>
                    <input 
                        type="password" 
                        id="loginPassword" 
                        name="password" 
                        class="form-control form-control-icon" 
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="toggle-password" onclick="togglePassword()" tabindex="-1">
                        <i data-lucide="eye" id="eyeIcon" style="width:18px;height:18px"></i>
                    </button>
                </div>
                <span class="field-error" id="passwordError"></span>
            </div>

            <div class="form-group" style="flex-direction:row;align-items:center;justify-content:space-between">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember_me" id="rememberMe" value="1">
                    <span class="checkmark"></span>
                    Remember me
                </label>
            </div>

            <button type="submit" id="loginBtn" class="btn btn-primary" style="width:100%;padding:0.875rem;font-size:0.9rem;margin-top:0.5rem">
                <span id="btnText">Log in</span>
                <span id="btnSpinner" class="spinner" style="display:none"></span>
            </button>
        </form>

        <!-- Footer -->
        <div style="text-align:center;margin-top:2.5rem;border-top:1px solid var(--border);padding-top:1.5rem">
            <p style="font-size:0.75rem;color:var(--text-muted);margin-bottom:1rem;line-height:1.6">
                Default credentials:<br>
                admin@boutique.com · store@boutique.com · seller@boutique.com<br>
                Password: Password123
            </p>
            <a href="/register" style="font-size:0.8rem;color:var(--accent-orange);font-weight:500">Create an account →</a>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    // Toggle password visibility
    function togglePassword() {
        const input = document.getElementById('loginPassword');
        const icon = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    }

    // Client-side validation
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        let valid = true;
        const email = document.getElementById('loginEmail');
        const password = document.getElementById('loginPassword');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');

        // Reset
        emailError.textContent = '';
        passwordError.textContent = '';
        email.classList.remove('is-invalid');
        password.classList.remove('is-invalid');

        // Email validation
        if (!email.value.trim()) {
            emailError.textContent = 'Email is required';
            email.classList.add('is-invalid');
            valid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            emailError.textContent = 'Enter a valid email address';
            email.classList.add('is-invalid');
            valid = false;
        }

        // Password validation
        if (!password.value) {
            passwordError.textContent = 'Password is required';
            password.classList.add('is-invalid');
            valid = false;
        } else if (password.value.length < 6) {
            passwordError.textContent = 'Password must be at least 6 characters';
            password.classList.add('is-invalid');
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
            return;
        }

        // Show loading state
        const btn = document.getElementById('loginBtn');
        document.getElementById('btnText').textContent = 'Signing in...';
        document.getElementById('btnSpinner').style.display = 'inline-block';
        btn.style.opacity = '0.7';
        btn.disabled = true;
    });

    // Auto-dismiss flash messages
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(-10px)';
            setTimeout(() => el.remove(), 300);
        });
    }, 5000);
</script>

</body>
</html>
