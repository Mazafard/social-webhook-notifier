<?php
/**
 * Webhook functionality class
 */

if (!defined('ABSPATH')) exit;

class SWN_Webhook {
    
    /**
     * Webhook instance
     */
    private static $instance = null;
    
    /**
     * Get webhook instance
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
        add_action('publish_post', array($this, 'notify_webhook'), 10, 2);
        SWN_Logger::log('Webhook hooks registered');
    }
    
    /**
     * Send webhook notification when post is published
     *
     * @param int $post_id Post ID
     * @param object $post Post object
     */
    public function notify_webhook($post_id, $post) {
        SWN_Logger::log('Webhook notification triggered for post ID: ' . $post_id);
        
        // Check if webhook is enabled
        if (!get_option('swn_enabled', '1')) {
            SWN_Logger::log('Webhook disabled, skipping notification');
            return;
        }
        
        $webhook_url = get_option('swn_webhook_url');
        if (!$webhook_url) {
            SWN_Logger::warning('No webhook URL configured');
            return;
        }
        
        // Get configuration options
        $settings = $this->get_webhook_settings();
        
        // Prepare post data
        $post_data = $this->prepare_post_data($post_id, $post);
        
        // Prepare headers
        $headers = $this->prepare_headers($settings);
        
        // Prepare body
        $body = $this->prepare_body($post_data, $settings);
        
        // Send webhook request
        $this->send_webhook($webhook_url, $settings['method'], $headers, $body);
    }
    
    /**
     * Get webhook settings
     *
     * @return array Webhook settings
     */
    private function get_webhook_settings() {
        return array(
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
    }
    
    /**
     * Prepare post data
     *
     * @param int $post_id Post ID
     * @param object $post Post object
     * @return array Post data
     */
    private function prepare_post_data($post_id, $post) {
        return array(
            'title' => get_the_title($post_id),
            'link' => get_permalink($post_id),
            'excerpt' => get_the_excerpt($post_id),
            'date' => get_the_date('', $post_id),
            'author' => get_the_author_meta('display_name', $post->post_author),
        );
    }
    
    /**
     * Prepare headers for webhook request
     *
     * @param array $settings Webhook settings
     * @return array Headers
     */
    private function prepare_headers($settings) {
        $headers = array('Content-Type' => 'application/json');
        
        // Add authentication based on type
        switch ($settings['auth_type']) {
            case 'basic':
                if ($settings['basic_username'] && $settings['basic_password']) {
                    $credentials = base64_encode($settings['basic_username'] . ':' . $settings['basic_password']);
                    $headers['Authorization'] = 'Basic ' . $credentials;
                    SWN_Logger::log('Added Basic authentication header');
                }
                break;
                
            case 'header':
                if ($settings['header_name'] && $settings['header_value']) {
                    $headers[$settings['header_name']] = $settings['header_value'];
                    SWN_Logger::log('Added custom header: ' . $settings['header_name']);
                }
                break;
                
            case 'jwt':
                if ($settings['jwt_secret'] && $settings['jwt_algorithm']) {
                    $jwt_token = $this->generate_jwt_token($settings);
                    if ($jwt_token) {
                        $headers['Authorization'] = 'Bearer ' . $jwt_token;
                        SWN_Logger::log('Added JWT authentication header');
                    } else {
                        SWN_Logger::warning('Failed to generate JWT token');
                    }
                }
                break;
                
            case 'none':
            default:
                // No authentication
                break;
        }
        
        return $headers;
    }
    
    /**
     * Prepare body for webhook request
     *
     * @param array $post_data Post data
     * @param array $settings Webhook settings
     * @return string Body content
     */
    private function prepare_body($post_data, $settings) {
        if ($settings['body_format'] === 'custom' && $settings['custom_body']) {
            // Replace placeholders in custom template
            $body = str_replace(
                array('{{title}}', '{{link}}', '{{excerpt}}', '{{date}}', '{{author}}'),
                array(
                    str_replace('"', '\"', $post_data['title']),
                    $post_data['link'],
                    str_replace('"', '\"', $post_data['excerpt']),
                    $post_data['date'],
                    str_replace('"', '\"', $post_data['author'])
                ),
                $settings['custom_body']
            );
        } else {
            // Use default JSON format
            $body = json_encode($post_data);
        }
        
        return $body;
    }
    
    /**
     * Generate JWT token
     *
     * @param array $settings JWT settings
     * @return string|false JWT token or false on failure
     */
    private function generate_jwt_token($settings) {
        $jwt = SWN_JWT::get_instance();
        
        return $jwt->generate_token(
            $settings['jwt_algorithm'],
            $settings['jwt_key_type'],
            $settings['jwt_secret'],
            $settings['jwt_passphrase'],
            $settings['jwt_payload']
        );
    }
    
    /**
     * Send webhook request
     *
     * @param string $webhook_url Webhook URL
     * @param string $method HTTP method
     * @param array $headers Request headers
     * @param string $body Request body
     */
    private function send_webhook($webhook_url, $method, $headers, $body) {
        // For GET requests, append data as query parameters
        if ($method === 'GET') {
            $post_data = json_decode($body, true);
            if ($post_data) {
                $webhook_url = add_query_arg($post_data, $webhook_url);
            }
            $args = array(
                'method' => $method,
                'headers' => $headers,
                'timeout' => 30
            );
        } else {
            // For POST, PUT, PATCH requests, send data in body
            $args = array(
                'method' => $method,
                'headers' => $headers,
                'body' => $body,
                'timeout' => 30
            );
        }
        
        // Add test mode prefix to logs
        $test_mode = get_option('swn_test_mode', '0');
        $log_prefix = $test_mode ? '[TEST MODE] ' : '';
        
        SWN_Logger::log($log_prefix . 'Sending webhook to: ' . $webhook_url . ' with method: ' . $method);
        
        // Skip actual request in test mode
        if ($test_mode) {
            SWN_Logger::log('[TEST MODE] Request simulation - would send: ' . $body);
            return;
        }
        
        $response = wp_remote_request($webhook_url, $args);
        
        if (is_wp_error($response)) {
            SWN_Logger::error('Webhook request failed: ' . $response->get_error_message());
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            SWN_Logger::log('Webhook sent successfully. Response code: ' . $response_code);
            
            // Log response body for debugging if needed
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $response_body = wp_remote_retrieve_body($response);
                if ($response_body) {
                    SWN_Logger::log('Response body: ' . substr($response_body, 0, 500));
                }
            }
        }
    }
}