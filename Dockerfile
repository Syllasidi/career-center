# Image officielle PHP avec Apache
FROM php:8.2-apache

# Activer mod_rewrite (MVC)
RUN a2enmod rewrite

# Installer dépendances PostgreSQL (OBLIGATOIRE)
RUN apt-get update && apt-get install -y libpq-dev

# Installer extensions PHP PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Dossier de travail Apache
WORKDIR /var/www/html

# Copier le projet
COPY . /var/www/html

# Droits corrects
RUN chown -R www-data:www-data /var/www/html
