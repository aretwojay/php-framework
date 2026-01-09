FROM php:8.4-apache

# Installer les dépendances système + libpq-dev pour PostgreSQL
RUN apt-get update -y && apt-get upgrade -y \
    && apt-get install -y zlib1g-dev libwebp-dev libpng-dev libzip-dev git unzip libpq-dev curl

# Installer les extensions PHP
RUN docker-php-ext-install gd zip pdo pdo_mysql mysqli pdo_pgsql

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installer Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

# Activer mod_rewrite d'Apache
RUN a2enmod rewrite

# Copier le projet dans l'image
COPY . /var/www/html/

# Configurer Apache pour autoriser l'accès au dossier public
RUN echo "<VirtualHost *:80>\n    DocumentRoot /var/www/html/app/public\n    <Directory /var/www/html/app/public>\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>" > /etc/apache2/sites-available/000-default.conf

# Exposer le port 80
EXPOSE 80

# Commande par défaut
CMD ["apache2-foreground"]

