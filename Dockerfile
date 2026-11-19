# PlugPlay.cz – image pro Raspberry Pi (arm64) i x86. PHP 8.4-FPM + nginx.
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction
COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts

FROM php:8.4-fpm
RUN apt-get update && apt-get install -y --no-install-recommends nginx libsqlite3-dev libzip-dev libicu-dev \
 && docker-php-ext-install pdo_mysql pdo_sqlite intl zip opcache \
 && rm -rf /var/lib/apt/lists/* /etc/nginx/sites-enabled/default \
 && printf 'upload_max_filesize=8M\npost_max_size=20M\nmemory_limit=256M\nexpose_php=Off\n' > /usr/local/etc/php/conf.d/plugplay.ini
COPY docker/nginx.conf /etc/nginx/conf.d/plugplay.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-plugplay.conf
WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY docker/entrypoint.sh /usr/local/bin/plugplay-entrypoint
RUN chmod +x /usr/local/bin/plugplay-entrypoint && chown -R www-data:www-data storage bootstrap/cache database
EXPOSE 80
ENTRYPOINT ["plugplay-entrypoint"]
# PHP-FPM na pozadí, nginx v popředí (PID 1 drží kontejner naživu)
CMD ["sh", "-c", "php-fpm -D && exec nginx -g 'daemon off;'"]
