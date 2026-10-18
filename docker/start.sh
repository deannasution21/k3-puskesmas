#!/bin/sh
set -e

if [ -z "$APP_KEY" ]; then
  echo "PERINGATAN: APP_KEY belum diset. Generate sekali secara lokal dengan:"
  echo "  php artisan key:generate --show"
  echo "lalu tempel hasilnya sebagai environment variable APP_KEY di Railway."
fi

php artisan config:clear
php artisan migrate --force

# Seeding data awal ("php artisan deploy:seed-once") SENGAJA tidak dijalankan
# otomatis di sini lagi. Database production sudah terisi data asli — kalau
# perintah ini tetap dipanggil setiap container start, ada risiko (walau kecil)
# dia menimpa/menambah data dummy begitu ada kondisi yang membuat tabel
# puskesmas kebaca kosong. Kalau suatu saat butuh setup environment baru dari
# nol (mis. staging terpisah), jalankan manual lewat Railway shell:
#   php artisan deploy:seed-once

exec php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"
