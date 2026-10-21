ARG PHP_VERSION=8.2
FROM composer:2.2 AS composer

# Test the minimum supported PHP by default; override to check newer PHP 8.x.
FROM php:${PHP_VERSION}-cli-bookworm AS runtime

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libcurl4-openssl-dev \
        libonig-dev \
        libsqlite3-dev \
        unzip \
    && docker-php-ext-install curl mbstring pdo_mysql pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /app/backend

FROM runtime AS tests

COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-progress

COPY backend/ ./
COPY scripts/phpcs-rules.xml /app/scripts/phpcs-rules.xml

ENTRYPOINT ["php", "vendor/bin/phpunit"]

# Explicit opt-in target: never copies host configuration or encryption keys.
FROM tests AS database-tests
RUN touch /app/.database-test-container
ENTRYPOINT ["php", "vendor/bin/phpunit", "--configuration", "phpunit.database.xml"]

# Keep the default build/run network-free and independent of MySQL.
FROM tests AS default
