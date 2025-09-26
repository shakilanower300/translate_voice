#!/usr/bin/env bash
# exit on error
set -o errexit

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Create SQLite database if it doesn't exist
mkdir -p database
touch database/database.sqlite

# Install Node.js dependencies and build assets
npm ci
npm run build

# Set proper permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache