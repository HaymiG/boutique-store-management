<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Users') ?> — Boutique Store</title>
    <link rel="stylesheet" href="/frontend/css/base.css">
    <link rel="stylesheet" href="/frontend/css/dashboard.css">
    <link rel="stylesheet" href="/frontend/css/admin.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<?php
    $fullName = $currentUser['full_name'] ?? 'Admin';
    $initials = $currentUser['initials'] ?? 'A';
    $roleName = $currentUser['role_name'] ?? 'Manager';
    $usersData = $users['data'] ?? [];
    $totalPages = $users['pages'] ?? 1;
    $currentPage = $users['current_page'] ?? 1;
    $totalUsers = $users['total'] ?? 0;
?>

<div class="app-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header d-flex justify-between align-center">
            <div class="logo">Boutique<span style="font-weight:300;margin-left:4px">Store</span></div>
        </div>
        <nav class="sidebar-nav">
            <a href="/dashboard" class="nav-item">
                <i data-lucide="layout-dashboard" class="nav-icon"></i>
                <span>Overview</span>
            </a>
            <div class="nav-section-title">Management</div>
            <a href="/manager/users" class="nav-item active">
                <i data-lucide="users" class="nav-icon"></i>
                <span>Users</span>
            </a>
            <a href="/dashboard#view-branches" class="nav-item">
                <i data-lucide="building-2" class="nav-icon"></i>
                <span>Branches</span>
            </a>
            <a href="/dashboard#view-inventory" class="nav-item">
                <i data-lucide="package" class="nav-icon"></i>
                <span>Inventory</span>
            </a>
            <div class="nav-section-title">Analytics</div>
            <a href="/dashboard#view-reports" class="nav-item">
                <i data-lucide="bar-chart-3" class="nav-icon"></i>
                <span>Reports</span>
            </a>
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
                <div style="font-size:0.8rem;color:var(--text-secondary)">User Management</div>
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

        <!-- Content Area -->
        <div class="content-area">
            <!-- Flash Messages -->
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success" style="margin-bottom:var(--space-lg)">
                    <i data-lucide="check-circle" style="width:16px;height:16px"></i>
                    <span><?= htmlspecialchars($flashSuccess) ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($flashError)): ?>
                <div class="alert alert-error" style="margin-bottom:var(--space-lg)">
                    <i data-lucide="alert-circle" style="width:16px;height:16px"></i>
                    <span><?= htmlspecialchars($flashError) ?></span>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="page-header d-flex justify-between align-center" style="flex-wrap:wrap;gap:1rem">
                <div>
                    <h1 class="page-title">Users</h1>
                    <p style="color:var(--text-secondary);font-size:0.85rem;margin-top:0.25rem"><?= $totalUsers ?> total users</p>
                </div>
                <a href="/manager/users/create" class="btn btn-primary">
                    <i data-lucide="plus" style="width:16px;height:16px"></i>
                    Add User
                </a>
            </div>

            <!-- Filters -->
            <div class="filter-bar">
                <form method="GET" action="/manager/users" class="filter-form" id="filterForm">
                    <div class="filter-group">
                        <div class="input-icon-wrapper" style="min-width:240px">
                            <i data-lucide="search" class="input-icon" style="width:16px;height:16px"></i>
                            <input type="text" name="search" class="form-control form-control-icon" 
                                   placeholder="Search users..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                        </div>

                        <select name="role_id" class="form-control form-select" onchange="this.form.submit()">
                            <option value="">All Roles</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role->id ?>" <?= ($filters['role_id'] ?? '') == $role->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="is_active" class="form-control form-select" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
                        </select>

                        <button type="submit" class="btn btn-outline" style="padding:0.5rem 1rem">
                            <i data-lucide="filter" style="width:14px;height:14px"></i>
                            Filter
                        </button>

                        <?php if (!empty($filters['search']) || !empty($filters['role_id']) || isset($filters['is_active']) && $filters['is_active'] !== ''): ?>
                            <a href="/manager/users" class="btn btn-outline" style="padding:0.5rem 1rem;color:var(--danger)">
                                <i data-lucide="x" style="width:14px;height:14px"></i>
                                Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="data-panel">
                <div class="table-wrapper">
                    <table id="usersTable">
                        <thead>
                            <tr>
                                <th style="width:40px">#</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Branch</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th style="text-align:right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usersData)): ?>
                                <tr>
                                    <td colspan="8" style="text-align:center;color:var(--text-muted);padding:3rem">
                                        <i data-lucide="users" style="width:32px;height:32px;margin-bottom:0.5rem;opacity:0.3"></i>
                                        <p>No users found.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usersData as $u): ?>
                                    <tr>
                                        <td style="color:var(--text-muted);font-size:0.8rem"><?= $u->id ?></td>
                                        <td>
                                            <div class="user-cell">
                                                <div class="avatar avatar-sm"><?= htmlspecialchars($u->getInitials()) ?></div>
                                                <div>
                                                    <div style="font-weight:500"><?= htmlspecialchars($u->getFullName()) ?></div>
                                                    <div style="font-size:0.75rem;color:var(--text-muted)">@<?= htmlspecialchars($u->username) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="font-size:0.85rem"><?= htmlspecialchars($u->email) ?></td>
                                        <td>
                                            <?php
                                                $roleClass = match($u->role_name ?? '') {
                                                    'Manager' => 'badge-role-manager',
                                                    'Store Keeper' => 'badge-role-keeper',
                                                    'Seller' => 'badge-role-seller',
                                                    default => ''
                                                };
                                            ?>
                                            <span class="badge <?= $roleClass ?>"><?= htmlspecialchars($u->role_name ?? 'Unknown') ?></span>
                                        </td>
                                        <td style="font-size:0.85rem"><?= htmlspecialchars($u->branch_name ?? '—') ?></td>
                                        <td>
                                            <?php if ($u->is_active): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-size:0.8rem;color:var(--text-secondary)">
                                            <?= $u->last_login ? date('M j, H:i', strtotime($u->last_login)) : 'Never' ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="/manager/users/<?= $u->id ?>/edit" class="btn-icon" title="Edit">
                                                    <i data-lucide="pencil" style="width:15px;height:15px"></i>
                                                </a>
                                                <?php if ($u->id != $currentUser['id']): ?>
                                                    <form method="POST" action="/manager/users/<?= $u->id ?>" 
                                                          onsubmit="return confirm('Are you sure you want to <?= $u->is_active ? 'deactivate' : 'reactivate' ?> this user?')"
                                                          style="display:inline">
                                                        <?= $csrfField ?>
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="submit" class="btn-icon btn-icon-danger" title="<?= $u->is_active ? 'Deactivate' : 'Delete' ?>">
                                                            <i data-lucide="<?= $u->is_active ? 'user-x' : 'trash-2' ?>" style="width:15px;height:15px"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?= $currentPage - 1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="pagination-btn">
                                <i data-lucide="chevron-left" style="width:16px;height:16px"></i> Prev
                            </a>
                        <?php endif; ?>

                        <div class="pagination-pages">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?= $i ?>&<?= http_build_query(array_filter($filters)) ?>" 
                                   class="pagination-page <?= $i === $currentPage ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?= $currentPage + 1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="pagination-btn">
                                Next <i data-lucide="chevron-right" style="width:16px;height:16px"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
    lucide.createIcons();

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
