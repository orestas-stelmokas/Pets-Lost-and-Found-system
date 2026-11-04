FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo_mysql \
    && a2enmod rewrite

COPY docker/apache-security.conf /etc/apache2/conf-available/pet-security.conf
RUN a2enconf pet-security

# Development: bind mount in compose.yaml shows local PHP changes immediately.
# The same image also includes project code for use without a bind mount.
COPY . /var/www/html/
