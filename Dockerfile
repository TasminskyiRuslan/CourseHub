FROM php:8.4-fpm-alpine

ARG UID=1000
ARG GID=1000

RUN apk add --no-cache \
    git unzip curl shadow libzip-dev libpng-dev libwebp-dev \
    libjpeg-turbo-dev freetype-dev oniguruma-dev libxml2-dev icu-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring zip pcntl gd intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN usermod -u ${UID} www-data && groupmod -g ${GID} www-data

WORKDIR /var/www/html

USER www-data

CMD ["php-fpm"]
