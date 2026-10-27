#!/usr/bin/env bash
set -e

# Configuración de puerto dinámico para Render / Koyeb / Docker
PORT="${PORT:-80}"
echo "Configuring Apache to listen on port ${PORT}..."
sed -i "s/Listen [0-9]*/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Asegurar directorios de almacenamiento y permisos
mkdir -p storage/framework/{sessions,views,cache/data} storage/logs storage/app/public/{cultivos_portadas,lotes,fotos_perfil,actividades_evidencia} bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Enlace simbólico de storage para fotos y archivos públicos
echo "Ensuring storage symlink..."
rm -rf public/storage
php artisan storage:link
chown -R www-data:www-data storage public/storage
chmod -R 777 storage

# Ejecutar migraciones si está configurado en el entorno
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# Optimización de caché para producción
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "Caching configuration, routes and views..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "Starting Apache on port ${PORT}..."
exec apache2-foreground
