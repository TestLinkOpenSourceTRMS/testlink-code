FROM php:8.2-apache

RUN apt update && apt upgrade -y
RUN apt install -y \
  default-mysql-client \
  zlib1g-dev \
  libpng-dev \
  libjpeg-dev \
  libfreetype-dev \
  curl
RUN docker-php-ext-install mysqli && \
  docker-php-ext-enable mysqli && \
  docker-php-ext-configure gd --with-freetype --with-jpeg && \
  docker-php-ext-install gd
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
  apt install -y nodejs
RUN apt clean

WORKDIR /var/www/html

COPY . .
COPY ./docker/php.ini-production /usr/local/etc/php/conf.d/php.ini

RUN npm ci && npm run build

RUN  chown -R www-data:www-data /var/www/html/gui/templates_c
