FROM php:7.2-fpm-alpine

ARG DOCKER_USER
ARG UID

RUN apk add --no-cache shadow \
    && groupadd -g ${UID} ${DOCKER_USER} \
    && useradd -r -m -s /usr/sbin/nologin -g ${DOCKER_USER} -u ${UID} ${DOCKER_USER} \
    && apk del shadow

RUN apk add --no-cache ${PHPIZE_DEPS} \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del ${PHPIZE_DEPS}

RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/bin --filename=composer \
    && rm -fv composer-setup.php

WORKDIR /var/www
