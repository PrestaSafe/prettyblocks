#!/usr/bin/env bash
set -Eeuo pipefail

script_dir=$(CDPATH='' cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
project_dir=$(dirname "$script_dir")
compose_files=(
  -f "$project_dir/docker/compose.yml"
  -f "$project_dir/docker/compose.release-test.yml"
)

version=$(awk -F"'" '/\$this->version[[:space:]]*=/{print $2; exit}' "$project_dir/prettyblocks.php")
expected_archive_name="prettyblocks-${version}.zip"

if [ "$#" -gt 1 ]; then
  echo "Usage: $0 [archive]" >&2
  exit 1
fi

if [ "$#" -eq 1 ]; then
  archive_path=$1
  if [ ! -f "$archive_path" ]; then
    echo "Release archive not found: $archive_path" >&2
    exit 1
  fi

  archive_dir=$(CDPATH='' cd -- "$(dirname -- "$archive_path")" && pwd)
  archive_path="$archive_dir/$(basename -- "$archive_path")"
else
  "$script_dir/build-release.sh"
  archive_path="$project_dir/dist/$expected_archive_name"
fi

if [ "$(basename -- "$archive_path")" != "$expected_archive_name" ]; then
  echo "Expected release archive name: $expected_archive_name" >&2
  exit 1
fi

export PRETTYBLOCKS_RELEASE_ARCHIVE_PATH="$archive_path"
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
    $manifestPath = "modules/prettyblocks/build/manifest.json";
    $manifest = json_decode(
        file_get_contents($manifestPath),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $entry = $manifest["index.html"] ?? null;
    if (!is_array($entry) || true !== ($entry["isEntry"] ?? false)) {
        throw new RuntimeException("The Vite manifest has no index.html entry.");
    }

    $javascript = $entry["file"] ?? null;
    $stylesheets = $entry["css"] ?? null;
    if (!is_string($javascript) || !str_ends_with($javascript, ".js")) {
        throw new RuntimeException("The Vite manifest has no entry JavaScript.");
    }
    if (!is_array($stylesheets) || [] === $stylesheets) {
        throw new RuntimeException("The Vite manifest has no entry stylesheet.");
    }

    foreach (array_merge([$javascript], $stylesheets) as $asset) {
        if (
            !is_string($asset)
            || str_starts_with($asset, "/")
            || str_contains($asset, "..")
            || str_contains($asset, "\\")
        ) {
            throw new RuntimeException("The Vite manifest contains an unsafe asset path.");
        }

        echo $asset, "\n";
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
