<?php
/*
Plugin Name: Social Webhook Notifier
Description: Sends a webhook when a post is published, so you can use it with n8n or Zapier to post on social media.
Version: 1.0
Author: Maza Fard
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) exit;

// Admin Menu UI
add_action('admin_menu', function() {
    add_options_page(
        'Social Webhook Notifier',
        'Social Webhook Notifier',
        'manage_options',
        'social-webhook-notifier',
        'swn_settings_page'
    );
});

// Register settings
add_action('admin_init', function() {
    register_setting('swn_settings', 'swn_webhook_url', array(
        'sanitize_callback' => 'esc_url_raw'
    ));
    register_setting('swn_settings', 'swn_webhook_method', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('swn_settings', 'swn_auth_type', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('swn_settings', 'swn_auth_header', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('swn_settings', 'swn_auth_token', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('swn_settings', 'swn_body_format', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('swn_settings', 'swn_custom_body', array(
        'sanitize_callback' => 'sanitize_textarea_field'
    ));
});

function swn_settings_page() {
    ?>
    <div class="wrap">
        <h2>Social Webhook Notifier</h2>
        <form method="post" action="options.php">
            <?php settings_fields('swn_settings'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Webhook URL</th>
                    <td>
                        <input type="text" name="swn_webhook_url" style="width: 100%;" value="<?php echo esc_attr(get_option('swn_webhook_url')); ?>" required />
                        <p class="description">example: https://your-n8n-instance.com/webhook/social-publish</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">HTTP Method</th>
                    <td>
                        <select name="swn_webhook_method">
                            <option value="POST" <?php selected(get_option('swn_webhook_method', 'POST'), 'POST'); ?>>POST</option>
                            <option value="PUT" <?php selected(get_option('swn_webhook_method'), 'PUT'); ?>>PUT</option>
                            <option value="PATCH" <?php selected(get_option('swn_webhook_method'), 'PATCH'); ?>>PATCH</option>
                        </select>
                        <p class="description">HTTP method for the webhook request</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Authentication</th>
                    <td>
                        <select name="swn_auth_type" id="swn_auth_type" onchange="toggleAuthFields()">
                            <option value="none" <?php selected(get_option('swn_auth_type', 'none'), 'none'); ?>>None</option>
                            <option value="bearer" <?php selected(get_option('swn_auth_type'), 'bearer'); ?>>Bearer Token</option>
                            <option value="custom" <?php selected(get_option('swn_auth_type'), 'custom'); ?>>Custom Header</option>
                        </select>
                        <p class="description">Authentication method for the webhook</p>
                    </td>
                </tr>
                <tr valign="top" id="auth_header_row" style="display: <?php echo get_option('swn_auth_type') === 'custom' ? 'table-row' : 'none'; ?>">
                    <th scope="row">Auth Header Name</th>
                    <td>
                        <input type="text" name="swn_auth_header" value="<?php echo esc_attr(get_option('swn_auth_header')); ?>" placeholder="X-API-Key" />
                        <p class="description">Header name for custom authentication</p>
                    </td>
                </tr>
                <tr valign="top" id="auth_token_row" style="display: <?php echo in_array(get_option('swn_auth_type'), ['bearer', 'custom']) ? 'table-row' : 'none'; ?>">
                    <th scope="row">Auth Token</th>
                    <td>
                        <input type="password" name="swn_auth_token" value="<?php echo esc_attr(get_option('swn_auth_token')); ?>" style="width: 100%;" />
                        <p class="description">Authentication token or API key</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Body Format</th>
                    <td>
                        <select name="swn_body_format" id="swn_body_format" onchange="toggleBodyFormat()">
                            <option value="default" <?php selected(get_option('swn_body_format', 'default'), 'default'); ?>>Default JSON</option>
                            <option value="custom" <?php selected(get_option('swn_body_format'), 'custom'); ?>>Custom Template</option>
                        </select>
                        <p class="description">Format of the request body</p>
                    </td>
                </tr>
                <tr valign="top" id="custom_body_row" style="display: <?php echo get_option('swn_body_format') === 'custom' ? 'table-row' : 'none'; ?>">
                    <th scope="row">Custom Body Template</th>
                    <td>
                        <textarea name="swn_custom_body" rows="8" style="width: 100%; font-family: monospace;"><?php echo esc_textarea(get_option('swn_custom_body', '{"title": "{{title}}", "url": "{{link}}", "excerpt": "{{excerpt}}", "date": "{{date}}", "author": "{{author}}"}")); ?></textarea>
                        <p class="description">Custom body template. Use placeholders: {{title}}, {{link}}, {{excerpt}}, {{date}}, {{author}}</p>
                    </td>
                </tr>
            </table>
            
            <script>
            function toggleAuthFields() {
                const authType = document.getElementById('swn_auth_type').value;
                const headerRow = document.getElementById('auth_header_row');
                const tokenRow = document.getElementById('auth_token_row');
                
                if (authType === 'custom') {
                    headerRow.style.display = 'table-row';
                    tokenRow.style.display = 'table-row';
                } else if (authType === 'bearer') {
                    headerRow.style.display = 'none';
                    tokenRow.style.display = 'table-row';
                } else {
                    headerRow.style.display = 'none';
                    tokenRow.style.display = 'none';
                }
            }
            
            function toggleBodyFormat() {
                const bodyFormat = document.getElementById('swn_body_format').value;
                const customBodyRow = document.getElementById('custom_body_row');
                
                if (bodyFormat === 'custom') {
                    customBodyRow.style.display = 'table-row';
                } else {
                    customBodyRow.style.display = 'none';
                }
            }
            </script>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Hook when a post is published
add_action('publish_post', function($post_ID, $post) {
    $webhook_url = get_option('swn_webhook_url');
    if (!$webhook_url) return;

    // Get configuration options
    $method = get_option('swn_webhook_method', 'POST');
    $auth_type = get_option('swn_auth_type', 'none');
    $auth_header = get_option('swn_auth_header');
    $auth_token = get_option('swn_auth_token');
    $body_format = get_option('swn_body_format', 'default');
    $custom_body = get_option('swn_custom_body');

    // Prepare post data
    $post_data = [
        'title'   => get_the_title($post_ID),
        'link'    => get_permalink($post_ID),
        'excerpt' => get_the_excerpt($post_ID),
        'date'    => get_the_date('', $post_ID),
        'author'  => get_the_author_meta('display_name', $post->post_author),
    ];

    // Prepare headers
    $headers = ['Content-Type' => 'application/json'];
    
    // Add authentication
    if ($auth_type === 'bearer' && $auth_token) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    } elseif ($auth_type === 'custom' && $auth_header && $auth_token) {
        $headers[$auth_header] = $auth_token;
    }

    // Prepare body
    if ($body_format === 'custom' && $custom_body) {
        // Replace placeholders in custom template
        $body = str_replace(
            ['{{title}}', '{{link}}', '{{excerpt}}', '{{date}}', '{{author}}'],
            [addslashes($post_data['title']), $post_data['link'], addslashes($post_data['excerpt']), $post_data['date'], addslashes($post_data['author'])],
            $custom_body
        );
    } else {
        // Use default JSON format
        $body = json_encode($post_data);
    }

    // Send webhook request
    $args = [
        'method' => $method,
        'headers' => $headers,
        'body' => $body,
        'timeout' => 30
    ];

    wp_remote_request($webhook_url, $args);
}, 10, 2);
