<?php
/**
 * Logger class for debugging
 */

if (!defined('ABSPATH')) exit;

class PWN_Logger {
    
    /**
     * Logger instance
     */
    private static $instance = null;
    
    /**
     * Get logger instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Logger is ready
    }
    
    /**
     * Log a message
     *
     * @param string $message Message to log
     * @param string $level Log level (info, warning, error)
     */
    public static function log($message, $level = 'info') {
        // Only log during development/debugging - can be disabled for production
        if (defined('WP_DEBUG') && WP_DEBUG === true && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG === true) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- This is debug code, only active when WP_DEBUG and WP_DEBUG_LOG are enabled
            error_log('PWN Plugin [' . strtoupper($level) . ']: ' . $message);
        }
    }
    
    /**
     * Log info message
     */
    public static function info($message) {
        self::log($message, 'info');
    }
    
    /**
     * Log warning message
     */
    public static function warning($message) {
        self::log($message, 'warning');
    }
    
    /**
     * Log error message
     */
    public static function error($message) {
        self::log($message, 'error');
    }
}