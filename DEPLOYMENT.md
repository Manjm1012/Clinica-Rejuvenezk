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
APP_NAME="Dr. Kevin Rejuvenezk"
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

La configuracion de TayrAI se toma de las variables `TAYRAI_*` y, si estan vacias, de los ajustes del sitio (ver "Paso a produccion").

Para que los archivos sobrevivan a los rebuilds, monta volumenes persistentes para **`storage/app/public`** (imagenes y videos subidos desde Filament) y **`storage/app/private`** (firmas de los medicos y subidas temporales), o mueve el disco `public` a S3.

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

### Administrador inicial

El seeder (`php artisan db:seed --force`) **no tiene credenciales por defecto**. Fuera de `local`/`testing` exige:

```
ADMIN_EMAIL=correo-real@dominio
ADMIN_PASSWORD=una-clave-de-al-menos-12-caracteres
```

Si faltan, falla con un mensaje claro. Si el administrador ya existe, no cambia su clave. Después del primer ingreso, borra `ADMIN_PASSWORD` del entorno. En `local`, si faltan las variables, crea `admin@localhost` con una clave aleatoria y la muestra una sola vez.

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

### Arranque del contenedor (automático, sin migraciones)

El repositorio incluye `nixpacks.toml`, que define el comando de inicio. **No hay que configurarlo en Dokploy.** En cada arranque o reinicio del contenedor, `scripts/container-start.sh`:

1. Crea las carpetas de `storage/` y `bootstrap/cache` (`scripts/deploy/ensure-storage.php`).
2. Ejecuta `php artisan storage:link` solo si `public/storage` no existe (`scripts/deploy/storage-link.php`).
3. Regenera `config:cache`, `route:cache` y `view:cache`.
4. Genera `/nginx.conf` con la plantilla de Nixpacks, fija `client_max_body_size 200M` y arranca `php-fpm` y `nginx`, igual que el inicio por defecto de Nixpacks.

**El arranque nunca ejecuta migraciones.** Si falla alguno de los pasos 1 a 3, queda un aviso `[arranque] AVISO: …` en el log y la web arranca igual. Si falta la plantilla de nginx, el contenedor se detiene con `[arranque] ERROR: …`.

> Si en Dokploy → *General* (o *Advanced*) hay un **Start Command personalizado**, bórralo o déjalo en `sh scripts/container-start.sh`: un comando ahí reemplaza al de `nixpacks.toml`. En particular, no pongas `composer run deploy` como comando de inicio, porque no arranca nginx.
>
> `docker-entrypoint.sh` y `build.sh` quedan solo como referencia: su ajuste de nginx ya lo hace `container-start.sh`.

### Post-deploy: migraciones

Las migraciones se ejecutan **una vez por deploy**, no en cada arranque. Si Dokploy te permite un comando post-deploy, usa:

```bash
composer run deploy
```

Ejecuta, en este orden: carpetas de storage, `migrate --force`, `storage:link` si falta, limpieza de cachés, `filament:upgrade` y `config/route/view:cache`. Funciona en Linux y en Windows: ya no usa `php -r "…$variable…"`, que el shell de Linux rompía.

Si Dokploy no tiene post-deploy, ejecuta las migraciones a mano desde la terminal del contenedor después de cada deploy que las traiga:

```bash
php artisan migrate --force
```

`composer run deploy:full` (instalar, compilar y luego `deploy`) sigue disponible para servidores sin Nixpacks.

### Verificar el arranque

En los logs del contenedor deben aparecer, en orden:

```
Carpetas de storage: listas
public/storage ya existe          (o "The [public/storage] link has been connected…")
INFO  Configuration cached successfully.
INFO  Routes cached successfully.
INFO  Blade templates cached successfully.
[arranque] Iniciando php-fpm y nginx
```

### Entorno local (Windows/XAMPP)

Nada de lo anterior se ejecuta en local: `nixpacks.toml` y `container-start.sh` solo los usa Nixpacks. `composer run deploy` también funciona en Windows; si lo pruebas en local, ten en cuenta que ejecuta migraciones sobre la base de tu `.env`. Para quitar las cachés que genera, usa `php artisan config:clear && php artisan route:clear && php artisan view:clear` (no `optimize:clear`, que con `CACHE_STORE=database` vacía la tabla `cache`).

### Volumenes persistentes

