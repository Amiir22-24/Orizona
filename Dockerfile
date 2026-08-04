FROM php:8.2-apache

# 1. Installation des dépendances système et extensions PHP
#    - libpq-dev    : requis pour pdo_pgsql (PostgreSQL)
#    - libzip-dev   : requis pour l'extension zip
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# 2. Activation du mod_rewrite pour les routes Laravel
RUN a2enmod rewrite

# 3. Configuration d'Apache pour le port dynamique de Render
#    Remplace le port 80 par la variable $PORT dans toute la configuration
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 4. Pointer le DocumentRoot vers le dossier /public de Laravel
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# 5. Activer .htaccess dans le VirtualHost (AllowOverride All)
RUN echo '<Directory /var/www/html/public>\n    Options Indexes FollowSymLinks\n    AllowOverride All\n    Require all granted\n</Directory>' \
    >> /etc/apache2/sites-available/000-default.conf

# 6. Copie des fichiers du projet
COPY . /var/www/html

# 7. Installation de Composer et des dépendances PHP
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader

# 8. Gestion des permissions pour le stockage et le cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 9. Créer le lien symbolique pour le storage (public disk)
RUN php artisan storage:link || true

# 10. Nettoyer et optimiser (sans connexion DB)
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# 11. Exposition du port (informatif, Render utilise la variable $PORT)
EXPOSE ${PORT:-10000}

# 12. COMMANDE FINALE :
#     - Exécute les migrations (--force requis en production)
#     - Lance Apache en mode foreground (maintient le conteneur actif)
#     Le "&&" garantit que le serveur ne démarre que si les migrations réussissent.
CMD php artisan migrate --force && php artisan db:seed --class=AdminSeeder --force && apache2-foreground