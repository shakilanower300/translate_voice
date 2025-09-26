#!/usr/bin/env bash
# exit on error
set -o errexit

# Install PHP and required extensions
echo "Installing PHP and extensions..."
apt-get update
apt-get install -y php8.2 php8.2-cli php8.2-common php8.2-curl php8.2-zip php8.2-gd php8.2-mysql php8.2-xml php8.2-mbstring php8.2-sqlite3 php8.2-intl unzip curl nodejs npm

# Install Composer
echo "Installing Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Clear all caches
echo "Clearing Laravel caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Create SQLite database if it doesn't exist
echo "Setting up database..."
mkdir -p database
touch database/database.sqlite

# Install Node.js dependencies and build assets
echo "Building frontend assets..."
npm ci --only=production
npm run build

# Set proper permissions
echo "Setting permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage
chmod -R 777 bootstrap/cache

echo "Build completed successfully!"