# ============================================================================
#  Laravel 13 + Svelte (Inertia) + Vite 8 — Production image
#  Base: php:8.4-cli  — composer.json says "php": "^8.3" (allows 8.4) AND the
#  committed composer.lock pins Symfony 8.1.x / aws-sdk deps that require
#  php >=8.4.1. PHP 8.4 satisfies BOTH (8.2/8.3 broke on these).
# ============================================================================
FROM php:8.4-cli

# --- System tools + required PHP extensions --------------------------------
# Added libpq-dev, pdo_pgsql, and pgsql for PostgreSQL support
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

# --- Composer (pinned 2.x, avoids PHP-version mismatch) ---------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- Node.js 22 + npm (required by Vite 8 / Svelte 5) ----------------------
ENV NODE_VERSION=22.14.0
RUN curl -fsSL "https://nodejs.org/dist/v${NODE_VERSION}/node-v${NODE_VERSION}-linux-x64.tar.xz" \
        -o /tmp/node.tar.xz \
    && tar -xJf /tmp/node.tar.xz -C /usr/local --strip-components=1 \
    && rm /tmp/node.tar.xz \
    && node --version \
    && npm --version

# --- Working directory ------------------------------------------------------
WORKDIR /var/www/html

# Copy only project files
COPY . .

# --- Install PHP dependencies (fresh Linux build) ---------------------------
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader

# --- App setup: .env + APP_KEY -----------------------------------------------
RUN cp .env.example .env \
    && php artisan key:generate \
    && (php artisan storage:link --force || true)

# --- Build the frontend ------------------------------------------------------
RUN npm ci --no-audit --no-fund \
    && npm run build

# --- Permissions (runtime user must be able to write) ------------------------
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# --- Runtime ----------------------------------------------------------------
ENV PORT=10000
EXPOSE 10000

USER www-data

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]