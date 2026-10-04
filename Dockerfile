FROM richarvey/nginx-php-fpm:latest

# Salin semua source code ke direktori web server
COPY . /var/www/html

# Konfigurasi Nginx dan PHP untuk Laravel
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 0
ENV REAL_IP_HEADER 1
ENV COMPOSER_ALLOW_SUPERUSER 1

# Masuk ke folder kerja dan install dependencies composer
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 80

CMD ["/start.sh"]
