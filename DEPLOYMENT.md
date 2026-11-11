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
APP_NAME="Doctor Kevin Rejuvenezk"
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

## Portal del paciente y nombre de marca

Esta version agrega el portal de pacientes (`/ingresar`, `/registro`, `/mi-cuenta`), la politica de datos (`/politica-de-datos`), la gestion de pacientes en Filament y el aislamiento por clinica en todo el panel. Tambien renombra la marca a "Doctor Kevin Rejuvenezk".

### Antes del primer deploy con estos cambios

1. **Haz backup de la base de datos.** Ejemplo con MySQL:

   ```bash
   mysqldump --single-transaction --routines -h <host> -u <usuario> -p <base> > backup-antes-portal-$(date +%F).sql
   ```

2. Confirma que el servicio usa **MySQL persistente** (no SQLite dentro del contenedor) y que `storage/app/public` esta en un **volumen persistente**. Sin eso, los pacientes, tratamientos e imagenes se pierden en el siguiente rebuild.
3. Verifica en las variables de entorno `APP_LOCALE=es`. Las fechas de renovacion usan la zona de la clinica (`clinics.timezone`, por defecto `America/Bogota`); no hace falta cambiar `APP_TIMEZONE`.

### Migraciones nuevas

| Migracion | Que hace | Reversion |
|---|---|---|
| `2026_11_10_000100_rename_brand_to_doctor_kevin` | Cambia `clinics.name` y el ajuste `branding.clinic_name` solo si valen exactamente "Clinica/Clínica Rejuvenezk" e invalida su cache. | `down()` no hace nada a proposito; el nombre se cambia desde Filament. |
| `2026_11_10_000200_create_patients_tables` | Crea `patients` y `patient_treatments` y agrega `services.renewal_interval_days`. | `down()` borra ambas tablas: **se pierden los pacientes**. Restaura desde el backup en su lugar. |

`docker-entrypoint.sh` **no** ejecuta migraciones. Deben correr con el comando post-deploy:

```bash
composer run deploy
```

Ese script ya ejecuta `migrate --force`, `optimize:clear`, `filament:upgrade` y las caches de config, rutas y vistas. Si el post-deploy no corre, `/ingresar` y `/registro` responden error 500 porque falta la tabla `patients`.

El Build Command de Dokploy debe seguir incluyendo `npm ci && npm run build` (el CSS nuevo vive en `resources/css/app.css`).

### Despues del deploy

```bash
php artisan migrate:status   # ambas migraciones 2026_11_10_* en "Ran"
php artisan route:list --name=patient
php artisan about
```

- Revisa `storage/logs/laravel.log` sin errores nuevos.
- Cada usuario del panel necesita `clinic_id` o `is_super_admin`. Un super admin sin `clinic_id` trabaja sobre la clinica activa por defecto (la primera activa).
- En **Servicios**, completa "Renovar cada (dias)" en los tratamientos que se renuevan (por ejemplo 180 para toxina botulinica); sin ese dato no aparecen en el widget de renovaciones.
- Revisa el texto de `/politica-de-datos` con un asesor legal y quita el aviso de borrador antes de promocionar el registro.

### Lista de verificacion manual (en celular)

Sitio publico:

- [ ] `/`: el titular y "Reservar valoracion" se ven sin hacer scroll; "Escribenos por WhatsApp" abre el chat con el mensaje prellenado; la barra inferior Llamar/WhatsApp no tapa el footer; no hay scroll horizontal.
- [ ] `/`: el formulario (nombre, celular, tratamiento) envia y muestra el mensaje de exito; el enlace de la politica abre `/politica-de-datos`.
- [ ] `/`: si hay galeria con antes y despues, el comparador se desliza con el dedo.
- [ ] `/quienes-somos` y `/servicios`: el menu muestra "Ingresar" y el footer "Politica de datos".

Portal del paciente:

- [ ] `/registro`: crear una cuenta nueva (sin marcar promociones) entra a "Mi cuenta".
- [ ] `/registro` con un celular o correo ya registrado: muestra "Ya tenemos un registro con estos datos..." y no entra.
- [ ] `/ingresar` con correo y con celular (`300 123 4567` o `+57...`); una contrasena incorrecta muestra el mensaje generico.
- [ ] `/mi-cuenta`: muestra la proxima renovacion, la lista de procedimientos con sus estados y "Agendar renovacion" abre WhatsApp con el nombre del tratamiento. Las notas internas no aparecen.
- [ ] `/mi-cuenta/perfil`: cambiar el celular y el consentimiento; cambiar la contrasena exige la actual.
- [ ] "Cerrar sesion" vuelve al inicio; `/mi-cuenta` sin sesion redirige a `/ingresar`.
- [ ] `/politica-de-datos` carga con los datos de contacto de la clinica.

Panel `/admin`:

- [ ] Login de un admin de la clinica y de un super admin.
- [ ] **Pacientes**: lista, busqueda, filtros "con consentimiento" y "con renovaciones vencidas"; crear un paciente sin contrasena y luego asignarle una.
- [ ] **Tratamientos** dentro del paciente: crear uno con notas visibles e internas; el estado se calcula con el servicio.
- [ ] **Escritorio**: el widget "Renovaciones proximas y vencidas" y su boton de WhatsApp.
- [ ] **Exportar CSV (con consentimiento)**: abre en Excel con tildes correctas y solo incluye pacientes con consentimiento.
- [ ] **Servicios**: crear y editar uno; campo "Renovar cada (dias)".
- [ ] **Banners Home**: subir una imagen.
- [ ] Un usuario de otra clinica (si existe) no ve leads, citas, servicios ni pacientes ajenos.

### Rollback

- Codigo: vuelve al commit anterior y redeploya.
- El aislamiento del panel esta en un commit propio (`feat(admin): aislamiento por clínica en todo el panel Filament`); se puede revertir con `git revert` sin tocar el portal.
- Datos: no ejecutes `migrate:rollback` en produccion sin backup; restaura el dump si necesitas volver atras.