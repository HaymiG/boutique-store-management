<?php
/**
 * Authentication Middleware
 * Checks if user is authenticated before allowing access to protected routes
 */

namespace App\Middleware;

use App\Core\Session;

class AuthMiddleware
{
    /**
     * Handle the middleware check
     */
    public static function handle()
    {
        $session = Session::getInstance();

        // Check if user is authenticated
        if (!$session->isAuthenticated()) {
            // Store intended URL for redirect after login
            $session->set('_intended_url', $_SERVER['REQUEST_URI'] ?? '/dashboard');
            $session->setFlash('error', 'Please log in to access this page.');
            
            header('Location: /login');
            exit;
        }

        // Refresh session timeout
        $session->refreshTimeout();

        return true;
    }
}
?>
