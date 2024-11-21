# Base image with PHP on Alpine Linux
FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install necessary system dependencies, including libsodium-dev
RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev \
    unzip \
    libsodium-dev \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    bcmath \
    gd \
    sodium

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html

# Set permissions for storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Install PHP dependencies and optimize the autoloader
RUN composer install --no-dev --optimize-autoloader

# Publish Swagger configuration and generate Swagger docs
RUN php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider" --force
RUN php artisan l5-swagger:generate

# Add script to dynamically set PHP-FPM port
RUN echo "env[PORT] = \$PORT" >> /usr/local/etc/php-fpm.d/www.conf
RUN echo "listen = 0.0.0.0:\$PORT" >> /usr/local/etc/php-fpm.d/www.conf

# Expose a placeholder port (Heroku sets the actual port)
EXPOSE 9000

# Add a startup script to modify the PHP-FPM configuration dynamically
COPY ./docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Use the custom entrypoint script
ENTRYPOINT ["docker-entrypoint.sh"]
