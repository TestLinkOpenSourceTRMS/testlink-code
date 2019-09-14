FROM pensiero/apache-php-mysql:latest

RUN apt update -q && apt install -yqq --force-yes \
    mysql-server

# Start mysql
RUN /etc/init.d/mysql 'start'

WORKDIR /var/www/public
COPY . ./
