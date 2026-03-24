FROM php:8.2-apache

RUN apt update && apt upgrade -y && \
  apt install -y \
  default-mysql-client \
  zlib1g-dev \
  libpng-dev \
  libjpeg-dev \
  libfreetype-dev \
  curl && \
  docker-php-ext-install mysqli && \
  docker-php-ext-enable mysqli && \
  docker-php-ext-configure gd --with-freetype --with-jpeg && \
  docker-php-ext-install gd && \
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
  apt install -y nodejs && \
  apt clean && \
  rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY package.json package-lock.json ./
RUN npm ci
COPY . .
COPY ./docker/php.ini-production /usr/local/etc/php/conf.d/php.ini
RUN npm run build

RUN  chown -R www-data:www-data /var/www/html/gui/templates_c
