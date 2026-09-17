# =========================================================================
# Etapa 1: Compilación de Assets Frontend (Vue 2, Vuetify, Laravel Mix)
# =========================================================================
FROM node:22-bookworm-slim AS frontend

WORKDIR /app

# Instalar dependencias npm
COPY package*.json ./
RUN npm install --legacy-peer-deps

# Copiar configuraciones y código fuente para la compilación
COPY webpack.mix.js babel.config.js ./
COPY resources ./resources
COPY public ./public

# Compilar assets para producción y asegurar chunks en public/js/chunks
RUN npm run prod \
    && if [ -d /app/js/chunks ]; then mkdir -p /app/public/js/chunks && cp -rn /app/js/chunks/* /app/public/js/chunks/ 2>/dev/null || true; fi

# =========================================================================
# Etapa 2: Aplicación PHP-FPM + Nginx (Producción)
# =========================================================================
FROM php:8.3-fpm-bookworm

# Evitar prompts interactivos durante la instalación
ENV DEBIAN_FRONTEND=noninteractive

WORKDIR /var/www/html

# Instalar dependencias del sistema y librerías necesarias
# Incluye paquetes requeridos por wkhtmltopdf (Snappy PDF)
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    curl \
    git \
    unzip \
    zip \
    libpq-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libicu-dev \
    libonig-dev \
    libxrender1 \
    libxext6 \
    libfontconfig1 \
    fontconfig \
    fonts-dejavu-core \
    xfonts-75dpi \
    xfonts-base \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configurar e instalar extensiones de PHP necesarias para Laravel, PostgreSQL y Snappy
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        bcmath \
        soap \
        intl \
        pcntl \
        opcache \
        exif

# Instalar Composer directamente (rápido y previene timeouts de Docker Hub)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Copiar configuración de PHP, Nginx y Supervisor
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copiar el código fuente del proyecto (incluye vendor si ya existe en local)
COPY . /var/www/html

# Copiar los assets compilados de la Etapa 1
COPY --from=frontend /app/public /var/www/html/public

# Instalar o sincronizar dependencias de Composer para producción
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --ignore-platform-req=php

# Permisos para directorios de escritura de Laravel
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Healthcheck para Dokploy y Docker Compose
HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Puerto expuesto para Dokploy / Nginx
EXPOSE 80

# Script de arranque y comando principal
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
