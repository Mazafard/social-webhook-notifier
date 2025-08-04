/**
 * Post Webhook Notifier - Admin JavaScript functionality
 */

(function() {
    'use strict';
    
    /**
     * Toggle authentication fields based on auth type
     */
    function toggleAuthFields() {
        var authType = document.getElementById('pwn_auth_type').value;
        
        // Hide all auth fields first
        var authFields = [
            'basic_username_row', 'basic_password_row',
            'header_name_row', 'header_value_row',
            'jwt_key_type_row', 'jwt_secret_row', 'jwt_passphrase_row', 
            'jwt_algorithm_row', 'jwt_payload_row'
        ];
        
        authFields.forEach(function(field) {
            var element = document.getElementById(field);
            if (element) {
                element.style.display = 'none';
            }
        });
        
        // Show relevant fields based on auth type
        if (authType === 'basic') {
            document.getElementById('basic_username_row').style.display = 'table-row';
            document.getElementById('basic_password_row').style.display = 'table-row';
        } else if (authType === 'header') {
            document.getElementById('header_name_row').style.display = 'table-row';
            document.getElementById('header_value_row').style.display = 'table-row';
        } else if (authType === 'jwt') {
            document.getElementById('jwt_key_type_row').style.display = 'table-row';
            document.getElementById('jwt_secret_row').style.display = 'table-row';
            document.getElementById('jwt_passphrase_row').style.display = 'table-row';
            document.getElementById('jwt_algorithm_row').style.display = 'table-row';
            document.getElementById('jwt_payload_row').style.display = 'table-row';
        }
    }
    
    /**
     * Toggle body format fields
     */
    function toggleBodyFormat() {
        var bodyFormat = document.getElementById('pwn_body_format').value;
        var customBodyRow = document.getElementById('custom_body_row');
        
        if (bodyFormat === 'custom') {
            customBodyRow.style.display = 'table-row';
        } else {
            customBodyRow.style.display = 'none';
        }
    }
    
    /**
     * Test webhook functionality with AJAX
     */
    function setupTestWebhook() {
        if (typeof jQuery === 'undefined') return;
        
        jQuery(document).ready(function($) {
            // Test webhook functionality
            $('#test-webhook-btn').on('click', function() {
                var $btn = $(this);
                var $result = $('#test-webhook-result');
                
                // Check if URL is configured
                var webhookUrl = $('#pwn_webhook_url').val();
                if (!webhookUrl) {
                    $result.html('<div class="notice notice-error"><p><strong>Error:</strong> Please configure a webhook URL first.</p></div>');
                    return;
                }
                
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Testing...');
                $result.empty();
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'pwn_test_webhook',
                        nonce: PWN_Admin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.html('<div class="notice notice-success"><p><strong>Success:</strong> ' + response.data + '</p></div>');
                        } else {
                            $result.html('<div class="notice notice-error"><p><strong>Error:</strong> ' + response.data + '</p></div>');
                        }
                    },
                    error: function() {
                        $result.html('<div class="notice notice-error"><p><strong>Error:</strong> Failed to test webhook. Please check your settings.</p></div>');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-arrow-right-alt"></span> Test Webhook');
                    }
                });
            });
            
            // Add event listeners for dynamic field toggling
            $('#pwn_auth_type').on('change', function() {
                toggleAuthFields();
            });
            
            $('#pwn_body_format').on('change', function() {
                toggleBodyFormat();
            });
        });
    }
    
    /**
     * Initialize page functionality
     */
    function init() {
        // Make functions global for inline handlers (fallback)
        window.toggleAuthFields = toggleAuthFields;
        window.toggleBodyFormat = toggleBodyFormat;
        
        // Initialize visibility on page load
        toggleAuthFields();
        toggleBodyFormat();
        
        // Setup test webhook functionality
        setupTestWebhook();
        
        // Add event listeners if jQuery is not available
        if (typeof jQuery === 'undefined') {
            var authSelect = document.getElementById('pwn_auth_type');
            var bodySelect = document.getElementById('pwn_body_format');
            
            if (authSelect) {
                authSelect.addEventListener('change', toggleAuthFields);
            }
            
            if (bodySelect) {
                bodySelect.addEventListener('change', toggleBodyFormat);
            }
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();