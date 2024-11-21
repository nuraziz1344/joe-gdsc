# Base image with PHP and Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
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
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd sodium \
    && docker-php-ext-enable sodium

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Publish Swagger configuration and generate documentation
RUN php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider" --force
RUN php artisan l5-swagger:generate

# Expose the web server port
EXPOSE 80

# Start the Apache server
CMD ["apache2-foreground"]
