FROM ubuntu:focal

ARG DEBIAN_FRONTEND=noninteractive

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

RUN apt-get update -y && apt-get install -y php7.4
RUN apt-get install -y php-pear
RUN apt-get install -y  libapache2-mod-php7.4
RUN apt-get install -y php7.4-mysql
RUN apt-get install -y php7.4-bcmath
RUN apt-get install -y php7.4-json
RUN apt-get install -y php7.4-cgi
RUN apt-get install -y php7.4-xsl
RUN apt-get install -y php7.4-gd
RUN apt-get install -y php7.4-imagick
RUN apt-get install -y php7.4-tidy
RUN apt-get install -y php7.4-xmlrpc
RUN apt-get install -y php7.4-xml
RUN apt-get install -y php7.4-cli
RUN apt-get install -y php7.4-common
RUN apt-get install -y php7.4-curl
RUN apt-get install -y php7.4-mbstring
RUN apt-get install -y php7.4-zip
RUN apt-get install -y php7.4-intl

RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -

RUN apt-get update
RUN apt-get install -y nodejs
RUN apt-get install -y net-tools
RUN apt-get install -y git

RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
&& curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
# Make sure we're installing what we think we're installing!
&& php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
&& php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer --snapshot --version=1.10.13 \
&& rm -f /tmp/composer-setup.*

RUN a2enmod headers
RUN a2enmod expires
RUN a2enmod http2

RUN usermod -u 501 www-data

COPY 000-default.conf /etc/apache2/sites-available
COPY custom.ini /etc/php/7.4/apache2/conf.d/custom.ini

RUN phpenmod intl

WORKDIR /var/www/html

EXPOSE 80

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
