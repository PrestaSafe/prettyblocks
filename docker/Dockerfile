ARG PRESTASHOP_IMAGE=prestashop/prestashop:8.2.7-8.1-apache

FROM composer:2 AS composer

FROM ${PRESTASHOP_IMAGE}

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY scripts/install-prettyblocks.sh /tmp/init-scripts/90-install-prettyblocks.sh

RUN chmod +x /tmp/init-scripts/90-install-prettyblocks.sh