Monta al menos estos paths si el contenedor se reemplaza en cada rebuild:

- `storage/app/public`: imagenes, videos y logos subidos desde el panel (se sirven en `/storage` y `/media`).
- `storage/app/private`: firmas de los medicos, subidas temporales de Livewire y otros archivos sin URL publica. **Obligatorio desde la version con portal/roles**: sin el volumen, las firmas se pierden en cada rebuild.
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

Esta version agrega el portal de pacientes (`/ingresar`, `/registro`, `/mi-cuenta`), la politica de datos (`/politica-de-datos`), la gestion de pacientes en Filament y el aislamiento por clinica en todo el panel. Tambien renombra la marca a "Dr. Kevin Rejuvenezk".

### Antes del primer deploy con estos cambios

1. **Haz backup de la base de datos.** Ejemplo con MySQL:

   ```bash
   mysqldump --single-transaction --routines -h <host> -u <usuario> -p <base> > backup-antes-portal-$(date +%F).sql
   ```

2. Confirma que el servicio usa **MySQL persistente** (no SQLite dentro del contenedor) y que `storage/app/public` y `storage/app/private` estan en **volumenes persistentes**. Sin eso, los pacientes, tratamientos e imagenes se pierden en el siguiente rebuild.
3. Verifica en las variables de entorno `APP_LOCALE=es`. Las fechas de renovacion usan la zona de la clinica (`clinics.timezone`, por defecto `America/Bogota`); no hace falta cambiar `APP_TIMEZONE`.

### Migraciones nuevas

| Migracion | Que hace | Reversion |
|---|---|---|
| `2026_11_10_000100_rename_brand_to_doctor_kevin` | Cambia `clinics.name` y el ajuste `branding.clinic_name` solo si valen exactamente "Clinica Rejuvenezk", "Clínica Rejuvenezk" o "Doctor Kevin Rejuvenezk" (los pasa a "Dr. Kevin Rejuvenezk") e invalida su cache. | `down()` no hace nada a proposito; el nombre se cambia desde Filament. |
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
## Panel médico, roles y seguridad (noviembre 2026)

### Panel médico: apagado por defecto

El panel médico (`/medico`), la historia clínica, las fórmulas del portal del paciente y el rol **Médico** están detrás de la variable:

```
MEDICAL_PANEL_ENABLED=false   # valor por defecto
```

Con `false`:

- `/medico`, sus recursos, `/formulas/{id}/imprimir` y `/mi-cuenta/formulas/{id}` **no existen** (responden 404).
- El portal del paciente no muestra la sección de fórmulas.
- En el admin no aparecen el rol Médico, el vínculo a un perfil de médico ni los datos de fórmula del médico.

**Solo se activa (`true`) después de la revisión médico-legal**, que debe cubrir al menos:

1. Las plantillas de indicaciones de `config/clinical.php`, revisadas y aprobadas por los médicos tratantes.
2. El cumplimiento de la normativa de historia clínica y fórmula médica (Res. 1995 de 1999, Decreto 2200 de 2005, Ley 1581 de 2012), con un asesor legal.
3. La política de respaldos y la custodia de `APP_KEY`.

Para activarlo: define `MEDICAL_PANEL_ENABLED=true` y ejecuta `php artisan optimize:clear` y luego `php artisan config:cache && php artisan route:cache`. Las rutas se registran al arrancar, así que sin limpiar la caché el cambio no surte efecto.


### Antes del deploy

1. **Respaldo de la base de datos.** Las migraciones nuevas solo agregan tablas y columnas, pero la historia clínica no se debe arriesgar.
2. **Mueve las claves de TayrAI a variables de entorno** (tienen prioridad sobre Ajustes del sitio):
   ```
   TAYRAI_ENABLED=true
   TAYRAI_API_KEY=...
   TAYRAI_WEBHOOK_SECRET=...
   SANCTUM_EXPIRATION=43200
   ```
   Luego borra `tayrai.api_key` y `tayrai.webhook_secret` de Ajustes del sitio.
3. **`APP_KEY`: se cambia una sola vez, antes del primer deploy con datos cifrados, y nunca más.** Cifra las notas clínicas, las alergias y los antecedentes. Si se pierde o se cambia después, esos datos quedan ilegibles. Guárdala en un gestor de secretos (ver "Paso a producción").
4. **Base de datos de producción**: para historia clínica usa MySQL o PostgreSQL con respaldos automáticos diarios, no SQLite.

### Migraciones nuevas

- `2026_11_22_000100_add_roles_and_doctor_accounts`: `users.role` (los usuarios existentes quedan como `admin`), `doctors.user_id`, registro profesional, firma y datos clínicos del paciente.
- `2026_11_22_000200_create_clinical_records_tables`: consultas, notas aclaratorias, fórmulas y medicamentos.
- `2026_11_22_000300_create_service_requests_and_audit_logs`: solicitudes de renovación, auditoría y vínculo de citas con paciente y médico.

### Después del deploy: dar acceso a un médico

1. Admin → **Contenido del sitio → Médicos / Especialistas**: crea o edita el perfil (registro profesional y firma en PNG).
2. Admin → **Configuración → Usuarios y roles**: crea el usuario con rol **Médico** y elige su perfil.
3. Con `MEDICAL_PANEL_ENABLED=true`, el médico entra por **`/medico`**. Un administrador que también atiende pacientes puede tener perfil y entra a ambos paneles.

| Rol | `/admin` | `/medico` |
|---|---|---|
| Administrador | Todo | Solo si tiene perfil de médico |
| Recepción | Leads, citas, pacientes, solicitudes | No |
| Médico | No | Pacientes, consultas, fórmulas, solicitudes |

### Reglas de la historia clínica

- Una **consulta firmada** no se modifica ni se borra; solo admite notas aclaratorias.
- Una **fórmula emitida** no se modifica; se anula con motivo y se crea otra. El paciente la ve en `/mi-cuenta`.
- Un paciente con consultas o fórmulas **no se puede eliminar**.
- Cada apertura de una historia, impresión o cambio queda en **Configuración → Auditoría**.
- Las plantillas de cuidados posteriores están en `config/clinical.php`: **deben revisarlas y aprobarlas los médicos** antes de usarlas.

### Archivos

- Las subidas temporales de Livewire ahora van al disco privado (`storage/app/private/livewire-tmp`). Ese directorio debe estar en un volumen persistente igual que `storage/app/public`.
- Las firmas de los médicos se guardan en `storage/app/private/signatures` y no tienen URL pública.

### SEO

- Envía `https://<tu-dominio>/sitemap.xml` en Google Search Console.
- Páginas nuevas: `/resultados`, `/equipo-medico` y `/preguntas-frecuentes`.

## Paso a producción: portal, roles, seguridad y marca (MariaDB 11.8)

Esta sección reúne todo lo necesario para desplegar la rama `feat/doctor-kevin-portal` con `MEDICAL_PANEL_ENABLED=false`.

### Variables de entorno nuevas o cambiadas

| Variable | Para qué sirve | Valor por defecto | ¿Obligatoria? | Si falta |
|---|---|---|---|---|
| `APP_KEY` | **No es nueva, pero ahora es crítica**: cifra alergias, antecedentes, medicamentos, consultas y fórmulas. | — | **Sí. Se cambia una sola vez ANTES del primer deploy con datos cifrados y nunca más** | Sin la misma clave, los datos clínicos cifrados quedan ilegibles. |
| `APP_NAME` | Nombre en correos y títulos internos. | `"Dr. Kevin Rejuvenezk"` (antes `"Clinica Rejuvenezk"`) | No | Se usa el valor anterior; la web toma el nombre de *Ajustes → branding.clinic_name*. |
| `MEDICAL_PANEL_ENABLED` | Activa `/medico`, la historia clínica, las fórmulas del portal y el rol Médico. | `false` | No | Panel apagado (404). **Solo `true` tras la revisión médico-legal.** |
| `TAYRAI_ENABLED` | Activa la sincronización de leads con TayrAI. Reemplaza el ajuste `tayrai.enabled`. | vacío → usa `tayrai.enabled` de la base | No | Se usa el valor de la base; si tampoco existe, la integración queda apagada. |
| `TAYRAI_API_KEY` | Token de la API de TayrAI. Reemplaza el ajuste `tayrai.api_key`. | vacío → usa `tayrai.api_key` de la base | Solo si TayrAI está activo | El lead **se guarda igual**; no se envía al CRM y queda un aviso `warning` en el log (máximo uno por hora). |
| `TAYRAI_WEBHOOK_SECRET` | Secreto HMAC para verificar el webhook. Reemplaza el ajuste `tayrai.webhook_secret`. | vacío → usa `tayrai.webhook_secret` de la base | Solo si TayrAI llama al webhook | El webhook responde **401** (nunca 500) y queda un aviso `warning` en el log. |
| `TAYRAI_BASE_URL` | URL base de la API de TayrAI. | `https://api.tayrai.com/v1` | No | Se usa el valor por defecto (el mismo que estaba fijo en el código). |
| `SANCTUM_EXPIRATION` | Minutos de vigencia de los tokens de la API móvil. | `43200` (30 días) | No | 30 días. Los tokens creados antes del deploy también vencen según su fecha de creación. |
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | Solo para `php artisan db:seed` (administrador inicial). **No se usan en tiempo de ejecución.** | sin valor | Solo si ejecutas el seeder fuera de local | El seeder falla con un mensaje claro (en la terminal, no en la web). **No ejecutes el seeder en producción con datos reales**: no hace falta para este deploy. |

