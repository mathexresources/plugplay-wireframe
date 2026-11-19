#!/bin/sh
set -e
cd /var/www/html
[ -f .env ] || cp .env.example .env
# APP_KEY z prostředí, jinak vygenerovat do persistentního úložiště
if [ -z "$APP_KEY" ]; then
  if [ ! -f storage/app/.app_key ]; then php artisan key:generate --show > storage/app/.app_key; fi
  export APP_KEY="$(cat storage/app/.app_key)"
fi
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
  DB_FILE="${DB_DATABASE:-/var/www/html/database/data/database.sqlite}"
  mkdir -p "$(dirname "$DB_FILE")"; [ -f "$DB_FILE" ] || touch "$DB_FILE"
  export DB_DATABASE="$DB_FILE"
fi
mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage database bootstrap/cache
php artisan storage:link >/dev/null 2>&1 || true
php artisan migrate --force
# první spuštění: naplnit ukázkovými daty
php artisan plugplay:seed-if-empty
php artisan config:cache && php artisan route:cache && php artisan view:cache
chown -R www-data:www-data storage database bootstrap/cache
exec "$@"
