FROM php:8.2-cli

# تثبيت الحزم وامتدادات PHP المطلوبة
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libzip-dev \
    libsqlite3-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_sqlite zip mbstring xml bcmath ctype fileinfo

# تثبيت Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www

COPY . .

# إنشاء ملف قاعدة البيانات
RUN mkdir -p database && touch database/database.sqlite

# تثبيت مكتبات PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts --ignore-platform-reqs

# تثبيت كافة مكتبات Node بما فيها أدوات البناء
RUN npm install --include=dev

# تجميع الواجهة مع رفع حد الذاكرة المسموح لـ Node
RUN NODE_OPTIONS="--max-old-space-size=2048" npm run build

EXPOSE 10000

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}