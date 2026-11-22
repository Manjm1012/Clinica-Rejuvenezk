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
3. **`APP_KEY` no puede cambiar nunca**: cifra las notas clínicas, las alergias y los antecedentes. Si se pierde o se cambia, esos datos quedan ilegibles. Guárdala en un gestor de secretos.
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
