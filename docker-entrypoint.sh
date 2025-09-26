#!/bin/bash
set -e

echo "Starting Laravel application setup..."

# Configure Apache port dynamically
if [ ! -z "$PORT" ]; then
    echo "Configuring Apache for port $PORT"
    # Update Apache configuration for dynamic port
    sed -i "s/:80>/:$PORT>/g" /etc/apache2/sites-available/000-default.conf
    sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
    # Ensure the port configuration is correct
    if ! grep -q "Listen $PORT" /etc/apache2/ports.conf; then
        echo "Listen $PORT" >> /etc/apache2/ports.conf
    fi
fi

# Wait for database to be ready
echo "Waiting for database connection..."
until php artisan migrate:status > /dev/null 2>&1; do
    echo "Database not ready, waiting 5 seconds..."
    sleep 5
done

echo "Database is ready!"

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Clear caches
echo "Clearing caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink if it doesn't exist
echo "Creating storage symlink..."
if [ ! -L "/var/www/html/public/storage" ]; then
    php artisan storage:link
else
    echo "Storage symlink already exists"
fi

# Set proper permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Laravel setup complete!"

# Ensure Apache runs in foreground and doesn't exit
echo "Starting Apache in foreground mode..."

# Start Apache
exec "$@"