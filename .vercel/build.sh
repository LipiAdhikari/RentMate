#!/usr/bin/env bash
set -e

echo "🚀 Starting Vercel build for RentMate..."

# Install PHP dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Cache configuration for production
echo "⚡ Caching configuration..."
php artisan config:cache --no-interaction 2>/dev/null || true
php artisan route:cache --no-interaction 2>/dev/null || true
php artisan view:cache --no-interaction 2>/dev/null || true

# Link storage for public files
echo "🔗 Linking storage..."
php artisan storage:link --no-interaction 2>/dev/null || true

echo "✅ Build complete!"
