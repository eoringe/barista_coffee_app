#!/bin/bash
set -e

# Set environment variables for the current session
export DB_CONNECTION=${DB_CONNECTION}
export DB_HOST=${DB_HOST}
export DB_PORT=${DB_PORT}
export DB_DATABASE=${DB_DATABASE}
export DB_USERNAME=${DB_USERNAME}
export DB_PASSWORD=${DB_PASSWORD}

DEFAULT_COMMAND=("/usr/bin/supervisord" "-c" "/etc/supervisor/conf.d/supervisord.conf")

if [ "$#" -eq 0 ]; then
    set -- "${DEFAULT_COMMAND[@]}"
fi

if [ "$1" = "${DEFAULT_COMMAND[0]}" ] && [ "$2" = "${DEFAULT_COMMAND[1]}" ]; then
    echo "🚀 Starting Laravel setup..."

    # Fix permissions for storage and logs
    echo "🔧 Setting up permissions..."
    mkdir -p storage/logs
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    chmod -R 777 storage/logs

    if [ ! -f .env ]; then
        echo "📄 Creating .env file from .env.example..."
        cp .env.example .env
    fi

    # Update .env with provided environment variables
    if [ -n "$DB_CONNECTION" ]; then
        echo "⚙️  Updating .env with database configuration..."
        sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=${DB_CONNECTION}/" .env
        sed -i "s/^DB_HOST=.*/DB_HOST=${DB_HOST}/" .env
        sed -i "s/^DB_PORT=.*/DB_PORT=${DB_PORT}/" .env
        sed -i "s/^DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE}/" .env
        sed -i "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME}/" .env
        sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" .env
    fi

    if ! grep -q "APP_KEY=base64:" .env; then
        echo "🔑 Generating Laravel app key..."
        php artisan key:generate --force
    fi

    # Wait for database
    echo "⏳ Waiting for PostgreSQL to be ready..."
    until PGPASSWORD="${DB_PASSWORD}" psql -h "${DB_HOST}" -U "${DB_USERNAME}" -d "${DB_DATABASE}" -p "${DB_PORT}" -c '\q' >/dev/null 2>&1; do
        echo "⏳ Waiting for database connection..."
        sleep 2
    done
    echo "✅ Database connection established!"

    # Run migrations and cache config
    echo "🔄 Running database migrations..."
    php artisan migrate --force || echo "⚠️  Migration skipped (possible fresh DB)."

    echo "♻️  Clearing and caching configuration..."
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
