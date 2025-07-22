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
                        <input type="text" name="swn_webhook_url" style="width: 100%;" value="<?php echo esc_attr(get_option('swn_webhook_url')); ?>" />
                        <p class="description">example: https://your-n8n-instance.com/webhook/social-publish</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Hook when a post is published
add_action('publish_post', function($post_ID, $post) {
    $webhook_url = get_option('swn_webhook_url');
    if (!$webhook_url) return;

    $payload = [
        'title'   => get_the_title($post_ID),
        'link'    => get_permalink($post_ID),
        'excerpt' => get_the_excerpt($post_ID),
        'date'    => get_the_date('', $post_ID),
        'author'  => get_the_author_meta('display_name', $post->post_author),
    ];

    wp_remote_post($webhook_url, [
        'headers' => [ 'Content-Type' => 'application/json' ],
        'body'    => json_encode($payload),
    ]);
}, 10, 2);
