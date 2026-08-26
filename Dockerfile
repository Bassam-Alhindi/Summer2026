# ============================================================================
#  Laravel 13 + Svelte (Inertia) + Vite 8 — Production image
#  Base: php:8.4-cli
# ============================================================================
FROM php:8.4-cli

# --- System tools + required PHP extensions --------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        libsqlite3-dev \
        libpq-dev \
    && docker-php-ext-install pdo_sqlite pdo_pgsql pgsql bcmath mbstring zip xml ctype fileinfo \
    && rm -rf /var/lib/apt/lists/*

# --- Composer ---------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- Node.js 22 + npm -------------------------------------------------------
ENV NODE_VERSION=22.14.0
RUN curl -fsSL "https://nodejs.org/dist/v${NODE_VERSION}/node-v${NODE_VERSION}-linux-x64.tar.xz" \
        -o /tmp/node.tar.xz \
    && tar -xJf /tmp/node.tar.xz -C /usr/local --strip-components=1 \
    && rm /tmp/node.tar.xz

# --- Working directory ------------------------------------------------------
WORKDIR /var/www/html

# Copy project files
COPY . .

# --- Clear local cached config files copied from your host machine ----------
RUN rm -f bootstrap/cache/config.php bootstrap/cache/services.php bootstrap/cache/packages.php

# --- Install PHP dependencies -----------------------------------------------
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader --ignore-platform-req=ext-pdo_pgsql

# --- App setup: .env + storage link ----------------------------------------
RUN touch .env \
    && (php artisan storage:link --force || true)

# --- Build frontend assets (Vite + Svelte) ----------------------------------
RUN npm ci --no-audit --no-fund \
    && npm run build

# --- Permissions ------------------------------------------------------------
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# --- Runtime ----------------------------------------------------------------
ENV PORT=10000
EXPOSE 10000

USER www-data

# Clear cache dynamically on container start and bind Railway PORT
CMD ["sh", "-c", "php artisan config:clear && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]