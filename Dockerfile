FROM php:8.2-apache

# Install sambungan yang diperlukan
RUN apt-get update && apt-get install -y git zip unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Salin fail kod
COPY . /var/www/html/

# Jalankan install library
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader

# Pastikan Apache boleh baca fail
RUN chown -R www-data:www-data /var/www/html
