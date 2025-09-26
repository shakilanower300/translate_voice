#!/usr/bin/env bash

set -o pipefail

echo "Starting Laravel application..."

# Ensure PHP is in PATH
export PATH="/usr/bin:$PATH"

# Verify PHP is available
php --version

# Clear stale caches (ignore failures during fresh deploys)
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Create storage symlink
echo "Creating storage symlink..."
php artisan storage:link || true

# Run database migrations with retries to allow the DB to come online
echo "Running database migrations..."
max_attempts=5
attempt=1
until php artisan migrate --force --no-interaction; do
	if [ $attempt -ge $max_attempts ]; then
		echo "Migrations failed after ${max_attempts} attempts. Continuing startup without exiting."
		break
	fi
	echo "Migration attempt ${attempt} failed. Retrying in 5 seconds..."
	attempt=$((attempt + 1))
	sleep 5
done

# Cache configuration for production (ignore failures if config is transient)
echo "Caching configuration..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start the Laravel server
echo "Starting server on port ${PORT:-8000}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}