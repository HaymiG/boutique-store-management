<?php

/**
 * RBAC Helper Functions
 * Convenient helper functions for RBAC operations
 */

/**
 * Get current authenticated user
 * @return array|null
 */
function auth()
{
    return $_SESSION[AUTH_SESSION_NAME] ?? null;
}

/**
 * Get current user's role
 * @return \App\Models\Role|null
 */
function user_role()
{
    $user = auth();
    if (!$user || !isset($user['role_id'])) {
        return null;
    }

    $roleModel = new \App\Models\Role();
    return $roleModel->findById($user['role_id']);
}

/**
 * Check if current user is authenticated
 * @return bool
 */
function is_authenticated()
{
    return auth() !== null;
}

/**
 * Check if current user has role
 * @param string $roleName
 * @return bool
 */
function has_role($roleName)
{
    $role = user_role();
    return $role && $role->name === $roleName;
}

/**
 * Check if current user has any of the given roles
 * @param array|string $roles
 * @return bool
 */
function has_any_role($roles)
{
    if (!is_array($roles)) {
        $roles = [$roles];
    }

    foreach ($roles as $role) {
        if (has_role($role)) {
            return true;
        }
    }

    return false;
}

/**
 * Check if current user has permission
 * @param string $permission
 * @return bool
 */
function has_permission($permission)
{
    $role = user_role();
    return $role && $role->hasPermission($permission);
}

/**
 * Check if current user has any of the given permissions
 * @param array|string $permissions
 * @return bool
 */
function has_any_permission($permissions)
{
    if (!is_array($permissions)) {
        $permissions = [$permissions];
    }

    foreach ($permissions as $permission) {
        if (has_permission($permission)) {
            return true;
        }
    }

    return false;
}

/**
 * Check if current user has all given permissions
 * @param array|string $permissions
 * @return bool
 */
function has_all_permissions($permissions)
{
    if (!is_array($permissions)) {
        $permissions = [$permissions];
    }

    foreach ($permissions as $permission) {
        if (!has_permission($permission)) {
            return false;
        }
    }

    return true;
}

/**
 * Check if user can access resource with action
 * @param string $resource
 * @param string $action
 * @return bool
 */
function can_access($resource, $action)
{
    return has_permission("{$resource}.{$action}");
}

/**
 * Get all permissions for current user
 * @return array
 */
function get_permissions()
{
    $role = user_role();
    return $role ? $role->getPermissions() : [];
}

/**
 * Get role by name
 * @param string $name
 * @return \App\Models\Role|null
 */
function get_role($name)
{
    return \App\Models\Role::findByName($name);
}

/**
 * Get all roles
 * @return array
 */
function get_all_roles()
{
    return \App\Models\Role::all();
}

/**
 * Get all permissions
 * @return array
 */
function get_all_permissions()
{
    return \App\Models\Permission::all();
}

/**
 * Create role
 * @param array $data
 * @return \App\Models\Role
 */
function create_role($data)
{
    return \App\Models\Role::create($data);
}

/**
 * Create permission
 * @param array $data
 * @return \App\Models\Permission
 */
function create_permission($data)
{
    return \App\Models\Permission::create($data);
}

/**
 * Check if user is admin
 * @param int|null $userId
 * @return bool
 */
function is_admin($userId = null)
{
    if ($userId === null) {
        return has_role('admin');
    }

    $user = new \App\Models\User();
    $user = $user->findById($userId);

    if (!$user || !isset($user->role_id)) {
        return false;
    }

    $role = new \App\Models\Role();
    $role = $role->findById($user->role_id);

    return $role && $role->name === 'admin';
}

/**
 * Check if user is manager
 * @param int|null $userId
 * @return bool
 */
function is_manager($userId = null)
{
    if ($userId === null) {
        return has_role('manager');
    }

    $user = new \App\Models\User();
    $user = $user->findById($userId);

    if (!$user || !isset($user->role_id)) {
        return false;
    }

    $role = new \App\Models\Role();
    $role = $role->findById($user->role_id);

    return $role && $role->name === 'manager';
}

/**
 * Check if user is staff
 * @param int|null $userId
 * @return bool
 */
function is_staff($userId = null)
{
    if ($userId === null) {
        return has_role('staff');
    }

    $user = new \App\Models\User();
    $user = $user->findById($userId);

    if (!$user || !isset($user->role_id)) {
        return false;
    }

    $role = new \App\Models\Role();
    $role = $role->findById($user->role_id);

    return $role && $role->name === 'staff';
}

/**
 * Require authentication or redirect to login
 * @return void
 */
function require_auth()
{
    if (!is_authenticated()) {
        header('Location: /login');
        exit;
    }
}

/**
 * Require specific role or abort with 403
 * @param string $roleName
 * @return void
 */
function require_role($roleName)
{
    if (!has_role($roleName)) {
        http_response_code(403);
        die("Access denied: Role '{$roleName}' required");
    }
}

/**
 * Require any of given roles or abort with 403
 * @param array|string $roles
 * @return void
 */
function require_any_role($roles)
{
    if (!has_any_role($roles)) {
        http_response_code(403);
        die('Access denied: Insufficient role privileges');
    }
}

/**
 * Require specific permission or abort with 403
 * @param string $permission
 * @return void
 */
function require_permission($permission)
{
    if (!has_permission($permission)) {
        http_response_code(403);
        die("Access denied: Permission '{$permission}' required");
    }
}

/**
 * Require resource access or abort with 403
 * @param string $resource
 * @param string $action
 * @return void
 */
function require_access($resource, $action)
{
    if (!can_access($resource, $action)) {
        http_response_code(403);
        die("Access denied to {$resource}.{$action}");
    }
}

/**
 * Get config for RBAC
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function rbac_config($key = null, $default = null)
{
    $config = require CONFIG_PATH . '/rbac.php';

    if ($key === null) {
        return $config;
    }

    $keys = explode('.', $key);
    $value = $config;

    foreach ($keys as $k) {
        $value = $value[$k] ?? null;
        if ($value === null) {
            return $default;
        }
    }

    return $value;
}

/**
 * Log permission action (if audit logging enabled)
 * @param string $action
 * @param string $resource
 * @param int $userId
 * @param string $details
 * @return void
 */
function log_permission($action, $resource, $userId, $details = '')
{
    if (!rbac_config('audit_logging.enabled', false)) {
        return;
    }

    $logMessage = sprintf(
        "[RBAC] Action: %s, Resource: %s, User: %d, Details: %s",
        $action,
        $resource,
        $userId,
        $details
    );

    error_log($logMessage, 3, STORAGE_PATH . '/logs/rbac.log');
}
