<?php

/**
 * Session Management Class
 * Handles user sessions, CSRF tokens, and session security
 */

namespace App\Core;

class Session
{
    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Session name
     */
    private $sessionName = 'BOUTIQUE_SESSION';

    /**
     * Auth session key
     */
    private $authKey = 'authenticated_user';

    /**
     * CSRF token key
     */
    private $csrfKey = 'csrf_token';

    /**
     * Constructor - Initialize session
     */
    private function __construct()
    {
        $this->start();
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
     * SESSION LIFECYCLE
     * ==========================================
     */

    /**
     * Start session
     */
    public function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name($this->sessionName);
            session_start();
        }
    }

    /**
     * Destroy session
     */
    public function destroy()
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies') && !headers_sent()) {
            setcookie(session_name(), '', time() - 42000, '/');
        }

        session_destroy();
    }

    /**
     * Regenerate session ID (for login security)
     */
    public function regenerate()
    {
        // Preserve auth data
        $authData = $_SESSION[$this->authKey] ?? null;

        session_regenerate_id(true);

        // Restore auth data if it existed
        if ($authData) {
            $_SESSION[$this->authKey] = $authData;
        }
    }

    /**
     * ==========================================
     * AUTHENTICATED USER MANAGEMENT
     * ==========================================
     */

    /**
     * Set authenticated user
     */
    public function setUser($userData)
    {
        $_SESSION[$this->authKey] = $userData;
        return $this;
    }

    /**
     * Get authenticated user
     */
    public function getUser()
    {
        return $_SESSION[$this->authKey] ?? null;
    }

    /**
     * Get user ID
     */
    public function getUserId()
    {
        $user = $this->getUser();
        return $user['id'] ?? null;
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated()
    {
        return isset($_SESSION[$this->authKey]) && !empty($_SESSION[$this->authKey]);
    }

    /**
     * Clear authenticated user
     */
    public function clearUser()
    {
        unset($_SESSION[$this->authKey]);
        return $this;
    }

    /**
     * ==========================================
     * CSRF TOKEN MANAGEMENT
     * ==========================================
     */

    /**
     * Generate CSRF token
     */
    public function generateCsrfToken()
    {
        if (!isset($_SESSION[$this->csrfKey])) {
            $_SESSION[$this->csrfKey] = bin2hex(random_bytes(32));
        }
        return $_SESSION[$this->csrfKey];
    }

    /**
     * Get CSRF token
     */
    public function getCsrfToken()
    {
        return $_SESSION[$this->csrfKey] ?? null;
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken($token)
    {
        $sessionToken = $this->getCsrfToken();

        if (!$sessionToken || !$token) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Refresh CSRF token
     */
    public function refreshCsrfToken()
    {
        $_SESSION[$this->csrfKey] = bin2hex(random_bytes(32));
        return $_SESSION[$this->csrfKey];
    }

    /**
     * ==========================================
     * GENERIC SESSION METHODS
     * ==========================================
     */

    /**
     * Set session value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
        return $this;
    }

    /**
     * Get session value
     */
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session value
     */
    public function remove($key)
    {
        unset($_SESSION[$key]);
        return $this;
    }

    /**
     * ==========================================
     * FLASH MESSAGES
     * ==========================================
     */

    /**
     * Set flash message
     */
    public function setFlash($key, $message)
    {
        $_SESSION['_flash'][$key] = $message;
        return $this;
    }

    /**
     * Get flash message (auto-delete)
     */
    public function getFlash($key, $default = null)
    {
        $message = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $message;
    }

    /**
     * Check if flash message exists
     */
    public function hasFlash($key)
    {
        return isset($_SESSION['_flash'][$key]);
    }
}
