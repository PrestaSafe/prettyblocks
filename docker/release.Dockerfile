# syntax=docker/dockerfile:1.7

FROM node:20-alpine AS assets

WORKDIR /src
COPY _dev/package.json _dev/yarn.lock ./_dev/
RUN --mount=type=cache,target=/usr/local/share/.cache/yarn \
    cd _dev \
    && corepack yarn install --frozen-lockfile --non-interactive

COPY _dev ./_dev
RUN cd _dev && corepack yarn build


FROM composer:2 AS production-dependencies

WORKDIR /src
COPY . .
RUN --mount=type=cache,target=/tmp/cache \
    COMPOSER_CACHE_DIR=/tmp/cache composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --optimize-autoloader \
        --classmap-authoritative


FROM composer:2 AS release-tools

WORKDIR /src
COPY . .
RUN --mount=type=cache,target=/tmp/cache \
    COMPOSER_CACHE_DIR=/tmp/cache composer install \
        --no-interaction \
        --no-progress \
        --prefer-dist


FROM composer:2 AS package

ARG MODULE_VERSION

RUN apk add --no-cache zip

COPY --from=production-dependencies /src /stage/prettyblocks
COPY --from=assets /src/build /stage/prettyblocks/build
COPY --from=release-tools /src/vendor /tools/vendor

RUN set -eux; \
    test -n "$MODULE_VERSION"; \
    rm -rf \
        /stage/prettyblocks/.git \
        /stage/prettyblocks/.github \
        /stage/prettyblocks/_dev \
        /stage/prettyblocks/docker \
        /stage/prettyblocks/dist \
        /stage/prettyblocks/node_modules \
        /stage/prettyblocks/scripts \
        /stage/prettyblocks/work; \
    rm -f \
        /stage/prettyblocks/.dockerignore \
        /stage/prettyblocks/.env \
        /stage/prettyblocks/.env.local \
        /stage/prettyblocks/.env.development.local \
        /stage/prettyblocks/.env.test.local \
        /stage/prettyblocks/.env.production.local \
        /stage/prettyblocks/.gitignore \
        /stage/prettyblocks/.php-cs-fixer.cache \
        /stage/prettyblocks/.php-cs-fixer.dist.php \
        /stage/prettyblocks/config.xml \
        /stage/prettyblocks/config_*.xml; \
    find /stage/prettyblocks/views/images \
        -mindepth 1 \
        -maxdepth 1 \
        -type f \
        ! -name favicon.ico \
        ! -name index.php \
        -delete; \
    /tools/vendor/bin/autoindex \
        prestashop:add:index \
        --exclude=vendor \
        --no-interaction \
        /stage/prettyblocks; \
    mkdir -p /out; \
    cd /stage; \
    zip -q -r "/out/prettyblocks-${MODULE_VERSION}.zip" prettyblocks


FROM scratch AS release

ARG MODULE_VERSION
COPY --from=package /out/ /
