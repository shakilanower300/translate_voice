#!/usr/bin/env bash

echo "Starting Laravel application..."

# Ensure PHP is in PATH
export PATH="/usr/bin:$PATH"

# Verify PHP is available
php --version

# Create storage symlink
echo "Creating storage symlink..."
php artisan storage:link || true

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Cache configuration for production
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the Laravel server
echo "Starting server on port ${PORT:-8000}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}