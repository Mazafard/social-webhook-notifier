/**
 * Admin JavaScript functionality
 */

(function() {
    'use strict';
    
    /**
     * Toggle authentication fields based on auth type
     */
    function toggleAuthFields() {
        var authType = document.getElementById('swn_auth_type').value;
        var headerRow = document.getElementById('auth_header_row');
        var tokenRow = document.getElementById('auth_token_row');
        
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
    
    /**
     * Toggle body format fields
     */
    function toggleBodyFormat() {
        var bodyFormat = document.getElementById('swn_body_format').value;
        var customBodyRow = document.getElementById('custom_body_row');
        
        if (bodyFormat === 'custom') {
            customBodyRow.style.display = 'table-row';
        } else {
            customBodyRow.style.display = 'none';
        }
    }
    
    /**
     * Initialize page functionality
     */
    function init() {
        // Make functions global for inline handlers
        window.toggleAuthFields = toggleAuthFields;
        window.toggleBodyFormat = toggleBodyFormat;
        
        // Initialize visibility on page load
        toggleAuthFields();
        toggleBodyFormat();
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();