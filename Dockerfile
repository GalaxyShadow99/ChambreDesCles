FROM php:8.2-apache

# Installation des dépendances et compilation des extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# Activation du module de réécriture Apache (a2enmod rewrite)
RUN a2enmod rewrite

WORKDIR /var/www/html

CMD ["apache2-foreground"]
