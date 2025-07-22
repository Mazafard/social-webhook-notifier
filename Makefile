# Social Webhook Notifier Plugin Makefile

PLUGIN_NAME = social-webhook-notifier
VERSION = 1.0
ZIP_FILE = $(PLUGIN_NAME)-$(VERSION).zip

# Default target
all: clean zip

# Create plugin zip file excluding hidden files and directories
zip:
	@echo "Creating plugin zip file..."
	@zip -r $(ZIP_FILE) . \
		-x ".*" \
		-x "*/.*" \
		-x "Makefile" \
		-x "*.zip"
	@echo "Plugin zip created: $(ZIP_FILE)"

# Clean up zip files
clean:
	@echo "Cleaning up old zip files..."
	@rm -f *.zip
	@echo "Cleanup complete"

# List files that will be included in the zip
list:
	@echo "Files that will be included in the plugin zip:"
	@find . -type f \
		! -path "./.*" \
		! -name "Makefile" \
		! -name "*.zip" \
		| sort

.PHONY: all zip clean list