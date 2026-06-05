FROM ubuntu:focal

ARG DEBIAN_FRONTEND=noninteractive
ARG PHP_VERSION=8.1
ENV TZ=Europe/Belgrade

RUN apt-get update -y
RUN apt-get install -y apt-utils
RUN apt-get install -y vim
RUN apt-get install -y curl
RUN apt-get install -y apache2
RUN apt-get install -y zip unzip
RUN a2enmod rewrite

RUN apt-get update -y

RUN apt-get install -y software-properties-common
RUN add-apt-repository ppa:ondrej/php

RUN apt-get update -y

RUN apt-get update -y && apt-get install -y php$PHP_VERSION
RUN apt-get install -y php-pear
RUN apt-get install -y libapache2-mod-php$PHP_VERSION
RUN apt-get install -y php$PHP_VERSION-mysql
RUN apt-get install -y php$PHP_VERSION-bcmath
RUN apt-get install -y php$PHP_VERSION-cgi
RUN apt-get install -y php$PHP_VERSION-xsl
RUN apt-get install -y php$PHP_VERSION-gd
RUN apt-get install -y php$PHP_VERSION-imagick
RUN apt-get install -y php$PHP_VERSION-tidy
RUN apt-get install -y php$PHP_VERSION-xml
RUN apt-get install -y php$PHP_VERSION-cli
RUN apt-get install -y php$PHP_VERSION-common
RUN apt-get install -y php$PHP_VERSION-curl
RUN apt-get install -y php$PHP_VERSION-mbstring
RUN apt-get install -y php$PHP_VERSION-zip
RUN apt-get install -y php$PHP_VERSION-intl

#RUN apt-get update && apt-get install -y curl gnupg ca-certificates
#RUN mkdir -p /etc/apt/keyrings
#RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
#RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_14.x jammy main" | tee /etc/apt/sources.list.d/nodesource.list
#RUN apt-get update && apt-get install -y nodejs

RUN curl -sL https://deb.nodesource.com | bash - \
    && apt-get install -y nodejs

RUN apt-get update
RUN apt-get install -y nodejs
RUN apt-get install -y net-tools
RUN apt-get install -y git

RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
&& curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
# Make sure we're installing what we think we're installing!
&& php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
&& php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer \
&& rm -f /tmp/composer-setup.*

RUN a2enmod headers
RUN a2enmod expires
RUN a2enmod http2

RUN usermod -u 501 www-data

COPY 000-default.conf /etc/apache2/sites-available
COPY custom.ini /etc/php/$PHP_VERSION/apache2/conf.d/custom.ini

RUN phpenmod intl

WORKDIR /var/www/html

EXPOSE 80

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
