<?php

/**
 * Base Controller Class
 * All controllers extend from this base class
 */

namespace App\Core;

class Controller
{
    /**
     * Database instance
     */
    protected $db;

    /**
     * Current user data
     */
    protected $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->user = $this->getCurrentUser();
        $this->authorize();
    }

    /**
     * Get current authenticated user
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
    protected function isAuthenticated()
    {
        return $this->user !== null;
    }

    /**
     * Authorize user - override in child controllers
     */
    protected function authorize()
    {
        // Override in child classes to implement authorization
    }

    /**
     * ==========================================
     * ROLE & PERMISSION METHODS (RBAC)
     * ==========================================
     */

    /**
     * Get user's role
     */
    protected function getUserRole()
    {
        if (!$this->isAuthenticated() || !isset($this->user['role_id'])) {
            return null;
        }

        $roleModel = new \App\Models\Role();
        return $roleModel->findById($this->user['role_id']);
    }

    /**
     * Check if user has a specific role
     */
    protected function hasRole($roleName)
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $role = $this->getUserRole();
        return $role && $role->name === $roleName;
    }

    /**
     * Check if user has any of the given roles
     */
    protected function hasAnyRole($roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Require a specific role or abort
     */
    protected function requireRole($roleName)
    {
        if (!$this->hasRole($roleName)) {
            $this->abort(403, "Role '{$roleName}' required");
        }
    }

    /**
     * Require any of the given roles or abort
     */
    protected function requireAnyRole($roles)
    {
        if (!$this->hasAnyRole($roles)) {
            $this->abort(403, 'Insufficient role privileges');
        }
    }

    /**
     * Check if user has a specific permission
     */
    protected function hasPermission($permission)
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $role = $this->getUserRole();
        return $role && $role->hasPermission($permission);
    }

    /**
     * Check if user has any of the given permissions
     */
    protected function hasAnyPermission($permissions)
    {
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all given permissions
     */
    protected function hasAllPermissions($permissions)
    {
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Require a specific permission or abort
     */
    protected function requirePermission($permission)
    {
        if (!$this->hasPermission($permission)) {
            $this->abort(403, "Permission '{$permission}' required");
        }
    }

    /**
     * Require any of the given permissions or abort
     */
    protected function requireAnyPermission($permissions)
    {
        if (!$this->hasAnyPermission($permissions)) {
            $this->abort(403, 'Insufficient permissions');
        }
    }

    /**
     * Require all given permissions or abort
     */
    protected function requireAllPermissions($permissions)
    {
        if (!$this->hasAllPermissions($permissions)) {
            $this->abort(403, 'Insufficient permissions');
        }
    }

    /**
     * Check resource access (resource.action pattern)
     * Examples: 'items.create', 'users.edit', 'sales.delete'
     */
    protected function canAccess($resource, $action)
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $permission = "{$resource}.{$action}";
        return $this->hasPermission($permission);
    }

    /**
     * Require resource access or abort
     */
    protected function requireAccess($resource, $action)
    {
        if (!$this->canAccess($resource, $action)) {
            $this->abort(403, "Access denied to {$resource}.{$action}");
        }
    }

    /**
     * Render a view
     */
    protected function view($name, $data = [])
    {
        $viewPath = APP_PATH . "/views/{$name}.php";

        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: {$name}");
        }

        // Extract variables to view scope
        extract($data);

        // Start output buffering
        ob_start();

        include $viewPath;

        // Get output and return
        return ob_get_clean();
    }

    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        return json_encode([
            'success' => $statusCode >= 200 && $statusCode < 300,
            'data' => $data,
            'code' => $statusCode,
        ]);
    }

    /**
     * Redirect to URL
     */
    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Redirect to named route
     */
    protected function redirectRoute($name, $params = [])
    {
        $url = route($name, $params);
        $this->redirect($url);
    }

    /**
     * Abort with error
     */
    protected function abort($code, $message = '')
    {
        http_response_code($code);

        if (APP_DEBUG) {
            die("[{$code}] {$message}");
        } else {
            // Load error view
            $errorView = APP_PATH . "/views/errors/{$code}.php";
            if (file_exists($errorView)) {
                include $errorView;
            }
        }

        exit;
    }

    /**
     * Validate request data
     */
    protected function validate($data, $rules)
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            if (!isset($data[$field])) {
                $errors[$field] = "Required field: {$field}";
                continue;
            }

            $value = $data[$field];
            $ruleArray = explode('|', $rule);

            foreach ($ruleArray as $singleRule) {
                $validation = $this->validateField($value, $singleRule, $field);
                if ($validation !== true) {
                    $errors[$field] = $validation;
                    break;
                }
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Validate single field
     */
    private function validateField($value, $rule, $field)
    {
        $rule = trim($rule);

        if ($rule === 'required') {
            return !empty($value) ? true : "{$field} is required";
        }

        if (strpos($rule, 'min:') === 0) {
            $min = (int) substr($rule, 4);
            return strlen($value) >= $min ? true : "{$field} must be at least {$min} characters";
        }

        if (strpos($rule, 'max:') === 0) {
            $max = (int) substr($rule, 4);
            return strlen($value) <= $max ? true : "{$field} must not exceed {$max} characters";
        }

        if ($rule === 'email') {
            return filter_var($value, FILTER_VALIDATE_EMAIL) ? true : "{$field} must be valid email";
        }

        if ($rule === 'numeric') {
            return is_numeric($value) ? true : "{$field} must be numeric";
        }

        if (strpos($rule, 'unique:') === 0) {
            $tableName = substr($rule, 7);
            $result = $this->db->query("SELECT COUNT(*) as count FROM {$tableName} WHERE {$field} = ?", [$value]);
            $row = $result->fetch_assoc();
            return $row['count'] == 0 ? true : "{$field} must be unique";
        }

        return true;
    }

    /**
     * Get request data
     */
    protected function request($key = null, $default = null)
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $data = $_GET;
        } elseif ($method === 'POST') {
            $data = $_POST;
        } else {
            $data = $this->getPutData();
        }

        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? $default;
    }

    /**
     * Get PUT/PATCH request data
     */
    private function getPutData()
    {
        $input = file_get_contents('php://input');
        parse_str($input, $data);
        return $data;
    }

    /**
     * Hash password
     */
    protected function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
    }

    /**
     * Verify password
     */
    protected function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Escape SQL input
     */
    protected function escape($string)
    {
        return $this->db->connection->real_escape_string($string);
    }
}
