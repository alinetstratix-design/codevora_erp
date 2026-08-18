FROM php:8.0-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd pdo_pgsql

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy codebase
COPY . /var/www

# Install composer packages
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Configure Nginx
COPY ./nginx.conf /etc/nginx/sites-available/default

# Setup permissions and make entrypoint executable
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod +x /var/www/docker-entrypoint.sh

# Expose port and start via entrypoint
EXPOSE 80
ENTRYPOINT ["./docker-entrypoint.sh"]
