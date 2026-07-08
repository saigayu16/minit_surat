FROM php:8.1-apache

# 1. Enable Apache mod_rewrite
RUN a2enmod rewrite

# 2. Update and install required system libraries
RUN apt-get update && apt-get install -y \
    libmariadb-dev-compat \
    libmariadb-dev \
    && rm -rf /var/lib/apt/lists/*

# 3. Install and configure mysqli specifically
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# 4. Copy your project files
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
