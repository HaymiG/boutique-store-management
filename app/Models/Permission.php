<?php

/**
 * Permission Model - Handles permission management
 */

namespace App\Models;

use App\Core\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';

    /**
     * ==========================================
     * QUERY METHODS
     * ==========================================
     */

    /**
     * Get all permissions
     */
    public static function all()
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT DISTINCT permission FROM {$instance->table} ORDER BY permission ASC"
        );

        $permissions = [];
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['permission'];
        }
        return $permissions;
    }

    /**
     * Get all permissions for a role
     */
    public static function getByRoleId($roleId)
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT * FROM {$instance->table} WHERE role_id = ? ORDER BY permission ASC",
            [$roleId]
        );

        $permissions = [];
        while ($row = $result->fetch_assoc()) {
            $permissions[] = new static($row);
        }
        return $permissions;
    }

    /**
     * Find permission by ID
     */
    public static function findById($id)
    {
        return static::find($id);
    }

    /**
     * Find permission for a role
     */
    public static function findByRoleAndName($roleId, $permission)
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT * FROM {$instance->table} WHERE role_id = ? AND permission = ? LIMIT 1",
            [$roleId, $permission]
        );

        if ($result->num_rows === 0) {
            return null;
        }

        $data = $result->fetch_assoc();
        return new static($data);
    }

    /**
     * ==========================================
     * CREATE/DELETE METHODS
     * ==========================================
     */

    /**
     * Create new permission
     */
    public static function create($data)
    {
        if (empty($data['role_id']) || empty($data['permission'])) {
            throw new \Exception('role_id and permission are required');
        }

        // Check if permission already exists for this role
        $existing = static::findByRoleAndName($data['role_id'], $data['permission']);
        if ($existing) {
            throw new \Exception('Permission already exists for this role');
        }

        $permission = new static([
            'role_id' => $data['role_id'],
            'permission' => $data['permission'],
        ]);

        $permission->save();
        return $permission;
    }

    /**
     * Delete permission
     */
    public function delete()
    {
        return parent::delete();
    }

    /**
     * Delete all permissions for a role
     */
    public static function deleteByRoleId($roleId)
    {
        $instance = new static();
        return $instance->db->execute(
            "DELETE FROM permissions WHERE role_id = ?",
            [$roleId]
        );
    }

    /**
     * ==========================================
     * UTILITY METHODS
     * ==========================================
     */

    /**
     * Get permission name
     */
    public function getName()
    {
        return $this->permission;
    }

    /**
     * Get role ID
     */
    public function getRoleId()
    {
        return $this->role_id;
    }
}
