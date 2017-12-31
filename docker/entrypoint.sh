#!/bin/sh
set -e

/usr/local/bin/php /var/www/supla-scripts init --no-interaction

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- apache2-foreground "$@"
fi

exec "$@"
