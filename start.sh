#!/bin/sh
# Clear caches to ensure we get fresh environment variables
php artisan optimize:clear

# Cache configuration, routes, and views using the real environment variables
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start supervisord to run PHP-FPM and Nginx
exec /usr/bin/supervisord -c /etc/supervisor.d/app.ini
