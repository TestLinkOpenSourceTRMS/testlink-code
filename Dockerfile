FROM pensiero/apache-php-mysql:latest

RUN apt update -q && apt install -yqq --force-yes \
    mysql-server

# Start mysql
RUN /etc/init.d/mysql 'start'

WORKDIR /var/www/public
COPY . ./

RUN mkdir /var/testlink/logs -p &&\
    mkdir /var/testlink/upload_area -p &&\
    chmod a+rw /var/www/public/gui/templates_c &&\
    chmod a+rw /var/testlink -R
