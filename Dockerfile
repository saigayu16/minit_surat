FROM php:8.0-apache

# This line installs the mysqli extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

COPY . /var/www/html/