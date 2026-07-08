FROM php:8.2-apache

# 1. Install sistem yang diperlukan & extension PHP
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip

# 2. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Tetapkan direktori kerja
WORKDIR /var/www/html

# 4. Salin composer.json dahulu (untuk optimasi cache)
COPY composer.json ./

# 5. Pasang library (tanpa dev untuk jimat masa)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 6. Salin semua fail kod anda
COPY . .

# 7. Tetapkan kebenaran folder
RUN chown -R www-data:www-data /var/www/html
