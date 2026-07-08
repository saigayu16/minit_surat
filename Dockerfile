FROM php:8.2-apache

# Install sambungan PHP yang diperlukan
RUN apt-get update && apt-get install -y git zip unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Salin fail konfigurasi dahulu untuk caching yang lebih baik
COPY composer.json composer.lock* /var/www/html/

# Masuk ke direktori dan install library
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# Salin semua fail kod anda
COPY . .

# Set hak akses
RUN chown -R www-data:www-data /var/www/html
