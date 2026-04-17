<?php
/**
 * Role Model
 * Handles role data operations for RBAC
 */

namespace App\Models;

use App\Core\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';

    /**
     * Get all roles
     */
    public static function all()
    {
        $instance = new static();
        $result = $instance->db->query("SELECT * FROM {$instance->table} ORDER BY id ASC");

        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = new static($row);
        }
        return $records;
    }

    /**
     * Find role by ID
     */
    public static function findById($id)
    {
        return static::find($id);
    }

    /**
     * Find role by name
     */
    public static function findByName($name)
    {
        return static::where('name', $name);
    }

    /**
     * Create a new role
     */
    public static function createRole($data)
    {
        $instance = new static();
        $id = $instance->db->insert('roles', [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        return static::find($id);
    }

    /**
     * Update role
     */
    public function updateRole($data)
    {
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['description'])) {
            $this->description = $data['description'];
        }
        return $this->save();
    }

    /**
     * Get all permissions for this role
     */
    public function getPermissions()
    {
        $result = $this->db->query(
            "SELECT * FROM permissions WHERE role_id = ? ORDER BY permission ASC",
            [$this->id]
        );

        $permissions = [];
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row;
        }
        return $permissions;
    }

    /**
     * Get permission strings array
     */
    public function getPermissionNames()
    {
        $result = $this->db->query(
            "SELECT permission FROM permissions WHERE role_id = ?",
            [$this->id]
        );

        $names = [];
        while ($row = $result->fetch_assoc()) {
            $names[] = $row['permission'];
        }
        return $names;
    }

    /**
     * Count users with this role
     */
    public function getUserCount()
    {
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM users WHERE role_id = ?",
            [$this->id]
        );
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }

    /**
     * Check if role can be safely deleted
     */
    public function canDelete()
    {
        return $this->getUserCount() === 0;
    }

    /**
     * Delete role and associated permissions
     */
    public function deleteRole()
    {
        if (!$this->canDelete()) {
            return false;
        }

        // Permissions will be cascade deleted due to FK constraint
        return $this->delete();
    }
}
?>
