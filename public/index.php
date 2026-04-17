<?php
/**
 * Boutique Store Management System
 * Application Entry Point (Front Controller)
 * 
 * All requests are routed through this file via .htaccess
 */

// Autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load environment variables
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }
            putenv($key . '=' . $value);
        }
    }
}

// Load configuration
require_once dirname(__DIR__) . '/config/config.php';

// Initialize Router
use App\Core\Router;

$router = new Router();

// Load routes
$registerRoutes = require dirname(__DIR__) . '/routes/web.php';
$registerRoutes($router);

// Dispatch the request
try {
    $router->dispatch();
} catch (\Exception $e) {
    if (APP_DEBUG) {
        echo '<h1>Error</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        http_response_code(500);
        $errorView = APP_PATH . '/views/errors/500.php';
        if (file_exists($errorView)) {
            include $errorView;
        } else {
            echo 'An internal error occurred.';
        }
    }
}
?>
