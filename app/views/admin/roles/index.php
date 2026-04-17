<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles & Permissions — Boutique Store</title>
    <link rel="stylesheet" href="/frontend/css/base.css">
    <link rel="stylesheet" href="/frontend/css/dashboard.css">
    <link rel="stylesheet" href="/frontend/css/admin.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<?php
    $fullName = $currentUser['full_name'] ?? 'Admin';
    $initials = $currentUser['initials'] ?? 'A';
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
            <a href="/manager/users" class="nav-item">
                <i data-lucide="users" class="nav-icon"></i>
                <span>Users</span>
            </a>
            <a href="/manager/roles" class="nav-item active">
                <i data-lucide="shield" class="nav-icon"></i>
                <span>Roles</span>
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
        <header class="topbar">
            <div class="d-flex align-center gap-md">
                <div style="font-size:0.8rem;color:var(--text-secondary)">Roles & Permissions</div>
            </div>
            <div class="topbar-right">
                <div class="user-profile">
                    <div style="display:flex;flex-direction:column;text-align:right">
                        <span style="font-size:0.875rem;font-weight:600"><?= htmlspecialchars($fullName) ?></span>
                    </div>
                    <div class="avatar"><?= htmlspecialchars($initials) ?></div>
                </div>
            </div>
        </header>

        <div class="content-area">
            <!-- Flash Messages -->
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success" style="margin-bottom:var(--space-lg)">
                    <i data-lucide="check-circle" style="width:16px;height:16px"></i>
                    <span><?= htmlspecialchars($flashSuccess) ?></span>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h1 class="page-title">Roles & Permissions</h1>
                <p style="color:var(--text-secondary);font-size:0.85rem;margin-top:0.25rem">Manage system roles and their permissions.</p>
            </div>

            <!-- Roles List -->
            <div class="roles-grid">
                <?php foreach ($roles ?? [] as $role): ?>
                    <div class="role-card hover-lift">
                        <div class="role-header">
                            <div>
                                <h3 class="role-name"><?= htmlspecialchars($role->name) ?></h3>
                                <p class="role-desc"><?= htmlspecialchars($role->description ?? 'No description') ?></p>
                            </div>
                            <span class="badge badge-info"><?= $role->getUserCount() ?> users</span>
                        </div>

                        <div class="role-permissions">
                            <h4 class="perm-title">Permissions</h4>
                            <div class="perm-list">
                                <?php 
                                    $roleKey = array_search($role->id, ROLES);
                                    $perms = ROLE_PERMISSIONS[$roleKey] ?? [];
                                    foreach ($perms as $perm): 
                                ?>
                                    <span class="perm-tag">
                                        <i data-lucide="check" style="width:10px;height:10px"></i>
                                        <?= htmlspecialchars($perm) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Permission Matrix -->
            <div class="data-panel" style="margin-top:var(--space-xl)">
                <h3 style="margin-bottom:var(--space-lg);font-size:0.875rem;font-weight:600">Permission Matrix</h3>
                <div class="table-wrapper">
                    <table class="permission-matrix">
                        <thead>
                            <tr>
                                <th>Permission</th>
                                <?php foreach ($roles ?? [] as $role): ?>
                                    <th style="text-align:center"><?= htmlspecialchars($role->name) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $allPerms = \App\Models\Permission::getAllUniquePermissions();
                                foreach ($allPerms as $perm):
                            ?>
                                <tr>
                                    <td style="font-size:0.8rem;font-family:monospace"><?= htmlspecialchars($perm) ?></td>
                                    <?php foreach ($roles ?? [] as $role): ?>
                                        <?php
                                            $roleKey = array_search($role->id, ROLES);
                                            $hasIt = in_array($perm, ROLE_PERMISSIONS[$roleKey] ?? []);
                                        ?>
                                        <td style="text-align:center">
                                            <?php if ($hasIt): ?>
                                                <span style="color:var(--success)"><i data-lucide="check-circle" style="width:16px;height:16px"></i></span>
                                            <?php else: ?>
                                                <span style="color:var(--text-muted)"><i data-lucide="minus" style="width:16px;height:16px"></i></span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
