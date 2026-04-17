<?php

/**
 * User Model
 * Handles user data operations, authentication, and account management
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    // ===================================
    // Finder Methods
    // ===================================

    /**
     * Find user by ID
     */
    public static function findById($id)
    {
        return static::find($id, true);
    }

    /**
     * Get user by username
     */
    public static function findByUsername($username)
    {
        return static::where('username', $username);
    }

    /**
     * Get user by email
     */
    public static function findByEmail($email)
    {
        return static::where('email', $email);
    }

    /**
     * Get all active users
     */
    public static function allActive()
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT u.*, r.name as role_name 
                FROM {$instance->table} u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.is_active = 1 
                ORDER BY u.created_at DESC"
        );

        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = new static($row);
        }
        return $records;
    }

    /**
     * Get all users with role info (including inactive)
     */
    public static function allWithRoles()
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT u.*, r.name as role_name 
                FROM {$instance->table} u 
                JOIN roles r ON u.role_id = r.id 
                ORDER BY u.created_at DESC"
        );

        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = new static($row);
        }
        return $records;
    }

    /**
     * Paginate users with role info and optional filters
     */
    public static function paginateWithFilters($page = 1, $perPage = PAGINATION_PER_PAGE, $filters = [])
    {
        $instance = new static();
        $offset = ($page - 1) * $perPage;
        $where = ['1=1'];
        $params = [];

        // Search by name or email
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        // Filter by role
        if (!empty($filters['role_id'])) {
            $where[] = "u.role_id = ?";
            $params[] = (int)$filters['role_id'];
        }

        // Filter by status
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $where[] = "u.is_active = ?";
            $params[] = (int)$filters['is_active'];
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $countResult = $instance->db->query(
            "SELECT COUNT(*) as count FROM {$instance->table} u WHERE {$whereClause}",
            $params
        );
        $countRow = $countResult->fetch_assoc();
        $total = $countRow['count'];

        // Fetch page
        $pageParams = array_merge($params, [$perPage, $offset]);
        $result = $instance->db->query(
            "SELECT u.*, r.name as role_name, b.name as branch_name
                FROM {$instance->table} u
                JOIN roles r ON u.role_id = r.id
                LEFT JOIN branches b ON u.branch_id = b.id
                WHERE {$whereClause}
                ORDER BY u.created_at DESC
                LIMIT ? OFFSET ?",
            $pageParams
        );

        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = new static($row);
        }

        return [
            'data' => $records,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page,
            'per_page' => $perPage,
        ];
    }

    // ===================================
    // CRUD Methods
    // ===================================

    /**
     * Create a new user with password hashing
     */
    public static function createUser($data)
    {
        $instance = new static();

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);

        $userData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'role_id' => (int)$data['role_id'],
            'branch_id' => !empty($data['branch_id']) ? (int)$data['branch_id'] : null,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
        ];

        $id = $instance->db->insert('users', $userData);
        return static::find($id);
    }

    /**
     * Update user data
     */
    public function updateUser($data)
    {
        $allowedFields = ['first_name', 'last_name', 'username', 'email', 'phone', 'role_id', 'branch_id', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $this->$field = $data[$field];
            }
        }

        // Handle password change if provided
        if (!empty($data['password'])) {
            $this->password = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
        }

        return $this->save();
    }

    /**
     * Soft delete the user (set is_active = 0)
     */
    public function softDelete()
    {
        $this->is_active = 0;
        return $this->save();
    }

    /**
     * Restore a soft-deleted user
     */
    public function restore()
    {
        $this->is_active = 1;
        return $this->save();
    }

    // ===================================
    // Authentication Methods
    // ===================================

    /**
     * Verify password
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Hash and set password
     */
    public function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
        return $this;
    }

    /**
     * Update login timestamp
     */
    public function updateLastLogin()
    {
        $this->last_login = date('Y-m-d H:i:s');
        $this->login_attempts = 0;
        $this->locked_until = null;
        return $this->save();
    }

    /**
     * Increment login attempts
     */
    public function incrementLoginAttempts()
    {
        $this->login_attempts++;

        if ($this->login_attempts >= AUTH_MAX_LOGIN_ATTEMPTS) {
            $this->locked_until = date('Y-m-d H:i:s', time() + AUTH_LOCKOUT_DURATION);
        }

        return $this->save();
    }

    /**
     * Check if account is locked
     */
    public function isLocked()
    {
        if (!$this->locked_until) {
            return false;
        }

        $lockedUntil = strtotime($this->locked_until);
        if (time() > $lockedUntil) {
            $this->locked_until = null;
            $this->login_attempts = 0;
            $this->save();
            return false;
        }

        return true;
    }

    /**
     * Get remaining lockout time in seconds
     */
    public function getLockoutRemaining()
    {
        if (!$this->locked_until) {
            return 0;
        }
        $remaining = strtotime($this->locked_until) - time();
        return max(0, $remaining);
    }

    /**
     * Unlock account
     */
    public function unlock()
    {
        $this->locked_until = null;
        $this->login_attempts = 0;
        return $this->save();
    }
}
