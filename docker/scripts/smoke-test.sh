#!/bin/sh
set -eu

script_dir=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
project_dir=$(dirname "$(dirname "$script_dir")")
compose_file="$project_dir/docker/compose.yml"

for service in ps82 ps91; do
  docker compose -f "$compose_file" exec -T "$service" php -r '
    require "/var/www/html/config/config.inc.php";
    $module = Module::getInstanceByName("prettyblocks");
    if (!$module || !Module::isInstalled("prettyblocks") || !$module->active) {
        fwrite(STDERR, "PrettyBlocks is not installed and enabled.\n");
        exit(1);
    }
    printf(
        "PrestaShop %s | PHP %s | PrettyBlocks %s\n",
        _PS_VERSION_,
        PHP_VERSION,
        $module->version
    );
  '

  docker compose -f "$compose_file" exec -T -e PS_DEV_MODE=0 "$service" \
    sh -lc 'php bin/console debug:router | grep -q prettyblocks'

  front_url=$(docker compose -f "$compose_file" exec -T "$service" php -r '
    require "/var/www/html/config/config.inc.php";
    $context = Context::getContext();
    echo (new Link())->getPageLink(
        "index",
        true,
        (int) $context->language->id,
        [],
        false,
        (int) $context->shop->id
    ), PHP_EOL;
  ' | tail -n 1 | tr -d '\r')

  front_page=$(curl -fsS "${front_url}?prettyblocks=1")
  if printf '%s' "$front_page" | grep -Fq '[Debug] This page has moved'; then
    echo "PrettyBlocks iframe URL redirects through a debug page: $front_url" >&2
    exit 1
  fi
done

curl -fsS "http://localhost:${PS82_PORT:-8082}/" >/dev/null
curl -fsS "http://localhost:${PS91_PORT:-8091}/" >/dev/null

echo "PrettyBlocks smoke tests passed on both PrestaShop versions."
