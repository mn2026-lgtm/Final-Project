FROM php:8.2-apache

RUN mkdir -p /var/lib/php/sessions \
    && chown -R www-data:www-data /var/lib/php/sessions \
    && chmod -R 733 /var/lib/php/sessions

RUN { \
    echo 'session.save_path = "/var/lib/php/sessions"'; \
    echo 'session.gc_probability = 1'; \
    echo 'session.gc_divisor = 100'; \
    } > /usr/local/etc/php/conf.d/session.ini

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
