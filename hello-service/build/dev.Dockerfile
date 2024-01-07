FROM php:8.3-fpm-alpine

# Install php-extension-installer for easy extension installment
# https://github.com/mlocati/docker-php-extension-installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions  \
    opentelemetry           \
    @composer               \
    xdebug

COPY build/conf.d /usr/local/etc/php/conf.d
