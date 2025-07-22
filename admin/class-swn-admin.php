<?php
/**
 * Admin functionality class
 */

if (!defined('ABSPATH')) exit;

class SWN_Admin {
    
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
        add_filter('plugin_action_links_' . SWN_PLUGIN_BASENAME, array($this, 'add_settings_link'));
        add_action('wp_ajax_swn_test_webhook', array($this, 'test_webhook'));
        add_action('admin_notices', array($this, 'admin_notices'));
        SWN_Logger::log('Admin hooks registered');
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        SWN_Logger::log('Adding admin menu');
        
        add_options_page(
            'Social Webhook Notifier',
            'Social Webhook Notifier',
            'manage_options',
            'social-webhook-notifier',
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
        if ($hook !== 'settings_page_social-webhook-notifier') {
            return;
        }
        
        wp_enqueue_script(
            'swn-admin-js',
            SWN_PLUGIN_URL . 'js/admin.js',
            array(),
            SWN_VERSION,
            true
        );
    }
    
    /**
     * Add settings link to plugin actions
     *
     * @param array $links Existing plugin action links
     * @return array Modified plugin action links
     */
    public function add_settings_link($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('options-general.php?page=social-webhook-notifier'),
            __('Settings', 'social-webhook-notifier')
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
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'swn_test_webhook') || !current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $webhook_url = get_option('swn_webhook_url');
        if (!$webhook_url) {
            wp_send_json_error('No webhook URL configured');
        }
        
        // Create test post data
        $test_data = array(
            'title' => 'Test Webhook - ' . gmdate('Y-m-d H:i:s'),
            'link' => home_url('/test-webhook'),
            'excerpt' => 'This is a test webhook notification from Social Webhook Notifier plugin.',
            'date' => gmdate('F j, Y'),
            'author' => wp_get_current_user()->display_name,
        );
        
        // Get webhook instance and send test
        $webhook = SWN_Webhook::get_instance();
        $settings = array(
            'method' => get_option('swn_webhook_method', 'POST'),
            'auth_type' => get_option('swn_auth_type', 'none'),
            // Basic Auth
            'basic_username' => get_option('swn_basic_username'),
            'basic_password' => get_option('swn_basic_password'),
            // Header Auth
            'header_name' => get_option('swn_header_name'),
            'header_value' => get_option('swn_header_value'),
            // JWT Auth
            'jwt_key_type' => get_option('swn_jwt_key_type', 'secret'),
            'jwt_secret' => get_option('swn_jwt_secret'),
            'jwt_passphrase' => get_option('swn_jwt_passphrase'),
            'jwt_algorithm' => get_option('swn_jwt_algorithm', 'HS256'),
            'jwt_payload' => get_option('swn_jwt_payload'),
            'body_format' => get_option('swn_body_format', 'default'),
            'custom_body' => get_option('swn_custom_body')
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
                    $jwt = SWN_JWT::get_instance();
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
        if ($screen->id !== 'settings_page_social-webhook-notifier') {
            return;
        }
        
        // Check if webhook URL is configured
        $webhook_url = get_option('swn_webhook_url');
        if (!$webhook_url) {
            echo '<div class="notice notice-warning"><p><strong>Social Webhook Notifier:</strong> Please configure your webhook URL to start receiving notifications.</p></div>';
        }
        
        // Show test mode notice
        if (get_option('swn_test_mode', '0')) {
            echo '<div class="notice notice-info"><p><strong>Test Mode Active:</strong> Webhooks are being logged but not actually sent.</p></div>';
        }
    }
    
    /**
     * Display settings page
     */
    public function settings_page() {
        SWN_Logger::log('Settings page accessed');
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'social-webhook-notifier'));
        }
        
        require_once SWN_PLUGIN_PATH . '/admin/views/settings-page.php';
    }
}