FROM composer:1.8.6 AS backend
COPY . /var/app
WORKDIR /var/app/backend
RUN composer install --optimize-autoloader --ignore-platform-reqs --no-dev

FROM node:14.21.2 AS frontend
COPY --from=backend /var/app /var/app
WORKDIR /var/app/frontend
RUN mkdir bower_components && npm ci && npm run dist
WORKDIR /var/app
RUN npm ci && node scripts/version-dump.js && node scripts/release.js && ls -ahl

FROM scratch
COPY --from=frontend /var/app/*.tar.gz .
