FROM php:8.3-fpm-alpine AS php

RUN docker-php-ext-install pdo_mysql

RUN docker-php-ext-install redis

RUN install -o www-data -g www-data -d /var/www/upload/image/

COPY ./php.ini ${PHP_INI_DIR}/php.ini
