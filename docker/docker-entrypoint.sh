#!/bin/bash
set -e

DEFAULT_COMMAND=("/usr/bin/supervisord" "-c" "/etc/supervisor/conf.d/supervisord.conf")

if [ "$#" -eq 0 ]; then
    set -- "${DEFAULT_COMMAND[@]}"
fi

if [ "$1" = "${DEFAULT_COMMAND[0]}" ] && [ "$2" = "${DEFAULT_COMMAND[1]}" ]; then
    echo "🚀 Starting Laravel setup..."

    if [ ! -f .env ]; then
        echo "Creating .env file from .env.example..."
        cp .env.example .env
    fi

    if ! grep -q "APP_KEY=base64:" .env; then
        echo "Generating Laravel app key..."
        php artisan key:generate --force
    fi

    echo "Setting permissions..."
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache

    echo "⏳ Waiting for PostgreSQL to be ready..."
    until PGPASSWORD="${DB_PASSWORD}" psql -h "${DB_HOST}" -U "${DB_USERNAME}" -d "${DB_DATABASE}" -p "${DB_PORT}" -c '\q' >/dev/null 2>&1; do
        echo "Waiting for database connection..."
        sleep 2
    done
    echo "✅ Database connection established!"

    echo "Running migrations..."
    php artisan migrate --force || echo "⚠️ Migration skipped (possible fresh DB)."

    echo "Clearing and caching config..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    if [ ! -L public/storage ]; then
        php artisan storage:link || true
    fi

    echo "✅ Laravel setup complete. Starting services..."
fi

exec "$@"
