<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Access Denied | Boutique Store</title>
    <link rel="stylesheet" href="/frontend/css/base.css">
    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-secondary);
            text-align: center;
        }
        .error-card {
            max-width: 480px;
            padding: var(--space-2xl);
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: var(--danger);
            line-height: 1;
            margin-bottom: var(--space-sm);
            letter-spacing: -0.04em;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: var(--space-md);
            color: var(--text-primary);
        }
        .error-message {
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: var(--space-xl);
        }
        .error-actions {
            display: flex;
            gap: var(--space-md);
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-code">403</div>
            <h1 class="error-title">Access Denied</h1>
            <p class="error-message">
                <?= htmlspecialchars($message ?? 'You do not have permission to access this page. Contact your administrator if you believe this is an error.') ?>
            </p>
            <div class="error-actions">
                <a href="/dashboard" class="btn btn-primary">Go to Dashboard</a>
                <a href="/login" class="btn btn-outline">Log In</a>
            </div>
        </div>
    </div>
</body>
</html>
