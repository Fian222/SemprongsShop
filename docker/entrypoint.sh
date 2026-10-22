#!/bin/sh
set -e

cd /var/www/html

# Laravel butuh file .env walaupun nilainya datang dari environment container
[ -f .env ] || cp .env.example .env

if [ -z "$APP_KEY" ]; then
    echo "[entrypoint] PERINGATAN: APP_KEY kosong, membuat key sementara (session akan hilang tiap restart)."
    echo "[entrypoint] Set APP_KEY di .env.podman, contoh: podman compose run --rm app php artisan key:generate --show"
    export APP_KEY="$(php artisan key:generate --show --no-ansi)"
fi

# Tunggu database siap
if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
    echo "[entrypoint] Menunggu database ${DB_HOST}:${DB_PORT:-3306} ..."
    i=0
    until php -r 'new PDO("mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: 3306), getenv("DB_USERNAME"), getenv("DB_PASSWORD"));' >/dev/null 2>&1; do
        i=$((i + 1))
        if [ "$i" -ge 60 ]; then
            echo "[entrypoint] Database tidak bisa dihubungi setelah 60 detik." >&2
            exit 1
        fi
        sleep 1
    done
fi

# Volume baru bisa dimiliki root; pastikan www-data bisa menulis
chown -R www-data:www-data storage bootstrap/cache public/build/images

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    # Seeder bawaan tidak idempotent (email admin unique), jadi kegagalan tidak menghentikan container
    php artisan db:seed --force || echo "[entrypoint] Seeder gagal (data mungkin sudah ada), dilewati."
fi

if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache
    # route:cache sengaja tidak dipakai: routes/web.php memakai nama 'admin.login' untuk dua route
    php artisan view:cache
else
    php artisan optimize:clear
fi

exec "$@"