**Valores de TayrAI guardados hoy en la base** (`site_settings`, grupo `tayrai`, claves `enabled`, `api_key` y `webhook_secret`):

- **Ninguna migración los borra ni los modifica.** Se quedan como están.
- La aplicación usa primero la variable de entorno; **si está vacía, usa el valor de la base**. Puedes desplegar sin definir las variables y TayrAI seguirá funcionando como hoy.
- Las variables son **globales** (una sola cuenta de TayrAI para todas las clínicas). Si alguna vez tienes varias clínicas con cuentas distintas, deja las variables vacías y usa los ajustes por clínica.
- La tabla de *Ajustes del sitio* ya no muestra esos valores (aparecen como `•••••••• (oculto)`).
- Recomendado, una vez confirmado que TayrAI funciona con las variables: borra `tayrai.api_key` y `tayrai.webhook_secret` de *Ajustes del sitio*, para que el secreto viva solo en Dokploy.

### Migraciones que correrán en producción

`origin/main` no tiene estas cinco; todas son aditivas (crean tablas o agregan columnas):

1. `2026_11_10_000100_rename_brand_to_doctor_kevin`: renombra `clinics.name` y `branding.clinic_name` solo si valen exactamente "Clínica Rejuvenezk", "Clinica Rejuvenezk" o "Doctor Kevin Rejuvenezk".
2. `2026_11_10_000200_create_patients_tables`: `patients`, `patient_treatments` y `services.renewal_interval_days`.
3. `2026_11_22_000100_add_roles_and_doctor_accounts`: `users.role` con valor por defecto `admin`. **Todos los usuarios existentes quedan como Administrador** (probado). También agrega columnas de médico y de datos clínicos del paciente.
4. `2026_11_22_000200_create_clinical_records_tables`: consultas y fórmulas (vacías mientras el panel médico esté apagado).
5. `2026_11_22_000300_create_service_requests_and_audit_logs`: solicitudes de renovación, auditoría, y `patient_id`/`doctor_id` (opcionales) en citas.

Ninguna toca leads, servicios, galería, ajustes (salvo el nombre de marca), usuarios ni citas existentes.

**Los `down()` que borrarían datos de pacientes están protegidos**: si las tablas tienen filas, el rollback se cancela con un mensaje. En producción **no se usa `migrate:rollback`**; se revierte el código (ver más abajo).

### Prueba previa con el respaldo (recomendada)

> **Probado el 22/11/2026** con un respaldo real de producción sobre MariaDB **11.8.9** (`SELECT VERSION()` = `11.8.9-MariaDB`). Las 5 migraciones aplicaron sin errores; el rollback del lote y la reaplicación también. El usuario existente (id 1) quedó con `role = admin` y entró a `/admin` con el menú completo. Servicios (24), ajustes (38), leads y galería se ven con sus datos, sin errores 500 ni de JavaScript. La marca se renombró en `clinics.name` y `branding.clinic_name`.

En una máquina con Docker, sin tocar producción:

