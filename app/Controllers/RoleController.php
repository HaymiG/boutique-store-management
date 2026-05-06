<?php

/**
 * Example: Role Management Controller
 * Demonstrates RBAC implementation
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Core\Middleware\AuthorizationMiddleware;

class RoleController extends Controller
{
    protected $auth;

    public function __construct()
    {
        parent::__construct();
        $this->auth = new AuthorizationMiddleware();
    }

    /**
     * ==========================================
     * ROLE MANAGEMENT
     * ==========================================
     */

    /**
     * List all roles
     * Requires: admin role
     */
    public function index()
    {
        $this->requireRole('admin');

        $roles = Role::all();

        return $this->json([
            'roles' => array_map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $role->description,
                    'permissions_count' => count($role->getPermissions()),
                    'created_at' => $role->created_at,
                ];
            }, $roles),
        ]);
    }

    /**
     * Get single role with permissions
     * Requires: admin role
     */
    public function show($id)
    {
        $this->requireRole('admin');

        $role = Role::findById($id);

        if (!$role) {
            return $this->json(['error' => 'Role not found'], 404);
        }

        return $this->json([
            'id' => $role->id,
            'name' => $role->name,
            'description' => $role->description,
            'permissions' => $role->getPermissions(),
        ]);
    }

    /**
     * Create new role
     * Requires: admin role or roles.create permission
     */
    public function create()
    {
        // Both role-based and permission-based checks work
        $this->requireAccess('roles', 'create');

        $data = $this->request();

        try {
            $role = Role::create([
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            // Assign permissions if provided
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $role->assignPermissions($data['permissions']);
            }

            return $this->json([
                'message' => 'Role created successfully',
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                ],
            ], 201);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Update role
     * Requires: roles.update permission
     */
    public function update($id)
    {
        $this->requirePermission('roles.update');

        $role = Role::findById($id);

        if (!$role) {
            return $this->json(['error' => 'Role not found'], 404);
        }

        $data = $this->request();

        try {
            $role->update($data);

            return $this->json([
                'message' => 'Role updated successfully',
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete role
     * Requires: admin role
     */
    public function delete($id)
    {
        $this->requireRole('admin');

        $role = Role::findById($id);

        if (!$role) {
            return $this->json(['error' => 'Role not found'], 404);
        }

        try {
            $role->delete();

            return $this->json(['message' => 'Role deleted successfully']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * ==========================================
     * PERMISSION MANAGEMENT
     * ==========================================
     */

    /**
     * Get all permissions for a role
     * Requires: admin role or roles.read permission
     */
    public function getPermissions($roleId)
    {
        if (!$this->hasAnyPermission(['roles.read', 'roles.update'])) {
            $this->abort(403, 'Insufficient permissions');
        }

        $role = Role::findById($roleId);

        if (!$role) {
            return $this->json(['error' => 'Role not found'], 404);
        }

        return $this->json([
            'role' => $role->name,
            'permissions' => $role->getPermissions(),
        ]);
    }

    /**
     * Assign permission to role
     * Requires: roles.update permission
     */
    public function assignPermission($roleId)
    {
        $this->requireAccess('roles', 'update');

        $role = Role::findById($roleId);

        if (!$role) {
            return $this->json(['error' => 'Role not found'], 404);
        }

        $data = $this->request();

        if (empty($data['permission'])) {
            return $this->json(['error' => 'Permission name required'], 400);
        }

        try {
            $role->assignPermission($data['permission']);

            return $this->json([
                'message' => 'Permission assigned successfully',
                'permission' => $data['permission'],
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove permission from role
     * Requires: roles.update permission
     */
    public function removePermission($roleId)
    {
        $this->requirePermission('roles.update');

        $role = Role::findById($roleId);

        if (!$role) {
            return $this->json(['error' => 'Role not found'], 404);
        }

        $data = $this->request();

        if (empty($data['permission'])) {
            return $this->json(['error' => 'Permission name required'], 400);
        }

        try {
            $role->removePermission($data['permission']);

            return $this->json([
                'message' => 'Permission removed successfully',
                'permission' => $data['permission'],
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Sync (replace) all permissions for a role
     * Requires: admin role
     */
    public function syncPermissions($roleId)
    {
        $this->requireRole('admin');

        $role = Role::findById($roleId);

        if (!$role) {
            return $this->json(['error' => 'Role not found'], 404);
        }

        $data = $this->request();

        if (!isset($data['permissions']) || !is_array($data['permissions'])) {
            return $this->json(['error' => 'Permissions array required'], 400);
        }

        try {
            $role->syncPermissions($data['permissions']);

            return $this->json([
                'message' => 'Permissions synced successfully',
                'permissions' => $role->getPermissions(),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * ==========================================
     * AUTHORIZATION EXAMPLES
     * ==========================================
     */

    /**
     * Example: Check current user permissions
     */
    public function myPermissions()
    {
        $this->requireAuthentication();

        $role = $this->getUserRole();

        if (!$role) {
            return $this->json(['error' => 'User role not found'], 404);
        }

        return $this->json([
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
            ],
            'permissions' => $role->getPermissions(),
        ]);
    }

    /**
     * Example: Check if current user can perform action
     */
    public function checkAccess()
    {
        $this->requireAuthentication();

        $resource = $this->request('resource');
        $action = $this->request('action');

        if (!$resource || !$action) {
            return $this->json(['error' => 'Resource and action required'], 400);
        }

        $hasAccess = $this->canAccess($resource, $action);

        return $this->json([
            'resource' => $resource,
            'action' => $action,
            'can_access' => $hasAccess,
        ]);
    }

    /**
     * Example: Multiple authorization checks
     */
    public function demonstrateAuthChecks()
    {
        return $this->json([
            'is_authenticated' => $this->isAuthenticated(),
            'has_admin_role' => $this->hasRole('admin'),
            'has_manager_or_admin' => $this->hasAnyRole(['manager', 'admin']),
            'can_create_items' => $this->canAccess('items', 'create'),
            'can_view_reports' => $this->hasPermission('reports.view'),
            'current_role' => $this->getUserRole()?->name,
        ]);
    }

    /**
     * Helper: Require authentication
     */
    protected function requireAuthentication()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401, 'Authentication required');
        }
    }
}
