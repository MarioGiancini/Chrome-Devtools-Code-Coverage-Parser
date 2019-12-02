FROM php:7.2-fpm

USER root
RUN apt-get update && \
    apt-get install -y libmemcached-dev zlib1g-dev && \
    pecl install \
        xdebug-2.6.0 \
        memcached-3.0.4

RUN docker-php-ext-enable xdebug memcached
