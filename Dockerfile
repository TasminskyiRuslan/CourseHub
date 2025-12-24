FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev curl supervisor \
    && docker-php-ext-install pdo_mysql mbstring zip pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

COPY . .

RUN composer dump-autoload --optimize

COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN mkdir -p /var/log/supervisor

CMD ["/usr/bin/supervisord"]
