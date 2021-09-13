#!/bin/sh
set -e

echo "set nginx root path and start nginx"
sed -i "/root.*html;/c\ root \/var\/www\/html\/web;" /etc/nginx/sites-enabled/default
nginx

echo "start socket server";
cd /var/www/html && /usr/local/bin/php /var/www/html/server.php start -d
php -a