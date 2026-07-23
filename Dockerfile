FROM php:8.0-apache

# Installation des dépendances système et de l'extension pdo_mysql pour la base de données
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Activation du module de réécriture Apache (a2enmod rewrite)
RUN a2enmod rewrite

WORKDIR /var/www/html

CMD ["apache2-foreground"]
