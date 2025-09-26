# Production Deployment Script
# This script helps deploy the Text-to-Speech Translator to production

# Environment setup
echo "Setting up production environment..."

# 1. Copy environment file
cp .env.example .env.production

# 2. Set production values
echo "APP_ENV=production" >> .env.production
echo "APP_DEBUG=false" >> .env.production
echo "APP_URL=https://your-domain.com" >> .env.production

# 3. Database configuration (update with your production values)
echo "DB_CONNECTION=mysql" >> .env.production
echo "DB_HOST=your-production-db-host" >> .env.production
echo "DB_PORT=3306" >> .env.production
echo "DB_DATABASE=text_to_speech_production" >> .env.production
echo "DB_USERNAME=your-db-username" >> .env.production
echo "DB_PASSWORD=your-secure-password" >> .env.production

# 4. Install production dependencies
echo "Installing production dependencies..."
composer install --optimize-autoloader --no-dev --ignore-platform-reqs

# 5. Generate application key
php artisan key:generate

# 6. Run database migrations (make sure database exists)
php artisan migrate --force

# 7. Cache everything for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Set proper permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "Production deployment completed!"
echo "Don't forget to:"
echo "1. Set up your web server (Nginx/Apache)"
echo "2. Configure SSL certificate"
echo "3. Update DNS records"
echo "4. Test the application"

# For Heroku deployment:
# heroku create your-app-name
# heroku config:set APP_KEY=$(php artisan --no-ansi key:generate --show)
# heroku addons:create heroku-mysql:shared-basic
# git push heroku main

# For DigitalOcean App Platform:
# Create .do/app.yaml file and use their git deployment