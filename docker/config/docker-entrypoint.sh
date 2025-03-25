#!/bin/bash
set -e

# Create writable directories if they don't exist
echo "Setting up writable directories..."
mkdir -p /var/www/html/writable/cache
mkdir -p /var/www/html/writable/logs
mkdir -p /var/www/html/writable/session
mkdir -p /var/www/html/writable/uploads

# Set proper permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/writable
chmod -R 775 /var/www/html/writable

# Pass the command to docker
exec "$@"