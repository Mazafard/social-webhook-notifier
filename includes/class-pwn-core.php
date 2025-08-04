<?php
/**
 * Core plugin class
 */

if (!defined('ABSPATH')) exit;

class PWN_Core {
    
    /**
     * Plugin version
     */
    const VERSION = '1.0.3';
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
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
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }
    
    /**
     * Define plugin constants
     */
    private function define_constants() {
        if (!defined('PWN_VERSION')) {
            define('PWN_VERSION', self::VERSION);
        }
        if (!defined('PWN_PLUGIN_FILE')) {
            define('PWN_PLUGIN_FILE', PWN_PLUGIN_PATH . '/post-webhook-notifier.php');
        }
        if (!defined('PWN_PLUGIN_URL')) {
            define('PWN_PLUGIN_URL', function_exists('plugin_dir_url') ? plugin_dir_url(PWN_PLUGIN_FILE) : '');
        }
        if (!defined('PWN_PLUGIN_BASENAME')) {
            define('PWN_PLUGIN_BASENAME', function_exists('plugin_basename') ? plugin_basename(PWN_PLUGIN_FILE) : basename(PWN_PLUGIN_FILE));
        }
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once PWN_PLUGIN_PATH . '/includes/class-pwn-logger.php';
        require_once PWN_PLUGIN_PATH . '/includes/class-pwn-jwt.php';
        require_once PWN_PLUGIN_PATH . '/includes/class-pwn-webhook.php';
        require_once PWN_PLUGIN_PATH . '/admin/class-pwn-admin.php';
        require_once PWN_PLUGIN_PATH . '/admin/class-pwn-settings.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Initialize components
        PWN_Logger::get_instance();
        PWN_Webhook::get_instance();
        
        if (function_exists('is_admin') && is_admin()) {
            PWN_Admin::get_instance();
            PWN_Settings::get_instance();
        }
        
        // Log plugin initialization
        PWN_Logger::log('Plugin initialized successfully');
    }
    
    /**
     * Plugin activation
     */
    public static function activate() {
        PWN_Logger::log('Plugin activated');
    }
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        PWN_Logger::log('Plugin deactivated');
    }
}