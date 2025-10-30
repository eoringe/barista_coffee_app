#!/bin/bash
set -e

# Start with a default command
DEFAULT_COMMAND=("/usr/bin/supervisord" "-c" "/etc/supervisor/conf.d/supervisord.conf")

# If no command was provided, use the default
if [ "$#" -eq 0 ]; then
    set -- "${DEFAULT_COMMAND[@]}"
fi

# Only run the following if we're starting the web server
if [ "$1" = "${DEFAULT_COMMAND[0]}" ] && [ "$2" = "${DEFAULT_COMMAND[1]}" ]; then
    echo "Starting Laravel application setup..."

    # Create .env file if it doesn't exist
    if [ ! -f .env ]; then
        echo "Creating .env file from .env.example..."
        cp .env.example .env
    fi

    # Generate application key if not set
    if ! grep -q "APP_KEY=base64:" .env; then
        echo "Generating application key..."
        php artisan key:generate --force
    fi

    # Set proper permissions
    echo "Setting permissions..."
    chown -R www-data:www-data /var/www/html/storage
    chown -R www-data:www-data /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/storage
    chmod -R 775 /var/www/html/bootstrap/cache

    # Ensure Apache can write to storage
    chmod -R 775 /var/www/html/storage/logs/
    chmod -R 775 /var/www/html/storage/framework/

    # Wait for PostgreSQL to be ready with environment variables
    echo "Waiting for PostgreSQL to be ready..."
    echo "DB Connection: postgresql://${DB_USERNAME}@${DB_HOST}:${DB_PORT}/${DB_DATABASE}"
    
    # Export environment variables for the current session
    export DB_CONNECTION=${DB_CONNECTION}
    export DB_HOST=${DB_HOST}
    export DB_PORT=${DB_PORT}
    export DB_DATABASE=${DB_DATABASE}
    export DB_USERNAME=${DB_USERNAME}
    export DB_PASSWORD=${DB_PASSWORD}
    
    # Test the database connection
    until PGPASSWORD="${DB_PASSWORD}" psql -h "${DB_HOST}" -U "${DB_USERNAME}" -d "${DB_DATABASE}" -p "${DB_PORT}" -c '\q' >/dev/null 2>&1; do
        echo "Waiting for database connection..."
        sleep 1
    done
    
    echo "Database connection successful!"

    # Run migrations
    echo "Running database migrations..."
    php artisan migrate --force

    # Clear and cache config
    echo "Optimizing application..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Create storage link if it doesn't exist
    if [ ! -L public/storage ]; then
        php artisan storage:link
    fi

    echo "Setup complete! Starting services..."
fi

# Execute the command
exec "$@"
