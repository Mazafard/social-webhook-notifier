<?php
/**
 * WordPress Compatibility Functions
 * Provides fallback functions when WordPress is not available (for IDE support)
 */

if (!defined('ABSPATH')) exit;

// Only define these functions if WordPress is not loaded
if (!function_exists('add_action')) {
    function add_action($hook, $function, $priority = 10, $accepted_args = 1) {}
    function add_filter($hook, $function, $priority = 10, $accepted_args = 1) {}
    function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {}
    function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') {}
    function wp_localize_script($handle, $object_name, $l10n) {}
    function wp_create_nonce($action = -1) { return ''; }
    function wp_verify_nonce($nonce, $action = -1) { return false; }
    function admin_url($path = '', $scheme = 'admin') { return ''; }
    function current_user_can($capability, ...$args) { return false; }
    function sanitize_text_field($str) { return $str; }
    function wp_unslash($value) { return $value; }
    function wp_die($message = '', $title = '', $args = array()) { die($message); }
    function get_option($option, $default = false) { return $default; }
    function wp_send_json_error($data = null, $status_code = null) {}
    function wp_send_json_success($data = null, $status_code = null) {}
    function home_url($path = '', $scheme = null) { return ''; }
    function wp_get_current_user() { return (object)array('display_name' => ''); }
    function add_query_arg($key, $value = null, $url = null) { return ''; }
    function wp_remote_request($url, $args = array()) { return array(); }
    function is_wp_error($thing) { return false; }
    function wp_remote_retrieve_response_code($response) { return 200; }
    function wp_remote_retrieve_body($response) { return ''; }
    function get_current_screen() { return (object)array('id' => ''); }
    function esc_html__($text, $domain = 'default') { return $text; }
    function add_options_page($page_title, $menu_title, $capability, $menu_slug, $function = '') {}
    function register_setting($option_group, $option_name, $args = array()) {}
    function settings_fields($option_group) {}
    function checked($checked, $current = true, $echo = true) { return ''; }
    function selected($selected, $current = true, $echo = true) { return ''; }
    function esc_attr($text) { return $text; }
    function esc_textarea($text) { return $text; }
    function submit_button($text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null) {}
    function __($text, $domain = 'default') { return $text; }
    function get_the_title($post = 0) { return ''; }
    function get_permalink($post = 0, $leavename = false) { return ''; }
    function get_the_excerpt($post = null) { return ''; }
    function get_the_date($format = '', $post = null) { return ''; }
    function get_the_author_meta($field = '', $user_id = false) { return ''; }
    
    // WordPress Error class compatibility
    class WP_Error {
        public function get_error_message() { return ''; }
        public function get_error_code() { return ''; }
    }
    
    // Additional WordPress functions
    function locate_template($template_names, $load = false, $require_once = true) { return ''; }
    function load_template($_template_file, $require_once = true) {}
}