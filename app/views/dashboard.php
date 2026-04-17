<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Boutique Store</title>
    <link rel="stylesheet" href="/frontend/css/base.css">
    <link rel="stylesheet" href="/frontend/css/dashboard.css">
    <link rel="stylesheet" href="/frontend/css/admin.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<?php
    $role = $currentUser['role'] ?? 'seller';
    $fullName = $currentUser['full_name'] ?? 'User';
    $initials = $currentUser['initials'] ?? 'U';
    $roleName = $currentUser['role_name'] ?? 'User';
?>

<div class="app-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header d-flex justify-between align-center">
            <div class="logo">Boutique<span style="font-weight:300;margin-left:4px">Store</span></div>
            <button class="btn" id="mobileMenuClose" style="display:none"><i data-lucide="x"></i></button>
        </div>

        <nav class="sidebar-nav" id="sidebarNav">
            <!-- Common Navigation -->
            <a href="#view-overview" class="nav-item active" data-view="overview" onclick="switchView('overview', this)">
                <i data-lucide="layout-dashboard" class="nav-icon"></i>
                <span>Overview</span>
            </a>

            <?php if ($role === 'manager'): ?>
                <!-- Manager Navigation -->
                <div class="nav-section-title">Management</div>
                <a href="/manager/users" class="nav-item">
                    <i data-lucide="users" class="nav-icon"></i>
                    <span>Users</span>
                </a>
                <a href="#view-branches" class="nav-item" data-view="branches" onclick="switchView('branches', this)">
                    <i data-lucide="building-2" class="nav-icon"></i>
                    <span>Branches</span>
                </a>
                <a href="#view-inventory" class="nav-item" data-view="inventory" onclick="switchView('inventory', this)">
                    <i data-lucide="package" class="nav-icon"></i>
                    <span>Inventory</span>
                </a>
                <div class="nav-section-title">Analytics</div>
                <a href="#view-reports" class="nav-item" data-view="reports" onclick="switchView('reports', this)">
                    <i data-lucide="bar-chart-3" class="nav-icon"></i>
                    <span>Reports</span>
                </a>
            <?php elseif ($role === 'store_keeper'): ?>
                <!-- Store Keeper Navigation -->
                <div class="nav-section-title">Inventory</div>
                <a href="#view-inventory" class="nav-item" data-view="inventory" onclick="switchView('inventory', this)">
                    <i data-lucide="package" class="nav-icon"></i>
                    <span>Inventory</span>
                </a>
                <a href="#view-stock-ops" class="nav-item" data-view="stock-ops" onclick="switchView('stock-ops', this)">
                    <i data-lucide="alert-triangle" class="nav-icon"></i>
                    <span>Stock Alerts</span>
                </a>
                <div class="nav-section-title">Reports</div>
                <a href="#view-reports" class="nav-item" data-view="reports" onclick="switchView('reports', this)">
                    <i data-lucide="bar-chart-3" class="nav-icon"></i>
                    <span>Reports</span>
                </a>
            <?php else: ?>
                <!-- Seller Navigation -->
                <div class="nav-section-title">Sales</div>
                <a href="#view-pos" class="nav-item" data-view="pos" onclick="switchView('pos', this)">
                    <i data-lucide="shopping-cart" class="nav-icon"></i>
                    <span>Point of Sale</span>
                </a>
                <a href="#view-my-sales" class="nav-item" data-view="my-sales" onclick="switchView('my-sales', this)">
                    <i data-lucide="receipt" class="nav-icon"></i>
                    <span>My Sales</span>
                </a>
                <div class="nav-section-title">Catalog</div>
                <a href="#view-inventory" class="nav-item" data-view="inventory" onclick="switchView('inventory', this)">
                    <i data-lucide="package" class="nav-icon"></i>
                    <span>Inventory</span>
                </a>
            <?php endif; ?>
        </nav>

        <div style="padding:var(--space-lg);border-top:1px solid var(--border)">
            <form method="POST" action="/logout" style="margin:0">
                <?= $csrfField ?>
                <button type="submit" class="nav-item" style="width:100%;border:none;background:transparent;padding:0;cursor:pointer">
                    <i data-lucide="log-out" class="nav-icon"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <div class="d-flex align-center gap-md">
                <button class="btn" id="mobileMenuBtn" style="padding:0.5rem;display:none"><i data-lucide="menu"></i></button>
                <div style="font-size:0.8rem;color:var(--text-secondary)" id="activeBranchDisplay">Boutique Store Management</div>
            </div>
            <div class="topbar-right">
                <div class="user-profile">
                    <div style="display:flex;flex-direction:column;text-align:right">
                        <span style="font-size:0.875rem;font-weight:600"><?= htmlspecialchars($fullName) ?></span>
                        <span style="font-size:0.7rem;color:var(--text-secondary)"><?= htmlspecialchars($roleName) ?></span>
                    </div>
                    <div class="avatar"><?= htmlspecialchars($initials) ?></div>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <?php if (!empty($flashSuccess)): ?>
            <div class="content-flash">
                <div class="alert alert-success">
                    <i data-lucide="check-circle" style="width:16px;height:16px"></i>
                    <span><?= htmlspecialchars($flashSuccess) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Scrollable Content -->
        <div class="content-area">
            <!-- OVERVIEW -->
            <section id="view-overview" class="view-section">
                <div class="page-header">
                    <h1 class="page-title">Overview</h1>
                    <p style="color:var(--text-secondary);font-size:0.875rem">Welcome back, <?= htmlspecialchars($currentUser['first_name'] ?? 'User') ?>.</p>
                </div>

                <div class="widget-grid">
                    <div class="widget hover-lift">
                        <div class="widget-icon" style="background:#e8f5e9;color:var(--success)">
                            <i data-lucide="dollar-sign"></i>
                        </div>
                        <div>
                            <div class="widget-title">Today's Revenue</div>
                            <div class="widget-value">$<?= number_format($stats['today_revenue'] ?? 0, 2) ?></div>
                        </div>
                    </div>
                    <div class="widget hover-lift">
                        <div class="widget-icon" style="background:#e3f2fd;color:#1565c0">
                            <i data-lucide="package"></i>
                        </div>
                        <div>
                            <div class="widget-title">Active Items</div>
                            <div class="widget-value"><?= number_format($stats['active_items'] ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="widget hover-lift">
                        <div class="widget-icon" style="background:#fff3e0;color:var(--warning)">
                            <i data-lucide="building-2"></i>
                        </div>
                        <div>
                            <div class="widget-title">Active Branches</div>
                            <div class="widget-value"><?= $stats['active_branches'] ?? 0 ?></div>
                        </div>
                    </div>
                    <?php if ($role === 'manager'): ?>
                        <div class="widget hover-lift">
                            <div class="widget-icon" style="background:#f3e5f5;color:#7b1fa2">
                                <i data-lucide="users"></i>
                            </div>
                            <div>
                                <div class="widget-title">Active Users</div>
                                <div class="widget-value"><?= $stats['active_users'] ?? 0 ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($role === 'manager' && ($stats['low_stock_count'] ?? 0) > 0): ?>
                    <div class="alert alert-warning" style="margin-bottom:var(--space-xl)">
                        <i data-lucide="alert-triangle" style="width:16px;height:16px"></i>
                        <span><strong><?= $stats['low_stock_count'] ?></strong> items are below reorder level.</span>
                    </div>
                <?php endif; ?>

                <!-- Recent Transactions -->
                <div class="data-panel">
                    <h3 style="margin-bottom:var(--space-lg);font-size:0.875rem;font-weight:600">Recent Transactions</h3>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Transaction #</th>
                                    <?php if ($role !== 'seller'): ?><th>Associate</th><?php endif; ?>
                                    <th>Branch</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentSales)): ?>
                                    <tr><td colspan="<?= $role !== 'seller' ? 5 : 4 ?>" style="text-align:center;color:var(--text-muted);padding:2rem">No transactions yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentSales as $sale): ?>
                                        <tr>
                                            <td><?= date('M j, Y', strtotime($sale['created_at'])) ?></td>
                                            <td style="font-family:monospace;font-size:0.8rem"><?= htmlspecialchars($sale['transaction_number']) ?></td>
                                            <?php if ($role !== 'seller'): ?>
                                                <td><?= htmlspecialchars($sale['first_name'] . ' ' . $sale['last_name']) ?></td>
                                            <?php endif; ?>
                                            <td><?= htmlspecialchars($sale['branch_name']) ?></td>
                                            <td style="font-weight:600">$<?= number_format($sale['final_amount'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </main>
</div>

<script src="/frontend/js/dashboard.js"></script>
<script>
    lucide.createIcons();

    // View switching for SPA-like navigation
    function switchView(viewName, element) {
        // Hide all sections
        document.querySelectorAll('.view-section').forEach(s => s.style.display = 'none');
        // Show target
        const target = document.getElementById('view-' + viewName);
        if (target) target.style.display = 'block';
        // Update active nav
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
        if (element) element.classList.add('active');
    }

    // Auto-dismiss flash
    setTimeout(() => {
        document.querySelectorAll('.content-flash .alert').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(-10px)';
            setTimeout(() => el.parentElement.remove(), 300);
        });
    }, 4000);

    // Mobile menu toggle
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const mobileClose = document.getElementById('mobileMenuClose');
    const sidebar = document.getElementById('sidebar');

    if (mobileBtn) mobileBtn.addEventListener('click', () => sidebar.classList.add('open'));
    if (mobileClose) mobileClose.addEventListener('click', () => sidebar.classList.remove('open'));
</script>

</body>
</html>
