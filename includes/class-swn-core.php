<?php
/**
 * Core plugin class
 */

if (!defined('ABSPATH')) exit;

class SWN_Core {
    
    /**
     * Plugin version
     */
    const VERSION = '1.0.2';
    
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
        define('SWN_VERSION', self::VERSION);
        define('SWN_PLUGIN_FILE', SWN_PLUGIN_PATH . '/social-webhook-notifier.php');
        define('SWN_PLUGIN_URL', plugin_dir_url(SWN_PLUGIN_FILE));
        define('SWN_PLUGIN_BASENAME', plugin_basename(SWN_PLUGIN_FILE));
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once SWN_PLUGIN_PATH . '/includes/class-swn-logger.php';
        require_once SWN_PLUGIN_PATH . '/includes/class-swn-jwt.php';
        require_once SWN_PLUGIN_PATH . '/includes/class-swn-webhook.php';
        require_once SWN_PLUGIN_PATH . '/admin/class-swn-admin.php';
        require_once SWN_PLUGIN_PATH . '/admin/class-swn-settings.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Initialize components
        SWN_Logger::get_instance();
        SWN_Webhook::get_instance();
        
        if (is_admin()) {
            SWN_Admin::get_instance();
            SWN_Settings::get_instance();
        }
        
        // Log plugin initialization
        SWN_Logger::log('Plugin initialized successfully');
    }
    
    /**
     * Plugin activation
     */
    public static function activate() {
        SWN_Logger::log('Plugin activated');
    }
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        SWN_Logger::log('Plugin deactivated');
    }
}