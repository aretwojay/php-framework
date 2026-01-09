FROM php:8.4-apache

# PHP
RUN apt-get update -y && apt-get upgrade -y

# Installer les dépendances pour GD, ZIP et PostgreSQL
RUN apt-get install -y zlib1g-dev libwebp-dev libpng-dev libzip-dev git unzip
RUN docker-php-ext-install gd zip pdo pdo_mysql mysqli pdo_pgsql

# Composer
#COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_22.x -o nodesource_setup.sh
RUN bash nodesource_setup.sh
RUN apt-get install -y nodejs

# Apache
RUN a2enmod rewrite
RUN service apache2 restart

EXPOSE 80
