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

# Map Railway-provided MySQL env vars to Laravel if DB_* not set
if [ -z "$DB_HOST" ] && [ -n "$MYSQLHOST" ]; then
    echo "Detected Railway MySQL environment variables. Mapping to Laravel..."
    export DB_CONNECTION=${DB_CONNECTION:-mysql}
    export DB_HOST="$MYSQLHOST"
    export DB_PORT="${MYSQLPORT:-3306}"
    export DB_DATABASE="$MYSQLDATABASE"
    export DB_USERNAME="$MYSQLUSER"
    export DB_PASSWORD="$MYSQLPASSWORD"
fi

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
    # Wait for DB readiness with retries
    MAX_RETRIES=20
    RETRY_DELAY=2
    TRY=1
    while [ $TRY -le $MAX_RETRIES ]; do
        php artisan migrate:status &> /dev/null && READY=1 || READY=0
        if [ $READY -eq 1 ]; then
            echo "Database connected successfully, running migrations..."
            php artisan migrate --force --no-interaction || echo "Migrations failed, continuing..."
            break
        else
            echo "[$TRY/$MAX_RETRIES] Database not ready yet, retrying in ${RETRY_DELAY}s..."
            sleep $RETRY_DELAY
        fi
        TRY=$((TRY+1))
    done
    if [ ${READY:-0} -ne 1 ]; then
        echo "Database not available after retries, skipping migrations..."
    fi
else
    echo "Database environment variables not set. Running in stateless mode (history disabled)."
fi

# Cache configuration
php artisan config:cache || echo "Config cache failed, continuing..."

echo "Laravel setup completed. Starting PHP server..."

# Start PHP built-in server
exec php -S 0.0.0.0:${PORT:-8000} -t public