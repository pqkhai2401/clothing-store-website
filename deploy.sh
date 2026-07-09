#!/bin/bash
set -e

echo "=============================="
echo "  HK Store - Deploy Script"
echo "=============================="

# Install PHP dependencies (no dev packages)
echo "[1/6] Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Clear old cached config
echo "[2/6] Clearing old caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache config/routes/views for production
echo "[3/6] Caching config, routes, views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run database migrations
echo "[4/6] Running database migrations..."
php artisan migrate --force

# Generate Passport encryption keys
echo "[5/6] Publishing Passport keys..."
php artisan passport:keys --force

# Create storage symlink (if needed)
echo "[6/6] Creating storage symlink..."
php artisan storage:link || true

echo "=============================="
echo "  Deploy completed successfully!"
echo "=============================="