```bash
docker run -d --name rejuv-prueba -p 127.0.0.1:3307:3306 \
  -e MARIADB_ROOT_PASSWORD=prueba -e MARIADB_DATABASE=prueba mariadb:11.8
# esperar a que acepte conexiones
docker exec -i rejuv-prueba mariadb -uroot -pprueba prueba < mariadb.sql

# Solo en esta terminal (no editar .env):
export DB_CONNECTION=mariadb DB_HOST=127.0.0.1 DB_PORT=3307 DB_DATABASE=prueba DB_USERNAME=root DB_PASSWORD=prueba CACHE_STORE=array SESSION_DRIVER=file
php artisan migrate:status
php artisan migrate --force
php artisan migrate:rollback --force   # lote nuevo, aún sin datos de pacientes
php artisan migrate --force
php artisan serve --port=8080           # entrar a /admin con el usuario existente

docker rm -f rejuv-prueba               # limpiar al terminar
```

### Orden de despliegue en Dokploy

**Antes del deploy**

1. **Cambia `APP_KEY` una sola vez, ahora, antes del primer deploy con datos cifrados**, y no la vuelvas a cambiar:
   - Genera la nueva clave en tu máquina: `php artisan key:generate --show` (copia el valor `base64:…`; no edites `.env`).
   - En Dokploy → *Environment*, reemplaza `APP_KEY` por ese valor y **guárdalo en un gestor de secretos** junto con la fecha.
   - Es seguro hacerlo ahora porque todavía no hay datos cifrados en la base: las tablas de pacientes e historia clínica se crean en este deploy y, con `MEDICAL_PANEL_ENABLED=false`, siguen vacías.
   - Efectos del cambio: se cierran todas las sesiones del panel y del portal (hay que volver a ingresar) y se invalidan los "recordarme". Los tokens de la API (Sanctum) **no** se ven afectados.
   - **Desde ese momento, `APP_KEY` no se vuelve a cambiar nunca.** Si alguna vez fuera imprescindible, la clave anterior debe ir en `APP_PREVIOUS_KEYS` y hay que volver a cifrar los datos. Nunca reemplaces la clave sin más.
2. Haz un **respaldo** de la base de producción y descárgalo:
   ```bash
   mariadb-dump --single-transaction --routines --triggers -h <host> -u <usuario> -p <base> > respaldo-antes-portal-$(date +%F).sql
   ```
   Respalda también los volúmenes `storage/app/public` y `storage/app/private` (si ya existe).
3. (Recomendado) Ejecuta la **prueba previa con el respaldo** de la sección anterior.
4. En Dokploy → *Environment*, agrega:
   ```dotenv
   MEDICAL_PANEL_ENABLED=false
   SANCTUM_EXPIRATION=43200
   APP_NAME="Dr. Kevin Rejuvenezk"
   # Opcional: si las defines, tienen prioridad sobre Ajustes del sitio
   TAYRAI_ENABLED=
   TAYRAI_API_KEY=
   TAYRAI_WEBHOOK_SECRET=
   ```
   Comprueba `APP_ENV=production` y `APP_DEBUG=false`. No agregues `ADMIN_EMAIL`/`ADMIN_PASSWORD`: no se usan en este deploy.
5. En Dokploy → *Volumes*, deben quedar **dos volúmenes persistentes**, uno junto al otro:
   - `storage/app/public`: ya existe; imágenes, videos y logos.
   - `storage/app/private`: **nuevo**; firmas de los médicos y subidas temporales de Livewire.
   Créalo antes del deploy para que la primera ejecución ya escriba en el volumen.
6. Anota el commit que está desplegado hoy (`origin/main` = `efad3b7`) para un posible rollback.

**Durante el deploy**

