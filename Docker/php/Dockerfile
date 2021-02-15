FROM php:7.4-apache

RUN a2enmod rewrite && sed -i "s|DocumentRoot /var/www/html|DocumentRoot /var/www/API/public|g" /etc/apache2/sites-enabled/000-default.conf

RUN apt-get update
RUN apt-get install -y libzip-dev zlib1g-dev libpq-dev git libicu-dev libxml2-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && docker-php-ext-install zip xml

RUN curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

# Set timezone
RUN rm /etc/localtime
RUN ln -s /usr/share/zoneinfo/Europe/London /etc/localtime
RUN "date"
WORKDIR /var/www/API