FROM php:8.5-apache

# Installation des dépendances, compilation des extensions, puis purge des paquets de dev
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get purge -y --auto-remove libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Activation du module de réécriture Apache (a2enmod rewrite)
RUN a2enmod rewrite

WORKDIR /var/www/html

CMD ["apache2-foreground"]
