# PrettyBlocks compatibility stacks

This Compose project runs the current PrettyBlocks checkout against two isolated
shops:

| Service | PrestaShop | PHP | URL |
| --- | --- | --- | --- |
| `ps82` | 8.2.7 | 8.1 | <http://localhost:8082> |
| `ps91` | 9.1.4 | 8.5 | <http://localhost:8091> |

Both shops use MariaDB 10.11. The repository is bind-mounted into each shop,
while each stack keeps its own Composer `vendor` volume. A one-shot Node
container builds the Vite assets before the shops start, then the image init
hook installs PrettyBlocks automatically on the first boot.

## Start

```bash
docker compose -f docker/compose.yml up --build -d --wait
docker/scripts/smoke-test.sh
```

The back office is available at `/admin-dev` on both ports:

- login: `demo@prestashop.com`
- password: `prestashop_demo`

Use `PS82_PORT` and `PS91_PORT` to override the host ports if needed. The PS 9.1
stack uses its recommended PHP 8.5 runtime by default. To compare both
PrestaShop cores on PHP 8.1 instead:

```bash
PS91_IMAGE=prestashop/prestashop:9.1.4-8.1 \
  docker compose -f docker/compose.yml up --build -d --wait
```

After changing files in `_dev`, rebuild the editor bundle with:

```bash
docker compose -f docker/compose.yml run --rm assets
```

## Logs and shell access

```bash
docker compose -f docker/compose.yml logs -f ps82 ps91
docker compose -f docker/compose.yml exec ps82 bash
docker compose -f docker/compose.yml exec ps91 bash
```

## Reset everything

This removes both shops and databases, including all test content:

```bash
docker compose -f docker/compose.yml down -v
```

## Build and test the release ZIP

The release builder runs Composer and Vite in Docker, creates an installable ZIP
with a top-level `prettyblocks/` directory, and validates its contents:

```bash
scripts/build-release.sh
```

The installable `prettyblocks-{version}.zip` archive is written to `dist/`. Its
top-level directory is always `prettyblocks/`, without the version suffix. To
prove that the ZIP is self-contained, run it against two fresh, temporary
shops. This test disables both the source asset build and the runtime Composer
install, so only files present in the archive can be used:

```bash
scripts/test-release.sh
```

Pass an existing archive to test the exact package without rebuilding it:

```bash
scripts/test-release.sh dist/prettyblocks-3.2.2.zip
```

The release test validates and extracts the ZIP with PrestaShop's native ZIP
handler, then installs that extracted artifact with the PrestaShop CLI. It uses
ports `8182` and `8191` by default and deletes its shops, databases, and
extracted module when it finishes. Override the ports with `PS82_RELEASE_PORT`
and `PS91_RELEASE_PORT` if needed.
