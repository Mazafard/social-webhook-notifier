<?php
/**
 * Settings management class
 */

if (!defined('ABSPATH')) exit;

class PWN_Settings {
    
    /**
     * Settings instance
     */
    private static $instance = null;
    
    /**
     * Settings fields
     */
    private $settings_fields = array(
        'pwn_webhook_url' => 'esc_url_raw',
        'pwn_webhook_method' => 'sanitize_text_field',
        'pwn_auth_type' => 'sanitize_text_field',
        // Basic Auth
        'pwn_basic_username' => 'sanitize_text_field',
        'pwn_basic_password' => 'sanitize_text_field',
        // Header Auth
        'pwn_header_name' => 'sanitize_text_field',
        'pwn_header_value' => 'sanitize_text_field',
        // JWT Auth
        'pwn_jwt_key_type' => 'sanitize_text_field',
        'pwn_jwt_secret' => 'sanitize_textarea_field',
        'pwn_jwt_passphrase' => 'sanitize_text_field',
        'pwn_jwt_algorithm' => 'sanitize_text_field',
        'pwn_jwt_payload' => 'sanitize_textarea_field',
        'pwn_body_format' => 'sanitize_text_field',
        'pwn_custom_body' => 'sanitize_textarea_field',
        'pwn_enabled' => 'sanitize_text_field',
        'pwn_test_mode' => 'sanitize_text_field'
    );
    
    /**
     * Get settings instance
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
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_init', array($this, 'register_settings'));
        PWN_Logger::log('Settings hooks registered');
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        PWN_Logger::log('Registering plugin settings');
        
        foreach ($this->settings_fields as $field => $sanitize_callback) {
            register_setting('pwn_settings', $field, array(
                'sanitize_callback' => $sanitize_callback
            ));
        }
    }
    
    /**
     * Get default values for settings
     *
     * @return array Default settings
     */
    public function get_default_settings() {
        return array(
            'pwn_webhook_url' => '',
            'pwn_webhook_method' => 'POST',
            'pwn_auth_type' => 'none',
            // Basic Auth
            'pwn_basic_username' => '',
            'pwn_basic_password' => '',
            // Header Auth
            'pwn_header_name' => '',
            'pwn_header_value' => '',
            // JWT Auth
            'pwn_jwt_key_type' => 'secret',
            'pwn_jwt_secret' => '',
            'pwn_jwt_passphrase' => '',
            'pwn_jwt_algorithm' => 'HS256',
            'pwn_jwt_payload' => '{"iss": "wordpress", "iat": {{timestamp}}}',
            'pwn_body_format' => 'default',
            'pwn_custom_body' => '{"title": "{{title}}", "url": "{{link}}", "excerpt": "{{excerpt}}", "date": "{{date}}", "author": "{{author}}"}',
            'pwn_enabled' => '1',
            'pwn_test_mode' => '0'
        );
    }
    
    /**
     * Get setting value with default fallback
     *
     * @param string $setting_name Setting name
     * @return mixed Setting value
     */
    public function get_setting($setting_name) {
        $defaults = $this->get_default_settings();
        $default = isset($defaults[$setting_name]) ? $defaults[$setting_name] : '';
        
        return get_option($setting_name, $default);
    }
    
    /**
     * Get all settings
     *
     * @return array All settings
     */
    public function get_all_settings() {
        $settings = array();
        
        foreach ($this->settings_fields as $field => $callback) {
            $settings[$field] = $this->get_setting($field);
        }
        
        return $settings;
    }
}