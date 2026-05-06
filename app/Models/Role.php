<?php

/**
 * Role Model - Handles role management and role queries
 */

namespace App\Models;

use App\Core\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';

    /**
     * ==========================================
     * QUERY METHODS
     * ==========================================
     */

    /**
     * Get all roles
     */
    public static function all()
    {
        $instance = new static();
        $result = $instance->db->query("SELECT * FROM {$instance->table} ORDER BY name ASC");

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
     * Check if role exists
     */
    public static function exists($name)
    {
        $role = static::where('name', $name);
        return $role !== null;
    }

    /**
     * ==========================================
     * CREATE/UPDATE METHODS
     * ==========================================
     */

    /**
     * Create new role
     */
    public static function create($data)
    {
        if (empty($data['name'])) {
            throw new \Exception('Role name is required');
        }

        if (static::exists($data['name'])) {
            throw new \Exception('Role already exists');
        }

        $role = new static([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $role->save();
        return $role;
    }

    /**
     * Update role
     */
    public function update($data = [])
    {
        if (isset($data['name']) && $data['name'] !== $this->name) {
            if (static::where('name', $data['name'])) {
                throw new \Exception('Role name already exists');
            }
            $this->name = $data['name'];
        }

        if (isset($data['description'])) {
            $this->description = $data['description'];
        }

        $this->save();
        return $this;
    }

    /**
     * Delete role
     */
    public function delete()
    {
        // Check if role is assigned to users
        $userCount = $this->db->query(
            "SELECT COUNT(*) as count FROM users WHERE role_id = ?",
            [$this->id]
        )->fetch_assoc();

        if ($userCount['count'] > 0) {
            throw new \Exception('Cannot delete role that is assigned to users');
        }

        return parent::delete();
    }

    /**
     * ==========================================
     * PERMISSION METHODS
     * ==========================================
     */

    /**
     * Get all permissions for this role
     */
    public function getPermissions()
    {
        if (!isset($this->id)) {
            return [];
        }

        $result = $this->db->query(
            "SELECT * FROM permissions WHERE role_id = ? ORDER BY permission ASC",
            [$this->id]
        );

        $permissions = [];
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['permission'];
        }
        return $permissions;
    }

    /**
     * Check if role has permission
     */
    public function hasPermission($permission)
    {
        if (!isset($this->id)) {
            return false;
        }

        $result = $this->db->query(
            "SELECT id FROM permissions WHERE role_id = ? AND permission = ? LIMIT 1",
            [$this->id, $permission]
        );

        return $result->num_rows > 0;
    }

    /**
     * Assign permission to role
     */
    public function assignPermission($permission)
    {
        if (!isset($this->id)) {
            throw new \Exception('Role must be saved before assigning permissions');
        }

        if (empty($permission)) {
            throw new \Exception('Permission name is required');
        }

        // Check if permission already exists
        if ($this->hasPermission($permission)) {
            return true; // Already assigned
        }

        $result = $this->db->query(
            "INSERT INTO permissions (role_id, permission) VALUES (?, ?)",
            [$this->id, $permission]
        );
        return $result !== null;
    }

    /**
     * Assign multiple permissions
     */
    public function assignPermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            $this->assignPermission($permission);
        }
        return $this;
    }

    /**
     * Remove permission from role
     */
    public function removePermission($permission)
    {
        if (!isset($this->id)) {
            return false;
        }

        $result = $this->db->query(
            "DELETE FROM permissions WHERE role_id = ? AND permission = ?",
            [$this->id, $permission]
        );
        return $result !== null;
    }

    /**
     * Sync permissions (replace all with given list)
     */
    public function syncPermissions(array $permissions)
    {
        if (!isset($this->id)) {
            throw new \Exception('Role must be saved before syncing permissions');
        }

        // Delete all existing permissions
        $this->db->query("DELETE FROM permissions WHERE role_id = ?", [$this->id]);

        // Assign new permissions
        foreach ($permissions as $permission) {
            $this->assignPermission($permission);
        }

        return $this;
    }
}
