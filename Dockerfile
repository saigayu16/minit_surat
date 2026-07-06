FROM php:8.0-apache

# Update and install system dependencies for mysqli
RUN apt-get update && apt-get install -y \
    libmariadb-dev \
    && docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

COPY . /var/www/html/