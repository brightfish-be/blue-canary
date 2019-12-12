FROM php:7.3-fpm-alpine

LABEL maintainer="Brightfish <operations@brightfish.be>"

ARG APP_ENV
ARG APP_TIMEZONE

ENV COMPOSER_NO_INTERACTION=1
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HTACCESS_PROTECT=0

RUN set -xe \
    && NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) \
\
# Set the container time and date
    && apk add --no-cache tzdata \
    && ln -sf /usr/share/zoneinfo/$APP_TIMEZONE /etc/localtime \
    && echo $APP_TIMEZONE > /etc/timezone \
\
# Run-time packages
    && apk add --no-cache \
        libzip-dev \
\
# Compile modules using available cores
    && docker-php-ext-configure zip --with-libzip \
    && docker-php-ext-install -j${NPROC} pdo_mysql opcache zip \
    && rm -rf /tmp/* /var/cache/apk/*

# PHP configuration
COPY ./install/php-${APP_ENV:-production}.ini /usr/local/etc/php/php.ini

# Install composer
COPY ./install/composer.sh /
RUN chmod +x /composer.sh \
    && sleep 1; /composer.sh \
    && rm /composer.sh

# Create the app root dir
WORKDIR /var/www/app

# Install Composer dependencies
COPY ./composer.* ./
RUN composer install -n --no-dev --no-scripts --no-autoloader --no-suggest

# Copy the app
COPY . .

# Eensure access to the storage and cache folders;
# make run command executable.
RUN chown -R www-data:www-data storage bootstrap \
    && chmod +x ./install/run.sh

CMD ./install/run.sh
