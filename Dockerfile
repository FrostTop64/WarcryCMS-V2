FROM php:8.2-apache

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libwebp-dev \
        unzip \
        git \
        ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        gd \
        mbstring \
        zip \
        intl \
        exif \
        bcmath \
    && pecl install apcu \
    && docker-php-ext-enable apcu opcache \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-warcry.ini
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/warcry-entrypoint.sh
RUN chmod +x /usr/local/bin/warcry-entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/warcry-entrypoint.sh"]
CMD ["apache2-foreground"]
