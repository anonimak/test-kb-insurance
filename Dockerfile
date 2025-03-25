FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    nodejs \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-scripts --no-autoloader --no-interaction

# Copy the rest of the application files
COPY . .

# Copy docker config files
COPY docker/config/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Run the environment setup script with default to development
ARG CI_ENVIRONMENT=production
ENV CI_ENVIRONMENT=${CI_ENVIRONMENT}

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# Install and build frontend
WORKDIR /var/www/html/frontend
RUN npm install && npm run build

WORKDIR /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/writable

# Configure Apache
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]