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

# Run Apache on an unprivileged port so the container can drop to www-data.
RUN sed -ri 's!^Listen 80$!Listen 8080!' /etc/apache2/ports.conf \
    && sed -ri 's!\${APACHE_LOG_DIR}/error\.log!/proc/self/fd/2!g; s!\${APACHE_LOG_DIR}/access\.log!/proc/self/fd/1!g' \
        /etc/apache2/apache2.conf /etc/apache2/sites-available/*.conf 2>/dev/null || true \
    && sed -ri 's!^(\s*PidFile\s+).*$!\1/tmp/apache2.pid!' /etc/apache2/apache2.conf || true \
    && echo 'PidFile /tmp/apache2.pid' >> /etc/apache2/apache2.conf \
    && chown -R www-data:www-data /var/log/apache2 /var/run/apache2 /var/lock/apache2 /var/www/html

COPY --chown=www-data:www-data --chmod=0444 docker/php/php.ini /usr/local/etc/php/conf.d/zz-warcry.ini
COPY --chown=www-data:www-data --chmod=0444 docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY --chown=www-data:www-data --chmod=0555 docker/entrypoint.sh /usr/local/bin/warcry-entrypoint.sh

WORKDIR /var/www/html

EXPOSE 8080

USER www-data

ENTRYPOINT ["/usr/local/bin/warcry-entrypoint.sh"]
CMD ["apache2-foreground"]
