<?php

/**
 * Authorization Middleware - Handles role and permission checks
 */

namespace App\Core\Middleware;

use App\Models\Role;
use App\Models\User;

class AuthorizationMiddleware
{
    /**
     * User session data
     */
    protected $user;

    /**
     * Current user's role
     */
    protected $role;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user = $this->getCurrentUser();
        if ($this->user) {
            $this->loadUserRole();
        }
    }

    /**
     * ==========================================
     * AUTHENTICATION CHECKS
     * ==========================================
     */

    /**
     * Get current authenticated user from session
     */
    protected function getCurrentUser()
    {
        if (!isset($_SESSION[AUTH_SESSION_NAME])) {
            return null;
        }

        return $_SESSION[AUTH_SESSION_NAME];
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated()
    {
        return $this->user !== null;
    }

    /**
     * Get authenticated user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Require authentication - redirect to login if not authenticated
     */
    public function requireAuthentication()
    {
        if (!$this->isAuthenticated()) {
            $this->redirectToLogin();
        }
    }

    /**
     * ==========================================
     * AUTHORIZATION CHECKS
     * ==========================================
     */

    /**
     * Load user's role from database
     */
    protected function loadUserRole()
    {
        if (!$this->user || !isset($this->user['id'])) {
            return;
        }

        // For efficiency, cache role in session if available
        if (isset($_SESSION[AUTH_SESSION_NAME]['role_data'])) {
            $this->role = $_SESSION[AUTH_SESSION_NAME]['role_data'];
        } else {
            // Load from database
            $userModel = new User($this->user);
            if ($userModel && isset($userModel->role_id)) {
                $this->role = Role::findById($userModel->role_id);
            }
        }
    }

    /**
     * Get user's role
     */
    public function getUserRole()
    {
        return $this->role;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($roleName)
    {
        $this->requireAuthentication();

        if (!$this->role) {
            return false;
        }

        return $this->role->name === $roleName;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole($roles)
    {
        $this->requireAuthentication();

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (!$this->role) {
            return false;
        }

        return in_array($this->role->name, $roles, true);
    }

    /**
     * Require specific role - abort with 403 if not authorized
     */
    public function requireRole($roleName)
    {
        if (!$this->hasRole($roleName)) {
            $this->abort(403, 'Insufficient privileges');
        }
    }

    /**
     * Require any of the given roles
     */
    public function requireAnyRole($roles)
    {
        if (!$this->hasAnyRole($roles)) {
            $this->abort(403, 'Insufficient privileges');
        }
    }

    /**
     * ==========================================
     * PERMISSION CHECKS
     * ==========================================
     */

    /**
     * Check if user has a specific permission
     */
    public function hasPermission($permission)
    {
        $this->requireAuthentication();

        if (!$this->role) {
            return false;
        }

        return $this->role->hasPermission($permission);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission($permissions)
    {
        $this->requireAuthentication();

        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        if (!$this->role) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($this->role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all given permissions
     */
    public function hasAllPermissions($permissions)
    {
        $this->requireAuthentication();

        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        if (!$this->role) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!$this->role->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Require specific permission - abort with 403 if not authorized
     */
    public function requirePermission($permission)
    {
        if (!$this->hasPermission($permission)) {
            $this->abort(403, 'Insufficient permissions');
        }
    }

    /**
     * Require any of the given permissions
     */
    public function requireAnyPermission($permissions)
    {
        if (!$this->hasAnyPermission($permissions)) {
            $this->abort(403, 'Insufficient permissions');
        }
    }

    /**
     * Require all given permissions
     */
    public function requireAllPermissions($permissions)
    {
        if (!$this->hasAllPermissions($permissions)) {
            $this->abort(403, 'Insufficient permissions');
        }
    }

    /**
     * ==========================================
     * RESOURCE-BASED ACCESS CONTROL
     * ==========================================
     */

    /**
     * Check if user can access a resource with a specific action
     * @param string $resource The resource name (e.g., 'items', 'users')
     * @param string $action The action (e.g., 'create', 'read', 'update', 'delete')
     */
    public function canAccess($resource, $action)
    {
        $this->requireAuthentication();

        if (!$this->role) {
            return false;
        }

        // Build permission string: resource.action (e.g., 'items.create')
        $permission = "{$resource}.{$action}";

        return $this->role->hasPermission($permission);
    }

    /**
     * Require access to a resource
     */
    public function requireAccess($resource, $action)
    {
        if (!$this->canAccess($resource, $action)) {
            $this->abort(403, "Access denied to {$resource}.{$action}");
        }
    }

    /**
     * ==========================================
     * ERROR HANDLING
     * ==========================================
     */

    /**
     * Redirect to login page
     */
    protected function redirectToLogin()
    {
        header('Location: /login');
        exit;
    }

    /**
     * Abort with HTTP status code
     */
    protected function abort($code, $message = '')
    {
        http_response_code($code);

        if ($code === 403) {
            header('HTTP/1.1 403 Forbidden');
            throw new \App\Exceptions\HttpException($message, $code);
        }

        throw new \Exception($message, $code);
    }
}
