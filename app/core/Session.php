<?php
/**
 * Session Management Class
 * Handles session lifecycle, CSRF tokens, flash messages, and user auth state
 */

namespace App\Core;

class Session
{
    /**
     * Singleton instance
     */
    private static $instance = null;

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
     * Private constructor (singleton)
     */
    private function __construct()
    {
        $this->start();
    }

    /**
     * Start the session if not already started
     */
    public function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure session security
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', SESSION_HTTP_ONLY ? 1 : 0);
            ini_set('session.cookie_secure', SESSION_SECURE_ONLY ? 1 : 0);
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.gc_maxlifetime', AUTH_SESSION_TIMEOUT);
            ini_set('session.name', AUTH_SESSION_NAME);

            session_start();
        }
    }

    /**
     * Destroy the session completely
     */
    public function destroy()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Unset all session variables
            $_SESSION = [];

            // Delete the session cookie
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            session_destroy();
        }
    }

    /**
     * Regenerate session ID (prevent session fixation)
     */
    public function regenerate()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Get a session value
     */
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Check if a session key exists
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session key
     */
    public function remove($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * Get all session data
     */
    public function all()
    {
        return $_SESSION;
    }

    // ===================================
    // CSRF Token Management
    // ===================================

    /**
     * Generate a CSRF token and store in session
     */
    public function generateCsrfToken()
    {
        $token = bin2hex(random_bytes(32));
        $this->set('_csrf_token', $token);
        return $token;
    }

    /**
     * Get the current CSRF token (generate if missing)
     */
    public function getCsrfToken()
    {
        if (!$this->has('_csrf_token')) {
            return $this->generateCsrfToken();
        }
        return $this->get('_csrf_token');
    }

    /**
     * Validate a CSRF token against the session token
     */
    public function validateCsrfToken($token)
    {
        $sessionToken = $this->get('_csrf_token');
        if (!$sessionToken || !$token) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }

    /**
     * Generate hidden input HTML for CSRF token
     */
    public function csrfField()
    {
        $token = $this->getCsrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    // ===================================
    // Flash Messages
    // ===================================

    /**
     * Set a flash message (available only for the next request)
     */
    public function setFlash($key, $message)
    {
        $_SESSION['_flash'][$key] = $message;
    }

    /**
     * Get and remove a flash message
     */
    public function getFlash($key, $default = null)
    {
        $message = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $message;
    }

    /**
     * Check if a flash message exists
     */
    public function hasFlash($key)
    {
        return isset($_SESSION['_flash'][$key]);
    }

    /**
     * Get all flash messages and clear them
     */
    public function getAllFlash()
    {
        $messages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $messages;
    }

    // ===================================
    // User Authentication Helpers
    // ===================================

    /**
     * Set the authenticated user in session
     */
    public function setUser($userData)
    {
        $this->set(AUTH_SESSION_NAME, $userData);
        $this->set('_auth_time', time());
    }

    /**
     * Get the authenticated user data
     */
    public function getUser()
    {
        return $this->get(AUTH_SESSION_NAME);
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated()
    {
        $user = $this->getUser();
        if (!$user) {
            return false;
        }

        // Check session timeout
        $authTime = $this->get('_auth_time', 0);
        if ((time() - $authTime) > AUTH_SESSION_TIMEOUT) {
            $this->removeUser();
            return false;
        }

        return true;
    }

    /**
     * Remove user from session (logout)
     */
    public function removeUser()
    {
        $this->remove(AUTH_SESSION_NAME);
        $this->remove('_auth_time');
    }

    /**
     * Refresh the session timeout
     */
    public function refreshTimeout()
    {
        if ($this->isAuthenticated()) {
            $this->set('_auth_time', time());
        }
    }

    /**
     * Get the authenticated user's role
     */
    public function getUserRole()
    {
        $user = $this->getUser();
        return $user['role'] ?? null;
    }

    /**
     * Get the authenticated user's ID
     */
    public function getUserId()
    {
        $user = $this->getUser();
        return $user['id'] ?? null;
    }
}
?>
