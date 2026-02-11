# ============================================================
# Stage 1: Build the React SPA
# ============================================================
FROM node:22-alpine AS frontend-build

WORKDIR /app
COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci
COPY frontend/ ./
RUN npm run build

# ============================================================
# Stage 2: Production image (PHP-FPM + Nginx + Supervisor)
# ============================================================
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    sqlite \
    sqlite-dev \
    curl \
    libzip-dev \
    pcre-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-install pdo_sqlite zip pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS pcre-dev \
    && rm -rf /var/cache/apk/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---- Backend setup ----
WORKDIR /var/www/backend
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction
COPY backend/ ./
RUN composer dump-autoload --optimize --no-interaction

# Create SQLite database file
RUN mkdir -p database && touch database/database.sqlite

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache database

# ---- Copy built React SPA ----
COPY --from=frontend-build /app/build /var/www/frontend

# ---- Copy infrastructure configs ----
COPY nginx/default.conf /etc/nginx/http.d/default.conf
COPY supervisord.conf /etc/supervisord.conf
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
