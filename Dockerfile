FROM php:8.2-apache

# Installer Python, pip, dépendances système (PostgreSQL, GD pour images)
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    git \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Installer PDO PostgreSQL (pour Supabase) et GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql gd

# Activer le module de réécriture d'URL Apache (pour Clean URLs)
RUN a2enmod rewrite

# Copier tout le code de l'application dans le répertoire web
WORKDIR /var/www/html
COPY . /var/www/html/

# Créer un environnement virtuel Python pour isoler les paquets
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"

# Installer les dépendances Python requises par l'application
RUN pip install --no-cache-dir -r requirements.txt

# Créer des répertoires pour s'assurer qu'Apache a le droit d'écriture (Uploads, Sphinx, Logs)
RUN mkdir -p /var/www/html/Docs/_build/html \
    && mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html

# Configuration d'Apache pour utiliser le port dynamique fourni par Render
# Render injectera la variable $PORT.
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Script de lancement : configure dynamiquement Apache pour écouter sur le $PORT
CMD sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf && docker-php-entrypoint apache2-foreground
