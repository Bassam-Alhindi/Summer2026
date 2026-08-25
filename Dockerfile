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

# حذف أي مجلدات منسوخة من الويندوز لضمان تثبيت حزم متوافقة مع Linux
RUN rm -rf node_modules vendor

# إنشاء ملف قاعدة البيانات
RUN mkdir -p database && touch database/database.sqlite

# تثبيت مكتبات PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts --ignore-platform-reqs

# ++++ التعديل السحري لحل مشكلتك ++++
# إنشاء ملف بيئة مؤقت وتوليد مفتاح تشفير ليتمكن Laravel من تشغيل الأوامر أثناء البناء
RUN cp .env.example .env && php artisan key:generate

# تثبيت مكتبات Node
RUN npm install

# تجميع ملفات الواجهة (لن يفشل الآن لأن Laravel جاهز)
RUN npm run build

EXPOSE 10000

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}