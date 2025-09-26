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

# Create .env file from environment variables if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file from environment variables..."
    cat > .env << EOF
APP_NAME="${APP_NAME:-Laravel}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL}"

DB_CONNECTION="${DB_CONNECTION:-mysql}"
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-laravel}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"

SESSION_DRIVER="${SESSION_DRIVER:-file}"
CACHE_STORE="${CACHE_STORE:-file}"
QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"

ELEVEN_LABS_API_KEY="${ELEVEN_LABS_API_KEY}"

LOG_CHANNEL=stderr
LOG_LEVEL=info
EOF
fi

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force --no-interaction
fi

# Create storage symlink
php artisan storage:link || echo "Storage link failed, continuing..."

# Check if database connection is configured before running migrations
if [ -n "$DB_HOST" ] && [ -n "$DB_DATABASE" ] && [ -n "$DB_USERNAME" ]; then
    echo "Database configuration found, attempting to run migrations..."
    # Test database connection first
    php artisan migrate:status &> /dev/null
    if [ $? -eq 0 ]; then
        echo "Database connected successfully, running migrations..."
        php artisan migrate --force --no-interaction || echo "Migrations failed, continuing..."
    else
        echo "Database not available yet, skipping migrations..."
    fi
else
    echo "Database environment variables not set. Running in stateless mode (history disabled)."
fi

# Cache configuration
php artisan config:cache || echo "Config cache failed, continuing..."

echo "Laravel setup completed. Starting PHP server..."

# Start PHP built-in server
exec php -S 0.0.0.0:${PORT:-8000} -t public