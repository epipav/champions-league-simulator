#!/bin/bash
set -e

echo "Starting Champions League API entrypoint..."

# Wait for database to be ready
echo "Waiting for database to be ready..."
until pg_isready -h database -U postgres; do
  echo "Database is unavailable - sleeping"
  sleep 2
done
echo "Database is ready!"

# Navigate to application directory
cd /var/www/html

# Install/update composer dependencies if needed
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Copy .env.docker to .env if .env doesn't exist
if [ ! -f ".env" ]; then
    echo "Copying .env.docker to .env..."
    cp .env.docker .env
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating application key..."
    php artisan key:generate --ansi
fi

# Create storage directories if they don't exist
echo "Setting up storage directories..."
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# # Set proper permissions
# echo "Setting permissions..."
# chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Clear and cache config
echo "Clearing configuration cache..."
php artisan config:clear
php artisan cache:clear

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

echo "Entrypoint completed successfully!"

# Execute the main command (php-fpm)
exec "$@"
