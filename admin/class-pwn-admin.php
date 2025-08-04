<?php
/**
 * Admin functionality class
 */

if (!defined('ABSPATH')) exit;

class PWN_Admin {
    
    /**
     * Admin instance
     */
    private static $instance = null;
    
    /**
     * Get admin instance
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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('plugin_action_links_' . PWN_PLUGIN_BASENAME, array($this, 'add_settings_link'));
        add_action('wp_ajax_pwn_test_webhook', array($this, 'test_webhook'));
        add_action('admin_notices', array($this, 'admin_notices'));
        PWN_Logger::log('Admin hooks registered');
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        PWN_Logger::log('Adding admin menu');
        
        add_options_page(
            'Post Webhook Notifier',
            'Post Webhook Notifier',
            'manage_options',
            'post-webhook-notifier',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_scripts($hook) {
        // Only load on our settings page
        if ($hook !== 'settings_page_post-webhook-notifier') {
            return;
        }
        
        wp_enqueue_script(
            'pwn-admin-js',
            PWN_PLUGIN_URL . 'js/admin.js',
            array('jquery'),
            PWN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'pwn-admin-css',
            PWN_PLUGIN_URL . 'css/admin.css',
            array(),
            PWN_VERSION
        );
        
        // Localize script with nonce and other data
        wp_localize_script('pwn-admin-js', 'PWN_Admin', array(
            'nonce' => wp_create_nonce('pwn_test_webhook'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }
    
    /**
     * Add settings link to plugin actions
     *
     * @param array $links Existing plugin action links
     * @return array Modified plugin action links
     */
    public function add_settings_link($links) {
        // Create settings link using sprintf placeholders (not file references)
        $settings_link = sprintf(
            '<a href="%s">%s</a>', // @noinspection SprintfStringArgumentMismatch
            admin_url('options-general.php?page=post-webhook-notifier'),
            __('Settings', 'post-webhook-notifier')
        );
        
        // Add settings link at the beginning
        array_unshift($links, $settings_link);
        
        return $links;
    }
    
    /**
     * Test webhook AJAX handler
     */
    public function test_webhook() {
        // Verify nonce and permissions
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'pwn_test_webhook') || !current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $webhook_url = get_option('pwn_webhook_url');
        if (!$webhook_url) {
            wp_send_json_error('No webhook URL configured');
        }
        
        // Create test post data
        $test_data = array(
            'title' => 'Test Webhook - ' . gmdate('Y-m-d H:i:s'),
            'link' => home_url('/test-webhook'),
            'excerpt' => 'This is a test webhook notification from Post Webhook Notifier plugin.',
            'date' => gmdate('F j, Y'),
            'author' => wp_get_current_user()->display_name,
        );
        
        // Get webhook instance and send test
        $webhook = PWN_Webhook::get_instance();
        $settings = array(
            'method' => get_option('pwn_webhook_method', 'POST'),
            'auth_type' => get_option('pwn_auth_type', 'none'),
            // Basic Auth
            'basic_username' => get_option('pwn_basic_username'),
            'basic_password' => get_option('pwn_basic_password'),
            // Header Auth
            'header_name' => get_option('pwn_header_name'),
            'header_value' => get_option('pwn_header_value'),
            // JWT Auth
            'jwt_key_type' => get_option('pwn_jwt_key_type', 'secret'),
            'jwt_secret' => get_option('pwn_jwt_secret'),
            'jwt_passphrase' => get_option('pwn_jwt_passphrase'),
            'jwt_algorithm' => get_option('pwn_jwt_algorithm', 'HS256'),
            'jwt_payload' => get_option('pwn_jwt_payload'),
            'body_format' => get_option('pwn_body_format', 'default'),
            'custom_body' => get_option('pwn_custom_body')
        );
        
        // Test the webhook manually
        try {
            $body = json_encode($test_data);
            $method = $settings['method'];
            $headers = $this->prepare_test_headers($settings);
            
            // Handle GET method
            if ($method === 'GET') {
                $webhook_url = add_query_arg($test_data, $webhook_url);
                $args = array('method' => $method, 'headers' => $headers, 'timeout' => 10);
            } else {
                $args = array('method' => $method, 'headers' => $headers, 'body' => $body, 'timeout' => 10);
            }
            
            $response = wp_remote_request($webhook_url, $args);
            
            if (is_wp_error($response)) {
                /** @var WP_Error $response */
                wp_send_json_error('Webhook test failed: ' . $response->get_error_message());
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                wp_send_json_success('Webhook test successful! Response code: ' . $response_code);
            }
        } catch (Exception $e) {
            wp_send_json_error('Webhook test failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Prepare test headers based on authentication settings
     *
     * @param array $settings Authentication settings
     * @return array Headers
     */
    private function prepare_test_headers($settings) {
        $headers = array('Content-Type' => 'application/json');
        
        switch ($settings['auth_type']) {
            case 'basic':
                if ($settings['basic_username'] && $settings['basic_password']) {
                    $credentials = base64_encode($settings['basic_username'] . ':' . $settings['basic_password']);
                    $headers['Authorization'] = 'Basic ' . $credentials;
                }
                break;
                
            case 'header':
                if ($settings['header_name'] && $settings['header_value']) {
                    $headers[$settings['header_name']] = $settings['header_value'];
                }
                break;
                
            case 'jwt':
                if ($settings['jwt_secret'] && $settings['jwt_algorithm']) {
                    $jwt = PWN_JWT::get_instance();
                    $jwt_token = $jwt->generate_token(
                        $settings['jwt_algorithm'],
                        $settings['jwt_key_type'],
                        $settings['jwt_secret'],
                        $settings['jwt_passphrase'],
                        $settings['jwt_payload']
                    );
                    if ($jwt_token) {
                        $headers['Authorization'] = 'Bearer ' . $jwt_token;
                    }
                }
                break;
        }
        
        return $headers;
    }
    
    /**
     * Display admin notices
     */
    public function admin_notices() {
        $screen = get_current_screen();
        if ($screen->id !== 'settings_page_post-webhook-notifier') {
            return;
        }
        
        // Check if webhook URL is configured
        $webhook_url = get_option('pwn_webhook_url');
        if (!$webhook_url) {
            echo '<div class="notice notice-warning"><p><strong>Post Webhook Notifier:</strong> Please configure your webhook URL to start receiving notifications.</p></div>';
        }
        
        // Show test mode notice
        if (get_option('pwn_test_mode', '0')) {
            echo '<div class="notice notice-info"><p><strong>Test Mode Active:</strong> Webhooks are being logged but not actually sent.</p></div>';
        }
    }
    
    /**
     * Display settings page
     */
    public function settings_page() {
        PWN_Logger::log('Settings page accessed');
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'post-webhook-notifier'));
        }
        
        require_once PWN_PLUGIN_PATH . '/admin/views/settings-page.php';
    }
}