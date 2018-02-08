#!/bin/sh

##
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#
# @author Michał Wieczorek @michael
##

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

