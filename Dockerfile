FROM php:8.5-apache

# Installation des dépendances système nécessaires (zip et client MariaDB/MySQL)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP requises
RUN docker-php-ext-install pdo pdo_mysql zip

# Activation du module de réécriture Apache (a2enmod rewrite)
RUN a2enmod rewrite

WORKDIR /var/www/html

CMD ["apache2-foreground"]
