# ==================================================
# Stage 1: Composer build
# ==================================================
FROM composer:2 AS builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs

COPY . .

RUN composer dump-autoload --optimize


# ==================================================
# Stage 2: Apache runtime
# ==================================================
FROM php:8.3-apache

WORKDIR /var/www

# System dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libpq-dev \
    unzip \
    curl \
    git \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql zip gd

# Configure Apache
COPY apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Copy application
COPY --from=builder /app /var/www

# Create missing storage directories
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache

# Permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R ug+rwx storage bootstrap/cache

# port 
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]