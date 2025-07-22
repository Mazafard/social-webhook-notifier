# Social Webhook Notifier

A WordPress plugin that sends a JSON webhook when a post is published, enabling seamless integration with automation tools like n8n, Zapier, and IFTTT for social media publishing.

## Features

- 🚀 Automatic webhook notifications on post publish
- 📝 JSON payload with post title, link, excerpt, date, and author
- ⚙️ Simple settings page in WordPress admin
- 🔗 Perfect for n8n and Zapier integrations
- 🛡️ Secure URL validation and sanitization

## Installation

### From WordPress Admin
1. Download the latest release zip file
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the zip file and activate the plugin
4. Configure your webhook URL in Settings → Social Webhook Notifier

### Manual Installation
1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure your webhook URL in Settings → Social Webhook Notifier

## Configuration

1. Navigate to **Settings → Social Webhook Notifier** in your WordPress admin
2. Enter your webhook URL (e.g., `https://your-n8n-instance.com/webhook/social-publish`)
3. Save the settings

## Webhook Payload

When a post is published, the plugin sends a POST request with the following JSON payload:

```json
{
  "title": "Post Title",
  "link": "https://yoursite.com/post-url",
  "excerpt": "Post excerpt or summary",
  "date": "January 1, 2024",
  "author": "Author Name"
}
```

## Integration Examples

### n8n Workflow
1. Create a new workflow in n8n
2. Add a "Webhook" trigger node
3. Use the webhook URL in the plugin settings
4. Add nodes to post to your social media platforms

### Zapier Integration
1. Create a new Zap in Zapier
2. Choose "Webhooks by Zapier" as the trigger
3. Use "Catch Hook" and get your webhook URL
4. Add the webhook URL to the plugin settings
5. Connect to your social media platforms

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Development

### Building the Plugin
```bash
# Create production zip file
make

# Clean up old builds
make clean

# List files that will be included
make list
```

### GitHub Releases
The plugin uses GitHub Actions for automated releases. Create a new release by tagging:

```bash
git tag v1.1.0
git push origin v1.1.0
```

This will automatically:
- Update version numbers
- Generate changelog from commits
- Create a GitHub release with zip file

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Support

- [GitHub Issues](https://github.com/yourusername/social-webhook-notifier/issues)
- [WordPress Plugin Directory](https://wordpress.org/plugins/social-webhook-notifier/)

## Changelog

### 1.0
* Initial release
* Basic webhook functionality
* WordPress admin integration
* n8n and Zapier ready