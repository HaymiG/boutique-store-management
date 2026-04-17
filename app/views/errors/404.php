<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Not Found | Boutique Store</title>
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
            color: var(--text-muted);
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
            <div class="error-code">404</div>
            <h1 class="error-title">Page Not Found</h1>
            <p class="error-message">
                The page you're looking for doesn't exist or has been moved.
            </p>
            <div class="error-actions">
                <a href="/dashboard" class="btn btn-primary">Go to Dashboard</a>
                <a href="/" class="btn btn-outline">Home</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php

/**
 * 404 Not Found Error View
 */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found — Boutique Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .error-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px 40px;
            max-width: 500px;
            text-align: center;
        }
        
        .error-code {
            font-size: 120px;
            font-weight: 700;
            color: #f56565;
            line-height: 1;
            margin-bottom: 20px;
        }
        
        .error-title {
            font-size: 28px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 12px;
        }
        
        .error-message {
            font-size: 16px;
            color: #718096;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 28px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #2d3748;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        
        <h1 class="error-title">Page Not Found</h1>
        
        <p class="error-message">
            The page you're looking for doesn't exist or has been moved.
        </p>
        
        <div class="error-actions">
            <a href="/" class="btn btn-primary">Back to Home</a>
            <a href="/login.html" class="btn btn-secondary">Login</a>
        </div>
    </div>
</body>
</html>
