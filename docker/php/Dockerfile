FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    librabbitmq-dev \
    libssh-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install AMQP extension
RUN pecl install amqp \
    && docker-php-ext-enable amqp

WORKDIR /var/www/html

# Copy existing application directory
COPY . /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html 