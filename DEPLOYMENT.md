# Deployment Guide

## Requisitos del servidor

- PHP 8.2 o superior
- Composer 2
- Node.js 20 o superior
- Servidor web Nginx o Apache
- Base de datos SQLite o MySQL/MariaDB
- Acceso para ejecutar tareas programadas y colas si se usan en produccion

Para produccion en Dokploy no uses SQLite dentro del contenedor si esperas conservar contenido tras rebuilds. Usa MySQL o MariaDB persistente.

## Variables de entorno minimas

Configura al menos estas variables en `.env`:

```dotenv
APP_NAME="Clinica Rejuvenezk"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=clinica_rejuvenezk
DB_USERNAME=usuario
DB_PASSWORD=secret

CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.tu-proveedor.com
MAIL_PORT=587
MAIL_USERNAME=usuario
MAIL_PASSWORD=secret
MAIL_FROM_ADDRESS="no-reply@tu-dominio.com"
MAIL_FROM_NAME="${APP_NAME}"
```

La configuracion de TayrAI se gestiona en la base de datos mediante los ajustes del sitio.

Si los archivos subidos desde Filament deben sobrevivir a rebuilds, monta un volumen persistente para `storage/app/public` o mueve el disco `public` a S3.

## Primer despliegue

```bash
git clone https://github.com/Manjm1012/Clinica-Rejuvenezk.git
cd Clinica-Rejuvenezk
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Alternativa usando el script del proyecto:

```bash
composer run deploy:full
```

## Permisos

Asegura permisos de escritura en:

- `storage`
- `bootstrap/cache`

## Actualizacion de version

```bash
git pull origin main
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan filament:upgrade
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Alternativa usando el script del proyecto:

```bash
composer run deploy:full
```

## Dokploy

Configura el servicio para que el rebuild no pierda contenido administrativo ni uploads.

### Variables recomendadas

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=<host-del-servicio-mysql>
DB_PORT=3306
DB_DATABASE=clinica_rejuvenezk
DB_USERNAME=<usuario>
DB_PASSWORD=<password>

FILESYSTEM_DISK=public
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### Build Command

```bash
composer install --no-dev --prefer-dist --optimize-autoloader && npm ci && npm run build
```

### Start o Post-Deploy Command

Si Dokploy te permite comando post-deploy, usa:

```bash
composer run deploy
```

Si solo te permite un comando unico de despliegue, usa:

```bash
composer run deploy:full
```

### Volumenes persistentes

Monta al menos estos paths si el contenedor se reemplaza en cada rebuild:

- `storage/app/public`
- `database` solo si aun insistes en SQLite

Si vas a usar MySQL, no montes `database/database.sqlite` como estrategia principal.

### Recomendacion operativa

- Base de datos: MySQL o MariaDB administrado por Dokploy o externo.
- Uploads: volumen persistente o S3.
- Despues del deploy valida el panel de Filament y una imagen subida desde el admin.

## Queue worker y scheduler

Si usas colas o tareas programadas, configura procesos persistentes. Ejemplo con Supervisor:

```ini
[program:clinica-rejuvenezk-queue]
command=php /var/www/clinica-rejuvenezk/artisan queue:work --sleep=3 --tries=3 --timeout=90
directory=/var/www/clinica-rejuvenezk
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/clinica-rejuvenezk/storage/logs/queue.log
```

Cron para scheduler:

```bash
* * * * * cd /var/www/clinica-rejuvenezk && php artisan schedule:run >> /dev/null 2>&1
```

## Verificaciones posteriores al deploy

- `php artisan about`
- `php artisan migrate:status`
- revisar logs en `storage/logs`
- comprobar `/`, `/procedimientos/{slug}` y el panel de Filament