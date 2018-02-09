#!/bin/sh

if [ "$(id -u)" != "0" ]; then
   echo "This script must be run as root. For example sudo ./$(basename "$0") 2.7.3" 1>&2
   exit 1

fi

if [ -z "$*" ]; then
   echo "Please add version to update. For example sudo ./$(basename "$0") 2.7.3" 1>&2
   exit 1
fi

wget https://github.com/fracz/supla-scripts/releases/download/v$1/supla-scripts-$1.tar.gz

tar -zxf supla-scripts-$1.tar.gz -C /var/www/supla-scripts

php /var/www/supla-scripts/supla-scripts init

chown -R www-data:www-data /var/www/supla-scripts

echo "FINISH!"

