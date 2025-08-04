<?php
/**
 * Enhanced settings page template with better UX
 */

if (!defined('ABSPATH')) exit;

$settings = PWN_Settings::get_instance();
?>

<div class="wrap">
    <h1>
        <span class="dashicons dashicons-share" style="font-size: 28px; margin-right: 8px;"></span>
        Post Webhook Notifier
    </h1>
    
    <p class="description">
        Automatically send webhook notifications when posts are published. Perfect for integration with n8n, Zapier, and other automation tools.
    </p>
    
    <!-- @noinspection HtmlUnknownTarget - WordPress core options.php handles form submission -->
    <form method="post" action="options.php" id="pwn-settings-form">
        <?php settings_fields('pwn_settings'); ?>
        
        <!-- Main Settings Card -->
        <div class="card" style="max-width: none;">
            <h2 class="title">Webhook Configuration</h2>
            
            <table class="form-table">
                <!-- Enable/Disable -->
                <tr class="form-field-row">
                    <th scope="row">Status</th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span>Webhook Status</span></legend>
                            <label>
                                <input type="checkbox" name="pwn_enabled" value="1" <?php checked($settings->get_setting('pwn_enabled'), '1'); ?> />
                                Enable webhook notifications
                            </label>
                            <p class="description">Uncheck to temporarily disable all webhook notifications.</p>
                        </fieldset>
                    </td>
                </tr>
                
                <!-- Webhook URL -->
                <tr class="form-field-row">
                    <th scope="row"><label for="pwn_webhook_url">Webhook URL <span class="required">*</span></label></th>
                    <td>
                        <input type="url" id="pwn_webhook_url" name="pwn_webhook_url" style="width: 100%;" 
                               value="<?php echo esc_attr($settings->get_setting('pwn_webhook_url')); ?>" 
                               placeholder="https://your-automation-service.com/webhook" required />
                        <p class="description">
                            The URL where webhook notifications will be sent. Examples:<br>
                            • <strong>n8n:</strong> https://your-n8n-instance.com/webhook/social-publish<br>
                            • <strong>Zapier:</strong> https://hooks.zapier.com/hooks/catch/[your-hook-id]/<br>
                            • <strong>Custom:</strong> https://yoursite.com/api/webhook
                        </p>
                    </td>
                </tr>
                
                <!-- HTTP Method -->
                <tr class="form-field-row">
                    <th scope="row"><label for="pwn_webhook_method">HTTP Method</label></th>
                    <td>
                        <select name="pwn_webhook_method" id="pwn_webhook_method">
                            <option value="POST" <?php selected($settings->get_setting('pwn_webhook_method'), 'POST'); ?>>POST (recommended)</option>
                            <option value="GET" <?php selected($settings->get_setting('pwn_webhook_method'), 'GET'); ?>>GET</option>
                            <option value="PUT" <?php selected($settings->get_setting('pwn_webhook_method'), 'PUT'); ?>>PUT</option>
                            <option value="PATCH" <?php selected($settings->get_setting('pwn_webhook_method'), 'PATCH'); ?>>PATCH</option>
                        </select>
                        <p class="description">HTTP method for the webhook request. POST is recommended for most services.</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Authentication Card -->
        <div class="card" style="max-width: none;">
            <h2 class="title">Authentication (Optional)</h2>
            
            <table class="form-table">
                <tr class="form-field-row">
                    <th scope="row"><label for="pwn_auth_type">Authentication Type</label></th>
                    <td>
                        <select name="pwn_auth_type" id="pwn_auth_type" onchange="toggleAuthFields()">
                            <option value="none" <?php selected($settings->get_setting('pwn_auth_type'), 'none'); ?>>None</option>
                            <option value="basic" <?php selected($settings->get_setting('pwn_auth_type'), 'basic'); ?>>Basic Auth</option>
                            <option value="header" <?php selected($settings->get_setting('pwn_auth_type'), 'header'); ?>>Header Auth</option>
                            <option value="jwt" <?php selected($settings->get_setting('pwn_auth_type'), 'jwt'); ?>>JWT Auth</option>
                        </select>
                        <p class="description">Choose authentication method if your webhook endpoint requires it.</p>
                    </td>
                </tr>
                
                <!-- Basic Auth Fields -->
                <tr class="form-field-row" id="basic_username_row" style="display: <?php echo $settings->get_setting('pwn_auth_type') === 'basic' ? 'table-row' : 'none'; ?>">
                    <th scope="row"><label for="pwn_basic_username">Username</label></th>
                    <td>
                        <input type="text" id="pwn_basic_username" name="pwn_basic_username" 
                               value="<?php echo esc_attr($settings->get_setting('pwn_basic_username')); ?>" 
                               placeholder="Username" />
                        <p class="description">Username for Basic authentication</p>
                    </td>
                </tr>
                
                <tr class="form-field-row" id="basic_password_row" style="display: <?php echo $settings->get_setting('pwn_auth_type') === 'basic' ? 'table-row' : 'none'; ?>">
                    <th scope="row"><label for="pwn_basic_password">Password</label></th>
                    <td>
                        <input type="password" id="pwn_basic_password" name="pwn_basic_password" 
                               value="<?php echo esc_attr($settings->get_setting('pwn_basic_password')); ?>" 
                               placeholder="Password" />
                        <p class="description">Password for Basic authentication</p>
                    </td>
                </tr>
                
                <!-- Header Auth Fields -->
                <tr class="form-field-row" id="header_name_row" style="display: <?php echo $settings->get_setting('pwn_auth_type') === 'header' ? 'table-row' : 'none'; ?>">
                    <th scope="row"><label for="pwn_header_name">Header Name</label></th>
                    <td>
                        <input type="text" id="pwn_header_name" name="pwn_header_name" 
                               value="<?php echo esc_attr($settings->get_setting('pwn_header_name')); ?>" 
                               placeholder="X-API-Key" />
                        <p class="description">Header name for custom authentication (e.g., X-API-Key, Authorization, etc.)</p>
                    </td>
                </tr>
                
                <tr class="form-field-row" id="header_value_row" style="display: <?php echo $settings->get_setting('pwn_auth_type') === 'header' ? 'table-row' : 'none'; ?>">
                    <th scope="row"><label for="pwn_header_value">Header Value</label></th>
                    <td>
                        <input type="password" id="pwn_header_value" name="pwn_header_value" style="width: 100%;" 
                               value="<?php echo esc_attr($settings->get_setting('pwn_header_value')); ?>" 
                               placeholder="Your API key or header value" />
                        <p class="description">Value for the custom header</p>
                    </td>
                </tr>
                
                <!-- JWT Auth Fields -->
                <tr class="form-field-row" id="jwt_key_type_row" style="display: <?php echo $settings->get_setting('pwn_auth_type') === 'jwt' ? 'table-row' : 'none'; ?>">
                    <th scope="row"><label for="pwn_jwt_key_type">Key Type</label></th>
                    <td>
                        <select name="pwn_jwt_key_type" id="pwn_jwt_key_type">
                            <option value="secret" <?php selected($settings->get_setting('pwn_jwt_key_type'), 'secret'); ?>>Secret (HMAC)</option>
                            <option value="pem" <?php selected($settings->get_setting('pwn_jwt_key_type'), 'pem'); ?>>PEM Key (RSA/ECDSA)</option>
                        </select>
                        <p class="description">Choose whether to use a secret string (HMAC) or PEM key file (RSA/ECDSA)</p>
                    </td>
                </tr>
                
                <tr class="form-field-row" id="jwt_secret_row" style="display: <?php echo $settings->get_setting('pwn_auth_type') === 'jwt' ? 'table-row' : 'none'; ?>">
                    <th scope="row"><label for="pwn_jwt_secret">Secret/PEM Key</label></th>
                    <td>
                        <textarea id="pwn_jwt_secret" name="pwn_jwt_secret" rows="4" style="width: 100%; font-family: monospace;"><?php echo esc_textarea($settings->get_setting('pwn_jwt_secret')); ?></textarea>
                        <p class="description">
                            For HMAC: Enter your secret string<br>
                            For RSA/ECDSA: Paste your PEM private key (including -----BEGIN/END----- lines)
                        </p>
                    </td>
                </tr>
                
                <tr class="form-field-row" id="jwt_passphrase_row" style="display: <?php echo $settings->get_setting('pwn_auth_type') === 'jwt' ? 'table-row' : 'none'; ?>">
                    <th scope="row"><label for="pwn_jwt_passphrase">Passphrase (Optional)</label></th>
                    <td>
                        <input type="password" id="pwn_jwt_passphrase" name="pwn_jwt_passphrase" 
                               value="<?php echo esc_attr($settings->get_setting('pwn_jwt_passphrase')); ?>" 
                               placeholder="PEM key passphrase (if required)" />
                        <p class="description">Only required if your PEM key is encrypted with a passphrase</p>
                    </td>
                </tr>
                
                <tr class="form-field-row" id="jwt_algorithm_row" style="display: <?php echo $settings->get_setting('pwn_auth_type') === 'jwt' ? 'table-row' : 'none'; ?>">
                    <th scope="row"><label for="pwn_jwt_algorithm">Algorithm</label></th>
                    <td>
                        <select name="pwn_jwt_algorithm" id="pwn_jwt_algorithm">
                            <optgroup label="HMAC (Secret)">
                                <option value="HS256" <?php selected($settings->get_setting('pwn_jwt_algorithm'), 'HS256'); ?>>HS256</option>
                                <option value="HS384" <?php selected($settings->get_setting('pwn_jwt_algorithm'), 'HS384'); ?>>HS384</option>
                                <option value="HS512" <?php selected($settings->get_setting('pwn_jwt_algorithm'), 'HS512'); ?>>HS512</option>
                            </optgroup>
                            <optgroup label="RSA (PEM Key)">
                                <option value="RS256" <?php selected($settings->get_setting('pwn_jwt_algorithm'), 'RS256'); ?>>RS256</option>
                                <option value="RS384" <?php selected($settings->get_setting('pwn_jwt_algorithm'), 'RS384'); ?>>RS384</option>
                                <option value="RS512" <?php selected($settings->get_setting('pwn_jwt_algorithm'), 'RS512'); ?>>RS512</option>
                            </optgroup>
                            <optgroup label="ECDSA (PEM Key)">
                                <option value="ES256" <?php selected($settings->get_setting('pwn_jwt_algorithm'), 'ES256'); ?>>ES256</option>
                                <option value="ES384" <?php selected($settings->get_setting('pwn_jwt_algorithm'), 'ES384'); ?>>ES384</option>
                                <option value="ES512" <?php selected($settings->get_setting('pwn_jwt_algorithm'), 'ES512'); ?>>ES512</option>
                            </optgroup>
                            <optgroup label="PSS (PEM Key)">
                                <option value="PS256" <?php selected($settings->get_setting('pwn_jwt_algorithm'), 'PS256'); ?>>PS256</option>
                                <option value="PS384" <?php selected($settings->get_setting('pwn_jwt_algorithm'), 'PS384'); ?>>PS384</option>
                                <option value="PS512" <?php selected($settings->get_setting('pwn_jwt_algorithm'), 'PS512'); ?>>PS512</option>
                            </optgroup>
                        </select>
                        <p class="description">JWT signing algorithm. HMAC algorithms use secret, RSA/ECDSA/PSS use PEM keys.</p>
                    </td>
                </tr>
                
                <tr class="form-field-row" id="jwt_payload_row" style="display: <?php echo $settings->get_setting('pwn_auth_type') === 'jwt' ? 'table-row' : 'none'; ?>">
                    <th scope="row"><label for="pwn_jwt_payload">JWT Payload Template</label></th>
                    <td>
                        <textarea id="pwn_jwt_payload" name="pwn_jwt_payload" rows="4" style="width: 100%; font-family: monospace;"><?php echo esc_textarea($settings->get_setting('pwn_jwt_payload')); ?></textarea>
                        <p class="description">
                            JWT payload in JSON format. Use <code>{{timestamp}}</code> for current Unix timestamp.<br>
                            <strong>Example:</strong> <code>{"iss": "wordpress", "iat": {{timestamp}}, "exp": {{timestamp+3600}}}</code>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Body Format Card -->
        <div class="card" style="max-width: none;">
            <h2 class="title">Request Format</h2>
            
            <table class="form-table">
                <tr class="form-field-row">
                    <th scope="row"><label for="pwn_body_format">Body Format</label></th>
                    <td>
                        <select name="pwn_body_format" id="pwn_body_format" onchange="toggleBodyFormat()">
                            <option value="default" <?php selected($settings->get_setting('pwn_body_format'), 'default'); ?>>Default JSON</option>
                            <option value="custom" <?php selected($settings->get_setting('pwn_body_format'), 'custom'); ?>>Custom Template</option>
                        </select>
                        <p class="description">Choose the format of data sent to your webhook endpoint.</p>
                    </td>
                </tr>
                
                <tr class="form-field-row" id="custom_body_row" style="display: <?php echo $settings->get_setting('pwn_body_format') === 'custom' ? 'table-row' : 'none'; ?>">
                    <th scope="row"><label for="pwn_custom_body">Custom Body Template</label></th>
                    <td>
                        <textarea id="pwn_custom_body" name="pwn_custom_body" rows="8" style="width: 100%; font-family: monospace;"><?php echo esc_textarea($settings->get_setting('pwn_custom_body')); ?></textarea>
                        <p class="description">
                            <strong>Available placeholders:</strong> 
                            <code>{{title}}</code>, <code>{{link}}</code>, <code>{{excerpt}}</code>, <code>{{date}}</code>, <code>{{author}}</code><br>
                            <strong>Default format:</strong> <code>{"title": "{{title}}", "url": "{{link}}", "excerpt": "{{excerpt}}", "date": "{{date}}", "author": "{{author}}"}</code>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Advanced Options Card -->
        <div class="card" style="max-width: none;">
            <h2 class="title">Advanced Options</h2>
            
            <table class="form-table">
                <tr class="form-field-row">
                    <th scope="row">Test Mode</th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span>Test Mode</span></legend>
                            <label>
                                <input type="checkbox" name="pwn_test_mode" value="1" <?php checked($settings->get_setting('pwn_test_mode'), '1'); ?> />
                                Enable test mode (log requests without sending)
                            </label>
                            <p class="description">When enabled, webhook requests will be logged but not actually sent. Useful for testing.</p>
                        </fieldset>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Action Buttons -->
        <div class="card" style="max-width: none;">
            <h2 class="title">Actions</h2>
            
            <p class="submit">
                <?php submit_button('Save Settings', 'primary', 'submit', false); ?>
                <button type="button" id="test-webhook-btn" class="button button-secondary" style="margin-left: 10px;">
                    <span class="dashicons dashicons-arrow-right-alt" style="font-size: 16px; line-height: 1.2; margin-right: 4px;"></span>
                    Test Webhook
                </button>
            </p>
            
            <div id="test-webhook-result" style="margin-top: 15px;"></div>
        </div>
    </form>
</div>
