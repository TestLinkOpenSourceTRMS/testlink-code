FROM pensiero/apache-php-mysql:latest

RUN apt update -q && apt install -yqq --force-yes \
    mysql-server

WORKDIR /var/www/public
COPY . ./

# Start mysql
CMD ["/etc/init.d/mysql", "start"]
