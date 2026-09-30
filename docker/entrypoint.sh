#!/bin/sh
set -e

echo "==> Iniciando SIM-EMAPAP en Dokploy..."

# 1. Asegurar directorios de almacenamiento y caché
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/storage/app/migracion_jobs
mkdir -p /var/www/html/storage/app/respaldos_migracion
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# 2. Permisos para el usuario web
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 3. Enlace simbólico de storage a public
rm -f /var/www/html/public/storage
php artisan storage:link --force || true

# 4. Asegurar permisos de ejecución en binarios wkhtmltopdf (Snappy)
if [ -f /var/www/html/vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64 ]; then
    chmod +x /var/www/html/vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64
fi
if [ -f /var/www/html/vendor/h4cc/wkhtmltoimage-amd64/bin/wkhtmltoimage-amd64 ]; then
    chmod +x /var/www/html/vendor/h4cc/wkhtmltoimage-amd64/bin/wkhtmltoimage-amd64
fi

# 5. Esperar a la base de datos si se requiere migrar, sembrar o preparar produccion
if [ "${RUN_MIGRATIONS:-false}" = "true" ] || [ "${RUN_SEEDERS:-false}" = "true" ] || [ "${PREPARAR_PRODUCCION:-false}" = "true" ]; then
    echo "==> Esperando disponibilidad de PostgreSQL..."
    for i in $(seq 1 30); do
        if php -r "try { new PDO('pgsql:host='.getenv('DB_HOST').';port='.(getenv('DB_PORT') ?: 5432).';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [PDO::ATTR_TIMEOUT => 2]); exit(0); } catch (\Throwable \$e) { exit(1); }"; then
            echo "==> Base de datos lista y conectada."
            break
        fi
        echo "==> Esperando conexion con PostgreSQL ($i/30)..."
        sleep 2
    done
fi

# 6. Modo Preparación de Producción limpia (si PREPARAR_PRODUCCION=true)
if [ "${PREPARAR_PRODUCCION:-false}" = "true" ]; then
    echo "==> Ejecutando reconstrucción limpia de base de datos para producción..."
    php artisan emapap:preparar-produccion --force || echo "==> [ADVERTENCIA] Falló emapap:preparar-produccion."
else
    # 7. Ejecutar migraciones automáticamente si RUN_MIGRATIONS=true
    if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
        echo "==> Ejecutando migraciones de base de datos..."
        php artisan migrate --force || echo "==> [ADVERTENCIA] Fallaron algunas migraciones."
    fi

    # 8. Ejecutar seeders si RUN_SEEDERS=true
    if [ "${RUN_SEEDERS:-false}" = "true" ]; then
        echo "==> Ejecutando seeders estructurales de producción..."
        php artisan db:seed --force || echo "==> [ADVERTENCIA] Fallaron algunos seeders."
    fi
fi

# 9. Optimización de Laravel (después de migraciones y seeders para asegurar consistencia)
if [ -n "$APP_KEY" ]; then
    echo "==> Optimizando configuración, rutas y vistas..."
    php artisan optimize:clear || true
    if [ "${APP_ENV}" = "production" ]; then
        php artisan config:cache || true
        php artisan route:cache || true
        php artisan view:cache || true
    fi
fi

# 10. Asegurar permisos finales para www-data tras comandos artisan
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "==> Listo. Iniciando Nginx y PHP-FPM..."
exec "$@"
