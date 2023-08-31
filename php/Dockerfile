FROM php:fpm-alpine

ADD php/php.ini /usr/local/etc/php/

RUN mkdir /var/log/php
RUN chgrp www-data /var/log/php
RUN chmod g+w /var/log/php
