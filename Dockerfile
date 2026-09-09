FROM php:8.2-apache

COPY php.ini /usr/local/etc/php/php.ini

# Installation des dépendances et compilation des extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# Activation du module de réécriture Apache (a2enmod rewrite) et masquage des en-têtes d'information
RUN a2enmod rewrite \
    && echo "ServerTokens Prod" >> /etc/apache2/apache2.conf \
    && echo "ServerSignature Off" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

CMD ["apache2-foreground"]
