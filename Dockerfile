FROM richarvey/nginx-php-fpm:latest

COPY . /var/www/html

ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 0
ENV REAL_IP_HEADER 1
ENV COMPOSER_ALLOW_SUPERUSER 1

# Pasang konfigurasi Nginx untuk Laravel routing (try_files $uri $uri/ /index.php?$query_string)
COPY conf/nginx/nginx-site.conf /etc/nginx/sites-available/default.conf
RUN cp /etc/nginx/sites-available/default.conf /etc/nginx/sites-enabled/default.conf 2>/dev/null || true

WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Berikan izin akses penuh ke folder storage Laravel
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/start.sh"]
