FROM composer:2.2 AS composer

FROM php:8.3-cli AS backend
RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev unzip \
    && docker-php-ext-install mbstring pdo_mysql \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY . /var/app
WORKDIR /var/app/backend
RUN composer install --optimize-autoloader --no-interaction --prefer-dist --no-progress --no-dev \
    && composer check-platform-reqs --no-dev

FROM node:14.21.2 AS frontend
WORKDIR /var/app/frontend
COPY frontend/package.json frontend/package-lock.json frontend/bower.json ./
RUN npm ci && npm run bower
COPY --from=backend /var/app /var/app
RUN npm run dist
WORKDIR /var/app
RUN npm ci --ignore-scripts && node scripts/version-dump.js && node scripts/release.js && ls -ahl

FROM scratch
COPY --from=frontend /var/app/*.tar.gz .
