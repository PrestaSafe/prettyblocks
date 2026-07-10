#!/bin/sh
set -eu

module_dir=/var/www/html/modules/prettyblocks
install_marker=/var/www/html/var/.prettyblocks-installed

echo "\n* Preparing PrettyBlocks dependencies..."
export COMPOSER_ALLOW_SUPERUSER=1
export COMPOSER_HOME=/tmp/composer

cd "$module_dir"
composer install \
  --no-dev \
  --no-interaction \
  --no-progress \
  --optimize-autoloader \
  --prefer-dist

chown -R www-data:www-data "$module_dir/vendor"

if [ ! -f "$install_marker" ]; then
  echo "\n* Installing PrettyBlocks..."
  cd /var/www/html
  runuser -g www-data -u www-data -- \
    php -d memory_limit=-1 bin/console prestashop:module install prettyblocks --no-interaction

  touch "$install_marker"
  chown www-data:www-data "$install_marker"
else
  echo "\n* PrettyBlocks is already installed."
fi
