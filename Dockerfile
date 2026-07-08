FROM php:8.1-apache

# Install sambungan sistem
RUN apt-get update && apt-get install -y git zip unzip libmariadb-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Salin fail projek
COPY . /var/www/html/

# Install library (Ini langkah kritikal)
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader

# Set permission
RUN chown -R www-data:www-data /var/www/html
