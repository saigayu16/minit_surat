FROM php:8.1-apache

# 1. Enable Apache mod_rewrite
RUN a2enmod rewrite

# 2. Update and install libraries
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libmariadb-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql

# 3. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Copy files
COPY . /var/www/html/

# 5. Install dependencies
RUN composer install --no-dev --optimize-autoloader --working-dir=/var/www/html

# 6. Permissions
RUN chown -R www-data:www-data /var/www/html
