FROM php:8.1-apache

# 1. Enable Apache mod_rewrite
RUN a2enmod rewrite

# 2. Update and install required system libraries untuk PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# 3. Install dan enable pdo_pgsql (untuk PostgreSQL/Neon)
RUN docker-php-ext-install pdo pdo_pgsql

# 4. Copy your project files
COPY . /var/www/html/
