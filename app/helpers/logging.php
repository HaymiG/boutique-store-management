<?php

/**
 * Simple Logging Helper
 * Basic file-based logging for the application
 */

if (!function_exists('log_message')) {
    /**
     * Log a message to the application log file
     *
     * @param string $message The message to log
     * @param string $level The log level (info, error, warning, debug)
     */
    function log_message($message, $level = 'info')
    {
        if (!defined('STORAGE_PATH')) {
            return false;
        }

        $logDir = STORAGE_PATH . '/logs';

        // Create log directory if needed
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/app.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message\n";

        return @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

// Simple Logger class wrapper
if (!class_exists('Logger')) {
    class Logger
    {
        public function info($message)
        {
            return log_message($message, 'info');
        }

        public function error($message)
        {
            return log_message($message, 'error');
        }

        public function warning($message)
        {
            return log_message($message, 'warning');
        }

        public function debug($message)
        {
            return log_message($message, 'debug');
        }
    }
}

// Global logger function
if (!function_exists('logger')) {
    function logger()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new Logger();
        }
        return $instance;
    }
}
