#!/bin/sh
set -e

echo "=== SWStarter: Starting up ==="

# Copy .env if not present
if [ ! -f /var/www/backend/.env ]; then
    cp /var/www/backend/.env.example /var/www/backend/.env
fi

# Generate app key if not already set
php /var/www/backend/artisan key:generate --force --no-interaction 2>/dev/null || true

# Run migrations
echo "Running database migrations..."
php /var/www/backend/artisan migrate --force --no-interaction

# Cache configuration for production performance
php /var/www/backend/artisan config:cache
php /var/www/backend/artisan route:cache

echo "=== SWStarter: Ready ==="

# ---- Graceful shutdown handler ----
shutdown() {
    echo "=== SWStarter: Received shutdown signal, stopping gracefully ==="

    # Stop accepting new connections
    nginx -s quit 2>/dev/null || true

    # Tell queue workers to finish current job and stop
    php /var/www/backend/artisan queue:restart 2>/dev/null || true

    # Give php-fpm time to finish in-flight requests (SIGQUIT = graceful)
    if [ -f /var/run/php-fpm.pid ]; then
        kill -QUIT "$(cat /var/run/php-fpm.pid)" 2>/dev/null || true
    fi

    # Allow some drain time
    sleep 5

    # Stop supervisor
    if [ -f /var/run/supervisord.pid ]; then
        kill -TERM "$(cat /var/run/supervisord.pid)" 2>/dev/null || true
    fi

    echo "=== SWStarter: Shutdown complete ==="
    exit 0
}

trap shutdown SIGTERM SIGINT SIGQUIT

exec "$@"
