#!/bin/sh
set -e

if [ -z "$APP_KEY" ]; then
  echo "PERINGATAN: APP_KEY belum diset. Generate sekali secara lokal dengan:"
  echo "  php artisan key:generate --show"
  echo "lalu tempel hasilnya sebagai environment variable APP_KEY di Railway."
fi

php artisan config:clear
php artisan migrate --force
php artisan deploy:seed-once

exec php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"
