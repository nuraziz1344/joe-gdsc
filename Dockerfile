# Base image with PHP and Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

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

# Disable all MPMs and make sure no MPM modules are loaded
RUN a2dismod mpm_prefork mpm_worker mpm_event \
    && rm /etc/apache2/mods-enabled/mpm_* \
    && a2enmod mpm_event

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

# Start the Apache server with logs for debugging
CMD apache2ctl -M && apache2-foreground
