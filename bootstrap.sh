#!/usr/bin/env bash

echo "Setting up Laravel application for Railway..."

# Create essential directories
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions  
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Set permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force --no-interaction
fi

# Create storage symlink
php artisan storage:link || echo "Storage link failed, continuing..."

# Run migrations if database is available
php artisan migrate --force --no-interaction || echo "Migrations failed, continuing..."

# Cache configuration
php artisan config:cache || echo "Config cache failed, continuing..."

echo "Laravel setup completed. Starting PHP server..."

# Start PHP built-in server
exec php -S 0.0.0.0:${PORT:-8000} -t public