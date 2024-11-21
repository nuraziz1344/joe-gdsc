# Base image with PHP and Apache
FROM php:8.3-alpine

# Set working directory
WORKDIR /app

# Install system dependencies, including libsodium
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libzip-dev \
    unzip \
    libpng-dev \
    libonig-dev \
    libmcrypt-dev \
    libxml2-dev \
    libsqlite3-dev \
    libsodium-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd sodium \
    && docker-php-ext-enable sodium

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /app

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Publish Swagger configuration and generate documentation
RUN php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider" --force
RUN php artisan l5-swagger:generate

# Expose the web server port
EXPOSE 8000

# Start the Apache server with logs for debugging
CMD ["/usr/local/bin/php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
