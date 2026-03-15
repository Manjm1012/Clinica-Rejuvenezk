# Clinica Rejuvenezk — Guía de despliegue local y CI

Breve guía para poner el proyecto en marcha localmente (XAMPP) y un workflow básico de CI.

Requisitos locales
- XAMPP (Apache + MySQL) instalado y corriendo.
- PHP >= 8.1 (la distribución XAMPP incluye PHP).
- Composer instalado globalmente.
- Node.js >= 16 y npm.

Pasos rápidos (desde la raíz del proyecto)

1. Copiar el `.env` y configurarlo:

```powershell
cd "C:/ruta/a/Clinica-Rejuvenezk"
copy .env.example .env
# Editar .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL y WHATICKET_*
```

2. Instalar dependencias PHP y JS:

```powershell
composer install --no-interaction --prefer-dist
npm install
```

3. Generar `APP_KEY`, crear DB y ejecutar migraciones/seeds:

```powershell
php artisan key:generate
# Crear la base de datos (MySQL):
# Usando phpMyAdmin o desde la línea de comandos:
# CREATE DATABASE clinica_rejuvenezk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
php artisan migrate --seed
php artisan storage:link
```

4. Compilar assets y servir:

```powershell
npm run build   # para producción
# o para desarrollo en caliente
npm run dev
# Asegurarse que Apache sirve la carpeta `public` (se puede crear un junction en XAMPP htdocs)
# Ejemplo Windows (ejecutar como Admin):
REM mklink /J "C:\xampp\htdocs\Clinica-Rejuvenezk" "C:\ruta\a\Clinica-Rejuvenezk\public"
```

Notas para multitenancy (DB única)
- El proyecto usa `client_id` y un `TenantResolver` middleware; por defecto se resuelve el tenant vía header `X-Tenant-Slug`, subdominio o query param `tenant`.
- Aplique `HasTenant` trait a los modelos para aislamiento lógico.

CI básico (GitHub Actions)
- El repo incluye un workflow que instala dependencias y ejecuta `phpunit` en MySQL service.

Si quieres, puedo generar un VirtualHost de Apache y un script de despliegue automatizado.