7. Despliega la rama o commit nuevo. El *Build Command* no cambia (`composer install … && npm ci && npm run build`).
8. Al arrancar, el contenedor prepara storage y cachés **sin migrar** (ver "Arranque del contenedor"). Las migraciones las ejecuta el post-deploy (`composer run deploy`): carpetas de storage, `migrate --force`, `storage:link`, limpieza de cachés, `filament:upgrade`, `config:cache`, `route:cache` y `view:cache`. Revisa en el log del deploy que las **5 migraciones** aparezcan como `DONE`.
   Si tu servicio no ejecuta el post-deploy, lánzalo a mano desde la terminal del contenedor:
   ```bash
   php artisan migrate --force
   php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
9. Mientras se ejecutan las migraciones (unos segundos), el login de `/admin` puede fallar porque `users.role` todavía no existe. Se normaliza solo al terminar.

**Después del deploy**

10. Terminal del contenedor:
    ```bash
    php artisan migrate:status | tail -6                  # las 5 nuevas en "Ran"
    php artisan tinker --execute="echo App\Models\User::query()->pluck('role','email');"   # todos 'admin'
    php artisan route:list --path=medico                  # sin rutas (panel apagado)
    ```
11. Navegador:
    - `/up` responde 200.
    - En la portada, el título y la marca dicen "Dr. Kevin Rejuvenezk"; los servicios, la galería y los testimonios muestran sus datos.
    - `/medico` responde **404**.
    - `/admin`: entra con el administrador de siempre. Ves todo el menú (Contenido, Procedimientos, Pacientes, CRM y Configuración → *Usuarios y roles*, *Auditoría*) y los leads, servicios, ajustes y galería existentes.
    - `/sitemap.xml` es válido. `/ingresar` y `/registro` cargan.
12. Envía un lead de prueba desde el formulario: aparece en *Leads*. Si TayrAI está activo, revisa que se sincronice y que el log no tenga avisos `TayrAI…`:
    ```bash
    grep -i "tayrai" storage/logs/laravel.log | tail
    ```
13. Desactiva desde el admin el testimonio de ejemplo "Paciente Demo" si existe.
14. Envía `https://<tu-dominio>/sitemap.xml` en Google Search Console.

### Revertir solo el código (sin borrar datos de pacientes)

**No uses `php artisan migrate:rollback` en producción**: borraría tablas. Los `down()` con datos de pacientes se niegan, pero aun así no es la vía.

0. **Antes de revertir**: si creaste usuarios con rol *Recepción* o *Médico*, quítales el acceso (cambia su clave o elimínalos desde *Usuarios y roles*). El código anterior no conoce los roles y les daría acceso completo al panel. Lista de afectados:
   ```bash
   php artisan tinker --execute="echo App\Models\User::query()->where('role','!=','admin')->pluck('email');"
   ```
1. En Dokploy, vuelve a desplegar el commit anterior (`efad3b7`) o la imagen anterior (*Deployments → Rollback*).
2. En la terminal del contenedor: `php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache`.
3. Las tablas y columnas nuevas **se quedan en la base** y el código anterior las ignora. Son compatibles: `users.role` y las columnas nuevas de citas tienen valores por defecto o aceptan nulos. Los pacientes, solicitudes y la auditoría siguen guardados para cuando se vuelva a desplegar la versión nueva.
4. El nombre de marca ya renombrado se queda; si hace falta, se cambia desde *Ajustes*.
5. Solo si hubiera corrupción de datos: restaura el respaldo del paso 2 (pierdes lo creado después del deploy).

## Contenido público: ajustes opcionales y datos de ejemplo

### Cifras de la portada

Las cifras de la franja de confianza ("+15 años de experiencia", etc.) ya **no** salen de la tabla `stats` (el seeder antiguo la llenaba con valores de ejemplo). Se crean en `/admin` → *Ajustes del sitio* → *Crear*, grupo `branding`, tipo *Texto corto*:

| Clave | Ejemplo de valor |
|---|---|
| `stat_1_value` / `stat_1_label` | `+15` / `años de experiencia` |
| `stat_2_value` / `stat_2_label` | … |
| `stat_3_value` / `stat_3_label` | … |
| `stat_4_value` / `stat_4_label` | … |

Una cifra se muestra solo si tiene valor **y** etiqueta. Sin ninguna, la franja muestra los textos `trust_item_1` … `trust_item_4`. Usa solo cifras reales y comprobables.

### Horarios en /contacto

Clave `business_hours`, grupo `contact`, tipo *Texto largo* (un horario por línea). Vacía = no se muestra.

### Reseñas

Solo se publican reseñas **activas y con enlace a la opinión original** (`source_url`). Las demás quedan en el panel pero no salen en el sitio ni en `/api/home`.

### Limpiar datos de ejemplo en producción (desde /admin, sin SQL)

Versiones antiguas del seeder crearon datos de ejemplo. Revisa y borra o corrige en `/admin`:

- *Testimonios*: "Paciente Demo" ("Este bloque también es editable desde Filament"). Ya no se muestra, pero conviene borrarlo.
- *Ajustes del sitio*: `hero_subtitle` = "Una plataforma comercializable…". El sitio lo reemplaza, pero conviene corregirlo.
- *Leads*: "Lead Demo".
