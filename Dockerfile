FROM richarvey/nginx-php-fpm:latest

COPY . /var/www/html

ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 0
ENV REAL_IP_HEADER 1
ENV COMPOSER_ALLOW_SUPERUSER 1

WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Berikan izin akses penuh ke folder storage Laravel
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/start.sh"]
