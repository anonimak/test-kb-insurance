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

# Copy and make entrypoint script executable
COPY docker/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Run the environment setup script with default to development
ARG CI_ENVIRONMENT=production
ENV CI_ENVIRONMENT=${CI_ENVIRONMENT}
ARG DB_HOST=mysql_db
ENV DB_HOST=${DB_HOST}
ARG DB_USER=root
ENV DB_USER=${DB_USER}
ARG DB_PASS=P4ij0royo
ENV DB_PASS=${DB_PASS}
ARG DB_NAME=kb_test_db
ENV DB_NAME=${DB_NAME}

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# Install and build frontend
WORKDIR /var/www/html/frontend
RUN npm install && npm run build

WORKDIR /var/www/html

# Set initial permissions (they'll also be set at runtime by entrypoint)
RUN mkdir -p /var/www/html/writable/cache \
    /var/www/html/writable/logs \
    /var/www/html/writable/session \
    /var/www/html/writable/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/writable

# Configure Apache
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80

# Set entrypoint that fixes permissions at startup
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Start Apache server
CMD ["apache2-foreground"]