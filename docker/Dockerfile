FROM php:7.0.27-apache

WORKDIR /var/www

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
      libicu-dev \
      libpq-dev \
      ca-certificates \
      ssl-cert \
      libcurl4-gnutls-dev \
      git \
      unzip \
      mysql-client \
      supervisor \
      cron \
      wget \
    && update-ca-certificates \
    && docker-php-ext-install \
      pdo_mysql \
      opcache \
      curl \
      zip \
    && apt-get autoremove \
    && rm -r /var/lib/apt/lists/*

COPY utils/crontab /etc/cron.d/suplascripts

RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=2'; \
		echo 'opcache.fast_shutdown=1'; \
		echo 'opcache.enable_cli=1'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini \
    && a2enmod rewrite expires deflate ssl cgi alias env && a2ensite default-ssl \
    && chmod 0644 /etc/cron.d/suplascripts


ENV SUPLA_SCRIPTS_VERSION=3.0.0-rc.12
#COPY supla-scripts-$SUPLA_SCRIPTS_VERSION.tar.gz ./
RUN wget -nc https://github.com/fracz/supla-scripts/releases/download/v${SUPLA_SCRIPTS_VERSION}/supla-scripts-${SUPLA_SCRIPTS_VERSION}.tar.gz \
    && tar -xzf supla-scripts-${SUPLA_SCRIPTS_VERSION}.tar.gz -C . \
    && rm -f supla-scripts-${SUPLA_SCRIPTS_VERSION}.tar.gz \
    && chown -hR www-data:www-data .

COPY utils/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY utils/entrypoint.sh /usr/local/bin/docker-php-entrypoint

CMD ["/usr/bin/supervisord", "--nodaemon", "--configuration", "/etc/supervisor/conf.d/supervisord.conf"]
