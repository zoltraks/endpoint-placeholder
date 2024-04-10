FROM php:fpm-alpine

ARG UID=1000
ARG GID=1000

ADD php/php.ini /usr/local/etc/php/

RUN mkdir /var/log/php
RUN chgrp $GID /var/log/php
RUN chmod g+w /var/log/php
RUN mkdir /home/upload
RUN chgrp $GID /home/upload
RUN chmod g+w /home/upload

USER $UID:$GID

CMD ["php-fpm", "-R"]
