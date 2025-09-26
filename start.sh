#!/usr/bin/env bash

# Create storage symlink
php artisan storage:link

# Run migrations
php artisan migrate --force

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the Laravel server
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}