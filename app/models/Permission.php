<?php
/**
 * Permission Model
 * Handles permission data operations for RBAC
 */

namespace App\Models;

use App\Core\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';

    /**
     * Get all permissions
     */
    public static function all()
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT p.*, r.name as role_name 
             FROM {$instance->table} p 
             JOIN roles r ON p.role_id = r.id 
             ORDER BY r.name ASC, p.permission ASC"
        );

        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = new static($row);
        }
        return $records;
    }

    /**
     * Find permission by ID
     */
    public static function findById($id)
    {
        return static::find($id);
    }

    /**
     * Get all permissions for a role
     */
    public static function getForRole($roleId)
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT * FROM {$instance->table} WHERE role_id = ? ORDER BY permission ASC",
            [$roleId]
        );

        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = new static($row);
        }
        return $records;
    }

    /**
     * Assign a permission to a role
     */
    public static function assignToRole($roleId, $permission)
    {
        $instance = new static();
        
        // Check if already assigned
        $result = $instance->db->query(
            "SELECT COUNT(*) as count FROM permissions WHERE role_id = ? AND permission = ?",
            [$roleId, $permission]
        );
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            return true; // Already assigned
        }

        return $instance->db->insert('permissions', [
            'role_id' => (int)$roleId,
            'permission' => $permission,
        ]);
    }

    /**
     * Remove a permission from a role
     */
    public static function removeFromRole($roleId, $permission)
    {
        $instance = new static();
        return $instance->db->delete(
            'permissions',
            'role_id = ? AND permission = ?',
            [$roleId, $permission]
        );
    }

    /**
     * Sync permissions for a role (replace all with new set)
     */
    public static function syncForRole($roleId, $permissions)
    {
        $instance = new static();

        // Delete all existing permissions for this role
        $instance->db->query(
            "DELETE FROM permissions WHERE role_id = ?",
            [$roleId]
        );

        // Insert new permissions
        foreach ($permissions as $permission) {
            $instance->db->insert('permissions', [
                'role_id' => (int)$roleId,
                'permission' => $permission,
            ]);
        }

        return true;
    }

    /**
     * Get all unique permission strings in the system
     */
    public static function getAllUniquePermissions()
    {
        // Combine database permissions with config-defined permissions
        $allPermissions = [];

        foreach (ROLE_PERMISSIONS as $role => $perms) {
            foreach ($perms as $perm) {
                if (!in_array($perm, $allPermissions)) {
                    $allPermissions[] = $perm;
                }
            }
        }

        sort($allPermissions);
        return $allPermissions;
    }

    /**
     * Check if a role has a specific permission
     */
    public static function roleHasPermission($roleId, $permission)
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT COUNT(*) as count FROM permissions WHERE role_id = ? AND permission = ?",
            [$roleId, $permission]
        );
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
}
?>
