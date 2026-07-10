#!/bin/sh
set -eu

module_dir=/var/www/html/modules/prettyblocks
install_marker=/var/www/html/var/.prettyblocks-installed

if [ -n "${PRETTYBLOCKS_RELEASE_ARCHIVE:-}" ]; then
  if [ ! -f "$install_marker" ]; then
    printf '\n* Installing the packaged PrettyBlocks release...\n'
    runuser -g www-data -u www-data -- \
      php -d memory_limit=-1 \
      /usr/local/lib/prettyblocks/install-release.php \
      "$PRETTYBLOCKS_RELEASE_ARCHIVE"

    cd /var/www/html
    runuser -g www-data -u www-data -- \
      php -d memory_limit=-1 \
      bin/console prestashop:module install prettyblocks --no-interaction

    # shellcheck disable=SC2016
    runuser -g www-data -u www-data -- php -r '
      require "/var/www/html/config/config.inc.php";
      $module = Module::getInstanceByName("prettyblocks");
      if (!$module || !Module::isInstalled("prettyblocks") || !$module->active) {
          fwrite(STDERR, "PrettyBlocks was not installed and enabled from the ZIP.\n");
          exit(1);
      }
      printf(
          "Installed PrettyBlocks %s from ZIP on PrestaShop %s.\n",
          $module->version,
          _PS_VERSION_
      );
    '

    touch "$install_marker"
    chown www-data:www-data "$install_marker"
  else
    printf '\n* Packaged PrettyBlocks release is already installed.\n'
  fi

  exit 0
fi

printf '\n* Preparing PrettyBlocks dependencies...\n'
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
  printf '\n* Installing PrettyBlocks...\n'
  cd /var/www/html
  runuser -g www-data -u www-data -- \
    php -d memory_limit=-1 bin/console prestashop:module install prettyblocks --no-interaction

  touch "$install_marker"
  chown www-data:www-data "$install_marker"
else
  printf '\n* PrettyBlocks is already installed.\n'
fi
