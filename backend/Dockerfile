FROM php:7.4-apache
# FROM php:7.4-fpm-alpine

LABEL maintainer="Your Name. <your@neme.ca>" \
    version="1.0"

COPY --chown=www-data:www-data . /var/www/html/app

COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html/app

RUN apt-get update && apt-get install -y \
    systemd \
    zip \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_mysql zip \
    && docker-php-ext-install opcache \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

COPY --from=composer:1.10.17 /usr/bin/composer /usr/local/bin/composer

#RUN /usr/local/bin/composer update
RUN COMPOSER_MEMORY_LIMIT=-1 $(which composer) update
RUN npm install
RUN cp /var/www/html/app/.env.example /var/www/html/app/.env \
    # && echo APP_KEY=base64:/vrrWGUT7LFTeZ7y8CuoiKF5ZTu+BIuq5QJpOExkoYU= > /var/www/html/app/.env \
    && php artisan key:generate \
    && php artisan config:clear \
    && composer dump-autoload \
    && php artisan config:cache \
    && npm rebuild node-sass \
    && npm run dev

RUN /usr/sbin/a2enmod rewrite \
    && service apache2 restart
