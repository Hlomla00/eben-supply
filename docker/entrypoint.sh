#!/bin/sh
# PRT362S — Eben Supply | Group KN3
#
# Runs on every container start, before Apache:
#   1. listen on $PORT when the host sets one (e.g. Render)
#   2. make sure there is an APP_KEY (kept in the storage volume)
#   3. wait for the database, run migrations
#   4. seed only when the database is empty, so restarts never
#      overwrite stock or prices changed in the admin panel
#   5. cache config/routes/views and hand over to Apache
set -e
cd /var/www/html

PORT="${PORT:-80}"
if [ "$PORT" != "80" ]; then
    sed -i "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

# ── App key ─────────────────────────────────────────────────────────────────
if [ -z "$APP_KEY" ]; then
    KEY_FILE=storage/app/.app_key
    if [ ! -s "$KEY_FILE" ]; then
        echo "[entrypoint] No APP_KEY given — generating one in $KEY_FILE"
        php artisan key:generate --show --no-ansi > "$KEY_FILE"
    fi
    APP_KEY="$(cat "$KEY_FILE")"
    export APP_KEY
fi

# ── Database ────────────────────────────────────────────────────────────────
if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
    echo "[entrypoint] Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
    tries=0
    until php -r '
        try {
            new PDO("mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: 3306).";dbname=".getenv("DB_DATABASE"),
                    getenv("DB_USERNAME"), getenv("DB_PASSWORD"), [PDO::ATTR_TIMEOUT => 3]);
        } catch (Throwable $e) { fwrite(STDERR, $e->getMessage()."\n"); exit(1); }
    ' 2>/dev/null; do
        tries=$((tries + 1))
        if [ "$tries" -ge 60 ]; then
            echo "[entrypoint] MySQL is still unreachable after 2 minutes — check DB_HOST, DB_DATABASE, DB_USERNAME and DB_PASSWORD." >&2
            exit 1
        fi
        sleep 2
    done
fi

php artisan migrate --force --no-interaction

if [ "${SEED_ON_EMPTY:-true}" = "true" ]; then
    users="$(php artisan tinker --execute='echo \App\Models\User::count();' 2>/dev/null | tail -n 1)"
    if [ "$users" = "0" ]; then
        echo "[entrypoint] Empty database — seeding demo users and products"
        php artisan db:seed --force --no-interaction
    fi
fi

# ── Storage and caches ──────────────────────────────────────────────────────
php artisan storage:link --force > /dev/null 2>&1 || true
php artisan optimize || echo "[entrypoint] Warning: optimize failed; running without caches" >&2

# artisan ran as root above; Apache runs as www-data and must write here
chown -R www-data:www-data storage bootstrap/cache

exec "$@"
