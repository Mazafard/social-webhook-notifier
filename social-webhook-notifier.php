<?php
/**
 * Social Webhook Notifier
 *
 * @package SocialWebhookNotifier
 * @version 1.0.2
 * @author Maza Fard
 * @license GPL-2.0+
 */

/*
Plugin Name: Social Webhook Notifier
Description: Sends a webhook when a post is published, so you can use it with n8n or Zapier to post on social media.
Version: 1.0.2
Author: Maza Fard
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin path
define('SWN_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Main plugin class
if (!class_exists('SocialWebhookNotifier')) {
    
    class SocialWebhookNotifier {
        
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
            require_once SWN_PLUGIN_PATH . 'includes/class-swn-core.php';
            
            // Initialize core
            SWN_Core::get_instance();
            
            // Register activation/deactivation hooks
            register_activation_hook(__FILE__, array('SWN_Core', 'activate'));
            register_deactivation_hook(__FILE__, array('SWN_Core', 'deactivate'));
        }
    }
}

// Initialize the plugin
SocialWebhookNotifier::get_instance();