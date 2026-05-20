FROM node:20-bookworm AS node_source

FROM php:8.4-apache-bookworm

ENV TZ=Europe/Belgrade

# System dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxslt-dev \
    libmagickwand-dev \
    libtidy-dev \
    zip unzip git vim curl \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo pdo_mysql mysqli \
        intl \
        zip \
        gd \
        xsl \
        tidy \
        bcmath \
        opcache

# Imagick via PECL (install from git for PHP 8.4 compatibility)
RUN git clone https://github.com/Imagick/imagick.git --depth 1 /tmp/imagick \
    && cd /tmp/imagick \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-enable imagick \
    && rm -rf /tmp/imagick

# Apache modules
RUN a2enmod rewrite headers expires

# Apache config — point DocumentRoot to /var/www/html/web
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# PHP config
COPY custom.ini /usr/local/etc/php/conf.d/custom.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY --from=node_source /usr/local/bin /usr/local/bin
COPY --from=node_source /usr/local/lib/node_modules /usr/local/lib/node_modules

WORKDIR /var/www/html

EXPOSE 80
