# Clinica Rejuvenezk

Aplicacion web para Clinica Rejuvenezk construida con Laravel 12, Filament 3 y Vite.

## Stack

- PHP 8.2+
- Laravel 12
- Filament 3.3
- Node.js 20+
- SQLite para desarrollo y CI

## Requisitos

- PHP 8.2 o superior
- Composer
- Node.js 20 o superior
- npm

## Instalacion local

```bash
composer install
copy .env.example .env
php artisan key:generate
type nul > database\database.sqlite
php artisan migrate
npm install
npm run build
php artisan serve
```

Tambien puedes usar el script incluido:

```bash
composer run setup
```

## Variables de entorno importantes

- `APP_URL`: URL base de la aplicacion
- `DB_CONNECTION`: `sqlite` para desarrollo rapido o `mysql` para produccion
- `DB_DATABASE`: ruta del archivo SQLite o nombre de base de datos MySQL
- `MAIL_*`: configuracion del proveedor de correo

La integracion con TayrAI se administra desde la configuracion persistida de la aplicacion y no desde variables de entorno.

## Scripts utiles

- `composer run dev`: servidor Laravel, cola, logs y Vite en paralelo
- `composer test`: limpia cache de config y ejecuta tests
- `npm run build`: compila assets de produccion

## CI en GitHub Actions

El repositorio incluye un workflow en `.github/workflows/ci.yml` que:

- instala dependencias PHP y Node
- prepara SQLite para el entorno de CI
- ejecuta migraciones
- compila assets con Vite
- corre la suite de tests con `php artisan test`

## Despliegue

La guia operativa esta en `DEPLOYMENT.md` e incluye:

- checklist de variables de entorno
- pasos de despliegue en servidor Linux
- permisos requeridos en `storage` y `bootstrap/cache`
- comandos de optimizacion posteriores al deploy

## Rutas publicas principales

- `/`: pagina de inicio
- `/procedimientos/{slug}`: detalle de servicios
- `/contacto`: captura de leads
- `/webhooks/tayrai`: endpoint de integracion
