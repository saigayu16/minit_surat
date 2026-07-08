FROM php:8.1-apache

# Install sistem yang diperlukan
RUN apt-get update && apt-get install -y git zip unzip
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy kod projek
COPY . /var/www/html/

# Jalankan composer install di dalam container
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader

# Set permission
RUN chown -R www-data:www-data /var/www/html
