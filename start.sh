#!/usr/bin/env bash

set -o pipefail

echo "Starting Laravel application..."

# Ensure PHP is in PATH
export PATH="/usr/bin:$PATH"

# Verify PHP is available
php --version

# Create essential directories
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Set permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Clear stale caches (ignore failures during fresh deploys)
php artisan config:clear || echo "Config clear failed, continuing..."
php artisan cache:clear || echo "Cache clear failed, continuing..."
php artisan route:clear || echo "Route clear failed, continuing..."
php artisan view:clear || echo "View clear failed, continuing..."

# Create storage symlink
echo "Creating storage symlink..."
php artisan storage:link || echo "Storage link failed, continuing..."

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force || echo "Key generation failed, continuing..."
fi

# Skip migrations if DB is not available - just start the server
echo "Attempting to run migrations..."
php artisan migrate --force --no-interaction || echo "Migrations failed, starting server anyway..."

# Cache configuration for production (ignore failures if config is transient)
echo "Caching configuration..."
php artisan config:cache || echo "Config cache failed, continuing..."
php artisan route:cache || echo "Route cache failed, continuing..."
php artisan view:cache || echo "View cache failed, continuing..."

# Start the Laravel server
echo "Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}