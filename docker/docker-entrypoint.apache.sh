#!/bin/sh
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

    # Ensure database file exists for SQLite
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        echo "Creating SQLite database file..."
        touch /var/www/html/database/database.sqlite
        chown www-data:www-data /var/www/html/database/database.sqlite
    fi

    # Run migrations
    echo "Running database migrations..."
    php artisan migrate --force

    # Clear and cache config
    echo "Optimizing application..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Set proper permissions
    echo "Setting permissions..."
    chown -R www-data:www-data /var/www/html/storage
    chown -R www-data:www-data /var/www/html/bootstrap/cache
    chown -R www-data:www-data /var/www/html/database

    echo "Setup complete! Starting services..."
fi

# Execute the command
exec "$@"
