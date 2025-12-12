# php/Dockerfile

FROM php:8.3-fpm

# Install dependencies for PHP extensions
RUN apt-get update && apt-get install -y \
    libjpeg-dev \
    libpng-dev \
    libwebp-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    zip \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
        exif \
        pcntl \
        gd \
    && docker-php-ext-enable \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
        exif \
        pcntl \
        gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# COPY YOUR PROJECT FILES
COPY . /var/www/html

# PERMISSIONS
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

WORKDIR /var/www/html
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
