#!/bin/bash

# Run migrations
docker exec sr-health-app php artisan migrate --force

# Run seeders (if any)
docker exec sr-health-app php artisan db:seed --force

echo "Database migration and seeding completed!"
