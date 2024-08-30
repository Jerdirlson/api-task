FROM php:8-fpm-alpine3.15 AS base

# Instalar las dependencias necesarias
RUN apk --update add \
    alpine-sdk \
    linux-headers \
    openssl-dev \
    php8-pear \
    php8-dev

# Instalar la extensión de Redis para PHP
RUN pecl install redis \
    && docker-php-ext-enable redis

# Instalar la extensión PDO MySQL
RUN docker-php-ext-install pdo_mysql

# Instalar y habilitar opcache
RUN docker-php-ext-install opcache \
    && echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.save_comments=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Limpiar la cache de APK
RUN rm -rf /var/cache/apk/*

EXPOSE 9000

FROM base AS development

ENV TZ ${TZ}

RUN pecl channel-update pecl.php.net

# Instalar xdebug para el entorno de desarrollo
RUN apk add --update --upgrade tzdata autoconf g++ make \
    && ln -s /usr/share/zoneinfo/$TZ /etc/localtime \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Copiar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
