#!/usr/bin/env bash
set -Eeuo pipefail

script_dir=$(CDPATH='' cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
project_dir=$(dirname "$script_dir")
output_dir=${1:-"$project_dir/dist"}

version=$(awk -F"'" '/\$this->version[[:space:]]*=/{print $2; exit}' "$project_dir/prettyblocks.php")
if [[ ! $version =~ ^[0-9A-Za-z][0-9A-Za-z.+-]*$ ]]; then
  echo "Unable to read a valid module version from prettyblocks.php." >&2
  exit 1
fi

for command_name in docker unzip; do
  if ! command -v "$command_name" >/dev/null 2>&1; then
    echo "Required command not found: $command_name" >&2
    exit 1
  fi
done

sha256_file() {
  if command -v sha256sum >/dev/null 2>&1; then
    sha256sum "$1" | awk '{print $1}'
  elif command -v shasum >/dev/null 2>&1; then
    shasum -a 256 "$1" | awk '{print $1}'
  else
    echo "A SHA-256 checksum command is required." >&2
    return 1
  fi
}

temporary_dir=$(mktemp -d "${TMPDIR:-/tmp}/prettyblocks-build.XXXXXX")
trap 'rm -rf "$temporary_dir"' EXIT

echo "Building PrettyBlocks ${version} release with Docker..."
docker build \
  --file "$project_dir/docker/release.Dockerfile" \
  --target release \
  --build-arg "MODULE_VERSION=$version" \
  --output "type=local,dest=$temporary_dir/output" \
  "$project_dir"

archive_name="prettyblocks-${version}.zip"
built_archive="$temporary_dir/output/$archive_name"
if [[ ! -f $built_archive ]]; then
  echo "Docker did not produce the expected archive: $archive_name" >&2
  exit 1
fi

listing="$temporary_dir/archive.list"
unzip -tq "$built_archive"
unzip -Z1 "$built_archive" > "$listing"

required_paths=(
  prettyblocks/prettyblocks.php
  prettyblocks/vendor/autoload.php
  prettyblocks/vendor/composer/installed.php
  prettyblocks/build/manifest.json
  prettyblocks/views/js/build/build.js
  prettyblocks/views/css/iframe.css
  prettyblocks/views/css/dist/main.css
  prettyblocks/index.php
)

for required_path in "${required_paths[@]}"; do
  if ! grep -Fxq "$required_path" "$listing"; then
    echo "Release archive is missing: $required_path" >&2
    exit 1
  fi
done

if grep -Evq '^prettyblocks(/|$)' "$listing"; then
  echo "Release archive contains entries outside the prettyblocks/ directory." >&2
  exit 1
fi

if grep -Eq '^prettyblocks/(\.git[^/]*|\.github|\.php-cs-fixer[^/]*|_dev|docker|dist|node_modules|scripts|views/css/_dev|work)(/|$)' "$listing"; then
  echo "Release archive contains development-only root files." >&2
  exit 1
fi

if grep -Eq '(^|/)node_modules(/|$)' "$listing"; then
  echo "Release archive contains Node dependencies." >&2
  exit 1
fi

if grep -Eq '(^|/)\.\.(/|$)' "$listing" || grep -Fq $'\\' "$listing"; then
  echo "Release archive contains an unsafe path." >&2
  exit 1
fi

if [[ -n $(sort "$listing" | uniq -d) ]]; then
  echo "Release archive contains duplicate paths." >&2
  exit 1
fi

if grep -Eq '^prettyblocks/\.env($|\.)' "$listing"; then
  echo "Release archive contains a local environment file." >&2
  exit 1
fi

if grep -Eq '^prettyblocks/vendor/(prestashop/(autoindex|php-dev-tools)|friendsofphp|squizlabs)(/|$)' "$listing"; then
  echo "Release archive contains development Composer dependencies." >&2
  exit 1
fi

if ! grep -Eq '^prettyblocks/build/assets/.+\.js$' "$listing"; then
  echo "Release archive does not contain the built editor JavaScript." >&2
  exit 1
fi

if ! grep -Eq '^prettyblocks/build/assets/.+\.css$' "$listing"; then
  echo "Release archive does not contain the built editor CSS." >&2
  exit 1
fi

mkdir -p "$output_dir"
cp "$built_archive" "$output_dir/$archive_name"
rm -f "$output_dir/prettyblocks.zip" "$output_dir/prettyblocks.zip.sha256"

versioned_checksum=$(sha256_file "$output_dir/$archive_name")

echo "Release ready: $output_dir/$archive_name"
echo "SHA-256:       $versioned_checksum"
