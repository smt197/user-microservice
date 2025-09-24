# --- 1. Stage 'base' ---
FROM php:8.2-fpm-alpine as base

# Installer dépendances système et PHP nécessaires
RUN apk add --no-cache \
    supervisor nginx bash git curl unzip \
    libpng-dev oniguruma-dev libxml2-dev libzip-dev icu-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

WORKDIR /var/www

# --- 2. Stage 'vendor' ---
FROM base as vendor

# Ajouter Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier uniquement les fichiers Composer pour profiter du cache
COPY composer.json composer.lock ./

# Installer les dépendances PHP
RUN composer install --no-dev --no-interaction --optimize-autoloader --prefer-dist

# --- 3. Stage 'app' ---
FROM base as app

# Copier tout le code de l'application
COPY . .

# Copier le dossier vendor depuis le stage vendor
COPY --from=vendor /var/www/vendor/ ./vendor/

# Copier les fichiers de configuration
COPY nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Donner les permissions correctes à Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

# Lancer Supervisor (qui gère PHP-FPM + Nginx)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
