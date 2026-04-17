<?php
/**
 * Role Middleware
 * Checks if authenticated user has the required role
 */

namespace App\Middleware;

use App\Core\Session;

class RoleMiddleware
{
    /**
     * Check if user has the manager role
     */
    public static function requireManager()
    {
        return self::checkRole('manager');
    }

    /**
     * Check if user has the store_keeper role
     */
    public static function requireStoreKeeper()
    {
        return self::checkRole(['manager', 'store_keeper']);
    }

    /**
     * Check if user has the seller role
     */
    public static function requireSeller()
    {
        return self::checkRole(['manager', 'store_keeper', 'seller']);
    }

    /**
     * Check if user has any of the required roles
     */
    public static function checkRole($requiredRoles)
    {
        $session = Session::getInstance();

        // First ensure authenticated
        if (!$session->isAuthenticated()) {
            $session->setFlash('error', 'Please log in to access this page.');
            header('Location: /login');
            exit;
        }

        $user = $session->getUser();
        $userRole = $user['role'] ?? null;

        $requiredRoles = (array)$requiredRoles;

        if (!in_array($userRole, $requiredRoles)) {
            http_response_code(403);
            $errorView = APP_PATH . '/views/errors/403.php';
            if (file_exists($errorView)) {
                $code = 403;
                $message = 'You do not have permission to access this page.';
                $currentUser = $user;
                include $errorView;
            } else {
                echo '<h1>403 - Forbidden</h1><p>You do not have permission to access this page.</p>';
            }
            exit;
        }

        return true;
    }
}
?>
