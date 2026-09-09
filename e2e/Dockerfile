# Image for the end-to-end sandbox app (e2e/app).
#
# The whole repository is bind-mounted at /app, so nothing is COPYed in here:
# the image only provides PHP, composer and the bits Laravel needs to run
# `artisan serve` against a SQLite database.
FROM php:8.3-cli-alpine

RUN set -eux; \
    apk add --no-cache git unzip curl sqlite sqlite-libs; \
    if ! php -m | grep -qi '^pdo_sqlite$'; then \
        apk add --no-cache --virtual .build-deps $PHPIZE_DEPS sqlite-dev; \
        docker-php-ext-install -j"$(nproc)" pdo_sqlite; \
        apk del .build-deps; \
    fi; \
    php -m | grep -qi '^pdo_sqlite$'

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_HOME=/tmp/composer \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1

WORKDIR /app/e2e/app
