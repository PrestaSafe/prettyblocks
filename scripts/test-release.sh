#!/usr/bin/env bash
set -Eeuo pipefail

script_dir=$(CDPATH='' cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
project_dir=$(dirname "$script_dir")
compose_files=(
  -f "$project_dir/docker/compose.yml"
  -f "$project_dir/docker/compose.release-test.yml"
)

"$script_dir/build-release.sh"

version=$(awk -F"'" '/\$this->version[[:space:]]*=/{print $2; exit}' "$project_dir/prettyblocks.php")
export PRETTYBLOCKS_RELEASE_ARCHIVE_PATH="$project_dir/dist/prettyblocks-${version}.zip"
export COMPOSE_PROJECT_NAME="prettyblocks-release-test-${$}"
export PS82_PORT=${PS82_RELEASE_PORT:-8182}
export PS91_PORT=${PS91_RELEASE_PORT:-8191}

cleanup() {
  status=$?
  if [ "$status" -ne 0 ]; then
    echo "Release test failed; PrestaShop logs follow:" >&2
    docker compose "${compose_files[@]}" logs --no-color ps82 ps91 >&2 || true
  fi
  docker compose "${compose_files[@]}" down -v --remove-orphans >/dev/null 2>&1 || true
  return "$status"
}
trap cleanup EXIT INT TERM

echo "Installing the packaged ZIP on clean PrestaShop 8.2 and 9.1 stacks..."
docker compose "${compose_files[@]}" up \
  --build \
  --detach \
  --wait \
  --wait-timeout "${PRETTYBLOCKS_WAIT_TIMEOUT:-600}"

"$project_dir/docker/scripts/smoke-test.sh"

for service in ps82 ps91; do
  docker compose "${compose_files[@]}" exec -T "$service" php -r '
    require "/var/www/html/modules/prettyblocks/vendor/autoload.php";

    if (!class_exists("ScssPhp\\ScssPhp\\Compiler")) {
        fwrite(STDERR, "The production SCSS dependency is missing.\n");
        exit(1);
    }

    if (Composer\InstalledVersions::isInstalled("prestashop/php-dev-tools")) {
        fwrite(STDERR, "A development Composer dependency leaked into the release.\n");
        exit(1);
    }
  '

  docker compose "${compose_files[@]}" exec -T "$service" sh -lc \
    'find modules/prettyblocks -type f -name "*.php" -print0 | xargs -0 -n 1 php -l >/dev/null'
done

for service_and_port in "ps82:$PS82_PORT" "ps91:$PS91_PORT"; do
  service=${service_and_port%%:*}
  port=${service_and_port##*:}

  asset_paths=$(docker compose "${compose_files[@]}" exec -T "$service" php -r '
    $manifest = json_decode(
        file_get_contents("modules/prettyblocks/build/.vite/manifest.json"),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    foreach ($manifest as $entry) {
        echo $entry["file"], "\n";
        foreach ($entry["css"] ?? [] as $css) {
            echo $css, "\n";
        }
    }
  ' | tr -d '\r')

  while IFS= read -r asset_path; do
    [ -n "$asset_path" ] || continue
    curl -fsS "http://localhost:${port}/modules/prettyblocks/build/${asset_path}" >/dev/null
  done <<< "$asset_paths"

  curl -fsS "http://localhost:${port}/modules/prettyblocks/views/js/build/build.js" >/dev/null
  curl -fsS "http://localhost:${port}/modules/prettyblocks/views/css/dist/main.css" >/dev/null
done

echo "Packaged release passed on both clean PrestaShop stacks."
