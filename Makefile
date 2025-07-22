# Social Webhook Notifier Plugin Makefile

PLUGIN_NAME = social-webhook-notifier
PLUGIN_FILE = social-webhook-notifier.php
README_FILE = readme.txt

# Get current version from plugin file
CURRENT_VERSION := $(shell grep "^Version:" $(PLUGIN_FILE) | sed 's/Version: V\?\(.*\)/\1/' | tr -d ' ')
ZIP_FILE = $(PLUGIN_NAME)-$(CURRENT_VERSION).zip

# Default target
all: clean zip

# Create plugin zip file excluding hidden files and directories
zip:
	@echo "Current version: $(CURRENT_VERSION)"
	@echo "Creating plugin zip file..."
	@zip -r $(ZIP_FILE) . \
		-x ".*" \
		-x "*/.*" \
		-x "Makefile" \
		-x "*.zip"
	@echo "Plugin zip created: $(ZIP_FILE)"

# Update version and create zip
release: clean bump zip

# Bump version (patch by default, can specify: make bump TYPE=minor or TYPE=major)
bump:
	@echo "Current version: $(CURRENT_VERSION)"
	@if [ "$(TYPE)" = "major" ]; then \
		NEW_VERSION=$$(echo $(CURRENT_VERSION) | awk -F. '{print $$1+1".0.0"}'); \
	elif [ "$(TYPE)" = "minor" ]; then \
		NEW_VERSION=$$(echo $(CURRENT_VERSION) | awk -F. '{print $$1"."$$2+1".0"}'); \
	else \
		NEW_VERSION=$$(echo $(CURRENT_VERSION) | awk -F. '{print $$1"."$$2"."$$3+1}'); \
	fi; \
	echo "Bumping version to: $$NEW_VERSION"; \
	sed -i.bak "s/Version: V\?$(CURRENT_VERSION)/Version: $$NEW_VERSION/" $(PLUGIN_FILE); \
	sed -i.bak "s/Stable tag: $(CURRENT_VERSION)/Stable tag: $$NEW_VERSION/" $(README_FILE); \
	rm -f $(PLUGIN_FILE).bak $(README_FILE).bak; \
	echo "Version updated to: $$NEW_VERSION"

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

# Show current version
version:
	@echo "Current version: $(CURRENT_VERSION)"

# Help
help:
	@echo "Available targets:"
	@echo "  zip     - Create zip file with current version"
	@echo "  bump    - Bump patch version (use TYPE=minor or TYPE=major for other bumps)"
	@echo "  release - Bump version and create zip file"
	@echo "  clean   - Remove old zip files"
	@echo "  list    - Show files that will be included in zip"
	@echo "  version - Show current version"
	@echo ""
	@echo "Examples:"
	@echo "  make release          # Bump patch version and create zip"
	@echo "  make bump TYPE=minor  # Bump minor version"
	@echo "  make bump TYPE=major  # Bump major version"

.PHONY: all zip bump release clean list version help