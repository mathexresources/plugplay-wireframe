# PlugPlay.cz – image pro Raspberry Pi (arm64) i x86. PHP 8.4 + Apache.
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction
COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts

FROM php:8.4-apache
RUN apt-get update && apt-get install -y --no-install-recommends libsqlite3-dev libzip-dev libicu-dev \
 && docker-php-ext-install pdo_mysql pdo_sqlite intl zip opcache \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf \
 && printf 'upload_max_filesize=8M\npost_max_size=20M\nmemory_limit=256M\nexpose_php=Off\n' > /usr/local/etc/php/conf.d/plugplay.ini
WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY docker/entrypoint.sh /usr/local/bin/plugplay-entrypoint
RUN chmod +x /usr/local/bin/plugplay-entrypoint && chown -R www-data:www-data storage bootstrap/cache database
ENTRYPOINT ["plugplay-entrypoint"]
CMD ["apache2-foreground"]
