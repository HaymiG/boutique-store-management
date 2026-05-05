<?php

/**
 * Authentication Service
 * Centralized authentication logic and helpers
 */

namespace App\Core;

use App\Models\User;

class Auth
{
    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Session instance
     */
    private $session;

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->session = Session::getInstance();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * ==========================================
     * AUTHENTICATION METHODS
     * ==========================================
     */

    /**
     * Attempt to authenticate user
     */
    public function attempt($email, $password)
    {
        $user = User::findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid email or password',
                'code' => 401
            ];
        }

        if ($user->isLocked()) {
            return [
                'success' => false,
                'message' => 'Account is temporarily locked. Try again later.',
                'code' => 429
            ];
        }

        if (!$user->verifyPassword($password)) {
            $user->incrementLoginAttempts();
            return [
                'success' => false,
                'message' => 'Invalid email or password',
                'code' => 401
            ];
        }

        if (!$user->is_active) {
            return [
                'success' => false,
                'message' => 'Account is inactive',
                'code' => 403
            ];
        }

        // Success
        $user->updateLastLogin();
        $this->login($user);

        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => $user->toApiResponse()
        ];
    }

    /**
     * Login user (create session)
     */
    public function login(User $user)
    {
        $this->session->regenerate();
        $this->session->setUser($user->toApiResponse());
        $this->session->generateCsrfToken();
    }

    /**
     * Logout user (destroy session)
     */
    public function logout()
    {
        $this->session->clearUser();
        $this->session->destroy();
    }

    /**
     * ==========================================
     * AUTHORIZATION METHODS
     * ==========================================
     */

    /**
     * Check if user is authenticated
     */
    public function check()
    {
        return $this->session->isAuthenticated();
    }

    /**
     * Get authenticated user
     */
    public function user()
    {
        return $this->session->getUser();
    }

    /**
     * Get user ID
     */
    public function userId()
    {
        return $this->session->getUserId();
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($permission)
    {
        if (!$this->check()) {
            return false;
        }

        $user = $this->user();
        $roleId = $user['role_id'] ?? null;

        // Get role name from ID
        $roles = array_flip(ROLES);
        $roleName = $roles[$roleId] ?? null;

        if (!$roleName) {
            return false;
        }

        $permissions = ROLE_PERMISSIONS[$roleName] ?? [];
        return in_array($permission, $permissions, true);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions)
    {
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
    public function hasAllPermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * ==========================================
     * PASSWORD RESET
     * ==========================================
     */

    /**
     * Request password reset
     */
    public function requestPasswordReset($email)
    {
        $user = User::findByEmail($email);

        if (!$user) {
            return null;
        }

        return $user->generateResetToken();
    }

    /**
     * Reset password
     */
    public function resetPassword($email, $token, $password)
    {
        $user = User::findByEmail($email);

        if (!$user || !$user->verifyResetToken($token)) {
            return false;
        }

        $user->setPassword($password);
        $user->clearResetToken();

        return true;
    }

    /**
     * Verify reset token
     */
    public function verifyResetToken($email, $token)
    {
        $user = User::findByEmail($email);

        if (!$user) {
            return false;
        }

        return $user->verifyResetToken($token);
    }

    /**
     * ==========================================
     * CSRF TOKEN
     * ==========================================
     */

    /**
     * Get CSRF token
     */
    public function csrf()
    {
        return $this->session->getCsrfToken();
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrf($token)
    {
        return $this->session->verifyCsrfToken($token);
    }
}
