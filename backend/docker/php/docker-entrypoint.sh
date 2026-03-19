#!/bin/sh
set -eu

cd /var/www/html

mkdir -p var/cache var/log var/share
chown -R www-data:www-data var
chmod -R ug+rwX var

git config --global --add safe.directory /var/www/html || true

if [ "${APP_ENV:-prod}" = "dev" ] && [ "${SKIP_COMPOSER_INSTALL:-0}" != "1" ] && [ ! -f vendor/autoload.php ]; then
    composer install --prefer-dist --no-interaction
fi

if [ "${APP_ENV:-prod}" = "prod" ] && [ -f bin/console ]; then
    php bin/console cache:warmup --env=prod --no-interaction
fi

exec "$@"
