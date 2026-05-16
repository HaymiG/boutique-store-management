<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Role;
use App\Models\Permission;

class RoleManagementController extends Controller
{
    protected $role;
    protected $permission;

    public function __construct()
    {
        parent::__construct();
        $this->permission = new Permission();
    }

    /**
     * List all roles with permissions
     * GET /api/roles
     */
    public function listRoles()
    {
        if (!$this->isAuthenticated()) {
            $this->respondJsonError('Unauthorized', 401);
            return;
        }

        if (!$this->hasPermission('roles.read')) {
            $this->respondJsonError('Forbidden: Permission denied', 403);
            return;
        }

        try {
            $roles = $this->db->query("SELECT * FROM roles WHERE is_active = 1 ORDER BY name");
            $roles = $this->resultToArray($roles);

            // Attach permissions to each role
            foreach ($roles as &$role) {
                $permissions = $this->db->query(
                    "SELECT p.* FROM permissions p 
                     JOIN role_permissions rp ON p.id = rp.permission_id 
                     WHERE rp.role_id = ?",
                    [$role['id']]
                );
                $role['permissions'] = $this->resultToArray($permissions);
            }

            $this->respondJson([
                'success' => true,
                'roles' => $roles
            ]);
        } catch (\Exception $e) {
            $this->respondJsonError('Error fetching roles: ' . $e->getMessage(), 500);
        }
    }

