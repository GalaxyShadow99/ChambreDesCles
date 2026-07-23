FROM php:8.5-apache

RUN docker-php-ext-install pdo pdo_mysql

# Activation du module de réécriture Apache (a2enmod rewrite)
RUN a2enmod rewrite

WORKDIR /var/www/html

CMD ["apache2-foreground"]
