FROM php:8.0-apache

# 1. Enable Apache mod_rewrite
RUN a2enmod rewrite

# 2. Update and install required system libraries
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libmariadb-dev-compat \
    libmariadb-dev \
    && rm -rf /var/lib/apt/lists/*

# 3. Install and configure mysqli specifically
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# 4. Install Composer (Perlu untuk pasang library)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Copy your project files
COPY . /var/www/html/

# 6. Jalankan composer install (Kunci untuk selesaikan ralat "Class not found")
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader

# 7. Set permission
RUN chown -R www-data:www-data /var/www/html
