FROM php:7.4-apache

RUN apt update && apt upgrade -y
RUN apt install -y \
  default-mysql-client \
  zlib1g-dev \
  libpng-dev \
  libjpeg-dev \
  libfreetype-dev
RUN docker-php-ext-install mysqli && \
  docker-php-ext-enable mysqli && \
  docker-php-ext-configure gd --with-freetype --with-jpeg && \
  docker-php-ext-install gd
RUN apt clean

WORKDIR /var/www/html

COPY . .
COPY ./docker/php.ini-production /usr/local/etc/php/conf.d/php.ini

RUN  chown -R www-data:www-data /var/www/html/gui/templates_c