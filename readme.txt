=== Social Webhook Notifier ===
Contributors: mazafard
Tags: webhook, n8n, zapier, automation, jwt
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send webhooks when posts are published with multiple HTTP methods and authentication options.

== Description ==

A powerful WordPress plugin that sends webhook notifications when posts are published, featuring multiple HTTP methods (GET, POST, PUT, PATCH) and comprehensive authentication options. Perfect for integration with automation tools like n8n, Zapier, and custom APIs.

**Key Features:**
* Multiple HTTP methods: GET, POST, PUT, PATCH
* Comprehensive authentication: None, Basic Auth, Header Auth, JWT (with full algorithm support)
* JWT support: HMAC (HS256/384/512), RSA (RS256/384/512), ECDSA (ES256/384/512), PSS (PS256/384/512)
* Custom request body templates with placeholders
* Test webhook functionality from admin panel
* Test mode for debugging without sending actual requests
* Professional admin interface with card-based UI
* Dynamic field visibility and comprehensive logging

== Installation ==

1. Upload the plugin zip file to the `wp-content/plugins/` directory.
2. Activate the plugin through the ‘Plugins’ menu.
3. Set your webhook URL in `Settings → Social Webhook Notifier`.

== Changelog ==

= 1.0.2 =
* Enhanced authentication system with comprehensive options
* Added JWT authentication with full algorithm support (HS256-PS512)
* Added Basic Auth (username/password) support
* Added custom Header Auth support
* Added support for multiple HTTP methods (GET, POST, PUT, PATCH)
* Implemented custom request body templates with placeholders
* Added test webhook functionality with real-time testing
* Added test mode for debugging without sending actual requests
* Enhanced admin interface with dynamic field visibility
* Improved UX with card-based settings layout
* Added comprehensive logging and error handling
* Fixed version format for WordPress compliance

= 1.0.1 =
* Added GET method support with query parameters
* Enhanced settings page with better UX
* Added AJAX test webhook functionality
* Improved code organization and structure

= 1.0 =
* Initial release with basic webhook functionality
