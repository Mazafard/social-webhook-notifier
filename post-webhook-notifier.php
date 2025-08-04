<?php
/**
 * Post Webhook Notifier
 *
 * @package PostWebhookNotifier
 * @version 1.0.3
 * @author Maza Fard
 * @license GPL-2.0+
 */

/*
Plugin Name: Post Webhook Notifier
Description: Sends a webhook when a post is published, so you can use it with automation tools like n8n or Zapier.
Version: 1.0.3
Author: Maza Fard
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin path
if (!defined('PWN_PLUGIN_PATH')) {
    define('PWN_PLUGIN_PATH', function_exists('plugin_dir_path') ? plugin_dir_path(__FILE__) : dirname(__FILE__));
}

// Load WordPress compatibility functions (for IDE support)
require_once PWN_PLUGIN_PATH . '/includes/wp-compat.php';

// Main plugin class
if (!class_exists('PostWebhookNotifier')) {
    
    class PostWebhookNotifier {
        
        /**
         * Plugin instance
         */
        private static $instance = null;
        
        /**
         * Get plugin instance (Singleton pattern)
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
            $this->init();
        }
        
        /**
         * Initialize plugin
         */
        private function init() {
            // Load core class
            require_once PWN_PLUGIN_PATH . 'includes/class-pwn-core.php';
            
            // Initialize core
            PWN_Core::get_instance();
            
            // Register activation/deactivation hooks
            if (function_exists('register_activation_hook')) {
                register_activation_hook(__FILE__, array('PWN_Core', 'activate'));
            }
            if (function_exists('register_deactivation_hook')) {
                register_deactivation_hook(__FILE__, array('PWN_Core', 'deactivate'));
            }
        }
    }
}

// Initialize the plugin
PostWebhookNotifier::get_instance();