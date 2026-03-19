FROM php:8.2-fpm-bookworm AS app_base

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        ${PHPIZE_DEPS} \
    && docker-php-ext-install intl opcache zip \
    && pecl install redis \
    && docker-php-ext-enable redis opcache \
    && apt-get purge -y --auto-remove ${PHPIZE_DEPS} \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]

FROM app_base AS dev

ENV APP_ENV=dev
ENV APP_DEBUG=1

CMD ["php-fpm"]

FROM app_base AS prod

ENV APP_ENV=prod
ENV APP_DEBUG=0

COPY composer.json composer.lock symfony.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --ignore-platform-req=ext-redis \
    --no-scripts

COPY . .

RUN composer dump-autoload --classmap-authoritative --no-dev --no-interaction \
    && rm -rf var/cache/* var/log/* \
    && chown -R www-data:www-data var

CMD ["php-fpm"]
