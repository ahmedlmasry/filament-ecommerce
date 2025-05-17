# استخدم صورة PHP 8.2 مع Apache
FROM php:8.2-apache

# تثبيت الإضافات المطلوبة
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl \
    && docker-php-ext-install zip pdo pdo_mysql

# تمكين mod_rewrite في Apache
RUN a2enmod rewrite

# نسخ composer من صورة composer الرسمية
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ضبط مسار Laravel كمجلد افتراضي في Apache
WORKDIR /var/www/html

# نسخ كل ملفات المشروع إلى المجلد
COPY . /var/www/html

# إعداد الصلاحيات
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# تثبيت التبعيات
RUN composer install --no-dev --optimize-autoloader

# نسخ ملف env لو مش موجود
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# توليد مفتاح التطبيق
RUN php artisan key:generate

# فتح البورت
EXPOSE 80
