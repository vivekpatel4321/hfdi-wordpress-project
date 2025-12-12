#!/bin/bash

PLUGIN_DIR="/var/www/property_point/app/public/wp-content/plugins"
UPGRADE_DIR="/var/www/property_point/app/public/wp-content/upgrade"

# Create upgrade directory if not exists
mkdir -p "$UPGRADE_DIR"

# Loop through each plugin folder
for plugin in "$PLUGIN_DIR"/*; do
    if [ -d "$plugin" ]; then
        plugin_name=$(basename "$plugin")
        echo "Creating upgrade directory for: $plugin_name"
        mkdir -p "$UPGRADE_DIR/$plugin_name"
    fi
done

# Fix permissions
chown -R www-data:www-data "$UPGRADE_DIR"
find "$UPGRADE_DIR" -type d -exec chmod 755 {} \;
find "$UPGRADE_DIR" -type f -exec chmod 644 {} \;

echo "Done! All plugin upgrade directories created."

