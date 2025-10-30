#!/bin/bash
set -e

# Define the default command for Supervisor
DEFAULT_COMMAND=("/usr/bin/supervisord" "-c" "/etc/supervisor/conf.d/supervisord.conf")

# If no command is passed, use Supervisor by default
if [ "$#" -eq 0 ]; then
    set -- "${DEFAULT_COMMAND[@]}"
fi

# Run setup only for the main container process
if [ "$1" = "${DEFAULT_COMMAND[0]}" ] && [ "$2" = "${DEFAULT_COMMAND[1]}" ]; then
    echo "🚀 Starting Laravel initialization..."

    # Ensure required folders exist
    echo "📂 Ensuring storage directories exist..."
    mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache

    # Fix permissions for Apache user
    echo "🔧 Setting permissions..."
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache storage/logs

    # Create .env file if not present
    if [ ! -f .env ]; then
        echo "📄 .env not found. Creating from example..."
        cp .env.example .env
    fi

    # Update .env dynamically with Railway-provided environment variables
    echo "⚙️  Updating .env file with environment variables..."
    sed -i "s|^APP_ENV=.*|APP_ENV=${APP_ENV:-production}|" .env
    sed -i "s|^APP_DEBUG=.*|APP_DEBUG=${APP_DEBUG:-false}|" .env

    if [ -n "$DB_CONNECTION" ]; then
        sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=${DB_CONNECTION}|" .env
    fi
    if [ -n "$DB_HOST" ]; then
        sed -i "s|^DB_HOST=.*|DB_HOST=${DB_HOST}|" .env
    fi
    if [ -n "$DB_PORT" ]; then
        sed -i "s|^DB_PORT=.*|DB_PORT=${DB_PORT}|" .env
    fi
    if [ -n "$DB_DATABASE" ]; then
        sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|" .env
    fi
    if [ -n "$DB_USERNAME" ]; then
        sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME}|" .env
    fi
    if [ -n "$DB_PASSWORD" ]; then
        sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env
    fi

    # Generate APP_KEY if missing
    if ! grep -q "^APP_KEY=base64:" .env; then
        echo "🔑 Generating Laravel application key..."
        php artisan key:generate --force || echo "⚠️ Could not generate key, continuing..."
    fi

    # Wait for PostgreSQL or MySQL to be ready (max 60 seconds)
    echo "⏳ Waiting for database to be ready..."
    MAX_RETRIES=30
    RETRY_COUNT=0

    until php -r "
        \$dsn = getenv('DB_CONNECTION') === 'pgsql' 
            ? 'pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE') 
            : 'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE');
        try {
            new PDO(\$dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    "; do
        RETRY_COUNT=$((RETRY_COUNT+1))
        if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
            echo "❌ Database not reachable after 60 seconds. Skipping migrations."
            break
        fi
        echo "   ↪ Retrying in 2s... ($RETRY_COUNT/$MAX_RETRIES)"
        sleep 2
    done

    echo "✅ Database connection established or skipped check."

    # Run Laravel migrations
    echo "🔄 Running database migrations..."
    php artisan migrate --force || echo "⚠️  Migration failed or skipped."

    # Clear and rebuild caches
    echo "♻️  Optimizing application..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Create storage link if it doesn't exist
    if [ ! -L public/storage ]; then
        php artisan storage:link || true
    fi

    echo "✅ Laravel setup complete. Starting services..."
fi

exec "$@"
