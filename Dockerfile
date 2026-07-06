FROM php:8.0-apache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Install mysqli dependencies and extension
RUN apt-get update && apt-get install -y \
    libmariadb-dev \
    && docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

COPY . /var/www/html/