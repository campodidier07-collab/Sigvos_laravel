# ==========================================
# 1. Compilación de Assets Frontend (Vite)
# ==========================================
FROM node:20-alpine AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources/ resources/
COPY public/ public/

RUN npm run build

# ==========================================
# 2. Servidor de Producción (PHP 8.3 + Apache)
# ==========================================
FROM php:8.3-apache-bookworm

# Instalar dependencias del sistema y utilidades
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
    git \
    unzip \
    zip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        bcmath \
        intl \
        opcache \
        gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Habilitar módulos de Apache
RUN a2enmod rewrite headers

# Configurar Apache VirtualHost
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Instalar dependencias de PHP primero para cachear capas de Docker
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Copiar el código fuente de la aplicación
COPY . .

# Copiar los assets compilados por Vite desde la etapa frontend
COPY --from=frontend /app/public/build ./public/build

# Finalizar autoload de Composer optimizado
RUN composer dump-autoload --optimize --no-dev --no-interaction

# Copiar y preparar script de entrada
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

# Configurar permisos para Apache y Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
