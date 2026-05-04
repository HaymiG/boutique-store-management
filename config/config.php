<?php
/**
 * Boutique Store Management System
 * Main Configuration File
 * 
 * This file loads environment variables and sets up core configuration
 * for the application. Do not modify this file directly. Use .env instead.
 */

// Load environment variables
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        
        // Handle boolean-like values
        if ($value === 'true') return true;
        if ($value === 'false') return false;
        if ($value === 'null') return null;
        
        return $value;
    }
}

// Initialize error reporting based on environment
if (env('APP_DEBUG') === true || env('APP_DEBUG') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Application Configuration
define('APP_NAME', env('APP_NAME', 'BoutiqueStoreManagement'));
define('APP_ENV', env('APP_ENV', 'development'));
define('APP_DEBUG', env('APP_DEBUG', false));
define('APP_URL', env('APP_URL', 'http://localhost'));
define('APP_TIMEZONE', env('APP_TIMEZONE', 'UTC'));
define('APP_KEY', env('APP_KEY', 'change-me-in-production'));

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Database Configuration
define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_PORT', env('DB_PORT', 3306));
define('DB_NAME', env('DB_NAME', 'boutique_store_db'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASSWORD', env('DB_PASSWORD', ''));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));
define('DB_COLLATION', env('DB_COLLATION', 'utf8mb4_unicode_ci'));

// Session Configuration
define('SESSION_DRIVER', env('SESSION_DRIVER', 'file'));
define('SESSION_LIFETIME', env('SESSION_LIFETIME', 120));
define('SESSION_SECURE_ONLY', env('SESSION_SECURE_ONLY', false));
define('SESSION_HTTP_ONLY', env('SESSION_HTTP_ONLY', true));

// Security Configuration
define('PASSWORD_HASH_ALGO', env('PASSWORD_HASH_ALGO', 'bcrypt'));
define('PASSWORD_HASH_COST', env('PASSWORD_HASH_COST', 12));

// Logging Configuration
define('LOG_CHANNEL', env('LOG_CHANNEL', 'single'));
define('LOG_LEVEL', env('LOG_LEVEL', 'debug'));
define('LOG_PATH', dirname(__DIR__) . env('LOG_PATH', '/storage/logs'));

// Cache Configuration
define('CACHE_DRIVER', env('CACHE_DRIVER', 'file'));
define('CACHE_PATH', dirname(__DIR__) . env('CACHE_PATH', '/storage/cache'));

// Project Root Directory
define('PROJECT_ROOT', dirname(__DIR__));
define('APP_PATH', PROJECT_ROOT . '/app');
define('CONFIG_PATH', PROJECT_ROOT . '/config');
define('PUBLIC_PATH', PROJECT_ROOT . '/public');
define('STORAGE_PATH', PROJECT_ROOT . '/storage');
define('ROUTES_PATH', PROJECT_ROOT . '/routes');

// Define User Roles (RBAC)
define('ROLES', [
    'manager' => 1,
    'store_keeper' => 2,
    'seller' => 3,
]);

// Define User Role Permissions
define('ROLE_PERMISSIONS', [
    'manager' => [
        'branch.create',
        'branch.read',
        'branch.update',
        'branch.delete',
        'user.create',
        'user.read',
        'user.update',
        'user.delete',
        'inventory.create',
        'inventory.read',
        'inventory.update',
        'inventory.delete',
        'inventory.transfer',
        'sales.read',
        'sales.report',
        'report.generate',
    ],
    'store_keeper' => [
        'inventory.create',
        'inventory.read',
        'inventory.update',
        'stock.manage',
        'sales.read.own',
        'report.inventory',
        'report.alerts',
    ],
    'seller' => [
        'sales.create',
        'sales.read.own',
        'inventory.read',
    ],
]);

// API Configuration
define('API_VERSION', 'v1');
define('API_RESPONSE_FORMAT', 'json');

// Pagination Configuration
define('PAGINATION_PER_PAGE', 15);

// Authentication Configuration
define('AUTH_SESSION_NAME', 'boutique_user');
define('AUTH_SESSION_TIMEOUT', SESSION_LIFETIME * 60); // Convert to seconds
define('AUTH_MAX_LOGIN_ATTEMPTS', 5);
define('AUTH_LOCKOUT_DURATION', 15 * 60); // 15 minutes in seconds

// Ensure required directories exist and are writable
$requiredDirs = [
    LOG_PATH,
    CACHE_PATH,
    STORAGE_PATH . '/uploads',
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    if (!is_writable($dir)) {
        @chmod($dir, 0755);
    }
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', AUTH_SESSION_NAME);
    session_start();
}

return [
    'app' => [
        'name' => APP_NAME,
        'env' => APP_ENV,
        'debug' => APP_DEBUG,
        'url' => APP_URL,
    ],
    'database' => [
        'host' => DB_HOST,
        'port' => DB_PORT,
        'name' => DB_NAME,
        'user' => DB_USER,
        'password' => DB_PASSWORD,
        'charset' => DB_CHARSET,
    ],
    'session' => [
        'driver' => SESSION_DRIVER,
        'lifetime' => SESSION_LIFETIME,
    ],
];
?>
