#!/bin/sh

if [ "$(id -u)" != "0" ]; then
   echo "This script must be run as root. For example sudo ./$(basename "$0")" 1>&2
   exit 1

fi

echo "Updating Supla-Scripts. Please wait..."

get_latest_release_supla_scripts() {
  curl --silent "https://api.github.com/repos/fracz/supla-scripts/releases/latest" |
    grep '"tag_name":' |
    sed -E 's/^.*\"v//' |
    sed -E 's/\".*$//'
}

SUPLASCRIPTS=`get_latest_release_supla_scripts`

wget https://github.com/fracz/supla-scripts/releases/download/v$SUPLASCRIPTS/supla-scripts-$SUPLASCRIPTS.tar.gz

tar -zxf supla-scripts-$SUPLASCRIPTS.tar.gz -C /var/www/supla-scripts

php /var/www/supla-scripts/supla-scripts init

chown -R www-data:www-data /var/www/supla-scripts

rm -fr supla-scripts-$SUPLASCRIPTS.tar.gz

echo "FINISH!"
