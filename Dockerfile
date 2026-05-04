FROM php:8.2-fpm-alpine


RUN apk add --no-cache \
    libzip-dev \
    zip \
    unzip \
    curl \
    oniguruma-dev \
    libxml2-dev


RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    mbstring \
    xml \
    bcmath


RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin \
    --filename=composer


WORKDIR /var/www/html
