#!/bin/sh
# Arranque del contenedor en Dokploy (Nixpacks). Lo invoca nixpacks.toml -> [start].
#
# 1. Prepara Laravel: carpetas de storage, public/storage y cachés de config/rutas/vistas.
#    NO ejecuta migraciones: se corren en el post-deploy (`composer run deploy`) o a mano.
# 2. Arranca php-fpm + nginx igual que el comando de inicio por defecto de Nixpacks
#    (que nixpacks.toml reemplaza), más el límite de subida de 200 MB para videos.
#
# Variables opcionales (para pruebas o imágenes distintas):
#   NIXPACKS_ASSETS_DIR  carpeta de assets de Nixpacks (por defecto /assets)
#   NGINX_CONF_PATH      dónde se genera la config de nginx (por defecto /nginx.conf)
#   SKIP_SERVER=1        solo prepara Laravel, no arranca nginx/php-fpm

set -u

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
ASSETS_DIR="${NIXPACKS_ASSETS_DIR:-/assets}"
NGINX_CONF="${NGINX_CONF_PATH:-/nginx.conf}"

log() {
    echo "[arranque] $*"
}

cd "$APP_DIR" || {
    log "ERROR: no se pudo entrar a $APP_DIR"
    exit 1
}

# --- 1. Preparar Laravel (un fallo aquí avisa, pero no impide que la web arranque) ---
php scripts/deploy/ensure-storage.php || log "AVISO: revisa los permisos de storage/ y bootstrap/cache/"
php scripts/deploy/storage-link.php || log "AVISO: no se pudo crear public/storage"

for cache in config route view; do
    php artisan "$cache:cache" --no-interaction || log "AVISO: falló $cache:cache; la app funciona sin esa caché (más lenta)"
done

if [ "${SKIP_SERVER:-0}" = "1" ]; then
    log "Laravel preparado (SKIP_SERVER=1: no se arranca el servidor)"
    exit 0
fi

# --- 2. Configuración de nginx con la herramienta de Nixpacks que traiga la imagen ---
if [ -f "$ASSETS_DIR/scripts/prestart.mjs" ]; then
    # Nixpacks actual: genera la config y ajusta permisos de storage.
    node "$ASSETS_DIR/scripts/prestart.mjs" "$ASSETS_DIR/nginx.template.conf" "$NGINX_CONF" || {
        log "ERROR: prestart.mjs no pudo generar $NGINX_CONF"
        exit 1
    }
elif [ -f "$ASSETS_DIR/transform-config.pl" ]; then
    # Nixpacks anterior.
    [ -e "$APP_DIR/storage" ] && chmod -R ugo+w "$APP_DIR/storage"
    perl "$ASSETS_DIR/transform-config.pl" "$ASSETS_DIR/nginx.template.conf" "$NGINX_CONF" || {
        log "ERROR: transform-config.pl no pudo generar $NGINX_CONF"
        exit 1
    }
else
    log "ERROR: no se encontró la plantilla de nginx de Nixpacks en $ASSETS_DIR"
    exit 1
fi

# Videos: subidas de hasta 200 MB (antes lo hacían docker-entrypoint.sh / build.sh).
if ! grep -q "client_max_body_size" "$NGINX_CONF"; then
    sed -i 's/tcp_nopush   on;/tcp_nopush   on;\n    client_max_body_size 200M;/' "$NGINX_CONF"
fi
if ! grep -q "client_max_body_size" "$NGINX_CONF"; then
    sed -i 's/^\([[:space:]]*\)http {/\1http {\n\1    client_max_body_size 200M;/' "$NGINX_CONF"
fi
grep -q "client_max_body_size" "$NGINX_CONF" || log "AVISO: no se pudo fijar client_max_body_size (subidas limitadas a 1 MB)"

# --- 3. Servidor: igual que el inicio por defecto de Nixpacks ---
log "Iniciando php-fpm y nginx"
php-fpm -y "$ASSETS_DIR/php-fpm.conf" &
exec nginx -c "$NGINX_CONF"
