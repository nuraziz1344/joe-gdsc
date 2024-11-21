# Base image with PHP and Apache
FROM php:8.3-alpine

# Set working directory
WORKDIR /app

RUN apk update && apk add --no-cache \
    git \
    curl \
    libzip-dev \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    oniguruma-dev \
    libmcrypt-dev \
    libxml2-dev \
    sqlite-dev \
    libsodium-dev \
    && apk add --no-cache --virtual .build-deps gcc g++ make autoconf libc-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd sodium \
    && docker-php-ext-enable sodium \
    && apk del .build-deps
    
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
