#!/bin/bash
set -e

# Set permissions for Laravel storage and cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Install composer dependencies if vendor is empty
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo ">>> Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader
fi

# Generate app key if not set
if grep -q "^APP_KEY=$" /var/www/html/.env 2>/dev/null; then
    echo ">>> Generating application key..."
    php artisan key:generate --force
fi

# Clear and cache config
php artisan config:clear 2>/dev/null || true

echo ">>> Laravel is ready. Starting Apache..."

# Execute the CMD
exec "$@"
