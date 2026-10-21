FROM composer:2.2 AS composer

# PHP 7.4 is only for characterizing the original application before migration.
FROM php:7.4-cli-bullseye AS runtime

COPY docker/tests/sources.list /etc/apt/sources.list

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libcurl4-openssl-dev \
        libonig-dev \
        unzip \
    && docker-php-ext-install curl mbstring pdo_mysql \
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
