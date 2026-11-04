#!/bin/bash
set -e

echo "=== [1/7] Melakukan Backup Database Pre-Deploy ==="
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
mysqldump -u root rentify > ~/rentify_db_backup_predeploy_${TIMESTAMP}.sql 2>/dev/null || true

echo "=== [2/7] Mengambil Kode Terbaru dari GitHub ==="
cd /var/www/rentify
sudo -u www-data git pull origin main

echo "=== [3/7] Menginstal Dependensi PHP ==="
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction

echo "=== [4/7] Menjalankan Database Migration & Platform Seeder ==="
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan db:seed --class=PlatformVouchersSeeder --force

echo "=== [5/7] Mengoptimasi Cache Laravel ==="
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan storage:link || true

echo "=== [6/7] Memperbaiki Permission Storage & Cache ==="
chown -R www-data:www-data /var/www/rentify/storage /var/www/rentify/bootstrap/cache
chmod -R 775 /var/www/rentify/storage /var/www/rentify/bootstrap/cache

echo "=== [7/7] Merestart Daemon Services ==="
systemctl restart rentify-queue
systemctl restart rentify-whatsapp
systemctl reload php8.3-fpm
systemctl reload nginx

echo "=== [SELESAI] DEPLOYMENT BERHASIL! STATUS SERVICES: ==="
systemctl is-active nginx php8.3-fpm rentify-queue rentify-whatsapp mysql
