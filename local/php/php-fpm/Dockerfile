## Use the official PHP image as the base image
FROM php:8.3.16-fpm-alpine

## install composer
COPY --from=composer/composer /usr/bin/composer /usr/bin/composer

## install bash
RUN apk update && apk add bash

## install php extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql
#RUN pecl install redis
#RUN docker-php-ext-enable redis

RUN apk --no-cache add pcre-dev ${PHPIZE_DEPS} \
  && pecl install redis \
  && docker-php-ext-enable redis \
  && apk del pcre-dev ${PHPIZE_DEPS}

RUN apk add --no-cache freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev && \
    docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg && \
    docker-php-ext-install gd

# Install zip extension (required by phpoffice/phpspreadsheet)
RUN apk add --no-cache libzip-dev && \
    docker-php-ext-install zip && \
    docker-php-ext-enable zip

RUN apk add --no-cache dcron

# Copy crontab file to the container
COPY ./php/crontab /etc/crontabs/root

HEALTHCHECK CMD ["php-fpm", "-t"]

CMD ["/bin/bash", "-c", "cd /var/www/html/; chmod +x post_install.sh 2>/dev/null || true; ./post_install.sh && crond && php-fpm"]
