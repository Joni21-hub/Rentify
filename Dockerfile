FROM php:8.2-fpm-alpine

# Install system dependencies + ekstensi PHP yang dibutuhkan Laravel
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Konfigurasi OPcache untuk performa PHP maksimal
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.memory_consumption=256"; \
    echo "opcache.interned_strings_buffer=16"; \
    echo "opcache.max_accelerated_files=20000"; \
    echo "opcache.revalidate_freq=0"; \
    echo "opcache.validate_timestamps=0"; \
    echo "opcache.save_comments=1"; \
    echo "opcache.fast_shutdown=1"; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Konfigurasi PHP production
RUN { \
    echo "memory_limit=256M"; \
    echo "upload_max_filesize=50M"; \
    echo "post_max_size=50M"; \
    echo "max_execution_time=60"; \
} > /usr/local/etc/php/conf.d/custom.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy aplikasi terlebih dahulu
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Konfigurasi Nginx untuk Laravel
RUN mkdir -p /etc/nginx/http.d
COPY conf/nginx/nginx-site.conf /etc/nginx/http.d/default.conf

# Konfigurasi PHP-FPM: listen di port 9000
RUN sed -i 's|listen = /var/run/php-fpm.sock|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || \
    sed -i 's|^listen =.*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/zz-docker.conf 2>/dev/null || true

# Update nginx config untuk gunakan TCP daripada unix socket
RUN sed -i 's|unix:/var/run/php-fpm.sock|127.0.0.1:9000|g' /etc/nginx/http.d/default.conf

# Konfigurasi Nginx utama
RUN echo $'user nginx;\n\
worker_processes auto;\n\
error_log /dev/stderr warn;\n\
pid /tmp/nginx.pid;\n\
\n\
events {\n\
    worker_connections 1024;\n\
}\n\
\n\
http {\n\
    include /etc/nginx/mime.types;\n\
    default_type application/octet-stream;\n\
    access_log /dev/stdout;\n\
    sendfile on;\n\
    keepalive_timeout 65;\n\
    include /etc/nginx/http.d/*.conf;\n\
}' > /etc/nginx/nginx.conf

# Konfigurasi Supervisor untuk menjalankan nginx + php-fpm bersamaan
RUN mkdir -p /etc/supervisor.d
RUN echo $'[supervisord]\n\
nodaemon=true\n\
logfile=/dev/null\n\
logfile_maxbytes=0\n\
pidfile=/tmp/supervisord.pid\n\
\n\
[program:php-fpm]\n\
command=php-fpm -F\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
autorestart=true\n\
\n\
[program:nginx]\n\
command=nginx -g "daemon off;"\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
autorestart=true' > /etc/supervisor.d/app.ini

# Pastikan direktori storage dan cache tersedia
RUN mkdir -p /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# Buat symlink public/storage
RUN php artisan storage:link --force || true

# Copy script startup dan beri izin eksekusi
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
