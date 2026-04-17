<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'User Form') ?> — Boutique Store</title>
    <link rel="stylesheet" href="/frontend/css/base.css">
    <link rel="stylesheet" href="/frontend/css/dashboard.css">
    <link rel="stylesheet" href="/frontend/css/admin.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<?php
    $fullName = $currentUser['full_name'] ?? 'Admin';
    $initials = $currentUser['initials'] ?? 'A';
    $mode = $mode ?? 'create';
    $isEdit = $mode === 'edit';
    $isView = $mode === 'view';
    $errors = $errors ?? [];
    $old = $old ?? [];

    // Get field values — prefer old input, then user data
    $val = function($field, $default = '') use ($old, $user, $isEdit) {
        if (!empty($old[$field])) return htmlspecialchars($old[$field]);
        if ($user && isset($user->$field)) return htmlspecialchars($user->$field);
        return htmlspecialchars($default);
    };
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
                <a href="/manager/users" class="btn btn-outline" style="padding:0.4rem 0.75rem;font-size:0.8rem">
                    <i data-lucide="arrow-left" style="width:14px;height:14px"></i>
                    Back
                </a>
                <div style="font-size:0.8rem;color:var(--text-secondary)"><?= $isEdit ? 'Edit User' : ($isView ? 'View User' : 'Create User') ?></div>
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

        <!-- Content Area -->
        <div class="content-area">
            <!-- Flash Messages -->
            <?php if (!empty($flashError)): ?>
                <div class="alert alert-error" style="margin-bottom:var(--space-lg)">
                    <i data-lucide="alert-circle" style="width:16px;height:16px"></i>
                    <span><?= htmlspecialchars($flashError) ?></span>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h1 class="page-title">
                    <?php if ($isEdit): ?>
                        Edit: <?= htmlspecialchars($user->getFullName()) ?>
                    <?php elseif ($isView): ?>
                        <?= htmlspecialchars($user->getFullName()) ?>
                    <?php else: ?>
                        Create New User
                    <?php endif; ?>
                </h1>
            </div>

            <!-- User Form -->
            <div class="form-panel">
                <form method="POST" 
                      action="<?= $isEdit ? '/manager/users/' . $user->id : '/manager/users' ?>" 
                      id="userForm" novalidate>
                    <?= $csrfField ?>
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <!-- Personal Information -->
                    <div class="form-section">
                        <h3 class="form-section-title">Personal Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="firstName">First Name <span class="required">*</span></label>
                                <input type="text" id="firstName" name="first_name" 
                                       class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                                       value="<?= $val('first_name') ?>" placeholder="John"
                                       <?= $isView ? 'disabled' : '' ?> required>
                                <?php if (isset($errors['first_name'])): ?>
                                    <span class="field-error"><?= htmlspecialchars($errors['first_name']) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="lastName">Last Name <span class="required">*</span></label>
                                <input type="text" id="lastName" name="last_name" 
                                       class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                                       value="<?= $val('last_name') ?>" placeholder="Doe"
                                       <?= $isView ? 'disabled' : '' ?> required>
                                <?php if (isset($errors['last_name'])): ?>
                                    <span class="field-error"><?= htmlspecialchars($errors['last_name']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?= $val('phone') ?>" placeholder="+1 (555) 000-0000"
                                   <?= $isView ? 'disabled' : '' ?>>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="form-section">
                        <h3 class="form-section-title">Account Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="username">Username <span class="required">*</span></label>
                                <input type="text" id="username" name="username" 
                                       class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                       value="<?= $val('username') ?>" placeholder="johndoe"
                                       <?= $isView ? 'disabled' : '' ?> required>
                                <?php if (isset($errors['username'])): ?>
                                    <span class="field-error"><?= htmlspecialchars($errors['username']) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="email">Email <span class="required">*</span></label>
                                <input type="email" id="email" name="email" 
                                       class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                       value="<?= $val('email') ?>" placeholder="john@example.com"
                                       <?= $isView ? 'disabled' : '' ?> required>
                                <?php if (isset($errors['email'])): ?>
                                    <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Role & Branch -->
                    <div class="form-section">
                        <h3 class="form-section-title">Assignment</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="roleId">Role <span class="required">*</span></label>
                                <select id="roleId" name="role_id" 
                                        class="form-control form-select <?= isset($errors['role_id']) ? 'is-invalid' : '' ?>"
                                        <?= $isView ? 'disabled' : '' ?> required>
                                    <option value="">Select Role</option>
                                    <?php foreach ($roles ?? [] as $role): ?>
                                        <option value="<?= $role->id ?>" 
                                            <?= ($val('role_id') == $role->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($role->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['role_id'])): ?>
                                    <span class="field-error"><?= htmlspecialchars($errors['role_id']) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="branchId">Branch</label>
                                <select id="branchId" name="branch_id" class="form-control form-select"
                                        <?= $isView ? 'disabled' : '' ?>>
                                    <option value="">No Branch</option>
                                    <?php foreach ($branches ?? [] as $branch): ?>
                                        <option value="<?= $branch->id ?>" 
                                            <?= ($val('branch_id') == $branch->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($branch->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <label class="toggle-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" 
                                       <?= ($val('is_active', '1') == '1') ? 'checked' : '' ?>
                                       <?= $isView ? 'disabled' : '' ?>>
                                <span class="toggle-slider"></span>
                                <span class="toggle-label">Active Account</span>
                            </label>
                        </div>
                    </div>

                    <!-- Password -->
                    <?php if (!$isView): ?>
                        <div class="form-section">
                            <h3 class="form-section-title">
                                <?= $isEdit ? 'Change Password' : 'Password' ?>
                                <?php if ($isEdit): ?>
                                    <span style="font-weight:400;font-size:0.75rem;color:var(--text-muted)"> (leave blank to keep current)</span>
                                <?php endif; ?>
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="password">
                                        Password <?php if (!$isEdit): ?><span class="required">*</span><?php endif; ?>
                                    </label>
                                    <input type="password" id="password" name="password" 
                                           class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                           placeholder="Min 8 chars, mixed case + number"
                                           <?= $isEdit ? '' : 'required' ?>>
                                    <?php if (isset($errors['password'])): ?>
                                        <span class="field-error"><?= htmlspecialchars($errors['password']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="passwordConfirmation">
                                        Confirm Password <?php if (!$isEdit): ?><span class="required">*</span><?php endif; ?>
                                    </label>
                                    <input type="password" id="passwordConfirmation" name="password_confirmation" 
                                           class="form-control <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>" 
                                           placeholder="Re-enter password"
                                           <?= $isEdit ? '' : 'required' ?>>
                                    <?php if (isset($errors['password_confirmation'])): ?>
                                        <span class="field-error"><?= htmlspecialchars($errors['password_confirmation']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Form Actions -->
                    <?php if (!$isView): ?>
                        <div class="form-actions">
                            <a href="/manager/users" class="btn btn-outline">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="<?= $isEdit ? 'save' : 'plus' ?>" style="width:16px;height:16px"></i>
                                <?= $isEdit ? 'Update User' : 'Create User' ?>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="form-actions">
                            <a href="/manager/users" class="btn btn-outline">Back to List</a>
                            <a href="/manager/users/<?= $user->id ?>/edit" class="btn btn-primary">
                                <i data-lucide="pencil" style="width:16px;height:16px"></i>
                                Edit User
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
    lucide.createIcons();
</script>

</body>
</html>
