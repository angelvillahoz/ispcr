# https://hub.docker.com/_/php
FROM php:7.4-apache

# Install the dependencies and the PHP extensions .
RUN apt-get update -y && \
    apt-get install -y --no-install-recommends \
        libcurl4-openssl-dev \
        libmcrypt-dev \
        libonig-dev \
        libpng-dev \
        libpq-dev \
        libxml2-dev \
        libyaml-dev \
        unzip \
        wget && \
    docker-php-ext-install \
        bcmath \
        gd \
        mysqli \
        pdo_mysql \
        pgsql && \
    pecl install \
        mcrypt-1.0.4 \
        yaml-2.2.1 \
        xdebug-3.0.2 && \
    docker-php-ext-enable \
        mcrypt \
        yaml

# Copy configuration and license files to their respective places.
COPY ./assets/config.ini /usr/local/etc/php/conf.d/
COPY ./assets/ispcr-site.conf /etc/apache2/conf-available/

# Enable mod_rewrite and the site configuration for the Apache HTTP server.
RUN a2enmod rewrite && \
    a2enconf ispcr-site

# Expose a mount point for mounting and working with web application files.
VOLUME ["/var/www"]
WORKDIR /var/www
