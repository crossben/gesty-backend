#!/bin/sh
set -e

# Wait for database to be ready (optional but recommended)
echo "Waiting for database..."
# In a real environment, you might use a tool like 'wait-for-it' or a simple loop
# sleep 5 

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Seed if requested (e.g. via an environment variable)
# if [ "$SEED_DATABASE" = "true" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
# fi

# Clear and cache configuration
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Apache..."
exec apache2-foreground
