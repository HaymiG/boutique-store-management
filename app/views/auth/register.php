<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Create a new Boutique Store Management account">
    <title>Register — Boutique Store</title>
    <link rel="stylesheet" href="/frontend/css/base.css">
    <link rel="stylesheet" href="/frontend/css/auth.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<?php
    $old = $session->getFlash('old', []);
    $errors = $session->getFlash('errors', []);
?>

<div class="auth-container">
    <div class="auth-form" style="max-width:480px">
        <!-- Logo -->
        <div style="text-align:center;margin-bottom:2rem">
            <div class="logo" style="margin-bottom:0.5rem;font-size:1.5rem">
                Boutique<span style="font-weight:300;margin-left:4px">Store</span>
            </div>
            <p style="color:var(--text-secondary);font-size:0.875rem">Create your account</p>
        </div>

        <!-- Flash Messages -->
        <?php if (!empty($flashError)): ?>
            <div class="alert alert-error">
                <i data-lucide="alert-circle" style="width:16px;height:16px"></i>
                <span><?= htmlspecialchars($flashError) ?></span>
            </div>
        <?php endif; ?>

        <!-- Register Form -->
        <form id="registerForm" method="POST" action="/register" novalidate>
            <?= $csrfField ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label" for="firstName">First Name</label>
                    <input type="text" id="firstName" name="first_name" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                           value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" placeholder="John" required>
                    <?php if (isset($errors['first_name'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['first_name']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="last_name" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                           value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" placeholder="Doe" required>
                    <?php if (isset($errors['last_name'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['last_name']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                       value="<?= htmlspecialchars($old['username'] ?? '') ?>" placeholder="johndoe" required>
                <?php if (isset($errors['username'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['username']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="you@example.com" required>
                <?php if (isset($errors['email'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                       placeholder="Min 8 chars, uppercase, lowercase, number" required>
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <span class="strength-text" id="strengthText"></span>
                <?php if (isset($errors['password'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="passwordConfirmation">Confirm Password</label>
                <input type="password" id="passwordConfirmation" name="password_confirmation" class="form-control <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>" 
                       placeholder="Re-enter your password" required>
                <?php if (isset($errors['password_confirmation'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password_confirmation']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" id="registerBtn" class="btn btn-primary" style="width:100%;padding:0.875rem;font-size:0.9rem;margin-top:0.5rem">
                <span id="btnText">Create Account</span>
                <span id="btnSpinner" class="spinner" style="display:none"></span>
            </button>
        </form>

        <!-- Footer -->
        <div style="text-align:center;margin-top:2rem;border-top:1px solid var(--border);padding-top:1.5rem">
            <p style="font-size:0.85rem;color:var(--text-secondary)">
                Already have an account? <a href="/login" style="color:var(--accent-orange);font-weight:500">Log in →</a>
            </p>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    // Password strength indicator
    document.getElementById('password').addEventListener('input', function() {
        const val = this.value;
        const bar = document.getElementById('strengthBar');
        const text = document.getElementById('strengthText');
        let score = 0;

        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[a-z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = ['', 'Weak', 'Fair', 'Good', 'Strong', 'Excellent'];
        const colors = ['', '#d13b3b', '#cc7700', '#cc7700', '#128a2e', '#128a2e'];
        const widths = ['0%', '20%', '40%', '60%', '80%', '100%'];

        bar.style.width = widths[score];
        bar.style.background = colors[score];
        text.textContent = val.length > 0 ? levels[score] : '';
        text.style.color = colors[score];
    });

    // Form validation
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        let valid = true;
        const fields = ['firstName', 'lastName', 'username', 'email', 'password', 'passwordConfirmation'];
        
        fields.forEach(f => {
            const el = document.getElementById(f);
            if (el) el.classList.remove('is-invalid');
        });

        const password = document.getElementById('password').value;
        const confirm = document.getElementById('passwordConfirmation').value;

        if (password !== confirm) {
            document.getElementById('passwordConfirmation').classList.add('is-invalid');
            valid = false;
        }

        if (valid) {
            const btn = document.getElementById('registerBtn');
            document.getElementById('btnText').textContent = 'Creating account...';
            document.getElementById('btnSpinner').style.display = 'inline-block';
            btn.style.opacity = '0.7';
            btn.disabled = true;
        } else {
            e.preventDefault();
        }
    });
</script>

</body>
</html>