    /**
     * List all available permissions
     * GET /api/permissions
     */
    public function listPermissions()
    {
        if (!$this->isAuthenticated()) {
            $this->respondJsonError('Unauthorized', 401);
            return;
        }

        if (!$this->hasPermission('roles.read')) {
            $this->respondJsonError('Forbidden: Permission denied', 403);
            return;
        }

        try {
            $permissions = $this->db->query("SELECT * FROM permissions WHERE is_active = 1 ORDER BY name");

            $this->respondJson([
                'success' => true,
                'permissions' => $this->resultToArray($permissions)
            ]);
        } catch (\Exception $e) {
            $this->respondJsonError('Error fetching permissions: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new role
     * POST /api/roles
     */
    public function createRole()
    {
        if (!$this->isAuthenticated()) {
            $this->respondJsonError('Unauthorized', 401);
            return;
        }

        if (!$this->hasPermission('roles.create')) {
            $this->respondJsonError('Forbidden: Permission denied', 403);
            return;
        }

        $data = $this->getJsonInput();

        // Validate input
        if (empty($data['name'])) {
            $this->respondJson([
                'success' => false,
                'message' => 'Role name is required',
                'errors' => ['name' => 'Name cannot be empty']
            ], 400);
            return;
        }

        try {
            // Check role name uniqueness
            $existing = $this->db->query("SELECT id FROM roles WHERE name = ?", [$data['name']]);
            if (!empty($existing)) {
                $this->respondJson([
                    'success' => false,
                    'message' => 'Role name already exists',
                    'errors' => ['name' => 'This role name is already in use']
                ], 400);
                return;
            }

            // Insert role
            $roleId = $this->db->insert('roles', [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if (!$roleId) {
                $this->respondJsonError('Failed to create role', 500);
                return;
            }

            // Attach permissions if provided
            if (!empty($data['permissions']) && is_array($data['permissions'])) {
                foreach ($data['permissions'] as $permissionId) {
                    $this->db->insert('role_permissions', [
                        'role_id' => $roleId,
                        'permission_id' => $permissionId
                    ]);
                }
            }

            // Fetch created role with permissions
            $roleResult = $this->db->query("SELECT * FROM roles WHERE id = ?", [$roleId]);
            $role = $this->resultToArray($roleResult);
            $permissions = $this->db->query(
                "SELECT p.* FROM permissions p 
                 JOIN role_permissions rp ON p.id = rp.permission_id 
                 WHERE rp.role_id = ?",
                [$roleId]
            );
            $permissionsArray = $this->resultToArray($permissions);

            $role[0]['permissions'] = $permissionsArray;

            $this->respondJson([
                'success' => true,
                'message' => 'Role created successfully',
                'role' => $role[0]
            ], 201);
        } catch (\Exception $e) {
            $this->respondJsonError('Error creating role: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update role
     * PUT /api/roles/{id}
     */
    public function updateRole($id)
    {
        if (!$this->isAuthenticated()) {
            $this->respondJsonError('Unauthorized', 401);
            return;
        }

        if (!$this->hasPermission('roles.update')) {
            $this->respondJsonError('Forbidden: Permission denied', 403);
            return;
        }

        $data = $this->getJsonInput();

        try {
            // Check role exists
            $role = $this->db->query("SELECT id FROM roles WHERE id = ?", [$id]);
            if (empty($role)) {
                $this->respondJsonError('Role not found', 404);
                return;
            }

            // Check name uniqueness
            if (isset($data['name'])) {
                $existing = $this->db->query(
                    "SELECT id FROM roles WHERE name = ? AND id != ?",
                    [$data['name'], $id]
                );
                if (!empty($existing)) {
                    $this->respondJson([
                        'success' => false,
                        'message' => 'Role name already exists',
                        'errors' => ['name' => 'This role name is already in use']
                    ], 400);
                    return;
                }
            }

            // Prepare update data
            $updateData = [];
            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }
            if (isset($data['description'])) {
                $updateData['description'] = $data['description'];
            }

            $updateData['updated_at'] = date('Y-m-d H:i:s');

            // Update role
            $updated = $this->db->update('roles', $updateData, 'id = ?', [$id]);

            if (!$updated) {
                $this->respondJsonError('Failed to update role', 500);
                return;
            }

            // Update permissions if provided
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                // Delete existing permissions
                $this->db->query("DELETE FROM role_permissions WHERE role_id = ?", [$id]);

                // Insert new permissions
                foreach ($data['permissions'] as $permissionId) {
                    $this->db->insert('role_permissions', [
                        'role_id' => $id,
                        'permission_id' => $permissionId
                    ]);
                }
            }

            // Fetch updated role
            $updatedRoleResult = $this->db->query("SELECT * FROM roles WHERE id = ?", [$id]);
            $updatedRole = $this->resultToArray($updatedRoleResult);
            $permissionsResult = $this->db->query(
                "SELECT p.* FROM permissions p 
                 JOIN role_permissions rp ON p.id = rp.permission_id 
                 WHERE rp.role_id = ?",
                [$id]
            );
            $permissions = $this->resultToArray($permissionsResult);

            $updatedRole[0]['permissions'] = $permissions;

            $this->respondJson([
                'success' => true,
                'message' => 'Role updated successfully',
                'role' => $updatedRole[0]
            ]);
        } catch (\Exception $e) {
            $this->respondJsonError('Error updating role: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete role
     * DELETE /api/roles/{id}
     */
    public function deleteRole($id)
    {
        if (!$this->isAuthenticated()) {
            $this->respondJsonError('Unauthorized', 401);
            return;
        }

        if (!$this->hasPermission('roles.delete')) {
            $this->respondJsonError('Forbidden: Permission denied', 403);
            return;
        }

        try {
            $role = $this->db->query("SELECT id FROM roles WHERE id = ?", [$id]);
            if (empty($role)) {
                $this->respondJsonError('Role not found', 404);
                return;
            }

            // Soft delete
            $deleted = $this->db->update('roles', ['is_active' => 0], 'id = ?', [$id]);

            if (!$deleted) {
                $this->respondJsonError('Failed to delete role', 500);
                return;
            }

            $this->respondJson([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]);
        } catch (\Exception $e) {
            $this->respondJsonError('Error deleting role: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Override hasPermission to handle API context with error handling
     */
    protected function hasPermission($permission)
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        try {
            $role = $this->getUserRole();
            if (!$role) {
                return false;
            }
            return method_exists($role, 'hasPermission') ? $role->hasPermission($permission) : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Helper: Get JSON input
     */
    protected function getJsonInput()
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    /**
     * Helper: Respond with JSON
     */
    protected function respondJson($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Helper: Respond with JSON error
     */
    protected function respondJsonError($message, $statusCode = 400)
    {
        $this->respondJson([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }
}
