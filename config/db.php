<?php
/**
 * Database Connection Helper
 * Loads configuration and provides utilities for database setup
 */

// Load main configuration
$config = require __DIR__ . '/config.php';

// Environment loader helper
if (!function_exists('loadEnv')) {
    function loadEnv($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1)) {
                    $value = substr($value, 1, -1);
                }

                putenv($key . '=' . $value);
            }
        }

        return true;
    }
}

// Load .env file if it exists
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    loadEnv($envFile);
}

// Also check for .env.local (local overrides)
$envLocalFile = dirname(__DIR__) . '/.env.local';
if (file_exists($envLocalFile)) {
    loadEnv($envLocalFile);
}

return $config;
?>
