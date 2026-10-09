# PHP 5.6 is the newest release that still has each(), ereg() and the
# mysql_* functions. PHP 7 deleted ereg and mysql_*, PHP 8 deleted each(),
# and 5.4 had already removed register_globals -- which lib.inc puts back
# by hand. This is as close to a 1998 runtime as anything that still builds.
FROM --platform=linux/amd64 php:5.6-apache

RUN docker-php-ext-install mysql

COPY php.ini           /usr/local/etc/php/conf.d/zz-1998.ini
COPY apache-php3.conf  /etc/apache2/conf-enabled/php3.conf
COPY www/              /var/www/html/

EXPOSE 80
