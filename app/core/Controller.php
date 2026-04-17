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
     * Session instance
     */
    protected $session;

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
        $this->session = Session::getInstance();
        $this->user = $this->getCurrentUser();
        $this->authorize();
    }

    /**
     * Get current authenticated user
     */
    protected function getCurrentUser()
    {
        return $this->session->getUser();
    }

    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated()
    {
        return $this->session->isAuthenticated();
    }

    /**
     * Authorize user - override in child controllers
     */
    protected function authorize()
    {
        // Override in child classes to implement authorization
    }

    /**
     * Check if user has permission
     */
    protected function hasPermission($permission)
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $role = $this->user['role'] ?? null;
        $permissions = ROLE_PERMISSIONS[$role] ?? [];

        return in_array($permission, $permissions, true);
    }

    /**
     * Require permission or abort
     */
    protected function requirePermission($permission)
    {
        if (!$this->hasPermission($permission)) {
            $this->abort(403, 'Unauthorized');
        }
    }

    /**
     * Require that the user has a specific role
     */
    protected function requireRole($roleName)
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        $userRole = $this->user['role'] ?? null;
        if ($userRole !== $roleName) {
            $this->abort(403, 'Insufficient role privileges');
        }
    }

    /**
     * Check if user has any of the given roles
     */
    protected function hasAnyRole($roles)
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $userRole = $this->user['role'] ?? null;
        return in_array($userRole, (array)$roles);
    }

    /**
     * Require any of the given roles
     */
    protected function requireAnyRole($roles)
    {
        if (!$this->hasAnyRole($roles)) {
            $this->abort(403, 'Insufficient role privileges');
        }
    }

    /**
     * Check if user can access a resource action
     */
    protected function canAccess($resource, $action)
    {
        $permission = $resource . '.' . $action;
        return $this->hasPermission($permission);
    }

    /**
     * Require authentication or redirect to login
     */
    protected function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            $this->session->setFlash('error', 'Please log in to continue.');
            $this->redirect('/login');
        }

        // Refresh session timeout on each authenticated request
        $this->session->refreshTimeout();
    }

    /**
     * Validate CSRF token from request
     */
    protected function validateCsrf()
    {
        $token = $_POST['_csrf_token'] ?? '';
        if (!$this->session->validateCsrfToken($token)) {
            $this->abort(403, 'Invalid CSRF token');
        }
    }

    /**
     * Set a flash message
     */
    protected function setFlash($key, $message)
    {
        $this->session->setFlash($key, $message);
    }

    /**
     * Get a flash message
     */
    protected function getFlash($key, $default = null)
    {
        return $this->session->getFlash($key, $default);
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

        // Add common data available to all views
        $data['session'] = $this->session;
        $data['currentUser'] = $this->user;
        $data['csrfField'] = $this->session->csrfField();
        $data['csrfToken'] = $this->session->getCsrfToken();

        // Add flash messages
        $data['flashSuccess'] = $this->session->getFlash('success');
        $data['flashError'] = $this->session->getFlash('error');
        $data['flashWarning'] = $this->session->getFlash('warning');
        $data['flashInfo'] = $this->session->getFlash('info');

        // Extract variables to view scope
        extract($data);

        // Start output buffering
        ob_start();

        include $viewPath;

        // Get output and echo
        echo ob_get_clean();
    }

    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        echo json_encode([
            'success' => $statusCode >= 200 && $statusCode < 300,
            'data' => $data,
            'code' => $statusCode,
        ]);
        exit;
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
     * Redirect back to previous page
     */
    protected function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/dashboard';
        $this->redirect($referer);
    }

    /**
     * Abort with error
     */
    protected function abort($code, $message = '')
    {
        http_response_code($code);

        // Try to load the error view
        $errorView = APP_PATH . "/views/errors/{$code}.php";
        if (file_exists($errorView)) {
            $data = [
                'code' => $code,
                'message' => $message,
                'session' => $this->session ?? null,
                'currentUser' => $this->user ?? null,
            ];
            extract($data);
            include $errorView;
        } elseif (APP_DEBUG) {
            die("[{$code}] {$message}");
        } else {
            die("Error {$code}");
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
            $value = $data[$field] ?? null;
            $ruleArray = explode('|', $rule);

            foreach ($ruleArray as $singleRule) {
                // Skip non-required empty fields
                if ($singleRule !== 'required' && ($value === null || $value === '')) {
                    continue;
                }

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
            return ($value !== null && $value !== '') ? true : "{$field} is required";
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
            return filter_var($value, FILTER_VALIDATE_EMAIL) ? true : "{$field} must be a valid email";
        }

        if ($rule === 'numeric') {
            return is_numeric($value) ? true : "{$field} must be numeric";
        }

        if (strpos($rule, 'unique:') === 0) {
            $tableName = substr($rule, 7);
            $this->db->query("SELECT COUNT(*) as count FROM {$tableName} WHERE {$field} = ?", [$value]);
            $row = $this->db->fetch();
            return $row['count'] == 0 ? true : "{$field} already exists";
        }

        if (strpos($rule, 'confirmed:') === 0) {
            $confirmField = substr($rule, 10);
            $confirmValue = $_POST[$confirmField] ?? '';
            return $value === $confirmValue ? true : "{$field} confirmation does not match";
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
     * Sanitize input string
     */
    protected function sanitize($string)
    {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }
}
