#!/bin/bash
set -e

echo "🚀 Starting SAD Task setup..."

# Wait for database to be ready
echo "⏳ Waiting for database..."
while ! mysqladmin ping -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent 2>/dev/null; do
    sleep 1
done
echo "✅ Database is ready!"

# Install composer dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    echo "📦 Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Generate app key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Seed database if tables are empty
echo "🌱 Seeding database..."
php artisan db:seed --force || true

# Clear and cache config
echo "⚡ Optimizing application..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Create storage link
php artisan storage:link 2>/dev/null || true

echo "✅ SAD Task is ready!"
echo "📍 Access the app at: http://localhost:8000"
echo "📍 phpMyAdmin at: http://localhost:8080"

# Start PHP-FPM
exec php-fpm
